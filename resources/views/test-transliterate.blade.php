<!DOCTYPE html>
<html>
<head>
    <title>Тест транслитерации</title>
    <meta charset="utf-8">
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .test-case { margin: 10px 0; padding: 10px; border: 1px solid #ccc; }
        .success { background-color: #d4edda; }
        .error { background-color: #f8d7da; }
        input[type="text"] { width: 300px; padding: 5px; }
        button { padding: 5px 10px; margin: 5px; }
    </style>
</head>
<body>
    <h1>Тест функции транслитерации</h1>
    
    <div>
        <input type="text" id="testInput" placeholder="Введите текст для тестирования">
        <button onclick="testTransliterate()">Тестировать</button>
    </div>
    
    <div id="results"></div>
    
    <script>
        function testTransliterate() {
            const input = document.getElementById('testInput').value;
            if (!input) return;
            
            fetch(`/test-transliterate/${encodeURIComponent(input)}`)
                .then(response => response.json())
                .then(data => {
                    const resultsDiv = document.getElementById('results');
                    const div = document.createElement('div');
                    div.className = 'test-case';
                    div.innerHTML = `
                        <strong>Исходный текст:</strong> ${data.original}<br>
                        <strong>Результат:</strong> ${data.transliterated}
                    `;
                    resultsDiv.appendChild(div);
                })
                .catch(error => {
                    console.error('Ошибка:', error);
                });
        }
        
        // Автоматическое тестирование при загрузке страницы
        window.onload = function() {
            const testCases = [
                'ABC-123',
                'ООО-Рога-и-Копыта',
                'Токарный-16К20',
                'Станок-ЧПУ-2024',
                'Компания-с-кириллицей',
                'ABC123',
                '---test---',
                'тест-с-пробелами',
                'МАШИНА-СТРОИТЕЛЬНАЯ',
                'machine-building'
            ];
            
            testCases.forEach(testCase => {
                fetch(`/test-transliterate/${encodeURIComponent(testCase)}`)
                    .then(response => response.json())
                    .then(data => {
                        const resultsDiv = document.getElementById('results');
                        const div = document.createElement('div');
                        div.className = 'test-case';
                        div.innerHTML = `
                            <strong>Исходный текст:</strong> ${data.original}<br>
                            <strong>Результат:</strong> ${data.transliterated}
                        `;
                        resultsDiv.appendChild(div);
                    })
                    .catch(error => {
                        console.error('Ошибка:', error);
                    });
            });
        };
    </script>
</body>
</html> 