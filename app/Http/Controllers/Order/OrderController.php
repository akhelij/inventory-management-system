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
            'customers' => Customer::ofAuth()
                ->when(! Auth::user()->can(PermissionEnum::READ_CUSTOMERS), fn ($q) => $q->where('category', '!=', 'b2b'))
                ->when(! Auth::user()->can(PermissionEnum::READ_CLIENTS), fn ($q) => $q->where('category', '!=', 'b2c'))
                ->orderBy('name')
                ->get(['id', 'name']),
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
                return redirect()->back()->with('error', __('Cannot create an order with an empty cart'));
            }

            $subTotal = collect($cartData)->sum('subtotal');

            $order = Order::create([
                'customer_id' => $request->customer_id,
                'payment_type' => $request->payment_type,
                'pay' => 0,
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
                'due' => $subTotal,
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
                $advanceAmount = (float) $request->input('advance_amount', 0);
                $advancePaymentId = null;

                if ($advanceAmount > 0) {
                    $advanceType = $request->input('advance_type', 'HandCash');
                    $advanceDate = $request->input('advance_date')
                        ? Carbon::createFromFormat('d/m/Y', $request->input('advance_date'))->format('Y-m-d')
                        : now()->format('Y-m-d');
                    $advanceData = [
                        'customer_id' => $order->customer_id,
                        'date' => $advanceDate,
                        'nature' => 'ADV-'.$order->invoice_no,
                        'payment_type' => $advanceType,
                        'echeance' => $advanceType === 'Cheque'
                            ? Carbon::createFromFormat('d/m/Y', $request->input('advance_echeance'))->format('Y-m-d')
                            : $advanceDate,
                        'amount' => $advanceAmount,
                        'description' => $request->input('advance_description'),
                    ];

                    if ($advanceType === 'Cheque') {
                        $advanceData['bank'] = $request->input('advance_bank');
                        $advanceData['nature'] = $request->input('advance_nature', 'ADV-'.$order->invoice_no);
                        $advanceData['cheque_photo'] = $request->input('advance_cheque_photo');
                        $advanceData['cashed_in'] = $request->boolean('advance_cash_in_immediately');
                        $advanceData['cashed_in_at'] = $request->boolean('advance_cash_in_immediately') ? now() : null;
                    }

                    $advancePayment = \App\Models\Payment::create($advanceData);
                    $advancePaymentId = $advancePayment->id;

                    $order->payments()->attach($advancePayment->id, [
                        'allocated_amount' => $advanceAmount,
                        'user_id' => Auth::id(),
                    ]);
                    $order->recalculatePayments();
                }

                $installableAmount = $order->total - $advanceAmount;

                $schedule = PaymentSchedule::create([
                    'order_id' => $order->id,
                    'customer_id' => $order->customer_id,
                    'total_installments' => $installmentCount,
                    'period_days' => $periodDays,
                    'total_amount' => $order->total,
                    'advance_amount' => $advanceAmount,
                    'advance_payment_id' => $advancePaymentId,
                    'user_id' => Auth::id(),
                ]);

                $baseAmount = floor($installableAmount / $installmentCount * 100) / 100;
                $remainder = $installableAmount - ($baseAmount * ($installmentCount - 1));

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

            return to_route('orders.index')->with('success', __('Order has been created!'));
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
            'customers' => Customer::ofAuth()
                ->when(! Auth::user()->can(PermissionEnum::READ_CUSTOMERS), fn ($q) => $q->where('category', '!=', 'b2b'))
                ->when(! Auth::user()->can(PermissionEnum::READ_CLIENTS), fn ($q) => $q->where('category', '!=', 'b2c'))
                ->orderBy('name')
                ->get(['id', 'name']),
            'users' => User::query()->get(['id', 'name']),
            'order' => $order,
        ]);
    }

    public function updateItems(Order $order, Request $request): RedirectResponse
    {
        abort_unless(Auth::user()->can(PermissionEnum::UPDATE_ORDERS), 403);

        if (! $request->has('product_id') || empty($request->product_id)) {
            return redirect()->back()->with('error', __('Cannot update an order with no items'));
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

        return to_route('orders.index')->with('success', __('Order items has been updated!'));
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
                    ->with('error', __('Cannot approve order due to insufficient stock: ').implode(', ', $stockCheck['issues']));
            }
        }

        try {
            DB::transaction(function () use ($order, $order_status, $request): void {
                $order->update([
                    'order_status' => $order_status,
                    'reason' => $request->reason,
                ]);
            });
        } catch (\RuntimeException $e) {
            return redirect()->back()
                ->with('error', __('Failed to update order status: ').$e->getMessage());
        }

        return to_route('orders.index')->with('success', __('Order status has been updated!'));
    }

    public function update(Request $request, Order $order): RedirectResponse
    {
        abort_if($order->order_status != null, 403, 'Only pending orders can be updated');
        abort_unless(Auth::user()->can(PermissionEnum::UPDATE_ORDERS), 403);

        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'purchase_date' => 'required|date',
            'author_id' => 'nullable|exists:users,id',
            'tagged_user_id' => 'nullable|exists:users,id',
            'payment_type' => 'nullable|string|in:HandCash,Cheque,Exchange',
        ]);

        $details = OrderDetails::where('order_id', $order->id)->get();

        if ($details->isEmpty()) {
            return redirect()->back()->with('error', __('Cannot update an order with no items'));
        }

        $total = $details->sum('total');

        $totalAllocated = $order->payments()->sum('order_payment.allocated_amount');

        $updateData = [
            'customer_id' => $validated['customer_id'],
            'purchase_date' => $validated['purchase_date'],
            'payment_type' => $validated['payment_type'] ?? null,
            'total_products' => $details->count(),
            'sub_total' => $total,
            'vat' => 0,
            'total' => $total,
            'pay' => $totalAllocated,
            'due' => $total - $totalAllocated,
        ];

        if (isset($validated['author_id'])) {
            $updateData['user_id'] = $validated['author_id'];
        }

        if (isset($validated['tagged_user_id'])) {
            $updateData['tagged_user_id'] = $validated['tagged_user_id'];
        }

        $order->update($updateData);

        return to_route('orders.index')->with('success', __('Order has been updated successfully!'));
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

            return to_route('orders.index')->with('success', __('Order has been deleted!'));
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
                $totalAllocated = $order->payments()->sum('order_payment.allocated_amount');

                $order->update([
                    'total_products' => $details->count(),
                    'sub_total' => $newTotal,
                    'vat' => 0,
                    'total' => $newTotal,
                    'pay' => $totalAllocated,
                    'due' => $newTotal - $totalAllocated,
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
