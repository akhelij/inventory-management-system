<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RepairPhoto extends Model
{
    use HasFactory;

    protected $fillable = ['repair_ticket_id', 'photo_path'];

    public function repairTicket()
    {
        return $this->belongsTo(RepairTicket::class);
    }
}
