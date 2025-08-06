<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use HasFactory;

    protected $guarded = false;

    // Связи
    public function category(): BelongsTo
    {
        return $this->belongsTo(ProductCategories::class, 'category_id');
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function regional(): BelongsTo
    {
        return $this->belongsTo(User::class, 'regional_id');
    }

    public function status(): BelongsTo
    {
        return $this->belongsTo(ProductStatus::class, 'status_id');
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouses::class, 'warehouse_id');
    }

    public function mediaOrdered(): HasMany
    {
        return $this->hasMany(ProductMedia::class)->orderBy('sort_order');
    }

    public function mainImage(): BelongsTo
    {
        return $this->belongsTo(ProductMedia::class)->where('sort_order', 0);
    }

    // Новые связи
    public function check(): HasMany
    {
        return $this->hasMany(ProductCheck::class);
    }

    public function loading(): HasMany
    {
        return $this->hasMany(ProductLoading::class);
    }

    public function removal(): HasMany
    {
        return $this->hasMany(ProductRemoval::class);
    }

    public function paymentVariants(): HasMany
    {
        return $this->hasMany(ProductPaymentVariants::class);
    }

    public function mainPaymentMethod(): BelongsTo
    {
        return $this->belongsTo(ProductPriceType::class, 'main_payment_method');
    }
}
