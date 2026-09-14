<?php
require_once __DIR__ . '/AuthController.php';
require_once __DIR__ . '/../Model/UserModel.php';
require_once __DIR__ . '/../Model/NoticeModel.php';

class AdminController {
    private $userModel;
    private $noticeModel;

    public function __construct() {
        AuthController::checkAccess('admin');
        $this->userModel = new UserModel();
        $this->noticeModel = new NoticeModel();
    }

    public function handleDashboard() {
        $message = "";

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_user'])) {
            $user_id = (int)($_POST['user_id'] ?? 0);
            $name = trim($_POST['name'] ?? '');
            $phone = trim($_POST['phone'] ?? '');
            $role = trim($_POST['role'] ?? '');

            if ($user_id > 0 && !empty($name) && !empty($phone) && in_array($role, ['patient', 'doctor', 'pharmacist'])) {
                if ($this->userModel->updateUser($user_id, $name, $phone, $role)) {
                    $message = "<span class='success'>User account updated successfully!</span>";
                } else {
                    $message = "<span class='error'>Failed to update user account.</span>";
                }
            } else {
                $message = "<span class='error'>Invalid user details provided.</span>";
            }
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_user'])) {
            $user_id = (int)($_POST['user_id'] ?? 0);
            if ($user_id > 0) {
                if ($this->userModel->deleteUserById($user_id)) {
                    $message = "<span class='success'>User account deleted successfully!</span>";
                } else {
                    $message = "<span class='error'>Failed to delete user account.</span>";
                }
            }
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['publish_notice'])) {
            $title = trim($_POST['title'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $target_role = trim($_POST['target_role'] ?? 'all');
            $admin_id = (int)($_SESSION['user_id'] ?? 0);

            if (!empty($title) && !empty($description)) {
                if ($this->noticeModel->addNotice($admin_id, $title, $description, $target_role)) {
                    $message = "<span class='success'>Notice published successfully!</span>";
                } else {
                    $message = "<span class='error'>Failed to publish notice.</span>";
                }
            } else {
                $message = "<span class='error'>Notice title and content cannot be empty!</span>";
            }
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_notice'])) {
            $notice_id = (int)($_POST['notice_id'] ?? 0);
            $title = trim($_POST['title'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $target_role = trim($_POST['target_role'] ?? 'all');

            if ($notice_id > 0 && !empty($title) && !empty($description)) {
                if ($this->noticeModel->updateNotice($notice_id, $title, $description, $target_role)) {
                    $message = "<span class='success'>Notice updated successfully!</span>";
                } else {
                    $message = "<span class='error'>Failed to update notice.</span>";
                }
            } else {
                $message = "<span class='error'>Notice title and content cannot be empty!</span>";
            }
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_notice'])) {
            $notice_id = (int)($_POST['notice_id'] ?? 0);
            if ($notice_id > 0) {
                if ($this->noticeModel->deleteNotice($notice_id)) {
                    $message = "<span class='success'>Notice removed successfully!</span>";
                }
            }
        }

        $edit_user = null;
        if (isset($_GET['edit_user']) && (int)$_GET['edit_user'] > 0) {
            $edit_user = $this->userModel->getUserById((int)$_GET['edit_user']);
        }

        $edit_notice = null;
        if (isset($_GET['edit_notice']) && (int)$_GET['edit_notice'] > 0) {
            $edit_notice = $this->noticeModel->getNoticeById((int)$_GET['edit_notice']);
        }

        $stats = $this->userModel->getRoleCounts();
        $users = $this->userModel->getAllNonAdminUsers();
        $notices = $this->noticeModel->getAll();

        return [
            'stats' => $stats,
            'users' => $users,
            'notices' => $notices,
            'edit_user' => $edit_user,
            'edit_notice' => $edit_notice,
            'message' => $message,
            'admin_name' => $_SESSION['name'] ?? 'Admin'
        ];
    }
}
?>
