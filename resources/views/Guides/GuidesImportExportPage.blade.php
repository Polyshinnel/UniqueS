@extends('layouts.layout')

@section('title', 'Импорт/Экспорт')

@section('header-title')
    <h1 class="header-title">Импорт/Экспорт</h1>
@endsection

@section('content')
<div class="import-export-container">
    <div class="breadcrumb">
        <a href="/">Главная</a> / <a href="/guide">Справочники</a> / Импорт/Экспорт
    </div>

    @if(session('success'))
        <div class="alert alert-success">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M20 7L10 17L5 12"></path>
            </svg>
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="12" cy="12" r="10"></circle>
                <line x1="15" y1="9" x2="9" y2="15"></line>
                <line x1="9" y1="9" x2="15" y2="15"></line>
            </svg>
            {{ session('error') }}
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="12" cy="12" r="10"></circle>
                <line x1="15" y1="9" x2="9" y2="15"></line>
                <line x1="9" y1="9" x2="15" y2="15"></line>
            </svg>
            <ul>
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="import-export-grid">
        <!-- Импорт компаний -->
        <div class="import-export-card">
            <div class="card-header">
                <div class="card-icon import-icon">
                    <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                        <polyline points="7,10 12,15 17,10"></polyline>
                        <line x1="12" y1="15" x2="12" y2="3"></line>
                    </svg>
                </div>
                <div class="card-title-section">
                    <h3 class="card-title">Импорт компаний</h3>
                    <p class="card-description">Загрузите файл Excel или CSV для импорта компаний в систему</p>
                </div>
            </div>
            <div class="card-body">
                <form action="{{ route('import-export.companies.import') }}" method="POST" enctype="multipart/form-data" class="import-form">
                    @csrf
                    <div class="form-group">
                        <label for="companies_file" class="file-label">
                            <div class="file-input-wrapper">
                                <input type="file" name="file" id="companies_file" accept=".xlsx,.xls,.csv" required class="file-input">
                                <div class="file-input-display">
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                                        <polyline points="17,8 12,3 7,8"></polyline>
                                        <line x1="12" y1="3" x2="12" y2="15"></line>
                                    </svg>
                                    <span class="file-text">Выберите файл</span>
                                    <span class="file-name" id="companies_file_name"></span>
                                </div>
                            </div>
                        </label>
                        <p class="file-hint">Поддерживаемые форматы: .xlsx, .xls, .csv (макс. 10 МБ)</p>
                    </div>
                    <button type="submit" class="btn btn-primary btn-block">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                            <polyline points="7,10 12,15 17,10"></polyline>
                            <line x1="12" y1="15" x2="12" y2="3"></line>
                        </svg>
                        Импортировать компании
                    </button>
                </form>
            </div>
        </div>

        <!-- Экспорт компаний -->
        <div class="import-export-card">
            <div class="card-header">
                <div class="card-icon export-icon">
                    <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                        <polyline points="7,10 12,15 17,10"></polyline>
                        <line x1="12" y1="15" x2="12" y2="3"></line>
                    </svg>
                </div>
                <div class="card-title-section">
                    <h3 class="card-title">Экспорт компаний</h3>
                    <p class="card-description">Скачайте список всех компаний в формате Excel</p>
                </div>
            </div>
            <div class="card-body">
                <form action="{{ route('import-export.companies.export') }}" method="POST" class="export-form">
                    @csrf
                    <div class="export-info">
                        <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                            <polyline points="7,10 12,15 17,10"></polyline>
                            <line x1="12" y1="15" x2="12" y2="3"></line>
                        </svg>
                        <p>Нажмите кнопку ниже, чтобы скачать файл со всеми компаниями</p>
                    </div>
                    <button type="submit" class="btn btn-primary btn-block">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                            <polyline points="7,10 12,15 17,10"></polyline>
                            <line x1="12" y1="15" x2="12" y2="3"></line>
                        </svg>
                        Экспортировать компании
                    </button>
                </form>
            </div>
        </div>

        <!-- Экспорт товаров -->
        <div class="import-export-card">
            <div class="card-header">
                <div class="card-icon export-icon">
                    <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                        <polyline points="7,10 12,15 17,10"></polyline>
                        <line x1="12" y1="15" x2="12" y2="3"></line>
                    </svg>
                </div>
                <div class="card-title-section">
                    <h3 class="card-title">Экспорт товаров</h3>
                    <p class="card-description">Скачайте список всех товаров в формате Excel</p>
                </div>
            </div>
            <div class="card-body">
                <form action="{{ route('import-export.products.export') }}" method="POST" class="export-form">
                    @csrf
                    <div class="export-info">
                        <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                            <polyline points="7,10 12,15 17,10"></polyline>
                            <line x1="12" y1="15" x2="12" y2="3"></line>
                        </svg>
                        <p>Нажмите кнопку ниже, чтобы скачать файл со всеми товарами</p>
                    </div>
                    <button type="submit" class="btn btn-primary btn-block">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                            <polyline points="7,10 12,15 17,10"></polyline>
                            <line x1="12" y1="15" x2="12" y2="3"></line>
                        </svg>
                        Экспортировать товары
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Модальное окно с результатами импорта -->
@if(session('import_results'))
<div id="importResultsModal" class="modal-overlay" style="display: block;">
    <div class="modal-content">
        <div class="modal-header">
            <h2 class="modal-title">Результаты импорта компаний</h2>
            <button class="modal-close" onclick="closeImportModal()">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
            </button>
        </div>
        <div class="modal-body">
            <div class="import-summary">
                @php
                    $results = session('import_results', []);
                    $successCount = 0;
                    $skippedCount = 0;
                    foreach ($results as $result) {
                        if (isset($result['skipped']) && $result['skipped']) {
                            $skippedCount++;
                        } else {
                            $successCount++;
                        }
                    }
                @endphp
                <p><strong>Всего обработано:</strong> {{ count($results) }}</p>
                <p><strong>Успешно создано:</strong> {{ $successCount }}</p>
                <p><strong>Пропущено:</strong> {{ $skippedCount }}</p>
            </div>
            <div class="import-table-wrapper">
                <table class="import-results-table">
                    <thead>
                        <tr>
                            <th>Название компании</th>
                            <th>Артикул компании</th>
                            <th>Владелец компании</th>
                            <th>Региональный представитель</th>
                            <th>Склад</th>
                            <th>Причина пропуска</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach(session('import_results') as $result)
                        <tr class="{{ ($result['skipped'] ?? false) ? 'skipped-row' : '' }}">
                            <td>{{ $result['name'] ?? 'Не указано' }}</td>
                            <td>{{ $result['sku'] ?? 'Не указано' }}</td>
                            <td>{{ $result['owner'] ?? 'Не указано' }}</td>
                            <td>{{ $result['regional'] ?? 'Не указано' }}</td>
                            <td>{{ $result['warehouse'] ?? 'Не указано' }}</td>
                            <td>{{ $result['reason'] ?? '-' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            @if(session('import_debug_data'))
            <div class="debug-section">
                <h3 class="debug-title">Отладочная информация (JSON)</h3>
                <div class="debug-content">
                    <pre class="debug-json">{{ json_encode(session('import_debug_data'), JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) }}</pre>
                </div>
            </div>
            @endif
        </div>
        <div class="modal-footer">
            <button class="btn btn-primary" onclick="closeImportModal()">Закрыть</button>
        </div>
    </div>
</div>
@endif
@endsection

@push('styles')
<style>
.import-export-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 20px;
}

.breadcrumb {
    margin-bottom: 20px;
    font-size: 14px;
    color: #666;
}

.breadcrumb a {
    color: #133E71;
    text-decoration: none;
    transition: color 0.3s ease;
}

.breadcrumb a:hover {
    color: #1C5BA4;
    text-decoration: underline;
}

.alert {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 16px 20px;
    border-radius: 8px;
    margin-bottom: 20px;
    font-weight: 500;
}

.alert-success {
    background-color: #e8f5e8;
    color: #2e7d32;
    border: 1px solid #c8e6c9;
}

.alert-danger {
    background-color: #ffebee;
    color: #c62828;
    border: 1px solid #ffcdd2;
}

.alert ul {
    margin: 8px 0 0 0;
    padding-left: 20px;
}

.alert li {
    margin-bottom: 4px;
}

.import-export-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
    gap: 25px;
}

.import-export-card {
    background: white;
    border-radius: 12px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.08);
    overflow: hidden;
    transition: all 0.3s ease;
}

.import-export-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 30px rgba(0,0,0,0.12);
}

.card-header {
    display: flex;
    align-items: flex-start;
    gap: 20px;
    padding: 25px;
    background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
    border-bottom: 1px solid #e9ecef;
}

.card-icon {
    flex-shrink: 0;
    width: 60px;
    height: 60px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
}

.import-icon {
    background: linear-gradient(135deg, #133E71, #1C5BA4);
}

.export-icon {
    background: linear-gradient(135deg, #2e7d32, #388e3c);
}

.card-title-section {
    flex: 1;
    min-width: 0;
}

.card-title {
    font-size: 20px;
    color: #133E71;
    margin: 0 0 8px 0;
    font-weight: 600;
}

.card-description {
    font-size: 14px;
    color: #666;
    margin: 0;
    line-height: 1.5;
}

.card-body {
    padding: 25px;
}

.import-form,
.export-form {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.form-group {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.file-label {
    cursor: pointer;
}

.file-input-wrapper {
    position: relative;
}

.file-input {
    position: absolute;
    opacity: 0;
    width: 0;
    height: 0;
}

.file-input-display {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 12px;
    padding: 40px 20px;
    border: 2px dashed #e9ecef;
    border-radius: 8px;
    background: #f8f9fa;
    transition: all 0.3s ease;
    cursor: pointer;
}

.file-label:hover .file-input-display {
    border-color: #133E71;
    background: #f0f4f8;
}

.file-input-display svg {
    color: #133E71;
}

.file-text {
    font-size: 16px;
    font-weight: 600;
    color: #133E71;
}

.file-name {
    font-size: 14px;
    color: #666;
    max-width: 100%;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.file-hint {
    font-size: 12px;
    color: #999;
    margin: 0;
    text-align: center;
}

.export-info {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 16px;
    padding: 30px 20px;
    text-align: center;
    background: #f8f9fa;
    border-radius: 8px;
}

.export-info svg {
    color: #2e7d32;
}

.export-info p {
    margin: 0;
    font-size: 14px;
    color: #666;
    line-height: 1.6;
}

.btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    padding: 12px 20px;
    border: none;
    border-radius: 8px;
    font-size: 14px;
    font-weight: 500;
    text-decoration: none;
    cursor: pointer;
    transition: all 0.3s ease;
    text-align: center;
}

.btn-primary {
    background: #133E71;
    color: white;
    box-shadow: 0 2px 8px rgba(19, 62, 113, 0.3);
}

.btn-primary:hover {
    background: #1C5BA4;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(19, 62, 113, 0.4);
}

.btn-block {
    width: 100%;
}

.btn svg {
    width: 16px;
    height: 16px;
}

/* Адаптивность */
@media (max-width: 768px) {
    .import-export-container {
        padding: 15px;
    }
    
    .import-export-grid {
        grid-template-columns: 1fr;
        gap: 20px;
    }
    
    .card-header {
        flex-direction: column;
        align-items: center;
        text-align: center;
        gap: 15px;
    }
    
    .card-icon {
        width: 50px;
        height: 50px;
    }
    
    .card-title {
        font-size: 18px;
    }
    
    .card-description {
        font-size: 13px;
    }
    
    .file-input-display {
        padding: 30px 15px;
    }
}

/* Модальное окно */
.modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1000;
    padding: 20px;
}

.modal-content {
    background: white;
    border-radius: 12px;
    max-width: 90%;
    max-height: 90vh;
    width: 1200px;
    display: flex;
    flex-direction: column;
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
}

.modal-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 20px 25px;
    border-bottom: 1px solid #e9ecef;
}

.modal-title {
    font-size: 24px;
    font-weight: 600;
    color: #133E71;
    margin: 0;
}

.modal-close {
    background: none;
    border: none;
    cursor: pointer;
    padding: 5px;
    color: #666;
    transition: color 0.3s ease;
    display: flex;
    align-items: center;
    justify-content: center;
}

.modal-close:hover {
    color: #133E71;
}

.modal-body {
    padding: 25px;
    overflow: auto;
    flex: 1;
}

.import-summary {
    display: flex;
    gap: 30px;
    margin-bottom: 20px;
    padding: 15px;
    background: #f8f9fa;
    border-radius: 8px;
}

.import-summary p {
    margin: 0;
    font-size: 14px;
    color: #333;
}

.import-table-wrapper {
    overflow-x: auto;
    overflow-y: auto;
    max-height: 500px;
    border: 1px solid #e9ecef;
    border-radius: 8px;
}

.import-results-table {
    width: 100%;
    border-collapse: collapse;
    min-width: 800px;
}

.import-results-table thead {
    background: #f8f9fa;
    position: sticky;
    top: 0;
    z-index: 10;
}

.import-results-table th {
    padding: 12px 15px;
    text-align: left;
    font-weight: 600;
    font-size: 14px;
    color: #133E71;
    border-bottom: 2px solid #e9ecef;
    white-space: nowrap;
}

.import-results-table td {
    padding: 12px 15px;
    font-size: 14px;
    color: #333;
    border-bottom: 1px solid #e9ecef;
}

.import-results-table tbody tr:hover {
    background: #f8f9fa;
}

.import-results-table tbody tr.skipped-row {
    background: #ffebee;
    color: #c62828;
}

.import-results-table tbody tr.skipped-row:hover {
    background: #ffcdd2;
}

.modal-footer {
    padding: 20px 25px;
    border-top: 1px solid #e9ecef;
    display: flex;
    justify-content: flex-end;
}

/* Отладочная секция */
.debug-section {
    margin-top: 30px;
    padding-top: 20px;
    border-top: 2px solid #e9ecef;
}

.debug-title {
    font-size: 18px;
    font-weight: 600;
    color: #133E71;
    margin: 0 0 15px 0;
}

.debug-content {
    background: #f8f9fa;
    border: 1px solid #e9ecef;
    border-radius: 8px;
    padding: 15px;
    max-height: 400px;
    overflow: auto;
}

.debug-json {
    margin: 0;
    font-family: 'Courier New', monospace;
    font-size: 12px;
    line-height: 1.5;
    color: #333;
    white-space: pre-wrap;
    word-wrap: break-word;
}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const fileInput = document.getElementById('companies_file');
    const fileNameDisplay = document.getElementById('companies_file_name');
    
    if (fileInput && fileNameDisplay) {
        fileInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                fileNameDisplay.textContent = file.name;
                fileNameDisplay.style.display = 'block';
            } else {
                fileNameDisplay.textContent = '';
                fileNameDisplay.style.display = 'none';
            }
        });
    }

    // Показываем модальное окно при наличии результатов импорта
    @if(session('import_results'))
        const modal = document.getElementById('importResultsModal');
        if (modal) {
            modal.style.display = 'flex';
        }
    @endif
});

function closeImportModal() {
    const modal = document.getElementById('importResultsModal');
    if (modal) {
        modal.style.display = 'none';
    }
}

// Закрытие модального окна при клике вне его
document.addEventListener('click', function(e) {
    const modal = document.getElementById('importResultsModal');
    if (modal && e.target === modal) {
        closeImportModal();
    }
});
</script>
@endpush

