document.addEventListener('DOMContentLoaded', function() {
    const endpointUrl = '../Controller/ApiController.php';

    const tradeNameInput = document.getElementById('trade_name');
    const productStatus = document.getElementById('product_status');

    if (tradeNameInput) {
        let timer;
        tradeNameInput.addEventListener('input', function() {
            clearTimeout(timer);
            const name = tradeNameInput.value.trim();

            if (!name) {
                if (productStatus) productStatus.innerHTML = '';
                tradeNameInput.style.borderColor = '#cbd5e1';
                return;
            }

            timer = setTimeout(() => {
                fetch(`${endpointUrl}?action=check_medicine&trade_name=${encodeURIComponent(name)}`)
                    .then(res => res.json())
                    .then(data => {
                        if (productStatus) {
                            if (data.exists) {
                                productStatus.innerHTML = `<span style="color: #d9534f; font-weight: 600;">✗ ${data.message}</span>`;
                                tradeNameInput.style.borderColor = '#d9534f';
                            } else {
                                productStatus.innerHTML = `<span style="color: #28a745; font-weight: 600;">✓ ${data.message}</span>`;
                                tradeNameInput.style.borderColor = '#28a745';
                            }
                        }
                    })
                    .catch(err => console.error(err));
            }, 300);
        });
    }

    const inventoryForm = document.getElementById('inventoryForm');
    const inventoryMsg = document.getElementById('inventory_msg');
    const inventoryTable = document.getElementById('inventory_table');

    if (inventoryForm) {
        inventoryForm.addEventListener('submit', function(e) {
            e.preventDefault();

            const formData = new FormData(inventoryForm);
            formData.append('action', 'add_medicine');

            const submitBtn = inventoryForm.querySelector('button[type="submit"]');
            if (submitBtn) submitBtn.disabled = true;

            fetch(endpointUrl, {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (submitBtn) submitBtn.disabled = false;
                if (inventoryMsg) {
                    if (data.status === 'success') {
                        inventoryMsg.innerHTML = `<div class="success-msg" style="margin-bottom: 15px;">${data.message}</div>`;
                        inventoryForm.reset();
                        if (productStatus) productStatus.innerHTML = '';
                        if (tradeNameInput) tradeNameInput.style.borderColor = '#cbd5e1';

                        if (inventoryTable && data.data) {
                            const tbody = inventoryTable.querySelector('tbody');
                            if (tbody) {
                                const d = data.data;
                                const stockDisplay = (d.stock_quantity < 20) ? `<span style='color:#e74c3c; font-weight:bold;'>${d.stock_quantity}</span>` : d.stock_quantity;
                                const newRow = document.createElement('tr');
                                newRow.id = `med-row-${d.medicine_id}`;
                                newRow.innerHTML = `
                                    <td>#${d.medicine_id}</td>
                                    <td><strong>${d.trade_name}</strong></td>
                                    <td>${d.generic_name}</td>
                                    <td>${d.category}</td>
                                    <td>${d.unit_price}</td>
                                    <td id="stock-qty-${d.medicine_id}">${stockDisplay}</td>
                                    <td>${d.expiry_date}</td>
                                    <td>
                                        <a href="inventory.php?edit_id=${d.medicine_id}" class="btn btn-secondary" style="padding: 5px 10px; font-size: 12px; text-decoration: none; margin-right: 5px; display: inline-block;">Edit</a>
                                        <form action="inventory.php" method="post" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this medicine?');">
                                            <input type="hidden" name="medicine_id" value="${d.medicine_id}">
                                            <button type="submit" name="delete_medicine" class="btn btn-danger" style="padding: 5px 10px; font-size: 12px;">Delete</button>
                                        </form>
                                    </td>
                                `;
                                const emptyRow = tbody.querySelector('td[colspan]');
                                if (emptyRow && emptyRow.parentElement) {
                                    emptyRow.parentElement.remove();
                                }
                                tbody.insertBefore(newRow, tbody.firstChild);
                            }
                        }
                    } else {
                        inventoryMsg.innerHTML = `<div class="error-msg" style="margin-bottom: 15px;">${data.message}</div>`;
                    }
                }
            })
            .catch(err => {
                if (submitBtn) submitBtn.disabled = false;
                console.error(err);
            });
        });
    }

    const inventorySearch = document.getElementById('inventory_live_search');
    if (inventorySearch) {
        inventorySearch.addEventListener('input', function() {
            const term = this.value.toLowerCase().trim();
            const rows = document.querySelectorAll('#inventory_table tbody tr');
            rows.forEach(row => {
                const text = row.innerText.toLowerCase();
                row.style.display = text.includes(term) ? '' : 'none';
            });
        });
    }
});
