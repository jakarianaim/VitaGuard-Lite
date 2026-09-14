<?php
require_once __DIR__ . '/../Controller/PatientController.php';

$patientController = new PatientController();
$tab = $_GET['tab'] ?? 'overview';

if ($tab === 'vitals') {
    $vitalsData = $patientController->handleVitals();
    $vitals = $vitalsData['vitals'];
    $edit_record = $vitalsData['edit_record'] ?? null;
    $message = $vitalsData['message'];
    $patient_name = $vitalsData['patient_name'];
} else {
    $dashboardData = $patientController->handleDashboard();
    $notices = $dashboardData['notices'];
    $recent_vitals = $dashboardData['recent_vitals'];
    $patient_name = $dashboardData['patient_name'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Patient Dashboard | VitaGuard Lite</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<div class="app-container">
    <div class="sidebar sidebar-patient">
        <h2>VitaGuard Lite</h2>
        <p class="role-title">Role: Patient</p>
        
        <a href="patient_dashboard.php?tab=overview" style="<?php echo $tab === 'overview' ? 'font-weight:bold; color:#fff;' : ''; ?>">🏠 Dashboard</a>
        <a href="book_appointment.php">📅 Book Appointment</a>
        <a href="prescription.php">📜 My Prescriptions</a>
        <a href="patient_dashboard.php?tab=vitals" style="<?php echo $tab === 'vitals' ? 'font-weight:bold; color:#fff;' : ''; ?>">❤️ Health Vitals</a>
        
        <a href="logout.php" class="btn btn-logout">Logout</a>
    </div>

    <div class="main-content">
        <h1>Patient Dashboard</h1>
        <p class="subtitle">Welcome, <?php echo htmlspecialchars($patient_name); ?>. Manage your daily healthcare here.</p>

        <?php if(!empty($message)) echo "<div style='margin-bottom:15px;'>$message</div>"; ?>

        <?php if ($tab === 'overview'): ?>
            <div class="card-container">
                <div class="card">
                    <h3>Prescriptions</h3>
                    <p style="margin: 10px 0; color: #666; font-size: 14px;">View official prescriptions issued by your consulting doctor.</p>
                    <a href="prescription.php" class="btn btn-primary" style="display:inline-block;">View Prescriptions</a>
                </div>
                <div class="card">
                    <h3>Appointments</h3>
                    <p style="margin: 10px 0; color: #666; font-size: 14px;">Schedule appointments and check current booking requests.</p>
                    <a href="book_appointment.php" class="btn btn-primary" style="display:inline-block;">Book Appointment</a>
                </div>
                <div class="card">
                    <h3>Health Status</h3>
                    <p style="margin: 10px 0; color: #666; font-size: 14px;">Log vitals and keep accurate records for your physicians.</p>
                    <a href="patient_dashboard.php?tab=vitals" class="btn btn-primary" style="display:inline-block;">Update Vitals</a>
                </div>
            </div>

            <div class="notice-box">
                <h3>📢 Announcements & Health Tips</h3>
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

        <?php elseif ($tab === 'vitals'): ?>
            <h2>Health Vitals Log</h2>

            <?php if ($edit_record): ?>
            <div class="form-container" style="max-width: 600px; border: 2px solid #3498db; background-color: #f8fafc;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                    <h3>Edit Health Vitals Record</h3>
                    <a href="patient_dashboard.php?tab=vitals" class="btn btn-secondary" style="padding: 5px 12px; font-size: 13px;">Cancel Edit</a>
                </div>
                <form action="patient_dashboard.php?tab=vitals" method="post">
                    <input type="hidden" name="record_id" value="<?php echo $edit_record['record_id']; ?>">
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                        <div class="form-group">
                            <label>Blood Pressure</label>
                            <input type="text" name="blood_pressure" value="<?php echo htmlspecialchars($edit_record['blood_pressure']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label>Blood Sugar (mg/dL)</label>
                            <input type="number" step="0.1" name="blood_sugar" value="<?php echo htmlspecialchars($edit_record['blood_sugar']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label>Pulse Rate (bpm)</label>
                            <input type="number" step="0.1" name="pulse_rate" value="<?php echo htmlspecialchars($edit_record['pulse_rate']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label>Temperature (°F)</label>
                            <input type="number" step="0.1" name="temperature" value="<?php echo htmlspecialchars($edit_record['temperature']); ?>" required>
                        </div>
                    </div>
                    <button type="submit" name="update_vitals" class="btn btn-primary" style="margin-top: 10px;">Update Record</button>
                </form>
            </div>
            <?php else: ?>
            <div class="form-container" style="max-width: 600px;">
                <h3>Add New Record</h3>
                <form action="patient_dashboard.php?tab=vitals" method="post" id="vitalsForm">
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                        <div class="form-group">
                            <label>Blood Pressure</label>
                            <input type="text" name="blood_pressure" id="blood_pressure" placeholder="e.g. 120/80" required>
                        </div>
                        <div class="form-group">
                            <label>Blood Sugar (mg/dL)</label>
                            <input type="number" step="0.1" name="blood_sugar" placeholder="e.g. 95.5" required>
                        </div>
                        <div class="form-group">
                            <label>Pulse Rate (bpm)</label>
                            <input type="number" step="0.1" name="pulse_rate" placeholder="e.g. 72" required>
                        </div>
                        <div class="form-group">
                            <label>Temperature (°F)</label>
                            <input type="number" step="0.1" name="temperature" placeholder="e.g. 98.6" required>
                        </div>
                    </div>
                    <button type="submit" name="log_vitals" class="btn btn-primary" style="margin-top: 10px;">Save Record</button>
                </form>
            </div>
            <?php endif; ?>

            <h3>Vitals History</h3>
            <table>
                <tr>
                    <th class="th-patient">Date & Time</th>
                    <th class="th-patient">Blood Pressure</th>
                    <th class="th-patient">Blood Sugar (mg/dL)</th>
                    <th class="th-patient">Pulse Rate (bpm)</th>
                    <th class="th-patient">Temperature (°F)</th>
                    <th class="th-patient">Action</th>
                </tr>
                <?php
                if (!empty($vitals)) {
                    foreach ($vitals as $row) {
                        $id = $row['record_id'];
                        $datetime = date("d M Y, h:i A", strtotime($row['recorded_at']));
                        $bp = htmlspecialchars($row['blood_pressure']);
                        $bs = htmlspecialchars($row['blood_sugar']);
                        $pr = htmlspecialchars($row['pulse_rate']);
                        $temp = htmlspecialchars($row['temperature']);

                        echo "<tr>
                                <td>$datetime</td>
                                <td>$bp</td>
                                <td>$bs</td>
                                <td>$pr</td>
                                <td>$temp</td>
                                <td>
                                    <a href='patient_dashboard.php?tab=vitals&edit_vital=$id' class='btn btn-secondary' style='padding: 5px 10px; font-size: 12px; text-decoration: none; margin-right: 5px; display: inline-block;'>Edit</a>
                                    <form action='patient_dashboard.php?tab=vitals' method='post' style='display:inline;' onsubmit=\"return confirm('Delete this vitals record?');\">
                                        <input type='hidden' name='record_id' value='$id'>
                                        <button type='submit' name='delete_record' class='btn btn-danger' style='padding: 5px 10px; font-size: 12px;'>Delete</button>
                                    </form>
                                </td>
                              </tr>";
                    }
                } else {
                    echo "<tr><td colspan='6' style='text-align:center;'>No health vitals logged yet.</td></tr>";
                }
                ?>
            </table>
        <?php endif; ?>

    </div>
</div>

<script src="js/validation.js"></script>
<script src="js/patient.js"></script>
</body>
</html>
