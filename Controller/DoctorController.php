<?php
require_once __DIR__ . '/AuthController.php';
require_once __DIR__ . '/../Model/UserModel.php';
require_once __DIR__ . '/../Model/AppointmentModel.php';
require_once __DIR__ . '/../Model/PrescriptionModel.php';
require_once __DIR__ . '/../Model/MedicineModel.php';
require_once __DIR__ . '/../Model/HealthRecordModel.php';
require_once __DIR__ . '/../Model/NoticeModel.php';

class DoctorController {
    private $userModel;
    private $appointmentModel;
    private $prescriptionModel;
    private $medicineModel;
    private $healthRecordModel;
    private $noticeModel;

    public function __construct() {
        AuthController::checkAccess('doctor');
        $this->userModel = new UserModel();
        $this->appointmentModel = new AppointmentModel();
        $this->prescriptionModel = new PrescriptionModel();
        $this->medicineModel = new MedicineModel();
        $this->healthRecordModel = new HealthRecordModel();
        $this->noticeModel = new NoticeModel();
    }

    public function handleDashboard() {
        $doctor_id = (int)($_SESSION['user_id'] ?? 0);
        $message = "";

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
            $appt_id = (int)($_POST['appointment_id'] ?? 0);
            $status = trim($_POST['status'] ?? '');
            if (in_array($status, ['Approved', 'Cancelled']) && $appt_id > 0) {
                if ($this->appointmentModel->updateStatus($appt_id, $doctor_id, $status)) {
                    $message = "<span class='success'>Appointment status updated to '$status'!</span>";
                }
            }
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_appointment'])) {
            $appt_id = (int)($_POST['appointment_id'] ?? 0);
            if ($appt_id > 0 && $this->appointmentModel->deleteAppointment($appt_id, $doctor_id)) {
                $message = "<span class='success'>Appointment record deleted successfully!</span>";
            } else {
                $message = "<span class='error'>Failed to delete appointment.</span>";
            }
        }

        $patient_info = null;
        $vitals = [];
        $search_error = "";
        if (isset($_GET['search_patient']) && !empty($_GET['patient_id'])) {
            $search_id = (int)$_GET['patient_id'];
            $user = $this->userModel->getUserById($search_id);
            if ($user && $user['role'] === 'patient') {
                $patient_info = $user;
                $vitals = $this->healthRecordModel->getByPatient($search_id);
            } else {
                $search_error = "No patient found with ID: " . htmlspecialchars($search_id);
            }
        }

        $appointments = $this->appointmentModel->getAppointmentsByDoctor($doctor_id);
        $notices = $this->noticeModel->getForRole('doctor', 3);

        return [
            'appointments' => $appointments,
            'notices' => $notices,
            'patient_info' => $patient_info,
            'vitals' => $vitals,
            'search_error' => $search_error,
            'message' => $message,
            'doctor_name' => $_SESSION['name'] ?? 'Doctor'
        ];
    }

    public function handlePrescription() {
        $doctor_id = (int)($_SESSION['user_id'] ?? 0);
        $message = "";
        $error = "";

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['issue_prescription'])) {
            $patient_id = (int)($_POST['patient_id'] ?? 0);
            $instructions = trim($_POST['instructions'] ?? '');
            $medicines = $_POST['medicine_id'] ?? [];
            $dosages = $_POST['dosage'] ?? [];
            $frequencies = $_POST['frequency'] ?? [];
            $durations = $_POST['duration'] ?? [];

            if ($patient_id <= 0) {
                $error = "Please select a valid patient!";
            } elseif (empty($medicines) || empty($medicines[0])) {
                $error = "Please prescribe at least one medicine!";
            } else {
                $pres_id = $this->prescriptionModel->createPrescription(
                    $doctor_id, $patient_id, $instructions, $medicines, $dosages, $frequencies, $durations
                );

                if ($pres_id) {
                    $message = "Digital prescription issued successfully! (ID: #$pres_id)";
                } else {
                    $error = "Failed to issue prescription. Please check details and try again.";
                }
            }
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_prescription'])) {
            $pres_id = (int)($_POST['prescription_id'] ?? 0);
            if ($pres_id > 0 && $this->prescriptionModel->deletePrescription($pres_id, $doctor_id)) {
                $message = "Prescription #$pres_id has been deleted successfully!";
            } else {
                $error = "Failed to delete prescription.";
            }
        }

        $patients = $this->userModel->getUsersByRole('patient');
        $medicines = $this->medicineModel->getAll();
        $prescriptions = $this->prescriptionModel->getPrescriptionsByDoctor($doctor_id);

        return [
            'patients' => $patients,
            'medicines' => $medicines,
            'prescriptions' => $prescriptions,
            'message' => $message,
            'error' => $error,
            'doctor_name' => $_SESSION['name'] ?? 'Doctor'
        ];
    }
}
?>
