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
        $query = Order::query();
        if(!auth()->user()->hasRole('admin'))
        {
            $query->where("user_id", auth()->id());
        }
        return view('orders.index', [
            'orders' => $query->count()
        ]);
    }

    public function create()
    {
        abort_unless(auth()->user()->can(PermissionEnum::CREATE_ORDERS), 403);

        return view('orders.create', [
            'products' => Product::with(['category', 'unit'])->get(),
            'customers' => Customer::where("user_id", auth()->id())->get(['id', 'name']),
            'carts' => Cart::content(),
        ]);
    }

    public function store(OrderStoreRequest $request)
    {
        abort_unless(auth()->user()->can(PermissionEnum::CREATE_ORDERS), 403);
        $customer = Customer::find($request->customer_id);

        $order = Order::create([
            'customer_id' => $request->customer_id,
            'payment_type' => $request->payment_type,
            'pay' => $request->pay ?? 0,
            'order_date' => Carbon::now()->format('Y-m-d'),
            'order_status' => ($customer->name === Customer::ALAMI) ? OrderStatus::APPROVED : OrderStatus::PENDING,
            'total_products' => Cart::count(),
            'sub_total' => Cart::subtotal(),
            'vat' => Cart::tax(),
            'total' => Cart::total(),
            'invoice_no' => IdGenerator::generate([
                'table' => 'orders',
                'field' => 'invoice_no',
                'length' => 10,
                'prefix' => 'INV-'
            ]),
            'due' => (Cart::total() - $request->pay),
            "user_id" => auth()->id(),
            "uuid" => Str::uuid(),
        ]);

        // Create Order Details
        $contents = Cart::content();
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

        // Delete Cart Sopping History
        Cart::destroy();

        return redirect()
            ->route('orders.index')
            ->with('success', 'Order has been created!');
    }

    public function show($uuid)
    {
        abort_unless(auth()->user()->can(PermissionEnum::READ_ORDERS), 403);
        $order = Order::where("uuid", $uuid)->firstOrFail();
        $order->loadMissing(['customer', 'details'])->get();

        return view('orders.'. ($order->order_status == null ? 'edit' : 'show'), [
            'products' => Product::with(['category', 'unit'])->get(),
            'customers' => Customer::where("user_id", auth()->id())->get(['id', 'name']),
            'order' => $order
        ]);
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

    public function update_status(Order $order, Int $order_status, Request $request){
        $order->update([
            'order_status' => $order_status,
            'reason' => $request->reason
        ]);

        return redirect()
            ->route('orders.index')
            ->with('success', 'Order status has been updated!');
    }

    public function destroy($uuid)
    {
        abort_unless(auth()->user()->can(PermissionEnum::DELETE_ORDERS), 403);
        $order = Order::where("uuid", $uuid)->firstOrFail();
        $order->delete();
    }

    public function downloadInvoice($uuid)
    {
        abort_unless(auth()->user()->can(PermissionEnum::READ_ORDERS), 403);
        $order = Order::with(['customer', 'details'])->where("uuid", $uuid)->firstOrFail();

        return view('orders.print-invoice', [
            'order' => $order,
        ]);
    }
}
