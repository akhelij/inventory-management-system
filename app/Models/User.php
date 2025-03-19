<?php

namespace App\Models;

use App\Traits\HasActivityLogs;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
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

    public function cart(): HasOne
    {
        return $this->hasOne(ShoppingCart::class, 'identifier', 'id');
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
     * Get the user's cart from the database
     * 
     * @return \Illuminate\Support\Collection
     */
    public function getCart()
    {
        try {
            // Log the start of cart retrieval
            \Illuminate\Support\Facades\Log::info('Starting cart retrieval for user: ' . $this->id);
            
            // Get cart data directly from database
            $cartData = \Illuminate\Support\Facades\DB::table('shoppingcart')
                ->where('identifier', $this->id)
                ->first();
                
            if (!$cartData) {
                \Illuminate\Support\Facades\Log::info('No cart data found for user: ' . $this->id);
                return collect();
            }
            
            // Log the raw cart data
            \Illuminate\Support\Facades\Log::info('Raw cart data retrieved:', [
                'identifier' => $cartData->identifier,
                'instance' => $cartData->instance,
                'content_length' => strlen($cartData->content)
            ]);
            
            try {
                // Try to unserialize the cart content
                $decoded = base64_decode($cartData->content);
                \Illuminate\Support\Facades\Log::info('Decoded content length: ' . strlen($decoded));
                
                $content = unserialize($decoded);
                
                // Log successful unserialization
                \Illuminate\Support\Facades\Log::info('Cart unserialized successfully', [
                    'item_count' => $content->count()
                ]);
                
                // If successful, return the content
                return $content;
            } catch (\Exception $e) {
                // Log unserialization error
                \Illuminate\Support\Facades\Log::error('Cart unserialization error: ' . $e->getMessage(), [
                    'exception' => $e,
                    'trace' => $e->getTraceAsString()
                ]);
                
                // If unserialization fails, try to use the Gloudemans\Shoppingcart package
                // to restore the cart from the database and then get the content
                \Illuminate\Support\Facades\Log::info('Attempting to restore cart using package');
                \Gloudemans\Shoppingcart\Facades\Cart::restore($this->id);
                $content = \Gloudemans\Shoppingcart\Facades\Cart::content();
                
                // Store the cart again to fix the serialization
                \Illuminate\Support\Facades\Log::info('Re-storing cart to fix serialization');
                \Gloudemans\Shoppingcart\Facades\Cart::store($this->id);
                
                return $content;
            }
        } catch (\Exception $e) {
            // Log the error
            \Illuminate\Support\Facades\Log::error('Cart retrieval error: ' . $e->getMessage(), [
                'exception' => $e,
                'trace' => $e->getTraceAsString()
            ]);
            
            // If there's an error, return empty collection
            return collect();
        }
    }

    /**
     * Clear the user's cart from the database
     */
    public function clearCart()
    {
        try {
            // Clear cart from database using the Gloudemans\Shoppingcart package
            \Gloudemans\Shoppingcart\Facades\Cart::erase($this->id);
            
            // Also destroy the session cart to ensure consistency
            \Gloudemans\Shoppingcart\Facades\Cart::destroy();
            
            return true;
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Cart clearing error: ' . $e->getMessage());
            return false;
        }
    }
}
