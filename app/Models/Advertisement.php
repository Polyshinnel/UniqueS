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
        'published_at' => 'datetime',
        'check_data' => 'array',
        'loading_data' => 'array',
        'removal_data' => 'array',
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
        // Копируем основные характеристики
        $this->main_characteristics = $product->main_chars;
        $this->complectation = $product->complectation;
        $this->category_id = $product->category_id;

        if (!$this->title) {
            $this->title = $product->name;
        }

        // Копируем данные проверки
        $check = $product->check->first();
        if ($check) {
            $this->check_data = [
                'status_id' => $check->check_status_id,
                'comment' => $check->comment,
            ];
        }

        // Копируем данные погрузки
        $loading = $product->loading->first();
        if ($loading) {
            $this->loading_data = [
                'status_id' => $loading->install_status_id,
                'comment' => $loading->comment,
            ];
        }

        // Копируем данные демонтажа
        $removal = $product->removal->first();
        if ($removal) {
            $this->removal_data = [
                'status_id' => $removal->install_status_id,
                'comment' => $removal->comment,
            ];
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

    // Методы для работы с данными проверки
    public function getCheckStatusId()
    {
        return $this->check_data['status_id'] ?? null;
    }

    public function getCheckComment()
    {
        return $this->check_data['comment'] ?? null;
    }

    public function setCheckData($statusId, $comment = null)
    {
        $this->check_data = [
            'status_id' => $statusId,
            'comment' => $comment,
        ];
        $this->save();
    }

    // Методы для работы с данными погрузки
    public function getLoadingStatusId()
    {
        return $this->loading_data['status_id'] ?? null;
    }

    public function getLoadingComment()
    {
        return $this->loading_data['comment'] ?? null;
    }

    public function setLoadingData($statusId, $comment = null)
    {
        $this->loading_data = [
            'status_id' => $statusId,
            'comment' => $comment,
        ];
        $this->save();
    }

    // Методы для работы с данными демонтажа
    public function getRemovalStatusId()
    {
        return $this->removal_data['status_id'] ?? null;
    }

    public function getRemovalComment()
    {
        return $this->removal_data['comment'] ?? null;
    }

    public function setRemovalData($statusId, $comment = null)
    {
        $this->removal_data = [
            'status_id' => $statusId,
            'comment' => $comment,
        ];
        $this->save();
    }
}
