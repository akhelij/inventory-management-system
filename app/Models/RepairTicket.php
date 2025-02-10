<?php

namespace App\Models;

use App\Observers\RepairTicketObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[ObservedBy([RepairTicketObserver::class])]
class RepairTicket extends Model
{
    use HasFactory;

    protected $fillable = [
        'ticket_number',
        'customer_id',
        'product_id',
        'created_by',
        'technician_id',
        'serial_number',
        'problem_description',
        'status'
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function technician()
    {
        return $this->belongsTo(User::class, 'technician_id');
    }

    public function photos()
    {
        return $this->hasMany(RepairPhoto::class);
    }

    public function statusHistories()
    {
        return $this->hasMany(RepairStatusHistory::class);
    }

    public function scopeSearch($query, $term = null)
    {
        if ($term) {
            return $query->where(function($query) use ($term) {
                $query->where('ticket_number', 'like', "%{$term}%")
                    ->orWhere('serial_number', 'like', "%{$term}%")
                    ->orWhere('problem_description', 'like', "%{$term}%")
                    ->orWhere('status', 'like', "%{$term}%")
                    ->orWhereHas('customer', function($query) use ($term) {
                        $query->where('name', 'like', "%{$term}%")
                            ->orWhere('phone', 'like', "%{$term}%");
                    })
                    ->orWhereHas('product', function($query) use ($term) {
                        $query->where('name', 'like', "%{$term}%")
                            ->orWhere('model', 'like', "%{$term}%");
                    })
                    ->orWhereHas('technician', function($query) use ($term) {
                        $query->where('name', 'like', "%{$term}%");
                    });
            });
        }

        return $query;
    }
}
