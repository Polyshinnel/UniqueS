@extends('layouts.layout')

@section('title', 'Организации')

@section('header-title')
    <h1 class="header-title">Организации</h1>
@endsection

@section('header-action-btn')
    <a href="{{ route('companies.create') }}" class="btn btn-primary">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <line x1="12" y1="5" x2="12" y2="19"></line>
            <line x1="5" y1="12" x2="19" y2="12"></line>
        </svg>
        Создать компанию
    </a>
@endsection

@section('content')
<div class="companies-container">
    <div class="companies-table-wrapper">
        <table class="companies-table">
            <thead>
                <tr>
                    <th>Название</th>
                    <th>Контактное лицо</th>
                    <th>Ответственные</th>
                    <th>Адрес компании</th>
                    <th>Общая информация</th>
                    <th>Дата след.действия</th>
                    <th>Статус</th>
                </tr>
            </thead>
            <tbody>
                @forelse($companies as $company)
                <tr class="company-row">
                    <td class="company-info-cell">
                        <div class="company-info">
                            <div class="company-sku">{{ $company->sku }}</div>
                            <div class="company-name">{{ $company->name }}</div>
                            <a href="{{ route('companies.show', $company) }}" class="company-link">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                    <circle cx="12" cy="12" r="3"></circle>
                                </svg>
                                Подробнее
                            </a>
                        </div>
                    </td>
                
                    <td class="contact-cell">
                        @if($company->contacts->isNotEmpty())
                        @php 
                            $mainContact = $company->contacts->where('main_contact', true)->first();
                            if (!$mainContact) {
                                $mainContact = $company->contacts->first();
                            }
                        @endphp
                        <div class="contact-info">
                            <div class="contact-details">
                                <div class="contact-position">{{ $mainContact->position }}</div>
                                <div class="contact-name">{{ $mainContact->name }}</div>
                                @foreach($mainContact->phones as $phone)
                                    <div class="contact-phone">{{ $phone->phone }}</div>
                                @endforeach
                            </div>
                        </div>
                        @else
                        <div class="no-contact">Контакт не указан</div>
                        @endif
                    </td>

                    <td class="responsible-cell">
                        <div class="responsible-list">
                            @if($company->regional)
                            <div class="responsible-item">
                                <div class="responsible-label">Регионал:</div>
                                <div class="responsible-name">{{ $company->regional->name }}</div>
                                <div class="responsible-actions">
                                    <button class="action-btn" title="Просмотр" onclick="showContactCard({{ $company->regional->id }}, '{{ $company->regional->name }}', '{{ $company->regional->email }}', '{{ $company->regional->phone }}', '{{ $company->regional->role->name ?? 'Роль не указана' }}', {{ $company->regional->has_telegram ? 'true' : 'false' }}, {{ $company->regional->has_whatsapp ? 'true' : 'false' }})">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                            <circle cx="12" cy="12" r="3"></circle>
                                        </svg>
                                    </button>
                                    @if($company->regional->has_telegram)
                                    <a href="https://t.me/{{ $company->regional->phone }}" target="_blank" class="action-btn" title="Telegram">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M22 2L11 13"></path>
                                            <path d="M22 2L15 22L11 13L2 9L22 2Z"></path>
                                        </svg>
                                    </a>
                                    @endif
                                    @if($company->regional->has_whatsapp)
                                    <a href="https://wa.me/{{ $company->regional->phone }}" target="_blank" class="action-btn" title="WhatsApp">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
                                        </svg>
                                    </a>
                                    @endif
                                </div>
                            </div>
                            @endif
                            
                            @if($company->owner)
                            <div class="responsible-item">
                                <div class="responsible-label">Менеджер:</div>
                                <div class="responsible-name">{{ $company->owner->name }}</div>
                                <div class="responsible-actions">
                                    <button class="action-btn" title="Просмотр" onclick="showContactCard({{ $company->owner->id }}, '{{ $company->owner->name }}', '{{ $company->owner->email }}', '{{ $company->owner->phone }}', '{{ $company->owner->role->name ?? 'Роль не указана' }}', {{ $company->owner->has_telegram ? 'true' : 'false' }}, {{ $company->owner->has_whatsapp ? 'true' : 'false' }})">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                            <circle cx="12" cy="12" r="3"></circle>
                                        </svg>
                                    </button>
                                    @if($company->owner->has_telegram)
                                    <a href="https://t.me/{{ $company->owner->phone }}" target="_blank" class="action-btn" title="Telegram">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M22 2L11 13"></path>
                                            <path d="M22 2L15 22L11 13L2 9L22 2Z"></path>
                                        </svg>
                                    </a>
                                    @endif
                                    @if($company->owner->has_whatsapp)
                                    <a href="https://wa.me/{{ $company->owner->phone }}" target="_blank" class="action-btn" title="WhatsApp">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
                                        </svg>
                                    </a>
                                    @endif
                                </div>
                            </div>
                            @endif
                        </div>
                    </td>

                    <td class="address-cell">
                        @if($company->addresses->isNotEmpty())
                            <div class="address-info">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                                    <circle cx="12" cy="10" r="3"></circle>
                                </svg>
                                <span>{{ $company->addresses->first()->address }}</span>
                            </div>
                        @else
                            <div class="no-address">Адрес не указан</div>
                        @endif
                    </td>
                    
                    <td class="info-cell">
                        <div class="company-description">
                            <p>{{ trim($company->common_info) ?: 'Описание не указано' }}</p>
                        </div>
                    </td>

                    <td class="action-cell">
                        <div class="action-info">
                            @if($company->actions->isNotEmpty())
                                @php $lastAction = $company->actions->first(); @endphp
                                <div class="action-date">{{ $lastAction->expired_at->format('d.m.Y') }}</div>
                                <div class="action-text">{{ $lastAction->action }}</div>
                            @else
                                <div class="action-date">{{ now()->format('d.m.Y') }}</div>
                                <div class="action-text">Нет активных действий</div>
                            @endif
                        </div>
                    </td>

                    <td class="status-cell">
                        @if($company->status)
                        <div class="status-badge status-{{ $company->status->id }}" style="background-color: {{ $company->status->color }}">
                            {{ $company->status->name }}
                        </div>
                        @else
                        <div class="status-badge status-unknown">Статус не указан</div>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="empty-state">
                        <div class="empty-content">
                            <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M20 7L10 17L5 12"></path>
                            </svg>
                            <p>Компании не найдены</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    <!-- Пагинация -->
    @if($companies->total() > 0)
    <div class="pagination-wrapper">
        <div class="pagination-info">
            Показано {{ $companies->firstItem() ?? 0 }} - {{ $companies->lastItem() ?? 0 }} из {{ $companies->total() }} компаний
        </div>
        @if($companies->hasPages())
        <div class="pagination-links">
            <ul class="pagination">
                {{-- Предыдущая страница --}}
                @if ($companies->onFirstPage())
                    <li class="page-item disabled">
                        <span class="page-link">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polyline points="15,18 9,12 15,6"></polyline>
                            </svg>
                        </span>
                    </li>
                @else
                    <li class="page-item">
                        <a class="page-link" href="{{ $companies->previousPageUrl() }}" rel="prev">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polyline points="15,18 9,12 15,6"></polyline>
                            </svg>
                        </a>
                    </li>
                @endif

                {{-- Номера страниц --}}
                @foreach ($companies->getUrlRange(1, $companies->lastPage()) as $page => $url)
                    @if ($page == $companies->currentPage())
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
                @if ($companies->hasMorePages())
                    <li class="page-item">
                        <a class="page-link" href="{{ $companies->nextPageUrl() }}" rel="next">
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
</div>

@endsection

@push('styles')
<style>
.companies-container {
    max-width: 1400px;
    margin: 0 auto;
    padding: 20px;
}

.companies-table-wrapper {
    background: white;
    border-radius: 12px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.1);
    overflow: hidden;
}

.companies-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 14px;
}

.companies-table th {
    background: linear-gradient(180deg, #ffff00 0%, #ffeb3b 100%);
    color: #333;
    padding: 16px 12px;
    text-align: left;
    font-weight: 600;
    font-size: 13px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    border-bottom: 2px solid #f57f17;
}

.companies-table th:first-child {
    padding-left: 20px;
}

.companies-table th:last-child {
    padding-right: 20px;
}

.company-row {
    border-bottom: 1px solid #f1f3f4;
    transition: all 0.3s ease;
}

.company-row:hover {
    background-color: #f8f9fa;
    transform: translateY(-1px);
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.company-row:last-child {
    border-bottom: none;
}

.companies-table td {
    padding: 16px 12px;
    vertical-align: top;
}

.companies-table td:first-child {
    padding-left: 20px;
}

.companies-table td:last-child {
    padding-right: 20px;
}

/* Стили для ячейки с информацией о компании */
.company-info-cell {
    min-width: 200px;
}

.company-info {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.company-sku {
    font-size: 12px;
    color: #666;
    font-weight: 500;
    background: #f8f9fa;
    padding: 4px 8px;
    border-radius: 4px;
    display: inline-block;
    width: fit-content;
}

.company-name {
    font-size: 16px;
    font-weight: 600;
    color: #133E71;
    line-height: 1.3;
}

.company-link {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    color: #133E71;
    text-decoration: none;
    font-size: 12px;
    font-weight: 500;
    padding: 6px 12px;
    background: #e8f0fe;
    border-radius: 20px;
    transition: all 0.3s ease;
    width: fit-content;
}

.company-link:hover {
    background: #133E71;
    color: white;
    transform: translateY(-1px);
}

.company-link svg {
    width: 14px;
    height: 14px;
}

/* Стили для ячейки контактов */
.contact-cell {
    min-width: 180px;
}

.contact-info {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.contact-details {
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.contact-position {
    font-size: 11px;
    color: #666;
    font-weight: 500;
}

.contact-name {
    font-size: 14px;
    font-weight: 600;
    color: #333;
}

.contact-phone {
    font-size: 13px;
    color: #133E71;
    font-weight: 500;
}

.contact-actions {
    display: flex;
    gap: 6px;
}

.action-btn {
    width: 28px;
    height: 28px;
    border-radius: 50%;
    background: #133E71;
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    text-decoration: none;
    transition: all 0.3s ease;
    border: none;
    cursor: pointer;
}

.action-btn:hover {
    background: #1C5BA4;
    transform: scale(1.1);
}

.action-btn svg {
    width: 14px;
    height: 14px;
}

.no-contact {
    color: #999;
    font-style: italic;
    font-size: 13px;
}



/* Стили для ячейки ответственных */
.responsible-cell {
    min-width: 200px;
}

.responsible-list {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.responsible-item {
    display: flex;
    flex-direction: column;
    gap: 6px;
    padding: 10px;
    background: #f8f9fa;
    border-radius: 6px;
    border: 1px solid #e9ecef;
}

.responsible-label {
    font-size: 11px;
    color: #666;
    font-weight: 500;
}

.responsible-name {
    font-size: 13px;
    font-weight: 600;
    color: #333;
}

.responsible-actions {
    display: flex;
    gap: 4px;
}

.responsible-actions .action-btn {
    width: 24px;
    height: 24px;
}

.responsible-actions .action-btn svg {
    width: 12px;
    height: 12px;
}

/* Стили для ячейки адреса */
.address-cell {
    min-width: 200px;
}

.address-info {
    display: flex;
    align-items: flex-start;
    gap: 8px;
    padding: 10px;
    background: #f8f9fa;
    border-radius: 6px;
    border: 1px solid #e9ecef;
}

.address-info svg {
    color: #133E71;
    flex-shrink: 0;
    margin-top: 1px;
}

.address-info span {
    font-size: 13px;
    color: #333;
    line-height: 1.4;
}

.no-address {
    color: #999;
    font-style: italic;
    font-size: 13px;
}

/* Стили для ячейки информации */
.info-cell {
    min-width: 250px;
}

.company-description {
    font-size: 13px;
    color: #495057;
    line-height: 1.5;
    background: #f8f9fa;
    padding: 10px;
    border-radius: 6px;
    border: 1px solid #e9ecef;
    word-wrap: break-word;
    white-space: pre-wrap;
    text-align: left;
}

/* Стили для ячейки действий */
.action-cell {
    min-width: 180px;
}

.action-info {
    display: flex;
    flex-direction: column;
    gap: 6px;
}

.action-date {
    font-size: 12px;
    color: #666;
    font-weight: 500;
}

.action-text {
    font-size: 12px;
    color: #333;
    line-height: 1.4;
    background: #fff3cd;
    padding: 8px;
    border-radius: 4px;
    border: 1px solid #ffeaa7;
}

/* Стили для ячейки статуса */
.status-cell {
    min-width: 120px;
}

.status-badge {
    display: inline-block;
    padding: 6px 12px;
    border-radius: 20px;
    font-weight: 600;
    text-align: center;
    font-size: 12px;
    color: white;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.status-1 { background-color: #e3f2fd !important; color: #1976d2 !important; }
.status-2 { background-color: #fff3e0 !important; color: #f57c00 !important; }
.status-3 { background-color: #e8f5e8 !important; color: #388e3c !important; }
.status-4 { background-color: #fce4ec !important; color: #c2185b !important; }
.status-unknown { background-color: #f5f5f5 !important; color: #666 !important; }

/* Стили для пустого состояния */
.empty-state {
    text-align: center;
    padding: 60px 20px !important;
}

.empty-content {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 16px;
    color: #666;
}

.empty-content svg {
    color: #ccc;
}

.empty-content p {
    font-size: 16px;
    margin: 0;
}

/* Адаптивность */
@media (max-width: 1200px) {
    .companies-table {
        font-size: 13px;
    }
    
    .companies-table th,
    .companies-table td {
        padding: 12px 8px;
    }
    
    .companies-table th:first-child,
    .companies-table td:first-child {
        padding-left: 15px;
    }
    
    .companies-table th:last-child,
    .companies-table td:last-child {
        padding-right: 15px;
    }
}

@media (max-width: 768px) {
    .companies-container {
        padding: 10px;
    }
    
    .companies-table-wrapper {
        border-radius: 8px;
    }
    
    .companies-table {
        font-size: 12px;
    }
    
    .companies-table th,
    .companies-table td {
        padding: 10px 6px;
    }
    
    .company-name {
        font-size: 14px;
    }
    
    .contact-name,
    .responsible-name {
        font-size: 12px;
    }
    
    .action-btn {
        width: 24px;
        height: 24px;
    }
    
    .action-btn svg {
        width: 12px;
        height: 12px;
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

.company-row {
    animation: fadeIn 0.3s ease;
}

/* Улучшенные стили для кнопки создания */
.btn-primary {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 10px 20px;
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

.btn-primary:hover {
    background: #1C5BA4;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(19, 62, 113, 0.4);
}

.btn-primary svg {
    width: 16px;
    height: 16px;
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

/* Стили для пагинации */
.pagination-wrapper {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: 30px;
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

/* Стили для стрелок */
.pagination-links .page-link svg {
    width: 16px;
    height: 16px;
    color: inherit;
}

.pagination-links .page-item:first-child .page-link,
.pagination-links .page-item:last-child .page-link {
    background: #e8f0fe;
    border-color: #133E71;
    color: #133E71;
}

.pagination-links .page-item:first-child .page-link:hover,
.pagination-links .page-item:last-child .page-link:hover {
    background: #133E71;
    color: white;
}

.pagination-links .page-item:first-child.disabled .page-link,
.pagination-links .page-item:last-child.disabled .page-link {
    background: #f5f5f5;
    color: #999;
    border-color: #e9ecef;
}

.pagination-links .page-item:first-child.disabled .page-link:hover,
.pagination-links .page-item:last-child.disabled .page-link:hover {
    background: #f5f5f5;
    color: #999;
}

/* Адаптивность для пагинации */
@media (max-width: 768px) {
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

/* Адаптивность для модального окна */
@media (max-width: 768px) {
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
}
</style>
@endpush

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

@push('scripts')
<script>
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
@endpush