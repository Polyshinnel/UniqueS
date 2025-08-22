@extends('layouts.layout')

@section('title', 'Редактирование товара')

@section('search-filter')
@endsection

@section('header-action-btn')
@endsection

@section('header-title')
    <h1 class="header-title">Редактирование товара: {{ $product->name }}</h1>
@endsection

@section('content')
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
        <div class="step" data-step="8">
            <span class="step-number">8</span>
            <span class="step-title">Комментарий</span>
        </div>
    </div>

    <form id="productForm" method="POST" action="{{ route('products.update', $product) }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <!-- Шаг 1: Основная информация -->
        <div class="step-content active" id="step-1">
            <h2>Основная информация</h2>

            <div class="form-row">
                <div class="form-group">
                    <label for="company_id">Поставщик</label>
                    <select name="company_id" id="company_id" class="form-control" required>
                        <option value="">Выберите поставщика</option>
                        @foreach($companies as $company)
                            <option value="{{ $company->id }}" {{ $product->company_id == $company->id ? 'selected' : '' }}>
                                {{ $company->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label for="warehouse_display">Склад</label>
                    <input type="text" id="warehouse_display" class="form-control" readonly value="{{ $product->warehouse ? $product->warehouse->name : '' }}" placeholder="Будет определен автоматически по поставщику">
                    <input type="hidden" name="warehouse_id" id="warehouse_id" value="{{ $product->warehouse_id }}">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="category_id">Категория</label>
                    <select name="category_id" id="category_id" class="form-control" required>
                        <option value="">Выберите категорию</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ $product->category_id == $category->id ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label for="name">Название станка</label>
                    <input type="text" name="name" id="name" class="form-control" value="{{ $product->name }}" required>
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
                <textarea name="main_chars" id="main_chars" class="form-control" rows="4">{{ $product->main_chars }}</textarea>
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
                <textarea name="mark" id="mark" class="form-control" rows="4">{{ $product->mark }}</textarea>
            </div>

            <div class="form-group">
                <label for="complectation">Комплектация</label>
                <textarea name="complectation" id="complectation" class="form-control" rows="4">{{ $product->complectation }}</textarea>
            </div>

            <div class="step-actions">
                <button type="button" class="btn btn-secondary prev-step">Предыдущий шаг</button>
                <button type="button" class="btn btn-primary next-step">Следующий шаг</button>
            </div>
        </div>

        <!-- Шаг 3: Статус -->
        <div class="step-content" id="step-3">
            <h2>Проверка</h2>

            <div class="form-group">
                <label for="status_id">Статус</label>
                <select name="status_id" id="status_id" class="form-control" required>
                    <option value="">Выберите статус</option>
                    @foreach($statuses as $status)
                        <option value="{{ $status->id }}" {{ $product->status_id == $status->id ? 'selected' : '' }}>
                            {{ $status->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label for="status_comment">Комментарий</label>
                <textarea name="status_comment" id="status_comment" class="form-control" rows="4">{{ $product->status_comment }}</textarea>
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
                <label for="loading_type">Тип погрузки</label>
                <select name="loading_type" id="loading_type" class="form-control" required>
                    <option value="">Выберите тип погрузки</option>
                    <option value="supplier" {{ $product->loading_type == 'supplier' ? 'selected' : '' }}>Поставщиком</option>
                    <option value="supplier_paid" {{ $product->loading_type == 'supplier_paid' ? 'selected' : '' }}>Поставщиком (за доп. плату)</option>
                    <option value="client" {{ $product->loading_type == 'client' ? 'selected' : '' }}>Клиентом</option>
                    <option value="other" {{ $product->loading_type == 'other' ? 'selected' : '' }}>Другое</option>
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
                <textarea name="loading_comment" id="loading_comment" class="form-control" rows="4">{{ $product->loading_comment }}</textarea>
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
                <label for="removal_type">Тип демонтажа</label>
                <select name="removal_type" id="removal_type" class="form-control" required>
                    <option value="">Выберите тип демонтажа</option>
                    <option value="supplier" {{ $product->removal_type == 'supplier' ? 'selected' : '' }}>Поставщиком</option>
                    <option value="supplier_paid" {{ $product->removal_type == 'supplier_paid' ? 'selected' : '' }}>Поставщиком (за доп. плату)</option>
                    <option value="client" {{ $product->removal_type == 'client' ? 'selected' : '' }}>Клиентом</option>
                    <option value="other" {{ $product->removal_type == 'other' ? 'selected' : '' }}>Другое</option>
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
                <textarea name="removal_comment" id="removal_comment" class="form-control" rows="4">{{ $product->removal_comment }}</textarea>
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
                <label for="payment_method">Приоритетный способ оплаты</label>
                <select name="payment_method" id="payment_method" class="form-control" required>
                    <option value="">Выберите способ оплаты</option>
                    <option value="cash" {{ $product->payment_method == 'cash' ? 'selected' : '' }}>Наличные</option>
                    <option value="cashless_with_vat" {{ $product->payment_method == 'cashless_with_vat' ? 'selected' : '' }}>Безнал с НДС</option>
                    <option value="cashless_without_vat" {{ $product->payment_method == 'cashless_without_vat' ? 'selected' : '' }}>Безнал без НДС</option>
                    <option value="combined" {{ $product->payment_method == 'combined' ? 'selected' : '' }}>Комбинированная</option>
                    <option value="other" {{ $product->payment_method == 'other' ? 'selected' : '' }}>Другое</option>
                </select>
            </div>

            <div class="form-group">
                <label for="purchase_price">Цена покупки (руб.)</label>
                <input type="number" name="purchase_price" id="purchase_price" class="form-control" step="0.01" min="0" value="{{ $product->purchase_price }}">
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
                <textarea name="payment_comment" id="payment_comment" class="form-control" rows="4">{{ $product->payment_comment }}</textarea>
            </div>

            <div class="step-actions">
                <button type="button" class="btn btn-secondary prev-step">Предыдущий шаг</button>
                <button type="button" class="btn btn-primary next-step">Следующий шаг</button>
            </div>
        </div>

        <!-- Шаг 7: Фото -->
        <div class="step-content" id="step-7">
            <h2>Фото и видео</h2>

            <!-- Существующие медиафайлы -->
            @if($product->mediaOrdered->count() > 0)
            <div class="existing-media">
                <h3>Существующие файлы</h3>
                <div class="existing-media-grid">
                    @foreach($product->mediaOrdered as $media)
                        <div class="existing-media-item" data-media-id="{{ $media->id }}">
                            @if($media->file_type === 'image')
                                <img src="{{ asset('storage/' . $media->file_path) }}" alt="{{ $media->file_name }}">
                            @else
                                <div class="video-placeholder">
                                    <svg width="48" height="48" viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <rect width="48" height="48" rx="8" fill="#f0f0f0"/>
                                        <path d="M20 16v16l12-8-12-8z" fill="#666"/>
                                    </svg>
                                    <span>{{ $media->file_name }}</span>
                                </div>
                            @endif
                            <div class="media-controls">
                                <label class="delete-media-checkbox">
                                    <input type="checkbox" name="delete_media[]" value="{{ $media->id }}">
                                    <span>Удалить</span>
                                </label>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
            @endif

            <div class="form-group">
                <label for="media_files">Добавить новые фото и видео</label>
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
                <button type="button" class="btn btn-primary next-step">Следующий шаг</button>
            </div>
        </div>

        <!-- Шаг 8: Общий комментарий -->
        <div class="step-content" id="step-8">
            <h2>Общий комментарий</h2>

            <div class="form-group">
                <label for="common_commentary_after">Общий комментарий после осмотра</label>
                <textarea name="common_commentary_after" id="common_commentary_after" class="form-control" rows="6" placeholder="Введите общий комментарий после осмотра товара...">{{ $product->common_commentary_after }}</textarea>
                <small class="form-text text-muted">Дополнительная информация о товаре после осмотра</small>
            </div>

            <div class="step-actions">
                <button type="button" class="btn btn-secondary prev-step">Предыдущий шаг</button>
                <button type="submit" class="btn btn-success">Сохранить изменения</button>
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
                if (currentStep < 8) {
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

    // Обработка загрузки новых файлов
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
            typeBadge.textContent = 'Новое изображение';
        } else if (file.type.startsWith('video/')) {
            typeBadge.className += ' video';
            typeBadge.textContent = 'Новое видео';
        } else {
            typeBadge.textContent = 'Новый файл';
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
    
    // Обработка смены компании
    const companySelect = document.getElementById('company_id');
    const warehouseDisplay = document.getElementById('warehouse_display');
    const warehouseHidden = document.getElementById('warehouse_id');

    if (companySelect && warehouseDisplay && warehouseHidden) {
        companySelect.addEventListener('change', function() {
            const selectedCompanyId = this.value;
            
            if (selectedCompanyId) {
                // Загружаем информацию о компании
                fetch(`/company/${selectedCompanyId}/info`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success && data.company && data.company.warehouse) {
                            warehouseDisplay.value = data.company.warehouse.name;
                            warehouseHidden.value = data.company.warehouse.id;
                        } else {
                            warehouseDisplay.value = '';
                            warehouseHidden.value = '';
                        }
                    })
                    .catch(error => {
                        console.error('Ошибка при загрузке информации о компании:', error);
                        warehouseDisplay.value = '';
                        warehouseHidden.value = '';
                    });
            } else {
                warehouseDisplay.value = '';
                warehouseHidden.value = '';
            }
        });
    }
});
</script>

<style>
.existing-media {
    margin-bottom: 30px;
    padding: 20px;
    border: 1px solid #e0e0e0;
    border-radius: 8px;
    background-color: #f9f9f9;
}

.existing-media h3 {
    margin: 0 0 20px 0;
    color: #133E71;
    font-size: 16px;
    font-weight: 600;
}

.existing-media-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
    gap: 15px;
}

.existing-media-item {
    position: relative;
    border: 1px solid #ddd;
    border-radius: 8px;
    overflow: hidden;
    background: white;
}

.existing-media-item img {
    width: 100%;
    height: 120px;
    object-fit: cover;
}

.video-placeholder {
    width: 100%;
    height: 120px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    background: #f5f5f5;
    color: #666;
    font-size: 12px;
    text-align: center;
    padding: 10px;
}

.video-placeholder span {
    margin-top: 8px;
    word-break: break-all;
}

.media-controls {
    padding: 8px;
    background: white;
}

.delete-media-checkbox {
    display: flex;
    align-items: center;
    font-size: 12px;
    cursor: pointer;
}

.delete-media-checkbox input {
    margin-right: 5px;
}

.file-type-badge.image {
    background-color: #e3f2fd;
    color: #1976d2;
}

.file-type-badge.video {
    background-color: #f3e5f5;
    color: #7b1fa2;
}
</style>
@endsection 