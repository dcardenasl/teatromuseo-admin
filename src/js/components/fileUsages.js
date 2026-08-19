const setHidden = (element, hidden) => {
    if (element instanceof HTMLElement) element.hidden = hidden;
};

const setDeleteState = (root, complete, usages) => {
    const form = root.querySelector('[data-file-delete-form]');
    const button = root.querySelector('[data-file-delete-button]');
    if (!(form instanceof HTMLFormElement) || !(button instanceof HTMLButtonElement)) return;

    const safeToDelete = complete && usages.length === 0;
    button.disabled = !safeToDelete;
    button.classList.toggle('opacity-50', !safeToDelete);
    button.classList.toggle('cursor-not-allowed', !safeToDelete);
    if (safeToDelete) {
        form.action = form.dataset.deleteUrl || '';
        button.title = '';
    }
};

const renderUsages = (root, usages) => {
    const list = root.querySelector('[data-file-usages-list]');
    if (!(list instanceof HTMLElement) || list.tagName !== 'UL') return;

    list.replaceChildren();
    usages.forEach((usage) => {
        if (!usage || typeof usage !== 'object') return;

        const item = document.createElement('li');
        item.className = 'py-2 flex items-center justify-between gap-3 text-sm';

        const details = document.createElement('div');
        details.className = 'min-w-0';
        const label = String(usage.label || `${usage.resource || ''} #${usage.resource_id || ''}`).trim();
        const editUrl = String(usage.edit_url || '').trim();
        if (editUrl !== '') {
            const link = document.createElement('a');
            link.href = editUrl;
            link.className = 'font-medium text-brand-600 hover:underline truncate block';
            link.textContent = label;
            details.append(link);
        } else {
            const text = document.createElement('p');
            text.className = 'font-medium text-gray-900 truncate';
            text.textContent = label;
            details.append(text);
        }

        const resource = document.createElement('p');
        resource.className = 'text-xs text-gray-500';
        resource.textContent = `${String(usage.resource || '')} #${String(usage.resource_id || '')}`;
        details.append(resource);

        const role = document.createElement('span');
        role.className = 'text-xs text-gray-400 uppercase shrink-0';
        role.textContent = String(usage.role || '');

        item.append(details, role);
        list.append(item);
    });
};

const showLoadedState = (root, complete, usages) => {
    setHidden(root.querySelector('[data-file-usages-loading]'), true);
    setHidden(root.querySelector('[data-file-usages-error]'), true);
    setHidden(root.querySelector('[data-file-usages-unavailable]'), complete);
    setHidden(root.querySelector('[data-file-in-use-warning]'), usages.length === 0);
    setHidden(root.querySelector('[data-file-usages-empty]'), !complete || usages.length !== 0);
    setHidden(root.querySelector('[data-file-usages-list]'), usages.length === 0);

    const message = root.querySelector('[data-file-usage-message]');
    if (message instanceof HTMLElement) {
        const template = root.dataset.inUseMessage || '';
        message.textContent = template.replace('{0}', String(usages.length));
    }

    renderUsages(root, usages);
    setDeleteState(root, complete, usages);
};

const showFailureState = (root) => {
    setHidden(root.querySelector('[data-file-usages-loading]'), true);
    setHidden(root.querySelector('[data-file-usages-error]'), false);
    setHidden(root.querySelector('[data-file-usages-unavailable]'), false);
    setHidden(root.querySelector('[data-file-in-use-warning]'), true);
    setHidden(root.querySelector('[data-file-usages-empty]'), true);
    setHidden(root.querySelector('[data-file-usages-list]'), true);
    setDeleteState(root, false, []);
};

const loadFileUsages = async (root) => {
    const url = String(root.dataset.usagesUrl || '').trim();
    if (url === '') return;

    try {
        const response = await fetch(url, {
            credentials: 'same-origin',
            headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        });
        const payload = await response.json();
        if (!response.ok || payload?.ok !== true) throw new Error('File usages request failed.');

        const aggregate = payload.data && typeof payload.data === 'object' ? payload.data : {};
        const usages = Array.isArray(aggregate.data) ? aggregate.data.filter((usage) => usage && typeof usage === 'object') : [];
        showLoadedState(root, aggregate.complete === true, usages);
    } catch {
        showFailureState(root);
    }
};

export const bootFileUsages = () => {
    document.querySelectorAll('[data-file-usages]').forEach((root) => {
        if (!(root instanceof HTMLElement) || root.dataset.fileUsagesBooted === '1') return;
        root.dataset.fileUsagesBooted = '1';
        void loadFileUsages(root);
    });
};
