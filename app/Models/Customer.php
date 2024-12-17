<?php

namespace App\Models;

use App\Traits\HasActivityLogs;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Customer extends Model
{
    use HasFactory, HasActivityLogs;

    const ALAMI = "electro@alami.com";

    protected $guarded = [
        'id',
    ];

    protected $fillable = [
        'name',
        'email',
        'type',
        'phone',
        'address',
        'city',
        'photo',
        'limit',
        'account_holder',
        'account_number',
        'bank_name',
        'user_id',
        'uuid'
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected $appends = [
        'total_orders_paid',
        'total_orders_not_paid',
        'total_payments',
        'is_out_of_limit',
        'have_unpaid_checks',
    ];

    public function getIsOutOfLimitAttribute(): bool
    {
        return $this->email === self::ALAMI ? false : ($this->total_orders - $this->total_payments > $this->limit);
    }

    public function getHaveUnpaidChecksAttribute(): bool
    {
        return $this->email === self::ALAMI ? false : ($this->total_orders - $this->total_payments > 0);
    }

    public function getTotalOrdersPaidAttribute(): float
    {
        return $this->orders->where('order_status', true)->where('due', 0)->sum('total');
    }

    public function getTotalOrdersNotPaidAttribute(): float
    {
        return $this->orders->where('order_status', true)->where('due', '<>', 0)->sum('total');
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
        return $this->HasMany(Quotation::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class)->orderBy('echeance', 'desc');
    }

    public function scopeSearch($query, $value): void
    {
        $query->where('name', 'like', "%{$value}%")
            ->orWhere('email', 'like', "%{$value}%")
            ->orWhere('phone', 'like', "%{$value}%");
    }

    public function scopeOfAuth($query)
    {
        if(!auth()->user()->can('see all customers')) {
            return $query->where("user_id", auth()->id());
        }
        return $query;
    }
}
