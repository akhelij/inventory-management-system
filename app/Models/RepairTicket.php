<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Observers\RepairTicketObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[ObservedBy([RepairTicketObserver::class])]
class RepairTicket extends Model
{
    use HasFactory;

    protected $fillable = [
        'ticket_number',
        'customer_id',
        'driver_id',
        'brought_by',
        'product_id',
        'created_by',
        'technician_id',
        'serial_number',
        'problem_description',
        'status',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function driver(): BelongsTo
    {
        return $this->belongsTo(Driver::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function technician(): BelongsTo
    {
        return $this->belongsTo(User::class, 'technician_id');
    }

    public function photos(): HasMany
    {
        return $this->hasMany(RepairPhoto::class);
    }

    public function statusHistories(): HasMany
    {
        return $this->hasMany(RepairStatusHistory::class);
    }

    public function scopeSearch($query, $term = null)
    {
        if ($term) {
            return $query->where(function ($query) use ($term) {
                $query->where('ticket_number', 'like', "%{$term}%")
                    ->orWhere('serial_number', 'like', "%{$term}%")
                    ->orWhere('problem_description', 'like', "%{$term}%")
                    ->orWhere('status', 'like', "%{$term}%")
                    ->orWhereHas('customer', function ($query) use ($term) {
                        $query->where('name', 'like', "%{$term}%")
                            ->orWhere('phone', 'like', "%{$term}%");
                    })
                    ->orWhereHas('product', function ($query) use ($term) {
                        $query->where('name', 'like', "%{$term}%")
                            ->orWhere('model', 'like', "%{$term}%");
                    })
                    ->orWhereHas('technician', function ($query) use ($term) {
                        $query->where('name', 'like', "%{$term}%");
                    });
            });
        }

        return $query;
    }
}
