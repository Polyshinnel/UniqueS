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
                <tr class="organization-item">
                    <td>
                        <div class="organization-name">
                            <p class="warehouse-name">Клг 001</p>
                            <p class="organization-name">ООО "Березка"</p>
                            <button class="organization-history">История</button>
                        </div>
                    </td>
                
                    <td>
                        <div class="contact-main">
                            <p class="contact-main-role">Главный инженер</p>
                            <p class="contact-main-name">Иванов Иван Иванович</p>
                            <p class="contact-main-phone">+7 (999) 999-99-99</p>
                            <p class="contact-main-phone">+7 (999) 999-99-99</p>
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
                    </td>

                    <td>
                        <div class="responsible-item">
                            <p class="responsible-main-name">Регионал:Минин Алексей</p>
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
                        
                        <div class="responsible-item">
                            <p class="responsible-main-name">Менеджер: Лосев Алексей</p>
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
                    </td>

                    <td>
                        <p class="address-main">г. Калуга, ул. Ленина 77</p>
                    </td>
                    
                    <td>
                        <p class="info-main">в продаже 2шт. Токарных, 2шт фрезерных (один продали), открыли вторую площадку, где оборудование на улице. Оборудование обновляют раз в 6 мес.</p>
                    </td>

                    <td>
                        <p class="date-main">01.02.2024 11:00:00</p>
                    </td>

                    <td>
                        <div class="status-item status-item-active">
                            <span class="status-main">В работе</span>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
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