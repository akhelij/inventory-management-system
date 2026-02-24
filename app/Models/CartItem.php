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
        'rowId',
    ];

    protected $casts = [
        'options' => 'array',
        'price' => 'float',
        'total' => 'float',
        'quantity' => 'integer',
    ];

    public function cart(): BelongsTo
    {
        return $this->belongsTo(Cart::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public static function generateRowId(int $id, array $options = []): string
    {
        return md5($id.serialize($options));
    }

    public function updateQuantity(int $quantity): static
    {
        $this->quantity = $quantity;
        $this->total = $this->price * $this->quantity;
        $this->save();

        $this->cart->recalculate();

        return $this;
    }

    public function updatePrice(float $price): static
    {
        $this->price = $price;
        $this->total = $this->price * $this->quantity;
        $this->save();

        $this->cart->recalculate();

        return $this;
    }
}
