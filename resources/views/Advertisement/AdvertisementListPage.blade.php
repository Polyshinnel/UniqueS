@extends('layouts.layout')

@section('title', 'Объявления')

@section('header-action-btn')
    <a href="{{ route('advertisements.create') }}" class="btn btn-primary">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <line x1="12" y1="5" x2="12" y2="19"></line>
            <line x1="5" y1="12" x2="19" y2="12"></line>
        </svg>
        Добавить объявление
    </a>
@endsection

@section('header-title')
    <h1 class="header-title">Объявления</h1>
@endsection

@section('content')
<div class="advertisements-container">
    <div class="advertisements-table-wrapper">
        <table class="advertisements-table">
            <thead>
                <tr>
                    <th>Название</th>
                    <th>Категория</th>
                    <th>Поставщик</th>
                    <th>Характеристики</th>
                    <th>Дата след. действия</th>
                    <th>Статус</th>
                </tr>
            </thead>
            <tbody>
                @forelse($advertisements as $advertisement)
                <tr class="advertisement-row">
                    <td class="advertisement-info-cell">
                        <div class="advertisement-info">
                            <div class="advertisement-sku">{{ $advertisement->product->sku ?? 'КЛГ-001-' . $advertisement->created_at->format('dmy') . '-' . $advertisement->id }}</div>
                            <div class="advertisement-name">{{ $advertisement->title }}</div>
                            <div class="advertisement-image">
                                <a href="{{ route('advertisements.show', $advertisement) }}">
                                    @if($advertisement->mainImage)
                                        <img src="{{ asset('storage/' . $advertisement->mainImage->file_path) }}" alt="{{ $advertisement->title }}">
                                    @else
                                        <img src="{{ asset('assets/img/stanok.png') }}" alt="{{ $advertisement->title }}">
                                    @endif
                                </a>
                            </div>
                            <a href="{{ route('advertisements.show', $advertisement) }}" class="advertisement-link">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                    <circle cx="12" cy="12" r="3"></circle>
                                </svg>
                                Подробнее
                            </a>
                        </div>
                    </td>
                
                    <td class="category-cell">
                        <div class="category-info">
                            <div class="category-name">{{ $advertisement->category->name ?? 'Не указана' }}</div>
                        </div>
                    </td>

                    <td class="supplier-cell">
                        <div class="supplier-info">
                            <div class="supplier-name">
                                @if($advertisement->product && $advertisement->product->company)
                                    <a href="{{ route('companies.show', $advertisement->product->company) }}" class="company-link">
                                        {{ $advertisement->product->company->name }}
                                    </a>
                                    <button class="action-btn company-card-btn" title="Просмотр компании" 
                                        data-company-id="{{ $advertisement->product->company->id }}"
                                        data-company-name="{{ $advertisement->product->company->name }}"
                                        data-company-sku="{{ $advertisement->product->company->sku }}"
                                        data-company-status="{{ $advertisement->product->company->status->name ?? 'Не указан' }}"
                                        data-company-info="{{ $advertisement->product->company->common_info ?? 'Не указано' }}">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                            <circle cx="12" cy="12" r="3"></circle>
                                        </svg>
                                    </button>
                                @else
                                    Не указана
                                @endif
                            </div>
                            @if($advertisement->product && $advertisement->product->company && $advertisement->product->company->addresses->count() > 0)
                                <div class="supplier-address">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                                        <circle cx="12" cy="10" r="3"></circle>
                                    </svg>
                                    <span>{{ $advertisement->product->company->addresses->first()->address ?? '' }}</span>
                                </div>
                            @endif
                            <div class="supplier-region">Регион: {{ $advertisement->product->warehouse->name ?? 'Не указан' }}</div>
                            
                            @if($advertisement->product && $advertisement->product->regional)
                            <div class="responsible-item">
                                <div class="responsible-label">Регионал:</div>
                                <div class="responsible-name">{{ $advertisement->product->regional->name }}</div>
                                <div class="responsible-actions">
                                    <button class="action-btn contact-card-btn" title="Просмотр" 
                                        data-id="{{ $advertisement->product->regional->id }}"
                                        data-name="{{ $advertisement->product->regional->name }}"
                                        data-email="{{ $advertisement->product->regional->email }}"
                                        data-phone="{{ $advertisement->product->regional->phone }}"
                                        data-role="{{ $advertisement->product->regional->role->name ?? 'Роль не указана' }}"
                                        data-telegram="{{ $advertisement->product->regional->has_telegram ? 'true' : 'false' }}"
                                        data-whatsapp="{{ $advertisement->product->regional->has_whatsapp ? 'true' : 'false' }}">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                            <circle cx="12" cy="12" r="3"></circle>
                                        </svg>
                                    </button>
                                    @if($advertisement->product->regional->has_telegram)
                                    <a href="https://t.me/{{ $advertisement->product->regional->phone }}" target="_blank" class="action-btn" title="Telegram">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M22 2L11 13"></path>
                                            <path d="M22 2L15 22L11 13L2 9L22 2Z"></path>
                                        </svg>
                                    </a>
                                    @endif
                                    @if($advertisement->product->regional->has_whatsapp)
                                    <a href="https://wa.me/{{ $advertisement->product->regional->phone }}" target="_blank" class="action-btn" title="WhatsApp">
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

                    <td class="characteristics-cell">
                        <div class="characteristics-info">
                            @if($advertisement->main_characteristics)
                            <div class="char-item">
                                <span class="char-label">Осн. хар:</span>
                                <span class="char-value">{{ $advertisement->main_characteristics }}</span>
                            </div>
                            @endif
                            @if($advertisement->complectation)
                            <div class="char-item">
                                <span class="char-label">Компл:</span>
                                <span class="char-value">{{ $advertisement->complectation }}</span>
                            </div>
                            @endif
                            @if($advertisement->check_data && isset($advertisement->check_data['loading_type']))
                            <div class="char-item">
                                <span class="char-label">Проверка:</span>
                                <span class="char-value">
                                    @switch($advertisement->check_data['loading_type'])
                                        @case('supplier')
                                            Поставщиком
                                            @break
                                        @case('supplier_paid')
                                            Поставщиком (за доп. плату)
                                            @break
                                        @case('client')
                                            Клиентом
                                            @break
                                        @case('other')
                                            Другое
                                            @break
                                        @default
                                            {{ $advertisement->check_data['loading_type'] }}
                                    @endswitch
                                </span>
                            </div>
                            @endif
                            @if($advertisement->removal_data && isset($advertisement->removal_data['removal_type']))
                            <div class="char-item">
                                <span class="char-label">Демонтаж:</span>
                                <span class="char-value">
                                    @switch($advertisement->removal_data['removal_type'])
                                        @case('supplier')
                                            Поставщиком
                                            @break
                                        @case('supplier_paid')
                                            Поставщиком (за доп. плату)
                                            @break
                                        @case('client')
                                            Клиентом
                                            @break
                                        @case('other')
                                            Другое
                                            @break
                                        @default
                                            {{ $advertisement->removal_data['removal_type'] }}
                                    @endswitch
                                </span>
                            </div>
                            @endif
                        </div>
                    </td>

                    <td class="action-cell">
                        <div class="action-info">
                            <div class="action-date">{{ $advertisement->created_at->format('d.m.Y H:i') }}</div>
                            <div class="action-text">Проверить статус объявления</div>
                        </div>
                    </td>

                    <td class="status-cell">
                        <div class="status-list">
                            <div class="status-item">
                                <div class="status-label">Статус объявления</div>
                                <div class="status-badge status-{{ $advertisement->status }}">
                                    {{ $advertisement->status_name }}
                                </div>
                            </div>
                            <div class="status-item">
                                <div class="status-label">Статус публикации</div>
                                <div class="status-badge status-{{ $advertisement->isPublished() ? 'published' : 'unpublished' }}">
                                    {{ $advertisement->isPublished() ? 'Опубликовано' : 'Не опубл.' }}
                                </div>
                            </div>
                        </div>
                        <div class="price-info">
                            <div class="price-label">Цена продажи:</div>
                            <div class="price-value">{{ $advertisement->product->purchase_price ?? 0 }} руб</div>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="empty-state">
                        <div class="empty-content">
                            <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M20 7L10 17L5 12"></path>
                            </svg>
                            <p>Объявления не найдены</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('assets/js/side-panel.js') }}"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Обработчик для кнопок контактных карточек
    document.addEventListener('click', function(e) {
        if (e.target.closest('.contact-card-btn')) {
            const btn = e.target.closest('.contact-card-btn');
            const id = btn.dataset.id;
            const name = btn.dataset.name;
            const email = btn.dataset.email;
            const phone = btn.dataset.phone;
            const role = btn.dataset.role;
            const hasTelegram = btn.dataset.telegram;
            const hasWhatsapp = btn.dataset.whatsapp;
            
            showContactCard(id, name, email, phone, role, hasTelegram, hasWhatsapp);
        }
    });
    
    // Обработчик для кнопок карточек компаний
    document.addEventListener('click', function(e) {
        if (e.target.closest('.company-card-btn')) {
            const btn = e.target.closest('.company-card-btn');
            const id = btn.dataset.companyId;
            const name = btn.dataset.companyName;
            const sku = btn.dataset.companySku;
            const status = btn.dataset.companyStatus;
            const info = btn.dataset.companyInfo;
            
            showCompanyCard(id, name, sku, status, info);
        }
    });
});

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

function showCompanyCard(id, name, sku, status, info) {
    document.getElementById('companyName').textContent = name;
    document.getElementById('companySku').textContent = sku;
    document.getElementById('companyStatus').textContent = status;
    document.getElementById('companyInfo').textContent = info;
    
    document.getElementById('companyModal').style.display = 'flex';
}

function closeCompanyCard() {
    document.getElementById('companyModal').style.display = 'none';
}

// Закрытие модальных окон при клике вне их
window.onclick = function(event) {
    const contactModal = document.getElementById('contactModal');
    const companyModal = document.getElementById('companyModal');
    
    if (event.target === contactModal) {
        closeContactCard();
    }
    
    if (event.target === companyModal) {
        closeCompanyCard();
    }
}
</script>
@endpush

@push('styles')
<style>
.advertisements-container {
    max-width: 1400px;
    margin: 0 auto;
    padding: 20px;
}

.advertisements-table-wrapper {
    background: white;
    border-radius: 12px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.1);
    overflow: hidden;
}

.advertisements-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 14px;
}

.advertisements-table th {
    background: #133E71;
    color: white;
    padding: 16px 12px;
    text-align: left;
    font-weight: 600;
    font-size: 13px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    border-bottom: 2px solid #0f2d56;
}

.advertisements-table th:first-child {
    padding-left: 20px;
}

.advertisements-table th:last-child {
    padding-right: 20px;
}

.advertisement-row {
    border-bottom: 1px solid #f1f3f4;
    transition: all 0.3s ease;
}

.advertisement-row:hover {
    background-color: #f8f9fa;
    transform: translateY(-1px);
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.advertisement-row:last-child {
    border-bottom: none;
}

.advertisements-table td {
    padding: 16px 12px;
    vertical-align: top;
}

.advertisements-table td:first-child {
    padding-left: 20px;
}

.advertisements-table td:last-child {
    padding-right: 20px;
}

/* Стили для ячейки с информацией об объявлении */
.advertisement-info-cell {
    min-width: 200px;
}

.advertisement-info {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.advertisement-sku {
    font-size: 12px;
    color: #666;
    font-weight: 500;
    background: #f8f9fa;
    padding: 4px 8px;
    border-radius: 4px;
    display: inline-block;
    width: fit-content;
}

.advertisement-name {
    font-size: 16px;
    font-weight: 600;
    color: #133E71;
    line-height: 1.3;
}

.advertisement-image {
    width: 80px;
    height: 60px;
    border-radius: 6px;
    overflow: hidden;
    border: 1px solid #e9ecef;
}

.advertisement-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.advertisement-link {
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

.advertisement-link:hover {
    background: #133E71;
    color: white;
    transform: translateY(-1px);
}

.advertisement-link svg {
    width: 14px;
    height: 14px;
}

/* Стили для ячейки категории */
.category-cell {
    min-width: 150px;
}

.category-info {
    display: flex;
    flex-direction: column;
    gap: 6px;
}

.category-name {
    font-size: 14px;
    font-weight: 600;
    color: #333;
    padding: 8px 12px;
    background: #f8f9fa;
    border-radius: 6px;
    border: 1px solid #e9ecef;
}

/* Стили для ячейки поставщика */
.supplier-cell {
    min-width: 200px;
}

.supplier-info {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.supplier-name {
    font-size: 14px;
    font-weight: 600;
    color: #333;
}

.company-link {
    color: #133E71;
    text-decoration: none;
    font-weight: 600;
    transition: all 0.3s ease;
    padding: 4px 8px;
    border-radius: 4px;
    display: inline-block;
}

.company-link:hover {
    color: #1C5BA4;
    background-color: #e8f0fe;
    transform: translateY(-1px);
}

.supplier-name {
    display: flex;
    align-items: center;
    gap: 8px;
}

.company-card-btn {
    width: 24px;
    height: 24px;
    margin-left: 4px;
}

.supplier-address {
    display: flex;
    align-items: flex-start;
    gap: 8px;
    padding: 8px;
    background: #f8f9fa;
    border-radius: 6px;
    border: 1px solid #e9ecef;
}

.supplier-address svg {
    color: #133E71;
    flex-shrink: 0;
    margin-top: 1px;
}

.supplier-address span {
    font-size: 12px;
    color: #333;
    line-height: 1.4;
}

.supplier-region {
    font-size: 12px;
    color: #666;
    font-weight: 500;
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

/* Стили для ячейки характеристик */
.characteristics-cell {
    min-width: 200px;
}

.characteristics-info {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.char-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 8px 12px;
    background: #f8f9fa;
    border-radius: 6px;
    border: 1px solid #e9ecef;
}

.char-label {
    font-size: 11px;
    color: #666;
    font-weight: 500;
}

.char-value {
    font-size: 12px;
    color: #333;
    font-weight: 500;
    text-align: right;
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
    min-width: 150px;
}

.status-list {
    display: flex;
    flex-direction: column;
    gap: 8px;
    margin-bottom: 12px;
}

.status-item {
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.status-label {
    font-size: 10px;
    color: #666;
    font-weight: 500;
}

.status-badge {
    display: inline-block;
    padding: 4px 8px;
    border-radius: 12px;
    font-weight: 600;
    text-align: center;
    font-size: 10px;
    color: white;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.status-draft {
    background-color: #ffc107 !important;
    color: #212529 !important;
}

.status-active {
    background-color: #28a745 !important;
}

.status-inactive {
    background-color: #dc3545 !important;
}

.status-archived {
    background-color: #6c757d !important;
}

.status-published {
    background-color: #28a745 !important;
}

.status-unpublished {
    background-color: #ffc107 !important;
    color: #212529 !important;
}

.price-info {
    display: flex;
    flex-direction: column;
    gap: 4px;
    padding: 8px;
    background: #f8f9fa;
    border-radius: 6px;
    border: 1px solid #e9ecef;
}

.price-label {
    font-size: 10px;
    color: #666;
    font-weight: 500;
}

.price-value {
    font-size: 12px;
    color: #333;
    font-weight: 600;
}

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
    .advertisements-table {
        font-size: 13px;
    }
    
    .advertisements-table th,
    .advertisements-table td {
        padding: 12px 8px;
    }
    
    .advertisements-table th:first-child,
    .advertisements-table td:first-child {
        padding-left: 15px;
    }
    
    .advertisements-table th:last-child,
    .advertisements-table td:last-child {
        padding-right: 15px;
    }
}

@media (max-width: 768px) {
    .advertisements-container {
        padding: 10px;
    }
    
    .advertisements-table-wrapper {
        border-radius: 8px;
    }
    
    .advertisements-table {
        font-size: 12px;
    }
    
    .advertisements-table th,
    .advertisements-table td {
        padding: 10px 6px;
    }
    
    .advertisement-name {
        font-size: 14px;
    }
    
    .supplier-name,
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

.advertisement-row {
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

<!-- Всплывающее окно для карточки компании -->
<div id="companyModal" class="contact-modal">
    <div class="contact-modal-content">
        <div class="contact-modal-header">
            <h3>Карточка компании</h3>
            <button class="contact-modal-close" onclick="closeCompanyCard()">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
            </button>
        </div>
        <div class="contact-modal-body">
            <div class="contact-info-item">
                <label>Название:</label>
                <span id="companyName"></span>
            </div>
            <div class="contact-info-item">
                <label>SKU:</label>
                <span id="companySku"></span>
            </div>
            <div class="contact-info-item">
                <label>Статус:</label>
                <span id="companyStatus"></span>
            </div>
            <div class="contact-info-item">
                <label>Описание:</label>
                <span id="companyInfo"></span>
            </div>
        </div>
    </div>
</div> 