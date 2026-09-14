<?php
require_once __DIR__ . '/db.php';

class NoticeModel {
    private $conn;

    public function __construct() {
        global $conn;
        $this->conn = $conn;
    }

    public function addNotice($admin_id, $title, $description, $target_role) {
        $target_role = strtolower($target_role);
        $stmt = $this->conn->prepare("INSERT INTO system_notices (admin_id, title, description, target_role) VALUES (?, ?, ?, ?)");
        $stmt->bind_param('isss', $admin_id, $title, $description, $target_role);
        $success = $stmt->execute();
        $stmt->close();
        return $success;
    }

    public function getAll() {
        $sql = "SELECT * FROM system_notices ORDER BY created_at DESC";
        $result = $this->conn->query($sql);
        $notices = [];
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $notices[] = $row;
            }
        }
        return $notices;
    }

    public function getForRole($role, $limit = 5) {
        $role = strtolower($role);
        $stmt = $this->conn->prepare("SELECT title, description, created_at, target_role FROM system_notices WHERE target_role = ? OR target_role = 'all' ORDER BY created_at DESC LIMIT ?");
        $stmt->bind_param('si', $role, $limit);
        $stmt->execute();
        $res = $stmt->get_result();
        $notices = [];
        while ($row = $res->fetch_assoc()) {
            $notices[] = $row;
        }
        $stmt->close();
        return $notices;
    }

    public function deleteNotice($notice_id) {
        $stmt = $this->conn->prepare("DELETE FROM system_notices WHERE notice_id = ?");
        $stmt->bind_param('i', $notice_id);
        $success = $stmt->execute();
        $stmt->close();
        return $success;
    }

    public function updateNotice($notice_id, $title, $description, $target_role) {
        $target_role = strtolower($target_role);
        $stmt = $this->conn->prepare("UPDATE system_notices SET title = ?, description = ?, target_role = ? WHERE notice_id = ?");
        $stmt->bind_param('sssi', $title, $description, $target_role, $notice_id);
        $success = $stmt->execute();
        $stmt->close();
        return $success;
    }

    public function getNoticeById($notice_id) {
        $stmt = $this->conn->prepare("SELECT * FROM system_notices WHERE notice_id = ?");
        $stmt->bind_param('i', $notice_id);
        $stmt->execute();
        $res = $stmt->get_result();
        $notice = $res->fetch_assoc();
        $stmt->close();
        return $notice;
    }
}
?>
