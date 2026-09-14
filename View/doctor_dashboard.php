<?php
require_once __DIR__ . '/../Controller/DoctorController.php';

$doctorController = new DoctorController();
$data = $doctorController->handleDashboard();

$appointments = $data['appointments'];
$notices = $data['notices'];
$patient_info = $data['patient_info'];
$vitals = $data['vitals'];
$search_error = $data['search_error'];
$message = $data['message'];
$doctor_name = $data['doctor_name'];

$tab = $_GET['tab'] ?? 'queue';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Doctor Dashboard | VitaGuard Lite</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<div class="app-container">
    <div class="sidebar sidebar-doctor">
        <h2>VitaGuard Lite</h2>
        <p class="role-title">Role: Doctor</p>
        
        <a href="doctor_dashboard.php?tab=queue" style="<?php echo $tab === 'queue' ? 'font-weight:bold; color:#fff;' : ''; ?>">🏠 Consultation Queue</a>
        <a href="doctor_dashboard.php?tab=vitals" style="<?php echo $tab === 'vitals' ? 'font-weight:bold; color:#fff;' : ''; ?>">🩺 Patient Records</a>
        <a href="prescription.php">✍️ Digital Prescription</a>
        
        <a href="logout.php" class="btn btn-logout">Logout</a>
    </div>

    <div class="main-content">
        <h1>Welcome, Dr. <?php echo htmlspecialchars(preg_replace('/^Dr\.?\s+/i', '', $doctor_name)); ?>!</h1>
        <p class="subtitle">Manage your appointments, patient vitals, and digital prescriptions.</p>

        <?php if(!empty($message)) echo "<div style='margin-bottom:15px;'>$message</div>"; ?>

        <?php if ($tab === 'queue'): ?>
            <div class="card">
                <h3>Consultation Queue (Appointments)</h3>
                <table>
                    <tr>
                        <th class="th-doctor">Patient Name</th>
                        <th class="th-doctor">Contact</th>
                        <th class="th-doctor">Date</th>
                        <th class="th-doctor">Time Slot</th>
                        <th class="th-doctor">Reason</th>
                        <th class="th-doctor">Status</th>
                        <th class="th-doctor">Actions</th>
                    </tr>
                    <?php
                    if (!empty($appointments)) {
                        foreach ($appointments as $row) {
                            $id = $row['appointment_id'];
                            $name = htmlspecialchars($row['patient_name']);
                            $phone = htmlspecialchars($row['patient_phone'] ?? 'N/A');
                            $date = htmlspecialchars($row['appointment_date']);
                            $time = htmlspecialchars($row['time_slot']);
                            $reason = htmlspecialchars($row['reason'] ?? '');
                            $status = htmlspecialchars($row['status']);

                            echo "<tr>
                                    <td><strong>$name</strong></td>
                                    <td>$phone</td>
                                    <td>$date</td>
                                    <td>$time</td>
                                    <td>$reason</td>
                                    <td><strong>$status</strong></td>
                                    <td>";
                            
                            if ($status == 'Pending') {
                                echo "<form action='doctor_dashboard.php?tab=queue' method='post' style='display:inline;'>
                                        <input type='hidden' name='appointment_id' value='$id'>
                                        <input type='hidden' name='status' value='Approved'>
                                        <button type='submit' name='update_status' class='btn btn-success' style='padding: 5px 10px; font-size: 12px; margin-right: 5px;'>Approve</button>
                                      </form>";
                            }

                            echo "<form action='doctor_dashboard.php?tab=queue' method='post' style='display:inline;' onsubmit=\"return confirm('Delete this appointment?');\">
                                    <input type='hidden' name='appointment_id' value='$id'>
                                    <button type='submit' name='delete_appointment' class='btn btn-danger' style='padding: 5px 10px; font-size: 12px;'>Delete</button>
                                  </form>";
                            echo "</td>
                                  </tr>";
                        }
                    } else {
                        echo "<tr><td colspan='7' style='text-align:center;'>No upcoming appointments.</td></tr>";
                    }
                    ?>
                </table>
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

        <?php elseif ($tab === 'vitals'): ?>
            <h2>Review Patient Medical History</h2>
            
            <div class="form-container" style="max-width: 550px;">
                <form action="doctor_dashboard.php" method="get">
                    <input type="hidden" name="tab" value="vitals">
                    <div style="display: flex; gap: 10px;">
                        <input type="number" name="patient_id" placeholder="Enter Patient User ID" required value="<?php echo isset($_GET['patient_id']) ? htmlspecialchars($_GET['patient_id']) : ''; ?>" style="flex: 1; padding: 10px; border: 1px solid #ccc; border-radius: 4px;">
                        <input type="submit" name="search_patient" value="Search Records" class="btn btn-primary">
                    </div>
                </form>
                <?php if(!empty($search_error)) echo "<p style='color: red; margin-top: 10px;'>$search_error</p>"; ?>
            </div>

            <?php if ($patient_info): ?>
                <div class="card" style="margin-bottom: 20px; background: #ecf0f1; border-left: 5px solid #2c3e50;">
                    <h3 style="color: #2c3e50;">Patient Profile</h3>
                    <p><strong>Name:</strong> <?php echo htmlspecialchars($patient_info['name']); ?></p>
                    <p><strong>Email:</strong> <?php echo htmlspecialchars($patient_info['email']); ?> | <strong>Phone:</strong> <?php echo htmlspecialchars($patient_info['phone']); ?></p>
                </div>

                <h3>Health Vitals History</h3>
                <table>
                    <tr>
                        <th class="th-doctor">Date & Time</th>
                        <th class="th-doctor">Blood Pressure</th>
                        <th class="th-doctor">Blood Sugar (mg/dL)</th>
                        <th class="th-doctor">Pulse Rate (bpm)</th>
                        <th class="th-doctor">Temperature (°F)</th>
                    </tr>
                    <?php
                    if (!empty($vitals)) {
                        foreach ($vitals as $row) {
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
                                  </tr>";
                        }
                    } else {
                        echo "<tr><td colspan='5' style='text-align:center;'>No health vitals logged by this patient yet.</td></tr>";
                    }
                    ?>
                </table>
            <?php endif; ?>

        <?php endif; ?>

    </div>
</div>

<script src="js/doctor.js"></script>
</body>
</html>
