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
    <!-- Панель фильтров -->
    <div class="filters-panel">
        <div class="filters-header">
            <h3>Фильтры</h3>
            <button class="btn btn-secondary" onclick="toggleFilters()">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polygon points="22,3 2,3 10,12.46 10,19 14,21 14,12.46"></polygon>
                </svg>
                Фильтр
            </button>
        </div>
        
        <div class="filters-content" id="filtersContent">
            <form method="GET" action="{{ route('advertisements.index') }}" class="filters-form">
                <div class="filters-grid">
                    <!-- Поиск -->
                    <div class="filter-group search-group">
                        <label for="search">Поиск:</label>
                        <div class="search-input-wrapper">
                            <input type="text" name="search" id="search" class="form-control" 
                                   placeholder="Поиск по названию объявления или артикулу товара"
                                   value="{{ request('search') }}">
                            <button type="submit" class="search-btn">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <circle cx="11" cy="11" r="8"></circle>
                                    <path d="M21 21L16.65 16.65"></path>
                                </svg>
                            </button>
                        </div>
                    </div>

                    <!-- Фильтр по типу объявлений -->
                    <div class="filter-group">
                        <label for="advertisement_type">Тип объявлений:</label>
                        <select name="advertisement_type" id="advertisement_type" class="form-select">
                            <option value="own" {{ request('advertisement_type', 'own') == 'own' ? 'selected' : '' }}>Мои объявления</option>
                            <option value="all" {{ request('advertisement_type') == 'all' ? 'selected' : '' }}>Все объявления</option>
                        </select>
                    </div>

                    <!-- Фильтр по категории -->
                    <div class="filter-group">
                        <label for="category_id">Категория:</label>
                        <select name="category_id" id="category_id" class="form-select">
                            <option value="">Все категории</option>
                            @foreach($filterData['categories'] as $category)
                                <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Фильтр по поставщику -->
                    <div class="filter-group">
                        <label for="company_id">Поставщик:</label>
                        <select name="company_id" id="company_id" class="form-select">
                            <option value="">Все поставщики</option>
                            @foreach($filterData['companies'] as $company)
                                <option value="{{ $company->id }}" {{ request('company_id') == $company->id ? 'selected' : '' }}>
                                    {{ $company->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Фильтр по статусу -->
                    <div class="filter-group">
                        <label for="status_id">Статус:</label>
                        <select name="status_id" id="status_id" class="form-select">
                            <option value="">Все статусы</option>
                            @foreach($filterData['statuses'] as $status)
                                <option value="{{ $status->id }}" {{ request('status_id') == $status->id ? 'selected' : '' }}>
                                    {{ $status->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Фильтр по региону -->
                    <div class="filter-group">
                        <label for="region_id">Регион:</label>
                        <select name="region_id" id="region_id" class="form-select">
                            <option value="">Все регионы</option>
                            @foreach($filterData['regions'] as $region)
                                <option value="{{ $region->id }}" {{ request('region_id') == $region->id ? 'selected' : '' }}>
                                    {{ $region->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="filters-actions">
                    <button type="submit" class="btn btn-primary">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="11" cy="11" r="8"></circle>
                            <path d="M21 21L16.65 16.65"></path>
                        </svg>
                        Применить фильтры
                    </button>
                    <a href="{{ route('advertisements.index') }}" class="btn btn-outline-secondary">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <line x1="18" y1="6" x2="6" y2="18"></line>
                            <line x1="6" y1="6" x2="18" y2="18"></line>
                        </svg>
                        Сбросить
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Активные фильтры -->
    @if(request('category_id') || request('company_id') || request('status_id') || request('region_id') || request('search') || request('advertisement_type') == 'all')
    <div class="active-filters">
        <div class="active-filters-header">
            <h4>Активные фильтры:</h4>
            <a href="{{ route('advertisements.index') }}" class="clear-all-filters">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
                Очистить все
            </a>
        </div>
        <div class="active-filters-list">
            @if(request('search'))
                <span class="filter-tag">
                    Поиск: "{{ request('search') }}"
                    <a href="{{ request()->fullUrlWithQuery(['search' => null]) }}" class="remove-filter">×</a>
                </span>
            @endif
            
            @if(request('advertisement_type') == 'all')
                <span class="filter-tag">
                    Тип: Все объявления
                    <a href="{{ request()->fullUrlWithQuery(['advertisement_type' => 'own']) }}" class="remove-filter">×</a>
                </span>
            @endif
            
            @if(request('category_id'))
                @php $category = $filterData['categories']->firstWhere('id', request('category_id')); @endphp
                @if($category)
                <span class="filter-tag">
                    Категория: {{ $category->name }}
                    <a href="{{ request()->fullUrlWithQuery(['category_id' => null]) }}" class="remove-filter">×</a>
                </span>
                @endif
            @endif
            
            @if(request('company_id'))
                @php $company = $filterData['companies']->firstWhere('id', request('company_id')); @endphp
                @if($company)
                <span class="filter-tag">
                    Поставщик: {{ $company->name }}
                    <a href="{{ request()->fullUrlWithQuery(['company_id' => null]) }}" class="remove-filter">×</a>
                </span>
                @endif
            @endif
            
            @if(request('status_id'))
                @php $status = $filterData['statuses']->firstWhere('id', request('status_id')); @endphp
                @if($status)
                <span class="filter-tag">
                    Статус: {{ $status->name }}
                    <a href="{{ request()->fullUrlWithQuery(['status_id' => null]) }}" class="remove-filter">×</a>
                </span>
                @endif
            @endif
            
            @if(request('region_id'))
                @php $region = $filterData['regions']->firstWhere('id', request('region_id')); @endphp
                @if($region)
                <span class="filter-tag">
                    Регион: {{ $region->name }}
                    <a href="{{ request()->fullUrlWithQuery(['region_id' => null]) }}" class="remove-filter">×</a>
                </span>
                @endif
            @endif
        </div>
    </div>
    @endif

    <!-- Информация о результатах -->
    <div class="results-info">
        <div class="results-count">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M20 7L10 17L5 12"></path>
            </svg>
            Найдено объявлений: <strong>{{ $advertisements->total() }}</strong>
        </div>
        @if(request('category_id') || request('company_id') || request('status_id') || request('region_id') || request('search') || request('advertisement_type') == 'all')
        <div class="results-filters-info">
            с примененными фильтрами
        </div>
        @endif
    </div>

    <div class="advertisements-table-wrapper">
        <table class="advertisements-table">
            <thead>
                <tr>
                    <th>Название</th>
                    <th>Категория</th>
                    <th>Поставщик</th>
                    <th>Характеристики</th>
                    <th>Следующее действие</th>
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
                                    @if($advertisement->getMainImage())
                                        <img src="{{ asset('storage/' . $advertisement->getMainImage()->file_path) }}" alt="{{ $advertisement->title }}">
                                    @else
                                        <img src="{{ asset('assets/img/stanok.png') }}" alt="{{ $advertisement->title }}">
                                    @endif
                                </a>
                            </div>
                            
                            @if($advertisement->tags->count() > 0)
                            <div class="advertisement-tags">
                                @foreach($advertisement->tags as $tag)
                                    <span class="tag">{{ $tag->tag }}</span>
                                @endforeach
                            </div>
                            @endif
                            
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
                                @if(\App\Helpers\AdvertisementHelper::canViewSupplierInfo($advertisement))
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
                                @else
                                    <span style="color: #999; font-style: italic;">Скрыто</span>
                                @endif
                            </div>
                            @if($advertisement->product && $advertisement->product->company && $advertisement->product->company->region)
                                <div class="supplier-region">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                                        <circle cx="12" cy="10" r="3"></circle>
                                    </svg>
                                    <span>Регион: {{ $advertisement->product->company->region->name }}</span>
                                </div>
                            @else
                                <div class="supplier-region">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                                        <circle cx="12" cy="10" r="3"></circle>
                                    </svg>
                                    <span>Регион: Не указан</span>
                                </div>
                            @endif
                            
                            @if($advertisement->creator)
                            <div class="responsible-item">
                                <div class="responsible-label">Ответственный:</div>
                                <div class="responsible-name">{{ $advertisement->creator->name }}</div>
                                <div class="responsible-actions">
                                    <button class="action-btn contact-card-btn" title="Просмотр" 
                                        data-id="{{ $advertisement->creator->id }}"
                                        data-name="{{ $advertisement->creator->name }}"
                                        data-email="{{ $advertisement->creator->email }}"
                                        data-phone="{{ $advertisement->creator->phone }}"
                                        data-role="{{ $advertisement->creator->role->name ?? 'Роль не указана' }}"
                                        data-telegram="{{ $advertisement->creator->has_telegram ? 'true' : 'false' }}"
                                        data-whatsapp="{{ $advertisement->creator->has_whatsapp ? 'true' : 'false' }}">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                            <circle cx="12" cy="12" r="3"></circle>
                                        </svg>
                                    </button>
                                    @if($advertisement->creator->has_telegram)
                                    <a href="https://t.me/{{ $advertisement->creator->phone }}" target="_blank" class="action-btn" title="Telegram">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M22 2L11 13"></path>
                                            <path d="M22 2L15 22L11 13L2 9L22 2Z"></path>
                                        </svg>
                                    </a>
                                    @endif
                                    @if($advertisement->creator->has_whatsapp)
                                    <a href="https://wa.me/{{ $advertisement->creator->phone }}" target="_blank" class="action-btn" title="WhatsApp">
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
                                <span class="char-value">{{ Str::limit($advertisement->main_characteristics, 50) }}</span>
                            </div>
                            @endif
                            @if($advertisement->product && $advertisement->product->mark)
                            <div class="char-item">
                                <span class="char-label">Оценка:</span>
                                <span class="char-value">{{ Str::limit($advertisement->product->mark, 50) }}</span>
                            </div>
                            @endif
                            @if($advertisement->complectation)
                            <div class="char-item">
                                <span class="char-label">Компл:</span>
                                <span class="char-value">{{ Str::limit($advertisement->complectation, 50) }}</span>
                            </div>
                            @endif
                            @if($advertisement->product && $advertisement->product->check->count() > 0)
                            <div class="char-item">
                                <span class="char-label">Проверка:</span>
                                <span class="char-value">{{ $advertisement->product->check->first()->checkStatus->name ?? 'Не указана' }}</span>
                            </div>
                            @endif
                            @if($advertisement->product && $advertisement->product->loading->count() > 0)
                            <div class="char-item">
                                <span class="char-label">Погрузка:</span>
                                <span class="char-value">{{ $advertisement->product->loading->first()->installStatus->name ?? 'Не указана' }}</span>
                            </div>
                            @endif
                            @if($advertisement->product && $advertisement->product->removal->count() > 0)
                            <div class="char-item">
                                <span class="char-label">Демонтаж:</span>
                                <span class="char-value">{{ $advertisement->product->removal->first()->installStatus->name ?? 'Не указан' }}</span>
                            </div>
                            @endif
                        </div>
                    </td>

                    <td class="action-cell">
                        <div class="action-info">
                            @if($advertisement->getLastAvailableAction())
                                @php $lastAction = $advertisement->getLastAvailableAction(); @endphp
                                <div class="action-date">{{ $lastAction->expired_at->format('d.m.Y') }}</div>
                                <div class="action-text">{{ Str::limit($lastAction->action, 80) }}</div>
                                <div class="action-status">
                                    <span class="status-indicator pending">Ожидает выполнения</span>
                                </div>
                            @else
                                <div class="action-date">{{ $advertisement->created_at->format('d.m.Y H:i') }}</div>
                                <div class="action-text">Нет активных действий</div>
                                <div class="action-status">
                                    <span class="status-indicator no-action">Действия не заданы</span>
                                </div>
                            @endif
                        </div>
                    </td>

                    <td class="status-cell">
                        <div class="status-list">
                            <div class="status-item">
                                <div class="status-label">Статус объявления</div>
                                <div class="status-badge status-{{ $advertisement->status?->id ?? 'unknown' }}">
                                    {{ $advertisement->status_name }}
                                </div>
                            </div>
                        </div>
                        <div class="price-info">
                            <div class="price-label">Цена продажи:</div>
                            <div class="price-value">{{ number_format($advertisement->adv_price ?? 0, 0, ',', ' ') }} руб</div>
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
    
    <!-- Пагинация -->
    @if($advertisements->total() > 0)
    <div class="pagination-wrapper">
        <div class="pagination-info">
            Показано {{ $advertisements->firstItem() ?? 0 }} - {{ $advertisements->lastItem() ?? 0 }} из {{ $advertisements->total() }} объявлений
        </div>
        @if($advertisements->hasPages())
        <div class="pagination-links">
            <ul class="pagination">
                {{-- Предыдущая страница --}}
                @if ($advertisements->onFirstPage())
                    <li class="page-item disabled">
                        <span class="page-link">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polyline points="15,18 9,12 15,6"></polyline>
                            </svg>
                        </span>
                    </li>
                @else
                    <li class="page-item">
                        <a class="page-link" href="{{ $advertisements->previousPageUrl() }}" rel="prev">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polyline points="15,18 9,12 15,6"></polyline>
                            </svg>
                        </a>
                    </li>
                @endif

                {{-- Номера страниц --}}
                @foreach ($advertisements->getUrlRange(1, $advertisements->lastPage()) as $page => $url)
                    @if ($page == $advertisements->currentPage())
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
                @if ($advertisements->hasMorePages())
                    <li class="page-item">
                        <a class="page-link" href="{{ $advertisements->nextPageUrl() }}" rel="next">
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

@push('scripts')
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

// Функция для переключения видимости фильтров
function toggleFilters() {
    const filtersContent = document.getElementById('filtersContent');
    const filtersPanel = document.querySelector('.filters-panel');
    const isVisible = filtersPanel.style.maxHeight !== '100px';
    
    if (isVisible) {
        filtersPanel.style.maxHeight = '100px';
        filtersContent.style.display = 'none';
    } else {
        filtersPanel.style.maxHeight = 'none';
        filtersContent.style.display = 'block';
    }
}

// Функция для автоматического поиска при вводе
function setupSearchInput() {
    const searchInput = document.getElementById('search');
    let searchTimeout;
    
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(function() {
                // Если поле поиска не пустое, отправляем форму
                if (searchInput.value.trim() !== '') {
                    searchInput.closest('form').submit();
                }
            }, 500); // Задержка 500мс после остановки ввода
        });
        
        // При нажатии Enter сразу отправляем форму
        searchInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                clearTimeout(searchTimeout);
                searchInput.closest('form').submit();
            }
        });
    }
}

// Инициализация поиска при загрузке страницы
document.addEventListener('DOMContentLoaded', function() {
    setupSearchInput();
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

// Закрытие модальных окон при клике вне их - ОТКЛЮЧЕНО
// window.onclick = function(event) {
//     const contactModal = document.getElementById('contactModal');
//     const companyModal = document.getElementById('companyModal');
//     
//     if (event.target === contactModal) {
//         closeContactCard();
//     }
//     
//     if (event.target === companyModal) {
//         closeCompanyCard();
//     }
// }
</script>
@endpush

@push('styles')
<style>
.advertisements-container {
    max-width: 1400px;
    margin: 0 auto;
    padding: 20px;
}

/* Стили для панели фильтров */
.filters-panel {
    background: white;
    border-radius: 12px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.1);
    margin-bottom: 20px;
    overflow: hidden;
}

.filters-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 20px 25px;
    border-bottom: 1px solid #e9ecef;
    background: linear-gradient(180deg, #f8f9fa 0%, #e9ecef 100%);
}

.filters-header h3 {
    margin: 0;
    color: #133E71;
    font-size: 18px;
    font-weight: 600;
}

.filters-content {
    padding: 25px;
    display: block;
}

.filters-form {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.filters-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
}

.filter-group {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.filter-group label {
    font-weight: 600;
    color: #495057;
    font-size: 14px;
}

.form-select {
    padding: 10px 12px;
    border: 1px solid #ced4da;
    border-radius: 6px;
    font-size: 14px;
    background-color: white;
    transition: all 0.3s ease;
}

.form-select:focus {
    outline: none;
    border-color: #133E71;
    box-shadow: 0 0 0 3px rgba(19, 62, 113, 0.1);
}

.filters-actions {
    display: flex;
    gap: 12px;
    align-items: center;
    flex-wrap: wrap;
}

.btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 10px 20px;
    border: none;
    border-radius: 8px;
    font-size: 14px;
    font-weight: 500;
    text-decoration: none;
    transition: all 0.3s ease;
    cursor: pointer;
}

.btn-primary {
    background: #133E71;
    color: white;
    box-shadow: 0 2px 8px rgba(19, 62, 113, 0.3);
}

.btn-primary:hover {
    background: #1C5BA4;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(19, 62, 113, 0.4);
}

.btn-secondary {
    background: #6c757d;
    color: white;
    box-shadow: 0 2px 8px rgba(108, 117, 125, 0.3);
}

.btn-secondary:hover {
    background: #5a6268;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(108, 117, 125, 0.4);
}

.btn-outline-secondary {
    background: transparent;
    color: #6c757d;
    border: 1px solid #6c757d;
}

.btn-outline-secondary:hover {
    background: #6c757d;
    color: white;
    transform: translateY(-2px);
}

.btn svg {
    width: 16px;
    height: 16px;
}

/* Стили для активных фильтров */
.active-filters {
    background: #f8f9fa;
    border-radius: 12px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.1);
    margin-bottom: 20px;
    padding: 20px;
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.active-filters-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding-bottom: 15px;
    border-bottom: 1px solid #e9ecef;
}

.active-filters-header h4 {
    margin: 0;
    color: #133E71;
    font-size: 16px;
    font-weight: 600;
}

.clear-all-filters {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    color: #6c757d;
    text-decoration: none;
    font-size: 14px;
    font-weight: 500;
    transition: all 0.3s ease;
}

.clear-all-filters:hover {
    color: #133E71;
    transform: translateY(-1px);
}

.clear-all-filters svg {
    width: 14px;
    height: 14px;
}

.active-filters-list {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
}

.filter-tag {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: #e8f0fe;
    border: 1px solid #133E71;
    border-radius: 12px;
    padding: 6px 12px;
    font-size: 12px;
    font-weight: 500;
    color: #133E71;
    white-space: nowrap;
}

.filter-tag .remove-filter {
    color: #dc3545;
    font-size: 16px;
    cursor: pointer;
    transition: all 0.3s ease;
}

.filter-tag .remove-filter:hover {
    color: #c82333;
    transform: scale(1.1);
}

/* Стили для информации о результатах */
.results-info {
    background: white;
    border-radius: 12px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.1);
    margin-bottom: 20px;
    padding: 20px;
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.results-count {
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 16px;
    color: #495057;
}

.results-count svg {
    color: #28a745;
}

.results-count strong {
    color: #133E71;
    font-weight: 600;
}

.results-filters-info {
    font-size: 14px;
    color: #6c757d;
    font-style: italic;
}

/* Стили для поля поиска */
.search-group {
    grid-column: 1 / -1;
}

.search-input-wrapper {
    position: relative;
    display: flex;
    align-items: center;
}

.form-control {
    padding: 10px 12px;
    border: 1px solid #ced4da;
    border-radius: 6px;
    font-size: 14px;
    background-color: white;
    transition: all 0.3s ease;
    width: 100%;
}

.form-control:focus {
    outline: none;
    border-color: #133E71;
    box-shadow: 0 0 0 3px rgba(19, 62, 113, 0.1);
}

.search-btn {
    position: absolute;
    right: 8px;
    top: 50%;
    transform: translateY(-50%);
    background: none;
    border: none;
    cursor: pointer;
    padding: 6px;
    border-radius: 4px;
    color: #6c757d;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    justify-content: center;
}

.search-btn:hover {
    background-color: #f8f9fa;
    color: #133E71;
}

.search-btn svg {
    width: 16px;
    height: 16px;
}

.advertisements-table-wrapper {
    background: white;
    border-radius: 12px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.1);
    overflow-x: auto;
    overflow-y: hidden;
}

.advertisements-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 14px;
}

.advertisements-table th {
    background: linear-gradient(180deg, #d9e2f3 0%, #c5d1e8 100%);
    color: #2c3e50;
    padding: 16px 12px;
    text-align: left;
    font-weight: 600;
    font-size: 13px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    border-bottom: 2px solid #a8b8d1;
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

/* Стили для тегов объявлений */
.advertisement-tags {
    display: flex;
    flex-wrap: wrap;
    gap: 4px;
    margin-top: 4px;
}

.tag {
    display: inline-block;
    padding: 2px 8px;
    background: #e8f0fe;
    color: #133E71;
    border: 1px solid #c5d1e8;
    border-radius: 12px;
    font-size: 10px;
    font-weight: 500;
    line-height: 1.2;
    transition: all 0.3s ease;
}

.tag:hover {
    background: #133E71;
    color: white;
    transform: translateY(-1px);
    box-shadow: 0 2px 4px rgba(19, 62, 113, 0.2);
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
    min-width: 250px;
    max-width: 300px;
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

.supplier-name .company-link {
    min-width: 120px;
    max-width: 180px;
    flex-shrink: 0;
    word-wrap: break-word;
    overflow-wrap: break-word;
    hyphens: auto;
    line-height: 1.3;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.company-card-btn {
    width: 24px;
    height: 24px;
    margin-left: 4px;
}

.supplier-region {
    display: flex;
    align-items: flex-start;
    gap: 8px;
    padding: 8px;
    background: #f8f9fa;
    border-radius: 6px;
    border: 1px solid #e9ecef;
}

.supplier-region svg {
    color: #133E71;
    flex-shrink: 0;
    margin-top: 1px;
}

.supplier-region span {
    font-size: 12px;
    color: #333;
    line-height: 1.4;
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
    margin-bottom: 6px;
}

.action-status {
    margin-top: 4px;
}

.status-indicator {
    display: inline-block;
    padding: 2px 8px;
    border-radius: 12px;
    font-size: 10px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.status-indicator.pending {
    background-color: #ffc107;
    color: #212529;
}

.status-indicator.no-action {
    background-color: #6c757d;
    color: white;
}

.action-buttons {
    margin-top: 8px;
}

.action-link {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    color: #133E71;
    text-decoration: none;
    font-size: 11px;
    font-weight: 500;
    padding: 4px 8px;
    background: #e8f0fe;
    border-radius: 12px;
    transition: all 0.3s ease;
    border: 1px solid #c5d1e8;
}

.action-link:hover {
    background: #133E71;
    color: white;
    transform: translateY(-1px);
    box-shadow: 0 2px 4px rgba(19, 62, 113, 0.2);
}

.action-link svg {
    width: 12px;
    height: 12px;
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

/* Стили для статусов объявлений по ID */
.status-1 {
    background-color: #FFA500 !important;
    color: white !important;
}

.status-2 {
    background-color: #28A745 !important;
    color: white !important;
}

.status-3 {
    background-color: #17A2B8 !important;
    color: white !important;
}

.status-4 {
    background-color: #FFC107 !important;
    color: #212529 !important;
}

.status-5 {
    background-color: #6F42C1 !important;
    color: white !important;
}

.status-6 {
    background-color: #6C757D !important;
    color: white !important;
}

.status-unknown {
    background-color: #f5f5f5 !important;
    color: #666 !important;
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

/* Адаптивность */
@media (max-width: 1600px) {
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
    
    .advertisement-name {
        font-size: 15px;
    }
    
    .supplier-name,
    .responsible-name {
        font-size: 13px;
    }
    
    .advertisement-sku,
    .responsible-label,
    .status-label {
        font-size: 11px;
    }
    
    .action-btn {
        width: 26px;
        height: 26px;
    }
    
    .action-btn svg {
        width: 13px;
        height: 13px;
    }
    
    .responsible-actions .action-btn {
        width: 22px;
        height: 22px;
    }
    
    .responsible-actions .action-btn svg {
        width: 11px;
        height: 11px;
    }
    
    .char-value {
        font-size: 11px;
    }
    
    .action-text {
        font-size: 11px;
    }
    
    .status-badge {
        font-size: 11px;
        padding: 5px 10px;
    }
    
    .tag {
        font-size: 9px;
        padding: 2px 6px;
    }
}

@media (max-width: 1200px) {
    .advertisements-table {
        font-size: 12px;
    }
    
    .advertisements-table th,
    .advertisements-table td {
        padding: 10px 6px;
    }
    
    .advertisements-table th:first-child,
    .advertisements-table td:first-child {
        padding-left: 12px;
    }
    
    .advertisements-table th:last-child,
    .advertisements-table td:last-child {
        padding-right: 12px;
    }
    
    .advertisement-name {
        font-size: 14px;
    }
    
    .supplier-name,
    .responsible-name {
        font-size: 12px;
    }
    
    .advertisement-sku,
    .responsible-label,
    .status-label {
        font-size: 10px;
    }
    
    .action-btn {
        width: 24px;
        height: 24px;
    }
    
    .action-btn svg {
        width: 12px;
        height: 12px;
    }
    
    .responsible-actions .action-btn {
        width: 20px;
        height: 20px;
    }
    
    .responsible-actions .action-btn svg {
        width: 10px;
        height: 10px;
    }
    
    .char-value {
        font-size: 10px;
    }
    
    .action-text {
        font-size: 10px;
    }
    
    .status-badge {
        font-size: 10px;
        padding: 4px 8px;
    }
    
    .tag {
        font-size: 8px;
        padding: 1px 5px;
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
    
    .advertisement-tags {
        gap: 3px;
    }
    
    .tag {
        font-size: 9px;
        padding: 1px 6px;
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
    
    /* Адаптивность для фильтров */
    .filters-grid {
        grid-template-columns: 1fr;
        gap: 15px;
    }
    
    .search-group {
        grid-column: 1;
    }
    
    .search-input-wrapper {
        flex-direction: column;
        gap: 10px;
    }
    
    .search-btn {
        position: static;
        transform: none;
        width: 100%;
        justify-content: center;
        padding: 10px;
        background-color: #133E71;
        color: white;
        border-radius: 6px;
    }
    
    .search-btn:hover {
        background-color: #1C5BA4;
        color: white;
    }
    
    .filters-header {
        padding: 15px 20px;
    }
    
    .filters-content {
        padding: 20px;
    }
    
    .filters-actions {
        flex-direction: column;
        align-items: stretch;
    }
    
    .btn {
        justify-content: center;
    }
    
    /* Адаптивность для активных фильтров */
    .active-filters {
        padding: 15px;
    }
    
    .active-filters-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 10px;
    }
    
    .clear-all-filters {
        width: 100%;
        justify-content: center;
    }
    
    .filter-tag {
        flex-wrap: wrap;
        padding: 6px 10px;
        font-size: 11px;
    }
    
    .filter-tag .remove-filter {
        font-size: 14px;
    }
    
    /* Адаптивность для информации о результатах */
    .results-info {
        padding: 15px;
    }
    
    .results-count {
        font-size: 14px;
    }
    
    .results-filters-info {
        font-size: 12px;
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

/* Стили для горизонтальной прокрутки */
.advertisements-table-wrapper::-webkit-scrollbar {
    height: 8px;
}

.advertisements-table-wrapper::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 4px;
}

.advertisements-table-wrapper::-webkit-scrollbar-thumb {
    background: #133E71;
    border-radius: 4px;
}

.advertisements-table-wrapper::-webkit-scrollbar-thumb:hover {
    background: #1C5BA4;
}

/* Адаптивность для горизонтальной прокрутки */
@media (max-width: 1600px) {
    .advertisements-table {
        min-width: 1100px;
    }
}

@media (max-width: 1200px) {
    .advertisements-table {
        min-width: 1000px;
    }
}

@media (max-width: 768px) {
    .advertisements-table {
        min-width: 900px;
    }
    
    .advertisements-table-wrapper::-webkit-scrollbar {
        height: 6px;
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