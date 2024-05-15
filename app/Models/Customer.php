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
        'total_orders',
        'total_payments',
        'is_out_of_limit',
    ];

    public function getIsOutOfLimitAttribute(): bool
    {
        return $this->email === self::ALAMI ? true : ($this->total_orders - $this->total_payments > $this->limit);
    }

    public function getTotalOrdersAttribute(): float
    {
        return $this->orders->where('order_status', true)->sum('total');
    }

    public function getTotalPaymentsAttribute(): float
    {
        return $this->payments->where('cashed_in', true)->sum('amount');
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class)->where('order_status', 1)->orderBy('created_at', 'desc');
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
        return $this->hasMany(Payment::class)->orderBy('created_at', 'desc');
    }

    public function scopeSearch($query, $value): void
    {
        $query->where('name', 'like', "%{$value}%")
            ->orWhere('email', 'like', "%{$value}%")
            ->orWhere('phone', 'like', "%{$value}%");
    }
}
