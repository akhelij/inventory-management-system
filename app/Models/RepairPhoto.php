<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RepairPhoto extends Model
{
    use HasFactory;

    protected $fillable = [
        'repair_ticket_id', 
        'photo_path',
        'photo_type'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'photo_type' => 'string',
    ];

    /**
     * Get the repair ticket that owns the photo
     */
    public function repairTicket()
    {
        return $this->belongsTo(RepairTicket::class);
    }
}
