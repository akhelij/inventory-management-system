<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShoppingCart extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'shoppingcart';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'identifier',
        'instance',
        'content',
    ];

    /**
     * Get the user that owns the cart.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'identifier', 'id');
    }

    /**
     * Get the cart content as a collection.
     *
     * @return \Illuminate\Support\Collection
     */
    public function getContent()
    {
        try {
            // Decode and unserialize the content
            $decoded = base64_decode($this->content);
            
            // For debugging
            \Illuminate\Support\Facades\Log::info('Cart content (raw): ' . $this->content);
            \Illuminate\Support\Facades\Log::info('Cart content (decoded): ' . $decoded);
            
            $unserialized = unserialize($decoded);
            
            // If it's already a collection, return it
            if ($unserialized instanceof \Illuminate\Support\Collection) {
                return $unserialized;
            }
            
            // Otherwise, create a new collection from the result
            return collect($unserialized);
        } catch (\Exception $e) {
            // Log the error for debugging
            \Illuminate\Support\Facades\Log::error('Cart deserialization error: ' . $e->getMessage());
            return collect();
        }
    }
}
