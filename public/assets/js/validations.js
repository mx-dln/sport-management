document.addEventListener('input', (event) => {
    const input = event.target;
    if (!input || !input.matches('input')) return;

    if (input.matches('[data-phone-11]') || input.type === 'tel' || /contact|phone/i.test(input.name || '')) {
        const cleaned = input.value.replace(/\D/g, '').slice(0, 11);
        if (input.value !== cleaned) {
            input.value = cleaned;
        }
    }
});

document.addEventListener('submit', (event) => {
    const form = event.target.closest('[data-validate]');
    if (!form) return;

    const invalidRequired = [...form.querySelectorAll('[required]')].find((input) => !input.value.trim());
    if (invalidRequired) {
        event.preventDefault();
        invalidRequired.focus();
        if (typeof showAlert === 'function') {
            showAlert('Please complete all required fields (marked with *).', 'error');
        } else {
            alert('Please complete all required fields (marked with *).');
        }
        return;
    }

    const phoneInputs = [...form.querySelectorAll('[data-phone-11], input[type="tel"], input[name*="contact"], input[name*="phone"]')];
    for (const phoneInput of phoneInputs) {
        const val = phoneInput.value.trim();
        if (val) {
            if (!/^09\d{9}$/.test(val)) {
                event.preventDefault();
                phoneInput.focus();
                const msg = 'Phone number must be exactly 11 digits starting with 09 (e.g., 09171234567).';
                if (typeof showAlert === 'function') {
                    showAlert(msg, 'error');
                } else {
                    alert(msg);
                }
                return;
            }
        }
    }
});
