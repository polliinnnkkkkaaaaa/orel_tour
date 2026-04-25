document.addEventListener('DOMContentLoaded', () => {
    const registerForm = document.querySelector('[data-validate="register"]');
    if (registerForm) {
        registerForm.addEventListener('submit', (e) => {
            const password = registerForm.querySelector('[name="password"]').value.trim();
            const password2 = registerForm.querySelector('[name="password_confirm"]').value.trim();
            if (password.length < 6) {
                alert('Пароль должен содержать минимум 6 символов.');
                e.preventDefault();
                return;
            }
            if (password !== password2) {
                alert('Пароли не совпадают.');
                e.preventDefault();
            }
        });

        const loginInput = registerForm.querySelector('[name="login"]');
        const loginHint = document.getElementById('login-check-result');
        if (loginInput && loginHint) {
            loginInput.addEventListener('blur', async () => {
                const login = loginInput.value.trim();
                if (!login) return;
                const response = await fetch('check_login.php?login=' + encodeURIComponent(login));
                const data = await response.json();
                loginHint.textContent = data.message;
                loginHint.className = data.available ? 'text-success' : 'text-danger';
            });
        }
    }

    document.querySelectorAll('[data-validate="login"], [data-validate="checkout"], [data-validate="profile"]').forEach((form) => {
        form.addEventListener('submit', (e) => {
            const requiredFields = form.querySelectorAll('[required]');
            for (const field of requiredFields) {
                if (!field.value.trim()) {
                    alert('Пожалуйста, заполните все обязательные поля.');
                    field.focus();
                    e.preventDefault();
                    return;
                }
            }
        });
    });
});
