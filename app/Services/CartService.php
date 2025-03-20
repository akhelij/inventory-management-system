<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;

class CartService
{
    /**
     * The current cart instance
     *
     * @var string
     */
    protected $instance = 'default';

    /**
     * Set the current cart instance
     *
     * @param string $instance
     * @return $this
     */
    public function instance($instance)
    {
        $this->instance = $instance;
        
        return $this;
    }

    /**
     * Get the current user ID or null if not authenticated
     *
     * @return int|null
     */
    protected function getCurrentUserId()
    {
        return auth()->check() ? auth()->id() : null;
    }

    /**
     * Get or create a cart for a user
     *
     * @param int|null $userId
     * @param string $instance
     * @return Cart
     */
    public function getCart($userId = null, $instance = null)
    {
        $userId = $userId ?? $this->getCurrentUserId();
        $instance = $instance ?? $this->instance;
        
        if (!$userId) {
            // Return an empty cart for unauthenticated users
            $cart = new Cart();
            $cart->instance = $instance;
            return $cart;
        }
        
        return Cart::with('items')->firstOrCreate(
            ['user_id' => $userId, 'instance' => $instance],
            ['total' => 0]
        );
    }
    
    /**
     * Add an item to the cart
     *
     * @param int|null $userId
     * @param int $productId
     * @param string $name
     * @param float $price
     * @param int $quantity
     * @param array $options
     * @return CartItem
     */
    public function addItem($userId = null, $productId, $name, $price, $quantity = 1, $options = [])
    {
        $userId = $userId ?? $this->getCurrentUserId();
        $instance = $this->instance;
        
        if (!$userId) {
            // Cannot add items for unauthenticated users
            return null;
        }
        
        $cart = $this->getCart($userId, $instance);
        
        // Generate a unique row ID for the item
        $rowId = CartItem::generateRowId($productId, $options);
        
        // Check if the item already exists in the cart
        $existingItem = $cart->items()->where('rowId', $rowId)->first();
        
        if ($existingItem) {
            // Update quantity if the item already exists
            $existingItem->quantity += $quantity;
            $existingItem->total = $existingItem->price * $existingItem->quantity;
            $existingItem->save();
            
            // Recalculate cart total
            $cart->recalculate();
            
            return $existingItem;
        }
        
        // Create a new cart item
        $item = $cart->items()->create([
            'product_id' => $productId,
            'name' => $name,
            'quantity' => $quantity,
            'price' => $price,
            'total' => $price * $quantity,
            'options' => $options,
            'rowId' => $rowId
        ]);
        
        // Recalculate cart total
        $cart->recalculate();
        
        return $item;
    }
    
    /**
     * Update the quantity of a cart item
     *
     * @param int|null $userId
     * @param string $rowId
     * @param int $quantity
     * @return CartItem|null
     */
    public function updateQuantity($userId = null, $rowId, $quantity)
    {
        $userId = $userId ?? $this->getCurrentUserId();
        $instance = $this->instance;
        
        if (!$userId) {
            return null;
        }
        
        $cart = $this->getCart($userId, $instance);
        $item = $cart->items()->where('rowId', $rowId)->first();
        
        if (!$item) {
            return null;
        }
        
        $item->quantity = $quantity;
        $item->total = $item->price * $quantity;
        $item->save();
        
        // Recalculate cart total
        $cart->recalculate();
        
        return $item;
    }
    
    /**
     * Update the price of a cart item
     *
     * @param int|null $userId
     * @param string $rowId
     * @param float $price
     * @return CartItem|null
     */
    public function updatePrice($userId = null, $rowId, $price)
    {
        $userId = $userId ?? $this->getCurrentUserId();
        $instance = $this->instance;
        
        if (!$userId) {
            return null;
        }
        
        $cart = $this->getCart($userId, $instance);
        $item = $cart->items()->where('rowId', $rowId)->first();
        
        if (!$item) {
            return null;
        }
        
        $item->price = $price;
        $item->total = $price * $item->quantity;
        $item->save();
        
        // Recalculate cart total
        $cart->recalculate();
        
        return $item;
    }
    
    /**
     * Update the options for a cart item
     *
     * @param int|null $userId
     * @param string $rowId
     * @param array $options
     * @return CartItem|null
     */
    public function updateItemOptions($userId = null, $rowId, $options)
    {
        $userId = $userId ?? $this->getCurrentUserId();
        $instance = $this->instance;
        
        if (!$userId) {
            return null;
        }
        
        $cart = $this->getCart($userId, $instance);
        $item = $cart->items()->where('rowId', $rowId)->first();
        
        if (!$item) {
            return null;
        }
        
        // Merge existing options with new options
        $newOptions = array_merge($item->options ?? [], $options);
        $item->options = $newOptions;
        $item->save();
        
        return $item;
    }
    
    /**
     * Remove an item from the cart
     *
     * @param int|null $userId
     * @param string $rowId
     * @return bool
     */
    public function removeItem($userId = null, $rowId)
    {
        $userId = $userId ?? $this->getCurrentUserId();
        $instance = $this->instance;
        
        if (!$userId) {
            return false;
        }
        
        $cart = $this->getCart($userId, $instance);
        $deleted = $cart->items()->where('rowId', $rowId)->delete();
        
        // Recalculate cart total
        $cart->recalculate();
        
        return $deleted > 0;
    }
    
    /**
     * Clear the cart
     *
     * @param int|null $userId
     * @param string|null $instance
     * @return Cart
     */
    public function clearCart($userId = null, $instance = null)
    {
        $userId = $userId ?? $this->getCurrentUserId();
        $instance = $instance ?? $this->instance;
        
        if (!$userId) {
            return null;
        }
        
        $cart = $this->getCart($userId, $instance);
        $cart->items()->delete();
        $cart->total = 0;
        $cart->save();
        
        return $cart;
    }
    
    /**
     * Get the cart content
     *
     * @param int|null $userId
     * @param string|null $instance
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function content($userId = null, $instance = null)
    {
        $userId = $userId ?? $this->getCurrentUserId();
        $instance = $instance ?? $this->instance;
        
        if (!$userId) {
            return collect();
        }
        
        $cart = $this->getCart($userId, $instance);
        return $cart->getFormattedContent();
    }
    
    /**
     * Count the items in the cart
     *
     * @param int|null $userId
     * @param string|null $instance
     * @return int
     */
    public function count($userId = null, $instance = null)
    {
        $userId = $userId ?? $this->getCurrentUserId();
        $instance = $instance ?? $this->instance;
        
        if (!$userId) {
            return 0;
        }
        
        $cart = $this->getCart($userId, $instance);
        return $cart->items->sum('quantity');
    }
    
    /**
     * Get cart subtotal
     *
     * @param int|null $userId
     * @param string|null $instance
     * @return float
     */
    public function subtotal($userId = null, $instance = null)
    {
        $userId = $userId ?? $this->getCurrentUserId();
        $instance = $instance ?? $this->instance;
        
        if (!$userId) {
            return 0;
        }
        
        $cart = $this->getCart($userId, $instance);
        return $cart->items->sum('total');
    }
    
    /**
     * Get cart tax
     *
     * @param int|null $userId
     * @param string|null $instance
     * @param float $taxRate
     * @return float
     */
    public function tax($userId = null, $instance = null, $taxRate = 0.1)
    {
        $userId = $userId ?? $this->getCurrentUserId();
        $instance = $instance ?? $this->instance;
        
        if (!$userId) {
            return 0;
        }
        
        $subtotal = $this->subtotal($userId, $instance);
        return $subtotal * $taxRate;
    }
    
    /**
     * Get cart total
     *
     * @param int|null $userId
     * @param string|null $instance
     * @return float
     */
    public function total($userId = null, $instance = null)
    {
        $userId = $userId ?? $this->getCurrentUserId();
        $instance = $instance ?? $this->instance;
        
        if (!$userId) {
            return 0;
        }
        
        $cart = $this->getCart($userId, $instance);
        return $cart->total;
    }
}
