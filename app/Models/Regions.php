<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
}
