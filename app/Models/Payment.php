<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $fillable =[
        'user_id',
        'customer_id',
        'date',
        'nature',
        'bank',
        'payment_type',
        'echeance',
        'amount',
        'reported',
        'cashed_in',
        'cashed_in_at',
    ];

    protected $with = [
        'customer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public static function boot()
    {
        parent::boot();

        static::creating(function ($payment) {
            $payment->user_id = auth()->id();
        });
    }
}
