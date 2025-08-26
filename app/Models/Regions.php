<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Regions extends Model
{
    use HasFactory;

    protected $guarded = false;

    protected $fillable = [
        'name',
        'city_name',
        'active'
    ];

    protected $casts = [
        'active' => 'boolean'
    ];

    /**
     * Связь с пользователями через промежуточную таблицу
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'users_to_regions', 'region_id', 'user_id')
            ->withTimestamps();
    }

    /**
     * Связь со складами через промежуточную таблицу
     */
    public function warehouses(): BelongsToMany
    {
        return $this->belongsToMany(Warehouses::class, 'warehouses_to_regions', 'region_id', 'warehouse_id')
            ->withTimestamps();
    }
}
