<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CartItem extends Model
{
    protected $fillable = [
        'cart_id', 
        'product_id', 
        'name', 
        'quantity', 
        'price', 
        'total', 
        'options', 
        'rowId'
    ];
    
    protected $casts = [
        'options' => 'array',
        'price' => 'float',
        'total' => 'float',
        'quantity' => 'integer',
    ];
    
    /**
     * Get the cart that owns the item
     */
    public function cart(): BelongsTo
    {
        return $this->belongsTo(Cart::class);
    }
    
    /**
     * Get the product associated with the item
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
    
    /**
     * Generate a unique rowId for a cart item
     * 
     * @param int $id
     * @param array $options
     * @return string
     */
    public static function generateRowId($id, $options = [])
    {
        return md5($id . serialize($options));
    }
    
    /**
     * Update the item quantity and recalculate total
     * 
     * @param int $quantity
     * @return $this
     */
    public function updateQuantity($quantity)
    {
        $this->quantity = $quantity;
        $this->total = $this->price * $this->quantity;
        $this->save();
        
        // Recalculate cart total
        $this->cart->recalculate();
        
        return $this;
    }
    
    /**
     * Update the item price and recalculate total
     * 
     * @param float $price
     * @return $this
     */
    public function updatePrice($price)
    {
        $this->price = $price;
        $this->total = $this->price * $this->quantity;
        $this->save();
        
        // Recalculate cart total
        $this->cart->recalculate();
        
        return $this;
    }
}
