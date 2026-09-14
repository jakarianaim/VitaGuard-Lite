<?php
require_once __DIR__ . '/../config/db_config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
    header("Location: login.php");
    exit();
}

$role = $_SESSION['role'];

if ($role === 'doctor') {
    require_once __DIR__ . '/../Controller/DoctorController.php';
    $doctorController = new DoctorController();
    $data = $doctorController->handlePrescription();

    $patients = $data['patients'];
    $medicines = $data['medicines'];
    $prescriptions = $data['prescriptions'] ?? [];
    $message = $data['message'];
    $error = $data['error'];
    $doctor_name = $data['doctor_name'];

    $med_options = "<option value=''>Select Medicine...</option>";
    foreach ($medicines as $m) {
        $med_options .= "<option value='" . $m['medicine_id'] . "'>" . htmlspecialchars($m['trade_name'] . " (" . $m['generic_name'] . ")") . "</option>";
    }
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Digital Prescription | VitaGuard Lite</title>
        <link rel="stylesheet" href="css/style.css">
        <style>
            .medicine-row { display: flex; gap: 10px; margin-bottom: 12px; align-items: center; }
            .medicine-row select { width: 40%; }
            .medicine-row input { padding: 9px 12px; border: 1px solid #cbd5e1; border-radius: 4px; }
            .medicine-row input.sm { width: 15%; }
            .medicine-row input.md { width: 25%; }
        </style>
    </head>
    <body>

    <div class="app-container">
        <div class="sidebar sidebar-doctor">
            <h2>VitaGuard Lite</h2>
            <p class="role-title">Role: Doctor</p>
            <a href="doctor_dashboard.php?tab=queue">🏠 Consultation Queue</a>
            <a href="doctor_dashboard.php?tab=vitals">🩺 Patient Records</a>
            <a href="prescription.php" style="font-weight: bold; color: #fff;">✍️ Digital Prescription</a>
            <a href="logout.php" class="btn btn-logout">Logout</a>
        </div>

        <div class="main-content">
            <h2>Issue Digital Prescription</h2>
            <p class="subtitle">Create and issue an official prescription for your patient.</p>

            <?php if(!empty($message)) echo "<div class='success-msg'>" . htmlspecialchars($message) . "</div>"; ?>
            <?php if(!empty($error)) echo "<div class='error-msg'>" . htmlspecialchars($error) . "</div>"; ?>
            <div id="prescription_msg"></div>

            <div class="form-container">
                <form action="prescription.php" method="post" id="prescriptionForm">
                    <div class="form-group">
                        <label>1. Select Patient</label>
                        <select name="patient_id" required>
                            <option value="">Select a Patient...</option>
                            <?php foreach ($patients as $p): ?>
                                <option value="<?php echo $p['user_id']; ?>"><?php echo htmlspecialchars($p['name']) . " (ID: #" . $p['user_id'] . ")"; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>2. Prescribe Medicines</label>
                        <div id="medicine-list">
                            <div class="medicine-row">
                                <select name="medicine_id[]" required>
                                    <?php echo $med_options; ?>
                                </select>
                                <input type="text" name="dosage[]" placeholder="Dosage (e.g. 500mg)" class="md" required>
                                <input type="text" name="frequency[]" placeholder="Freq (e.g. 1+0+1)" class="sm" required>
                                <input type="number" name="duration[]" placeholder="Days" class="sm" required min="1">
                            </div>
                        </div>
                        <button type="button" class="btn btn-secondary" onclick="addMedicineRow()">+ Add Another Medicine</button>
                    </div>

                    <div class="form-group">
                        <label>3. General Instructions</label>
                        <textarea name="instructions" rows="4" placeholder="e.g. Take medicines after meals. Drink plenty of fluids."></textarea>
                    </div>

                    <button type="submit" name="issue_prescription" class="btn btn-primary" style="width: 100%; font-size: 16px; padding: 12px;">Submit & Issue Prescription</button>
                </form>
            </div>

            <div class="card" style="margin-top: 30px;">
                <h3>Prescriptions Issued by You</h3>
                <table>
                    <tr>
                        <th class="th-doctor">ID</th>
                        <th class="th-doctor">Patient</th>
                        <th class="th-doctor">Date</th>
                        <th class="th-doctor">Status</th>
                        <th class="th-doctor">Instructions</th>
                        <th class="th-doctor">Action</th>
                    </tr>
                    <?php
                    if (!empty($prescriptions)) {
                        foreach ($prescriptions as $pr) {
                            $pr_id = (int)$pr['prescription_id'];
                            $pt_name = htmlspecialchars($pr['patient_name']);
                            $pr_date = date("d M Y", strtotime($pr['created_at']));
                            $pr_status = htmlspecialchars($pr['status']);
                            $pr_inst = htmlspecialchars($pr['instructions'] ?? 'Standard dosage');

                            echo "<tr>
                                    <td>#$pr_id</td>
                                    <td>$pt_name</td>
                                    <td>$pr_date</td>
                                    <td><strong>$pr_status</strong></td>
                                    <td>$pr_inst</td>
                                    <td>
                                        <form action='prescription.php' method='post' onsubmit=\"return confirm('Delete this prescription?');\">
                                            <input type='hidden' name='prescription_id' value='$pr_id'>
                                            <button type='submit' name='delete_prescription' class='btn btn-danger' style='padding: 5px 10px; font-size: 12px;'>Delete</button>
                                        </form>
                                    </td>
                                  </tr>";
                        }
                    } else {
                        echo "<tr><td colspan='6' style='text-align:center;'>No prescriptions issued yet.</td></tr>";
                    }
                    ?>
                </table>
            </div>
        </div>
    </div>

    <script>
        function addMedicineRow() {
            const container = document.getElementById('medicine-list');
            const rowHTML = `
                <div class="medicine-row">
                    <select name="medicine_id[]" required>
                        <?php echo $med_options; ?>
                    </select>
                    <input type="text" name="dosage[]" placeholder="Dosage (e.g. 500mg)" class="md" required>
                    <input type="text" name="frequency[]" placeholder="Freq (e.g. 1+0+1)" class="sm" required>
                    <input type="number" name="duration[]" placeholder="Days" class="sm" required min="1">
                    <button type="button" class="btn btn-danger" style="padding: 6px 10px;" onclick="this.parentElement.remove()">X</button>
                </div>
            `;
            container.insertAdjacentHTML('beforeend', rowHTML);
        }
    </script>
    <script src="js/doctor.js"></script>
    </body>
    </html>
    <?php
    exit();
}

if ($role === 'patient') {
    require_once __DIR__ . '/../Controller/PatientController.php';
    $patientController = new PatientController();
    $data = $patientController->handleMyPrescriptions();
    $prescriptions = $data['prescriptions'];
    $patient_name = $data['patient_name'];
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>My Prescriptions | VitaGuard Lite</title>
        <link rel="stylesheet" href="css/style.css">
    </head>
    <body>

    <div class="app-container">
        <div class="sidebar sidebar-patient">
            <h2>VitaGuard Lite</h2>
            <p class="role-title">Role: Patient</p>
            <a href="patient_dashboard.php">🏠 Dashboard</a>
            <a href="book_appointment.php">📅 Book Appointment</a>
            <a href="prescription.php" style="font-weight: bold; color: #fff;">📜 My Prescriptions</a>
            <a href="patient_dashboard.php?tab=vitals">❤️ Health Vitals</a>
            <a href="logout.php" class="btn btn-logout">Logout</a>
        </div>

        <div class="main-content">
            <h1>My Prescriptions</h1>
            <p class="subtitle">Official digital prescriptions issued by your healthcare specialists.</p>

            <?php if (!empty($prescriptions)): ?>
                <?php foreach ($prescriptions as $p): 
                    $doc_name = htmlspecialchars($p['doctor_name']);
                    $date = date("d M Y, h:i A", strtotime($p['created_at']));
                    $instructions = htmlspecialchars($p['instructions'] ?? 'Follow doctor dosage.');
                    $status = htmlspecialchars($p['status']);
                    $status_class = $status === 'Completed' ? 'status-completed' : 'status-active';
                ?>
                    <div class="card" style="margin-bottom: 25px;">
                        <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #eee; padding-bottom: 10px;">
                            <h3 style="color: #117a65; margin: 0;">Prescription #<?php echo $p['prescription_id']; ?> - Dr. <?php echo $doc_name; ?></h3>
                            <div>
                                <span style="font-size: 13px; color: #64748b; margin-right: 10px;"><?php echo $date; ?></span>
                                <span class="status-badge <?php echo $status_class; ?>"><?php echo $status; ?></span>
                            </div>
                        </div>

                        <p style="margin-top: 10px;"><strong>Instructions:</strong> <?php echo $instructions; ?></p>

                        <h4 style="margin-top: 15px; color: #334155;">Prescribed Medicines:</h4>
                        <table>
                            <tr>
                                <th class="th-patient">Medicine Name</th>
                                <th class="th-patient">Dosage</th>
                                <th class="th-patient">Frequency</th>
                                <th class="th-patient">Duration</th>
                            </tr>
                            <?php if (!empty($p['items'])): ?>
                                <?php foreach ($p['items'] as $item): ?>
                                    <tr>
                                        <td><strong><?php echo htmlspecialchars($item['trade_name']); ?></strong> <small>(<?php echo htmlspecialchars($item['generic_name']); ?>)</small></td>
                                        <td><?php echo htmlspecialchars($item['dosage']); ?></td>
                                        <td><?php echo htmlspecialchars($item['frequency']); ?></td>
                                        <td><?php echo htmlspecialchars($item['duration_days']); ?> Days</td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td colspan="4" style="text-align:center;">No medicines recorded.</td></tr>
                            <?php endif; ?>
                        </table>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="card"><p style="text-align: center; color: #64748b;">No digital prescriptions found.</p></div>
            <?php endif; ?>
        </div>
    </div>

    </body>
    </html>
    <?php
    exit();
}

header("Location: login.php");
exit();
?>
