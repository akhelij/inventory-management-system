<?php

namespace App\Models;

use App\Enums\OrderStatus;
use App\Enums\PermissionEnum;
use App\Observers\OrderObserver;
use App\Traits\HasActivityLogs;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[ObservedBy([OrderObserver::class])]
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
        'reason',
        'total_products',
        'sub_total',
        'vat',
        'total',
        'invoice_no',
        'payment_type',
        'pay',
        'due',
        'user_id',
        'tagged_user_id',
        'uuid',
        'stock_affected',
    ];

    protected $appends = ['status', 'status_color', 'is_updatable_status'];

    protected function casts(): array
    {
        return [
            'order_date' => 'date',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'stock_affected' => 'boolean',
        ];
    }

    public function getStatusAttribute()
    {
        $statuses = [
            OrderStatus::PENDING => 'Pending',
            OrderStatus::APPROVED => 'Approved',
            OrderStatus::CANCELED => 'Canceled',
        ];

        return $statuses[$this->order_status] ?? 'Unknown';
    }

    public function getStatusColorAttribute()
    {
        $colors = [
            OrderStatus::PENDING => 'orange',
            OrderStatus::APPROVED => 'green',
            OrderStatus::CANCELED => 'red',
        ];

        return $colors[$this->order_status] ?? 'grey';
    }

    public function getIsUpdatableStatusAttribute(): bool
    {
        return ($this->order_status === OrderStatus::PENDING || $this->order_status === null) && auth()->user()->can(PermissionEnum::UPDATE_ORDERS_STATUS);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function details(): HasMany
    {
        return $this->hasMany(OrderDetails::class);
    }

    public function payments(): BelongsToMany
    {
        return $this->belongsToMany(Payment::class, 'order_payment')
            ->withPivot('allocated_amount', 'user_id')
            ->withTimestamps();
    }

    public function scopeSearch($query, $value): void
    {
        $query->where('invoice_no', 'like', "%{$value}%")
            ->orWhere('order_status', 'like', "%{$value}%")
            ->orWhere('payment_type', 'like', "%{$value}%")
            ->orWhereRelation('user', 'name', 'like', "%{$value}%")
            ->orWhereRelation('customer', 'name', 'like', "%{$value}%");
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function taggedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'tagged_user_id');
    }
}
