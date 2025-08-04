@extends('layouts.layout')

@section('title', 'Категории')


@section('header-action-btn')
    <div class="add-category header-btn">
        <img src="{{ asset('assets/img/icons/plus.svg') }}" alt="add">
        <span>Добавить объявление</span>
    </div>
@endsection

@section('header-title')
    <h1 class="header-title">Объявления</h1>
@endsection


@section('content')
    <div class="adv-table">
        <table class="table">
            <thead>
                <tr>
                    <th>Название</th>
                    <th>Категория</th>
                    <th>Ответственный</th>
                    <th>Осн. хар-ки, сост, компл</th>
                    <th>Дата след. действия</th>
                    <th>Действие</th>
                    <th>Статус</th>
                </tr>
            </thead>
            <tbody>
                <tr class="adv-item">
                    <td>
                        <div class="adv-name">
                            <div class="adv-name__img-block">
                                <img src="{{ asset('assets/img/stanok.png') }}" alt="">
                            </div>
                            <div class="adv-name__name-block">
                                <p>токарно-винторезный станок 16К20</p>
                                <p>КЛГ001-06072024-1032</p>
                            </div>
                            <p class="adv-price">100 000 руб.</p>
                        </div>
                        <div class="tag-list">
                            <a href="#">Тег 1</a>
                            <a href="#">Тег 2</a>
                            <a href="#">Тег 3</a>
                        </div>
                        <a href="/adv/1" class="adv-link">Подробнее</a>
                    </td>
                    <td>
                        <div class="adv-category">
                            <a href="#">Токарный станок</a>
                            <a href="#">16К20 и аналоги</a>
                        </div>
                    </td>
                    <td>
                        <div class="manager-block">
                            <div class="responsible-item">
                                <p class="responsible-main-name">Менеджер: Нестеров Андрей</p>
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
                        </div>
                    </td>
                    <td>
                        <p><b>Оценка: </b> В отличном состоянии, с проверкой в работе. Направляющие без износа, электрика на пускателях.</p>
                        <p><b>Проверка:</b> Подключен</p>
                        <p><b>Демонтаж:</b> Поставщиком</p>
                        <p><b>Погрузка:</b> Поставщиком</p>
                    </td>
                    <td>
                        <p>01.02.24 11:00</p>
                    </td>
                    <td>
                        <p>Обновить статус объявления</p>
                    </td>
                    <td>
                        <div class="status-block">
                            <p>Статус обьявления</p>
                            <div class="status-item" style="background-color: #133E71">
                                <span class="status-main">Опубликовано</span>
                            </div>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
@endsection
