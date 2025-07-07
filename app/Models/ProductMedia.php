<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductMedia extends Model
{
    use HasFactory;

    protected $table = 'products_media';

    protected $fillable = [
        'product_id',
        'file_name',
        'file_path',
        'file_type',
        'mime_type',
        'file_size',
        'sort_order',
    ];

    protected $casts = [
        'file_size' => 'integer',
        'sort_order' => 'integer',
    ];

    // Связь с товаром
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    // Получить URL файла
    public function getFileUrlAttribute(): string
    {
        return asset('storage/' . $this->file_path);
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

    // Скоуп для сортировки по порядку
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order');
    }

    // Скоуп для получения только изображений
    public function scopeImages($query)
    {
        return $query->where('file_type', 'image');
    }

    // Скоуп для получения только видео
    public function scopeVideos($query)
    {
        return $query->where('file_type', 'video');
    }
}
