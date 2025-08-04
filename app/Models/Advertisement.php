<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Advertisement extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'title',
        'category_id',
        'main_characteristics',
        'complectation',
        'technical_characteristics',
        'additional_info',
        'check_data',
        'loading_data',
        'removal_data',
        'status',
        'created_by',
        'published_at',
    ];

    protected $casts = [
        'check_data' => 'array',
        'loading_data' => 'array',
        'removal_data' => 'array',
        'published_at' => 'datetime',
    ];

    // Связь с товаром
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    // Связь с категорией
    public function category(): BelongsTo
    {
        return $this->belongsTo(ProductCategories::class, 'category_id');
    }

    // Связь с создателем
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Связь с медиафайлами
    public function media(): HasMany
    {
        return $this->hasMany(AdvertisementMedia::class);
    }

    // Получить медиафайлы по порядку
    public function mediaOrdered(): HasMany
    {
        return $this->hasMany(AdvertisementMedia::class)->ordered();
    }

    // Получить только изображения
    public function images(): HasMany
    {
        return $this->hasMany(AdvertisementMedia::class)->images()->ordered();
    }

    // Получить только видео
    public function videos(): HasMany
    {
        return $this->hasMany(AdvertisementMedia::class)->videos()->ordered();
    }

    // Получить главное изображение (первое по порядку)
    public function mainImage()
    {
        return $this->hasOne(AdvertisementMedia::class)->images()->orderBy('sort_order');
    }

    // Скопировать данные из товара
    public function copyFromProduct(Product $product): void
    {
        $this->check_data = [
            'status_id' => $product->status_id,
            'status_comment' => $product->status_comment,
        ];

        $this->loading_data = [
            'loading_type' => $product->loading_type,
            'loading_comment' => $product->loading_comment,
        ];

        $this->removal_data = [
            'removal_type' => $product->removal_type,
            'removal_comment' => $product->removal_comment,
        ];

        // Копируем основные характеристики
        $this->main_characteristics = $product->main_chars;
        $this->complectation = $product->complectation;
        $this->category_id = $product->category_id;

        if (!$this->title) {
            $this->title = $product->name;
        }
    }

    // Статусы объявлений
    public static function getStatuses(): array
    {
        return [
            'draft' => 'Черновик',
            'active' => 'Активно',
            'inactive' => 'Неактивно',
            'archived' => 'Архив',
        ];
    }

    // Получить название статуса
    public function getStatusNameAttribute(): string
    {
        return self::getStatuses()[$this->status] ?? $this->status;
    }

    // Проверить, опубликовано ли объявление
    public function isPublished(): bool
    {
        return $this->status === 'active' && $this->published_at !== null;
    }

    // Опубликовать объявление
    public function publish(): void
    {
        $this->status = 'active';
        $this->published_at = now();
        $this->save();
    }
}
