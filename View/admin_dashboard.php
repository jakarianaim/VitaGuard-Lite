<?php
require_once __DIR__ . '/../Controller/AdminController.php';

$adminController = new AdminController();
$data = $adminController->handleDashboard();

$stats = $data['stats'];
$users = $data['users'];
$notices = $data['notices'];
$edit_user = $data['edit_user'] ?? null;
$edit_notice = $data['edit_notice'] ?? null;
$message = $data['message'];
$admin_name = $data['admin_name'];

$tab = $_GET['tab'] ?? 'overview';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>System Administrator Dashboard | VitaGuard Lite</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<div class="app-container">
    <div class="sidebar sidebar-admin">
        <h2>VitaGuard Lite</h2>
        <p class="role-title">Role: System Admin</p>
        
        <a href="admin_dashboard.php?tab=overview" style="<?php echo $tab === 'overview' ? 'font-weight:bold; color:#fff;' : ''; ?>">📊 Overview</a>
        <a href="admin_dashboard.php?tab=users" style="<?php echo $tab === 'users' ? 'font-weight:bold; color:#fff;' : ''; ?>">👥 User Management</a>
        <a href="admin_dashboard.php?tab=notices" style="<?php echo $tab === 'notices' ? 'font-weight:bold; color:#fff;' : ''; ?>">📢 Broadcast Notices</a>
        
        <a href="logout.php" class="btn btn-logout">Logout</a>
    </div>

    <div class="main-content">
        <h1>System Administrator Dashboard</h1>
        <p class="subtitle">Welcome, <?php echo htmlspecialchars($admin_name); ?>. Here is the current system overview.</p>

        <?php if(!empty($message)) echo "<div style='margin-bottom:15px;'>$message</div>"; ?>

        <?php if ($tab === 'overview'): ?>
            <div class="card-container">
                <div class="card">
                    <h3>Total Patients</h3>
                    <h1><?php echo (int)$stats['patient']; ?></h1>
                </div>
                <div class="card">
                    <h3>Active Doctors</h3>
                    <h1><?php echo (int)$stats['doctor']; ?></h1>
                </div>
                <div class="card">
                    <h3>Available Pharmacists</h3>
                    <h1><?php echo (int)$stats['pharmacist']; ?></h1>
                </div>
            </div>

            <div style="margin-top: 35px;">
                <h2>Quick Actions</h2>
                <div style="display: flex; gap: 15px; margin-top: 15px;">
                    <a href="admin_dashboard.php?tab=users" class="btn btn-primary">Manage User Accounts</a>
                    <a href="admin_dashboard.php?tab=notices" class="btn" style="background-color: #1b4f72;">Publish Announcement</a>
                </div>
            </div>

        <?php elseif ($tab === 'users'): ?>
            <h2>User Accounts Management</h2>

            <?php if ($edit_user): ?>
            <div class="form-container" style="max-width: 550px; margin-bottom: 25px; border: 2px solid #3498db; background: #f8fafc;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                    <h3>Edit User: <?php echo htmlspecialchars($edit_user['name']); ?></h3>
                    <a href="admin_dashboard.php?tab=users" class="btn btn-secondary" style="padding: 5px 12px; font-size: 13px;">Cancel Edit</a>
                </div>
                <form action="admin_dashboard.php?tab=users" method="post">
                    <input type="hidden" name="user_id" value="<?php echo (int)$edit_user['user_id']; ?>">
                    <div class="form-group">
                        <label>Full Name</label>
                        <input type="text" name="name" value="<?php echo htmlspecialchars($edit_user['name']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Phone Number</label>
                        <input type="text" name="phone" value="<?php echo htmlspecialchars($edit_user['phone']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Role</label>
                        <select name="role" required>
                            <option value="patient" <?php if ($edit_user['role'] === 'patient') echo 'selected'; ?>>Patient</option>
                            <option value="doctor" <?php if ($edit_user['role'] === 'doctor') echo 'selected'; ?>>Doctor</option>
                            <option value="pharmacist" <?php if ($edit_user['role'] === 'pharmacist') echo 'selected'; ?>>Pharmacist</option>
                        </select>
                    </div>
                    <button type="submit" name="update_user" class="btn btn-primary">Save User Details</button>
                </form>
            </div>
            <?php endif; ?>

            <table>
                <tr>
                    <th class="th-admin">Name</th>
                    <th class="th-admin">Email</th>
                    <th class="th-admin">Phone</th>
                    <th class="th-admin">Role</th>
                    <th class="th-admin">Joined Date</th>
                    <th class="th-admin">Action</th>
                </tr>
                <?php
                if (!empty($users)) {
                    foreach ($users as $row) {
                        $id = $row['user_id'];
                        $name = htmlspecialchars($row['name']);
                        $email = htmlspecialchars($row['email']);
                        $phone = htmlspecialchars($row['phone']);
                        $role = htmlspecialchars($row['role']);
                        $joined = date("d M Y", strtotime($row['created_at']));
                        $role_class = 'role-' . $role;

                        echo "<tr>
                                <td>$name</td>
                                <td>$email</td>
                                <td>$phone</td>
                                <td><span class='role-badge $role_class'>$role</span></td>
                                <td>$joined</td>
                                <td>
                                    <a href='admin_dashboard.php?tab=users&edit_user=$id' class='btn btn-secondary' style='padding: 5px 10px; font-size: 12px; text-decoration: none; margin-right: 5px; display: inline-block;'>Edit</a>
                                    <form action='admin_dashboard.php?tab=users' method='post' style='display:inline;' onsubmit=\"return confirm('Are you sure you want to permanently delete this user?');\">
                                        <input type='hidden' name='user_id' value='$id'>
                                        <button type='submit' name='delete_user' class='btn btn-danger' style='padding: 5px 10px; font-size: 12px;'>Delete</button>
                                    </form>
                                </td>
                              </tr>";
                    }
                } else {
                    echo "<tr><td colspan='6' style='text-align:center;'>No users found in the system.</td></tr>";
                }
                ?>
            </table>

        <?php elseif ($tab === 'notices'): ?>
            <h2>System Notices & Announcements</h2>
            
            <div style="display: flex; gap: 20px; align-items: flex-start; margin-top: 20px; flex-wrap: wrap;">
                <?php if ($edit_notice): ?>
                <div class="form-container" style="flex: 1; min-width: 300px; border: 2px solid #3498db; background: #f8fafc;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                        <h3>Edit Notice #<?php echo (int)$edit_notice['notice_id']; ?></h3>
                        <a href="admin_dashboard.php?tab=notices" class="btn btn-secondary" style="padding: 5px 12px; font-size: 13px;">Cancel Edit</a>
                    </div>
                    <form action="admin_dashboard.php?tab=notices" method="post">
                        <input type="hidden" name="notice_id" value="<?php echo (int)$edit_notice['notice_id']; ?>">
                        <div class="form-group">
                            <label>Notice Title</label>
                            <input type="text" name="title" value="<?php echo htmlspecialchars($edit_notice['title']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label>Target Audience</label>
                            <select name="target_role" required>
                                <option value="all" <?php if ($edit_notice['target_role'] === 'all') echo 'selected'; ?>>All Users</option>
                                <option value="patient" <?php if ($edit_notice['target_role'] === 'patient') echo 'selected'; ?>>Patients Only</option>
                                <option value="doctor" <?php if ($edit_notice['target_role'] === 'doctor') echo 'selected'; ?>>Doctors Only</option>
                                <option value="pharmacist" <?php if ($edit_notice['target_role'] === 'pharmacist') echo 'selected'; ?>>Pharmacists Only</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Message Content</label>
                            <textarea name="description" rows="4" required><?php echo htmlspecialchars($edit_notice['description']); ?></textarea>
                        </div>
                        <button type="submit" name="update_notice" class="btn btn-primary" style="width: 100%;">Update Notice</button>
                    </form>
                </div>
                <?php else: ?>
                <div class="form-container" style="flex: 1; min-width: 300px;">
                    <h3>Publish New Notice</h3>
                    <div id="notice_msg"></div>
                    <form action="admin_dashboard.php?tab=notices" method="post" id="noticeForm">
                        <div class="form-group">
                            <label>Notice Title</label>
                            <input type="text" name="title" placeholder="e.g. System Maintenance" required>
                        </div>
                        <div class="form-group">
                            <label>Target Audience</label>
                            <select name="target_role" required>
                                <option value="all">All Users</option>
                                <option value="patient">Patients Only</option>
                                <option value="doctor">Doctors Only</option>
                                <option value="pharmacist">Pharmacists Only</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Message Content</label>
                            <textarea name="description" rows="4" placeholder="Write announcement here..." required></textarea>
                        </div>
                        <button type="submit" name="publish_notice" class="btn btn-primary" style="width: 100%;">Publish Notice</button>
                    </form>
                </div>
                <?php endif; ?>

                <div style="flex: 2; min-width: 350px;">
                    <h3>Notice History</h3>
                    <table id="notice_table">
                        <tr>
                            <th class="th-admin">Date</th>
                            <th class="th-admin">Title & Content</th>
                            <th class="th-admin">Target</th>
                            <th class="th-admin">Action</th>
                        </tr>
                        <?php
                        if (!empty($notices)) {
                            foreach ($notices as $row) {
                                $id = $row['notice_id'];
                                $date = date("d M Y", strtotime($row['created_at']));
                                $title = htmlspecialchars($row['title']);
                                $desc = htmlspecialchars($row['description']);
                                $target = htmlspecialchars(ucfirst($row['target_role']));

                                echo "<tr>
                                        <td style='white-space: nowrap;'>$date</td>
                                        <td><strong>$title</strong><br><small style='color: #64748b;'>$desc</small></td>
                                        <td><span class='role-badge' style='background-color: #34495e;'>$target</span></td>
                                        <td>
                                            <a href='admin_dashboard.php?tab=notices&edit_notice=$id' class='btn btn-secondary' style='padding: 5px 10px; font-size: 12px; text-decoration: none; margin-right: 5px; display: inline-block;'>Edit</a>
                                            <form action='admin_dashboard.php?tab=notices' method='post' style='display:inline;' onsubmit=\"return confirm('Delete this notice?');\">
                                                <input type='hidden' name='notice_id' value='$id'>
                                                <button type='submit' name='delete_notice' class='btn btn-danger' style='padding: 5px 10px; font-size: 12px;'>Delete</button>
                                            </form>
                                        </td>
                                      </tr>";
                            }
                        } else {
                            echo "<tr><td colspan='4' style='text-align:center;'>No notices published yet.</td></tr>";
                        }
                        ?>
                    </table>
                </div>
            </div>
        <?php endif; ?>

    </div>
</div>

<script src="js/admin.js"></script>
</body>
</html>
