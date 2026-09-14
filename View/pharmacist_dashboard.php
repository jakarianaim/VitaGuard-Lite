<?php
require_once __DIR__ . '/../Controller/PharmacistController.php';

$pharmacistController = new PharmacistController();
$data = $pharmacistController->handleDashboard();
$dispenseData = $pharmacistController->handleDispense();

$low_stock_count = $data['low_stock_count'];
$notices = $data['notices'];
$pharmacist_name = $data['pharmacist_name'];

$prescription_data = $dispenseData['prescription_data'];
$prescription_items = $dispenseData['prescription_items'];
$message = $dispenseData['message'];

$tab = $_GET['tab'] ?? 'overview';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pharmacist Dashboard | VitaGuard Lite</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<div class="app-container">
    <div class="sidebar sidebar-pharmacist">
        <h2>VitaGuard Lite</h2>
        <p class="role-title">Role: Pharmacist</p>
        
        <a href="pharmacist_dashboard.php?tab=overview" style="<?php echo $tab === 'overview' ? 'font-weight:bold; color:#fff;' : ''; ?>">🏠 Dashboard</a>
        <a href="inventory.php">📦 Medicine Inventory</a>
        <a href="pharmacist_dashboard.php?tab=dispense" style="<?php echo $tab === 'dispense' ? 'font-weight:bold; color:#fff;' : ''; ?>">✔️ Prescription Verify</a>
        
        <a href="logout.php" class="btn btn-logout">Logout</a>
    </div>

    <div class="main-content">
        <h1>Welcome, <?php echo htmlspecialchars($pharmacist_name); ?>!</h1>
        <p class="subtitle">Pharmacy inventory overview and prescription fulfillment.</p>

        <?php if(!empty($message)) echo "<div style='margin-bottom:15px;'>$message</div>"; ?>

        <?php if ($tab === 'overview'): ?>
            <div class="card-container">
                <div class="card">
                    <h3>Medicine Inventory</h3>
                    <p>Add new medicines, update stock levels, and check unit prices.</p>
                    <a href="inventory.php" class="btn btn-primary">Manage Inventory</a>
                </div>

                <div class="card">
                    <h3>Prescription Verification</h3>
                    <p>Search active prescriptions and dispense medicines to patients.</p>
                    <a href="pharmacist_dashboard.php?tab=dispense" class="btn btn-primary">Verify & Dispense</a>
                </div>

                <div class="card">
                    <h3>Inventory Status</h3>
                    <p>Low Stock Items: <strong><?php echo $low_stock_count; ?></strong></p>
                    <?php if ($low_stock_count > 0): ?>
                        <span class="role-badge" style="background-color: #e74c3c;">Attention Needed</span>
                    <?php else: ?>
                        <span class="role-badge" style="background-color: #27ae60;">Stock Healthy</span>
                    <?php endif; ?>
                </div>
            </div>

            <div class="notice-box">
                <h3>📢 System Announcements</h3>
                <?php
                if (!empty($notices)) {
                    foreach ($notices as $notice) {
                        $n_title = htmlspecialchars($notice['title']);
                        $n_desc = htmlspecialchars($notice['description']);
                        $n_date = date("d M Y", strtotime($notice['created_at']));
                        echo "<div class='notice-item'>
                                <strong>$n_title</strong> <small style='color: #b58900;'>($n_date)</small>
                                <p style='margin-top: 4px; color: #555; font-size: 14px;'>$n_desc</p>
                              </div>";
                    }
                } else {
                    echo "<p style='color: #856404; font-size: 14px;'>No new announcements at this time.</p>";
                }
                ?>
            </div>

        <?php elseif ($tab === 'dispense'): ?>
            <h2>Prescription Verification & Fulfillment</h2>

            <div class="form-container" style="max-width: 550px;">
                <form action="pharmacist_dashboard.php" method="get">
                    <input type="hidden" name="tab" value="dispense">
                    <div style="display: flex; gap: 10px;">
                        <input type="number" name="search_id" placeholder="Enter Prescription ID (e.g. 1)" required value="<?php echo isset($_GET['search_id']) ? htmlspecialchars($_GET['search_id']) : ''; ?>" style="flex: 1; padding: 10px; border: 1px solid #ccc; border-radius: 4px;">
                        <input type="submit" value="Search" class="btn btn-primary">
                    </div>
                </form>
            </div>

            <?php if ($prescription_data): ?>
                <div class="card" style="margin-top: 20px;">
                    <h3>Prescription Details (ID: #<?php echo $prescription_data['prescription_id']; ?>)</h3>
                    
                    <p>
                        <strong>Patient Name:</strong> <?php echo htmlspecialchars($prescription_data['patient_name'] ?? 'N/A'); ?> | 
                        <strong>Doctor:</strong> Dr. <?php echo htmlspecialchars($prescription_data['doctor_name'] ?? 'N/A'); ?>
                    </p>
                    <p>
                        <strong>Status:</strong> 
                        <?php 
                        $status = $prescription_data['status'];
                        $badge_class = $status === 'Completed' ? 'status-completed' : 'status-active';
                        echo "<span class='status-badge $badge_class'>$status</span>";
                        ?>
                    </p>
                    <p><strong>Instructions:</strong> <?php echo htmlspecialchars($prescription_data['instructions']); ?></p>

                    <h4 style="margin-top: 15px;">Prescribed Medicines:</h4>
                    <table>
                        <tr>
                            <th class="th-pharmacist">Item Name (Trade)</th>
                            <th class="th-pharmacist">Generic Name</th>
                            <th class="th-pharmacist">Dosage</th>
                            <th class="th-pharmacist">Frequency</th>
                            <th class="th-pharmacist">Current Stock</th>
                        </tr>
                        <?php
                        $can_dispense = true;
                        if (!empty($prescription_items)) {
                            foreach ($prescription_items as $item) {
                                $trade = htmlspecialchars($item['trade_name']);
                                $generic = htmlspecialchars($item['generic_name']);
                                $dosage = htmlspecialchars($item['dosage']);
                                $frequency = htmlspecialchars($item['frequency']);
                                $stock = (int)$item['stock_quantity'];

                                $stockDisplay = $stock;
                                if ($stock <= 0) {
                                    $stockDisplay = "<span style='color:red; font-weight:bold;'>Out of Stock</span>";
                                    $can_dispense = false;
                                }

                                echo "<tr>
                                        <td><strong>$trade</strong></td>
                                        <td>$generic</td>
                                        <td>$dosage</td>
                                        <td>$frequency</td>
                                        <td>$stockDisplay</td>
                                      </tr>";
                            }
                        } else {
                            echo "<tr><td colspan='5' style='text-align:center;'>No medicines listed.</td></tr>";
                            $can_dispense = false;
                        }
                        ?>
                    </table>

                    <br>
                    <?php if ($prescription_data['status'] !== 'Completed' && $can_dispense): ?>
                        <form action="pharmacist_dashboard.php?tab=dispense" method="post" onsubmit="return confirm('Verify and dispense this prescription? Medicine stock will be updated.');">
                            <input type="hidden" name="prescription_id" value="<?php echo $prescription_data['prescription_id']; ?>">
                            <button type="submit" name="dispense_prescription" class="btn btn-success" style="padding: 10px 20px;">Verify & Dispense (Update Stock)</button>
                        </form>
                    <?php elseif ($prescription_data['status'] === 'Completed'): ?>
                        <p class="success-msg">✓ This prescription has already been dispensed.</p>
                    <?php else: ?>
                        <p class="error-msg">⚠ Cannot dispense: One or more medicines are currently out of stock.</p>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

        <?php endif; ?>

    </div>
</div>

<script src="js/pharmacist.js"></script>
</body>
</html>
