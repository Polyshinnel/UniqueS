@extends('layouts.layout')

@section('title', 'Создание компании')

@section('search-filter')
@endsection

@section('header-action-btn')
@endsection

@section('header-title')
    <h1 class="header-title">Создание компании</h1>
@endsection

@section('content')
<div class="company-create-container">
    <!-- Индикатор шагов -->
    <div class="steps-indicator">
        <div class="step active" data-step="1">
            <span class="step-number">1</span>
            <span class="step-title">Основное</span>
        </div>
        <div class="step" data-step="2">
            <span class="step-number">2</span>
            <span class="step-title">Адреса</span>
        </div>
        <div class="step" data-step="3">
            <span class="step-number">3</span>
            <span class="step-title">Контакты</span>
        </div>
        <div class="step" data-step="4">
            <span class="step-number">4</span>
            <span class="step-title">Дополнительно</span>
        </div>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form id="companyForm" method="POST" action="{{ route('companies.store') }}">
        @csrf

        <!-- Шаг 1: Основная информация -->
        <div class="step-content active" id="step-1">
            <h2>Основная информация</h2>

            <div class="form-row">
                <div class="form-group">
                    <label for="sku">Артикул поставщика</label>
                    <input type="text" name="sku" id="sku" value="{{ old('sku') }}" class="form-control" placeholder="Будет сгенерирован автоматически">
                    <small class="form-text text-muted">Артикул будет сгенерирован автоматически при выборе склада</small>
                    @error('sku')
                        <span class="error">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="name">Название компании</label>
                    <input type="text" name="name" id="name" value="{{ old('name') }}" class="form-control" required>
                    @error('name')
                        <span class="error">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="warehouse_id">Склад поставщика</label>
                    <select name="warehouse_id" id="warehouse_id" class="form-control" required>
                        <option value="">Выберите склад</option>
                        @foreach($warehouses as $warehouse)
                            <option value="{{ $warehouse->id }}" {{ old('warehouse_id') == $warehouse->id ? 'selected' : '' }}>
                                {{ $warehouse->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('warehouse_id')
                        <span class="error">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="source_id">Источник контакта</label>
                    <select name="source_id" id="source_id" class="form-control" required>
                        <option value="">Выберите источник</option>
                        @foreach($sources as $source)
                            <option value="{{ $source->id }}" {{ old('source_id') == $source->id ? 'selected' : '' }}>
                                {{ $source->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('source_id')
                        <span class="error">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="region">Регион</label>
                    <select name="region" id="region" class="form-control" required>
                        <option value="">Сначала выберите склад</option>
                    </select>
                    @if(old('region'))
                        <input type="hidden" id="old_region" value="{{ old('region') }}">
                    @endif
                    @error('region')
                        <span class="error">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="region_id">ФИО регионала</label>
                    <select name="region_id" id="region_id" class="form-control" required>
                        <option value="">Сначала выберите регион</option>
                    </select>
                    @if(old('region_id'))
                        <input type="hidden" id="old_region_id" value="{{ old('region_id') }}">
                    @endif
                    @error('region_id')
                        <span class="error">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            <div class="form-group">
                <label for="inn">ИНН</label>
                <input type="text" name="inn" id="inn" value="{{ old('inn') }}" class="form-control">
                @error('inn')
                    <span class="error">{{ $message }}</span>
                @enderror
            </div>

            <div class="step-actions">
                <button type="button" class="btn btn-primary next-step">
                    Следующий шаг
                </button>
            </div>
        </div>

        <!-- Шаг 2: Адреса -->
        <div class="step-content" id="step-2">
            <h2>Адреса компании</h2>

            <div class="form-group">
                <label>Адреса</label>
                <div id="addresses-container">
                    <div class="address-block">
                        <input type="text" name="addresses[]" class="form-control" placeholder="Введите адрес" required>
                        <div class="form-check">
                            <input type="checkbox" name="main_address[]" class="form-check-input" value="1">
                            <label class="form-check-label">Основной адрес</label>
                        </div>
                    </div>
                </div>
                <button type="button" class="btn btn-secondary" id="add-address">+ Добавить адрес</button>
                @error('main_address')
                    <span class="error">{{ $message }}</span>
                @enderror
            </div>

            <div class="step-actions">
                <button type="button" class="btn btn-secondary prev-step">Предыдущий шаг</button>
                <button type="button" class="btn btn-primary next-step">Следующий шаг</button>
            </div>
        </div>

        <!-- Шаг 3: Контактные лица -->
        <div class="step-content" id="step-3">
            <h2>Контактные лица</h2>

            <div class="form-group">
                <label>Контактные лица</label>
                <div id="contacts-container">
                    <div class="contact-block">
                        <input type="text" name="contact_name[]" class="form-control" placeholder="ФИО контактного лица" required>
                        @error('contact_name.0')
                            <span class="error">{{ $message }}</span>
                        @enderror
                        
                        <div class="phones-container">
                            <div class="phone-block">
                                <div class="phone-input-group">
                                    <input type="tel" name="phones[0][]" class="form-control" placeholder="Телефон" required>
                                    <button type="button" class="btn btn-secondary add-phone">+</button>
                                </div>
                            </div>
                        </div>
                        
                        <div class="emails-container">
                            <div class="email-block">
                                <div class="email-input-group">
                                    <input type="email" name="contact_emails[0][]" class="form-control" placeholder="Email">
                                    <button type="button" class="btn btn-secondary add-email">+</button>
                                </div>
                            </div>
                        </div>
                        
                        <input type="text" name="position[]" class="form-control" placeholder="Должность" required>
                        @error('position.0')
                            <span class="error">{{ $message }}</span>
                        @enderror
                        
                        <div class="form-check">
                            <input type="checkbox" name="main_contact[]" class="form-check-input" value="1">
                            <label class="form-check-label">Основной контакт</label>
                        </div>
                    </div>
                </div>
                <button type="button" class="btn btn-secondary" id="add-contact">+ Добавить контактное лицо</button>
                @error('main_contact')
                    <span class="error">{{ $message }}</span>
                @enderror
            </div>

            <div class="step-actions">
                <button type="button" class="btn btn-secondary prev-step">Предыдущий шаг</button>
                <button type="button" class="btn btn-primary next-step">Следующий шаг</button>
            </div>
        </div>

        <!-- Шаг 4: Дополнительная информация -->
        <div class="step-content" id="step-4">
            <h2>Дополнительная информация</h2>

            <div class="form-row">
                <div class="form-group">
                    <label for="email">Email организации</label>
                    <input type="email" name="email" id="email" value="{{ old('email') }}" class="form-control">
                    @error('email')
                        <span class="error">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="phone">Телефон компании</label>
                    <input type="tel" name="phone" id="phone" value="{{ old('phone') }}" class="form-control" placeholder="+7 (999) 123-45-67">
                    @error('phone')
                        <span class="error">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="site">Сайт</label>
                    <input type="url" name="site" id="site" value="{{ old('site') }}" class="form-control" placeholder="https://example.com">
                    @error('site')
                        <span class="error">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            <div class="form-group">
                <label>Дополнительные email организации</label>
                <div id="company-emails-container">
                    <div class="company-email-block">
                        <div class="email-input-group">
                            <input type="email" name="company_emails[]" class="form-control" placeholder="Дополнительный email">
                            <button type="button" class="btn btn-secondary add-company-email">+</button>
                        </div>
                    </div>
                </div>
                <small class="form-text text-muted">Можно добавить дополнительные email адреса для организации</small>
            </div>

            <div class="form-group">
                <label for="common_info">Суть разговора</label>
                <textarea name="common_info" id="common_info" class="form-control" rows="4" required>{{ old('common_info') }}</textarea>
                @error('common_info')
                    <span class="error">{{ $message }}</span>
                @enderror
            </div>

            <div class="step-actions">
                <button type="button" class="btn btn-secondary prev-step">Предыдущий шаг</button>
                <button type="submit" class="btn btn-success">Создать компанию</button>
            </div>
        </div>
    </form>
</div>

@endsection

@push('scripts')
<script src="{{ asset('assets/js/company-create.js') }}"></script>
@endpush 