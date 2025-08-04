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
                if (currentStep < 4) {
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

    // Автоматическое формирование артикула при выборе склада
    const warehouseSelect = document.getElementById('warehouse_id');
    const skuInput = document.getElementById('sku');

    if (warehouseSelect && skuInput) {
        warehouseSelect.addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            if (selectedOption.value && !skuInput.value.trim()) {
                // Получаем название склада
                const warehouseName = selectedOption.text.trim();
                
                // Получаем следующий доступный номер через API
                fetch(`/company/next-sku/${encodeURIComponent(warehouseName)}`)
                    .then(response => response.json())
                    .then(data => {
                        skuInput.value = data.next_sku;
                    })
                    .catch(error => {
                        console.error('Ошибка при получении следующего номера артикула:', error);
                        // Fallback: используем базовый формат
                        const sku = `${warehouseName}-001`;
                        skuInput.value = sku;
                    });
            }
        });

        // Обрабатываем случай, когда пользователь очищает поле артикула
        skuInput.addEventListener('blur', function() {
            if (!this.value.trim() && warehouseSelect.value) {
                const selectedOption = warehouseSelect.options[warehouseSelect.selectedIndex];
                const warehouseName = selectedOption.text.trim();
                
                // Получаем следующий доступный номер через API
                fetch(`/company/next-sku/${encodeURIComponent(warehouseName)}`)
                    .then(response => response.json())
                    .then(data => {
                        skuInput.value = data.next_sku;
                    })
                    .catch(error => {
                        console.error('Ошибка при получении следующего номера артикула:', error);
                        // Fallback: используем базовый формат
                        const sku = `${warehouseName}-001`;
                        skuInput.value = sku;
                    });
            }
        });

        // Обрабатываем случай, когда пользователь вводит что-то вручную
        skuInput.addEventListener('input', function() {
            // Если поле пустое и склад выбран, предлагаем автоматический артикул
            if (!this.value.trim() && warehouseSelect.value) {
                const selectedOption = warehouseSelect.options[warehouseSelect.selectedIndex];
                const warehouseName = selectedOption.text.trim();
                
                // Получаем следующий доступный номер через API
                fetch(`/company/next-sku/${encodeURIComponent(warehouseName)}`)
                    .then(response => response.json())
                    .then(data => {
                        this.placeholder = `Предлагаемый: ${data.next_sku}`;
                    })
                    .catch(error => {
                        console.error('Ошибка при получении следующего номера артикула:', error);
                        // Fallback: используем базовый формат
                        this.placeholder = `Предлагаемый: ${warehouseName}-001`;
                    });
            } else {
                this.placeholder = "Будет сгенерирован автоматически";
            }
        });
    }

    // Обработка добавления адресов
    const addAddressBtn = document.getElementById('add-address');
    const addressesContainer = document.getElementById('addresses-container');

    if (addAddressBtn && addressesContainer) {
        addAddressBtn.addEventListener('click', function() {
            const addressBlock = document.createElement('div');
            addressBlock.className = 'address-block';
            addressBlock.innerHTML = `
                <input type="text" name="addresses[]" class="form-control" placeholder="Введите адрес">
                <div class="form-check">
                    <input type="checkbox" name="main_address[]" class="form-check-input" value="1">
                    <label class="form-check-label">Основной адрес</label>
                </div>
                <button type="button" class="btn btn-danger remove-address">Удалить</button>
            `;
            
            addressesContainer.appendChild(addressBlock);
        });
    }

    // Обработка удаления адресов через делегирование событий
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('remove-address')) {
            const addressBlock = e.target.closest('.address-block');
            addressBlock.remove();
        }
    });

    // Обработка добавления контактов
    const addContactBtn = document.getElementById('add-contact');
    const contactsContainer = document.getElementById('contacts-container');
    let contactIndex = 1;

    if (addContactBtn && contactsContainer) {
        addContactBtn.addEventListener('click', function() {
            const contactBlock = document.createElement('div');
            contactBlock.className = 'contact-block';
            contactBlock.innerHTML = `
                <input type="text" name="contact_name[]" class="form-control" placeholder="ФИО контактного лица" required>
                <div class="phones-container">
                    <div class="phone-block">
                        <div class="phone-input-group">
                            <input type="tel" name="phones[${contactIndex}][]" class="form-control" placeholder="Телефон" required>
                            <button type="button" class="btn btn-secondary add-phone">+</button>
                        </div>
                    </div>
                </div>
                <input type="text" name="position[]" class="form-control" placeholder="Должность" required>
                <div class="form-check">
                    <input type="checkbox" name="main_contact[]" class="form-check-input" value="1">
                    <label class="form-check-label">Основной контакт</label>
                </div>
                <button type="button" class="btn btn-danger remove-contact">Удалить</button>
            `;
            
            contactsContainer.appendChild(contactBlock);
            contactIndex++;
        });
    }

    // Обработка удаления контактов через делегирование событий
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('remove-contact')) {
            const contactBlock = e.target.closest('.contact-block');
            contactBlock.remove();
            // Обновляем индексы после удаления
            updateContactIndexes();
        }
    });

    // Функция для обновления индексов контактов
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
        });
    }

    // Обработка добавления телефонов через делегирование событий
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('add-phone')) {
            const contactBlock = e.target.closest('.contact-block');
            const contactsContainer = document.getElementById('contacts-container');
            const contactIndex = Array.from(contactsContainer.children).indexOf(contactBlock);
            
            addPhoneToContact(contactBlock, contactIndex);
        }
    });

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
        
        phoneBlock.querySelector('.remove-phone').addEventListener('click', function() {
            phoneBlock.remove();
        });
        
        phonesContainer.appendChild(phoneBlock);
    }

    // Инициализация
    showStep(1);
}); 