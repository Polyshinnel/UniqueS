<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'sku',
        'category_id',
        'company_id',
        'owner_id',
        'regional_id',
        'status_id',
        'main_chars',
        'mark',
        'complectation',
        'price_comment',
        'add_expenses',
        'warehouse_id',
        'status_comment',
        'loading_type',
        'loading_comment',
        'removal_type',
        'removal_comment',
        'payment_method',
        'purchase_price',
        'payment_comment',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(ProductCategories::class, 'category_id');
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'company_id');
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

    // Связь с медиафайлами
    public function media(): HasMany
    {
        return $this->hasMany(ProductMedia::class);
    }

    // Получить медиафайлы по порядку
    public function mediaOrdered(): HasMany
    {
        return $this->hasMany(ProductMedia::class)->ordered();
    }

    // Получить только изображения
    public function images(): HasMany
    {
        return $this->hasMany(ProductMedia::class)->images()->ordered();
    }

    // Получить только видео
    public function videos(): HasMany
    {
        return $this->hasMany(ProductMedia::class)->videos()->ordered();
    }

    // Получить главное изображение (первое по порядку)
    public function mainImage()
    {
        return $this->hasOne(ProductMedia::class)->images()->orderBy('sort_order');
    }
}
