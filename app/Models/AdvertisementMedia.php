<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class AdvertisementMedia extends Model
{
    use HasFactory;

    protected $fillable = [
        'advertisement_id',
        'product_media_id',
        'file_name',
        'file_path',
        'file_type',
        'mime_type',
        'file_size',
        'sort_order',
        'is_selected_from_product',
    ];

    protected $casts = [
        'is_selected_from_product' => 'boolean',
    ];

    // Связь с объявлением
    public function advertisement(): BelongsTo
    {
        return $this->belongsTo(Advertisement::class);
    }

    // Связь с медиафайлом товара (если выбран из товара)
    public function productMedia(): BelongsTo
    {
        return $this->belongsTo(ProductMedia::class, 'product_media_id');
    }

    // Скоуп для сортировки по порядку
    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order');
    }

    // Скоуп для получения только изображений
    public function scopeImages(Builder $query): Builder
    {
        return $query->where('file_type', 'image');
    }

    // Скоуп для получения только видео
    public function scopeVideos(Builder $query): Builder
    {
        return $query->where('file_type', 'video');
    }

    // Скоуп для медиафайлов, выбранных из товара
    public function scopeSelectedFromProduct(Builder $query): Builder
    {
        return $query->where('is_selected_from_product', true);
    }

    // Скоуп для загруженных медиафайлов
    public function scopeUploaded(Builder $query): Builder
    {
        return $query->where('is_selected_from_product', false);
    }

    // Получить полный URL файла
    public function getFullUrlAttribute(): string
    {
        return asset('storage/' . $this->file_path);
    }

    // Получить форматированный размер файла
    public function getFormattedSizeAttribute(): string
    {
        return $this->formatBytes($this->file_size);
    }

    // Проверить, является ли файл изображением
    public function isImage(): bool
    {
        return $this->file_type === 'image';
    }

    // Проверить, является ли файл видео
    public function isVideo(): bool
    {
        return $this->file_type === 'video';
    }

    // Форматирование размера файла
    private function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, $precision) . ' ' . $units[$i];
    }

    // Создать медиафайл объявления из медиафайла товара
    public static function createFromProductMedia(int $advertisementId, ProductMedia $productMedia, int $sortOrder = 0): self
    {
        return self::create([
            'advertisement_id' => $advertisementId,
            'product_media_id' => $productMedia->id,
            'file_name' => $productMedia->file_name,
            'file_path' => $productMedia->file_path,
            'file_type' => $productMedia->file_type,
            'mime_type' => $productMedia->mime_type,
            'file_size' => $productMedia->file_size,
            'sort_order' => $sortOrder,
            'is_selected_from_product' => true,
        ]);
    }
}
