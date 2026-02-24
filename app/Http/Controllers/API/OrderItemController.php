<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderDetails;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderItemController extends Controller
{
    public function index(int $orderId): JsonResponse
    {
        Order::findOrFail($orderId);

        $items = OrderDetails::with(['product' => fn ($query) => $query->select('id', 'name', 'quantity', 'selling_price', 'product_image')])
            ->where('order_id', $orderId)
            ->get();

        return response()->json($items);
    }

    public function store(Request $request, int $orderId): JsonResponse
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
            'unitcost' => 'required|numeric|min:0',
        ]);

        $order = Order::findOrFail($orderId);

        if (OrderDetails::where('order_id', $orderId)->where('product_id', $validated['product_id'])->exists()) {
            return response()->json([
                'status' => 'error',
                'message' => 'This product is already in the order',
            ], 400);
        }

        $product = Product::findOrFail($validated['product_id']);

        if ($validated['quantity'] > $product->quantity) {
            return response()->json([
                'status' => 'error',
                'message' => "Requested quantity exceeds available stock ({$product->quantity})",
            ], 400);
        }

        try {
            DB::beginTransaction();

            $orderDetail = OrderDetails::create([
                'order_id' => $orderId,
                'product_id' => $validated['product_id'],
                'quantity' => $validated['quantity'],
                'unitcost' => $validated['unitcost'],
                'total' => $validated['quantity'] * $validated['unitcost'],
            ]);

            $this->updateOrderTotals($order);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Item added to order',
                'data' => $orderDetail,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function update(Request $request, int $orderId, int $itemId): JsonResponse
    {
        $validated = $request->validate([
            'quantity' => 'sometimes|integer|min:1',
            'unitcost' => 'sometimes|numeric|min:0',
        ]);

        $order = Order::findOrFail($orderId);
        $orderDetail = OrderDetails::findOrFail($itemId);

        if ($orderDetail->order_id != $orderId) {
            return response()->json([
                'status' => 'error',
                'message' => 'Item does not belong to this order',
            ], 400);
        }

        if (isset($validated['quantity'])) {
            $product = Product::findOrFail($orderDetail->product_id);

            if ($validated['quantity'] > $product->quantity) {
                return response()->json([
                    'status' => 'error',
                    'message' => "Requested quantity exceeds available stock ({$product->quantity})",
                ], 400);
            }
        }

        try {
            DB::beginTransaction();

            $orderDetail->fill($validated);
            $orderDetail->total = $orderDetail->quantity * $orderDetail->unitcost;
            $orderDetail->save();

            $this->updateOrderTotals($order);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Item updated',
                'data' => $orderDetail,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function destroy(int $orderId, int $itemId): JsonResponse
    {
        $order = Order::findOrFail($orderId);
        $orderDetail = OrderDetails::findOrFail($itemId);

        if ($orderDetail->order_id != $orderId) {
            return response()->json([
                'status' => 'error',
                'message' => 'Item does not belong to this order',
            ], 400);
        }

        try {
            DB::beginTransaction();

            $orderDetail->delete();
            $this->updateOrderTotals($order);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Item removed from order',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    private function updateOrderTotals(Order $order): Order
    {
        $details = OrderDetails::where('order_id', $order->id)->get();
        $total = $details->sum('total');
        $due = $total - ($order->pay ?? 0);

        $order->update([
            'total_products' => $details->count(),
            'sub_total' => $total,
            'vat' => 0,
            'total' => $total,
            'due' => $due,
        ]);

        return $order;
    }
}
