document.addEventListener('DOMContentLoaded', function() {
    // Добавление адреса
    const addAddressBtn = document.getElementById('add-address');
    const addressesContainer = document.getElementById('addresses-container');

    if (addAddressBtn && addressesContainer) {
        addAddressBtn.addEventListener('click', function() {
            const addressBlock = document.createElement('div');
            addressBlock.className = 'address-block';
            addressBlock.innerHTML = `
                <input type="text" name="addresses[]" class="form-control" placeholder="Введите адрес">
                <div class="form-check">
                    <input type="checkbox" name="main_address[]" class="form-check-input">
                    <label class="form-check-label">Основной адрес</label>
                </div>
            `;
            addressesContainer.appendChild(addressBlock);
        });
    }

    const contactsContainer = document.getElementById('contacts-container');
    const addContactButton = document.getElementById('add-contact');
    let contactIndex = 1;

    // Функция для добавления нового контакта
    function addContact() {
        const contactBlock = document.createElement('div');
        contactBlock.className = 'contact-block';
        contactBlock.innerHTML = `
            <input type="text" name="contact_name[]" class="form-control" placeholder="ФИО контактного лица" value="{{ old('contact_name.${contactIndex}') }}">
            <div class="phones-container">
                <div class="phone-block">
                    <div class="phone-input-group">
                        <input type="tel" name="phones[${contactIndex}][]" class="form-control" placeholder="Телефон">
                        <button type="button" class="btn btn-secondary add-phone">+</button>
                    </div>
                </div>
            </div>
            <input type="text" name="position[]" class="form-control" placeholder="Должность" value="{{ old('position.${contactIndex}') }}">
            <div class="form-check">
                <input type="checkbox" name="main_contact[]" class="form-check-input" value="1">
                <label class="form-check-label">Основной контакт</label>
            </div>
            <button type="button" class="btn btn-danger remove-contact">Удалить контакт</button>
        `;
        contactsContainer.appendChild(contactBlock);
        contactIndex++;

        // Добавляем обработчик для кнопки удаления контакта
        const removeButton = contactBlock.querySelector('.remove-contact');
        removeButton.addEventListener('click', function() {
            contactBlock.remove();
        });

        // Добавляем обработчик для кнопки добавления телефона
        const addPhoneButton = contactBlock.querySelector('.add-phone');
        addPhoneButton.addEventListener('click', function() {
            addPhone(contactBlock.querySelector('.phones-container'));
        });
    }

    // Функция для добавления нового телефона
    function addPhone(phonesContainer) {
        const phoneBlock = document.createElement('div');
        phoneBlock.className = 'phone-block';
        phoneBlock.innerHTML = `
            <div class="phone-input-group">
                <input type="tel" name="phones[${contactIndex-1}][]" class="form-control" placeholder="Телефон">
                <button type="button" class="btn btn-danger remove-phone">-</button>
            </div>
        `;
        phonesContainer.appendChild(phoneBlock);

        // Добавляем обработчик для кнопки удаления телефона
        const removeButton = phoneBlock.querySelector('.remove-phone');
        removeButton.addEventListener('click', function() {
            phoneBlock.remove();
        });
    }

    // Добавляем обработчик для кнопки добавления контакта
    addContactButton.addEventListener('click', addContact);

    // Добавляем обработчики для существующих кнопок добавления телефона
    document.querySelectorAll('.add-phone').forEach(button => {
        button.addEventListener('click', function() {
            addPhone(this.closest('.phones-container'));
        });
    });
}); 