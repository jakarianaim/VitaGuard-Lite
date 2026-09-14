document.addEventListener('DOMContentLoaded', function() {
    const endpointUrl = '../Controller/ApiController.php';

    const prescriptionForm = document.getElementById('prescriptionForm');
    const prescriptionMsg = document.getElementById('prescription_msg');

    if (prescriptionForm) {
        prescriptionForm.addEventListener('submit', function(e) {
            e.preventDefault();

            const formData = new FormData(prescriptionForm);
            formData.append('action', 'add_prescription');

            const submitBtn = prescriptionForm.querySelector('button[type="submit"]');
            if (submitBtn) submitBtn.disabled = true;

            fetch(endpointUrl, {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (submitBtn) submitBtn.disabled = false;
                if (prescriptionMsg) {
                    if (data.status === 'success') {
                        prescriptionMsg.innerHTML = `<div class="success-msg" style="margin-bottom: 15px;">${data.message}</div>`;
                        prescriptionForm.reset();
                    } else {
                        prescriptionMsg.innerHTML = `<div class="error-msg" style="margin-bottom: 15px;">${data.message}</div>`;
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
