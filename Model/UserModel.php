<?php
require_once __DIR__ . '/db.php';

class UserModel {
    private $conn;

    public function __construct() {
        global $conn;
        $this->conn = $conn;
    }

    public function getUserByEmailAndRole($email, $role) {
        $stmt = $this->conn->prepare("SELECT * FROM users WHERE email = ? AND role = ?");
        $stmt->bind_param("ss", $email, $role);
        $stmt->execute();
        $res = $stmt->get_result();
        $user = $res->fetch_assoc();
        $stmt->close();
        if ($user && !isset($user['password']) && isset($user['password_hash'])) {
            $user['password'] = $user['password_hash'];
        }
        return $user;
    }

    public function getUserByEmail($email) {
        $stmt = $this->conn->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $res = $stmt->get_result();
        $user = $res->fetch_assoc();
        $stmt->close();
        if ($user && !isset($user['password']) && isset($user['password_hash'])) {
            $user['password'] = $user['password_hash'];
        }
        return $user;
    }

    public function getUserById($id) {
        $stmt = $this->conn->prepare("SELECT * FROM users WHERE user_id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $res = $stmt->get_result();
        $user = $res->fetch_assoc();
        $stmt->close();
        if ($user && !isset($user['password']) && isset($user['password_hash'])) {
            $user['password'] = $user['password_hash'];
        }
        return $user;
    }

    public function emailExists($email) {
        $stmt = $this->conn->prepare("SELECT user_id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();
        $exists = $stmt->num_rows > 0;
        $stmt->close();
        return $exists;
    }

    public function registerUser($name, $email, $password, $role, $phone) {
        $colCheck = $this->conn->query("SHOW COLUMNS FROM users LIKE 'password'");
        $col = ($colCheck && $colCheck->num_rows > 0) ? 'password' : 'password_hash';
        $stmt = $this->conn->prepare("INSERT INTO users (name, email, {$col}, role, phone) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssss", $name, $email, $password, $role, $phone);
        $success = $stmt->execute();
        $stmt->close();
        return $success;
    }

    public function getAllNonAdminUsers() {
        $sql = "SELECT user_id, name, email, role, phone, created_at FROM users WHERE role != 'admin' ORDER BY created_at DESC";
        $result = $this->conn->query($sql);
        $users = [];
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $users[] = $row;
            }
        }
        return $users;
    }

    public function deleteUserById($user_id) {
        $stmt = $this->conn->prepare("DELETE FROM users WHERE user_id = ? AND role != 'admin'");
        $stmt->bind_param("i", $user_id);
        $success = $stmt->execute();
        $stmt->close();
        return $success;
    }

    public function getUsersByRole($role) {
        $stmt = $this->conn->prepare("SELECT user_id, name, email, phone FROM users WHERE role = ? ORDER BY name ASC");
        $stmt->bind_param("s", $role);
        $stmt->execute();
        $res = $stmt->get_result();
        $users = [];
        while ($row = $res->fetch_assoc()) {
            $users[] = $row;
        }
        $stmt->close();
        return $users;
    }

    public function getRoleCounts() {
        $stats = [
            'patient' => 0,
            'doctor' => 0,
            'pharmacist' => 0
        ];
        $sql = "SELECT role, COUNT(*) as total FROM users GROUP BY role";
        $result = $this->conn->query($sql);
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                if (array_key_exists($row['role'], $stats)) {
                    $stats[$row['role']] = (int)$row['total'];
                }
            }
        }
        return $stats;
    }

    public function updateUser($user_id, $name, $phone, $role) {
        $stmt = $this->conn->prepare("UPDATE users SET name = ?, phone = ?, role = ? WHERE user_id = ? AND role != 'admin'");
        $stmt->bind_param("sssi", $name, $phone, $role, $user_id);
        $success = $stmt->execute();
        $stmt->close();
        return $success;
    }
}
?>
