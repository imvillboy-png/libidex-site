<?php
require_once '../config/database.php';
require_once '../includes/auth.php';
require_once '../includes/helpers.php';

requireLogin();

$page = isset($_GET['page']) ? $_GET['page'] : 'proman';

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $content = $_POST['content'] ?? '';
    
    $file = $page === 'proman' 
        ? __DIR__ . '/../../landing/proman/index.html'
        : __DIR__ . '/../../index.html';
    
    if (file_exists($file)) {
        if (file_put_contents($file, $content) !== false) {
            $message = 'Landing page updated successfully!';
        } else {
            $error = 'Failed to save file.';
        }
    } else {
        $error = 'File not found.';
    }
}

$file = $page === 'proman' 
    ? __DIR__ . '/../../landing/proman/index.html'
    : __DIR__ . '/../../index.html';

$content = file_exists($file) ? file_get_contents($file) : 'File not found';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Landing Page - Libidex Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', sans-serif; background: #f0f2f5; }
        .container { max-width: 1400px; margin: 0 auto; padding: 20px; }
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; background: #fff; padding: 20px; border-radius: 12px; }
        .header h1 { font-size: 24px; }
        .btn { padding: 10px 20px; border: none; border-radius: 8px; cursor: pointer; font-size: 14px; text-decoration: none; display: inline-block; }
        .btn-success { background: #28a745; color: #fff; }
        .btn-secondary { background: #6c757d; color: #fff; }
        .card { background: #fff; border-radius: 12px; overflow: hidden; margin-bottom: 20px; }
        .tabs { display: flex; gap: 10px; padding: 20px; background: #fff; border-bottom: 1px solid #eee; }
        .tab { padding: 10px 20px; border: none; background: #f8f9fa; border-radius: 8px; cursor: pointer; font-size: 14px; text-decoration: none; color: #333; }
        .tab.active { background: #667eea; color: #fff; }
        .editor { width: 100%; min-height: 500px; padding: 20px; border: none; font-family: 'Monaco', 'Consolas', monospace; font-size: 13px; resize: vertical; background: #1e1e1e; color: #d4d4d4; }
        .alert { padding: 15px 20px; border-radius: 8px; margin-bottom: 20px; }
        .alert-success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .alert-error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .sidebar { position: fixed; left: 0; top: 0; width: 260px; height: 100vh; background: linear-gradient(180deg, #1a1a2e 0%, #16213e 100%); color: #fff; padding: 20px; }
        .sidebar-logo { padding: 20px 0; border-bottom: 1px solid rgba(255,255,255,0.1); margin-bottom: 20px; }
        .sidebar-logo h2 { font-size: 22px; }
        .nav-item { padding: 14px 0; }
        .nav-item a { color: rgba(255,255,255,0.7); text-decoration: none; display: flex; align-items: center; gap: 12px; }
        .nav-item.active a { color: #667eea; font-weight: 600; }
        .main { margin-left: 260px; }
        .save-bar { padding: 20px; background: #fff; border-top: 1px solid #eee; position: sticky; bottom: 0; }
    </style>
</head>
<body>
    <div class="sidebar">
        <div class="sidebar-logo"><h2>Libidex Admin</h2></div>
        <div class="nav-item"><a href="../dashboard.php">📊 Dashboard</a></div>
        <div class="nav-item"><a href="../orders/">📦 Orders</a></div>
        <div class="nav-item"><a href="../products/">🏷️ Products</a></div>
        <div class="nav-item active"><a href="index.php">🌐 Landing Pages</a></div>
        <div class="nav-item"><a href="../reviews/">⭐ Reviews</a></div>
        <div class="nav-item"><a href="../settings.php">⚙️ Settings</a></div>
        <div class="nav-item"><a href="../logout.php">🚪 Logout</a></div>
    </div>
    
    <div class="main">
        <div class="container">
            <div class="header">
                <h1>Edit Landing Page</h1>
                <div>
                    <a href="index.php" class="btn btn-secondary">← Back</a>
                </div>
            </div>
            
            <?php if ($message): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div>
            <?php endif; ?>
            
            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            
            <div class="card">
                <div class="tabs">
                    <a href="edit.php?page=proman" class="tab <?php echo $page === 'proman' ? 'active' : ''; ?>">🛒 ProMan</a>
                    <a href="edit.php?page=libidex" class="tab <?php echo $page === 'libidex' ? 'active' : ''; ?>">💊 Libidex</a>
                </div>
                <form method="POST">
                    <textarea name="content" class="editor"><?php echo htmlspecialchars($content); ?></textarea>
                    <div class="save-bar">
                        <button type="submit" class="btn btn-success">💾 Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
