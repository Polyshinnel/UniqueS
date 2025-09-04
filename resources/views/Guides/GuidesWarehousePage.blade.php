@extends('layouts.layout')

@section('title', 'Склады')

@section('header-title')
    <h1 class="header-title">Склады</h1>
@endsection

@section('header-action-btn')
    <button class="btn btn-primary" onclick="openModal()">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <line x1="12" y1="5" x2="12" y2="19"></line>
            <line x1="5" y1="12" x2="19" y2="12"></line>
        </svg>
        Добавить склад
    </button>
@endsection

@section('content')
<div class="warehouses-container">
    <div class="breadcrumb">
        <a href="/">Главная</a> / <a href="/guide">Справочники</a> / Склады
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

    <div class="warehouses-table-wrapper">
        <table class="warehouses-table">
            <thead>
                <tr>
                    <th>Название склада</th>
                    <th>Регионы</th>
                    <th>Статус</th>
                    <th>Действия</th>
                </tr>
            </thead>
            <tbody>
                @forelse($warehouses as $warehouse)
                <tr class="warehouse-row">
                    <td class="warehouse-info-cell">
                        <div class="warehouse-info">
                            <div class="warehouse-name">{{ $warehouse->name }}</div>
                            <div class="warehouse-id">ID: {{ $warehouse->id }}</div>
                        </div>
                    </td>
                    <td class="regions-cell">
                        <div class="regions-list">
                            @if($warehouse->regions->isNotEmpty())
                                @foreach($warehouse->regions as $region)
                                <div class="region-item">
                                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                                        <circle cx="12" cy="10" r="3"></circle>
                                    </svg>
                                    <span>{{ $region->name }}</span>
                                </div>
                                @endforeach
                            @else
                                <div class="no-regions">Регионы не назначены</div>
                            @endif
                        </div>
                    </td>
                    <td class="status-cell">
                        @if($warehouse->active)
                        <div class="status-badge status-active">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M20 7L10 17L5 12"></path>
                            </svg>
                            Активен
                        </div>
                        @else
                        <div class="status-badge status-inactive">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="12" cy="12" r="10"></circle>
                                <line x1="15" y1="9" x2="9" y2="15"></line>
                                <line x1="9" y1="9" x2="15" y2="15"></line>
                            </svg>
                            Не активен
                        </div>
                        @endif
                    </td>
                    <td class="actions-cell">
                        <div class="action-buttons">
                            <button class="action-btn edit-btn" onclick="openEditModal({{ $warehouse->id }})" title="Редактировать">
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
                                <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                                <line x1="9" y1="9" x2="15" y2="9"></line>
                                <line x1="9" y1="12" x2="15" y2="12"></line>
                                <line x1="9" y1="15" x2="15" y2="15"></line>
                            </svg>
                            <p>Склады не найдены</p>
                            <button class="btn btn-primary" onclick="openModal()">Добавить первый склад</button>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Модальное окно для добавления склада -->
<div id="warehouseModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Добавление склада</h3>
            <button class="close-btn" onclick="closeModal()">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
            </button>
        </div>
        <div class="modal-body">
            <form action="{{ route('warehouses.store') }}" method="POST" class="warehouse-form">
                @csrf
                <div class="form-group">
                    <label for="name">Название склада *</label>
                    <input type="text" name="name" id="name" value="{{ old('name') }}" class="form-control @error('name') is-invalid @enderror" required>
                    @error('name')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>
                <div class="form-group">
                    <label for="regions">Регионы *</label>
                    <div class="multiselect-wrapper">
                        <div class="multiselect-input" id="regions_multiselect" tabindex="0">
                            <span class="multiselect-placeholder">Выберите регионы *</span>
                            <svg class="multiselect-arrow" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polyline points="6,9 12,15 18,9"></polyline>
                            </svg>
                        </div>
                        <div class="multiselect-dropdown" id="regions_multiselect_dropdown">
                            <div class="multiselect-search">
                                <input type="text" id="regions_multiselect_search" placeholder="Поиск регионов..." class="multiselect-search-input">
                            </div>
                            <div class="multiselect-options" id="regions_multiselect_options">
                                <!-- Опции будут заполнены JavaScript -->
                            </div>
                        </div>
                        <select name="regions[]" id="regions" class="form-control @error('regions') is-invalid @enderror" multiple style="display: none;">
                            @foreach($regions->where('active', true) as $region)
                                <option value="{{ $region->id }}" {{ in_array($region->id, old('regions', [])) ? 'selected' : '' }}>
                                    {{ $region->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    @error('regions')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>
                <div class="form-group">
                    <label for="active">Статус *</label>
                    <select name="active" id="active" class="form-control @error('active') is-invalid @enderror" required>
                        <option value="1" {{ old('active') == '1' ? 'selected' : '' }}>Активен</option>
                        <option value="0" {{ old('active') == '0' ? 'selected' : '' }}>Не активен</option>
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
                        Добавить склад
                    </button>
                    <button type="button" class="btn btn-secondary" onclick="closeModal()">Отмена</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Модальное окно для редактирования склада -->
<div id="editWarehouseModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Редактирование склада</h3>
            <button class="close-btn" onclick="closeEditModal()">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
            </button>
        </div>
        <div class="modal-body">
            <form id="editWarehouseForm" method="POST" class="warehouse-form">
                @csrf
                @method('PUT')
                <div class="form-group">
                    <label for="edit_name">Название склада *</label>
                    <input type="text" name="name" id="edit_name" class="form-control" required>
                    <span class="error-message" id="edit_name_error" style="display: none;"></span>
                </div>
                <div class="form-group">
                    <label for="edit_regions">Регионы *</label>
                    <div class="multiselect-wrapper">
                        <div class="multiselect-input" id="edit_regions_multiselect" tabindex="0">
                            <span class="multiselect-placeholder">Выберите регионы *</span>
                            <svg class="multiselect-arrow" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polyline points="6,9 12,15 18,9"></polyline>
                            </svg>
                        </div>
                        <div class="multiselect-dropdown" id="edit_regions_multiselect_dropdown">
                            <div class="multiselect-search">
                                <input type="text" id="edit_regions_multiselect_search" placeholder="Поиск регионов..." class="multiselect-search-input">
                            </div>
                            <div class="multiselect-options" id="edit_regions_multiselect_options">
                                <!-- Опции будут заполнены JavaScript -->
                            </div>
                        </div>
                        <select name="regions[]" id="edit_regions" class="form-control" multiple style="display: none;">
                            @foreach($regions->where('active', true) as $region)
                                <option value="{{ $region->id }}">{{ $region->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <span class="error-message" id="edit_regions_error" style="display: none;"></span>
                </div>
                <div class="form-group">
                    <label for="edit_active">Статус *</label>
                    <select name="active" id="edit_active" class="form-control" required>
                        <option value="1">Активен</option>
                        <option value="0">Не активен</option>
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
.warehouses-container {
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

.warehouses-table-wrapper {
    background: white;
    border-radius: 12px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.1);
    overflow: hidden;
}

.warehouses-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 14px;
}

.warehouses-table th {
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

.warehouses-table th:first-child {
    padding-left: 20px;
}

.warehouses-table th:last-child {
    padding-right: 20px;
}

.warehouse-row {
    border-bottom: 1px solid #f1f3f4;
    transition: all 0.3s ease;
}

.warehouse-row:hover {
    background-color: #f8f9fa;
    transform: translateY(-1px);
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.warehouse-row:last-child {
    border-bottom: none;
}

.warehouses-table td {
    padding: 16px 12px;
    vertical-align: middle;
}

.warehouses-table td:first-child {
    padding-left: 20px;
}

.warehouses-table td:last-child {
    padding-right: 20px;
}

/* Стили для ячейки с информацией о складе */
.warehouse-info-cell {
    min-width: 200px;
}

.warehouse-info {
    display: flex;
    flex-direction: column;
    gap: 6px;
}

.warehouse-name {
    font-size: 16px;
    font-weight: 600;
    color: #133E71;
    line-height: 1.3;
}

.warehouse-id {
    font-size: 12px;
    color: #666;
    font-weight: 500;
    background: #f8f9fa;
    padding: 4px 8px;
    border-radius: 4px;
    display: inline-block;
    width: fit-content;
}

/* Стили для ячейки регионов */
.regions-cell {
    min-width: 200px;
}

.regions-list {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.region-item {
    display: flex;
    align-items: center;
    gap: 6px;
    padding: 6px 10px;
    background: #f8f9fa;
    border-radius: 6px;
    border: 1px solid #e9ecef;
    font-size: 12px;
    color: #333;
}

.region-item svg {
    color: #133E71;
    flex-shrink: 0;
}

.no-regions {
    color: #999;
    font-style: italic;
    font-size: 13px;
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
    display: none;
    align-items: center;
    justify-content: center;
    z-index: 1000;
    opacity: 0;
    visibility: hidden;
    transition: all 0.3s ease;
}

.modal.active {
    display: flex;
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

.warehouse-form {
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

/* Адаптивность */
@media (max-width: 768px) {
    .warehouses-container {
        padding: 10px;
    }
    
    .warehouses-table-wrapper {
        border-radius: 8px;
    }
    
    .warehouses-table {
        font-size: 12px;
    }
    
    .warehouses-table th,
    .warehouses-table td {
        padding: 10px 6px;
    }
    
    .warehouse-name {
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

.warehouse-row {
    animation: fadeIn 0.3s ease;
}

/* Стили для мультиселекта */
.multiselect-wrapper {
    position: relative;
    width: 100%;
}

.multiselect-input {
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

.multiselect-input:hover {
    border-color: #133E71;
}

.multiselect-input:focus {
    outline: none;
    border-color: #133E71;
    box-shadow: 0 0 0 3px rgba(19, 62, 113, 0.1);
}

.multiselect-input.active {
    border-color: #133E71;
    box-shadow: 0 0 0 3px rgba(19, 62, 113, 0.1);
}

.multiselect-placeholder {
    color: #6c757d;
    font-size: 14px;
}

.multiselect-values {
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
    flex: 1;
}

.multiselect-tag {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 4px 8px;
    background: #e3f2fd;
    color: #133E71;
    border-radius: 12px;
    font-size: 12px;
    font-weight: 500;
}

.multiselect-tag-remove {
    cursor: pointer;
    color: #133E71;
    font-weight: bold;
    font-size: 14px;
    line-height: 1;
}

.multiselect-arrow {
    color: #6c757d;
    transition: transform 0.3s ease;
    flex-shrink: 0;
}

.multiselect-input.active .multiselect-arrow {
    transform: rotate(180deg);
}

.multiselect-dropdown {
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

.multiselect-dropdown.active {
    display: block;
    animation: multiselectFadeIn 0.15s ease-out;
}

@keyframes multiselectFadeIn {
    from {
        opacity: 0;
        transform: translateY(-8px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.multiselect-search {
    padding: 12px;
    border-bottom: 1px solid #f1f3f4;
}

.multiselect-search-input {
    width: 100%;
    padding: 8px 12px;
    border: 1px solid #dee2e6;
    border-radius: 4px;
    font-size: 14px;
    outline: none;
    transition: border-color 0.3s ease;
}

.multiselect-search-input:focus {
    border-color: #133E71;
}

.multiselect-options {
    max-height: 200px;
    overflow-y: auto;
    padding: 8px 0;
}

.multiselect-option {
    display: flex;
    align-items: center;
    padding: 8px 12px;
    cursor: pointer;
    transition: background-color 0.15s ease;
    font-size: 14px;
    color: #495057;
}

.multiselect-option:hover {
    background: #f8f9fa;
}

.multiselect-option.selected {
    background: #e3f2fd;
    color: #133E71;
    font-weight: 500;
}

.multiselect-checkbox {
    margin-right: 8px;
    width: 16px;
    height: 16px;
    accent-color: #133E71;
}

/* Стили для скроллбара в мультиселекте */
.multiselect-options::-webkit-scrollbar {
    width: 6px;
}

.multiselect-options::-webkit-scrollbar-track {
    background: #f8f9fa;
    border-radius: 3px;
}

.multiselect-options::-webkit-scrollbar-thumb {
    background: #dee2e6;
    border-radius: 3px;
}

.multiselect-options::-webkit-scrollbar-thumb:hover {
    background: #adb5bd;
}
</style>
@endpush

@push('scripts')
<script>
function openModal() {
    document.getElementById('warehouseModal').classList.add('active');
    document.body.style.overflow = 'hidden';
}

function closeModal() {
    document.getElementById('warehouseModal').classList.remove('active');
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



// Обработка успешного добавления/обновления склада
document.addEventListener('DOMContentLoaded', function() {
    @if(session('success'))
        setTimeout(() => {
            closeModal();
            closeEditModal();
        }, 1000);
    @endif
    
    // Инициализация мультиселекта для регионов
    const regionsData = @json($regions->where('active', true)->values());
    console.log('Данные регионов для создания:', regionsData);
    initializeMultiSelect('regions_multiselect', 'regions', regionsData);
});

// Функция для очистки формы
function resetForm() {
    const form = document.querySelector('#warehouseModal form');
    form.reset();
    const errorMessages = document.querySelectorAll('#warehouseModal .error-message');
    errorMessages.forEach(error => error.remove());
    const errorInputs = document.querySelectorAll('#warehouseModal .form-control.is-invalid');
    errorInputs.forEach(input => input.classList.remove('is-invalid'));
    
    // Сброс мультиселекта
    const multiselectInput = document.getElementById('regions_multiselect');
    const placeholder = multiselectInput.querySelector('.multiselect-placeholder');
    const values = multiselectInput.querySelector('.multiselect-values');
    
    if (values) {
        values.innerHTML = '';
        values.style.display = 'none';
    }
    placeholder.style.display = 'block';
    // Восстанавливаем placeholder с звездочкой
    placeholder.textContent = 'Выберите регионы *';
}

// Функции для модального окна редактирования
function openEditModal(warehouseId) {
    // Загружаем данные склада
    fetch(`/guide/warehouses/${warehouseId}/edit`)
        .then(response => response.json())
        .then(warehouse => {
            // Заполняем форму данными
            document.getElementById('edit_name').value = warehouse.name;
            document.getElementById('edit_active').value = warehouse.active ? '1' : '0';
            
            // Устанавливаем action для формы
            document.getElementById('editWarehouseForm').action = `/guide/warehouses/${warehouseId}`;
            
            // Инициализируем мультиселект для регионов с выбранными значениями
            const regionsData = @json($regions->where('active', true)->values());
            console.log('Выбранные регионы для редактирования:', warehouse.regions);
            initializeMultiSelect('edit_regions_multiselect', 'edit_regions', regionsData, warehouse.regions);
            
            // Открываем модальное окно
            document.getElementById('editWarehouseModal').classList.add('active');
            document.body.style.overflow = 'hidden';
        })
        .catch(error => {
            console.error('Ошибка загрузки данных склада:', error);
            alert('Ошибка загрузки данных склада');
        });
}

function closeEditModal() {
    document.getElementById('editWarehouseModal').classList.remove('active');
    document.body.style.overflow = '';
    resetEditForm();
}

function resetEditForm() {
    const form = document.querySelector('#editWarehouseForm');
    form.reset();
    // Очищаем ошибки валидации
    const errorMessages = document.querySelectorAll('#editWarehouseModal .error-message');
    errorMessages.forEach(error => {
        error.style.display = 'none';
        error.textContent = '';
    });
    const errorInputs = document.querySelectorAll('#editWarehouseModal .form-control.is-invalid');
    errorInputs.forEach(input => input.classList.remove('is-invalid'));
    
    // Сброс мультиселекта
    const multiselectInput = document.getElementById('edit_regions_multiselect');
    if (multiselectInput) {
        const placeholder = multiselectInput.querySelector('.multiselect-placeholder');
        const values = multiselectInput.querySelector('.multiselect-values');
        
        if (values) {
            values.innerHTML = '';
            values.style.display = 'none';
        }
        if (placeholder) {
            placeholder.style.display = 'block';
            // Восстанавливаем placeholder с звездочкой
            placeholder.textContent = 'Выберите регионы *';
        }
    }
}

// Функция инициализации мультиселекта
function initializeMultiSelect(multiselectId, selectId, options, preSelectedValues = []) {
    const multiselectInput = document.getElementById(multiselectId);
    const multiselectDropdown = document.getElementById(multiselectId + '_dropdown');
    const multiselectOptions = document.getElementById(multiselectId + '_options');
    const multiselectSearch = document.getElementById(multiselectId + '_search');
    const hiddenSelect = document.getElementById(selectId);
    
    if (!multiselectInput || !multiselectDropdown || !multiselectOptions || !hiddenSelect) return;
    
    let isOpen = false;
    let selectedValues = preSelectedValues.map(item => item.id.toString());
    
    console.log('Инициализация мультиселекта:', {
        multiselectId: multiselectId,
        selectId: selectId,
        preSelectedValues: preSelectedValues,
        selectedValues: selectedValues
    });
    
    // Создаем контейнер для выбранных значений
    const valuesContainer = document.createElement('div');
    valuesContainer.className = 'multiselect-values';
    valuesContainer.style.display = 'none';
    multiselectInput.insertBefore(valuesContainer, multiselectInput.querySelector('.multiselect-arrow'));
    
    // Обновляем опции
    function updateOptions(filteredOptions = null) {
        const optionsToUse = filteredOptions || options;
        
        let html = '';
        optionsToUse.forEach(option => {
            const isSelected = selectedValues.includes(option.id.toString());
            html += `<div class="multiselect-option ${isSelected ? 'selected' : ''}" data-value="${option.id}">
                <input type="checkbox" class="multiselect-checkbox" ${isSelected ? 'checked' : ''}>
                <span>${option.name}</span>
            </div>`;
        });
        
        multiselectOptions.innerHTML = html;
        attachOptionEvents();
    }
    
    // Привязываем события к опциям
    function attachOptionEvents() {
        const optionElements = multiselectOptions.querySelectorAll('.multiselect-option');
        optionElements.forEach(option => {
            option.addEventListener('click', function(e) {
                e.stopPropagation();
                const value = this.dataset.value;
                const checkbox = this.querySelector('.multiselect-checkbox');
                
                if (selectedValues.includes(value)) {
                    selectedValues = selectedValues.filter(v => v !== value);
                    checkbox.checked = false;
                    this.classList.remove('selected');
                } else {
                    selectedValues.push(value);
                    checkbox.checked = true;
                    this.classList.add('selected');
                }
                
                updateSelectedValues();
                updateHiddenSelect();
            });
        });
    }
    
    // Обновляем отображение выбранных значений
    function updateSelectedValues() {
        const placeholder = multiselectInput.querySelector('.multiselect-placeholder');
        const values = multiselectInput.querySelector('.multiselect-values');
        
        if (selectedValues.length === 0) {
            placeholder.style.display = 'block';
            values.style.display = 'none';
            // Убеждаемся, что placeholder содержит звездочку
            if (!placeholder.textContent.includes('*')) {
                placeholder.textContent = placeholder.textContent.replace('Выберите регионы', 'Выберите регионы *');
            }
            return;
        }
        
        placeholder.style.display = 'none';
        values.style.display = 'flex';
        
        let html = '';
        selectedValues.forEach(value => {
            const option = options.find(opt => opt.id.toString() === value);
            if (option) {
                html += `<div class="multiselect-tag">
                    <span>${option.name}</span>
                    <span class="multiselect-tag-remove" data-value="${value}">&times;</span>
                </div>`;
            }
        });
        
        values.innerHTML = html;
        
        // Привязываем события к кнопкам удаления
        const removeButtons = values.querySelectorAll('.multiselect-tag-remove');
        removeButtons.forEach(button => {
            button.addEventListener('click', function(e) {
                e.stopPropagation();
                const value = this.dataset.value;
                selectedValues = selectedValues.filter(v => v !== value);
                updateSelectedValues();
                updateHiddenSelect();
                updateOptions();
            });
        });
    }
    
    // Обновляем скрытый select
    function updateHiddenSelect() {
        // Очищаем все опции
        hiddenSelect.innerHTML = '';
        
        // Добавляем выбранные значения
        selectedValues.forEach(value => {
            const option = document.createElement('option');
            option.value = value;
            option.selected = true;
            hiddenSelect.appendChild(option);
        });
        
        console.log('Обновление скрытого select:', {
            selectedValues: selectedValues,
            optionsCount: hiddenSelect.options.length,
            selectedOptionsCount: hiddenSelect.selectedOptions.length
        });
    }
    
    // Открытие/закрытие выпадающего списка
    function toggleDropdown() {
        isOpen = !isOpen;
        
        if (isOpen) {
            multiselectInput.classList.add('active');
            multiselectDropdown.classList.add('active');
            updateOptions();
            multiselectSearch.focus();
        } else {
            closeDropdown();
        }
    }
    
    function closeDropdown() {
        isOpen = false;
        multiselectInput.classList.remove('active');
        multiselectDropdown.classList.remove('active');
    }
    
    // Фильтрация
    function filterOptions(searchTerm) {
        if (!searchTerm) {
            updateOptions();
            return;
        }
        
        const searchTermLower = searchTerm.toLowerCase();
        const filteredOptions = options.filter(option => 
            option.name.toLowerCase().includes(searchTermLower)
        );
        
        updateOptions(filteredOptions);
    }
    
    // События
    multiselectInput.addEventListener('click', toggleDropdown);
    
    multiselectInput.addEventListener('keydown', function(e) {
        if (e.key === 'Enter' || e.key === ' ' || e.key === 'ArrowDown') {
            e.preventDefault();
            if (!isOpen) {
                toggleDropdown();
            }
        } else if (e.key === 'Escape') {
            closeDropdown();
        }
    });
    
    multiselectSearch.addEventListener('input', function() {
        filterOptions(this.value);
    });
    
    multiselectSearch.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeDropdown();
        }
    });
    
    // Закрытие при клике вне
    document.addEventListener('click', function(e) {
        if (!multiselectInput.contains(e.target) && !multiselectDropdown.contains(e.target)) {
            closeDropdown();
        }
    });
    
    // Инициализация с текущими значениями (если нет предварительно выбранных)
    if (selectedValues.length === 0) {
        const currentOptions = hiddenSelect.querySelectorAll('option[selected]');
        currentOptions.forEach(option => {
            selectedValues.push(option.value);
        });
    }
    
    if (selectedValues.length > 0) {
        updateSelectedValues();
        updateHiddenSelect(); // Обновляем скрытое поле при инициализации
    }
}

// Добавляем валидацию форм
document.addEventListener('DOMContentLoaded', function() {
    // Валидация формы добавления склада
    const addForm = document.querySelector('#warehouseModal form');
    if (addForm) {
        addForm.addEventListener('submit', function(e) {
            const regionsSelect = document.getElementById('regions');
            if (!regionsSelect || regionsSelect.selectedOptions.length === 0) {
                e.preventDefault();
                alert('Пожалуйста, выберите хотя бы один регион');
                return false;
            }
        });
    }
    
    // Валидация формы редактирования склада
    const editForm = document.getElementById('editWarehouseForm');
    if (editForm) {
        editForm.addEventListener('submit', function(e) {
            const regionsSelect = document.getElementById('edit_regions');
            console.log('Валидация регионов:', {
                selectExists: !!regionsSelect,
                selectedOptions: regionsSelect ? regionsSelect.selectedOptions.length : 0,
                allOptions: regionsSelect ? regionsSelect.options.length : 0
            });
            
            if (!regionsSelect || regionsSelect.selectedOptions.length === 0) {
                e.preventDefault();
                alert('Пожалуйста, выберите хотя бы один регион');
                return false;
            }
        });
    }
});
</script>
@endpush