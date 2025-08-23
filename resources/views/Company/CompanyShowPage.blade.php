@extends('layouts.layout')

@section('title', $company->name ?? 'Организация')

@section('header-title')
    <h1 class="header-title">{{ $company->name ?? 'Организация' }}</h1>
@endsection

@section('content')
<div class="company-item-container">
    <div class="company-header">
        <div class="breadcrumb">
            <a href="{{ route('companies.index') }}">Организации</a> / {{ $company->name }}
        </div>
        <div class="company-header-actions">
            <h1 class="company-title">{{ $company->name ?? 'Название не указано' }}</h1>
            <div class="company-actions">
                <a href="#" class="btn btn-primary">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"></path>
                    </svg>
                    Редактировать
                </a>
            </div>
        </div>
        <div class="company-sku">SKU: {{ $company->sku ?? 'SKU не указан' }}</div>
    </div>

    <div class="company-content">
        <!-- Основная информация о компании -->
        <div class="company-info-section">
            <div class="info-block">
                <h3>Основная информация</h3>
                <div class="main-info-container">
                    <div class="main-info-content" id="main_info_content">
                        <div class="info-list">
                            <div class="info-item">
                                <span class="label">Статус:</span>
                                <span class="value">
                                    <div class="status-selector">
                                        <div class="status-badge status-{{ $company->status->id ?? 'unknown' }} clickable" onclick="toggleStatusDropdown()">
                                            {{ $company->status->name ?? 'Статус не указан' }}
                                            <svg class="dropdown-arrow" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <polyline points="6,9 12,15 18,9"></polyline>
                                            </svg>
                                        </div>
                                        <div class="status-dropdown" id="statusDropdown">
                                            @foreach($statuses as $status)
                                                <div class="status-option" data-status-id="{{ $status->id }}" data-status-name="{{ $status->name }}" onclick="changeStatus(this)">
                                                    <div class="status-badge status-{{ $status->id }}">{{ $status->name }}</div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </span>
                            </div>
                            <div class="info-item">
                                <span class="label">Регион:</span>
                                <span class="value">{{ $company->region->name ?? 'Регион не указан' }}</span>
                            </div>
                            <div class="info-item">
                                <span class="label">Источник контакта:</span>
                                <span class="value">{{ $company->source->name ?? 'Источник не указан' }}</span>
                            </div>
                            <div class="info-item">
                                <span class="label">Региональный менеджер:</span>
                                <span class="value">{{ $company->regional->name ?? 'Регионал не назначен' }}</span>
                            </div>
                            <div class="info-item">
                                <span class="label">Ответственный менеджер:</span>
                                <span class="value">{{ $company->owner->name ?? 'Менеджер не назначен' }}</span>
                            </div>
                        </div>
                        @if($company->common_info)
                            <div class="company-description">
                                <div class="description-header">
                                    <strong>Описание:</strong>
                                    <button class="edit-comment-btn" onclick="editMainInfoBlock()">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"></path>
                                        </svg>
                                        Редактировать
                                    </button>
                                </div>
                                <p>{{ $company->common_info }}</p>
                            </div>
                        @else
                            <div class="company-description">
                                <div class="description-header">
                                    <strong>Описание:</strong>
                                    <button class="edit-comment-btn" onclick="editMainInfoBlock()">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"></path>
                                        </svg>
                                        Редактировать
                                    </button>
                                </div>
                                <p style="color: #666; font-style: italic;">Описание не указано</p>
                            </div>
                        @endif
                    </div>
                    <div class="main-info-edit" id="main_info_edit" style="display: none;">
                        <div class="form-group">
                            <label for="common_info_textarea">Описание компании:</label>
                            <textarea class="comment-textarea" id="common_info_textarea" rows="6" data-original="{{ $company->common_info ?? '' }}">{{ $company->common_info ?? '' }}</textarea>
                        </div>
                    </div>
                    <!-- Кнопки действий для блока основной информации -->
                    <div class="main-info-actions" id="main_info_actions" style="display: none;">
                        <button class="btn btn-primary" onclick="saveMainInfoBlock()">Сохранить</button>
                        <button class="btn btn-secondary" onclick="cancelMainInfoEdit()">Отмена</button>
                    </div>
                </div>
            </div>

            <div class="info-block">
                <div class="block-header">
                    <h3>Контактная информация</h3>
                    <button class="edit-comment-btn" onclick="editContactInfoBlock()">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"></path>
                        </svg>
                        Редактировать
                    </button>
                </div>
                <div class="contact-info-container">
                    <div class="contact-info-content" id="contact_info_content">
                        <div class="contact-list">
                            @if($company->email)
                                <div class="contact-item">
                                    <span class="label">Email:</span>
                                    <span class="value">{{ $company->email }}</span>
                                </div>
                            @endif
                            @if($company->phone)
                                <div class="contact-item">
                                    <span class="label">Телефон компании:</span>
                                    <span class="value">
                                        <a href="tel:{{ $company->phone }}" class="link">{{ $company->phone }}</a>
                                    </span>
                                </div>
                            @endif
                            @if($company->emails->count() > 0)
                                @foreach($company->emails as $email)
                                    <div class="contact-item">
                                        <span class="label">Дополнительный email:</span>
                                        <span class="value">{{ $email->email }}</span>
                                    </div>
                                @endforeach
                            @endif
                            @if($company->site)
                                <div class="contact-item">
                                    <span class="label">Сайт:</span>
                                    <span class="value">
                                        <a href="{{ $company->site }}" target="_blank" class="link">{{ $company->site }}</a>
                                    </span>
                                </div>
                            @endif
                            @forelse($company->contacts as $contact)
                                @if($contact->main_contact)
                                    <div class="contact-item">
                                        <span class="label">Основной контакт:</span>
                                        <span class="value">{{ $contact->name }}</span>
                                    </div>
                                    @forelse($contact->phones as $phone)
                                        <div class="contact-item">
                                            <span class="label">Телефон:</span>
                                            <span class="value">{{ $phone->phone }}</span>
                                        </div>
                                    @empty
                                    @endforelse
                                @endif
                            @empty
                            @endforelse
                        </div>
                    </div>
                    <div class="contact-info-edit" id="contact_info_edit" style="display: none;">
                        <div class="form-group">
                            <label for="company_email">Email компании:</label>
                            <input type="email" class="form-control" id="company_email" value="{{ $company->email ?? '' }}" data-original="{{ $company->email ?? '' }}">
                        </div>
                        <div class="form-group">
                            <label for="company_phone">Телефон компании:</label>
                            <input type="tel" class="form-control" id="company_phone" value="{{ $company->phone ?? '' }}" data-original="{{ $company->phone ?? '' }}">
                        </div>
                        <div class="form-group">
                            <label for="company_site">Сайт:</label>
                            <input type="url" class="form-control" id="company_site" value="{{ $company->site ?? '' }}" data-original="{{ $company->site ?? '' }}">
                        </div>
                    </div>
                    <!-- Кнопки действий для блока контактной информации -->
                    <div class="contact-info-actions" id="contact_info_actions" style="display: none;">
                        <button class="btn btn-primary" onclick="saveContactInfoBlock()">Сохранить</button>
                        <button class="btn btn-secondary" onclick="cancelContactInfoEdit()">Отмена</button>
                    </div>
                </div>
            </div>

            @if($company->contacts->count() > 0)
                <div class="info-block">
                    <div class="block-header">
                        <h3>Все контакты</h3>
                        <button class="edit-comment-btn" onclick="editContactsBlock()">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"></path>
                            </svg>
                            Редактировать
                        </button>
                    </div>
                    <div class="contacts-container">
                        <div class="contacts-content" id="contacts_content">
                            <div class="contacts-list">
                                @foreach($company->contacts as $contact)
                                    <div class="contact-card">
                                        <div class="contact-header">
                                            <span class="contact-name">{{ $contact->name }}</span>
                                            @if($contact->main_contact)
                                                <span class="main-contact-badge">Основной</span>
                                            @endif
                                        </div>
                                        @if($contact->position)
                                            <div class="contact-position">{{ $contact->position }}</div>
                                        @endif
                                        @if($contact->phones->count() > 0)
                                            <div class="contact-phones">
                                                @foreach($contact->phones as $phone)
                                                    <div class="phone-item">
                                                        <span class="phone-number">{{ $phone->phone }}</span>
                                                        <div class="contact-actions">
                                                            <a href="tel:{{ $phone->phone }}" class="action-btn" title="Позвонить">
                                                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                                    <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path>
                                                                </svg>
                                                            </a>
                                                            <a href="#" class="action-btn" title="Просмотр">
                                                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                                                    <circle cx="12" cy="12" r="3"></circle>
                                                                </svg>
                                                            </a>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @endif
                                        @if($contact->emails->count() > 0)
                                            <div class="contact-emails">
                                                @foreach($contact->emails as $email)
                                                    <div class="email-item">
                                                        <span class="email-address">{{ $email->email }}</span>
                                                        @if($email->is_primary)
                                                            <span class="primary-badge">Основной</span>
                                                        @endif
                                                        <div class="contact-actions">
                                                            <a href="mailto:{{ $email->email }}" class="action-btn" title="Написать письмо">
                                                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                                    <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
                                                                    <polyline points="22,6 12,13 2,6"></polyline>
                                                                </svg>
                                                            </a>
                                                            <a href="#" class="action-btn" title="Просмотр">
                                                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                                                    <circle cx="12" cy="12" r="3"></circle>
                                                                </svg>
                                                            </a>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                        <div class="contacts-edit" id="contacts_edit" style="display: none;">
                            <div class="form-group">
                                <label>Контактные лица</label>
                                <div id="contacts-container">
                                    @foreach($company->contacts as $index => $contact)
                                        <div class="contact-block" data-contact-id="{{ $contact->id }}">
                                            <div class="form-group">
                                                <label for="contact_name_{{ $index }}">ФИО контактного лица</label>
                                                <input type="text" name="contact_name[]" id="contact_name_{{ $index }}" class="form-control" placeholder="ФИО контактного лица" value="{{ $contact->name }}" required>
                                            </div>
                                            
                                            <div class="form-group">
                                                <label>Телефоны</label>
                                                <div class="phones-container">
                                                    @foreach($contact->phones as $phoneIndex => $phone)
                                                        <div class="phone-block">
                                                            <div class="phone-input-group">
                                                                <input type="tel" name="phones[{{ $index }}][]" class="form-control" placeholder="Телефон" value="{{ $phone->phone }}" required>
                                                                @if($phoneIndex > 0)
                                                                    <button type="button" class="btn btn-danger remove-phone">×</button>
                                                                @else
                                                                    <button type="button" class="btn btn-secondary add-phone">+</button>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                            
                                            <div class="form-group">
                                                <label>Email адреса</label>
                                                <div class="emails-container">
                                                    @foreach($contact->emails as $emailIndex => $email)
                                                        <div class="email-block">
                                                            <div class="email-input-group">
                                                                <input type="email" name="contact_emails[{{ $index }}][]" class="form-control" placeholder="Email" value="{{ $email->email }}">
                                                                @if($emailIndex > 0)
                                                                    <button type="button" class="btn btn-danger remove-email">×</button>
                                                                @else
                                                                    <button type="button" class="btn btn-secondary add-email">+</button>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                            
                                            <div class="form-group">
                                                <label for="position_{{ $index }}">Должность</label>
                                                <input type="text" name="position[]" id="position_{{ $index }}" class="form-control" placeholder="Должность" value="{{ $contact->position }}" required>
                                            </div>
                                            
                                            <div class="form-check">
                                                <input type="checkbox" name="main_contact[]" class="form-check-input" value="1" {{ $contact->main_contact ? 'checked' : '' }}>
                                                <label class="form-check-label">Основной контакт</label>
                                            </div>
                                            
                                            <button type="button" class="btn btn-danger remove-contact">Удалить контакт</button>
                                        </div>
                                    @endforeach
                                </div>
                                <button type="button" class="btn btn-secondary" id="add-contact">+ Добавить контактное лицо</button>
                            </div>
                        </div>
                        <!-- Кнопки действий для блока контактов -->
                        <div class="contacts-actions" id="contacts_actions" style="display: none;">
                            <button class="btn btn-primary" onclick="saveContactsBlock()">Сохранить</button>
                            <button class="btn btn-secondary" onclick="cancelContactsEdit()">Отмена</button>
                        </div>
                    </div>
                </div>
            @else
                <div class="info-block">
                    <div class="block-header">
                        <h3>Все контакты</h3>
                        <button class="edit-comment-btn" onclick="editContactsBlock()">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"></path>
                            </svg>
                            Добавить контакты
                        </button>
                    </div>
                    <div class="contacts-container">
                        <div class="contacts-content" id="contacts_content">
                            <div class="contacts-list">
                                <p style="color: #666; font-style: italic; text-align: center; padding: 20px;">Контакты не добавлены</p>
                            </div>
                        </div>
                        <div class="contacts-edit" id="contacts_edit" style="display: none;">
                            <div class="form-group">
                                <label>Контактные лица</label>
                                <div id="contacts-container">
                                    <!-- Контакты будут добавляться динамически -->
                                </div>
                                <button type="button" class="btn btn-secondary" id="add-contact">+ Добавить контактное лицо</button>
                            </div>
                        </div>
                        <!-- Кнопки действий для блока контактов -->
                        <div class="contacts-actions" id="contacts_actions" style="display: none;">
                            <button class="btn btn-primary" onclick="saveContactsBlock()">Сохранить</button>
                            <button class="btn btn-secondary" onclick="cancelContactsEdit()">Отмена</button>
                        </div>
                    </div>
                </div>
            @endif

            <div class="info-block">
                <div class="block-header">
                    <h3>Адреса</h3>
                    <button class="edit-comment-btn" onclick="editAddressesBlock()">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"></path>
                        </svg>
                        {{ $company->addresses->count() > 0 ? 'Редактировать' : 'Добавить адреса' }}
                    </button>
                </div>
                <div class="addresses-content" id="addresses_content">
                    @if($company->addresses->count() > 0)
                        <div class="addresses-list">
                            @foreach($company->addresses as $address)
                                <div class="address-item">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                                        <circle cx="12" cy="10" r="3"></circle>
                                    </svg>
                                    <span>{{ $address->address }}</span>
                                    @if($address->main_address)
                                        <span class="main-address-badge">Основной</span>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p style="color: #666; font-style: italic;">Адреса не указаны</p>
                    @endif
                </div>
                <div class="addresses-edit" id="addresses_edit" style="display: none;">
                    <form id="addressesForm">
                        <div class="form-group">
                            <label>Адреса</label>
                            <div id="addresses-container">
                                @if($company->addresses->count() > 0)
                                    @foreach($company->addresses as $address)
                                        <div class="address-block">
                                            <input type="hidden" name="addresses[{{ $loop->index }}][id]" value="{{ $address->id }}">
                                            <input type="text" name="addresses[{{ $loop->index }}][address]" class="form-control" value="{{ $address->address }}" placeholder="Введите адрес" required>
                                            <div class="form-check">
                                                <input type="checkbox" name="addresses[{{ $loop->index }}][main_address]" class="form-check-input" value="1" {{ $address->main_address ? 'checked' : '' }}>
                                                <label class="form-check-label">Основной адрес</label>
                                            </div>
                                            <button type="button" class="btn btn-danger remove-address">Удалить адрес</button>
                                        </div>
                                    @endforeach
                                @else
                                    <div class="address-block">
                                        <input type="text" name="addresses[0][address]" class="form-control" placeholder="Введите адрес" required>
                                        <div class="form-check">
                                            <input type="checkbox" name="addresses[0][main_address]" class="form-check-input" value="1">
                                            <label class="form-check-label">Основной адрес</label>
                                        </div>
                                        <button type="button" class="btn btn-danger remove-address">Удалить адрес</button>
                                    </div>
                                @endif
                            </div>
                            <button type="button" class="btn btn-secondary" id="add-address">+ Добавить адрес</button>
                        </div>
                        <div class="addresses-actions">
                            <button type="button" class="btn btn-secondary" onclick="cancelAddressesEdit()">Отмена</button>
                            <button type="button" class="btn btn-primary" onclick="saveAddresses()">Сохранить</button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="info-block">
                <div class="block-header">
                    <h3>Юридическая информация</h3>
                    <button class="edit-comment-btn" onclick="editLegalInfoBlock()">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"></path>
                        </svg>
                        {{ $company->inn ? 'Редактировать' : 'Добавить' }}
                    </button>
                </div>
                <div class="legal-info-content" id="legal_info_content">
                    @if($company->inn)
                        <div class="legal-list">
                            <div class="legal-item">
                                <span class="label">ИНН:</span>
                                <span class="value">{{ $company->inn }}</span>
                            </div>
                            <div class="legal-item">
                                <span class="label">Название:</span>
                                <span class="value">{{ $company->name }}</span>
                            </div>
                        </div>
                    @else
                        <p style="color: #666; font-style: italic;">Юридическая информация не указана</p>
                    @endif
                </div>
                <div class="legal-info-edit" id="legal_info_edit" style="display: none;">
                    <form id="legalInfoForm">
                        <div class="form-group">
                            <label for="company_name">Название компании:</label>
                            <input type="text" id="company_name" name="name" value="{{ $company->name }}" required>
                        </div>
                        <div class="form-group">
                            <label for="company_inn">ИНН:</label>
                            <input type="text" id="company_inn" name="inn" value="{{ $company->inn }}" maxlength="12" placeholder="Введите ИНН">
                        </div>
                    </form>
                </div>
                <div class="legal-info-actions" id="legal_info_actions" style="display: none;">
                    <div class="form-actions">
                        <button type="button" class="btn btn-secondary" onclick="cancelLegalInfoEdit()">Отмена</button>
                        <button type="button" class="btn btn-primary" onclick="saveLegalInfoBlock()">Сохранить</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Блок действий и событий -->
        <div class="company-actions-section">
            <div class="info-block">
                <h3>Следующие действия</h3>
                <div class="action-info">
                    @if($lastAction)
                        <div class="action-date">
                            <span class="label">Дата:</span>
                            <span class="value">{{ $lastAction->expired_at->format('d.m.Y') }}</span>
                        </div>
                        <div class="action-description">
                            <span class="label">Что требуется сделать:</span>
                            <p>{{ $lastAction->action }}</p>
                        </div>
                        <div class="action-buttons">
                            <button class="btn btn-primary" onclick="showNewActionModal()">Задать новое действие</button>
                            <button class="btn btn-secondary" onclick="showActionsModal()">Подробнее</button>
                        </div>
                    @else
                        <div class="action-description">
                            <p style="color: #666; font-style: italic;">Нет активных действий</p>
                        </div>
                        <div class="action-buttons">
                            <button class="btn btn-primary" onclick="showNewActionModal()">Задать новое действие</button>
                        </div>
                    @endif
                </div>
            </div>

            <div class="info-block">
                <h3>Лог событий</h3>
                <div class="events-list">
                    @if($lastLog)
                        <div class="event-item">
                            <div class="event-header">
                                <span class="event-type" data-color="{{ $lastLog->type ? $lastLog->type->color : '#133E71' }}">{{ $lastLog->type ? $lastLog->type->name : 'Неизвестный тип' }}</span>
                                <span class="event-date">{{ $lastLog->created_at->format('d.m.Y H:i:s') }}</span>
                            </div>
                            <div class="event-content">
                                <p>{{ $lastLog->log }}</p>
                            </div>
                            <div class="event-footer">
                                <span>Создал: {{ $lastLog->user_id ? ($lastLog->user ? $lastLog->user->name : 'Пользователь не найден') : 'Система' }}</span>
                            </div>
                        </div>
                    @else
                        <div class="event-item">
                            <div class="event-content">
                                <p style="color: #666; font-style: italic;">Логи событий отсутствуют</p>
                            </div>
                        </div>
                    @endif
                </div>
                <div class="events-actions">
                    <button class="btn btn-secondary" onclick="showLogsHistory()">История</button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Модальное окно для комментария при смене статуса -->
<div id="statusCommentModal" class="modal" style="display: none;">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Смена статуса компании</h3>
            <span class="close" onclick="cancelStatusChange()">&times;</span>
        </div>
        <div class="modal-body">
            <p>Оставьте комментарий по причине смены статуса компании.</p>
            <div class="form-group">
                <label for="statusComment">Комментарий:</label>
                <textarea id="statusComment" rows="4" placeholder="Введите комментарий..." required></textarea>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" onclick="cancelStatusChange()">Отмена</button>
            <button type="button" class="btn btn-primary" onclick="saveStatusChange()">Сохранить</button>
        </div>
    </div>
</div>

<!-- Модальное окно для истории логов -->
<div id="logsHistoryModal" class="modal" style="display: none;">
    <div class="modal-content logs-history-modal">
        <div class="modal-header">
            <h3>История логов компании</h3>
            <span class="close" onclick="closeLogsHistory()">&times;</span>
        </div>
        <div class="modal-body">
            <div id="logsHistoryContent">
                <div class="loading-spinner">
                    <div class="spinner"></div>
                    <p>Загрузка логов...</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Модальное окно для действий -->
<div id="actionsModal" class="modal" style="display: none;">
    <div class="modal-content actions-modal">
        <div class="modal-header">
            <h3>Список необходимых действий</h3>
            <span class="close" onclick="closeActionsModal()">&times;</span>
        </div>
        <div class="modal-body">
            <div id="actionsList">
                <div class="loading-spinner">
                    <div class="spinner"></div>
                    <p>Загрузка действий...</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Модальное окно для комментария к выполненному действию -->
<div id="actionCommentModal" class="modal" style="display: none;">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Завершение действия</h3>
            <span class="close" onclick="closeActionCommentModal()">&times;</span>
        </div>
        <div class="modal-body">
            <p>Оставьте комментарий о выполнении действия.</p>
            <div class="form-group">
                <label for="actionComment">Комментарий:</label>
                <textarea id="actionComment" rows="4" placeholder="Введите комментарий..." required></textarea>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" onclick="closeActionCommentModal()">Отмена</button>
            <button type="button" class="btn btn-primary" onclick="saveActionComment()">Сохранить</button>
        </div>
    </div>
</div>

<!-- Модальное окно для создания нового действия -->
<div id="newActionModal" class="modal" style="display: none;">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Создать новое действие</h3>
            <span class="close" onclick="closeNewActionModal()">&times;</span>
        </div>
        <div class="modal-body">
            <form id="newActionForm">
                <div class="form-group">
                    <label for="actionExpiredAt">Дата истечения срока задачи:</label>
                    <input type="date" id="actionExpiredAt" name="expired_at" required min="{{ date('Y-m-d', strtotime('+1 day')) }}">
                </div>
                <div class="form-group">
                    <label for="actionDescription">Что требуется сделать:</label>
                    <textarea id="actionDescription" name="action" rows="4" placeholder="Опишите задачу..." required maxlength="1000"></textarea>
                    <div class="char-counter">
                        <span id="charCount">0</span>/1000
                    </div>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" onclick="closeNewActionModal()">Отмена</button>
            <button type="button" class="btn btn-primary" onclick="saveNewAction()">Создать</button>
        </div>
    </div>
</div>

<style>
.company-item-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 20px;
}

.company-header {
    margin-bottom: 30px;
}

.breadcrumb {
    font-size: 14px;
    color: #666;
    margin-bottom: 10px;
}

.breadcrumb a {
    color: #133E71;
    text-decoration: none;
}

.breadcrumb a:hover {
    text-decoration: underline;
}

.company-header-actions {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 10px;
}

.company-title {
    font-size: 28px;
    color: #133E71;
    margin: 0;
    font-weight: 600;
}

.company-actions {
    display: flex;
    gap: 10px;
}

.company-actions .btn {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 8px 16px;
    border-radius: 6px;
    text-decoration: none;
    font-size: 14px;
    font-weight: 500;
    transition: all 0.3s ease;
    border: 1px solid;
}

.company-actions .btn-primary {
    background-color: #133E71;
    color: white;
    border-color: #133E71;
}

.company-actions .btn-primary:hover {
    background-color: #0f2d56;
    border-color: #0f2d56;
}

/* Стили для модального окна */
.modal {
    display: none;
    position: fixed;
    z-index: 10000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.5);
}

.modal-content {
    background-color: #fefefe;
    margin: 5% auto;
    padding: 0;
    border-radius: 8px;
    width: 90%;
    max-width: 500px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
}

.modal-header {
    padding: 20px 20px 0 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.modal-header h3 {
    margin: 0;
    color: #133E71;
    font-size: 18px;
    font-weight: 600;
}

.close {
    color: #aaa;
    font-size: 28px;
    font-weight: bold;
    cursor: pointer;
    line-height: 1;
}

.close:hover,
.close:focus {
    color: #000;
}

.modal-body {
    padding: 20px;
}

.modal-body p {
    margin: 0 0 15px 0;
    color: #666;
}

.form-group {
    margin-bottom: 15px;
}

.form-group label {
    display: block;
    margin-bottom: 5px;
    font-weight: 500;
    color: #333;
}

.form-group textarea {
    width: 100%;
    padding: 10px;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 14px;
    resize: vertical;
    min-height: 80px;
}

.form-group textarea:focus {
    outline: none;
    border-color: #133E71;
    box-shadow: 0 0 0 2px rgba(19, 62, 113, 0.1);
}

.form-group input[type="date"] {
    width: 100%;
    padding: 10px;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 14px;
    font-family: inherit;
}

.form-group input[type="date"]:focus {
    outline: none;
    border-color: #133E71;
    box-shadow: 0 0 0 2px rgba(19, 62, 113, 0.1);
}

.modal-footer {
    padding: 0 20px 20px 20px;
    display: flex;
    justify-content: flex-end;
    gap: 10px;
}

.modal-footer .btn {
    padding: 8px 16px;
    border-radius: 4px;
    font-size: 14px;
    font-weight: 500;
    cursor: pointer;
    border: 1px solid;
    transition: all 0.3s ease;
}

.modal-footer .btn-secondary {
    background-color: #6c757d;
    color: white;
    border-color: #6c757d;
}

.modal-footer .btn-secondary:hover {
    background-color: #5a6268;
    border-color: #5a6268;
}

.modal-footer .btn-primary {
    background-color: #133E71;
    color: white;
    border-color: #133E71;
}

.modal-footer .btn-primary:hover {
    background-color: #0f2d56;
    border-color: #0f2d56;
}

/* Стили для модального окна истории логов */
.logs-history-modal {
    max-height: 600px;
    display: flex;
    flex-direction: column;
}

.logs-history-modal .modal-body {
    flex: 1;
    overflow-y: auto;
    max-height: 500px;
}

.logs-history-modal .modal-body::-webkit-scrollbar {
    width: 8px;
}

.logs-history-modal .modal-body::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 4px;
}

.logs-history-modal .modal-body::-webkit-scrollbar-thumb {
    background: #c1c1c1;
    border-radius: 4px;
}

.logs-history-modal .modal-body::-webkit-scrollbar-thumb:hover {
    background: #a8a8a8;
}

/* Стили для модального окна действий */
.actions-modal {
    max-height: 600px;
    display: flex;
    flex-direction: column;
}

.actions-modal .modal-body {
    flex: 1;
    overflow-y: auto;
    max-height: 500px;
}

.actions-modal .modal-body::-webkit-scrollbar {
    width: 8px;
}

.actions-modal .modal-body::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 4px;
}

.actions-modal .modal-body::-webkit-scrollbar-thumb {
    background: #c1c1c1;
    border-radius: 4px;
}

.actions-modal .modal-body::-webkit-scrollbar-thumb:hover {
    background: #a8a8a8;
}

/* Стили для списка действий */
.actions-list {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.action-item {
    background: #f8f9fa;
    border-radius: 8px;
    padding: 15px;
    border: 1px solid #e9ecef;
    transition: all 0.3s ease;
}

.action-item.completed {
    background: #e8f5e8;
    border-color: #28a745;
}

.action-item.completed .action-text {
    text-decoration: line-through;
    color: #666;
}

.action-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 10px;
}

.action-text {
    font-weight: 500;
    color: #495057;
    line-height: 1.5;
    margin: 0;
    flex-grow: 1;
}

.action-date {
    color: #666;
    font-size: 12px;
    margin-left: 15px;
}

.action-button {
    background: #28a745;
    color: white;
    border: none;
    padding: 6px 12px;
    border-radius: 4px;
    font-size: 12px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.3s ease;
    margin-left: 10px;
}

.action-button:hover {
    background: #218838;
}

.action-button:disabled {
    background: #6c757d;
    cursor: not-allowed;
}

.action-comment-block {
    margin-top: 10px;
    padding: 10px;
    background: white;
    border-radius: 4px;
    border: 1px solid #e9ecef;
    display: none;
}

.action-comment-block.show {
    display: block;
    animation: fadeIn 0.3s ease;
}

.action-comment-textarea {
    width: 100%;
    padding: 8px;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 14px;
    resize: vertical;
    min-height: 60px;
    margin-bottom: 10px;
}

.action-comment-textarea:focus {
    outline: none;
    border-color: #133E71;
    box-shadow: 0 0 0 2px rgba(19, 62, 113, 0.1);
}

.action-comment-buttons {
    display: flex;
    gap: 8px;
    justify-content: flex-end;
}

@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Стили для спиннера загрузки */
.loading-spinner {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 40px 20px;
}

.spinner {
    width: 40px;
    height: 40px;
    border: 4px solid #f3f3f3;
    border-top: 4px solid #133E71;
    border-radius: 50%;
    animation: spin 1s linear infinite;
    margin-bottom: 15px;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

.loading-spinner p {
    color: #666;
    margin: 0;
    font-size: 14px;
}

/* Стили для счетчика символов */
.char-counter {
    font-size: 12px;
    color: #666;
    text-align: right;
    margin-top: 5px;
}

.char-counter span {
    font-weight: bold;
}

/* Стили для списка логов в модальном окне */
.logs-history-list {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.logs-history-item {
    background: #f8f9fa;
    border-radius: 8px;
    padding: 15px;
    border: 1px solid #e9ecef;
}

.logs-history-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 10px;
}

.logs-history-type {
    background: #133E71;
    color: white;
    padding: 4px 8px;
    border-radius: 12px;
    font-size: 10px;
    font-weight: 600;
}

.logs-history-date {
    color: #666;
    font-size: 12px;
}

.logs-history-content {
    margin-bottom: 10px;
}

.logs-history-content p {
    color: #495057;
    line-height: 1.5;
    margin: 0;
}

.logs-history-footer {
    color: #666;
    font-size: 12px;
}

.company-actions .btn svg {
    width: 16px;
    height: 16px;
}

.company-sku {
    font-size: 14px;
    color: #666;
    background: #f8f9fa;
    padding: 5px 10px;
    border-radius: 4px;
    display: inline-block;
}

.company-content {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 40px;
    align-items: start;
}

.company-info-section {
    display: flex;
    flex-direction: column;
    gap: 25px;
}

.company-actions-section {
    display: flex;
    flex-direction: column;
    gap: 25px;
}

.info-block {
    background: white;
    border-radius: 10px;
    padding: 25px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.info-block h3 {
    font-size: 18px;
    color: #133E71;
    margin-bottom: 20px;
    font-weight: 600;
    border-bottom: 2px solid #e9ecef;
    padding-bottom: 10px;
}

.info-list {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.info-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 10px 0;
    border-bottom: 1px solid #f1f3f4;
}

.info-item:last-child {
    border-bottom: none;
}

.label {
    font-weight: 600;
    color: #495057;
    flex-shrink: 0;
    margin-right: 15px;
}

.value {
    color: #333;
    text-align: right;
    flex-grow: 1;
}

.status-badge {
    display: inline-block;
    padding: 6px 12px;
    border-radius: 20px;
    font-weight: 600;
    text-align: center;
    font-size: 12px;
}

.status-1 { background-color: #e3f2fd; color: #1976d2; }
.status-2 { background-color: #fff3e0; color: #f57c00; }
.status-3 { background-color: #e8f5e8; color: #388e3c; }
.status-4 { background-color: #fce4ec; color: #c2185b; }
.status-unknown { background-color: #f5f5f5; color: #666; }

.status-selector {
    position: relative;
    display: inline-block;
}

.status-badge.clickable {
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 5px;
    transition: all 0.3s ease;
}

.status-badge.clickable:hover {
    transform: translateY(-1px);
    box-shadow: 0 2px 8px rgba(0,0,0,0.15);
}

.dropdown-arrow {
    transition: transform 0.3s ease;
}

.status-dropdown {
    position: absolute;
    top: 100%;
    left: 0;
    right: 0;
    background: white;
    border: 1px solid #e9ecef;
    border-radius: 8px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    z-index: 1000;
    display: none;
    min-width: 150px;
    margin-top: 5px;
}

.status-dropdown.show {
    display: block;
    animation: fadeInDown 0.3s ease;
}

.status-option {
    padding: 8px 12px;
    cursor: pointer;
    transition: background-color 0.2s ease;
}

.status-option:hover {
    background-color: #f8f9fa;
}

.status-option:first-child {
    border-radius: 8px 8px 0 0;
}

.status-option:last-child {
    border-radius: 0 0 8px 8px;
}

@keyframes fadeInDown {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.company-description {
    margin-top: 20px;
    padding-top: 20px;
    border-top: 1px solid #f1f3f4;
}

.description-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 10px;
}

.company-description p {
    margin-top: 10px;
    color: #495057;
    line-height: 1.6;
    background: #f8f9fa;
    padding: 15px;
    border-radius: 6px;
}

.contact-list {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.contact-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 10px 0;
    border-bottom: 1px solid #f1f3f4;
}

.contact-item:last-child {
    border-bottom: none;
}

.link {
    color: #133E71;
    text-decoration: none;
}

.link:hover {
    text-decoration: underline;
}

.contacts-list {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.contact-card {
    background: #f8f9fa;
    border-radius: 8px;
    padding: 15px;
    border: 1px solid #e9ecef;
}

.contact-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 8px;
}

.contact-name {
    font-weight: 600;
    color: #133E71;
    font-size: 16px;
}

.main-contact-badge {
    background: #28a745;
    color: white;
    padding: 2px 8px;
    border-radius: 12px;
    font-size: 10px;
    font-weight: 600;
}

.contact-position {
    color: #666;
    font-size: 14px;
    margin-bottom: 10px;
}

.contact-phones {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.contact-emails {
    display: flex;
    flex-direction: column;
    gap: 8px;
    margin-top: 10px;
}

.email-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 8px 12px;
    background: white;
    border-radius: 6px;
    border: 1px solid #e9ecef;
}

.email-address {
    font-weight: 500;
    color: #333;
    display: flex;
    align-items: center;
    gap: 8px;
}

.primary-badge {
    background: #28a745;
    color: white;
    padding: 2px 6px;
    border-radius: 10px;
    font-size: 10px;
    font-weight: 600;
}

.phone-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 8px 12px;
    background: white;
    border-radius: 6px;
    border: 1px solid #e9ecef;
}

.phone-number {
    font-weight: 500;
    color: #333;
}

.contact-actions {
    display: flex;
    gap: 5px;
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

.addresses-list {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.address-item {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 12px;
    background: #f8f9fa;
    border-radius: 6px;
    border: 1px solid #e9ecef;
}

.address-item svg {
    color: #133E71;
    flex-shrink: 0;
}

.legal-list {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.legal-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 10px 0;
    border-bottom: 1px solid #f1f3f4;
}

.legal-item:last-child {
    border-bottom: none;
}

.action-info {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.action-date {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 10px 0;
    border-bottom: 1px solid #f1f3f4;
}

.action-description {
    padding: 10px 0;
}

.action-description p {
    margin-top: 8px;
    color: #495057;
    line-height: 1.5;
    background: #f8f9fa;
    padding: 10px;
    border-radius: 4px;
}

.action-buttons {
    display: flex;
    gap: 10px;
    margin-top: 15px;
}

.btn {
    padding: 8px 16px;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    font-size: 14px;
    font-weight: 500;
    transition: all 0.3s ease;
    text-decoration: none;
    display: inline-block;
}

.btn-primary {
    background: #133E71;
    color: white;
}

.btn-primary:hover {
    background: #1C5BA4;
}

.btn-secondary {
    background: #6c757d;
    color: white;
}

.btn-secondary:hover {
    background: #5a6268;
}

.events-list {
    display: flex;
    flex-direction: column;
    gap: 15px;
    margin-bottom: 20px;
}

.event-item {
    background: #f8f9fa;
    border-radius: 8px;
    padding: 15px;
    border: 1px solid #e9ecef;
}

.event-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 10px;
}

.event-type {
    background: #133E71;
    color: white;
    padding: 4px 8px;
    border-radius: 12px;
    font-size: 10px;
    font-weight: 600;
}

.event-date {
    color: #666;
    font-size: 12px;
}

.event-content {
    margin-bottom: 10px;
}

.event-content p {
    color: #495057;
    line-height: 1.5;
    margin: 0;
}

.event-footer {
    color: #666;
    font-size: 12px;
}

.events-actions {
    display: flex;
    justify-content: flex-end;
}

/* Адаптивность */
@media (max-width: 768px) {
    .company-content {
        grid-template-columns: 1fr;
        gap: 20px;
    }
    
    .company-title {
        font-size: 24px;
    }
    
    .info-item {
        flex-direction: column;
        align-items: flex-start;
        gap: 5px;
    }
    
    .value {
        text-align: left;
    }
    
    .contact-item {
        flex-direction: column;
        align-items: flex-start;
        gap: 5px;
    }
    
    .action-buttons {
        flex-direction: column;
    }
    
    .action-buttons .btn {
        width: 100%;
        text-align: center;
    }
}

/* Стили для заголовка блока */
.block-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
}

.block-header h3 {
    margin: 0;
    border-bottom: none;
    padding-bottom: 0;
}

.edit-comment-btn {
    display: flex;
    align-items: center;
    gap: 6px;
    background: none;
    border: 1px solid #133E71;
    color: #133E71;
    padding: 6px 12px;
    border-radius: 6px;
    font-size: 12px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.3s ease;
}

.edit-comment-btn:hover {
    background-color: #133E71;
    color: white;
    transform: translateY(-1px);
}

.edit-comment-btn svg {
    width: 14px;
    height: 14px;
}

/* Стили для контейнеров редактирования */
.main-info-container,
.contact-info-container,
.contacts-container,
.addresses-container,
.legal-info-container {
    margin-top: 15px;
}

.main-info-content,
.contact-info-content,
.contacts-content,
.addresses-content,
.legal-info-content {
    margin-bottom: 15px;
}

.main-info-edit,
.contact-info-edit,
.contacts-edit,
.addresses-edit,
.legal-info-edit {
    margin-bottom: 15px;
}

.main-info-edit .form-group,
.contact-info-edit .form-group,
.contacts-edit .form-group,
.addresses-edit .form-group,
.legal-info-edit .form-group {
    margin-bottom: 15px;
}

.main-info-edit .form-group label,
.contact-info-edit .form-group label,
.contacts-edit .form-group label,
.addresses-edit .form-group label,
.legal-info-edit .form-group label {
    display: block;
    font-weight: 600;
    color: #333;
    margin-bottom: 5px;
    font-size: 14px;
}

.main-info-edit .form-group textarea,
.contact-info-edit .form-group textarea,
.contacts-edit .form-group textarea,
.addresses-edit .form-group textarea,
.legal-info-edit .form-group textarea,
.main-info-edit .form-group input,
.contact-info-edit .form-group input,
.contacts-edit .form-group input,
.addresses-edit .form-group input,
.legal-info-edit .form-group input {
    width: 100%;
    padding: 10px;
    border: 1px solid #ddd;
    border-radius: 6px;
    font-size: 14px;
    font-family: inherit;
    resize: vertical;
    height: 42px;
    box-sizing: border-box;
    transition: border-color 0.3s ease;
}

.main-info-edit .form-group textarea:focus,
.contact-info-edit .form-group textarea:focus,
.contacts-edit .form-group textarea:focus,
.addresses-edit .form-group textarea:focus,
.legal-info-edit .form-group textarea:focus,
.main-info-edit .form-group input:focus,
.contact-info-edit .form-group input:focus,
.contacts-edit .form-group input:focus,
.addresses-edit .form-group input:focus,
.legal-info-edit .form-group input:focus {
    outline: none;
    border-color: #133E71;
    box-shadow: 0 0 0 2px rgba(19, 62, 113, 0.1);
}

.main-info-actions,
.contact-info-actions,
.contacts-actions,
.addresses-actions,
.legal-info-actions {
    display: flex;
    gap: 10px;
    justify-content: flex-start;
}

.main-info-actions .btn,
.contact-info-actions .btn,
.contacts-actions .btn,
.addresses-actions .btn,
.legal-info-actions .btn {
    padding: 8px 16px;
    border-radius: 6px;
    font-size: 14px;
    font-weight: 500;
    border: 1px solid;
    cursor: pointer;
    transition: all 0.3s ease;
}

.main-info-actions .btn-primary,
.contact-info-actions .btn-primary,
.contacts-actions .btn-primary,
.addresses-actions .btn-primary,
.legal-info-actions .btn-primary {
    background-color: #133E71;
    color: white;
    border-color: #133E71;
}

/* Стили для формы контактов */
.contact-block {
    background: #f8f9fa;
    border: 1px solid #e9ecef;
    border-radius: 8px;
    padding: 20px;
    margin-bottom: 20px;
    position: relative;
}

.contact-block:last-child {
    margin-bottom: 0;
}

.contact-block input[type="text"],
.contact-block input[type="tel"],
.contact-block input[type="email"] {
    width: 100%;
    padding: 12px;
    border: 1px solid #ddd;
    border-radius: 6px;
    font-size: 14px;
    font-family: inherit;
    margin-bottom: 15px;
    transition: border-color 0.3s ease;
    height: 42px;
    box-sizing: border-box;
}

.contact-block input[type="text"]:focus,
.contact-block input[type="tel"]:focus,
.contact-block input[type="email"]:focus {
    outline: none;
    border-color: #133E71;
    box-shadow: 0 0 0 2px rgba(19, 62, 113, 0.1);
}

.phones-container,
.emails-container {
    margin-bottom: 15px;
}

.phone-block,
.email-block {
    margin-bottom: 10px;
}

.phone-input-group,
.email-input-group {
    display: flex;
    gap: 10px;
    align-items: center;
}

.phone-input-group input,
.email-input-group input {
    flex: 1;
    margin-bottom: 0;
}

.phone-input-group .btn,
.email-input-group .btn {
    padding: 8px 12px;
    border-radius: 4px;
    font-size: 12px;
    font-weight: 500;
    border: 1px solid;
    cursor: pointer;
    transition: all 0.3s ease;
    min-width: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.phone-input-group .btn-secondary,
.email-input-group .btn-secondary {
    background-color: #6c757d;
    color: white;
    border-color: #6c757d;
}

.phone-input-group .btn-secondary:hover,
.email-input-group .btn-secondary:hover {
    background-color: #5a6268;
    border-color: #545b62;
}

.phone-input-group .btn-danger,
.email-input-group .btn-danger {
    background-color: #dc3545;
    color: white;
    border-color: #dc3545;
}

.phone-input-group .btn-danger:hover,
.email-input-group .btn-danger:hover {
    background-color: #c82333;
    border-color: #bd2130;
}

.form-check {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 15px;
}

.form-check-input {
    margin: 0;
    cursor: pointer;
    width: 15px !important;
    height: 15px !important;
    min-width: 15px !important;
    max-width: 15px !important;
    min-height: 15px !important;
    max-height: 15px !important;
    box-sizing: border-box !important;
}

.form-check-label {
    font-size: 14px;
    color: #495057;
    cursor: pointer;
    margin: 0;
}

/* Стили для labels в форме контактов */
.contact-block label {
    display: block;
    font-weight: 600;
    color: #333;
    margin-bottom: 5px;
    font-size: 14px;
}

.contact-block .form-group {
    margin-bottom: 15px;
}

.remove-contact {
    position: absolute;
    top: 15px;
    right: 15px;
    background-color: #dc3545;
    color: white;
    border: 1px solid #dc3545;
    padding: 6px 12px;
    border-radius: 4px;
    font-size: 12px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.3s ease;
}

.remove-contact:hover {
    background-color: #c82333;
    border-color: #bd2130;
}

/* Стили для формы адресов */
.address-block {
    background: #f8f9fa;
    border: 1px solid #e9ecef;
    border-radius: 8px;
    padding: 20px;
    margin-bottom: 20px;
    position: relative;
}

.address-block:last-child {
    margin-bottom: 0;
}

.address-block input[type="text"] {
    width: 100%;
    padding: 12px;
    border: 1px solid #ddd;
    border-radius: 6px;
    font-size: 14px;
    font-family: inherit;
    margin-bottom: 15px;
    transition: border-color 0.3s ease;
    height: 42px;
    box-sizing: border-box;
}

.address-block input[type="text"]:focus {
    outline: none;
    border-color: #133E71;
    box-shadow: 0 0 0 2px rgba(19, 62, 113, 0.1);
}

.remove-address {
    position: absolute;
    top: 15px;
    right: 15px;
    background-color: #dc3545;
    color: white;
    border: 1px solid #dc3545;
    padding: 6px 12px;
    border-radius: 4px;
    font-size: 12px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.3s ease;
}

.remove-address:hover {
    background-color: #c82333;
    border-color: #bd2130;
}

/* Стили для бейджа основного адреса */
.main-address-badge {
    background-color: #28a745;
    color: white;
    padding: 2px 8px;
    border-radius: 12px;
    font-size: 11px;
    font-weight: 500;
    margin-left: 8px;
}

/* Стили для кнопки добавления адреса */
#add-address {
    margin-top: 10px;
    background-color: #6c757d;
    color: white;
    border: 1px solid #6c757d;
    padding: 8px 16px;
    border-radius: 6px;
    font-size: 14px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.3s ease;
}

#add-address:hover {
    background-color: #5a6268;
    border-color: #545b62;
}
.addresses-actions .btn-primary,
.legal-info-actions .btn-primary {
    background-color: #133E71;
    color: white;
    border-color: #133E71;
}

.main-info-actions .btn-primary:hover,
.contact-info-actions .btn-primary:hover,
.contacts-actions .btn-primary:hover,
.addresses-actions .btn-primary:hover,
.legal-info-actions .btn-primary:hover {
    background-color: #1C5BA4;
    border-color: #1C5BA4;
}

.main-info-actions .btn-secondary,
.contact-info-actions .btn-secondary,
.contacts-actions .btn-secondary,
.addresses-actions .btn-secondary,
.legal-info-actions .btn-secondary {
    background-color: #6c757d;
    color: white;
    border-color: #6c757d;
}

.main-info-actions .btn-secondary:hover,
.contact-info-actions .btn-secondary:hover,
.contacts-actions .btn-secondary:hover,
.addresses-actions .btn-secondary:hover,
.legal-info-actions .btn-secondary:hover {
    background-color: #5a6268;
    border-color: #5a6268;
}

.main-info-actions .btn:disabled,
.contact-info-actions .btn:disabled,
.contacts-actions .btn:disabled,
.addresses-actions .btn:disabled,
.legal-info-actions .btn:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

/* Стили для редактирования контактов */
.contact-block {
    background: #f8f9fa;
    border: 1px solid #e9ecef;
    border-radius: 8px;
    padding: 20px;
    margin-bottom: 20px;
    position: relative;
}

.contact-block:last-child {
    margin-bottom: 0;
}

.contact-block input[type="text"],
.contact-block input[type="tel"],
.contact-block input[type="email"] {
    width: 100%;
    padding: 10px;
    border: 1px solid #ddd;
    border-radius: 6px;
    font-size: 14px;
    margin-bottom: 15px;
    font-family: inherit;
}

.contact-block input:focus {
    outline: none;
    border-color: #133E71;
    box-shadow: 0 0 0 2px rgba(19, 62, 113, 0.1);
}

.phones-container,
.emails-container {
    margin-bottom: 15px;
}

.phone-block,
.email-block {
    margin-bottom: 10px;
}

.phone-input-group,
.email-input-group {
    display: flex;
    gap: 10px;
    align-items: center;
}

.phone-input-group input,
.email-input-group input {
    flex: 1;
    margin-bottom: 0;
}

.phone-input-group .btn,
.email-input-group .btn {
    padding: 10px 15px;
    border-radius: 6px;
    font-size: 14px;
    font-weight: 500;
    cursor: pointer;
    border: 1px solid;
    transition: all 0.3s ease;
    white-space: nowrap;
}

.phone-input-group .btn-danger,
.email-input-group .btn-danger {
    background-color: #dc3545;
    color: white;
    border-color: #dc3545;
}

.phone-input-group .btn-danger:hover,
.email-input-group .btn-danger:hover {
    background-color: #c82333;
    border-color: #bd2130;
}

.add-phone,
.add-email {
    background-color: #6c757d;
    color: white;
    border-color: #6c757d;
    margin-top: 10px;
}

.add-phone:hover,
.add-email:hover {
    background-color: #5a6268;
    border-color: #545b62;
}

.form-check {
    margin-bottom: 15px;
}

.form-check-input {
    margin-right: 8px;
    width: 15px !important;
    height: 15px !important;
    min-width: 15px !important;
    max-width: 15px !important;
    min-height: 15px !important;
    max-height: 15px !important;
    box-sizing: border-box !important;
}

.form-check-label {
    font-size: 14px;
    color: #495057;
}

.remove-contact {
    background-color: #dc3545;
    color: white;
    border-color: #dc3545;
    padding: 8px 16px;
    border-radius: 6px;
    font-size: 14px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.3s ease;
    border: 1px solid;
}

.remove-contact:hover {
    background-color: #c82333;
    border-color: #bd2130;
}

#add-contact {
    background-color: #28a745;
    color: white;
    border-color: #28a745;
    padding: 10px 20px;
    border-radius: 6px;
    font-size: 14px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.3s ease;
    border: 1px solid;
    margin-top: 20px;
}

#add-contact:hover {
    background-color: #218838;
    border-color: #1e7e34;
}

/* Дополнительные стили для input полей в группах */
.phone-input-group input,
.email-input-group input {
    height: 42px !important;
    box-sizing: border-box !important;
}

/* Исключение чекбоксов из общих стилей input */
.contact-block input[type="checkbox"],
.address-block input[type="checkbox"],
.form-check input[type="checkbox"] {
    width: 15px !important;
    height: 15px !important;
    min-width: 15px !important;
    max-width: 15px !important;
    min-height: 15px !important;
    max-height: 15px !important;
    box-sizing: border-box !important;
    margin: 0 !important;
    padding: 0 !important;
}
</style>

<script>
let currentCompanyId = '{{ $company->id }}';

function toggleStatusDropdown() {
    const dropdown = document.getElementById('statusDropdown');
    const arrow = document.querySelector('.dropdown-arrow');
    
    if (dropdown.classList.contains('show')) {
        dropdown.classList.remove('show');
        arrow.style.transform = 'rotate(0deg)';
    } else {
        dropdown.classList.add('show');
        arrow.style.transform = 'rotate(180deg)';
    }
}

// Глобальные переменные для хранения данных о смене статуса
let pendingStatusChange = null;

function changeStatus(element) {
    // Получаем данные из data-атрибутов
    const statusId = element.getAttribute('data-status-id');
    const statusName = element.getAttribute('data-status-name');
    
    // Сохраняем данные о смене статуса
    pendingStatusChange = {
        statusId: statusId,
        statusName: statusName
    };
    
    // Показываем модальное окно для комментария
    showStatusModal();
}

function showStatusModal() {
    const modal = document.getElementById('statusCommentModal');
    const textarea = document.getElementById('statusComment');
    
    // Очищаем поле комментария
    textarea.value = '';
    
    // Показываем модальное окно
    modal.style.display = 'block';
    
    // Фокусируемся на поле комментария
    textarea.focus();
}

function closeStatusModal() {
    const modal = document.getElementById('statusCommentModal');
    modal.style.display = 'none';
    
    // НЕ сбрасываем данные о смене статуса при закрытии модального окна
    // pendingStatusChange = null;
}

function cancelStatusChange() {
    const modal = document.getElementById('statusCommentModal');
    modal.style.display = 'none';
    
    // Сбрасываем данные о смене статуса при отмене
    pendingStatusChange = null;
}

function saveStatusChange() {
    const comment = document.getElementById('statusComment').value.trim();
    
    if (!comment) {
        alert('Пожалуйста, введите комментарий');
        return;
    }
    
    if (!pendingStatusChange || !pendingStatusChange.statusId || !pendingStatusChange.statusName) {
        alert('Ошибка: данные о смене статуса не найдены. Пожалуйста, выберите статус заново.');
        closeStatusModal();
        return;
    }
    
    // Показываем индикатор загрузки
    const statusBadge = document.querySelector('.status-badge.clickable');
    const originalContent = statusBadge.innerHTML;
    statusBadge.innerHTML = '<span>Обновление...</span>';
    
    // Закрываем модальное окно
    closeStatusModal();
    
    // Отправляем запрос на сервер
    fetch(`/company/${currentCompanyId}/status`, {
        method: 'PATCH',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            status_id: pendingStatusChange.statusId,
            comment: comment
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Обновляем отображение статуса
            statusBadge.className = `status-badge status-${pendingStatusChange.statusId} clickable`;
            statusBadge.innerHTML = `
                ${pendingStatusChange.statusName}
                <svg class="dropdown-arrow" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="6,9 12,15 18,9"></polyline>
                </svg>
            `;
            
            // Закрываем выпадающий список
            toggleStatusDropdown();
            
            // Обновляем лог событий, если получен новый лог
            if (data.log) {
                updateEventsLog(data.log);
            }
            
            // Показываем уведомление об успехе
            showNotification('Статус успешно обновлен', 'success');
        } else {
            throw new Error(data.message || 'Ошибка при обновлении статуса');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        // Возвращаем оригинальное содержимое при ошибке
        statusBadge.innerHTML = originalContent;
        showNotification('Ошибка при обновлении статуса', 'error');
    })
    .finally(() => {
        // Сбрасываем данные о смене статуса только после завершения операции
        if (pendingStatusChange) {
            pendingStatusChange = null;
        }
    });
}

function updateEventsLog(log) {
    const eventsList = document.querySelector('.events-list');
    
    // Создаем новый элемент лога
    const eventItem = document.createElement('div');
    eventItem.className = 'event-item';
    
    const eventHeader = document.createElement('div');
    eventHeader.className = 'event-header';
    
    const eventType = document.createElement('span');
    eventType.className = 'event-type';
    eventType.textContent = log.type ? log.type.name : 'Неизвестный тип';
    if (log.type && log.type.color) {
        eventType.style.backgroundColor = log.type.color;
    }
    
    const eventDate = document.createElement('span');
    eventDate.className = 'event-date';
    eventDate.textContent = new Date(log.created_at).toLocaleString('ru-RU');
    
    eventHeader.appendChild(eventType);
    eventHeader.appendChild(eventDate);
    
    const eventContent = document.createElement('div');
    eventContent.className = 'event-content';
    const contentParagraph = document.createElement('p');
    contentParagraph.textContent = log.log;
    eventContent.appendChild(contentParagraph);
    
    const eventFooter = document.createElement('div');
    eventFooter.className = 'event-footer';
    const footerSpan = document.createElement('span');
    footerSpan.textContent = `Создал: ${log.user ? log.user.name : 'Система'}`;
    eventFooter.appendChild(footerSpan);
    
    eventItem.appendChild(eventHeader);
    eventItem.appendChild(eventContent);
    eventItem.appendChild(eventFooter);
    
    // Добавляем новый лог в начало списка
    if (eventsList.firstChild) {
        eventsList.insertBefore(eventItem, eventsList.firstChild);
    } else {
        eventsList.appendChild(eventItem);
    }
    
    // Удаляем сообщение об отсутствии логов, если оно есть
    const noLogsMessage = eventsList.querySelector('.event-item p[style*="color: #666"]');
    if (noLogsMessage) {
        noLogsMessage.parentElement.parentElement.remove();
    }
}

function showNotification(message, type) {
    // Создаем уведомление
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    notification.textContent = message;
    
    // Добавляем стили для уведомления
    let backgroundColor;
    if (type === 'success') {
        backgroundColor = '#28a745';
    } else if (type === 'error') {
        backgroundColor = '#dc3545';
    } else if (type === 'info') {
        backgroundColor = '#17a2b8';
    } else {
        backgroundColor = '#6c757d';
    }
    
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        padding: 12px 20px;
        border-radius: 6px;
        color: white;
        font-weight: 500;
        z-index: 10000;
        animation: slideInRight 0.3s ease;
        background-color: ${backgroundColor};
    `;
    
    document.body.appendChild(notification);
    
    // Удаляем уведомление через 3 секунды
    setTimeout(() => {
        notification.style.animation = 'slideOutRight 0.3s ease';
        setTimeout(() => {
            document.body.removeChild(notification);
        }, 300);
    }, 3000);
}

// Закрытие выпадающего списка при клике вне его
document.addEventListener('click', function(event) {
    const statusSelector = document.querySelector('.status-selector');
    const dropdown = document.getElementById('statusDropdown');
    
    if (!statusSelector.contains(event.target) && dropdown.classList.contains('show')) {
        toggleStatusDropdown();
    }
});

// Закрытие модального окна при клике вне его
document.addEventListener('click', function(event) {
    const statusModal = document.getElementById('statusCommentModal');
    const logsModal = document.getElementById('logsHistoryModal');
    
    if (event.target === statusModal) {
        cancelStatusChange();
    }
    
    if (event.target === logsModal) {
        closeLogsHistory();
    }
});

// Закрытие модального окна при нажатии Escape
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        const statusModal = document.getElementById('statusCommentModal');
        const logsModal = document.getElementById('logsHistoryModal');
        
        if (statusModal && statusModal.style.display === 'block') {
            cancelStatusChange();
        }
        
        if (logsModal && logsModal.style.display === 'block') {
            closeLogsHistory();
        }
    }
});

// Добавляем стили для анимаций уведомлений
const style = document.createElement('style');
style.textContent = `
    @keyframes slideInRight {
        from {
            transform: translateX(100%);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }
    
    @keyframes slideOutRight {
        from {
            transform: translateX(0);
            opacity: 1;
        }
        to {
            transform: translateX(100%);
            opacity: 0;
        }
    }
`;
document.head.appendChild(style);

// Устанавливаем цвета для типов событий
document.addEventListener('DOMContentLoaded', function() {
    const eventTypes = document.querySelectorAll('.event-type[data-color]');
    eventTypes.forEach(function(element) {
        const color = element.getAttribute('data-color');
        element.style.backgroundColor = color;
    });
});

// Функции для работы с историей логов
function showLogsHistory() {
    const modal = document.getElementById('logsHistoryModal');
    modal.style.display = 'block';
    
    // Загружаем логи
    loadLogsHistory();
}

function closeLogsHistory() {
    const modal = document.getElementById('logsHistoryModal');
    modal.style.display = 'none';
}

function loadLogsHistory() {
    const content = document.getElementById('logsHistoryContent');
    
    // Показываем спиннер загрузки
    content.innerHTML = `
        <div class="loading-spinner">
            <div class="spinner"></div>
            <p>Загрузка логов...</p>
        </div>
    `;
    
    // Отправляем запрос на сервер
    fetch(`/company/${currentCompanyId}/logs`, {
        method: 'GET',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            displayLogsHistory(data.logs);
        } else {
            throw new Error(data.message || 'Ошибка при загрузке логов');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        content.innerHTML = `
            <div class="loading-spinner">
                <p style="color: #dc3545;">Ошибка при загрузке логов: ${error.message}</p>
            </div>
        `;
    });
}

function displayLogsHistory(logs) {
    const content = document.getElementById('logsHistoryContent');
    
    if (logs.length === 0) {
        content.innerHTML = `
            <div class="loading-spinner">
                <p style="color: #666; font-style: italic;">Логи отсутствуют</p>
            </div>
        `;
        return;
    }
    
    const logsList = document.createElement('div');
    logsList.className = 'logs-history-list';
    
    logs.forEach(log => {
        const logItem = document.createElement('div');
        logItem.className = 'logs-history-item';
        
        const logHeader = document.createElement('div');
        logHeader.className = 'logs-history-header';
        
        const logType = document.createElement('span');
        logType.className = 'logs-history-type';
        logType.textContent = log.type ? log.type.name : 'Неизвестный тип';
        if (log.type && log.type.color) {
            logType.style.backgroundColor = log.type.color;
        }
        
        const logDate = document.createElement('span');
        logDate.className = 'logs-history-date';
        logDate.textContent = new Date(log.created_at).toLocaleString('ru-RU');
        
        logHeader.appendChild(logType);
        logHeader.appendChild(logDate);
        
        const logContent = document.createElement('div');
        logContent.className = 'logs-history-content';
        const contentParagraph = document.createElement('p');
        contentParagraph.textContent = log.log;
        logContent.appendChild(contentParagraph);
        
        const logFooter = document.createElement('div');
        logFooter.className = 'logs-history-footer';
        const footerSpan = document.createElement('span');
        footerSpan.textContent = `Создал: ${log.user ? log.user.name : 'Система'}`;
        logFooter.appendChild(footerSpan);
        
        logItem.appendChild(logHeader);
        logItem.appendChild(logContent);
        logItem.appendChild(logFooter);
        
        logsList.appendChild(logItem);
    });
    
    content.innerHTML = '';
    content.appendChild(logsList);
}

// Глобальные переменные для работы с действиями
let currentActionId = null;
let currentActionText = null;

// Функции для работы с модальным окном действий
function showActionsModal() {
    const modal = document.getElementById('actionsModal');
    modal.style.display = 'block';
    
    // Загружаем действия
    loadActions();
}

function closeActionsModal() {
    const modal = document.getElementById('actionsModal');
    modal.style.display = 'none';
}

function loadActions() {
    const content = document.getElementById('actionsList');
    
    // Показываем спиннер загрузки
    content.innerHTML = `
        <div class="loading-spinner">
            <div class="spinner"></div>
            <p>Загрузка действий...</p>
        </div>
    `;
    
    // Отправляем запрос на сервер
    fetch(`/company/${currentCompanyId}/actions`, {
        method: 'GET',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            displayActions(data.actions);
        } else {
            throw new Error(data.message || 'Ошибка при загрузке действий');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        content.innerHTML = `
            <div class="loading-spinner">
                <p style="color: #dc3545;">Ошибка при загрузке действий: ${error.message}</p>
            </div>
        `;
    });
}

function displayActions(actions) {
    const content = document.getElementById('actionsList');
    
    if (actions.length === 0) {
        content.innerHTML = `
            <div class="loading-spinner">
                <p style="color: #666; font-style: italic;">Действия отсутствуют</p>
            </div>
        `;
        return;
    }
    
    const actionsList = document.createElement('div');
    actionsList.className = 'actions-list';
    
    actions.forEach(action => {
        const actionItem = document.createElement('div');
        actionItem.className = `action-item ${action.status ? 'completed' : ''}`;
        actionItem.setAttribute('data-action-id', action.id);
        
        const actionHeader = document.createElement('div');
        actionHeader.className = 'action-header';
        
        const actionText = document.createElement('p');
        actionText.className = 'action-text';
        actionText.textContent = action.action;
        
        const actionDate = document.createElement('span');
        actionDate.className = 'action-date';
        actionDate.textContent = new Date(action.expired_at).toLocaleDateString('ru-RU');
        
        const actionButton = document.createElement('button');
        actionButton.className = 'action-button';
        actionButton.textContent = action.status ? 'Выполнено' : 'Я сделал';
        actionButton.disabled = action.status;
        
        if (!action.status) {
            actionButton.onclick = () => showActionCommentBlock(action.id, action.action);
        }
        
        actionHeader.appendChild(actionText);
        actionHeader.appendChild(actionDate);
        actionHeader.appendChild(actionButton);
        
        const commentBlock = document.createElement('div');
        commentBlock.className = 'action-comment-block';
        commentBlock.id = `comment-block-${action.id}`;
        
        const commentTextarea = document.createElement('textarea');
        commentTextarea.className = 'action-comment-textarea';
        commentTextarea.placeholder = 'Введите комментарий о выполнении...';
        
        const commentButtons = document.createElement('div');
        commentButtons.className = 'action-comment-buttons';
        
        const cancelButton = document.createElement('button');
        cancelButton.className = 'btn btn-secondary';
        cancelButton.textContent = 'Отмена';
        cancelButton.onclick = () => hideActionCommentBlock(action.id);
        
        const saveButton = document.createElement('button');
        saveButton.className = 'btn btn-primary';
        saveButton.textContent = 'Сохранить';
        saveButton.onclick = () => saveActionComment(action.id, action.action);
        
        commentButtons.appendChild(cancelButton);
        commentButtons.appendChild(saveButton);
        
        commentBlock.appendChild(commentTextarea);
        commentBlock.appendChild(commentButtons);
        
        actionItem.appendChild(actionHeader);
        actionItem.appendChild(commentBlock);
        
        actionsList.appendChild(actionItem);
    });
    
    content.innerHTML = '';
    content.appendChild(actionsList);
}

function showActionCommentBlock(actionId, actionText) {
    // Скрываем все другие блоки комментариев
    const allCommentBlocks = document.querySelectorAll('.action-comment-block');
    allCommentBlocks.forEach(block => {
        block.classList.remove('show');
    });
    
    // Показываем нужный блок
    const commentBlock = document.getElementById(`comment-block-${actionId}`);
    if (commentBlock) {
        commentBlock.classList.add('show');
        const textarea = commentBlock.querySelector('.action-comment-textarea');
        textarea.focus();
    }
}

function hideActionCommentBlock(actionId) {
    const commentBlock = document.getElementById(`comment-block-${actionId}`);
    if (commentBlock) {
        commentBlock.classList.remove('show');
        const textarea = commentBlock.querySelector('.action-comment-textarea');
        textarea.value = '';
    }
}

function saveActionComment(actionId, actionText) {
    const commentBlock = document.getElementById(`comment-block-${actionId}`);
    const textarea = commentBlock.querySelector('.action-comment-textarea');
    const comment = textarea.value.trim();
    
    if (!comment) {
        alert('Пожалуйста, введите комментарий');
        return;
    }
    
    // Показываем индикатор загрузки
    const actionItem = document.querySelector(`[data-action-id="${actionId}"]`);
    const actionButton = actionItem.querySelector('.action-button');
    const originalText = actionButton.textContent;
    actionButton.textContent = 'Сохранение...';
    actionButton.disabled = true;
    
    // Отправляем запрос на сервер
    fetch(`/company/${currentCompanyId}/actions/${actionId}/complete`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            comment: comment
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Обновляем отображение действия
            actionItem.classList.add('completed');
            actionItem.querySelector('.action-text').style.textDecoration = 'line-through';
            actionItem.querySelector('.action-text').style.color = '#666';
            actionButton.textContent = 'Выполнено';
            actionButton.disabled = true;
            
            // Скрываем блок комментария
            hideActionCommentBlock(actionId);
            
            // Обновляем лог событий, если получен новый лог
            if (data.log) {
                updateEventsLog(data.log);
            }
            
            // Показываем уведомление об успехе
            showNotification('Действие успешно выполнено', 'success');
        } else {
            throw new Error(data.message || 'Ошибка при сохранении действия');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        // Возвращаем оригинальное состояние при ошибке
        actionButton.textContent = originalText;
        actionButton.disabled = false;
        showNotification('Ошибка при сохранении действия', 'error');
    });
}

// Функции для работы с модальным окном создания нового действия
function showNewActionModal() {
    const modal = document.getElementById('newActionModal');
    const form = document.getElementById('newActionForm');
    
    // Очищаем форму
    form.reset();
    
    // Устанавливаем минимальную дату (завтра)
    const tomorrow = new Date();
    tomorrow.setDate(tomorrow.getDate() + 1);
    const tomorrowStr = tomorrow.toISOString().split('T')[0];
    document.getElementById('actionExpiredAt').min = tomorrowStr;
    
    // Сбрасываем счетчик символов
    document.getElementById('charCount').textContent = '0';
    
    // Показываем модальное окно
    modal.style.display = 'block';
    
    // Фокусируемся на поле описания
    document.getElementById('actionDescription').focus();
}

function closeNewActionModal() {
    const modal = document.getElementById('newActionModal');
    modal.style.display = 'none';
}

function saveNewAction() {
    const form = document.getElementById('newActionForm');
    const expiredAt = document.getElementById('actionExpiredAt').value;
    const action = document.getElementById('actionDescription').value.trim();
    
    if (!expiredAt) {
        alert('Пожалуйста, выберите дату истечения срока');
        return;
    }
    
    if (!action) {
        alert('Пожалуйста, опишите задачу');
        return;
    }
    
    // Показываем индикатор загрузки
    const saveButton = document.querySelector('#newActionModal .btn-primary');
    const originalText = saveButton.textContent;
    saveButton.textContent = 'Создание...';
    saveButton.disabled = true;
    
    // Отправляем запрос на сервер
    fetch(`/company/${currentCompanyId}/actions`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            action: action,
            expired_at: expiredAt
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Закрываем модальное окно
            closeNewActionModal();
            
            // Обновляем отображение действий
            updateActionsDisplay(data.action);
            
            // Обновляем лог событий, если получен новый лог
            if (data.log) {
                updateEventsLog(data.log);
            }
            
            // Показываем уведомление об успехе
            showNotification('Действие успешно создано', 'success');
        } else {
            throw new Error(data.message || 'Ошибка при создании действия');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Ошибка при создании действия', 'error');
    })
    .finally(() => {
        // Возвращаем оригинальное состояние кнопки
        saveButton.textContent = originalText;
        saveButton.disabled = false;
    });
}

function updateActionsDisplay(newAction) {
    // Обновляем блок действий
    const actionInfo = document.querySelector('.action-info');
    if (actionInfo) {
        const actionDate = actionInfo.querySelector('.action-date');
        const actionDescription = actionInfo.querySelector('.action-description');
        const actionButtons = actionInfo.querySelector('.action-buttons');
        
        if (actionDate && actionDescription) {
            // Форматируем дату
            const expiredDate = new Date(newAction.expired_at);
            const formattedDate = expiredDate.toLocaleDateString('ru-RU');
            
            // Обновляем содержимое
            actionDate.innerHTML = `
                <span class="label">Дата:</span>
                <span class="value">${formattedDate}</span>
            `;
            
            actionDescription.innerHTML = `
                <span class="label">Что требуется сделать:</span>
                <p>${newAction.action}</p>
            `;
            
            // Убеждаемся, что кнопки на месте
            if (!actionButtons.querySelector('.btn-secondary')) {
                actionButtons.innerHTML = `
                    <button class="btn btn-primary" onclick="showNewActionModal()">Задать новое действие</button>
                    <button class="btn btn-secondary" onclick="showActionsModal()">Подробнее</button>
                `;
            }
        }
    }
}

// Обработчик для счетчика символов
document.addEventListener('DOMContentLoaded', function() {
    const actionDescription = document.getElementById('actionDescription');
    const charCount = document.getElementById('charCount');
    
    if (actionDescription && charCount) {
        actionDescription.addEventListener('input', function() {
            charCount.textContent = this.value.length;
        });
    }
});

// Обновляем обработчики закрытия модальных окон
document.addEventListener('click', function(event) {
    const statusModal = document.getElementById('statusCommentModal');
    const logsModal = document.getElementById('logsHistoryModal');
    const actionsModal = document.getElementById('actionsModal');
    const newActionModal = document.getElementById('newActionModal');
    
    if (event.target === statusModal) {
        cancelStatusChange();
    }
    
    if (event.target === logsModal) {
        closeLogsHistory();
    }
    
    if (event.target === actionsModal) {
        closeActionsModal();
    }
    
    if (event.target === newActionModal) {
        closeNewActionModal();
    }
});

// Обновляем обработчики закрытия по Escape
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        const statusModal = document.getElementById('statusCommentModal');
        const logsModal = document.getElementById('logsHistoryModal');
        const actionsModal = document.getElementById('actionsModal');
        const newActionModal = document.getElementById('newActionModal');
        
        if (statusModal && statusModal.style.display === 'block') {
            cancelStatusChange();
        }
        
        if (logsModal && logsModal.style.display === 'block') {
            closeLogsHistory();
        }
        
        if (actionsModal && actionsModal.style.display === 'block') {
            closeActionsModal();
        }
        
        if (newActionModal && newActionModal.style.display === 'block') {
            closeNewActionModal();
        }
    }
});

// Функция для редактирования блока основной информации
function editMainInfoBlock() {
    const mainInfoContent = document.getElementById('main_info_content');
    const mainInfoEdit = document.getElementById('main_info_edit');
    const mainInfoActions = document.getElementById('main_info_actions');

    mainInfoContent.style.display = 'none';
    mainInfoEdit.style.display = 'block';
    mainInfoActions.style.display = 'block';

    // Фокусируемся на поле описания
    document.getElementById('common_info_textarea').focus();
}

// Функция для отмены редактирования блока основной информации
function cancelMainInfoEdit() {
    const mainInfoContent = document.getElementById('main_info_content');
    const mainInfoEdit = document.getElementById('main_info_edit');
    const mainInfoActions = document.getElementById('main_info_actions');

    mainInfoEdit.style.display = 'none';
    mainInfoContent.style.display = 'block';
    mainInfoActions.style.display = 'none';

    // Восстанавливаем содержимое
    const textarea = document.getElementById('common_info_textarea');
    textarea.value = textarea.getAttribute('data-original');
}

// Функция для сохранения изменений в блоке основной информации
function saveMainInfoBlock() {
    const textarea = document.getElementById('common_info_textarea');
    const commonInfo = textarea.value.trim();

    if (commonInfo === textarea.getAttribute('data-original')) {
        showNotification('Нет изменений для сохранения', 'info');
        return;
    }

    // Показываем индикатор загрузки
    const saveButton = document.querySelector('#main_info_actions .btn-primary');
    const originalText = saveButton.textContent;
    saveButton.textContent = 'Сохранение...';
    saveButton.disabled = true;

    // Отправляем запрос на сервер
    fetch(`/company/${currentCompanyId}/common-info`, {
        method: 'PATCH',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            common_info: commonInfo
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Обновляем отображение
            const companyDescription = document.querySelector('.company-description');
            if (commonInfo) {
                if (companyDescription) {
                    companyDescription.querySelector('p').textContent = commonInfo;
                } else {
                    // Если элемента описания нет, но есть текст, создаем его
                    const mainInfoContent = document.getElementById('main_info_content');
                    const descriptionDiv = document.createElement('div');
                    descriptionDiv.className = 'company-description';
                    descriptionDiv.innerHTML = `
                        <div class="description-header">
                            <strong>Описание:</strong>
                            <button class="edit-comment-btn" onclick="editMainInfoBlock()">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"></path>
                                </svg>
                                Редактировать
                            </button>
                        </div>
                        <p>${commonInfo}</p>
                    `;
                    mainInfoContent.appendChild(descriptionDiv);
                }
            } else if (companyDescription) {
                // Если текста нет, удаляем элемент описания
                companyDescription.remove();
            }
            textarea.setAttribute('data-original', commonInfo); // Обновляем data-атрибут

            // Скрываем блок редактирования
            cancelMainInfoEdit();

            // Обновляем лог событий, если получен новый лог
            if (data.log) {
                updateEventsLog(data.log);
            }

            showNotification('Основная информация успешно сохранена', 'success');
        } else {
            throw new Error(data.message || 'Ошибка при сохранении основной информации');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Ошибка при сохранении основной информации', 'error');
    })
    .finally(() => {
        // Возвращаем оригинальное состояние кнопки
        saveButton.textContent = originalText;
        saveButton.disabled = false;
    });
}

// Функции для редактирования блока контактной информации
function editContactInfoBlock() {
    const contactInfoContent = document.getElementById('contact_info_content');
    const contactInfoEdit = document.getElementById('contact_info_edit');
    const contactInfoActions = document.getElementById('contact_info_actions');

    contactInfoContent.style.display = 'none';
    contactInfoEdit.style.display = 'block';
    contactInfoActions.style.display = 'block';

    // Фокусируемся на первом поле
    document.getElementById('company_email').focus();
}

function cancelContactInfoEdit() {
    const contactInfoContent = document.getElementById('contact_info_content');
    const contactInfoEdit = document.getElementById('contact_info_edit');
    const contactInfoActions = document.getElementById('contact_info_actions');

    contactInfoEdit.style.display = 'none';
    contactInfoContent.style.display = 'block';
    contactInfoActions.style.display = 'none';

    // Восстанавливаем оригинальные значения
    const emailInput = document.getElementById('company_email');
    const phoneInput = document.getElementById('company_phone');
    const siteInput = document.getElementById('company_site');

    emailInput.value = emailInput.getAttribute('data-original');
    phoneInput.value = phoneInput.getAttribute('data-original');
    siteInput.value = siteInput.getAttribute('data-original');
}

function saveContactInfoBlock() {
    const emailInput = document.getElementById('company_email');
    const phoneInput = document.getElementById('company_phone');
    const siteInput = document.getElementById('company_site');

    const contactData = {
        email: emailInput.value.trim(),
        phone: phoneInput.value.trim(),
        site: siteInput.value.trim()
    };

    // Проверяем, есть ли изменения
    const hasChanges = contactData.email !== emailInput.getAttribute('data-original') ||
                      contactData.phone !== phoneInput.getAttribute('data-original') ||
                      contactData.site !== siteInput.getAttribute('data-original');

    if (!hasChanges) {
        showNotification('Нет изменений для сохранения', 'info');
        return;
    }

    // Показываем индикатор загрузки
    const saveButton = document.querySelector('#contact_info_actions .btn-primary');
    const originalText = saveButton.textContent;
    saveButton.textContent = 'Сохранение...';
    saveButton.disabled = true;

    // Отправляем запрос на сервер
    fetch(`/company/${currentCompanyId}/contact-info`, {
        method: 'PATCH',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify(contactData)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Обновляем отображение
            updateContactInfoDisplay(contactData);

            // Обновляем data-атрибуты
            emailInput.setAttribute('data-original', contactData.email);
            phoneInput.setAttribute('data-original', contactData.phone);
            siteInput.setAttribute('data-original', contactData.site);

            // Скрываем блок редактирования
            cancelContactInfoEdit();

            // Обновляем лог событий, если получен новый лог
            if (data.log) {
                updateEventsLog(data.log);
            }

            showNotification('Контактная информация успешно сохранена', 'success');
        } else {
            throw new Error(data.message || 'Ошибка при сохранении контактной информации');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Ошибка при сохранении контактной информации', 'error');
    })
    .finally(() => {
        // Возвращаем оригинальное состояние кнопки
        saveButton.textContent = originalText;
        saveButton.disabled = false;
    });
}

function updateContactInfoDisplay(contactData) {
    const contactList = document.querySelector('#contact_info_content .contact-list');
    
    // Функция для поиска элемента по тексту в label
    function findContactItem(labelText) {
        const items = contactList.querySelectorAll('.contact-item');
        for (let item of items) {
            const label = item.querySelector('.label');
            if (label && label.textContent.includes(labelText)) {
                return item;
            }
        }
        return null;
    }
    
    // Обновляем email
    let emailItem = findContactItem('Email');
    if (contactData.email) {
        if (emailItem) {
            emailItem.querySelector('.value').textContent = contactData.email;
        } else {
            const newEmailItem = document.createElement('div');
            newEmailItem.className = 'contact-item';
            newEmailItem.innerHTML = `
                <span class="label">Email:</span>
                <span class="value">${contactData.email}</span>
            `;
            contactList.insertBefore(newEmailItem, contactList.firstChild);
        }
    } else if (emailItem) {
        emailItem.remove();
    }

    // Обновляем телефон
    let phoneItem = findContactItem('Телефон компании');
    if (contactData.phone) {
        if (phoneItem) {
            phoneItem.querySelector('.value').innerHTML = `<a href="tel:${contactData.phone}" class="link">${contactData.phone}</a>`;
        } else {
            const newPhoneItem = document.createElement('div');
            newPhoneItem.className = 'contact-item';
            newPhoneItem.innerHTML = `
                <span class="label">Телефон компании:</span>
                <span class="value">
                    <a href="tel:${contactData.phone}" class="link">${contactData.phone}</a>
                </span>
            `;
            contactList.appendChild(newPhoneItem);
        }
    } else if (phoneItem) {
        phoneItem.remove();
    }

    // Обновляем сайт
    let siteItem = findContactItem('Сайт');
    if (contactData.site) {
        if (siteItem) {
            siteItem.querySelector('.value').innerHTML = `<a href="${contactData.site}" target="_blank" class="link">${contactData.site}</a>`;
        } else {
            const newSiteItem = document.createElement('div');
            newSiteItem.className = 'contact-item';
            newSiteItem.innerHTML = `
                <span class="label">Сайт:</span>
                <span class="value">
                    <a href="${contactData.site}" target="_blank" class="link">${contactData.site}</a>
                </span>
            `;
            contactList.appendChild(newSiteItem);
        }
    } else if (siteItem) {
        siteItem.remove();
    }
}

// Функции для редактирования блока контактов
function editContactsBlock() {
    const contactsContent = document.getElementById('contacts_content');
    const contactsEdit = document.getElementById('contacts_edit');
    const contactsActions = document.getElementById('contacts_actions');

    contactsContent.style.display = 'none';
    contactsEdit.style.display = 'block';
    contactsActions.style.display = 'block';

    // Инициализируем обработчики событий для контактов
    initializeContactHandlers();
}

function cancelContactsEdit() {
    const contactsContent = document.getElementById('contacts_content');
    const contactsEdit = document.getElementById('contacts_edit');
    const contactsActions = document.getElementById('contacts_actions');

    contactsEdit.style.display = 'none';
    contactsContent.style.display = 'block';
    contactsActions.style.display = 'none';
}

function saveContactsBlock() {
    // Собираем данные о контактах
    const contactsData = collectContactsData();

    // Валидация данных
    if (contactsData.length === 0) {
        showNotification('Добавьте хотя бы один контакт', 'error');
        return;
    }

    // Проверяем, что у каждого контакта есть обязательные поля
    for (let i = 0; i < contactsData.length; i++) {
        const contact = contactsData[i];
        if (!contact.name.trim()) {
            showNotification(`Контакт ${i + 1}: укажите ФИО`, 'error');
            return;
        }
        if (!contact.position.trim()) {
            showNotification(`Контакт ${i + 1}: укажите должность`, 'error');
            return;
        }
        if (contact.phones.length === 0) {
            showNotification(`Контакт ${i + 1}: укажите хотя бы один телефон`, 'error');
            return;
        }
    }

    // Показываем индикатор загрузки
    const saveButton = document.querySelector('#contacts_actions .btn-primary');
    const originalText = saveButton.textContent;
    saveButton.textContent = 'Сохранение...';
    saveButton.disabled = true;

    // Отладочная информация
    console.log('Отправляемые данные:', { contacts: contactsData });
    console.log('URL:', `/company/${currentCompanyId}/contacts`);

    // Отправляем запрос на сервер
    fetch(`/company/${currentCompanyId}/contacts`, {
        method: 'PATCH',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({ contacts: contactsData })
    })
    .then(response => {
        console.log('Response status:', response.status);
        console.log('Response headers:', response.headers);
        
        if (!response.ok) {
            return response.text().then(text => {
                console.log('Error response text:', text);
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            });
        }
        
        return response.json();
    })
    .then(data => {
        if (data.success) {
            // Отладочная информация
            console.log('Полученные данные контактов:', data.contacts);
            
            // Обновляем отображение
            updateContactsDisplay(data.contacts);

            // Скрываем блок редактирования
            cancelContactsEdit();

            // Обновляем лог событий, если получен новый лог
            if (data.log) {
                updateEventsLog(data.log);
            }

            showNotification('Контакты успешно сохранены', 'success');
        } else {
            throw new Error(data.message || 'Ошибка при сохранении контактов');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        console.error('Error details:', {
            message: error.message,
            stack: error.stack
        });
        showNotification('Ошибка при сохранении контактов: ' + error.message, 'error');
    })
    .finally(() => {
        // Возвращаем оригинальное состояние кнопки
        saveButton.textContent = originalText;
        saveButton.disabled = false;
    });
}

function collectContactsData() {
    const contactsContainer = document.getElementById('contacts-container');
    const contactBlocks = contactsContainer.querySelectorAll('.contact-block');
    const contactsData = [];

    contactBlocks.forEach((contactBlock, index) => {
        const contactId = contactBlock.getAttribute('data-contact-id');
        const name = contactBlock.querySelector('input[name^="contact_name"]').value.trim();
        const position = contactBlock.querySelector('input[name^="position"]').value.trim();
        const isMainContact = contactBlock.querySelector('input[name^="main_contact"]').checked;

        // Собираем телефоны
        const phones = [];
        const phoneInputs = contactBlock.querySelectorAll('input[name^="phones"]');
        phoneInputs.forEach(phoneInput => {
            const phone = phoneInput.value.trim();
            if (phone) {
                phones.push(phone);
            }
        });

        // Собираем email
        const emails = [];
        const emailInputs = contactBlock.querySelectorAll('input[name^="contact_emails"]');
        emailInputs.forEach(emailInput => {
            const email = emailInput.value.trim();
            if (email) {
                emails.push(email);
            }
        });

        contactsData.push({
            id: contactId && contactId !== '' ? contactId : null,
            name: name,
            position: position,
            phones: phones,
            emails: emails,
            main_contact: isMainContact
        });
    });

    return contactsData;
}

function updateContactsDisplay(contacts) {
    const contactsList = document.querySelector('#contacts_content .contacts-list');
    
    console.log('Обновление отображения контактов:', contacts);
    
    if (contacts.length === 0) {
        contactsList.innerHTML = '<p style="color: #666; font-style: italic; text-align: center; padding: 20px;">Контакты не добавлены</p>';
        return;
    }

    let html = '';
    contacts.forEach(contact => {
        html += `
            <div class="contact-card">
                <div class="contact-header">
                    <span class="contact-name">${contact.name}</span>
                    ${contact.main_contact ? '<span class="main-contact-badge">Основной</span>' : ''}
                </div>
                ${contact.position ? `<div class="contact-position">${contact.position}</div>` : ''}
        `;

        if (contact.phones && contact.phones.length > 0) {
            html += '<div class="contact-phones">';
            contact.phones.forEach(phone => {
                // Проверяем, является ли phone объектом или строкой
                const phoneNumber = typeof phone === 'object' ? phone.phone : phone;
                html += `
                    <div class="phone-item">
                        <span class="phone-number">${phoneNumber}</span>
                        <div class="contact-actions">
                            <a href="tel:${phoneNumber}" class="action-btn" title="Позвонить">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path>
                                </svg>
                            </a>
                            <a href="#" class="action-btn" title="Просмотр">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                    <circle cx="12" cy="12" r="3"></circle>
                                </svg>
                            </a>
                        </div>
                    </div>
                `;
            });
            html += '</div>';
        }

        if (contact.emails && contact.emails.length > 0) {
            html += '<div class="contact-emails">';
            contact.emails.forEach((email, emailIndex) => {
                // Проверяем, является ли email объектом или строкой
                const emailAddress = typeof email === 'object' ? email.email : email;
                const isPrimary = typeof email === 'object' ? email.is_primary : (emailIndex === 0);
                html += `
                    <div class="email-item">
                        <span class="email-address">${emailAddress}</span>
                        ${isPrimary ? '<span class="primary-badge">Основной</span>' : ''}
                        <div class="contact-actions">
                            <a href="mailto:${emailAddress}" class="action-btn" title="Написать письмо">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
                                    <polyline points="22,6 12,13 2,6"></polyline>
                                </svg>
                            </a>
                            <a href="#" class="action-btn" title="Просмотр">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                    <circle cx="12" cy="12" r="3"></circle>
                                </svg>
                            </a>
                        </div>
                    </div>
                `;
            });
            html += '</div>';
        }

        html += '</div>';
    });

    contactsList.innerHTML = html;
}

function initializeContactHandlers() {
    // Обработчик добавления контакта
    const addContactBtn = document.getElementById('add-contact');
    if (addContactBtn) {
        addContactBtn.onclick = addNewContact;
    }

    // Обработчики удаления контактов
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('remove-contact')) {
            const contactBlock = e.target.closest('.contact-block');
            contactBlock.remove();
            updateContactIndexes();
        }
    });

    // Обработчики добавления телефонов
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('add-phone')) {
            const contactBlock = e.target.closest('.contact-block');
            const contactsContainer = document.getElementById('contacts-container');
            const contactIndex = Array.from(contactsContainer.children).indexOf(contactBlock);
            addPhoneToContact(contactBlock, contactIndex);
        }
    });

    // Обработчики добавления email
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('add-email')) {
            const contactBlock = e.target.closest('.contact-block');
            const contactsContainer = document.getElementById('contacts-container');
            const contactIndex = Array.from(contactsContainer.children).indexOf(contactBlock);
            addEmailToContact(contactBlock, contactIndex);
        }
    });

    // Обработчики удаления телефонов и email
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('remove-phone')) {
            const phoneBlock = e.target.closest('.phone-block');
            phoneBlock.remove();
        }
        
        if (e.target.classList.contains('remove-email')) {
            const emailBlock = e.target.closest('.email-block');
            emailBlock.remove();
        }
    });
}

function addNewContact() {
    const contactsContainer = document.getElementById('contacts-container');
    const contactIndex = contactsContainer.children.length;
    
    const contactBlock = document.createElement('div');
    contactBlock.className = 'contact-block';
    contactBlock.setAttribute('data-contact-id', '');
    contactBlock.innerHTML = `
        <div class="form-group">
            <label>ФИО контактного лица</label>
            <input type="text" name="contact_name[]" class="form-control" placeholder="ФИО контактного лица" required>
        </div>
        
        <div class="form-group">
            <label>Телефоны</label>
            <div class="phones-container">
                <div class="phone-block">
                    <div class="phone-input-group">
                        <input type="tel" name="phones[${contactIndex}][]" class="form-control" placeholder="Телефон" required>
                        <button type="button" class="btn btn-secondary add-phone">+</button>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="form-group">
            <label>Email адреса</label>
            <div class="emails-container">
                <div class="email-block">
                    <div class="email-input-group">
                        <input type="email" name="contact_emails[${contactIndex}][]" class="form-control" placeholder="Email">
                        <button type="button" class="btn btn-secondary add-email">+</button>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="form-group">
            <label>Должность</label>
            <input type="text" name="position[]" class="form-control" placeholder="Должность" required>
        </div>
        
        <div class="form-check">
            <input type="checkbox" name="main_contact[]" class="form-check-input" value="1">
            <label class="form-check-label">Основной контакт</label>
        </div>
        
        <button type="button" class="btn btn-danger remove-contact">Удалить контакт</button>
    `;
    
    contactsContainer.appendChild(contactBlock);
    updateContactIndexes();
}

function addPhoneToContact(contactBlock, contactIndex) {
    const phonesContainer = contactBlock.querySelector('.phones-container');
    const phoneBlock = document.createElement('div');
    phoneBlock.className = 'phone-block';
    phoneBlock.innerHTML = `
        <div class="phone-input-group">
            <input type="tel" name="phones[${contactIndex}][]" class="form-control" placeholder="Телефон" required>
            <button type="button" class="btn btn-danger remove-phone">×</button>
        </div>
    `;
    
    phonesContainer.insertBefore(phoneBlock, phonesContainer.lastElementChild);
    updateContactIndexes();
}

function addEmailToContact(contactBlock, contactIndex) {
    const emailsContainer = contactBlock.querySelector('.emails-container');
    const emailBlock = document.createElement('div');
    emailBlock.className = 'email-block';
    emailBlock.innerHTML = `
        <div class="email-input-group">
            <input type="email" name="contact_emails[${contactIndex}][]" class="form-control" placeholder="Email">
            <button type="button" class="btn btn-danger remove-email">×</button>
        </div>
    `;
    
    emailsContainer.insertBefore(emailBlock, emailsContainer.lastElementChild);
    updateContactIndexes();
}

function updateContactIndexes() {
    const contactsContainer = document.getElementById('contacts-container');
    const contactBlocks = contactsContainer.querySelectorAll('.contact-block');
    
    contactBlocks.forEach((contactBlock, index) => {
        // Обновляем имена полей
        const nameInput = contactBlock.querySelector('input[name^="contact_name"]');
        const positionInput = contactBlock.querySelector('input[name^="position"]');
        const mainContactCheckbox = contactBlock.querySelector('input[name^="main_contact"]');
        
        if (nameInput) nameInput.name = `contact_name[${index}]`;
        if (positionInput) positionInput.name = `position[${index}]`;
        if (mainContactCheckbox) mainContactCheckbox.name = `main_contact[${index}]`;
        
        // Обновляем имена полей телефонов
        const phoneInputs = contactBlock.querySelectorAll('input[name^="phones"]');
        phoneInputs.forEach(phoneInput => {
            phoneInput.name = `phones[${index}][]`;
        });

        // Обновляем имена полей email
        const emailInputs = contactBlock.querySelectorAll('input[name^="contact_emails"]');
        emailInputs.forEach(emailInput => {
            emailInput.name = `contact_emails[${index}][]`;
        });
    });
}

// Функции для редактирования блока адресов
function editAddressesBlock() {
    const addressesContent = document.getElementById('addresses_content');
    const addressesEdit = document.getElementById('addresses_edit');
    
    addressesContent.style.display = 'none';
    addressesEdit.style.display = 'block';
    
    // Добавляем обработчики событий для кнопок добавления и удаления адресов
    setupAddressesEventListeners();
}

function cancelAddressesEdit() {
    const addressesContent = document.getElementById('addresses_content');
    const addressesEdit = document.getElementById('addresses_edit');
    
    addressesContent.style.display = 'block';
    addressesEdit.style.display = 'none';
}

function setupAddressesEventListeners() {
    // Обработчик для добавления нового адреса
    const addAddressBtn = document.getElementById('add-address');
    if (addAddressBtn) {
        addAddressBtn.addEventListener('click', addAddress);
    }
    
    // Обработчики для удаления адресов
    const removeAddressBtns = document.querySelectorAll('.remove-address');
    removeAddressBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            removeAddress(this);
        });
    });
}

function addAddress() {
    const addressesContainer = document.getElementById('addresses-container');
    const addressBlocks = addressesContainer.querySelectorAll('.address-block');
    const newIndex = addressBlocks.length;
    
    const addressBlock = document.createElement('div');
    addressBlock.className = 'address-block';
    addressBlock.innerHTML = `
        <input type="text" name="addresses[${newIndex}][address]" class="form-control" placeholder="Введите адрес" required>
        <div class="form-check">
            <input type="checkbox" name="addresses[${newIndex}][main_address]" class="form-check-input" value="1">
            <label class="form-check-label">Основной адрес</label>
        </div>
        <button type="button" class="btn btn-danger remove-address">Удалить адрес</button>
    `;
    
    addressesContainer.appendChild(addressBlock);
    
    // Добавляем обработчик для новой кнопки удаления
    const removeBtn = addressBlock.querySelector('.remove-address');
    removeBtn.addEventListener('click', function() {
        removeAddress(this);
    });
    
    updateAddressIndexes();
}

function removeAddress(button) {
    const addressBlock = button.closest('.address-block');
    const addressesContainer = document.getElementById('addresses-container');
    const addressBlocks = addressesContainer.querySelectorAll('.address-block');
    
    // Не удаляем последний адрес, если он один
    if (addressBlocks.length > 1) {
        addressBlock.remove();
        updateAddressIndexes();
    } else {
        showNotification('Должен быть указан хотя бы один адрес', 'warning');
    }
}

function updateAddressIndexes() {
    const addressesContainer = document.getElementById('addresses-container');
    const addressBlocks = addressesContainer.querySelectorAll('.address-block');
    
    addressBlocks.forEach((addressBlock, index) => {
        const addressInput = addressBlock.querySelector('input[name^="addresses"][name$="[address]"]');
        const mainAddressCheckbox = addressBlock.querySelector('input[name^="addresses"][name$="[main_address]"]');
        const idInput = addressBlock.querySelector('input[name^="addresses"][name$="[id]"]');
        
        if (addressInput) addressInput.name = `addresses[${index}][address]`;
        if (mainAddressCheckbox) mainAddressCheckbox.name = `addresses[${index}][main_address]`;
        if (idInput) idInput.name = `addresses[${index}][id]`;
    });
}

function saveAddresses() {
    const form = document.getElementById('addressesForm');
    const formData = new FormData(form);
    
    // Собираем данные адресов
    const addresses = [];
    const addressBlocks = document.querySelectorAll('.address-block');
    
    addressBlocks.forEach((block, index) => {
        const addressInput = block.querySelector('input[name^="addresses"][name$="[address]"]');
        const mainAddressCheckbox = block.querySelector('input[name^="addresses"][name$="[main_address]"]');
        const idInput = block.querySelector('input[name^="addresses"][name$="[id]"]');
        
        if (addressInput && addressInput.value.trim()) {
            const addressData = {
                address: addressInput.value.trim()
            };
            
            if (idInput && idInput.value) {
                addressData.id = idInput.value;
            }
            
            if (mainAddressCheckbox && mainAddressCheckbox.checked) {
                addressData.main_address = true;
            }
            
            addresses.push(addressData);
        }
    });
    
    if (addresses.length === 0) {
        showNotification('Должен быть указан хотя бы один адрес', 'warning');
        return;
    }
    
    // Отправляем запрос на сервер
    fetch(`/company/{{ $company->id }}/addresses`, {
        method: 'PATCH',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({ addresses: addresses })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification(data.message, 'success');
            
            // Обновляем отображение адресов
            updateAddressesDisplay(data.addresses);
            
            // Обновляем текст кнопки
            const editButton = document.querySelector('.block-header .edit-comment-btn');
            if (editButton) {
                editButton.innerHTML = `
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"></path>
                    </svg>
                    ${data.addresses.length > 0 ? 'Редактировать' : 'Добавить адреса'}
                `;
            }
            
            // Возвращаемся к режиму просмотра
            cancelAddressesEdit();
            
            // Обновляем лог событий, если есть новый лог
            if (data.log) {
                updateEventsLog(data.log);
            }
        } else {
            showNotification(data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Произошла ошибка при сохранении адресов', 'error');
    });
}

function updateAddressesDisplay(addresses) {
    const addressesContent = document.querySelector('#addresses_content');
    
    if (addresses.length === 0) {
        addressesContent.innerHTML = '<p style="color: #666; font-style: italic;">Адреса не указаны</p>';
        return;
    }
    
    let addressesHtml = '<div class="addresses-list">';
    addresses.forEach(address => {
        addressesHtml += `
            <div class="address-item">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                    <circle cx="12" cy="10" r="3"></circle>
                </svg>
                <span>${address.address}</span>
                ${address.main_address ? '<span class="main-address-badge">Основной</span>' : ''}
            </div>
        `;
    });
    addressesHtml += '</div>';
    
    addressesContent.innerHTML = addressesHtml;
}

// Функции для редактирования блока юридической информации
function editLegalInfoBlock() {
    const legalInfoContent = document.getElementById('legal_info_content');
    const legalInfoEdit = document.getElementById('legal_info_edit');
    const legalInfoActions = document.getElementById('legal_info_actions');

    legalInfoContent.style.display = 'none';
    legalInfoEdit.style.display = 'block';
    legalInfoActions.style.display = 'block';

    // Сохраняем оригинальные значения
    const nameInput = document.getElementById('company_name');
    const innInput = document.getElementById('company_inn');
    nameInput.setAttribute('data-original', nameInput.value);
    innInput.setAttribute('data-original', innInput.value);

    // Фокусируемся на поле названия
    nameInput.focus();
}

// Функция для отмены редактирования блока юридической информации
function cancelLegalInfoEdit() {
    const legalInfoContent = document.getElementById('legal_info_content');
    const legalInfoEdit = document.getElementById('legal_info_edit');
    const legalInfoActions = document.getElementById('legal_info_actions');

    legalInfoEdit.style.display = 'none';
    legalInfoContent.style.display = 'block';
    legalInfoActions.style.display = 'none';

    // Восстанавливаем оригинальные значения
    const nameInput = document.getElementById('company_name');
    const innInput = document.getElementById('company_inn');
    nameInput.value = nameInput.getAttribute('data-original');
    innInput.value = innInput.getAttribute('data-original');
}

// Функция для сохранения изменений в блоке юридической информации
function saveLegalInfoBlock() {
    const nameInput = document.getElementById('company_name');
    const innInput = document.getElementById('company_inn');
    
    const name = nameInput.value.trim();
    const inn = innInput.value.trim();

    // Проверяем, есть ли изменения
    if (name === nameInput.getAttribute('data-original') && 
        inn === innInput.getAttribute('data-original')) {
        showNotification('Нет изменений для сохранения', 'info');
        return;
    }

    // Валидация
    if (!name) {
        showNotification('Название компании обязательно для заполнения', 'error');
        nameInput.focus();
        return;
    }

    // Показываем индикатор загрузки
    const saveButton = document.querySelector('#legal_info_actions .btn-primary');
    const originalText = saveButton.textContent;
    saveButton.textContent = 'Сохранение...';
    saveButton.disabled = true;

    // Отправляем запрос на сервер
    fetch(`/company/${currentCompanyId}/legal-info`, {
        method: 'PATCH',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            name: name,
            inn: inn
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Обновляем отображение
            updateLegalInfoDisplay(data.company);
            
            // Обновляем data-атрибуты
            nameInput.setAttribute('data-original', name);
            innInput.setAttribute('data-original', inn);
            
            // Обновляем заголовок страницы
            const companyTitle = document.querySelector('.company-title');
            if (companyTitle) {
                companyTitle.textContent = name;
            }
            
            // Обновляем текст кнопки
            const editButton = document.querySelector('.info-block .block-header .edit-comment-btn');
            if (editButton) {
                editButton.innerHTML = `
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"></path>
                    </svg>
                    ${inn ? 'Редактировать' : 'Добавить'}
                `;
            }

            // Скрываем блок редактирования
            cancelLegalInfoEdit();

            // Обновляем лог событий, если получен новый лог
            if (data.log) {
                updateEventsLog(data.log);
            }

            showNotification('Юридическая информация успешно сохранена', 'success');
        } else {
            throw new Error(data.message || 'Ошибка при сохранении юридической информации');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification(error.message || 'Произошла ошибка при сохранении юридической информации', 'error');
    })
    .finally(() => {
        // Восстанавливаем кнопку
        saveButton.textContent = originalText;
        saveButton.disabled = false;
    });
}

// Функция для обновления отображения юридической информации
function updateLegalInfoDisplay(company) {
    const legalInfoContent = document.getElementById('legal_info_content');
    
    if (company.inn) {
        legalInfoContent.innerHTML = `
            <div class="legal-list">
                <div class="legal-item">
                    <span class="label">ИНН:</span>
                    <span class="value">${company.inn}</span>
                </div>
                <div class="legal-item">
                    <span class="label">Название:</span>
                    <span class="value">${company.name}</span>
                </div>
            </div>
        `;
    } else {
        legalInfoContent.innerHTML = '<p style="color: #666; font-style: italic;">Юридическая информация не указана</p>';
    }
}
</script>
@endsection