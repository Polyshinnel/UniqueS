@extends('layouts.layout')

@section('title', $advertisement->title  ?? 'Обьявление')

@section('header-title')
    <h1 class="header-title">{{ $advertisement->title }}</h1>
@endsection

@section('content')
<!-- CSRF токен для AJAX запросов -->
<meta name="csrf-token" content="{{ csrf_token() }}">
<!-- Подключение Quill.js -->
<link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
<script src="https://cdn.quilljs.com/1.3.6/quill.min.js"></script>
<div class="advertisement-item-container">
    <div class="advertisement-header">
        <div class="breadcrumb">
            <a href="{{ route('advertisements.index') }}">Объявления</a> / {{ $advertisement->title }}
        </div>
        <div class="advertisement-header-actions">
            <div class="advertisement-title-container">
                <h1 class="advertisement-title" id="title_content">{{ $advertisement->title }}</h1>
                <div class="advertisement-title-edit" id="title_edit" style="display: none;">
                    <input type="text" id="title_input" class="title-input" value="{{ $advertisement->title }}" maxlength="255">
                    <div class="title-edit-buttons">
                        <button type="button" class="btn btn-success btn-sm" onclick="saveTitle()">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polyline points="20,6 9,17 4,12"></polyline>
                            </svg>
                            Сохранить
                        </button>
                        <button type="button" class="btn btn-secondary btn-sm" onclick="cancelTitleEdit()">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <line x1="18" y1="6" x2="6" y2="18"></line>
                                <line x1="6" y1="6" x2="18" y2="18"></line>
                            </svg>
                            Отмена
                        </button>
                    </div>
                </div>
                <button type="button" class="btn btn-outline-primary btn-sm edit-title-btn" onclick="editTitle()" title="Редактировать заголовок">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                        <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                    </svg>
                </button>
            </div>
        </div>
        <div class="advertisement-status">
            <div class="status-selector">
                <div class="status-badge clickable" onclick="toggleAdvertisementStatusDropdown()" style="background-color: {{ $advertisement->status?->color ?? '#6c757d' }}; color: white;">
                    {{ $advertisement->status_name }}
                    <svg class="dropdown-arrow" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="6,9 12,15 18,9"></polyline>
                    </svg>
                </div>
                <div class="status-dropdown" id="advertisementStatusDropdown">
                    @foreach($advertisementStatuses as $status)
                        <div class="status-option" onclick="changeAdvertisementStatus({{ $status->id }}, '{{ $status->name }}', '{{ $status->color }}')">
                            <div class="status-badge" style="background-color: {{ $status->color }}; color: white;">{{ $status->name }}</div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <div class="advertisement-content">
        <!-- Блок с медиафайлами -->
        <div class="advertisement-media-section">
            @if($advertisement->mediaOrdered->count() > 0)
                <div class="media-gallery">
                    <div class="main-image-container">
                        @php
                            // Получаем главное изображение
                            $mainImage = $advertisement->getMainImage();
                            $mainImageIndex = 0;
                            
                            // Если главное изображение не найдено, берем первое изображение из медиафайлов объявления
                            if (!$mainImage) {
                                $mainImage = $advertisement->mediaOrdered->where('file_type', 'image')->first();
                            } else {
                                // Определяем индекс главного изображения для галереи
                                // Если главное изображение из медиафайлов объявления
                                $mainImageIndex = $advertisement->mediaOrdered->search(function($item) use ($mainImage) {
                                    return $item->id == $mainImage->id;
                                });
                                
                                // Если не найдено в медиафайлах объявления, значит это медиафайл товара
                                if ($mainImageIndex === false) {
                                    $mainImageIndex = 0; // Показываем первое изображение из объявления
                                }
                            }
                        @endphp
                        @if($mainImage)
                            <img id="mainImage" src="{{ asset('storage/' . $mainImage->file_path) }}" 
                                 alt="{{ $advertisement->title }}" class="main-image" 
                                 onclick="openGallery({{ $mainImageIndex }})">
                            @if(\App\Helpers\AdvertisementHelper::canEditAdvertisement($advertisement))
                            <div style="position: absolute; top: 10px; right: 10px; display: flex; gap: 4px; opacity: 0; transition: opacity 0.2s ease; z-index: 10;">
                                @if($mainImage->file_type === 'image')
                                <button style="width: 28px; height: 28px; border: none; border-radius: 50%; background: rgba(40, 167, 69, 1); color: white; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: all 0.2s ease; box-shadow: 0 0 0 2px rgba(40, 167, 69, 0.5);" 
                                        onclick="setAsMainImage({{ $mainImage->id }}, {{ $mainImageIndex }})" 
                                        title="Главное изображение">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M9 12l2 2 4-4"/>
                                        <circle cx="12" cy="12" r="10"/>
                                    </svg>
                                </button>
                                @endif
                                <button style="width: 28px; height: 28px; border: none; border-radius: 50%; background: rgba(220, 53, 69, 0.9); color: white; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: all 0.2s ease;" 
                                        onclick="deleteMedia({{ $mainImage->id }}, '{{ $mainImage->file_type }}')" 
                                        title="Удалить медиафайл">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <polyline points="3,6 5,6 21,6"></polyline>
                                        <path d="m19,6v14a2,2 0 0,1 -2,2H7a2,2 0 0,1 -2,-2V6m3,0V4a2,2 0 0,1 2,-2h4a2,2 0 0,1 2,2v2"></path>
                                        <line x1="10" y1="11" x2="10" y2="17"></line>
                                        <line x1="14" y1="11" x2="14" y2="17"></line>
                                    </svg>
                                </button>
                            </div>
                            @endif
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
                                <div class="thumbnail {{ $index === $mainImageIndex ? 'active' : '' }}" 
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
                                    @if(\App\Helpers\AdvertisementHelper::canEditAdvertisement($advertisement))
                                    <div style="position: absolute; top: 3px; right: 3px; display: flex; gap: 4px; opacity: 0; transition: opacity 0.2s ease; z-index: 10;">
                                        @if($media->file_type === 'image')
                                        <button style="width: 20px; height: 20px; border: none; border-radius: 50%; background: {{ $index === $mainImageIndex ? 'rgba(40, 167, 69, 1)' : 'rgba(40, 167, 69, 0.9)' }}; color: white; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: all 0.2s ease; {{ $index === $mainImageIndex ? 'box-shadow: 0 0 0 2px rgba(40, 167, 69, 0.5);' : '' }}" 
                                                onclick="event.stopPropagation(); setAsMainImage({{ $media->id }}, {{ $index }})" 
                                                title="{{ $index === $mainImageIndex ? 'Главное изображение' : 'Сделать главным' }}">
                                            <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <path d="M9 12l2 2 4-4"/>
                                                <circle cx="12" cy="12" r="10"/>
                                            </svg>
                                        </button>
                                        @endif
                                        <button style="width: 20px; height: 20px; border: none; border-radius: 50%; background: rgba(220, 53, 69, 0.9); color: white; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: all 0.2s ease;" 
                                                onclick="event.stopPropagation(); deleteMedia({{ $media->id }}, '{{ $media->file_type }}')" 
                                                title="Удалить медиафайл">
                                            <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <polyline points="3,6 5,6 21,6"></polyline>
                                                <path d="m19,6v14a2,2 0 0,1 -2,2H7a2,2 0 0,1 -2,-2V6m3,0V4a2,2 0 0,1 2,-2h4a2,2 0 0,1 2,2v2"></path>
                                                <line x1="10" y1="11" x2="10" y2="17"></line>
                                                <line x1="14" y1="11" x2="14" y2="17"></line>
                                            </svg>
                                        </button>
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

             @if(\App\Helpers\AdvertisementHelper::canEditAdvertisement($advertisement))
             <div class="media-controls">
                 <button class="btn btn-success btn-full-width" onclick="showMediaUploadModal()">
                     <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin-right: 8px;">
                         <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                         <polyline points="14,2 14,8 20,8"></polyline>
                         <line x1="16" y1="13" x2="8" y2="13"></line>
                         <line x1="16" y1="17" x2="8" y2="17"></line>
                         <polyline points="10,9 9,9 8,9"></polyline>
                     </svg>
                     <span>Добавить фото/видео</span>
                 </button>
             </div>
             @endif

            <!-- Блок действий и событий -->
            @if(\App\Helpers\AdvertisementHelper::canEditAdvertisement($advertisement))
            <div class="advertisement-actions-section">
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
                                <button class="btn btn-primary" onclick="showNewActionModal()">Задать новое действие</button>
                                <button class="btn btn-secondary" onclick="showActionsModal()">Подробнее</button>
                            </div>
                        @else
                            <div class="action-description">
                                <p style="color: #666; font-style: italic;">Нет активных действий</p>
                            </div>
                            <div class="action-buttons">
                                <button class="btn btn-primary" onclick="showNewActionModal()">Задать новое действие</button>
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
                            @if(\App\Helpers\AdvertisementHelper::canViewSupplierInfo($advertisement))
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
                            @else
                                <span style="color: #999; font-style: italic;">Скрыто</span>
                            @endif
                        </span>
                    </div>
                    <div class="info-item">
                        <span class="label">Организация:</span>
                        <span class="value">
                            @if(\App\Helpers\AdvertisementHelper::canViewSupplierInfo($advertisement))
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
                            @else
                                <span style="color: #999; font-style: italic;">Скрыто</span>
                            @endif
                        </span>
                    </div>
                    <div class="info-item">
                        <span class="label">Регион:</span>
                        <span class="value">
                            @if($advertisement->product && $advertisement->product->company && $advertisement->product->company->region)
                                {{ $advertisement->product->company->region->name }}
                            @else
                                Не указан
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
                    <div class="info-item">
                        <span class="label">Состояние станка:</span>
                        <span class="value">{{ $advertisement->productState?->name ?? 'Не указано' }}</span>
                    </div>
                    <div class="info-item">
                        <span class="label">Доступность станка:</span>
                        <span class="value">{{ $advertisement->productAvailable?->name ?? 'Не указано' }}</span>
                    </div>
                    @if($advertisement->published_at)
                    <div class="info-item">
                        <span class="label">Дата публикации:</span>
                        <span class="value">{{ $advertisement->published_at->format('d.m.Y H:i') }}</span>
                    </div>
                    @endif
                </div>
            </div>

            @if($advertisement->main_characteristics || $advertisement->complectation || true)
                <div class="info-block">
                    <div class="block-header">
                        <h3>Характеристики</h3>
                        @if(\App\Helpers\AdvertisementHelper::canEditAdvertisement($advertisement))
                        <button class="edit-comment-btn" onclick="editCharacteristicsBlock()">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"></path>
                            </svg>
                            Редактировать
                        </button>
                        @endif
                    </div>
                    
                    <!-- Основные характеристики -->
                    <div class="chars-item">
                        <strong>Основные характеристики:</strong>
                        <div class="chars-content" id="main_characteristics_content">
                            @if($advertisement->main_characteristics)
                                <p>{{ $advertisement->main_characteristics }}</p>
                            @else
                                <p class="no-comment">Основные характеристики не указаны</p>
                            @endif
                        </div>
                        <div class="chars-edit" id="main_characteristics_edit" style="display: none;">
                            <textarea class="form-control" id="main_characteristics_textarea" rows="4" 
                                      data-original="{{ $advertisement->main_characteristics ?? '' }}"
                                      placeholder="Введите основные характеристики">{{ $advertisement->main_characteristics ?? '' }}</textarea>
                        </div>
                    </div>
                    
                    <!-- Комплектация -->
                    <div class="chars-item">
                        <strong>Комплектация:</strong>
                        <div class="chars-content" id="complectation_content">
                            @if($advertisement->complectation)
                                <p>{{ $advertisement->complectation }}</p>
                            @else
                                <p class="no-comment">Комплектация не указана</p>
                            @endif
                        </div>
                        <div class="chars-edit" id="complectation_edit" style="display: none;">
                            <textarea class="form-control" id="complectation_textarea" rows="4" 
                                      data-original="{{ $advertisement->complectation ?? '' }}"
                                      placeholder="Введите комплектацию">{{ $advertisement->complectation ?? '' }}</textarea>
                        </div>
                    </div>
                    
                    <!-- Кнопки действий для блока характеристик -->
                    <div class="chars-actions" id="characteristics_actions" style="display: none;">
                        <button class="btn btn-primary" onclick="saveCharacteristicsBlock()">Сохранить</button>
                        <button class="btn btn-secondary" onclick="cancelCharacteristicsEdit()">Отмена</button>
                    </div>
                </div>
            @endif

            @if($advertisement->main_info || true)
                <div class="info-block">
                    <div class="block-header">
                        <h3>Основная информация</h3>
                        @if(\App\Helpers\AdvertisementHelper::canEditAdvertisement($advertisement))
                        <button class="edit-comment-btn" onclick="editComment('main_info')">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"></path>
                            </svg>
                            Редактировать
                        </button>
                        @endif
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

            @if($advertisement->technical_characteristics || true)
                <div class="info-block">
                    <div class="block-header">
                        <h3>Технические характеристики</h3>
                        @if(\App\Helpers\AdvertisementHelper::canEditAdvertisement($advertisement))
                        <button class="edit-comment-btn" onclick="editComment('technical_characteristics')">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"></path>
                            </svg>
                            Редактировать
                        </button>
                        @endif
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


            @if($advertisement->check_data || true)
                <div class="info-block">
                    <div class="block-header">
                        <h3>Информация о проверке</h3>
                        @if(\App\Helpers\AdvertisementHelper::canEditAdvertisement($advertisement))
                        <button class="edit-comment-btn" onclick="editCheckBlock()">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"></path>
                            </svg>
                            Редактировать
                        </button>
                        @endif
                    </div>
                    @php
                        $checkStatus = null;
                        if (isset($advertisement->check_data['status_id'])) {
                            $checkStatus = \App\Models\ProductCheckStatuses::find($advertisement->check_data['status_id']);
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
                                <select class="form-control" id="check_status_select" data-original="{{ $advertisement->check_data['status_id'] ?? '' }}">
                                    <option value="">Выберите статус</option>
                                    @foreach($checkStatuses as $status)
                                        <option value="{{ $status->id }}" 
                                                {{ (isset($advertisement->check_data['status_id']) && $advertisement->check_data['status_id'] == $status->id) ? 'selected' : '' }}
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
                                @if(isset($advertisement->check_data['comment']) && $advertisement->check_data['comment'])
                                    <p>{{ $advertisement->check_data['comment'] }}</p>
                                @else
                                    <p class="no-comment">Комментарий к проверке не указан</p>
                                @endif
                            </div>
                            <div class="comment-edit" id="check_comment_edit" style="display: none;">
                                <textarea class="comment-textarea" id="check_comment_textarea" rows="3" data-original="{{ $advertisement->check_data['comment'] ?? '' }}">{{ $advertisement->check_data['comment'] ?? '' }}</textarea>
                            </div>
                        </div>
                        <!-- Кнопки действий для блока проверки -->
                        <div class="check-actions" id="check_actions" style="display: none;">
                            <button class="btn btn-primary" onclick="saveCheckBlock()">Сохранить</button>
                            <button class="btn btn-secondary" onclick="cancelCheckEdit()">Отмена</button>
                        </div>
                    </div>
                </div>
            @endif

            @if($advertisement->loading_data || true)
                <div class="info-block">
                    <div class="block-header">
                        <h3>Информация о погрузке</h3>
                        @if(\App\Helpers\AdvertisementHelper::canEditAdvertisement($advertisement))
                        <button class="edit-comment-btn" onclick="editLoadingBlock()">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"></path>
                            </svg>
                            Редактировать
                        </button>
                        @endif
                    </div>
                    @php
                        $loadingStatus = null;
                        if (isset($advertisement->loading_data['status_id'])) {
                            $loadingStatus = \App\Models\ProductInstallStatuses::find($advertisement->loading_data['status_id']);
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
                                <select class="form-control" id="loading_status_select" data-original="{{ $advertisement->loading_data['status_id'] ?? '' }}">
                                    <option value="">Выберите статус</option>
                                    @foreach($installStatuses as $status)
                                        <option value="{{ $status->id }}" 
                                                {{ (isset($advertisement->loading_data['status_id']) && $advertisement->loading_data['status_id'] == $status->id) ? 'selected' : '' }}>
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
                                @if(isset($advertisement->loading_data['comment']) && $advertisement->loading_data['comment'])
                                    <p>{{ $advertisement->loading_data['comment'] }}</p>
                                @else
                                    <p class="no-comment">Комментарий по погрузке не указан</p>
                                @endif
                            </div>
                            <div class="comment-edit" id="loading_comment_edit" style="display: none;">
                                <textarea class="comment-textarea" id="loading_comment_textarea" rows="3" data-original="{{ $advertisement->loading_data['comment'] ?? '' }}">{{ $advertisement->loading_data['comment'] ?? '' }}</textarea>
                            </div>
                        </div>
                        <!-- Кнопки действий для блока погрузки -->
                        <div class="loading-actions" id="loading_actions" style="display: none;">
                            <button class="btn btn-primary" onclick="saveLoadingBlock()">Сохранить</button>
                            <button class="btn btn-secondary" onclick="cancelLoadingEdit()">Отмена</button>
                        </div>
                    </div>
                </div>
            @endif

            @if($advertisement->removal_data || true)
                <div class="info-block">
                    <div class="block-header">
                        <h3>Информация о демонтаже</h3>
                        @if(\App\Helpers\AdvertisementHelper::canEditAdvertisement($advertisement))
                        <button class="edit-comment-btn" onclick="editRemovalBlock()">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"></path>
                            </svg>
                            Редактировать
                        </button>
                        @endif
                    </div>
                    @php
                        $removalStatus = null;
                        if (isset($advertisement->removal_data['status_id'])) {
                            $removalStatus = \App\Models\ProductInstallStatuses::find($advertisement->removal_data['status_id']);
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
                                <select class="form-control" id="removal_status_select" data-original="{{ $advertisement->removal_data['status_id'] ?? '' }}">
                                    <option value="">Выберите статус</option>
                                    @foreach($installStatuses as $status)
                                        <option value="{{ $status->id }}" 
                                                {{ (isset($advertisement->removal_data['status_id']) && $advertisement->removal_data['status_id'] == $status->id) ? 'selected' : '' }}>
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
                                @if(isset($advertisement->removal_data['comment']) && $advertisement->removal_data['comment'])
                                    <p>{{ $advertisement->removal_data['comment'] }}</p>
                                @else
                                    <p class="no-comment">Комментарий по демонтажу не указан</p>
                                @endif
                            </div>
                            <div class="comment-edit" id="removal_comment_edit" style="display: none;">
                                <textarea class="comment-textarea" id="removal_comment_textarea" rows="3" data-original="{{ $advertisement->removal_data['comment'] ?? '' }}">{{ $advertisement->removal_data['comment'] ?? '' }}</textarea>
                            </div>
                        </div>
                        <!-- Кнопки действий для блока демонтажа -->
                        <div class="removal-actions" id="removal_actions" style="display: none;">
                            <button class="btn btn-primary" onclick="saveRemovalBlock()">Сохранить</button>
                            <button class="btn btn-secondary" onclick="cancelRemovalEdit()">Отмена</button>
                        </div>
                    </div>
                </div>
            @endif

            @if($advertisement->product || true)
                <!-- Блок информации о покупке -->
                <div class="info-block">
                    <div class="block-header">
                        <h3>Информация о покупке</h3>
                        @if(\App\Helpers\AdvertisementHelper::canEditAdvertisement($advertisement))
                        <button class="edit-payment-btn" onclick="editPurchaseBlock()">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"></path>
                            </svg>
                            Редактировать
                        </button>
                        @endif
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

                    <!-- Стоимость покупки -->
                    <div class="payment-item">
                        <strong>Стоимость покупки:</strong>
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
                                   placeholder="Введите стоимость покупки">
                        </div>
                    </div>

                    <!-- Комментарий к покупке -->
                    <div class="payment-item">
                        <strong>Комментарий к покупке:</strong>
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
                                      placeholder="Введите комментарий к покупке">{{ $advertisement->product->payment_comment ?? '' }}</textarea>
                        </div>
                    </div>

                    <!-- Кнопки действий для блока покупки -->
                    <div class="payment-actions" id="purchase_actions" style="display: none;">
                        <button class="btn btn-primary" onclick="savePurchaseBlock()">Сохранить</button>
                        <button class="btn btn-secondary" onclick="cancelPurchaseEdit()">Отмена</button>
                    </div>
                </div>

                <!-- Блок информации о продаже -->
                <div class="info-block">
                    <div class="block-header">
                        <h3>Информация о продаже</h3>
                        @if(\App\Helpers\AdvertisementHelper::canEditAdvertisement($advertisement))
                        <button class="edit-payment-btn" onclick="editSaleBlock()">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"></path>
                            </svg>
                            Редактировать
                        </button>
                        @endif
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

                    <!-- Кнопки действий для блока продажи -->
                    <div class="payment-actions" id="sale_actions" style="display: none;">
                        <button class="btn btn-primary" onclick="saveSaleBlock()">Сохранить</button>
                        <button class="btn btn-secondary" onclick="cancelSaleEdit()">Отмена</button>
                    </div>
                </div>
            @endif

            @if($advertisement->additional_info || true)
                <div class="info-block">
                    <div class="block-header">
                        <h3>Дополнительная информация</h3>
                        @if(\App\Helpers\AdvertisementHelper::canEditAdvertisement($advertisement))
                        <button class="edit-comment-btn" onclick="editComment('additional_info')">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"></path>
                            </svg>
                            Редактировать
                        </button>
                        @endif
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
                @if(\App\Helpers\AdvertisementHelper::canEditAdvertisement($advertisement))
                <div id="galleryMediaActions" style="position: absolute; top: 20px; right: 20px; display: flex; gap: 8px; opacity: 0; transition: opacity 0.2s ease; z-index: 10;">
                    <button id="gallerySetMainBtn" style="width: 40px; height: 40px; border: none; border-radius: 50%; background: rgba(40, 167, 69, 0.9); color: white; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: all 0.2s ease;" 
                            onclick="setGalleryImageAsMain()" 
                            title="Сделать главным изображением">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M9 12l2 2 4-4"/>
                            <circle cx="12" cy="12" r="10"/>
                        </svg>
                    </button>
                    <button id="galleryDeleteBtn" style="width: 40px; height: 40px; border: none; border-radius: 50%; background: rgba(220, 53, 69, 0.9); color: white; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: all 0.2s ease;" 
                            onclick="deleteGalleryMedia()" 
                            title="Удалить медиафайл">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="3,6 5,6 21,6"></polyline>
                            <path d="m19,6v14a2,2 0 0,1 -2,2H7a2,2 0 0,1 -2,-2V6m3,0V4a2,2 0 0,1 2,-2h4a2,2 0 0,1 2,2v2"></path>
                            <line x1="10" y1="11" x2="10" y2="17"></line>
                            <line x1="14" y1="11" x2="14" y2="17"></line>
                        </svg>
                    </button>
                </div>
                @endif
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

<!-- Модальное окно для истории логов -->
<div id="logsHistoryModal" class="modal" style="display: none;">
    <div class="modal-content logs-history-modal">
        <div class="modal-header">
            <h3>История логов объявления</h3>
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

<!-- Модальное окно для комментария при смене статуса объявления -->
<div id="advertisementStatusCommentModal" class="modal" style="display: none;">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Смена статуса объявления</h3>
            <span class="close" onclick="cancelAdvertisementStatusChange()">&times;</span>
        </div>
        <div class="modal-body">
            <p>Оставьте комментарий по причине смены статуса объявления.</p>
            <div class="form-group">
                <label for="advertisementStatusComment">Комментарий:</label>
                <textarea id="advertisementStatusComment" rows="4" placeholder="Введите комментарий..." required></textarea>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" onclick="cancelAdvertisementStatusChange()">Отмена</button>
            <button type="button" class="btn btn-primary" onclick="saveAdvertisementStatusChange()">Сохранить</button>
        </div>
    </div>
</div>

<!-- Модальное окно для предупреждения о статусе товара -->
<div id="productStatusWarningModal" class="modal" style="display: none;">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Внимание!</h3>
            <span class="close" onclick="closeProductStatusWarning()">&times;</span>
        </div>
        <div class="modal-body">
            <div class="warning-icon">
                <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#FFC107" stroke-width="2">
                    <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path>
                    <line x1="12" y1="9" x2="12" y2="13"></line>
                    <line x1="12" y1="17" x2="12.01" y2="17"></line>
                </svg>
            </div>
            <div id="productStatusWarningMessage"></div>
            <div class="warning-actions">
                <p><strong>Для продолжения необходимо:</strong></p>
                <ol>
                    <li>Перейти к товару</li>
                    <li>Изменить статус товара с текущего на другой</li>
                    <li>Вернуться к объявлению и повторить смену статуса</li>
                </ol>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" onclick="closeProductStatusWarning()">Закрыть</button>
            <button type="button" class="btn btn-primary" onclick="goToProduct()">Перейти к товару</button>
        </div>
    </div>
</div>

<style>
/* Стили для отображения HTML-контента из редактора */
.html-content {
    line-height: 1.6;
    color: #333;
}

/* Стили для модального окна предупреждения о статусе товара */
.warning-icon {
    text-align: center;
    margin-bottom: 20px;
}

.warning-icon svg {
    width: 48px;
    height: 48px;
}

#productStatusWarningMessage {
    background-color: #fff3cd;
    border: 1px solid #ffeaa7;
    border-radius: 4px;
    padding: 15px;
    margin-bottom: 20px;
    color: #856404;
    font-weight: 500;
}

.warning-actions {
    background-color: #f8f9fa;
    border: 1px solid #dee2e6;
    border-radius: 4px;
    padding: 15px;
    margin-top: 15px;
}

.warning-actions p {
    margin-bottom: 10px;
    color: #495057;
}

.warning-actions ol {
    margin: 0;
    padding-left: 20px;
    color: #495057;
}

.warning-actions li {
    margin-bottom: 5px;
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

.advertisement-title-container {
    display: flex;
    align-items: center;
    gap: 10px;
    position: relative;
}

.advertisement-title {
    font-size: 28px;
    color: #133E71;
    margin: 0;
    font-weight: 600;
    flex: 1;
}

.edit-title-btn {
    opacity: 0;
    transition: opacity 0.3s ease;
    padding: 6px 8px;
    border: 1px solid #133E71;
    color: #133E71;
    background: transparent;
    border-radius: 4px;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 4px;
}

.advertisement-title-container:hover .edit-title-btn {
    opacity: 1;
}

.edit-title-btn:hover {
    background-color: #133E71;
    color: white;
}

.advertisement-title-edit {
    display: flex;
    flex-direction: column;
    gap: 10px;
    width: 100%;
}

.title-input {
    font-size: 28px;
    color: #133E71;
    font-weight: 600;
    border: 2px solid #133E71;
    border-radius: 6px;
    padding: 8px 12px;
    background: white;
    outline: none;
    transition: border-color 0.3s ease;
}

.title-input:focus {
    border-color: #0f2d56;
    box-shadow: 0 0 0 3px rgba(19, 62, 113, 0.1);
}

.title-edit-buttons {
    display: flex;
    gap: 8px;
    justify-content: flex-start;
}

.title-edit-buttons .btn {
    display: flex;
    align-items: center;
    gap: 6px;
    padding: 6px 12px;
    border-radius: 4px;
    font-size: 14px;
    font-weight: 500;
    transition: all 0.3s ease;
    border: 1px solid;
}

.title-edit-buttons .btn-success {
    background-color: #28a745;
    color: white;
    border-color: #28a745;
}

.title-edit-buttons .btn-success:hover {
    background-color: #218838;
    border-color: #1e7e34;
}

.title-edit-buttons .btn-secondary {
    background-color: #6c757d;
    color: white;
    border-color: #6c757d;
}

.title-edit-buttons .btn-secondary:hover {
    background-color: #5a6268;
    border-color: #545b62;
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

.advertisement-actions .btn-success {
    background-color: #28a745;
    color: white;
    border-color: #28a745;
}

.advertisement-actions .btn-success:hover {
    background-color: #218838;
    border-color: #1e7e34;
    transform: translateY(-1px);
}

.btn-full-width {
    width: 100%;
    justify-content: center;
}

.media-controls {
    margin: 10px 0;
}

.advertisement-actions .btn svg {
    width: 16px;
    height: 16px;
}

.advertisement-status {
    margin-top: 10px;
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

.thumbnail:hover div[style*="position: absolute"] {
    opacity: 1 !important;
}

.main-image-container:hover div[style*="position: absolute"] {
    opacity: 1 !important;
}

.gallery-item-container:hover #galleryMediaActions {
    opacity: 1 !important;
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

/* Стили для блока характеристик */
.chars-content {
    margin-top: 10px;
}

.chars-content p {
    margin-top: 8px;
    color: #495057;
    line-height: 1.5;
    background: #f8f9fa;
    padding: 10px;
    border-radius: 4px;
}

.chars-edit {
    margin-top: 15px;
}

.chars-actions {
    display: flex;
    gap: 10px;
    margin-top: 15px;
    justify-content: flex-end;
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

.payment-content{
    margin-top: 10px;
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

/* Стили для модальных окон действий и логов */
.form-group {
    margin-bottom: 20px;
}

.form-group label {
    display: block;
    margin-bottom: 8px;
    font-weight: 600;
    color: #495057;
}

.form-group .form-control {
    width: 100%;
    padding: 12px;
    border: 1px solid #ddd;
    border-radius: 6px;
    font-size: 14px;
    line-height: 1.5;
    font-family: inherit;
}

.form-group .form-control:focus {
    outline: none;
    border-color: #133E71;
    box-shadow: 0 0 0 2px rgba(19, 62, 113, 0.1);
}

.modal-actions {
    display: flex;
    gap: 10px;
    justify-content: flex-end;
    margin-top: 20px;
}

.action-info {
    background: #f8f9fa;
    padding: 15px;
    border-radius: 6px;
    margin-bottom: 20px;
    border-left: 4px solid #133E71;
}

.action-info p {
    margin: 0;
    color: #495057;
}

.history-list {
    max-height: 500px;
    overflow-y: auto;
}

.history-item {
    background: #f8f9fa;
    border-radius: 8px;
    padding: 15px;
    margin-bottom: 15px;
    border: 1px solid #e9ecef;
}

.history-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 10px;
}

.history-date {
    color: #666;
    font-size: 12px;
}

.history-type {
    background: #133E71;
    color: white;
    padding: 4px 8px;
    border-radius: 12px;
    font-size: 10px;
    font-weight: 600;
}

.history-status {
    padding: 4px 8px;
    border-radius: 12px;
    font-size: 10px;
    font-weight: 600;
}

.history-status.completed {
    background: #28a745;
    color: white;
}

.history-status.pending {
    background: #ffc107;
    color: #212529;
}

.history-content {
    margin-bottom: 10px;
}

.history-content p {
    color: #495057;
    line-height: 1.5;
    margin: 0;
}

.history-footer {
    color: #666;
    font-size: 12px;
    display: flex;
    justify-content: space-between;
}

.no-data {
    text-align: center;
    color: #999;
    font-style: italic;
    padding: 40px 20px;
}

/* Стили для кнопок */
.btn-success {
    background: #28a745;
    color: white;
    border: none;
    padding: 8px 16px;
    border-radius: 6px;
    font-size: 14px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.3s ease;
}

.btn-success:hover {
    background: #218838;
    transform: translateY(-1px);
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
    
    .title-input {
        font-size: 24px;
    }
    
    .advertisement-title-container {
        flex-direction: column;
        align-items: flex-start;
        gap: 8px;
    }
    
    .edit-title-btn {
        opacity: 1;
        align-self: flex-start;
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
                    mime: '{{ $media->mime_type }}',
                    isMainImage: {{ $advertisement->main_img == $media->id ? 'true' : 'false' }},
                    mediaId: {{ $media->id }}
                },
            @endforeach
        ];
    @endif
});

function changeMainImage(url, index, type) {
    const mainImage = document.getElementById('mainImage');
    if (mainImage && type === 'image') {
        mainImage.src = url;
        
        // Обновляем главное изображение на сервере
        const mediaId = getMediaIdByIndex(index);
        if (mediaId) {
            updateMainImageOnServer(mediaId);
        }
    }
    
    // Обновляем активный thumbnail
    document.querySelectorAll('.thumbnail').forEach(thumb => thumb.classList.remove('active'));
    document.querySelectorAll('.thumbnail')[index].classList.add('active');
    
    currentGalleryIndex = index;
}

function getMediaIdByIndex(index) {
    const thumbnails = document.querySelectorAll('.thumbnail');
    if (thumbnails[index]) {
        const deleteBtn = thumbnails[index].querySelector('.delete-media-btn');
        if (deleteBtn) {
            const onclick = deleteBtn.getAttribute('onclick');
            const match = onclick.match(/deleteMedia\((\d+),/);
            return match ? match[1] : null;
        }
    }
    return null;
}

function updateMainImageOnServer(mediaId) {
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    
    fetch(`/advertisements/{{ $advertisement->id }}/main-image`, {
        method: 'PATCH',
        headers: {
            'X-CSRF-TOKEN': csrfToken,
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            media_id: mediaId
        })
    })
    .then(response => response.json())
    .then(data => {
        if (!data.success) {
            console.error('Ошибка при обновлении главного изображения:', data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
    });
}

function setAsMainImage(mediaId, index) {
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    
    fetch(`/advertisements/{{ $advertisement->id }}/main-image`, {
        method: 'PATCH',
        headers: {
            'X-CSRF-TOKEN': csrfToken,
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            media_id: mediaId
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Обновляем главное изображение
            const mainImage = document.getElementById('mainImage');
            if (mainImage && data.main_image_url) {
                mainImage.src = data.main_image_url;
            }
            
            // Обновляем активные кнопки
            document.querySelectorAll('.thumbnail').forEach(thumb => thumb.classList.remove('active'));
            document.querySelectorAll('.thumbnail')[index].classList.add('active');
            
            // Обновляем стили кнопок "Сделать главным"
            document.querySelectorAll('button[onclick*="setAsMainImage"]').forEach(btn => {
                btn.style.background = 'rgba(40, 167, 69, 0.9)';
                btn.style.boxShadow = 'none';
            });
            
            // Активируем текущую кнопку
            const currentBtn = document.querySelector(`button[onclick*="setAsMainImage(${mediaId}"]`);
            if (currentBtn) {
                currentBtn.style.background = 'rgba(40, 167, 69, 1)';
                currentBtn.style.boxShadow = '0 0 0 2px rgba(40, 167, 69, 0.5)';
            }
            
            // Показываем уведомление
            showNotification('Главное изображение обновлено', 'success');
        } else {
            showNotification(data.message || 'Ошибка при обновлении главного изображения', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Ошибка при обновлении главного изображения', 'error');
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
        padding: 12px 20px;
        border-radius: 6px;
        color: white;
        font-weight: 500;
        z-index: 10000;
        opacity: 0;
        transform: translateX(100%);
        transition: all 0.3s ease;
    `;
    
    // Цвета для разных типов
    const colors = {
        success: '#28a745',
        error: '#dc3545',
        info: '#17a2b8',
        warning: '#ffc107'
    };
    
    notification.style.backgroundColor = colors[type] || colors.info;
    
    // Добавляем в DOM
    document.body.appendChild(notification);
    
    // Анимация появления
    setTimeout(() => {
        notification.style.opacity = '1';
        notification.style.transform = 'translateX(0)';
    }, 100);
    
    // Удаляем через 3 секунды
    setTimeout(() => {
        notification.style.opacity = '0';
        notification.style.transform = 'translateX(100%)';
        setTimeout(() => {
            if (notification.parentNode) {
                notification.parentNode.removeChild(notification);
            }
        }, 300);
    }, 3000);
}

function setGalleryImageAsMain() {
    if (currentGalleryIndex < 0 || currentGalleryIndex >= galleryItems.length) return;
    
    const currentItem = galleryItems[currentGalleryIndex];
    if (currentItem.type !== 'image') return;
    
    // Находим ID медиафайла по URL
    const mediaId = getMediaIdByUrl(currentItem.url);
    if (!mediaId) return;
    
    setAsMainImage(mediaId, currentGalleryIndex);
}

function deleteGalleryMedia() {
    if (currentGalleryIndex < 0 || currentGalleryIndex >= galleryItems.length) return;
    
    const currentItem = galleryItems[currentGalleryIndex];
    
    if (!confirm('Вы уверены, что хотите удалить этот медиафайл?')) {
        return;
    }
    
    // Находим ID медиафайла по URL
    const mediaId = getMediaIdByUrl(currentItem.url);
    if (!mediaId) return;
    
    // Определяем тип файла
    const fileType = currentItem.type;
    
    deleteMedia(mediaId, fileType);
}

function getMediaIdByUrl(url) {
    // Ищем медиафайл по URL в galleryItems
    for (let i = 0; i < galleryItems.length; i++) {
        if (galleryItems[i].url === url) {
            return galleryItems[i].mediaId;
        }
    }
    
    return null;
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
    const setMainBtn = document.getElementById('gallerySetMainBtn');
    const deleteBtn = document.getElementById('galleryDeleteBtn');
    
    if (item.type === 'image') {
        image.src = item.url;
        image.style.display = 'block';
        video.style.display = 'none';
        
        // Показываем кнопки для изображений
        if (setMainBtn) setMainBtn.style.display = 'flex';
        if (deleteBtn) deleteBtn.style.display = 'flex';
        
        // Обновляем стиль кнопки "Сделать главным" в зависимости от того, является ли это изображение главным
        if (setMainBtn) {
            const isMainImage = item.isMainImage;
            if (isMainImage) {
                setMainBtn.style.background = 'rgba(40, 167, 69, 1)';
                setMainBtn.style.boxShadow = '0 0 0 2px rgba(40, 167, 69, 0.5)';
                setMainBtn.title = 'Главное изображение';
            } else {
                setMainBtn.style.background = 'rgba(40, 167, 69, 0.9)';
                setMainBtn.style.boxShadow = 'none';
                setMainBtn.title = 'Сделать главным изображением';
            }
        }
    } else if (item.type === 'video') {
        video.src = item.url;
        video.style.display = 'block';
        image.style.display = 'none';
        
        // Скрываем кнопку "Сделать главным" для видео
        if (setMainBtn) setMainBtn.style.display = 'none';
        if (deleteBtn) deleteBtn.style.display = 'flex';
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
// Отключено закрытие при клике вне галереи
/*
document.getElementById('galleryModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeGallery();
    }
});
*/

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
// Отключено закрытие при клике вне модального окна контакта
/*
window.onclick = function(event) {
    const contactModal = document.getElementById('contactModal');
    if (event.target === contactModal) {
        closeContactCard();
    }
}
*/

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

// Функции для редактирования заголовка объявления
let originalTitle = '';

function editTitle() {
    const content = document.getElementById('title_content');
    const edit = document.getElementById('title_edit');
    const input = document.getElementById('title_input');
    
    // Сохраняем оригинальное значение
    originalTitle = content.textContent.trim();
    
    // Скрываем заголовок и показываем форму редактирования
    content.style.display = 'none';
    edit.style.display = 'flex';
    
    // Устанавливаем значение в поле ввода и фокусируемся на нем
    input.value = originalTitle;
    input.focus();
    input.select();
}

function cancelTitleEdit() {
    const content = document.getElementById('title_content');
    const edit = document.getElementById('title_edit');
    const input = document.getElementById('title_input');
    
    // Восстанавливаем оригинальное значение
    input.value = originalTitle;
    
    // Скрываем форму редактирования и показываем заголовок
    edit.style.display = 'none';
    content.style.display = 'block';
}

function saveTitle() {
    const input = document.getElementById('title_input');
    const content = document.getElementById('title_content');
    const edit = document.getElementById('title_edit');
    const newTitle = input.value.trim();
    
    // Проверяем, что заголовок не пустой
    if (!newTitle) {
        showNotification('Заголовок не может быть пустым', 'error');
        return;
    }
    
    // Проверяем, что заголовок изменился
    if (newTitle === originalTitle) {
        cancelTitleEdit();
        return;
    }
    
    // Показываем индикатор загрузки
    const saveBtn = document.querySelector('#title_edit .btn-success');
    const originalText = saveBtn.innerHTML;
    saveBtn.innerHTML = '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 12a9 9 0 11-6.219-8.56"/></svg> Сохранение...';
    saveBtn.disabled = true;
    
    // Отправляем запрос на сервер
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    
    fetch(`/advertisements/{{ $advertisement->id }}/title`, {
        method: 'PATCH',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken
        },
        body: JSON.stringify({
            title: newTitle
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Обновляем заголовок в интерфейсе
            content.textContent = newTitle;
            
            // Обновляем заголовок в breadcrumb
            const breadcrumb = document.querySelector('.breadcrumb');
            if (breadcrumb) {
                const breadcrumbText = breadcrumb.textContent;
                const newBreadcrumbText = breadcrumbText.replace(originalTitle, newTitle);
                breadcrumb.textContent = newBreadcrumbText;
            }
            
            // Обновляем заголовок страницы
            document.title = newTitle + ' - Объявление';
            
            // Скрываем форму редактирования и показываем заголовок
            edit.style.display = 'none';
            content.style.display = 'block';
            
            // Обновляем оригинальное значение
            originalTitle = newTitle;
            
            // Показываем уведомление об успехе
            showNotification('Заголовок успешно обновлен', 'success');
        } else {
            throw new Error(data.message || 'Ошибка при сохранении заголовка');
        }
    })
    .catch(error => {
        console.error('Ошибка:', error);
        showNotification('Ошибка при сохранении заголовка', 'error');
    })
    .finally(() => {
        // Восстанавливаем кнопку
        saveBtn.innerHTML = originalText;
        saveBtn.disabled = false;
    });
}

// Обработчик клавиши Enter для сохранения заголовка
document.addEventListener('DOMContentLoaded', function() {
    const titleInput = document.getElementById('title_input');
    if (titleInput) {
        titleInput.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                saveTitle();
            } else if (e.key === 'Escape') {
                e.preventDefault();
                cancelTitleEdit();
            }
        });
    }
});

// Функции для редактирования блока покупки
function editPurchaseBlock() {
    // Скрываем все контенты и показываем формы редактирования для блока покупки
    const fields = ['payment_method', 'purchase_price', 'payment_comment'];
    
    fields.forEach(field => {
        const content = document.getElementById(field + '_content');
        const edit = document.getElementById(field + '_edit');
        
        if (content && edit) {
            content.style.display = 'none';
            edit.style.display = 'block';
        }
    });
    
    // Показываем кнопки действий для блока покупки
    document.getElementById('purchase_actions').style.display = 'flex';
    
    // Скрываем кнопку редактирования
    document.querySelector('#purchase_actions').closest('.info-block').querySelector('.edit-payment-btn').style.display = 'none';
}

function cancelPurchaseEdit() {
    // Восстанавливаем все оригинальные значения для блока покупки
    const fields = ['payment_method', 'purchase_price', 'payment_comment'];
    
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
            
            content.style.display = 'block';
            edit.style.display = 'none';
        }
    });
    
    // Скрываем кнопки действий для блока покупки
    document.getElementById('purchase_actions').style.display = 'none';
    
    // Показываем кнопку редактирования
    document.querySelector('#purchase_actions').closest('.info-block').querySelector('.edit-payment-btn').style.display = 'flex';
}

function savePurchaseBlock() {
    // Собираем данные для отправки (только данные покупки)
    const purchaseData = {
        payment_types: [],
        purchase_price: null,
        payment_comment: null
    };
    
    // Получаем выбранные варианты оплаты
    const paymentCheckboxes = document.querySelectorAll('input[name="payment_types[]"]:checked');
    paymentCheckboxes.forEach(checkbox => {
        purchaseData.payment_types.push(parseInt(checkbox.value));
    });
    
    // Получаем стоимость покупки
    const purchasePriceInput = document.getElementById('purchase_price_input');
    if (purchasePriceInput && purchasePriceInput.value) {
        purchaseData.purchase_price = parseFloat(purchasePriceInput.value);
    }
    
    // Получаем комментарий к покупке
    const paymentCommentTextarea = document.getElementById('payment_comment_textarea');
    if (paymentCommentTextarea) {
        purchaseData.payment_comment = paymentCommentTextarea.value.trim();
    }
    
    // Показываем индикатор загрузки
    const saveBtn = document.querySelector('#purchase_actions .btn-primary');
    const originalText = saveBtn.textContent;
    saveBtn.textContent = 'Сохранение...';
    saveBtn.disabled = true;
    
    // Отправляем AJAX запрос
    fetch(`/advertisements/{{ $advertisement->id }}/purchase-info`, {
        method: 'PATCH',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify(purchaseData)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Обновляем отображение
            updatePurchaseDisplay(purchaseData);
            
            // Сохраняем новые значения как оригинальные
            savePurchaseOriginals(purchaseData);
            
            // Скрываем формы редактирования
            cancelPurchaseEdit();
            
            // Показываем уведомление об успехе
            showNotification('Информация о покупке успешно обновлена', 'success');
        } else {
            throw new Error(data.message || 'Ошибка при сохранении');
        }
    })
    .catch(error => {
        console.error('Ошибка:', error);
        showNotification('Ошибка при сохранении информации о покупке', 'error');
    })
    .finally(() => {
        // Восстанавливаем кнопку
        saveBtn.textContent = originalText;
        saveBtn.disabled = false;
    });
}

// Функции для редактирования блока продажи
function editSaleBlock() {
    // Скрываем все контенты и показываем формы редактирования для блока продажи
    const fields = ['adv_price', 'adv_price_comment'];
    
    fields.forEach(field => {
        const content = document.getElementById(field + '_content');
        const edit = document.getElementById(field + '_edit');
        
        if (content && edit) {
            content.style.display = 'none';
            edit.style.display = 'block';
        }
    });
    
    // Показываем кнопки действий для блока продажи
    document.getElementById('sale_actions').style.display = 'flex';
    
    // Скрываем кнопку редактирования
    document.querySelector('#sale_actions').closest('.info-block').querySelector('.edit-payment-btn').style.display = 'none';
}

function cancelSaleEdit() {
    // Восстанавливаем все оригинальные значения для блока продажи
    const fields = ['adv_price', 'adv_price_comment'];
    
    fields.forEach(field => {
        const content = document.getElementById(field + '_content');
        const edit = document.getElementById(field + '_edit');
        
        if (content && edit) {
            if (field === 'adv_price') {
                const input = document.getElementById(field + '_input');
                if (input) {
                    const originalValue = input.getAttribute('data-original') || '';
                    input.value = originalValue;
                }
            } else if (field === 'adv_price_comment') {
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
    
    // Скрываем кнопки действий для блока продажи
    document.getElementById('sale_actions').style.display = 'none';
    
    // Показываем кнопку редактирования
    document.querySelector('#sale_actions').closest('.info-block').querySelector('.edit-payment-btn').style.display = 'flex';
}

function saveSaleBlock() {
    // Собираем данные для отправки (только данные продажи)
    const saleData = {
        adv_price: null,
        adv_price_comment: null
    };
    
    // Получаем цену продажи
    const advPriceInput = document.getElementById('adv_price_input');
    if (advPriceInput && advPriceInput.value) {
        saleData.adv_price = parseFloat(advPriceInput.value);
    }
    
    // Получаем комментарий к продаже
    const advPriceCommentTextarea = document.getElementById('adv_price_comment_textarea');
    if (advPriceCommentTextarea) {
        saleData.adv_price_comment = advPriceCommentTextarea.value.trim();
    }
    
    // Показываем индикатор загрузки
    const saveBtn = document.querySelector('#sale_actions .btn-primary');
    const originalText = saveBtn.textContent;
    saveBtn.textContent = 'Сохранение...';
    saveBtn.disabled = true;
    
    // Отправляем AJAX запрос
    fetch(`/advertisements/{{ $advertisement->id }}/sale-info`, {
        method: 'PATCH',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify(saleData)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Обновляем отображение
            updateSaleDisplay(saleData);
            
            // Сохраняем новые значения как оригинальные
            saveSaleOriginals(saleData);
            
            // Скрываем формы редактирования
            cancelSaleEdit();
            
            // Показываем уведомление об успехе
            showNotification('Информация о продаже успешно обновлена', 'success');
        } else {
            throw new Error(data.message || 'Ошибка при сохранении');
        }
    })
    .catch(error => {
        console.error('Ошибка:', error);
        showNotification('Ошибка при сохранении информации о продаже', 'error');
    })
    .finally(() => {
        // Восстанавливаем кнопку
        saveBtn.textContent = originalText;
        saveBtn.disabled = false;
    });
}

function updatePurchaseDisplay(purchaseData) {
    // Обновляем отображение вариантов оплаты
    const paymentContent = document.getElementById('payment_method_content');
    if (purchaseData.payment_types.length > 0) {
        const variantNames = purchaseData.payment_types.map(typeId => {
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
    
    // Обновляем отображение стоимости покупки
    const priceContent = document.getElementById('purchase_price_content');
    if (purchaseData.purchase_price) {
        priceContent.innerHTML = `<span class="price">${purchaseData.purchase_price.toLocaleString('ru-RU')} ₽</span>`;
    } else {
        priceContent.innerHTML = '<span class="no-value">Не указана</span>';
    }
    
    // Обновляем отображение комментария к покупке
    const commentContent = document.getElementById('payment_comment_content');
    if (purchaseData.payment_comment) {
        commentContent.innerHTML = `<p>${purchaseData.payment_comment}</p>`;
    } else {
        commentContent.innerHTML = '<span class="no-value">Не указан</span>';
    }
}

function savePurchaseOriginals(purchaseData) {
    // Сохраняем новые значения как оригинальные для блока покупки
    const paymentCheckboxes = document.querySelectorAll('input[name="payment_types[]"]');
    paymentCheckboxes.forEach(checkbox => {
        const isChecked = purchaseData.payment_types.includes(parseInt(checkbox.value));
        checkbox.setAttribute('data-original', isChecked.toString());
    });
    
    const purchasePriceInput = document.getElementById('purchase_price_input');
    if (purchasePriceInput) {
        purchasePriceInput.setAttribute('data-original', purchaseData.purchase_price || '');
    }
    
    const paymentCommentTextarea = document.getElementById('payment_comment_textarea');
    if (paymentCommentTextarea) {
        paymentCommentTextarea.setAttribute('data-original', purchaseData.payment_comment || '');
    }
}

function updateSaleDisplay(saleData) {
    // Обновляем отображение цены продажи
    const advPriceContent = document.getElementById('adv_price_content');
    if (saleData.adv_price) {
        advPriceContent.innerHTML = `<span class="price">${saleData.adv_price.toLocaleString('ru-RU')} ₽</span>`;
    } else {
        advPriceContent.innerHTML = '<span class="no-value">Не указана</span>';
    }
    
    // Обновляем отображение комментария к продаже
    const advPriceCommentContent = document.getElementById('adv_price_comment_content');
    if (saleData.adv_price_comment) {
        advPriceCommentContent.innerHTML = `<p>${saleData.adv_price_comment}</p>`;
    } else {
        advPriceCommentContent.innerHTML = '<span class="no-value">Не указан</span>';
    }
}

function saveSaleOriginals(saleData) {
    // Сохраняем новые значения как оригинальные для блока продажи
    const advPriceInput = document.getElementById('adv_price_input');
    if (advPriceInput) {
        advPriceInput.setAttribute('data-original', saleData.adv_price || '');
    }
    
    const advPriceCommentTextarea = document.getElementById('adv_price_comment_textarea');
    if (advPriceCommentTextarea) {
        advPriceCommentTextarea.setAttribute('data-original', saleData.adv_price_comment || '');
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

// Функции для редактирования блока характеристик
function editCharacteristicsBlock() {
    // Скрываем все контенты и показываем формы редактирования для блока характеристик
    const fields = ['main_characteristics', 'complectation'];
    
    fields.forEach(field => {
        const content = document.getElementById(field + '_content');
        const edit = document.getElementById(field + '_edit');
        
        if (content && edit) {
            content.style.display = 'none';
            edit.style.display = 'block';
        }
    });
    
    // Показываем кнопки действий для блока характеристик
    document.getElementById('characteristics_actions').style.display = 'flex';
    
    // Скрываем кнопку редактирования
    document.querySelector('#characteristics_actions').closest('.info-block').querySelector('.edit-comment-btn').style.display = 'none';
}

function cancelCharacteristicsEdit() {
    // Восстанавливаем все оригинальные значения для блока характеристик
    const fields = ['main_characteristics', 'complectation'];
    
    fields.forEach(field => {
        const content = document.getElementById(field + '_content');
        const edit = document.getElementById(field + '_edit');
        const textarea = document.getElementById(field + '_textarea');
        
        if (content && edit && textarea) {
            const originalValue = textarea.getAttribute('data-original') || '';
            textarea.value = originalValue;
            
            content.style.display = 'block';
            edit.style.display = 'none';
        }
    });
    
    // Скрываем кнопки действий для блока характеристик
    document.getElementById('characteristics_actions').style.display = 'none';
    
    // Показываем кнопку редактирования
    document.querySelector('#characteristics_actions').closest('.info-block').querySelector('.edit-comment-btn').style.display = 'flex';
}

function saveCharacteristicsBlock() {
    // Собираем данные для отправки
    const characteristicsData = {
        main_characteristics: null,
        complectation: null
    };
    
    // Получаем основные характеристики
    const mainCharacteristicsTextarea = document.getElementById('main_characteristics_textarea');
    if (mainCharacteristicsTextarea) {
        characteristicsData.main_characteristics = mainCharacteristicsTextarea.value.trim();
    }
    
    // Получаем комплектацию
    const complectationTextarea = document.getElementById('complectation_textarea');
    if (complectationTextarea) {
        characteristicsData.complectation = complectationTextarea.value.trim();
    }
    
    // Показываем индикатор загрузки
    const saveBtn = document.querySelector('#characteristics_actions .btn-primary');
    const originalText = saveBtn.textContent;
    saveBtn.textContent = 'Сохранение...';
    saveBtn.disabled = true;
    
    // Отправляем AJAX запрос
    fetch(`/advertisements/{{ $advertisement->id }}/characteristics`, {
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
            showNotification('Характеристики успешно обновлены', 'success');
        } else {
            throw new Error(data.message || 'Ошибка при сохранении');
        }
    })
    .catch(error => {
        console.error('Ошибка:', error);
        showNotification('Ошибка при сохранении характеристик', 'error');
    })
    .finally(() => {
        // Восстанавливаем кнопку
        saveBtn.textContent = originalText;
        saveBtn.disabled = false;
    });
}

function updateCharacteristicsDisplay(characteristicsData) {
    // Обновляем отображение основных характеристик
    const mainCharacteristicsContent = document.getElementById('main_characteristics_content');
    if (characteristicsData.main_characteristics) {
        mainCharacteristicsContent.innerHTML = `<p>${characteristicsData.main_characteristics}</p>`;
    } else {
        mainCharacteristicsContent.innerHTML = '<p class="no-comment">Основные характеристики не указаны</p>';
    }
    
    // Обновляем отображение комплектации
    const complectationContent = document.getElementById('complectation_content');
    if (characteristicsData.complectation) {
        complectationContent.innerHTML = `<p>${characteristicsData.complectation}</p>`;
    } else {
        complectationContent.innerHTML = '<p class="no-comment">Комплектация не указана</p>';
    }
}

function saveCharacteristicsOriginals(characteristicsData) {
    // Сохраняем новые значения как оригинальные для блока характеристик
    const mainCharacteristicsTextarea = document.getElementById('main_characteristics_textarea');
    if (mainCharacteristicsTextarea) {
        mainCharacteristicsTextarea.setAttribute('data-original', characteristicsData.main_characteristics || '');
    }
    
    const complectationTextarea = document.getElementById('complectation_textarea');
    if (complectationTextarea) {
        complectationTextarea.setAttribute('data-original', characteristicsData.complectation || '');
    }
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
    fetch(`/advertisements/{{ $advertisement->id }}/check-status`, {
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
    fetch(`/advertisements/{{ $advertisement->id }}/loading-status`, {
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
    fetch(`/advertisements/{{ $advertisement->id }}/removal-status`, {
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

// Глобальные переменные для работы с действиями
let currentActionId = null;
let currentActionText = null;
let currentAdvertisementId = {{ $advertisement->id }};

// Функции для работы с модальным окном создания нового действия
function showNewActionModal() {
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
    fetch(`/advertisements/${currentAdvertisementId}/actions`, {
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

// Функции для работы с модальным окном действий
function showActionsModal() {
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
    fetch(`/advertisements/${currentAdvertisementId}/actions`, {
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
        
        actionItem.appendChild(actionHeader);
        
        // Добавляем блок для комментария
        const commentBlock = document.createElement('div');
        commentBlock.className = 'action-comment-block';
        commentBlock.id = `comment-block-${action.id}`;
        commentBlock.innerHTML = `
            <textarea class="action-comment-textarea" placeholder="Введите комментарий о выполнении..."></textarea>
            <div class="action-comment-buttons">
                <button class="btn btn-secondary btn-sm" onclick="closeActionCommentBlock(${action.id})">Отмена</button>
                <button class="btn btn-primary btn-sm" onclick="completeAction(${action.id})">Выполнить</button>
            </div>
        `;
        
        actionItem.appendChild(commentBlock);
        actionsList.appendChild(actionItem);
    });
    
    content.innerHTML = '';
    content.appendChild(actionsList);
}

function showActionCommentBlock(actionId, actionText) {
    currentActionId = actionId;
    currentActionText = actionText;
    
    // Скрываем все блоки комментариев
    document.querySelectorAll('.action-comment-block').forEach(block => {
        block.classList.remove('show');
    });
    
    // Показываем нужный блок
    const commentBlock = document.getElementById(`comment-block-${actionId}`);
    if (commentBlock) {
        commentBlock.classList.add('show');
        commentBlock.querySelector('.action-comment-textarea').focus();
    }
}

function closeActionCommentBlock(actionId) {
    const commentBlock = document.getElementById(`comment-block-${actionId}`);
    if (commentBlock) {
        commentBlock.classList.remove('show');
        commentBlock.querySelector('.action-comment-textarea').value = '';
    }
}

function completeAction(actionId) {
    const commentBlock = document.getElementById(`comment-block-${actionId}`);
    const comment = commentBlock.querySelector('.action-comment-textarea').value.trim();
    
    if (!comment) {
        alert('Пожалуйста, введите комментарий о выполнении действия');
        return;
    }
    
    const actionButton = commentBlock.querySelector('.btn-primary');
    const originalText = actionButton.textContent;
    actionButton.textContent = 'Выполнение...';
    actionButton.disabled = true;
    
    fetch(`/advertisements/${currentAdvertisementId}/actions/${actionId}/complete`, {
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
            const actionItem = document.querySelector(`[data-action-id="${actionId}"]`);
            if (actionItem) {
                actionItem.classList.add('completed');
                const button = actionItem.querySelector('.action-button');
                button.textContent = 'Выполнено';
                button.disabled = true;
                closeActionCommentBlock(actionId);
            }
            
            // Обновляем лог событий, если получен новый лог
            if (data.log) {
                updateEventsLog(data.log);
            }
            
            showNotification('Действие успешно выполнено', 'success');
        } else {
            throw new Error(data.message || 'Ошибка при выполнении действия');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Ошибка при выполнении действия', 'error');
    })
    .finally(() => {
        actionButton.textContent = originalText;
        actionButton.disabled = false;
    });
}

// Функции для работы с модальным окном истории логов
function showLogsHistory() {
    const modal = document.getElementById('logsHistoryModal');
    modal.style.display = 'block';
    
    // Загружаем логи
    loadLogs();
}

function closeLogsHistory() {
    const modal = document.getElementById('logsHistoryModal');
    modal.style.display = 'none';
}

function loadLogs() {
    const content = document.getElementById('logsHistoryContent');
    
    // Показываем спиннер загрузки
    content.innerHTML = `
        <div class="loading-spinner">
            <div class="spinner"></div>
            <p>Загрузка логов...</p>
        </div>
    `;
    
    // Отправляем запрос на сервер
    fetch(`/advertisements/${currentAdvertisementId}/logs`, {
        method: 'GET',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            displayLogs(data.logs);
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

function displayLogs(logs) {
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
        logFooter.textContent = `Создал: ${log.user ? log.user.name : 'Система'}`;
        
        logItem.appendChild(logHeader);
        logItem.appendChild(logContent);
        logItem.appendChild(logFooter);
        
        logsList.appendChild(logItem);
    });
    
    content.innerHTML = '';
    content.appendChild(logsList);
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

// Глобальные переменные для хранения данных о смене статуса объявления
let pendingAdvertisementStatusChange = null;

// Функции для работы с выпадающим списком статусов объявления
function toggleAdvertisementStatusDropdown() {
    const dropdown = document.getElementById('advertisementStatusDropdown');
    const arrow = document.querySelector('.advertisement-status .status-badge.clickable .dropdown-arrow');
    
    if (dropdown.classList.contains('show')) {
        dropdown.classList.remove('show');
        arrow.style.transform = 'rotate(0deg)';
    } else {
        dropdown.classList.add('show');
        arrow.style.transform = 'rotate(180deg)';
    }
}

function changeAdvertisementStatus(statusId, statusName, statusColor) {
    // Получаем данные о смене статуса
    pendingAdvertisementStatusChange = {
        statusId: statusId,
        statusName: statusName,
        statusColor: statusColor
    };
    
    // Показываем модальное окно для комментария
    showAdvertisementStatusModal();
}

function showAdvertisementStatusModal() {
    const modal = document.getElementById('advertisementStatusCommentModal');
    const textarea = document.getElementById('advertisementStatusComment');
    
    // Очищаем поле комментария
    textarea.value = '';
    
    // Показываем модальное окно
    modal.style.display = 'block';
    
    // Фокусируемся на поле комментария
    textarea.focus();
}

function closeAdvertisementStatusModal() {
    const modal = document.getElementById('advertisementStatusCommentModal');
    modal.style.display = 'none';
}

function cancelAdvertisementStatusChange() {
    const modal = document.getElementById('advertisementStatusCommentModal');
    modal.style.display = 'none';
    
    // Сбрасываем данные о смене статуса при отмене
    pendingAdvertisementStatusChange = null;
}

function saveAdvertisementStatusChange() {
    const comment = document.getElementById('advertisementStatusComment').value.trim();
    
    if (!comment) {
        alert('Пожалуйста, введите комментарий');
        return;
    }
    
    if (!pendingAdvertisementStatusChange || !pendingAdvertisementStatusChange.statusId || !pendingAdvertisementStatusChange.statusName) {
        alert('Ошибка: данные о смене статуса не найдены. Пожалуйста, выберите статус заново.');
        closeAdvertisementStatusModal();
        return;
    }
    
    // Показываем индикатор загрузки
    const statusBadge = document.querySelector('.advertisement-status .status-badge.clickable');
    const originalContent = statusBadge.innerHTML;
    const originalStyle = statusBadge.getAttribute('style');
    statusBadge.innerHTML = '<span>Обновление...</span>';
    
    // Закрываем модальное окно
    closeAdvertisementStatusModal();
    
    // Отправляем запрос на сервер
    fetch(`/advertisements/{{ $advertisement->id }}/status`, {
        method: 'PATCH',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            status_id: pendingAdvertisementStatusChange.statusId,
            comment: comment
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Обновляем отображение статуса
            statusBadge.className = 'status-badge clickable';
            statusBadge.style.cssText = `background-color: ${pendingAdvertisementStatusChange.statusColor}; color: white;`;
            statusBadge.innerHTML = `
                ${pendingAdvertisementStatusChange.statusName}
                <svg class="dropdown-arrow" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="6,9 12,15 18,9"></polyline>
                </svg>
            `;
            
            // Закрываем выпадающий список
            toggleAdvertisementStatusDropdown();
            
            // Обновляем лог событий, если получен новый лог
            if (data.log) {
                updateAdvertisementEventsLog(data.log);
            }
            
            // Показываем уведомление об успехе
            showNotification('Статус объявления успешно обновлен', 'success');
        } else {
            // Проверяем, является ли ошибка связанной со статусом товара
            if (data.product_status && (data.product_status === 'Холд' || data.product_status === 'Отказ')) {
                showProductStatusWarning(data.message, data.product_id, data.product_status);
            } else {
                throw new Error(data.message || 'Ошибка при обновлении статуса');
            }
        }
    })
    .catch(error => {
        console.error('Error:', error);
        // Возвращаем оригинальное содержимое при ошибке
        statusBadge.innerHTML = originalContent;
        statusBadge.setAttribute('style', originalStyle);
        showNotification('Ошибка при обновлении статуса объявления', 'error');
    })
    .finally(() => {
        // Сбрасываем данные о смене статуса только после завершения операции
        if (pendingAdvertisementStatusChange) {
            pendingAdvertisementStatusChange = null;
        }
    });
}

function updateAdvertisementEventsLog(log) {
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
// Отключено закрытие при клике вне выпадающего списка
/*
document.addEventListener('click', function(event) {
    const statusSelector = document.querySelector('.advertisement-status .status-selector');
    const dropdown = document.getElementById('advertisementStatusDropdown');
    
    if (!statusSelector.contains(event.target) && dropdown && dropdown.classList.contains('show')) {
        toggleAdvertisementStatusDropdown();
    }
});
*/

// Глобальные переменные для хранения данных о предупреждении статуса товара
let productStatusWarningData = null;

// Функции для работы с модальным окном предупреждения о статусе товара
function showProductStatusWarning(message, productId, productStatus) {
    productStatusWarningData = {
        productId: productId,
        productStatus: productStatus
    };
    
    const modal = document.getElementById('productStatusWarningModal');
    const messageElement = document.getElementById('productStatusWarningMessage');
    
    messageElement.innerHTML = `<p>${message}</p>`;
    
    modal.style.display = 'block';
}

function closeProductStatusWarning() {
    const modal = document.getElementById('productStatusWarningModal');
    modal.style.display = 'none';
    productStatusWarningData = null;
}

function goToProduct() {
    if (productStatusWarningData && productStatusWarningData.productId) {
        // Переходим к странице товара
        window.location.href = `/product/${productStatusWarningData.productId}`;
    } else {
        // Если нет данных о товаре, переходим к списку товаров
        window.location.href = '/products';
    }
}

// Обновляем обработчики закрытия модальных окон
// Отключено закрытие при клике вне модальных окон
/*
document.addEventListener('click', function(event) {
    const logsModal = document.getElementById('logsHistoryModal');
    const actionsModal = document.getElementById('actionsModal');
    const newActionModal = document.getElementById('newActionModal');
    const advertisementStatusModal = document.getElementById('advertisementStatusCommentModal');
    const productStatusWarningModal = document.getElementById('productStatusWarningModal');
    
    if (event.target === logsModal) {
        closeLogsHistory();
    }
    
    if (event.target === actionsModal) {
        closeActionsModal();
    }
    
    if (event.target === newActionModal) {
        closeNewActionModal();
    }
    
    if (event.target === advertisementStatusModal) {
        cancelAdvertisementStatusChange();
    }
    
    if (event.target === productStatusWarningModal) {
        closeProductStatusWarning();
    }
});
*/

// Обновляем обработчики закрытия по Escape
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        const logsModal = document.getElementById('logsHistoryModal');
        const actionsModal = document.getElementById('actionsModal');
        const newActionModal = document.getElementById('newActionModal');
        const advertisementStatusModal = document.getElementById('advertisementStatusCommentModal');
        const productStatusWarningModal = document.getElementById('productStatusWarningModal');
        
        if (logsModal && logsModal.style.display === 'block') {
            closeLogsHistory();
        }
        
        if (actionsModal && actionsModal.style.display === 'block') {
            closeActionsModal();
        }
        
        if (newActionModal && newActionModal.style.display === 'block') {
            closeNewActionModal();
        }
        
        if (advertisementStatusModal && advertisementStatusModal.style.display === 'block') {
            cancelAdvertisementStatusChange();
        }
        
        if (productStatusWarningModal && productStatusWarningModal.style.display === 'block') {
            closeProductStatusWarning();
        }
    }
});

// Функции для работы с медиафайлами
function showMediaUploadModal() {
    const modal = document.getElementById('mediaUploadModal');
    modal.style.display = 'block';
    document.body.style.overflow = 'hidden';
}

function closeMediaUploadModal() {
    const modal = document.getElementById('mediaUploadModal');
    modal.style.display = 'none';
    document.body.style.overflow = 'auto';
    
    // Очищаем форму
    const fileInput = document.getElementById('media_files');
    if (fileInput) {
        fileInput.value = '';
    }
}

function deleteMedia(mediaId, fileType) {
    if (!confirm('Вы уверены, что хотите удалить этот медиафайл?')) {
        return;
    }
    
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    
    fetch(`/advertisements/{{ $advertisement->id }}/media/${mediaId}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': csrfToken,
            'Content-Type': 'application/json',
        },
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Перезагружаем страницу для обновления медиафайлов
            location.reload();
        } else {
            alert('Ошибка при удалении медиафайла');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Ошибка при удалении медиафайла');
    });
}

// Функции для работы с прогрессбаром загрузки
function showUploadProgress() {
    const overlay = document.getElementById('uploadOverlay');
    overlay.style.display = 'flex';
    document.body.style.overflow = 'hidden';
    
    updateUploadProgress(0, 'Подготовка к загрузке...');
}

function hideUploadProgress() {
    const overlay = document.getElementById('uploadOverlay');
    overlay.style.display = 'none';
    document.body.style.overflow = 'auto';
}

function updateUploadProgress(percent, text) {
    const progressBar = document.getElementById('uploadProgressBar');
    const progressText = document.getElementById('uploadProgressText');
    
    if (progressBar) {
        progressBar.style.width = percent + '%';
    }
    
    if (progressText) {
        progressText.textContent = text;
    }
}

function updateFileProgress(files) {
    const progressDetails = document.getElementById('uploadProgressDetails');
    const progressActions = document.getElementById('uploadProgressActions');
    
    progressDetails.style.display = 'block';
    progressActions.style.display = 'flex';
    
    let html = '';
    let completedFiles = 0;
    let totalFiles = files.length;
    
    files.forEach((file, index) => {
        if (file.status === 'success') {
            completedFiles++;
        }
        
        let statusClass = file.status;
        let statusText = file.statusText;
        
        // Улучшенные статусы
        switch(file.status) {
            case 'success':
                statusClass = 'success';
                statusText = '✓ Загружен';
                break;
            case 'loading':
                statusClass = 'loading';
                statusText = '⏳ Загружается...';
                break;
            case 'error':
                statusClass = 'error';
                statusText = '✗ Ошибка';
                break;
            default:
                statusClass = 'pending';
                statusText = '⏸ Ожидает';
        }
        
        html += `
            <div class="upload-progress-file">
                <span class="upload-progress-file-name" title="${file.fullName}">${file.name}</span>
                <span class="upload-progress-file-status ${statusClass}">${statusText}</span>
            </div>
        `;
    });
    
    // Добавляем заголовок с информацией о прогрессе
    const progressHeader = `
        <div class="upload-progress-file" style="background: #e3f2fd; margin: -15px -15px 10px -15px; padding: 10px 15px; border-radius: 8px 8px 0 0; font-weight: 600; color: #133E71;">
            Загружено: ${completedFiles} из ${totalFiles} файлов
        </div>
    `;
    
    progressDetails.innerHTML = progressHeader + html;
}

function showUploadSuccess() {
    updateUploadProgress(100, 'Медиафайлы успешно загружены!');
    
    const continueBtn = document.getElementById('continueBtn');
    if (continueBtn) {
        continueBtn.style.display = 'block';
        continueBtn.onclick = function() {
            hideUploadProgress();
            location.reload(); // Перезагружаем страницу для отображения новых медиафайлов
        };
    }
}

function uploadMediaFiles() {
    const fileInput = document.getElementById('media_files');
    const files = Array.from(fileInput.files);
    
    if (files.length === 0) {
        alert('Выберите файлы для загрузки');
        return;
    }
    
    // Показываем прогрессбар
    showUploadProgress();
    
    const formData = new FormData();
    files.forEach(file => {
        formData.append('media_files[]', file);
    });
    
    // Подготавливаем данные о файлах для отображения прогресса
    const fileProgressData = files.map(file => ({
        name: file.name.length > 30 ? file.name.substring(0, 27) + '...' : file.name,
        fullName: file.name,
        status: 'pending',
        statusText: 'Ожидает загрузки',
        size: file.size
    }));
    
    updateFileProgress(fileProgressData);
    
    // Создаем XMLHttpRequest для отслеживания прогресса
    const xhr = new XMLHttpRequest();
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    
    xhr.upload.addEventListener('progress', function(e) {
        if (e.lengthComputable) {
            const percentComplete = Math.round((e.loaded / e.total) * 100);
            updateUploadProgress(percentComplete, `Загрузка файлов: ${percentComplete}%`);
            
            // Более точное обновление статуса файлов
            const totalSize = e.total;
            const loadedSize = e.loaded;
            const filesPerPercent = files.length / 100;
            const currentFileIndex = Math.floor(percentComplete * filesPerPercent);
            
            fileProgressData.forEach((file, index) => {
                if (index < currentFileIndex) {
                    file.status = 'success';
                    file.statusText = '✓ Загружен';
                } else if (index === currentFileIndex) {
                    file.status = 'loading';
                    file.statusText = '⏳ Загружается...';
                } else {
                    file.status = 'pending';
                    file.statusText = '⏸ Ожидает';
                }
            });
            
            updateFileProgress(fileProgressData);
        }
    });
    
    xhr.addEventListener('load', function() {
        if (xhr.status === 200) {
            try {
                const response = JSON.parse(xhr.responseText);
                if (response.success) {
                    showUploadSuccess();
                } else {
                    updateUploadProgress(100, 'Ошибка при загрузке файлов');
                    alert(response.message || 'Ошибка при загрузке файлов');
                }
            } catch (e) {
                updateUploadProgress(100, 'Ошибка при обработке ответа');
                alert('Ошибка при обработке ответа сервера');
            }
        } else {
            updateUploadProgress(100, 'Ошибка при загрузке файлов');
            alert('Ошибка при загрузке файлов');
        }
    });
    
    xhr.addEventListener('error', function() {
        updateUploadProgress(100, 'Ошибка при загрузке файлов');
        alert('Ошибка при загрузке файлов');
    });
    
    xhr.open('POST', `/advertisements/{{ $advertisement->id }}/media`);
    xhr.setRequestHeader('X-CSRF-TOKEN', csrfToken);
    xhr.send(formData);
}

// Обработчик для кнопки отмены загрузки
document.addEventListener('DOMContentLoaded', function() {
    const cancelUploadBtn = document.getElementById('cancelUploadBtn');
    if (cancelUploadBtn) {
        cancelUploadBtn.addEventListener('click', function() {
            hideUploadProgress();
        });
    }
});
</script>

<!-- Модальное окно для загрузки медиафайлов -->
<div class="modal" id="mediaUploadModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Добавить медиафайлы</h3>
            <button class="modal-close" onclick="closeMediaUploadModal()">&times;</button>
        </div>
        <div class="modal-body">
            <div class="form-group">
                <label for="media_files">Выберите файлы:</label>
                <input type="file" id="media_files" name="media_files[]" multiple accept="image/*,video/*" class="form-control">
                <small class="form-text text-muted">
                    Поддерживаемые форматы: JPG, PNG, GIF, MP4, MOV, AVI. Максимальный размер файла: 50 МБ.
                </small>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" onclick="closeMediaUploadModal()">Отмена</button>
            <button type="button" class="btn btn-primary" onclick="uploadMediaFiles()">Загрузить</button>
        </div>
    </div>
</div>

<!-- Оверлей загрузки файлов -->
<div class="upload-overlay" id="uploadOverlay">
    <div class="upload-progress-container">
        <div class="upload-progress-header">
            <h3 class="upload-progress-title">
                <span class="upload-spinner" id="uploadSpinner"></span>
                Загрузка медиафайлов
            </h3>
            <p class="upload-progress-subtitle">Пожалуйста, дождитесь завершения загрузки файлов</p>
        </div>
        
        <div class="upload-progress-bar-container">
            <div class="upload-progress-bar" id="uploadProgressBar"></div>
        </div>
        
        <div class="upload-progress-text" id="uploadProgressText">
            Подготовка к загрузке...
        </div>
        
        <div class="upload-progress-details" id="uploadProgressDetails" style="display: none;">
            <div class="upload-progress-file">
                <span class="upload-progress-file-name">Файл 1</span>
                <span class="upload-progress-file-status">Загружается...</span>
            </div>
        </div>
        
        <div class="upload-progress-actions" id="uploadProgressActions" style="display: none;">
            <button class="upload-progress-btn secondary" id="cancelUploadBtn">Отмена</button>
            <button class="upload-progress-btn primary" id="continueBtn" style="display: none;">Продолжить</button>
        </div>
    </div>
</div>

@endsection 