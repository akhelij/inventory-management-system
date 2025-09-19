<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class StockService
{
    /**
     * Deduct stock for an order
     */
    public function deductStockForOrder(Order $order): bool
    {
        if ($order->stock_affected) {
            return true; // Already deducted
        }

        try {
            DB::beginTransaction();

            foreach ($order->details as $item) {
                $product = Product::find($item->product_id);
                
                if (!$product) {
                    throw new \Exception("Product not found: {$item->product_id}");
                }

                if ($product->quantity < $item->quantity) {
                    throw new \Exception("Insufficient stock for product: {$product->name}. Available: {$product->quantity}, Required: {$item->quantity}");
                }

                // Deduct stock
                $newQuantity = $product->quantity - $item->quantity;
                $product->update(['quantity' => $newQuantity]);

                // Log stock movement
                $this->logStockMovement(
                    $product->id,
                    $order->id,
                    'deducted',
                    $item->quantity,
                    $newQuantity,
                    "Order #{$order->invoice_no} approved"
                );
            }

            // Mark order as stock affected
            $order->update(['stock_affected' => true]);

            DB::commit();
            return true;

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Restore stock for an order
     */
    public function restoreStockForOrder(Order $order): bool
    {
        if (!$order->stock_affected) {
            return true; // No stock to restore
        }

        try {
            DB::beginTransaction();

            foreach ($order->details as $item) {
                $product = Product::find($item->product_id);
                
                if (!$product) {
                    continue; // Skip if product was deleted
                }

                // Restore stock
                $newQuantity = $product->quantity + $item->quantity;
                $product->update(['quantity' => $newQuantity]);

                // Log stock movement
                $this->logStockMovement(
                    $product->id,
                    $order->id,
                    'restored',
                    $item->quantity,
                    $newQuantity,
                    "Order #{$order->invoice_no} canceled/deleted"
                );
            }

            // Mark order as stock not affected
            $order->update(['stock_affected' => false]);

            DB::commit();
            return true;

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Check if order can be approved (sufficient stock)
     */
    public function canApproveOrder(Order $order): array
    {
        $issues = [];

        foreach ($order->details as $item) {
            $product = Product::find($item->product_id);
            
            if (!$product) {
                $issues[] = "Product not found: ID {$item->product_id}";
                continue;
            }

            if ($product->quantity < $item->quantity) {
                $issues[] = "Insufficient stock for {$product->name}. Available: {$product->quantity}, Required: {$item->quantity}";
            }
        }

        return [
            'can_approve' => empty($issues),
            'issues' => $issues
        ];
    }

    /**
     * Adjust stock difference when order items are updated
     */
    public function adjustStockForOrderUpdate(Order $order, array $oldQuantities, array $newQuantities): bool
    {
        if (!$order->stock_affected) {
            return true; // Order hasn't affected stock yet
        }

        try {
            DB::beginTransaction();

            foreach ($newQuantities as $productId => $newQty) {
                $oldQty = $oldQuantities[$productId] ?? 0;
                $difference = $newQty - $oldQty;

                if ($difference == 0) {
                    continue; // No change
                }

                $product = Product::find($productId);
                if (!$product) {
                    continue;
                }

                // Calculate new stock quantity
                $newStockQuantity = $product->quantity - $difference;
                
                if ($newStockQuantity < 0) {
                    throw new \Exception("Insufficient stock for {$product->name}. Cannot increase order quantity.");
                }

                $product->update(['quantity' => $newStockQuantity]);

                // Log stock movement
                $movementType = $difference > 0 ? 'deducted' : 'restored';
                $this->logStockMovement(
                    $product->id,
                    $order->id,
                    $movementType,
                    abs($difference),
                    $newStockQuantity,
                    "Order #{$order->invoice_no} items updated"
                );
            }

            DB::commit();
            return true;

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Log stock movement
     */
    private function logStockMovement(int $productId, ?int $orderId, string $movementType, int $quantity, int $balanceAfter, string $reason = null): void
    {
        StockMovement::create([
            'product_id' => $productId,
            'order_id' => $orderId,
            'movement_type' => $movementType,
            'quantity' => $quantity,
            'balance_after' => $balanceAfter,
            'reason' => $reason,
            'user_id' => Auth::id(),
        ]);
    }

    /**
     * Get stock movement history for a product
     */
    public function getStockHistory(int $productId, int $limit = 50): \Illuminate\Database\Eloquent\Collection
    {
        return StockMovement::with(['order', 'user'])
            ->where('product_id', $productId)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }
}
