<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RepairStatusHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'repair_ticket_id',
        'user_id',
        'from_status',
        'to_status',
        'comment',
    ];

    public function repairTicket()
    {
        return $this->belongsTo(RepairTicket::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
