<?php

namespace App\Models;

use App\Traits\HasActivityLogs;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PaymentSchedule extends Model
{
    use HasActivityLogs;

    protected $fillable = [
        'order_id',
        'customer_id',
        'total_installments',
        'period_days',
        'total_amount',
        'advance_amount',
        'advance_payment_id',
        'user_id',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function entries(): HasMany
    {
        return $this->hasMany(InstallmentEntry::class);
    }

    public function advancePayment(): BelongsTo
    {
        return $this->belongsTo(Payment::class, 'advance_payment_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
