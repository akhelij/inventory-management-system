<?php

namespace App\Models;

use App\Services\CartService;
use App\Traits\HasActivityLogs;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Collection;
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

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

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
        return $this->hasOne(Cart::class)->where('instance', 'default');
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function getCart(): Collection
    {
        return app(CartService::class)->content($this->id);
    }

    public function clearCart(): bool
    {
        app(CartService::class)->clearCart($this->id);

        return true;
    }
}
