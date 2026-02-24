<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cart extends Model
{
    protected $fillable = ['user_id', 'instance', 'total'];

    public function items(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function recalculate(): static
    {
        $this->total = $this->items->sum('total');
        $this->save();

        return $this;
    }

    public function getFormattedContent(): \Illuminate\Support\Collection
    {
        $this->loadMissing('items');

        return $this->items->map(fn (CartItem $item) => (object) [
            'id' => $item->product_id,
            'name' => $item->name,
            'qty' => $item->quantity,
            'price' => $item->price,
            'weight' => 1,
            'options' => (object) ($item->options ?? []),
            'rowId' => $item->rowId,
            'subtotal' => $item->total,
        ]);
    }
}
