@extends('layouts.layout')

@section('title', 'Логи')

@section('header-title')
    <h1 class="header-title">Логи</h1>
@endsection

@section('content')
<div class="logs-container">
    <div class="logs-header">
        <div class="breadcrumb">
            <a href="/">Главная</a> / 
            <a href="{{ route('events.index') }}">События</a> / 
            Логи
        </div>
        <div class="logs-info">
            <div class="logs-count">
                Всего логов: {{ $paginator->total() }}
            </div>
            @if($isAdmin)
                <div class="admin-controls">
                    <div class="admin-badge">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M12 2L2 7l10 5 10-5-10-5z"></path>
                            <path d="M2 17l10 5 10-5"></path>
                            <path d="M2 12l10 5 10-5"></path>
                        </svg>
                        Режим администратора
                    </div>
                    <div class="user-filter">
                        <form method="GET" action="{{ request()->url() }}" class="filter-form">
                            <select name="user_id" class="user-select" onchange="this.form.submit()">
                                <option value="">Все пользователи</option>
                                <option value="system" {{ request('user_id') == 'system' ? 'selected' : '' }}>Система</option>
                                @if(isset($usersForFilter))
                                    @foreach($usersForFilter as $user)
                                        <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>
                                            {{ $user->name }} ({{ $user->email }})
                                        </option>
                                    @endforeach
                                @endif
                            </select>
                            @if(request('user_id'))
                                <a href="{{ request()->url() }}" class="clear-filter">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <line x1="18" y1="6" x2="6" y2="18"></line>
                                        <line x1="6" y1="6" x2="18" y2="18"></line>
                                    </svg>
                                </a>
                            @endif
                        </form>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <div class="logs-content">
        @if($paginator->count() > 0)
            <div class="logs-table-wrapper">
                <table class="logs-table">
                    <thead>
                        <tr>
                            <th>Тип</th>
                            <th>Связанный объект</th>
                            <th>Сообщение</th>
                            <th>Тип лога</th>
                            <th>Пользователь</th>
                            <th>Дата создания</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($paginator as $log)
                        <tr class="log-row">
                            <td class="log-type-cell">
                                <div class="log-type-badge log-type-{{ $log['type'] }}">
                                    @switch($log['type'])
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
                            
                            <td class="log-related-cell">
                                <a href="{{ $log['route'] }}" class="log-related-link">
                                    <div class="log-related-name">{{ $log['related_name'] }}</div>
                                    @if($log['related_sku'])
                                        <div class="log-related-sku">SKU: {{ $log['related_sku'] }}</div>
                                    @endif
                                </a>
                            </td>
                            
                            <td class="log-message-cell">
                                <div class="log-message-text">
                                    {{ $log['log'] }}
                                </div>
                            </td>
                            
                            <td class="log-category-cell">
                                @if($log['log_type'])
                                    <div class="log-category-badge" style="background-color: {{ $log['log_type']->color }}">
                                        {{ $log['log_type']->name }}
                                    </div>
                                @else
                                    <div class="log-category-badge log-category-default">
                                        Неизвестный тип
                                    </div>
                                @endif
                            </td>
                            
                            <td class="log-user-cell">
                                @if($log['user'])
                                    <div class="log-user-info">
                                        <div class="log-user-name">{{ $log['user']->name }}</div>
                                        <div class="log-user-email">{{ $log['user']->email }}</div>
                                    </div>
                                @else
                                    <div class="log-user-system">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <rect x="2" y="3" width="20" height="14" rx="2" ry="2"></rect>
                                            <line x1="8" y1="21" x2="16" y2="21"></line>
                                            <line x1="12" y1="17" x2="12" y2="21"></line>
                                        </svg>
                                        Система
                                    </div>
                                @endif
                            </td>
                            
                            <td class="log-date-cell">
                                <div class="log-date-info">
                                    <div class="log-date">{{ $log['created_at']->format('d.m.Y') }}</div>
                                    <div class="log-time">{{ $log['created_at']->format('H:i:s') }}</div>
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
                    Показано {{ $paginator->firstItem() ?? 0 }} - {{ $paginator->lastItem() ?? 0 }} из {{ $paginator->total() }} логов
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
            <div class="no-logs-message">
                <div class="no-logs-icon">
                    <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                        <polyline points="14,2 14,8 20,8"></polyline>
                        <line x1="16" y1="13" x2="8" y2="13"></line>
                        <line x1="16" y1="17" x2="8" y2="17"></line>
                        <polyline points="10,9 9,9 8,9"></polyline>
                    </svg>
                </div>
                <div class="no-logs-text">
                    <h3>Логи не найдены</h3>
                    <p>В данный момент нет доступных логов для отображения.</p>
                </div>
            </div>
        @endif
    </div>
</div>
@endsection

@push('styles')
<style>
.logs-container {
    padding: 20px;
    max-width: 1400px;
    margin: 0 auto;
}

.logs-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 30px;
    padding-bottom: 20px;
    border-bottom: 1px solid #e5e7eb;
}

.breadcrumb {
    font-size: 14px;
    color: #6b7280;
}

.breadcrumb a {
    color: #3b82f6;
    text-decoration: none;
}

.breadcrumb a:hover {
    text-decoration: underline;
}

.logs-info {
    display: flex;
    align-items: center;
    gap: 20px;
}

.logs-count {
    font-size: 14px;
    color: #6b7280;
    font-weight: 500;
}

.admin-controls {
    display: flex;
    align-items: center;
    gap: 20px;
}

.admin-badge {
    display: flex;
    align-items: center;
    gap: 6px;
    padding: 6px 12px;
    background-color: #fef3c7;
    color: #92400e;
    border-radius: 6px;
    font-size: 12px;
    font-weight: 500;
}

.user-filter {
    display: flex;
    align-items: center;
    gap: 10px;
}

.filter-form {
    display: flex;
    align-items: center;
    gap: 10px;
}

.user-select {
    padding: 8px 12px;
    border: 2px solid #e9ecef;
    border-radius: 8px;
    background: white;
    color: #333;
    font-size: 14px;
    font-weight: 500;
    min-width: 200px;
    transition: all 0.3s ease;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.user-select:focus {
    outline: none;
    border-color: #3b82f6;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.clear-filter {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 32px;
    height: 32px;
    background: #dc3545;
    color: white;
    border-radius: 6px;
    text-decoration: none;
    transition: all 0.3s ease;
    box-shadow: 0 2px 4px rgba(220, 53, 69, 0.3);
}

.clear-filter:hover {
    background: #c82333;
    transform: translateY(-1px);
    box-shadow: 0 4px 8px rgba(220, 53, 69, 0.4);
}

.clear-filter svg {
    width: 16px;
    height: 16px;
}

.logs-table-wrapper {
    background: white;
    border-radius: 8px;
    box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
    overflow: hidden;
    margin-bottom: 30px;
}

.logs-table {
    width: 100%;
    border-collapse: collapse;
}

.logs-table th {
    background-color: #f9fafb;
    padding: 16px 12px;
    text-align: left;
    font-weight: 600;
    font-size: 14px;
    color: #374151;
    border-bottom: 1px solid #e5e7eb;
}

.logs-table td {
    padding: 16px 12px;
    border-bottom: 1px solid #f3f4f6;
    vertical-align: top;
}

.log-row:hover {
    background-color: #f9fafb;
}

.log-type-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 6px 12px;
    border-radius: 6px;
    font-size: 12px;
    font-weight: 500;
    color: white;
}

.log-type-product {
    background-color: #10b981;
}

.log-type-company {
    background-color: #3b82f6;
}

.log-type-advertisement {
    background-color: #f59e0b;
}

.log-related-link {
    text-decoration: none;
    color: inherit;
}

.log-related-link:hover {
    text-decoration: underline;
}

.log-related-name {
    font-weight: 500;
    color: #1f2937;
    margin-bottom: 4px;
}

.log-related-sku {
    font-size: 12px;
    color: #6b7280;
}

.log-message-text {
    color: #374151;
    line-height: 1.5;
    max-width: 300px;
    word-wrap: break-word;
}

.log-category-badge {
    display: inline-block;
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 11px;
    font-weight: 500;
    color: white;
    text-align: center;
}

.log-category-default {
    background-color: #6b7280;
}

.log-user-info {
    display: flex;
    flex-direction: column;
    gap: 2px;
}

.log-user-name {
    font-weight: 500;
    color: #1f2937;
    font-size: 14px;
}

.log-user-email {
    font-size: 12px;
    color: #6b7280;
}

.log-user-system {
    display: flex;
    align-items: center;
    gap: 6px;
    color: #6b7280;
    font-size: 14px;
}

.log-date-info {
    display: flex;
    flex-direction: column;
    gap: 2px;
}

.log-date {
    font-weight: 500;
    color: #1f2937;
    font-size: 14px;
}

.log-time {
    font-size: 12px;
    color: #6b7280;
}

.no-logs-message {
    text-align: center;
    padding: 60px 20px;
    background: white;
    border-radius: 8px;
    box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
}

.no-logs-icon {
    color: #9ca3af;
    margin-bottom: 20px;
}

.no-logs-text h3 {
    color: #374151;
    font-size: 18px;
    font-weight: 600;
    margin-bottom: 8px;
}

.no-logs-text p {
    color: #6b7280;
    font-size: 14px;
}

/* Пагинация */
.pagination-wrapper {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 20px 0;
    background: white;
    border-radius: 8px;
    box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
    padding: 20px;
}

.pagination-info {
    font-size: 14px;
    color: #6b7280;
}

.pagination-links {
    display: flex;
    align-items: center;
}

.pagination-links .pagination {
    display: flex;
    list-style: none;
    margin: 0;
    padding: 0;
    gap: 4px;
}

.pagination-links .page-item {
    margin: 0;
}

.pagination-links .page-link {
    display: flex;
    align-items: center;
    justify-content: center;
    min-width: 36px;
    height: 36px;
    padding: 0 8px;
    border: 1px solid #d1d5db;
    background-color: white;
    color: #374151;
    text-decoration: none;
    border-radius: 6px;
    font-size: 14px;
    font-weight: 500;
    transition: all 0.2s ease;
}

.pagination-links .page-link:hover {
    background-color: #f3f4f6;
    border-color: #9ca3af;
    color: #1f2937;
}

.pagination-links .page-item.active .page-link {
    background-color: #3b82f6;
    border-color: #3b82f6;
    color: white;
}

.pagination-links .page-item.disabled .page-link {
    background-color: #f9fafb;
    border-color: #e5e7eb;
    color: #9ca3af;
    cursor: not-allowed;
}

.pagination-links .page-item.disabled .page-link:hover {
    background-color: #f9fafb;
    border-color: #e5e7eb;
    color: #9ca3af;
}

.pagination-links .page-link svg {
    width: 16px;
    height: 16px;
}

.pagination-links .page-item:first-child .page-link,
.pagination-links .page-item:last-child .page-link {
    min-width: 36px;
}

.pagination-links .page-item:first-child .page-link:hover,
.pagination-links .page-item:last-child .page-link:hover {
    background-color: #f3f4f6;
}

.pagination-links .page-item:first-child.disabled .page-link,
.pagination-links .page-item:last-child.disabled .page-link {
    background-color: #f9fafb;
}

.pagination-links .page-item:first-child.disabled .page-link:hover,
.pagination-links .page-item:last-child.disabled .page-link:hover {
    background-color: #f9fafb;
}

/* Адаптивность */
@media (max-width: 768px) {
    .logs-container {
        padding: 10px;
    }
    
    .logs-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 15px;
    }
    
    /* Адаптивность для админских контролов */
    .admin-controls {
        flex-direction: column;
        gap: 15px;
        align-items: flex-start;
    }
    
    .user-select {
        min-width: 180px;
        font-size: 13px;
    }
    
    .logs-table-wrapper {
        overflow-x: auto;
    }
    
    .logs-table {
        min-width: 800px;
    }
    
    .pagination-wrapper {
        flex-direction: column;
        gap: 15px;
        align-items: center;
    }
    
    .pagination-links .pagination {
        flex-wrap: wrap;
        justify-content: center;
    }
}
</style>
@endpush
