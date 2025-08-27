@extends('layouts.layout')
@section('title', $product->name ?? 'Товар')

@section('header-title')
    <h1 class="header-title">{{ $product->name }}</h1>
@endsection

@section('content')
<div class="product-item-container">
    @if(session('success'))
        <div style="margin-bottom: 20px; padding: 15px; background-color: #d4edda; border: 1px solid #c3e6cb; border-radius: 8px; color: #155724;">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin-right: 8px; vertical-align: middle;">
                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                <polyline points="22,4 12,14.01 9,11.01"></polyline>
            </svg>
            {{ session('success') }}
        </div>
    @endif
    <div class="product-header">
        <div class="breadcrumb">
            <a href="{{ route('products.index') }}">Товары</a> / {{ $product->name }}
        </div>
        <div class="product-header-actions">
            <h1 class="product-title">{{ $product->name }}</h1>
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

            <!-- Кнопка скачивания медиафайлов -->
            @if($product->mediaOrdered->count() > 0)
                <div class="download-media-section">
                    <button id="downloadMediaBtn" class="btn btn-primary download-media-btn" onclick="downloadAllMedia()">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin-right: 8px;">
                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                            <polyline points="7,10 12,15 17,10"></polyline>
                            <line x1="12" y1="15" x2="12" y2="3"></line>
                        </svg>
                        <span class="btn-text">Скачать все медиа</span>
                        <div class="loading-spinner" style="display: none;">
                            <div class="spinner"></div>
                        </div>
                    </button>
                </div>
            @endif

            <!-- Блок действий и событий -->
            @if($canEdit)
                <div class="product-actions-section">
                    <div class="info-block">
                        <h3>Следующие действия</h3>
                        <div class="action-info">
                            @if($lastAction)
                                <div class="action-date">
                                    <span class="label">Дата:</span>
                                    <span class="value">{{ $lastAction->expired_at->format('d.m.Y') }}</span>
                                </div>
                                <div class="action-description">
                                    <span class="label">Что требуется сделать:</span>
                                    <p>{{ $lastAction->action }}</p>
                                </div>
                                <div class="action-buttons">
                                    @if($canChangeStatus)
                                        <button class="btn btn-primary" onclick="showNewActionModal()">Задать новое действие</button>
                                    @endif
                                    <button class="btn btn-secondary" onclick="showActionsModal()">Подробнее</button>
                                </div>
                            @else
                                <div class="action-description">
                                    <p style="color: #666; font-style: italic;">Нет активных действий</p>
                                </div>
                                <div class="action-buttons">
                                    @if($canChangeStatus)
                                        <button class="btn btn-primary" onclick="showNewActionModal()">Задать новое действие</button>
                                    @endif
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="info-block">
                        <h3>Лог событий</h3>
                        <div class="events-list">
                            @if($lastLog)
                                <div class="event-item">
                                    <div class="event-header">
                                        <span class="event-type" data-color="{{ $lastLog->type ? $lastLog->type->color : '#133E71' }}">{{ $lastLog->type ? $lastLog->type->name : 'Неизвестный тип' }}</span>
                                        <span class="event-date">{{ $lastLog->created_at->format('d.m.Y H:i:s') }}</span>
                                    </div>
                                    <div class="event-content">
                                        <p>{{ $lastLog->log }}</p>
                                    </div>
                                    <div class="event-footer">
                                        <span>Создал: {{ $lastLog->user_id ? ($lastLog->user ? $lastLog->user->name : 'Пользователь не найден') : 'Система' }}</span>
                                    </div>
                                </div>
                            @else
                                <div class="event-item">
                                    <div class="event-content">
                                        <p style="color: #666; font-style: italic;">Логи событий отсутствуют</p>
                                    </div>
                                </div>
                            @endif
                        </div>
                        <div class="events-actions">
                            <button class="btn btn-secondary" onclick="showLogsHistory()">История</button>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        <!-- Основная информация о товаре -->
        <div class="product-info-section">
            <div class="info-block">
                <h3>Статус товара</h3>
                <div class="status-container">
                    @if($canChangeStatus)
                        <div class="status-selector">
                            <div class="status-badge clickable" onclick="toggleProductStatusDropdown()" style="background-color: {{ $product->status->color ?? '#6c757d' }}; color: white;">
                                {{ $product->status->name ?? 'Не указан' }}
                                <svg class="dropdown-arrow" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <polyline points="6,9 12,15 18,9"></polyline>
                                </svg>
                            </div>
                            <div class="status-dropdown" id="productStatusDropdown">
                                @foreach($statuses as $status)
                                    <div class="status-option" onclick="changeProductStatus({{ $status->id }}, '{{ $status->name }}', '{{ $status->color }}')">
                                        <div class="status-badge" style="background-color: {{ $status->color }}; color: white;">{{ $status->name }}</div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @else
                        <div class="status-badge" style="background-color: {{ $product->status->color ?? '#6c757d' }}; color: white;">
                            {{ $product->status->name ?? 'Не указан' }}
                        </div>
                    @endif
                </div>
            </div>

            @if($product->common_commentary_after)
                <div class="info-block">
                    <h3>Общий комментарий после осмотра</h3>
                    <div class="comment-section" data-field="common_commentary_after">
                        <div class="comment-header">
                            <strong>Комментарий:</strong>
                            <button class="edit-comment-btn" onclick="editComment('common_commentary_after')">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"></path>
                                </svg>
                                Редактировать
                            </button>
                        </div>
                        <div class="comment-content" id="common_commentary_after_content">
                            <p>{{ $product->common_commentary_after }}</p>
                        </div>
                        <div class="comment-edit" id="common_commentary_after_edit" style="display: none;">
                            <textarea class="comment-textarea" id="common_commentary_after_textarea" rows="6">{{ $product->common_commentary_after }}</textarea>
                            <div class="comment-actions">
                                <button class="btn btn-primary btn-sm" onclick="saveComment('common_commentary_after')">Сохранить</button>
                                <button class="btn btn-secondary btn-sm" onclick="cancelEdit('common_commentary_after')">Отмена</button>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <div class="info-block">
                <div class="block-header">
                    <h3>Основная информация</h3>
                    <button class="edit-comment-btn" onclick="editMainInfoBlock()">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"></path>
                        </svg>
                        Редактировать
                    </button>
                </div>
                <div class="main-info-container">
                    <div class="main-info-content" id="main_info_content">
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
                                <span class="label">Адрес станка:</span>
                                <span class="value">{{ $product->product_address ?? 'Не указан' }}</span>
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
                                        <button class="contact-link" onclick="showContactCard({{ $product->owner->id }}, '{{ $product->owner->name }}', '{{ $product->owner->email }}', '{{ $product->owner->phone }}', '{{ $product->owner->role->name ?? 'Роль не указана' }}', {{ $product->owner->has_telegram ? 'true' : 'false' }}, {{ $product->regional->has_whatsapp ? 'true' : 'false' }})">
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
                        </div>
                    </div>
                    <div class="main-info-edit" id="main_info_edit" style="display: none;">
                        <div class="form-group">
                            <label for="category_treeselect">Категория:</label>
                            <div class="treeselect-wrapper">
                                <div class="treeselect-input" id="category_treeselect" tabindex="0">
                                    <span class="treeselect-placeholder">Выберите категорию</span>
                                    <svg class="treeselect-arrow" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <polyline points="6,9 12,15 18,9"></polyline>
                                    </svg>
                                </div>
                                <div class="treeselect-dropdown" id="category_treeselect_dropdown">
                                    <div class="treeselect-search">
                                        <input type="text" id="category_treeselect_search" placeholder="Поиск категорий..." class="treeselect-search-input">
                                    </div>
                                    <div class="treeselect-tree" id="category_treeselect_tree">
                                        <!-- Дерево будет заполнено JavaScript -->
                                    </div>
                                </div>
                                <select name="category_id" id="category_id" class="form-control" style="display: none;">
                                    <option value="">Выберите категорию</option>
                                    @foreach(\App\Models\ProductCategories::all() as $category)
                                        <option value="{{ $category->id }}" {{ $product->category_id == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <small class="form-text text-muted">Доступны только категории без подкатегорий</small>
                        </div>
                        <div class="form-group">
                            <label for="product_address_input">Адрес станка:</label>
                            <input type="text" id="product_address_input" class="form-control" value="{{ $product->product_address ?? '' }}" placeholder="Введите адрес станка">
                        </div>
                    </div>
                    <!-- Кнопки действий для блока основной информации -->
                    <div class="main-info-actions" id="main_info_actions" style="display: none;">
                        <button class="btn btn-primary" onclick="saveMainInfoBlock()">Сохранить</button>
                        <button class="btn btn-secondary" onclick="cancelMainInfoEdit()">Отмена</button>
                    </div>
                </div>
            </div>

            <div class="info-block">
                <div class="block-header">
                    <h3>Характеристики</h3>
                    <button class="edit-comment-btn" onclick="editCharacteristicsBlock()">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"></path>
                        </svg>
                        Редактировать
                    </button>
                </div>
                <div class="characteristics-container">
                    <div class="characteristics-content" id="characteristics_content">
                        @if($product->main_chars || $product->complectation || $product->mark)
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
                            @if($product->mark)
                                <div class="chars-item">
                                    <strong>Оценка:</strong>
                                    <p>{{ $product->mark }}</p>
                                </div>
                            @endif
                        @else
                            <p class="no-comment">Характеристики не указаны</p>
                        @endif
                    </div>
                    <div class="characteristics-edit" id="characteristics_edit" style="display: none;">
                        <div class="form-group">
                            <label for="main_chars_textarea">Основные характеристики:</label>
                            <textarea class="comment-textarea" id="main_chars_textarea" rows="3" data-original="{{ $product->main_chars ?? '' }}">{{ $product->main_chars ?? '' }}</textarea>
                        </div>
                        <div class="form-group">
                            <label for="complectation_textarea">Комплектация:</label>
                            <textarea class="comment-textarea" id="complectation_textarea" rows="3" data-original="{{ $product->complectation ?? '' }}">{{ $product->complectation ?? '' }}</textarea>
                        </div>
                        <div class="form-group">
                            <label for="mark_textarea">Оценка:</label>
                            <textarea class="comment-textarea" id="mark_textarea" rows="3" data-original="{{ $product->mark ?? '' }}">{{ $product->mark ?? '' }}</textarea>
                        </div>
                    </div>
                    <!-- Кнопки действий для блока характеристик -->
                    <div class="characteristics-actions" id="characteristics_actions" style="display: none;">
                        <button class="btn btn-primary" onclick="saveCharacteristicsBlock()">Сохранить</button>
                        <button class="btn btn-secondary" onclick="cancelCharacteristicsEdit()">Отмена</button>
                    </div>
                </div>
            </div>

            <div class="info-block">
                <div class="block-header">
                    <h3>Информация о проверке</h3>
                    <button class="edit-comment-btn" onclick="editCheckBlock()">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"></path>
                        </svg>
                        Редактировать
                    </button>
                </div>
                @php
                    $checkStatus = null;
                    $check = $product->check->first();
                    if ($check && $check->checkStatus) {
                        $checkStatus = $check->checkStatus;
                    }
                @endphp
                <div class="check-container">
                    <div class="check-status">
                        <strong>Статус проверки:</strong>
                        <div class="status-content" id="check_status_content">
                            <span class="status-badge" style="background-color: {{ $checkStatus->color ?? '#6c757d' }}; color: white;">
                                {{ $checkStatus->name ?? 'Не указан' }}
                            </span>
                        </div>
                        <div class="status-edit" id="check_status_edit" style="display: none;">
                            <select class="form-control" id="check_status_select" data-original="{{ $check->check_status_id ?? '' }}">
                                <option value="">Выберите статус</option>
                                @foreach($checkStatuses as $status)
                                    <option value="{{ $status->id }}" 
                                            {{ ($check && $check->check_status_id == $status->id) ? 'selected' : '' }}
                                            data-color="{{ $status->color }}">
                                        {{ $status->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="comment-section" data-field="check_comment">
                        <div class="comment-header">
                            <strong>Комментарий к проверке:</strong>
                        </div>
                        <div class="comment-content" id="check_comment_content">
                            @if($check && $check->comment)
                                <p>{{ $check->comment }}</p>
                            @else
                                <p class="no-comment">Комментарий к проверке не указан</p>
                            @endif
                        </div>
                        <div class="comment-edit" id="check_comment_edit" style="display: none;">
                            <textarea class="comment-textarea" id="check_comment_textarea" rows="3" data-original="{{ $check->comment ?? '' }}">{{ $check->comment ?? '' }}</textarea>
                        </div>
                    </div>
                    <!-- Кнопки действий для блока проверки -->
                    <div class="check-actions" id="check_actions" style="display: none;">
                        <button class="btn btn-primary" onclick="saveCheckBlock()">Сохранить</button>
                        <button class="btn btn-secondary" onclick="cancelCheckEdit()">Отмена</button>
                    </div>
                </div>
            </div>

            <div class="info-block">
                <div class="block-header">
                    <h3>Информация о погрузке</h3>
                    <button class="edit-comment-btn" onclick="editLoadingBlock()">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"></path>
                        </svg>
                        Редактировать
                    </button>
                </div>
                @php
                    $loadingStatus = null;
                    $loading = $product->loading->first();
                    if ($loading && $loading->installStatus) {
                        $loadingStatus = $loading->installStatus;
                    }
                @endphp
                <div class="loading-container">
                    <div class="loading-status">
                        <strong>Статус погрузки:</strong>
                        <div class="status-content" id="loading_status_content">
                            <span class="status-badge">
                                {{ $loadingStatus->name ?? 'Не указан' }}
                            </span>
                        </div>
                        <div class="status-edit" id="loading_status_edit" style="display: none;">
                            <select class="form-control" id="loading_status_select" data-original="{{ $loading->install_status_id ?? '' }}">
                                <option value="">Выберите статус</option>
                                @foreach($installStatuses as $status)
                                    <option value="{{ $status->id }}" 
                                            {{ ($loading && $loading->install_status_id == $status->id) ? 'selected' : '' }}>
                                        {{ $status->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="comment-section" data-field="loading_comment">
                        <div class="comment-header">
                            <strong>Комментарий по погрузке:</strong>
                        </div>
                        <div class="comment-content" id="loading_comment_content">
                            @if($loading && $loading->comment)
                                <p>{{ $loading->comment }}</p>
                            @else
                                <p class="no-comment">Комментарий по погрузке не указан</p>
                            @endif
                        </div>
                        <div class="comment-edit" id="loading_comment_edit" style="display: none;">
                            <textarea class="comment-textarea" id="loading_comment_textarea" rows="3" data-original="{{ $loading->comment ?? '' }}">{{ $loading->comment ?? '' }}</textarea>
                        </div>
                    </div>
                    <!-- Кнопки действий для блока погрузки -->
                    <div class="loading-actions" id="loading_actions" style="display: none;">
                        <button class="btn btn-primary" onclick="saveLoadingBlock()">Сохранить</button>
                        <button class="btn btn-secondary" onclick="cancelLoadingEdit()">Отмена</button>
                    </div>
                </div>
            </div>

            <div class="info-block">
                <div class="block-header">
                    <h3>Информация о демонтаже</h3>
                    <button class="edit-comment-btn" onclick="editRemovalBlock()">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"></path>
                        </svg>
                        Редактировать
                    </button>
                </div>
                @php
                    $removalStatus = null;
                    $removal = $product->removal->first();
                    if ($removal && $removal->installStatus) {
                        $removalStatus = $removal->installStatus;
                    }
                @endphp
                <div class="removal-container">
                    <div class="removal-status">
                        <strong>Статус демонтажа:</strong>
                        <div class="status-content" id="removal_status_content">
                            <span class="status-badge">
                                {{ $removalStatus->name ?? 'Не указан' }}
                            </span>
                        </div>
                        <div class="status-edit" id="removal_status_edit" style="display: none;">
                            <select class="form-control" id="removal_status_select" data-original="{{ $removal->install_status_id ?? '' }}">
                                <option value="">Выберите статус</option>
                                @foreach($installStatuses as $status)
                                    <option value="{{ $status->id }}" 
                                            {{ ($removal && $removal->install_status_id == $status->id) ? 'selected' : '' }}>
                                        {{ $status->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="comment-section" data-field="removal_comment">
                        <div class="comment-header">
                            <strong>Комментарий по демонтажу:</strong>
                        </div>
                        <div class="comment-content" id="removal_comment_content">
                            @if($removal && $removal->comment)
                                <p>{{ $removal->comment }}</p>
                            @else
                                <p class="no-comment">Комментарий по демонтажу не указан</p>
                            @endif
                        </div>
                        <div class="comment-edit" id="removal_comment_edit" style="display: none;">
                            <textarea class="comment-textarea" id="removal_comment_textarea" rows="3" data-original="{{ $removal->comment ?? '' }}">{{ $removal->comment ?? '' }}</textarea>
                        </div>
                    </div>
                    <!-- Кнопки действий для блока демонтажа -->
                    <div class="removal-actions" id="removal_actions" style="display: none;">
                        <button class="btn btn-primary" onclick="saveRemovalBlock()">Сохранить</button>
                        <button class="btn btn-secondary" onclick="cancelRemovalEdit()">Отмена</button>
                    </div>
                </div>
            </div>

            <div class="info-block">
                <div class="block-header">
                    <h3>Информация о покупке</h3>
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
                        @if($product->paymentVariants->count() > 0)
                            <div class="payment-variants">
                                @foreach($product->paymentVariants as $variant)
                                    <span class="payment-variant">{{ $variant->priceType->name }}</span>
                                @endforeach
                            </div>
                        @else
                            <span class="no-value">Не указаны</span>
                        @endif
                    </div>
                    <div class="payment-edit" id="payment_method_edit" style="display: none;">
                        @php
                            $priceTypes = \App\Models\ProductPriceType::all();
                            $selectedTypes = $product->paymentVariants->pluck('price_type')->toArray();
                        @endphp
                        <div class="payment-checkboxes">
                            @foreach($priceTypes as $priceType)
                                <label class="payment-checkbox">
                                    <input type="checkbox" name="payment_types[]" value="{{ $priceType->id }}" 
                                           {{ in_array($priceType->id, $selectedTypes) ? 'checked' : '' }}>
                                    <span>{{ $priceType->name }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                </div>

                <!-- Основной способ оплаты -->
                <div class="payment-item">
                    <strong>Основной способ оплаты:</strong>
                    <div class="payment-content" id="main_payment_method_content">
                        @if($product->main_payment_method)
                            <span>{{ $product->mainPaymentMethod->name ?? $product->main_payment_method }}</span>
                        @else
                            <span class="no-value">Не указан</span>
                        @endif
                    </div>
                    <div class="payment-edit" id="main_payment_method_edit" style="display: none;">
                        @php
                            $priceTypes = \App\Models\ProductPriceType::all();
                        @endphp
                        <select name="main_payment_method" id="main_payment_method_select" class="payment-select">
                            <option value="">Выберите способ оплаты</option>
                            @foreach($priceTypes as $priceType)
                                <option value="{{ $priceType->id }}" {{ $product->main_payment_method == $priceType->id ? 'selected' : '' }}>
                                    {{ $priceType->name }}
                                </option>
                            @endforeach
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
    align-items: center;
    gap: 15px;
}

.status-selector {
    position: relative;
    display: inline-block;
}

.status-badge.clickable {
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 5px;
    transition: all 0.3s ease;
}

.status-badge.clickable:hover {
    transform: translateY(-1px);
    box-shadow: 0 2px 8px rgba(0,0,0,0.15);
}

.dropdown-arrow {
    transition: transform 0.3s ease;
}

.status-dropdown {
    position: absolute;
    top: 100%;
    left: 0;
    right: 0;
    background: white;
    border: 1px solid #e9ecef;
    border-radius: 8px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    z-index: 1000;
    display: none;
    min-width: 150px;
    margin-top: 5px;
}

.status-dropdown.show {
    display: block;
    animation: fadeInDown 0.3s ease;
}

.status-option {
    padding: 8px 12px;
    cursor: pointer;
    transition: background-color 0.2s ease;
}

.status-option:hover {
    background-color: #f8f9fa;
}

.status-option:first-child {
    border-radius: 8px 8px 0 0;
}

.status-option:last-child {
    border-radius: 0 0 8px 8px;
}

@keyframes fadeInDown {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.status-badge {
    display: inline-block;
    padding: 8px 16px;
    border-radius: 20px;
    font-weight: 600;
    text-align: center;
    width: fit-content;
}



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
    margin-top: 10px;
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
    padding: 8px;
    border-radius: 4px;
    transition: background-color 0.3s ease;
}

.payment-checkbox:hover {
    background-color: #f8f9fa;
}

.payment-checkbox input[type="checkbox"] {
    margin: 0;
    cursor: pointer;
}

.payment-checkbox span {
    font-size: 14px;
    color: #495057;
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

/* Стили для новых элементов */
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
    margin-bottom: 15px;
}

.check-status strong, .loading-status strong, .removal-status strong {
    min-width: 120px;
    flex-shrink: 0;
}

/* Стили для редактирования статусов */
.status-content, .status-edit {
    display: inline-block;
}

.status-edit {
    margin-top: 10px;
}

.status-edit select {
    min-width: 200px;
}

.check-actions, .loading-actions, .removal-actions {
    display: flex;
    gap: 10px;
    margin-top: 15px;
    justify-content: flex-end;
}

/* Стили для заголовка блока */
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

.edit-comment-btn {
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

.edit-comment-btn:hover {
    background-color: #133E71;
    color: white;
    transform: translateY(-1px);
}

.edit-comment-btn svg {
    width: 14px;
    height: 14px;
}

/* Стили для комментариев */
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

.comment-edit {
    margin-top: 15px;
}

.comment-textarea {
    width: 100%;
    padding: 12px;
    border: 1px solid #ddd;
    border-radius: 6px;
    font-size: 14px;
    line-height: 1.5;
    resize: vertical;
    font-family: inherit;
}

.comment-textarea:focus {
    outline: none;
    border-color: #133E71;
    box-shadow: 0 0 0 2px rgba(19, 62, 113, 0.1);
}

.no-comment {
    color: #999;
    font-style: italic;
}

/* Стили для кнопок */
.btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 8px 16px;
    border-radius: 6px;
    text-decoration: none;
    font-size: 14px;
    font-weight: 500;
    transition: all 0.3s ease;
    border: 1px solid;
    cursor: pointer;
}

.btn-primary {
    background-color: #133E71;
    color: white;
    border-color: #133E71;
}

.btn-primary:hover {
    background-color: #0f2d56;
    border-color: #0f2d56;
    transform: translateY(-1px);
}

.btn-secondary {
    background-color: #6c757d;
    color: white;
    border-color: #6c757d;
}

.btn-secondary:hover {
    background-color: #5a6268;
    border-color: #5a6268;
    transform: translateY(-1px);
}

.btn-sm {
    padding: 6px 12px;
    font-size: 12px;
}

/* Стили для форм */
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

.no-data {
    color: #6c757d;
    font-style: italic;
    text-align: center;
    padding: 20px;
    background: #f8f9fa;
    border-radius: 6px;
    border: 1px solid #e9ecef;
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

/* Стили для блока характеристик */
.characteristics-container {
    margin-top: 15px;
}

.characteristics-content {
    margin-bottom: 15px;
}

.characteristics-edit {
    margin-bottom: 15px;
}

.characteristics-edit .form-group {
    margin-bottom: 15px;
}

.characteristics-edit .form-group label {
    display: block;
    font-weight: 600;
    color: #333;
    margin-bottom: 5px;
    font-size: 14px;
}

.characteristics-edit .form-group textarea {
    width: 100%;
    padding: 10px;
    border: 1px solid #ddd;
    border-radius: 6px;
    font-size: 14px;
    font-family: inherit;
    resize: vertical;
    min-height: 80px;
    transition: border-color 0.3s ease;
}

.characteristics-edit .form-group textarea:focus {
    outline: none;
    border-color: #133E71;
    box-shadow: 0 0 0 2px rgba(19, 62, 113, 0.1);
}

.characteristics-actions {
    display: flex;
    gap: 10px;
    justify-content: flex-start;
}

.characteristics-actions .btn {
    padding: 8px 16px;
    border-radius: 6px;
    font-size: 14px;
    font-weight: 500;
    border: 1px solid;
    cursor: pointer;
    transition: all 0.3s ease;
}

.characteristics-actions .btn-primary {
    background-color: #133E71;
    color: white;
    border-color: #133E71;
}

.characteristics-actions .btn-primary:hover {
    background-color: #0f2d56;
    border-color: #0f2d56;
}

.characteristics-actions .btn-secondary {
    background-color: #6c757d;
    color: white;
    border-color: #6c757d;
}

.characteristics-actions .btn-secondary:hover {
    background-color: #5a6268;
    border-color: #5a6268;
}

    .characteristics-actions .btn:disabled {
        opacity: 0.6;
        cursor: not-allowed;
    }

    /* Стили для модальных окон */
    .modal {
        display: none;
        position: fixed;
        z-index: 10000;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.5);
    }

    .modal-content {
        background-color: #fefefe;
        margin: 5% auto;
        padding: 0;
        border-radius: 8px;
        width: 90%;
        max-width: 500px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
    }

    .modal-header {
        padding: 20px 20px 0 20px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .modal-header h3 {
        margin: 0;
        color: #133E71;
        font-size: 18px;
        font-weight: 600;
    }

    .close {
        color: #aaa;
        font-size: 28px;
        font-weight: bold;
        cursor: pointer;
        line-height: 1;
    }

    .close:hover,
    .close:focus {
        color: #000;
    }

    .modal-body {
        padding: 20px;
    }

    .modal-body p {
        margin: 0 0 15px 0;
        color: #666;
    }

    .form-group {
        margin-bottom: 15px;
    }

    .form-group label {
        display: block;
        margin-bottom: 5px;
        font-weight: 500;
        color: #333;
    }

    .form-group textarea {
        width: 100%;
        padding: 10px;
        border: 1px solid #ddd;
        border-radius: 4px;
        font-size: 14px;
        resize: vertical;
        min-height: 80px;
    }

    .form-group textarea:focus {
        outline: none;
        border-color: #133E71;
        box-shadow: 0 0 0 2px rgba(19, 62, 113, 0.1);
    }

    .form-group input[type="date"] {
        width: 100%;
        padding: 10px;
        border: 1px solid #ddd;
        border-radius: 4px;
        font-size: 14px;
        font-family: inherit;
    }

    .form-group input[type="date"]:focus {
        outline: none;
        border-color: #133E71;
        box-shadow: 0 0 0 2px rgba(19, 62, 113, 0.1);
    }

    .modal-footer {
        padding: 0 20px 20px 20px;
        display: flex;
        justify-content: flex-end;
        gap: 10px;
    }

    .modal-footer .btn {
        padding: 8px 16px;
        border-radius: 4px;
        font-size: 14px;
        font-weight: 500;
        cursor: pointer;
        border: 1px solid;
        transition: all 0.3s ease;
    }

    .modal-footer .btn-secondary {
        background-color: #6c757d;
        color: white;
        border-color: #6c757d;
    }

    .modal-footer .btn-secondary:hover {
        background-color: #5a6268;
        border-color: #5a6268;
    }

    .modal-footer .btn-primary {
        background-color: #133E71;
        color: white;
        border-color: #133E71;
    }

    .modal-footer .btn-primary:hover {
        background-color: #0f2d56;
        border-color: #0f2d56;
    }

    /* Стили для модального окна истории логов */
    .logs-history-modal {
        max-height: 600px;
        display: flex;
        flex-direction: column;
    }

    .logs-history-modal .modal-body {
        flex: 1;
        overflow-y: auto;
        max-height: 500px;
    }

    .logs-history-modal .modal-body::-webkit-scrollbar {
        width: 8px;
    }

    .logs-history-modal .modal-body::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 4px;
    }

    .logs-history-modal .modal-body::-webkit-scrollbar-thumb {
        background: #c1c1c1;
        border-radius: 4px;
    }

    .logs-history-modal .modal-body::-webkit-scrollbar-thumb:hover {
        background: #a8a8a8;
    }

    /* Стили для модального окна действий */
    .actions-modal {
        max-height: 600px;
        display: flex;
        flex-direction: column;
    }

    .actions-modal .modal-body {
        flex: 1;
        overflow-y: auto;
        max-height: 500px;
    }

    .actions-modal .modal-body::-webkit-scrollbar {
        width: 8px;
    }

    .actions-modal .modal-body::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 4px;
    }

    .actions-modal .modal-body::-webkit-scrollbar-thumb {
        background: #c1c1c1;
        border-radius: 4px;
    }

    .actions-modal .modal-body::-webkit-scrollbar-thumb:hover {
        background: #a8a8a8;
    }

    /* Стили для списка действий */
    .actions-list {
        display: flex;
        flex-direction: column;
        gap: 15px;
    }

    .action-item {
        background: #f8f9fa;
        border-radius: 8px;
        padding: 15px;
        border: 1px solid #e9ecef;
        transition: all 0.3s ease;
    }

    .action-item.completed {
        background: #e8f5e8;
        border-color: #28a745;
    }

    .action-item.completed .action-text {
        text-decoration: line-through;
        color: #666;
    }

    .action-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 10px;
    }

    .action-text {
        font-weight: 500;
        color: #495057;
        line-height: 1.5;
        margin: 0;
        flex-grow: 1;
    }

    .action-date {
        color: #666;
        font-size: 12px;
        margin-left: 15px;
    }

    .action-button {
        background: #28a745;
        color: white;
        border: none;
        padding: 6px 12px;
        border-radius: 4px;
        font-size: 12px;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.3s ease;
        margin-left: 10px;
    }

    .action-button:hover {
        background: #218838;
    }

    .action-button:disabled {
        background: #6c757d;
        cursor: not-allowed;
    }

    .action-comment-block {
        margin-top: 10px;
        padding: 10px;
        background: white;
        border-radius: 4px;
        border: 1px solid #e9ecef;
        display: none;
    }

    .action-comment-block.show {
        display: block;
        animation: fadeIn 0.3s ease;
    }

    .action-comment-textarea {
        width: 100%;
        padding: 8px;
        border: 1px solid #ddd;
        border-radius: 4px;
        font-size: 14px;
        resize: vertical;
        min-height: 60px;
        margin-bottom: 10px;
    }

    .action-comment-textarea:focus {
        outline: none;
        border-color: #133E71;
        box-shadow: 0 0 0 2px rgba(19, 62, 113, 0.1);
    }

    .action-comment-buttons {
        display: flex;
        gap: 8px;
        justify-content: flex-end;
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(-10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    /* Стили для спиннера загрузки */
    .loading-spinner {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        padding: 40px 20px;
    }

    .spinner {
        width: 40px;
        height: 40px;
        border: 4px solid #f3f3f3;
        border-top: 4px solid #133E71;
        border-radius: 50%;
        animation: spin 1s linear infinite;
        margin-bottom: 15px;
    }

    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }

    .loading-spinner p {
        color: #666;
        margin: 0;
        font-size: 14px;
    }

    /* Стили для счетчика символов */
    .char-counter {
        font-size: 12px;
        color: #666;
        text-align: right;
        margin-top: 5px;
    }

    .char-counter span {
        font-weight: bold;
    }

    /* Стили для списка логов в модальном окне */
    .logs-history-list {
        display: flex;
        flex-direction: column;
        gap: 15px;
    }

    .logs-history-item {
        background: #f8f9fa;
        border-radius: 8px;
        padding: 15px;
        border: 1px solid #e9ecef;
    }

    .logs-history-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 10px;
    }

    .logs-history-type {
        background: #133E71;
        color: white;
        padding: 4px 8px;
        border-radius: 12px;
        font-size: 10px;
        font-weight: 600;
    }

    .logs-history-date {
        color: #666;
        font-size: 12px;
    }

    .logs-history-content {
        margin-bottom: 10px;
    }

    .logs-history-content p {
        color: #495057;
        line-height: 1.5;
        margin: 0;
    }

    .logs-history-footer {
        color: #666;
        font-size: 12px;
    }

    /* Стили для TreeSelect (аналогично PrimeVue TreeSelect) */
    .treeselect-wrapper {
        position: relative;
        width: 100%;
    }

    .treeselect-input {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 12px 16px;
        border: 2px solid #e9ecef;
        border-radius: 8px;
        background: white;
        cursor: pointer;
        transition: all 0.3s ease;
        min-height: 48px;
    }

    .treeselect-input:hover {
        border-color: #133E71;
    }

    .treeselect-input:focus {
        outline: none;
        border-color: #133E71;
        box-shadow: 0 0 0 3px rgba(19, 62, 113, 0.1);
    }

    .treeselect-input.active {
        border-color: #133E71;
        box-shadow: 0 0 0 3px rgba(19, 62, 113, 0.1);
    }

    .treeselect-placeholder {
        color: #6c757d;
        font-size: 14px;
    }

    .treeselect-value {
        color: #495057;
        font-size: 14px;
        font-weight: 500;
    }

    .treeselect-arrow {
        color: #6c757d;
        transition: transform 0.3s ease;
        flex-shrink: 0;
    }

    .treeselect-input.active .treeselect-arrow {
        transform: rotate(180deg);
    }

    .treeselect-dropdown {
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        z-index: 1000;
        background: white;
        border: 1px solid #dee2e6;
        border-radius: 6px;
        box-shadow: 0 2px 12px rgba(0, 0, 0, 0.1);
        margin-top: 2px;
        display: none;
        max-height: 300px;
        overflow: hidden;
    }

    .treeselect-dropdown.active {
        display: block;
        animation: treeselectFadeIn 0.15s ease-out;
    }

    @keyframes treeselectFadeIn {
        from {
            opacity: 0;
            transform: translateY(-8px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .treeselect-search {
        padding: 12px;
        border-bottom: 1px solid #f1f3f4;
    }

    .treeselect-search-input {
        width: 100%;
        padding: 8px 12px;
        border: 1px solid #dee2e6;
        border-radius: 4px;
        font-size: 14px;
        outline: none;
        transition: border-color 0.3s ease;
    }

    .treeselect-search-input:focus {
        border-color: #133E71;
    }

    .treeselect-tree {
        max-height: 200px;
        overflow-y: auto;
        padding: 8px 0;
    }

    .treeselect-node {
        display: flex;
        align-items: center;
        padding: 8px 12px;
        cursor: pointer;
        transition: background-color 0.15s ease;
        font-size: 14px;
        color: #495057;
        position: relative;
    }

    .treeselect-node:hover {
        background: #f8f9fa;
    }

    .treeselect-node.selected {
        background: #e3f2fd;
        color: #133E71;
        font-weight: 500;
    }

    .treeselect-node.focused {
        background: #e3f2fd;
        color: #133E71;
    }

    .treeselect-node.disabled {
        opacity: 0.5;
        background: #f8f9fa;
        pointer-events: auto;
    }

    .treeselect-node.disabled:hover {
        background: #f8f9fa;
    }

    .treeselect-node.disabled .treeselect-label {
        color: #6c757d;
        cursor: pointer;
    }

    .treeselect-node.disabled .treeselect-toggle {
        cursor: pointer;
    }

    .treeselect-node.disabled .treeselect-toggle:hover {
        color: #133E71;
    }

    .treeselect-toggle {
        width: 16px;
        height: 16px;
        margin-right: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: transform 0.2s ease;
        color: #6c757d;
        flex-shrink: 0;
    }

    .treeselect-toggle.expanded {
        transform: rotate(90deg);
    }

    .treeselect-toggle-icon {
        width: 12px;
        height: 12px;
    }

    .treeselect-indent {
        width: 20px;
        flex-shrink: 0;
    }

    .treeselect-label {
        flex-grow: 1;
        cursor: pointer;
    }

    .treeselect-children {
        display: none;
    }

    .treeselect-children.expanded {
        display: block;
    }

    /* Стили для блока основной информации */
    .main-info-container {
        margin-top: 15px;
    }

    .main-info-content {
        margin-bottom: 15px;
    }

    .main-info-edit {
        margin-bottom: 15px;
    }

    .main-info-edit .form-group {
        margin-bottom: 15px;
    }

    .main-info-edit .form-group label {
        display: block;
        font-weight: 600;
        color: #333;
        margin-bottom: 5px;
        font-size: 14px;
    }

    .main-info-edit .form-group input {
        width: 100%;
        padding: 10px;
        border: 1px solid #ddd;
        border-radius: 6px;
        font-size: 14px;
        font-family: inherit;
        transition: border-color 0.3s ease;
    }

    .main-info-edit .form-group input:focus {
        outline: none;
        border-color: #133E71;
        box-shadow: 0 0 0 2px rgba(19, 62, 113, 0.1);
    }

    .main-info-actions {
        display: flex;
        gap: 10px;
        justify-content: flex-start;
    }

    .main-info-actions .btn {
        padding: 8px 16px;
        border-radius: 6px;
        font-size: 14px;
        font-weight: 500;
        border: 1px solid;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .main-info-actions .btn-primary {
        background-color: #133E71;
        color: white;
        border-color: #133E71;
    }

    .main-info-actions .btn-primary:hover {
        background-color: #0f2d56;
        border-color: #0f2d56;
    }

    .main-info-actions .btn-secondary {
        background-color: #6c757d;
        color: white;
        border-color: #6c757d;
    }

    .main-info-actions .btn-secondary:hover {
        background-color: #5a6268;
        border-color: #5a6268;
    }

    .main-info-actions .btn:disabled {
        opacity: 0.6;
        cursor: not-allowed;
    }

    /* Стили для кнопки скачивания медиафайлов */
    .download-media-section {
        margin-top: 20px;
        padding: 15px;
        background: #f8f9fa;
        border-radius: 8px;
        border: 1px solid #e9ecef;
    }

    .download-media-btn {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 100%;
        padding: 12px 20px;
        background: #133E71;
        color: white;
        border: none;
        border-radius: 6px;
        font-size: 14px;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.3s ease;
        position: relative;
    }

    .download-media-btn:hover {
        background: #0f2d56;
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(19, 62, 113, 0.3);
    }

    .download-media-btn:disabled {
        opacity: 0.6;
        cursor: not-allowed;
        transform: none;
        box-shadow: none;
    }

    .download-media-btn .btn-text {
        margin-right: 8px;
    }

    .download-media-btn .loading-spinner {
        position: absolute;
        right: 20px;
        top: 50%;
        transform: translateY(-50%);
    }

    .download-media-btn .spinner {
        width: 16px;
        height: 16px;
        border: 2px solid rgba(255, 255, 255, 0.3);
        border-top: 2px solid white;
        border-radius: 50%;
        animation: spin 1s linear infinite;
    }

    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
</style>

<script>
// Переменные для определения прав пользователя
let canEdit = {{ $canEdit ? 'true' : 'false' }};
let canChangeStatus = {{ $canChangeStatus ? 'true' : 'false' }};

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

// Закрытие галереи при клике вне изображения - ОТКЛЮЧЕНО
// document.getElementById('galleryModal').addEventListener('click', function(e) {
//     if (e.target === this) {
//         closeGallery();
//     }
// });

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

// Функции для редактирования блока проверки
function editCheckBlock() {
    // Скрываем контент и показываем формы редактирования
    document.getElementById('check_status_content').style.display = 'none';
    document.getElementById('check_status_edit').style.display = 'block';
    document.getElementById('check_comment_content').style.display = 'none';
    document.getElementById('check_comment_edit').style.display = 'block';
    
    // Показываем кнопки действий
    document.getElementById('check_actions').style.display = 'flex';
    
    // Скрываем кнопку редактирования
    document.querySelector('#check_actions').closest('.info-block').querySelector('.edit-comment-btn').style.display = 'none';
}

function cancelCheckEdit() {
    // Восстанавливаем оригинальные значения
    const statusSelect = document.getElementById('check_status_select');
    const commentTextarea = document.getElementById('check_comment_textarea');
    
    if (statusSelect) {
        const originalStatus = statusSelect.getAttribute('data-original') || '';
        statusSelect.value = originalStatus;
    }
    
    if (commentTextarea) {
        const originalComment = commentTextarea.getAttribute('data-original') || '';
        commentTextarea.value = originalComment;
    }
    
    // Показываем контент и скрываем формы редактирования
    document.getElementById('check_status_content').style.display = 'block';
    document.getElementById('check_status_edit').style.display = 'none';
    document.getElementById('check_comment_content').style.display = 'block';
    document.getElementById('check_comment_edit').style.display = 'none';
    
    // Скрываем кнопки действий
    document.getElementById('check_actions').style.display = 'none';
    
    // Показываем кнопку редактирования
    document.querySelector('#check_actions').closest('.info-block').querySelector('.edit-comment-btn').style.display = 'flex';
}

function saveCheckBlock() {
    // Собираем данные
    const checkData = {
        status_id: document.getElementById('check_status_select').value || null,
        comment: document.getElementById('check_comment_textarea').value.trim()
    };
    
    // Показываем индикатор загрузки
    const saveBtn = document.querySelector('#check_actions .btn-primary');
    const originalText = saveBtn.textContent;
    saveBtn.textContent = 'Сохранение...';
    saveBtn.disabled = true;
    
    // Отправляем AJAX запрос
    fetch(`/product/{{ $product->id }}/check-status`, {
        method: 'PATCH',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify(checkData)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Обновляем отображение
            updateCheckDisplay(checkData);
            
            // Сохраняем новые значения как оригинальные
            saveCheckOriginals(checkData);
            
            // Скрываем формы редактирования
            cancelCheckEdit();
            
            // Показываем уведомление об успехе
            showNotification('Информация о проверке успешно обновлена', 'success');
        } else {
            throw new Error(data.message || 'Ошибка при сохранении');
        }
    })
    .catch(error => {
        console.error('Ошибка:', error);
        showNotification('Ошибка при сохранении информации о проверке', 'error');
    })
    .finally(() => {
        // Восстанавливаем кнопку
        saveBtn.textContent = originalText;
        saveBtn.disabled = false;
    });
}

// Функции для редактирования блока погрузки
function editLoadingBlock() {
    // Скрываем контент и показываем формы редактирования
    document.getElementById('loading_status_content').style.display = 'none';
    document.getElementById('loading_status_edit').style.display = 'block';
    document.getElementById('loading_comment_content').style.display = 'none';
    document.getElementById('loading_comment_edit').style.display = 'block';
    
    // Показываем кнопки действий
    document.getElementById('loading_actions').style.display = 'flex';
    
    // Скрываем кнопку редактирования
    document.querySelector('#loading_actions').closest('.info-block').querySelector('.edit-comment-btn').style.display = 'none';
}

function cancelLoadingEdit() {
    // Восстанавливаем оригинальные значения
    const statusSelect = document.getElementById('loading_status_select');
    const commentTextarea = document.getElementById('loading_comment_textarea');
    
    if (statusSelect) {
        const originalStatus = statusSelect.getAttribute('data-original') || '';
        statusSelect.value = originalStatus;
    }
    
    if (commentTextarea) {
        const originalComment = commentTextarea.getAttribute('data-original') || '';
        commentTextarea.value = originalComment;
    }
    
    // Показываем контент и скрываем формы редактирования
    document.getElementById('loading_status_content').style.display = 'block';
    document.getElementById('loading_status_edit').style.display = 'none';
    document.getElementById('loading_comment_content').style.display = 'block';
    document.getElementById('loading_comment_edit').style.display = 'none';
    
    // Скрываем кнопки действий
    document.getElementById('loading_actions').style.display = 'none';
    
    // Показываем кнопку редактирования
    document.querySelector('#loading_actions').closest('.info-block').querySelector('.edit-comment-btn').style.display = 'flex';
}

function saveLoadingBlock() {
    // Собираем данные
    const loadingData = {
        status_id: document.getElementById('loading_status_select').value || null,
        comment: document.getElementById('loading_comment_textarea').value.trim()
    };
    
    // Показываем индикатор загрузки
    const saveBtn = document.querySelector('#loading_actions .btn-primary');
    const originalText = saveBtn.textContent;
    saveBtn.textContent = 'Сохранение...';
    saveBtn.disabled = true;
    
    // Отправляем AJAX запрос
    fetch(`/product/{{ $product->id }}/loading-status`, {
        method: 'PATCH',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify(loadingData)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Обновляем отображение
            updateLoadingDisplay(loadingData);
            
            // Сохраняем новые значения как оригинальные
            saveLoadingOriginals(loadingData);
            
            // Скрываем формы редактирования
            cancelLoadingEdit();
            
            // Показываем уведомление об успехе
            showNotification('Информация о погрузке успешно обновлена', 'success');
        } else {
            throw new Error(data.message || 'Ошибка при сохранении');
        }
    })
    .catch(error => {
        console.error('Ошибка:', error);
        showNotification('Ошибка при сохранении информации о погрузке', 'error');
    })
    .finally(() => {
        // Восстанавливаем кнопку
        saveBtn.textContent = originalText;
        saveBtn.disabled = false;
    });
}

// Функции для редактирования блока демонтажа
function editRemovalBlock() {
    // Скрываем контент и показываем формы редактирования
    document.getElementById('removal_status_content').style.display = 'none';
    document.getElementById('removal_status_edit').style.display = 'block';
    document.getElementById('removal_comment_content').style.display = 'none';
    document.getElementById('removal_comment_edit').style.display = 'block';
    
    // Показываем кнопки действий
    document.getElementById('removal_actions').style.display = 'flex';
    
    // Скрываем кнопку редактирования
    document.querySelector('#removal_actions').closest('.info-block').querySelector('.edit-comment-btn').style.display = 'none';
}

function cancelRemovalEdit() {
    // Восстанавливаем оригинальные значения
    const statusSelect = document.getElementById('removal_status_select');
    const commentTextarea = document.getElementById('removal_comment_textarea');
    
    if (statusSelect) {
        const originalStatus = statusSelect.getAttribute('data-original') || '';
        statusSelect.value = originalStatus;
    }
    
    if (commentTextarea) {
        const originalComment = commentTextarea.getAttribute('data-original') || '';
        commentTextarea.value = originalComment;
    }
    
    // Показываем контент и скрываем формы редактирования
    document.getElementById('removal_status_content').style.display = 'block';
    document.getElementById('removal_status_edit').style.display = 'none';
    document.getElementById('removal_comment_content').style.display = 'block';
    document.getElementById('removal_comment_edit').style.display = 'none';
    
    // Скрываем кнопки действий
    document.getElementById('removal_actions').style.display = 'none';
    
    // Показываем кнопку редактирования
    document.querySelector('#removal_actions').closest('.info-block').querySelector('.edit-comment-btn').style.display = 'flex';
}

function saveRemovalBlock() {
    // Собираем данные
    const removalData = {
        status_id: document.getElementById('removal_status_select').value || null,
        comment: document.getElementById('removal_comment_textarea').value.trim()
    };
    
    // Показываем индикатор загрузки
    const saveBtn = document.querySelector('#removal_actions .btn-primary');
    const originalText = saveBtn.textContent;
    saveBtn.textContent = 'Сохранение...';
    saveBtn.disabled = true;
    
    // Отправляем AJAX запрос
    fetch(`/product/{{ $product->id }}/removal-status`, {
        method: 'PATCH',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify(removalData)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Обновляем отображение
            updateRemovalDisplay(removalData);
            
            // Сохраняем новые значения как оригинальные
            saveRemovalOriginals(removalData);
            
            // Скрываем формы редактирования
            cancelRemovalEdit();
            
            // Показываем уведомление об успехе
            showNotification('Информация о демонтаже успешно обновлена', 'success');
        } else {
            throw new Error(data.message || 'Ошибка при сохранении');
        }
    })
    .catch(error => {
        console.error('Ошибка:', error);
        showNotification('Ошибка при сохранении информации о демонтаже', 'error');
    })
    .finally(() => {
        // Восстанавливаем кнопку
        saveBtn.textContent = originalText;
        saveBtn.disabled = false;
    });
}

// Функции обновления отображения
function updateCheckDisplay(checkData) {
    // Обновляем отображение статуса проверки
    const statusContent = document.getElementById('check_status_content');
    if (checkData.status_id) {
        const statusSelect = document.getElementById('check_status_select');
        const selectedOption = statusSelect.options[statusSelect.selectedIndex];
        const statusName = selectedOption.text;
        
        // Получаем цвет из data-атрибута или используем цвет по умолчанию
        const statusColor = selectedOption.getAttribute('data-color') || '#6c757d';
        
        statusContent.innerHTML = `
            <span class="status-badge" style="background-color: ${statusColor}; color: white;">
                ${statusName}
            </span>
        `;
    } else {
        statusContent.innerHTML = '<span class="status-badge" style="background-color: #6c757d; color: white;">Не указан</span>';
    }
    
    // Обновляем отображение комментария
    const commentContent = document.getElementById('check_comment_content');
    if (checkData.comment) {
        commentContent.innerHTML = `<p>${checkData.comment}</p>`;
    } else {
        commentContent.innerHTML = '<p class="no-comment">Комментарий к проверке не указан</p>';
    }
}

function updateLoadingDisplay(loadingData) {
    // Обновляем отображение статуса погрузки
    const statusContent = document.getElementById('loading_status_content');
    if (loadingData.status_id) {
        const statusSelect = document.getElementById('loading_status_select');
        const selectedOption = statusSelect.options[statusSelect.selectedIndex];
        const statusName = selectedOption.text;
        
        statusContent.innerHTML = `<span class="status-badge">${statusName}</span>`;
    } else {
        statusContent.innerHTML = '<span class="status-badge">Не указан</span>';
    }
    
    // Обновляем отображение комментария
    const commentContent = document.getElementById('loading_comment_content');
    if (loadingData.comment) {
        commentContent.innerHTML = `<p>${loadingData.comment}</p>`;
    } else {
        commentContent.innerHTML = '<p class="no-comment">Комментарий по погрузке не указан</p>';
    }
}

function updateRemovalDisplay(removalData) {
    // Обновляем отображение статуса демонтажа
    const statusContent = document.getElementById('removal_status_content');
    if (removalData.status_id) {
        const statusSelect = document.getElementById('removal_status_select');
        const selectedOption = statusSelect.options[statusSelect.selectedIndex];
        const statusName = selectedOption.text;
        
        statusContent.innerHTML = `<span class="status-badge">${statusName}</span>`;
    } else {
        statusContent.innerHTML = '<span class="status-badge">Не указан</span>';
    }
    
    // Обновляем отображение комментария
    const commentContent = document.getElementById('removal_comment_content');
    if (removalData.comment) {
        commentContent.innerHTML = `<p>${removalData.comment}</p>`;
    } else {
        commentContent.innerHTML = '<p class="no-comment">Комментарий по демонтажу не указан</p>';
    }
}

// Функции сохранения оригинальных значений
function saveCheckOriginals(checkData) {
    const statusSelect = document.getElementById('check_status_select');
    const commentTextarea = document.getElementById('check_comment_textarea');
    
    if (statusSelect) {
        statusSelect.setAttribute('data-original', checkData.status_id || '');
    }
    
    if (commentTextarea) {
        commentTextarea.setAttribute('data-original', checkData.comment || '');
    }
}

function saveLoadingOriginals(loadingData) {
    const statusSelect = document.getElementById('loading_status_select');
    const commentTextarea = document.getElementById('loading_comment_textarea');
    
    if (statusSelect) {
        statusSelect.setAttribute('data-original', loadingData.status_id || '');
    }
    
    if (commentTextarea) {
        commentTextarea.setAttribute('data-original', loadingData.comment || '');
    }
}

function saveRemovalOriginals(removalData) {
    const statusSelect = document.getElementById('removal_status_select');
    const commentTextarea = document.getElementById('removal_comment_textarea');
    
    if (statusSelect) {
        statusSelect.setAttribute('data-original', removalData.status_id || '');
    }
    
    if (commentTextarea) {
        commentTextarea.setAttribute('data-original', removalData.comment || '');
    }
}

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
    
    const paymentSelects = document.querySelectorAll('.payment-select, select[name="main_payment_method"]');
    paymentSelects.forEach(select => {
        select.setAttribute('data-original', select.value);
    });
    
    // Инициализация чекбоксов для вариантов оплаты
    const paymentCheckboxes = document.querySelectorAll('input[name="payment_types[]"]');
    paymentCheckboxes.forEach(checkbox => {
        checkbox.setAttribute('data-original', checkbox.checked);
    });
});

// Функции для редактирования блока оплаты
function editPaymentBlock() {
    // Скрываем все контенты и показываем формы редактирования
    const fields = ['payment_method', 'main_payment_method', 'purchase_price', 'payment_comment'];
    
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
    
    // Фокусируемся на первом поле
    const firstSelect = document.getElementById('payment_method_select');
    if (firstSelect) {
        firstSelect.focus();
    }
}

function cancelPaymentEdit() {
    // Восстанавливаем все оригинальные значения
    const fields = ['payment_method', 'main_payment_method', 'purchase_price', 'payment_comment'];
    
    fields.forEach(field => {
        const content = document.getElementById(field + '_content');
        const edit = document.getElementById(field + '_edit');
        
        if (content && edit) {
            // Восстанавливаем оригинальное значение
            if (field === 'payment_method') {
                const select = document.getElementById(field + '_select');
                if (select) {
                    const originalValue = select.getAttribute('data-original') || '';
                    select.value = originalValue;
                }
            } else if (field === 'main_payment_method') {
                const select = document.getElementById(field + '_select');
                if (select) {
                    const originalValue = select.getAttribute('data-original') || '';
                    select.value = originalValue;
                }
            } else if (field === 'purchase_price') {
                const input = document.getElementById(field + '_input');
                if (input) {
                    const originalValue = input.getAttribute('data-original') || '';
                    input.value = originalValue;
                }
            } else if (field === 'payment_comment') {
                const textarea = document.getElementById(field + '_textarea');
                if (textarea) {
                    const originalValue = textarea.getAttribute('data-original') || '';
                    textarea.value = originalValue;
                }
            }
            
            // Восстанавливаем состояние чекбоксов для вариантов оплаты
            const paymentCheckboxes = document.querySelectorAll('input[name="payment_types[]"]');
            paymentCheckboxes.forEach(checkbox => {
                const originalState = checkbox.getAttribute('data-original') === 'true';
                checkbox.checked = originalState;
            });
            
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
        main_payment_method: null,
        purchase_price: null,
        payment_comment: null
    };
    
    // Получаем выбранные варианты оплаты
    const paymentCheckboxes = document.querySelectorAll('input[name="payment_types[]"]:checked');
    paymentCheckboxes.forEach(checkbox => {
        paymentData.payment_types.push(checkbox.value);
    });
    
    // Получаем основной способ оплаты
    const mainPaymentSelect = document.getElementById('main_payment_method_select');
    if (mainPaymentSelect) {
        paymentData.main_payment_method = mainPaymentSelect.value;
    }
    
    // Получаем закупочную цену
    const purchasePriceInput = document.getElementById('purchase_price_input');
    if (purchasePriceInput) {
        paymentData.purchase_price = purchasePriceInput.value.trim();
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
    fetch(`/product/{{ $product->id }}/payment-variants`, {
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
            // Обновляем отображение вариантов оплаты
            const paymentMethodContent = document.getElementById('payment_method_content');
            if (paymentData.payment_types.length > 0) {
                let variantsHtml = '<div class="payment-variants">';
                paymentData.payment_types.forEach(typeId => {
                    // Получаем название типа оплаты из соответствующего чекбокса
                    const checkbox = document.querySelector(`input[name="payment_types[]"][value="${typeId}"]`);
                    const typeName = checkbox ? checkbox.nextElementSibling.textContent : `Тип ${typeId}`;
                    variantsHtml += `<span class="payment-variant">${typeName}</span>`;
                });
                variantsHtml += '</div>';
                paymentMethodContent.innerHTML = variantsHtml;
            } else {
                paymentMethodContent.innerHTML = '<span class="no-value">Не указаны</span>';
            }
            
            // Обновляем основной способ оплаты
            const mainPaymentContent = document.getElementById('main_payment_method_content');
            if (paymentData.main_payment_method) {
                const select = document.getElementById('main_payment_method_select');
                const selectedOption = select ? select.options[select.selectedIndex] : null;
                const typeName = selectedOption ? selectedOption.textContent : `Тип ${paymentData.main_payment_method}`;
                mainPaymentContent.innerHTML = `<span>${typeName}</span>`;
            } else {
                mainPaymentContent.innerHTML = '<span class="no-value">Не указан</span>';
            }
            
            // Обновляем закупочную цену
            const purchasePriceContent = document.getElementById('purchase_price_content');
            if (paymentData.purchase_price) {
                const formattedPrice = new Intl.NumberFormat('ru-RU').format(parseFloat(paymentData.purchase_price));
                purchasePriceContent.innerHTML = `<span class="price">${formattedPrice} ₽</span>`;
            } else {
                purchasePriceContent.innerHTML = '<span class="no-value">Не указана</span>';
            }
            
            // Обновляем комментарий по оплате
            const paymentCommentContent = document.getElementById('payment_comment_content');
            if (paymentData.payment_comment) {
                paymentCommentContent.innerHTML = `<p>${paymentData.payment_comment}</p>`;
            } else {
                paymentCommentContent.innerHTML = '<p class="no-comment">Комментарий не указан</p>';
            }
            
            // Сохраняем новые значения как оригинальные
            if (mainPaymentSelect) mainPaymentSelect.setAttribute('data-original', paymentData.main_payment_method);
            if (purchasePriceInput) purchasePriceInput.setAttribute('data-original', paymentData.purchase_price);
            if (paymentCommentTextarea) paymentCommentTextarea.setAttribute('data-original', paymentData.payment_comment);
            
            // Сохраняем состояние чекбоксов
            const paymentCheckboxes = document.querySelectorAll('input[name="payment_types[]"]');
            paymentCheckboxes.forEach(checkbox => {
                const isChecked = paymentData.payment_types.includes(checkbox.value);
                checkbox.setAttribute('data-original', isChecked.toString());
            });
            
            // Скрываем формы редактирования и показываем контент
            document.querySelectorAll('.payment-edit').forEach(edit => edit.style.display = 'none');
            document.querySelectorAll('.payment-content').forEach(content => content.style.display = 'block');
            
            // Скрываем общие кнопки действий
            document.getElementById('payment_actions').style.display = 'none';
            
            // Показываем кнопку редактирования
            document.querySelector('.edit-payment-btn').style.display = 'flex';
            
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

// Закрытие модального окна при клике вне его - ОТКЛЮЧЕНО
// window.onclick = function(event) {
//     const modal = document.getElementById('contactModal');
//     if (event.target === modal) {
//         closeContactCard();
//     }
// }

// Функции для работы с выпадающим списком статусов товара
function toggleProductStatusDropdown() {
    // Проверяем права пользователя на изменение статуса
    if (!canChangeStatus) {
        showNotification('У вас нет прав на изменение статуса товара', 'error');
        return;
    }
    
    const dropdown = document.getElementById('productStatusDropdown');
    const arrow = document.querySelector('.status-badge.clickable .dropdown-arrow');
    
    if (dropdown.classList.contains('show')) {
        dropdown.classList.remove('show');
        arrow.style.transform = 'rotate(0deg)';
    } else {
        dropdown.classList.add('show');
        arrow.style.transform = 'rotate(180deg)';
    }
}

// Глобальные переменные для хранения данных о смене статуса товара
let pendingProductStatusChange = null;

function changeProductStatus(statusId, statusName, statusColor) {
    // Проверяем права пользователя на изменение статуса
    if (!canChangeStatus) {
        showNotification('У вас нет прав на изменение статуса товара', 'error');
        return;
    }
    
    // Получаем данные о смене статуса
    pendingProductStatusChange = {
        statusId: statusId,
        statusName: statusName,
        statusColor: statusColor
    };
    
    // Показываем модальное окно для комментария
    showProductStatusModal();
}

function showProductStatusModal() {
    const modal = document.getElementById('productStatusCommentModal');
    const textarea = document.getElementById('productStatusComment');
    
    // Очищаем поле комментария
    textarea.value = '';
    
    // Показываем модальное окно
    modal.style.display = 'block';
    
    // Фокусируемся на поле комментария
    textarea.focus();
}

function closeProductStatusModal() {
    const modal = document.getElementById('productStatusCommentModal');
    modal.style.display = 'none';
}

function cancelProductStatusChange() {
    const modal = document.getElementById('productStatusCommentModal');
    modal.style.display = 'none';
    
    // Сбрасываем данные о смене статуса при отмене
    pendingProductStatusChange = null;
}

function saveProductStatusChange() {
    const comment = document.getElementById('productStatusComment').value.trim();
    
    if (!comment) {
        alert('Пожалуйста, введите комментарий');
        return;
    }
    
    if (!pendingProductStatusChange || !pendingProductStatusChange.statusId || !pendingProductStatusChange.statusName) {
        alert('Ошибка: данные о смене статуса не найдены. Пожалуйста, выберите статус заново.');
        closeProductStatusModal();
        return;
    }
    
    // Показываем индикатор загрузки
    const statusBadge = document.querySelector('.status-badge.clickable');
    const originalContent = statusBadge.innerHTML;
    const originalStyle = statusBadge.getAttribute('style');
    statusBadge.innerHTML = '<span>Обновление...</span>';
    
    // Закрываем модальное окно
    closeProductStatusModal();
    
    // Отправляем запрос на сервер
    fetch(`/product/{{ $product->id }}/status`, {
        method: 'PATCH',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            status_id: pendingProductStatusChange.statusId,
            comment: comment
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Обновляем отображение статуса
            statusBadge.className = 'status-badge clickable';
            statusBadge.style.cssText = `background-color: ${pendingProductStatusChange.statusColor}; color: white;`;
            statusBadge.innerHTML = `
                ${pendingProductStatusChange.statusName}
                <svg class="dropdown-arrow" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="6,9 12,15 18,9"></polyline>
                </svg>
            `;
            
            // Закрываем выпадающий список
            toggleProductStatusDropdown();
            
            // Обновляем лог событий, если получен новый лог
            if (data.log) {
                updateProductEventsLog(data.log);
            }
            
            // Показываем уведомление об успехе
            showNotification('Статус товара успешно обновлен', 'success');
        } else {
            throw new Error(data.message || 'Ошибка при обновлении статуса');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        // Возвращаем оригинальное содержимое при ошибке
        statusBadge.innerHTML = originalContent;
        statusBadge.setAttribute('style', originalStyle);
        showNotification('Ошибка при обновлении статуса товара', 'error');
    })
    .finally(() => {
        // Сбрасываем данные о смене статуса только после завершения операции
        if (pendingProductStatusChange) {
            pendingProductStatusChange = null;
        }
    });
}

function updateProductEventsLog(log) {
    const eventsList = document.querySelector('.events-list');
    
    // Создаем новый элемент лога
    const eventItem = document.createElement('div');
    eventItem.className = 'event-item';
    
    const eventHeader = document.createElement('div');
    eventHeader.className = 'event-header';
    
    const eventType = document.createElement('span');
    eventType.className = 'event-type';
    eventType.textContent = log.type ? log.type.name : 'Неизвестный тип';
    if (log.type && log.type.color) {
        eventType.style.backgroundColor = log.type.color;
    }
    
    const eventDate = document.createElement('span');
    eventDate.className = 'event-date';
    eventDate.textContent = new Date(log.created_at).toLocaleString('ru-RU');
    
    eventHeader.appendChild(eventType);
    eventHeader.appendChild(eventDate);
    
    const eventContent = document.createElement('div');
    eventContent.className = 'event-content';
    const contentParagraph = document.createElement('p');
    contentParagraph.textContent = log.log;
    eventContent.appendChild(contentParagraph);
    
    const eventFooter = document.createElement('div');
    eventFooter.className = 'event-footer';
    const footerSpan = document.createElement('span');
    footerSpan.textContent = `Создал: ${log.user ? log.user.name : 'Система'}`;
    eventFooter.appendChild(footerSpan);
    
    eventItem.appendChild(eventHeader);
    eventItem.appendChild(eventContent);
    eventItem.appendChild(eventFooter);
    
    // Добавляем новый лог в начало списка
    if (eventsList.firstChild) {
        eventsList.insertBefore(eventItem, eventsList.firstChild);
    } else {
        eventsList.appendChild(eventItem);
    }
    
    // Удаляем сообщение об отсутствии логов, если оно есть
    const noLogsMessage = eventsList.querySelector('.event-item p[style*="color: #666"]');
    if (noLogsMessage) {
        noLogsMessage.parentElement.parentElement.remove();
    }
}

// Закрытие выпадающего списка статусов при клике вне его
document.addEventListener('click', function(event) {
    const statusSelector = document.querySelector('.status-selector');
    const dropdown = document.getElementById('productStatusDropdown');
    
    if (!statusSelector.contains(event.target) && dropdown && dropdown.classList.contains('show')) {
        toggleProductStatusDropdown();
    }
});

// Функции для редактирования блока характеристик
function editCharacteristicsBlock() {
    // Скрываем контент и показываем формы редактирования
    document.getElementById('characteristics_content').style.display = 'none';
    document.getElementById('characteristics_edit').style.display = 'block';
    
    // Показываем кнопки действий
    document.getElementById('characteristics_actions').style.display = 'flex';
    
    // Скрываем кнопку редактирования
    document.querySelector('#characteristics_actions').closest('.info-block').querySelector('.edit-comment-btn').style.display = 'none';
}

function cancelCharacteristicsEdit() {
    // Восстанавливаем оригинальные значения
    const mainCharsTextarea = document.getElementById('main_chars_textarea');
    const complectationTextarea = document.getElementById('complectation_textarea');
    const markTextarea = document.getElementById('mark_textarea');
    
    if (mainCharsTextarea) {
        const originalMainChars = mainCharsTextarea.getAttribute('data-original') || '';
        mainCharsTextarea.value = originalMainChars;
    }
    
    if (complectationTextarea) {
        const originalComplectation = complectationTextarea.getAttribute('data-original') || '';
        complectationTextarea.value = originalComplectation;
    }
    
    if (markTextarea) {
        const originalMark = markTextarea.getAttribute('data-original') || '';
        markTextarea.value = originalMark;
    }
    
    // Показываем контент и скрываем формы редактирования
    document.getElementById('characteristics_content').style.display = 'block';
    document.getElementById('characteristics_edit').style.display = 'none';
    
    // Скрываем кнопки действий
    document.getElementById('characteristics_actions').style.display = 'none';
    
    // Показываем кнопку редактирования
    document.querySelector('#characteristics_actions').closest('.info-block').querySelector('.edit-comment-btn').style.display = 'flex';
}

function saveCharacteristicsBlock() {
    // Собираем данные
    const characteristicsData = {
        main_chars: document.getElementById('main_chars_textarea').value.trim(),
        complectation: document.getElementById('complectation_textarea').value.trim(),
        mark: document.getElementById('mark_textarea').value.trim()
    };
    
    // Показываем индикатор загрузки
    const saveBtn = document.querySelector('#characteristics_actions .btn-primary');
    const originalText = saveBtn.textContent;
    saveBtn.textContent = 'Сохранение...';
    saveBtn.disabled = true;
    
    // Отправляем AJAX запрос
    fetch(`/product/{{ $product->id }}/characteristics`, {
        method: 'PATCH',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify(characteristicsData)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Обновляем отображение
            updateCharacteristicsDisplay(characteristicsData);
            
            // Сохраняем новые значения как оригинальные
            saveCharacteristicsOriginals(characteristicsData);
            
            // Скрываем формы редактирования
            cancelCharacteristicsEdit();
            
            // Показываем уведомление об успехе
            showNotification('Характеристики товара успешно обновлены', 'success');
        } else {
            throw new Error(data.message || 'Ошибка при сохранении');
        }
    })
    .catch(error => {
        console.error('Ошибка:', error);
        showNotification('Ошибка при сохранении характеристик товара', 'error');
    })
    .finally(() => {
        // Восстанавливаем кнопку
        saveBtn.textContent = originalText;
        saveBtn.disabled = false;
    });
}

function updateCharacteristicsDisplay(data) {
    const contentDiv = document.getElementById('characteristics_content');
    let html = '';
    
    if (data.main_chars || data.complectation || data.mark) {
        if (data.main_chars) {
            html += `<div class="chars-item">
                <strong>Основные характеристики:</strong>
                <p>${data.main_chars}</p>
            </div>`;
        }
        if (data.complectation) {
            html += `<div class="chars-item">
                <strong>Комплектация:</strong>
                <p>${data.complectation}</p>
            </div>`;
        }
        if (data.mark) {
            html += `<div class="chars-item">
                <strong>Оценка:</strong>
                <p>${data.mark}</p>
            </div>`;
        }
    } else {
        html = '<p class="no-comment">Характеристики не указаны</p>';
    }
    
    contentDiv.innerHTML = html;
}

function saveCharacteristicsOriginals(data) {
    const mainCharsTextarea = document.getElementById('main_chars_textarea');
    const complectationTextarea = document.getElementById('complectation_textarea');
    const markTextarea = document.getElementById('mark_textarea');
    
    if (mainCharsTextarea) {
        mainCharsTextarea.setAttribute('data-original', data.main_chars);
    }
    if (complectationTextarea) {
        complectationTextarea.setAttribute('data-original', data.complectation);
    }
    if (markTextarea) {
        markTextarea.setAttribute('data-original', data.mark);
    }
}

// Глобальные переменные для работы с товаром
let currentProductId = '{{ $product->id }}';

// Функции для работы с историей логов
function showLogsHistory() {
    // Проверяем права пользователя на просмотр логов
    if (!canEdit) {
        showNotification('У вас нет прав на просмотр логов', 'error');
        return;
    }
    
    const modal = document.getElementById('logsHistoryModal');
    modal.style.display = 'block';
    
    // Загружаем логи
    loadLogsHistory();
}

function closeLogsHistory() {
    const modal = document.getElementById('logsHistoryModal');
    modal.style.display = 'none';
}

function loadLogsHistory() {
    const content = document.getElementById('logsHistoryContent');
    
    // Показываем спиннер загрузки
    content.innerHTML = `
        <div class="loading-spinner">
            <div class="spinner"></div>
            <p>Загрузка логов...</p>
        </div>
    `;
    
    // Отправляем запрос на сервер
    fetch(`/product/${currentProductId}/logs`, {
        method: 'GET',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            displayLogsHistory(data.logs);
        } else {
            throw new Error(data.message || 'Ошибка при загрузке логов');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        content.innerHTML = `
            <div class="loading-spinner">
                <p style="color: #dc3545;">Ошибка при загрузке логов: ${error.message}</p>
            </div>
        `;
    });
}

function displayLogsHistory(logs) {
    const content = document.getElementById('logsHistoryContent');
    
    if (logs.length === 0) {
        content.innerHTML = `
            <div class="loading-spinner">
                <p style="color: #666; font-style: italic;">Логи отсутствуют</p>
            </div>
        `;
        return;
    }
    
    const logsList = document.createElement('div');
    logsList.className = 'logs-history-list';
    
    logs.forEach(log => {
        const logItem = document.createElement('div');
        logItem.className = 'logs-history-item';
        
        const logHeader = document.createElement('div');
        logHeader.className = 'logs-history-header';
        
        const logType = document.createElement('span');
        logType.className = 'logs-history-type';
        logType.textContent = log.type ? log.type.name : 'Неизвестный тип';
        if (log.type && log.type.color) {
            logType.style.backgroundColor = log.type.color;
        }
        
        const logDate = document.createElement('span');
        logDate.className = 'logs-history-date';
        logDate.textContent = new Date(log.created_at).toLocaleString('ru-RU');
        
        logHeader.appendChild(logType);
        logHeader.appendChild(logDate);
        
        const logContent = document.createElement('div');
        logContent.className = 'logs-history-content';
        const contentParagraph = document.createElement('p');
        contentParagraph.textContent = log.log;
        logContent.appendChild(contentParagraph);
        
        const logFooter = document.createElement('div');
        logFooter.className = 'logs-history-footer';
        const footerSpan = document.createElement('span');
        footerSpan.textContent = `Создал: ${log.user ? log.user.name : 'Система'}`;
        logFooter.appendChild(footerSpan);
        
        logItem.appendChild(logHeader);
        logItem.appendChild(logContent);
        logItem.appendChild(logFooter);
        
        logsList.appendChild(logItem);
    });
    
    content.innerHTML = '';
    content.appendChild(logsList);
}

// Глобальные переменные для работы с действиями
let currentActionId = null;
let currentActionText = null;

// Функции для работы с модальным окном действий
function showActionsModal() {
    // Проверяем права пользователя на просмотр действий
    if (!canEdit) {
        showNotification('У вас нет прав на просмотр действий', 'error');
        return;
    }
    
    const modal = document.getElementById('actionsModal');
    modal.style.display = 'block';
    
    // Загружаем действия
    loadActions();
}

function closeActionsModal() {
    const modal = document.getElementById('actionsModal');
    modal.style.display = 'none';
}

function loadActions() {
    const content = document.getElementById('actionsList');
    
    // Показываем спиннер загрузки
    content.innerHTML = `
        <div class="loading-spinner">
            <div class="spinner"></div>
            <p>Загрузка действий...</p>
        </div>
    `;
    
    // Отправляем запрос на сервер
    fetch(`/product/${currentProductId}/actions`, {
        method: 'GET',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            displayActions(data.actions);
        } else {
            throw new Error(data.message || 'Ошибка при загрузке действий');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        content.innerHTML = `
            <div class="loading-spinner">
                <p style="color: #dc3545;">Ошибка при загрузке действий: ${error.message}</p>
            </div>
        `;
    });
}

function displayActions(actions) {
    const content = document.getElementById('actionsList');
    
    if (actions.length === 0) {
        content.innerHTML = `
            <div class="loading-spinner">
                <p style="color: #666; font-style: italic;">Действия отсутствуют</p>
            </div>
        `;
        return;
    }
    
    const actionsList = document.createElement('div');
    actionsList.className = 'actions-list';
    
    actions.forEach(action => {
        const actionItem = document.createElement('div');
        actionItem.className = `action-item ${action.status ? 'completed' : ''}`;
        actionItem.setAttribute('data-action-id', action.id);
        
        const actionHeader = document.createElement('div');
        actionHeader.className = 'action-header';
        
        const actionText = document.createElement('p');
        actionText.className = 'action-text';
        actionText.textContent = action.action;
        
        const actionDate = document.createElement('span');
        actionDate.className = 'action-date';
        actionDate.textContent = new Date(action.expired_at).toLocaleDateString('ru-RU');
        
        const actionButton = document.createElement('button');
        actionButton.className = 'action-button';
        actionButton.textContent = action.status ? 'Выполнено' : 'Я сделал';
        actionButton.disabled = action.status;
        
        if (!action.status) {
            actionButton.onclick = () => showActionCommentBlock(action.id, action.action);
        }
        
        actionHeader.appendChild(actionText);
        actionHeader.appendChild(actionDate);
        actionHeader.appendChild(actionButton);
        
        const commentBlock = document.createElement('div');
        commentBlock.className = 'action-comment-block';
        commentBlock.id = `comment-block-${action.id}`;
        
        const commentTextarea = document.createElement('textarea');
        commentTextarea.className = 'action-comment-textarea';
        commentTextarea.placeholder = 'Введите комментарий о выполнении...';
        
        const commentButtons = document.createElement('div');
        commentButtons.className = 'action-comment-buttons';
        
        const cancelButton = document.createElement('button');
        cancelButton.className = 'btn btn-secondary';
        cancelButton.textContent = 'Отмена';
        cancelButton.onclick = () => hideActionCommentBlock(action.id);
        
        const saveButton = document.createElement('button');
        saveButton.className = 'btn btn-primary';
        saveButton.textContent = 'Сохранить';
        saveButton.onclick = () => saveActionComment(action.id, action.action);
        
        commentButtons.appendChild(cancelButton);
        commentButtons.appendChild(saveButton);
        
        commentBlock.appendChild(commentTextarea);
        commentBlock.appendChild(commentButtons);
        
        actionItem.appendChild(actionHeader);
        actionItem.appendChild(commentBlock);
        
        actionsList.appendChild(actionItem);
    });
    
    content.innerHTML = '';
    content.appendChild(actionsList);
}

function showActionCommentBlock(actionId, actionText) {
    // Скрываем все другие блоки комментариев
    const allCommentBlocks = document.querySelectorAll('.action-comment-block');
    allCommentBlocks.forEach(block => {
        block.classList.remove('show');
    });
    
    // Показываем нужный блок
    const commentBlock = document.getElementById(`comment-block-${actionId}`);
    if (commentBlock) {
        commentBlock.classList.add('show');
        const textarea = commentBlock.querySelector('.action-comment-textarea');
        textarea.focus();
    }
}

function hideActionCommentBlock(actionId) {
    const commentBlock = document.getElementById(`comment-block-${actionId}`);
    if (commentBlock) {
        commentBlock.classList.remove('show');
        const textarea = commentBlock.querySelector('.action-comment-textarea');
        textarea.value = '';
    }
}

function saveActionComment(actionId, actionText) {
    const commentBlock = document.getElementById(`comment-block-${actionId}`);
    const textarea = commentBlock.querySelector('.action-comment-textarea');
    const comment = textarea.value.trim();
    
    if (!comment) {
        alert('Пожалуйста, введите комментарий');
        return;
    }
    
    // Показываем индикатор загрузки
    const actionItem = document.querySelector(`[data-action-id="${actionId}"]`);
    const actionButton = actionItem.querySelector('.action-button');
    const originalText = actionButton.textContent;
    actionButton.textContent = 'Сохранение...';
    actionButton.disabled = true;
    
    // Отправляем запрос на сервер
    fetch(`/product/${currentProductId}/actions/${actionId}/complete`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            comment: comment
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Обновляем отображение действия
            actionItem.classList.add('completed');
            actionItem.querySelector('.action-text').style.textDecoration = 'line-through';
            actionItem.querySelector('.action-text').style.color = '#666';
            actionButton.textContent = 'Выполнено';
            actionButton.disabled = true;
            
            // Скрываем блок комментария
            hideActionCommentBlock(actionId);
            
            // Обновляем лог событий, если получен новый лог
            if (data.log) {
                updateEventsLog(data.log);
            }
            
            // Показываем уведомление об успехе
            showNotification('Действие успешно выполнено', 'success');
        } else {
            throw new Error(data.message || 'Ошибка при сохранении действия');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        // Возвращаем оригинальное состояние при ошибке
        actionButton.textContent = originalText;
        actionButton.disabled = false;
        showNotification('Ошибка при сохранении действия', 'error');
    });
}

// Функции для работы с модальным окном создания нового действия
function showNewActionModal() {
    // Проверяем права пользователя на создание действий
    if (!canChangeStatus) {
        showNotification('У вас нет прав на создание действий', 'error');
        return;
    }
    
    const modal = document.getElementById('newActionModal');
    const form = document.getElementById('newActionForm');
    
    // Очищаем форму
    form.reset();
    
    // Устанавливаем минимальную дату (завтра)
    const tomorrow = new Date();
    tomorrow.setDate(tomorrow.getDate() + 1);
    const tomorrowStr = tomorrow.toISOString().split('T')[0];
    document.getElementById('actionExpiredAt').min = tomorrowStr;
    
    // Сбрасываем счетчик символов
    document.getElementById('charCount').textContent = '0';
    
    // Показываем модальное окно
    modal.style.display = 'block';
    
    // Фокусируемся на поле описания
    document.getElementById('actionDescription').focus();
}

function closeNewActionModal() {
    const modal = document.getElementById('newActionModal');
    modal.style.display = 'none';
}

function saveNewAction() {
    const form = document.getElementById('newActionForm');
    const expiredAt = document.getElementById('actionExpiredAt').value;
    const action = document.getElementById('actionDescription').value.trim();
    
    if (!expiredAt) {
        alert('Пожалуйста, выберите дату истечения срока');
        return;
    }
    
    if (!action) {
        alert('Пожалуйста, опишите задачу');
        return;
    }
    
    // Показываем индикатор загрузки
    const saveButton = document.querySelector('#newActionModal .btn-primary');
    const originalText = saveButton.textContent;
    saveButton.textContent = 'Создание...';
    saveButton.disabled = true;
    
    // Отправляем запрос на сервер
    fetch(`/product/${currentProductId}/actions`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            action: action,
            expired_at: expiredAt
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Закрываем модальное окно
            closeNewActionModal();
            
            // Обновляем отображение действий
            updateActionsDisplay(data.action);
            
            // Обновляем лог событий, если получен новый лог
            if (data.log) {
                updateEventsLog(data.log);
            }
            
            // Показываем уведомление об успехе
            showNotification('Действие успешно создано', 'success');
        } else {
            throw new Error(data.message || 'Ошибка при создании действия');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Ошибка при создании действия', 'error');
    })
    .finally(() => {
        // Возвращаем оригинальное состояние кнопки
        saveButton.textContent = originalText;
        saveButton.disabled = false;
    });
}

function updateActionsDisplay(newAction) {
    // Обновляем блок действий
    const actionInfo = document.querySelector('.action-info');
    if (actionInfo) {
        const actionDate = actionInfo.querySelector('.action-date');
        const actionDescription = actionInfo.querySelector('.action-description');
        const actionButtons = actionInfo.querySelector('.action-buttons');
        
        if (actionDate && actionDescription) {
            // Форматируем дату
            const expiredDate = new Date(newAction.expired_at);
            const formattedDate = expiredDate.toLocaleDateString('ru-RU');
            
            // Обновляем содержимое
            actionDate.innerHTML = `
                <span class="label">Дата:</span>
                <span class="value">${formattedDate}</span>
            `;
            
            actionDescription.innerHTML = `
                <span class="label">Что требуется сделать:</span>
                <p>${newAction.action}</p>
            `;
            
            // Убеждаемся, что кнопки на месте
            if (!actionButtons.querySelector('.btn-secondary')) {
                actionButtons.innerHTML = `
                    <button class="btn btn-primary" onclick="showNewActionModal()">Задать новое действие</button>
                    <button class="btn btn-secondary" onclick="showActionsModal()">Подробнее</button>
                `;
            }
        }
    }
}

function updateEventsLog(log) {
    const eventsList = document.querySelector('.events-list');
    
    // Создаем новый элемент лога
    const eventItem = document.createElement('div');
    eventItem.className = 'event-item';
    
    const eventHeader = document.createElement('div');
    eventHeader.className = 'event-header';
    
    const eventType = document.createElement('span');
    eventType.className = 'event-type';
    eventType.textContent = log.type ? log.type.name : 'Неизвестный тип';
    if (log.type && log.type.color) {
        eventType.style.backgroundColor = log.type.color;
    }
    
    const eventDate = document.createElement('span');
    eventDate.className = 'event-date';
    eventDate.textContent = new Date(log.created_at).toLocaleString('ru-RU');
    
    eventHeader.appendChild(eventType);
    eventHeader.appendChild(eventDate);
    
    const eventContent = document.createElement('div');
    eventContent.className = 'event-content';
    const contentParagraph = document.createElement('p');
    contentParagraph.textContent = log.log;
    eventContent.appendChild(contentParagraph);
    
    const eventFooter = document.createElement('div');
    eventFooter.className = 'event-footer';
    const footerSpan = document.createElement('span');
    footerSpan.textContent = `Создал: ${log.user ? log.user.name : 'Система'}`;
    eventFooter.appendChild(footerSpan);
    
    eventItem.appendChild(eventHeader);
    eventItem.appendChild(eventContent);
    eventItem.appendChild(eventFooter);
    
    // Добавляем новый лог в начало списка
    if (eventsList.firstChild) {
        eventsList.insertBefore(eventItem, eventsList.firstChild);
    } else {
        eventsList.appendChild(eventItem);
    }
    
    // Удаляем сообщение об отсутствии логов, если оно есть
    const noLogsMessage = eventsList.querySelector('.event-item p[style*="color: #666"]');
    if (noLogsMessage) {
        noLogsMessage.parentElement.parentElement.remove();
    }
}

// Обработчик для счетчика символов
document.addEventListener('DOMContentLoaded', function() {
    const actionDescription = document.getElementById('actionDescription');
    const charCount = document.getElementById('charCount');
    
    if (actionDescription && charCount) {
        actionDescription.addEventListener('input', function() {
            charCount.textContent = this.value.length;
        });
    }
    
    // Устанавливаем цвета для типов событий
    const eventTypes = document.querySelectorAll('.event-type[data-color]');
    eventTypes.forEach(function(element) {
        const color = element.getAttribute('data-color');
        element.style.backgroundColor = color;
    });
});

// Обновляем обработчики закрытия модальных окон - ОТКЛЮЧЕНО
// document.addEventListener('click', function(event) {
//     const logsModal = document.getElementById('logsHistoryModal');
//     const actionsModal = document.getElementById('actionsModal');
//     const newActionModal = document.getElementById('newActionModal');
//     const productStatusModal = document.getElementById('productStatusCommentModal');
//     
//     if (event.target === logsModal) {
//         closeLogsHistory();
//     }
//     
//     if (event.target === actionsModal) {
//         closeActionsModal();
//     }
//     
//     if (event.target === newActionModal) {
//         closeNewActionModal();
//     }
//     
//     if (event.target === productStatusModal) {
//         cancelProductStatusChange();
//     }
// });

// Обновляем обработчики закрытия по Escape
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        const logsModal = document.getElementById('logsHistoryModal');
        const actionsModal = document.getElementById('actionsModal');
        const newActionModal = document.getElementById('newActionModal');
        const productStatusModal = document.getElementById('productStatusCommentModal');
        
        if (logsModal && logsModal.style.display === 'block') {
            closeLogsHistory();
        }
        
        if (actionsModal && actionsModal.style.display === 'block') {
            closeActionsModal();
        }
        
        if (newActionModal && newActionModal.style.display === 'block') {
            closeNewActionModal();
        }
        
        if (productStatusModal && productStatusModal.style.display === 'block') {
            cancelProductStatusChange();
        }
    }
});

// Функции для редактирования блока основной информации
function editMainInfoBlock() {
    // Скрываем контент и показываем формы редактирования
    document.getElementById('main_info_content').style.display = 'none';
    document.getElementById('main_info_edit').style.display = 'block';
    
    // Показываем кнопки действий
    document.getElementById('main_info_actions').style.display = 'flex';
    
    // Скрываем кнопку редактирования
    document.querySelector('#main_info_actions').closest('.info-block').querySelector('.edit-comment-btn').style.display = 'none';
    
    // Инициализируем TreeSelect для категорий
    initializeTreeSelectForEdit();
}

function cancelMainInfoEdit() {
    // Восстанавливаем оригинальные значения
    const categorySelect = document.getElementById('category_id');
    const productAddressInput = document.getElementById('product_address_input');
    
    if (categorySelect) {
        const originalCategory = categorySelect.getAttribute('data-original') || '';
        categorySelect.value = originalCategory;
    }
    
    if (productAddressInput) {
        const originalAddress = productAddressInput.getAttribute('data-original') || '';
        productAddressInput.value = originalAddress;
    }
    
    // Показываем контент и скрываем формы редактирования
    document.getElementById('main_info_content').style.display = 'block';
    document.getElementById('main_info_edit').style.display = 'none';
    
    // Скрываем кнопки действий
    document.getElementById('main_info_actions').style.display = 'none';
    
    // Показываем кнопку редактирования
    document.querySelector('#main_info_actions').closest('.info-block').querySelector('.edit-comment-btn').style.display = 'flex';
}

function saveMainInfoBlock() {
    // Собираем данные
    const mainInfoData = {
        category_id: document.getElementById('category_id').value || null,
        product_address: document.getElementById('product_address_input').value.trim()
    };
    
    // Показываем индикатор загрузки
    const saveBtn = document.querySelector('#main_info_actions .btn-primary');
    const originalText = saveBtn.textContent;
    saveBtn.textContent = 'Сохранение...';
    saveBtn.disabled = true;
    
    // Отправляем AJAX запрос
    fetch(`/product/{{ $product->id }}/main-info`, {
        method: 'PATCH',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify(mainInfoData)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Обновляем отображение
            updateMainInfoDisplay(mainInfoData);
            
            // Сохраняем новые значения как оригинальные
            saveMainInfoOriginals(mainInfoData);
            
            // Скрываем формы редактирования
            cancelMainInfoEdit();
            
            // Показываем уведомление об успехе
            showNotification('Основная информация успешно обновлена', 'success');
        } else {
            throw new Error(data.message || 'Ошибка при сохранении');
        }
    })
    .catch(error => {
        console.error('Ошибка:', error);
        showNotification('Ошибка при сохранении основной информации', 'error');
    })
    .finally(() => {
        // Восстанавливаем кнопку
        saveBtn.textContent = originalText;
        saveBtn.disabled = false;
    });
}

// Функции обновления отображения основной информации
function updateMainInfoDisplay(mainInfoData) {
    // Обновляем отображение категории
    const categoryValue = document.querySelector('#main_info_content .info-item:first-child .value');
    if (mainInfoData.category_id) {
        const categorySelect = document.getElementById('category_id');
        const selectedOption = categorySelect.options[categorySelect.selectedIndex];
        const categoryName = selectedOption ? selectedOption.textContent : 'Не указана';
        categoryValue.textContent = categoryName;
    } else {
        categoryValue.textContent = 'Не указана';
    }
    
    // Обновляем отображение адреса станка
    const addressValue = document.querySelector('#main_info_content .info-item:nth-child(3) .value');
    if (mainInfoData.product_address) {
        addressValue.textContent = mainInfoData.product_address;
    } else {
        addressValue.textContent = 'Не указан';
    }
}

// Функции сохранения оригинальных значений
function saveMainInfoOriginals(mainInfoData) {
    const categorySelect = document.getElementById('category_id');
    const productAddressInput = document.getElementById('product_address_input');
    
    if (categorySelect) {
        categorySelect.setAttribute('data-original', mainInfoData.category_id || '');
    }
    
    if (productAddressInput) {
        productAddressInput.setAttribute('data-original', mainInfoData.product_address || '');
    }
}

// Функция инициализации TreeSelect для редактирования
function initializeTreeSelectForEdit() {
    // Используем данные категорий, которые уже переданы на страницу
    const categoriesData = @json(\App\Models\ProductCategories::all());
    initializeTreeSelect('category_treeselect', 'category_id', categoriesData);
}

// Функция инициализации TreeSelect (аналогично PrimeVue TreeSelect)
function initializeTreeSelect(treeselectId, selectId, categories) {
    const treeselectInput = document.getElementById(treeselectId);
    const treeselectDropdown = document.getElementById(treeselectId + '_dropdown');
    const treeselectTree = document.getElementById(treeselectId + '_tree');
    const treeselectSearch = document.getElementById(treeselectId + '_search');
    const hiddenSelect = document.getElementById(selectId);
    
    if (!treeselectInput || !treeselectDropdown || !treeselectTree || !hiddenSelect) return;
    
    let isOpen = false;
    let focusedIndex = -1;
    let treeNodes = [];
    
    // Строим дерево из категорий
    function buildTree(categories, parentId = 0) {
        const tree = [];
        categories.forEach(category => {
            if (category.parent_id == parentId) {
                const node = {
                    id: category.id,
                    name: category.name,
                    parent_id: category.parent_id,
                    children: buildTree(categories, category.id),
                    expanded: false
                };
                tree.push(node);
            }
        });
        return tree;
    }
    
    // Функция для проверки, является ли категория выбираемой (без подкатегорий)
    function isSelectableCategory(categoryId) {
        return !categories.some(cat => cat.parent_id == categoryId);
    }
    
    // Создаем HTML для узла дерева
    function createNodeHTML(node, level = 0) {
        const hasChildren = node.children && node.children.length > 0;
        const indent = level * 20;
        
        // Проверяем, является ли категория выбираемой (без подкатегорий)
        const isSelectable = isSelectableCategory(node.id);
        
        let html = `<div class="treeselect-node ${!isSelectable ? 'disabled' : ''}" data-id="${node.id}" data-level="${level}" data-selectable="${isSelectable}" tabindex="0">`;
        
        // Отступ
        html += `<div class="treeselect-indent" style="width: ${indent}px;"></div>`;
        
        // Кнопка разворачивания
        if (hasChildren) {
            html += `<div class="treeselect-toggle" data-id="${node.id}">
                <svg class="treeselect-toggle-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="9,18 15,12 9,6"></polyline>
                </svg>
            </div>`;
        } else {
            html += `<div class="treeselect-indent" style="width: 16px;"></div>`;
        }
        
        // Название узла
        html += `<div class="treeselect-label">${node.name}${!isSelectable ? ' <span style="color: #6c757d; font-size: 12px;">(недоступно для выбора)</span>' : ''}</div>`;
        html += `</div>`;
        
        // Дочерние узлы
        if (hasChildren) {
            html += `<div class="treeselect-children" data-parent="${node.id}">`;
            node.children.forEach(child => {
                html += createNodeHTML(child, level + 1);
            });
            html += `</div>`;
        }
        
        return html;
    }
    
    // Обновляем дерево
    function updateTree(filteredCategories = null) {
        const categoriesToUse = filteredCategories || categories;
        treeNodes = buildTree(categoriesToUse);
        
        let html = '';
        treeNodes.forEach(node => {
            html += createNodeHTML(node);
        });
        
        treeselectTree.innerHTML = html;
        attachNodeEvents();
    }
    
    // Привязываем события к узлам
    function attachNodeEvents() {
        // События для узлов
        const nodes = treeselectTree.querySelectorAll('.treeselect-node');
        nodes.forEach((node, index) => {
            const nodeId = node.dataset.id;
            
            // Проверяем, является ли категория выбираемой (без подкатегорий)
            const isSelectable = isSelectableCategory(nodeId);
            
            // Добавляем события только для выбираемых узлов
            if (isSelectable) {
                node.addEventListener('click', function(e) {
                    e.stopPropagation();
                    
                    const nodeLabel = this.querySelector('.treeselect-label');
                    // Получаем текст без HTML-разметки
                    const nodeName = nodeLabel.textContent || nodeLabel.innerText;
                    selectNode(nodeId, nodeName);
                });
                
                node.addEventListener('keydown', function(e) {
                    if (e.key === 'Enter' || e.key === ' ') {
                        e.preventDefault();
                        
                        const nodeLabel = this.querySelector('.treeselect-label');
                        const nodeName = nodeLabel.textContent || nodeLabel.innerText;
                        selectNode(nodeId, nodeName);
                    }
                });
            }
            
            // События для кнопок разворачивания
            const toggle = node.querySelector('.treeselect-toggle');
            if (toggle) {
                toggle.addEventListener('click', function(e) {
                    e.stopPropagation();
                    toggleNode(nodeId);
                });
            }
        });
    }
    
    // Функция выбора узла
    function selectNode(nodeId, nodeName) {
        // Обновляем скрытый select
        hiddenSelect.value = nodeId;
        
        // Обновляем отображение
        const placeholder = treeselectInput.querySelector('.treeselect-placeholder');
        const value = treeselectInput.querySelector('.treeselect-value');
        
        if (placeholder) {
            placeholder.style.display = 'none';
        }
        
        if (value) {
            value.textContent = nodeName;
            value.style.display = 'block';
        } else {
            const newValue = document.createElement('span');
            newValue.className = 'treeselect-value';
            newValue.textContent = nodeName;
            treeselectInput.insertBefore(newValue, treeselectInput.querySelector('.treeselect-arrow'));
        }
        
        // Закрываем dropdown
        closeDropdown();
        
        // Убираем выделение со всех узлов
        treeselectTree.querySelectorAll('.treeselect-node').forEach(node => {
            node.classList.remove('selected');
        });
        
        // Выделяем выбранный узел
        const selectedNode = treeselectTree.querySelector(`[data-id="${nodeId}"]`);
        if (selectedNode) {
            selectedNode.classList.add('selected');
        }
    }
    
    // Функция разворачивания/сворачивания узла
    function toggleNode(nodeId) {
        const node = treeselectTree.querySelector(`[data-id="${nodeId}"]`);
        const children = treeselectTree.querySelector(`[data-parent="${nodeId}"]`);
        const toggle = node.querySelector('.treeselect-toggle');
        
        if (children && toggle) {
            const isExpanded = children.classList.contains('expanded');
            
            if (isExpanded) {
                children.classList.remove('expanded');
                toggle.classList.remove('expanded');
            } else {
                children.classList.add('expanded');
                toggle.classList.add('expanded');
            }
        }
    }
    
    // Функция открытия dropdown
    function openDropdown() {
        if (isOpen) return;
        
        treeselectDropdown.classList.add('active');
        treeselectInput.classList.add('active');
        isOpen = true;
        
        // Фокусируемся на поле поиска
        if (treeselectSearch) {
            treeselectSearch.focus();
        }
    }
    
    // Функция закрытия dropdown
    function closeDropdown() {
        if (!isOpen) return;
        
        treeselectDropdown.classList.remove('active');
        treeselectInput.classList.remove('active');
        isOpen = false;
        focusedIndex = -1;
    }
    
    // События для input
    treeselectInput.addEventListener('click', function(e) {
        e.preventDefault();
        if (isOpen) {
            closeDropdown();
        } else {
            openDropdown();
        }
    });
    
    treeselectInput.addEventListener('keydown', function(e) {
        if (e.key === 'Enter' || e.key === ' ') {
            e.preventDefault();
            if (isOpen) {
                closeDropdown();
            } else {
                openDropdown();
            }
        } else if (e.key === 'Escape') {
            closeDropdown();
        }
    });
    
    // События для поиска
    if (treeselectSearch) {
        treeselectSearch.addEventListener('input', function(e) {
            const searchTerm = e.target.value.toLowerCase();
            
            if (searchTerm === '') {
                updateTree();
            } else {
                const filteredCategories = categories.filter(category => 
                    category.name.toLowerCase().includes(searchTerm)
                );
                updateTree(filteredCategories);
            }
        });
    }
    
    // Закрытие dropdown при клике вне его
    document.addEventListener('click', function(e) {
        if (!treeselectInput.contains(e.target) && !treeselectDropdown.contains(e.target)) {
            closeDropdown();
        }
    });
    
    // Инициализация с текущим значением
    const currentValue = hiddenSelect.value;
    if (currentValue) {
        const selectedCategory = categories.find(cat => cat.id == currentValue);
        if (selectedCategory) {
            selectNode(selectedCategory.id, selectedCategory.name);
        }
    }
    
    // Инициализируем дерево
    updateTree();
}

// Инициализация оригинальных значений для основной информации
document.addEventListener('DOMContentLoaded', function() {
    // Инициализация оригинальных значений для основной информации
    const categorySelect = document.getElementById('category_id');
    const productAddressInput = document.getElementById('product_address_input');
    
    if (categorySelect) {
        categorySelect.setAttribute('data-original', categorySelect.value);
    }
    
    if (productAddressInput) {
        productAddressInput.setAttribute('data-original', productAddressInput.value);
    }
});
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

<!-- Модальное окно для истории логов -->
<div id="logsHistoryModal" class="modal" style="display: none;">
    <div class="modal-content logs-history-modal">
        <div class="modal-header">
            <h3>История логов товара</h3>
            <span class="close" onclick="closeLogsHistory()">&times;</span>
        </div>
        <div class="modal-body">
            <div id="logsHistoryContent">
                <div class="loading-spinner">
                    <div class="spinner"></div>
                    <p>Загрузка логов...</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Модальное окно для действий -->
<div id="actionsModal" class="modal" style="display: none;">
    <div class="modal-content actions-modal">
        <div class="modal-header">
            <h3>Список необходимых действий</h3>
            <span class="close" onclick="closeActionsModal()">&times;</span>
        </div>
        <div class="modal-body">
            <div id="actionsList">
                <div class="loading-spinner">
                    <div class="spinner"></div>
                    <p>Загрузка действий...</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Модальное окно для комментария к выполненному действию -->
<div id="actionCommentModal" class="modal" style="display: none;">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Завершение действия</h3>
            <span class="close" onclick="closeActionCommentModal()">&times;</span>
        </div>
        <div class="modal-body">
            <p>Оставьте комментарий о выполнении действия.</p>
            <div class="form-group">
                <label for="actionComment">Комментарий:</label>
                <textarea id="actionComment" rows="4" placeholder="Введите комментарий..." required></textarea>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" onclick="closeActionCommentModal()">Отмена</button>
            <button type="button" class="btn btn-primary" onclick="saveActionComment()">Сохранить</button>
        </div>
    </div>
</div>

<!-- Модальное окно для комментария при смене статуса товара -->
<div id="productStatusCommentModal" class="modal" style="display: none;">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Смена статуса товара</h3>
            <span class="close" onclick="cancelProductStatusChange()">&times;</span>
        </div>
        <div class="modal-body">
            <p>Оставьте комментарий по причине смены статуса товара.</p>
            <div class="form-group">
                <label for="productStatusComment">Комментарий:</label>
                <textarea id="productStatusComment" rows="4" placeholder="Введите комментарий..." required></textarea>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" onclick="cancelProductStatusChange()">Отмена</button>
            <button type="button" class="btn btn-primary" onclick="saveProductStatusChange()">Сохранить</button>
        </div>
    </div>
</div>

<!-- Модальное окно для создания нового действия -->
<div id="newActionModal" class="modal" style="display: none;">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Создать новое действие</h3>
            <span class="close" onclick="closeNewActionModal()">&times;</span>
        </div>
        <div class="modal-body">
            <form id="newActionForm">
                <div class="form-group">
                    <label for="actionExpiredAt">Дата истечения срока задачи:</label>
                    <input type="date" id="actionExpiredAt" name="expired_at" required min="{{ date('Y-m-d', strtotime('+1 day')) }}">
                </div>
                <div class="form-group">
                    <label for="actionDescription">Что требуется сделать:</label>
                    <textarea id="actionDescription" name="action" rows="4" placeholder="Опишите задачу..." required maxlength="1000"></textarea>
                    <div class="char-counter">
                        <span id="charCount">0</span>/1000
                    </div>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" onclick="closeNewActionModal()">Отмена</button>
            <button type="button" class="btn btn-primary" onclick="saveNewAction()">Создать</button>
        </div>
    </div>
</div>

<script>
// Функция для скачивания всех медиафайлов товара
function downloadAllMedia() {
    const downloadBtn = document.getElementById('downloadMediaBtn');
    const btnText = downloadBtn.querySelector('.btn-text');
    const loadingSpinner = downloadBtn.querySelector('.loading-spinner');
    const originalText = btnText.textContent;
    
    // Показываем анимацию загрузки
    downloadBtn.disabled = true;
    btnText.textContent = 'Создание архива...';
    loadingSpinner.style.display = 'block';
    
    // Функция для восстановления состояния кнопки
    function resetButton() {
        downloadBtn.disabled = false;
        btnText.textContent = originalText;
        loadingSpinner.style.display = 'none';
    }
    
    // Функция для обработки ошибок
    function handleError(message) {
        resetButton();
        showNotification(message, 'error');
    }
    
    // Проверяем доступность сервера и создаем архив
    fetch('{{ route("products.download-media", $product) }}', {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => {
        if (!response.ok) {
            if (response.status === 403) {
                throw new Error('У вас нет прав для скачивания медиафайлов этого товара');
            } else if (response.status === 404) {
                throw new Error('У товара нет медиафайлов для скачивания');
            } else {
                throw new Error('Ошибка при создании архива');
            }
        }
        
        // Если ответ успешный, начинаем скачивание
        btnText.textContent = 'Скачивание...';
        
        // Создаем скрытую ссылку для скачивания
        const link = document.createElement('a');
        link.href = '{{ route("products.download-media", $product) }}';
        link.download = '';
        link.style.display = 'none';
        document.body.appendChild(link);
        
        // Отслеживаем загрузку файла
        let downloadStarted = false;
        let downloadCompleted = false;
        
        // Функция для проверки завершения загрузки
        function checkDownloadComplete() {
            if (downloadCompleted) {
                // Восстанавливаем кнопку
                resetButton();
                
                // Удаляем ссылку
                document.body.removeChild(link);
                
                // Показываем уведомление об успехе
                showNotification('Архив с медиафайлами успешно скачан', 'success');
            } else if (!downloadStarted) {
                // Если загрузка еще не началась, проверяем еще раз через 100мс
                setTimeout(checkDownloadComplete, 100);
            }
        }
        
        // Обработчик начала загрузки
        link.addEventListener('click', function() {
            downloadStarted = true;
        });
        
        // Обработчик завершения загрузки
        window.addEventListener('focus', function() {
            if (downloadStarted && !downloadCompleted) {
                downloadCompleted = true;
                checkDownloadComplete();
            }
        });
        
        // Запускаем скачивание
        link.click();
        
        // Запускаем проверку завершения загрузки
        checkDownloadComplete();
        
        // Резервный таймер на случай, если событие focus не сработает
        setTimeout(function() {
            if (!downloadCompleted) {
                downloadCompleted = true;
                checkDownloadComplete();
            }
        }, 10000); // 10 секунд таймаут
    })
    .catch(error => {
        console.error('Ошибка при скачивании:', error);
        handleError(error.message || 'Произошла ошибка при скачивании медиафайлов');
    });
}
</script>

@endsection
