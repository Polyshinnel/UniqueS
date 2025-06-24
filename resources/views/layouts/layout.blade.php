<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Сайт')</title>
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">
    @yield('styles')
</head>
<body>
    <div class="sidebar">
        <div class="logo-block">
            <a href="/"><img src="{{ asset('assets/img/logo-small.png') }}" alt="logo"></a>
        </div>

        <div class="menu-block">
            <ul class="events menu-list">
                <li>
                    <div class="menu-block-item">
                        <a href="/events"><img src="{{ asset('assets/img/icons/bell.svg') }}" alt="events"></a>
                    </div>
                </li>

                <li>
                    <div class="menu-block-item">
                        @section('add-btn-link')
                            <img src="{{ asset('assets/img/icons/plus.svg') }}" alt="plus" class="add-btn">
                        @show
                    </div>
                </li>
            </ul>

            <ul class="menu-list main-menu-list">
                <li>
                    <div class="menu-block-item {{ request()->is('company*') ? 'menu-block-item__active' : '' }}">
                        <a href="/company"><img src="{{ asset('assets/img/icons/organizations.svg') }}" alt="events"></a>
                    </div>
                </li>

                <li>
                    <div class="menu-block-item {{ request()->is('products*') ? 'menu-block-item__active' : '' }}">
                        <a href="/products"><img src="{{ asset('assets/img/icons/machines.svg') }}" alt="events"></a>
                    </div>
                </li>

                <li>
                    <div class="menu-block-item {{ request()->is('adv*') ? 'menu-block-item__active' : '' }}">
                        <a href="/adv"><img src="{{ asset('assets/img/icons/adv.svg') }}" alt="events"></a>
                    </div>
                </li>

                <li>
                    <div class="menu-block-item {{ request()->is('guide*') ? 'menu-block-item__active' : '' }}">
                        <a href="/guide"><img src="{{ asset('assets/img/icons/book.svg') }}" alt="events"></a>
                    </div>
                </li>
            </ul>
        </div>
    </div>

    <div class="main-container">
        <div class="header">
            @section('header-title')
                <!-- Содержимое шапки по умолчанию -->
                <h1 class="header-title">Заголовок страницы</h1>
                
            @show

            @section('search-filter')
            <div class="search-container">
                <form action="/search" method="GET" class="search-form">
                    <input type="text" name="query" placeholder="Поиск..." class="search-input">
                    <button type="submit" class="search-button">
                        <img src="{{ asset('assets/img/icons/search.svg') }}" alt="search">
                    </button>
                </form>
            </div>

            <div class="filter-container">
                <button class="filter-button">
                    <img src="{{ asset('assets/img/icons/filter.svg') }}" alt="filter">
                    <span>Фильтр</span>
                </button>
            </div>
            @show
            
            @section('header-action-btn')
            <div class="add-org-container header-btn">
                <img src="{{ asset('assets/img/icons/plus.svg') }}" alt="add">
                <span>Добавить организацию</span>
            </div>
            @show
        </div>

        <div class="content">
            @yield('content')
        </div>
    </div>

    @stack('scripts')
</body>
</html>
