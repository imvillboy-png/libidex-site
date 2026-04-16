<?php
session_start();
require_once 'config/database.php';
require_once 'config/admin_config.php';
require_once 'includes/auth.php';
require_once 'includes/helpers.php';

if (isLoggedIn()) {
    header('Location: dashboard.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitize($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($username) || empty($password)) {
        $error = 'Please enter credentials.';
    } else {
        if ($username === 'admin' && password_verify($password, getAdminPasswordHash())) {
            login(1, 'admin');
            header('Location: dashboard.php');
            exit;
        } else {
            $error = 'Invalid credentials.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Login</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif; min-height: 100vh; display: flex; align-items: center; justify-content: center; background: #0f0f1a; }
        .login-box { background: #1a1a2e; padding: 50px; border-radius: 20px; width: 100%; max-width: 400px; }
        .login-box h1 { text-align: center; margin-bottom: 30px; color: #fff; font-size: 24px; letter-spacing: 2px; }
        .form-group { margin-bottom: 20px; }
        .form-group input { width: 100%; padding: 16px; background: #0f0f1a; border: 1px solid #333; border-radius: 10px; color: #fff; font-size: 14px; }
        .form-group input:focus { border-color: #667eea; outline: none; }
        .btn { width: 100%; background: #667eea; color: #fff; padding: 16px; border: none; border-radius: 10px; cursor: pointer; font-size: 14px; font-weight: 600; letter-spacing: 1px; }
        .btn:hover { background: #5a6fd6; }
        .error { background: rgba(220, 53, 69, 0.2); color: #ff6b6b; padding: 12px; border-radius: 8px; margin-bottom: 20px; text-align: center; font-size: 13px; }
    </style>
</head>
<body>
    <div class="login-box">
        <h1>ADMIN</h1>
        
        <?php if ($error): ?>
            <div class="error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <form method="POST" autocomplete="off">
            <div class="form-group">
                <input type="text" name="username" required autocomplete="off" autofocus>
            </div>
            <div class="form-group">
                <input type="password" name="password" required autocomplete="off">
            </div>
            <button type="submit" class="btn">LOGIN</button>
        </form>
    </div>
</body>
</html>
