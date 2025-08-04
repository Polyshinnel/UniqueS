document.addEventListener('DOMContentLoaded', function() {
    const addSourceBtn = document.querySelector('.header-btn');
    const sidePanel = document.querySelector('.side-panel');
    const closeBtn = document.querySelector('.side-panel-close');

    // Если элементы не найдены, просто завершаем выполнение без ошибок
    if (!addSourceBtn || !sidePanel || !closeBtn) {
        // Тихо завершаем выполнение, так как эти элементы могут отсутствовать на некоторых страницах
        return;
    }

    addSourceBtn.addEventListener('click', function(e) {
        e.preventDefault();
        console.log('Клик по кнопке добавления');
        sidePanel.classList.add('active');
    });

    closeBtn.addEventListener('click', function(e) {
        e.preventDefault();
        console.log('Клик по кнопке закрытия');
        sidePanel.classList.remove('active');
    });
}); 