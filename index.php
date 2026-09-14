<?php
require_once __DIR__ . '/config/db_config.php';
require_once __DIR__ . '/Model/UserModel.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if ((isset($_GET['action']) && $_GET['action'] === 'logout') || isset($_GET['logout'])) {
    header("Location: View/logout.php");
    exit();
}

if (!isset($_SESSION['user_id']) && isset($_COOKIE['vitaguard_remember'])) {
    $token_data = explode(':', base64_decode($_COOKIE['vitaguard_remember']));
    if (count($token_data) === 2) {
        $userModel = new UserModel();
        $user = $userModel->getUserById((int)$token_data[0]);
        if ($user && hash_equals(sha1($user['email']), $token_data[1])) {
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['name']    = $user['name'];
            $_SESSION['username']= $user['name'];
            $_SESSION['email']   = $user['email'];
            $_SESSION['role']    = $user['role'];
        }
    }
}

if (isset($_SESSION['user_id']) && isset($_SESSION['role'])) {
    switch ($_SESSION['role']) {
        case 'admin':
            header("Location: View/admin_dashboard.php");
            exit();
        case 'doctor':
            header("Location: View/doctor_dashboard.php");
            exit();
        case 'pharmacist':
            header("Location: View/pharmacist_dashboard.php");
            exit();
        case 'patient':
            header("Location: View/patient_dashboard.php");
            exit();
    }
}

header("Location: View/login.php");
exit();
?>