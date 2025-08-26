<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Warehouses extends Model
{
    use HasFactory;

    protected $guarded = false;

    public function regions(): BelongsToMany
    {
        return $this->belongsToMany(Regions::class, 'warehouses_to_regions', 'warehouse_id', 'region_id');
    }

    /**
     * Связь с компаниями через промежуточную таблицу
     */
    public function companies(): BelongsToMany
    {
        return $this->belongsToMany(Company::class, 'company_to_warehouses', 'warehouse_id', 'company_id')
            ->withTimestamps();
    }

    /**
     * Связь с товарами
     */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class, 'warehouse_id');
    }
}
