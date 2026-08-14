document.addEventListener('submit', async (event) => {
    const form = event.target.closest('[data-ajax-form]');
    if (!form) return;
    event.preventDefault();
    if (form.dataset.confirm && !confirm(form.dataset.confirm)) {
        return;
    }

    const button = form.querySelector('[type="submit"]');
    const original = button?.textContent;
    if (button) {
        button.disabled = true;
        button.textContent = 'Saving...';
    }

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
        if (data.reload) setTimeout(() => location.reload(), 700);
        if (data.redirect) setTimeout(() => location.href = data.redirect, 700);
        form.dispatchEvent(new CustomEvent('ajax:done', { detail: data }));
    } catch (error) {
        showAlert(error.message || 'Request failed. Please try again.', 'error');
    } finally {
        if (button) {
            button.disabled = false;
            button.textContent = original;
        }
    }
});

document.addEventListener('change', async (event) => {
    const field = event.target.closest('[data-ajax-status]');
    if (!field) return;
    const formData = new FormData();
    formData.append('action', field.dataset.action || 'status');
    formData.append('id', field.dataset.id);
    formData.append('status', field.value);
    const response = await fetch(field.dataset.url, { method: 'POST', body: formData });
    const data = await response.json();
    showAlert(data.message || 'Status updated', data.ok ? 'success' : 'error');
});
