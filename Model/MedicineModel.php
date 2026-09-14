<?php
require_once __DIR__ . '/db.php';

class MedicineModel {
    private $conn;

    public function __construct() {
        global $conn;
        $this->conn = $conn;
    }

    public function getAll() {
        $sql = "SELECT * FROM medicines ORDER BY medicine_id DESC";
        $result = $this->conn->query($sql);
        $list = [];
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $list[] = $row;
            }
        }
        return $list;
    }

    public function getById($id) {
        $stmt = $this->conn->prepare("SELECT * FROM medicines WHERE medicine_id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $res = $stmt->get_result();
        $med = $res->fetch_assoc();
        $stmt->close();
        return $med;
    }

    public function addMedicine($trade_name, $generic_name, $category, $unit_price, $stock_quantity, $expiry_date) {
        $stmt = $this->conn->prepare("INSERT INTO medicines (trade_name, generic_name, category, unit_price, stock_quantity, expiry_date) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssdis", $trade_name, $generic_name, $category, $unit_price, $stock_quantity, $expiry_date);
        $success = $stmt->execute();
        $stmt->close();
        return $success;
    }

    public function deleteMedicine($id) {
        $stmt = $this->conn->prepare("DELETE FROM medicines WHERE medicine_id = ?");
        $stmt->bind_param("i", $id);
        $success = $stmt->execute();
        $stmt->close();
        return $success;
    }

    public function updateMedicine($medicine_id, $trade_name, $generic_name, $category, $unit_price, $stock_quantity, $expiry_date) {
        $stmt = $this->conn->prepare("UPDATE medicines SET trade_name = ?, generic_name = ?, category = ?, unit_price = ?, stock_quantity = ?, expiry_date = ? WHERE medicine_id = ?");
        $stmt->bind_param("sssdisi", $trade_name, $generic_name, $category, $unit_price, $stock_quantity, $expiry_date, $medicine_id);
        $success = $stmt->execute();
        $stmt->close();
        return $success;
    }

    public function searchMedicines($query) {
        $searchTerm = "%" . $query . "%";
        $stmt = $this->conn->prepare("SELECT medicine_id, trade_name, generic_name, category, unit_price, stock_quantity, expiry_date FROM medicines WHERE trade_name LIKE ? OR generic_name LIKE ? ORDER BY trade_name ASC LIMIT 20");
        $stmt->bind_param("ss", $searchTerm, $searchTerm);
        $stmt->execute();
        $res = $stmt->get_result();
        $results = [];
        while ($row = $res->fetch_assoc()) {
            $results[] = $row;
        }
        $stmt->close();
        return $results;
    }

    public function deductStock($medicine_id, $qty) {
        $stmt = $this->conn->prepare("UPDATE medicines SET stock_quantity = GREATEST(0, stock_quantity - ?) WHERE medicine_id = ?");
        $stmt->bind_param("ii", $qty, $medicine_id);
        $success = $stmt->execute();
        $stmt->close();
        return $success;
    }

    public function addStock($medicine_id, $amount) {
        $stmt = $this->conn->prepare("UPDATE medicines SET stock_quantity = stock_quantity + ? WHERE medicine_id = ?");
        $stmt->bind_param("ii", $amount, $medicine_id);
        $success = $stmt->execute();
        $stmt->close();
        return $success;
    }

    public function getLowStockCount($threshold = 20) {
        $stmt = $this->conn->prepare("SELECT COUNT(*) as low_count FROM medicines WHERE stock_quantity < ?");
        $stmt->bind_param("i", $threshold);
        $stmt->execute();
        $res = $stmt->get_result();
        $row = $res->fetch_assoc();
        $stmt->close();
        return (int)($row['low_count'] ?? 0);
    }

    public function checkMedicineExists($trade_name) {
        $stmt = $this->conn->prepare("SELECT medicine_id, trade_name, generic_name, stock_quantity FROM medicines WHERE LOWER(trade_name) = LOWER(?) LIMIT 1");
        $stmt->bind_param("s", $trade_name);
        $stmt->execute();
        $res = $stmt->get_result();
        $med = $res->fetch_assoc();
        $stmt->close();
        return $med;
    }
}
?>
