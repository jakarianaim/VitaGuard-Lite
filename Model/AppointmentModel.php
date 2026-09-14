<?php
require_once __DIR__ . '/db.php';

class AppointmentModel {
    private $conn;

    public function __construct() {
        global $conn;
        $this->conn = $conn;
    }

    public function createAppointment($patient_id, $doctor_id, $appointment_date, $time_slot, $reason) {
        $stmt = $this->conn->prepare("INSERT INTO appointments (patient_id, doctor_id, appointment_date, time_slot, status, reason) VALUES (?, ?, ?, ?, 'Pending', ?)");
        $stmt->bind_param("iisss", $patient_id, $doctor_id, $appointment_date, $time_slot, $reason);
        $success = $stmt->execute();
        $stmt->close();
        return $success;
    }

    public function getAppointmentsByDoctor($doctor_id) {
        $sql = "SELECT a.*, u.name as patient_name, u.phone as patient_phone 
                FROM appointments a 
                JOIN users u ON a.patient_id = u.user_id 
                WHERE a.doctor_id = ? AND a.status != 'Cancelled'
                ORDER BY a.appointment_date ASC, a.time_slot ASC";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $doctor_id);
        $stmt->execute();
        $res = $stmt->get_result();
        $appointments = [];
        while ($row = $res->fetch_assoc()) {
            $appointments[] = $row;
        }
        $stmt->close();
        return $appointments;
    }

    public function getAppointmentsByPatient($patient_id) {
        $sql = "SELECT a.*, u.name as doctor_name 
                FROM appointments a 
                JOIN users u ON a.doctor_id = u.user_id 
                WHERE a.patient_id = ? 
                ORDER BY a.appointment_date DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $patient_id);
        $stmt->execute();
        $res = $stmt->get_result();
        $appointments = [];
        while ($row = $res->fetch_assoc()) {
            $appointments[] = $row;
        }
        $stmt->close();
        return $appointments;
    }

    public function updateStatus($appointment_id, $doctor_id, $status) {
        $stmt = $this->conn->prepare("UPDATE appointments SET status = ? WHERE appointment_id = ? AND doctor_id = ?");
        $stmt->bind_param("sii", $status, $appointment_id, $doctor_id);
        $success = $stmt->execute();
        $stmt->close();
        return $success;
    }

    public function getBookedSlots($doctor_id, $date) {
        $stmt = $this->conn->prepare("SELECT time_slot FROM appointments WHERE doctor_id = ? AND appointment_date = ? AND status != 'Cancelled'");
        $stmt->bind_param("is", $doctor_id, $date);
        $stmt->execute();
        $res = $stmt->get_result();
        $booked = [];
        while ($row = $res->fetch_assoc()) {
            $booked[] = $row['time_slot'];
        }
        $stmt->close();
        return $booked;
    }

    public function cancelAppointmentByPatient($appointment_id, $patient_id) {
        $stmt = $this->conn->prepare("UPDATE appointments SET status = 'Cancelled' WHERE appointment_id = ? AND patient_id = ? AND status = 'Pending'");
        $stmt->bind_param("ii", $appointment_id, $patient_id);
        $success = $stmt->execute();
        $stmt->close();
        return $success;
    }

    public function deleteAppointment($appointment_id, $doctor_id) {
        $stmt = $this->conn->prepare("DELETE FROM appointments WHERE appointment_id = ? AND doctor_id = ?");
        $stmt->bind_param("ii", $appointment_id, $doctor_id);
        $success = $stmt->execute();
        $stmt->close();
        return $success;
    }
}
?>
