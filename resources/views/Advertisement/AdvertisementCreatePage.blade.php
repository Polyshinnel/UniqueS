@extends('layouts.layout')

@section('title', 'Создание объявления')

@section('search-filter')
@endsection

@section('header-action-btn')
@endsection

@section('header-title')
    <h1 class="header-title">Создание объявления</h1>
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
            <span class="step-title">Медиафайлы</span>
        </div>
    </div>

    <form id="advertisementForm" method="POST" action="{{ route('advertisements.store') }}" enctype="multipart/form-data">
        @csrf

        <!-- Шаг 1: Основная информация -->
        <div class="step-content active" id="step-1">
            <h2>Основная информация</h2>

            <div class="form-row">
                <div class="form-group">
                    <label for="product_id">Товар</label>
                    <select name="product_id" id="product_id" class="form-control" required>
                        <option value="">Выберите товар</option>
                        @foreach($products as $productItem)
                            <option value="{{ $productItem->id }}" {{ $product && $product->id == $productItem->id ? 'selected' : '' }}>
                                {{ $productItem->name }} ({{ $productItem->category->name }})
                            </option>
                        @endforeach
                    </select>
                    <button type="button" id="copyFromProduct" class="btn btn-secondary mt-2">Заполнить данными товара</button>
                </div>

                <div class="form-group">
                    <label for="title">Название объявления</label>
                    <input type="text" name="title" id="title" class="form-control" required value="{{ $product ? $product->name : '' }}">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="category_id">Категория</label>
                    <select name="category_id" id="category_id" class="form-control" required>
                        <option value="">Выберите категорию</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ $product && $product->category_id == $category->id ? 'selected' : '' }}>
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
                <textarea name="main_characteristics" id="main_characteristics" class="form-control" rows="4">{{ $product ? $product->main_chars : '' }}</textarea>
            </div>

            <div class="form-group">
                <label for="complectation">Комплектация</label>
                <textarea name="complectation" id="complectation" class="form-control" rows="4">{{ $product ? $product->complectation : '' }}</textarea>
            </div>

            <div class="form-group">
                <label for="technical_characteristics">Технические характеристики</label>
                <textarea name="technical_characteristics" id="technical_characteristics" class="form-control" rows="4"></textarea>
            </div>

            <div class="form-group">
                <label for="additional_info">Дополнительная информация</label>
                <textarea name="additional_info" id="additional_info" class="form-control" rows="4"></textarea>
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
                    @foreach($productStatuses as $status)
                        <option value="{{ $status->id }}" {{ $product && $product->status_id == $status->id ? 'selected' : '' }}>
                            {{ $status->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label for="check_comment">Комментарий по проверке</label>
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
                <label for="loading_type">Тип погрузки</label>
                <select name="loading_type" id="loading_type" class="form-control">
                    <option value="">Выберите тип погрузки</option>
                    <option value="supplier">Поставщиком</option>
                    <option value="supplier_paid">Поставщиком (за доп. плату)</option>
                    <option value="client">Клиентом</option>
                    <option value="other">Другое</option>
                </select>
            </div>

            <div class="form-group">
                <label for="loading_comment">Комментарий по погрузке</label>
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
                <label for="removal_type">Тип демонтажа</label>
                <select name="removal_type" id="removal_type" class="form-control">
                    <option value="">Выберите тип демонтажа</option>
                    <option value="supplier">Поставщиком</option>
                    <option value="supplier_paid">Поставщиком (за доп. плату)</option>
                    <option value="client">Клиентом</option>
                    <option value="other">Другое</option>
                </select>
            </div>

            <div class="form-group">
                <label for="removal_comment">Комментарий по демонтажу</label>
                <textarea name="removal_comment" id="removal_comment" class="form-control" rows="4"></textarea>
            </div>

            <div class="step-actions">
                <button type="button" class="btn btn-secondary prev-step">Предыдущий шаг</button>
                <button type="button" class="btn btn-primary next-step">Следующий шаг</button>
            </div>
        </div>

        <!-- Шаг 6: Медиафайлы -->
        <div class="step-content" id="step-6">
            <h2>Медиафайлы</h2>

            <!-- Выбор медиафайлов из товара -->
            <div class="form-group" id="product-media-section">
                <label>Медиафайлы товара</label>
                <div class="product-media-grid" id="productMediaGrid">
                    <!-- Будет заполнено через JavaScript -->
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
                <button type="submit" class="btn btn-success">Создать объявление</button>
            </div>
        </div>
    </form>
</div>

<!-- Tooltip -->
<div class="tooltip" id="tooltip"></div>

<style>
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
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const steps = document.querySelectorAll('.step');
    const stepContents = document.querySelectorAll('.step-content');
    const nextButtons = document.querySelectorAll('.next-step');
    const prevButtons = document.querySelectorAll('.prev-step');
    const productSelect = document.getElementById('product_id');
    const copyButton = document.getElementById('copyFromProduct');
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

        // Загружаем медиафайлы товара на 6 шаге
        if (stepNumber === 6) {
            loadProductMedia();
        }
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
                if (currentStep < 6) {
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

    // Копирование данных из товара
    copyButton.addEventListener('click', function() {
        const productId = productSelect.value;
        if (!productId) {
            alert('Сначала выберите товар');
            return;
        }

        fetch(`{{ route('advertisements.copy-from-product') }}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ product_id: productId })
        })
        .then(response => response.json())
        .then(data => {
            // Заполняем поля данными из товара
            document.getElementById('main_characteristics').value = data.main_characteristics || '';
            document.getElementById('complectation').value = data.complectation || '';
            document.getElementById('category_id').value = data.category_id || '';
            
            // Заполняем данные проверки
            if (data.check_data) {
                document.getElementById('check_status_id').value = data.check_data.status_id || '';
                document.getElementById('check_comment').value = data.check_data.status_comment || '';
            }
            
            // Заполняем данные погрузки
            if (data.loading_data) {
                document.getElementById('loading_type').value = data.loading_data.loading_type || '';
                document.getElementById('loading_comment').value = data.loading_data.loading_comment || '';
            }
            
            // Заполняем данные демонтажа
            if (data.removal_data) {
                document.getElementById('removal_type').value = data.removal_data.removal_type || '';
                document.getElementById('removal_comment').value = data.removal_data.removal_comment || '';
            }
            
            alert('Данные успешно скопированы из товара!');
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Ошибка при копировании данных');
        });
    });

    function loadProductMedia() {
        const productId = productSelect.value;
        const mediaGrid = document.getElementById('productMediaGrid');
        
        if (!productId) {
            mediaGrid.innerHTML = '<div class="no-media-message">Сначала выберите товар</div>';
            return;
        }

        mediaGrid.innerHTML = '<div class="no-media-message">Загрузка медиафайлов...</div>';

        fetch(`/advertisements/product/${productId}/media`)
            .then(response => response.json())
            .then(data => {
                if (data.length === 0) {
                    mediaGrid.innerHTML = '<div class="no-media-message">У выбранного товара нет медиафайлов</div>';
                    return;
                }

                let html = '';
                data.forEach(media => {
                    html += `
                        <div class="product-media-item" data-media-id="${media.id}">
                            <input type="checkbox" name="selected_product_media[]" value="${media.id}" class="media-checkbox">
                            ${media.file_type === 'image' 
                                ? `<img src="${media.full_url}" alt="${media.file_name}">`
                                : `<video src="${media.full_url}" muted></video>`
                            }
                            <span class="media-type-badge">${media.file_type === 'image' ? 'Фото' : 'Видео'}</span>
                            <div class="media-info">
                                <div class="media-name">${media.file_name}</div>
                                <div class="media-size">${media.formatted_size}</div>
                            </div>
                        </div>
                    `;
                });

                mediaGrid.innerHTML = html;

                // Добавляем обработчики для выбора медиафайлов
                document.querySelectorAll('.product-media-item').forEach(item => {
                    item.addEventListener('click', function(e) {
                        if (e.target.type !== 'checkbox') {
                            const checkbox = this.querySelector('.media-checkbox');
                            checkbox.checked = !checkbox.checked;
                        }
                        this.classList.toggle('selected', this.querySelector('.media-checkbox').checked);
                    });
                });
            })
            .catch(error => {
                console.error('Error:', error);
                mediaGrid.innerHTML = '<div class="no-media-message">Ошибка загрузки медиафайлов</div>';
            });
    }

    // Обработка загрузки файлов (аналогично ProductCreatePage)
    const fileInput = document.getElementById('media_files');
    const fileUploadArea = document.getElementById('fileUploadArea');
    const filePreview = document.getElementById('filePreview');
    let selectedFiles = [];

    // Аналогичная логика для обработки файлов как в ProductCreatePage
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
});
</script>
@endsection 