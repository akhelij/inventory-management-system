<?php

namespace App\Models;

use App\Enums\OrderStatus;
use App\Enums\PermissionEnum;
use App\Traits\HasActivityLogs;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Order extends Model
{
    use HasActivityLogs;

    protected $guarded = [
        'id',
    ];

    protected $fillable = [
        'customer_id',
        'order_date',
        'order_status',
        'total_products',
        'sub_total',
        'vat',
        'total',
        'invoice_no',
        'payment_type',
        'pay',
        'due',
        "user_id",
        "uuid"
    ];

    protected $casts = [
        'order_date'    => 'date',
        'created_at'    => 'datetime',
        'updated_at'    => 'datetime',
    ];

    protected $appends = ['status', 'status_color', 'is_updatable_status'];

    public function getStatusAttribute()
    {
        $statuses = [
            OrderStatus::PENDING => 'Pending',
            OrderStatus::APPROVED => 'Approved',
            OrderStatus::CANCELED => 'Canceled',
        ];

        return $statuses[$this->order_status];
    }


    public function getStatusColorAttribute()
    {
        $colors = [
            OrderStatus::PENDING => 'orange',
            OrderStatus::APPROVED => 'green',
            OrderStatus::CANCELED => 'red',
        ];

        return $colors[$this->order_status];
    }

    public function getIsUpdatableStatusAttribute(): bool
    {
        return ($this->order_status === OrderStatus::PENDING) && auth()->user()->can(PermissionEnum::UPDATE_ORDERS_STATUS);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function details(): HasMany
    {
        return $this->hasMany(OrderDetails::class);
    }

    public function scopeSearch($query, $value): void
    {
        $query->where('invoice_no', 'like', "%{$value}%")
            ->orWhere('order_status', 'like', "%{$value}%")
            ->orWhere('payment_type', 'like', "%{$value}%");
    }

     /**
     * Get the user that owns the Category
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
