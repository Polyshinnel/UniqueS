<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Сайт')</title>
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">
    @yield('styles')
    @stack('styles')
    
    <style>
        /* Обновленные стили для layout в соответствии с GuidesMainPage */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            margin: 0;
            padding: 0;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            display: flex;
            box-sizing: border-box;
            background-color: #f8f9fa;
            color: #333;
            line-height: 1.6;
        }

        .sidebar {
            width: 120px;
            background: linear-gradient(180deg, #133E71 0%, #1C5BA4 100%);
            color: white;
            height: 100vh;
            position: fixed;
            left: 0;
            top: 0;
            box-shadow: 4px 0 20px rgba(19, 62, 113, 0.15);
            z-index: 1000;
        }

        .main-container {
            margin-left: 120px;
            width: calc(100% - 120px);
            min-height: 100vh;
            background-color: #f8f9fa;
        }

        .header {
            padding: 0 30px;
            border-bottom: 1px solid #e9ecef;
            background: white;
            box-shadow: 0 2px 15px rgba(0,0,0,0.08);
            height: 80px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .header-title {
            font-size: 24px;
            font-weight: 600;
            color: #133E71;
            margin: 0;
        }

        .content {
            padding: 30px;
            overflow-y: auto;
            min-height: calc(100vh - 80px);
        }

        .sidebar ul {
            list-style-type: none;
            padding: 0;
        }

        .sidebar a {
            color: white;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .logo-block {
            width: 100%;
            height: 60px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-top: 20px;
            padding: 10px;
        }

        .logo-block img {
            max-width: 100%;
            height: auto;
            transition: transform 0.3s ease;
        }

        .logo-block:hover img {
            transform: scale(1.05);
        }

        .menu-list {
            display: flex;
            flex-direction: column;
            align-items: center;
            margin-top: 20px;
        }

        .menu-block-item {
            width: 90px;
            height: 78px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 12px;
            margin-top: 8px;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .menu-block-item::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(255, 255, 255, 0.1);
            transform: scaleX(0);
            transition: transform 0.3s ease;
        }

        .menu-block-item__active {
            background: rgba(255, 255, 255, 0.15);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        }

        .menu-block-item__active::before {
            transform: scaleX(1);
        }

        .menu-block-item:hover {
            background: rgba(255, 255, 255, 0.1);
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
        }

        .menu-block-item:hover::before {
            transform: scaleX(1);
        }

        .menu-block-item img {
            transition: transform 0.3s ease;
            filter: brightness(0) invert(1);
        }

        .menu-block-item:hover img {
            transform: scale(1.1);
        }

        .events {
            margin-top: 30px;
        }

        /* Стили для поиска и фильтров */
        .search-container {
            display: flex;
            align-items: center;
        }

        .search-form {
            display: flex;
            align-items: center;
            background: white;
            border: 2px solid #e9ecef;
            border-radius: 25px;
            padding: 4px;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .search-form:focus-within {
            border-color: #133E71;
            box-shadow: 0 0 0 3px rgba(19, 62, 113, 0.1);
        }

        .search-input {
            border: none;
            background: transparent;
            padding: 8px 16px;
            font-size: 14px;
            color: #333;
            outline: none;
            min-width: 200px;
            flex: 1;
        }

        .search-input::placeholder {
            color: #999;
        }

        .search-button {
            background: linear-gradient(135deg, #133E71, #1C5BA4);
            border: none;
            border-radius: 50%;
            width: 36px;
            height: 36px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s ease;
            margin: 0;
            flex-shrink: 0;
        }

        .search-button:hover {
            background: linear-gradient(135deg, #1C5BA4, #133E71);
            transform: scale(1.05);
            box-shadow: 0 4px 15px rgba(19, 62, 113, 0.3);
        }

        .search-button img {
            width: 16px;
            height: 16px;
            filter: brightness(0) invert(1);
        }

        .filter-container {
            margin-left: 15px;
        }

        .filter-button {
            background: linear-gradient(135deg, #133E71, #1C5BA4);
            border: none;
            border-radius: 25px;
            padding: 12px 20px;
            display: flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 14px;
            font-weight: 500;
            color: white;
            box-shadow: 0 2px 10px rgba(19, 62, 113, 0.2);
        }

        .filter-button:hover {
            background: linear-gradient(135deg, #1C5BA4, #133E71);
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(19, 62, 113, 0.3);
        }

        .filter-button img {
            width: 16px;
            height: 16px;
            filter: brightness(0) invert(1);
        }

        .add-org-container {
            background: linear-gradient(135deg, #133E71, #1C5BA4);
            border: none;
            border-radius: 25px;
            padding: 12px 24px;
            display: flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
            color: white;
            font-size: 14px;
            font-weight: 500;
            text-decoration: none;
        }

        .add-org-container:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(19, 62, 113, 0.3);
        }

        .add-org-container img {
            width: 16px;
            height: 16px;
            filter: brightness(0) invert(1);
        }

        /* Адаптивность */
        @media (max-width: 768px) {
            .sidebar {
                width: 80px;
            }

            .main-container {
                margin-left: 80px;
                width: calc(100% - 80px);
            }

            .header {
                padding: 0 20px;
                height: 70px;
            }

            .header-title {
                font-size: 20px;
            }

            .content {
                padding: 20px;
            }

            .menu-block-item {
                width: 70px;
                height: 60px;
            }

            .search-input {
                min-width: 150px;
            }

            .filter-button span {
                display: none;
            }

            .add-org-container span {
                display: none;
            }
        }

        @media (max-width: 480px) {
            .header {
                flex-direction: column;
                height: auto;
                padding: 15px 20px;
                gap: 15px;
            }

            .search-input {
                min-width: 120px;
            }
        }
    </style>
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
                    <div class="menu-block-item {{ request()->is('product*') ? 'menu-block-item__active' : '' }}">
                        <a href="/product"><img src="{{ asset('assets/img/icons/machines.svg') }}" alt="events"></a>
                    </div>
                </li>

                <li>
                    <div class="menu-block-item {{ request()->is('advertisements*') ? 'menu-block-item__active' : '' }}">
                        <a href="/advertisements"><img src="{{ asset('assets/img/icons/adv.svg') }}" alt="events"></a>
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
