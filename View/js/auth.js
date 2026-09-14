document.addEventListener('DOMContentLoaded', function() {
    const endpointUrl = '../Controller/ApiController.php';

    const emailInput = document.getElementById('email');
    const emailStatus = document.getElementById('email_status');

    if (emailInput && document.getElementById('regForm')) {
        let debounceTimer;

        emailInput.addEventListener('input', function() {
            clearTimeout(debounceTimer);
            const email = emailInput.value.trim();

            if (!email) {
                if (emailStatus) emailStatus.innerHTML = '';
                emailInput.style.borderColor = '#cbd5e1';
                return;
            }

            debounceTimer = setTimeout(() => {
                fetch(`${endpointUrl}?action=check_email&email=${encodeURIComponent(email)}`)
                    .then(response => response.json())
                    .then(data => {
                        if (emailStatus) {
                            if (data.status === 'success') {
                                if (data.exists) {
                                    emailStatus.innerHTML = `<span style="color: #d9534f; font-size: 12px; font-weight: 600;">✗ ${data.message}</span>`;
                                    emailInput.style.borderColor = '#d9534f';
                                } else {
                                    emailStatus.innerHTML = `<span style="color: #28a745; font-size: 12px; font-weight: 600;">✓ ${data.message}</span>`;
                                    emailInput.style.borderColor = '#28a745';
                                }
                            } else if (data.status === 'invalid') {
                                emailStatus.innerHTML = `<span style="color: #f39c12; font-size: 12px;">${data.message}</span>`;
                            }
                        }
                    })
                    .catch(err => console.error(err));
            }, 350);
        });
    }

    const regForm = document.getElementById('regForm');
    const regMsg = document.getElementById('reg_msg');

    if (regForm) {
        regForm.addEventListener('submit', function(e) {
            e.preventDefault();

            const pwd = document.getElementById('password');
            const cpwd = document.getElementById('confirm_password');
            if (pwd && cpwd && pwd.value !== cpwd.value) {
                if (regMsg) regMsg.innerHTML = '<div class="error-msg">Passwords do not match.</div>';
                return;
            }

            const formData = new FormData(regForm);
            formData.append('action', 'register');

            const submitBtn = regForm.querySelector('button[type="submit"]');
            if (submitBtn) submitBtn.disabled = true;

            fetch(endpointUrl, {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (submitBtn) submitBtn.disabled = false;
                if (regMsg) {
                    if (data.status === 'success') {
                        regMsg.innerHTML = `<div class="success-msg">${data.message}</div>`;
                        regForm.reset();
                        if (emailStatus) emailStatus.innerHTML = '';
                        setTimeout(() => {
                            window.location.href = 'login.php';
                        }, 1200);
                    } else {
                        regMsg.innerHTML = `<div class="error-msg">${data.message}</div>`;
                    }
                }
            })
            .catch(err => {
                if (submitBtn) submitBtn.disabled = false;
                console.error(err);
            });
        });
    }
});
