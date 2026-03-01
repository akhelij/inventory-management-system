<?php

namespace App\Models;

use App\Observers\PaymentObserver;
use App\Traits\HasActivityLogs;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Schema;

#[ObservedBy([PaymentObserver::class])]
class Payment extends Model
{
    use HasActivityLogs, HasFactory;

    protected $guarded = [];

    protected $with = ['customer'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function orders(): BelongsToMany
    {
        return $this->belongsToMany(Order::class, 'order_payment')
            ->withPivot('allocated_amount', 'user_id')
            ->withTimestamps();
    }

    public function getUnallocatedAmountAttribute(): float
    {
        if (! Schema::hasTable('order_payment')) {
            return (float) $this->amount;
        }

        if ($this->relationLoaded('orders')) {
            return $this->amount - $this->orders->sum('pivot.allocated_amount');
        }

        return $this->amount - $this->orders()->sum('order_payment.allocated_amount');
    }

    public function getIsFullyAllocatedAttribute(): bool
    {
        return $this->unallocated_amount <= 0;
    }

    protected static function booted(): void
    {
        static::creating(fn (Payment $payment) => $payment->user_id = auth()->id());
    }
}
