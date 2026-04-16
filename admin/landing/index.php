<?php
require_once '../config/database.php';
require_once '../includes/auth.php';
require_once '../includes/helpers.php';

requireLogin();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Landing Pages - Libidex Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', sans-serif; background: #f0f2f5; }
        .container { max-width: 1200px; margin: 0 auto; padding: 20px; }
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; background: #fff; padding: 25px; border-radius: 12px; }
        .header h1 { font-size: 24px; color: #333; }
        .card { background: #fff; border-radius: 12px; overflow: hidden; margin-bottom: 20px; }
        .card-header { padding: 20px 25px; border-bottom: 1px solid #eee; display: flex; justify-content: space-between; align-items: center; }
        .card-header h2 { font-size: 18px; }
        .page-item { display: flex; justify-content: space-between; align-items: center; padding: 20px 25px; border-bottom: 1px solid #f0f0f0; }
        .page-item:last-child { border-bottom: none; }
        .page-info h3 { font-size: 16px; margin-bottom: 5px; }
        .page-info p { font-size: 13px; color: #888; }
        .page-badge { padding: 5px 12px; border-radius: 20px; font-size: 12px; font-weight: 600; }
        .badge-active { background: #d4edda; color: #155724; }
        .btn { padding: 10px 20px; border: none; border-radius: 8px; cursor: pointer; font-size: 14px; text-decoration: none; display: inline-block; }
        .btn-primary { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: #fff; }
        .btn-sm { padding: 8px 15px; font-size: 13px; }
        .sidebar { position: fixed; left: 0; top: 0; width: 260px; height: 100vh; background: linear-gradient(180deg, #1a1a2e 0%, #16213e 100%); color: #fff; padding: 20px; }
        .sidebar-logo { padding: 20px 0; border-bottom: 1px solid rgba(255,255,255,0.1); margin-bottom: 20px; }
        .sidebar-logo h2 { font-size: 22px; }
        .nav-item { padding: 14px 0; }
        .nav-item a { color: rgba(255,255,255,0.7); text-decoration: none; display: flex; align-items: center; gap: 12px; }
        .nav-item.active a { color: #667eea; font-weight: 600; }
        .main { margin-left: 260px; }
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
                <h1>🌐 Landing Pages</h1>
                <a href="../index.html" target="_blank" class="btn btn-primary">👁️ View Website</a>
            </div>
            
            <div class="card">
                <div class="card-header">
                    <h2>Available Landing Pages</h2>
                </div>
                
                <div class="page-item">
                    <div class="page-info">
                        <h3>🛒 ProMan Landing Page</h3>
                        <p>pro-man-o8qm.onrender.com | /landing/proman/index.html</p>
                    </div>
                    <div>
                        <span class="page-badge badge-active">Active</span>
                        <a href="edit.php?page=proman" class="btn btn-primary btn-sm">✏️ Edit</a>
                    </div>
                </div>
                
                <div class="page-item">
                    <div class="page-info">
                        <h3>💊 Libidex Landing Page</h3>
                        <p>libidex-site.onrender.com | /index.html</p>
                    </div>
                    <div>
                        <span class="page-badge badge-active">Active</span>
                        <a href="edit.php?page=libidex" class="btn btn-primary btn-sm">✏️ Edit</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
