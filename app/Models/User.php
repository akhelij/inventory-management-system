<?php

namespace App\Models;

use App\Traits\HasActivityLogs;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasActivityLogs, HasApiTokens, HasFactory, HasRoles, Notifiable;

    protected $fillable = [
        'uuid',
        'photo',
        'name',
        'username',
        'email',
        'email_verified_atg',
        'password',
        'store_name',
        'store_address',
        'store_phone',
        'store_email',
        'warehouse_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $appends = [
        'role',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function getRoleAttribute()
    {
        $this->load('roles');
        if ($this->roles->count() > 1) {
            $this->roles()->detach($this->roles->first()->id);
        }

        return $this->roles()->with('permissions')->latest()->first();
    }

    public function scopeSearch($query, $value): void
    {
        $query->where('name', 'like', "%{$value}%")
            ->orWhere('email', 'like', "%{$value}%");
    }

    public function getRouteKeyName(): string
    {
        return 'name';
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    /**
     * Get cart content directly from the database
     * This bypasses session limitations
     * 
     * @return \Illuminate\Support\Collection
     */
    public function getCart()
    {
        try {
            // Get cart data from database
            $cartData = \Illuminate\Support\Facades\DB::table('shoppingcart')
                ->where('identifier', $this->id)
                ->first();

            if (!$cartData) {
                return collect();
            }

            // Unserialize the cart content
            $content = unserialize(base64_decode($cartData->content));
            
            return $content;
        } catch (\Exception $e) {
            // If there's an error, return empty collection
            return collect();
        }
    }

    /**
     * Clear the user's cart from the database
     * 
     * @return void
     */
    public function clearCart()
    {
        try {
            // Clear the cart from the database
            \Gloudemans\Shoppingcart\Facades\Cart::erase($this->id);
            
            // Also clear the session cart
            \Gloudemans\Shoppingcart\Facades\Cart::destroy();
        } catch (\Exception $e) {
            // Silently continue if there's an error
        }
    }
}
