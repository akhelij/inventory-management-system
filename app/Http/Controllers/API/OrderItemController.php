<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderDetails;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderItemController extends Controller
{
    /**
     * Get all items for an order
     */
    public function index($orderId)
    {
        $order = Order::findOrFail($orderId);
        
        $items = OrderDetails::with(['product' => function($query) {
            $query->select('id', 'name', 'quantity', 'selling_price', 'product_image');
        }])
            ->where('order_id', $orderId)
            ->get();
            
        return response()->json($items);
    }
    
    /**
     * Add a new item to an order
     */
    public function store(Request $request, $orderId)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
            'unitcost' => 'required|numeric|min:0'
        ]);
        
        $order = Order::findOrFail($orderId);
        
        // Check if the product is already in the order
        $existingItem = OrderDetails::where('order_id', $orderId)
            ->where('product_id', $validated['product_id'])
            ->first();
            
        if ($existingItem) {
            return response()->json([
                'status' => 'error',
                'message' => 'This product is already in the order'
            ], 400);
        }
        
        // Check product stock availability
        $product = Product::findOrFail($validated['product_id']);
        
        if ($validated['quantity'] > $product->quantity) {
            return response()->json([
                'status' => 'error',
                'message' => 'Requested quantity exceeds available stock (' . $product->quantity . ')'
            ], 400);
        }
        
        try {
            DB::beginTransaction();
            
            // Create new order detail
            $orderDetail = OrderDetails::create([
                'order_id' => $orderId,
                'product_id' => $validated['product_id'],
                'quantity' => $validated['quantity'],
                'unitcost' => $validated['unitcost'],
                'total' => $validated['quantity'] * $validated['unitcost']
            ]);
            
            // Update order totals
            $this->updateOrderTotals($order);
            
            DB::commit();
            
            return response()->json([
                'status' => 'success',
                'message' => 'Item added to order',
                'data' => $orderDetail
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Update an order item
     */
    public function update(Request $request, $orderId, $itemId)
    {
        $validated = $request->validate([
            'quantity' => 'sometimes|integer|min:1',
            'unitcost' => 'sometimes|numeric|min:0'
        ]);
        
        $order = Order::findOrFail($orderId);
        $orderDetail = OrderDetails::findOrFail($itemId);
        
        if ($orderDetail->order_id != $orderId) {
            return response()->json([
                'status' => 'error',
                'message' => 'Item does not belong to this order'
            ], 400);
        }
        
        // Check stock availability if updating quantity
        if (isset($validated['quantity'])) {
            $product = Product::findOrFail($orderDetail->product_id);
            
            if ($validated['quantity'] > $product->quantity) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Requested quantity exceeds available stock (' . $product->quantity . ')'
                ], 400);
            }
        }
        
        try {
            DB::beginTransaction();
            
            // Update fields if provided
            if (isset($validated['quantity'])) {
                $orderDetail->quantity = $validated['quantity'];
            }
            
            if (isset($validated['unitcost'])) {
                $orderDetail->unitcost = $validated['unitcost'];
            }
            
            // Recalculate total
            $orderDetail->total = $orderDetail->quantity * $orderDetail->unitcost;
            $orderDetail->save();
            
            // Update order totals
            $this->updateOrderTotals($order);
            
            DB::commit();
            
            return response()->json([
                'status' => 'success',
                'message' => 'Item updated',
                'data' => $orderDetail
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Remove an item from an order
     */
    public function destroy($orderId, $itemId)
    {
        $order = Order::findOrFail($orderId);
        $orderDetail = OrderDetails::findOrFail($itemId);
        
        if ($orderDetail->order_id != $orderId) {
            return response()->json([
                'status' => 'error',
                'message' => 'Item does not belong to this order'
            ], 400);
        }
        
        try {
            DB::beginTransaction();
            
            // Delete the item
            $orderDetail->delete();
            
            // Update order totals
            $this->updateOrderTotals($order);
            
            DB::commit();
            
            return response()->json([
                'status' => 'success',
                'message' => 'Item removed from order'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Update order totals based on its items
     */
    private function updateOrderTotals(Order $order)
    {
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
        
        return $order;
    }
} 