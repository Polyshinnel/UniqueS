@extends('layouts.layout')

@section('title', 'Главная')

@section('header-title')
    <h1 class="header-title">{{ $company->name ?? 'Название не указано' }}</h1>
@endsection

@section('search-filter')
@endsection

@section('header-action-btn')
<div class="edit-organization header-btn">
    <span>Редактировать</span>
</div>
@endsection

@section('content')

<div class="organization-block">
    <div class="organization_top">
        <div class="common-info-block">
            <p class="organization-sku">{{ $company->sku ?? 'SKU не указан' }}</p>

            <h2 class="organization_name">{{ $company->name ?? 'Название не указано' }}</h2>

            <div class="organization_info-common">
                <span>Общая информация</span>
                <p>{{ $company->common_info ?? 'Информация отсутствует' }}</p>
            </div>
        </div>

        <div class="organization-sku-status">
            <span class="organization-sku-status-status-item">{{ $company->status->name ?? 'Статус не указан' }}</span>
        </div>

        <div class="organization-next-action-date">
            <span>Дата следующего действия</span>
            <p>{{ now()->format('d.m.Y') }}</p>
        </div>

        <div class="organization-action-info">
            <span>Что требуется сделать</span>
            <p>Позвонить клиенту, уточнить по наличию оборудования</p>
            <a href="#">Подробнее</a>
        </div>

        <button class="add-new-action-btn">Задать новое действие</button>
    </div>

    <div class="organization-next-line">
        <div class="organization-next-line-item">
            <span>Регион</span>
            <p>{{ $company->region->name ?? 'Регион не указан' }}</p>
        </div>

        <div class="organization-next-line-item">
            <span>Источник контакта</span>
            <p>{{ $company->source->name ?? 'Источник не указан' }}</p>
        </div>

        <div class="organization-next-line-item">
            <span>ФИО Регионала</span>
            <p>{{ $company->regional->name ?? 'Регионал не назначен' }}</p>

            <div class="contact-socials">
                <a href="#">
                    <div class="social-round">
                        <img src="{{ asset('assets/img/icons/eye-w.svg') }}" alt="telegram">
                    </div>
                </a>
                <a href="#">
                    <div class="social-round">
                        <img src="{{ asset('assets/img/icons/phone-w.svg') }}" alt="telegram">
                    </div>
                </a>
            </div>
        </div>

        <div class="organization-next-line-item">
            <span>ФИО менеджера</span>
            <p>{{ $company->owner->name ?? 'Менеджер не назначен' }}</p>

            <div class="contact-socials">
                <a href="#">
                    <div class="social-round">
                        <img src="{{ asset('assets/img/icons/eye-w.svg') }}" alt="telegram">
                    </div>
                </a>
                <a href="#">
                    <div class="social-round">
                        <img src="{{ asset('assets/img/icons/phone-w.svg') }}" alt="telegram">
                    </div>
                </a>
            </div>
        </div>
        
        <div class="organization-next-line-item">
            <span>Фактический адрес</span>
            @forelse($company->addresses as $address)
                <p>{{ $address->address }}</p>
            @empty
                <p>Адреса не указаны</p>
            @endforelse
        </div>
    </div>

    <div class="organization-next-line">
        <div class="organization-next-line-item">
            <span>Основной контакт</span>
            @forelse($company->contacts as $contact)
                @if($contact->main_contact)
                    <p>{{ $contact->name }}</p>
                    @forelse($contact->phones as $phone)
                        <p>{{ $phone->phone }}</p>
                    @empty
                        <p>Телефоны не указаны</p>
                    @endforelse
                @endif
            @empty
                <p>Контакты не указаны</p>
            @endforelse
        </div>

        <div class="organization-next-line-item">
            <span>Контакты</span>
            @forelse($company->contacts as $contact)
                <div class="organization-contact-item">
                    <span>{{ $contact->position ?? 'Должность не указана' }}</span>
                    <div class="contact-line">
                        <p>{{ $contact->name }}</p>
                        <a href="#">
                            <div class="social-round">
                                <img src="{{ asset('assets/img/icons/eye-w.svg') }}" alt="telegram">
                            </div>
                        </a>
                    </div>
                </div>
            @empty
                <p>Контакты не указаны</p>
            @endforelse
        </div>

        <div class="organization-next-line-item">
            <span>Общ. почта</span>
            <p>{{ $company->email ?? 'Email не указан' }}</p>
        </div>

        <div class="organization-next-line-item">
            <span>Общ. телефон</span>
            @forelse($company->contacts as $contact)
                @forelse($contact->phones as $phone)
                    <p>{{ $phone->phone }}</p>
                @empty
                    <p>Телефоны не указаны</p>
                @endforelse
            @empty
                <p>Контакты не указаны</p>
            @endforelse
        </div>

        <div class="organization-next-line-item">
            <span>Сайт</span>
            <p>{{ $company->site ?? 'Сайт не указан' }}</p>
        </div>
        
        <div class="organization-next-line-item">
            <span>Фактический адрес</span>
            @forelse($company->addresses as $address)
                <p>{{ $address->address }}</p>
            @empty
                <p>Адреса не указаны</p>
            @endforelse
        </div>
    </div>

    <div class="organization-next-line">
        <div class="organization-next-line-item">
            <span>Юр. лицо</span>
            <p>ИНН: {{ $company->inn ?? 'ИНН не указан' }}</p>
            <p>Название: {{ $company->name ?? 'Название не указано' }}</p>
        </div>
    </div>

    <div class="event-block">
        <div class="event-block-title">
            <h2>Лог событий</h2>
            <div class="event-btn">История</div>
        </div>

        <div class="event-block-item">
            <div class="event-block-item-title">
                <span>Тип: комментарий</span>
                <span>{{ now()->format('d.m.Y H:i:s') }}</span>
            </div>

            <div class="event-block-item-content">
                <p>Позвонил клиенту, уточнил по наличию оборудования</p>
            </div>

            <div class="event-block-item-footer">
                <span>Создал: {{ $company->owner->name ?? 'Создатель не указан' }}</span>
            </div>
        </div>
    </div>
</div>

<x-right-sidebar>
    <div class="source-form">
        <h2>Добавление источника</h2>
        <form action="{{ route('sources.store') }}" method="POST">
            @csrf
            <div class="form-group">
                <label for="name">Название источника</label>
                <input type="text" name="name" id="name" value="{{ old('name') }}" class="form-control" required>
                @error('name')
                    <span class="error">{{ $message }}</span>
                @enderror
            </div>
            <div class="form-group">
                <label for="active">Статус</label>
                <select name="active" id="active" class="form-control" required>
                    <option value="1" {{ old('active') == '1' ? 'selected' : '' }}>Активен</option>
                    <option value="0" {{ old('active') == '0' ? 'selected' : '' }}>Не активен</option>
                </select>
                @error('active')
                    <span class="error">{{ $message }}</span>
                @enderror
            </div>
            <button type="submit" class="btn btn-primary">Добавить</button>
        </form>
    </div>
</x-right-sidebar>
@endsection

@push('scripts')
<script src="{{ asset('assets/js/side-panel.js') }}"></script>
@endpush