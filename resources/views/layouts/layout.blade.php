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
            pointer-events: none;
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

        #addBtn {
            cursor: pointer;
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

            /* Стили для кнопки выхода */
            .header-actions {
                display: flex;
                align-items: center;
                gap: 20px;
            }

            .user-info {
                font-size: 14px;
                color: #666;
                font-weight: 500;
            }

            .logout-btn {
                display: flex;
                align-items: center;
                gap: 8px;
                background: #f8f9fa;
                border: 2px solid #e9ecef;
                border-radius: 8px;
                padding: 8px 16px;
                color: #666;
                font-size: 14px;
                cursor: pointer;
                transition: all 0.3s ease;
                text-decoration: none;
            }

            .logout-btn:hover {
                background: #e9ecef;
                border-color: #dee2e6;
                color: #495057;
            }

            .logout-btn svg {
                width: 16px;
                height: 16px;
            }
        }

        /* Стили для модального окна */
        .modal {
            display: none;
            position: fixed;
            z-index: 2000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(5px);
            align-items: center;
            justify-content: center;
            animation: fadeIn 0.3s ease;
        }
        
        /* Стили для модальных окон справочников */
        .modal.active {
            display: flex;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        .modal-content {
            background: white;
            border-radius: 16px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            max-width: 600px;
            width: 90%;
            max-height: 80vh;
            overflow: hidden;
            animation: slideIn 0.3s ease;
        }

        @keyframes slideIn {
            from { 
                transform: translateY(-50px);
                opacity: 0;
            }
            to { 
                transform: translateY(0);
                opacity: 1;
            }
        }

        .modal-header {
            padding: 25px 30px 20px 30px;
            border-bottom: 1px solid #e9ecef;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .modal-title {
            font-size: 24px;
            color: #133E71;
            margin: 0;
            font-weight: 600;
        }

        .modal-close {
            font-size: 28px;
            color: #666;
            cursor: pointer;
            transition: all 0.3s ease;
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
        }

        .modal-close:hover {
            background: #f8f9fa;
            color: #133E71;
            transform: scale(1.1);
        }

        .modal-body {
            padding: 30px;
        }

        .create-options-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
        }

        .create-option-card {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 20px;
            background: #f8f9fa;
            border: 1px solid #e9ecef;
            border-radius: 12px;
            text-decoration: none;
            color: inherit;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .create-option-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: linear-gradient(90deg, #133E71, #1C5BA4);
            transform: scaleX(0);
            transition: transform 0.3s ease;
        }

        .create-option-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
            border-color: #133E71;
            background: white;
        }

        .create-option-card:hover::before {
            transform: scaleX(1);
        }

        .create-option-icon {
            flex-shrink: 0;
            width: 50px;
            height: 50px;
            background: linear-gradient(135deg, #133E71, #1C5BA4);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            transition: all 0.3s ease;
        }

        .create-option-card:hover .create-option-icon {
            transform: scale(1.1);
            box-shadow: 0 4px 15px rgba(19, 62, 113, 0.3);
        }

        .create-option-content {
            flex: 1;
            min-width: 0;
        }

        .create-option-title {
            font-size: 18px;
            color: #133E71;
            margin: 0 0 5px 0;
            font-weight: 600;
            transition: color 0.3s ease;
        }

        .create-option-card:hover .create-option-title {
            color: #1C5BA4;
        }

        .create-option-description {
            font-size: 13px;
            color: #666;
            margin: 0;
            line-height: 1.4;
        }

        .create-option-arrow {
            flex-shrink: 0;
            width: 28px;
            height: 28px;
            background: #e9ecef;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #666;
            transition: all 0.3s ease;
        }

        .create-option-card:hover .create-option-arrow {
            background: #133E71;
            color: white;
            transform: translateX(2px);
        }

        /* Адаптивность для модального окна */
        @media (max-width: 768px) {
            .modal-content {
                width: 95%;
                margin: 20px;
            }
            
            .modal-header {
                padding: 20px 25px 15px 25px;
            }
            
            .modal-title {
                font-size: 20px;
            }
            
            .modal-body {
                padding: 25px;
            }
            
            .create-options-grid {
                grid-template-columns: 1fr;
                gap: 15px;
            }
            
            .create-option-card {
                padding: 15px;
                gap: 12px;
            }
            
            .create-option-icon {
                width: 45px;
                height: 45px;
            }
            
            .create-option-title {
                font-size: 16px;
            }
            
            .create-option-description {
                font-size: 12px;
            }
        }

        @media (max-width: 480px) {
            .create-option-card {
                flex-direction: column;
                text-align: center;
                gap: 12px;
            }
            
            .create-option-arrow {
                align-self: center;
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
                            <img src="{{ asset('assets/img/icons/plus.svg') }}" alt="plus" class="add-btn" id="addBtn">
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

                @if(Auth::user()->role->name !== 'Региональный представитель')
                <li>
                    <div class="menu-block-item {{ request()->is('advertisements*') ? 'menu-block-item__active' : '' }}">
                        <a href="/advertisements"><img src="{{ asset('assets/img/icons/adv.svg') }}" alt="events"></a>
                    </div>
                </li>
                @endif

                @if(Auth::user()->role->name !== 'Региональный представитель' && Auth::user()->role->name !== 'Менеджер')
                <li>
                    <div class="menu-block-item {{ request()->is('guide*') ? 'menu-block-item__active' : '' }}">
                        <a href="/guide"><img src="{{ asset('assets/img/icons/book.svg') }}" alt="events"></a>
                    </div>
                </li>
                @endif
            </ul>
        </div>
    </div>

    <div class="main-container">
        <div class="header">
            @section('header-title')
                <!-- Содержимое шапки по умолчанию -->
                <h1 class="header-title">Заголовок страницы</h1>
            @show

            @section('header-action-btn')
            <div class="header-actions">
                <div class="user-info">
                    <span>{{ Auth::user()->name ?? 'Пользователь' }}</span>
                </div>
                <form method="POST" action="{{ route('logout') }}" style="display: inline;">
                    @csrf
                    <button type="submit" class="logout-btn">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                            <polyline points="16,17 21,12 16,7"></polyline>
                            <line x1="21" y1="12" x2="9" y2="12"></line>
                        </svg>
                        <span>Выйти</span>
                    </button>
                </form>
            </div>
            @show
        </div>

        <div class="content">
            @yield('content')
        </div>
    </div>

    @stack('scripts')
    
    <!-- Модальное окно для создания -->
    <div id="createModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title">Что требуется создать?</h2>
                <span class="modal-close" id="closeModal">&times;</span>
            </div>
            <div class="modal-body">
                <div class="create-options-grid">
                    @if(Auth::user()->role->name !== 'Региональный представитель')
                    <a href="/company/create" class="create-option-card">
                        <div class="create-option-icon">
                            <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                                <circle cx="9" cy="7" r="4"></circle>
                                <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                                <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                            </svg>
                        </div>
                        <div class="create-option-content">
                            <h3 class="create-option-title">Компанию</h3>
                            <p class="create-option-description">Создать новую компанию</p>
                        </div>
                        <div class="create-option-arrow">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polyline points="9,18 15,12 9,6"></polyline>
                            </svg>
                        </div>
                    </a>
                    @endif

                    <a href="/product/create" class="create-option-card">
                        <div class="create-option-icon">
                            <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path>
                            </svg>
                        </div>
                        <div class="create-option-content">
                            <h3 class="create-option-title">Товар</h3>
                            <p class="create-option-description">Добавить новый товар</p>
                        </div>
                        <div class="create-option-arrow">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polyline points="9,18 15,12 9,6"></polyline>
                            </svg>
                        </div>
                    </a>

                    @if(Auth::user()->role->name !== 'Региональный представитель')
                    <a href="/advertisements/create" class="create-option-card">
                        <div class="create-option-icon">
                            <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                                <polyline points="14,2 14,8 20,8"></polyline>
                                <line x1="16" y1="13" x2="8" y2="13"></line>
                                <line x1="16" y1="17" x2="8" y2="17"></line>
                                <polyline points="10,9 9,9 8,9"></polyline>
                            </svg>
                        </div>
                        <div class="create-option-content">
                            <h3 class="create-option-title">Объявление</h3>
                            <p class="create-option-description">Создать новое объявление</p>
                        </div>
                        <div class="create-option-arrow">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polyline points="9,18 15,12 9,6"></polyline>
                            </svg>
                        </div>
                    </a>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <script>
        // JavaScript для модального окна
        document.addEventListener('DOMContentLoaded', function() {
            const modal = document.getElementById('createModal');
            const addBtn = document.getElementById('addBtn');
            const closeBtn = document.getElementById('closeModal');

            // Открытие модального окна
            addBtn.addEventListener('click', function(e) {
                e.preventDefault();
                modal.style.display = 'flex';
                document.body.style.overflow = 'hidden';
            });

            // Закрытие модального окна при клике на крестик
            closeBtn.addEventListener('click', function() {
                modal.style.display = 'none';
                document.body.style.overflow = 'auto';
            });



            // Закрытие модального окна при нажатии Escape
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape' && modal.style.display === 'flex') {
                    modal.style.display = 'none';
                    document.body.style.overflow = 'auto';
                }
            });
        });
    </script>
</body>
</html>
