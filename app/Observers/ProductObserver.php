<?php

namespace App\Observers;

use App\Models\Product;
use App\Models\ProductEntry;

class ProductObserver
{
    /**
     * Handle the Product "created" event.
     */
    public function created(Product $product): void
    {
        //
    }

    /**
     * Handle the Product "updating" event.
     */
    public function updating(Product $product): void
    {
        if ($product->isDirty('quantity')) {
            // Calculate the quantity added
            $quantityAdded = $product->quantity - $product->getOriginal('quantity');
            // If the quantity has increased
            if ($quantityAdded >= 0) {
                // Create a new product entry
                ProductEntry::create([
                    'product_id' => $product->id,
                    'user_id' => auth()->id(),
                    'quantity_added' => $quantityAdded,
                ]);
            }
        }
    }

    /**
     * Handle the Product "deleted" event.
     */
    public function deleted(Product $product): void
    {
        //
    }

    /**
     * Handle the Product "restored" event.
     */
    public function restored(Product $product): void
    {
        //
    }

    /**
     * Handle the Product "force deleted" event.
     */
    public function forceDeleted(Product $product): void
    {
        //
    }
}
