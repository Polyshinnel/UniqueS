@extends('layouts.layout')

@section('title', 'Главная')

@section('header-title')
    <h1 class="header-title">ООО "Березка"</h1>
@endsection

@section('search-filter')
@endsection

@section('header-action-btn')
<div class="edit-organization header-btn">
    <span>Редактировать</span>
</div>
@endsection

@section('content')

<div class="organization-block">
    <div class="organization_top">
        <div class="common-info-block">
            <div class="organization-sku-status">
                <p>КЛГ 001</p>
                <div class="organization-sku-status-status">
                    <span class="organization-sku-status-status-item">В работе</span>
                </div>
            </div>
            <!-- /.organization-sku-status -->

            <h2 class="organization_name">ООО "Березка"</h2>

            <div class="organization_info">
                <span>Общая информация</span>
                <p>в продаже 2шт. Токарных, 2шт фрезерных (один продали), открыли вторую площадку, где оборудование на улице. Оборудование обновляют раз в 6 мес.</p>
            </div>
            <!-- /.organization_info -->
        </div>
        <!-- /.common-info-block -->

        <div class="organization-next-action-date">
            <span>Дата следующего действия</span>
            <p>23.04.2025</p>
        </div>
        <!-- /.organization-next-action-date -->

        <div class="organization-action-info">
            <span>Что требуется сделать</span>
            <p>Позвонить клиенту, уточнить по наличию оборудования</p>
        </div>
        <!-- /.organization-action-info -->

        <button class="add-new-action-btn">Задать новое действие</button>
    </div>
    <!-- /.organization_top -->

    <div class="organization-next-line">
        <div class="organization-next-line-item">
            <span>Регион</span>
            <p>Калужская обл.</p>
        </div>
        <!-- /.organization-next-line-item -->
    </div>
    <!-- /.organization-next-line -->
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