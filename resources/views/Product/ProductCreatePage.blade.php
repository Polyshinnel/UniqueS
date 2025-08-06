@extends('layouts.layout')

@section('title', 'Создание товара')

@section('search-filter')
@endsection

@section('header-action-btn')
@endsection

@section('header-title')
    <h1 class="header-title">Создание товара</h1>
@endsection

@section('content')
<style>
/* Стили для multiselect */
.multiselect-wrapper {
    position: relative;
    width: 100%;
}

.multiselect-input {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 8px 12px;
    border: 1px solid #ddd;
    border-radius: 4px;
    background: white;
    cursor: pointer;
    min-height: 38px;
    position: relative;
}

.multiselect-input:hover {
    border-color: #133E71;
}

.multiselect-input:focus {
    outline: none;
    border-color: #133E71;
    box-shadow: 0 0 0 2px rgba(19, 62, 113, 0.2);
}

.multiselect-input.active {
    border-color: #133E71;
    box-shadow: 0 0 0 2px rgba(19, 62, 113, 0.2);
}

.multiselect-placeholder {
    color: #6c757d;
    flex: 1;
}

.multiselect-values {
    display: flex;
    flex-wrap: wrap;
    gap: 4px;
    flex: 1;
}

.multiselect-tag {
    background: #133E71;
    color: white;
    padding: 2px 8px;
    border-radius: 12px;
    font-size: 12px;
    display: flex;
    align-items: center;
    gap: 4px;
}

.multiselect-tag-remove {
    cursor: pointer;
    font-weight: bold;
    font-size: 14px;
}

.multiselect-tag-remove:hover {
    opacity: 0.8;
}

.multiselect-arrow {
    transition: transform 0.2s;
    color: #6c757d;
}

.multiselect-input.active .multiselect-arrow {
    transform: rotate(180deg);
}

.multiselect-dropdown {
    position: absolute;
    top: 100%;
    left: 0;
    right: 0;
    background: white;
    border: 1px solid #ddd;
    border-top: none;
    border-radius: 0 0 4px 4px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    z-index: 1000;
    display: none;
    max-height: 200px;
    overflow: hidden;
}

.multiselect-dropdown.active {
    display: block;
    animation: multiselectFadeIn 0.15s ease-out;
}

@keyframes multiselectFadeIn {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.multiselect-search {
    padding: 8px;
    border-bottom: 1px solid #eee;
}

.multiselect-search-input {
    width: 100%;
    padding: 6px 8px;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 14px;
}

.multiselect-search-input:focus {
    outline: none;
    border-color: #133E71;
}

.multiselect-options {
    max-height: 150px;
    overflow-y: auto;
}

.multiselect-option {
    padding: 8px 12px;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 8px;
    transition: background-color 0.2s;
}

.multiselect-option:hover {
    background-color: #f8f9fa;
}

.multiselect-option.selected {
    background-color: #e3f2fd;
    color: #133E71;
}

.multiselect-checkbox {
    margin: 0;
}

.multiselect-options::-webkit-scrollbar {
    width: 6px;
}

.multiselect-options::-webkit-scrollbar-track {
    background: #f1f1f1;
}

.multiselect-options::-webkit-scrollbar-thumb {
    background: #c1c1c1;
    border-radius: 3px;
}

.multiselect-options::-webkit-scrollbar-thumb:hover {
    background: #a8a8a8;
}
</style>

<div class="product-create-container">
    <!-- Индикатор шагов -->
    <div class="steps-indicator">
        <div class="step active" data-step="1">
            <span class="step-number">1</span>
            <span class="step-title">Основное</span>
        </div>
        <div class="step" data-step="2">
            <span class="step-number">2</span>
            <span class="step-title">Характеристики</span>
        </div>
        <div class="step" data-step="3">
            <span class="step-number">3</span>
            <span class="step-title">Проверка</span>
        </div>
        <div class="step" data-step="4">
            <span class="step-number">4</span>
            <span class="step-title">Погрузка</span>
        </div>
        <div class="step" data-step="5">
            <span class="step-number">5</span>
            <span class="step-title">Демонтаж</span>
        </div>
        <div class="step" data-step="6">
            <span class="step-number">6</span>
            <span class="step-title">Оплата</span>
        </div>
        <div class="step" data-step="7">
            <span class="step-number">7</span>
            <span class="step-title">Фото</span>
        </div>
    </div>

    <form id="productForm" method="POST" action="{{ route('products.store') }}" enctype="multipart/form-data">
        @csrf

        <!-- Шаг 1: Основная информация -->
        <div class="step-content active" id="step-1">
            <h2>Основная информация</h2>

            <div class="form-row">
                <div class="form-group">
                    <label for="warehouse_id">Склад</label>
                    <select name="warehouse_id" id="warehouse_id" class="form-control" required>
                        <option value="">Выберите склад</option>
                        @foreach($warehouses as $warehouse)
                            <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label for="company_id">Поставщик</label>
                    <select name="company_id" id="company_id" class="form-control" required>
                        <option value="">Выберите поставщика</option>
                        @foreach($companies as $company)
                            <option value="{{ $company->id }}" data-sku="{{ $company->sku }}">{{ $company->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="category_id">Категория</label>
                    <select name="category_id" id="category_id" class="form-control" required>
                        <option value="">Выберите категорию</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label for="name">Название станка</label>
                    <input type="text" name="name" id="name" class="form-control" required>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="product_address">Адрес станка</label>
                    <input type="text" name="product_address" id="product_address" class="form-control" placeholder="Например: Цех 1, участок А, позиция 5">
                </div>

                <div class="form-group">
                    <label for="sku">Артикул</label>
                    <input type="text" name="sku" id="sku" class="form-control" placeholder="Оставьте пустым для автоматической генерации (артикул_поставщика-дата_время)">
                </div>
            </div>

            <div class="step-actions">
                <button type="button" class="btn btn-primary next-step">Следующий шаг</button>
            </div>
        </div>

        <!-- Шаг 2: Характеристики -->
        <div class="step-content" id="step-2">
            <h2>Характеристики</h2>

            <div class="form-group">
                <label for="main_chars">Основные характеристики</label>
                <textarea name="main_chars" id="main_chars" class="form-control" rows="4"></textarea>
            </div>

            <div class="form-group">
                <label for="mark">
                    Оценка
                    <span class="tooltip-trigger" data-tooltip="Пример: В отличном состоянии, с проверкой в работе. Направляющие без износа, электрика на пускателях.">
                        <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <circle cx="8" cy="8" r="7" stroke="#133E71" stroke-width="2"/>
                            <path d="M8 6v4M8 4h.01" stroke="#133E71" stroke-width="2"/>
                        </svg>
                    </span>
                </label>
                <textarea name="mark" id="mark" class="form-control" rows="4"></textarea>
            </div>

            <div class="form-group">
                <label for="complectation">Комплектация</label>
                <textarea name="complectation" id="complectation" class="form-control" rows="4"></textarea>
            </div>

            <div class="step-actions">
                <button type="button" class="btn btn-secondary prev-step">Предыдущий шаг</button>
                <button type="button" class="btn btn-primary next-step">Следующий шаг</button>
            </div>
        </div>

        <!-- Шаг 3: Проверка -->
        <div class="step-content" id="step-3">
            <h2>Проверка</h2>

            <div class="form-group">
                <label for="check_status_id">Статус проверки</label>
                <select name="check_status_id" id="check_status_id" class="form-control">
                    <option value="">Выберите статус проверки</option>
                    @foreach($checkStatuses as $status)
                        <option value="{{ $status->id }}">{{ $status->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label for="check_comment">Комментарий к проверке</label>
                <textarea name="check_comment" id="check_comment" class="form-control" rows="4"></textarea>
            </div>

            <div class="step-actions">
                <button type="button" class="btn btn-secondary prev-step">Предыдущий шаг</button>
                <button type="button" class="btn btn-primary next-step">Следующий шаг</button>
            </div>
        </div>

        <!-- Шаг 4: Погрузка -->
        <div class="step-content" id="step-4">
            <h2>Погрузка</h2>

            <div class="form-group">
                <label for="loading_status_id">Статус погрузки</label>
                <select name="loading_status_id" id="loading_status_id" class="form-control">
                    <option value="">Выберите статус погрузки</option>
                    @foreach($installStatuses as $status)
                        <option value="{{ $status->id }}">{{ $status->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label for="loading_comment">
                    Комментарий
                    <span class="tooltip-trigger" data-tooltip="Поставщиком за доп плату, а арендаторов есть погрузчик, цену согласовывем отдельно. Поставщик погрузит, есть кран балка. Клиентом, потребуется кран, проезд в цех возможен">
                        <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <circle cx="8" cy="8" r="7" stroke="#133E71" stroke-width="2"/>
                            <path d="M8 6v4M8 4h.01" stroke="#133E71" stroke-width="2"/>
                        </svg>
                    </span>
                </label>
                <textarea name="loading_comment" id="loading_comment" class="form-control" rows="4"></textarea>
            </div>

            <div class="step-actions">
                <button type="button" class="btn btn-secondary prev-step">Предыдущий шаг</button>
                <button type="button" class="btn btn-primary next-step">Следующий шаг</button>
            </div>
        </div>

        <!-- Шаг 5: Демонтаж -->
        <div class="step-content" id="step-5">
            <h2>Демонтаж</h2>

            <div class="form-group">
                <label for="removal_status_id">Статус демонтажа</label>
                <select name="removal_status_id" id="removal_status_id" class="form-control">
                    <option value="">Выберите статус демонтажа</option>
                    @foreach($installStatuses as $status)
                        <option value="{{ $status->id }}">{{ $status->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label for="removal_comment">
                    Комментарий
                    <span class="tooltip-trigger" data-tooltip="Пример: Стоит на бетонном основании, потребуется отбить основание. Сложный демонтаж, потребуется разбирать на несколько частей, так как доступ в цех через маленькие ворота.">
                        <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <circle cx="8" cy="8" r="7" stroke="#133E71" stroke-width="2"/>
                            <path d="M8 6v4M8 4h.01" stroke="#133E71" stroke-width="2"/>
                        </svg>
                    </span>
                </label>
                <textarea name="removal_comment" id="removal_comment" class="form-control" rows="4"></textarea>
            </div>

            <div class="step-actions">
                <button type="button" class="btn btn-secondary prev-step">Предыдущий шаг</button>
                <button type="button" class="btn btn-primary next-step">Следующий шаг</button>
            </div>
        </div>

        <!-- Шаг 6: Оплата -->
        <div class="step-content" id="step-6">
            <h2>Оплата</h2>

            <div class="form-group">
                <label for="payment_types">Варианты оплаты</label>
                <div class="multiselect-wrapper">
                    <div class="multiselect-input" id="payment_types_multiselect" tabindex="0">
                        <span class="multiselect-placeholder">Выберите варианты оплаты</span>
                        <svg class="multiselect-arrow" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="6,9 12,15 18,9"></polyline>
                        </svg>
                    </div>
                    <div class="multiselect-dropdown" id="payment_types_multiselect_dropdown">
                        <div class="multiselect-search">
                            <input type="text" id="payment_types_multiselect_search" placeholder="Поиск вариантов оплаты..." class="multiselect-search-input">
                        </div>
                        <div class="multiselect-options" id="payment_types_multiselect_options">
                            <!-- Опции будут заполнены JavaScript -->
                        </div>
                    </div>
                    <select name="payment_types[]" id="payment_types" class="form-control" multiple style="display: none;">
                        @foreach($priceTypes as $priceType)
                            <option value="{{ $priceType->id }}">{{ $priceType->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label for="purchase_price">Цена покупки (руб.)</label>
                <input type="number" name="purchase_price" id="purchase_price" class="form-control" step="0.01" min="0">
            </div>

            <div class="form-group">
                <label for="payment_comment">
                    Комментарий
                    <span class="tooltip-trigger" data-tooltip="Пример: Поставщик продает за НАЛ, устно подтвердил любую форму безнала, условия необходимо проговорить дополнительно.">
                        <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <circle cx="8" cy="8" r="7" stroke="#133E71" stroke-width="2"/>
                            <path d="M8 6v4M8 4h.01" stroke="#133E71" stroke-width="2"/>
                        </svg>
                    </span>
                </label>
                <textarea name="payment_comment" id="payment_comment" class="form-control" rows="4"></textarea>
            </div>

            <div class="step-actions">
                <button type="button" class="btn btn-secondary prev-step">Предыдущий шаг</button>
                <button type="button" class="btn btn-primary next-step">Следующий шаг</button>
            </div>
        </div>

        <!-- Шаг 7: Фото -->
        <div class="step-content" id="step-7">
            <h2>Фото и видео</h2>

            <div class="form-group">
                <label for="media_files">Загрузка фото и видео</label>
                <div class="file-upload-container">
                    <input type="file" name="media_files[]" id="media_files" class="file-input" multiple accept="image/*,video/*">
                    <div class="file-upload-area" id="fileUploadArea">
                        <div class="upload-icon">
                            <svg width="48" height="48" viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M24 16V32M16 24H32" stroke="#133E71" stroke-width="2" stroke-linecap="round"/>
                                <rect x="4" y="4" width="40" height="40" rx="8" stroke="#133E71" stroke-width="2" stroke-dasharray="8 8"/>
                            </svg>
                        </div>
                        <p class="upload-text">Нажмите или перетащите файлы сюда</p>
                        <p class="upload-hint">Поддерживаются изображения (JPG, PNG, GIF) и видео (MP4, MOV, AVI)</p>
                    </div>
                </div>
                <div class="file-preview" id="filePreview"></div>
            </div>

            <div class="step-actions">
                <button type="button" class="btn btn-secondary prev-step">Предыдущий шаг</button>
                <button type="submit" class="btn btn-success">Создать товар</button>
            </div>
        </div>
    </form>
</div>

<!-- Tooltip -->
<div class="tooltip" id="tooltip"></div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const steps = document.querySelectorAll('.step');
    const stepContents = document.querySelectorAll('.step-content');
    const nextButtons = document.querySelectorAll('.next-step');
    const prevButtons = document.querySelectorAll('.prev-step');
    let currentStep = 1;

    function showStep(stepNumber) {
        // Обновляем индикатор шагов
        steps.forEach(step => {
            step.classList.remove('active', 'completed');
            if (parseInt(step.dataset.step) < stepNumber) {
                step.classList.add('completed');
            } else if (parseInt(step.dataset.step) === stepNumber) {
                step.classList.add('active');
            }
        });

        // Показываем контент шага
        stepContents.forEach(content => {
            content.classList.remove('active');
        });
        document.getElementById(`step-${stepNumber}`).classList.add('active');

        currentStep = stepNumber;
    }

    function validateStep(stepNumber) {
        const stepElement = document.getElementById(`step-${stepNumber}`);
        const requiredFields = stepElement.querySelectorAll('[required]');

        for (let field of requiredFields) {
            if (!field.value.trim()) {
                field.focus();
                return false;
            }
        }
        return true;
    }

    nextButtons.forEach(button => {
        button.addEventListener('click', function() {
            if (validateStep(currentStep)) {
                if (currentStep < 7) {
                    showStep(currentStep + 1);
                }
            }
        });
    });

    prevButtons.forEach(button => {
        button.addEventListener('click', function() {
            if (currentStep > 1) {
                showStep(currentStep - 1);
            }
        });
    });

    // Обработка подсказок
    const tooltipTriggers = document.querySelectorAll('.tooltip-trigger');
    const tooltip = document.getElementById('tooltip');

    tooltipTriggers.forEach(trigger => {
        trigger.addEventListener('mouseenter', function(e) {
            const text = this.getAttribute('data-tooltip');
            tooltip.textContent = text;
            tooltip.style.display = 'block';

            const rect = this.getBoundingClientRect();
            tooltip.style.left = rect.left + 'px';
            tooltip.style.top = (rect.top - tooltip.offsetHeight - 5) + 'px';
        });

        trigger.addEventListener('mouseleave', function() {
            tooltip.style.display = 'none';
        });
    });

    // Обработка загрузки файлов
    const fileInput = document.getElementById('media_files');
    const fileUploadArea = document.getElementById('fileUploadArea');
    const filePreview = document.getElementById('filePreview');
    let selectedFiles = [];

    // Обработка клика по области загрузки
    fileUploadArea.addEventListener('click', function() {
        fileInput.click();
    });

    // Обработка выбора файлов
    fileInput.addEventListener('change', function(e) {
        handleFiles(e.target.files);
    });

    // Drag & Drop обработка
    fileUploadArea.addEventListener('dragover', function(e) {
        e.preventDefault();
        fileUploadArea.classList.add('dragover');
    });

    fileUploadArea.addEventListener('dragleave', function(e) {
        e.preventDefault();
        fileUploadArea.classList.remove('dragover');
    });

    fileUploadArea.addEventListener('drop', function(e) {
        e.preventDefault();
        fileUploadArea.classList.remove('dragover');
        handleFiles(e.dataTransfer.files);
    });

    function handleFiles(files) {
        Array.from(files).forEach(file => {
            if (validateFile(file)) {
                selectedFiles.push(file);
                addFilePreview(file);
            }
        });
        updateFileInput();
    }

    function validateFile(file) {
        const maxSize = 50 * 1024 * 1024; // 50MB
        const allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'video/mp4', 'video/quicktime', 'video/x-msvideo'];
        
        if (file.size > maxSize) {
            alert('Файл слишком большой. Максимальный размер: 50MB');
            return false;
        }
        
        if (!allowedTypes.includes(file.type)) {
            alert('Неподдерживаемый формат файла');
            return false;
        }
        
        return true;
    }

    function addFilePreview(file) {
        const previewItem = document.createElement('div');
        previewItem.className = 'file-preview-item';
        
        // Создаем контейнер для информации о файле
        const fileInfo = document.createElement('div');
        fileInfo.className = 'file-info';
        
        // Добавляем бейдж типа файла
        const typeBadge = document.createElement('span');
        typeBadge.className = 'file-type-badge';
        if (file.type.startsWith('image/')) {
            typeBadge.className += ' image';
            typeBadge.textContent = 'Изображение';
        } else if (file.type.startsWith('video/')) {
            typeBadge.className += ' video';
            typeBadge.textContent = 'Видео';
        } else {
            typeBadge.textContent = 'Файл';
        }
        
        const fileName = document.createElement('span');
        fileName.className = 'file-name';
        fileName.textContent = file.name;
        
        // Добавляем размер файла
        const fileSize = document.createElement('span');
        fileSize.className = 'file-size';
        fileSize.textContent = formatFileSize(file.size);
        
        const removeBtn = document.createElement('button');
        removeBtn.type = 'button';
        removeBtn.className = 'remove-file';
        removeBtn.innerHTML = '×';
        removeBtn.addEventListener('click', function() {
            removeFile(file, previewItem);
        });
        
        // Предварительный просмотр для изображений
        if (file.type.startsWith('image/')) {
            const img = document.createElement('img');
            img.className = 'file-preview-image';
            const reader = new FileReader();
            reader.onload = function(e) {
                img.src = e.target.result;
            };
            reader.readAsDataURL(file);
            previewItem.appendChild(img);
        }
        
        fileInfo.appendChild(typeBadge);
        fileInfo.appendChild(fileName);
        fileInfo.appendChild(fileSize);
        
        previewItem.appendChild(fileInfo);
        previewItem.appendChild(removeBtn);
        
        filePreview.appendChild(previewItem);
    }

    function removeFile(file, previewItem) {
        const index = selectedFiles.indexOf(file);
        if (index > -1) {
            selectedFiles.splice(index, 1);
        }
        previewItem.remove();
        updateFileInput();
    }

    function updateFileInput() {
        const dt = new DataTransfer();
        selectedFiles.forEach(file => {
            dt.items.add(file);
        });
        fileInput.files = dt.files;
    }

    function formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }

    // Инициализация
    showStep(1);
    
    // Инициализация multiselect для вариантов оплаты
    initializeMultiSelect('payment_types_multiselect', 'payment_types', @json($priceTypes));
    
    // Автоматическая подстановка артикула поставщика при выборе поставщика
    const companySelect = document.getElementById('company_id');
    const skuInput = document.getElementById('sku');

    if (companySelect && skuInput) {
        companySelect.addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            if (selectedOption.value && !skuInput.value.trim()) {
                // Получаем артикул поставщика из выбранной опции
                const supplierSku = selectedOption.getAttribute('data-sku') || '000';
                const date = new Date();
                const dateStr = date.getDate().toString().padStart(2, '0') + 
                               (date.getMonth() + 1).toString().padStart(2, '0') + 
                               date.getFullYear().toString() + 
                               date.getHours().toString().padStart(2, '0') + 
                               date.getMinutes().toString().padStart(2, '0');
                
                const generatedSku = supplierSku + dateStr;
                skuInput.value = generatedSku;
            }
        });

        // Обрабатываем случай, когда пользователь очищает поле артикула
        skuInput.addEventListener('blur', function() {
            if (!this.value.trim() && companySelect.value) {
                const selectedOption = companySelect.options[companySelect.selectedIndex];
                const supplierSku = selectedOption.getAttribute('data-sku') || '000';
                const date = new Date();
                const dateStr = date.getDate().toString().padStart(2, '0') + 
                               (date.getMonth() + 1).toString().padStart(2, '0') + 
                               date.getFullYear().toString() + 
                               date.getHours().toString().padStart(2, '0') + 
                               date.getMinutes().toString().padStart(2, '0');
                
                const generatedSku = supplierSku + dateStr;
                skuInput.value = generatedSku;
            }
        });
    }
});

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
        } else {
            placeholder.style.display = 'none';
            values.style.display = 'block';
            
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
                    updateOptions();
                    updateHiddenSelect();
                });
            });
        }
    }
    
    // Обновляем скрытый select
    function updateHiddenSelect() {
        const options = hiddenSelect.querySelectorAll('option');
        options.forEach(option => {
            if (selectedValues.includes(option.value)) {
                option.selected = true;
            } else {
                option.selected = false;
            }
        });
    }
    
    // Переключение выпадающего списка
    function toggleDropdown() {
        if (isOpen) {
            multiselectInput.classList.remove('active');
            multiselectDropdown.classList.remove('active');
            isOpen = false;
        } else {
            multiselectInput.classList.add('active');
            multiselectDropdown.classList.add('active');
            isOpen = true;
            multiselectSearch.focus();
        }
    }
    
    // Закрытие при клике вне элемента
    document.addEventListener('click', function(e) {
        if (!multiselectInput.contains(e.target) && !multiselectDropdown.contains(e.target)) {
            multiselectInput.classList.remove('active');
            multiselectDropdown.classList.remove('active');
            isOpen = false;
        }
    });
    
    // Обработка поиска
    multiselectSearch.addEventListener('input', function() {
        const searchTerm = this.value.toLowerCase();
        const filteredOptions = options.filter(option => 
            option.name.toLowerCase().includes(searchTerm)
        );
        updateOptions(filteredOptions);
    });
    
    // Привязываем события
    multiselectInput.addEventListener('click', toggleDropdown);
    multiselectInput.addEventListener('keydown', function(e) {
        if (e.key === 'Enter' || e.key === ' ') {
            e.preventDefault();
            toggleDropdown();
        }
    });
    
    // Инициализация
    updateOptions();
    updateSelectedValues();
}
</script>
@endsection
