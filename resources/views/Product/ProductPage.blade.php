@extends('layouts.layout')

@section('title', 'Главная')

@section('header-action-btn')
    <a href="/product/create">
        <div class="header-btn">
            <img src="{{ asset('assets/img/icons/plus.svg') }}" alt="add">
            <span>Добавить товар</span>
        </div>
    </a>

@endsection


@section('header-title')
    <h1 class="header-title">Товары</h1>
@endsection


@section('content')
<div class="organizations-table">
    <table class="table">
        <thead>
            <tr>
                <th>Название</th>
                <th>Категория</th>
                <th>Поставщик</th>
                <th>Осн. хар-ки, сост, компл</th>
                <th>Дата след. действия</th>
                <th>Действие</th>
                <th>Статус</th>
            </tr>
        </thead>
        <tbody>
            @forelse($products as $product)
            <tr class="product-item">
                <td>
                    <div class="product-name-block">
                        <div class="img-block">
                            @if($product->mainImage)
                                <img src="{{ asset('storage/' . $product->mainImage->file_path) }}" alt="{{ $product->name }}">
                            @else
                                <img src="{{ asset('assets/img/stanok.png') }}" alt="{{ $product->name }}">
                            @endif
                        </div>

                        <div class="name-block">
                            <p>{{ $product->name }}</p>
                            <span>{{ $product->sku }}</span>
                        </div>
                    </div>

                    @if($product->status_comment)
                    <p class="comment-block">Комментарий: Без комментариев</p>
                    @endif

                    <div class="price-block">
                        <p><b>Цена покупки: </b>{{ number_format($product->purchase_price ?? 0, 0, ',', ' ') }} руб</p>
                        <p><b>Доп расходы: </b>{{ number_format($product->add_expenses ?? 0, 0, ',', ' ') }} руб</p>
                        <p><b>Маржа: </b>{{ number_format(($product->purchase_price ?? 0) + ($product->add_expenses ?? 0), 0, ',', ' ') }} руб</p>
                    </div>

                    <a href="/product/{{ $product->id }}" class="product-link">Подробнее</a>
                </td>

                <td>
                    <a href="#">{{ $product->category->name ?? 'Не указана' }}</a>
                </td>

                <td>
                    <div class="organization-product-block">
                        <p>{{ $product->company->name ?? 'Не указана' }}</p>
                        @if($product->company && $product->company->addresses->count() > 0)
                            <p>{{ $product->company->addresses->first()->address ?? '' }}</p>
                        @endif
                        <p>Регион: {{ $product->warehouse->name ?? 'Не указан' }}</p>
                        @if($product->company && $product->company->addresses->count() > 0)
                            <p>Адрес станка: {{ $product->company->addresses->first()->address ?? '' }}</p>
                        @endif

                        <div class="manager-block">
                            <div class="responsible-item">
                                <p class="responsible-main-name">Регионал: {{ $product->regional->name ?? 'Не назначен' }}</p>
                                <div class="contact-socials">
                                    <a href="#">
                                        <div class="social-round">
                                            <img src="{{ asset('assets/img/icons/eye-w.svg') }}" alt="telegram">
                                        </div>
                                    </a>
                                    <a href="#">
                                        <div class="social-round">
                                            <img src="{{ asset('assets/img/icons/telegram.svg') }}" alt="telegram">
                                        </div>
                                    </a>
                                    <a href="#">
                                        <div class="social-round">
                                            <img src="{{ asset('assets/img/icons/whatsapp.svg') }}" alt="telegram">
                                        </div>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </td>

                <td>
                    <div class="main-product-info-block">
                        @if($product->main_chars)
                        <p><b>Осн. хар:</b> {{ $product->main_chars }}</p>
                        @endif
                        @if($product->mark)
                        <p><b>Марка:</b> {{ $product->mark }}</p>
                        @endif
                        @if($product->complectation)
                        <p><b>Компл:</b> {{ $product->complectation }}</p>
                        @endif
                        @if($product->loading_type)
                        <p><b>Проверка:</b> {{ $product->loading_type }}</p>
                        @endif
                        @if($product->removal_type)
                        <p><b>Демонтаж:</b> {{ $product->removal_type }}</p>
                        @endif
                        @if($product->loading_comment)
                        <p><b>Погрузка:</b> {{ $product->loading_comment }}</p>
                        @endif
                    </div>
                </td>

                <td>
                    <p class="product-action-date">{{ $product->created_at->format('d.m.y H:i') }}</p>
                </td>

                <td>
                    <p class="product-action-text">Тестовое действие</p>
                </td>

                <td>
                    <div class="product-status-list">
                        <div class="status-block">
                            <p>Статус единицы</p>
                            <div class="status-item" style="background-color: #133E71">
                                <span class="status-main">{{ $product->status->name ?? 'Не указан' }}</span>
                            </div>
                        </div>

                        <div class="status-block">
                            <p>Статус обьявления</p>
                            <div class="status-item" style="background-color: #133E71">
                                <span class="status-main">Актив</span>
                            </div>
                        </div>

                        <div class="status-block">
                            <p>Статус публикации</p>
                            <div class="status-item" style="background-color: #133E71">
                                <span class="status-main">Не опубл.</span>
                            </div>
                        </div>
                    </div>

                    <p class="product-adv-price"><b>Цена продажи</b>: 0 руб</p>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" style="text-align: center; padding: 20px;">
                    <p>Товары не найдены</p>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection

@push('scripts')
<script src="{{ asset('assets/js/side-panel.js') }}"></script>
@endpush
