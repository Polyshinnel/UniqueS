@extends('layouts.layout')

@section('title', 'Главная')

@section('header-title')
    <h1 class="header-title">Организации</h1>
@endsection

@section('content')

<div class="organizations-table">
    <table class="table">
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
            @foreach($companies as $company)
            <tr class="organization-item">
                <td>
                    <div class="organization-name">
                        <p class="warehouse-name">{{ $company->sku }}</p>
                        <p class="organization-name">{{ $company->name }}</p>
                        <a href="/company/{{ $company->id }}" class="organization-history">Подробнее</a>
                    </div>
                </td>
            
                <td>
                    @if($company->contacts->isNotEmpty())
                    <div class="contact-main">
                        <p class="contact-main-role">{{ $company->contacts->first()->position }}</p>
                        <p class="contact-main-name">{{ $company->contacts->first()->name }}</p>
                        @foreach($company->contacts->first()->phones as $phone)
                            <p class="contact-main-phone">{{ $phone->phone }}</p>
                        @endforeach
                    </div>

                    <div class="contact-socials">
                        <a href="#">
                            <div class="social-round">
                                <img src="{{ asset('assets/img/icons/eye-w.svg') }}" alt="telegram">
                            </div>
                        </a>
                        <a href="#">
                            <div class="social-round">
                                <img src="{{ asset('assets/img/icons/telegram.svg') }}" alt="telegram">
                            </div>
                        </a>
                        <a href="#">
                            <div class="social-round">
                                <img src="{{ asset('assets/img/icons/whatsapp.svg') }}" alt="telegram">
                            </div>
                        </a>
                    </div>
                    @endif
                </td>

                <td>
                    @if($company->regional)
                    <div class="responsible-item">
                        <p class="responsible-main-name">Регионал: {{ $company->regional->name }}</p>
                        <div class="contact-socials">
                            <a href="#">
                                <div class="social-round">
                                    <img src="{{ asset('assets/img/icons/eye-w.svg') }}" alt="telegram">
                                </div>
                            </a>
                            <a href="#">
                                <div class="social-round">
                                    <img src="{{ asset('assets/img/icons/telegram.svg') }}" alt="telegram">
                                </div>
                            </a>
                            <a href="#">
                                <div class="social-round">
                                    <img src="{{ asset('assets/img/icons/whatsapp.svg') }}" alt="telegram">
                                </div>
                            </a>
                        </div>
                    </div>
                    @endif
                    
                    @if($company->owner)
                    <div class="responsible-item">
                        <p class="responsible-main-name">Менеджер: {{ $company->owner->name }}</p>
                        <div class="contact-socials">
                            <a href="#">
                                <div class="social-round">
                                    <img src="{{ asset('assets/img/icons/eye-w.svg') }}" alt="telegram">
                                </div>
                            </a>
                            <a href="#">
                                <div class="social-round">
                                    <img src="{{ asset('assets/img/icons/telegram.svg') }}" alt="telegram">
                                </div>
                            </a>
                            <a href="#">
                                <div class="social-round">
                                    <img src="{{ asset('assets/img/icons/whatsapp.svg') }}" alt="telegram">
                                </div>
                            </a>
                        </div>
                    </div>
                    @endif
                </td>

                <td>
                    @if($company->addresses->isNotEmpty())
                        <p class="address-main">{{ $company->addresses->first()->address }}</p>
                    @endif
                </td>
                
                <td>
                    <p class="info-main">{{ $company->common_info }}</p>
                </td>

                <td>
                    <p class="date-main">{{ now()->format('d.m.Y H:i:s') }}</p>
                    <p>Вывести текст действия</p>
                </td>

                <td>
                    @if($company->status)
                    <div class="status-item" style="background-color: {{ $company->status->color }}">
                        <span class="status-main">{{ $company->status->name }}</span>
                    </div>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>



<x-right-sidebar>
    <div class="source-form">
        <h2>Создание компании</h2>
        @if ($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        <form action="{{ route('companies.store') }}" method="POST">
            @csrf
            <div class="form-group">
                <label for="sku">Артикул поставщика</label>
                <input type="text" name="sku" id="sku" value="{{ old('sku') }}" class="form-control" required>
                @error('sku')
                    <span class="error">{{ $message }}</span>
                @enderror
            </div>

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

            <div class="form-group">
                <label for="region_id">ФИО регионала</label>
                <select name="region_id" id="region_id" class="form-control" required>
                    <option value="">Выберите регионала</option>
                    @foreach($regionals as $regional)
                        <option value="{{ $regional->id }}" {{ old('region_id') == $regional->id ? 'selected' : '' }}>
                            {{ $regional->name }}
                        </option>
                    @endforeach
                </select>
                @error('region_id')
                    <span class="error">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label for="region">Регион</label>
                <select name="region" id="region" class="form-control" required>
                    <option value="">Выберите регион</option>
                    @foreach($regions as $region)
                        <option value="{{ $region->id }}" {{ old('region') == $region->id ? 'selected' : '' }}>
                            {{ $region->name }}
                        </option>
                    @endforeach
                </select>
                @error('region')
                    <span class="error">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label for="inn">ИНН</label>
                <input type="text" name="inn" id="inn" value="{{ old('inn') }}" class="form-control">
                @error('inn')
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

            <div class="form-group">
                <label>Адреса</label>
                <div id="addresses-container">
                    <div class="address-block">
                        <input type="text" name="addresses[]" class="form-control" placeholder="Введите адрес">
                        <div class="form-check">
                            <input type="checkbox" name="main_address[]" class="form-check-input">
                            <label class="form-check-label">Основной адрес</label>
                        </div>
                    </div>
                </div>
                <button type="button" class="btn btn-secondary" id="add-address">+ Добавить адрес</button>
            </div>

            <div class="form-group">
                <label>Контактные лица</label>
                <div id="contacts-container">
                    <div class="contact-block">
                        <input type="text" name="contact_name[]" class="form-control" placeholder="ФИО контактного лица" value="{{ old('contact_name.0') }}">
                        @error('contact_name.0')
                            <span class="error">{{ $message }}</span>
                        @enderror
                        <div class="phones-container">
                            <div class="phone-block">
                                <div class="phone-input-group">
                                    <input type="tel" name="phones[0][]" class="form-control" placeholder="Телефон" value="{{ old('phones.0.0') }}">
                                    <button type="button" class="btn btn-secondary add-phone">+</button>
                                </div>
                            </div>
                        </div>
                        <input type="text" name="position[]" class="form-control" placeholder="Должность" value="{{ old('position.0') }}">
                        @error('position.0')
                            <span class="error">{{ $message }}</span>
                        @enderror
                        <div class="form-check">
                            <input type="checkbox" name="main_contact[]" class="form-check-input" value="1" {{ old('main_contact.0') ? 'checked' : '' }}>
                            <label class="form-check-label">Основной контакт</label>
                        </div>
                    </div>
                </div>
                <button type="button" class="btn btn-secondary" id="add-contact">+ Добавить контактное лицо</button>
            </div>

            <div class="form-group">
                <label for="site">Сайт</label>
                <input type="url" name="site" id="site" value="{{ old('site') }}" class="form-control">
                @error('site')
                    <span class="error">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label for="email">Email организации</label>
                <input type="email" name="email" id="email" value="{{ old('email') }}" class="form-control">
                @error('email')
                    <span class="error">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label for="common_info">Суть разговора</label>
                <textarea name="common_info" id="common_info" class="form-control" rows="4">{{ old('common_info') }}</textarea>
                @error('common_info')
                    <span class="error">{{ $message }}</span>
                @enderror
            </div>

            <button type="submit" class="btn btn-primary">Создать компанию</button>
        </form>
    </div>
</x-right-sidebar>
@endsection

@push('scripts')
<script src="{{ asset('assets/js/side-panel.js') }}"></script>
<script src="{{ asset('assets/js/company-form.js') }}"></script>
@endpush