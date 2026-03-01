<?php

namespace App\Models;

use App\Traits\HasActivityLogs;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InstallmentEntry extends Model
{
    use HasActivityLogs;

    protected $fillable = [
        'payment_schedule_id',
        'installment_number',
        'amount',
        'due_date',
        'status',
        'payment_id',
        'paid_at',
        'notes',
    ];

    protected $casts = [
        'due_date' => 'date',
        'paid_at' => 'date',
    ];

    public function schedule(): BelongsTo
    {
        return $this->belongsTo(PaymentSchedule::class, 'payment_schedule_id');
    }

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }

    public function scopeOverdue($query): void
    {
        $query->where('status', 'pending')
            ->where('due_date', '<', now()->startOfDay());
    }

    public function scopeDueSoon($query, int $days = 3): void
    {
        $query->where('status', 'pending')
            ->whereBetween('due_date', [now()->startOfDay(), now()->addDays($days)]);
    }
}
