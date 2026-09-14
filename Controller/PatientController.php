<?php
require_once __DIR__ . '/AuthController.php';
require_once __DIR__ . '/../Model/UserModel.php';
require_once __DIR__ . '/../Model/AppointmentModel.php';
require_once __DIR__ . '/../Model/PrescriptionModel.php';
require_once __DIR__ . '/../Model/MedicineModel.php';
require_once __DIR__ . '/../Model/HealthRecordModel.php';
require_once __DIR__ . '/../Model/NoticeModel.php';

class PatientController {
    private $userModel;
    private $appointmentModel;
    private $prescriptionModel;
    private $medicineModel;
    private $healthRecordModel;
    private $noticeModel;

    public function __construct() {
        AuthController::checkAccess('patient');
        $this->userModel = new UserModel();
        $this->appointmentModel = new AppointmentModel();
        $this->prescriptionModel = new PrescriptionModel();
        $this->medicineModel = new MedicineModel();
        $this->healthRecordModel = new HealthRecordModel();
        $this->noticeModel = new NoticeModel();
    }

    public function handleDashboard() {
        $patient_id = (int)($_SESSION['user_id'] ?? 0);
        $notices = $this->noticeModel->getForRole('patient', 3);
        $vitals = $this->healthRecordModel->getByPatient($patient_id);

        return [
            'notices' => $notices,
            'recent_vitals' => array_slice($vitals, 0, 3),
            'patient_name' => $_SESSION['name'] ?? 'Patient'
        ];
    }

    public function handleBookAppointment() {
        $patient_id = (int)($_SESSION['user_id'] ?? 0);
        $msg = "";
        $error = "";

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['book_appt'])) {
            $doctor_id = (int)($_POST['doctor_id'] ?? 0);
            $appointment_date = trim($_POST['appointment_date'] ?? '');
            $time_slot = trim($_POST['time_slot'] ?? '');
            $reason = trim($_POST['reason'] ?? '');

            if (empty($doctor_id) || empty($appointment_date) || empty($time_slot)) {
                $error = "Please fill in all required fields.";
            } elseif ($appointment_date < date('Y-m-d')) {
                $error = "Appointment date cannot be in the past.";
            } else {
                if ($this->appointmentModel->createAppointment($patient_id, $doctor_id, $appointment_date, $time_slot, $reason)) {
                    $msg = "Appointment requested successfully! Waiting for doctor's approval.";
                } else {
                    $error = "Failed to book appointment. Please try again.";
                }
            }
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cancel_appt'])) {
            $appointment_id = (int)($_POST['appointment_id'] ?? 0);
            if ($appointment_id > 0 && $this->appointmentModel->cancelAppointmentByPatient($appointment_id, $patient_id)) {
                $msg = "Appointment request cancelled successfully.";
            } else {
                $error = "Unable to cancel this appointment.";
            }
        }

        $doctors = $this->userModel->getUsersByRole('doctor');
        $appointments = $this->appointmentModel->getAppointmentsByPatient($patient_id);

        return [
            'doctors' => $doctors,
            'appointments' => $appointments,
            'msg' => $msg,
            'error' => $error,
            'patient_name' => $_SESSION['name'] ?? 'Patient'
        ];
    }

    public function handleVitals() {
        $patient_id = (int)($_SESSION['user_id'] ?? 0);
        $message = "";

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['log_vitals'])) {
            $blood_pressure = trim($_POST['blood_pressure'] ?? '');
            $blood_sugar = trim($_POST['blood_sugar'] ?? '');
            $pulse_rate = trim($_POST['pulse_rate'] ?? '');
            $temperature = trim($_POST['temperature'] ?? '');

            if (empty($blood_pressure) || empty($blood_sugar) || empty($pulse_rate) || empty($temperature)) {
                $message = "<span class='error'>All vitals fields are required!</span>";
            } elseif (!preg_match('/^\d{2,3}\/\d{2,3}$/', $blood_pressure)) {
                $message = "<span class='error'>Invalid blood pressure format! (Example: 120/80)</span>";
            } else {
                if ($this->healthRecordModel->addRecord($patient_id, $blood_pressure, $blood_sugar, $pulse_rate, $temperature)) {
                    $message = "<span class='success'>Health vitals logged successfully!</span>";
                } else {
                    $message = "<span class='error'>Failed to log vitals.</span>";
                }
            }
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_vitals'])) {
            $record_id = (int)($_POST['record_id'] ?? 0);
            $blood_pressure = trim($_POST['blood_pressure'] ?? '');
            $blood_sugar = trim($_POST['blood_sugar'] ?? '');
            $pulse_rate = trim($_POST['pulse_rate'] ?? '');
            $temperature = trim($_POST['temperature'] ?? '');

            if ($record_id > 0 && !empty($blood_pressure) && !empty($blood_sugar) && !empty($pulse_rate) && !empty($temperature)) {
                if ($this->healthRecordModel->updateRecord($record_id, $patient_id, $blood_pressure, $blood_sugar, $pulse_rate, $temperature)) {
                    $message = "<span class='success'>Health vitals updated successfully!</span>";
                } else {
                    $message = "<span class='error'>Failed to update vitals record.</span>";
                }
            } else {
                $message = "<span class='error'>All vitals fields are required!</span>";
            }
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_record'])) {
            $record_id = (int)($_POST['record_id'] ?? 0);
            if ($record_id > 0 && $this->healthRecordModel->deleteRecord($record_id, $patient_id)) {
                $message = "<span class='success'>Record deleted successfully!</span>";
            }
        }

        $edit_record = null;
        if (isset($_GET['edit_vital']) && (int)$_GET['edit_vital'] > 0) {
            $edit_record = $this->healthRecordModel->getRecordById((int)$_GET['edit_vital'], $patient_id);
        }

        $vitals = $this->healthRecordModel->getByPatient($patient_id);

        return [
            'vitals' => $vitals,
            'edit_record' => $edit_record,
            'message' => $message,
            'patient_name' => $_SESSION['name'] ?? 'Patient'
        ];
    }

    public function handleMyPrescriptions() {
        $patient_id = (int)($_SESSION['user_id'] ?? 0);
        $prescriptions = $this->prescriptionModel->getPrescriptionsByPatient($patient_id);

        foreach ($prescriptions as &$p) {
            $p['items'] = $this->prescriptionModel->getPrescriptionItems((int)$p['prescription_id']);
        }

        return [
            'prescriptions' => $prescriptions,
            'patient_name' => $_SESSION['name'] ?? 'Patient'
        ];
    }
}
?>
