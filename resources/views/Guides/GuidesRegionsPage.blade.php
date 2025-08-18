@extends('layouts.layout')

@section('title', 'Регионы')

@section('header-title')
    <h1 class="header-title">Регионы</h1>
@endsection

@section('header-action-btn')
    <button class="btn btn-primary" onclick="openModal()">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <line x1="12" y1="5" x2="12" y2="19"></line>
            <line x1="5" y1="12" x2="19" y2="12"></line>
        </svg>
        Добавить регион
    </button>
@endsection

@section('content')
<div class="regions-container">
    <div class="breadcrumb">
        <a href="/">Главная</a> / <a href="/guide">Справочники</a> / Регионы
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

    <div class="regions-table-wrapper">
        <table class="regions-table">
            <thead>
                <tr>
                    <th>Название региона</th>
                    <th>Город</th>
                    <th>Статус</th>
                    <th>Действия</th>
                </tr>
            </thead>
            <tbody>
                @forelse($regions as $region)
                <tr class="region-row">
                    <td class="region-info-cell">
                        <div class="region-info">
                            <div class="region-name">{{ $region->name }}</div>
                            <div class="region-id">ID: {{ $region->id }}</div>
                        </div>
                    </td>
                    <td class="city-cell">
                        <div class="city-info">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                                <circle cx="12" cy="10" r="3"></circle>
                            </svg>
                            <span>{{ $region->city_name }}</span>
                        </div>
                    </td>
                    <td class="status-cell">
                        @if($region->active)
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
                            <button class="action-btn edit-btn" onclick="openEditModal({{ $region->id }})" title="Редактировать">
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
                                <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                                <circle cx="12" cy="10" r="3"></circle>
                            </svg>
                            <p>Регионы не найдены</p>
                            <button class="btn btn-primary" onclick="openModal()">Добавить первый регион</button>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Модальное окно для добавления региона -->
<div id="regionModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Добавление региона</h3>
            <button class="close-btn" onclick="closeModal()">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
            </button>
        </div>
        <div class="modal-body">
            <form action="{{ route('regions.store') }}" method="POST" class="region-form">
                @csrf
                <div class="form-group">
                    <label for="name">Название региона *</label>
                    <input type="text" name="name" id="name" value="{{ old('name') }}" class="form-control @error('name') is-invalid @enderror" required>
                    @error('name')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>
                <div class="form-group">
                    <label for="city_name">Город *</label>
                    <input type="text" name="city_name" id="city_name" value="{{ old('city_name') }}" class="form-control @error('city_name') is-invalid @enderror" required>
                    @error('city_name')
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
                        Добавить регион
                    </button>
                    <button type="button" class="btn btn-secondary" onclick="closeModal()">Отмена</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Модальное окно для редактирования региона -->
<div id="editRegionModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Редактирование региона</h3>
            <button class="close-btn" onclick="closeEditModal()">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
            </button>
        </div>
        <div class="modal-body">
            <form id="editRegionForm" method="POST" class="region-form">
                @csrf
                @method('PUT')
                <div class="form-group">
                    <label for="edit_name">Название региона *</label>
                    <input type="text" name="name" id="edit_name" class="form-control" required>
                    <span class="error-message" id="edit_name_error" style="display: none;"></span>
                </div>
                <div class="form-group">
                    <label for="edit_city_name">Город *</label>
                    <input type="text" name="city_name" id="edit_city_name" class="form-control" required>
                    <span class="error-message" id="edit_city_name_error" style="display: none;"></span>
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
.regions-container {
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

.regions-table-wrapper {
    background: white;
    border-radius: 12px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.1);
    overflow: hidden;
}

.regions-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 14px;
}

.regions-table th {
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

.regions-table th:first-child {
    padding-left: 20px;
}

.regions-table th:last-child {
    padding-right: 20px;
}

.region-row {
    border-bottom: 1px solid #f1f3f4;
    transition: all 0.3s ease;
}

.region-row:hover {
    background-color: #f8f9fa;
    transform: translateY(-1px);
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.region-row:last-child {
    border-bottom: none;
}

.regions-table td {
    padding: 16px 12px;
    vertical-align: middle;
}

.regions-table td:first-child {
    padding-left: 20px;
}

.regions-table td:last-child {
    padding-right: 20px;
}

/* Стили для ячейки с информацией о регионе */
.region-info-cell {
    min-width: 200px;
}

.region-info {
    display: flex;
    flex-direction: column;
    gap: 6px;
}

.region-name {
    font-size: 16px;
    font-weight: 600;
    color: #133E71;
    line-height: 1.3;
}

.region-id {
    font-size: 12px;
    color: #666;
    font-weight: 500;
    background: #f8f9fa;
    padding: 4px 8px;
    border-radius: 4px;
    display: inline-block;
    width: fit-content;
}

/* Стили для ячейки города */
.city-cell {
    min-width: 180px;
}

.city-info {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 10px;
    background: #f8f9fa;
    border-radius: 6px;
    border: 1px solid #e9ecef;
}

.city-info svg {
    color: #133E71;
    flex-shrink: 0;
}

.city-info span {
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

.region-form {
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
    .regions-container {
        padding: 10px;
    }
    
    .regions-table-wrapper {
        border-radius: 8px;
    }
    
    .regions-table {
        font-size: 12px;
    }
    
    .regions-table th,
    .regions-table td {
        padding: 10px 6px;
    }
    
    .region-name {
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

.region-row {
    animation: fadeIn 0.3s ease;
}


</style>
@endpush

@push('scripts')
<script>
function openModal() {
    document.getElementById('regionModal').classList.add('active');
    document.body.style.overflow = 'hidden';
}

function closeModal() {
    document.getElementById('regionModal').classList.remove('active');
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
    const modal = document.getElementById('regionModal');
    const editModal = document.getElementById('editRegionModal');
    if (event.target === modal) {
        closeModal();
    }
    if (event.target === editModal) {
        closeEditModal();
    }
});

// Обработка успешного добавления/обновления региона
document.addEventListener('DOMContentLoaded', function() {
    @if(session('success'))
        setTimeout(() => {
            closeModal();
            closeEditModal();
        }, 1000);
    @endif
    
    // Очистка формы при закрытии модального окна
    const modal = document.getElementById('regionModal');
    modal.addEventListener('hidden.bs.modal', function() {
        const form = modal.querySelector('form');
        form.reset();
        // Очищаем ошибки валидации
        const errorMessages = modal.querySelectorAll('.error-message');
        errorMessages.forEach(error => error.remove());
        const errorInputs = modal.querySelectorAll('.form-control.is-invalid');
        errorInputs.forEach(input => input.classList.remove('is-invalid'));
    });
});

// Функция для очистки формы
function resetForm() {
    const form = document.querySelector('#regionModal form');
    form.reset();
    const errorMessages = document.querySelectorAll('#regionModal .error-message');
    errorMessages.forEach(error => error.remove());
    const errorInputs = document.querySelectorAll('#regionModal .form-control.is-invalid');
    errorInputs.forEach(input => input.classList.remove('is-invalid'));
}



// Функции для модального окна редактирования
function openEditModal(regionId) {
    // Загружаем данные региона
    fetch(`/guide/regions/${regionId}/edit`)
        .then(response => response.json())
        .then(region => {
            // Заполняем форму данными
            document.getElementById('edit_name').value = region.name;
            document.getElementById('edit_city_name').value = region.city_name;
            document.getElementById('edit_active').value = region.active ? '1' : '0';
            
            // Устанавливаем action для формы
            document.getElementById('editRegionForm').action = `/guide/regions/${regionId}`;
            
            // Открываем модальное окно
            document.getElementById('editRegionModal').classList.add('active');
            document.body.style.overflow = 'hidden';
        })
        .catch(error => {
            console.error('Ошибка загрузки данных региона:', error);
            alert('Ошибка загрузки данных региона');
        });
}

function closeEditModal() {
    document.getElementById('editRegionModal').classList.remove('active');
    document.body.style.overflow = '';
    resetEditForm();
}

function resetEditForm() {
    const form = document.querySelector('#editRegionForm');
    form.reset();
    // Очищаем ошибки валидации
    const errorMessages = document.querySelectorAll('#editRegionModal .error-message');
    errorMessages.forEach(error => {
        error.style.display = 'none';
        error.textContent = '';
    });
    const errorInputs = document.querySelectorAll('#editRegionModal .form-control.is-invalid');
    errorInputs.forEach(input => input.classList.remove('is-invalid'));
}
</script>
@endpush
