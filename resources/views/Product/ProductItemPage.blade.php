@extends('layouts.layout')

@section('content')
<div class="product-item-container">
    <div class="product-header">
        <div class="breadcrumb">
            <a href="{{ route('products.index') }}">Товары</a> / {{ $product->name }}
        </div>
        <div class="product-header-actions">
            <h1 class="product-title">{{ $product->name }}</h1>
            <div class="product-actions">
                <a href="{{ route('products.edit', $product) }}" class="btn btn-primary">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"></path>
                    </svg>
                    Редактировать
                </a>
            </div>
        </div>
        <div class="product-sku">Артикул: {{ $product->sku }}</div>
    </div>

    <div class="product-content">
        <!-- Блок с медиафайлами -->
        <div class="product-media-section">
            @if($product->mediaOrdered->count() > 0)
                <div class="media-gallery">
                    <div class="main-image-container">
                        @php
                            $mainImage = $product->mediaOrdered->where('file_type', 'image')->first();
                        @endphp
                        @if($mainImage)
                            <img id="mainImage" src="{{ asset('storage/' . $mainImage->file_path) }}" 
                                 alt="{{ $product->name }}" class="main-image" 
                                 onclick="openGallery(0)">
                        @else
                            <div class="no-image">
                                <svg width="120" height="120" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                    <rect x="3" y="3" width="18" height="18" rx="2" ry="2"/>
                                    <circle cx="8.5" cy="8.5" r="1.5"/>
                                    <polyline points="21,15 16,10 5,21"/>
                                </svg>
                                <p>Нет изображений</p>
                            </div>
                        @endif
                    </div>
                    
                    @if($product->mediaOrdered->count() > 1)
                        <div class="thumbnails-container">
                            @foreach($product->mediaOrdered as $index => $media)
                                <div class="thumbnail {{ $index === 0 ? 'active' : '' }}" 
                                     onclick="changeMainImage('{{ asset('storage/' . $media->file_path) }}', {{ $index }}, '{{ $media->file_type }}')">
                                    @if($media->file_type === 'image')
                                        <img src="{{ asset('storage/' . $media->file_path) }}" alt="Фото {{ $index + 1 }}">
                                    @else
                                        <div class="video-thumbnail">
                                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                                <polygon points="5,3 19,12 5,21 5,3"/>
                                            </svg>
                                            <span>Видео</span>
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            @else
                <div class="no-media">
                    <svg width="120" height="120" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <rect x="3" y="3" width="18" height="18" rx="2" ry="2"/>
                        <circle cx="8.5" cy="8.5" r="1.5"/>
                        <polyline points="21,15 16,10 5,21"/>
                    </svg>
                    <p>Медиафайлы не загружены</p>
                </div>
            @endif

            <!-- Блок действий и событий -->
            <div class="product-actions-section">
                <div class="info-block">
                    <h3>Следующие действия</h3>
                    <div class="action-info">
                        <div class="action-date">
                            <span class="label">Дата:</span>
                            <span class="value">{{ now()->format('d.m.Y') }}</span>
                        </div>
                        <div class="action-description">
                            <span class="label">Что требуется сделать:</span>
                            <p>Позвонить клиенту, уточнить по наличию оборудования</p>
                        </div>
                        <div class="action-buttons">
                            <button class="btn btn-primary">Задать новое действие</button>
                            <a href="#" class="btn btn-secondary">Подробнее</a>
                        </div>
                    </div>
                </div>

                <div class="info-block">
                    <h3>Лог событий</h3>
                    <div class="events-list">
                        <div class="event-item">
                            <div class="event-header">
                                <span class="event-type">Комментарий</span>
                                <span class="event-date">{{ now()->format('d.m.Y H:i:s') }}</span>
                            </div>
                            <div class="event-content">
                                <p>Позвонил клиенту, уточнил по наличию оборудования</p>
                            </div>
                            <div class="event-footer">
                                <span>Создал: {{ $product->owner->name ?? 'Создатель не указан' }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="events-actions">
                        <button class="btn btn-secondary">История</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Основная информация о товаре -->
        <div class="product-info-section">
            <div class="info-block">
                <h3>Основная информация</h3>
                <div class="info-grid">
                    <div class="info-item">
                        <span class="label">Категория:</span>
                        <span class="value">{{ $product->category->name ?? 'Не указана' }}</span>
                    </div>
                    <div class="info-item">
                        <span class="label">Склад:</span>
                        <span class="value">{{ $product->warehouse->name ?? 'Не указан' }}</span>
                    </div>
                    <div class="info-item">
                        <span class="label">Организация:</span>
                        <span class="value">
                            @if($product->company)
                                <a href="{{ route('companies.show', $product->company) }}" class="company-link">
                                    {{ $product->company->name }}
                                </a>
                            @else
                                Не указана
                            @endif
                        </span>
                    </div>
                    <div class="info-item">
                        <span class="label">Владелец:</span>
                        <span class="value">
                            @if($product->owner)
                                <button class="contact-link" onclick="showContactCard({{ $product->owner->id }}, '{{ $product->owner->name }}', '{{ $product->owner->email }}', '{{ $product->owner->phone }}', '{{ $product->owner->role->name ?? 'Роль не указана' }}', {{ $product->owner->has_telegram ? 'true' : 'false' }}, {{ $product->owner->has_whatsapp ? 'true' : 'false' }})">
                                    {{ $product->owner->name }}
                                </button>
                            @else
                                Не указан
                            @endif
                        </span>
                    </div>
                    <div class="info-item">
                        <span class="label">Региональный:</span>
                        <span class="value">
                            @if($product->regional)
                                <button class="contact-link" onclick="showContactCard({{ $product->regional->id }}, '{{ $product->regional->name }}', '{{ $product->regional->email }}', '{{ $product->regional->phone }}', '{{ $product->regional->role->name ?? 'Роль не указана' }}', {{ $product->regional->has_telegram ? 'true' : 'false' }}, {{ $product->regional->has_whatsapp ? 'true' : 'false' }})">
                                    {{ $product->regional->name }}
                                </button>
                            @else
                                Не указан
                            @endif
                        </span>
                    </div>
                    @if($product->mark)
                        <div class="info-item">
                            <span class="label">Оценка:</span>
                            <span class="value">{{ $product->mark }}</span>
                        </div>
                    @endif
                </div>
            </div>

            <div class="info-block">
                <h3>Статус товара</h3>
                <div class="status-container">
                    <div class="status-badge status-{{ $product->status->id ?? 'unknown' }}">
                        {{ $product->status->name ?? 'Не указан' }}
                    </div>
                    <div class="comment-section" data-field="status_comment">
                        <div class="comment-header">
                            <strong>Комментарий к статусу:</strong>
                            <button class="edit-comment-btn" onclick="editComment('status_comment')">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"></path>
                                </svg>
                                Редактировать
                            </button>
                        </div>
                        <div class="comment-content" id="status_comment_content">
                            @if($product->status_comment)
                                <p>{{ $product->status_comment }}</p>
                            @else
                                <p class="no-comment">Комментарий не указан</p>
                            @endif
                        </div>
                        <div class="comment-edit" id="status_comment_edit" style="display: none;">
                            <textarea class="comment-textarea" id="status_comment_textarea" rows="3">{{ $product->status_comment }}</textarea>
                            <div class="comment-actions">
                                <button class="btn btn-primary btn-sm" onclick="saveComment('status_comment')">Сохранить</button>
                                <button class="btn btn-secondary btn-sm" onclick="cancelEdit('status_comment')">Отмена</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            @if($product->main_chars || $product->complectation)
                <div class="info-block">
                    <h3>Характеристики</h3>
                    @if($product->main_chars)
                        <div class="chars-item">
                            <strong>Основные характеристики:</strong>
                            <p>{{ $product->main_chars }}</p>
                        </div>
                    @endif
                    @if($product->complectation)
                        <div class="chars-item">
                            <strong>Комплектация:</strong>
                            <p>{{ $product->complectation }}</p>
                        </div>
                    @endif
                </div>
            @endif

            @if($product->loading_type || $product->loading_comment)
                <div class="info-block">
                    <h3>Информация о погрузке</h3>
                    @if($product->loading_type)
                        <div class="loading-item">
                            <strong>Тип загрузки:</strong>
                            <span>
                                @switch($product->loading_type)
                                    @case('supplier')
                                        Поставщиком
                                        @break
                                    @case('supplier_paid')
                                        Поставщиком (за доп. плату)
                                        @break
                                    @case('client')
                                        Клиентом
                                        @break
                                    @case('other')
                                        Другое
                                        @break
                                    @default
                                        {{ $product->loading_type }}
                                @endswitch
                            </span>
                        </div>
                    @endif
                    <div class="comment-section" data-field="loading_comment">
                        <div class="comment-header">
                            <strong>Комментарий по погрузке:</strong>
                            <button class="edit-comment-btn" onclick="editComment('loading_comment')">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"></path>
                                </svg>
                                Редактировать
                            </button>
                        </div>
                        <div class="comment-content" id="loading_comment_content">
                            @if($product->loading_comment)
                                <p>{{ $product->loading_comment }}</p>
                            @else
                                <p class="no-comment">Комментарий не указан</p>
                            @endif
                        </div>
                        <div class="comment-edit" id="loading_comment_edit" style="display: none;">
                            <textarea class="comment-textarea" id="loading_comment_textarea" rows="3">{{ $product->loading_comment }}</textarea>
                            <div class="comment-actions">
                                <button class="btn btn-primary btn-sm" onclick="saveComment('loading_comment')">Сохранить</button>
                                <button class="btn btn-secondary btn-sm" onclick="cancelEdit('loading_comment')">Отмена</button>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            @if($product->removal_type || $product->removal_comment)
                <div class="info-block">
                    <h3>Информация о вывозе</h3>
                    @if($product->removal_type)
                        <div class="removal-item">
                            <strong>Тип вывоза:</strong>
                            <span>
                                @switch($product->removal_type)
                                    @case('supplier')
                                        Поставщиком
                                        @break
                                    @case('supplier_paid')
                                        Поставщиком (за доп. плату)
                                        @break
                                    @case('client')
                                        Клиентом
                                        @break
                                    @case('other')
                                        Другое
                                        @break
                                    @default
                                        {{ $product->removal_type }}
                                @endswitch
                            </span>
                        </div>
                    @endif
                    <div class="comment-section" data-field="removal_comment">
                        <div class="comment-header">
                            <strong>Комментарий по вывозу:</strong>
                            <button class="edit-comment-btn" onclick="editComment('removal_comment')">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"></path>
                                </svg>
                                Редактировать
                            </button>
                        </div>
                        <div class="comment-content" id="removal_comment_content">
                            @if($product->removal_comment)
                                <p>{{ $product->removal_comment }}</p>
                            @else
                                <p class="no-comment">Комментарий не указан</p>
                            @endif
                        </div>
                        <div class="comment-edit" id="removal_comment_edit" style="display: none;">
                            <textarea class="comment-textarea" id="removal_comment_textarea" rows="3">{{ $product->removal_comment }}</textarea>
                            <div class="comment-actions">
                                <button class="btn btn-primary btn-sm" onclick="saveComment('removal_comment')">Сохранить</button>
                                <button class="btn btn-secondary btn-sm" onclick="cancelEdit('removal_comment')">Отмена</button>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <div class="info-block">
                <div class="block-header">
                    <h3>Информация об оплате</h3>
                    <button class="edit-payment-btn" onclick="editPaymentBlock()">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"></path>
                        </svg>
                        Редактировать
                    </button>
                </div>
                
                <!-- Способ оплаты -->
                <div class="payment-item">
                    <strong>Способ оплаты:</strong>
                    <div class="payment-content" id="payment_method_content">
                        @if($product->payment_method)
                            <span>
                                @switch($product->payment_method)
                                    @case('cash')
                                        Наличные
                                        @break
                                    @case('cashless_with_vat')
                                        Безнал с НДС
                                        @break
                                    @case('cashless_without_vat')
                                        Безнал без НДС
                                        @break
                                    @case('combined')
                                        Комбинированная
                                        @break
                                    @case('other')
                                        Другое
                                        @break
                                    @default
                                        {{ $product->payment_method }}
                                @endswitch
                            </span>
                        @else
                            <span class="no-value">Не указан</span>
                        @endif
                    </div>
                    <div class="payment-edit" id="payment_method_edit" style="display: none;">
                        <select class="payment-select" id="payment_method_select">
                            <option value="">Выберите способ оплаты</option>
                            <option value="cash" {{ $product->payment_method === 'cash' ? 'selected' : '' }}>Наличные</option>
                            <option value="cashless_with_vat" {{ $product->payment_method === 'cashless_with_vat' ? 'selected' : '' }}>Безнал с НДС</option>
                            <option value="cashless_without_vat" {{ $product->payment_method === 'cashless_without_vat' ? 'selected' : '' }}>Безнал без НДС</option>
                            <option value="combined" {{ $product->payment_method === 'combined' ? 'selected' : '' }}>Комбинированная</option>
                            <option value="other" {{ $product->payment_method === 'other' ? 'selected' : '' }}>Другое</option>
                        </select>
                    </div>
                </div>

                <!-- Закупочная цена -->
                <div class="payment-item">
                    <strong>Закупочная цена:</strong>
                    <div class="payment-content" id="purchase_price_content">
                        @if($product->purchase_price)
                            <span class="price">{{ number_format($product->purchase_price, 0, ',', ' ') }} ₽</span>
                        @else
                            <span class="no-value">Не указана</span>
                        @endif
                    </div>
                    <div class="payment-edit" id="purchase_price_edit" style="display: none;">
                        <input type="number" class="payment-input" id="purchase_price_input" 
                               value="{{ $product->purchase_price }}" step="0.01" min="0" placeholder="Введите цену">
                    </div>
                </div>

                <!-- Комментарий по оплате -->
                <div class="payment-item">
                    <strong>Комментарий по оплате:</strong>
                    <div class="payment-content" id="payment_comment_content">
                        @if($product->payment_comment)
                            <p>{{ $product->payment_comment }}</p>
                        @else
                            <p class="no-comment">Комментарий не указан</p>
                        @endif
                    </div>
                    <div class="payment-edit" id="payment_comment_edit" style="display: none;">
                        <textarea class="payment-textarea" id="payment_comment_textarea" rows="3">{{ $product->payment_comment }}</textarea>
                    </div>
                </div>

                <!-- Общие кнопки действий -->
                <div class="payment-actions" id="payment_actions" style="display: none;">
                    <button class="btn btn-primary" onclick="savePaymentBlock()">Сохранить все изменения</button>
                    <button class="btn btn-secondary" onclick="cancelPaymentEdit()">Отмена</button>
                </div>
            </div>
        </div>
    </div>

    
</div>

<!-- Модальное окно галереи -->
    <div id="galleryModal" class="gallery-modal">
        <div class="gallery-modal-content">
            <span class="gallery-close" onclick="closeGallery()">&times;</span>
            <div class="gallery-main">
                <button class="gallery-nav gallery-prev" onclick="prevImage()">&#10094;</button>
                <div class="gallery-item-container">
                    <img id="galleryImage" src="" alt="">
                    <video id="galleryVideo" controls style="display: none;">
                        <source src="" type="">
                        Ваш браузер не поддерживает воспроизведение видео.
                    </video>
                </div>
                <button class="gallery-nav gallery-next" onclick="nextImage()">&#10095;</button>
            </div>
            <div class="gallery-counter">
                <span id="galleryCounter">1 / 1</span>
            </div>
        </div>
    </div>
</div>

<style>
.product-item-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 20px;
}

.product-header {
    margin-bottom: 30px;
}

.breadcrumb {
    font-size: 14px;
    color: #666;
    margin-bottom: 10px;
}

.breadcrumb a {
    color: #133E71;
    text-decoration: none;
}

.breadcrumb a:hover {
    text-decoration: underline;
}

.product-header-actions {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 10px;
}

.product-title {
    font-size: 28px;
    color: #133E71;
    margin: 0;
    font-weight: 600;
}

.product-actions {
    display: flex;
    gap: 10px;
}

.product-actions .btn {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 8px 16px;
    border-radius: 6px;
    text-decoration: none;
    font-size: 14px;
    font-weight: 500;
    transition: all 0.3s ease;
    border: 1px solid;
}

.product-actions .btn-primary {
    background-color: #133E71;
    color: white;
    border-color: #133E71;
}

.product-actions .btn-primary:hover {
    background-color: #0f2d56;
    border-color: #0f2d56;
    transform: translateY(-1px);
}

.product-actions .btn svg {
    width: 16px;
    height: 16px;
}

.product-sku {
    font-size: 14px;
    color: #666;
    background: #f8f9fa;
    padding: 5px 10px;
    border-radius: 4px;
    display: inline-block;
}

.product-content {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 40px;
    align-items: start;
}

.product-actions-section {
    display: flex;
    flex-direction: column;
    gap: 25px;
    margin-top: 40px;
    grid-column: 1 / -1;
}

.action-info {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.action-date {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 10px 0;
    border-bottom: 1px solid #f1f3f4;
}

.action-description {
    padding: 10px 0;
}

.action-description p {
    margin-top: 8px;
    color: #495057;
    line-height: 1.5;
    background: #f8f9fa;
    padding: 10px;
    border-radius: 4px;
}

.action-buttons {
    display: flex;
    gap: 10px;
    margin-top: 15px;
}

.events-list {
    display: flex;
    flex-direction: column;
    gap: 15px;
    margin-bottom: 20px;
}

.event-item {
    background: #f8f9fa;
    border-radius: 8px;
    padding: 15px;
    border: 1px solid #e9ecef;
}

.event-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 10px;
}

.event-type {
    background: #133E71;
    color: white;
    padding: 4px 8px;
    border-radius: 12px;
    font-size: 10px;
    font-weight: 600;
}

.event-date {
    color: #666;
    font-size: 12px;
}

.event-content {
    margin-bottom: 10px;
}

.event-content p {
    color: #495057;
    line-height: 1.5;
    margin: 0;
}

.event-footer {
    color: #666;
    font-size: 12px;
}

.events-actions {
    display: flex;
    justify-content: flex-end;
}

.product-media-section {
    background: white;
    border-radius: 10px;
    padding: 20px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.media-gallery {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.main-image-container {
    position: relative;
    border-radius: 8px;
    overflow: hidden;
    background: #f8f9fa;
    aspect-ratio: 4/3;
    display: flex;
    align-items: center;
    justify-content: center;
}

.main-image {
    width: 100%;
    height: 100%;
    object-fit: contain;
    cursor: pointer;
    transition: transform 0.3s ease;
}

.main-image:hover {
    transform: scale(1.02);
}

.no-image, .no-media {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    color: #666;
    height: 100%;
    min-height: 300px;
}

.no-image svg, .no-media svg {
    opacity: 0.3;
    margin-bottom: 15px;
}

.thumbnails-container {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
}

.thumbnail {
    width: 80px;
    height: 60px;
    border-radius: 6px;
    overflow: hidden;
    cursor: pointer;
    border: 2px solid transparent;
    transition: all 0.3s ease;
    position: relative;
}

.thumbnail.active {
    border-color: #133E71;
}

.thumbnail:hover {
    border-color: #1C5BA4;
    transform: scale(1.05);
}

.thumbnail img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.video-thumbnail {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    background: #333;
    color: white;
    height: 100%;
    font-size: 10px;
}

.product-info-section {
    display: flex;
    flex-direction: column;
    gap: 25px;
}

.info-block {
    background: white;
    border-radius: 10px;
    padding: 25px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.info-block h3 {
    font-size: 18px;
    color: #133E71;
    margin-bottom: 20px;
    font-weight: 600;
    border-bottom: 2px solid #e9ecef;
    padding-bottom: 10px;
}

.info-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 15px;
}

.info-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 10px 0;
    border-bottom: 1px solid #f1f3f4;
}

.info-item:last-child {
    border-bottom: none;
}

.label {
    font-weight: 600;
    color: #495057;
    flex-shrink: 0;
    margin-right: 15px;
}

.value {
    color: #333;
    text-align: right;
    flex-grow: 1;
}

.status-container {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.status-badge {
    display: inline-block;
    padding: 8px 16px;
    border-radius: 20px;
    font-weight: 600;
    text-align: center;
    width: fit-content;
}

.status-1 { background-color: #e3f2fd; color: #1976d2; }
.status-2 { background-color: #fff3e0; color: #f57c00; }
.status-3 { background-color: #e8f5e8; color: #388e3c; }
.status-4 { background-color: #fce4ec; color: #c2185b; }
.status-unknown { background-color: #f5f5f5; color: #666; }

.comment-section {
    margin-top: 15px;
}

.comment-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 10px;
}

.edit-comment-btn {
    display: flex;
    align-items: center;
    gap: 6px;
    padding: 6px 12px;
    background: #133E71;
    color: white;
    border: none;
    border-radius: 4px;
    font-size: 12px;
    cursor: pointer;
    transition: all 0.3s ease;
}

.edit-comment-btn:hover {
    background: #0f2d56;
    transform: translateY(-1px);
}

.edit-comment-btn svg {
    width: 14px;
    height: 14px;
}

.comment-content {
    background: #f8f9fa;
    padding: 15px;
    border-radius: 6px;
    border-left: 4px solid #133E71;
}

.comment-content p {
    margin: 0;
    color: #495057;
    line-height: 1.5;
}

.no-comment {
    color: #6c757d !important;
    font-style: italic;
}

.comment-edit {
    background: #f8f9fa;
    padding: 15px;
    border-radius: 6px;
    border-left: 4px solid #133E71;
}

.comment-textarea {
    width: 100%;
    padding: 10px;
    border: 1px solid #ced4da;
    border-radius: 4px;
    font-family: inherit;
    font-size: 14px;
    line-height: 1.5;
    resize: vertical;
    min-height: 80px;
}

.comment-textarea:focus {
    outline: none;
    border-color: #133E71;
    box-shadow: 0 0 0 2px rgba(19, 62, 113, 0.2);
}

.comment-actions {
    display: flex;
    gap: 10px;
    margin-top: 10px;
}

.btn-sm {
    padding: 6px 12px;
    font-size: 12px;
}

.btn-secondary {
    background-color: #6c757d;
    color: white;
    border: 1px solid #6c757d;
}

.btn-secondary:hover {
    background-color: #5a6268;
    border-color: #545b62;
}

/* Стили для редактирования оплаты */
.block-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
}

.block-header h3 {
    margin: 0;
}

.payment-item {
    margin-bottom: 20px;
    padding: 15px;
    border: 1px solid #e9ecef;
    border-radius: 6px;
    background: #f8f9fa;
}

.payment-item:last-child {
    margin-bottom: 0;
}

.payment-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 10px;
}

.edit-payment-btn {
    display: flex;
    align-items: center;
    gap: 6px;
    padding: 6px 12px;
    background: #133E71;
    color: white;
    border: none;
    border-radius: 4px;
    font-size: 12px;
    cursor: pointer;
    transition: all 0.3s ease;
}

.edit-payment-btn:hover {
    background: #0f2d56;
    transform: translateY(-1px);
}

.edit-payment-btn svg {
    width: 14px;
    height: 14px;
}

.payment-content {
    color: #495057;
}

.payment-content p {
    margin: 8px 0 0 0;
    line-height: 1.5;
}

.no-value {
    color: #6c757d;
    font-style: italic;
}

.payment-edit {
    margin-top: 10px;
}

.payment-select, .payment-input {
    width: 100%;
    padding: 10px;
    border: 1px solid #ced4da;
    border-radius: 4px;
    font-family: inherit;
    font-size: 14px;
    background: white;
}

.payment-select:focus, .payment-input:focus {
    outline: none;
    border-color: #133E71;
    box-shadow: 0 0 0 2px rgba(19, 62, 113, 0.2);
}

.payment-textarea {
    width: 100%;
    padding: 10px;
    border: 1px solid #ced4da;
    border-radius: 4px;
    font-family: inherit;
    font-size: 14px;
    line-height: 1.5;
    resize: vertical;
    min-height: 80px;
    background: white;
}

.payment-textarea:focus {
    outline: none;
    border-color: #133E71;
    box-shadow: 0 0 0 2px rgba(19, 62, 113, 0.2);
}

.payment-actions {
    display: flex;
    gap: 10px;
    margin-top: 10px;
}

/* Стили для кнопок контактов */
.contact-link {
    background: none;
    border: none;
    color: #133E71;
    font-weight: 600;
    cursor: pointer;
    padding: 0;
    text-decoration: underline;
    text-decoration-color: transparent;
    transition: all 0.3s ease;
    font-size: inherit;
}

.contact-link:hover {
    color: #1C5BA4;
    text-decoration-color: #1C5BA4;
    transform: translateY(-1px);
}

/* Стили для ссылки на организацию */
.company-link {
    color: #133E71;
    text-decoration: none;
    font-weight: 600;
    transition: all 0.3s ease;
    padding: 2px 4px;
    border-radius: 4px;
}

.company-link:hover {
    color: #1C5BA4;
    background-color: #e8f0fe;
    text-decoration: underline;
    transform: translateY(-1px);
}

.status-comment {
    background: #f8f9fa;
    padding: 15px;
    border-radius: 6px;
    border-left: 4px solid #133E71;
}

.status-comment p {
    margin-top: 8px;
    color: #495057;
    line-height: 1.5;
}

.chars-item, .loading-item, .removal-item, .payment-item {
    margin-bottom: 15px;
}

.chars-item:last-child, .loading-item:last-child, .removal-item:last-child, .payment-item:last-child {
    margin-bottom: 0;
}

.chars-item p, .loading-item p, .removal-item p, .payment-item p {
    margin-top: 8px;
    color: #495057;
    line-height: 1.5;
    background: #f8f9fa;
    padding: 10px;
    border-radius: 4px;
}

.price {
    font-size: 18px;
    font-weight: 700;
    color: #28a745;
}

/* Стили для галереи */
.gallery-modal {
    display: none;
    position: fixed;
    z-index: 2000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0,0,0,0.9);
}

.gallery-modal-content {
    position: relative;
    width: 100%;
    height: 100%;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
}

.gallery-close {
    position: absolute;
    top: 20px;
    right: 35px;
    color: white;
    font-size: 40px;
    font-weight: bold;
    cursor: pointer;
    z-index: 2001;
}

.gallery-close:hover {
    opacity: 0.7;
}

.gallery-main {
    position: relative;
    display: flex;
    align-items: center;
    justify-content: center;
    width: 90%;
    height: 80%;
}

.gallery-item-container {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
}

.gallery-item-container img,
.gallery-item-container video {
    max-width: 100%;
    max-height: 100%;
    width: auto;
    height: auto;
    object-fit: contain;
}

.gallery-nav {
    position: absolute;
    background: rgba(255,255,255,0.8);
    border: none;
    color: #333;
    font-size: 24px;
    font-weight: bold;
    padding: 15px 20px;
    cursor: pointer;
    border-radius: 50%;
    transition: all 0.3s ease;
    z-index: 2001;
}

.gallery-nav:hover {
    background: rgba(255,255,255,1);
    transform: scale(1.1);
}

.gallery-prev {
    left: 20px;
}

.gallery-next {
    right: 20px;
}

    .gallery-counter {
        position: absolute;
        bottom: 20px;
        left: 50%;
        transform: translateX(-50%);
        color: white;
        background: rgba(0,0,0,0.7);
        padding: 10px 20px;
        border-radius: 20px;
        font-size: 16px;
    }

    /* Стили для модального окна контакта */
    .contact-modal {
        display: none;
        position: fixed;
        z-index: 10000;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0,0,0,0.5);
        align-items: center;
        justify-content: center;
    }

    .contact-modal-content {
        background-color: white;
        border-radius: 12px;
        width: 90%;
        max-width: 500px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.3);
        animation: modalSlideIn 0.3s ease;
    }

    @keyframes modalSlideIn {
        from {
            opacity: 0;
            transform: translateY(-50px) scale(0.9);
        }
        to {
            opacity: 1;
            transform: translateY(0) scale(1);
        }
    }

    .contact-modal-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 20px 25px;
        border-bottom: 1px solid #e9ecef;
    }

    .contact-modal-header h3 {
        margin: 0;
        color: #133E71;
        font-size: 18px;
        font-weight: 600;
    }

    .contact-modal-close {
        background: none;
        border: none;
        cursor: pointer;
        padding: 8px;
        border-radius: 50%;
        transition: background-color 0.3s ease;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .contact-modal-close:hover {
        background-color: #f8f9fa;
    }

    .contact-modal-close svg {
        color: #666;
    }

    .contact-modal-body {
        padding: 25px;
    }

    .contact-info-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 12px 0;
        border-bottom: 1px solid #f1f3f4;
    }

    .contact-info-item:last-child {
        border-bottom: none;
    }

    .contact-info-item label {
        font-weight: 600;
        color: #495057;
        min-width: 100px;
    }

    .contact-info-item span {
        color: #333;
        text-align: right;
        flex: 1;
    }

    .contact-messengers {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
    }

    .messenger-link {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 8px 12px;
        border-radius: 20px;
        text-decoration: none;
        font-size: 12px;
        font-weight: 500;
        transition: all 0.3s ease;
    }

    .messenger-link.telegram {
        background-color: #0088cc;
        color: white;
    }

    .messenger-link.telegram:hover {
        background-color: #0077b3;
        transform: translateY(-1px);
    }

    .messenger-link.whatsapp {
        background-color: #25d366;
        color: white;
    }

    .messenger-link.whatsapp:hover {
        background-color: #20ba5a;
        transform: translateY(-1px);
    }

    .no-messengers {
        color: #999;
        font-style: italic;
        font-size: 13px;
    }

/* Адаптивность */
@media (max-width: 768px) {
    .product-content {
        grid-template-columns: 1fr;
        gap: 20px;
    }
    
    .product-title {
        font-size: 24px;
    }
    
    .info-grid {
        grid-template-columns: 1fr;
    }
    
    .info-item {
        flex-direction: column;
        align-items: flex-start;
        gap: 5px;
    }
    
    .value {
        text-align: left;
    }
    
    .thumbnails-container {
        justify-content: center;
    }
    
    .gallery-nav {
        font-size: 18px;
        padding: 10px 15px;
    }
    
    .gallery-prev {
        left: 10px;
    }
    
    .gallery-next {
        right: 10px;
    }
    
    /* Адаптивность для модального окна контакта */
    .contact-modal-content {
        width: 95%;
        margin: 20px;
    }
    
    .contact-modal-header {
        padding: 15px 20px;
    }
    
    .contact-modal-body {
        padding: 20px;
    }
    
    .contact-info-item {
        flex-direction: column;
        align-items: flex-start;
        gap: 5px;
    }
    
    .contact-info-item span {
        text-align: left;
    }
    
    .action-buttons {
        flex-direction: column;
    }
    
    .action-buttons .btn {
        width: 100%;
        text-align: center;
    }
}
</style>

<script>
let currentGalleryIndex = 0;
let galleryItems = [];

// Инициализация галереи
document.addEventListener('DOMContentLoaded', function() {
    @if($product->mediaOrdered->count() > 0)
        galleryItems = [
            @foreach($product->mediaOrdered as $media)
                {
                    url: '{{ asset('storage/' . $media->file_path) }}',
                    type: '{{ $media->file_type }}',
                    name: '{{ $media->file_name }}',
                    mime: '{{ $media->mime_type }}'
                },
            @endforeach
        ];
    @endif
});

function changeMainImage(url, index, type) {
    const mainImage = document.getElementById('mainImage');
    if (mainImage && type === 'image') {
        mainImage.src = url;
    }
    
    // Обновляем активный thumbnail
    document.querySelectorAll('.thumbnail').forEach(thumb => thumb.classList.remove('active'));
    document.querySelectorAll('.thumbnail')[index].classList.add('active');
    
    currentGalleryIndex = index;
}

function openGallery(index = 0) {
    if (galleryItems.length === 0) return;
    
    currentGalleryIndex = index;
    const modal = document.getElementById('galleryModal');
    modal.style.display = 'block';
    document.body.style.overflow = 'hidden';
    
    showGalleryItem(currentGalleryIndex);
}

function closeGallery() {
    const modal = document.getElementById('galleryModal');
    modal.style.display = 'none';
    document.body.style.overflow = 'auto';
}

function showGalleryItem(index) {
    if (index < 0 || index >= galleryItems.length) return;
    
    const item = galleryItems[index];
    const image = document.getElementById('galleryImage');
    const video = document.getElementById('galleryVideo');
    const counter = document.getElementById('galleryCounter');
    
    if (item.type === 'image') {
        image.src = item.url;
        image.style.display = 'block';
        video.style.display = 'none';
    } else if (item.type === 'video') {
        video.src = item.url;
        video.style.display = 'block';
        image.style.display = 'none';
    }
    
    counter.textContent = `${index + 1} / ${galleryItems.length}`;
}

function prevImage() {
    currentGalleryIndex = currentGalleryIndex > 0 ? currentGalleryIndex - 1 : galleryItems.length - 1;
    showGalleryItem(currentGalleryIndex);
}

function nextImage() {
    currentGalleryIndex = currentGalleryIndex < galleryItems.length - 1 ? currentGalleryIndex + 1 : 0;
    showGalleryItem(currentGalleryIndex);
}

// Клавиатурные сокращения для галереи
document.addEventListener('keydown', function(e) {
    const modal = document.getElementById('galleryModal');
    if (modal.style.display === 'block') {
        switch(e.key) {
            case 'Escape':
                closeGallery();
                break;
            case 'ArrowLeft':
                prevImage();
                break;
            case 'ArrowRight':
                nextImage();
                break;
        }
    }
});

// Закрытие галереи при клике вне изображения
document.getElementById('galleryModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeGallery();
    }
});

// Функции для редактирования комментариев
function editComment(field) {
    const content = document.getElementById(field + '_content');
    const edit = document.getElementById(field + '_edit');
    const textarea = document.getElementById(field + '_textarea');
    
    content.style.display = 'none';
    edit.style.display = 'block';
    textarea.focus();
    textarea.select();
}

function cancelEdit(field) {
    const content = document.getElementById(field + '_content');
    const edit = document.getElementById(field + '_edit');
    const textarea = document.getElementById(field + '_textarea');
    
    // Восстанавливаем оригинальное значение
    const originalValue = textarea.getAttribute('data-original') || '';
    textarea.value = originalValue;
    
    content.style.display = 'block';
    edit.style.display = 'none';
}

function saveComment(field) {
    const textarea = document.getElementById(field + '_textarea');
    const content = document.getElementById(field + '_content');
    const edit = document.getElementById(field + '_edit');
    const value = textarea.value.trim();
    
    // Показываем индикатор загрузки
    const saveBtn = edit.querySelector('.btn-primary');
    const originalText = saveBtn.textContent;
    saveBtn.textContent = 'Сохранение...';
    saveBtn.disabled = true;
    
    // Отправляем AJAX запрос
    fetch(`/product/{{ $product->id }}/comment`, {
        method: 'PATCH',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            field: field,
            value: value
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Обновляем отображение
            if (value) {
                content.innerHTML = `<p>${value}</p>`;
            } else {
                content.innerHTML = '<p class="no-comment">Комментарий не указан</p>';
            }
            
            // Сохраняем новое значение как оригинальное
            textarea.setAttribute('data-original', value);
            
            // Скрываем форму редактирования
            content.style.display = 'block';
            edit.style.display = 'none';
            
            // Показываем уведомление об успехе
            showNotification('Комментарий успешно обновлен', 'success');
        } else {
            throw new Error(data.message || 'Ошибка при сохранении');
        }
    })
    .catch(error => {
        console.error('Ошибка:', error);
        showNotification('Ошибка при сохранении комментария', 'error');
    })
    .finally(() => {
        // Восстанавливаем кнопку
        saveBtn.textContent = originalText;
        saveBtn.disabled = false;
    });
}

function showNotification(message, type = 'info') {
    // Создаем уведомление
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    notification.textContent = message;
    
    // Добавляем стили
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        padding: 15px 20px;
        border-radius: 6px;
        color: white;
        font-weight: 500;
        z-index: 3000;
        animation: slideIn 0.3s ease;
        max-width: 300px;
    `;
    
    // Цвета для разных типов уведомлений
    if (type === 'success') {
        notification.style.backgroundColor = '#28a745';
    } else if (type === 'error') {
        notification.style.backgroundColor = '#dc3545';
    } else {
        notification.style.backgroundColor = '#17a2b8';
    }
    
    document.body.appendChild(notification);
    
    // Удаляем уведомление через 3 секунды
    setTimeout(() => {
        notification.style.animation = 'slideOut 0.3s ease';
        setTimeout(() => {
            if (notification.parentNode) {
                notification.parentNode.removeChild(notification);
            }
        }, 300);
    }, 3000);
}

// Добавляем CSS анимации для уведомлений
const style = document.createElement('style');
style.textContent = `
    @keyframes slideIn {
        from {
            transform: translateX(100%);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }
    
    @keyframes slideOut {
        from {
            transform: translateX(0);
            opacity: 1;
        }
        to {
            transform: translateX(100%);
            opacity: 0;
        }
    }
`;
document.head.appendChild(style);

// Инициализация оригинальных значений для textarea
document.addEventListener('DOMContentLoaded', function() {
    const textareas = document.querySelectorAll('.comment-textarea');
    textareas.forEach(textarea => {
        textarea.setAttribute('data-original', textarea.value);
    });
    
    // Инициализация оригинальных значений для полей оплаты
    const paymentTextareas = document.querySelectorAll('.payment-textarea');
    paymentTextareas.forEach(textarea => {
        textarea.setAttribute('data-original', textarea.value);
    });
    
    const paymentInputs = document.querySelectorAll('.payment-input');
    paymentInputs.forEach(input => {
        input.setAttribute('data-original', input.value);
    });
    
    const paymentSelects = document.querySelectorAll('.payment-select');
    paymentSelects.forEach(select => {
        select.setAttribute('data-original', select.value);
    });
});

// Функции для редактирования блока оплаты
function editPaymentBlock() {
    // Скрываем все контенты и показываем формы редактирования
    const fields = ['payment_method', 'purchase_price', 'payment_comment'];
    
    fields.forEach(field => {
        const content = document.getElementById(field + '_content');
        const edit = document.getElementById(field + '_edit');
        
        content.style.display = 'none';
        edit.style.display = 'block';
    });
    
    // Показываем общие кнопки действий
    document.getElementById('payment_actions').style.display = 'flex';
    
    // Скрываем кнопку редактирования
    document.querySelector('.edit-payment-btn').style.display = 'none';
    
    // Фокусируемся на первом поле
    const firstSelect = document.getElementById('payment_method_select');
    if (firstSelect) {
        firstSelect.focus();
    }
}

function cancelPaymentEdit() {
    // Восстанавливаем все оригинальные значения
    const fields = ['payment_method', 'purchase_price', 'payment_comment'];
    
    fields.forEach(field => {
        const content = document.getElementById(field + '_content');
        const edit = document.getElementById(field + '_edit');
        
        // Восстанавливаем оригинальное значение
        if (field === 'payment_method') {
            const select = document.getElementById(field + '_select');
            const originalValue = select.getAttribute('data-original') || '';
            select.value = originalValue;
        } else if (field === 'purchase_price') {
            const input = document.getElementById(field + '_input');
            const originalValue = input.getAttribute('data-original') || '';
            input.value = originalValue;
        } else if (field === 'payment_comment') {
            const textarea = document.getElementById(field + '_textarea');
            const originalValue = textarea.getAttribute('data-original') || '';
            textarea.value = originalValue;
        }
        
        content.style.display = 'block';
        edit.style.display = 'none';
    });
    
    // Скрываем общие кнопки действий
    document.getElementById('payment_actions').style.display = 'none';
    
    // Показываем кнопку редактирования
    document.querySelector('.edit-payment-btn').style.display = 'flex';
}

function savePaymentBlock() {
    const fields = ['payment_method', 'purchase_price', 'payment_comment'];
    const updates = {};
    
    // Собираем все значения
    fields.forEach(field => {
        let value;
        let element;
        
        if (field === 'payment_method') {
            element = document.getElementById(field + '_select');
            value = element.value;
        } else if (field === 'purchase_price') {
            element = document.getElementById(field + '_input');
            value = element.value.trim();
        } else if (field === 'payment_comment') {
            element = document.getElementById(field + '_textarea');
            value = element.value.trim();
        }
        
        updates[field] = value;
    });
    
    // Показываем индикатор загрузки
    const saveBtn = document.querySelector('#payment_actions .btn-primary');
    const originalText = saveBtn.textContent;
    saveBtn.textContent = 'Сохранение...';
    saveBtn.disabled = true;
    
    // Отправляем AJAX запросы для каждого поля
    const promises = Object.entries(updates).map(([field, value]) => {
        return fetch(`/product/{{ $product->id }}/comment`, {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                field: field,
                value: value
            })
        }).then(response => response.json());
    });
    
    Promise.all(promises)
        .then(results => {
            const allSuccess = results.every(result => result.success);
            
            if (allSuccess) {
                // Обновляем отображение всех полей
                fields.forEach(field => {
                    const content = document.getElementById(field + '_content');
                    const value = updates[field];
                    
                    if (field === 'payment_method') {
                        let displayText = 'Не указан';
                        if (value) {
                            switch(value) {
                                case 'cash': displayText = 'Наличные'; break;
                                case 'cashless_with_vat': displayText = 'Безнал с НДС'; break;
                                case 'cashless_without_vat': displayText = 'Безнал без НДС'; break;
                                case 'combined': displayText = 'Комбинированная'; break;
                                case 'other': displayText = 'Другое'; break;
                                default: displayText = value;
                            }
                        }
                        content.innerHTML = value ? `<span>${displayText}</span>` : '<span class="no-value">Не указан</span>';
                    } else if (field === 'purchase_price') {
                        if (value) {
                            const formattedPrice = new Intl.NumberFormat('ru-RU').format(parseFloat(value));
                            content.innerHTML = `<span class="price">${formattedPrice} ₽</span>`;
                        } else {
                            content.innerHTML = '<span class="no-value">Не указана</span>';
                        }
                    } else if (field === 'payment_comment') {
                        content.innerHTML = value ? `<p>${value}</p>` : '<p class="no-comment">Комментарий не указан</p>';
                    }
                    
                    // Сохраняем новое значение как оригинальное
                    const element = document.getElementById(field + '_select') || 
                                   document.getElementById(field + '_input') || 
                                   document.getElementById(field + '_textarea');
                    element.setAttribute('data-original', value);
                    
                    // Показываем контент и скрываем форму редактирования
                    content.style.display = 'block';
                    document.getElementById(field + '_edit').style.display = 'none';
                });
                
                // Скрываем общие кнопки действий
                document.getElementById('payment_actions').style.display = 'none';
                
                // Показываем кнопку редактирования
                document.querySelector('.edit-payment-btn').style.display = 'flex';
                
                // Показываем уведомление об успехе
                showNotification('Все данные успешно обновлены', 'success');
            } else {
                throw new Error('Ошибка при сохранении некоторых данных');
            }
        })
        .catch(error => {
            console.error('Ошибка:', error);
            showNotification('Ошибка при сохранении данных', 'error');
        })
        .finally(() => {
            // Восстанавливаем кнопку
            saveBtn.textContent = originalText;
            saveBtn.disabled = false;
        });
}

// Функции для модального окна контакта
function showContactCard(id, name, email, phone, role, hasTelegram, hasWhatsapp) {
    document.getElementById('contactName').textContent = name;
    document.getElementById('contactEmail').textContent = email || 'Не указан';
    document.getElementById('contactPhone').textContent = phone || 'Не указан';
    document.getElementById('contactRole').textContent = role;
    
    const messengersDiv = document.getElementById('contactMessengers');
    messengersDiv.innerHTML = '';
    
    if (hasTelegram === 'true' || hasTelegram === true) {
        messengersDiv.innerHTML += '<a href="https://t.me/' + phone + '" target="_blank" class="messenger-link telegram">Telegram</a>';
    }
    
    if (hasWhatsapp === 'true' || hasWhatsapp === true) {
        messengersDiv.innerHTML += '<a href="https://wa.me/' + phone + '" target="_blank" class="messenger-link whatsapp">WhatsApp</a>';
    }
    
    if (!hasTelegram && !hasWhatsapp) {
        messengersDiv.innerHTML = '<span class="no-messengers">Мессенджеры не подключены</span>';
    }
    
    document.getElementById('contactModal').style.display = 'flex';
}

function closeContactCard() {
    document.getElementById('contactModal').style.display = 'none';
}

// Закрытие модального окна при клике вне его
window.onclick = function(event) {
    const modal = document.getElementById('contactModal');
    if (event.target === modal) {
        closeContactCard();
    }
}
</script>

<!-- Всплывающее окно для карточки контакта -->
<div id="contactModal" class="contact-modal">
    <div class="contact-modal-content">
        <div class="contact-modal-header">
            <h3>Карточка контакта</h3>
            <button class="contact-modal-close" onclick="closeContactCard()">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
            </button>
        </div>
        <div class="contact-modal-body">
            <div class="contact-info-item">
                <label>Имя:</label>
                <span id="contactName"></span>
            </div>
            <div class="contact-info-item">
                <label>Email:</label>
                <span id="contactEmail"></span>
            </div>
            <div class="contact-info-item">
                <label>Телефон:</label>
                <span id="contactPhone"></span>
            </div>
            <div class="contact-info-item">
                <label>Роль:</label>
                <span id="contactRole"></span>
            </div>
            <div class="contact-info-item">
                <label>Мессенджеры:</label>
                <div id="contactMessengers" class="contact-messengers"></div>
            </div>
        </div>
    </div>
</div>

@endsection
