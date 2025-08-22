# Система логирования изменений блоков объявлений

## Описание

Система автоматически создает системные логи при изменении следующих блоков объявлений:
- **Технические характеристики** (`technical_characteristics`)
- **Основная информация** (`main_info`)
- **Дополнительная информация** (`additional_info`)

## Формат логов

Логи создаются в формате:
```
Пользователь [Имя пользователя] изменил [Название блока]
```

### Примеры:
- `Пользователь Иван Петров изменил Технические характеристики`
- `Пользователь Мария Сидорова изменил Основная информация`
- `Пользователь Алексей Козлов изменил Дополнительная информация`

## Техническая реализация

### Контроллер
Функциональность реализована в методе `updateComment` контроллера `AdvertisementController`:

```php
public function updateComment(Request $request, Advertisement $advertisement)
{
    // ... валидация и обновление данных ...
    
    // Логируем изменения для технических характеристик, основной информации и дополнительной информации
    if (in_array($field, ['technical_characteristics', 'main_info', 'additional_info'])) {
        $this->logAdvertisementBlockChanges($advertisement, $field, $oldValue, $value);
    }
}
```

### Метод логирования
Логирование выполняется методом `logAdvertisementBlockChanges`:

```php
private function logAdvertisementBlockChanges(Advertisement $advertisement, $field, $oldValue, $newValue)
{
    // Проверяем изменение значения
    if ($oldValue !== $newValue) {
        $userName = auth()->user()->name ?? 'Неизвестный пользователь';
        $blockName = match($field) {
            'technical_characteristics' => 'Технические характеристики',
            'main_info' => 'Основная информация',
            'additional_info' => 'Дополнительная информация',
            default => ucfirst($field)
        };
        $logMessage = "Пользователь {$userName} изменил {$blockName}";
        
        // Создаем запись в логе от имени системы
        $systemLogType = LogType::where('name', 'Системный')->first();
        AdvLog::create([
            'advertisement_id' => $advertisement->id,
            'user_id' => null, // От имени системы
            'log' => $logMessage,
            'type_id' => $systemLogType ? $systemLogType->id : null
        ]);
    }
}
```

## Особенности

1. **Системные логи**: Логи создаются от имени системы (`user_id = null`)
2. **Тип лога**: Используется тип "Системный" с цветом `#6c757d`
3. **Условное логирование**: Логи создаются только при фактическом изменении значений
4. **Безопасность**: Проверяется аутентификация пользователя

## Тестирование

Создан тестовый класс `AdvertisementBlockLogTest` с тремя тестами:
- `test_technical_characteristics_update_creates_system_log()`
- `test_main_info_update_creates_system_log()`
- `test_additional_info_update_creates_system_log()`

### Запуск тестов:
```bash
php artisan test --filter=AdvertisementBlockLogTest
```

## Маршруты

Функциональность доступна через маршрут:
```
PATCH /advertisements/{id}/comment
```

### Параметры запроса:
- `field`: Поле для обновления (`technical_characteristics`, `main_info`, `additional_info`)
- `value`: Новое значение

### Пример запроса:
```json
{
    "field": "technical_characteristics",
    "value": "Новые технические характеристики"
}
```

## База данных

Логи сохраняются в таблице `adv_logs` со следующими полями:
- `advertisement_id`: ID объявления
- `user_id`: null (от имени системы)
- `log`: Текст лога
- `type_id`: ID типа лога "Системный"
- `created_at`: Время создания лога
