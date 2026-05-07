<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InstallmentGuarantee extends Model
{
    protected $fillable = [
        'payment_schedule_id',
        'type',
        'person_customer_id',
        'cheque_nature',
        'cheque_amount',
        'cheque_bank',
        'cheque_echeance',
        'cheque_account_holder',
        'cheque_photo',
        'user_id',
    ];

    protected $casts = [
        'cheque_echeance' => 'date',
        'cheque_amount' => 'decimal:2',
    ];

    public function schedule(): BelongsTo
    {
        return $this->belongsTo(PaymentSchedule::class, 'payment_schedule_id');
    }

    public function person(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'person_customer_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    protected static function booted(): void
    {
        static::creating(fn (self $g) => $g->user_id = $g->user_id ?? auth()->id());
    }
}
