<?php
require_once __DIR__ . '/../Controller/AuthController.php';

$authController = new AuthController();
$data = $authController->handleRegister();
$error = $data['error'] ?? '';
$success = $data['success'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VitaGuard Lite - Register</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        body { display: flex; justify-content: center; align-items: center; min-height: 100vh; background-color: #f4f7f6; padding: 20px 0; }
        .reg-card { background: #ffffff; padding: 35px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); width: 100%; max-width: 450px; }
        .reg-card h2 { text-align: center; color: #1c5d5a; margin-bottom: 8px; }
        .reg-card p.subtitle { text-align: center; color: #666; font-size: 14px; margin-bottom: 20px; }
        .form-group { margin-bottom: 14px; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: 600; color: #333; font-size: 13px; }
        .form-group select, .form-group input { width: 100%; padding: 9px 12px; border: 1px solid #ccc; border-radius: 6px; font-size: 14px; outline: none; box-sizing: border-box; }
        .form-group select:focus, .form-group input:focus { border-color: #1c5d5a; }
        .password-wrapper { position: relative; width: 100%; }
        .password-wrapper input { padding-right: 42px; }
        .btn-toggle-pwd { position: absolute; right: 12px; top: 50%; transform: translateY(-50%); background: none; border: none; cursor: pointer; font-size: 16px; padding: 0; line-height: 1; outline: none; user-select: none; }
        .btn-submit { width: 100%; padding: 12px; background-color: #1c5d5a; color: #fff; border: none; border-radius: 6px; font-size: 16px; font-weight: bold; cursor: pointer; transition: background 0.3s; margin-top: 10px; }
        .btn-submit:hover { background-color: #144442; }
        .error-msg { background-color: #ffe6e6; color: #d9534f; padding: 10px; border-radius: 6px; margin-bottom: 14px; font-size: 14px; text-align: center; }
        .success-msg { background-color: #e6ffed; color: #28a745; padding: 10px; border-radius: 6px; margin-bottom: 14px; font-size: 14px; text-align: center; }
        .footer-link { text-align: center; margin-top: 16px; font-size: 13px; color: #666; }
        .footer-link a { color: #1c5d5a; text-decoration: none; font-weight: bold; }
        .input-feedback { margin-top: 4px; font-size: 12px; }
    </style>
</head>
<body>

<div class="reg-card">
    <h2>Create an Account</h2>
    <p class="subtitle">Sign up to access VitaGuard Lite</p>

    <?php if (!empty($error)): ?>
        <div class="error-msg"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <?php if (!empty($success)): ?>
        <div class="success-msg"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>
    <div id="reg_msg"></div>

    <form action="register.php" method="POST" id="regForm">
        <div class="form-group">
            <label for="name">Full Name</label>
            <input type="text" name="name" id="name" placeholder="John Doe" required>
        </div>

        <div class="form-group">
            <label for="email">Email Address</label>
            <input type="email" name="email" id="email" placeholder="example@gmail.com" required autocomplete="off">
            <div id="email_status" class="input-feedback"></div>
        </div>

        <div class="form-group">
            <label for="phone">Phone Number</label>
            <input type="text" name="phone" id="phone" placeholder="017xxxxxxxx" required>
        </div>

        <div class="form-group">
            <label for="role">Register as</label>
            <select name="role" id="role" required>
                <option value="patient">Patient</option>
                <option value="doctor">Doctor</option>
                <option value="pharmacist">Pharmacist</option>
                <option value="admin">System Administrator</option>
            </select>
        </div>

        <div class="form-group">
            <label for="password">Password</label>
            <div class="password-wrapper">
                <input type="password" name="password" id="password" placeholder="••••••••" required>
                <button type="button" class="btn-toggle-pwd" onclick="togglePasswordVisibility('password', this)" title="Show/Hide Password"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#666" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg></button>
            </div>
        </div>

        <div class="form-group">
            <label for="confirm_password">Confirm Password</label>
            <div class="password-wrapper">
                <input type="password" name="confirm_password" id="confirm_password" placeholder="••••••••" required>
                <button type="button" class="btn-toggle-pwd" onclick="togglePasswordVisibility('confirm_password', this)" title="Show/Hide Password"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#666" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg></button>
            </div>
        </div>

        <button type="submit" class="btn-submit">Register</button>
    </form>

    <div class="footer-link">
        Already have an account? <a href="login.php">Log In here</a>
    </div>
</div>

<script src="js/validation.js"></script>
<script src="js/auth.js"></script>
</body>
</html>
