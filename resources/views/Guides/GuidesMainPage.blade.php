@extends('layouts.layout')

@section('title', 'Справочники')

@section('search-filter')
@endsection

@section('header-action-btn')
@endsection

@section('header-title')
    <h1 class="header-title">Справочники</h1>
@endsection

@section('content')
<div class="guides-container">
    <div class="guides-grid">
        <a href="/guide/users" class="guide-tile">
            <div class="guide-content">
                <h3>Сотрудники</h3>
            </div>
        </a>
        <a href="/guide/regions" class="guide-tile">
            <div class="guide-content">
                <h3>Регионы</h3>
            </div>
        </a>
        <a href="/guide/sources" class="guide-tile">
            <div class="guide-content">
                <h3>Источники</h3>
            </div>
        </a>
        <a href="/guide/categories" class="guide-tile">
            <div class="guide-content">
                <h3>Категории</h3>
            </div>
        </a>
        <a href="/guide/warehouses" class="guide-tile">
            <div class="guide-content">
                <h3>Склады</h3>
            </div>
        </a>
    </div>
</div>
@endsection
