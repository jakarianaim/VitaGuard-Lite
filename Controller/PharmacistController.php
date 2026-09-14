<?php
require_once __DIR__ . '/AuthController.php';
require_once __DIR__ . '/../Model/MedicineModel.php';
require_once __DIR__ . '/../Model/PrescriptionModel.php';
require_once __DIR__ . '/../Model/NoticeModel.php';

class PharmacistController {
    private $medicineModel;
    private $prescriptionModel;
    private $noticeModel;

    public function __construct() {
        AuthController::checkAccess('pharmacist');
        $this->medicineModel = new MedicineModel();
        $this->prescriptionModel = new PrescriptionModel();
        $this->noticeModel = new NoticeModel();
    }

    public function handleDashboard() {
        $low_stock_count = $this->medicineModel->getLowStockCount(20);
        $notices = $this->noticeModel->getForRole('pharmacist', 3);

        return [
            'low_stock_count' => $low_stock_count,
            'notices' => $notices,
            'pharmacist_name' => $_SESSION['name'] ?? 'Pharmacist'
        ];
    }

    public function handleInventory() {
        $message = "";

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_medicine'])) {
            $trade_name = trim($_POST['trade_name'] ?? '');
            $generic_name = trim($_POST['generic_name'] ?? '');
            $category = trim($_POST['category'] ?? '');
            $unit_price = (float)($_POST['unit_price'] ?? 0);
            $stock_quantity = (int)($_POST['stock_quantity'] ?? 0);
            $expiry_date = trim($_POST['expiry_date'] ?? '');

            if (empty($trade_name) || empty($generic_name) || empty($category) || empty($expiry_date)) {
                $message = "<span class='error'>All fields are required!</span>";
            } elseif ($unit_price <= 0) {
                $message = "<span class='error'>Unit price must be a positive number!</span>";
            } elseif ($stock_quantity < 0) {
                $message = "<span class='error'>Stock quantity cannot be negative!</span>";
            } else {
                if ($this->medicineModel->addMedicine($trade_name, $generic_name, $category, $unit_price, $stock_quantity, $expiry_date)) {
                    $message = "<span class='success'>Medicine added successfully!</span>";
                } else {
                    $message = "<span class='error'>Failed to add medicine.</span>";
                }
            }
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_medicine'])) {
            $medicine_id = (int)($_POST['medicine_id'] ?? 0);
            $trade_name = trim($_POST['trade_name'] ?? '');
            $generic_name = trim($_POST['generic_name'] ?? '');
            $category = trim($_POST['category'] ?? '');
            $unit_price = (float)($_POST['unit_price'] ?? 0);
            $stock_quantity = (int)($_POST['stock_quantity'] ?? 0);
            $expiry_date = trim($_POST['expiry_date'] ?? '');

            if ($medicine_id > 0 && !empty($trade_name) && !empty($generic_name) && !empty($category) && $unit_price > 0 && $stock_quantity >= 0 && !empty($expiry_date)) {
                if ($this->medicineModel->updateMedicine($medicine_id, $trade_name, $generic_name, $category, $unit_price, $stock_quantity, $expiry_date)) {
                    $message = "<span class='success'>Medicine updated successfully!</span>";
                } else {
                    $message = "<span class='error'>Failed to update medicine.</span>";
                }
            } else {
                $message = "<span class='error'>Invalid details provided for update.</span>";
            }
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_medicine'])) {
            $medicine_id = (int)($_POST['medicine_id'] ?? 0);
            if ($medicine_id > 0 && $this->medicineModel->deleteMedicine($medicine_id)) {
                $message = "<span class='success'>Medicine removed successfully!</span>";
            } else {
                $message = "<span class='error'>Failed to remove medicine.</span>";
            }
        }

        $edit_medicine = null;
        if (isset($_GET['edit_id']) && (int)$_GET['edit_id'] > 0) {
            $edit_medicine = $this->medicineModel->getById((int)$_GET['edit_id']);
        }

        $medicines = $this->medicineModel->getAll();

        return [
            'medicines' => $medicines,
            'edit_medicine' => $edit_medicine,
            'message' => $message,
            'pharmacist_name' => $_SESSION['name'] ?? 'Pharmacist'
        ];
    }

    public function handleDispense() {
        $message = "";
        $prescription_data = null;
        $prescription_items = [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['dispense_prescription'])) {
            $pres_id = (int)($_POST['prescription_id'] ?? 0);
            if ($pres_id > 0) {
                if ($this->prescriptionModel->dispensePrescription($pres_id)) {
                    $message = "<span class='success'>Prescription ID #$pres_id has been verified, dispensed, and inventory stock updated!</span>";
                } else {
                    $message = "<span class='error'>Failed to dispense prescription.</span>";
                }
            }
        }

        if (isset($_GET['search_id']) && !empty($_GET['search_id'])) {
            $search_id = (int)$_GET['search_id'];
            $prescription_data = $this->prescriptionModel->getPrescriptionById($search_id);

            if ($prescription_data) {
                $prescription_items = $this->prescriptionModel->getPrescriptionItems($search_id);
            } else {
                $message = "<span class='error'>No prescription found with ID: $search_id</span>";
            }
        }

        return [
            'prescription_data' => $prescription_data,
            'prescription_items' => $prescription_items,
            'message' => $message,
            'pharmacist_name' => $_SESSION['name'] ?? 'Pharmacist'
        ];
    }
}
?>
