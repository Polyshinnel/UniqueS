@extends('layouts.layout')

@php
use App\Models\ProductCategories;
@endphp

@section('title', 'Категории')

@section('header-action-btn')
    <button class="btn btn-primary" onclick="openModal()">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <line x1="12" y1="5" x2="12" y2="19"></line>
            <line x1="5" y1="12" x2="19" y2="12"></line>
        </svg>
        Добавить категорию
    </button>
@endsection

@section('header-title')
    <h1 class="header-title">Категории</h1>
@endsection

@section('content')
<div class="categories-container">
    <div class="breadcrumb">
        <a href="/">Главная</a> / <a href="/guide">Справочники</a> / Категории
    </div>
    @if(session('success'))
        <div class="alert alert-success">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M20 7L10 17L5 12"></path>
            </svg>
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="12" cy="12" r="10"></circle>
                <line x1="15" y1="9" x2="9" y2="15"></line>
                <line x1="9" y1="9" x2="15" y2="15"></line>
            </svg>
            {{ session('error') }}
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="12" cy="12" r="10"></circle>
                <line x1="15" y1="9" x2="9" y2="15"></line>
                <line x1="9" y1="9" x2="15" y2="15"></line>
            </svg>
            <ul>
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="categories-table-wrapper">
        <table class="categories-table">
            <thead>
                <tr>
                    <th>Название категории</th>
                    <th>Родительская категория</th>
                    <th>Статус</th>
                    <th>Действия</th>
                </tr>
            </thead>
            <tbody>
                @forelse($tree as $category)
                <tr class="category-row" data-level="{{ $category->level }}">
                    <td class="category-info-cell">
                        <div class="category-info">
                            <div class="category-name" style="padding-left: {{ $category->level * 20 }}px">
                                @if(ProductCategories::where('parent_id', $category->id)->exists())
                                    <span class="toggle-category" data-category-id="{{ $category->id }}">▼</span>
                                @else
                                    <span style="margin-left: 16px"></span>
                                @endif
                                {{ $category->name }}
                            </div>
                            <div class="category-id">ID: {{ $category->id }}</div>
                        </div>
                    </td>
                    <td class="parent-cell">
                        <div class="parent-info">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
                                <polyline points="9,22 9,12 15,12 15,22"></polyline>
                            </svg>
                            <span>
                                @if($category->parent_id == 0)
                                    Без категории
                                @else
                                    @php
                                        $parent = $categories->firstWhere('id', $category->parent_id);
                                    @endphp
                                    {{ $parent ? $parent->name : 'Неизвестно' }}
                                @endif
                            </span>
                        </div>
                    </td>
                    <td class="status-cell">
                        @if($category->active)
                        <div class="status-badge status-active">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M20 7L10 17L5 12"></path>
                            </svg>
                            Активна
                        </div>
                        @else
                        <div class="status-badge status-inactive">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="12" cy="12" r="10"></circle>
                                <line x1="15" y1="9" x2="9" y2="15"></line>
                                <line x1="9" y1="9" x2="15" y2="15"></line>
                            </svg>
                            Не активна
                        </div>
                        @endif
                    </td>
                    <td class="actions-cell">
                        <div class="action-buttons">
                            <button class="action-btn edit-btn" onclick="openEditModal({{ $category->id }})" title="Редактировать" type="button">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                    <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                                </svg>
                            </button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="empty-state">
                        <div class="empty-content">
                            <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
                                <polyline points="9,22 9,12 15,12 15,22"></polyline>
                            </svg>
                            <p>Категории не найдены</p>
                            <button class="btn btn-primary" onclick="openModal()">Добавить первую категорию</button>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Модальное окно для добавления категории -->
<div id="categoryModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Добавление категории</h3>
            <button class="close-btn" onclick="closeModal()">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
            </button>
        </div>
        <div class="modal-body">
            <form action="{{ route('categories.store') }}" method="POST" class="category-form">
                @csrf
                <div class="form-group">
                    <label for="name">Название категории *</label>
                    <input type="text" name="name" id="name" value="{{ old('name') }}" class="form-control @error('name') is-invalid @enderror" required>
                    @error('name')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>
                <div class="form-group">
                    <label for="parent_id">Родительская категория *</label>
                    <div class="treeselect-wrapper">
                        <div class="treeselect-input" id="parent_treeselect" tabindex="0">
                            <span class="treeselect-placeholder">Выберите родительскую категорию</span>
                            <svg class="treeselect-arrow" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polyline points="6,9 12,15 18,9"></polyline>
                            </svg>
                        </div>
                        <div class="treeselect-dropdown" id="parent_treeselect_dropdown">
                            <div class="treeselect-search">
                                <input type="text" id="parent_treeselect_search" placeholder="Поиск категорий..." class="treeselect-search-input">
                            </div>
                            <div class="treeselect-tree" id="parent_treeselect_tree">
                                <!-- Дерево будет заполнено JavaScript -->
                            </div>
                        </div>
                        <select name="parent_id" id="parent_id" class="form-control @error('parent_id') is-invalid @enderror" required style="display: none;">
                            <option value="0" {{ old('parent_id') == '0' ? 'selected' : '' }}>Без категории</option>
                            @foreach($activeCategories as $category)
                                <option value="{{ $category->id }}" {{ old('parent_id') == $category->id ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    @error('parent_id')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>
                <div class="form-group">
                    <label for="active">Статус *</label>
                    <select name="active" id="active" class="form-control @error('active') is-invalid @enderror" required>
                        <option value="1" {{ old('active') == '1' ? 'selected' : '' }}>Активна</option>
                        <option value="0" {{ old('active') == '0' ? 'selected' : '' }}>Не активна</option>
                    </select>
                    @error('active')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M20 7L10 17L5 12"></path>
                        </svg>
                        Добавить категорию
                    </button>
                    <button type="button" class="btn btn-secondary" onclick="closeModal()">Отмена</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Модальное окно для редактирования категории -->
<div id="editCategoryModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Редактирование категории</h3>
            <button class="close-btn" onclick="closeEditModal()">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
            </button>
        </div>
        <div class="modal-body">
            <form id="editCategoryForm" method="POST" class="category-form">
                @csrf
                @method('PUT')
                <div class="form-group">
                    <label for="edit_name">Название категории *</label>
                    <input type="text" name="name" id="edit_name" class="form-control" required>
                    <span class="error-message" id="edit_name_error" style="display: none;"></span>
                </div>
                <div class="form-group">
                    <label for="edit_parent_id">Родительская категория *</label>
                    <div class="treeselect-wrapper">
                        <div class="treeselect-input" id="edit_parent_treeselect" tabindex="0">
                            <span class="treeselect-placeholder">Выберите родительскую категорию</span>
                            <svg class="treeselect-arrow" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polyline points="6,9 12,15 18,9"></polyline>
                            </svg>
                        </div>
                        <div class="treeselect-dropdown" id="edit_parent_treeselect_dropdown">
                            <div class="treeselect-search">
                                <input type="text" id="edit_parent_treeselect_search" placeholder="Поиск категорий..." class="treeselect-search-input">
                            </div>
                            <div class="treeselect-tree" id="edit_parent_treeselect_tree">
                                <!-- Дерево будет заполнено JavaScript -->
                            </div>
                        </div>
                        <select name="parent_id" id="edit_parent_id" class="form-control" required style="display: none;">
                            <option value="0">Без категории</option>
                            @foreach($activeCategories as $category)
                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <span class="error-message" id="edit_parent_id_error" style="display: none;"></span>
                </div>
                <div class="form-group">
                    <label for="edit_active">Статус *</label>
                    <select name="active" id="edit_active" class="form-control" required>
                        <option value="1">Активна</option>
                        <option value="0">Не активна</option>
                    </select>
                    <span class="error-message" id="edit_active_error" style="display: none;"></span>
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M20 7L10 17L5 12"></path>
                        </svg>
                        Сохранить изменения
                    </button>
                    <button type="button" class="btn btn-secondary" onclick="closeEditModal()">Отмена</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.categories-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 20px;
}

.breadcrumb {
    margin-bottom: 20px;
    font-size: 14px;
    color: #666;
}

.breadcrumb a {
    color: #133E71;
    text-decoration: none;
    transition: color 0.3s ease;
}

.breadcrumb a:hover {
    color: #1C5BA4;
    text-decoration: underline;
}

.categories-table-wrapper {
    background: white;
    border-radius: 12px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.1);
    overflow: hidden;
}

.categories-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 14px;
}

.categories-table th {
    background: linear-gradient(180deg, #133E71 0%, #1C5BA4 100%);
    color: white;
    padding: 16px 12px;
    text-align: left;
    font-weight: 600;
    font-size: 13px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    border-bottom: 2px solid #0f2d56;
}

.categories-table th:first-child {
    padding-left: 20px;
}

.categories-table th:last-child {
    padding-right: 20px;
}

.category-row {
    border-bottom: 1px solid #f1f3f4;
    transition: all 0.3s ease;
}

.category-row:hover {
    background-color: #f8f9fa;
    transform: translateY(-1px);
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.category-row:last-child {
    border-bottom: none;
}

.categories-table td {
    padding: 16px 12px;
    vertical-align: middle;
}

.categories-table td:first-child {
    padding-left: 20px;
}

.categories-table td:last-child {
    padding-right: 20px;
}

/* Стили для ячейки с информацией о категории */
.category-info-cell {
    min-width: 250px;
}

.category-info {
    display: flex;
    flex-direction: column;
    gap: 6px;
}

.category-name {
    font-size: 16px;
    font-weight: 600;
    color: #133E71;
    line-height: 1.3;
    display: flex;
    align-items: center;
    gap: 8px;
}

.toggle-category {
    cursor: pointer;
    color: #133E71;
    font-weight: bold;
    transition: color 0.3s ease;
}

.toggle-category:hover {
    color: #1C5BA4;
}

.category-id {
    font-size: 12px;
    color: #666;
    font-weight: 500;
    background: #f8f9fa;
    padding: 4px 8px;
    border-radius: 4px;
    display: inline-block;
    width: fit-content;
}

/* Стили для ячейки родительской категории */
.parent-cell {
    min-width: 180px;
}

.parent-info {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 10px;
    background: #f8f9fa;
    border-radius: 6px;
    border: 1px solid #e9ecef;
}

.parent-info svg {
    color: #133E71;
    flex-shrink: 0;
}

.parent-info span {
    font-size: 14px;
    color: #333;
    font-weight: 500;
}

/* Стили для ячейки статуса */
.status-cell {
    min-width: 120px;
}

.status-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 8px 12px;
    border-radius: 20px;
    font-weight: 600;
    font-size: 12px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.status-active {
    background-color: #e8f5e8;
    color: #388e3c;
}

.status-inactive {
    background-color: #ffebee;
    color: #d32f2f;
}

.status-badge svg {
    width: 12px;
    height: 12px;
}

/* Стили для ячейки действий */
.actions-cell {
    min-width: 100px;
}

.action-buttons {
    display: flex;
    gap: 8px;
    justify-content: flex-start;
}

.action-btn {
    width: 36px;
    height: 36px;
    border-radius: 8px;
    border: none;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.3s ease;
    background: #f8f9fa;
    color: #666;
}

.action-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}

.edit-btn:hover {
    background: #e3f2fd;
    color: #1976d2;
}



.action-btn svg {
    width: 16px;
    height: 16px;
}

/* Стили для пустого состояния */
.empty-state {
    text-align: center;
    padding: 60px 20px !important;
}

.empty-content {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 16px;
    color: #666;
}

.empty-content svg {
    color: #ccc;
}

.empty-content p {
    font-size: 16px;
    margin: 0;
}

/* Стили для уведомлений */
.alert {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 16px 20px;
    border-radius: 8px;
    margin-bottom: 20px;
    font-weight: 500;
}

.alert-success {
    background-color: #e8f5e8;
    color: #2e7d32;
    border: 1px solid #c8e6c9;
}

.alert-danger {
    background-color: #ffebee;
    color: #c62828;
    border: 1px solid #ffcdd2;
}

.alert ul {
    margin: 8px 0 0 0;
    padding-left: 20px;
}

.alert li {
    margin-bottom: 4px;
}

/* Стили для модального окна */
.modal {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1000;
    opacity: 0;
    visibility: hidden;
    transition: all 0.3s ease;
}

.modal.active {
    opacity: 1;
    visibility: visible;
}

.modal-content {
    background: white;
    border-radius: 12px;
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
    width: 90%;
    max-width: 500px;
    max-height: 90vh;
    overflow-y: auto;
    transform: scale(0.9);
    transition: transform 0.3s ease;
}

.modal.active .modal-content {
    transform: scale(1);
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 20px 25px;
    border-bottom: 1px solid #e9ecef;
    background: #f8f9fa;
    border-radius: 12px 12px 0 0;
}

.modal-header h3 {
    margin: 0;
    color: #133E71;
    font-size: 18px;
    font-weight: 600;
}

.close-btn {
    background: none;
    border: none;
    cursor: pointer;
    padding: 8px;
    border-radius: 50%;
    transition: background-color 0.3s ease;
    display: flex;
    align-items: center;
    justify-content: center;
}

.close-btn:hover {
    background-color: #e9ecef;
}

.close-btn svg {
    color: #666;
}

.modal-body {
    padding: 25px;
}

.category-form {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.form-group {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.form-group label {
    font-weight: 600;
    color: #495057;
    font-size: 14px;
}

.form-control {
    padding: 12px 16px;
    border: 2px solid #e9ecef;
    border-radius: 8px;
    font-size: 14px;
    transition: all 0.3s ease;
    background: white;
}

.form-control:focus {
    outline: none;
    border-color: #133E71;
    box-shadow: 0 0 0 3px rgba(19, 62, 113, 0.1);
}

.form-control.is-invalid {
    border-color: #d32f2f;
    box-shadow: 0 0 0 3px rgba(211, 47, 47, 0.1);
}

.form-control.is-invalid:focus {
    border-color: #d32f2f;
    box-shadow: 0 0 0 3px rgba(211, 47, 47, 0.2);
}

.error-message {
    color: #d32f2f;
    font-size: 12px;
    font-weight: 500;
    margin-top: 4px;
    display: block;
}

.form-actions {
    display: flex;
    gap: 12px;
    margin-top: 20px;
}

/* Стили для кнопок */
.btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 12px 20px;
    border: none;
    border-radius: 8px;
    font-size: 14px;
    font-weight: 500;
    text-decoration: none;
    cursor: pointer;
    transition: all 0.3s ease;
    text-align: center;
    justify-content: center;
}

.btn-primary {
    background: #133E71;
    color: white;
    box-shadow: 0 2px 8px rgba(19, 62, 113, 0.3);
}

.btn-primary:hover {
    background: #1C5BA4;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(19, 62, 113, 0.4);
}

.btn-secondary {
    background: #6c757d;
    color: white;
}

.btn-secondary:hover {
    background: #5a6268;
    transform: translateY(-2px);
}

.btn svg {
    width: 16px;
    height: 16px;
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

/* Адаптивность */
@media (max-width: 768px) {
    .categories-container {
        padding: 10px;
    }
    
    .categories-table-wrapper {
        border-radius: 8px;
    }
    
    .categories-table {
        font-size: 12px;
    }
    
    .categories-table th,
    .categories-table td {
        padding: 10px 6px;
    }
    
    .category-name {
        font-size: 14px;
    }
    
    .action-btn {
        width: 32px;
        height: 32px;
    }
    
    .modal-content {
        width: 95%;
        margin: 20px;
    }
}

/* Анимации */
@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.category-row {
    animation: fadeIn 0.3s ease;
}
</style>
@endpush

@push('scripts')
<script>
function openModal() {
    document.getElementById('categoryModal').classList.add('active');
    document.body.style.overflow = 'hidden';
}

function closeModal() {
    document.getElementById('categoryModal').classList.remove('active');
    document.body.style.overflow = '';
    resetForm();
}

// Закрытие модального окна по Escape
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        closeModal();
        closeEditModal();
    }
});

// Закрытие модального окна при клике вне его
document.addEventListener('click', function(event) {
    const modal = document.getElementById('categoryModal');
    const editModal = document.getElementById('editCategoryModal');
    if (event.target === modal) {
        closeModal();
    }
    if (event.target === editModal) {
        closeEditModal();
    }
});

// Обработка успешного добавления/обновления категории
document.addEventListener('DOMContentLoaded', function() {
    @if(session('success'))
        setTimeout(() => {
            closeModal();
            closeEditModal();
        }, 1000);
    @endif
    
    // Инициализация TreeSelect
    initializeTreeSelect('parent_treeselect', 'parent_id', @json($activeCategories));
    initializeTreeSelect('edit_parent_treeselect', 'edit_parent_id', @json($activeCategories));
    
    // Обработка сворачивания/разворачивания категорий
    const toggleButtons = document.querySelectorAll('.toggle-category');
    
    toggleButtons.forEach(button => {
        button.addEventListener('click', function() {
            const categoryId = this.dataset.categoryId;
            const rows = document.querySelectorAll('.category-row');
            let isExpanded = this.textContent === '▼';
            
            this.textContent = isExpanded ? '▶' : '▼';
            
            let foundParent = false;
            let level = 0;
            
            rows.forEach(row => {
                if (row.dataset.categoryId === categoryId) {
                    foundParent = true;
                    level = parseInt(row.dataset.level);
                    return;
                }
                
                if (foundParent) {
                    const rowLevel = parseInt(row.dataset.level);
                    if (rowLevel <= level) {
                        foundParent = false;
                        return;
                    }
                    
                    row.style.display = isExpanded ? 'none' : 'table-row';
                }
            });
        });
    });
});

// Функция для очистки формы
function resetForm() {
    const form = document.querySelector('#categoryModal form');
    form.reset();
    const errorMessages = document.querySelectorAll('#categoryModal .error-message');
    errorMessages.forEach(error => error.remove());
    const errorInputs = document.querySelectorAll('#categoryModal .form-control.is-invalid');
    errorInputs.forEach(input => input.classList.remove('is-invalid'));
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
    
    // Создаем HTML для узла дерева
    function createNodeHTML(node, level = 0) {
        const hasChildren = node.children && node.children.length > 0;
        const indent = level * 20;
        
        let html = `<div class="treeselect-node" data-id="${node.id}" data-level="${level}" tabindex="0">`;
        
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
        html += `<div class="treeselect-label">${node.name}</div>`;
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
        
        // Добавляем опцию "Без категории"
        html += `<div class="treeselect-node" data-id="0" data-level="0" tabindex="0">
            <div class="treeselect-indent" style="width: 0px;"></div>
            <div class="treeselect-indent" style="width: 16px;"></div>
            <div class="treeselect-label">Без категории</div>
        </div>`;
        
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
            node.addEventListener('click', function(e) {
                e.stopPropagation();
                const nodeId = this.dataset.id;
                const nodeName = this.querySelector('.treeselect-label').textContent;
                selectNode(nodeId, nodeName);
            });
            
            node.addEventListener('keydown', function(e) {
                if (e.key === 'Enter' || e.key === ' ') {
                    e.preventDefault();
                    const nodeId = this.dataset.id;
                    const nodeName = this.querySelector('.treeselect-label').textContent;
                    selectNode(nodeId, nodeName);
                }
            });
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
        
        value.textContent = nodeName;
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
        
        // Фильтруем категории, которые содержат поисковый запрос
        const filteredCategories = categories.filter(category => 
            category.name.toLowerCase().includes(searchTermLower)
        );
        
        // Создаем плоский список отфильтрованных категорий для отображения
        let html = '';
        
        // Добавляем опцию "Без категории"
        html += `<div class="treeselect-node" data-id="0" data-level="0" tabindex="0">
            <div class="treeselect-indent" style="width: 0px;"></div>
            <div class="treeselect-indent" style="width: 16px;"></div>
            <div class="treeselect-label">Без категории</div>
        </div>`;
        
        // Добавляем отфильтрованные категории
        filteredCategories.forEach(category => {
            // Подсвечиваем найденный текст
            const highlightedName = category.name.replace(
                new RegExp(searchTerm, 'gi'),
                match => `<mark style="background-color: #ffeb3b; padding: 1px 2px; border-radius: 2px;">${match}</mark>`
            );
            
            html += `<div class="treeselect-node" data-id="${category.id}" data-level="0" tabindex="0">
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
    if (hiddenSelect.value && hiddenSelect.value !== '0') {
        const selectedOption = hiddenSelect.options[hiddenSelect.selectedIndex];
        if (selectedOption) {
            selectNode(selectedOption.value, selectedOption.textContent);
        }
    } else {
        // По умолчанию выбираем "Без категории"
        selectNode('0', 'Без категории');
    }
}

// Функции для модального окна редактирования
function openEditModal(categoryId) {
    // Загружаем данные категории
    fetch(`/guide/categories/${categoryId}/edit`)
        .then(response => response.json())
        .then(category => {
            // Заполняем форму данными
            document.getElementById('edit_name').value = category.name;
            document.getElementById('edit_parent_id').value = category.parent_id;
            document.getElementById('edit_active').value = category.active ? '1' : '0';
            
            // Обновляем TreeSelect с активными категориями
            const editParentSelect = document.getElementById('edit_parent_id');
            editParentSelect.innerHTML = '<option value="0">Без категории</option>';
            
            if (category.activeCategories) {
                category.activeCategories.forEach(activeCategory => {
                    const option = document.createElement('option');
                    option.value = activeCategory.id;
                    option.textContent = activeCategory.name;
                    if (activeCategory.id == category.parent_id) {
                        option.selected = true;
                    }
                    editParentSelect.appendChild(option);
                });
            }
            
            // Обновляем TreeSelect и устанавливаем выбранное значение
            initializeTreeSelect('edit_parent_treeselect', 'edit_parent_id', category.activeCategories);
            
            // Устанавливаем выбранное значение в TreeSelect
            setTimeout(() => {
                const treeselectInput = document.getElementById('edit_parent_treeselect');
                const placeholder = treeselectInput.querySelector('.treeselect-placeholder');
                let value = treeselectInput.querySelector('.treeselect-value');
                
                if (category.parent_id == '0') {
                    placeholder.style.display = 'none';
                    if (!value) {
                        value = document.createElement('span');
                        value.className = 'treeselect-value';
                        treeselectInput.insertBefore(value, treeselectInput.querySelector('.treeselect-arrow'));
                    }
                    value.textContent = 'Без категории';
                    value.style.display = 'block';
                } else if (category.parent_name) {
                    placeholder.style.display = 'none';
                    if (!value) {
                        value = document.createElement('span');
                        value.className = 'treeselect-value';
                        treeselectInput.insertBefore(value, treeselectInput.querySelector('.treeselect-arrow'));
                    }
                    value.textContent = category.parent_name;
                    value.style.display = 'block';
                }
            }, 100);
            
            // Устанавливаем action для формы
            document.getElementById('editCategoryForm').action = `/guide/categories/${categoryId}`;
            
            // Открываем модальное окно
            document.getElementById('editCategoryModal').classList.add('active');
            document.body.style.overflow = 'hidden';
        })
        .catch(error => {
            console.error('Ошибка загрузки данных категории:', error);
            alert('Ошибка загрузки данных категории');
        });
}

function closeEditModal() {
    document.getElementById('editCategoryModal').classList.remove('active');
    document.body.style.overflow = '';
    resetEditForm();
}

function resetEditForm() {
    const form = document.querySelector('#editCategoryForm');
    form.reset();
    // Очищаем ошибки валидации
    const errorMessages = document.querySelectorAll('#editCategoryModal .error-message');
    errorMessages.forEach(error => {
        error.style.display = 'none';
        error.textContent = '';
    });
    const errorInputs = document.querySelectorAll('#editCategoryModal .form-control.is-invalid');
    errorInputs.forEach(input => input.classList.remove('is-invalid'));
}
</script>
@endpush
