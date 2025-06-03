@extends('layouts.layout')

@section('title', 'Источники')

@section('search-filter')
@endsection

@section('header-action-btn')
<div class="add-source header-btn">
    <img src="{{ asset('assets/img/icons/plus.svg') }}" alt="add">
    <span>Добавить источник</span>
</div>
@endsection

@section('header-title')
    <h1 class="header-title">Источники</h1>
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

    <div class="sources-table">
        <table class="table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Название</th>
                    <th>Статус</th>
                    <th>Действия</th>
                </tr>
            </thead>
            <tbody>
                @foreach($sources as $source)
                    <tr>
                        <td>{{ $source->id }}</td>
                        <td>{{ $source->name }}</td>
                        <td>{{ $source->active ? 'Активен' : 'Не активен' }}</td>
                        <td>
                            <div class="action-buttons">
                                <button class="btn btn-edit" data-source-id="{{ $source->id }}" title="Редактировать">
                                    <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M11.5 1.5L14.5 4.5M1.5 14.5H4.5L11.5 7.5L8.5 4.5L1.5 11.5V14.5Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                </button>
                                <form action="{{ route('sources.destroy', $source) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-delete" title="Удалить" onclick="return confirm('Вы уверены, что хотите удалить этот источник?')">
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
        <div class="source-form">
            <h2>Добавление источника</h2>
            <form action="{{ route('sources.store') }}" method="POST">
                @csrf
                <div class="form-group">
                    <label for="name">Название источника</label>
                    <input type="text" name="name" id="name" value="{{ old('name') }}" class="form-control" required>
                    @error('name')
                        <span class="error">{{ $message }}</span>
                    @enderror
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
@endpush
