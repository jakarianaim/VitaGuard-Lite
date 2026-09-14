document.addEventListener('DOMContentLoaded', function() {
    const endpointUrl = '../Controller/ApiController.php';

    const docSelect = document.getElementById('doctor_select');
    const dateInput = document.getElementById('appointment_date');
    const slotSelect = document.getElementById('time_slot_select');

    function checkDoctorSlots() {
        if (!docSelect || !dateInput || !slotSelect) return;
        const doctorId = docSelect.value;
        const date = dateInput.value;

        if (doctorId && date) {
            fetch(`${endpointUrl}?action=get_slots&doctor_id=${doctorId}&date=${date}`)
                .then(res => res.json())
                .then(data => {
                    if (data.status === 'success') {
                        const booked = data.booked || [];
                        Array.from(slotSelect.options).forEach(opt => {
                            if (opt.value && booked.includes(opt.value)) {
                                opt.disabled = true;
                                opt.text = `${opt.value} (Booked)`;
                            } else if (opt.value) {
                                opt.disabled = false;
                                opt.text = opt.value;
                            }
                        });
                    }
                })
                .catch(err => console.error(err));
        }
    }

    if (docSelect && dateInput) {
        docSelect.addEventListener('change', checkDoctorSlots);
        dateInput.addEventListener('change', checkDoctorSlots);
    }

    const appointmentForm = document.getElementById('appointmentForm');
    const apptMsg = document.getElementById('appt_msg');
    const apptTable = document.getElementById('appointment_table');

    if (appointmentForm) {
        appointmentForm.addEventListener('submit', function(e) {
            e.preventDefault();

            const formData = new FormData(appointmentForm);
            formData.append('action', 'book_appointment');

            const submitBtn = appointmentForm.querySelector('button[type="submit"]');
            if (submitBtn) submitBtn.disabled = true;

            fetch(endpointUrl, {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (submitBtn) submitBtn.disabled = false;
                if (apptMsg) {
                    if (data.status === 'success') {
                        apptMsg.innerHTML = `<div class="success-msg" style="margin-bottom: 15px;">${data.message}</div>`;
                        appointmentForm.reset();

                        if (apptTable && data.data) {
                            const tbody = apptTable.querySelector('tbody') || apptTable;
                            const d = data.data;
                            const newRow = document.createElement('tr');
                            newRow.innerHTML = `
                                <td>Dr. ${d.doctor_name}</td>
                                <td>${d.appointment_date}</td>
                                <td>${d.time_slot}</td>
                                <td>${d.reason}</td>
                                <td><strong style="color: #f39c12;">${d.status}</strong></td>
                            `;
                            const firstDataRow = tbody.querySelector('tr:nth-child(2)');
                            if (firstDataRow) {
                                tbody.insertBefore(newRow, firstDataRow);
                            } else {
                                tbody.appendChild(newRow);
                            }
                        }
                    } else {
                        apptMsg.innerHTML = `<div class="error-msg" style="margin-bottom: 15px;">${data.message}</div>`;
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
