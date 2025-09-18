<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'phone',
        'role_id',
        'has_whatsapp',
        'has_telegram',
        'active',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'has_whatsapp' => 'boolean',
        'has_telegram' => 'boolean',
        'active' => 'boolean',
    ];

    public function regions()
    {
        return $this->belongsToMany(Regions::class, 'users_to_regions', 'user_id', 'region_id')
            ->withTimestamps();
    }

    public function warehouses()
    {
        return $this->belongsToMany(Warehouses::class, 'users_to_warehouses', 'user_id', 'warehouse_id')
            ->withTimestamps();
    }

    public function role()
    {
        return $this->belongsTo(RoleList::class, 'role_id');
    }
}
