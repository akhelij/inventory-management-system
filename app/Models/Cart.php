<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Cart extends Model
{
    protected $fillable = ['user_id', 'instance', 'total'];
    
    /**
     * Get the items in the cart
     */
    public function items(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }
    
    /**
     * Get the user that owns the cart
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    
    /**
     * Recalculate the cart total based on items
     */
    public function recalculate()
    {
        $this->total = $this->items->sum('total');
        $this->save();
        
        return $this;
    }
    
    /**
     * Get cart content formatted for compatibility with existing code
     */
    public function getFormattedContent()
    {
        // Ensure items are eager loaded
        if (!$this->relationLoaded('items')) {
            $this->load('items');
        }
        
        return $this->items->map(function ($item) {
            return (object) [
                'id' => $item->product_id,
                'name' => $item->name,
                'qty' => $item->quantity,
                'price' => $item->price,
                'weight' => 1,
                'options' => (object) ($item->options ?? []),
                'rowId' => $item->rowId,
                'subtotal' => $item->total,
            ];
        });
    }
}
