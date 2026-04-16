<?php
require_once 'config/database.php';
require_once 'config/admin_config.php';
require_once 'includes/auth.php';
require_once 'includes/helpers.php';

requireLogin();

$user = getCurrentUser();
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
        $error = 'All fields are required.';
    } elseif ($new_password !== $confirm_password) {
        $error = 'New password and confirm password do not match.';
    } elseif (strlen($new_password) < 6) {
        $error = 'New password must be at least 6 characters long.';
    } elseif (!password_verify($current_password, getAdminPasswordHash())) {
        $error = 'Current password is incorrect.';
    } else {
        setAdminPasswordHash(password_hash($new_password, PASSWORD_DEFAULT));
        $success = 'Password changed successfully!';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings - Libidex Admin</title>
    <link rel="stylesheet" href="assets/css/admin.css">
    <style>
        body { font-family: 'Inter', sans-serif; background: #f0f2f5; }
        .settings-container { max-width: 600px; margin: 40px auto; }
        .settings-card { background: white; border-radius: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); overflow: hidden; }
        .settings-header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 24px; }
        .settings-header h2 { font-size: 20px; font-weight: 600; margin-bottom: 4px; }
        .settings-body { padding: 24px; }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; margin-bottom: 8px; font-weight: 500; color: #333; }
        .form-group input { width: 100%; padding: 12px 15px; border: 2px solid #e1e1e1; border-radius: 8px; font-size: 14px; }
        .form-group input:focus { border-color: #667eea; outline: none; }
        .btn-save { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 12px 24px; border: none; border-radius: 8px; font-size: 14px; font-weight: 500; cursor: pointer; }
        .alert { padding: 15px; border-radius: 8px; margin-bottom: 20px; }
        .alert-success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .alert-error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .back-link { display: inline-block; margin-bottom: 20px; color: #667eea; text-decoration: none; }
        .back-link:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <div class="settings-container">
        <a href="index.php" class="back-link">← Back to Dashboard</a>
        
        <div class="settings-card">
            <div class="settings-header">
                <h2>Change Password</h2>
                <p>Update your admin password</p>
            </div>
            <div class="settings-body">
                <?php if ($success): ?>
                    <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
                <?php endif; ?>
                
                <?php if ($error): ?>
                    <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>
                
                <form method="POST">
                    <div class="form-group">
                        <label>Current Password</label>
                        <input type="password" name="current_password" required placeholder="Enter current password">
                    </div>
                    
                    <div class="form-group">
                        <label>New Password</label>
                        <input type="password" name="new_password" required placeholder="Enter new password (min 6 characters)">
                    </div>
                    
                    <div class="form-group">
                        <label>Confirm New Password</label>
                        <input type="password" name="confirm_password" required placeholder="Confirm new password">
                    </div>
                    
                    <button type="submit" class="btn-save">Change Password</button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
