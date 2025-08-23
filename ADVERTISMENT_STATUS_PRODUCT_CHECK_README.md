# Проверка статуса товара при изменении статуса объявления

## Описание

Добавлена функциональность проверки статуса товара при попытке перевода объявления в статусы: **Ревизия**, **Активное**, **Резерв**.

Если связанный с объявлением товар находится в статусе **Холд** или **Отказ**, система блокирует изменение статуса объявления и показывает пользователю соответствующее предупреждение.

## Логика работы

### Ограничения

При попытке перевода объявления в следующие статусы:
- **Ревизия**
- **Активное** 
- **Резерв**

Система проверяет статус связанного товара. Если товар находится в статусе:
- **Холд**
- **Отказ**

То изменение статуса объявления блокируется.

### Разрешенные действия

1. **Перевод в другие статусы объявлений** - если товар в статусе "Холд" или "Отказ", можно переводить объявление в статусы "Холд", "Архив", "Продано" и другие (кроме Ревизия, Активное, Резерв).

2. **Изменение статуса объявления** - если товар НЕ в статусе "Холд" или "Отказ", можно переводить объявление в любой статус.

## Реализация

### Backend (PHP)

#### Контроллер: `app/Http/Controllers/Advertisement/AdvertisementController.php`

Метод `updateStatus()` был модифицирован для добавления проверки:

```php
public function updateStatus(Request $request, Advertisement $advertisement)
{
    // ... валидация ...

    // Проверяем, что объявление связано с товаром
    if (!$advertisement->product) {
        return response()->json([
            'success' => false,
            'message' => 'Объявление не связано с товаром'
        ], 400);
    }

    // Проверяем статус товара при переводе объявления в статусы: Ревизия, Активное, Резерв
    $restrictedStatuses = ['Ревизия', 'Активное', 'Резерв'];
    if (in_array($newStatus->name, $restrictedStatuses)) {
        $product = $advertisement->product;
        $productStatus = $product->status;
        
        if ($productStatus && in_array($productStatus->name, ['Холд', 'Отказ'])) {
            return response()->json([
                'success' => false,
                'message' => "Нельзя перевести объявление в статус '{$newStatus->name}', так как связанный товар находится в статусе '{$productStatus->name}'. Сначала переведите товар из статуса '{$productStatus->name}'.",
                'product_status' => $productStatus->name,
                'product_id' => $product->id
            ], 400);
        }
    }

    // ... обновление статуса и логирование ...
}
```

### Frontend (JavaScript)

#### Файл: `resources/views/Advertisement/AdvertisementShowPage.blade.php`

1. **Модальное окно предупреждения** - добавлено новое модальное окно для отображения предупреждения:

```html
<!-- Модальное окно для предупреждения о статусе товара -->
<div id="productStatusWarningModal" class="modal" style="display: none;">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Внимание!</h3>
            <span class="close" onclick="closeProductStatusWarning()">&times;</span>
        </div>
        <div class="modal-body">
            <div class="warning-icon">
                <!-- Иконка предупреждения -->
            </div>
            <div id="productStatusWarningMessage"></div>
            <div class="warning-actions">
                <p><strong>Для продолжения необходимо:</strong></p>
                <ol>
                    <li>Перейти к товару</li>
                    <li>Изменить статус товара с текущего на другой</li>
                    <li>Вернуться к объявлению и повторить смену статуса</li>
                </ol>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" onclick="closeProductStatusWarning()">Закрыть</button>
            <button type="button" class="btn btn-primary" onclick="goToProduct()">Перейти к товару</button>
        </div>
    </div>
</div>
```

2. **JavaScript функции** - добавлены функции для обработки предупреждения:

```javascript
// Показ предупреждения о статусе товара
function showProductStatusWarning(message, productId, productStatus) {
    productStatusWarningData = {
        productId: productId,
        productStatus: productStatus
    };
    
    const modal = document.getElementById('productStatusWarningModal');
    const messageElement = document.getElementById('productStatusWarningMessage');
    
    messageElement.innerHTML = `<p>${message}</p>`;
    modal.style.display = 'block';
}

// Переход к товару
function goToProduct() {
    if (productStatusWarningData && productStatusWarningData.productId) {
        window.location.href = `/product/${productStatusWarningData.productId}`;
    } else {
        window.location.href = '/products';
    }
}
```

3. **Обработка ошибок** - модифицирована функция `saveAdvertisementStatusChange()`:

```javascript
.then(data => {
    if (data.success) {
        // ... успешное обновление ...
    } else {
        // Проверяем, является ли ошибка связанной со статусом товара
        if (data.product_status && (data.product_status === 'Холд' || data.product_status === 'Отказ')) {
            showProductStatusWarning(data.message, data.product_id, data.product_status);
        } else {
            throw new Error(data.message || 'Ошибка при обновлении статуса');
        }
    }
})
```

## Тестирование

Создан тест `tests/Feature/AdvertisementStatusProductCheckTest.php` для проверки функциональности:

### Тестовые сценарии

1. **Блокировка при статусе товара "Холд"**:
   - Попытка перевода объявления в статус "Ревизия"
   - Попытка перевода объявления в статус "Активное"
   - Попытка перевода объявления в статус "Резерв"

2. **Блокировка при статусе товара "Отказ"**:
   - Попытка перевода объявления в статус "Ревизия"
   - Попытка перевода объявления в статус "Активное"
   - Попытка перевода объявления в статус "Резерв"

3. **Разрешенные действия**:
   - Перевод объявления в другие статусы при товаре в статусе "Холд"
   - Перевод объявления в любой статус при товаре в разрешенном статусе

4. **Обработка ошибок**:
   - Объявление без связанного товара

### Запуск тестов

```bash
php artisan test tests/Feature/AdvertisementStatusProductCheckTest.php
```

## Пользовательский интерфейс

### Сообщения об ошибках

При попытке изменить статус объявления на ограниченный статус, когда товар находится в статусе "Холд" или "Отказ", пользователь видит:

1. **Всплывающее окно** с предупреждением
2. **Подробное описание** проблемы
3. **Пошаговые инструкции** для решения
4. **Кнопку перехода** к товару для изменения его статуса

### Пример сообщения

```
Нельзя перевести объявление в статус 'Активное', так как связанный товар находится в статусе 'Холд'. Сначала переведите товар из статуса 'Холд'.
```

## Задействованные модели

- `Advertisement` - объявление
- `Product` - товар
- `ProductStatus` - статусы товаров
- `AdvertisementStatus` - статусы объявлений
- `AdvLog` - логи объявлений

## API Endpoints

### PATCH `/advertisements/{id}/status`

**Параметры:**
- `status_id` (required) - ID нового статуса объявления
- `comment` (required) - комментарий к изменению

**Ответы:**

**Успех (200):**
```json
{
    "success": true,
    "message": "Статус объявления успешно обновлен",
    "log": { ... }
}
```

**Ошибка (400) - товар в ограниченном статусе:**
```json
{
    "success": false,
    "message": "Нельзя перевести объявление в статус 'Активное', так как связанный товар находится в статусе 'Холд'. Сначала переведите товар из статуса 'Холд'.",
    "product_status": "Холд",
    "product_id": 123
}
```

**Ошибка (400) - объявление без товара:**
```json
{
    "success": false,
    "message": "Объявление не связано с товаром"
}
```
