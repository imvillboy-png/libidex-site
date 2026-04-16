<?php
session_start();
require_once 'config/database.php';
require_once 'includes/auth.php';
require_once 'includes/helpers.php';

if (isLoggedIn()) {
    header('Location: dashboard.php');
    exit;
}

$error = '';

$ADMIN_USERNAME = 'admin';
$ADMIN_PASSWORD_HASH = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitize($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($username) || empty($password)) {
        $error = 'Please enter both username and password.';
    } else {
        if ($username === $ADMIN_USERNAME && password_verify($password, $ADMIN_PASSWORD_HASH)) {
            login(1, 'admin');
            header('Location: dashboard.php');
            exit;
        } else {
            $db = getDB();
            $user = $db->getAdminUser($username);
            if ($user && password_verify($password, $user['password'])) {
                login($user['id'], $user['username']);
                header('Location: dashboard.php');
                exit;
            } else {
                $error = 'Invalid username or password.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - Libidex</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', sans-serif; min-height: 100vh; display: flex; align-items: center; justify-content: center; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
        .login-box { background: #fff; padding: 50px; border-radius: 20px; box-shadow: 0 20px 60px rgba(0,0,0,0.3); width: 100%; max-width: 420px; }
        .login-box h1 { text-align: center; margin-bottom: 10px; color: #333; font-size: 28px; }
        .login-box p { text-align: center; color: #666; margin-bottom: 30px; }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; margin-bottom: 8px; font-weight: 500; color: #555; }
        .form-group input { width: 100%; padding: 14px; border: 2px solid #e1e1e1; border-radius: 10px; font-size: 14px; transition: all 0.3s; }
        .form-group input:focus { border-color: #667eea; outline: none; }
        .btn { width: 100%; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: #fff; padding: 14px 30px; border: none; border-radius: 10px; cursor: pointer; font-size: 16px; font-weight: 500; }
        .error { background: #f8d7da; color: #721c24; padding: 15px; border-radius: 10px; margin-bottom: 20px; border: 1px solid #f5c6cb; }
    </style>
</head>
<body>
    <div class="login-box">
        <h1><i class="fas fa-lock"></i></h1>
        <p>Libidex Admin Panel</p>
        
        <?php if ($error): ?>
            <div class="error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="form-group">
                <label>Username</label>
                <input type="text" name="username" required placeholder="Enter username">
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" required placeholder="Enter password">
            </div>
            <button type="submit" class="btn">Login</button>
        </form>
    </div>
</body>
</html>
