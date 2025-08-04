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
                    <th>Регион</th>
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
                    <td class="region-cell">
                        <div class="region-info">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                                <circle cx="12" cy="10" r="3"></circle>
                            </svg>
                            <span>{{ $warehouse->regions->first()->name ?? 'Не указан' }}</span>
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
                    <label for="region_id">Регион *</label>
                    <select name="region_id" id="region_id" class="form-control @error('region_id') is-invalid @enderror" required>
                        <option value="">Выберите регион</option>
                        @foreach($regions->where('active', true) as $region)
                            <option value="{{ $region->id }}" {{ old('region_id') == $region->id ? 'selected' : '' }}>
                                {{ $region->name }} ({{ $region->city_name }})
                            </option>
                        @endforeach
                    </select>
                    @error('region_id')
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
                    <label for="edit_region_id">Регион *</label>
                    <select name="region_id" id="edit_region_id" class="form-control" required>
                        <option value="">Выберите регион</option>
                        @foreach($regions->where('active', true) as $region)
                            <option value="{{ $region->id }}">{{ $region->name }} ({{ $region->city_name }})</option>
                        @endforeach
                    </select>
                    <span class="error-message" id="edit_region_id_error" style="display: none;"></span>
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

/* Стили для ячейки региона */
.region-cell {
    min-width: 180px;
}

.region-info {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 10px;
    background: #f8f9fa;
    border-radius: 6px;
    border: 1px solid #e9ecef;
}

.region-info svg {
    color: #133E71;
    flex-shrink: 0;
}

.region-info span {
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

// Закрытие модального окна при клике вне его
document.addEventListener('click', function(event) {
    const modal = document.getElementById('warehouseModal');
    const editModal = document.getElementById('editWarehouseModal');
    if (event.target === modal) {
        closeModal();
    }
    if (event.target === editModal) {
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
});

// Функция для очистки формы
function resetForm() {
    const form = document.querySelector('#warehouseModal form');
    form.reset();
    const errorMessages = document.querySelectorAll('#warehouseModal .error-message');
    errorMessages.forEach(error => error.remove());
    const errorInputs = document.querySelectorAll('#warehouseModal .form-control.is-invalid');
    errorInputs.forEach(input => input.classList.remove('is-invalid'));
}

// Функции для модального окна редактирования
function openEditModal(warehouseId) {
    // Загружаем данные склада
    fetch(`/guide/warehouses/${warehouseId}/edit`)
        .then(response => response.json())
        .then(warehouse => {
            // Заполняем форму данными
            document.getElementById('edit_name').value = warehouse.name;
            document.getElementById('edit_region_id').value = warehouse.region_id || '';
            document.getElementById('edit_active').value = warehouse.active ? '1' : '0';
            
            // Устанавливаем action для формы
            document.getElementById('editWarehouseForm').action = `/guide/warehouses/${warehouseId}`;
            
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
}


</script>
@endpush