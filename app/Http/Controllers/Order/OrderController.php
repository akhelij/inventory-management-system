<?php

namespace App\Http\Controllers\Order;

use App\Enums\OrderStatus;
use App\Enums\PermissionEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\Order\OrderStoreRequest;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderDetails;
use App\Models\Product;
use App\Models\User;
use Carbon\Carbon;
use Gloudemans\Shoppingcart\Facades\Cart;
use Haruncpi\LaravelIdGenerator\IdGenerator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Str;

class OrderController extends Controller
{
    public function index()
    {
        abort_unless(auth()->user()->can(PermissionEnum::READ_ORDERS), 403);

        return view('orders.index', [
            'orders' => 1,
        ]);
    }

    public function store(OrderStoreRequest $request)
    {
        try {
            DB::beginTransaction();
            abort_unless(auth()->user()->can(PermissionEnum::CREATE_ORDERS), 403);
            //            $customer = Customer::find($request->customer_id);
            //            $approve_automatically = $customer->email === Customer::ALAMI;

            $order = Order::create([
                'customer_id' => $request->customer_id,
                'payment_type' => $request->payment_type,
                'pay' => $request->pay ?? 0,
                'order_date' => Carbon::now()->format('Y-m-d'),
                'order_status' => OrderStatus::PENDING, // $approve_automatically ? OrderStatus::APPROVED : OrderStatus::PENDING,
                'total_products' => auth()->check() ? auth()->user()->getCart()->count() : 0,
                'sub_total' => auth()->check() ? auth()->user()->getCart()->sum('subtotal') : 0,
                'vat' => auth()->check() ? auth()->user()->getCart()->sum('tax') : 0,
                'total' => auth()->check() ? auth()->user()->getCart()->sum('total') : 0,
                'invoice_no' => IdGenerator::generate([
                    'table' => 'orders',
                    'field' => 'invoice_no',
                    'length' => 10,
                    'prefix' => 'INV-',
                ]),
                'due' => (auth()->check() ? auth()->user()->getCart()->sum('total') : 0) - $request->pay,
                'user_id' => $request->author_id ?? auth()->id(),
                'tagged_user_id' => $request->tagged_user_id,
                'uuid' => Str::uuid(),
            ]);

            // Create Order Details
            if (auth()->check()) {
                $contents = auth()->user()->getCart();
                $oDetails = [];

                foreach ($contents as $content) {
                    $oDetails['order_id'] = $order['id'];
                    $oDetails['product_id'] = $content->id;
                    $oDetails['quantity'] = $content->qty;
                    $oDetails['unitcost'] = $content->price;
                    $oDetails['total'] = $content->subtotal;
                    $oDetails['created_at'] = Carbon::now();

                    OrderDetails::insert($oDetails);
                }
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()
                ->back()
                ->with('error', $e->getMessage());
        }

        if (auth()->check()) {
            auth()->user()->clearCart();
        }

        return redirect()
            ->route('orders.index')
            ->with('success', 'Order has been created!');
    }

    public function create()
    {
        abort_unless(auth()->user()->can(PermissionEnum::CREATE_ORDERS), 403);
        
        return view('orders.create', [
            'products' => Product::with(['category', 'unit'])->get(),
            'customers' => Customer::ofAuth()->get(['id', 'name']),
            'users' => User::query()->get(['id', 'name']),
            'carts' => auth()->check() ? auth()->user()->getCart() : collect(),
        ]);
    }

    public function show($uuid)
    {
        abort_unless(auth()->user()->can(PermissionEnum::READ_ORDERS), 403);
        $order = Order::where('uuid', $uuid)->firstOrFail();
        $order->loadMissing(['customer', 'details'])->get();

        return view('orders.'.($order->order_status === null ? 'edit' : 'show'), [
            'products' => Product::with(['category', 'unit'])->get(),
            'customers' => Customer::ofAuth()->get(['id', 'name']),
            'order' => $order,
        ]);
    }

    public function updateItems(Order $order, Request $request)
    {
        abort_unless(auth()->user()->can(PermissionEnum::UPDATE_ORDERS), 403);
        $order->details()->delete();
        $oDetails = [];
        foreach ($request->product_id as $key => $product_id) {
            $oDetails['order_id'] = $order['id'];
            $oDetails['product_id'] = $product_id;
            $oDetails['quantity'] = $request->quantity[$key];
            $oDetails['unitcost'] = $request->unitcost[$key];
            $oDetails['total'] = $request->total[$key];
            $oDetails['created_at'] = Carbon::now();

            OrderDetails::insert($oDetails);
        }

        return redirect()
            ->route('orders.index')
            ->with('success', 'Order items has been updated!');
    }

    public function updateStatus(Order $order, int $order_status, Request $request)
    {
        abort_unless(auth()->user()->can(PermissionEnum::UPDATE_ORDERS_STATUS), 403);
        abort_unless(in_array($order_status, [OrderStatus::APPROVED, OrderStatus::CANCELED]), 403);

        $customer = Customer::find($order->customer_id);

        if ($order_status == OrderStatus::APPROVED && $customer->is_out_of_limit) {
            abort(403, 'Customer is out of limit');
        }

        $order->update([
            'order_status' => $order_status,
            'reason' => $request->reason,
        ]);

        return redirect()
            ->route('orders.index')
            ->with('success', 'Order status has been updated!');
    }

    public function update(Order $order)
    {
        abort_if($order->order_status != null, 403, 'Only pending orders can be updated');
        abort_unless(auth()->user()->can(PermissionEnum::UPDATE_ORDERS), 403);

        $details = OrderDetails::where('order_id', $order->id)->get();
        $total = 0;

        foreach ($details as $item) {
            $total += $item->total;
        }

        $order->update([
            'total_products' => $details->count(),
            'sub_total' => $total,
            'vat' => 0,
            'total' => $total,
            'due' => $total,
        ]);

        return redirect()
            ->route('orders.index')
            ->with('success', 'Order has been completed!');
    }

    public function destroy($uuid)
    {
        abort_unless(auth()->user()->can(PermissionEnum::DELETE_ORDERS), 403);

        try {
            DB::beginTransaction();
            $order = Order::where('uuid', $uuid)->firstOrFail();
            if ($order->order_status != null) {
                $details = OrderDetails::where('order_id', $order->id)->get();
                foreach ($details as $detail) {
                    $product = Product::find($detail->product_id);
                    $product->update([
                        'quantity' => $product->quantity + $detail->quantity,
                    ]);
                }
            }
            $order->delete();
            DB::commit();

            return redirect()
                ->route('orders.index')
                ->with('success', 'Order has been deleted!');
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()
                ->route('orders.index')
                ->with('error', $e->getMessage());
        }
    }

    public function downloadInvoice($uuid)
    {
        abort_unless(auth()->user()->can(PermissionEnum::READ_ORDERS), 403);
        $order = Order::with(['customer', 'details'])->where('uuid', $uuid)->firstOrFail();

        return view('orders.print-invoice', [
            'order' => $order,
        ]);
    }

    public function bulkDownloadInvoice(Request $request)
    {
        abort_unless(auth()->user()->can(PermissionEnum::READ_ORDERS), 403);
        $orders = Order::with(['customer', 'details', 'user'])->whereIn('id', $request->order_ids)->get();

        return view('orders.bulk-print-invoice', [
            'orders' => $orders,
        ]);
    }
}
