@extends('layouts.layout')

@section('title', 'Сотрудники')

@section('header-action-btn')
    <button class="btn btn-primary" onclick="openModal()">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <line x1="12" y1="5" x2="12" y2="19"></line>
            <line x1="5" y1="12" x2="19" y2="12"></line>
        </svg>
        Добавить сотрудника
    </button>
@endsection

@section('header-title')
    <h1 class="header-title">Сотрудники</h1>
@endsection

@section('content')
<div class="users-container">
    <div class="breadcrumb">
        <a href="/">Главная</a> / <a href="/guide">Справочники</a> / Сотрудники
    </div>
    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="users-table-wrapper">
        <table class="users-table">
            <thead>
                <tr>
                    <th>Сотрудник</th>
                    <th>Контактная информация</th>
                    <th>Доступные регионы</th>
                    <th>Роль</th>
                    <th>Статус</th>
                    <th>Действия</th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $user)
                <tr class="user-row">
                    <td class="user-info-cell">
                        <div class="user-info">
                            <div class="user-name">{{ $user->name }}</div>
                            <div class="user-id">ID: {{ $user->id }}</div>
                        </div>
                    </td>
                
                    <td class="contact-cell">
                        <div class="contact-info">
                            <div class="contact-details">
                                <div class="contact-email">{{ $user->email }}</div>
                                <div class="contact-phone">{{ $user->phone }}</div>
                                <div class="contact-messengers">
                                    @if($user->has_telegram)
                                    <a href="https://t.me/{{ $user->phone }}" target="_blank" class="messenger-link telegram" title="Telegram">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M22 2L11 13"></path>
                                            <path d="M22 2L15 22L11 13L2 9L22 2Z"></path>
                                        </svg>
                                        Telegram
                                    </a>
                                    @endif
                                    @if($user->has_whatsapp)
                                    <a href="https://wa.me/{{ $user->phone }}" target="_blank" class="messenger-link whatsapp" title="WhatsApp">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
                                        </svg>
                                        WhatsApp
                                    </a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </td>

                    <td class="regions-cell">
                        <div class="regions-list">
                            @if($user->regions->isNotEmpty())
                                @foreach($user->regions as $region)
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
                    
                    <td class="role-cell">
                        <div class="role-info">
                            <div class="role-name">{{ $user->role->name ?? 'Роль не назначена' }}</div>
                            @if($user->role)
                            <div class="role-description">{{ $user->role->description ?? 'Описание роли отсутствует' }}</div>
                            @endif
                        </div>
                    </td>

                    <td class="status-cell">
                        <div class="status-badge {{ $user->active ? 'status-active' : 'status-inactive' }}">
                            {{ $user->active ? 'Активен' : 'Не активен' }}
                        </div>
                    </td>

                    <td class="actions-cell">
                        <div class="action-buttons">
                            <button class="action-btn edit-btn" onclick="openEditModal({{ $user->id }})" title="Редактировать">
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
                    <td colspan="6" class="empty-state">
                        <div class="empty-content">
                            <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                                <circle cx="12" cy="7" r="4"></circle>
                            </svg>
                            <p>Сотрудники не найдены</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Модальное окно для добавления сотрудника -->
<div id="userModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Добавление сотрудника</h3>
            <button class="close-btn" onclick="closeModal()">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
            </button>
        </div>
        <div class="modal-body">
            <form action="{{ route('users.store') }}" method="POST" class="user-form">
                @csrf
                <div class="form-group">
                    <label for="name">Имя сотрудника *</label>
                    <input type="text" name="name" id="name" value="{{ old('name') }}" class="form-control @error('name') is-invalid @enderror" required>
                    @error('name')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>
                <div class="form-group">
                    <label for="email">Email *</label>
                    <input type="email" name="email" id="email" value="{{ old('email') }}" class="form-control @error('email') is-invalid @enderror" required>
                    @error('email')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>
                <div class="form-group">
                    <label for="phone">Телефон *</label>
                    <input type="tel" name="phone" id="phone" value="{{ old('phone') }}" class="form-control @error('phone') is-invalid @enderror" placeholder="+7(999)999-99-99" required>
                    @error('phone')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>
                <div class="form-group">
                    <label for="role_id">Роль *</label>
                    <select name="role_id" id="role_id" class="form-control @error('role_id') is-invalid @enderror" required>
                        <option value="">Выберите роль</option>
                        @foreach($roles as $role)
                            <option value="{{ $role->id }}" {{ old('role_id') == $role->id ? 'selected' : '' }}>
                                {{ $role->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('role_id')
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
                            @foreach($regions as $region)
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
                    <label for="password">Пароль *</label>
                    <div class="password-field">
                        <input type="password" name="password" id="password" class="form-control @error('password') is-invalid @enderror" required>
                        <button type="button" class="password-toggle" onclick="generatePassword('password')" title="Сгенерировать случайный пароль">
                            <svg width="20" height="20" viewBox="0 0 32 32" fill="#133E71" xmlns="http://www.w3.org/2000/svg">
                                <path d="M31.255,11.746l-11-11c-0.141-0.141-0.369-0.141-0.51,0l-11,11c-0.141,0.141-0.141,0.368,0,0.509
                                l2.386,2.386H1c-0.199,0-0.36,0.161-0.36,0.36v16c0,0.199,0.161,0.36,0.36,0.36h16c0.199,0,0.36-0.161,0.36-0.36V20.87l2.385,2.385
                                c0.07,0.07,0.163,0.105,0.255,0.105s0.185-0.035,0.255-0.105l11-11C31.396,12.114,31.396,11.886,31.255,11.746z M16.64,30.64H1.36
                                V15.36h15.28V30.64z M20,22.491l-2.64-2.641V15c0-0.199-0.161-0.36-0.36-0.36h-4.851L9.509,12L20,1.509L30.491,12L20,22.491z M14,19
                                c0,0.552-0.448,1-1,1s-1-0.448-1-1s0.448-1,1-1S14,18.448,14,19z M20,6c-0.552,0-1,0.448-1,1s0.448,1,1,1s1-0.448,1-1S20.552,6,20,6
                                z M20,11c-0.552,0-1,0.448-1,1s0.448,1,1,1s1-0.448,1-1S20.552,11,20,11z M15,11c-0.552,0-1,0.448-1,1s0.448,1,1,1s1-0.448,1-1
                                S15.552,11,15,11z M25,11c-0.552,0-1,0.448-1,1s0.448,1,1,1s1-0.448,1-1S25.552,11,25,11z M20,16c-0.552,0-1,0.448-1,1s0.448,1,1,1
                                s1-0.448,1-1S20.552,16,20,16z M9,22c-0.552,0-1,0.448-1,1s0.448,1,1,1s1-0.448,1-1S9.552,22,9,22z M5,26c-0.552,0-1,0.448-1,1
                                s0.448,1,1,1s1-0.448,1-1S5.552,26,5,26z"/>
                            </svg>
                        </button>
                    </div>
                    @error('password')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>
                <div class="form-group">
                    <div class="switch-group">
                        <label class="switch-label">
                            <input type="checkbox" name="has_whatsapp" id="has_whatsapp" value="1" {{ old('has_whatsapp') ? 'checked' : '' }}>
                            <span class="switch-slider"></span>
                            <span class="switch-text">Есть WhatsApp</span>
                        </label>
                    </div>
                </div>
                <div class="form-group">
                    <div class="switch-group">
                        <label class="switch-label">
                            <input type="checkbox" name="has_telegram" id="has_telegram" value="1" {{ old('has_telegram') ? 'checked' : '' }}>
                            <span class="switch-slider"></span>
                            <span class="switch-text">Есть Telegram</span>
                        </label>
                    </div>
                </div>
                <div class="form-group">
                    <div class="switch-group">
                        <label class="switch-label">
                            <input type="checkbox" name="active" id="active" value="1" {{ old('active', '1') ? 'checked' : '' }}>
                            <span class="switch-slider"></span>
                            <span class="switch-text">Активен</span>
                        </label>
                    </div>
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M20 7L10 17L5 12"></path>
                        </svg>
                        Добавить сотрудника
                    </button>
                    <button type="button" class="btn btn-secondary" onclick="closeModal()">Отмена</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Модальное окно для отображения данных созданного пользователя -->
<div id="createdUserModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Сотрудник успешно создан</h3>
            <button class="close-btn" onclick="closeCreatedUserModal()">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
            </button>
        </div>
        <div class="modal-body">
            <div class="created-user-info">
                <div class="info-item">
                    <label>Имя:</label>
                    <span id="created_user_name"></span>
                </div>
                <div class="info-item">
                    <label>Логин:</label>
                    <span id="created_user_email"></span>
                </div>
                <div class="info-item">
                    <label>Пароль:</label>
                    <span id="created_user_password"></span>
                </div>
            </div>
            <div class="modal-actions">
                <button type="button" class="btn btn-primary" onclick="closeCreatedUserModal()">Закрыть</button>
            </div>
        </div>
    </div>
</div>

<!-- Модальное окно для редактирования сотрудника -->
<div id="editUserModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Редактирование сотрудника</h3>
            <button class="close-btn" onclick="closeEditModal()">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
            </button>
        </div>
        <div class="modal-body">
            <form id="editUserForm" method="POST" class="user-form">
                @csrf
                @method('PUT')
                <div class="form-group">
                    <label for="edit_name">Имя сотрудника *</label>
                    <input type="text" name="name" id="edit_name" class="form-control" required>
                    <span class="error-message" id="edit_name_error" style="display: none;"></span>
                </div>
                <div class="form-group">
                    <label for="edit_email">Email *</label>
                    <input type="email" name="email" id="edit_email" class="form-control" required>
                    <span class="error-message" id="edit_email_error" style="display: none;"></span>
                </div>
                <div class="form-group">
                    <label for="edit_phone">Телефон *</label>
                    <input type="tel" name="phone" id="edit_phone" class="form-control" placeholder="+7(999)999-99-99" required>
                    <span class="error-message" id="edit_phone_error" style="display: none;"></span>
                </div>
                <div class="form-group">
                    <label for="edit_role_id">Роль *</label>
                    <select name="role_id" id="edit_role_id" class="form-control" required>
                        <option value="">Выберите роль</option>
                        @foreach($roles as $role)
                            <option value="{{ $role->id }}">{{ $role->name }}</option>
                        @endforeach
                    </select>
                    <span class="error-message" id="edit_role_id_error" style="display: none;"></span>
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
                            @foreach($regions as $region)
                                <option value="{{ $region->id }}">{{ $region->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <span class="error-message" id="edit_regions_error" style="display: none;"></span>
                </div>
                <div class="form-group">
                    <label for="edit_password">Новый пароль (оставьте пустым, если не хотите менять)</label>
                    <div class="password-field">
                        <input type="password" name="password" id="edit_password" class="form-control">
                        <button type="button" class="password-toggle" onclick="generatePassword('edit_password')" title="Сгенерировать случайный пароль">
                            <svg width="20" height="20" viewBox="0 0 32 32" fill="#133E71" xmlns="http://www.w3.org/2000/svg">
                                <path d="M31.255,11.746l-11-11c-0.141-0.141-0.369-0.141-0.51,0l-11,11c-0.141,0.141-0.141,0.368,0,0.509
                                l2.386,2.386H1c-0.199,0-0.36,0.161-0.36,0.36v16c0,0.199,0.161,0.36,0.36,0.36h16c0.199,0,0.36-0.161,0.36-0.36V20.87l2.385,2.385
                                c0.07,0.07,0.163,0.105,0.255,0.105s0.185-0.035,0.255-0.105l11-11C31.396,12.114,31.396,11.886,31.255,11.746z M16.64,30.64H1.36
                                V15.36h15.28V30.64z M20,22.491l-2.64-2.641V15c0-0.199-0.161-0.36-0.36-0.36h-4.851L9.509,12L20,1.509L30.491,12L20,22.491z M14,19
                                c0,0.552-0.448,1-1,1s-1-0.448-1-1s0.448-1,1-1S14,18.448,14,19z M20,6c-0.552,0-1,0.448-1,1s0.448,1,1,1s1-0.448,1-1S20.552,6,20,6
                                z M20,11c-0.552,0-1,0.448-1,1s0.448,1,1,1s1-0.448,1-1S20.552,11,20,11z M15,11c-0.552,0-1,0.448-1,1s0.448,1,1,1s1-0.448,1-1
                                S15.552,11,15,11z M25,11c-0.552,0-1,0.448-1,1s0.448,1,1,1s1-0.448,1-1S25.552,11,25,11z M20,16c-0.552,0-1,0.448-1,1s0.448,1,1,1
                                s1-0.448,1-1S20.552,16,20,16z M9,22c-0.552,0-1,0.448-1,1s0.448,1,1,1s1-0.448,1-1S9.552,22,9,22z M5,26c-0.552,0-1,0.448-1,1
                                s0.448,1,1,1s1-0.448,1-1S5.552,26,5,26z"/>
                            </svg>
                        </button>
                    </div>
                    <span class="error-message" id="edit_password_error" style="display: none;"></span>
                </div>
                <div class="form-group">
                    <div class="switch-group">
                        <label class="switch-label">
                            <input type="checkbox" name="has_whatsapp" id="edit_has_whatsapp" value="1">
                            <span class="switch-slider"></span>
                            <span class="switch-text">Есть WhatsApp</span>
                        </label>
                    </div>
                </div>
                <div class="form-group">
                    <div class="switch-group">
                        <label class="switch-label">
                            <input type="checkbox" name="has_telegram" id="edit_has_telegram" value="1">
                            <span class="switch-slider"></span>
                            <span class="switch-text">Есть Telegram</span>
                        </label>
                    </div>
                </div>
                <div class="form-group">
                    <div class="switch-group">
                        <label class="switch-label">
                            <input type="checkbox" name="active" id="edit_active" value="1">
                            <span class="switch-slider"></span>
                            <span class="switch-text">Активен</span>
                        </label>
                    </div>
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
.users-container {
    max-width: 1400px;
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

.users-table-wrapper {
    background: white;
    border-radius: 12px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.1);
    overflow: hidden;
}

.users-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 14px;
}

.users-table th {
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

.users-table th:first-child {
    padding-left: 20px;
}

.users-table th:last-child {
    padding-right: 20px;
}

.user-row {
    border-bottom: 1px solid #f1f3f4;
    transition: all 0.3s ease;
}

.user-row:hover {
    background-color: #f8f9fa;
    transform: translateY(-1px);
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.user-row:last-child {
    border-bottom: none;
}

.users-table td {
    padding: 16px 12px;
    vertical-align: top;
}

.users-table td:first-child {
    padding-left: 20px;
}

.users-table td:last-child {
    padding-right: 20px;
}

/* Стили для ячейки с информацией о сотруднике */
.user-info-cell {
    min-width: 180px;
}

.user-info {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.user-name {
    font-size: 16px;
    font-weight: 600;
    color: #133E71;
    line-height: 1.3;
}

.user-id {
    font-size: 12px;
    color: #666;
    font-weight: 500;
    background: #f8f9fa;
    padding: 4px 8px;
    border-radius: 4px;
    display: inline-block;
    width: fit-content;
}

/* Стили для ячейки контактов */
.contact-cell {
    min-width: 200px;
}

.contact-info {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.contact-details {
    display: flex;
    flex-direction: column;
    gap: 6px;
}

.contact-email {
    font-size: 13px;
    color: #133E71;
    font-weight: 500;
}

.contact-phone {
    font-size: 14px;
    font-weight: 600;
    color: #333;
}

.contact-messengers {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
}

.messenger-link {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    padding: 4px 8px;
    border-radius: 12px;
    text-decoration: none;
    font-size: 11px;
    font-weight: 500;
    transition: all 0.3s ease;
}

.messenger-link.telegram {
    background-color: #0088cc;
    color: white;
}

.messenger-link.telegram:hover {
    background-color: #0077b3;
    transform: translateY(-1px);
}

.messenger-link.whatsapp {
    background-color: #25d366;
    color: white;
}

.messenger-link.whatsapp:hover {
    background-color: #20ba5a;
    transform: translateY(-1px);
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

/* Стили для ячейки роли */
.role-cell {
    min-width: 150px;
}

.role-info {
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.role-name {
    font-size: 14px;
    font-weight: 600;
    color: #333;
}

.role-description {
    font-size: 12px;
    color: #666;
    line-height: 1.3;
}

/* Стили для ячейки статуса */
.status-cell {
    min-width: 120px;
}

.status-badge {
    display: inline-block;
    padding: 6px 12px;
    border-radius: 20px;
    font-weight: 600;
    text-align: center;
    font-size: 12px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.status-active {
    background-color: #e8f5e8;
    color: #388e3c;
}

.status-inactive {
    background-color: #fce4ec;
    color: #c2185b;
}

/* Стили для ячейки действий */
.actions-cell {
    min-width: 100px;
}

.action-buttons {
    display: flex;
    gap: 6px;
}

.action-btn {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    background: #133E71;
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    text-decoration: none;
    transition: all 0.3s ease;
    border: none;
    cursor: pointer;
}

.action-btn:hover {
    background: #1C5BA4;
    transform: scale(1.1);
}

.edit-btn:hover {
    background: #e3f2fd;
    color: #1976d2;
}

.action-btn svg {
    width: 14px;
    height: 14px;
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

/* Адаптивность */
@media (max-width: 1200px) {
    .users-table {
        font-size: 13px;
    }
    
    .users-table th,
    .users-table td {
        padding: 12px 8px;
    }
    
    .users-table th:first-child,
    .users-table td:first-child {
        padding-left: 15px;
    }
    
    .users-table th:last-child,
    .users-table td:last-child {
        padding-right: 15px;
    }
}

@media (max-width: 768px) {
    .users-container {
        padding: 10px;
    }
    
    .users-table-wrapper {
        border-radius: 8px;
    }
    
    .users-table {
        font-size: 12px;
    }
    
    .users-table th,
    .users-table td {
        padding: 10px 6px;
    }
    
    .user-name {
        font-size: 14px;
    }
    
    .contact-phone {
        font-size: 12px;
    }
    
    .action-btn {
        width: 28px;
        height: 28px;
    }
    
    .action-btn svg {
        width: 12px;
        height: 12px;
    }
    
    /* Адаптивность модального окна */
    .modal {
        padding: 10px;
    }
    
    .modal-content {
        width: 100%;
        max-width: none;
        max-height: 95vh;
    }
    
    .modal-body {
        max-height: calc(95vh - 80px);
        padding: 20px;
    }
    
    .modal-header {
        padding: 15px 20px;
    }
    
    .modal-header h3 {
        font-size: 16px;
    }
    
    .form-actions {
        flex-direction: column;
        gap: 10px;
    }
    
    .btn {
        width: 100%;
        justify-content: center;
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

.user-row {
    animation: fadeIn 0.3s ease;
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
    padding: 20px;
    box-sizing: border-box;
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
    max-width: 600px;
    max-height: 90vh;
    display: flex;
    flex-direction: column;
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
    overflow-y: auto;
    flex: 1;
    max-height: calc(90vh - 80px); /* Вычитаем высоту header */
}

.user-form {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.form-group {
    display: flex;
    flex-direction: column;
    gap: 6px;
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
    margin-top: 16px;
}

/* Стили для переключателей */
.switch-group {
    display: flex;
    align-items: center;
}

.switch-label {
    display: flex;
    align-items: center;
    gap: 12px;
    cursor: pointer;
    font-size: 14px;
    color: #495057;
    font-weight: 500;
}

.switch-label input[type="checkbox"] {
    display: none;
}

.switch-slider {
    position: relative;
    width: 48px;
    height: 24px;
    background-color: #ccc;
    border-radius: 12px;
    transition: background-color 0.3s ease;
}

.switch-slider:before {
    content: '';
    position: absolute;
    top: 2px;
    left: 2px;
    width: 20px;
    height: 20px;
    background-color: white;
    border-radius: 50%;
    transition: transform 0.3s ease;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
}

.switch-label input[type="checkbox"]:checked + .switch-slider {
    background-color: #133E71;
}

.switch-label input[type="checkbox"]:checked + .switch-slider:before {
    transform: translateX(24px);
}

.switch-text {
    font-weight: 500;
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

/* Стили для скроллбара в модальном окне */
.modal-body::-webkit-scrollbar {
    width: 8px;
}

.modal-body::-webkit-scrollbar-track {
    background: #f8f9fa;
    border-radius: 4px;
}

.modal-body::-webkit-scrollbar-thumb {
    background: #dee2e6;
    border-radius: 4px;
}

.modal-body::-webkit-scrollbar-thumb:hover {
    background: #adb5bd;
}

/* Для Firefox */
.modal-body {
    scrollbar-width: thin;
    scrollbar-color: #dee2e6 #f8f9fa;
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

/* Стили для алертов */
.alert {
    padding: 15px;
    margin-bottom: 20px;
    border-radius: 8px;
    font-size: 14px;
    border: 1px solid;
}

.alert-success {
    background-color: #d4edda;
    border-color: #c3e6cb;
    color: #155724;
}

.alert-danger {
    background-color: #f8d7da;
    border-color: #f5c6cb;
    color: #721c24;
}

.alert ul {
    margin: 0;
    padding-left: 20px;
}

.alert li {
    margin: 5px 0;
}

/* Стили для модального окна с данными созданного пользователя */
.created-user-info {
    display: flex;
    flex-direction: column;
    gap: 16px;
    margin-bottom: 24px;
}

.info-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px 16px;
    background: #f8f9fa;
    border-radius: 8px;
    border: 1px solid #e9ecef;
}

.info-item label {
    font-weight: 600;
    color: #133E71;
    min-width: 80px;
    margin: 0;
}

.info-item span {
    font-size: 14px;
    color: #333;
    font-weight: 500;
    word-break: break-all;
}

.modal-actions {
    display: flex;
    justify-content: center;
    margin-top: 20px;
}

/* Стили для поля пароля */
.password-field {
    position: relative;
}

.password-toggle {
    position: absolute;
    right: 15px;
    top: 50%;
    transform: translateY(-50%);
    background: none;
    border: none;
    cursor: pointer;
    color: #666;
    padding: 5px;
    border-radius: 5px;
    transition: all 0.3s ease;
}

.password-toggle:hover {
    color: #133E71;
    background: rgba(19, 62, 113, 0.1);
}

.password-toggle svg {
    width: 20px;
    height: 20px;
    transition: transform 0.3s ease;
}

.password-toggle:hover svg {
    transform: rotate(180deg);
}

/* Анимация для кнопки при генерации */
.password-toggle.generating {
    animation: spin 0.6s linear infinite;
}

@keyframes spin {
    from { transform: translateY(-50%) rotate(0deg); }
    to { transform: translateY(-50%) rotate(360deg); }
}
</style>
@endpush

@push('scripts')
<script>
function openModal() {
    document.getElementById('userModal').classList.add('active');
    document.body.style.overflow = 'hidden';
}

function closeModal() {
    document.getElementById('userModal').classList.remove('active');
    document.body.style.overflow = '';
    resetForm();
}

// Закрытие модального окна по Escape
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        closeModal();
        closeEditModal();
        closeCreatedUserModal();
    }
});

// Закрытие модального окна при клике вне его
document.addEventListener('click', function(event) {
    const modal = document.getElementById('userModal');
    const editModal = document.getElementById('editUserModal');
    const createdUserModal = document.getElementById('createdUserModal');
    if (event.target === modal) {
        closeModal();
    }
    if (event.target === editModal) {
        closeEditModal();
    }
    if (event.target === createdUserModal) {
        closeCreatedUserModal();
    }
});

// Обработка успешного добавления/обновления пользователя
document.addEventListener('DOMContentLoaded', function() {
    @if(session('success'))
        @if(session('created_user'))
            // Показываем модальное окно с данными созданного пользователя
            showCreatedUserModal(@json(session('created_user')));
        @else
            setTimeout(() => {
                closeModal();
                closeEditModal();
            }, 1000);
        @endif
    @endif
    
    // Инициализация мультиселекта для регионов
    initializeMultiSelect('regions_multiselect', 'regions', @json($regions));
});

// Функция для очистки формы
function resetForm() {
    const form = document.querySelector('#userModal form');
    form.reset();
    const errorMessages = document.querySelectorAll('#userModal .error-message');
    errorMessages.forEach(error => error.remove());
    const errorInputs = document.querySelectorAll('#userModal .form-control.is-invalid');
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

// Функции для модального окна редактирования
function openEditModal(userId) {
    // Загружаем данные пользователя
    fetch(`/guide/users/${userId}/edit`)
        .then(response => response.json())
        .then(user => {
            // Заполняем форму данными
            document.getElementById('edit_name').value = user.name;
            document.getElementById('edit_email').value = user.email;
            
            // Применяем маску к телефону
            const phoneInput = document.getElementById('edit_phone');
            phoneInput.value = user.phone;
            applyPhoneMask(phoneInput);
            
            document.getElementById('edit_role_id').value = user.role_id;
            document.getElementById('edit_has_whatsapp').checked = user.has_whatsapp;
            document.getElementById('edit_has_telegram').checked = user.has_telegram;
            document.getElementById('edit_active').checked = user.active;
            
            // Устанавливаем action для формы
            document.getElementById('editUserForm').action = `/guide/users/${userId}`;
            
            // Инициализируем мультиселект для регионов с выбранными значениями
            const selectedRegions = user.regions.map(region => ({
                id: region.id,
                name: region.name
            }));
            console.log('Выбранные регионы для редактирования:', selectedRegions);
            initializeMultiSelect('edit_regions_multiselect', 'edit_regions', @json($regions), selectedRegions);
            
            // Открываем модальное окно
            document.getElementById('editUserModal').classList.add('active');
            document.body.style.overflow = 'hidden';
        })
        .catch(error => {
            console.error('Ошибка загрузки данных пользователя:', error);
            alert('Ошибка загрузки данных пользователя');
        });
}

// Добавляем валидацию форм
document.addEventListener('DOMContentLoaded', function() {
    // Валидация формы добавления пользователя
    const addForm = document.querySelector('#userModal form');
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
    
    // Валидация формы редактирования пользователя
    const editForm = document.getElementById('editUserForm');
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

function closeEditModal() {
    document.getElementById('editUserModal').classList.remove('active');
    document.body.style.overflow = '';
    resetEditForm();
}

// Функции для модального окна созданного пользователя
function showCreatedUserModal(userData) {
    // Заполняем данные в модальном окне
    document.getElementById('created_user_name').textContent = userData.name;
    document.getElementById('created_user_email').textContent = userData.email;
    document.getElementById('created_user_password').textContent = userData.password;
    
    // Показываем модальное окно
    document.getElementById('createdUserModal').classList.add('active');
    document.body.style.overflow = 'hidden';
    
    // Закрываем модальное окно добавления пользователя
    closeModal();
}

function closeCreatedUserModal() {
    document.getElementById('createdUserModal').classList.remove('active');
    document.body.style.overflow = '';
}

function resetEditForm() {
    const form = document.querySelector('#editUserForm');
    form.reset();
    // Очищаем ошибки валидации
    const errorMessages = document.querySelectorAll('#editUserModal .error-message');
    errorMessages.forEach(error => {
        error.style.display = 'none';
        error.textContent = '';
    });
    const errorInputs = document.querySelectorAll('#editUserModal .form-control.is-invalid');
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

// Функция генерации случайного пароля
function generatePassword(inputId) {
    const button = event.target.closest('.password-toggle');
    const input = document.getElementById(inputId);
    
    if (!button || !input) return;
    
    // Добавляем анимацию к кнопке
    button.classList.add('generating');
    
    // Определяем наборы символов
    const uppercase = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    const lowercase = 'abcdefghijklmnopqrstuvwxyz';
    const numbers = '0123456789';
    const symbols = '_)()[]@-';
    
    // Создаем массив для пароля
    let password = [];
    
    // Генерируем 10 символов
    for (let i = 0; i < 10; i++) {
        let charSet, randomChar;
        
        if (i === 0 || i === 9) {
            // Первый и последний символ - только буквы и цифры
            charSet = uppercase + lowercase + numbers;
        } else {
            // Остальные символы - любые
            charSet = uppercase + lowercase + numbers + symbols;
        }
        
        randomChar = charSet.charAt(Math.floor(Math.random() * charSet.length));
        password.push(randomChar);
    }
    
    // Перемешиваем массив (кроме первого и последнего символа)
    for (let i = 1; i < password.length - 1; i++) {
        const j = Math.floor(Math.random() * (password.length - 2)) + 1;
        [password[i], password[j]] = [password[j], password[i]];
    }
    
    // Объединяем в строку
    const generatedPassword = password.join('');
    
    // Устанавливаем значение в поле
    input.value = generatedPassword;
    
    // Показываем пароль на короткое время
    const originalType = input.type;
    input.type = 'text';
    
    // Убираем анимацию с кнопки
    setTimeout(() => {
        button.classList.remove('generating');
    }, 300);
    
    // Скрываем пароль через 2 секунды
    setTimeout(() => {
        input.type = originalType;
    }, 2000);
    
    // Показываем уведомление
    showNotification('Пароль сгенерирован и скопирован в поле', 'success');
}

// Функция для показа уведомлений
function showNotification(message, type = 'info') {
    // Создаем элемент уведомления
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        background: ${type === 'success' ? '#28a745' : '#17a2b8'};
        color: white;
        padding: 12px 20px;
        border-radius: 6px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        z-index: 10000;
        font-size: 14px;
        max-width: 300px;
        animation: slideIn 0.3s ease;
    `;
    notification.textContent = message;
    
    // Добавляем стили для анимации
    const style = document.createElement('style');
    style.textContent = `
        @keyframes slideIn {
            from { transform: translateX(100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
        @keyframes slideOut {
            from { transform: translateX(0); opacity: 1; }
            to { transform: translateX(100%); opacity: 0; }
        }
    `;
    document.head.appendChild(style);
    
    // Добавляем уведомление на страницу
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

// Функция для маски телефона
function applyPhoneMask(input) {
    let value = input.value.replace(/\D/g, ''); // Удаляем все нецифровые символы
    
    // Если первая цифра 8, заменяем на 7
    if (value.length > 0 && value[0] === '8') {
        value = '7' + value.substring(1);
    }
    
    // Если номер начинается с 7 и имеет больше 1 цифры, добавляем +
    if (value.length > 1 && value[0] === '7') {
        value = '+' + value;
    }
    
    // Если номер начинается с 9 и имеет 10 цифр, добавляем +7
    if (value.length === 10 && value[0] === '9') {
        value = '+7' + value;
    }
    
    // Применяем маску +7(999)999-99-99
    if (value.startsWith('+7')) {
        const digits = value.substring(2).replace(/\D/g, '');
        
        if (digits.length === 0) {
            value = '+7';
        } else if (digits.length <= 3) {
            value = '+7(' + digits;
        } else if (digits.length <= 6) {
            value = '+7(' + digits.substring(0, 3) + ')' + digits.substring(3);
        } else if (digits.length <= 8) {
            value = '+7(' + digits.substring(0, 3) + ')' + digits.substring(3, 6) + '-' + digits.substring(6);
        } else {
            value = '+7(' + digits.substring(0, 3) + ')' + digits.substring(3, 6) + '-' + digits.substring(6, 8) + '-' + digits.substring(8, 10);
        }
    }
    
    input.value = value;
}

// Инициализация масок телефона при загрузке страницы
document.addEventListener('DOMContentLoaded', function() {
    // Применяем маску к полям телефона
    const phoneInputs = document.querySelectorAll('input[type="tel"]');
    phoneInputs.forEach(input => {
        // Применяем маску при вводе
        input.addEventListener('input', function() {
            applyPhoneMask(this);
        });
        
        // Применяем маску при фокусе
        input.addEventListener('focus', function() {
            if (!this.value) {
                this.value = '+7';
            }
        });
        
        // Применяем маску при потере фокуса
        input.addEventListener('blur', function() {
            if (this.value === '+7') {
                this.value = '';
            }
        });
        
        // Предотвращаем ввод недопустимых символов
        input.addEventListener('keydown', function(e) {
            // Разрешаем: backspace, delete, tab, escape, enter, стрелки
            if ([8, 9, 27, 13, 46, 37, 38, 39, 40].indexOf(e.keyCode) !== -1 ||
                // Разрешаем Ctrl+A, Ctrl+C, Ctrl+V, Ctrl+X
                (e.keyCode === 65 && e.ctrlKey === true) ||
                (e.keyCode === 67 && e.ctrlKey === true) ||
                (e.keyCode === 86 && e.ctrlKey === true) ||
                (e.keyCode === 88 && e.ctrlKey === true)) {
                return;
            }
            
            // Разрешаем только цифры
            if ((e.keyCode >= 48 && e.keyCode <= 57) || (e.keyCode >= 96 && e.keyCode <= 105)) {
                return;
            }
            
            e.preventDefault();
        });
    });
    

});
</script>
@endpush
