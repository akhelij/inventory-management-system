<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class ShoppingCart extends Model
{
    protected $table = 'shoppingcart';

    protected $fillable = [
        'identifier',
        'instance',
        'content',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'identifier', 'id');
    }

    public function getContent(): Collection
    {
        try {
            $decoded = base64_decode($this->content);

            Log::info('Cart content (raw): '.$this->content);
            Log::info('Cart content (decoded): '.$decoded);

            $unserialized = unserialize($decoded);

            return $unserialized instanceof Collection ? $unserialized : collect($unserialized);
        } catch (\Exception $e) {
            Log::error('Cart deserialization error: '.$e->getMessage());

            return collect();
        }
    }

    public static function clearCart(int $userId): bool
    {
        try {
            return (bool) static::where('identifier', $userId)->delete();
        } catch (\Exception $e) {
            Log::error('Error clearing cart: '.$e->getMessage());

            return false;
        }
    }
}
