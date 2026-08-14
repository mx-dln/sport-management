document.addEventListener('click', (event) => {
    const themeToggle = event.target.closest('[data-theme-toggle]');
    if (themeToggle) {
        const isDark = document.documentElement.classList.toggle('dark');
        localStorage.setItem('smis-theme', isDark ? 'dark' : 'light');
        updateThemeToggle();
    }

    const toggle = event.target.closest('[data-sidebar-toggle]');
    if (toggle) {
        document.querySelector('[data-sidebar]')?.classList.toggle('-translate-x-full');
    }

    const printBtn = event.target.closest('[data-print]');
    if (printBtn) {
        window.print();
    }

    const exportBtn = event.target.closest('[data-export-table]');
    if (exportBtn) {
        exportTableToCsv(exportBtn.dataset.exportTable, exportBtn.dataset.filename || 'report.csv');
    }

    const panelToggle = event.target.closest('[data-toggle-panel]');
    if (panelToggle) {
        const panel = document.querySelector(panelToggle.dataset.target);
        if (panel) {
            const hidden = panel.classList.toggle('hidden');
            panelToggle.querySelector('[data-chevron]')?.classList.toggle('rotate-180', !hidden);
            const label = panelToggle.querySelector('[data-toggle-label]');
            if (label) {
                label.textContent = hidden
                    ? (panelToggle.dataset.showLabel || 'Show')
                    : (panelToggle.dataset.hideLabel || 'Hide');
            } else if (!panelToggle.querySelector('[data-chevron]')) {
                panelToggle.textContent = hidden
                    ? (panelToggle.dataset.showLabel || 'Show')
                    : (panelToggle.dataset.hideLabel || 'Hide');
            }
        }
    }

    const modalOpen = event.target.closest('[data-modal-open]');
    if (modalOpen) {
        document.querySelector(modalOpen.dataset.modalOpen)?.classList.remove('hidden');
        document.body.classList.add('overflow-hidden');
    }

    const modalClose = event.target.closest('[data-modal-close]');
    if (modalClose || event.target.matches('[data-modal]')) {
        event.target.closest('[data-modal]')?.classList.add('hidden');
        document.body.classList.remove('overflow-hidden');
    }

    const previewLink = event.target.closest('[data-attachment-preview]');
    if (previewLink) {
        event.preventDefault();
        openAttachmentPreview(previewLink.dataset.attachmentUrl || previewLink.href, previewLink.dataset.attachmentName || previewLink.textContent.trim());
    }

    const closePreview = event.target.closest('[data-preview-close]');
    if (closePreview || event.target.matches('[data-preview-modal]')) {
        closeAttachmentPreview();
    }
});

document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape') closeAttachmentPreview();
    if (event.key === 'Escape') {
        document.querySelectorAll('[data-modal]').forEach((modal) => modal.classList.add('hidden'));
        document.body.classList.remove('overflow-hidden');
    }
});

document.addEventListener('DOMContentLoaded', () => {
    updateThemeToggle();
    initEnhancedTables();
    openRequestedDocumentModal();
});

function updateThemeToggle() {
    const isDark = document.documentElement.classList.contains('dark');
    document.querySelectorAll('[data-theme-toggle]').forEach((button) => {
        button.setAttribute('aria-pressed', isDark ? 'true' : 'false');
        button.querySelector('[data-theme-moon]')?.classList.toggle('hidden', isDark);
        button.querySelector('[data-theme-sun]')?.classList.toggle('hidden', !isDark);
        const label = button.querySelector('[data-theme-label]');
        if (label) label.textContent = isDark ? 'Light' : 'Dark';
    });
}

function showAlert(message, type = 'success') {
    const el = document.createElement('div');
    el.className = `fixed right-4 top-4 z-50 rounded-lg px-4 py-3 text-sm shadow-lg ${type === 'success' ? 'bg-emerald-600 text-white' : 'bg-rose-600 text-white'}`;
    el.textContent = message;
    document.body.appendChild(el);
    setTimeout(() => el.remove(), 3200);
}

function previewExtension(url, name) {
    const source = (name || url).split('?')[0].toLowerCase();
    return source.includes('.') ? source.split('.').pop() : '';
}

function openAttachmentPreview(url, name = 'Attachment') {
    closeAttachmentPreview();

    const ext = previewExtension(url, name);
    const isImage = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp'].includes(ext);
    const isPdf = ext === 'pdf';
    const modal = document.createElement('div');
    modal.className = 'fixed inset-0 z-[80] bg-slate-950/70 p-4 backdrop-blur-sm';
    modal.setAttribute('data-preview-modal', '');

    let body = '';
    if (isImage) {
        body = `<div class="grid min-h-[55vh] place-items-center bg-slate-100 p-4"><img class="max-h-[72vh] max-w-full rounded-lg object-contain shadow-sm" src="${escapeHtml(url)}" alt="${escapeHtml(name)}"></div>`;
    } else if (isPdf) {
        body = `<iframe class="h-[72vh] w-full bg-white" src="${escapeHtml(url)}" title="${escapeHtml(name)}"></iframe>`;
    } else {
        body = `<div class="grid min-h-[45vh] place-items-center bg-slate-50 p-8 text-center">
            <div>
                <p class="text-lg font-bold text-slate-900">Preview not available</p>
                <p class="mt-2 max-w-md text-sm text-slate-500">This file type cannot be previewed in the browser modal. You can still open or download it separately.</p>
                <a class="mt-5 inline-block rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white" href="${escapeHtml(url)}" target="_blank" rel="noopener">Open file</a>
            </div>
        </div>`;
        showAlert('This attachment type cannot be previewed.', 'error');
    }

    modal.innerHTML = `
        <div class="mx-auto flex h-full max-w-5xl items-center">
            <section class="w-full overflow-hidden rounded-2xl bg-white shadow-2xl">
                <header class="flex items-center justify-between gap-3 border-b border-slate-200 px-5 py-4">
                    <div class="min-w-0">
                        <p class="text-xs font-bold uppercase tracking-wide text-slate-400">Attachment Preview</p>
                        <h2 class="truncate text-lg font-bold text-slate-950">${escapeHtml(name)}</h2>
                    </div>
                    <div class="flex items-center gap-2">
                        <a class="rounded-lg border border-slate-300 px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50" href="${escapeHtml(url)}" target="_blank" rel="noopener">Open</a>
                        <button class="rounded-lg bg-slate-900 px-3 py-2 text-sm font-semibold text-white hover:bg-slate-700" type="button" data-preview-close>Close</button>
                    </div>
                </header>
                ${body}
            </section>
        </div>
    `;

    document.body.appendChild(modal);
    document.body.classList.add('overflow-hidden');
}

function closeAttachmentPreview() {
    document.querySelector('[data-preview-modal]')?.remove();
    document.body.classList.remove('overflow-hidden');
}

function escapeHtml(value) {
    return String(value)
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;')
        .replaceAll("'", '&#039;');
}

function exportTableToCsv(target, filename) {
    const table = document.querySelector(target);
    if (!table) {
        showAlert('Report table was not found.', 'error');
        return;
    }

    const rows = Array.from(table.querySelectorAll('tr')).map((row) => {
        return Array.from(row.querySelectorAll('th,td')).map((cell) => {
            const control = cell.querySelector('select, input, textarea');
            const rawValue = control
                ? (control.tagName === 'SELECT'
                    ? control.options[control.selectedIndex]?.textContent || control.value
                    : control.value)
                : cell.innerText;
            const value = String(rawValue).replace(/\s+/g, ' ').trim().replaceAll('"', '""');
            return `"${value}"`;
        }).join(',');
    }).join('\n');

    const blob = new Blob([rows], { type: 'text/csv;charset=utf-8;' });
    const url = URL.createObjectURL(blob);
    const link = document.createElement('a');
    link.href = url;
    link.download = filename.endsWith('.csv') ? filename : `${filename}.csv`;
    document.body.appendChild(link);
    link.click();
    link.remove();
    URL.revokeObjectURL(url);
}

function openRequestedDocumentModal() {
    const params = new URLSearchParams(window.location.search);
    const athleteId = params.get('document_athlete_id');
    if (!athleteId) return;

    const modal = document.querySelector(`#athlete-documents-modal-${CSS.escape(athleteId)}`);
    if (!modal) return;

    modal.classList.remove('hidden');
    document.body.classList.add('overflow-hidden');
}

function initEnhancedTables() {
    document.querySelectorAll('table').forEach((table, index) => {
        if (table.dataset.enhanceTable === 'false' || (table.closest('.print-card') && table.dataset.enhanceTable !== 'true') || table.closest('.biodata-sheet')) {
            return;
        }

        const tbody = table.querySelector('tbody');
        const rows = Array.from(tbody?.querySelectorAll('tr') || []);
        if (!tbody || table.dataset.enhanced === 'true' || (rows.length < 2 && table.dataset.enhanceTable !== 'true')) {
            return;
        }

        table.dataset.enhanced = 'true';
        const container = table.closest('.overflow-x-auto') || table.parentElement;
        const card = container?.parentElement?.classList.contains('rounded-xl') || container?.parentElement?.classList.contains('rounded-2xl')
            ? container.parentElement
            : container;
        const title = card?.querySelector('h2, .font-bold')?.textContent?.trim() || 'Table';

        const toolbar = document.createElement('div');
        toolbar.className = 'data-table-toolbar no-print flex flex-col gap-3 border-b border-slate-200 p-4 sm:flex-row sm:items-center sm:justify-between';
        toolbar.innerHTML = `
            <div>
                <p class="text-sm font-bold text-slate-900">${escapeHtml(title)}</p>
                <p class="text-xs text-slate-500"><span data-table-count>${rows.length}</span> records shown</p>
            </div>
            <div class="flex flex-col gap-2 sm:flex-row sm:items-center">
                <input class="form-input min-w-[220px]" type="search" placeholder="Search table..." data-table-search>
                <select class="form-input sm:w-28" data-table-page-size>
                    <option value="10">10 rows</option>
                    <option value="25">25 rows</option>
                    <option value="50">50 rows</option>
                    <option value="all">All</option>
                </select>
            </div>
        `;

        const pager = document.createElement('div');
        pager.className = 'data-table-pager no-print flex flex-col gap-3 border-t border-slate-200 px-4 py-3 sm:flex-row sm:items-center sm:justify-between';

        if (card && !card.querySelector(':scope > .data-table-toolbar')) {
            card.insertBefore(toolbar, card.firstChild);
            card.appendChild(pager);
        } else {
            container.before(toolbar);
            container.after(pager);
        }

        let page = 1;
        const state = {
            search: toolbar.querySelector('[data-table-search]'),
            pageSize: toolbar.querySelector('[data-table-page-size]'),
            count: toolbar.querySelector('[data-table-count]'),
        };

        const render = () => {
            const query = state.search.value.trim().toLowerCase();
            const filtered = rows.filter((row) => row.textContent.toLowerCase().includes(query));
            const pageSizeValue = state.pageSize.value;
            const pageSize = pageSizeValue === 'all' ? filtered.length || 1 : Number(pageSizeValue);
            const totalPages = Math.max(1, Math.ceil(filtered.length / pageSize));
            page = Math.min(page, totalPages);
            const start = (page - 1) * pageSize;
            const visible = new Set(filtered.slice(start, start + pageSize));

            rows.forEach((row) => row.classList.toggle('hidden', !visible.has(row)));
            state.count.textContent = String(filtered.length);
            pager.innerHTML = `
                <p class="text-sm text-slate-500">${filtered.length ? `Showing ${start + 1} to ${Math.min(start + pageSize, filtered.length)} of ${filtered.length}` : 'No matching records'}</p>
                <div class="flex flex-wrap gap-2">
                    <button class="rounded-lg border border-slate-300 px-3 py-2 text-sm font-semibold ${page <= 1 ? 'opacity-40' : 'hover:bg-slate-50'}" type="button" data-table-prev ${page <= 1 ? 'disabled' : ''}>Previous</button>
                    <span class="rounded-lg bg-slate-100 px-3 py-2 text-sm font-semibold text-slate-600">Page ${page} of ${totalPages}</span>
                    <button class="rounded-lg border border-slate-300 px-3 py-2 text-sm font-semibold ${page >= totalPages ? 'opacity-40' : 'hover:bg-slate-50'}" type="button" data-table-next ${page >= totalPages ? 'disabled' : ''}>Next</button>
                </div>
            `;
        };

        state.search.addEventListener('input', () => {
            page = 1;
            render();
        });
        state.pageSize.addEventListener('change', () => {
            page = 1;
            render();
        });
        pager.addEventListener('click', (event) => {
            if (event.target.closest('[data-table-prev]')) {
                page--;
                render();
            }
            if (event.target.closest('[data-table-next]')) {
                page++;
                render();
            }
        });
        render();
    });
}
