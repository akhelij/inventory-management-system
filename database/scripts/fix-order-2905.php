<?php

// Retroactive stock fix for order 2905 (INV-002827) — mirrors FixApprovedOrdersStock apply block.
use App\Models\Order;
use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Support\Facades\DB;

$order = Order::with('details')->findOrFail(2905);

if ($order->invoice_no !== 'INV-002827') {
    throw new RuntimeException("Order 2905 is {$order->invoice_no}, expected INV-002827 — aborting.");
}

if ($order->stock_affected) {
    echo "SKIP: order {$order->invoice_no} already has stock_affected=1\n";
} else {
    DB::transaction(function () use ($order): void {
        foreach ($order->details as $item) {
            $product = Product::withTrashed()->findOrFail($item->product_id);
            $newQuantity = $product->quantity - $item->quantity;

            $product->update(['quantity' => $newQuantity]);

            StockMovement::create([
                'product_id' => $product->id,
                'order_id' => $order->id,
                'movement_type' => 'deducted',
                'quantity' => $item->quantity,
                'balance_after' => $newQuantity,
                'reason' => "Order #{$order->invoice_no} approved (retroactive fix)",
                'user_id' => $order->user_id,
            ]);

            echo "  {$product->name}: -{$item->quantity} => {$newQuantity}\n";
        }

        $order->update(['stock_affected' => true]);
    });

    echo "DONE: {$order->invoice_no} stock_affected=".($order->fresh()->stock_affected ? '1' : '0')."\n";
}
