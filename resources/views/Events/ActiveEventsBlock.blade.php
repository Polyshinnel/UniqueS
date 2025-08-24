@extends('layouts.layout')

@section('title', 'Активные задачи')

@section('header-title')
    <h1 class="header-title">Активные задачи</h1>
@endsection

@section('content')
<div class="active-tasks-container">
    <div class="active-tasks-header">
        <div class="breadcrumb">
            <a href="/">Главная</a> / 
            <a href="{{ route('events.index') }}">События</a> / 
            Активные задачи
        </div>
        <div class="tasks-info">
            <div class="tasks-count">
                Всего задач: {{ $paginator->total() }}
            </div>
            @if($isAdmin)
                <div class="admin-badge">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M12 2L2 7l10 5 10-5-10-5z"></path>
                        <path d="M2 17l10 5 10-5"></path>
                        <path d="M2 12l10 5 10-5"></path>
                    </svg>
                    Режим администратора
                </div>
            @endif
        </div>
    </div>

    <div class="active-tasks-content">
        @if($paginator->count() > 0)
            <div class="tasks-table-wrapper">
                <table class="tasks-table">
                    <thead>
                        <tr>
                            <th>Тип</th>
                            <th>Задача</th>
                            <th>Связанный объект</th>
                            <th>Срок выполнения</th>
                            <th>Ответственный</th>
                            <th>Дата создания</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($paginator as $task)
                        <tr class="task-row">
                            <td class="task-type-cell">
                                <div class="task-type-badge task-type-{{ $task['type'] }}">
                                    @switch($task['type'])
                                        @case('product')
                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <path d="M20 7L10 17L5 12"></path>
                                            </svg>
                                            Товар
                                            @break
                                        @case('company')
                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                                                <circle cx="9" cy="7" r="4"></circle>
                                                <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                                                <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                                            </svg>
                                            Компания
                                            @break
                                        @case('advertisement')
                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                                                <circle cx="8.5" cy="8.5" r="1.5"></circle>
                                                <polyline points="21,15 16,10 5,21"></polyline>
                                            </svg>
                                            Объявление
                                            @break
                                    @endswitch
                                </div>
                            </td>
                            
                            <td class="task-action-cell">
                                <div class="task-action-text">
                                    {{ $task['action'] }}
                                </div>
                            </td>
                            
                            <td class="task-related-cell">
                                <a href="{{ $task['route'] }}" class="task-related-link">
                                    <div class="task-related-name">{{ $task['related_name'] }}</div>
                                    @if($task['related_sku'])
                                        <div class="task-related-sku">SKU: {{ $task['related_sku'] }}</div>
                                    @endif
                                </a>
                            </td>
                            
                            <td class="task-expired-cell">
                                @php
                                    $now = \Carbon\Carbon::now();
                                    $expiredAt = \Carbon\Carbon::parse($task['expired_at']);
                                    $daysLeft = $now->diffInDays($expiredAt, false);
                                    $isUrgent = $daysLeft <= 3 && $daysLeft >= 0;
                                    $isOverdue = $daysLeft < 0;
                                @endphp
                                
                                <div class="task-expired-date {{ $isUrgent ? 'urgent' : '' }} {{ $isOverdue ? 'overdue' : '' }}">
                                    {{ $expiredAt->format('d.m.Y H:i') }}
                                    @if($isUrgent)
                                        <div class="urgent-badge">
                                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <circle cx="12" cy="12" r="10"></circle>
                                                <polyline points="12,6 12,12 16,14"></polyline>
                                            </svg>
                                            Срочно!
                                        </div>
                                    @elseif($isOverdue)
                                        <div class="overdue-badge">
                                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <circle cx="12" cy="12" r="10"></circle>
                                                <line x1="15" y1="9" x2="9" y2="15"></line>
                                                <line x1="9" y1="9" x2="15" y2="15"></line>
                                            </svg>
                                            Просрочено
                                        </div>
                                    @endif
                                </div>
                            </td>
                            
                            <td class="task-user-cell">
                                <div class="task-user-info">
                                    <div class="task-user-name">{{ $task['user']->name ?? 'Не указан' }}</div>
                                    <div class="task-user-email">{{ $task['user']->email ?? '' }}</div>
                                </div>
                            </td>
                            
                            <td class="task-created-cell">
                                <div class="task-created-date">
                                    {{ \Carbon\Carbon::parse($task['created_at'])->format('d.m.Y H:i') }}
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            <!-- Пагинация -->
            @if($paginator->total() > 0)
            <div class="pagination-wrapper">
                <div class="pagination-info">
                    Показано {{ $paginator->firstItem() ?? 0 }} - {{ $paginator->lastItem() ?? 0 }} из {{ $paginator->total() }} задач
                </div>
                @if($paginator->hasPages())
                <div class="pagination-links">
                    <ul class="pagination">
                        {{-- Предыдущая страница --}}
                        @if ($paginator->onFirstPage())
                            <li class="page-item disabled">
                                <span class="page-link">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <polyline points="15,18 9,12 15,6"></polyline>
                                    </svg>
                                </span>
                            </li>
                        @else
                            <li class="page-item">
                                <a class="page-link" href="{{ $paginator->previousPageUrl() }}" rel="prev">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <polyline points="15,18 9,12 15,6"></polyline>
                                    </svg>
                                </a>
                            </li>
                        @endif

                        {{-- Номера страниц --}}
                        @foreach ($paginator->getUrlRange(1, $paginator->lastPage()) as $page => $url)
                            @if ($page == $paginator->currentPage())
                                <li class="page-item active">
                                    <span class="page-link">{{ $page }}</span>
                                </li>
                            @else
                                <li class="page-item">
                                    <a class="page-link" href="{{ $url }}">{{ $page }}</a>
                                </li>
                            @endif
                        @endforeach

                        {{-- Следующая страница --}}
                        @if ($paginator->hasMorePages())
                            <li class="page-item">
                                <a class="page-link" href="{{ $paginator->nextPageUrl() }}" rel="next">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <polyline points="9,18 15,12 9,6"></polyline>
                                    </svg>
                                </a>
                            </li>
                        @else
                            <li class="page-item disabled">
                                <span class="page-link">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <polyline points="9,18 15,12 9,6"></polyline>
                                    </svg>
                                </span>
                            </li>
                        @endif
                    </ul>
                </div>
                @endif
            </div>
            @endif
        @else
            <div class="empty-state">
                <div class="empty-content">
                    <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10"></circle>
                        <polyline points="12,6 12,12 16,14"></polyline>
                    </svg>
                    <h3>Нет активных задач</h3>
                    <p>Все задачи выполнены или еще не созданы</p>
                    <a href="{{ route('events.index') }}" class="btn btn-primary">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="15,18 9,12 15,6"></polyline>
                        </svg>
                        Вернуться к событиям
                    </a>
                </div>
            </div>
        @endif
    </div>
</div>
@endsection

@push('styles')
<style>
.active-tasks-container {
    max-width: 1400px;
    margin: 0 auto;
    padding: 20px;
}

.active-tasks-header {
    margin-bottom: 30px;
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

.tasks-info {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
}

.tasks-count {
    font-size: 16px;
    font-weight: 600;
    color: #133E71;
}

.admin-badge {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 8px 16px;
    background: linear-gradient(135deg, #ff6b6b, #ee5a24);
    color: white;
    border-radius: 20px;
    font-size: 14px;
    font-weight: 500;
}

.admin-badge svg {
    width: 16px;
    height: 16px;
}

.tasks-table-wrapper {
    background: white;
    border-radius: 12px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.1);
    overflow: hidden;
    margin-bottom: 30px;
}

.tasks-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 14px;
}

.tasks-table th {
    background: linear-gradient(180deg, #c5e0b3 0%, #a8d08a 100%);
    color: #2d5016;
    padding: 16px 12px;
    text-align: left;
    font-weight: 600;
    font-size: 13px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    border-bottom: 2px solid #8fbc8f;
}

.tasks-table th:first-child {
    padding-left: 20px;
}

.tasks-table th:last-child {
    padding-right: 20px;
}

.task-row {
    border-bottom: 1px solid #f1f3f4;
    transition: all 0.3s ease;
}

.task-row:hover {
    background-color: #f8f9fa;
    transform: translateY(-1px);
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.task-row:last-child {
    border-bottom: none;
}

.tasks-table td {
    padding: 16px 12px;
    vertical-align: top;
}

.tasks-table td:first-child {
    padding-left: 20px;
}

.tasks-table td:last-child {
    padding-right: 20px;
}

/* Стили для типа задачи */
.task-type-cell {
    min-width: 120px;
}

.task-type-badge {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 8px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: white;
}

.task-type-product {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.task-type-company {
    background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
}

.task-type-advertisement {
    background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
}

.task-type-badge svg {
    width: 16px;
    height: 16px;
}

/* Стили для описания задачи */
.task-action-cell {
    min-width: 300px;
    max-width: 400px;
}

.task-action-text {
    font-size: 14px;
    color: #333;
    line-height: 1.4;
    padding: 12px;
    background: #f8f9fa;
    border-radius: 8px;
    border-left: 4px solid #133E71;
}

/* Стили для связанного объекта */
.task-related-cell {
    min-width: 200px;
}

.task-related-link {
    display: block;
    text-decoration: none;
    color: inherit;
    padding: 12px;
    background: #e8f0fe;
    border-radius: 8px;
    border: 1px solid #d1e7dd;
    transition: all 0.3s ease;
}

.task-related-link:hover {
    background: #d1e7dd;
    transform: translateY(-1px);
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.task-related-name {
    font-size: 14px;
    font-weight: 600;
    color: #133E71;
    margin-bottom: 4px;
}

.task-related-sku {
    font-size: 12px;
    color: #666;
}

/* Стили для срока выполнения */
.task-expired-cell {
    min-width: 150px;
}

.task-expired-date {
    display: flex;
    flex-direction: column;
    gap: 6px;
    padding: 12px;
    background: #f8f9fa;
    border-radius: 8px;
    border: 1px solid #e9ecef;
}

.task-expired-date.urgent {
    background: #fff3cd;
    border-color: #ffeaa7;
}

.task-expired-date.overdue {
    background: #f8d7da;
    border-color: #f5c6cb;
}

.urgent-badge, .overdue-badge {
    display: flex;
    align-items: center;
    gap: 4px;
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.urgent-badge {
    color: #856404;
}

.overdue-badge {
    color: #721c24;
}

.urgent-badge svg, .overdue-badge svg {
    width: 12px;
    height: 12px;
}

/* Стили для ответственного */
.task-user-cell {
    min-width: 150px;
}

.task-user-info {
    display: flex;
    flex-direction: column;
    gap: 4px;
    padding: 12px;
    background: #f8f9fa;
    border-radius: 8px;
    border: 1px solid #e9ecef;
}

.task-user-name {
    font-size: 14px;
    font-weight: 600;
    color: #333;
}

.task-user-email {
    font-size: 12px;
    color: #666;
}

/* Стили для даты создания */
.task-created-cell {
    min-width: 120px;
}

.task-created-date {
    font-size: 13px;
    color: #666;
    padding: 12px;
    background: #f8f9fa;
    border-radius: 8px;
    border: 1px solid #e9ecef;
    text-align: center;
}

/* Стили для пустого состояния */
.empty-state {
    text-align: center;
    padding: 80px 20px;
}

.empty-content {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 20px;
    color: #666;
}

.empty-content svg {
    color: #ccc;
}

.empty-content h3 {
    font-size: 24px;
    color: #333;
    margin: 0;
}

.empty-content p {
    font-size: 16px;
    margin: 0;
}

/* Стили для пагинации */
.pagination-wrapper {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 20px;
    background: white;
    border-radius: 12px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.1);
}

.pagination-info {
    font-size: 14px;
    color: #666;
    font-weight: 500;
}

.pagination-links {
    display: flex;
    align-items: center;
    gap: 8px;
}

.pagination-links .pagination {
    display: flex;
    list-style: none;
    margin: 0;
    padding: 0;
    gap: 6px;
    align-items: center;
}

.pagination-links .page-item {
    margin: 0;
}

.pagination-links .page-link {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 40px;
    height: 40px;
    border-radius: 8px;
    background: #f8f9fa;
    color: #133E71;
    text-decoration: none;
    font-weight: 500;
    font-size: 14px;
    border: 1px solid #e9ecef;
    transition: all 0.3s ease;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.pagination-links .page-link:hover {
    background: #133E71;
    color: white;
    transform: translateY(-1px);
    box-shadow: 0 4px 8px rgba(19, 62, 113, 0.3);
}

.pagination-links .page-item.active .page-link {
    background: #133E71;
    color: white;
    border-color: #133E71;
    box-shadow: 0 4px 8px rgba(19, 62, 113, 0.3);
}

.pagination-links .page-item.disabled .page-link {
    background: #f5f5f5;
    color: #999;
    cursor: not-allowed;
    border-color: #e9ecef;
    opacity: 0.6;
}

.pagination-links .page-item.disabled .page-link:hover {
    background: #f5f5f5;
    color: #999;
    transform: none;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.pagination-links .page-link svg {
    width: 16px;
    height: 16px;
    color: inherit;
}

/* Стили для кнопки */
.btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 12px 24px;
    background: #133E71;
    color: white;
    border: none;
    border-radius: 8px;
    font-size: 14px;
    font-weight: 500;
    text-decoration: none;
    transition: all 0.3s ease;
    box-shadow: 0 2px 8px rgba(19, 62, 113, 0.3);
}

.btn:hover {
    background: #1C5BA4;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(19, 62, 113, 0.4);
}

.btn svg {
    width: 16px;
    height: 16px;
}

/* Адаптивность */
@media (max-width: 1200px) {
    .tasks-table {
        font-size: 13px;
    }
    
    .tasks-table th,
    .tasks-table td {
        padding: 12px 8px;
    }
    
    .tasks-table th:first-child,
    .tasks-table td:first-child {
        padding-left: 15px;
    }
    
    .tasks-table th:last-child,
    .tasks-table td:last-child {
        padding-right: 15px;
    }
}

@media (max-width: 768px) {
    .active-tasks-container {
        padding: 10px;
    }
    
    .tasks-table-wrapper {
        border-radius: 8px;
    }
    
    .tasks-table {
        font-size: 12px;
    }
    
    .tasks-table th,
    .tasks-table td {
        padding: 10px 6px;
    }
    
    .task-action-text,
    .task-related-name {
        font-size: 13px;
    }
    
    .task-type-badge {
        font-size: 11px;
        padding: 6px 10px;
    }
    
    .task-type-badge svg {
        width: 14px;
        height: 14px;
    }
    
    /* Адаптивность для пагинации */
    .pagination-wrapper {
        flex-direction: column;
        gap: 15px;
        text-align: center;
    }
    
    .pagination-links .page-link {
        width: 36px;
        height: 36px;
        font-size: 13px;
    }
    
    .pagination-links .page-link svg {
        width: 14px;
        height: 14px;
    }
}

/* Анимации */
@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.task-row {
    animation: fadeIn 0.3s ease;
}
</style>
@endpush
