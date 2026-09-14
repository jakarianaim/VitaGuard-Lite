<?php
require_once __DIR__ . '/../config/db_config.php';
require_once __DIR__ . '/../Model/UserModel.php';
require_once __DIR__ . '/../Model/MedicineModel.php';
require_once __DIR__ . '/../Model/AppointmentModel.php';
require_once __DIR__ . '/../Model/HealthRecordModel.php';
require_once __DIR__ . '/../Model/PrescriptionModel.php';
require_once __DIR__ . '/../Model/NoticeModel.php';

class ApiController {
    private $userModel;
    private $medicineModel;
    private $appointmentModel;
    private $healthRecordModel;
    private $prescriptionModel;
    private $noticeModel;

    public function __construct() {
        $this->userModel = new UserModel();
        $this->medicineModel = new MedicineModel();
        $this->appointmentModel = new AppointmentModel();
        $this->healthRecordModel = new HealthRecordModel();
        $this->prescriptionModel = new PrescriptionModel();
        $this->noticeModel = new NoticeModel();
    }

    public function handleRequest() {
        header('Content-Type: application/json; charset=utf-8');

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $action = $_GET['action'] ?? $_POST['action'] ?? '';

        switch ($action) {
            case 'check_email':
                $email = trim($_GET['email'] ?? $_POST['email'] ?? '');
                if (empty($email)) {
                    echo json_encode(['status' => 'error', 'message' => 'Email is required']);
                    exit();
                }
                if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    echo json_encode(['status' => 'invalid', 'message' => 'Invalid email format']);
                    exit();
                }
                $exists = $this->userModel->emailExists($email);
                echo json_encode([
                    'status' => 'success',
                    'exists' => $exists,
                    'message' => $exists ? 'This email is already registered!' : 'Email is available!'
                ]);
                exit();

            case 'check_medicine':
                $trade_name = trim($_GET['trade_name'] ?? $_POST['trade_name'] ?? '');
                if (empty($trade_name)) {
                    echo json_encode(['status' => 'empty', 'message' => '']);
                    exit();
                }
                $existing = $this->medicineModel->checkMedicineExists($trade_name);
                if ($existing) {
                    echo json_encode([
                        'status' => 'exists',
                        'exists' => true,
                        'message' => 'Medicine "' . $existing['trade_name'] . '" already exists in database (Current stock: ' . $existing['stock_quantity'] . ').',
                        'stock' => $existing['stock_quantity']
                    ]);
                } else {
                    echo json_encode([
                        'status' => 'available',
                        'exists' => false,
                        'message' => 'Medicine name is available to add.'
                    ]);
                }
                exit();

            case 'add_medicine':
                if (($_SESSION['role'] ?? '') !== 'pharmacist' && ($_SESSION['role'] ?? '') !== 'admin') {
                    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
                    exit();
                }
                $trade_name = trim($_POST['trade_name'] ?? '');
                $generic_name = trim($_POST['generic_name'] ?? '');
                $category = trim($_POST['category'] ?? '');
                $unit_price = (float)($_POST['unit_price'] ?? 0);
                $stock_quantity = (int)($_POST['stock_quantity'] ?? 0);
                $expiry_date = trim($_POST['expiry_date'] ?? '');

                if (empty($trade_name) || empty($generic_name) || empty($category) || $unit_price <= 0 || $stock_quantity < 0 || empty($expiry_date)) {
                    echo json_encode(['status' => 'error', 'message' => 'Please fill in all required fields properly.']);
                    exit();
                }

                $existing = $this->medicineModel->checkMedicineExists($trade_name);
                if ($existing) {
                    echo json_encode(['status' => 'error', 'message' => 'Medicine with this name already exists in database.']);
                    exit();
                }

                $success = $this->medicineModel->addMedicine($trade_name, $generic_name, $category, $unit_price, $stock_quantity, $expiry_date);
                if ($success) {
                    global $conn;
                    $new_id = $conn->insert_id;
                    echo json_encode([
                        'status' => 'success',
                        'message' => 'Medicine added to inventory successfully!',
                        'data' => [
                            'medicine_id' => $new_id,
                            'trade_name' => $trade_name,
                            'generic_name' => $generic_name,
                            'category' => $category,
                            'unit_price' => number_format($unit_price, 2),
                            'stock_quantity' => $stock_quantity,
                            'expiry_date' => $expiry_date
                        ]
                    ]);
                } else {
                    echo json_encode(['status' => 'error', 'message' => 'Failed to add medicine to database.']);
                }
                exit();

            case 'get_slots':
                $doctor_id = (int)($_GET['doctor_id'] ?? 0);
                $date = trim($_GET['date'] ?? '');
                if ($doctor_id > 0 && !empty($date)) {
                    $booked = $this->appointmentModel->getBookedSlots($doctor_id, $date);
                    echo json_encode(['status' => 'success', 'booked' => $booked]);
                } else {
                    echo json_encode(['status' => 'error', 'message' => 'Invalid doctor or date']);
                }
                exit();

            case 'book_appointment':
                $patient_id = (int)($_SESSION['user_id'] ?? 0);
                if ($patient_id <= 0 || ($_SESSION['role'] ?? '') !== 'patient') {
                    echo json_encode(['status' => 'error', 'message' => 'Please login as a patient to book an appointment.']);
                    exit();
                }
                $doctor_id = (int)($_POST['doctor_id'] ?? 0);
                $appointment_date = trim($_POST['appointment_date'] ?? '');
                $time_slot = trim($_POST['time_slot'] ?? '');
                $reason = trim($_POST['reason'] ?? '');

                if ($doctor_id <= 0 || empty($appointment_date) || empty($time_slot)) {
                    echo json_encode(['status' => 'error', 'message' => 'Please select a doctor, appointment date, and time slot.']);
                    exit();
                }

                $booked = $this->appointmentModel->getBookedSlots($doctor_id, $appointment_date);
                if (in_array($time_slot, $booked)) {
                    echo json_encode(['status' => 'error', 'message' => 'This time slot is already booked. Please choose another slot.']);
                    exit();
                }

                $success = $this->appointmentModel->createAppointment($patient_id, $doctor_id, $appointment_date, $time_slot, $reason);
                if ($success) {
                    $doctorUser = $this->userModel->getUserById($doctor_id);
                    echo json_encode([
                        'status' => 'success',
                        'message' => 'Appointment requested successfully! Pending doctor approval.',
                        'data' => [
                            'doctor_name' => $doctorUser['name'] ?? 'Doctor',
                            'appointment_date' => $appointment_date,
                            'time_slot' => $time_slot,
                            'reason' => !empty($reason) ? $reason : 'Routine consultation',
                            'status' => 'Pending'
                        ]
                    ]);
                } else {
                    echo json_encode(['status' => 'error', 'message' => 'Failed to book appointment.']);
                }
                exit();

            case 'add_prescription':
                $doctor_id = (int)($_SESSION['user_id'] ?? 0);
                if ($doctor_id <= 0 || ($_SESSION['role'] ?? '') !== 'doctor') {
                    echo json_encode(['status' => 'error', 'message' => 'Unauthorized. Only doctors can issue prescriptions.']);
                    exit();
                }
                $patient_id = (int)($_POST['patient_id'] ?? 0);
                $instructions = trim($_POST['instructions'] ?? '');
                $medicines = $_POST['medicine_id'] ?? [];
                $dosages = $_POST['dosage'] ?? [];
                $frequencies = $_POST['frequency'] ?? [];
                $durations = $_POST['duration'] ?? [];

                if ($patient_id <= 0 || empty($medicines) || count($medicines) === 0) {
                    echo json_encode(['status' => 'error', 'message' => 'Please select a patient and at least one medicine.']);
                    exit();
                }

                $prescription_id = $this->prescriptionModel->createPrescription($doctor_id, $patient_id, $instructions, $medicines, $dosages, $frequencies, $durations);
                if ($prescription_id) {
                    echo json_encode([
                        'status' => 'success',
                        'message' => 'Prescription #' . $prescription_id . ' issued successfully!'
                    ]);
                } else {
                    echo json_encode(['status' => 'error', 'message' => 'Failed to issue prescription.']);
                }
                exit();

            case 'register':
                $name = trim($_POST['name'] ?? '');
                $email = trim($_POST['email'] ?? '');
                $password = $_POST['password'] ?? '';
                $phone = trim($_POST['phone'] ?? '');
                $role = trim($_POST['role'] ?? '');

                if (empty($name) || empty($email) || empty($password) || empty($phone) || empty($role)) {
                    echo json_encode(['status' => 'error', 'message' => 'All fields are required.']);
                    exit();
                }
                if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    echo json_encode(['status' => 'error', 'message' => 'Invalid email address.']);
                    exit();
                }
                if ($this->userModel->emailExists($email)) {
                    echo json_encode(['status' => 'error', 'message' => 'Email is already registered.']);
                    exit();
                }
                $success = $this->userModel->registerUser($name, $email, $password, $phone, $role);
                if ($success) {
                    echo json_encode([
                        'status' => 'success',
                        'message' => 'Registration successful! Redirecting to login...'
                    ]);
                } else {
                    echo json_encode(['status' => 'error', 'message' => 'Registration failed. Please try again.']);
                }
                exit();

            case 'add_notice':
                $admin_id = (int)($_SESSION['user_id'] ?? 0);
                if ($admin_id <= 0 || ($_SESSION['role'] ?? '') !== 'admin') {
                    echo json_encode(['status' => 'error', 'message' => 'Unauthorized.']);
                    exit();
                }
                $title = trim($_POST['title'] ?? '');
                $description = trim($_POST['description'] ?? '');
                $target_role = trim($_POST['target_role'] ?? 'all');

                if (empty($title) || empty($description)) {
                    echo json_encode(['status' => 'error', 'message' => 'Title and description are required.']);
                    exit();
                }
                $success = $this->noticeModel->addNotice($admin_id, $title, $description, $target_role);
                if ($success) {
                    global $conn;
                    $notice_id = $conn->insert_id;
                    echo json_encode([
                        'status' => 'success',
                        'message' => 'Notice published successfully!',
                        'data' => [
                            'notice_id' => $notice_id,
                            'date' => date("d M Y"),
                            'title' => $title,
                            'description' => $description,
                            'target' => ucfirst($target_role)
                        ]
                    ]);
                } else {
                    echo json_encode(['status' => 'error', 'message' => 'Failed to publish notice.']);
                }
                exit();

            default:
                echo json_encode(['status' => 'error', 'message' => 'Unknown action']);
                exit();
        }
    }
}

if (basename($_SERVER['SCRIPT_FILENAME']) === basename(__FILE__)) {
    $api = new ApiController();
    $api->handleRequest();
}
?>
