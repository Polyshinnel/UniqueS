@extends('layouts.layout')

@section('title', 'Создание объявления')

@section('search-filter')
@endsection

@section('header-action-btn')
@endsection

@section('header-title')
    <h1 class="header-title">Создание объявления</h1>
@endsection

@section('content')
<!-- Подключение Quill.js -->
<link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
<script src="https://cdn.quilljs.com/1.3.6/quill.min.js"></script>

<style>
/* Стили для Quill.js */
.ql-editor {
    min-height: 200px !important;
    max-height: 400px !important;
    font-size: 14px;
    line-height: 1.6;
}

.ql-container {
    border: 2px solid #e9ecef !important;
    border-top: none !important;
    border-radius: 0 0 8px 8px !important;
    background-color: #fff !important;
}

.ql-toolbar {
    border: 2px solid #e9ecef !important;
    border-bottom: 1px solid #e9ecef !important;
    border-radius: 8px 8px 0 0 !important;
    background-color: #f8f9fa !important;
}

.ql-toolbar button {
    color: #495057 !important;
}

.ql-toolbar button:hover {
    color: #133E71 !important;
}

.ql-toolbar button.ql-active {
    color: #133E71 !important;
}

.ql-toolbar .ql-stroke {
    stroke: #495057 !important;
}

.ql-toolbar .ql-fill {
    fill: #495057 !important;
}

.ql-toolbar button:hover .ql-stroke {
    stroke: #133E71 !important;
}

.ql-toolbar button:hover .ql-fill {
    fill: #133E71 !important;
}

.ql-toolbar button.ql-active .ql-stroke {
    stroke: #133E71 !important;
}

.ql-toolbar button.ql-active .ql-fill {
    fill: #133E71 !important;
}

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

/* Стили для подсказок */
.tooltip-trigger {
    display: inline-flex;
    align-items: center;
    margin-left: 5px;
    cursor: help;
}

.tooltip {
    position: fixed;
    background: #333;
    color: white;
    padding: 8px 12px;
    border-radius: 4px;
    font-size: 12px;
    max-width: 300px;
    z-index: 10000;
    display: none;
    pointer-events: none;
}

/* Остальные стили остаются без изменений */
.product-media-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
    gap: 15px;
    margin-top: 10px;
}

.product-media-item {
    position: relative;
    border: 2px solid #ddd;
    border-radius: 8px;
    overflow: hidden;
    cursor: pointer;
    transition: all 0.3s ease;
}

.product-media-item:hover {
    transform: scale(1.05);
    border-color: #133E71;
}

.product-media-item.selected {
    border-color: #133E71;
    box-shadow: 0 0 10px rgba(19, 62, 113, 0.3);
}

.product-media-item img,
.product-media-item video {
    width: 100%;
    height: 120px;
    object-fit: cover;
}

.media-type-badge {
    position: absolute;
    top: 5px;
    left: 5px;
    background: rgba(0, 0, 0, 0.7);
    color: white;
    padding: 2px 6px;
    border-radius: 4px;
    font-size: 12px;
}

.media-checkbox {
    position: absolute;
    top: 5px;
    right: 5px;
    width: 20px;
    height: 20px;
}

.no-media-message {
    text-align: center;
    color: #666;
    font-style: italic;
    padding: 20px;
    background: #f9f9f9;
    border-radius: 8px;
}

.media-info {
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    background: linear-gradient(to top, rgba(0,0,0,0.8), transparent);
    color: white;
    padding: 8px;
    font-size: 12px;
}

.media-name {
    font-weight: 500;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.media-size {
    opacity: 0.8;
    font-size: 11px;
}

.product-media-preview-btn {
    position: absolute;
    top: 8px;
    right: 35px;
    width: 28px;
    height: 28px;
    background: rgba(0, 0, 0, 0.7);
    border: none;
    border-radius: 50%;
    color: white;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 0;
    transition: all 0.3s ease;
    z-index: 10;
}

.product-media-item:hover .product-media-preview-btn {
    opacity: 1;
}

.product-media-preview-btn:hover {
    background: rgba(19, 62, 113, 0.9);
    transform: scale(1.1);
}

.product-media-preview-btn svg {
    width: 14px;
    height: 14px;
}

.payment-types-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 15px;
    margin-top: 10px;
}

.payment-type-item {
    display: flex;
    align-items: center;
    padding: 15px;
    border: 2px solid #ddd;
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.3s ease;
}

.payment-type-item:hover {
    border-color: #133E71;
    background-color: #f8f9fa;
}

.payment-type-item.selected {
    border-color: #133E71;
    background-color: #e3f2fd;
}

.payment-type-checkbox {
    margin-right: 10px;
    width: 18px;
    height: 18px;
}

.payment-type-label {
    cursor: pointer;
    font-weight: 500;
    margin: 0;
}

/* Стили для тегов */
.tags-container {
    margin-top: 10px;
}

.tags-input-wrapper {
    display: flex;
    gap: 10px;
    margin-bottom: 10px;
}

.tags-input-wrapper input {
    flex: 1;
}

.tags-input-wrapper button {
    white-space: nowrap;
}

.tags-list {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    min-height: 40px;
    padding: 8px;
    border: 2px solid #e9ecef;
    border-radius: 8px;
    background-color: #f8f9fa;
}

.tag-item {
    display: inline-flex;
    align-items: center;
    background: #133E71;
    color: white;
    padding: 4px 8px;
    border-radius: 16px;
    font-size: 12px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.3s ease;
}

.tag-item:hover {
    background: #0d2a4f;
    transform: scale(1.05);
}

.tag-remove {
    margin-left: 6px;
    cursor: pointer;
    font-weight: bold;
    font-size: 14px;
    line-height: 1;
}

.tag-remove:hover {
    color: #ff6b6b;
}

/* Стили для select с поиском */
.select-wrapper {
    position: relative;
    width: 100%;
}

.select-input {
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

.select-input:hover {
    border-color: #133E71;
}

.select-input:focus {
    outline: none;
    border-color: #133E71;
    box-shadow: 0 0 0 3px rgba(19, 62, 113, 0.1);
}

.select-input.active {
    border-color: #133E71;
    box-shadow: 0 0 0 3px rgba(19, 62, 113, 0.1);
}

.select-placeholder {
    color: #6c757d;
    font-size: 14px;
}

.select-value {
    color: #495057;
    font-size: 14px;
    font-weight: 500;
}

.select-arrow {
    color: #6c757d;
    transition: transform 0.3s ease;
    flex-shrink: 0;
}

.select-input.active .select-arrow {
    transform: rotate(180deg);
}

.select-dropdown {
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

.select-dropdown.active {
    display: block;
    animation: selectFadeIn 0.15s ease-out;
}

@keyframes selectFadeIn {
    from {
        opacity: 0;
        transform: translateY(-8px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.select-search {
    padding: 12px;
    border-bottom: 1px solid #f1f3f4;
}

.select-search-input {
    width: 100%;
    padding: 8px 12px;
    border: 1px solid #dee2e6;
    border-radius: 4px;
    font-size: 14px;
    outline: none;
    transition: border-color 0.3s ease;
}

.select-search-input:focus {
    border-color: #133E71;
}

.select-options {
    max-height: 200px;
    overflow-y: auto;
    padding: 8px 0;
}

.select-option {
    display: flex;
    align-items: center;
    padding: 8px 12px;
    cursor: pointer;
    transition: background-color 0.15s ease;
    font-size: 14px;
    color: #495057;
}

.select-option:hover {
    background: #f8f9fa;
}

.select-option.selected {
    background: #e3f2fd;
    color: #133E71;
    font-weight: 500;
}

/* Стили для скроллбара в select */
.select-options::-webkit-scrollbar {
    width: 6px;
}

.select-options::-webkit-scrollbar-track {
    background: #f8f9fa;
    border-radius: 3px;
}

.select-options::-webkit-scrollbar-thumb {
    background: #dee2e6;
    border-radius: 3px;
}

.select-options::-webkit-scrollbar-thumb:hover {
    background: #adb5bd;
}

/* Стили для селектора главного изображения */
.main-image-selector {
    margin-top: 10px;
}

.main-image-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
    gap: 12px;
    max-height: 300px;
    overflow-y: auto;
    padding: 8px;
    border: 2px solid #e9ecef;
    border-radius: 8px;
    background-color: #f8f9fa;
}

.main-image-item {
    position: relative;
    border: 3px solid #ddd;
    border-radius: 8px;
    overflow: hidden;
    cursor: pointer;
    transition: all 0.3s ease;
    background: white;
    aspect-ratio: 1;
}

.main-image-item:hover {
    transform: scale(1.05);
    border-color: #133E71;
    box-shadow: 0 4px 12px rgba(19, 62, 113, 0.2);
}

.main-image-item.selected {
    border-color: #133E71;
    box-shadow: 0 0 0 3px rgba(19, 62, 113, 0.3);
}

.main-image-item img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
}

.main-image-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(19, 62, 113, 0.8);
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 0;
    transition: opacity 0.3s ease;
}

.main-image-item.selected .main-image-overlay {
    opacity: 1;
}

.main-image-check {
    color: white;
    font-size: 24px;
    font-weight: bold;
}

.main-image-info {
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    background: linear-gradient(to top, rgba(0,0,0,0.8), transparent);
    color: white;
    padding: 6px 8px;
    font-size: 11px;
    opacity: 0;
    transition: opacity 0.3s ease;
}

.main-image-item:hover .main-image-info {
    opacity: 1;
}

.main-image-name {
    font-weight: 500;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    margin-bottom: 2px;
}

.main-image-size {
    opacity: 0.8;
    font-size: 10px;
}

.main-image-preview-btn {
    position: absolute;
    top: 8px;
    right: 8px;
    width: 28px;
    height: 28px;
    background: rgba(0, 0, 0, 0.7);
    border: none;
    border-radius: 50%;
    color: white;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 0;
    transition: all 0.3s ease;
    z-index: 10;
}

.main-image-item:hover .main-image-preview-btn {
    opacity: 1;
}

.main-image-preview-btn:hover {
    background: rgba(19, 62, 113, 0.9);
    transform: scale(1.1);
}

.main-image-preview-btn svg {
    width: 14px;
    height: 14px;
}

.no-images-message {
    text-align: center;
    padding: 40px 20px;
    border: 2px solid #e9ecef;
    border-radius: 8px;
    background-color: #f8f9fa;
}

.no-images-content {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 12px;
}

.no-images-content svg {
    opacity: 0.5;
}

.no-images-content p {
    margin: 0;
    color: #666;
    font-weight: 500;
}

.no-images-content small {
    color: #999;
    font-size: 12px;
}

/* Стили для модального окна предварительного просмотра */
.image-preview-modal {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.8);
    display: none;
    align-items: center;
    justify-content: center;
    z-index: 9999;
    padding: 20px;
}

.image-preview-modal.active {
    display: flex;
    animation: modalFadeIn 0.3s ease;
}

@keyframes modalFadeIn {
    from {
        opacity: 0;
    }
    to {
        opacity: 1;
    }
}

.image-preview-content {
    background: white;
    border-radius: 12px;
    max-width: 90vw;
    max-height: 90vh;
    overflow: hidden;
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
    animation: modalSlideIn 0.3s ease;
}

@keyframes modalSlideIn {
    from {
        transform: scale(0.8);
        opacity: 0;
    }
    to {
        transform: scale(1);
        opacity: 1;
    }
}

.image-preview-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 16px 20px;
    border-bottom: 1px solid #e9ecef;
    background: #f8f9fa;
}

.image-preview-header h3 {
    margin: 0;
    color: #495057;
    font-size: 18px;
    font-weight: 600;
}

.image-preview-close {
    background: none;
    border: none;
    font-size: 24px;
    color: #6c757d;
    cursor: pointer;
    padding: 0;
    width: 32px;
    height: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 4px;
    transition: all 0.2s ease;
}

.image-preview-close:hover {
    background: #e9ecef;
    color: #495057;
}

.image-preview-body {
    padding: 20px;
    text-align: center;
}

.image-preview-body img {
    max-width: 100%;
    max-height: 70vh;
    object-fit: contain;
    border-radius: 8px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.main-image-help {
    margin-top: 8px;
    padding: 12px;
    background: #f8f9fa;
    border-radius: 6px;
    border-left: 4px solid #133E71;
}

.main-image-help small {
    line-height: 1.5;
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
            <span class="step-title">Медиафайлы</span>
        </div>
    </div>

    <form id="advertisementForm" method="POST" action="{{ route('advertisements.store') }}" enctype="multipart/form-data">
        @csrf

        <!-- Шаг 1: Основная информация -->
        <div class="step-content active" id="step-1">
            <h2>Основная информация</h2>

            <div class="form-row">
                <div class="form-group">
                    <label for="product_id">Товар</label>
                    <div class="select-wrapper">
                        <div class="select-input" id="product_select" tabindex="0">
                            <span class="select-placeholder">Выберите товар</span>
                            <svg class="select-arrow" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polyline points="6,9 12,15 18,9"></polyline>
                            </svg>
                        </div>
                        <div class="select-dropdown" id="product_select_dropdown">
                            <div class="select-search">
                                <input type="text" id="product_select_search" placeholder="Поиск товаров..." class="select-search-input">
                            </div>
                            <div class="select-options" id="product_select_options">
                                <!-- Опции будут заполнены JavaScript -->
                            </div>
                        </div>
                        <select name="product_id" id="product_id" class="form-control" required style="display: none;">
                            <option value="">Выберите товар</option>
                            @foreach($products as $productItem)
                                <option value="{{ $productItem->id }}" {{ $product && $product->id == $productItem->id ? 'selected' : '' }}>
                                    {{ $productItem->name }} ({{ $productItem->sku }}) - {{ $productItem->category->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <small class="form-text text-muted">Доступны для создания только товары со статусом "В продаже" и без созданных объявлений.</small>
                    <button type="button" id="copyFromProduct" class="btn btn-secondary mt-2" style="margin-top: 7px;">Заполнить данными товара</button>
                </div>

                <div class="form-group">
                    <label for="title">Название объявления</label>
                    <input type="text" name="title" id="title" class="form-control" required value="{{ $product ? $product->name : '' }}">
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
                                <option value="{{ $category->id }}" {{ $product && $product->category_id == $category->id ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <small class="form-text text-muted">Доступны только категории без подкатегорий</small>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="product_state">Состояние товара</label>
                    <select name="product_state" id="product_state" class="form-control">
                        <option value="">Выберите состояние</option>
                        @foreach($productStates as $state)
                            <option value="{{ $state->id }}" {{ $product && $product->state_id == $state->id ? 'selected' : '' }}>
                                {{ $state->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label for="product_available">Доступность товара</label>
                    <select name="product_available" id="product_available" class="form-control">
                        <option value="">Выберите доступность</option>
                        @foreach($productAvailables as $available)
                            <option value="{{ $available->id }}" {{ $product && $product->available_id == $available->id ? 'selected' : '' }}>
                                {{ $available->name }}
                            </option>
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
                <label for="main_characteristics">Основные характеристики</label>
                <textarea name="main_characteristics" id="main_characteristics" class="form-control" rows="4">{{ $product ? $product->main_chars : '' }}</textarea>
            </div>

            <div class="form-group">
                <label for="complectation">Комплектация</label>
                <textarea name="complectation" id="complectation" class="form-control" rows="4">{{ $product ? $product->complectation : '' }}</textarea>
            </div>

            <div class="form-group">
                <label for="technical_characteristics">Технические характеристики</label>
                <div class="editor-container">
                    <textarea name="technical_characteristics" id="technical_characteristics" class="form-control" style="display: none;"></textarea>
                    <div id="technical_characteristics_editor"></div>
                </div>
                <small class="form-text text-muted">Используйте панель инструментов для форматирования текста</small>
            </div>

            <div class="form-group">
                <label for="main_info">Основная информация</label>
                <div class="editor-container">
                    <textarea name="main_info" id="main_info" class="form-control" style="display: none;"></textarea>
                    <div id="main_info_editor"></div>
                </div>
                <small class="form-text text-muted">Используйте панель инструментов для форматирования текста</small>
            </div>

            <div class="form-group">
                <label for="tags">Теги</label>
                <div class="tags-container">
                    <div class="tags-input-wrapper">
                        <input type="text" id="tag_input" class="form-control" placeholder="Введите тег и нажмите Enter">
                        <button type="button" id="add_tag_btn" class="btn btn-secondary">Добавить</button>
                    </div>
                    <div class="tags-list" id="tags_list">
                        <!-- Теги будут добавляться сюда -->
                    </div>
                    <input type="hidden" name="tags" id="tags_hidden" value="">
                </div>
                <small class="form-text text-muted">Добавляйте теги для лучшего поиска объявления</small>
            </div>

            <div class="form-group">
                <label for="additional_info">Дополнительная информация</label>
                <div class="editor-container">
                    <textarea name="additional_info" id="additional_info" class="form-control" style="display: none;"></textarea>
                    <div id="additional_info_editor"></div>
                </div>
                <small class="form-text text-muted">Используйте панель инструментов для форматирования текста</small>
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
                    <option value="">Выберите статус</option>
                    @foreach($checkStatuses as $status)
                        <option value="{{ $status->id }}" {{ $product && $product->check->first() && $product->check->first()->check_status_id == $status->id ? 'selected' : '' }}>
                            {{ $status->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label for="check_comment">Комментарий по проверке</label>
                <textarea name="check_comment" id="check_comment" class="form-control" rows="4">{{ $product && $product->check->first() ? $product->check->first()->comment : '' }}</textarea>
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
                    <option value="">Выберите статус</option>
                    @foreach($installStatuses as $status)
                        <option value="{{ $status->id }}" {{ $product && $product->loading->first() && $product->loading->first()->install_status_id == $status->id ? 'selected' : '' }}>
                            {{ $status->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label for="loading_comment">Комментарий по погрузке</label>
                <textarea name="loading_comment" id="loading_comment" class="form-control" rows="4">{{ $product && $product->loading->first() ? $product->loading->first()->comment : '' }}</textarea>
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
                    <option value="">Выберите статус</option>
                    @foreach($installStatuses as $status)
                        <option value="{{ $status->id }}" {{ $product && $product->removal->first() && $product->removal->first()->install_status_id == $status->id ? 'selected' : '' }}>
                            {{ $status->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label for="removal_comment">Комментарий по демонтажу</label>
                <textarea name="removal_comment" id="removal_comment" class="form-control" rows="4">{{ $product && $product->removal->first() ? $product->removal->first()->comment : '' }}</textarea>
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
                <label for="adv_price">Цена продажи</label>
                <input type="number" name="adv_price" id="adv_price" class="form-control" 
                       step="0.01" min="0" value="{{ $product ? $product->purchase_price : '' }}">
            </div>

            <div class="form-group">
                <input type="hidden" name="show_price" value="0">
                <div class="form-check">
                    <input type="checkbox" name="show_price" id="show_price" class="form-check-input" value="1" checked>
                    <label for="show_price" class="form-check-label">Отображать цену на сайте</label>
                </div>
                <small class="form-text text-muted">Если отмечено, цена будет видна посетителям сайта</small>
            </div>

            <div class="form-group">
                <label for="adv_price_comment">Комментарий <span style="color: red;">*</span></label>
                <textarea name="adv_price_comment" id="adv_price_comment" class="form-control" rows="4" required>{{ $product ? $product->payment_comment : '' }}</textarea>
            </div>

            <div class="step-actions">
                <button type="button" class="btn btn-secondary prev-step">Предыдущий шаг</button>
                <button type="button" class="btn btn-primary next-step">Следующий шаг</button>
            </div>
        </div>

        <!-- Шаг 7: Медиафайлы -->
        <div class="step-content" id="step-7">
            <h2>Медиафайлы</h2>

            <!-- Выбор медиафайлов из товара -->
            <div class="form-group" id="product-media-section">
                <label>Медиафайлы товара</label>
                <div class="product-media-grid" id="productMediaGrid">
                    <!-- Будет заполнено через JavaScript -->
                </div>
            </div>

            <!-- Выбор главного изображения -->
            <div class="form-group">
                <label for="main_img">Главное изображение</label>
                <div class="main-image-selector" id="mainImageSelector">
                    <div class="main-image-grid" id="mainImageGrid">
                        <!-- Миниатюры изображений будут добавлены сюда -->
                    </div>
                    <div class="no-images-message" id="noImagesMessage" style="display: none;">
                        <div class="no-images-content">
                            <svg width="48" height="48" viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <rect x="8" y="8" width="32" height="32" rx="4" stroke="#ccc" stroke-width="2" stroke-dasharray="4 4"/>
                                <path d="M16 20L22 26L32 16" stroke="#ccc" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            <p>Нет доступных изображений</p>
                            <small>Сначала выберите товар с изображениями</small>
                        </div>
                    </div>
                </div>
                <input type="hidden" name="main_img" id="main_img" value="">
                <div class="main-image-help">
                    <small class="form-text text-muted">
                        <strong>Как выбрать главное изображение:</strong><br>
                        • Кликните на миниатюру изображения для выбора<br>
                        • Наведите курсор и нажмите кнопку 👁 для предварительного просмотра<br>
                        • Или используйте двойной клик или правый клик для просмотра<br>
                        • Выбранное изображение будет выделено синей рамкой
                    </small>
                </div>
            </div>

            <!-- Загрузка новых медиафайлов -->
            <div class="form-group">
                <label for="media_files">Загрузить дополнительные фото и видео</label>
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
                        <p class="upload-hint">Поддерживаются изображения (JPG, PNG, GIF) и видео (MP4, MOV, AVI)</p>
                    </div>
                </div>
                <div class="file-preview" id="filePreview"></div>
            </div>

            <div class="step-actions">
                <button type="button" class="btn btn-secondary prev-step">Предыдущий шаг</button>
                <button type="submit" class="btn btn-success">Создать объявление</button>
            </div>
        </div>
    </form>
</div>

<!-- Tooltip -->
<div class="tooltip" id="tooltip"></div>

<!-- Модальное окно для предварительного просмотра изображения -->
<div class="image-preview-modal" id="imagePreviewModal">
    <div class="image-preview-content">
        <div class="image-preview-header">
            <h3 id="imagePreviewTitle">Предварительный просмотр</h3>
            <button class="image-preview-close" id="imagePreviewClose">&times;</button>
        </div>
        <div class="image-preview-body">
            <img id="imagePreviewImg" src="" alt="Предварительный просмотр">
        </div>
    </div>
</div>

<script>
// Данные для TreeSelect
const categoriesData = @json($categories);

// Данные о товарах с их статусами
const productsData = @json($products);

// Глобальные переменные для редакторов
let technicalEditor, additionalInfoEditor, mainInfoEditor;
let tags = [];

document.addEventListener('DOMContentLoaded', function() {
    const steps = document.querySelectorAll('.step');
    const stepContents = document.querySelectorAll('.step-content');
    const nextButtons = document.querySelectorAll('.next-step');
    const prevButtons = document.querySelectorAll('.prev-step');
    const productSelect = document.getElementById('product_id');
    const copyButton = document.getElementById('copyFromProduct');
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

        // Загружаем медиафайлы товара на 7 шаге
        if (stepNumber === 7) {
            loadProductMedia();
        }
    }

    function validateStep(stepNumber) {
        const stepElement = document.getElementById(`step-${stepNumber}`);
        const requiredFields = stepElement.querySelectorAll('[required]');

        // Проверяем обязательные поля
        for (let field of requiredFields) {
            if (!field.value.trim()) {
                field.focus();
                return false;
            }
        }

        // Дополнительная проверка для первого шага - товар должен быть выбран
        if (stepNumber === 1) {
            const productId = document.getElementById('product_id').value;
            if (!productId) {
                showNotification('Пожалуйста, выберите товар для создания объявления.', 'error');
                return false;
            }
        }

        // Дополнительная проверка для шага 6 - комментарий к цене обязателен
        if (stepNumber === 6) {
            const priceComment = document.getElementById('adv_price_comment').value.trim();
            if (!priceComment) {
                showNotification('Пожалуйста, заполните комментарий к цене продажи.', 'error');
                document.getElementById('adv_price_comment').focus();
                return false;
            }
        }

        return true;
    }

    nextButtons.forEach(button => {
        button.addEventListener('click', function() {
            if (validateStep(currentStep)) {
                if (currentStep < 7) {
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

    // Копирование данных из товара
    copyButton.addEventListener('click', function() {
        const productId = productSelect.value;
        if (!productId) {
            showNotification('Сначала выберите товар', 'error');
            return;
        }

        fetch(`{{ route('advertisements.copy-from-product') }}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ product_id: productId })
        })
        .then(response => response.json())
        .then(data => {
            // Заполняем поля данными из товара
            document.getElementById('main_characteristics').value = data.main_characteristics || '';
            document.getElementById('complectation').value = data.complectation || '';
            document.getElementById('category_id').value = data.category_id || '';
            document.getElementById('product_state').value = data.product_state || '';
            document.getElementById('product_available').value = data.product_available || '';
            
            // Обновляем TreeSelect для категории
            if (data.category_id) {
                const categorySelect = document.getElementById('category_id');
                const selectedOption = categorySelect.options[categorySelect.selectedIndex];
                if (selectedOption) {
                    // Находим функцию selectNode для категории и вызываем её
                    if (typeof window.selectCategoryNode === 'function') {
                        window.selectCategoryNode(data.category_id, selectedOption.textContent);
                    }
                }
            }
            
            // Заполняем редакторы технических характеристик и дополнительной информации
            if (technicalEditor && data.technical_characteristics) {
                technicalEditor.root.innerHTML = data.technical_characteristics;
                document.getElementById('technical_characteristics').value = data.technical_characteristics;
            }
            if (additionalInfoEditor && data.additional_info) {
                additionalInfoEditor.root.innerHTML = data.additional_info;
                document.getElementById('additional_info').value = data.additional_info;
            }
            
            // Заполняем данные проверки
            if (data.check_data) {
                document.getElementById('check_status_id').value = data.check_data.status_id || '';
                document.getElementById('check_comment').value = data.check_data.comment || '';
            }
            
            // Заполняем данные погрузки
            if (data.loading_data) {
                document.getElementById('loading_status_id').value = data.loading_data.status_id || '';
                document.getElementById('loading_comment').value = data.loading_data.comment || '';
            }
            
            // Заполняем данные демонтажа
            if (data.removal_data) {
                document.getElementById('removal_status_id').value = data.removal_data.status_id || '';
                document.getElementById('removal_comment').value = data.removal_data.comment || '';
            }
            
            // Заполняем данные оплаты
            if (data.payment_data) {
                document.getElementById('adv_price').value = data.payment_data.adv_price || '';
                document.getElementById('adv_price_comment').value = data.payment_data.adv_price_comment || '';
            }
            
            showNotification('Данные успешно скопированы из товара!', 'success');
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('Ошибка при копировании данных', 'error');
        });
    });

    // Обработчики для тегов
    const tagInput = document.getElementById('tag_input');
    const addTagBtn = document.getElementById('add_tag_btn');
    const tagsList = document.getElementById('tags_list');
    const tagsHidden = document.getElementById('tags_hidden');

    function addTag(tagText) {
        const trimmedTag = tagText.trim();
        if (trimmedTag && !tags.includes(trimmedTag)) {
            tags.push(trimmedTag);
            updateTagsDisplay();
            tagInput.value = '';
        }
    }

    function removeTag(tagText) {
        const index = tags.indexOf(tagText);
        if (index > -1) {
            tags.splice(index, 1);
            updateTagsDisplay();
        }
    }

    // Делаем функцию доступной глобально
    window.removeTag = removeTag;

    function updateTagsDisplay() {
        tagsList.innerHTML = '';
        tags.forEach(tag => {
            const tagElement = document.createElement('div');
            tagElement.className = 'tag-item';
            tagElement.innerHTML = `
                ${tag.replace(/</g, '&lt;').replace(/>/g, '&gt;')}
                <span class="tag-remove" onclick="removeTag('${tag.replace(/'/g, '\\\'')}')">&times;</span>
            `;
            tagsList.appendChild(tagElement);
        });
        tagsHidden.value = JSON.stringify(tags);
    }

    // Добавление тега по Enter
    tagInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            addTag(this.value);
        }
    });

    // Добавление тега по кнопке
    addTagBtn.addEventListener('click', function() {
        addTag(tagInput.value);
    });

    // Обработчики для чекбоксов типов оплаты
    document.querySelectorAll('.payment-type-checkbox').forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            const item = this.closest('.payment-type-item');
            if (this.checked) {
                item.classList.add('selected');
            } else {
                item.classList.remove('selected');
            }
        });
    });

    function loadProductMedia() {
        const productId = productSelect.value;
        const mediaGrid = document.getElementById('productMediaGrid');
        
        if (!productId) {
            mediaGrid.innerHTML = '<div class="no-media-message">Сначала выберите товар</div>';
            
            // Очищаем селектор главного изображения
            const mainImageGrid = document.getElementById('mainImageGrid');
            const noImagesMessage = document.getElementById('noImagesMessage');
            const mainImgHidden = document.getElementById('main_img');
            
            mainImageGrid.style.display = 'none';
            noImagesMessage.style.display = 'block';
            mainImgHidden.value = '';
            return;
        }

        mediaGrid.innerHTML = '<div class="no-media-message">Загрузка медиафайлов...</div>';

        fetch(`/advertisements/product/${productId}/media`)
            .then(response => response.json())
            .then(data => {
                if (data.length === 0) {
                    mediaGrid.innerHTML = '<div class="no-media-message">У выбранного товара нет медиафайлов</div>';
                    return;
                }

                let html = '';
                const mainImageGrid = document.getElementById('mainImageGrid');
                const noImagesMessage = document.getElementById('noImagesMessage');
                const mainImgHidden = document.getElementById('main_img');
                
                // Собираем только изображения для главного изображения
                const images = data.filter(media => media.file_type === 'image');
                
                if (images.length === 0) {
                    mainImageGrid.style.display = 'none';
                    noImagesMessage.style.display = 'block';
                    mainImgHidden.value = '';
                } else {
                    mainImageGrid.style.display = 'grid';
                    noImagesMessage.style.display = 'none';
                    
                    let mainImageHtml = '';
                    images.forEach(media => {
                        mainImageHtml += `
                            <div class="main-image-item" data-media-id="${media.id}">
                                <img src="${media.full_url}" alt="${media.file_name}">
                                <div class="main-image-overlay">
                                    <span class="main-image-check">✓</span>
                                </div>
                                <div class="main-image-info">
                                    <div class="main-image-name">${media.file_name}</div>
                                    <div class="main-image-size">${media.formatted_size}</div>
                                </div>
                                <button class="main-image-preview-btn" title="Предварительный просмотр">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                        <circle cx="12" cy="12" r="3"></circle>
                                    </svg>
                                </button>
                            </div>
                        `;
                    });
                    mainImageGrid.innerHTML = mainImageHtml;
                    
                    // Добавляем обработчики для выбора главного изображения
                    const mainImageItems = document.querySelectorAll('.main-image-item');
                    mainImageItems.forEach(item => {
                        item.addEventListener('click', function(e) {
                            // Убираем выделение со всех изображений
                            document.querySelectorAll('.main-image-item').forEach(img => {
                                img.classList.remove('selected');
                            });
                            
                            // Выделяем выбранное изображение
                            this.classList.add('selected');
                            
                            // Устанавливаем значение в скрытое поле
                            const mediaId = this.dataset.mediaId;
                            mainImgHidden.value = mediaId;
                        });
                        
                        // Отдельный обработчик для предварительного просмотра по двойному клику
                        item.addEventListener('dblclick', function(e) {
                            e.preventDefault();
                            e.stopPropagation();
                            const img = this.querySelector('img');
                            const imageName = this.querySelector('.main-image-name').textContent;
                            showImagePreview(img.src, imageName);
                        });
                        
                        // Обработчик для правого клика (контекстное меню)
                        item.addEventListener('contextmenu', function(e) {
                            e.preventDefault();
                            const img = this.querySelector('img');
                            const imageName = this.querySelector('.main-image-name').textContent;
                            showImagePreview(img.src, imageName);
                        });
                        
                        // Обработчик для кнопки предварительного просмотра
                        const previewBtn = item.querySelector('.main-image-preview-btn');
                        if (previewBtn) {
                            previewBtn.addEventListener('click', function(e) {
                                e.preventDefault();
                                e.stopPropagation();
                                const img = item.querySelector('img');
                                const imageName = item.querySelector('.main-image-name').textContent;
                                showImagePreview(img.src, imageName);
                            });
                            
                            // Предотвращаем всплытие событий от кнопки
                            previewBtn.addEventListener('mousedown', function(e) {
                                e.stopPropagation();
                            });
                        }
                    });
                    
                    // Автоматически выбираем первое изображение как главное, если ничего не выбрано
                    if (!mainImgHidden.value && images.length > 0) {
                        const firstImage = document.querySelector('.main-image-item');
                        if (firstImage) {
                            firstImage.classList.add('selected');
                            mainImgHidden.value = firstImage.dataset.mediaId;
                        }
                    }
                }
                
                data.forEach(media => {
                    html += `
                        <div class="product-media-item" data-media-id="${media.id}">
                            <input type="checkbox" name="selected_product_media[]" value="${media.id}" class="media-checkbox">
                            ${media.file_type === 'image' 
                                ? `<img src="${media.full_url}" alt="${media.file_name}">`
                                : `<video src="${media.full_url}" muted></video>`
                            }
                            <span class="media-type-badge">${media.file_type === 'image' ? 'Фото' : 'Видео'}</span>
                            ${media.file_type === 'image' ? `
                                <button class="product-media-preview-btn" title="Предварительный просмотр">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                        <circle cx="12" cy="12" r="3"></circle>
                                    </svg>
                                </button>
                            ` : ''}
                            <div class="media-info">
                                <div class="media-name">${media.file_name}</div>
                                <div class="media-size">${media.formatted_size}</div>
                            </div>
                        </div>
                    `;
                });

                mediaGrid.innerHTML = html;

                // Добавляем обработчики для выбора медиафайлов
                document.querySelectorAll('.product-media-item').forEach(item => {
                    item.addEventListener('click', function(e) {
                        if (e.target.type !== 'checkbox') {
                            const checkbox = this.querySelector('.media-checkbox');
                            checkbox.checked = !checkbox.checked;
                        }
                        this.classList.toggle('selected', this.querySelector('.media-checkbox').checked);
                    });
                    
                    // Добавляем обработчики для предварительного просмотра изображений
                    const previewBtn = item.querySelector('.product-media-preview-btn');
                    if (previewBtn) {
                        previewBtn.addEventListener('click', function(e) {
                            e.preventDefault();
                            e.stopPropagation();
                            const img = item.querySelector('img');
                            const imageName = item.querySelector('.media-name').textContent;
                            showImagePreview(img.src, imageName);
                        });
                        
                        // Предотвращаем всплытие событий от кнопки
                        previewBtn.addEventListener('mousedown', function(e) {
                            e.stopPropagation();
                        });
                    }
                    
                    // Отдельный обработчик для предварительного просмотра по двойному клику (только для изображений)
                    const img = item.querySelector('img');
                    if (img) {
                        item.addEventListener('dblclick', function(e) {
                            e.preventDefault();
                            e.stopPropagation();
                            const imageName = this.querySelector('.media-name').textContent;
                            showImagePreview(img.src, imageName);
                        });
                        
                        // Обработчик для правого клика (контекстное меню)
                        item.addEventListener('contextmenu', function(e) {
                            e.preventDefault();
                            const imageName = this.querySelector('.media-name').textContent;
                            showImagePreview(img.src, imageName);
                        });
                    }
                });
            })
            .catch(error => {
                console.error('Error:', error);
                mediaGrid.innerHTML = '<div class="no-media-message">Ошибка загрузки медиафайлов</div>';
                
                // Очищаем селектор главного изображения при ошибке
                const mainImageGrid = document.getElementById('mainImageGrid');
                const noImagesMessage = document.getElementById('noImagesMessage');
                const mainImgHidden = document.getElementById('main_img');
                
                mainImageGrid.style.display = 'none';
                noImagesMessage.style.display = 'block';
                mainImgHidden.value = '';
            });
    }

    // Обработка загрузки файлов (аналогично ProductCreatePage)
    const fileInput = document.getElementById('media_files');
    const fileUploadArea = document.getElementById('fileUploadArea');
    const filePreview = document.getElementById('filePreview');
    let selectedFiles = [];

    // Аналогичная логика для обработки файлов как в ProductCreatePage
    fileUploadArea.addEventListener('click', function() {
        fileInput.click();
    });

    fileInput.addEventListener('change', function(e) {
        handleFiles(e.target.files);
    });

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
        Array.from(files).forEach(file => {
            if (validateFile(file)) {
                selectedFiles.push(file);
                addFilePreview(file);
            }
        });
        updateFileInput();
    }

    function validateFile(file) {
        const maxSize = 50 * 1024 * 1024; // 50MB
        const allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'video/mp4', 'video/quicktime', 'video/x-msvideo'];
        
        if (file.size > maxSize) {
            alert('Файл слишком большой. Максимальный размер: 50MB');
            return false;
        }
        
        if (!allowedTypes.includes(file.type)) {
            alert('Неподдерживаемый формат файла');
            return false;
        }
        
        return true;
    }

    function addFilePreview(file) {
        const previewItem = document.createElement('div');
        previewItem.className = 'file-preview-item';
        
        const fileInfo = document.createElement('div');
        fileInfo.className = 'file-info';
        
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
    
    // Инициализация CKEditor для полей с разметкой
    initializeEditors();
    
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
    
    // Обработка отправки формы - синхронизация данных редакторов
    document.getElementById('advertisementForm').addEventListener('submit', function(e) {
        // Синхронизируем данные из редакторов в скрытые textarea
        if (technicalEditor) {
            document.getElementById('technical_characteristics').value = technicalEditor.root.innerHTML;
        }
        if (mainInfoEditor) {
            document.getElementById('main_info').value = mainInfoEditor.root.innerHTML;
        }
        if (additionalInfoEditor) {
            document.getElementById('additional_info').value = additionalInfoEditor.root.innerHTML;
        }
    });
});

// Функция инициализации Quill.js редакторов
function initializeEditors() {
    // Конфигурация редактора
    const toolbarOptions = [
        [{ 'header': [1, 2, 3, false] }],
        ['bold', 'italic', 'underline', 'strike'],
        [{ 'list': 'ordered'}, { 'list': 'bullet' }],
        [{ 'indent': '-1'}, { 'indent': '+1' }],
        ['link', 'blockquote'],
        ['clean']
    ];

    // Инициализация редактора технических характеристик
    const technicalEditorElement = document.getElementById('technical_characteristics_editor');
    if (technicalEditorElement) {
        technicalEditor = new Quill(technicalEditorElement, {
            theme: 'snow',
            modules: {
                toolbar: toolbarOptions
            },
            placeholder: 'Введите технические характеристики...'
        });
        
        // Устанавливаем начальное значение из скрытого textarea
        const initialValue = document.getElementById('technical_characteristics').value;
        if (initialValue) {
            technicalEditor.root.innerHTML = initialValue;
        }
        
        // Синхронизируем изменения обратно в скрытое поле
        technicalEditor.on('text-change', function() {
            document.getElementById('technical_characteristics').value = technicalEditor.root.innerHTML;
        });
    }

    // Инициализация редактора основной информации
    const mainInfoEditorElement = document.getElementById('main_info_editor');
    if (mainInfoEditorElement) {
        mainInfoEditor = new Quill(mainInfoEditorElement, {
            theme: 'snow',
            modules: {
                toolbar: toolbarOptions
            },
            placeholder: 'Введите основную информацию...'
        });
        
        // Устанавливаем начальное значение из скрытого textarea
        const initialValue = document.getElementById('main_info').value;
        if (initialValue) {
            mainInfoEditor.root.innerHTML = initialValue;
        }
        
        // Синхронизируем изменения обратно в скрытое поле
        mainInfoEditor.on('text-change', function() {
            document.getElementById('main_info').value = mainInfoEditor.root.innerHTML;
        });
    }

    // Инициализация редактора дополнительной информации
    const additionalInfoEditorElement = document.getElementById('additional_info_editor');
    if (additionalInfoEditorElement) {
        additionalInfoEditor = new Quill(additionalInfoEditorElement, {
            theme: 'snow',
            modules: {
                toolbar: toolbarOptions
            },
            placeholder: 'Введите дополнительную информацию...'
        });
        
        // Устанавливаем начальное значение из скрытого textarea
        const initialValue = document.getElementById('additional_info').value;
        if (initialValue) {
            additionalInfoEditor.root.innerHTML = initialValue;
        }
        
        // Синхронизируем изменения обратно в скрытое поле
        additionalInfoEditor.on('text-change', function() {
            document.getElementById('additional_info').value = additionalInfoEditor.root.innerHTML;
        });
    }
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
    
    // Делаем функцию selectNode глобальной для категории
    if (treeselectId === 'category_treeselect') {
        window.selectCategoryNode = selectNode;
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

// Инициализация select с поиском для товаров
document.addEventListener('DOMContentLoaded', function() {
    initializeProductSelect();
});

// Функция инициализации select с поиском для товаров
function initializeProductSelect() {
    const selectInput = document.getElementById('product_select');
    const selectDropdown = document.getElementById('product_select_dropdown');
    const selectOptions = document.getElementById('product_select_options');
    const selectSearch = document.getElementById('product_select_search');
    const hiddenSelect = document.getElementById('product_id');
    
    if (!selectInput || !selectDropdown || !selectOptions || !hiddenSelect) return;
    
    // Получаем данные товаров из скрытого select
    const products = [];
    const options = hiddenSelect.querySelectorAll('option');
    options.forEach(option => {
        if (option.value) {
            products.push({
                id: option.value,
                name: option.textContent.trim()
            });
        }
    });
    
    let isOpen = false;
    let selectedValue = hiddenSelect.value;
    
    // Обновляем опции
    function updateOptions(filteredProducts = null) {
        const productsToUse = filteredProducts || products;
        
        let html = '';
        productsToUse.forEach(product => {
            const isSelected = selectedValue && selectedValue.toString() === product.id.toString();
            html += `<div class="select-option ${isSelected ? 'selected' : ''}" data-value="${product.id}">
                <span>${product.name}</span>
            </div>`;
        });
        
        selectOptions.innerHTML = html;
        attachOptionEvents();
    }
    
    // Привязываем события к опциям
    function attachOptionEvents() {
        const optionElements = selectOptions.querySelectorAll('.select-option');
        optionElements.forEach(option => {
            option.addEventListener('click', function(e) {
                e.stopPropagation();
                const value = this.dataset.value;
                const productData = products.find(prod => prod.id.toString() === value);
                
                selectedValue = value;
                updateSelectedDisplay(productData);
                updateHiddenSelect();
                closeDropdown();
                
                // Триггерим событие изменения для совместимости с существующим кодом
                const event = new Event('change', { bubbles: true });
                hiddenSelect.dispatchEvent(event);
            });
        });
    }
    
    // Обновляем отображение выбранного значения
    function updateSelectedDisplay(productData) {
        const placeholder = selectInput.querySelector('.select-placeholder');
        const valueElement = selectInput.querySelector('.select-value');
        
        if (!productData) {
            placeholder.style.display = 'block';
            if (valueElement) {
                valueElement.style.display = 'none';
            }
            return;
        }
        
        placeholder.style.display = 'none';
        
        if (!valueElement) {
            const newValueElement = document.createElement('span');
            newValueElement.className = 'select-value';
            selectInput.insertBefore(newValueElement, selectInput.querySelector('.select-arrow'));
        }
        
        const currentValueElement = selectInput.querySelector('.select-value');
        currentValueElement.textContent = productData.name;
        currentValueElement.style.display = 'block';
    }
    
    // Обновляем скрытый select
    function updateHiddenSelect() {
        // Очищаем все опции
        hiddenSelect.innerHTML = '';
        
        if (selectedValue) {
            const option = document.createElement('option');
            option.value = selectedValue;
            option.selected = true;
            option.textContent = products.find(prod => prod.id.toString() === selectedValue.toString())?.name || '';
            hiddenSelect.appendChild(option);
        }
    }
    
    // Открытие/закрытие выпадающего списка
    function toggleDropdown() {
        isOpen = !isOpen;
        
        if (isOpen) {
            selectInput.classList.add('active');
            selectDropdown.classList.add('active');
            updateOptions();
            selectSearch.focus();
        } else {
            closeDropdown();
        }
    }
    
    function closeDropdown() {
        isOpen = false;
        selectInput.classList.remove('active');
        selectDropdown.classList.remove('active');
    }
    
    // Фильтрация
    function filterOptions(searchTerm) {
        if (!searchTerm) {
            updateOptions();
            return;
        }
        
        const searchTermLower = searchTerm.toLowerCase();
        const filteredProducts = products.filter(product => 
            product.name.toLowerCase().includes(searchTermLower)
        );
        
        updateOptions(filteredProducts);
    }
    
    // События
    selectInput.addEventListener('click', toggleDropdown);
    
    selectInput.addEventListener('keydown', function(e) {
        if (e.key === 'Enter' || e.key === ' ' || e.key === 'ArrowDown') {
            e.preventDefault();
            if (!isOpen) {
                toggleDropdown();
            }
        } else if (e.key === 'Escape') {
            closeDropdown();
        }
    });
    
    selectSearch.addEventListener('input', function() {
        filterOptions(this.value);
    });
    
    selectSearch.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeDropdown();
        }
    });
    
    // Закрытие при клике вне
    document.addEventListener('click', function(e) {
        if (!selectInput.contains(e.target) && !selectDropdown.contains(e.target)) {
            closeDropdown();
        }
    });
    
    // Инициализация с текущими значениями
    if (selectedValue) {
        const productData = products.find(prod => prod.id.toString() === selectedValue.toString());
        if (productData) {
            updateSelectedDisplay(productData);
            updateHiddenSelect();
        }
    }
}

// Функция для показа уведомлений (аналогично ProductItemPage)
function showNotification(message, type = 'info') {
    // Создаем уведомление
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    notification.textContent = message;
    
    // Добавляем стили
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        padding: 15px 20px;
        border-radius: 6px;
        color: white;
        font-weight: 500;
        z-index: 3000;
        animation: slideIn 0.3s ease;
        max-width: 300px;
    `;
    
    // Цвета для разных типов уведомлений
    if (type === 'success') {
        notification.style.backgroundColor = '#28a745';
    } else if (type === 'error') {
        notification.style.backgroundColor = '#dc3545';
    } else {
        notification.style.backgroundColor = '#17a2b8';
    }
    
    document.body.appendChild(notification);
    
    // Удаляем уведомление через 3 секунды
    setTimeout(() => {
        notification.style.animation = 'slideOut 0.3s ease';
        setTimeout(() => {
            if (notification.parentNode) {
                notification.parentNode.removeChild(notification);
            }
        }, 300);
    }, 3000);
}

// Функция для показа предварительного просмотра изображения
function showImagePreview(imageSrc, imageName) {
    const modal = document.getElementById('imagePreviewModal');
    const modalImg = document.getElementById('imagePreviewImg');
    const modalTitle = document.getElementById('imagePreviewTitle');
    
    modalImg.src = imageSrc;
    modalTitle.textContent = `Предварительный просмотр: ${imageName}`;
    modal.classList.add('active');
    
    // Блокируем прокрутку страницы
    document.body.style.overflow = 'hidden';
}

// Функция для закрытия предварительного просмотра
function closeImagePreview() {
    const modal = document.getElementById('imagePreviewModal');
    modal.classList.remove('active');
    
    // Восстанавливаем прокрутку страницы
    document.body.style.overflow = '';
}

// Обработчики для модального окна предварительного просмотра
document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('imagePreviewModal');
    const closeBtn = document.getElementById('imagePreviewClose');
    
    if (modal && closeBtn) {
        // Закрытие по кнопке
        closeBtn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            closeImagePreview();
        });
        
        // Закрытие по клику вне модального окна
        modal.addEventListener('click', function(e) {
            if (e.target === modal) {
                closeImagePreview();
            }
        });
        
        // Закрытие по клавише Escape
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && modal.classList.contains('active')) {
                closeImagePreview();
            }
        });
    }
});

// Добавляем CSS анимации для уведомлений
const notificationStyle = document.createElement('style');
notificationStyle.textContent = `
    @keyframes slideIn {
        from {
            transform: translateX(100%);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }
    
    @keyframes slideOut {
        from {
            transform: translateX(0);
            opacity: 1;
        }
        to {
            transform: translateX(100%);
            opacity: 0;
        }
    }
`;
document.head.appendChild(notificationStyle);
</script>
@endsection 