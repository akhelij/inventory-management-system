<?php

namespace App\Observers;

use App\Models\Order;
use App\Models\Product;

class OrderObserver
{
    /**
     * Handle the Order "created" event.
     */
    public function created(Order $order): void
    {
        if ($order->order_status == 1){
            foreach($order->details as $item) {
                $product = Product::find($item->product_id);
                $product->quantity = $product->quantity - $item->quantity;
                $product->save();
            }
        }
    }

    /**
     * Handle the Order "updated" event.
     */
    public function updated(Order $order): void
    {
        if ($order->wasChanged('order_status') && $order->order_status == 1){
           foreach($order->details as $item) {
               $product = Product::find($item->product_id);
               $product->quantity = $product->quantity - $item->quantity;
               $product->save();
           }
        }
    }

    /**
     * Handle the Order "deleted" event.
     */
    public function deleted(Order $order): void
    {
        //
    }

    /**
     * Handle the Order "restored" event.
     */
    public function restored(Order $order): void
    {
        //
    }

    /**
     * Handle the Order "force deleted" event.
     */
    public function forceDeleted(Order $order): void
    {
        //
    }
}
