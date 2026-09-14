const eyeOpenSvg = '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#666" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>';
const eyeSlashSvg = '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#666" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>';

function togglePasswordVisibility(fieldId, btn) {
    const input = document.getElementById(fieldId);
    if (!input) return;
    if (input.type === 'password') {
        input.type = 'text';
        if (btn) btn.innerHTML = eyeSlashSvg;
    } else {
        input.type = 'password';
        if (btn) btn.innerHTML = eyeOpenSvg;
    }
}

document.addEventListener('DOMContentLoaded', function() {

    function showFieldError(input, message) {
        clearFieldError(input);
        input.style.borderColor = '#d9534f';
        const errorSpan = document.createElement('span');
        errorSpan.className = 'field-error';
        errorSpan.innerText = message;
        const targetContainer = input.closest('.form-group') || input.parentNode;
        targetContainer.appendChild(errorSpan);
    }

    function showFieldSuccess(input, message) {
        clearFieldError(input);
        input.style.borderColor = '#28a745';
        const successSpan = document.createElement('span');
        successSpan.className = 'field-success';
        successSpan.innerText = message;
        const targetContainer = input.closest('.form-group') || input.parentNode;
        targetContainer.appendChild(successSpan);
    }

    function clearFieldError(input) {
        input.style.borderColor = '#cbd5e1';
        const targetContainer = input.closest('.form-group') || input.parentNode;
        const existing = targetContainer.querySelectorAll('.field-error, .field-success');
        existing.forEach(el => el.remove());
    }

    const regForm = document.getElementById('regForm');
    if (regForm) {
        const nameInput = document.getElementById('name');
        const emailInput = document.getElementById('email');
        const phoneInput = document.getElementById('phone');
        const passwordInput = document.getElementById('password');
        const confirmInput = document.getElementById('confirm_password');

        regForm.addEventListener('submit', function(e) {
            let isValid = true;

            if (!nameInput.value.trim()) {
                showFieldError(nameInput, 'Full Name is required.');
                isValid = false;
            } else {
                clearFieldError(nameInput);
            }

            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailInput.value.trim()) {
                showFieldError(emailInput, 'Email Address is required.');
                isValid = false;
            } else if (!emailRegex.test(emailInput.value.trim())) {
                showFieldError(emailInput, 'Please enter a valid email address.');
                isValid = false;
            }

            const phoneRegex = /^01[3-9]\d{8}$/;
            if (!phoneInput.value.trim()) {
                showFieldError(phoneInput, 'Phone Number is required.');
                isValid = false;
            } else if (!phoneRegex.test(phoneInput.value.trim())) {
                showFieldError(phoneInput, 'Invalid phone number! Must be 11 digits (e.g., 017xxxxxxxx).');
                isValid = false;
            } else {
                clearFieldError(phoneInput);
            }

            if (passwordInput.value.length < 6) {
                showFieldError(passwordInput, 'Password must be at least 6 characters.');
                isValid = false;
            } else {
                clearFieldError(passwordInput);
            }

            if (passwordInput.value !== confirmInput.value) {
                showFieldError(confirmInput, 'Passwords do not match!');
                isValid = false;
            } else if (confirmInput.value.length >= 6) {
                clearFieldError(confirmInput);
            }

            if (!isValid) {
                e.preventDefault();
            }
        });

        if (confirmInput && passwordInput) {
            confirmInput.addEventListener('input', function() {
                if (confirmInput.value && passwordInput.value !== confirmInput.value) {
                    showFieldError(confirmInput, 'Passwords do not match!');
                } else if (confirmInput.value && passwordInput.value === confirmInput.value) {
                    showFieldSuccess(confirmInput, '✓ Passwords match.');
                }
            });
        }
    }

    const loginForm = document.getElementById('loginForm');
    if (loginForm) {
        loginForm.addEventListener('submit', function(e) {
            let isValid = true;
            const emailInput = document.getElementById('email');
            const passwordInput = document.getElementById('password');

            if (!emailInput.value.trim()) {
                showFieldError(emailInput, 'Email is required.');
                isValid = false;
            } else {
                clearFieldError(emailInput);
            }

            if (!passwordInput.value.trim()) {
                showFieldError(passwordInput, 'Password is required.');
                isValid = false;
            } else {
                clearFieldError(passwordInput);
            }

            if (!isValid) {
                e.preventDefault();
            }
        });
    }

    const vitalsForm = document.getElementById('vitalsForm');
    if (vitalsForm) {
        vitalsForm.addEventListener('submit', function(e) {
            let isValid = true;
            const bp = document.getElementById('blood_pressure');
            const bpRegex = /^\d{2,3}\/\d{2,3}$/;

            if (bp && !bpRegex.test(bp.value.trim())) {
                showFieldError(bp, 'Format must be Systolic/Diastolic (e.g., 120/80)');
                isValid = false;
            }

            if (!isValid) {
                e.preventDefault();
            }
        });
    }

    const apptForm = document.getElementById('appointmentForm');
    if (apptForm) {
        const dateInput = document.getElementById('appointment_date');
        if (dateInput) {
            const today = new Date().toISOString().split('T')[0];
            dateInput.min = today;
            apptForm.addEventListener('submit', function(e) {
                if (dateInput.value < today) {
                    alert('Appointment date cannot be in the past.');
                    e.preventDefault();
                }
            });
        }
    }
});
