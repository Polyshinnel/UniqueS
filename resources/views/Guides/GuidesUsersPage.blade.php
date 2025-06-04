@extends('layouts.layout')

@section('title', 'Сотрудники')

@section('search-filter')
@endsection

@section('header-action-btn')
<div class="add-user header-btn">
    <img src="{{ asset('assets/img/icons/plus.svg') }}" alt="add">
    <span>Добавить сотрудника</span>
</div>
@endsection

@section('header-title')
    <h1 class="header-title">Сотрудники</h1>
@endsection

@section('content')
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

    <div class="users-table">
        <table class="table">
            <thead>
                <tr>
                    <th>ФИО</th>
                    <th>Телефон</th>
                    <th>Доступные регионы</th>
                    <th>Роль</th>
                    <th>Статус</th>
                    <th>Действия</th>
                </tr>
            </thead>
            <tbody>
                @foreach($users as $user)
                    <tr>
                        <td>
                            <p class="responsible-main-name">{{ $user->name }}</p>
                            <div class="contact-socials">
                                @if($user->has_telegram)
                                <a href="#">
                                    <div class="social-round">
                                        <img src="{{ asset('assets/img/icons/telegram.svg') }}" alt="telegram">
                                    </div>
                                </a>
                                @endif
                                @if($user->has_whatsapp)
                                <a href="#">
                                    <div class="social-round">
                                        <img src="{{ asset('assets/img/icons/whatsapp.svg') }}" alt="telegram">
                                    </div>
                                </a>
                                @endif
                            </div>
                        </td>
                        <td>{{ $user->phone }}</td>
                        <td>
                            <div class="region-list">
                                @foreach($user->regions as $region)
                                    <span class="region-item">{{ $region->name }}</span>
                                @endforeach
                            </div>
                        </td>
                        <td>{{ $user->role->name ?? 'Не назначена' }}</td>
                        <td>
                            <span class="status-item {{ $user->active ? 'status-item-active' : '' }}">
                                {{ $user->active ? 'Активен' : 'Не активен' }}
                            </span>
                        </td>
                        <td>
                            <div class="action-buttons">
                                <button class="btn btn-edit" data-user-id="{{ $user->id }}" title="Редактировать">
                                    <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M11.5 1.5L14.5 4.5M1.5 14.5H4.5L11.5 7.5L8.5 4.5L1.5 11.5V14.5Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                </button>
                                <form action="{{ route('users.destroy', $user) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-delete" title="Удалить" onclick="return confirm('Вы уверены, что хотите удалить этого сотрудника?')">
                                        <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M2 4H3.33333H14" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                            <path d="M12.6667 4V13.3333C12.6667 13.687 12.5262 14.0261 12.2761 14.2761C12.0261 14.5262 11.687 14.6667 11.3333 14.6667H4.66667C4.31304 14.6667 3.97391 14.5262 3.72386 14.2761C3.47381 14.0261 3.33333 13.687 3.33333 13.3333V4M5.33333 4V2.66667C5.33333 2.31304 5.47381 1.97391 5.72386 1.72386C5.97391 1.47381 6.31304 1.33333 6.66667 1.33333H9.33333C9.68696 1.33333 10.0261 1.47381 10.2761 1.72386C10.5262 1.97391 10.6667 2.31304 10.6667 2.66667V4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                        </svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <x-right-sidebar>
        <div class="user-form">
            <h2>Добавление сотрудника</h2>
            <form action="{{ route('users.store') }}" method="POST" class="user-form-content">
                @csrf
                <div class="form-group">
                    <label for="name">ФИО Сотрудника</label>
                    <input type="text" name="name" id="name" value="{{ old('name') }}" class="form-control" required>
                    @error('name')
                        <span class="error">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="phone">Телефон Сотрудника</label>
                    <input type="tel" name="phone" id="phone" value="{{ old('phone') }}" class="form-control" required>
                    @error('phone')
                        <span class="error">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="email">Почта сотрудника</label>
                    <input type="email" name="email" id="email" value="{{ old('email') }}" class="form-control" required>
                    @error('email')
                        <span class="error">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="regions">Регионы</label>
                    <div id="selected-regions" class="selected-regions"></div>
                    <select name="regions[]" id="regions" class="form-control" multiple size="5">
                        @foreach($regions as $region)
                            <option value="{{ $region->id }}">{{ $region->name }}</option>
                        @endforeach
                    </select>
                    @error('regions')
                        <span class="error">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="role_id">Роль</label>
                    <select name="role_id" id="role_id" class="form-control" required>
                        <option value="">Выберите роль</option>
                        @foreach($roles as $role)
                            <option value="{{ $role->id }}" {{ old('role_id') == $role->id ? 'selected' : '' }}>
                                {{ $role->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('role_id')
                        <span class="error">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <div class="checkbox-group">
                        <label>
                            <input type="checkbox" name="has_whatsapp" value="1" {{ old('has_whatsapp') ? 'checked' : '' }}>
                            Есть WhatsApp
                        </label>
                    </div>
                    <div class="checkbox-group">
                        <label>
                            <input type="checkbox" name="has_telegram" value="1" {{ old('has_telegram') ? 'checked' : '' }}>
                            Есть Telegram
                        </label>
                    </div>
                </div>

                <div class="form-group">
                    <label for="active">Статус</label>
                    <select name="active" id="active" class="form-control" required>
                        <option value="1" {{ old('active') == '1' ? 'selected' : '' }}>Активен</option>
                        <option value="0" {{ old('active') == '0' ? 'selected' : '' }}>Не активен</option>
                    </select>
                    @error('active')
                        <span class="error">{{ $message }}</span>
                    @enderror
                </div>

                <button type="submit" class="btn btn-primary">Добавить</button>
            </form>
        </div>
    </x-right-sidebar>
@endsection

@push('scripts')
<script src="{{ asset('assets/js/side-panel.js') }}"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const regionsSelect = document.getElementById('regions');
    const selectedRegions = document.getElementById('selected-regions');

    // Функция для обновления отображения выбранных регионов
    function updateSelectedRegions() {
        selectedRegions.innerHTML = '';
        const selectedOptions = Array.from(regionsSelect.selectedOptions);
        
        selectedOptions.forEach(option => {
            const regionTag = document.createElement('span');
            regionTag.className = 'status-item status-item-active';
            regionTag.textContent = option.text;
            
            const removeButton = document.createElement('button');
            removeButton.innerHTML = '&times;';
            removeButton.className = 'remove-region';
            removeButton.onclick = function(e) {
                e.preventDefault();
                e.stopPropagation();
                option.selected = false;
                regionTag.remove();
            };
            
            regionTag.appendChild(removeButton);
            selectedRegions.appendChild(regionTag);
        });
    }

    // Обработчик изменения выбора регионов
    regionsSelect.addEventListener('change', function(e) {
        e.preventDefault();
        updateSelectedRegions();
    });

    // Обработчик клика по селекту для предотвращения закрытия при множественном выборе
    regionsSelect.addEventListener('mousedown', function(e) {
        if (e.target.tagName === 'OPTION') {
            e.preventDefault();
            const option = e.target;
            option.selected = !option.selected;
            updateSelectedRegions();
        }
    });

    // Инициализация при загрузке страницы
    updateSelectedRegions();
});
</script>
@endpush
