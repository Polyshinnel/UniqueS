@extends('layouts.layout')

@section('title', 'Просмотр объявления')

@section('search-filter')
@endsection

@section('header-action-btn')
    <div class="header-actions">
        <a href="{{ route('advertisements.edit', $advertisement) }}" class="btn btn-primary">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                <path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
            Редактировать
        </a>
        <a href="{{ route('advertisements.index') }}" class="btn btn-outline">Назад к списку</a>
    </div>
@endsection

@section('header-title')
    <h1 class="header-title">{{ $advertisement->title }}</h1>
    <span class="status-badge status-{{ $advertisement->status }}">{{ $advertisement->status_name }}</span>
@endsection

@section('content')
<div class="advertisement-show-container">
    <div class="advertisement-content">
        <!-- Медиафайлы -->
        @if($advertisement->mediaOrdered->count() > 0)
        <div class="media-section">
            <div class="media-gallery">
                @foreach($advertisement->mediaOrdered as $media)
                    <div class="media-item">
                        @if($media->file_type === 'image')
                            <img src="{{ asset('storage/' . $media->file_path) }}" alt="{{ $media->file_name }}">
                        @else
                            <video controls>
                                <source src="{{ asset('storage/' . $media->file_path) }}" type="{{ $media->mime_type }}">
                                Ваш браузер не поддерживает воспроизведение видео.
                            </video>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
        @endif

        <!-- Основная информация -->
        <div class="info-section">
            <h2>Основная информация</h2>
            <div class="info-grid">
                <div class="info-item">
                    <label>Категория:</label>
                    <span>{{ $advertisement->category->name }}</span>
                </div>
                <div class="info-item">
                    <label>Товар:</label>
                    <span>{{ $advertisement->product->name }}</span>
                </div>
                <div class="info-item">
                    <label>Поставщик:</label>
                    <span>{{ $advertisement->product->company->name }}</span>
                </div>
                <div class="info-item">
                    <label>Создал:</label>
                    <span>{{ $advertisement->creator->name ?? 'Неизвестно' }}</span>
                </div>
                <div class="info-item">
                    <label>Создано:</label>
                    <span>{{ $advertisement->created_at->format('d.m.Y H:i') }}</span>
                </div>
                @if($advertisement->published_at)
                <div class="info-item">
                    <label>Опубликовано:</label>
                    <span>{{ $advertisement->published_at->format('d.m.Y H:i') }}</span>
                </div>
                @endif
            </div>
        </div>

        <!-- Характеристики -->
        @if($advertisement->main_characteristics)
        <div class="content-section">
            <h3>Основные характеристики</h3>
            <p>{{ $advertisement->main_characteristics }}</p>
        </div>
        @endif

        @if($advertisement->complectation)
        <div class="content-section">
            <h3>Комплектация</h3>
            <p>{{ $advertisement->complectation }}</p>
        </div>
        @endif

        @if($advertisement->technical_characteristics)
        <div class="content-section">
            <h3>Технические характеристики</h3>
            <p>{{ $advertisement->technical_characteristics }}</p>
        </div>
        @endif

        @if($advertisement->additional_info)
        <div class="content-section">
            <h3>Дополнительная информация</h3>
            <p>{{ $advertisement->additional_info }}</p>
        </div>
        @endif

        <!-- Данные проверки -->
        @if(isset($advertisement->check_data['status_id']) || isset($advertisement->check_data['status_comment']))
        <div class="content-section">
            <h3>Проверка</h3>
            @if(isset($advertisement->check_data['status_id']))
                <p><strong>Статус:</strong> {{ $advertisement->check_data['status_id'] }}</p>
            @endif
            @if(isset($advertisement->check_data['status_comment']))
                <p><strong>Комментарий:</strong> {{ $advertisement->check_data['status_comment'] }}</p>
            @endif
        </div>
        @endif

        <!-- Данные погрузки -->
        @if(isset($advertisement->loading_data['loading_type']) || isset($advertisement->loading_data['loading_comment']))
        <div class="content-section">
            <h3>Погрузка</h3>
            @if(isset($advertisement->loading_data['loading_type']))
                <p><strong>Тип погрузки:</strong> {{ $advertisement->loading_data['loading_type'] }}</p>
            @endif
            @if(isset($advertisement->loading_data['loading_comment']))
                <p><strong>Комментарий:</strong> {{ $advertisement->loading_data['loading_comment'] }}</p>
            @endif
        </div>
        @endif

        <!-- Данные демонтажа -->
        @if(isset($advertisement->removal_data['removal_type']) || isset($advertisement->removal_data['removal_comment']))
        <div class="content-section">
            <h3>Демонтаж</h3>
            @if(isset($advertisement->removal_data['removal_type']))
                <p><strong>Тип демонтажа:</strong> {{ $advertisement->removal_data['removal_type'] }}</p>
            @endif
            @if(isset($advertisement->removal_data['removal_comment']))
                <p><strong>Комментарий:</strong> {{ $advertisement->removal_data['removal_comment'] }}</p>
            @endif
        </div>
        @endif
    </div>
</div>

<style>
.advertisement-show-container {
    padding: 20px;
    max-width: 1200px;
    margin: 0 auto;
}

.status-badge {
    padding: 4px 12px;
    border-radius: 12px;
    font-size: 12px;
    font-weight: 500;
    margin-left: 15px;
}

.status-draft {
    background: #fef3c7;
    color: #92400e;
}

.status-active {
    background: #d1fae5;
    color: #065f46;
}

.status-inactive {
    background: #fee2e2;
    color: #991b1b;
}

.status-archived {
    background: #f3f4f6;
    color: #374151;
}

.advertisement-content {
    background: white;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    overflow: hidden;
}

.media-section {
    padding: 20px;
    border-bottom: 1px solid #e5e7eb;
}

.media-gallery {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 15px;
}

.media-item img,
.media-item video {
    width: 100%;
    height: 200px;
    object-fit: cover;
    border-radius: 8px;
}

.info-section,
.content-section {
    padding: 20px;
    border-bottom: 1px solid #e5e7eb;
}

.content-section:last-child {
    border-bottom: none;
}

.info-section h2,
.content-section h3 {
    margin: 0 0 15px 0;
    color: #133E71;
    font-size: 18px;
    font-weight: 600;
}

.info-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 15px;
}

.info-item {
    display: flex;
    flex-direction: column;
}

.info-item label {
    font-weight: 600;
    color: #374151;
    margin-bottom: 4px;
    font-size: 14px;
}

.info-item span {
    color: #111827;
    font-size: 16px;
}

.content-section p {
    margin: 0;
    line-height: 1.6;
    color: #111827;
}

.header-actions {
    display: flex;
    gap: 10px;
    align-items: center;
}

.btn {
    padding: 8px 16px;
    border-radius: 6px;
    text-decoration: none;
    font-weight: 500;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    border: none;
    cursor: pointer;
    transition: all 0.2s ease;
}

.btn-primary {
    background: #133E71;
    color: white;
}

.btn-primary:hover {
    background: #0f2a52;
}

.btn-outline {
    background: white;
    color: #133E71;
    border: 1px solid #133E71;
}

.btn-outline:hover {
    background: #133E71;
    color: white;
}
</style>
@endsection 