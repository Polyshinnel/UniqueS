<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Warehouses extends Model
{
    use HasFactory;

    protected $guarded = false;

    public function regions()
    {
        return $this->belongsToMany(Regions::class, 'warehouses_to_regions', 'warehouse_id', 'region_id');
    }
}
