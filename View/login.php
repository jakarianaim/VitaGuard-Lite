<?php
require_once __DIR__ . '/../Controller/AuthController.php';

$authController = new AuthController();
$data = $authController->handleLogin();
$error = $data['error'] ?? '';

$remembered_email = $_COOKIE['vitaguard_remember_email'] ?? '';
$remembered_role = $_COOKIE['vitaguard_remember_role'] ?? 'patient';
$is_remembered = !empty($remembered_email);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VitaGuard Lite - Login</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        body { display: flex; justify-content: center; align-items: center; min-height: 100vh; background-color: #f4f7f6; }
        .login-card { background: #ffffff; padding: 40px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); width: 100%; max-width: 400px; }
        .login-card h2 { text-align: center; color: #1c5d5a; margin-bottom: 8px; }
        .login-card p.subtitle { text-align: center; color: #666; font-size: 14px; margin-bottom: 24px; }
        .form-group { margin-bottom: 18px; }
        .form-group label { display: block; margin-bottom: 6px; font-weight: 600; color: #333; font-size: 14px; }
        .form-group select, .form-group input { width: 100%; padding: 10px 14px; border: 1px solid #ccc; border-radius: 6px; font-size: 14px; outline: none; box-sizing: border-box; }
        .form-group select:focus, .form-group input:focus { border-color: #1c5d5a; }
        .password-wrapper { position: relative; width: 100%; }
        .password-wrapper input { padding-right: 42px; }
        .btn-toggle-pwd { position: absolute; right: 12px; top: 50%; transform: translateY(-50%); background: none; border: none; cursor: pointer; font-size: 16px; padding: 0; line-height: 1; outline: none; user-select: none; }
        .btn-submit { width: 100%; padding: 12px; background-color: #1c5d5a; color: #fff; border: none; border-radius: 6px; font-size: 16px; font-weight: bold; cursor: pointer; transition: background 0.3s; }
        .btn-submit:hover { background-color: #144442; }
        .error-msg { background-color: #ffe6e6; color: #d9534f; padding: 10px; border-radius: 6px; margin-bottom: 16px; font-size: 14px; text-align: center; }
        .footer-link { text-align: center; margin-top: 18px; font-size: 13px; color: #666; }
        .footer-link a { color: #1c5d5a; text-decoration: none; font-weight: bold; }
        .remember-me { display: flex; align-items: center; gap: 8px; font-size: 13px; color: #555; margin-bottom: 18px; }
        .remember-me input { width: auto; }
    </style>
</head>
<body>

<div class="login-card">
    <h2>Welcome Back</h2>
    <p class="subtitle">Please log in to continue</p>

    <?php if (!empty($error)): ?>
        <div class="error-msg"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <form action="login.php" method="POST" id="loginForm">
        <div class="form-group">
            <label for="role">I am a...</label>
            <select name="role" id="role" required>
                <option value="patient" <?php echo $remembered_role === 'patient' ? 'selected' : ''; ?>>Patient</option>
                <option value="doctor" <?php echo $remembered_role === 'doctor' ? 'selected' : ''; ?>>Doctor</option>
                <option value="pharmacist" <?php echo $remembered_role === 'pharmacist' ? 'selected' : ''; ?>>Pharmacist</option>
                <option value="admin" <?php echo $remembered_role === 'admin' ? 'selected' : ''; ?>>System Administrator</option>
            </select>
        </div>

        <div class="form-group">
            <label for="email">Email Address</label>
            <input type="email" name="email" id="email" placeholder="example@gmail.com" required value="<?php echo htmlspecialchars($remembered_email); ?>">
        </div>

        <div class="form-group">
            <label for="password">Password</label>
            <div class="password-wrapper">
                <input type="password" name="password" id="password" placeholder="••••••••" required>
                <button type="button" class="btn-toggle-pwd" onclick="togglePasswordVisibility('password', this)" title="Show/Hide Password"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#666" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg></button>
            </div>
        </div>

        <div class="remember-me">
            <input type="checkbox" name="remember_me" id="remember_me" <?php echo $is_remembered ? 'checked' : ''; ?>>
            <label for="remember_me">Remember me </label>
        </div>

        <button type="submit" class="btn-submit">Log In</button>
    </form>

    <div class="footer-link">
        Don't have an account? <a href="register.php">Create one here</a>
    </div>
</div>

<script src="js/validation.js"></script>
</body>
</html>
