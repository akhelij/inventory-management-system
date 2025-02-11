<?php

namespace App\Models;

use App\Observers\PaymentObserver;
use App\Traits\HasActivityLogs;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[ObservedBy([PaymentObserver::class])]
class Payment extends Model
{
    use HasFactory;
    use HasActivityLogs;

    protected $guarded =[];

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
