@extends('layouts.layout')

@section('title', 'Справочники')

@section('content')
<div class="guides-main-container">
    <div class="guides-header">
        <div class="breadcrumb">
            <a href="/">Главная</a> / Справочники
        </div>
        <div class="guides-header-content">
            <h1 class="guides-title">Справочники системы</h1>
            <p class="guides-subtitle">Управление справочными данными и настройками системы</p>
        </div>
    </div>

    <div class="guides-content">
        <div class="guides-grid">
            <a href="/guide/users" class="guide-card">
                <div class="guide-card-icon">
                    <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                        <circle cx="12" cy="7" r="4"></circle>
                    </svg>
                </div>
                <div class="guide-card-content">
                    <h3 class="guide-card-title">Сотрудники</h3>
                    <p class="guide-card-description">Управление пользователями системы и их ролями</p>
                </div>
                <div class="guide-card-arrow">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="9,18 15,12 9,6"></polyline>
                    </svg>
                </div>
            </a>

            <a href="/guide/regions" class="guide-card">
                <div class="guide-card-icon">
                    <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                        <circle cx="12" cy="10" r="3"></circle>
                    </svg>
                </div>
                <div class="guide-card-content">
                    <h3 class="guide-card-title">Регионы</h3>
                    <p class="guide-card-description">Настройка региональных зон и территорий</p>
                </div>
                <div class="guide-card-arrow">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="9,18 15,12 9,6"></polyline>
                    </svg>
                </div>
            </a>

            <a href="/guide/sources" class="guide-card">
                <div class="guide-card-icon">
                    <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path>
                        <line x1="12" y1="9" x2="12" y2="13"></line>
                        <line x1="12" y1="17" x2="12.01" y2="17"></line>
                    </svg>
                </div>
                <div class="guide-card-content">
                    <h3 class="guide-card-title">Источники</h3>
                    <p class="guide-card-description">Управление источниками контактов и лидов</p>
                </div>
                <div class="guide-card-arrow">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="9,18 15,12 9,6"></polyline>
                    </svg>
                </div>
            </a>

            <a href="/guide/categories" class="guide-card">
                <div class="guide-card-icon">
                    <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="3" y="3" width="7" height="7"></rect>
                        <rect x="14" y="3" width="7" height="7"></rect>
                        <rect x="14" y="14" width="7" height="7"></rect>
                        <rect x="3" y="14" width="7" height="7"></rect>
                    </svg>
                </div>
                <div class="guide-card-content">
                    <h3 class="guide-card-title">Категории</h3>
                    <p class="guide-card-description">Классификация товаров и услуг по категориям</p>
                </div>
                <div class="guide-card-arrow">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="9,18 15,12 9,6"></polyline>
                    </svg>
                </div>
            </a>

            <a href="/guide/warehouses" class="guide-card">
                <div class="guide-card-icon">
                    <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
                        <polyline points="9,22 9,12 15,12 15,22"></polyline>
                    </svg>
                </div>
                <div class="guide-card-content">
                    <h3 class="guide-card-title">Склады</h3>
                    <p class="guide-card-description">Управление складскими помещениями и логистикой</p>
                </div>
                <div class="guide-card-arrow">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="9,18 15,12 9,6"></polyline>
                    </svg>
                </div>
            </a>
        </div>
    </div>
</div>

<style>
.guides-main-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 20px;
}

.guides-header {
    margin-bottom: 40px;
}

.breadcrumb {
    font-size: 14px;
    color: #666;
    margin-bottom: 15px;
}

.breadcrumb a {
    color: #133E71;
    text-decoration: none;
    transition: color 0.3s ease;
}

.breadcrumb a:hover {
    color: #1C5BA4;
    text-decoration: underline;
}

.guides-header-content {
    text-align: center;
    margin-bottom: 30px;
}

.guides-title {
    font-size: 32px;
    color: #133E71;
    margin: 0 0 10px 0;
    font-weight: 600;
}

.guides-subtitle {
    font-size: 16px;
    color: #666;
    margin: 0;
    line-height: 1.5;
}

.guides-content {
    background: white;
    border-radius: 12px;
    padding: 30px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.08);
}

.guides-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
    gap: 25px;
}

.guide-card {
    display: flex;
    align-items: center;
    gap: 20px;
    padding: 25px;
    background: #f8f9fa;
    border: 1px solid #e9ecef;
    border-radius: 12px;
    text-decoration: none;
    color: inherit;
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
}

.guide-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 3px;
    background: linear-gradient(90deg, #133E71, #1C5BA4);
    transform: scaleX(0);
    transition: transform 0.3s ease;
}

.guide-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.15);
    border-color: #133E71;
    background: white;
}

.guide-card:hover::before {
    transform: scaleX(1);
}

.guide-card-icon {
    flex-shrink: 0;
    width: 60px;
    height: 60px;
    background: linear-gradient(135deg, #133E71, #1C5BA4);
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    transition: all 0.3s ease;
}

.guide-card:hover .guide-card-icon {
    transform: scale(1.1);
    box-shadow: 0 4px 15px rgba(19, 62, 113, 0.3);
}

.guide-card-content {
    flex: 1;
    min-width: 0;
}

.guide-card-title {
    font-size: 20px;
    color: #133E71;
    margin: 0 0 8px 0;
    font-weight: 600;
    transition: color 0.3s ease;
}

.guide-card:hover .guide-card-title {
    color: #1C5BA4;
}

.guide-card-description {
    font-size: 14px;
    color: #666;
    margin: 0;
    line-height: 1.4;
}

.guide-card-arrow {
    flex-shrink: 0;
    width: 32px;
    height: 32px;
    background: #e9ecef;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #666;
    transition: all 0.3s ease;
}

.guide-card:hover .guide-card-arrow {
    background: #133E71;
    color: white;
    transform: translateX(3px);
}

/* Адаптивность */
@media (max-width: 768px) {
    .guides-main-container {
        padding: 15px;
    }
    
    .guides-title {
        font-size: 24px;
    }
    
    .guides-subtitle {
        font-size: 14px;
    }
    
    .guides-content {
        padding: 20px;
    }
    
    .guides-grid {
        grid-template-columns: 1fr;
        gap: 20px;
    }
    
    .guide-card {
        padding: 20px;
        gap: 15px;
    }
    
    .guide-card-icon {
        width: 50px;
        height: 50px;
    }
    
    .guide-card-title {
        font-size: 18px;
    }
    
    .guide-card-description {
        font-size: 13px;
    }
}

@media (max-width: 480px) {
    .guide-card {
        flex-direction: column;
        text-align: center;
        gap: 15px;
    }
    
    .guide-card-arrow {
        align-self: center;
    }
}
</style>
@endsection
