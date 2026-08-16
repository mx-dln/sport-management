function setButtonLoading(button, loading, text = 'Saving...') {
    if (!button) return;
    if (loading) {
        button.dataset.smisOriginal = button.innerHTML;
        button.disabled = true;
        button.classList.add('smis-btn-loading');
        button.innerHTML = `${loadingSpinner()}<span>${text}</span>`;
    } else {
        button.disabled = false;
        button.classList.remove('smis-btn-loading');
        if (button.dataset.smisOriginal !== undefined) {
            button.innerHTML = button.dataset.smisOriginal;
            delete button.dataset.smisOriginal;
        }
    }
}

document.addEventListener('submit', async (event) => {
    const form = event.target.closest('[data-ajax-form]');
    if (!form) return;
    event.preventDefault();
    if (form.dataset.confirm && !confirm(form.dataset.confirm)) {
        return;
    }

    const button = form.querySelector('button[type="submit"], button:not([type]), input[type="submit"]');
    setButtonLoading(button, true, button?.dataset.loadingText || 'Saving...');
    startTopProgress();

    let willNavigate = false;

    try {
        const response = await fetch(form.getAttribute('action'), {
            method: form.method || 'POST',
            body: new FormData(form),
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        });
        const text = await response.text();
        let data;
        try {
            data = JSON.parse(text);
        } catch (parseError) {
            throw new Error(text.trim() || 'Request failed. Please try again.');
        }
        showAlert(data.message || 'Done', data.ok ? 'success' : 'error');
        if (data.reload) { willNavigate = true; setTimeout(() => location.reload(), 700); }
        if (data.redirect) { willNavigate = true; setTimeout(() => location.href = data.redirect, 700); }
        form.dispatchEvent(new CustomEvent('ajax:done', { detail: data }));
    } catch (error) {
        showAlert(error.message || 'Request failed. Please try again.', 'error');
    } finally {
        finishTopProgress();
        if (!willNavigate) setButtonLoading(button, false);
    }
});

document.addEventListener('change', async (event) => {
    const field = event.target.closest('[data-ajax-status]');
    if (!field) return;
    const formData = new FormData();
    formData.append('action', field.dataset.action || 'status');
    formData.append('id', field.dataset.id);
    formData.append('status', field.value);
    startTopProgress();
    try {
        const response = await fetch(field.dataset.url, { method: 'POST', body: formData });
        const data = await response.json();
        showAlert(data.message || 'Status updated', data.ok ? 'success' : 'error');
    } finally {
        finishTopProgress();
    }
});
