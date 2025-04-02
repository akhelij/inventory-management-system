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
use Haruncpi\LaravelIdGenerator\IdGenerator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cookie;

class OrderController extends Controller
{
    public function index()
    {
        abort_unless(Auth::user()->can(PermissionEnum::READ_ORDERS), 403);

        return view('orders.index', [
            'orders' => 1,
        ]);
    }

    public function store(OrderStoreRequest $request)
    {
        try {
            DB::beginTransaction();
            abort_unless(Auth::user()->can(PermissionEnum::CREATE_ORDERS), 403);
            
            // Get cart data from form submission
            $cartData = [];
            if ($request->has('cart_data')) {
                $cartData = json_decode($request->input('cart_data'), true);
            }
            
            // Check if cart is empty
            if (empty($cartData)) {
                return redirect()
                    ->back()
                    ->with('error', 'Cannot create an order with an empty cart');
            }
            
            // Calculate totals
            $totalProducts = count($cartData);
            $subTotal = 0;
            
            foreach ($cartData as $item) {
                $subTotal += $item['subtotal'];
            }
            
            $total = $subTotal;

            $order = Order::create([
                'customer_id' => $request->customer_id,
                'payment_type' => $request->payment_type,
                'pay' => $request->pay ?? 0,
                'order_date' => Carbon::now()->format('Y-m-d'),
                'order_status' => OrderStatus::PENDING,
                'total_products' => $totalProducts,
                'sub_total' => $subTotal,
                'vat' => 0,
                'total' => $total,
                'invoice_no' => IdGenerator::generate([
                    'table' => 'orders',
                    'field' => 'invoice_no',
                    'length' => 10,
                    'prefix' => 'INV-',
                ]),
                'due' => $total - ($request->pay ?? 0),
                'user_id' => $request->author_id ?? Auth::id(),
                'tagged_user_id' => $request->tagged_user_id,
                'uuid' => Str::uuid(),
            ]);

            // Create Order Details from cart data
            foreach ($cartData as $item) {
                OrderDetails::create([
                    'order_id' => $order->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['qty'],
                    'unitcost' => $item['price'],
                    'total' => $item['subtotal'],
                ]);
            }
            
            DB::commit();
            
            return redirect()
                ->route('orders.index')
                ->with('success', 'Order has been created!');
                
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()
                ->back()
                ->with('error', $e->getMessage());
        }
    }

    public function create()
    {
        abort_unless(Auth::user()->can(PermissionEnum::CREATE_ORDERS), 403);
        
        return view('orders.create', [
            'products' => Product::with(['category', 'unit'])->get(),
            'customers' => Customer::ofAuth()->get(['id', 'name']),
            'users' => User::query()->get(['id', 'name']),
        ]);
    }

    public function show($uuid)
    {
        abort_unless(Auth::user()->can(PermissionEnum::READ_ORDERS), 403);
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
        abort_unless(Auth::user()->can(PermissionEnum::UPDATE_ORDERS), 403);
        
        // Check if product_id array is empty
        if (!$request->has('product_id') || empty($request->product_id)) {
            return redirect()
                ->back()
                ->with('error', 'Cannot update an order with no items');
        }
        
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
        abort_unless(Auth::user()->can(PermissionEnum::UPDATE_ORDERS_STATUS), 403);
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
        abort_unless(Auth::user()->can(PermissionEnum::UPDATE_ORDERS), 403);

        $details = OrderDetails::where('order_id', $order->id)->get();
        
        // Check if order has no items
        if ($details->isEmpty()) {
            return redirect()
                ->back()
                ->with('error', 'Cannot update an order with no items');
        }
        
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
        abort_unless(Auth::user()->can(PermissionEnum::DELETE_ORDERS), 403);

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
        abort_unless(Auth::user()->can(PermissionEnum::READ_ORDERS), 403);
        $order = Order::with(['customer', 'details'])->where('uuid', $uuid)->firstOrFail();

        return view('orders.print-invoice', [
            'order' => $order,
        ]);
    }

    public function bulkDownloadInvoice(Request $request)
    {
        abort_unless(Auth::user()->can(PermissionEnum::READ_ORDERS), 403);
        $orders = Order::with(['customer', 'details', 'user'])->whereIn('id', $request->order_ids)->get();

        return view('orders.bulk-print-invoice', [
            'orders' => $orders,
        ]);
    }
}
