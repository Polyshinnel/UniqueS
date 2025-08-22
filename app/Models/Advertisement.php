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
        'main_info',
        'additional_info',
        'check_data',
        'loading_data',
        'removal_data',
        'adv_price',
        'adv_price_comment',
        'main_img',
        'status_id',
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

    // Связь со статусом объявления
    public function status(): BelongsTo
    {
        return $this->belongsTo(AdvertisementStatus::class, 'status_id');
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

    // Получить главное изображение по полю main_img
    public function getMainImage()
    {
        if ($this->main_img) {
            return \App\Models\ProductMedia::find($this->main_img);
        }
        return null;
    }

    // Связь с тегами
    public function tags(): HasMany
    {
        return $this->hasMany(AdvertisementsTags::class);
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
        return AdvertisementStatus::all()->pluck('name', 'id')->toArray();
    }

    // Получить название статуса
    public function getStatusNameAttribute(): string
    {
        return $this->status?->name ?? 'Неизвестный статус';
    }

    // Проверить, опубликовано ли объявление
    public function isPublished(): bool
    {
        return $this->status?->is_published === true && $this->published_at !== null;
    }

    // Опубликовать объявление
    public function publish(): void
    {
        $activeStatus = AdvertisementStatus::where('name', 'Активное')->first();
        if ($activeStatus) {
            $this->status_id = $activeStatus->id;
            $this->published_at = now();
            $this->save();
        }
    }

    // Методы для работы с данными проверки
    public function getCheckStatusId(): ?int
    {
        return $this->check_data['status_id'] ?? null;
    }

    public function getCheckComment(): ?string
    {
        return $this->check_data['comment'] ?? null;
    }

    public function setCheckData(?int $statusId, ?string $comment = null): void
    {
        $this->check_data = [
            'status_id' => $statusId,
            'comment' => $comment,
        ];
        $this->save();
    }

    // Методы для работы с данными погрузки
    public function getLoadingStatusId(): ?int
    {
        return $this->loading_data['status_id'] ?? null;
    }

    public function getLoadingComment(): ?string
    {
        return $this->loading_data['comment'] ?? null;
    }

    public function setLoadingData(?int $statusId, ?string $comment = null): void
    {
        $this->loading_data = [
            'status_id' => $statusId,
            'comment' => $comment,
        ];
        $this->save();
    }

    // Методы для работы с данными демонтажа
    public function getRemovalStatusId(): ?int
    {
        return $this->removal_data['status_id'] ?? null;
    }

    public function getRemovalComment(): ?string
    {
        return $this->removal_data['comment'] ?? null;
    }

    public function setRemovalData(?int $statusId, ?string $comment = null): void
    {
        $this->removal_data = [
            'status_id' => $statusId,
            'comment' => $comment,
        ];
        $this->save();
    }

    // Связь с логами
    public function logs(): HasMany
    {
        return $this->hasMany(AdvLog::class);
    }

    // Связь с действиями
    public function actions(): HasMany
    {
        return $this->hasMany(AdvAction::class);
    }

    // Получить последнее доступное действие
    public function getLastAvailableAction()
    {
        return $this->actions()
            ->where('status', false)
            ->latest('expired_at')
            ->first();
    }

    // Получить активное объявление для товара
    public function getActiveAdvertisement()
    {
        $activeStatus = AdvertisementStatus::where('name', 'Активное')->first();
        if (!$activeStatus) {
            return null;
        }
        
        return $this->where('product_id', $this->product_id)
                   ->where('status_id', $activeStatus->id)
                   ->first();
    }
}
