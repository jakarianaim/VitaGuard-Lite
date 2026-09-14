<?php
require_once __DIR__ . '/../Controller/PatientController.php';

$patientController = new PatientController();
$data = $patientController->handleBookAppointment();

$doctors = $data['doctors'];
$appointments = $data['appointments'];
$msg = $data['msg'];
$error = $data['error'];
$patient_name = $data['patient_name'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Appointment | VitaGuard Lite</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<div class="app-container">
    <div class="sidebar sidebar-patient">
        <h2>VitaGuard Lite</h2>
        <p class="role-title">Role: Patient</p>
        
        <a href="patient_dashboard.php">🏠 Dashboard</a>
        <a href="book_appointment.php" style="font-weight: bold; color: #fff;">📅 Book Appointment</a>
        <a href="prescription.php">📜 My Prescriptions</a>
        <a href="patient_dashboard.php?tab=vitals">❤️ Health Vitals</a>
        
        <a href="logout.php" class="btn btn-logout">Logout</a>
    </div>

    <div class="main-content">
        <h1>Appointment Management</h1>
        <p class="subtitle">Book an appointment with a specialist and track your requests.</p>

        <?php if (!empty($msg)) echo "<div class='success-msg'>" . htmlspecialchars($msg) . "</div>"; ?>
        <?php if (!empty($error)) echo "<div class='error-msg'>" . htmlspecialchars($error) . "</div>"; ?>
        <div id="appt_msg"></div>

        <div class="card" style="margin-bottom: 30px;">
            <h3>Book New Appointment</h3>
            <form action="book_appointment.php" method="post" id="appointmentForm">
                <div class="form-group">
                    <label>Select Doctor:</label>
                    <select name="doctor_id" id="doctor_select" required>
                        <option value="">Choose a doctor...</option>
                        <?php foreach ($doctors as $d): ?>
                            <option value="<?php echo $d['user_id']; ?>">Dr. <?php echo htmlspecialchars(preg_replace('/^Dr\.?\s+/i', '', $d['name'])); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>Appointment Date:</label>
                    <input type="date" name="appointment_date" id="appointment_date" required min="<?php echo date('Y-m-d'); ?>">
                </div>

                <div class="form-group">
                    <label>Time Slot: <small style="color: #64748b;">(Dynamic Slot Availability)</small></label>
                    <select name="time_slot" id="time_slot_select" required>
                        <option value="">Select Time Slot...</option>
                        <option value="09:00 AM">09:00 AM</option>
                        <option value="10:30 AM">10:30 AM</option>
                        <option value="11:30 AM">11:30 AM</option>
                        <option value="02:00 PM">02:00 PM</option>
                        <option value="04:00 PM">04:00 PM</option>
                        <option value="06:00 PM">06:00 PM</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Reason for Visit:</label>
                    <textarea name="reason" rows="3" placeholder="Briefly describe symptoms or reason..."></textarea>
                </div>

                <button type="submit" name="book_appt" class="btn btn-primary" style="padding: 10px 20px;">Request Appointment</button>
            </form>
        </div>

        <div class="card">
            <h3>My Appointment Requests</h3>
            <table id="appointment_table">
                <tr>
                    <th class="th-patient">Doctor Name</th>
                    <th class="th-patient">Date</th>
                    <th class="th-patient">Time Slot</th>
                    <th class="th-patient">Reason</th>
                    <th class="th-patient">Status</th>
                </tr>
                <?php
                if (!empty($appointments)) {
                    foreach ($appointments as $row) {
                        $doc_name = htmlspecialchars($row['doctor_name']);
                        $date = htmlspecialchars($row['appointment_date']);
                        $time = htmlspecialchars($row['time_slot']);
                        $reason = htmlspecialchars($row['reason'] ?? 'Routine consultation');
                        $status = htmlspecialchars($row['status']);

                        $status_color = "#f39c12";
                        if ($status === 'Approved') $status_color = "#27ae60";
                        if ($status === 'Cancelled') $status_color = "#e74c3c";

                        echo "<tr>
                                <td>Dr. $doc_name</td>
                                <td>$date</td>
                                <td>$time</td>
                                <td>$reason</td>
                                <td><strong style='color: $status_color;'>$status</strong></td>
                              </tr>";
                    }
                } else {
                    echo "<tr><td colspan='5' style='text-align:center;'>No appointment requests found.</td></tr>";
                }
                ?>
            </table>
        </div>

    </div>
</div>

<script src="js/validation.js"></script>
<script src="js/patient.js"></script>
</body>
</html>
