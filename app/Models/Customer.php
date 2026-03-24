<?php

namespace App\Models;

use App\Enums\CustomerCategory;
use App\Traits\HasActivityLogs;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends Model
{
    use HasActivityLogs, HasFactory;

    protected $fillable = [
        'name',
        'email',
        'type',
        'category',
        'cin',
        'date_of_birth',
        'cin_photo',
        'phone',
        'address',
        'city',
        'limit',
        'account_holder',
        'account_number',
        'bank_name',
        'user_id',
        'uuid',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'category' => CustomerCategory::class,
    ];

    protected $appends = [
        'total_orders',
        'total_payments',
        'has_missed_installments',
        'have_unpaid_checks',
    ];

    protected $with = ['user'];

    public function getHasMissedInstallmentsAttribute(): bool
    {
        return $this->paymentSchedules()
            ->whereHas('entries', fn ($q) => $q->where('status', '!=', 'paid')->where('due_date', '<', now()->startOfDay()))
            ->exists();
    }

    public function getHaveUnpaidChecksAttribute(): bool
    {
        return $this->total_orders - $this->total_payments > 0;
    }

    public function getTotalOrdersAttribute(): float
    {
        return $this->orders->where('order_status', true)->sum('total');
    }

    public function getTotalPaymentsAttribute(): float
    {
        return $this->payments->sum('amount');
    }

    public function getTotalPendingPaymentsAttribute(): float
    {
        return $this->payments->where('cashed_in', 0)->sum('amount');
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class)->orderBy('created_at', 'desc');
    }

    public function quotations(): HasMany
    {
        return $this->hasMany(Quotation::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class)->orderBy('echeance', 'desc');
    }

    public function paymentSchedules(): HasMany
    {
        return $this->hasMany(PaymentSchedule::class);
    }

    public function scopeB2b($query): void
    {
        $query->where('category', CustomerCategory::B2B);
    }

    public function scopeB2c($query): void
    {
        $query->where('category', CustomerCategory::B2C);
    }

    public function scopeSearch($query, $value): void
    {
        $query->where('name', 'like', "%{$value}%")
            ->orWhere('email', 'like', "%{$value}%")
            ->orWhere('phone', 'like', "%{$value}%")
            ->orWhere('cin', 'like', "%{$value}%");
    }

    public function scopeOfAuth($query)
    {
        if (! auth()->user()->can('see all customers')) {
            return $query->where('user_id', auth()->id());
        }

        return $query;
    }
}
