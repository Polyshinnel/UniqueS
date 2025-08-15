@extends('layouts.layout')

@section('content')
<!-- Подключение Quill.js -->
<link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
<script src="https://cdn.quilljs.com/1.3.6/quill.min.js"></script>
<div class="advertisement-item-container">
    <div class="advertisement-header">
        <div class="breadcrumb">
            <a href="{{ route('advertisements.index') }}">Объявления</a> / {{ $advertisement->title }}
        </div>
        <div class="advertisement-header-actions">
            <h1 class="advertisement-title">{{ $advertisement->title }}</h1>
            <div class="advertisement-actions">
                <a href="{{ route('advertisements.edit', $advertisement) }}" class="btn btn-primary">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"></path>
                    </svg>
                    Редактировать
                </a>
            </div>
        </div>
        <div class="advertisement-status">
            <span class="status-badge" style="background-color: {{ $advertisement->status === 'active' ? '#28a745' : ($advertisement->status === 'draft' ? '#ffc107' : '#6c757d') }}; color: white;">
                {{ $advertisement->status_name }}
            </span>
        </div>
    </div>

    <div class="advertisement-content">
        <!-- Блок с медиафайлами -->
        <div class="advertisement-media-section">
            @if($advertisement->mediaOrdered->count() > 0)
                <div class="media-gallery">
                    <div class="main-image-container">
                        @php
                            $mainImage = $advertisement->mediaOrdered->where('file_type', 'image')->first();
                        @endphp
                        @if($mainImage)
                            <img id="mainImage" src="{{ asset('storage/' . $mainImage->file_path) }}" 
                                 alt="{{ $advertisement->title }}" class="main-image" 
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
                    
                    @if($advertisement->mediaOrdered->count() > 1)
                        <div class="thumbnails-container">
                            @foreach($advertisement->mediaOrdered as $index => $media)
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
            <div class="advertisement-actions-section">
                <div class="info-block">
                    <h3>Следующие действия</h3>
                    <div class="action-info">
                        <div class="action-date">
                            <span class="label">Дата:</span>
                            <span class="value">{{ now()->format('d.m.Y') }}</span>
                        </div>
                        <div class="action-description">
                            <span class="label">Что требуется сделать:</span>
                            <p>Проверить актуальность объявления, обновить информацию при необходимости</p>
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
                                <span class="event-type">Создание</span>
                                <span class="event-date">{{ $advertisement->created_at->format('d.m.Y H:i:s') }}</span>
                            </div>
                            <div class="event-content">
                                <p>Объявление создано</p>
                            </div>
                            <div class="event-footer">
                                <span>Создал: {{ $advertisement->creator->name ?? 'Создатель не указан' }}</span>
                            </div>
                        </div>
                        @if($advertisement->published_at)
                        <div class="event-item">
                            <div class="event-header">
                                <span class="event-type">Публикация</span>
                                <span class="event-date">{{ $advertisement->published_at->format('d.m.Y H:i:s') }}</span>
                            </div>
                            <div class="event-content">
                                <p>Объявление опубликовано</p>
                            </div>
                            <div class="event-footer">
                                <span>Опубликовал: {{ $advertisement->creator->name ?? 'Не указан' }}</span>
                            </div>
                        </div>
                        @endif
                    </div>
                    <div class="events-actions">
                        <button class="btn btn-secondary">История</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Основная информация об объявлении -->
        <div class="advertisement-info-section">
            <div class="info-block">
                <h3>Основная информация</h3>
                <div class="info-grid">
                    <div class="info-item">
                        <span class="label">Категория:</span>
                        <span class="value">{{ $advertisement->category->name ?? 'Не указана' }}</span>
                    </div>
                    <div class="info-item">
                        <span class="label">Товар:</span>
                        <span class="value">
                            @if($advertisement->product && $advertisement->product->name)
                                <a href="{{ route('products.show', $advertisement->product) }}" class="company-link">
                                    {{ $advertisement->product->name }}
                                </a>
                            @elseif($advertisement->product)
                                <a href="{{ route('products.show', $advertisement->product) }}" class="company-link">
                                    Товар #{{ $advertisement->product->id }}
                                </a>
                            @else
                                Не указан
                            @endif
                        </span>
                    </div>
                    <div class="info-item">
                        <span class="label">Организация:</span>
                        <span class="value">
                            @if($advertisement->product && $advertisement->product->company && $advertisement->product->company->name)
                                <a href="{{ route('companies.show', $advertisement->product->company) }}" class="company-link">
                                    {{ $advertisement->product->company->name }}
                                </a>
                            @elseif($advertisement->product && $advertisement->product->company)
                                <a href="{{ route('companies.show', $advertisement->product->company) }}" class="company-link">
                                    Организация #{{ $advertisement->product->company->id }}
                                </a>
                            @else
                                Не указана
                            @endif
                        </span>
                    </div>
                    <div class="info-item">
                        <span class="label">Отвественный:</span>
                        <span class="value">
                            @if($advertisement->creator)
                                <button class="contact-link" onclick="showContactCard({{ $advertisement->creator->id }}, '{{ $advertisement->creator->name }}', '{{ $advertisement->creator->email }}', '{{ $advertisement->creator->phone }}', '{{ $advertisement->creator->role->name ?? 'Роль не указана' }}', {{ $advertisement->creator->has_telegram ? 'true' : 'false' }}, {{ $advertisement->creator->has_whatsapp ? 'true' : 'false' }})">
                                    {{ $advertisement->creator->name }}
                                </button>
                            @else
                                Не указан
                            @endif
                        </span>
                    </div>
                    <div class="info-item">
                        <span class="label">Дата создания:</span>
                        <span class="value">{{ $advertisement->created_at->format('d.m.Y H:i') }}</span>
                    </div>
                    @if($advertisement->published_at)
                    <div class="info-item">
                        <span class="label">Дата публикации:</span>
                        <span class="value">{{ $advertisement->published_at->format('d.m.Y H:i') }}</span>
                    </div>
                    @endif
                </div>
            </div>

            @if($advertisement->main_characteristics || $advertisement->complectation)
                <div class="info-block">
                    <h3>Характеристики</h3>
                    @if($advertisement->main_characteristics)
                        <div class="chars-item">
                            <strong>Основные характеристики:</strong>
                            <p>{{ $advertisement->main_characteristics }}</p>
                        </div>
                    @endif
                    @if($advertisement->complectation)
                        <div class="chars-item">
                            <strong>Комплектация:</strong>
                            <p>{{ $advertisement->complectation }}</p>
                        </div>
                    @endif
                </div>
            @endif

            @if($advertisement->technical_characteristics || true)
                <div class="info-block">
                    <div class="block-header">
                        <h3>Технические характеристики</h3>
                        <button class="edit-comment-btn" onclick="editComment('technical_characteristics')">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"></path>
                            </svg>
                            Редактировать
                        </button>
                    </div>
                    <div class="comment-content" id="technical_characteristics_content">
                        @if($advertisement->technical_characteristics)
                            <div class="html-content">{!! $advertisement->technical_characteristics !!}</div>
                        @else
                            <p class="no-comment">Технические характеристики не указаны</p>
                        @endif
                    </div>
                    <div class="comment-edit" id="technical_characteristics_edit" style="display: none;">
                        <div class="editor-container">
                            <textarea class="comment-textarea" id="technical_characteristics_textarea" rows="5" data-original="{{ $advertisement->technical_characteristics }}" style="display: none;">{{ $advertisement->technical_characteristics }}</textarea>
                            <div id="technical_characteristics_edit_editor"></div>
                        </div>
                        <div class="comment-actions">
                            <button class="btn btn-primary btn-sm" onclick="saveComment('technical_characteristics')">Сохранить</button>
                            <button class="btn btn-secondary btn-sm" onclick="cancelEdit('technical_characteristics')">Отмена</button>
                        </div>
                    </div>
                </div>
            @endif

            @if($advertisement->main_info || true)
                <div class="info-block">
                    <div class="block-header">
                        <h3>Основная информация</h3>
                        <button class="edit-comment-btn" onclick="editComment('main_info')">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"></path>
                            </svg>
                            Редактировать
                        </button>
                    </div>
                    <div class="comment-content" id="main_info_content">
                        @if($advertisement->main_info)
                            <div class="html-content">{!! $advertisement->main_info !!}</div>
                        @else
                            <p class="no-comment">Основная информация не указана</p>
                        @endif
                    </div>
                    <div class="comment-edit" id="main_info_edit" style="display: none;">
                        <div class="editor-container">
                            <textarea class="comment-textarea" id="main_info_textarea" rows="5" data-original="{{ $advertisement->main_info }}" style="display: none;">{{ $advertisement->main_info }}</textarea>
                            <div id="main_info_edit_editor"></div>
                        </div>
                        <div class="comment-actions">
                            <button class="btn btn-primary btn-sm" onclick="saveComment('main_info')">Сохранить</button>
                            <button class="btn btn-secondary btn-sm" onclick="cancelEdit('main_info')">Отмена</button>
                        </div>
                    </div>
                </div>
            @endif

            @if($advertisement->tags && $advertisement->tags->count() > 0)
                <div class="info-block">
                    <h3>Теги</h3>
                    <div class="tags-container">
                        @foreach($advertisement->tags as $tag)
                            <span class="tag">{{ $tag->tag }}</span>
                        @endforeach
                    </div>
                </div>
            @endif

            @if($advertisement->additional_info || true)
                <div class="info-block">
                    <div class="block-header">
                        <h3>Дополнительная информация</h3>
                        <button class="edit-comment-btn" onclick="editComment('additional_info')">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"></path>
                            </svg>
                            Редактировать
                        </button>
                    </div>
                    <div class="comment-content" id="additional_info_content">
                        @if($advertisement->additional_info)
                            <div class="html-content">{!! $advertisement->additional_info !!}</div>
                        @else
                            <p class="no-comment">Дополнительная информация не указана</p>
                        @endif
                    </div>
                    <div class="comment-edit" id="additional_info_edit" style="display: none;">
                        <div class="editor-container">
                            <textarea class="comment-textarea" id="additional_info_textarea" rows="5" data-original="{{ $advertisement->additional_info }}" style="display: none;">{{ $advertisement->additional_info }}</textarea>
                            <div id="additional_info_edit_editor"></div>
                        </div>
                        <div class="comment-actions">
                            <button class="btn btn-primary btn-sm" onclick="saveComment('additional_info')">Сохранить</button>
                            <button class="btn btn-secondary btn-sm" onclick="cancelEdit('additional_info')">Отмена</button>
                        </div>
                    </div>
                </div>
            @endif

            @if($advertisement->check_data || true)
                <div class="info-block">
                    <h3>Информация о проверке</h3>
                    @php
                        $checkStatus = null;
                        if (isset($advertisement->check_data['status_id'])) {
                            $checkStatus = \App\Models\ProductCheckStatuses::find($advertisement->check_data['status_id']);
                        }
                    @endphp
                    <div class="check-container">
                        <div class="check-status">
                            <strong>Статус проверки:</strong>
                            <span class="status-badge" style="background-color: {{ $checkStatus->color ?? '#6c757d' }}; color: white;">
                                {{ $checkStatus->name ?? 'Не указан' }}
                            </span>
                        </div>
                        <div class="comment-section" data-field="check_comment">
                            <div class="comment-header">
                                <strong>Комментарий к проверке:</strong>
                                <button class="edit-comment-btn" onclick="editComment('check_comment')">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"></path>
                                    </svg>
                                    Редактировать
                                </button>
                            </div>
                            <div class="comment-content" id="check_comment_content">
                                @if(isset($advertisement->check_data['comment']) && $advertisement->check_data['comment'])
                                    <p>{{ $advertisement->check_data['comment'] }}</p>
                                @else
                                    <p class="no-comment">Комментарий к проверке не указан</p>
                                @endif
                            </div>
                            <div class="comment-edit" id="check_comment_edit" style="display: none;">
                                <textarea class="comment-textarea" id="check_comment_textarea" rows="3" data-original="{{ $advertisement->check_data['comment'] ?? '' }}">{{ $advertisement->check_data['comment'] ?? '' }}</textarea>
                                <div class="comment-actions">
                                    <button class="btn btn-primary btn-sm" onclick="saveComment('check_comment')">Сохранить</button>
                                    <button class="btn btn-secondary btn-sm" onclick="cancelEdit('check_comment')">Отмена</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            @if($advertisement->loading_data || true)
                <div class="info-block">
                    <h3>Информация о погрузке</h3>
                    @php
                        $loadingStatus = null;
                        if (isset($advertisement->loading_data['status_id'])) {
                            $loadingStatus = \App\Models\ProductInstallStatuses::find($advertisement->loading_data['status_id']);
                        }
                    @endphp
                    <div class="loading-container">
                        <div class="loading-status">
                            <strong>Статус погрузки:</strong>
                            <span class="status-badge">
                                {{ $loadingStatus->name ?? 'Не указан' }}
                            </span>
                        </div>
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
                                @if(isset($advertisement->loading_data['comment']) && $advertisement->loading_data['comment'])
                                    <p>{{ $advertisement->loading_data['comment'] }}</p>
                                @else
                                    <p class="no-comment">Комментарий по погрузке не указан</p>
                                @endif
                            </div>
                            <div class="comment-edit" id="loading_comment_edit" style="display: none;">
                                <textarea class="comment-textarea" id="loading_comment_textarea" rows="3" data-original="{{ $advertisement->loading_data['comment'] ?? '' }}">{{ $advertisement->loading_data['comment'] ?? '' }}</textarea>
                                <div class="comment-actions">
                                    <button class="btn btn-primary btn-sm" onclick="saveComment('loading_comment')">Сохранить</button>
                                    <button class="btn btn-secondary btn-sm" onclick="cancelEdit('loading_comment')">Отмена</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            @if($advertisement->removal_data || true)
                <div class="info-block">
                    <h3>Информация о демонтаже</h3>
                    @php
                        $removalStatus = null;
                        if (isset($advertisement->removal_data['status_id'])) {
                            $removalStatus = \App\Models\ProductInstallStatuses::find($advertisement->removal_data['status_id']);
                        }
                    @endphp
                    <div class="removal-container">
                        <div class="removal-status">
                            <strong>Статус демонтажа:</strong>
                            <span class="status-badge">
                                {{ $removalStatus->name ?? 'Не указан' }}
                            </span>
                        </div>
                        <div class="comment-section" data-field="removal_comment">
                            <div class="comment-header">
                                <strong>Комментарий по демонтажу:</strong>
                                <button class="edit-comment-btn" onclick="editComment('removal_comment')">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"></path>
                                    </svg>
                                    Редактировать
                                </button>
                            </div>
                            <div class="comment-content" id="removal_comment_content">
                                @if(isset($advertisement->removal_data['comment']) && $advertisement->removal_data['comment'])
                                    <p>{{ $advertisement->removal_data['comment'] }}</p>
                                @else
                                    <p class="no-comment">Комментарий по демонтажу не указан</p>
                                @endif
                            </div>
                            <div class="comment-edit" id="removal_comment_edit" style="display: none;">
                                <textarea class="comment-textarea" id="removal_comment_textarea" rows="3" data-original="{{ $advertisement->removal_data['comment'] ?? '' }}">{{ $advertisement->removal_data['comment'] ?? '' }}</textarea>
                                <div class="comment-actions">
                                    <button class="btn btn-primary btn-sm" onclick="saveComment('removal_comment')">Сохранить</button>
                                    <button class="btn btn-secondary btn-sm" onclick="cancelEdit('removal_comment')">Отмена</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            @if($advertisement->product || true)
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
                    
                    <!-- Варианты оплаты -->
                    <div class="payment-item">
                        <strong>Варианты оплаты:</strong>
                        <div class="payment-content" id="payment_method_content">
                            @if($advertisement->product && $advertisement->product->paymentVariants->count() > 0)
                                <div class="payment-variants">
                                    @foreach($advertisement->product->paymentVariants as $variant)
                                        <span class="payment-variant">{{ $variant->priceType->name }}</span>
                                    @endforeach
                                </div>
                            @else
                                <span class="no-value">Не указаны</span>
                            @endif
                        </div>
                        <div class="payment-edit" id="payment_method_edit" style="display: none;">
                            <div class="payment-checkboxes">
                                @foreach($priceTypes as $priceType)
                                    <label class="payment-checkbox">
                                        <input type="checkbox" name="payment_types[]" value="{{ $priceType->id }}"
                                               {{ $advertisement->product && $advertisement->product->paymentVariants->where('price_type_id', $priceType->id)->count() > 0 ? 'checked' : '' }}
                                               data-original="{{ $advertisement->product && $advertisement->product->paymentVariants->where('price_type_id', $priceType->id)->count() > 0 ? 'true' : 'false' }}">
                                        {{ $priceType->name }}
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <!-- Закупочная цена -->
                    <div class="payment-item">
                        <strong>Закупочная цена:</strong>
                        <div class="payment-content" id="purchase_price_content">
                            @if($advertisement->product && $advertisement->product->purchase_price)
                                <span class="price">{{ number_format($advertisement->product->purchase_price, 0, ',', ' ') }} ₽</span>
                            @else
                                <span class="no-value">Не указана</span>
                            @endif
                        </div>
                        <div class="payment-edit" id="purchase_price_edit" style="display: none;">
                            <input type="number" class="form-control" id="purchase_price_input" 
                                   value="{{ $advertisement->product->purchase_price ?? '' }}" 
                                   data-original="{{ $advertisement->product->purchase_price ?? '' }}"
                                   placeholder="Введите закупочную цену">
                        </div>
                    </div>

                    <!-- Цена продажи -->
                    <div class="payment-item">
                        <strong>Цена продажи:</strong>
                        <div class="payment-content" id="adv_price_content">
                            @if($advertisement->adv_price)
                                <span class="price">{{ number_format($advertisement->adv_price, 0, ',', ' ') }} ₽</span>
                            @else
                                <span class="no-value">Не указана</span>
                            @endif
                        </div>
                        <div class="payment-edit" id="adv_price_edit" style="display: none;">
                            <input type="number" class="form-control" id="adv_price_input" 
                                   value="{{ $advertisement->adv_price ?? '' }}" 
                                   data-original="{{ $advertisement->adv_price ?? '' }}"
                                   placeholder="Введите цену продажи">
                        </div>
                    </div>

                    <!-- Комментарий к продаже -->
                    <div class="payment-item">
                        <strong>Комментарий к продаже:</strong>
                        <div class="payment-content" id="adv_price_comment_content">
                            @if($advertisement->adv_price_comment)
                                <p>{{ $advertisement->adv_price_comment }}</p>
                            @else
                                <span class="no-value">Не указан</span>
                            @endif
                        </div>
                        <div class="payment-edit" id="adv_price_comment_edit" style="display: none;">
                            <textarea class="form-control" id="adv_price_comment_textarea" rows="3" 
                                      data-original="{{ $advertisement->adv_price_comment ?? '' }}"
                                      placeholder="Введите комментарий к продаже">{{ $advertisement->adv_price_comment ?? '' }}</textarea>
                        </div>
                    </div>

                    <!-- Комментарий по оплате -->
                    <div class="payment-item">
                        <strong>Комментарий по оплате:</strong>
                        <div class="payment-content" id="payment_comment_content">
                            @if($advertisement->product && $advertisement->product->payment_comment)
                                <p>{{ $advertisement->product->payment_comment }}</p>
                            @else
                                <span class="no-value">Не указан</span>
                            @endif
                        </div>
                        <div class="payment-edit" id="payment_comment_edit" style="display: none;">
                            <textarea class="form-control" id="payment_comment_textarea" rows="3" 
                                      data-original="{{ $advertisement->product->payment_comment ?? '' }}"
                                      placeholder="Введите комментарий по оплате">{{ $advertisement->product->payment_comment ?? '' }}</textarea>
                        </div>
                    </div>

                    <!-- Кнопки действий для блока оплаты -->
                    <div class="payment-actions" id="payment_actions" style="display: none;">
                        <button class="btn btn-primary" onclick="savePaymentBlock()">Сохранить</button>
                        <button class="btn btn-secondary" onclick="cancelPaymentEdit()">Отмена</button>
                    </div>
                </div>
            @endif
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

<style>
/* Стили для отображения HTML-контента из редактора */
.html-content {
    line-height: 1.6;
    color: #333;
}

.html-content h1,
.html-content h2,
.html-content h3,
.html-content h4,
.html-content h5,
.html-content h6 {
    margin: 1em 0 0.5em 0;
    color: #133E71;
    font-weight: 600;
}

.html-content h1 { font-size: 1.8em; }
.html-content h2 { font-size: 1.6em; }
.html-content h3 { font-size: 1.4em; }
.html-content h4 { font-size: 1.2em; }
.html-content h5 { font-size: 1.1em; }
.html-content h6 { font-size: 1em; }

.html-content p {
    margin: 0.5em 0;
}

.html-content ul,
.html-content ol {
    margin: 0.5em 0;
    padding-left: 2em;
}

.html-content li {
    margin: 0.25em 0;
}

.html-content blockquote {
    margin: 1em 0;
    padding: 0.5em 1em;
    border-left: 4px solid #133E71;
    background-color: #f8f9fa;
    font-style: italic;
}

.html-content table {
    width: 100%;
    border-collapse: collapse;
    margin: 1em 0;
}

.html-content th,
.html-content td {
    border: 1px solid #dee2e6;
    padding: 0.5em;
    text-align: left;
}

.html-content th {
    background-color: #f8f9fa;
    font-weight: 600;
}

.html-content a {
    color: #133E71;
    text-decoration: none;
}

.html-content a:hover {
    text-decoration: underline;
}

.html-content strong,
.html-content b {
    font-weight: 600;
}

.html-content em,
.html-content i {
    font-style: italic;
}

.html-content u {
    text-decoration: underline;
}

.html-content s,
.html-content strike {
    text-decoration: line-through;
}

.advertisement-item-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 20px;
}

.advertisement-header {
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

.advertisement-header-actions {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 10px;
}

.advertisement-title {
    font-size: 28px;
    color: #133E71;
    margin: 0;
    font-weight: 600;
}

.advertisement-actions {
    display: flex;
    gap: 10px;
}

.advertisement-actions .btn {
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

.advertisement-actions .btn-primary {
    background-color: #133E71;
    color: white;
    border-color: #133E71;
}

.advertisement-actions .btn-primary:hover {
    background-color: #0f2d56;
    border-color: #0f2d56;
    transform: translateY(-1px);
}

.advertisement-actions .btn svg {
    width: 16px;
    height: 16px;
}

.advertisement-status {
    margin-top: 10px;
}

.status-badge {
    display: inline-block;
    padding: 8px 16px;
    border-radius: 20px;
    font-weight: 600;
    text-align: center;
    width: fit-content;
}

.advertisement-content {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 40px;
    align-items: start;
}

.advertisement-actions-section {
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

.advertisement-media-section {
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

.advertisement-info-section {
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

.comment-section {
    margin-top: 15px;
}

.comment-header {
    margin-bottom: 10px;
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

.payment-variants {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
}

.payment-variant {
    background: #133E71;
    color: white;
    padding: 4px 8px;
    border-radius: 12px;
    font-size: 12px;
    font-weight: 500;
}

/* Стили для тегов */
.tags-container {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    margin-top: 10px;
}

.tag {
    background: #e8f0fe;
    color: #133E71;
    padding: 6px 12px;
    border-radius: 16px;
    font-size: 12px;
    font-weight: 500;
    border: 1px solid #d1e7ff;
    transition: all 0.3s ease;
}

.tag:hover {
    background: #d1e7ff;
    transform: translateY(-1px);
    box-shadow: 0 2px 4px rgba(19, 62, 113, 0.1);
}

.additional-info {
    background: #f8f9fa;
    padding: 15px;
    border-radius: 6px;
    border-left: 4px solid #133E71;
}

.additional-info p {
    margin: 0;
    color: #495057;
    line-height: 1.5;
}

.technical-chars {
    background: #f8f9fa;
    padding: 15px;
    border-radius: 6px;
    border-left: 4px solid #133E71;
}

.technical-chars p {
    margin: 0;
    color: #495057;
    line-height: 1.5;
}

/* Стили для редактирования */
.block-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
}

.block-header h3 {
    margin: 0;
    border-bottom: none;
    padding-bottom: 0;
}

.edit-comment-btn, .edit-payment-btn {
    display: flex;
    align-items: center;
    gap: 6px;
    background: none;
    border: 1px solid #133E71;
    color: #133E71;
    padding: 6px 12px;
    border-radius: 6px;
    font-size: 12px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.3s ease;
}

.edit-comment-btn:hover, .edit-payment-btn:hover {
    background-color: #133E71;
    color: white;
    transform: translateY(-1px);
}

.edit-comment-btn svg, .edit-payment-btn svg {
    width: 14px;
    height: 14px;
}

.comment-content, .payment-content {
    background: #f8f9fa;
    padding: 15px;
    border-radius: 6px;
    border-left: 4px solid #133E71;
}

.comment-edit, .payment-edit {
    margin-top: 15px;
}

.comment-textarea, .payment-edit textarea, .payment-edit input {
    width: 100%;
    padding: 12px;
    border: 1px solid #ddd;
    border-radius: 6px;
    font-size: 14px;
    line-height: 1.5;
    resize: vertical;
    font-family: inherit;
}

.comment-textarea:focus, .payment-edit textarea:focus, .payment-edit input:focus {
    outline: none;
    border-color: #133E71;
    box-shadow: 0 0 0 2px rgba(19, 62, 113, 0.1);
}

.comment-actions, .payment-actions {
    display: flex;
    gap: 10px;
    margin-top: 15px;
}

.btn-sm {
    padding: 6px 12px;
    font-size: 12px;
}

.no-comment, .no-value {
    color: #999;
    font-style: italic;
}

.payment-checkboxes {
    display: flex;
    flex-direction: column;
    gap: 8px;
    margin-top: 10px;
}

.payment-checkbox {
    display: flex;
    align-items: center;
    gap: 8px;
    cursor: pointer;
    font-size: 14px;
}

.payment-checkbox input[type="checkbox"] {
    width: 16px;
    height: 16px;
    cursor: pointer;
}

.form-control {
    width: 100%;
    padding: 12px;
    border: 1px solid #ddd;
    border-radius: 6px;
    font-size: 14px;
    line-height: 1.5;
    font-family: inherit;
}

.form-control:focus {
    outline: none;
    border-color: #133E71;
    box-shadow: 0 0 0 2px rgba(19, 62, 113, 0.1);
}

.check-container, .loading-container, .removal-container {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.check-status, .loading-status, .removal-status {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 10px;
    background: #f8f9fa;
    border-radius: 6px;
    border: 1px solid #e9ecef;
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

/* Стили для ссылок на товар и организацию */
.product-link, .company-link {
    color: #133E71 !important;
    text-decoration: none !important;
    font-weight: 600 !important;
    transition: all 0.3s ease !important;
    padding: 2px 4px !important;
    border-radius: 4px !important;
    display: inline-block !important;
}

.product-link:hover, .company-link:hover {
    color: #1C5BA4 !important;
    background-color: #e8f0fe !important;
    text-decoration: underline !important;
    transform: translateY(-1px) !important;
    box-shadow: 0 2px 4px rgba(19, 62, 113, 0.1) !important;
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
    .advertisement-content {
        grid-template-columns: 1fr;
        gap: 20px;
    }
    
    .advertisement-title {
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
    @if($advertisement->mediaOrdered->count() > 0)
        galleryItems = [
            @foreach($advertisement->mediaOrdered as $media)
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

// Глобальные переменные для редакторов
let editEditors = {};

// Функции для редактирования комментариев
function editComment(field) {
    const content = document.getElementById(field + '_content');
    const edit = document.getElementById(field + '_edit');
    const textarea = document.getElementById(field + '_textarea');
    
    content.style.display = 'none';
    edit.style.display = 'block';
    
    // Для полей с HTML-разметкой инициализируем Quill.js редактор
    if (field === 'technical_characteristics' || field === 'main_info' || field === 'additional_info') {
        initializeEditEditor(field);
    } else {
        textarea.focus();
        textarea.select();
    }
}

function cancelEdit(field) {
    const content = document.getElementById(field + '_content');
    const edit = document.getElementById(field + '_edit');
    const textarea = document.getElementById(field + '_textarea');
    
    // Восстанавливаем оригинальное значение
    const originalValue = textarea.getAttribute('data-original') || '';
    textarea.value = originalValue;
    
    // Для полей с HTML-разметкой уничтожаем редактор
    if (field === 'technical_characteristics' || field === 'main_info' || field === 'additional_info') {
        if (editEditors[field]) {
            const existingEditor = editEditors[field];
            if (existingEditor.container && existingEditor.container.parentNode) {
                existingEditor.container.parentNode.removeChild(existingEditor.container);
            }
            delete editEditors[field];
        }
    }
    
    content.style.display = 'block';
    edit.style.display = 'none';
}

function saveComment(field) {
    const textarea = document.getElementById(field + '_textarea');
    const content = document.getElementById(field + '_content');
    const edit = document.getElementById(field + '_edit');
    
    // Получаем значение из редактора или textarea
    let value;
    if (field === 'technical_characteristics' || field === 'main_info' || field === 'additional_info') {
        value = editEditors[field] ? editEditors[field].root.innerHTML.trim() : textarea.value.trim();
    } else {
        value = textarea.value.trim();
    }
    
    // Показываем индикатор загрузки
    const saveBtn = edit.querySelector('.btn-primary');
    const originalText = saveBtn.textContent;
    saveBtn.textContent = 'Сохранение...';
    saveBtn.disabled = true;
    
    // Отправляем AJAX запрос
    fetch(`/advertisements/{{ $advertisement->id }}/comment`, {
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
                // Для полей с HTML-разметкой используем специальное отображение
                if (field === 'technical_characteristics' || field === 'main_info' || field === 'additional_info') {
                    content.innerHTML = `<div class="html-content">${value}</div>`;
                } else {
                    content.innerHTML = `<p>${value}</p>`;
                }
            } else {
                content.innerHTML = '<p class="no-comment">' + getNoCommentText(field) + '</p>';
            }
            
            // Сохраняем новое значение как оригинальное
            textarea.setAttribute('data-original', value);
            
            // Скрываем форму редактирования
            content.style.display = 'block';
            edit.style.display = 'none';
            
            // Для полей с HTML-разметкой уничтожаем редактор
            if (field === 'technical_characteristics' || field === 'main_info' || field === 'additional_info') {
                if (editEditors[field]) {
                    const existingEditor = editEditors[field];
                    if (existingEditor.container && existingEditor.container.parentNode) {
                        existingEditor.container.parentNode.removeChild(existingEditor.container);
                    }
                    delete editEditors[field];
                }
            }
            
            // Показываем уведомление об успехе
            showNotification('Данные успешно обновлены', 'success');
        } else {
            throw new Error(data.message || 'Ошибка при сохранении');
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

function getNoCommentText(field) {
    const texts = {
        'technical_characteristics': 'Технические характеристики не указаны',
        'main_info': 'Основная информация не указана',
        'additional_info': 'Дополнительная информация не указана',
        'check_comment': 'Комментарий к проверке не указан',
        'loading_comment': 'Комментарий по погрузке не указан',
        'removal_comment': 'Комментарий по демонтажу не указан'
    };
    return texts[field] || 'Не указано';
}

// Функции для редактирования блока оплаты
function editPaymentBlock() {
    // Скрываем все контенты и показываем формы редактирования
    const fields = ['payment_method', 'purchase_price', 'adv_price', 'adv_price_comment', 'payment_comment'];
    
    fields.forEach(field => {
        const content = document.getElementById(field + '_content');
        const edit = document.getElementById(field + '_edit');
        
        if (content && edit) {
            content.style.display = 'none';
            edit.style.display = 'block';
        }
    });
    
    // Показываем общие кнопки действий
    document.getElementById('payment_actions').style.display = 'flex';
    
    // Скрываем кнопку редактирования
    document.querySelector('.edit-payment-btn').style.display = 'none';
}

function cancelPaymentEdit() {
    // Восстанавливаем все оригинальные значения
    const fields = ['payment_method', 'purchase_price', 'adv_price', 'adv_price_comment', 'payment_comment'];
    
    fields.forEach(field => {
        const content = document.getElementById(field + '_content');
        const edit = document.getElementById(field + '_edit');
        
        if (content && edit) {
            if (field === 'payment_method') {
                // Восстанавливаем состояние чекбоксов
                const paymentCheckboxes = document.querySelectorAll('input[name="payment_types[]"]');
                paymentCheckboxes.forEach(checkbox => {
                    const originalState = checkbox.getAttribute('data-original') === 'true';
                    checkbox.checked = originalState;
                });
            } else if (field === 'purchase_price' || field === 'adv_price') {
                const input = document.getElementById(field + '_input');
                if (input) {
                    const originalValue = input.getAttribute('data-original') || '';
                    input.value = originalValue;
                }
            } else if (field === 'payment_comment' || field === 'adv_price_comment') {
                const textarea = document.getElementById(field + '_textarea');
                if (textarea) {
                    const originalValue = textarea.getAttribute('data-original') || '';
                    textarea.value = originalValue;
                }
            }
            
            content.style.display = 'block';
            edit.style.display = 'none';
        }
    });
    
    // Скрываем общие кнопки действий
    document.getElementById('payment_actions').style.display = 'none';
    
    // Показываем кнопку редактирования
    document.querySelector('.edit-payment-btn').style.display = 'flex';
}

function savePaymentBlock() {
    // Собираем данные для отправки
    const paymentData = {
        payment_types: [],
        purchase_price: null,
        adv_price: null,
        adv_price_comment: null,
        payment_comment: null
    };
    
    // Получаем выбранные варианты оплаты
    const paymentCheckboxes = document.querySelectorAll('input[name="payment_types[]"]:checked');
    paymentCheckboxes.forEach(checkbox => {
        paymentData.payment_types.push(parseInt(checkbox.value));
    });
    
    // Получаем закупочную цену
    const purchasePriceInput = document.getElementById('purchase_price_input');
    if (purchasePriceInput && purchasePriceInput.value) {
        paymentData.purchase_price = parseFloat(purchasePriceInput.value);
    }
    
    // Получаем цену продажи
    const advPriceInput = document.getElementById('adv_price_input');
    if (advPriceInput && advPriceInput.value) {
        paymentData.adv_price = parseFloat(advPriceInput.value);
    }
    
    // Получаем комментарий к продаже
    const advPriceCommentTextarea = document.getElementById('adv_price_comment_textarea');
    if (advPriceCommentTextarea) {
        paymentData.adv_price_comment = advPriceCommentTextarea.value.trim();
    }
    
    // Получаем комментарий по оплате
    const paymentCommentTextarea = document.getElementById('payment_comment_textarea');
    if (paymentCommentTextarea) {
        paymentData.payment_comment = paymentCommentTextarea.value.trim();
    }
    
    // Показываем индикатор загрузки
    const saveBtn = document.querySelector('#payment_actions .btn-primary');
    const originalText = saveBtn.textContent;
    saveBtn.textContent = 'Сохранение...';
    saveBtn.disabled = true;
    
    // Отправляем AJAX запрос
    fetch(`/advertisements/{{ $advertisement->id }}/payment-info`, {
        method: 'PATCH',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify(paymentData)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Обновляем отображение
            updatePaymentDisplay(paymentData);
            
            // Сохраняем новые значения как оригинальные
            savePaymentOriginals(paymentData);
            
            // Скрываем формы редактирования
            cancelPaymentEdit();
            
            // Показываем уведомление об успехе
            showNotification('Информация об оплате успешно обновлена', 'success');
        } else {
            throw new Error(data.message || 'Ошибка при сохранении');
        }
    })
    .catch(error => {
        console.error('Ошибка:', error);
        showNotification('Ошибка при сохранении информации об оплате', 'error');
    })
    .finally(() => {
        // Восстанавливаем кнопку
        saveBtn.textContent = originalText;
        saveBtn.disabled = false;
    });
}

function updatePaymentDisplay(paymentData) {
    // Обновляем отображение вариантов оплаты
    const paymentContent = document.getElementById('payment_method_content');
    if (paymentData.payment_types.length > 0) {
        const variantNames = paymentData.payment_types.map(typeId => {
            const checkbox = document.querySelector(`input[name="payment_types[]"][value="${typeId}"]`);
            return checkbox ? checkbox.parentElement.textContent.trim() : '';
        }).filter(name => name);
        
        paymentContent.innerHTML = `
            <div class="payment-variants">
                ${variantNames.map(name => `<span class="payment-variant">${name}</span>`).join('')}
            </div>
        `;
    } else {
        paymentContent.innerHTML = '<span class="no-value">Не указаны</span>';
    }
    
    // Обновляем отображение закупочной цены
    const priceContent = document.getElementById('purchase_price_content');
    if (paymentData.purchase_price) {
        priceContent.innerHTML = `<span class="price">${paymentData.purchase_price.toLocaleString('ru-RU')} ₽</span>`;
    } else {
        priceContent.innerHTML = '<span class="no-value">Не указана</span>';
    }
    
    // Обновляем отображение цены продажи
    const advPriceContent = document.getElementById('adv_price_content');
    if (paymentData.adv_price) {
        advPriceContent.innerHTML = `<span class="price">${paymentData.adv_price.toLocaleString('ru-RU')} ₽</span>`;
    } else {
        advPriceContent.innerHTML = '<span class="no-value">Не указана</span>';
    }
    
    // Обновляем отображение комментария к продаже
    const advPriceCommentContent = document.getElementById('adv_price_comment_content');
    if (paymentData.adv_price_comment) {
        advPriceCommentContent.innerHTML = `<p>${paymentData.adv_price_comment}</p>`;
    } else {
        advPriceCommentContent.innerHTML = '<span class="no-value">Не указан</span>';
    }
    
    // Обновляем отображение комментария по оплате
    const commentContent = document.getElementById('payment_comment_content');
    if (paymentData.payment_comment) {
        commentContent.innerHTML = `<p>${paymentData.payment_comment}</p>`;
    } else {
        commentContent.innerHTML = '<span class="no-value">Не указан</span>';
    }
}

function savePaymentOriginals(paymentData) {
    // Сохраняем новые значения как оригинальные
    const paymentCheckboxes = document.querySelectorAll('input[name="payment_types[]"]');
    paymentCheckboxes.forEach(checkbox => {
        const isChecked = paymentData.payment_types.includes(parseInt(checkbox.value));
        checkbox.setAttribute('data-original', isChecked.toString());
    });
    
    const purchasePriceInput = document.getElementById('purchase_price_input');
    if (purchasePriceInput) {
        purchasePriceInput.setAttribute('data-original', paymentData.purchase_price || '');
    }
    
    const advPriceInput = document.getElementById('adv_price_input');
    if (advPriceInput) {
        advPriceInput.setAttribute('data-original', paymentData.adv_price || '');
    }
    
    const advPriceCommentTextarea = document.getElementById('adv_price_comment_textarea');
    if (advPriceCommentTextarea) {
        advPriceCommentTextarea.setAttribute('data-original', paymentData.adv_price_comment || '');
    }
    
    const paymentCommentTextarea = document.getElementById('payment_comment_textarea');
    if (paymentCommentTextarea) {
        paymentCommentTextarea.setAttribute('data-original', paymentData.payment_comment || '');
    }
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
        z-index: 10000;
        animation: slideIn 0.3s ease;
        max-width: 300px;
    `;
    
    // Устанавливаем цвет в зависимости от типа
    if (type === 'success') {
        notification.style.backgroundColor = '#28a745';
    } else if (type === 'error') {
        notification.style.backgroundColor = '#dc3545';
    } else {
        notification.style.backgroundColor = '#133E71';
    }
    
    // Добавляем в DOM
    document.body.appendChild(notification);
    
    // Удаляем через 3 секунды
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

// Функция инициализации Quill.js для редактирования
function initializeEditEditor(field) {
    // Если редактор уже существует, уничтожаем его
    if (editEditors[field]) {
        const existingEditor = editEditors[field];
        if (existingEditor.container && existingEditor.container.parentNode) {
            existingEditor.container.parentNode.removeChild(existingEditor.container);
        }
        delete editEditors[field];
    }
    
    const editorElement = document.getElementById(field + '_edit_editor');
    const textarea = document.getElementById(field + '_textarea');
    
    if (!editorElement) return;
    
    // Конфигурация редактора
    const toolbarOptions = [
        [{ 'header': [1, 2, 3, false] }],
        ['bold', 'italic', 'underline', 'strike'],
        [{ 'list': 'ordered'}, { 'list': 'bullet' }],
        [{ 'indent': '-1'}, { 'indent': '+1' }],
        ['link', 'blockquote'],
        ['clean']
    ];

    // Инициализация редактора
    editEditors[field] = new Quill(editorElement, {
        theme: 'snow',
        modules: {
            toolbar: toolbarOptions
        },
        placeholder: 'Введите текст...'
    });
    
    // Устанавливаем начальное значение
    const initialValue = textarea.value;
    if (initialValue) {
        editEditors[field].root.innerHTML = initialValue;
    }
    
    // Синхронизируем изменения обратно в скрытое поле
    editEditors[field].on('text-change', function() {
        textarea.value = editEditors[field].root.innerHTML;
    });
}
</script>

@endsection 