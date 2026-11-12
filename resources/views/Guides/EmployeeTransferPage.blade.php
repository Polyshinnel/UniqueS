@extends('layouts.layout')

@section('title', 'Изменить сотрудника')
@section('header-title')
    <h1 class="header-title">Изменить сотрудника</h1>
@endsection
@section('header-action-btn')
    <a href="{{ route('users.index') }}" class="transfer-back">К списку сотрудников</a>
@endsection

@section('content')
<div class="employee-transfer">
    <nav class="transfer-breadcrumb" aria-label="Навигация">
        <a href="/">Главная</a> / <a href="/guide">Справочники</a> /
        <a href="{{ route('users.index') }}">Сотрудники</a> / Изменить сотрудника
    </nav>
    <p class="transfer-intro">Передайте компании другому менеджеру или региональному представителю. Учитываются компании во всех статусах.</p>
    <div id="transfer-message" role="status" hidden></div>
    <section class="transfer-panel" aria-labelledby="transfer-filters-title">
        <div class="transfer-panel-header"><h2 id="transfer-filters-title">Фильтры</h2></div>
        <form id="transfer-form">
            <div class="transfer-grid">
                <div>
                    <label for="transfer-role">Роль:</label>
                    <select id="transfer-role" required>
                        <option value="">Выберите роль</option>
                        <option value="manager">Менеджер</option>
                        <option value="regional">Региональный представитель</option>
                    </select>
                </div>
                <div>
                    <label for="transfer-source">Текущий сотрудник:</label>
                    <select id="transfer-source" required disabled><option value="">Выберите сотрудника</option></select>
                </div>
                <div>
                    <label for="transfer-region">Регион:</label>
                    <select id="transfer-region" disabled><option value="">Все регионы</option></select>
                </div>
                <div>
                    <label for="transfer-target">Новый сотрудник:</label>
                    <select id="transfer-target" required disabled><option value="">Выберите сотрудника</option></select>
                </div>
            </div>
            <p class="transfer-help">Передаются компании, к которым новый сотрудник имеет доступ хотя бы через один склад. Остальные останутся у текущего сотрудника. Вместе с компаниями передаются связанные товары, а при смене менеджера — также объявления.</p>
            <div class="transfer-actions"><button id="transfer-open" class="transfer-button" type="submit" disabled>Изменить сотрудника</button></div>
        </form>
    </section>
    <div class="transfer-summary" id="transfer-summary" role="status" aria-live="polite">Выберите роль и текущего сотрудника.</div>

    <dialog id="transfer-confirm" aria-labelledby="transfer-confirm-title">
        <h2 id="transfer-confirm-title">Подтвердите замену сотрудника</h2>
        <p id="transfer-confirm-text"></p>
        <p id="transfer-confirm-region"></p>
        <p id="transfer-confirm-remaining"></p>
        <div class="transfer-actions">
            <button type="button" class="transfer-button transfer-cancel" id="transfer-cancel">Отмена</button>
            <button type="button" class="transfer-button" id="transfer-apply">Подтвердить замену</button>
        </div>
    </dialog>
</div>
@endsection

@push('styles')
<style>
    .transfer-back, .transfer-breadcrumb a { color: #133e71; text-decoration: none; }
    .transfer-breadcrumb { font-size: 14px; margin-bottom: 24px; }
    .transfer-intro { margin-bottom: 20px; color: #566575; }
    .transfer-panel, .transfer-summary { background: white; border-radius: 12px; box-shadow: 0 4px 16px #133e7110; overflow: hidden; }
    .transfer-panel-header { padding: 20px 24px; background: linear-gradient(180deg, #f8fafc, #e9eef2); color: #133e71; }
    .transfer-panel-header h2 { font-size: 18px; font-weight: 600; }
    #transfer-form { padding: 24px; }
    .transfer-grid { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 20px; }
    .transfer-grid label { display: block; margin-bottom: 8px; color: #133e71; font-weight: 500; }
    .transfer-grid select { width: 100%; min-height: 42px; padding: 10px; border: 1px solid #ccd5df; border-radius: 5px; background: white; font: inherit; font-size: 14px; }
    .transfer-grid select:disabled { background: #f1f4f7; color: #6c757d; }
    .transfer-grid select:focus-visible, .transfer-button:focus-visible { outline: 3px solid #77a9df; outline-offset: 2px; }
    .transfer-help { margin-top: 22px; font-size: 14px; color: #566575; }
    .transfer-actions { display: flex; justify-content: flex-end; flex-wrap: wrap; gap: 12px; margin-top: 24px; }
    .transfer-button { padding: 11px 20px; border: 0; border-radius: 6px; background: #133e71; color: white; font: inherit; font-weight: 600; cursor: pointer; }
    .transfer-button:hover { background: #1c5ba4; }
    .transfer-button:disabled { opacity: .5; cursor: default; }
    .transfer-cancel { background: #647180; }
    .transfer-summary { margin-top: 20px; padding: 20px 24px; color: #133e71; }
    #transfer-message { margin-bottom: 20px; padding: 16px 20px; border-radius: 8px; background: #eaf5ed; color: #206333; }
    #transfer-message.transfer-error { background: #fbecec; color: #a12626; }
    #transfer-confirm { margin: auto; padding: 28px; max-width: 580px; width: calc(100% - 40px); border: 0; border-radius: 12px; box-shadow: 0 20px 60px #0003; color: #333; }
    #transfer-confirm::backdrop { background: #10233888; }
    #transfer-confirm h2 { color: #133e71; font-size: 21px; margin-bottom: 18px; }
    #transfer-confirm p { margin-top: 12px; }
    @media (max-width: 1100px) { .transfer-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
    @media (max-width: 650px) { .transfer-grid { grid-template-columns: 1fr; } #transfer-form { padding: 16px; } }
</style>
@endpush

@push('scripts')
<script>
(() => {
    const users = @json($users);
    const urls = @json(['options' => route('users.transfer.options'), 'preview' => route('users.transfer.preview'), 'store' => route('users.transfer.store')]);
    const role = document.getElementById('transfer-role');
    const source = document.getElementById('transfer-source');
    const region = document.getElementById('transfer-region');
    const target = document.getElementById('transfer-target');
    const summary = document.getElementById('transfer-summary');
    const message = document.getElementById('transfer-message');
    const open = document.getElementById('transfer-open');
    const dialog = document.getElementById('transfer-confirm');
    const apply = document.getElementById('transfer-apply');
    const cancel = document.getElementById('transfer-cancel');
    let preview = null;
    let version = 0;
    let busy = false;

    function resetSelect(select, placeholder) {
        select.replaceChildren(new Option(placeholder, ''));
        select.disabled = true;
    }
    function invalidate() {
        version++;
        preview = null;
        open.disabled = true;
        if (dialog.open) dialog.close();
    }
    function showMessage(text, error = false) {
        message.textContent = text;
        message.classList.toggle('transfer-error', error);
        message.hidden = false;
    }
    async function request(url, data, method = 'POST') {
        const response = await fetch(method === 'GET' ? `${url}?${new URLSearchParams(data)}` : url, {
            method,
            headers: { 'Accept': 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
            ...(method === 'GET' ? {} : { body: JSON.stringify(data) }),
        });
        if (response.redirected) throw new Error('Сессия завершена или доступ запрещён. Обновите страницу.');
        const result = await response.json();
        if (!response.ok) throw new Error(result.errors ? Object.values(result.errors).flat().join(' ') : (result.message || 'Не удалось выполнить запрос.'));
        return result;
    }
    function parameters() {
        return { role: role.value, source_id: source.value, region_id: region.value, target_id: target.value };
    }
    async function loadOptions(resetRegion = false) {
        invalidate();
        const currentVersion = version;
        resetSelect(target, 'Выберите сотрудника');
        if (resetRegion) resetSelect(region, 'Все регионы');
        if (!source.value) {
            summary.textContent = 'Выберите текущего сотрудника.';
            return;
        }
        summary.textContent = 'Подсчитываем компании…';
        try {
            const result = await request(urls.options, parameters(), 'GET');
            if (version !== currentVersion) return;
            if (resetRegion) result.regions.forEach(item => region.add(new Option(item.name, item.id)));
            region.disabled = false;
            result.targets.forEach(item => target.add(new Option(`${item.name} — доступно компаний: ${item.count}`, item.id)));
            target.disabled = !result.targets.length;
            summary.textContent = `Найдено компаний: ${result.total}. ` + (result.targets.length ? 'Выберите нового сотрудника для подсчёта передачи.' : 'Нет сотрудников с доступом к складам этих компаний.');
        } catch (error) {
            if (version === currentVersion) {
                summary.textContent = 'Не удалось загрузить данные. Измените фильтр, чтобы повторить запрос.';
                showMessage(error.message, true);
            }
        }
    }
    async function loadPreview() {
        invalidate();
        const currentVersion = version;
        if (!target.value) {
            summary.textContent = 'Выберите нового сотрудника.';
            return;
        }
        summary.textContent = 'Подсчитываем доступные компании…';
        try {
            const result = await request(urls.preview, parameters());
            if (version !== currentVersion) return;
            preview = result;
            summary.textContent = `Найдено компаний: ${result.total}. Доступно для передачи: ${result.eligible}. Останутся у текущего сотрудника: ${result.remaining}.`;
            open.disabled = !result.eligible;
        } catch (error) {
            if (version === currentVersion) {
                summary.textContent = 'Подсчёт не выполнен. Выберите сотрудника заново.';
                showMessage(error.message, true);
            }
        }
    }
    role.addEventListener('change', () => {
        invalidate();
        message.hidden = true;
        resetSelect(source, 'Выберите сотрудника');
        resetSelect(region, 'Все регионы');
        resetSelect(target, 'Выберите сотрудника');
        users.filter(user => user.role === role.value).forEach(user => source.add(new Option(`${user.name}${user.active ? '' : ' (неактивен)'}`, user.id)));
        source.disabled = !role.value;
        summary.textContent = 'Выберите роль и текущего сотрудника.';
    });
    source.addEventListener('change', () => loadOptions(true));
    region.addEventListener('change', () => loadOptions());
    target.addEventListener('change', loadPreview);
    document.getElementById('transfer-form').addEventListener('submit', event => {
        event.preventDefault();
        if (!preview?.eligible || busy) return;
        document.getElementById('transfer-confirm-text').textContent = `Заменить сотрудника «${preview.source}» на «${preview.target}» в ${preview.eligible} компаниях? Роль: ${role.selectedOptions[0].textContent}.`;
        document.getElementById('transfer-confirm-region').textContent = `Регион: ${region.selectedOptions[0].textContent}.`;
        document.getElementById('transfer-confirm-remaining').textContent = `Останутся у текущего сотрудника: ${preview.remaining}.`;
        dialog.showModal();
    });
    cancel.addEventListener('click', () => dialog.close());
    dialog.addEventListener('cancel', event => { if (busy) event.preventDefault(); });
    apply.addEventListener('click', async () => {
        if (!preview || busy) return;
        busy = true;
        apply.disabled = cancel.disabled = true;
        apply.textContent = 'Выполняется передача…';
        [role, source, region, target].forEach(select => select.disabled = true);
        try {
            const result = await request(urls.store, { token: preview.token });
            showMessage(result.message);
        } catch (error) {
            showMessage(error.message, true);
        } finally {
            busy = false;
            apply.disabled = cancel.disabled = false;
            apply.textContent = 'Подтвердить замену';
            dialog.close();
            role.disabled = source.disabled = false;
            await loadOptions();
        }
    });
})();
</script>
@endpush
