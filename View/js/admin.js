document.addEventListener('DOMContentLoaded', function() {
    const endpointUrl = '../Controller/ApiController.php';

    const noticeForm = document.getElementById('noticeForm');
    const noticeMsg = document.getElementById('notice_msg');
    const noticeTable = document.getElementById('notice_table');

    if (noticeForm) {
        noticeForm.addEventListener('submit', function(e) {
            e.preventDefault();

            const formData = new FormData(noticeForm);
            formData.append('action', 'add_notice');

            const submitBtn = noticeForm.querySelector('button[type="submit"]');
            if (submitBtn) submitBtn.disabled = true;

            fetch(endpointUrl, {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (submitBtn) submitBtn.disabled = false;
                if (noticeMsg) {
                    if (data.status === 'success') {
                        noticeMsg.innerHTML = `<div class="success-msg" style="margin-bottom: 15px;">${data.message}</div>`;
                        noticeForm.reset();

                        if (noticeTable && data.data) {
                            const tbody = noticeTable.querySelector('tbody') || noticeTable;
                            const d = data.data;
                            const newRow = document.createElement('tr');
                            newRow.innerHTML = `
                                <td style="white-space: nowrap;">${d.date}</td>
                                <td><strong>${d.title}</strong><br><small style="color: #64748b;">${d.description}</small></td>
                                <td><span class="role-badge" style="background-color: #34495e;">${d.target}</span></td>
                                <td>
                                    <a href="admin_dashboard.php?tab=notices&edit_notice=${d.notice_id}" class="btn btn-secondary" style="padding: 5px 10px; font-size: 12px; text-decoration: none; margin-right: 5px; display: inline-block;">Edit</a>
                                    <form action="admin_dashboard.php?tab=notices" method="post" onsubmit="return confirm('Delete this notice?');">
                                        <input type="hidden" name="notice_id" value="${d.notice_id}">
                                        <button type="submit" name="delete_notice" class="btn btn-danger" style="padding: 5px 10px; font-size: 12px;">Delete</button>
                                    </form>
                                </td>
                            `;
                            const emptyRow = tbody.querySelector('td[colspan]');
                            if (emptyRow && emptyRow.parentElement) {
                                emptyRow.parentElement.remove();
                            }
                            const firstDataRow = tbody.querySelector('tr:nth-child(2)');
                            if (firstDataRow) {
                                tbody.insertBefore(newRow, firstDataRow);
                            } else {
                                tbody.appendChild(newRow);
                            }
                        }
                    } else {
                        noticeMsg.innerHTML = `<div class="error-msg" style="margin-bottom: 15px;">${data.message}</div>`;
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
