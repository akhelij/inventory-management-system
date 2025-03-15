<?php

namespace App\Models;

use App\Observers\PaymentObserver;
use App\Traits\HasActivityLogs;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[ObservedBy([PaymentObserver::class])]
class Payment extends Model
{
    use HasActivityLogs;
    use HasFactory;

    protected $guarded = [];

    protected $with = [
        'customer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public static function boot()
    {
        parent::boot();

        static::creating(function ($payment) {
            $payment->user_id = auth()->id();
        });
    }
}
