document.addEventListener('submit', (event) => {
    const form = event.target.closest('[data-validate]');
    if (!form) return;

    const invalid = [...form.querySelectorAll('[required]')].find((input) => !input.value.trim());
    if (invalid) {
        event.preventDefault();
        invalid.focus();
        showAlert('Please complete all required fields.', 'error');
    }
});
