@extends('layouts.layout')

@section('title', 'Создание товара')

@section('search-filter')
@endsection

@section('header-action-btn')
@endsection

@section('header-title')
    <h1 class="header-title">Создание товара</h1>
@endsection

@section('content')
<style>
/* Стили для TreeSelect (аналогично PrimeVue TreeSelect) */
.treeselect-wrapper {
    position: relative;
    width: 100%;
}

.treeselect-input {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 12px 16px;
    border: 2px solid #e9ecef;
    border-radius: 8px;
    background: white;
    cursor: pointer;
    transition: all 0.3s ease;
    min-height: 48px;
}

.treeselect-input:hover {
    border-color: #133E71;
}

.treeselect-input:focus {
    outline: none;
    border-color: #133E71;
    box-shadow: 0 0 0 3px rgba(19, 62, 113, 0.1);
}

.treeselect-input.active {
    border-color: #133E71;
    box-shadow: 0 0 0 3px rgba(19, 62, 113, 0.1);
}

.treeselect-placeholder {
    color: #6c757d;
    font-size: 14px;
}

.treeselect-value {
    color: #495057;
    font-size: 14px;
    font-weight: 500;
}

.treeselect-arrow {
    color: #6c757d;
    transition: transform 0.3s ease;
    flex-shrink: 0;
}

.treeselect-input.active .treeselect-arrow {
    transform: rotate(180deg);
}

.treeselect-dropdown {
    position: absolute;
    top: 100%;
    left: 0;
    right: 0;
    z-index: 1000;
    background: white;
    border: 1px solid #dee2e6;
    border-radius: 6px;
    box-shadow: 0 2px 12px rgba(0, 0, 0, 0.1);
    margin-top: 2px;
    display: none;
    max-height: 300px;
    overflow: hidden;
}

.treeselect-dropdown.active {
    display: block;
    animation: treeselectFadeIn 0.15s ease-out;
}

@keyframes treeselectFadeIn {
    from {
        opacity: 0;
        transform: translateY(-8px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.treeselect-search {
    padding: 12px;
    border-bottom: 1px solid #f1f3f4;
}

.treeselect-search-input {
    width: 100%;
    padding: 8px 12px;
    border: 1px solid #dee2e6;
    border-radius: 4px;
    font-size: 14px;
    outline: none;
    transition: border-color 0.3s ease;
}

.treeselect-search-input:focus {
    border-color: #133E71;
}

.treeselect-tree {
    max-height: 200px;
    overflow-y: auto;
    padding: 8px 0;
}

.treeselect-node {
    display: flex;
    align-items: center;
    padding: 8px 12px;
    cursor: pointer;
    transition: background-color 0.15s ease;
    font-size: 14px;
    color: #495057;
    position: relative;
}

.treeselect-node:hover {
    background: #f8f9fa;
}

.treeselect-node.selected {
    background: #e3f2fd;
    color: #133E71;
    font-weight: 500;
}

.treeselect-node.focused {
    background: #e3f2fd;
    color: #133E71;
}

.treeselect-node.disabled {
    opacity: 0.5;
    background: #f8f9fa;
    pointer-events: auto; /* Разрешаем события для разворачивания */
}

.treeselect-node.disabled:hover {
    background: #f8f9fa;
}

.treeselect-node.disabled .treeselect-label {
    color: #6c757d;
    cursor: pointer; /* Курсор pointer для возможности разворачивания */
}

.treeselect-node.disabled .treeselect-toggle {
    cursor: pointer; /* Кнопки разворачивания должны работать */
}

.treeselect-node.disabled .treeselect-toggle:hover {
    color: #133E71; /* Подсветка кнопки при наведении */
}

.treeselect-toggle {
    width: 16px;
    height: 16px;
    margin-right: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: transform 0.2s ease;
    color: #6c757d;
    flex-shrink: 0;
}

.treeselect-toggle.expanded {
    transform: rotate(90deg);
}

.treeselect-toggle-icon {
    width: 12px;
    height: 12px;
}

.treeselect-indent {
    width: 20px;
    flex-shrink: 0;
}

.treeselect-label {
    flex: 1;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.treeselect-children {
    display: none;
    margin-left: 20px;
}

.treeselect-children.expanded {
    display: block;
}

/* Стили для скроллбара в TreeSelect */
.treeselect-tree::-webkit-scrollbar {
    width: 6px;
}

.treeselect-tree::-webkit-scrollbar-track {
    background: #f8f9fa;
    border-radius: 3px;
}

.treeselect-tree::-webkit-scrollbar-thumb {
    background: #dee2e6;
    border-radius: 3px;
}

.treeselect-tree::-webkit-scrollbar-thumb:hover {
    background: #adb5bd;
}

/* Стили для чекбоксов вариантов оплаты */
.payment-checkboxes {
    display: flex;
    flex-direction: column;
    gap: 8px;
    margin-top: 10px;
}

.payment-checkbox {
    display: flex;
    align-items: center;
    gap: 8px;
    cursor: pointer;
    padding: 8px;
    border-radius: 4px;
    transition: background-color 0.3s ease;
}

.payment-checkbox:hover {
    background-color: #f8f9fa;
}

.payment-checkbox input[type="checkbox"] {
    margin: 0;
    cursor: pointer;
}

.payment-checkbox span {
    font-size: 14px;
    color: #495057;
}

/* Стили для оверлея загрузки */
.upload-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.8);
    display: none;
    justify-content: center;
    align-items: center;
    z-index: 10000;
    backdrop-filter: blur(5px);
}

.upload-overlay.active {
    display: flex;
    animation: fadeIn 0.3s ease;
}

@keyframes fadeIn {
    from {
        opacity: 0;
    }
    to {
        opacity: 1;
    }
}

.upload-progress-container {
    background: white;
    border-radius: 12px;
    padding: 40px;
    text-align: center;
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
    max-width: 500px;
    width: 90%;
    max-height: 700px;
    overflow-y: auto;
    animation: slideUp 0.3s ease;
}

@keyframes slideUp {
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.upload-progress-header {
    margin-bottom: 30px;
}

.upload-progress-title {
    font-size: 24px;
    font-weight: 600;
    color: #133E71;
    margin-bottom: 10px;
}

.upload-progress-subtitle {
    font-size: 16px;
    color: #6c757d;
    margin-bottom: 20px;
}

.upload-progress-bar-container {
    background: #f8f9fa;
    border-radius: 10px;
    height: 12px;
    overflow: hidden;
    margin-bottom: 20px;
    border: 1px solid #e9ecef;
}

.upload-progress-bar {
    height: 100%;
    background: linear-gradient(90deg, #133E71, #1C5BA4);
    border-radius: 10px;
    width: 0%;
    transition: width 0.3s ease;
    position: relative;
}

.upload-progress-bar::after {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
    animation: shimmer 2s infinite;
}

@keyframes shimmer {
    0% {
        transform: translateX(-100%);
    }
    100% {
        transform: translateX(100%);
    }
}

.upload-progress-text {
    font-size: 14px;
    color: #495057;
    margin-bottom: 20px;
}

.upload-progress-details {
    background: #f8f9fa;
    border-radius: 8px;
    padding: 15px;
    margin-bottom: 20px;
    border: 1px solid #e9ecef;
    max-height: 300px;
    overflow-y: auto;
}

.upload-progress-file {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 8px 0;
    border-bottom: 1px solid #e9ecef;
    font-size: 14px;
    min-height: 40px;
}

.upload-progress-file:last-child {
    border-bottom: none;
}

.upload-progress-file-name {
    color: #495057;
    flex: 1;
    text-align: left;
    margin-right: 10px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    font-size: 13px;
    line-height: 1.3;
}

.upload-progress-file-status {
    color: #133E71;
    font-weight: 500;
    flex-shrink: 0;
    font-size: 12px;
    padding: 2px 8px;
    border-radius: 12px;
    background: #e3f2fd;
    min-width: 80px;
    text-align: center;
}

.upload-progress-file-status.error {
    color: #dc3545;
    background: #f8d7da;
}

.upload-progress-file-status.success {
    color: #28a745;
    background: #d4edda;
}

.upload-progress-file-status.loading {
    color: #133E71;
    background: #e3f2fd;
    animation: pulse 1.5s infinite;
}

.upload-progress-file-status.pending {
    color: #6c757d;
    background: #f8f9fa;
}

@keyframes pulse {
    0% { opacity: 1; }
    50% { opacity: 0.7; }
    100% { opacity: 1; }
}

/* Адаптивность для мобильных устройств */
@media (max-width: 768px) {
    .upload-progress-container {
        max-width: 95%;
        padding: 20px;
        max-height: 80vh;
    }
    
    .upload-progress-title {
        font-size: 20px;
    }
    
    .upload-progress-subtitle {
        font-size: 14px;
    }
    
    .upload-progress-details {
        max-height: 200px;
    }
    
    .upload-progress-file-name {
        font-size: 12px;
        max-width: 150px;
    }
    
    .upload-progress-file-status {
        font-size: 11px;
        min-width: 70px;
        padding: 1px 6px;
    }
    
    .upload-progress-actions {
        flex-direction: column;
        gap: 10px;
    }
    
    .upload-progress-btn {
        width: 100%;
        padding: 10px 20px;
    }
}

.upload-progress-actions {
    display: flex;
    gap: 15px;
    justify-content: center;
}

.upload-progress-btn {
    padding: 12px 24px;
    border: none;
    border-radius: 6px;
    font-size: 14px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.3s ease;
}

.upload-progress-btn.primary {
    background: #133E71;
    color: white;
}

.upload-progress-btn.primary:hover {
    background: #0f2d56;
    transform: translateY(-1px);
}

.upload-progress-btn.secondary {
    background: #6c757d;
    color: white;
}

.upload-progress-btn.secondary:hover {
    background: #5a6268;
    transform: translateY(-1px);
}

.upload-progress-btn:disabled {
    opacity: 0.6;
    cursor: not-allowed;
    transform: none !important;
}

/* Стили для спиннера */
.upload-spinner {
    display: inline-block;
    width: 20px;
    height: 20px;
    border: 2px solid #f3f3f3;
    border-top: 2px solid #133E71;
    border-radius: 50%;
    animation: spin 1s linear infinite;
    margin-right: 8px;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

/* Стили для скроллбара в контейнере прогрессбара */
.upload-progress-container::-webkit-scrollbar {
    width: 8px;
}

.upload-progress-container::-webkit-scrollbar-track {
    background: #f8f9fa;
    border-radius: 4px;
}

.upload-progress-container::-webkit-scrollbar-thumb {
    background: #dee2e6;
    border-radius: 4px;
}

.upload-progress-container::-webkit-scrollbar-thumb:hover {
    background: #adb5bd;
}

/* Ограничиваем высоту деталей загрузки файлов */
.upload-progress-details {
    background: #f8f9fa;
    border-radius: 8px;
    padding: 15px;
    margin-bottom: 20px;
    border: 1px solid #e9ecef;
    max-height: 300px;
    overflow-y: auto;
}

.upload-progress-details::-webkit-scrollbar {
    width: 6px;
}

.upload-progress-details::-webkit-scrollbar-track {
    background: #f1f3f4;
    border-radius: 3px;
}

.upload-progress-details::-webkit-scrollbar-thumb {
    background: #c1c1c1;
    border-radius: 3px;
}

.upload-progress-details::-webkit-scrollbar-thumb:hover {
    background: #a8a8a8;
}

/* Стили для обязательных полей */
.required {
    color: #dc3545;
    font-weight: bold;
}

.form-group label .required {
    margin-left: 2px;
}

/* Стили для ошибок валидации */
.form-control.error {
    border-color: #dc3545;
    box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25);
}

.error-message {
    color: #dc3545;
    font-size: 0.875rem;
    margin-top: 0.25rem;
    display: none;
}

.error-message.show {
    display: block;
}
</style>

<div class="product-create-container">
    <!-- Индикатор шагов -->
    <div class="steps-indicator">
        <div class="step active" data-step="1">
            <span class="step-number">1</span>
            <span class="step-title">Основное</span>
        </div>
        <div class="step" data-step="2">
            <span class="step-number">2</span>
            <span class="step-title">Характеристики</span>
        </div>
        <div class="step" data-step="3">
            <span class="step-number">3</span>
            <span class="step-title">Проверка</span>
        </div>
        <div class="step" data-step="4">
            <span class="step-number">4</span>
            <span class="step-title">Погрузка</span>
        </div>
        <div class="step" data-step="5">
            <span class="step-number">5</span>
            <span class="step-title">Демонтаж</span>
        </div>
        <div class="step" data-step="6">
            <span class="step-number">6</span>
            <span class="step-title">Оплата</span>
        </div>
        <div class="step" data-step="7">
            <span class="step-number">7</span>
            <span class="step-title">Фото</span>
        </div>
        <div class="step" data-step="8">
            <span class="step-number">8</span>
            <span class="step-title">Комментарий</span>
        </div>
    </div>

    <form id="productForm" method="POST" action="{{ route('products.store') }}" enctype="multipart/form-data">
        @csrf

        <!-- Шаг 1: Основная информация -->
        <div class="step-content active" id="step-1">
            <h2>Основная информация</h2>

            <div class="form-row">
                <div class="form-group">
                    <label for="company_id">
                        Поставщик
                        <span class="tooltip-trigger" data-tooltip="Доступны только компании со статусами 'В работе' и 'Вторая очередь'. Компании со статусами 'Холд' и 'Отказ' недоступны для создания товаров.">
                            <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <circle cx="8" cy="8" r="7" stroke="#133E71" stroke-width="2"/>
                                <path d="M8 6v4M8 4h.01" stroke="#133E71" stroke-width="2"/>
                            </svg>
                        </span>
                    </label>
                    <select name="company_id" id="company_id" class="form-control" required>
                        <option value="">Выберите поставщика</option>
                        @foreach($companies as $company)
                            <option value="{{ $company->id }}" data-sku="{{ $company->sku }}">{{ $company->name }}</option>
                        @endforeach
                    </select>
                    <small class="form-text text-muted">Компании со статусами "Холд" и "Отказ" недоступны для создания товаров</small>
                </div>

                <div class="form-group">
                    <label for="warehouse_display">Склад</label>
                    <input type="text" id="warehouse_display" class="form-control" readonly placeholder="Будет определен автоматически по поставщику">
                    <input type="hidden" name="warehouse_id" id="warehouse_id" value="">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="category_id">
                        Категория
                        <span class="tooltip-trigger" data-tooltip="Можно выбирать только категории без подкатегорий. Недоступные категории отображаются серым цветом.">
                            <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <circle cx="8" cy="8" r="7" stroke="#133E71" stroke-width="2"/>
                                <path d="M8 6v4M8 4h.01" stroke="#133E71" stroke-width="2"/>
                            </svg>
                        </span>
                    </label>
                    <div class="treeselect-wrapper">
                        <div class="treeselect-input" id="category_treeselect" tabindex="0">
                            <span class="treeselect-placeholder">Выберите категорию</span>
                            <svg class="treeselect-arrow" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polyline points="6,9 12,15 18,9"></polyline>
                            </svg>
                        </div>
                        <div class="treeselect-dropdown" id="category_treeselect_dropdown">
                            <div class="treeselect-search">
                                <input type="text" id="category_treeselect_search" placeholder="Поиск категорий..." class="treeselect-search-input">
                            </div>
                            <div class="treeselect-tree" id="category_treeselect_tree">
                                <!-- Дерево будет заполнено JavaScript -->
                            </div>
                        </div>
                        <select name="category_id" id="category_id" class="form-control" required style="display: none;">
                            <option value="">Выберите категорию</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <small class="form-text text-muted">Доступны только категории без подкатегорий</small>
                </div>

                <div class="form-group">
                    <label for="name">Название станка</label>
                    <input type="text" name="name" id="name" class="form-control" required>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="region_display">Регион</label>
                    <input type="text" id="region_display" class="form-control" readonly placeholder="Будет определен автоматически по поставщику">
                    <input type="hidden" name="region" id="region" value="">
                </div>

                <div class="form-group">
                    <label for="sku">Артикул</label>
                    <input type="text" name="sku" id="sku" class="form-control" placeholder="Генерируется автоматически при выборе поставщика (артикул_поставщика-дата_время)">
                </div>
            </div>

            <div class="form-row">
            <div class="form-group">
                <label for="product_address">Адрес станка</label>
                <input type="text" name="product_address" id="product_address" class="form-control" placeholder="Будет подставлен из основного адреса поставщика">
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="state_id">Состояние станка</label>
                <select name="state_id" id="state_id" class="form-control" required>
                    <option value="">Выберите состояние</option>
                    @foreach($states as $state)
                        <option value="{{ $state->id }}">{{ $state->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label for="available_id">Доступность</label>
                <select name="available_id" id="available_id" class="form-control" required>
                    <option value="">Выберите доступность</option>
                    @foreach($availables as $available)
                        <option value="{{ $available->id }}">{{ $available->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="step-actions">
            <button type="button" class="btn btn-primary next-step">Следующий шаг</button>
        </div>
        </div>

        <!-- Шаг 2: Характеристики -->
        <div class="step-content" id="step-2">
            <h2>Характеристики</h2>

            <div class="form-group">
                <label for="main_chars">
                    Основные характеристики
                    <span class="tooltip-trigger" data-tooltip="Пример: токарный 16К20, Рязанец, РМЦ 1500">
                        <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <circle cx="8" cy="8" r="7" stroke="#133E71" stroke-width="2"/>
                            <path d="M8 6v4M8 4h.01" stroke="#133E71" stroke-width="2"/>
                        </svg>
                    </span>
                </label>
                <textarea name="main_chars" id="main_chars" class="form-control" rows="4"></textarea>
            </div>

            <div class="form-group">
                <label for="mark">
                    Оценка
                    <span class="tooltip-trigger" data-tooltip="Пример: В отличном состоянии, с проверкой в работе. Направляющие без износа, электрика на пускателях.">
                        <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <circle cx="8" cy="8" r="7" stroke="#133E71" stroke-width="2"/>
                            <path d="M8 6v4M8 4h.01" stroke="#133E71" stroke-width="2"/>
                        </svg>
                    </span>
                </label>
                <textarea name="mark" id="mark" class="form-control" rows="4"></textarea>
            </div>

            <div class="form-group">
                <label for="complectation">
                    Комплектация
                    <span class="tooltip-trigger" data-tooltip="Пример: Комплектный, но без станции СОЖ">
                        <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <circle cx="8" cy="8" r="7" stroke="#133E71" stroke-width="2"/>
                            <path d="M8 6v4M8 4h.01" stroke="#133E71" stroke-width="2"/>
                        </svg>
                    </span>
                </label>
                <textarea name="complectation" id="complectation" class="form-control" rows="4"></textarea>
            </div>

            <div class="step-actions">
                <button type="button" class="btn btn-secondary prev-step">Предыдущий шаг</button>
                <button type="button" class="btn btn-primary next-step">Следующий шаг</button>
            </div>
        </div>

        <!-- Шаг 3: Проверка -->
        <div class="step-content" id="step-3">
            <h2>Проверка</h2>

            <div class="form-group">
                <label for="check_status_id">Статус проверки</label>
                <select name="check_status_id" id="check_status_id" class="form-control">
                    <option value="">Выберите статус проверки</option>
                    @foreach($checkStatuses as $status)
                        <option value="{{ $status->id }}">{{ $status->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label for="check_comment">
                    Комментарий к проверке
                    <span class="tooltip-trigger" data-tooltip="Пример: Перемещен на склад, может быть подключен своими силами. Подключен, стоит на своем рабочем месте.">
                        <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <circle cx="8" cy="8" r="7" stroke="#133E71" stroke-width="2"/>
                            <path d="M8 6v4M8 4h.01" stroke="#133E71" stroke-width="2"/>
                        </svg>
                    </span>
                </label>
                <textarea name="check_comment" id="check_comment" class="form-control" rows="4"></textarea>
            </div>

            <div class="step-actions">
                <button type="button" class="btn btn-secondary prev-step">Предыдущий шаг</button>
                <button type="button" class="btn btn-primary next-step">Следующий шаг</button>
            </div>
        </div>

        <!-- Шаг 4: Погрузка -->
        <div class="step-content" id="step-4">
            <h2>Погрузка</h2>

            <div class="form-group">
                <label for="loading_status_id">Статус погрузки</label>
                <select name="loading_status_id" id="loading_status_id" class="form-control">
                    <option value="">Выберите статус погрузки</option>
                    @foreach($installStatuses as $status)
                        <option value="{{ $status->id }}">{{ $status->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label for="loading_comment">
                    Комментарий
                    <span class="tooltip-trigger" data-tooltip="Поставщиком за доп плату, а арендаторов есть погрузчик, цену согласовывем отдельно. Поставщик погрузит, есть кран балка. Клиентом, потребуется кран, проезд в цех возможен">
                        <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <circle cx="8" cy="8" r="7" stroke="#133E71" stroke-width="2"/>
                            <path d="M8 6v4M8 4h.01" stroke="#133E71" stroke-width="2"/>
                        </svg>
                    </span>
                </label>
                <textarea name="loading_comment" id="loading_comment" class="form-control" rows="4"></textarea>
            </div>

            <div class="step-actions">
                <button type="button" class="btn btn-secondary prev-step">Предыдущий шаг</button>
                <button type="button" class="btn btn-primary next-step">Следующий шаг</button>
            </div>
        </div>

        <!-- Шаг 5: Демонтаж -->
        <div class="step-content" id="step-5">
            <h2>Демонтаж</h2>

            <div class="form-group">
                <label for="removal_status_id">Статус демонтажа</label>
                <select name="removal_status_id" id="removal_status_id" class="form-control">
                    <option value="">Выберите статус демонтажа</option>
                    @foreach($installStatuses as $status)
                        <option value="{{ $status->id }}">{{ $status->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label for="removal_comment">
                    Комментарий
                    <span class="tooltip-trigger" data-tooltip="Пример: Стоит на бетонном основании, потребуется отбить основание. Сложный демонтаж, потребуется разбирать на несколько частей, так как доступ в цех через маленькие ворота.">
                        <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <circle cx="8" cy="8" r="7" stroke="#133E71" stroke-width="2"/>
                            <path d="M8 6v4M8 4h.01" stroke="#133E71" stroke-width="2"/>
                        </svg>
                    </span>
                </label>
                <textarea name="removal_comment" id="removal_comment" class="form-control" rows="4"></textarea>
            </div>

            <div class="step-actions">
                <button type="button" class="btn btn-secondary prev-step">Предыдущий шаг</button>
                <button type="button" class="btn btn-primary next-step">Следующий шаг</button>
            </div>
        </div>

        <!-- Шаг 6: Оплата -->
        <div class="step-content" id="step-6">
            <h2>Оплата</h2>

            <div class="form-group">
                <label for="payment_types">Варианты оплаты</label>
                <div class="payment-checkboxes">
                    @foreach($priceTypes as $priceType)
                        <label class="payment-checkbox">
                            <input type="checkbox" name="payment_types[]" value="{{ $priceType->id }}">
                            <span>{{ $priceType->name }}</span>
                        </label>
                    @endforeach
                </div>
            </div>

            <div class="form-group">
                <label for="main_payment_method">Основной способ оплаты <span class="required">*</span></label>
                <select name="main_payment_method" id="main_payment_method" class="form-control" required>
                    <option value="">Выберите основной способ оплаты</option>
                    @foreach($priceTypes as $priceType)
                        <option value="{{ $priceType->id }}">{{ $priceType->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label for="purchase_price">Цена покупки (руб.)</label>
                <input type="number" name="purchase_price" id="purchase_price" class="form-control" step="0.01" min="0">
            </div>

            <div class="form-group">
                <label for="payment_comment">
                    Комментарий <span class="required">*</span>
                    <span class="tooltip-trigger" data-tooltip="Пример: Поставщик продает за НАЛ, устно подтвердил любую форму безнала, условия необходимо проговорить дополнительно.">
                        <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <circle cx="8" cy="8" r="7" stroke="#133E71" stroke-width="2"/>
                            <path d="M8 6v4M8 4h.01" stroke="#133E71" stroke-width="2"/>
                        </svg>
                    </span>
                </label>
                <textarea name="payment_comment" id="payment_comment" class="form-control" rows="4" required></textarea>
            </div>

            <div class="step-actions">
                <button type="button" class="btn btn-secondary prev-step">Предыдущий шаг</button>
                <button type="button" class="btn btn-primary next-step">Следующий шаг</button>
            </div>
        </div>

        <!-- Шаг 7: Фото -->
        <div class="step-content" id="step-7">
            <h2>Фото и видео</h2>

            <div class="form-group">
                <label for="media_files">Загрузка фото и видео</label>
                <div class="file-upload-container">
                    <input type="file" name="media_files[]" id="media_files" class="file-input" multiple accept="image/*,video/*">
                    <div class="file-upload-area" id="fileUploadArea">
                        <div class="upload-icon">
                            <svg width="48" height="48" viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M24 16V32M16 24H32" stroke="#133E71" stroke-width="2" stroke-linecap="round"/>
                                <rect x="4" y="4" width="40" height="40" rx="8" stroke="#133E71" stroke-width="2" stroke-dasharray="8 8"/>
                            </svg>
                        </div>
                        <p class="upload-text">Нажмите или перетащите файлы сюда</p>
                        <p class="upload-hint">Поддерживаются изображения (JPG, PNG, GIF) и видео (MP4, MOV, AVI). Максимальный размер файла: 1000MB, общий размер всех файлов: 1000MB</p>
                    </div>
                </div>
                <div class="file-preview" id="filePreview"></div>
            </div>

            <div class="step-actions">
                <button type="button" class="btn btn-secondary prev-step">Предыдущий шаг</button>
                <button type="button" class="btn btn-primary next-step">Следующий шаг</button>
            </div>
        </div>

        <!-- Шаг 8: Общий комментарий -->
        <div class="step-content" id="step-8">
            <h2>Общий комментарий</h2>

            <div class="form-group">
                <label for="common_commentary_after">
                    Общий комментарий после осмотра
                    <span class="tooltip-trigger" data-tooltip="Пример: Станок в хорошем состоянии, имеет минимальный износ, недавно прошел капитальный ремонт.">
                        <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <circle cx="8" cy="8" r="7" stroke="#133E71" stroke-width="2"/>
                            <path d="M8 6v4M8 4h.01" stroke="#133E71" stroke-width="2"/>
                        </svg>
                    </span>
                </label>
                <textarea name="common_commentary_after" id="common_commentary_after" class="form-control" rows="6" placeholder="Введите общий комментарий после осмотра товара..."></textarea>
                <small class="form-text text-muted">Дополнительная информация о товаре после осмотра</small>
            </div>

            <div class="step-actions">
                <button type="button" class="btn btn-secondary prev-step">Предыдущий шаг</button>
                <button type="submit" class="btn btn-success">Создать товар</button>
            </div>
        </div>
    </form>
</div>

<!-- Tooltip -->
<div class="tooltip" id="tooltip"></div>

<!-- Оверлей загрузки файлов -->
<div class="upload-overlay" id="uploadOverlay">
    <div class="upload-progress-container">
        <div class="upload-progress-header">
            <h3 class="upload-progress-title">
                <span class="upload-spinner" id="uploadSpinner"></span>
                Загрузка товара
            </h3>
            <p class="upload-progress-subtitle">Пожалуйста, дождитесь завершения загрузки файлов</p>
        </div>
        
        <div class="upload-progress-bar-container">
            <div class="upload-progress-bar" id="uploadProgressBar"></div>
        </div>
        
        <div class="upload-progress-text" id="uploadProgressText">
            Подготовка к загрузке...
        </div>
        
        <div class="upload-progress-details" id="uploadProgressDetails" style="display: none;">
            <div class="upload-progress-file">
                <span class="upload-progress-file-name">Файл 1</span>
                <span class="upload-progress-file-status">Загружается...</span>
            </div>
        </div>
        
        <div class="upload-progress-actions" id="uploadProgressActions" style="display: none;">
            <button class="upload-progress-btn secondary" id="cancelUploadBtn">Отмена</button>
            <button class="upload-progress-btn primary" id="continueBtn" style="display: none;">Продолжить</button>
        </div>
    </div>
</div>

<script>
// Данные для TreeSelect и MultiSelect
const categoriesData = @json($categories);

// Переменные для загрузки файлов
let uploadInProgress = false;
let uploadCancelled = false;
let createdProductUrl = null; // URL созданного товара

// Функции для работы с прогрессбаром загрузки
function showUploadProgress() {
    const overlay = document.getElementById('uploadOverlay');
    const progressBar = document.getElementById('uploadProgressBar');
    const progressText = document.getElementById('uploadProgressText');
    const progressDetails = document.getElementById('uploadProgressDetails');
    const progressActions = document.getElementById('uploadProgressActions');
    
    overlay.classList.add('active');
    progressBar.style.width = '0%';
    progressText.textContent = 'Подготовка к загрузке...';
    progressDetails.style.display = 'none';
    progressActions.style.display = 'none';
    
    uploadInProgress = true;
    uploadCancelled = false;
    
    // Блокируем прокрутку страницы
    document.body.style.overflow = 'hidden';
}

function updateUploadProgress(percent, text) {
    const progressBar = document.getElementById('uploadProgressBar');
    const progressText = document.getElementById('uploadProgressText');
    
    progressBar.style.width = percent + '%';
    progressText.textContent = text;
}

function updateFileProgress(files) {
    const progressDetails = document.getElementById('uploadProgressDetails');
    const progressActions = document.getElementById('uploadProgressActions');
    
    progressDetails.style.display = 'block';
    progressActions.style.display = 'flex';
    
    let html = '';
    let completedFiles = 0;
    let totalFiles = files.length;
    
    files.forEach((file, index) => {
        if (file.status === 'success') {
            completedFiles++;
        }
        
        let statusClass = file.status;
        let statusText = file.statusText;
        
        // Улучшенные статусы
        switch(file.status) {
            case 'success':
                statusClass = 'success';
                statusText = '✓ Загружен';
                break;
            case 'loading':
                statusClass = 'loading';
                statusText = '⏳ Загружается...';
                break;
            case 'error':
                statusClass = 'error';
                statusText = '✗ Ошибка';
                break;
            default:
                statusClass = 'pending';
                statusText = '⏸ Ожидает';
        }
        
        html += `
            <div class="upload-progress-file">
                <span class="upload-progress-file-name" title="${file.fullName}">${file.name}</span>
                <span class="upload-progress-file-status ${statusClass}">${statusText}</span>
            </div>
        `;
    });
    
    // Добавляем заголовок с информацией о прогрессе
    const progressHeader = `
        <div class="upload-progress-file" style="background: #e3f2fd; margin: -15px -15px 10px -15px; padding: 10px 15px; border-radius: 8px 8px 0 0; font-weight: 600; color: #133E71;">
            <span>Файлы: ${completedFiles}/${totalFiles} загружено</span>
            <span>${Math.round((completedFiles / totalFiles) * 100)}%</span>
        </div>
    `;
    
    progressDetails.innerHTML = progressHeader + html;
}

function hideUploadProgress() {
    const overlay = document.getElementById('uploadOverlay');
    overlay.classList.remove('active');
    uploadInProgress = false;
    
    // Восстанавливаем прокрутку страницы
    document.body.style.overflow = 'auto';
}

function showUploadSuccess() {
    const progressText = document.getElementById('uploadProgressText');
    const continueBtn = document.getElementById('continueBtn');
    const cancelBtn = document.getElementById('cancelUploadBtn');
    
    progressText.textContent = 'Загрузка завершена успешно!';
    continueBtn.style.display = 'inline-block';
    cancelBtn.textContent = 'Закрыть';
    
    // Сохраняем URL для перенаправления
    if (createdProductUrl) {
        continueBtn.onclick = function() {
            hideUploadProgress();
            window.location.href = createdProductUrl;
        };
            } else {
            continueBtn.onclick = function() {
                hideUploadProgress();
                console.log('Кнопка продолжения: createdProductUrl =', createdProductUrl);
                // Если есть URL созданного товара, перенаправляем на него
                if (createdProductUrl) {
                    console.log('Кнопка продолжения: редирект на createdProductUrl:', createdProductUrl);
                    window.location.href = createdProductUrl;
                } else {
                    // Если нет файлов, перенаправляем на список товаров
                    console.log('Кнопка продолжения: редирект на fallback /product');
                    window.location.href = '/product';
                }
            };
        }
    
    // Автоматически скрываем через 3 секунды
    setTimeout(() => {
        if (!uploadCancelled) {
            hideUploadProgress();
        }
    }, 3000);
}

function showUploadError(message) {
    const progressText = document.getElementById('uploadProgressText');
    const continueBtn = document.getElementById('continueBtn');
    const cancelBtn = document.getElementById('cancelUploadBtn');
    
    progressText.textContent = 'Ошибка загрузки: ' + message;
    continueBtn.style.display = 'none';
    cancelBtn.textContent = 'Закрыть';
}

// Обработчики событий для кнопок прогрессбара
document.addEventListener('DOMContentLoaded', function() {
    const cancelUploadBtn = document.getElementById('cancelUploadBtn');
    const continueBtn = document.getElementById('continueBtn');
    
    if (cancelUploadBtn) {
        cancelUploadBtn.addEventListener('click', function() {
            if (uploadInProgress) {
                uploadCancelled = true;
                // Отменяем текущую загрузку
                if (window.currentUploadXHR) {
                    window.currentUploadXHR.abort();
                }
                hideUploadProgress();
            } else {
                hideUploadProgress();
            }
        });
    }
    
    if (continueBtn) {
        continueBtn.addEventListener('click', function() {
            hideUploadProgress();
            console.log('Обработчик событий: createdProductUrl =', createdProductUrl);
            // Если есть URL созданного товара, перенаправляем на него
            if (createdProductUrl) {
                console.log('Обработчик событий: редирект на createdProductUrl:', createdProductUrl);
                window.location.href = createdProductUrl;
            } else {
                // Если нет файлов, перенаправляем на список товаров
                console.log('Обработчик событий: редирект на fallback /product');
                window.location.href = '/product';
            }
        });
    }
    
    // Обработка клавиши Escape для закрытия оверлея
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && uploadInProgress) {
            uploadCancelled = true;
            if (window.currentUploadXHR) {
                window.currentUploadXHR.abort();
            }
            hideUploadProgress();
        }
    });
});

document.addEventListener('DOMContentLoaded', function() {
    const steps = document.querySelectorAll('.step');
    const stepContents = document.querySelectorAll('.step-content');
    const nextButtons = document.querySelectorAll('.next-step');
    const prevButtons = document.querySelectorAll('.prev-step');
    let currentStep = 1;

    function showStep(stepNumber) {
        // Обновляем индикатор шагов
        steps.forEach(step => {
            step.classList.remove('active', 'completed');
            if (parseInt(step.dataset.step) < stepNumber) {
                step.classList.add('completed');
            } else if (parseInt(step.dataset.step) === stepNumber) {
                step.classList.add('active');
            }
        });

        // Показываем контент шага
        stepContents.forEach(content => {
            content.classList.remove('active');
        });
        document.getElementById(`step-${stepNumber}`).classList.add('active');

        currentStep = stepNumber;
    }

    function validateStep(stepNumber) {
        const stepElement = document.getElementById(`step-${stepNumber}`);
        const requiredFields = stepElement.querySelectorAll('[required]');

        // Очищаем предыдущие ошибки
        clearValidationErrors(stepElement);

        // Специальная валидация для шага 6 (Оплата)
        if (stepNumber === 6) {
            return validateStep6(stepElement);
        }

        // Стандартная валидация для остальных шагов
        for (let field of requiredFields) {
            if (!field.value.trim()) {
                showFieldError(field, 'Это поле обязательно для заполнения');
                field.focus();
                return false;
            }
        }
        return true;
    }

    function validateStep6(stepElement) {
        let isValid = true;

        // Проверяем основной способ оплаты
        const mainPaymentMethod = stepElement.querySelector('#main_payment_method');
        if (!mainPaymentMethod.value.trim()) {
            showFieldError(mainPaymentMethod, 'Выберите основной способ оплаты');
            isValid = false;
        }

        // Проверяем комментарий
        const paymentComment = stepElement.querySelector('#payment_comment');
        if (!paymentComment.value.trim()) {
            showFieldError(paymentComment, 'Комментарий обязателен для заполнения');
            isValid = false;
        }

        // Проверяем, что выбран хотя бы один вариант оплаты
        const paymentTypes = stepElement.querySelectorAll('input[name="payment_types[]"]:checked');
        if (paymentTypes.length === 0) {
            const paymentTypesContainer = stepElement.querySelector('.payment-checkboxes');
            showContainerError(paymentTypesContainer, 'Выберите хотя бы один вариант оплаты');
            isValid = false;
        }

        if (!isValid) {
            // Фокусируемся на первом поле с ошибкой
            const firstErrorField = stepElement.querySelector('.form-control.error');
            if (firstErrorField) {
                firstErrorField.focus();
            }
        }

        return isValid;
    }

    function showFieldError(field, message) {
        field.classList.add('error');
        
        // Создаем или обновляем сообщение об ошибке
        let errorMessage = field.parentNode.querySelector('.error-message');
        if (!errorMessage) {
            errorMessage = document.createElement('div');
            errorMessage.className = 'error-message';
            field.parentNode.appendChild(errorMessage);
        }
        errorMessage.textContent = message;
        errorMessage.classList.add('show');
    }

    function showContainerError(container, message) {
        // Создаем или обновляем сообщение об ошибке для контейнера
        let errorMessage = container.parentNode.querySelector('.error-message');
        if (!errorMessage) {
            errorMessage = document.createElement('div');
            errorMessage.className = 'error-message';
            container.parentNode.appendChild(errorMessage);
        }
        errorMessage.textContent = message;
        errorMessage.classList.add('show');
    }

    function clearValidationErrors(stepElement) {
        // Убираем классы ошибок
        const errorFields = stepElement.querySelectorAll('.form-control.error');
        errorFields.forEach(field => field.classList.remove('error'));

        // Скрываем сообщения об ошибках
        const errorMessages = stepElement.querySelectorAll('.error-message');
        errorMessages.forEach(message => {
            message.classList.remove('show');
            message.textContent = '';
        });
    }

    nextButtons.forEach(button => {
        button.addEventListener('click', function() {
            if (validateStep(currentStep)) {
                if (currentStep < 8) {
                    showStep(currentStep + 1);
                }
            }
        });
    });

    prevButtons.forEach(button => {
        button.addEventListener('click', function() {
            if (currentStep > 1) {
                showStep(currentStep - 1);
            }
        });
    });

    // Обработка подсказок
    const tooltipTriggers = document.querySelectorAll('.tooltip-trigger');
    const tooltip = document.getElementById('tooltip');

    tooltipTriggers.forEach(trigger => {
        trigger.addEventListener('mouseenter', function(e) {
            const text = this.getAttribute('data-tooltip');
            tooltip.textContent = text;
            tooltip.style.display = 'block';

            const rect = this.getBoundingClientRect();
            tooltip.style.left = rect.left + 'px';
            tooltip.style.top = (rect.top - tooltip.offsetHeight - 5) + 'px';
        });

        trigger.addEventListener('mouseleave', function() {
            tooltip.style.display = 'none';
        });
    });

    // Обработка загрузки файлов
    const fileInput = document.getElementById('media_files');
    const fileUploadArea = document.getElementById('fileUploadArea');
    const filePreview = document.getElementById('filePreview');
    let selectedFiles = [];

    // Обработка клика по области загрузки
    fileUploadArea.addEventListener('click', function() {
        fileInput.click();
    });

    // Обработка выбора файлов
    fileInput.addEventListener('change', function(e) {
        handleFiles(e.target.files);
    });

    // Drag & Drop обработка
    fileUploadArea.addEventListener('dragover', function(e) {
        e.preventDefault();
        fileUploadArea.classList.add('dragover');
    });

    fileUploadArea.addEventListener('dragleave', function(e) {
        e.preventDefault();
        fileUploadArea.classList.remove('dragover');
    });

    fileUploadArea.addEventListener('drop', function(e) {
        e.preventDefault();
        fileUploadArea.classList.remove('dragover');
        handleFiles(e.dataTransfer.files);
    });

    function handleFiles(files) {
        // Проверяем общий размер файлов
        if (!validateTotalFileSize(files)) {
            return;
        }
        
        Array.from(files).forEach(file => {
            if (validateFile(file)) {
                selectedFiles.push(file);
                addFilePreview(file);
            }
        });
        updateFileInput();
    }

    function validateFile(file) {
        const maxSize = 1000 * 1024 * 1024; // 1000MB
        const allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'video/mp4', 'video/quicktime', 'video/x-msvideo'];
        
        if (file.size > maxSize) {
            alert('Файл слишком большой. Максимальный размер: 1000MB');
            return false;
        }
        
        if (!allowedTypes.includes(file.type)) {
            alert('Неподдерживаемый формат файла');
            return false;
        }
        
        return true;
    }

    function validateTotalFileSize(files) {
        const maxTotalSize = 1000 * 1024 * 1024; // 1000MB
        let totalSize = 0;
        
        Array.from(files).forEach(file => {
            totalSize += file.size;
        });
        
        if (totalSize > maxTotalSize) {
            alert(`Общий размер файлов (${formatFileSize(totalSize)}) превышает лимит в 1000MB. Пожалуйста, загрузите файлы по частям.`);
            return false;
        }
        
        return true;
    }

    function addFilePreview(file) {
        const previewItem = document.createElement('div');
        previewItem.className = 'file-preview-item';
        
        // Создаем контейнер для информации о файле
        const fileInfo = document.createElement('div');
        fileInfo.className = 'file-info';
        
        // Добавляем бейдж типа файла
        const typeBadge = document.createElement('span');
        typeBadge.className = 'file-type-badge';
        if (file.type.startsWith('image/')) {
            typeBadge.className += ' image';
            typeBadge.textContent = 'Изображение';
        } else if (file.type.startsWith('video/')) {
            typeBadge.className += ' video';
            typeBadge.textContent = 'Видео';
        } else {
            typeBadge.textContent = 'Файл';
        }
        
        const fileName = document.createElement('span');
        fileName.className = 'file-name';
        fileName.textContent = file.name;
        
        // Добавляем размер файла
        const fileSize = document.createElement('span');
        fileSize.className = 'file-size';
        fileSize.textContent = formatFileSize(file.size);
        
        const removeBtn = document.createElement('button');
        removeBtn.type = 'button';
        removeBtn.className = 'remove-file';
        removeBtn.innerHTML = '×';
        removeBtn.addEventListener('click', function() {
            removeFile(file, previewItem);
        });
        
        // Предварительный просмотр для изображений
        if (file.type.startsWith('image/')) {
            const img = document.createElement('img');
            img.className = 'file-preview-image';
            const reader = new FileReader();
            reader.onload = function(e) {
                img.src = e.target.result;
            };
            reader.readAsDataURL(file);
            previewItem.appendChild(img);
        }
        
        fileInfo.appendChild(typeBadge);
        fileInfo.appendChild(fileName);
        fileInfo.appendChild(fileSize);
        
        previewItem.appendChild(fileInfo);
        previewItem.appendChild(removeBtn);
        
        filePreview.appendChild(previewItem);
    }

    function removeFile(file, previewItem) {
        const index = selectedFiles.indexOf(file);
        if (index > -1) {
            selectedFiles.splice(index, 1);
        }
        previewItem.remove();
        updateFileInput();
    }

    function updateFileInput() {
        const dt = new DataTransfer();
        selectedFiles.forEach(file => {
            dt.items.add(file);
        });
        fileInput.files = dt.files;
    }

    function formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }

    // Инициализация
    showStep(1);
    
    // Инициализация TreeSelect для категорий
    initializeTreeSelect('category_treeselect', 'category_id', categoriesData);
    
    // Обработка редиректа после создания товара без файлов
    const urlParams = new URLSearchParams(window.location.search);
    const createdProductId = urlParams.get('created_product_id');
    if (createdProductId) {
        // Если есть ID созданного товара в URL, перенаправляем на него
        const productUrl = `/product/${createdProductId}`;
        window.location.href = productUrl;
    }
    
    // Автоматическая генерация артикула и загрузка информации при выборе поставщика
    const companySelect = document.getElementById('company_id');
    const skuInput = document.getElementById('sku');
    const warehouseDisplay = document.getElementById('warehouse_display');
    const warehouseHidden = document.getElementById('warehouse_id');
    const regionDisplay = document.getElementById('region_display');
    const regionHidden = document.getElementById('region');
    const productAddressInput = document.getElementById('product_address');

    if (companySelect) {
        companySelect.addEventListener('change', function() {
            const selectedCompanyId = this.value;
            
            // Очищаем поля при смене компании
            if (skuInput) skuInput.value = '';
            if (warehouseDisplay) warehouseDisplay.value = '';
            if (warehouseHidden) warehouseHidden.value = '';
            if (regionDisplay) regionDisplay.value = '';
            if (regionHidden) regionHidden.value = '';
            if (productAddressInput) productAddressInput.value = '';
            
            if (selectedCompanyId) {
                // Загружаем информацию о компании
                fetch(`/company/${selectedCompanyId}/info`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success && data.company) {
                            const company = data.company;
                            
                            // Заполняем склад
                            if (warehouseDisplay && company.warehouse) {
                                warehouseDisplay.value = company.warehouse.name;
                            }
                            if (warehouseHidden && company.warehouse) {
                                warehouseHidden.value = company.warehouse.id;
                            }
                            
                            // Заполняем регион
                            if (regionDisplay && company.region) {
                                regionDisplay.value = company.region.name;
                            }
                            if (regionHidden && company.region) {
                                regionHidden.value = company.region.id;
                            }
                            
                            // Заполняем адрес станка
                            if (productAddressInput && company.main_address) {
                                productAddressInput.value = company.main_address;
                            }
                            
                            // Генерируем артикул
                            if (skuInput) {
                                const supplierSku = company.sku || '000';
                                const date = new Date();
                                const dayMonth = date.getDate().toString().padStart(2, '0') + 
                                                (date.getMonth() + 1).toString().padStart(2, '0');
                                const year = date.getFullYear().toString();
                                const time = date.getHours().toString().padStart(2, '0') + 
                                            date.getMinutes().toString().padStart(2, '0');
                                
                                const generatedSku = supplierSku + '-' + dayMonth + year + '-' + time;
                                skuInput.value = generatedSku;
                            }
                        } else {
                            console.error('Ошибка при получении информации о компании:', data.message);
                        }
                    })
                    .catch(error => {
                        console.error('Ошибка при загрузке информации о компании:', error);
                    });
            }
        });

        // Обрабатываем случай, когда пользователь очищает поле артикула
        if (skuInput) {
            skuInput.addEventListener('blur', function() {
                if (!this.value.trim() && companySelect.value) {
                    const selectedOption = companySelect.options[companySelect.selectedIndex];
                    const supplierSku = selectedOption.getAttribute('data-sku') || '000';
                    const date = new Date();
                    const dayMonth = date.getDate().toString().padStart(2, '0') + 
                                    (date.getMonth() + 1).toString().padStart(2, '0');
                    const year = date.getFullYear().toString();
                    const time = date.getHours().toString().padStart(2, '0') + 
                                date.getMinutes().toString().padStart(2, '0');
                    
                    const generatedSku = supplierSku + '-' + dayMonth + year + '-' + time;
                    skuInput.value = generatedSku;
                }
            });
        }
    }

    // Обработка отправки формы с прогрессбаром
    const productForm = document.getElementById('productForm');
    if (productForm) {
        productForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Проверяем, есть ли файлы для загрузки
            const fileInput = document.getElementById('media_files');
            const hasFiles = fileInput && fileInput.files && fileInput.files.length > 0;
            
            if (hasFiles) {
                // Показываем прогрессбар и начинаем загрузку
                showUploadProgress();
                uploadFormWithProgress(this);
            } else {
                // Если файлов нет, отправляем форму обычным способом
                // Но сначала показываем индикатор загрузки
                showUploadProgress();
                updateUploadProgress(50, 'Создание товара...');
                
                // Имитируем прогресс для создания товара
                setTimeout(() => {
                    updateUploadProgress(100, 'Товар создан! Перенаправление...');
                    showUploadSuccess();
                    
                    // Отправляем форму обычным способом
                    this.submit();
                }, 1000);
            }
        });
    }
});

// Функция для загрузки формы с прогрессбаром
function uploadFormWithProgress(form) {
    const formData = new FormData(form);
    const fileInput = document.getElementById('media_files');
    const files = Array.from(fileInput.files);
    
    // Подготавливаем данные о файлах для отображения прогресса
    const fileProgressData = files.map(file => ({
        name: file.name.length > 30 ? file.name.substring(0, 27) + '...' : file.name,
        fullName: file.name,
        status: 'pending',
        statusText: 'Ожидает загрузки',
        size: file.size
    }));
    
    updateFileProgress(fileProgressData);
    
    // Создаем XMLHttpRequest для отслеживания прогресса
    const xhr = new XMLHttpRequest();
    
    xhr.upload.addEventListener('progress', function(e) {
        if (e.lengthComputable) {
            const percentComplete = Math.round((e.loaded / e.total) * 100);
            updateUploadProgress(percentComplete, `Загрузка файлов: ${percentComplete}%`);
            
            // Более точное обновление статуса файлов
            const totalSize = e.total;
            const loadedSize = e.loaded;
            const filesPerPercent = files.length / 100;
            const currentFileIndex = Math.floor(percentComplete * filesPerPercent);
            
            fileProgressData.forEach((file, index) => {
                if (index < currentFileIndex) {
                    file.status = 'success';
                    file.statusText = '✓ Загружен';
                } else if (index === currentFileIndex) {
                    file.status = 'loading';
                    file.statusText = '⏳ Загружается...';
                } else {
                    file.status = 'pending';
                    file.statusText = '⏸ Ожидает';
                }
            });
            
            updateFileProgress(fileProgressData);
        }
    });
    
    xhr.addEventListener('load', function() {
        console.log('XHR загрузка завершена. Статус:', xhr.status);
        console.log('XHR ответ:', xhr.responseText);
        if (xhr.status === 200) {
            try {
                const response = JSON.parse(xhr.responseText);
                if (response.success) {
                    // Помечаем все файлы как успешно загруженные
                    fileProgressData.forEach(file => {
                        file.status = 'success';
                        file.statusText = '✓ Загружен';
                    });
                    updateFileProgress(fileProgressData);
                    
                    // Сохраняем URL созданного товара
                    if (response.redirect) {
                        createdProductUrl = response.redirect;
                        console.log('Создан товар, URL для редиректа:', createdProductUrl);
                    } else {
                        console.log('Ответ сервера не содержит redirect:', response);
                    }
                    
                    showUploadSuccess();
                    // Перенаправляем на страницу созданного товара
                    setTimeout(() => {
                        console.log('Попытка редиректа. response.redirect:', response.redirect, 'createdProductUrl:', createdProductUrl);
                        if (response.redirect) {
                            console.log('Редирект на response.redirect:', response.redirect);
                            window.location.href = response.redirect;
                        } else if (createdProductUrl) {
                            console.log('Редирект на createdProductUrl:', createdProductUrl);
                            window.location.href = createdProductUrl;
                        } else {
                            console.log('Редирект на fallback /product');
                            window.location.href = '/product';
                        }
                    }, 2000);
                } else {
                    showUploadError(response.message || 'Ошибка при создании товара');
                }
            } catch (e) {
                // Если ответ не JSON, значит это обычная форма
                fileProgressData.forEach(file => {
                    file.status = 'success';
                    file.statusText = '✓ Загружен';
                });
                updateFileProgress(fileProgressData);
                
                showUploadSuccess();
                // Перенаправляем на созданный товар или список товаров
                setTimeout(() => {
                    console.log('Catch блок: попытка редиректа. createdProductUrl:', createdProductUrl);
                    if (createdProductUrl) {
                        console.log('Catch блок: редирект на createdProductUrl:', createdProductUrl);
                        window.location.href = createdProductUrl;
                    } else {
                        console.log('Catch блок: редирект на fallback /product');
                        window.location.href = '/product';
                    }
                }, 2000);
            }
        } else if (xhr.status === 422) {
            // Ошибка валидации
            try {
                const response = JSON.parse(xhr.responseText);
                let errorMessage = response.message || 'Ошибка валидации';
                
                if (response.errors) {
                    const errorFields = Object.keys(response.errors);
                    if (errorFields.length > 0) {
                        errorMessage = `Ошибки в полях: ${errorFields.join(', ')}`;
                    }
                }
                
                showUploadError(errorMessage);
            } catch (e) {
                showUploadError('Ошибка валидации данных');
            }
        } else if (xhr.status === 413) {
            // Ошибка слишком большого размера файлов
            showUploadError('Общий размер файлов слишком большой. Максимальный размер: 1000MB. Попробуйте загрузить файлы по частям.');
        } else {
            showUploadError(`Ошибка сервера: ${xhr.status}`);
        }
    });
    
    xhr.addEventListener('error', function() {
        showUploadError('Ошибка сети при загрузке файлов');
    });
    
    xhr.addEventListener('abort', function() {
        if (uploadCancelled) {
            hideUploadProgress();
        }
    });
    
    // Сохраняем ссылку на xhr для возможности отмены
    window.currentUploadXHR = xhr;
    
    // Отправляем запрос
    xhr.open('POST', form.action);
    xhr.setRequestHeader('X-CSRF-TOKEN', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
    xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
    xhr.send(formData);
}

// Функция инициализации TreeSelect (аналогично PrimeVue TreeSelect)
function initializeTreeSelect(treeselectId, selectId, categories) {
    const treeselectInput = document.getElementById(treeselectId);
    const treeselectDropdown = document.getElementById(treeselectId + '_dropdown');
    const treeselectTree = document.getElementById(treeselectId + '_tree');
    const treeselectSearch = document.getElementById(treeselectId + '_search');
    const hiddenSelect = document.getElementById(selectId);
    
    if (!treeselectInput || !treeselectDropdown || !treeselectTree || !hiddenSelect) return;
    
    let isOpen = false;
    let focusedIndex = -1;
    let treeNodes = [];
    
    // Строим дерево из категорий
    function buildTree(categories, parentId = 0) {
        const tree = [];
        categories.forEach(category => {
            if (category.parent_id == parentId) {
                const node = {
                    id: category.id,
                    name: category.name,
                    parent_id: category.parent_id,
                    children: buildTree(categories, category.id),
                    expanded: false
                };
                tree.push(node);
            }
        });
        return tree;
    }
    
    // Функция для проверки, является ли категория выбираемой (без подкатегорий)
    function isSelectableCategory(categoryId) {
        return !categories.some(cat => cat.parent_id == categoryId);
    }
    
    // Функция для получения только выбираемых категорий
    function getSelectableCategories() {
        return categories.filter(category => {
            // Категория выбираема только если у неё нет подкатегорий
            return !categories.some(cat => cat.parent_id == category.id);
        });
    }
    
    // Создаем HTML для узла дерева
    function createNodeHTML(node, level = 0) {
        const hasChildren = node.children && node.children.length > 0;
        const indent = level * 20;
        
        // Проверяем, является ли категория выбираемой (без подкатегорий)
        const isSelectable = isSelectableCategory(node.id);
        
        let html = `<div class="treeselect-node ${!isSelectable ? 'disabled' : ''}" data-id="${node.id}" data-level="${level}" data-selectable="${isSelectable}" tabindex="0">`;
        
        // Отступ
        html += `<div class="treeselect-indent" style="width: ${indent}px;"></div>`;
        
        // Кнопка разворачивания
        if (hasChildren) {
            html += `<div class="treeselect-toggle" data-id="${node.id}">
                <svg class="treeselect-toggle-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="9,18 15,12 9,6"></polyline>
                </svg>
            </div>`;
        } else {
            html += `<div class="treeselect-indent" style="width: 16px;"></div>`;
        }
        
        // Название узла
        html += `<div class="treeselect-label">${node.name}${!isSelectable ? ' <span style="color: #6c757d; font-size: 12px;">(недоступно для выбора)</span>' : ''}</div>`;
        html += `</div>`;
        
        // Дочерние узлы
        if (hasChildren) {
            html += `<div class="treeselect-children" data-parent="${node.id}">`;
            node.children.forEach(child => {
                html += createNodeHTML(child, level + 1);
            });
            html += `</div>`;
        }
        
        return html;
    }
    
    // Обновляем дерево
    function updateTree(filteredCategories = null) {
        const categoriesToUse = filteredCategories || categories;
        treeNodes = buildTree(categoriesToUse);
        
        let html = '';
        treeNodes.forEach(node => {
            html += createNodeHTML(node);
        });
        
        treeselectTree.innerHTML = html;
        attachNodeEvents();
    }
    
    // Привязываем события к узлам
    function attachNodeEvents() {
        // События для узлов
        const nodes = treeselectTree.querySelectorAll('.treeselect-node');
        nodes.forEach((node, index) => {
            const nodeId = node.dataset.id;
            
            // Проверяем, является ли категория выбираемой (без подкатегорий)
            const isSelectable = isSelectableCategory(nodeId);
            
            // Добавляем события только для выбираемых узлов
            if (isSelectable) {
                node.addEventListener('click', function(e) {
                    e.stopPropagation();
                    
                    const nodeLabel = this.querySelector('.treeselect-label');
                    // Получаем текст без HTML-разметки
                    const nodeName = nodeLabel.textContent || nodeLabel.innerText;
                    selectNode(nodeId, nodeName);
                });
                
                node.addEventListener('keydown', function(e) {
                    if (e.key === 'Enter' || e.key === ' ') {
                        e.preventDefault();
                        
                        const nodeLabel = this.querySelector('.treeselect-label');
                        // Получаем текст без HTML-разметки
                        const nodeName = nodeLabel.textContent || nodeLabel.innerText;
                        selectNode(nodeId, nodeName);
                    }
                });
            } else {
                // Для недоступных узлов добавляем обработчик, который разворачивает дерево
                node.addEventListener('click', function(e) {
                    e.stopPropagation();
                    
                    // Проверяем, есть ли у узла кнопка разворачивания
                    const toggle = this.querySelector('.treeselect-toggle');
                    if (toggle) {
                        // Если есть кнопка разворачивания, кликаем по ней
                        toggle.click();
                    } else {
                        // Если нет кнопки разворачивания, показываем предупреждение
                        alert('Эта категория недоступна для выбора.\n\nДоступны только категории без подкатегорий.\n\nНедоступные категории отображаются серым цветом.');
                    }
                });
                
                node.addEventListener('keydown', function(e) {
                    if (e.key === 'Enter' || e.key === ' ') {
                        e.preventDefault();
                        
                        // Проверяем, есть ли у узла кнопка разворачивания
                        const toggle = this.querySelector('.treeselect-toggle');
                        if (toggle) {
                            // Если есть кнопка разворачивания, кликаем по ней
                            toggle.click();
                        } else {
                            // Если нет кнопки разворачивания, показываем предупреждение
                            alert('Эта категория недоступна для выбора.\n\nДоступны только категории без подкатегорий.\n\nНедоступные категории отображаются серым цветом.');
                        }
                    }
                });
            }
        });
        
        // События для кнопок разворачивания
        const toggles = treeselectTree.querySelectorAll('.treeselect-toggle');
        toggles.forEach(toggle => {
            toggle.addEventListener('click', function(e) {
                e.stopPropagation();
                const nodeId = this.dataset.id;
                toggleNode(nodeId);
            });
        });
    }
    
    // Выбор узла
    function selectNode(nodeId, nodeName) {
        hiddenSelect.value = nodeId;
        
        const placeholder = treeselectInput.querySelector('.treeselect-placeholder');
        let value = treeselectInput.querySelector('.treeselect-value');
        
        // Скрываем плейсхолдер
        placeholder.style.display = 'none';
        
        // Показываем выбранное значение
        if (!value) {
            value = document.createElement('span');
            value.className = 'treeselect-value';
            treeselectInput.insertBefore(value, treeselectInput.querySelector('.treeselect-arrow'));
        }
        
        // Убираем дополнительный текст "(недоступно для выбора)" из названия и HTML-разметку
        const cleanNodeName = nodeName
            .replace(' (недоступно для выбора)', '')
            .replace(/<[^>]*>/g, ''); // Убираем HTML-теги
        value.textContent = cleanNodeName;
        value.style.display = 'block';
        
        closeDropdown();
    }
    
    // Разворачивание/сворачивание узла
    function toggleNode(nodeId) {
        const toggle = treeselectTree.querySelector(`[data-id="${nodeId}"] .treeselect-toggle`);
        const children = treeselectTree.querySelector(`[data-parent="${nodeId}"]`);
        
        if (toggle && children) {
            const isExpanded = toggle.classList.contains('expanded');
            
            if (isExpanded) {
                toggle.classList.remove('expanded');
                children.classList.remove('expanded');
            } else {
                toggle.classList.add('expanded');
                children.classList.add('expanded');
            }
        }
    }
    
    // Открытие/закрытие выпадающего списка
    function toggleDropdown() {
        isOpen = !isOpen;
        
        if (isOpen) {
            treeselectInput.classList.add('active');
            treeselectDropdown.classList.add('active');
            updateTree();
            treeselectSearch.focus();
        } else {
            closeDropdown();
        }
    }
    
    function closeDropdown() {
        isOpen = false;
        treeselectInput.classList.remove('active');
        treeselectDropdown.classList.remove('active');
    }
    
    // Фильтрация
    function filterTree(searchTerm) {
        if (!searchTerm) {
            updateTree();
            return;
        }
        
        const searchTermLower = searchTerm.toLowerCase();
        
        // Фильтруем категории, которые содержат поисковый запрос И являются выбираемыми
        const filteredCategories = categories.filter(category => {
            const matchesSearch = category.name.toLowerCase().includes(searchTermLower);
            const isSelectable = isSelectableCategory(category.id);
            return matchesSearch && isSelectable;
        });
        
        // Создаем плоский список отфильтрованных категорий для отображения
        let html = '';
        filteredCategories.forEach(category => {
            // Проверяем, является ли категория выбираемой (без подкатегорий)
            const isSelectable = isSelectableCategory(category.id);
            
            // Подсвечиваем найденный текст
            const highlightedName = category.name.replace(
                new RegExp(searchTerm, 'gi'),
                match => `<mark style="background-color: #ffeb3b; padding: 1px 2px; border-radius: 2px;">${match}</mark>`
            );
            
            html += `<div class="treeselect-node ${!isSelectable ? 'disabled' : ''}" data-id="${category.id}" data-level="0" data-selectable="${isSelectable}" tabindex="0">
                <div class="treeselect-indent" style="width: 0px;"></div>
                <div class="treeselect-indent" style="width: 16px;"></div>
                <div class="treeselect-label">${highlightedName}</div>
            </div>`;
        });
        
        treeselectTree.innerHTML = html;
        attachNodeEvents();
    }
    
    // События
    treeselectInput.addEventListener('click', toggleDropdown);
    
    treeselectInput.addEventListener('keydown', function(e) {
        if (e.key === 'Enter' || e.key === ' ' || e.key === 'ArrowDown') {
            e.preventDefault();
            if (!isOpen) {
                toggleDropdown();
            }
        } else if (e.key === 'Escape') {
            closeDropdown();
        }
    });
    
    treeselectSearch.addEventListener('input', function() {
        filterTree(this.value);
    });
    
    treeselectSearch.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeDropdown();
        }
    });
    
    // Закрытие при клике вне
    document.addEventListener('click', function(e) {
        if (!treeselectInput.contains(e.target) && !treeselectDropdown.contains(e.target)) {
            closeDropdown();
        }
    });
    
    // Инициализация с текущим значением
    if (hiddenSelect.value && hiddenSelect.value !== '') {
        const selectedOption = hiddenSelect.options[hiddenSelect.selectedIndex];
        if (selectedOption) {
            selectNode(selectedOption.value, selectedOption.textContent);
        }
    }
}
</script>
@endsection
