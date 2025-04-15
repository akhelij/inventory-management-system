<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProgressItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'price',
        'status',
        'payment_status',
        'amount_paid',
        'is_visible'
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'amount_paid' => 'decimal:2',
        'is_visible' => 'boolean'
    ];

    public function getRemainingAmountAttribute()
    {
        return $this->price - $this->amount_paid;
    }

    public function updatePaymentStatus()
    {
        if ($this->amount_paid <= 0) {
            $this->payment_status = 'unpaid';
        } elseif ($this->amount_paid < $this->price) {
            $this->payment_status = 'partially_paid';
        } else {
            $this->payment_status = 'paid';
            
            // Auto-hide paid and completed features
            if ($this->status === 'completed') {
                $this->is_visible = false;
            }
        }
        
        $this->save();
    }
}
