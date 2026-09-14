<?php
require_once __DIR__ . '/../config/db_config.php';
require_once __DIR__ . '/../Model/UserModel.php';

class AuthController {
    private $userModel;

    public function __construct() {
        $this->userModel = new UserModel();
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    public static function checkAccess($allowed_role) {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
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

        if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
            header("Location: login.php");
            exit();
        }

        if ($_SESSION['role'] !== $allowed_role) {
            switch ($_SESSION['role']) {
                case 'admin':
                    header("Location: admin_dashboard.php");
                    break;
                case 'doctor':
                    header("Location: doctor_dashboard.php");
                    break;
                case 'pharmacist':
                    header("Location: pharmacist_dashboard.php");
                    break;
                case 'patient':
                    header("Location: patient_dashboard.php");
                    break;
                default:
                    header("Location: login.php");
                    break;
            }
            exit();
        }
    }

    public function handleLogin() {
        if ((isset($_GET['action']) && $_GET['action'] === 'logout') || isset($_GET['logout']) || isset($_POST['logout'])) {
            $this->handleLogout();
        }

        if (isset($_SESSION['user_id']) && isset($_SESSION['role'])) {
            $this->redirectDashboard($_SESSION['role']);
        }

        $error = "";

        if ($_SERVER["REQUEST_METHOD"] === "POST") {
            $role        = trim($_POST['role'] ?? '');
            $email       = trim($_POST['email'] ?? '');
            $password    = trim($_POST['password'] ?? '');
            $remember_me = isset($_POST['remember_me']);

            if (empty($role) || empty($email) || empty($password)) {
                $error = "All fields are required!";
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $error = "Invalid email format!";
            } else {
                $user = $this->userModel->getUserByEmailAndRole($email, $role);

                if ($user) {
                    $user_password = $user['password'] ?? $user['password_hash'] ?? '';
                    if ($password === $user_password) {
                        session_regenerate_id(true);

                        $_SESSION['user_id'] = $user['user_id'];
                        $_SESSION['name']    = $user['name'];
                        $_SESSION['username']= $user['name'];
                        $_SESSION['email']   = $user['email'];
                        $_SESSION['role']    = $user['role'];

                        if ($remember_me) {
                            $expire_one_week = time() + (7 * 24 * 60 * 60);
                            $cookie_val = base64_encode($user['user_id'] . ':' . sha1($user['email']));
                            setcookie('vitaguard_remember', $cookie_val, $expire_one_week, '/', '', false, true);
                            setcookie('vitaguard_remember_email', $user['email'], $expire_one_week, '/', '', false, false);
                            setcookie('vitaguard_remember_role', $user['role'], $expire_one_week, '/', '', false, false);
                        } else {
                            setcookie('vitaguard_remember', '', time() - 3600, '/');
                            setcookie('vitaguard_remember_email', '', time() - 3600, '/');
                            setcookie('vitaguard_remember_role', '', time() - 3600, '/');
                        }

                        $this->redirectDashboard($user['role']);
                    } else {
                        $error = "Invalid password!";
                    }
                } else {
                    $error = "No account found matching this email and role!";
                }
            }
        }

        return ['error' => $error];
    }

    public function handleRegister() {
        $error = "";
        $success = "";

        if ($_SERVER["REQUEST_METHOD"] === "POST") {
            $name     = trim($_POST['name'] ?? '');
            $email    = trim($_POST['email'] ?? '');
            $phone    = trim($_POST['phone'] ?? '');
            $role     = trim($_POST['role'] ?? '');
            $password = trim($_POST['password'] ?? '');
            $confirm  = trim($_POST['confirm_password'] ?? '');

            if (empty($name) || empty($email) || empty($phone) || empty($role) || empty($password)) {
                $error = "All fields are required!";
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $error = "Please provide a valid email address!";
            } elseif (!preg_match('/^01[3-9]\d{8}$/', $phone)) {
                $error = "Invalid phone number! Must be 11 digits (e.g., 017xxxxxxxx).";
            } elseif (strlen($password) < 6) {
                $error = "Password must be at least 6 characters long!";
            } elseif ($password !== $confirm) {
                $error = "Passwords do not match!";
            } else {
                if ($this->userModel->emailExists($email)) {
                    $error = "This email is already registered!";
                } else {
                    $registered = $this->userModel->registerUser($name, $email, $password, $role, $phone);

                    if ($registered) {
                        $success = "Registration successful! You can now log in.";
                    } else {
                        $error = "Registration failed! Please try again.";
                    }
                }
            }
        }

        return ['error' => $error, 'success' => $success];
    }

    public function handleLogout() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION = [];
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        if (isset($_COOKIE['vitaguard_remember'])) {
            setcookie('vitaguard_remember', '', time() - 3600, '/');
            setcookie('vitaguard_remember', '', time() - 3600);
            unset($_COOKIE['vitaguard_remember']);
        }
        session_destroy();
        header("Location: login.php");
        exit();
    }

    private function redirectDashboard($role) {
        switch ($role) {
            case 'admin':
                header("Location: admin_dashboard.php");
                break;
            case 'doctor':
                header("Location: doctor_dashboard.php");
                break;
            case 'pharmacist':
                header("Location: pharmacist_dashboard.php");
                break;
            case 'patient':
                header("Location: patient_dashboard.php");
                break;
            default:
                header("Location: login.php");
                break;
        }
        exit();
    }
}
?>
