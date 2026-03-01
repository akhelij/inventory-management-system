<?php

namespace App\Http\Controllers\Order;

use App\Enums\OrderStatus;
use App\Enums\PermissionEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\Order\OrderStoreRequest;
use App\Models\Customer;
use App\Models\InstallmentEntry;
use App\Models\Order;
use App\Models\OrderDetails;
use App\Models\PaymentSchedule;
use App\Models\Product;
use App\Models\User;
use App\Services\StockService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Haruncpi\LaravelIdGenerator\IdGenerator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function index(): View
    {
        abort_unless(Auth::user()->can(PermissionEnum::READ_ORDERS), 403);

        return view('orders.index', [
            'orders' => 1,
        ]);
    }

    public function create(): View
    {
        abort_unless(Auth::user()->can(PermissionEnum::CREATE_ORDERS), 403);

        return view('orders.create', [
            'products' => Product::with(['category', 'unit'])->get(),
            'customers' => Customer::ofAuth()->orderBy('name')->get(['id', 'name']),
            'users' => User::query()->get(['id', 'name']),
        ]);
    }

    public function store(OrderStoreRequest $request): RedirectResponse
    {
        try {
            DB::beginTransaction();
            abort_unless(Auth::user()->can(PermissionEnum::CREATE_ORDERS), 403);

            $cartData = $request->has('cart_data')
                ? json_decode($request->input('cart_data'), true)
                : [];

            if (empty($cartData)) {
                return redirect()->back()->with('error', 'Cannot create an order with an empty cart');
            }

            $subTotal = collect($cartData)->sum('subtotal');

            $order = Order::create([
                'customer_id' => $request->customer_id,
                'payment_type' => $request->payment_type,
                'pay' => $request->pay ?? 0,
                'order_date' => Carbon::now()->format('Y-m-d'),
                'order_status' => OrderStatus::PENDING,
                'total_products' => count($cartData),
                'sub_total' => $subTotal,
                'vat' => 0,
                'total' => $subTotal,
                'invoice_no' => IdGenerator::generate([
                    'table' => 'orders',
                    'field' => 'invoice_no',
                    'length' => 10,
                    'prefix' => 'INV-',
                ]),
                'due' => $subTotal - ($request->pay ?? 0),
                'user_id' => $request->author_id ?? Auth::id(),
                'tagged_user_id' => $request->tagged_user_id,
                'uuid' => Str::uuid(),
            ]);

            foreach ($cartData as $item) {
                OrderDetails::create([
                    'order_id' => $order->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['qty'],
                    'unitcost' => $item['price'],
                    'total' => $item['subtotal'],
                ]);
            }

            if ($request->input('payment_mode') === 'installment') {
                $installmentCount = (int) $request->input('installment_count', 4);
                $periodDays = (int) $request->input('installment_period_days', 30);

                $schedule = PaymentSchedule::create([
                    'order_id' => $order->id,
                    'customer_id' => $order->customer_id,
                    'total_installments' => $installmentCount,
                    'period_days' => $periodDays,
                    'total_amount' => $order->total,
                    'user_id' => Auth::id(),
                ]);

                $baseAmount = floor($order->total / $installmentCount * 100) / 100;
                $remainder = $order->total - ($baseAmount * ($installmentCount - 1));

                for ($i = 1; $i <= $installmentCount; $i++) {
                    InstallmentEntry::create([
                        'payment_schedule_id' => $schedule->id,
                        'installment_number' => $i,
                        'amount' => $i === $installmentCount ? $remainder : $baseAmount,
                        'due_date' => now()->addDays($periodDays * $i),
                        'status' => 'pending',
                    ]);
                }
            }

            app(\App\Http\Controllers\CartController::class)->clear();

            DB::commit();

            return to_route('orders.index')->with('success', 'Order has been created!');
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function show(string $uuid): View
    {
        abort_unless(Auth::user()->can(PermissionEnum::READ_ORDERS), 403);

        $order = Order::where('uuid', $uuid)->firstOrFail();
        $order->loadMissing(['customer', 'details']);

        $viewName = $order->order_status === null ? 'orders.edit' : 'orders.show';

        return view($viewName, [
            'products' => Product::with(['category', 'unit'])->get(),
            'customers' => Customer::ofAuth()->orderBy('name')->get(['id', 'name']),
            'users' => User::query()->get(['id', 'name']),
            'order' => $order,
        ]);
    }

    public function updateItems(Order $order, Request $request): RedirectResponse
    {
        abort_unless(Auth::user()->can(PermissionEnum::UPDATE_ORDERS), 403);

        if (! $request->has('product_id') || empty($request->product_id)) {
            return redirect()->back()->with('error', 'Cannot update an order with no items');
        }

        $order->details()->delete();

        foreach ($request->product_id as $key => $product_id) {
            OrderDetails::insert([
                'order_id' => $order->id,
                'product_id' => $product_id,
                'quantity' => $request->quantity[$key],
                'unitcost' => $request->unitcost[$key],
                'total' => $request->total[$key],
                'created_at' => Carbon::now(),
            ]);
        }

        return to_route('orders.index')->with('success', 'Order items has been updated!');
    }

    public function updateStatus(Order $order, int $order_status, Request $request): RedirectResponse
    {
        abort_unless(Auth::user()->can(PermissionEnum::UPDATE_ORDERS_STATUS), 403);
        abort_unless(in_array($order_status, [OrderStatus::APPROVED, OrderStatus::CANCELED]), 403);

        $customer = Customer::find($order->customer_id);

        if ($order_status == OrderStatus::APPROVED && $customer->is_out_of_limit) {
            abort(403, 'Customer is out of limit');
        }

        if ($order_status == OrderStatus::APPROVED) {
            $stockCheck = app(StockService::class)->canApproveOrder($order);

            if (! $stockCheck['can_approve']) {
                return redirect()->back()
                    ->with('error', 'Cannot approve order due to insufficient stock: '.implode(', ', $stockCheck['issues']));
            }
        }

        $order->update([
            'order_status' => $order_status,
            'reason' => $request->reason,
        ]);

        return to_route('orders.index')->with('success', 'Order status has been updated!');
    }

    public function update(Request $request, Order $order): RedirectResponse
    {
        abort_if($order->order_status != null, 403, 'Only pending orders can be updated');
        abort_unless(Auth::user()->can(PermissionEnum::UPDATE_ORDERS), 403);

        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'purchase_date' => 'required|date',
            'payment_type' => 'required|in:HandCash,Cheque,Exchange',
            'author_id' => 'nullable|exists:users,id',
            'tagged_user_id' => 'nullable|exists:users,id',
        ]);

        $details = OrderDetails::where('order_id', $order->id)->get();

        if ($details->isEmpty()) {
            return redirect()->back()->with('error', 'Cannot update an order with no items');
        }

        $total = $details->sum('total');

        $updateData = [
            'customer_id' => $validated['customer_id'],
            'purchase_date' => $validated['purchase_date'],
            'payment_type' => $validated['payment_type'],
            'total_products' => $details->count(),
            'sub_total' => $total,
            'vat' => 0,
            'total' => $total,
            'due' => $total,
        ];

        if (isset($validated['author_id'])) {
            $updateData['user_id'] = $validated['author_id'];
        }

        if (isset($validated['tagged_user_id'])) {
            $updateData['tagged_user_id'] = $validated['tagged_user_id'];
        }

        $order->update($updateData);

        return to_route('orders.index')->with('success', 'Order has been updated successfully!');
    }

    public function destroy(string $uuid): RedirectResponse
    {
        abort_unless(Auth::user()->can(PermissionEnum::DELETE_ORDERS), 403);

        try {
            DB::beginTransaction();

            $order = Order::where('uuid', $uuid)->firstOrFail();

            if ($order->stock_affected) {
                app(StockService::class)->restoreStockForOrder($order);
            }

            $order->delete();

            DB::commit();

            return to_route('orders.index')->with('success', 'Order has been deleted!');
        } catch (\Exception $e) {
            DB::rollBack();

            return to_route('orders.index')->with('error', $e->getMessage());
        }
    }

    public function downloadInvoice(string $uuid)
    {
        abort_unless(Auth::user()->can(PermissionEnum::READ_ORDERS), 403);

        $order = Order::with(['customer', 'details.product', 'user', 'payments'])->where('uuid', $uuid)->firstOrFail();

        return Pdf::loadView('orders.pdf-invoice', ['order' => $order])
            ->setPaper('a4', 'portrait')
            ->stream("invoice-{$order->invoice_no}.pdf");
    }

    public function bulkDownloadInvoice(Request $request)
    {
        abort_unless(Auth::user()->can(PermissionEnum::READ_ORDERS), 403);

        $orders = Order::with(['customer', 'details.product', 'user', 'payments'])
            ->whereIn('id', $request->order_ids)
            ->get();

        return Pdf::loadView('orders.pdf-bulk-invoice', ['orders' => $orders])
            ->setPaper('a4', 'portrait')
            ->stream('orders-report-'.now()->format('Y-m-d').'.pdf');
    }

    public function recalculateTotals(Request $request): JsonResponse
    {
        abort_unless(Auth::user()->can(PermissionEnum::UPDATE_ORDERS), 403);

        try {
            $orderIds = $request->input('order_ids', []);

            $orders = empty($orderIds)
                ? Order::all()
                : Order::whereIn('id', $orderIds)->get();

            $updatedCount = 0;
            $results = [];

            foreach ($orders as $order) {
                $oldTotal = $order->total;
                $details = OrderDetails::where('order_id', $order->id)->get();
                $newTotal = $details->sum('total');
                $due = $newTotal - ($order->pay ?? 0);

                $order->update([
                    'total_products' => $details->count(),
                    'sub_total' => $newTotal,
                    'vat' => 0,
                    'total' => $newTotal,
                    'due' => $due,
                ]);

                if ($oldTotal != $newTotal) {
                    $updatedCount++;
                    $results[] = "Order #{$order->id} (Invoice: {$order->invoice_no}): Total updated from {$oldTotal} to {$newTotal}";
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Order totals recalculated successfully',
                'orders_updated' => $updatedCount,
                'total_orders' => $orders->count(),
                'details' => $results,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error recalculating totals: '.$e->getMessage(),
            ], 500);
        }
    }
}
