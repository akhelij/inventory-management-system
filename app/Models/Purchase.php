<?php

namespace App\Models;

use App\Enums\PurchaseStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Purchase extends Model
{
    use HasFactory;

    protected $guarded = [
        'id',
    ];

    protected $fillable = [
        'supplier_id',
        'date',
        'purchase_no',
        'status',
        'total_amount',
        'created_by',
        'updated_by',
        'user_id',
        'uuid',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'status' => PurchaseStatus::class,
        ];
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class, 'supplier_id', 'id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by', 'id');
    }

    public function details(): HasMany
    {
        return $this->hasMany(PurchaseDetails::class);
    }

    public function scopeSearch($query, $value): void
    {
        $query->where('purchase_no', 'like', "%{$value}%")
            ->orWhere('status', 'like', "%{$value}%");
    }

    /**
     * Get the user that owns the Category
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
