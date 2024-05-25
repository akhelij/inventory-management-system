<?php

namespace App\Models;

use App\Traits\HasActivityLogs;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles, HasActivityLogs;

    protected $fillable = [
        'uuid',
        'photo',
        'name',
        'username',
        'email',
        'email_verified_atg',
        'password',
        "store_name",
        "store_address",
        "store_phone",
        "store_email",
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    protected $appends = [
        'role'
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
}
