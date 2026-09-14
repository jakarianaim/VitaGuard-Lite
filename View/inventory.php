<?php
require_once __DIR__ . '/../Controller/PharmacistController.php';

$pharmacistController = new PharmacistController();
$data = $pharmacistController->handleInventory();

$medicines = $data['medicines'];
$edit_medicine = $data['edit_medicine'] ?? null;
$message = $data['message'];
$pharmacist_name = $data['pharmacist_name'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Medicine Inventory | VitaGuard Lite</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<div class="app-container">
    <div class="sidebar sidebar-pharmacist">
        <h2>VitaGuard Lite</h2>
        <p class="role-title">Role: Pharmacist</p>
        
        <a href="pharmacist_dashboard.php">🏠 Dashboard</a>
        <a href="inventory.php" style="font-weight: bold; color: #fff;">📦 Medicine Inventory</a>
        <a href="pharmacist_dashboard.php?tab=dispense">✔️ Prescription Verify</a>
        
        <a href="logout.php" class="btn btn-logout">Logout</a>
    </div>

    <div class="main-content">
        <h2>Medicine Inventory Management</h2>
        <p class="subtitle">Monitor inventory stock levels, add new drugs, and manage catalogue.</p>

        <?php if(!empty($message)) echo "<div style='margin-bottom:15px;'>$message</div>"; ?>
        <div id="inventory_msg"></div>

        <?php if ($edit_medicine): ?>
        <div class="form-container" style="border: 2px solid #3498db; background-color: #f8fafc;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                <h3>Edit Medicine: <?php echo htmlspecialchars($edit_medicine['trade_name']); ?></h3>
                <a href="inventory.php" class="btn btn-secondary" style="padding: 5px 12px; font-size: 13px;">Cancel Edit</a>
            </div>
            <form action="inventory.php" method="post">
                <input type="hidden" name="medicine_id" value="<?php echo $edit_medicine['medicine_id']; ?>">
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px;">
                    <div class="form-group">
                        <label>Trade Name</label>
                        <input type="text" name="trade_name" value="<?php echo htmlspecialchars($edit_medicine['trade_name']); ?>" required>
                    </div>

                    <div class="form-group">
                        <label>Generic Name</label>
                        <input type="text" name="generic_name" value="<?php echo htmlspecialchars($edit_medicine['generic_name']); ?>" required>
                    </div>

                    <div class="form-group">
                        <label>Category</label>
                        <select name="category" required>
                            <option value="Tablet" <?php if ($edit_medicine['category'] === 'Tablet') echo 'selected'; ?>>Tablet</option>
                            <option value="Syrup" <?php if ($edit_medicine['category'] === 'Syrup') echo 'selected'; ?>>Syrup</option>
                            <option value="Injection" <?php if ($edit_medicine['category'] === 'Injection') echo 'selected'; ?>>Injection</option>
                            <option value="Capsule" <?php if ($edit_medicine['category'] === 'Capsule') echo 'selected'; ?>>Capsule</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Unit Price (৳)</label>
                        <input type="number" step="0.01" name="unit_price" value="<?php echo htmlspecialchars($edit_medicine['unit_price']); ?>" required min="0.01">
                    </div>

                    <div class="form-group">
                        <label>Stock Quantity</label>
                        <input type="number" name="stock_quantity" value="<?php echo htmlspecialchars($edit_medicine['stock_quantity']); ?>" required min="0">
                    </div>

                    <div class="form-group">
                        <label>Expiry Date</label>
                        <input type="date" name="expiry_date" value="<?php echo htmlspecialchars($edit_medicine['expiry_date']); ?>" required>
                    </div>
                </div>

                <button type="submit" name="update_medicine" class="btn btn-primary" style="margin-top: 5px;">Save Changes</button>
            </form>
        </div>
        <?php else: ?>
        <div class="form-container">
            <h3>Add New Medicine</h3>
            <form action="inventory.php" method="post" id="inventoryForm">
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px;">
                    <div class="form-group">
                        <label>Trade Name</label>
                        <input type="text" name="trade_name" id="trade_name" placeholder="e.g. Napa" required>
                        <div id="product_status" style="margin-top: 4px; font-size: 12px;"></div>
                    </div>

                    <div class="form-group">
                        <label>Generic Name</label>
                        <input type="text" name="generic_name" placeholder="e.g. Paracetamol" required>
                    </div>

                    <div class="form-group">
                        <label>Category</label>
                        <select name="category" required>
                            <option value="">Select Category</option>
                            <option value="Tablet">Tablet</option>
                            <option value="Syrup">Syrup</option>
                            <option value="Injection">Injection</option>
                            <option value="Capsule">Capsule</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Unit Price (৳)</label>
                        <input type="number" step="0.01" name="unit_price" placeholder="e.g. 3.50" required min="0.01">
                    </div>

                    <div class="form-group">
                        <label>Stock Quantity</label>
                        <input type="number" name="stock_quantity" placeholder="e.g. 100" required min="0">
                    </div>

                    <div class="form-group">
                        <label>Expiry Date</label>
                        <input type="date" name="expiry_date" required min="<?php echo date('Y-m-d'); ?>">
                    </div>
                </div>

                <button type="submit" name="add_medicine" class="btn btn-primary" style="margin-top: 5px;">Add to Inventory</button>
            </form>
        </div>
        <?php endif; ?>

        <div style="margin-bottom: 15px; max-width: 450px; position: relative;">
            <label style="font-weight: 600; font-size: 13px; color: #334155;">🔍 Live Search Medicine:</label>
            <input type="text" id="inventory_live_search" placeholder="Type trade or generic name to filter..." style="width: 100%; padding: 9px 12px; border: 1px solid #cbd5e1; border-radius: 6px; margin-top: 4px;">
            <div id="search_results_container" style="position: absolute; width: 100%; z-index: 100; display: none; margin-top: 2px;"></div>
        </div>

        <h3>Current Stock List</h3>
        <table id="inventory_table">
            <thead>
                <tr>
                    <th class="th-pharmacist">ID</th>
                    <th class="th-pharmacist">Trade Name</th>
                    <th class="th-pharmacist">Generic Name</th>
                    <th class="th-pharmacist">Category</th>
                    <th class="th-pharmacist">Price (৳)</th>
                    <th class="th-pharmacist">Stock</th>
                    <th class="th-pharmacist">Expiry Date</th>
                    <th class="th-pharmacist">Action</th>
                </tr>
            </thead>
            <tbody>
            <?php
            if (!empty($medicines)) {
                foreach ($medicines as $med) {
                    $id = $med['medicine_id'];
                    $trade = htmlspecialchars($med['trade_name']);
                    $generic = htmlspecialchars($med['generic_name']);
                    $cat = htmlspecialchars($med['category']);
                    $price = number_format((float)$med['unit_price'], 2);
                    $stock = (int)$med['stock_quantity'];
                    $expiry = htmlspecialchars($med['expiry_date']);

                    $stockDisplay = ($stock < 20) ? "<span style='color:#e74c3c; font-weight:bold;'>$stock</span>" : $stock;

                    echo "<tr id='med-row-$id'>
                            <td>#$id</td>
                            <td><strong>$trade</strong></td>
                            <td>$generic</td>
                            <td>$cat</td>
                            <td>$price</td>
                            <td id='stock-qty-$id'>$stockDisplay</td>
                            <td>$expiry</td>
                            <td>
                                <a href='inventory.php?edit_id=$id' class='btn btn-secondary' style='padding: 5px 10px; font-size: 12px; text-decoration: none; margin-right: 5px; display: inline-block;'>Edit</a>
                                <form action='inventory.php' method='post' style='display:inline;' onsubmit=\"return confirm('Are you sure you want to delete this medicine?');\">
                                    <input type='hidden' name='medicine_id' value='$id'>
                                    <button type='submit' name='delete_medicine' class='btn btn-danger' style='padding: 5px 10px; font-size: 12px;'>Delete</button>
                                </form>
                            </td>
                          </tr>";
                }
            } else {
                echo "<tr><td colspan='8' style='text-align:center;'>No medicines found in inventory.</td></tr>";
            }
            ?>
            </tbody>
        </table>
    </div>
</div>

<script src="js/validation.js"></script>
<script src="js/pharmacist.js"></script>
</body>
</html>
