<?php

namespace App\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \Illuminate\Support\Collection content()
 * @method static int count()
 * @method static float subtotal()
 * @method static float tax()
 * @method static float total()
 * @method static \App\Models\CartItem addItem($productId, $name, $price, $quantity = 1, $options = [])
 * @method static \App\Models\CartItem|null updateQuantity($rowId, $quantity)
 * @method static \App\Models\CartItem|null updatePrice($rowId, $price)
 * @method static bool removeItem($rowId)
 * @method static \App\Models\Cart clearCart()
 * @method static \App\Models\CartItem|null updateItemOptions($rowId, $options)
 * @method static \App\Services\CartService instance($instance)
 * 
 * @see \App\Services\CartService
 */
class Cart extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'cart';
    }
}
