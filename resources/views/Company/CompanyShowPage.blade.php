@extends('layouts.layout')

@section('title', 'Организация')

@section('content')
<div class="company-item-container">
    <div class="company-header">
        <div class="breadcrumb">
            <a href="{{ route('companies.index') }}">Организации</a> / {{ $company->name }}
        </div>
        <div class="company-header-actions">
            <h1 class="company-title">{{ $company->name ?? 'Название не указано' }}</h1>
            <div class="company-actions">
                <a href="#" class="btn btn-primary">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"></path>
                    </svg>
                    Редактировать
                </a>
            </div>
        </div>
        <div class="company-sku">SKU: {{ $company->sku ?? 'SKU не указан' }}</div>
    </div>

    <div class="company-content">
        <!-- Основная информация о компании -->
        <div class="company-info-section">
            <div class="info-block">
                <h3>Основная информация</h3>
                <div class="info-list">
                    <div class="info-item">
                        <span class="label">Статус:</span>
                        <span class="value">
                            <div class="status-selector">
                                <div class="status-badge status-{{ $company->status->id ?? 'unknown' }} clickable" onclick="toggleStatusDropdown()">
                                    {{ $company->status->name ?? 'Статус не указан' }}
                                    <svg class="dropdown-arrow" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <polyline points="6,9 12,15 18,9"></polyline>
                                    </svg>
                                </div>
                                <div class="status-dropdown" id="statusDropdown">
                                    @foreach($statuses as $status)
                                        <div class="status-option" onclick="changeStatus({{ $status->id }}, '{{ $status->name }}')">
                                            <div class="status-badge status-{{ $status->id }}">{{ $status->name }}</div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </span>
                    </div>
                    <div class="info-item">
                        <span class="label">Регион:</span>
                        <span class="value">{{ $company->region->name ?? 'Регион не указан' }}</span>
                    </div>
                    <div class="info-item">
                        <span class="label">Источник контакта:</span>
                        <span class="value">{{ $company->source->name ?? 'Источник не указан' }}</span>
                    </div>
                    <div class="info-item">
                        <span class="label">Региональный менеджер:</span>
                        <span class="value">{{ $company->regional->name ?? 'Регионал не назначен' }}</span>
                    </div>
                    <div class="info-item">
                        <span class="label">Ответственный менеджер:</span>
                        <span class="value">{{ $company->owner->name ?? 'Менеджер не назначен' }}</span>
                    </div>
                </div>
                @if($company->common_info)
                    <div class="company-description">
                        <strong>Описание:</strong>
                        <p>{{ $company->common_info }}</p>
                    </div>
                @endif
            </div>

            <div class="info-block">
                <h3>Контактная информация</h3>
                <div class="contact-list">
                    @if($company->email)
                        <div class="contact-item">
                            <span class="label">Email:</span>
                            <span class="value">{{ $company->email }}</span>
                        </div>
                    @endif
                    @if($company->phone)
                        <div class="contact-item">
                            <span class="label">Телефон компании:</span>
                            <span class="value">
                                <a href="tel:{{ $company->phone }}" class="link">{{ $company->phone }}</a>
                            </span>
                        </div>
                    @endif
                    @if($company->emails->count() > 0)
                        @foreach($company->emails as $email)
                            <div class="contact-item">
                                <span class="label">Дополнительный email:</span>
                                <span class="value">{{ $email->email }}</span>
                            </div>
                        @endforeach
                    @endif
                    @if($company->site)
                        <div class="contact-item">
                            <span class="label">Сайт:</span>
                            <span class="value">
                                <a href="{{ $company->site }}" target="_blank" class="link">{{ $company->site }}</a>
                            </span>
                        </div>
                    @endif
                    @forelse($company->contacts as $contact)
                        @if($contact->main_contact)
                            <div class="contact-item">
                                <span class="label">Основной контакт:</span>
                                <span class="value">{{ $contact->name }}</span>
                            </div>
                            @forelse($contact->phones as $phone)
                                <div class="contact-item">
                                    <span class="label">Телефон:</span>
                                    <span class="value">{{ $phone->phone }}</span>
                                </div>
                            @empty
                            @endforelse
                        @endif
                    @empty
                    @endforelse
                </div>
            </div>

            @if($company->contacts->count() > 0)
                <div class="info-block">
                    <h3>Все контакты</h3>
                    <div class="contacts-list">
                        @foreach($company->contacts as $contact)
                            <div class="contact-card">
                                <div class="contact-header">
                                    <span class="contact-name">{{ $contact->name }}</span>
                                    @if($contact->main_contact)
                                        <span class="main-contact-badge">Основной</span>
                                    @endif
                                </div>
                                @if($contact->position)
                                    <div class="contact-position">{{ $contact->position }}</div>
                                @endif
                                @if($contact->phones->count() > 0)
                                    <div class="contact-phones">
                                        @foreach($contact->phones as $phone)
                                            <div class="phone-item">
                                                <span class="phone-number">{{ $phone->phone }}</span>
                                                <div class="contact-actions">
                                                    <a href="tel:{{ $phone->phone }}" class="action-btn" title="Позвонить">
                                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                            <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path>
                                                        </svg>
                                                    </a>
                                                    <a href="#" class="action-btn" title="Просмотр">
                                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                                            <circle cx="12" cy="12" r="3"></circle>
                                                        </svg>
                                                    </a>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                                @if($contact->emails->count() > 0)
                                    <div class="contact-emails">
                                        @foreach($contact->emails as $email)
                                            <div class="email-item">
                                                <span class="email-address">{{ $email->email }}</span>
                                                @if($email->is_primary)
                                                    <span class="primary-badge">Основной</span>
                                                @endif
                                                <div class="contact-actions">
                                                    <a href="mailto:{{ $email->email }}" class="action-btn" title="Написать письмо">
                                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                            <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
                                                            <polyline points="22,6 12,13 2,6"></polyline>
                                                        </svg>
                                                    </a>
                                                    <a href="#" class="action-btn" title="Просмотр">
                                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                                            <circle cx="12" cy="12" r="3"></circle>
                                                        </svg>
                                                    </a>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            @if($company->addresses->count() > 0)
                <div class="info-block">
                    <h3>Адреса</h3>
                    <div class="addresses-list">
                        @foreach($company->addresses as $address)
                            <div class="address-item">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                                    <circle cx="12" cy="10" r="3"></circle>
                                </svg>
                                <span>{{ $address->address }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            @if($company->inn)
                <div class="info-block">
                    <h3>Юридическая информация</h3>
                    <div class="legal-list">
                        <div class="legal-item">
                            <span class="label">ИНН:</span>
                            <span class="value">{{ $company->inn }}</span>
                        </div>
                        <div class="legal-item">
                            <span class="label">Название:</span>
                            <span class="value">{{ $company->name }}</span>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        <!-- Блок действий и событий -->
        <div class="company-actions-section">
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
                            <span>Создал: {{ $company->owner->name ?? 'Создатель не указан' }}</span>
                        </div>
                    </div>
                </div>
                <div class="events-actions">
                    <button class="btn btn-secondary">История</button>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.company-item-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 20px;
}

.company-header {
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

.company-header-actions {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 10px;
}

.company-title {
    font-size: 28px;
    color: #133E71;
    margin: 0;
    font-weight: 600;
}

.company-actions {
    display: flex;
    gap: 10px;
}

.company-actions .btn {
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

.company-actions .btn-primary {
    background-color: #133E71;
    color: white;
    border-color: #133E71;
}

.company-actions .btn-primary:hover {
    background-color: #0f2d56;
    border-color: #0f2d56;
    transform: translateY(-1px);
}

.company-actions .btn svg {
    width: 16px;
    height: 16px;
}

.company-sku {
    font-size: 14px;
    color: #666;
    background: #f8f9fa;
    padding: 5px 10px;
    border-radius: 4px;
    display: inline-block;
}

.company-content {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 40px;
    align-items: start;
}

.company-info-section {
    display: flex;
    flex-direction: column;
    gap: 25px;
}

.company-actions-section {
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

.info-list {
    display: flex;
    flex-direction: column;
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

.status-badge {
    display: inline-block;
    padding: 6px 12px;
    border-radius: 20px;
    font-weight: 600;
    text-align: center;
    font-size: 12px;
}

.status-1 { background-color: #e3f2fd; color: #1976d2; }
.status-2 { background-color: #fff3e0; color: #f57c00; }
.status-3 { background-color: #e8f5e8; color: #388e3c; }
.status-4 { background-color: #fce4ec; color: #c2185b; }
.status-unknown { background-color: #f5f5f5; color: #666; }

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

.company-description {
    margin-top: 20px;
    padding-top: 20px;
    border-top: 1px solid #f1f3f4;
}

.company-description p {
    margin-top: 10px;
    color: #495057;
    line-height: 1.6;
    background: #f8f9fa;
    padding: 15px;
    border-radius: 6px;
}

.contact-list {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.contact-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 10px 0;
    border-bottom: 1px solid #f1f3f4;
}

.contact-item:last-child {
    border-bottom: none;
}

.link {
    color: #133E71;
    text-decoration: none;
}

.link:hover {
    text-decoration: underline;
}

.contacts-list {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.contact-card {
    background: #f8f9fa;
    border-radius: 8px;
    padding: 15px;
    border: 1px solid #e9ecef;
}

.contact-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 8px;
}

.contact-name {
    font-weight: 600;
    color: #133E71;
    font-size: 16px;
}

.main-contact-badge {
    background: #28a745;
    color: white;
    padding: 2px 8px;
    border-radius: 12px;
    font-size: 10px;
    font-weight: 600;
}

.contact-position {
    color: #666;
    font-size: 14px;
    margin-bottom: 10px;
}

.contact-phones {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.contact-emails {
    display: flex;
    flex-direction: column;
    gap: 8px;
    margin-top: 10px;
}

.email-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 8px 12px;
    background: white;
    border-radius: 6px;
    border: 1px solid #e9ecef;
}

.email-address {
    font-weight: 500;
    color: #333;
    display: flex;
    align-items: center;
    gap: 8px;
}

.primary-badge {
    background: #28a745;
    color: white;
    padding: 2px 6px;
    border-radius: 10px;
    font-size: 10px;
    font-weight: 600;
}

.phone-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 8px 12px;
    background: white;
    border-radius: 6px;
    border: 1px solid #e9ecef;
}

.phone-number {
    font-weight: 500;
    color: #333;
}

.contact-actions {
    display: flex;
    gap: 5px;
}

.action-btn {
    width: 32px;
    height: 32px;
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

.addresses-list {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.address-item {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 12px;
    background: #f8f9fa;
    border-radius: 6px;
    border: 1px solid #e9ecef;
}

.address-item svg {
    color: #133E71;
    flex-shrink: 0;
}

.legal-list {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.legal-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 10px 0;
    border-bottom: 1px solid #f1f3f4;
}

.legal-item:last-child {
    border-bottom: none;
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

.btn {
    padding: 8px 16px;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    font-size: 14px;
    font-weight: 500;
    transition: all 0.3s ease;
    text-decoration: none;
    display: inline-block;
}

.btn-primary {
    background: #133E71;
    color: white;
}

.btn-primary:hover {
    background: #1C5BA4;
}

.btn-secondary {
    background: #6c757d;
    color: white;
}

.btn-secondary:hover {
    background: #5a6268;
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

/* Адаптивность */
@media (max-width: 768px) {
    .company-content {
        grid-template-columns: 1fr;
        gap: 20px;
    }
    
    .company-title {
        font-size: 24px;
    }
    
    .info-item {
        flex-direction: column;
        align-items: flex-start;
        gap: 5px;
    }
    
    .value {
        text-align: left;
    }
    
    .contact-item {
        flex-direction: column;
        align-items: flex-start;
        gap: 5px;
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
let currentCompanyId = {{ $company->id }};

function toggleStatusDropdown() {
    const dropdown = document.getElementById('statusDropdown');
    const arrow = document.querySelector('.dropdown-arrow');
    
    if (dropdown.classList.contains('show')) {
        dropdown.classList.remove('show');
        arrow.style.transform = 'rotate(0deg)';
    } else {
        dropdown.classList.add('show');
        arrow.style.transform = 'rotate(180deg)';
    }
}

function changeStatus(statusId, statusName) {
    // Показываем индикатор загрузки
    const statusBadge = document.querySelector('.status-badge.clickable');
    const originalContent = statusBadge.innerHTML;
    statusBadge.innerHTML = '<span>Обновление...</span>';
    
    // Отправляем запрос на сервер
    fetch(`/company/${currentCompanyId}/status`, {
        method: 'PATCH',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            status_id: statusId
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Обновляем отображение статуса
            statusBadge.className = `status-badge status-${statusId} clickable`;
            statusBadge.innerHTML = `
                ${statusName}
                <svg class="dropdown-arrow" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="6,9 12,15 18,9"></polyline>
                </svg>
            `;
            
            // Закрываем выпадающий список
            toggleStatusDropdown();
            
            // Показываем уведомление об успехе
            showNotification('Статус успешно обновлен', 'success');
        } else {
            throw new Error(data.message || 'Ошибка при обновлении статуса');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        // Возвращаем оригинальное содержимое при ошибке
        statusBadge.innerHTML = originalContent;
        showNotification('Ошибка при обновлении статуса', 'error');
    });
}

function showNotification(message, type) {
    // Создаем уведомление
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    notification.textContent = message;
    
    // Добавляем стили для уведомления
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        padding: 12px 20px;
        border-radius: 6px;
        color: white;
        font-weight: 500;
        z-index: 10000;
        animation: slideInRight 0.3s ease;
        ${type === 'success' ? 'background-color: #28a745;' : 'background-color: #dc3545;'}
    `;
    
    document.body.appendChild(notification);
    
    // Удаляем уведомление через 3 секунды
    setTimeout(() => {
        notification.style.animation = 'slideOutRight 0.3s ease';
        setTimeout(() => {
            document.body.removeChild(notification);
        }, 300);
    }, 3000);
}

// Закрытие выпадающего списка при клике вне его
document.addEventListener('click', function(event) {
    const statusSelector = document.querySelector('.status-selector');
    const dropdown = document.getElementById('statusDropdown');
    
    if (!statusSelector.contains(event.target) && dropdown.classList.contains('show')) {
        toggleStatusDropdown();
    }
});

// Добавляем стили для анимаций уведомлений
const style = document.createElement('style');
style.textContent = `
    @keyframes slideInRight {
        from {
            transform: translateX(100%);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }
    
    @keyframes slideOutRight {
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
</script>
@endsection