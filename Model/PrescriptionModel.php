<?php
require_once __DIR__ . '/db.php';

class PrescriptionModel {
    private $conn;

    public function __construct() {
        global $conn;
        $this->conn = $conn;
    }

    public function createPrescription($doctor_id, $patient_id, $instructions, $medicines, $dosages, $frequencies, $durations) {
        $this->conn->begin_transaction();
        try {
            $sql_pres = "INSERT INTO prescriptions (doctor_id, patient_id, instructions, status) VALUES (?, ?, ?, 'Active')";
            $stmt_pres = $this->conn->prepare($sql_pres);
            $stmt_pres->bind_param('iis', $doctor_id, $patient_id, $instructions);
            $stmt_pres->execute();
            $prescription_id = $this->conn->insert_id;
            $stmt_pres->close();

            $sql_item = "INSERT INTO prescription_items (prescription_id, medicine_id, dosage, frequency, duration_days) VALUES (?, ?, ?, ?, ?)";
            $stmt_item = $this->conn->prepare($sql_item);

            for ($i = 0; $i < count($medicines); $i++) {
                if (!empty($medicines[$i])) {
                    $med_id = (int)$medicines[$i];
                    $dosage = $dosages[$i];
                    $freq = $frequencies[$i];
                    $dur = (int)$durations[$i];

                    $stmt_item->bind_param('iissi', $prescription_id, $med_id, $dosage, $freq, $dur);
                    $stmt_item->execute();
                }
            }
            $stmt_item->close();

            $this->conn->commit();
            return $prescription_id;
        } catch (Exception $e) {
            $this->conn->rollback();
            return false;
        }
    }

    public function getPrescriptionsByPatient($patient_id) {
        $sql = "SELECT p.*, u.name as doctor_name 
                FROM prescriptions p 
                JOIN users u ON p.doctor_id = u.user_id 
                WHERE p.patient_id = ? 
                ORDER BY p.created_at DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('i', $patient_id);
        $stmt->execute();
        $res = $stmt->get_result();
        $prescriptions = [];
        while ($row = $res->fetch_assoc()) {
            $prescriptions[] = $row;
        }
        $stmt->close();
        return $prescriptions;
    }

    public function getPrescriptionById($id) {
        $stmt = $this->conn->prepare("SELECT p.*, d.name as doctor_name, pt.name as patient_name, pt.phone as patient_phone 
                                      FROM prescriptions p 
                                      JOIN users d ON p.doctor_id = d.user_id 
                                      JOIN users pt ON p.patient_id = pt.user_id 
                                      WHERE p.prescription_id = ?");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $res = $stmt->get_result();
        $pres = $res->fetch_assoc();
        $stmt->close();
        return $pres;
    }

    public function getPrescriptionItems($prescription_id) {
        $sql = "SELECT pi.*, m.trade_name, m.generic_name, m.stock_quantity 
                FROM prescription_items pi 
                JOIN medicines m ON pi.medicine_id = m.medicine_id 
                WHERE pi.prescription_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('i', $prescription_id);
        $stmt->execute();
        $res = $stmt->get_result();
        $items = [];
        while ($row = $res->fetch_assoc()) {
            $items[] = $row;
        }
        $stmt->close();
        return $items;
    }

    public function dispensePrescription($prescription_id) {
        $this->conn->begin_transaction();
        try {
            $stmt = $this->conn->prepare("UPDATE prescriptions SET status = 'Completed' WHERE prescription_id = ?");
            $stmt->bind_param('i', $prescription_id);
            $stmt->execute();
            $stmt->close();

            $items = $this->getPrescriptionItems($prescription_id);
            $deduct_stmt = $this->conn->prepare("UPDATE medicines SET stock_quantity = GREATEST(0, stock_quantity - 1) WHERE medicine_id = ?");
            foreach ($items as $item) {
                $med_id = (int)$item['medicine_id'];
                $deduct_stmt->bind_param('i', $med_id);
                $deduct_stmt->execute();
            }
            $deduct_stmt->close();

            $this->conn->commit();
            return true;
        } catch (Exception $e) {
            $this->conn->rollback();
            return false;
        }
    }

    public function deletePrescription($prescription_id, $doctor_id) {
        $this->conn->begin_transaction();
        try {
            $stmt1 = $this->conn->prepare("DELETE FROM prescription_items WHERE prescription_id = ?");
            $stmt1->bind_param("i", $prescription_id);
            $stmt1->execute();
            $stmt1->close();

            $stmt2 = $this->conn->prepare("DELETE FROM prescriptions WHERE prescription_id = ? AND doctor_id = ?");
            $stmt2->bind_param("ii", $prescription_id, $doctor_id);
            $success = $stmt2->execute();
            $stmt2->close();

            $this->conn->commit();
            return $success;
        } catch (Exception $e) {
            $this->conn->rollback();
            return false;
        }
    }

    public function getPrescriptionsByDoctor($doctor_id) {
        $sql = "SELECT p.*, u.name as patient_name, u.phone as patient_phone 
                FROM prescriptions p 
                JOIN users u ON p.patient_id = u.user_id 
                WHERE p.doctor_id = ? 
                ORDER BY p.created_at DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('i', $doctor_id);
        $stmt->execute();
        $res = $stmt->get_result();
        $prescriptions = [];
        while ($row = $res->fetch_assoc()) {
            $prescriptions[] = $row;
        }
        $stmt->close();
        return $prescriptions;
    }
}
?>
