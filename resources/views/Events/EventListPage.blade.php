@extends('layouts.layout')

@section('title', 'События')

@section('header-title')
    <h1 class="header-title">События</h1>
@endsection

@section('content')
<div class="events-main-container">
    <div class="events-header">
        <div class="breadcrumb">
            <a href="/">Главная</a> / События
        </div>
        <div class="events-header-content">
            <h1 class="events-title">События системы</h1>
            <p class="events-subtitle">Мониторинг задач, дедлайнов и активности в системе</p>
        </div>
    </div>

    <div class="events-content">
        <div class="events-grid">
            <a href="/events/active" class="event-card">
                <div class="event-card-icon active">
                    <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10"></circle>
                        <polyline points="12,6 12,12 16,14"></polyline>
                    </svg>
                </div>
                <div class="event-card-content">
                    <h3 class="event-card-title">Активные задачи</h3>
                    <p class="event-card-description">Текущие задачи в работе</p>
                    <div class="event-card-count">{{ $activeTasks ?? 0 }}</div>
                </div>
                <div class="event-card-arrow">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="9,18 15,12 9,6"></polyline>
                    </svg>
                </div>
            </a>

            <a href="/events/expired" class="event-card">
                <div class="event-card-icon expired">
                    <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path>
                        <line x1="12" y1="9" x2="12" y2="13"></line>
                        <line x1="12" y1="17" x2="12.01" y2="17"></line>
                    </svg>
                </div>
                <div class="event-card-content">
                    <h3 class="event-card-title">Просроч. задачи</h3>
                    <p class="event-card-description">Задачи с истекшим сроком</p>
                    <div class="event-card-count expired-count">{{ $expiredTasks ?? 0 }}</div>
                </div>
                <div class="event-card-arrow">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="9,18 15,12 9,6"></polyline>
                    </svg>
                </div>
            </a>

            <a href="/events/logs" class="event-card">
                <div class="event-card-icon logs">
                    <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                        <polyline points="14,2 14,8 20,8"></polyline>
                        <line x1="16" y1="13" x2="8" y2="13"></line>
                        <line x1="16" y1="17" x2="8" y2="17"></line>
                        <polyline points="10,9 9,9 8,9"></polyline>
                    </svg>
                </div>
                <div class="event-card-content">
                    <h3 class="event-card-title">Последние логи</h3>
                    <p class="event-card-description">Активность в системе</p>
                    <div class="event-card-date">{{ $lastLogDate ?? 'Нет логов' }}</div>
                </div>
                <div class="event-card-arrow">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="9,18 15,12 9,6"></polyline>
                    </svg>
                </div>
            </a>
        </div>
    </div>
</div>

<style>
.events-main-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 20px;
}

.events-header {
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

.events-header-content {
    text-align: center;
    margin-bottom: 30px;
}

.events-title {
    font-size: 32px;
    color: #133E71;
    margin: 0 0 10px 0;
    font-weight: 600;
}

.events-subtitle {
    font-size: 16px;
    color: #666;
    margin: 0;
    line-height: 1.5;
}

.events-content {
    background: white;
    border-radius: 12px;
    padding: 30px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.08);
}

.events-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
    gap: 25px;
}

.event-card {
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

.event-card::before {
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

.event-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.15);
    border-color: #133E71;
    background: white;
}

.event-card:hover::before {
    transform: scaleX(1);
}

.event-card-icon {
    flex-shrink: 0;
    width: 60px;
    height: 60px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    transition: all 0.3s ease;
}

.event-card-icon.active {
    background: linear-gradient(135deg, #28a745, #20c997);
}

.event-card-icon.expired {
    background: linear-gradient(135deg, #dc3545, #fd7e14);
}

.event-card-icon.logs {
    background: linear-gradient(135deg, #6f42c1, #e83e8c);
}

.event-card:hover .event-card-icon {
    transform: scale(1.1);
    box-shadow: 0 4px 15px rgba(19, 62, 113, 0.3);
}

.event-card-content {
    flex: 1;
    min-width: 0;
}

.event-card-title {
    font-size: 20px;
    color: #133E71;
    margin: 0 0 8px 0;
    font-weight: 600;
    transition: color 0.3s ease;
}

.event-card:hover .event-card-title {
    color: #1C5BA4;
}

.event-card-description {
    font-size: 14px;
    color: #666;
    margin: 0 0 10px 0;
    line-height: 1.4;
}

.event-card-count {
    font-size: 24px;
    font-weight: 700;
    color: #28a745;
    margin: 0;
}

.event-card-count.expired-count {
    color: #dc3545;
}

.event-card-date {
    font-size: 14px;
    font-weight: 600;
    color: #6f42c1;
    margin: 0;
}

.event-card-arrow {
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

.event-card:hover .event-card-arrow {
    background: #133E71;
    color: white;
    transform: translateX(3px);
}

/* Адаптивность */
@media (max-width: 768px) {
    .events-main-container {
        padding: 15px;
    }
    
    .events-title {
        font-size: 24px;
    }
    
    .events-subtitle {
        font-size: 14px;
    }
    
    .events-content {
        padding: 20px;
    }
    
    .events-grid {
        grid-template-columns: 1fr;
        gap: 20px;
    }
    
    .event-card {
        padding: 20px;
        gap: 15px;
    }
    
    .event-card-icon {
        width: 50px;
        height: 50px;
    }
    
    .event-card-title {
        font-size: 18px;
    }
    
    .event-card-description {
        font-size: 13px;
    }
    
    .event-card-count {
        font-size: 20px;
    }
}

@media (max-width: 480px) {
    .event-card {
        flex-direction: column;
        text-align: center;
        gap: 15px;
    }
    
    .event-card-arrow {
        align-self: center;
    }
}
</style>
@endsection
