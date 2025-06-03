document.addEventListener('DOMContentLoaded', function() {
    const addSourceBtn = document.querySelector('.header-btn');
    const sidePanel = document.querySelector('.side-panel');
    const closeBtn = document.querySelector('.side-panel-close');

    if (!addSourceBtn || !sidePanel || !closeBtn) {
        console.error('Не найдены необходимые элементы:', {
            addSourceBtn: !!addSourceBtn,
            sidePanel: !!sidePanel,
            closeBtn: !!closeBtn
        });
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