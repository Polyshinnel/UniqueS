<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductCheck extends Model
{
    use HasFactory;

    protected $guarded = false;

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function checkStatus(): BelongsTo
    {
        return $this->belongsTo(ProductCheckStatuses::class, 'check_status_id');
    }
}
