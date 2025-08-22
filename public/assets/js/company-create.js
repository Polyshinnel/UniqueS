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

    // Автоматическое формирование артикула и загрузка регионалов при выборе склада
    const warehouseSelect = document.getElementById('warehouse_id');
    const skuInput = document.getElementById('sku');
    const regionalSelect = document.getElementById('region_id');
    const regionDisplay = document.getElementById('region_display');
    const regionHidden = document.getElementById('region');

    if (warehouseSelect) {
        warehouseSelect.addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            const selectedWarehouseId = this.value;
            
            // Очищаем поля при смене склада
            if (skuInput) skuInput.value = '';
            if (regionalSelect) {
                regionalSelect.value = '';
                regionalSelect.innerHTML = '<option value="">Загрузка...</option>';
                regionalSelect.disabled = true;
            }
            if (regionDisplay) regionDisplay.value = '';
            if (regionHidden) regionHidden.value = '';
            
            if (selectedOption.value) {
                // Получаем название склада
                const warehouseName = selectedOption.text.trim();
                
                // Получаем следующий доступный номер через API
                if (skuInput) {
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
                
                // Загружаем региональных представителей
                if (regionalSelect) {
                    loadRegionals(selectedWarehouseId);
                }
                
                // Получаем информацию о регионе
                if (regionDisplay && regionHidden) {
                    fetch(`/company/warehouse-region/${selectedWarehouseId}`)
                        .then(response => response.json())
                        .then(data => {
                            if (data.region) {
                                regionDisplay.value = data.region.name;
                                regionHidden.value = data.region.id;
                            }
                        })
                        .catch(error => {
                            console.error('Ошибка при получении информации о регионе:', error);
                        });
                }
            } else {
                // Если склад не выбран, показываем сообщение
                if (regionalSelect) {
                    regionalSelect.innerHTML = '<option value="">Сначала выберите склад</option>';
                    regionalSelect.disabled = false;
                }
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

        // Обрабатываем ввод в поле артикула
        skuInput.addEventListener('input', function() {
            // Если склад выбран, показываем актуальный предлагаемый артикул
            if (warehouseSelect.value) {
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

            // Если поле очищено и склад выбран, автоматически заполняем через 1 секунду
            if (!this.value.trim() && warehouseSelect.value) {
                // Небольшая задержка, чтобы пользователь мог закончить ввод
                setTimeout(() => {
                    if (!this.value.trim()) {
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
                }, 1000); // Задержка в 1 секунду
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
                <div class="emails-container">
                    <div class="email-block">
                        <div class="email-input-group">
                            <input type="email" name="contact_emails[${contactIndex}][]" class="form-control" placeholder="Email">
                            <button type="button" class="btn btn-secondary add-email">+</button>
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

    // Обработка удаления телефонов и email через делегирование событий
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('remove-phone')) {
            const phoneBlock = e.target.closest('.phone-block');
            phoneBlock.remove();
        }
        
        if (e.target.classList.contains('remove-email')) {
            const emailBlock = e.target.closest('.email-block');
            emailBlock.remove();
        }
        
        if (e.target.classList.contains('remove-company-email')) {
            const emailBlock = e.target.closest('.company-email-block');
            emailBlock.remove();
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

            // Обновляем имена полей email
            const emailInputs = contactBlock.querySelectorAll('input[name^="contact_emails"]');
            emailInputs.forEach(emailInput => {
                emailInput.name = `contact_emails[${index}][]`;
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

    // Обработка добавления email контактов через делегирование событий
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('add-email')) {
            const contactBlock = e.target.closest('.contact-block');
            const contactsContainer = document.getElementById('contacts-container');
            const contactIndex = Array.from(contactsContainer.children).indexOf(contactBlock);
            
            addEmailToContact(contactBlock, contactIndex);
        }
    });

    // Обработка добавления email компании
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('add-company-email')) {
            addCompanyEmail();
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
        
        emailBlock.querySelector('.remove-email').addEventListener('click', function() {
            emailBlock.remove();
        });
        
        emailsContainer.appendChild(emailBlock);
    }

    function addCompanyEmail() {
        const companyEmailsContainer = document.getElementById('company-emails-container');
        const emailBlock = document.createElement('div');
        emailBlock.className = 'company-email-block';
        emailBlock.innerHTML = `
            <div class="email-input-group">
                <input type="email" name="company_emails[]" class="form-control" placeholder="Дополнительный email">
                <button type="button" class="btn btn-danger remove-company-email">×</button>
            </div>
        `;
        
        emailBlock.querySelector('.remove-company-email').addEventListener('click', function() {
            emailBlock.remove();
        });
        
        companyEmailsContainer.appendChild(emailBlock);
    }

    // Функция для загрузки региональных представителей
    function loadRegionals(warehouseId, selectedRegionalId = null) {
        const regionalSelect = document.getElementById('region_id');
        if (!regionalSelect) return;
        
        regionalSelect.innerHTML = '<option value="">Загрузка...</option>';
        regionalSelect.disabled = true;
        
        if (warehouseId) {
            fetch(`/company/regionals/warehouse/${warehouseId}`)
                .then(response => response.json())
                .then(data => {
                    regionalSelect.innerHTML = '<option value="">Выберите регионала</option>';
                    regionalSelect.disabled = false;
                    
                    if (data.length > 0) {
                        data.forEach(regional => {
                            const option = document.createElement('option');
                            option.value = regional.id;
                            option.textContent = regional.name;
                            if (selectedRegionalId && regional.id == selectedRegionalId) {
                                option.selected = true;
                            }
                            regionalSelect.appendChild(option);
                        });
                    } else {
                        const option = document.createElement('option');
                        option.value = '';
                        option.textContent = 'Нет доступных региональных представителей';
                        option.disabled = true;
                        regionalSelect.appendChild(option);
                    }
                })
                .catch(error => {
                    console.error('Ошибка при получении региональных представителей:', error);
                    regionalSelect.innerHTML = '<option value="">Ошибка загрузки данных</option>';
                    regionalSelect.disabled = true;
                });
        } else {
            regionalSelect.innerHTML = '<option value="">Сначала выберите склад</option>';
            regionalSelect.disabled = false;
        }
    }

    // Инициализация при загрузке страницы
    showStep(1);
    
    // При загрузке страницы, если есть выбранный склад (например, при ошибках валидации)
    const selectedWarehouseId = warehouseSelect ? warehouseSelect.value : null;
    const oldRegionalId = document.getElementById('old_region_id') ? document.getElementById('old_region_id').value : null;
    
    if (selectedWarehouseId) {
        loadRegionals(selectedWarehouseId, oldRegionalId);
        
        // Загружаем информацию о регионе
        const selectedOption = warehouseSelect.options[warehouseSelect.selectedIndex];
        const warehouseName = selectedOption.text.trim();
        
        fetch(`/company/warehouse-region/${selectedWarehouseId}`)
            .then(response => response.json())
            .then(data => {
                if (data.region) {
                    const regionDisplay = document.getElementById('region_display');
                    const regionHidden = document.getElementById('region');
                    if (regionDisplay) regionDisplay.value = data.region.name;
                    if (regionHidden) regionHidden.value = data.region.id;
                }
            })
            .catch(error => {
                console.error('Ошибка при получении информации о регионе:', error);
            });
    }
}); 