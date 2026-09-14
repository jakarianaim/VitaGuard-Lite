<?php
require_once __DIR__ . '/db.php';

class HealthRecordModel {
    private $conn;

    public function __construct() {
        global $conn;
        $this->conn = $conn;
    }

    public function addRecord($patient_id, $bp, $sugar, $pulse, $temp) {
        $stmt = $this->conn->prepare("INSERT INTO health_records (patient_id, blood_pressure, blood_sugar, pulse_rate, temperature) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param('issss', $patient_id, $bp, $sugar, $pulse, $temp);
        $success = $stmt->execute();
        $stmt->close();
        return $success;
    }

    public function getByPatient($patient_id) {
        $stmt = $this->conn->prepare("SELECT * FROM health_records WHERE patient_id = ? ORDER BY recorded_at DESC");
        $stmt->bind_param('i', $patient_id);
        $stmt->execute();
        $res = $stmt->get_result();
        $records = [];
        while ($row = $res->fetch_assoc()) {
            $records[] = $row;
        }
        $stmt->close();
        return $records;
    }

    public function deleteRecord($record_id, $patient_id) {
        $stmt = $this->conn->prepare("DELETE FROM health_records WHERE record_id = ? AND patient_id = ?");
        $stmt->bind_param('ii', $record_id, $patient_id);
        $success = $stmt->execute();
        $stmt->close();
        return $success;
    }

    public function updateRecord($record_id, $patient_id, $bp, $sugar, $pulse, $temp) {
        $stmt = $this->conn->prepare("UPDATE health_records SET blood_pressure = ?, blood_sugar = ?, pulse_rate = ?, temperature = ? WHERE record_id = ? AND patient_id = ?");
        $stmt->bind_param('ssssii', $bp, $sugar, $pulse, $temp, $record_id, $patient_id);
        $success = $stmt->execute();
        $stmt->close();
        return $success;
    }

    public function getRecordById($record_id, $patient_id) {
        $stmt = $this->conn->prepare("SELECT * FROM health_records WHERE record_id = ? AND patient_id = ?");
        $stmt->bind_param('ii', $record_id, $patient_id);
        $stmt->execute();
        $res = $stmt->get_result();
        $record = $res->fetch_assoc();
        $stmt->close();
        return $record;
    }
}
?>
