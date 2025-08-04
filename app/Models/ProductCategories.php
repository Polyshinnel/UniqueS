<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductCategories extends Model
{
    use HasFactory;

    protected $guarded = false;
    
    protected $fillable = [
        'name',
        'parent_id',
        'active'
    ];
    
    protected $casts = [
        'active' => 'boolean'
    ];
}
