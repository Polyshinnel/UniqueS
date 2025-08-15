@extends('layouts.layout')

@section('title', 'Редактирование объявления')

@section('search-filter')
@endsection

@section('header-action-btn')
@endsection

@section('header-title')
    <h1 class="header-title">Редактирование объявления</h1>
@endsection

@section('content')
<!-- Подключение Quill.js -->
<link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
<script src="https://cdn.quilljs.com/1.3.6/quill.min.js"></script>

<style>
/* Стили для Quill.js */
.ql-editor {
    min-height: 200px !important;
    max-height: 400px !important;
    font-size: 14px;
    line-height: 1.6;
}

.ql-container {
    border: 2px solid #e9ecef !important;
    border-top: none !important;
    border-radius: 0 0 8px 8px !important;
    background-color: #fff !important;
}

.ql-toolbar {
    border: 2px solid #e9ecef !important;
    border-bottom: 1px solid #e9ecef !important;
    border-radius: 8px 8px 0 0 !important;
    background-color: #f8f9fa !important;
}

.ql-toolbar button {
    color: #495057 !important;
}

.ql-toolbar button:hover {
    color: #133E71 !important;
}

.ql-toolbar button.ql-active {
    color: #133E71 !important;
}

.ql-toolbar .ql-stroke {
    stroke: #495057 !important;
}

.ql-toolbar .ql-fill {
    fill: #495057 !important;
}

.ql-toolbar button:hover .ql-stroke {
    stroke: #133E71 !important;
}

.ql-toolbar button:hover .ql-fill {
    fill: #133E71 !important;
}

.ql-toolbar button.ql-active .ql-stroke {
    stroke: #133E71 !important;
}

.ql-toolbar button.ql-active .ql-fill {
    fill: #133E71 !important;
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
            <span class="step-title">Медиафайлы</span>
        </div>
    </div>

    <form id="advertisementForm" method="POST" action="{{ route('advertisements.update', $advertisement) }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <!-- Шаг 1: Основная информация -->
        <div class="step-content active" id="step-1">
            <h2>Основная информация</h2>

            <div class="form-row">
                <div class="form-group">
                    <label for="product_id">Товар</label>
                    <select name="product_id" id="product_id" class="form-control" required disabled>
                        @foreach($products as $productItem)
                            <option value="{{ $productItem->id }}" {{ $advertisement->product_id == $productItem->id ? 'selected' : '' }}>
                                {{ $productItem->name }} ({{ $productItem->category->name }})
                            </option>
                        @endforeach
                    </select>
                    <small class="text-muted">Товар нельзя изменить при редактировании объявления</small>
                    <input type="hidden" name="product_id" value="{{ $advertisement->product_id }}">
                </div>

                <div class="form-group">
                    <label for="title">Название объявления</label>
                    <input type="text" name="title" id="title" class="form-control" required value="{{ $advertisement->title }}">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="category_id">Категория</label>
                    <select name="category_id" id="category_id" class="form-control" required>
                        <option value="">Выберите категорию</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ $advertisement->category_id == $category->id ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
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
                <label for="main_characteristics">Основные характеристики</label>
                <textarea name="main_characteristics" id="main_characteristics" class="form-control" rows="4">{{ $advertisement->main_characteristics }}</textarea>
            </div>

            <div class="form-group">
                <label for="complectation">Комплектация</label>
                <textarea name="complectation" id="complectation" class="form-control" rows="4">{{ $advertisement->complectation }}</textarea>
            </div>

            <div class="form-group">
                <label for="technical_characteristics">Технические характеристики</label>
                <div class="editor-container">
                    <textarea name="technical_characteristics" id="technical_characteristics" class="form-control" style="display: none;">{{ $advertisement->technical_characteristics }}</textarea>
                    <div id="technical_characteristics_editor"></div>
                </div>
                <small class="form-text text-muted">Используйте панель инструментов для форматирования текста</small>
            </div>

            <div class="form-group">
                <label for="additional_info">Дополнительная информация</label>
                <div class="editor-container">
                    <textarea name="additional_info" id="additional_info" class="form-control" style="display: none;">{{ $advertisement->additional_info }}</textarea>
                    <div id="additional_info_editor"></div>
                </div>
                <small class="form-text text-muted">Используйте панель инструментов для форматирования текста</small>
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
                    <option value="">Выберите статус</option>
                    @foreach($checkStatuses as $status)
                        <option value="{{ $status->id }}" 
                            {{ ($advertisement->check_data && $advertisement->check_data['status_id'] == $status->id) ? 'selected' : '' }}>
                            {{ $status->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label for="check_comment">Комментарий по проверке</label>
                <textarea name="check_comment" id="check_comment" class="form-control" rows="4">{{ $advertisement->check_data ? $advertisement->check_data['comment'] : '' }}</textarea>
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
                    <option value="">Выберите статус</option>
                    @foreach($installStatuses as $status)
                        <option value="{{ $status->id }}" 
                            {{ ($advertisement->loading_data && $advertisement->loading_data['status_id'] == $status->id) ? 'selected' : '' }}>
                            {{ $status->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label for="loading_comment">Комментарий по погрузке</label>
                <textarea name="loading_comment" id="loading_comment" class="form-control" rows="4">{{ $advertisement->loading_data ? $advertisement->loading_data['comment'] : '' }}</textarea>
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
                    <option value="">Выберите статус</option>
                    @foreach($installStatuses as $status)
                        <option value="{{ $status->id }}" 
                            {{ ($advertisement->removal_data && $advertisement->removal_data['status_id'] == $status->id) ? 'selected' : '' }}>
                            {{ $status->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label for="removal_comment">Комментарий по демонтажу</label>
                <textarea name="removal_comment" id="removal_comment" class="form-control" rows="4">{{ $advertisement->removal_data ? $advertisement->removal_data['comment'] : '' }}</textarea>
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
                <label>Варианты оплаты</label>
                <div class="payment-types-grid">
                    @foreach($priceTypes as $priceType)
                        <div class="payment-type-item">
                            <input type="checkbox" name="payment_types[]" id="payment_type_{{ $priceType->id }}" 
                                   value="{{ $priceType->id }}" class="payment-type-checkbox"
                                   {{ $advertisement->product->paymentVariants->where('price_type', $priceType->id)->count() > 0 ? 'checked' : '' }}>
                            <label for="payment_type_{{ $priceType->id }}" class="payment-type-label">
                                {{ $priceType->name }}
                            </label>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="form-group">
                <label for="purchase_price">Цена покупки</label>
                <input type="number" name="purchase_price" id="purchase_price" class="form-control" 
                       step="0.01" min="0" value="{{ $advertisement->product->purchase_price }}">
            </div>

            <div class="form-group">
                <label for="payment_comment">Комментарий по оплате</label>
                <textarea name="payment_comment" id="payment_comment" class="form-control" rows="4">{{ $advertisement->product->payment_comment }}</textarea>
            </div>

            <div class="step-actions">
                <button type="button" class="btn btn-secondary prev-step">Предыдущий шаг</button>
                <button type="button" class="btn btn-primary next-step">Следующий шаг</button>
            </div>
        </div>

        <!-- Шаг 7: Медиафайлы -->
        <div class="step-content" id="step-7">
            <h2>Медиафайлы</h2>

            <!-- Текущие медиафайлы -->
            <div class="form-group" id="current-media-section">
                <label>Текущие медиафайлы</label>
                <div class="current-media-grid" id="currentMediaGrid">
                    @if($advertisement->mediaOrdered->count() > 0)
                        @foreach($advertisement->mediaOrdered as $media)
                            <div class="current-media-item" data-media-id="{{ $media->id }}">
                                @if($media->file_type === 'image')
                                    <img src="{{ asset('storage/' . $media->file_path) }}" alt="{{ $media->file_name }}">
                                @else
                                    <video src="{{ asset('storage/' . $media->file_path) }}" muted></video>
                                @endif
                                <span class="media-type-badge">{{ $media->file_type === 'image' ? 'Фото' : 'Видео' }}</span>
                                <button type="button" class="delete-media-btn" data-media-id="{{ $media->id }}">×</button>
                                <div class="media-info">
                                    <div class="media-name">{{ $media->file_name }}</div>
                                    @if($media->is_selected_from_product)
                                        <div class="media-source">Из товара</div>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    @else
                        <div class="no-media-message">У объявления пока нет медиафайлов</div>
                    @endif
                </div>
            </div>

            <!-- Загрузка новых медиафайлов -->
            <div class="form-group">
                <label for="media_files">Загрузить дополнительные фото и видео</label>
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
                <button type="submit" class="btn btn-success">Сохранить изменения</button>
            </div>
        </div>
    </form>
</div>

<!-- Tooltip -->
<div class="tooltip" id="tooltip"></div>

<style>
.current-media-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
    gap: 15px;
    margin-top: 10px;
    margin-bottom: 20px;
}

.current-media-item {
    position: relative;
    border: 2px solid #ddd;
    border-radius: 8px;
    overflow: hidden;
    transition: all 0.3s ease;
}

.current-media-item:hover {
    transform: scale(1.05);
    border-color: #133E71;
}

.current-media-item img,
.current-media-item video {
    width: 100%;
    height: 120px;
    object-fit: cover;
}

.delete-media-btn {
    position: absolute;
    top: 5px;
    right: 5px;
    width: 25px;
    height: 25px;
    background: rgba(220, 53, 69, 0.9);
    color: white;
    border: none;
    border-radius: 50%;
    font-size: 16px;
    font-weight: bold;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.3s ease;
}

.delete-media-btn:hover {
    background: rgba(220, 53, 69, 1);
    transform: scale(1.1);
}

.media-source {
    font-size: 10px;
    color: #6c757d;
    font-style: italic;
}

.product-media-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
    gap: 15px;
    margin-top: 10px;
}

.product-media-item {
    position: relative;
    border: 2px solid #ddd;
    border-radius: 8px;
    overflow: hidden;
    cursor: pointer;
    transition: all 0.3s ease;
}

.product-media-item:hover {
    transform: scale(1.05);
    border-color: #133E71;
}

.product-media-item.selected {
    border-color: #133E71;
    box-shadow: 0 0 10px rgba(19, 62, 113, 0.3);
}

.product-media-item img,
.product-media-item video {
    width: 100%;
    height: 120px;
    object-fit: cover;
}

.media-type-badge {
    position: absolute;
    top: 5px;
    left: 5px;
    background: rgba(0, 0, 0, 0.7);
    color: white;
    padding: 2px 6px;
    border-radius: 4px;
    font-size: 12px;
}

.media-checkbox {
    position: absolute;
    top: 5px;
    right: 5px;
    width: 20px;
    height: 20px;
}

.no-media-message {
    text-align: center;
    color: #666;
    font-style: italic;
    padding: 20px;
    background: #f9f9f9;
    border-radius: 8px;
}

.media-info {
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    background: linear-gradient(to top, rgba(0,0,0,0.8), transparent);
    color: white;
    padding: 8px;
    font-size: 12px;
}

.media-name {
    font-weight: 500;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.media-size {
    opacity: 0.8;
    font-size: 11px;
}

.payment-types-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 15px;
    margin-top: 10px;
}

.payment-type-item {
    display: flex;
    align-items: center;
    padding: 15px;
    border: 2px solid #ddd;
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.3s ease;
}

.payment-type-item:hover {
    border-color: #133E71;
    background-color: #f8f9fa;
}

.payment-type-item.selected {
    border-color: #133E71;
    background-color: #e3f2fd;
}

.payment-type-checkbox {
    margin-right: 10px;
    width: 18px;
    height: 18px;
}

.payment-type-label {
    cursor: pointer;
    font-weight: 500;
    margin: 0;
}
</style>

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

    // Обработчики для чекбоксов типов оплаты
    document.querySelectorAll('.payment-type-checkbox').forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            const item = this.closest('.payment-type-item');
            if (this.checked) {
                item.classList.add('selected');
            } else {
                item.classList.remove('selected');
            }
        });
    });

    // Удаление медиафайлов
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('delete-media-btn')) {
            const mediaId = e.target.dataset.mediaId;
            const mediaItem = e.target.closest('.current-media-item');
            
            if (confirm('Вы уверены, что хотите удалить этот медиафайл?')) {
                fetch(`{{ route('advertisements.delete-media', $advertisement) }}`, {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({ media_id: mediaId })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        mediaItem.remove();
                        
                        // Проверяем, остались ли медиафайлы
                        const remainingMedia = document.querySelectorAll('.current-media-item');
                        if (remainingMedia.length === 0) {
                            document.getElementById('currentMediaGrid').innerHTML = 
                                '<div class="no-media-message">У объявления пока нет медиафайлов</div>';
                        }
                    } else {
                        alert('Ошибка при удалении медиафайла');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Ошибка при удалении медиафайла');
                });
            }
        }
    });

    // Обработка загрузки файлов (аналогично ProductCreatePage)
    const fileInput = document.getElementById('media_files');
    const fileUploadArea = document.getElementById('fileUploadArea');
    const filePreview = document.getElementById('filePreview');
    let selectedFiles = [];

    fileUploadArea.addEventListener('click', function() {
        fileInput.click();
    });

    fileInput.addEventListener('change', function(e) {
        handleFiles(e.target.files);
    });

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
        
        const fileInfo = document.createElement('div');
        fileInfo.className = 'file-info';
        
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
    
    // Инициализация CKEditor для полей с разметкой
    initializeEditors();
    
    // Обработка отправки формы - синхронизация данных редакторов
    document.getElementById('advertisementForm').addEventListener('submit', function(e) {
        // Синхронизируем данные из редакторов в скрытые textarea
        if (technicalEditor) {
            document.getElementById('technical_characteristics').value = technicalEditor.root.innerHTML;
        }
        if (additionalInfoEditor) {
            document.getElementById('additional_info').value = additionalInfoEditor.root.innerHTML;
        }
    });
});

// Глобальные переменные для редакторов
let technicalEditor, additionalInfoEditor;

// Функция инициализации Quill.js редакторов
function initializeEditors() {
    // Конфигурация редактора
    const toolbarOptions = [
        [{ 'header': [1, 2, 3, false] }],
        ['bold', 'italic', 'underline', 'strike'],
        [{ 'list': 'ordered'}, { 'list': 'bullet' }],
        [{ 'indent': '-1'}, { 'indent': '+1' }],
        ['link', 'blockquote'],
        ['clean']
    ];

    // Инициализация редактора технических характеристик
    const technicalEditorElement = document.getElementById('technical_characteristics_editor');
    if (technicalEditorElement) {
        technicalEditor = new Quill(technicalEditorElement, {
            theme: 'snow',
            modules: {
                toolbar: toolbarOptions
            },
            placeholder: 'Введите технические характеристики...'
        });
        
        // Устанавливаем начальное значение из скрытого textarea
        const initialValue = document.getElementById('technical_characteristics').value;
        if (initialValue) {
            technicalEditor.root.innerHTML = initialValue;
        }
        
        // Синхронизируем изменения обратно в скрытое поле
        technicalEditor.on('text-change', function() {
            document.getElementById('technical_characteristics').value = technicalEditor.root.innerHTML;
        });
    }

    // Инициализация редактора дополнительной информации
    const additionalInfoEditorElement = document.getElementById('additional_info_editor');
    if (additionalInfoEditorElement) {
        additionalInfoEditor = new Quill(additionalInfoEditorElement, {
            theme: 'snow',
            modules: {
                toolbar: toolbarOptions
            },
            placeholder: 'Введите дополнительную информацию...'
        });
        
        // Устанавливаем начальное значение из скрытого textarea
        const initialValue = document.getElementById('additional_info').value;
        if (initialValue) {
            additionalInfoEditor.root.innerHTML = initialValue;
        }
        
        // Синхронизируем изменения обратно в скрытое поле
        additionalInfoEditor.on('text-change', function() {
            document.getElementById('additional_info').value = additionalInfoEditor.root.innerHTML;
        });
    }
}
</script>
@endsection 