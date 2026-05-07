<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class StockService
{
    public function deductStockForOrder(Order $order): bool
    {
        if ($order->stock_affected) {
            return true;
        }

        return DB::transaction(function () use ($order): bool {
            foreach ($order->details as $item) {
                $product = Product::findOrFail($item->product_id);

                if ($product->quantity < $item->quantity) {
                    throw new \RuntimeException(
                        "Insufficient stock for product: {$product->name}. Available: {$product->quantity}, Required: {$item->quantity}"
                    );
                }

                $newQuantity = $product->quantity - $item->quantity;
                $product->update(['quantity' => $newQuantity]);

                $this->logStockMovement(
                    $product->id,
                    $order->id,
                    'deducted',
                    $item->quantity,
                    $newQuantity,
                    "Order #{$order->invoice_no} approved",
                );
            }

            $order->update(['stock_affected' => true]);

            return true;
        });
    }

    public function restoreStockForOrder(Order $order): bool
    {
        if (! $order->stock_affected) {
            return true;
        }

        return DB::transaction(function () use ($order): bool {
            foreach ($order->details as $item) {
                $product = Product::find($item->product_id);

                if (! $product) {
                    continue;
                }

                $newQuantity = $product->quantity + $item->quantity;
                $product->update(['quantity' => $newQuantity]);

                $this->logStockMovement(
                    $product->id,
                    $order->id,
                    'restored',
                    $item->quantity,
                    $newQuantity,
                    "Order #{$order->invoice_no} canceled/deleted",
                );
            }

            $order->update(['stock_affected' => false]);

            return true;
        });
    }

    public function canApproveOrder(Order $order): array
    {
        $issues = [];

        foreach ($order->details as $item) {
            $product = Product::find($item->product_id);

            if (! $product) {
                $issues[] = "Product not found: ID {$item->product_id}";

                continue;
            }

            if ($product->quantity < $item->quantity) {
                $issues[] = "Insufficient stock for {$product->name}. Available: {$product->quantity}, Required: {$item->quantity}";
            }
        }

        return [
            'can_approve' => empty($issues),
            'issues' => $issues,
        ];
    }

    public function adjustStockForOrderUpdate(Order $order, array $oldQuantities, array $newQuantities): bool
    {
        if (! $order->stock_affected) {
            return true;
        }

        return DB::transaction(function () use ($order, $oldQuantities, $newQuantities): bool {
            foreach ($newQuantities as $productId => $newQty) {
                $oldQty = $oldQuantities[$productId] ?? 0;
                $difference = $newQty - $oldQty;

                if ($difference === 0) {
                    continue;
                }

                $product = Product::find($productId);

                if (! $product) {
                    continue;
                }

                $newStockQuantity = $product->quantity - $difference;

                if ($newStockQuantity < 0) {
                    throw new \RuntimeException(
                        "Insufficient stock for {$product->name}. Cannot increase order quantity."
                    );
                }

                $product->update(['quantity' => $newStockQuantity]);

                $this->logStockMovement(
                    $product->id,
                    $order->id,
                    $difference > 0 ? 'deducted' : 'restored',
                    abs($difference),
                    $newStockQuantity,
                    "Order #{$order->invoice_no} items updated",
                );
            }

            return true;
        });
    }

    public function transferStock(Product $source, Product $destination, int $quantity, ?string $reason = null): bool
    {
        if ($source->id === $destination->id) {
            throw new \RuntimeException('Source and destination products must differ.');
        }

        if ($quantity < 1) {
            throw new \RuntimeException('Transfer quantity must be at least 1.');
        }

        if ($source->quantity < $quantity) {
            throw new \RuntimeException("Insufficient stock for {$source->name}. Available: {$source->quantity}, Requested: {$quantity}");
        }

        return DB::transaction(function () use ($source, $destination, $quantity, $reason): bool {
            $sourceNew = $source->quantity - $quantity;
            $source->update(['quantity' => $sourceNew]);

            $destNew = $destination->quantity + $quantity;
            $destination->update(['quantity' => $destNew]);

            $this->logStockMovement($source->id, null, 'transferred_out', $quantity, $sourceNew, $reason);
            $this->logStockMovement($destination->id, null, 'transferred_in', $quantity, $destNew, $reason);

            return true;
        });
    }

    private function logStockMovement(
        int $productId,
        ?int $orderId,
        string $movementType,
        int $quantity,
        int $balanceAfter,
        ?string $reason = null,
    ): void {
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

    public function getStockHistory(int $productId, int $limit = 50): Collection
    {
        return StockMovement::with(['order', 'user'])
            ->where('product_id', $productId)
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get();
    }
}
