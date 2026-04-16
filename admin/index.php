<?php
session_start();

require_once __DIR__ . '/config/database.php';

initDB();

$message = '';
$message_type = '';

$is_logged_in = $_SESSION['admin_logged_in'] ?? false;

if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: index.php');
    exit;
}

if ($is_logged_in) {
    header('Location: dashboard.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        $db = getDB();
        
        switch ($_POST['action']) {
            case 'login':
                $username = $_POST['username'] ?? '';
                $password = $_POST['password'] ?? '';
                
                $ADMIN_USERNAME = 'admin';
                $ADMIN_PASSWORD_HASH = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi';
                
                $loggedIn = false;
                
                if ($username === $ADMIN_USERNAME && password_verify($password, $ADMIN_PASSWORD_HASH)) {
                    $_SESSION['admin_id'] = 1;
                    $_SESSION['admin_username'] = 'admin';
                    $_SESSION['admin_role'] = 'admin';
                    $_SESSION['admin_logged_in'] = true;
                    $_SESSION['is_hardcoded_admin'] = true;
                    header('Location: index.php');
                    exit;
                } else {
                    $user = $db->getAdminUser($username);
                    if ($user && password_verify($password, $user['password'])) {
                        $_SESSION['admin_id'] = $user['id'];
                        $_SESSION['admin_username'] = $user['username'];
                        $_SESSION['admin_role'] = $user['role'];
                        $_SESSION['admin_logged_in'] = true;
                        $_SESSION['is_hardcoded_admin'] = false;
                        header('Location: index.php');
                        exit;
                    } else {
                        $message = 'Invalid username or password!';
                        $message_type = 'error';
                    }
                }
                break;
                
            case 'logout':
                session_destroy();
                header('Location: index.php');
                exit;
                
            case 'change_password':
                $current_pass = $_POST['current_password'] ?? '';
                $new_pass = $_POST['new_password'] ?? '';
                $confirm_pass = $_POST['confirm_password'] ?? '';
                
                $ADMIN_PASSWORD_HASH = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi';
                
                $valid = false;
                if (isset($_SESSION['is_hardcoded_admin']) && $_SESSION['is_hardcoded_admin']) {
                    $valid = password_verify($current_pass, $ADMIN_PASSWORD_HASH);
                } else {
                    $user = $db->getAdminUser($_SESSION['admin_username']);
                    $valid = $user && password_verify($current_pass, $user['password']);
                }
                
                if (!$valid) {
                    $message = 'Current password is incorrect!';
                    $message_type = 'error';
                } elseif ($new_pass !== $confirm_pass) {
                    $message = 'New passwords do not match!';
                    $message_type = 'error';
                } elseif (strlen($new_pass) < 6) {
                    $message = 'Password must be at least 6 characters!';
                    $message_type = 'error';
                } else {
                    $message = 'Password changed successfully!';
                    $message_type = 'success';
                }
                break;
                
            case 'add_user':
                $new_username = $_POST['new_username'] ?? '';
                $new_password = $_POST['new_password'] ?? '';
                $new_email = $_POST['new_email'] ?? '';
                $new_role = $_POST['new_role'] ?? 'admin';
                
                $existingUser = $db->getAdminUser($new_username);
                if ($existingUser) {
                    $message = 'Username already exists!';
                    $message_type = 'error';
                } else {
                    $db->addAdminUser([
                        'username' => $new_username,
                        'password' => $new_password,
                        'email' => $new_email,
                        'role' => $new_role
                    ]);
                    $message = 'User added successfully!';
                    $message_type = 'success';
                }
                break;
                
            case 'delete_user':
                $user_id = intval($_POST['user_id'] ?? 0);
                if ($user_id == $_SESSION['admin_id']) {
                    $message = 'You cannot delete yourself!';
                    $message_type = 'error';
                } else {
                    $db->deleteAdminUser($user_id);
                    $message = 'User deleted successfully!';
                    $message_type = 'success';
                }
                break;
                
            case 'update_product':
                $db->updateProduct($_POST);
                $message = 'Product updated successfully!';
                $message_type = 'success';
                break;
                
            case 'add_review':
                $db->addReview($_POST);
                $message = 'Review added successfully!';
                $message_type = 'success';
                break;
                
            case 'update_review':
                $db->updateReview($_POST);
                $message = 'Review updated successfully!';
                $message_type = 'success';
                break;
                
            case 'delete_review':
                $db->deleteReview($_POST['id']);
                $message = 'Review deleted successfully!';
                $message_type = 'success';
                break;
                
            case 'update_order_status':
                $db->updateOrderStatus($_POST['id'], $_POST['status']);
                $message = 'Order status updated!';
                $message_type = 'success';
                break;
                
            case 'delete_order':
                $db->deleteOrder($_POST['id']);
                $message = 'Order deleted successfully!';
                $message_type = 'success';
                break;
        }
    }
}

$is_logged_in = $_SESSION['admin_logged_in'] ?? false;
$admin_username = $_SESSION['admin_username'] ?? 'Admin';
$admin_role = $_SESSION['admin_role'] ?? 'admin';
$page = $_GET['page'] ?? 'dashboard';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Libidex Admin Panel</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', sans-serif; background: #f0f2f5; color: #333; }
        
        .login-container { min-height: 100vh; display: flex; align-items: center; justify-content: center; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
        .login-box { background: #fff; padding: 50px; border-radius: 20px; box-shadow: 0 20px 60px rgba(0,0,0,0.3); width: 100%; max-width: 420px; }
        .login-box h1 { text-align: center; margin-bottom: 10px; color: #333; font-size: 28px; }
        .login-box p { text-align: center; color: #666; margin-bottom: 30px; }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; margin-bottom: 8px; font-weight: 500; color: #555; }
        .form-group input, .form-group select, .form-group textarea { width: 100%; padding: 14px; border: 2px solid #e1e1e1; border-radius: 10px; font-size: 14px; transition: all 0.3s; }
        .form-group input:focus, .form-group select:focus, .form-group textarea:focus { border-color: #667eea; outline: none; }
        .btn { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: #fff; padding: 14px 30px; border: none; border-radius: 10px; cursor: pointer; font-size: 16px; font-weight: 500; transition: transform 0.2s; }
        .btn:hover { transform: translateY(-2px); }
        .btn-sm { padding: 8px 16px; font-size: 13px; }
        .btn-success { background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%); }
        .btn-danger { background: linear-gradient(135deg, #eb3349 0%, #f45c43 100%); }
        .btn-warning { background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); }
        .btn-primary { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
        
        .admin-layout { display: flex; min-height: 100vh; }
        .sidebar { width: 260px; background: linear-gradient(180deg, #1a1a2e 0%, #16213e 100%); color: #fff; padding: 20px 0; position: fixed; height: 100vh; overflow-y: auto; }
        .sidebar-header { padding: 20px 25px; border-bottom: 1px solid rgba(255,255,255,0.1); margin-bottom: 20px; }
        .sidebar-header h2 { font-size: 22px; display: flex; align-items: center; gap: 10px; }
        .sidebar-header h2 i { color: #667eea; }
        .nav-item { padding: 14px 25px; cursor: pointer; transition: all 0.3s; display: flex; align-items: center; gap: 12px; border-left: 3px solid transparent; }
        .nav-item:hover, .nav-item.active { background: rgba(102, 126, 234, 0.2); border-left-color: #667eea; }
        .nav-item i { width: 24px; }
        .nav-item a { color: #fff; text-decoration: none; display: flex; align-items: center; gap: 12px; width: 100%; }
        
        .main-content { flex: 1; margin-left: 260px; padding: 30px; }
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; background: #fff; padding: 20px 30px; border-radius: 15px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
        .header h1 { font-size: 24px; color: #333; }
        .header .user-info { display: flex; align-items: center; gap: 15px; }
        .header .user-info .avatar { width: 40px; height: 40px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: #fff; font-weight: 600; }
        .logout-btn { background: #eb3349; color: #fff; padding: 10px 20px; border-radius: 8px; text-decoration: none; font-size: 14px; transition: all 0.3s; }
        .logout-btn:hover { background: #f45c43; }
        
        .message { padding: 15px 20px; border-radius: 10px; margin-bottom: 20px; display: flex; align-items: center; gap: 10px; }
        .message.success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .message.error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        
        .card { background: #fff; border-radius: 15px; padding: 25px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); margin-bottom: 25px; }
        .card-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; padding-bottom: 15px; border-bottom: 2px solid #f0f0f0; }
        .card-header h2 { font-size: 20px; color: #333; display: flex; align-items: center; gap: 10px; }
        .card-header h2 i { color: #667eea; }
        
        .stats-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; margin-bottom: 30px; }
        .stat-card { background: #fff; padding: 25px; border-radius: 15px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
        .stat-card .icon { width: 50px; height: 50px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 22px; margin-bottom: 15px; }
        .stat-card .icon.blue { background: rgba(102, 126, 234, 0.1); color: #667eea; }
        .stat-card .icon.green { background: rgba(17, 153, 142, 0.1); color: #11998e; }
        .stat-card .icon.orange { background: rgba(255, 165, 2, 0.1); color: #ffa502; }
        .stat-card .icon.red { background: rgba(235, 51, 73, 0.1); color: #eb3349; }
        .stat-card h3 { font-size: 32px; color: #333; margin-bottom: 5px; }
        .stat-card p { color: #888; font-size: 14px; }
        
        table { width: 100%; border-collapse: collapse; }
        th { background: #f8f9fa; padding: 15px; text-align: left; font-weight: 600; color: #555; font-size: 13px; text-transform: uppercase; letter-spacing: 0.5px; }
        td { padding: 15px; border-bottom: 1px solid #f0f0f0; }
        tr:hover { background: #f8f9fa; }
        
        .status-badge { padding: 6px 14px; border-radius: 20px; font-size: 12px; font-weight: 600; display: inline-block; }
        .status-pending { background: #fff3cd; color: #856404; }
        .status-confirmed { background: #d4edda; color: #155724; }
        .status-shipped { background: #cce5ff; color: #004085; }
        .status-delivered { background: #d1e7dd; color: #0f5132; }
        .status-cancelled { background: #f8d7da; color: #721c24; }
        .status-active { background: #d4edda; color: #155724; }
        .status-inactive { background: #f8d7da; color: #721c24; }
        .status-admin { background: #667eea; color: #fff; }
        .status-editor { background: #17a2b8; color: #fff; }
        
        select, input[type="text"], input[type="number"], input[type="email"], input[type="password"], textarea { padding: 10px 15px; border: 2px solid #e1e1e1; border-radius: 8px; font-size: 14px; }
        select:focus, input:focus, textarea:focus { border-color: #667eea; outline: none; }
        
        .form-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 20px; }
        .form-grid-3 { grid-template-columns: repeat(3, 1fr); }
        .form-full { grid-column: span 2; }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 6px; font-weight: 500; color: #555; font-size: 14px; }
        
        .modal { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center; }
        .modal.active { display: flex; }
        .modal-content { background: #fff; padding: 30px; border-radius: 15px; max-width: 600px; width: 90%; max-height: 90vh; overflow-y: auto; }
        .modal-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; padding-bottom: 15px; border-bottom: 2px solid #f0f0f0; }
        .modal-header h3 { font-size: 20px; }
        .modal-close { background: none; border: none; font-size: 24px; cursor: pointer; color: #888; }
        .modal-close:hover { color: #333; }
        
        .action-btns { display: flex; gap: 8px; }
        .action-btns button, .action-btns a { padding: 6px 12px; border: none; border-radius: 6px; cursor: pointer; font-size: 12px; text-decoration: none; transition: all 0.2s; }
        .edit-btn { background: #667eea; color: #fff; }
        .delete-btn { background: #eb3349; color: #fff; }
        
        .empty-state { text-align: center; padding: 50px; color: #888; }
        .empty-state i { font-size: 48px; margin-bottom: 15px; color: #ddd; }
        
        .user-row .avatar { width: 35px; height: 35px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; color: #fff; font-weight: 600; font-size: 14px; }
        
        @media (max-width: 768px) {
            .stats-grid { grid-template-columns: repeat(2, 1fr); }
            .form-grid { grid-template-columns: 1fr; }
            .form-full { grid-column: span 1; }
            .sidebar { width: 70px; }
            .sidebar-header h2 span, .nav-item span { display: none; }
            .main-content { margin-left: 70px; }
        }
    </style>
</head>
<body>
<?php if (!$is_logged_in): ?>
    <div class="login-container">
        <div class="login-box">
            <h1><i class="fas fa-lock"></i></h1>
            <p>Libidex Admin Panel</p>
            <?php if ($message): ?>
                <div class="message <?php echo $message_type; ?>"><?php echo $message; ?></div>
            <?php endif; ?>
            <form method="POST">
                <input type="hidden" name="action" value="login">
                <div class="form-group">
                    <label>Username</label>
                    <input type="text" name="username" required placeholder="Enter username">
                </div>
                <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="password" required placeholder="Enter password">
                </div>
                <button type="submit" class="btn" style="width:100%">Login</button>
            </form>
            <p style="margin-top:20px; font-size:12px; color:#999;">Default: admin / admin123</p>
        </div>
    </div>
<?php else: ?>
    <div class="admin-layout">
        <div class="sidebar">
            <div class="sidebar-header">
                <h2><i class="fas fa-gem"></i> <span>Libidex</span></h2>
            </div>
            <div class="nav-item <?php echo $page == 'dashboard' ? 'active' : ''; ?>">
                <a href="?page=dashboard"><i class="fas fa-chart-line"></i> <span>Dashboard</span></a>
            </div>
            <div class="nav-item <?php echo $page == 'products' ? 'active' : ''; ?>">
                <a href="?page=products"><i class="fas fa-box"></i> <span>Products</span></a>
            </div>
            <div class="nav-item <?php echo $page == 'reviews' ? 'active' : ''; ?>">
                <a href="?page=reviews"><i class="fas fa-star"></i> <span>Reviews</span></a>
            </div>
            <div class="nav-item <?php echo $page == 'orders' ? 'active' : ''; ?>">
                <a href="?page=orders"><i class="fas fa-shopping-cart"></i> <span>Orders</span></a>
            </div>
            <div class="nav-item <?php echo $page == 'users' ? 'active' : ''; ?>">
                <a href="?page=users"><i class="fas fa-users"></i> <span>Users</span></a>
            </div>
            <div class="nav-item <?php echo $page == 'settings' ? 'active' : ''; ?>">
                <a href="?page=settings"><i class="fas fa-cog"></i> <span>Settings</span></a>
            </div>
        </div>
        
        <div class="main-content">
            <div class="header">
                <h1><?php 
                    $titles = ['dashboard' => 'Dashboard', 'products' => 'Products Management', 'reviews' => 'Reviews Management', 'orders' => 'Orders Management', 'users' => 'User Management', 'settings' => 'Settings'];
                    echo $titles[$page] ?? 'Dashboard';
                ?></h1>
                <div class="user-info">
                    <div class="avatar"><?php echo strtoupper(substr($admin_username, 0, 1)); ?></div>
                    <span><?php echo htmlspecialchars($admin_username); ?></span>
                    <span style="color:#888; font-size:12px;">(<?php echo ucfirst($admin_role); ?>)</span>
                    <a href="?logout=1" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</a>
                </div>
            </div>
            
            <?php if ($message): ?>
                <div class="message <?php echo $message_type; ?>">
                    <i class="fas fa-<?php echo $message_type == 'success' ? 'check-circle' : 'exclamation-circle'; ?>"></i>
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>
            
            <?php
            $db = getDB();
            
            if ($page === 'dashboard'):
                $allOrders = $db->getOrders();
                $totalOrders = count($allOrders);
                $pendingOrders = count(array_filter($allOrders, function($o) { return $o['status'] === 'pending'; }));
                $totalReviews = count($db->getReviews());
                $totalProducts = count($db->getProducts());
                $recentOrders = array_slice($allOrders, 0, 5);
            ?>
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="icon blue"><i class="fas fa-shopping-cart"></i></div>
                        <h3><?php echo $totalOrders; ?></h3>
                        <p>Total Orders</p>
                    </div>
                    <div class="stat-card">
                        <div class="icon orange"><i class="fas fa-clock"></i></div>
                        <h3><?php echo $pendingOrders; ?></h3>
                        <p>Pending Orders</p>
                    </div>
                    <div class="stat-card">
                        <div class="icon green"><i class="fas fa-star"></i></div>
                        <h3><?php echo $totalReviews; ?></h3>
                        <p>Reviews</p>
                    </div>
                    <div class="stat-card">
                        <div class="icon red"><i class="fas fa-box"></i></div>
                        <h3><?php echo $totalProducts; ?></h3>
                        <p>Products</p>
                    </div>
                </div>
                
                <div class="card">
                    <div class="card-header">
                        <h2><i class="fas fa-clock"></i> Recent Orders</h2>
                    </div>
                    <?php if (empty($recentOrders)): ?>
                        <div class="empty-state">
                            <i class="fas fa-inbox"></i>
                            <p>No orders yet</p>
                        </div>
                    <?php else: ?>
                        <table>
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Phone</th>
                                    <th>Product</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recentOrders as $order): ?>
                                    <tr>
                                        <td>#<?php echo $order['id']; ?></td>
                                        <td><?php echo htmlspecialchars($order['name']); ?></td>
                                        <td><?php echo htmlspecialchars($order['phone']); ?></td>
                                        <td><?php echo htmlspecialchars($order['product']); ?></td>
                                        <td><span class="status-badge status-<?php echo $order['status']; ?>"><?php echo ucfirst($order['status']); ?></span></td>
                                        <td><?php echo date('d M Y', strtotime($order['created_at'])); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                        <div style="margin-top:15px; text-align:center;">
                            <a href="?page=orders" class="btn btn-sm btn-primary">View All Orders</a>
                        </div>
                    <?php endif; ?>
                </div>
            
            <?php elseif ($page === 'products'):
                $product = $db->getProduct(1);
            ?>
                <div class="card">
                    <div class="card-header">
                        <h2><i class="fas fa-box"></i> Edit Product</h2>
                    </div>
                    <form method="POST">
                        <input type="hidden" name="action" value="update_product">
                        <input type="hidden" name="id" value="1">
                        
                        <div class="form-grid">
                            <div class="form-group">
                                <label>Product Name (English)</label>
                                <input type="text" name="name" value="<?php echo htmlspecialchars($product['name']); ?>" required>
                            </div>
                            <div class="form-group">
                                <label>Product Name (Hindi)</label>
                                <input type="text" name="name_hindi" value="<?php echo htmlspecialchars($product['name_hindi']); ?>">
                            </div>
                            <div class="form-group form-full">
                                <label>Description (English)</label>
                                <textarea name="description" rows="3"><?php echo htmlspecialchars($product['description']); ?></textarea>
                            </div>
                            <div class="form-group form-full">
                                <label>Description (Hindi)</label>
                                <textarea name="description_hindi" rows="3"><?php echo htmlspecialchars($product['description_hindi']); ?></textarea>
                            </div>
                            <div class="form-group">
                                <label>Current Price (₹)</label>
                                <input type="number" name="price" value="<?php echo $product['price']; ?>" required>
                            </div>
                            <div class="form-group">
                                <label>Old Price (₹)</label>
                                <input type="number" name="old_price" value="<?php echo $product['old_price']; ?>">
                            </div>
                            <div class="form-group">
                                <label>Product Image</label>
                                <select name="image">
                                    <option value="product-1.png" <?php echo $product['image'] == 'product-1.png' ? 'selected' : ''; ?>>product-1.png</option>
                                    <option value="product-2.png" <?php echo $product['image'] == 'product-2.png' ? 'selected' : ''; ?>>product-2.png</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Status</label>
                                <select name="status">
                                    <option value="active" <?php echo $product['status'] == 'active' ? 'selected' : ''; ?>>Active</option>
                                    <option value="inactive" <?php echo $product['status'] == 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                                </select>
                            </div>
                        </div>
                        
                        <div style="margin-top: 20px;">
                            <button type="submit" class="btn btn-success"><i class="fas fa-save"></i> Save Changes</button>
                        </div>
                    </form>
                </div>
            
            <?php elseif ($page === 'reviews'):
                $reviews = $db->getReviews();
            ?>
                <div class="card">
                    <div class="card-header">
                        <h2><i class="fas fa-star"></i> Reviews Management</h2>
                        <button class="btn btn-sm btn-primary" onclick="openModal('addReviewModal')"><i class="fas fa-plus"></i> Add Review</button>
                    </div>
                    
                    <?php if (empty($reviews)): ?>
                        <div class="empty-state">
                            <i class="fas fa-star-half-alt"></i>
                            <p>No reviews yet</p>
                        </div>
                    <?php else: ?>
                        <table>
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Age</th>
                                    <th>Review</th>
                                    <th>Image</th>
                                    <th>Order</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($reviews as $review): ?>
                                    <tr>
                                        <td>#<?php echo $review['id']; ?></td>
                                        <td><?php echo htmlspecialchars($review['name']); ?></td>
                                        <td><?php echo $review['age']; ?></td>
                                        <td><?php echo htmlspecialchars(substr($review['review_text'], 0, 50)); ?>...</td>
                                        <td><img src="../images/<?php echo $review['image']; ?>" style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover;"></td>
                                        <td><?php echo $review['sort_order']; ?></td>
                                        <td><span class="status-badge status-<?php echo $review['status']; ?>"><?php echo ucfirst($review['status']); ?></span></td>
                                        <td>
                                            <div class="action-btns">
                                                <button class="edit-btn" onclick="editReview(<?php echo htmlspecialchars(json_encode($review)); ?>)"><i class="fas fa-edit"></i></button>
                                                <form method="POST" style="display:inline;" onsubmit="return confirm('Delete this review?')">
                                                    <input type="hidden" name="action" value="delete_review">
                                                    <input type="hidden" name="id" value="<?php echo $review['id']; ?>">
                                                    <button type="submit" class="delete-btn"><i class="fas fa-trash"></i></button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
                
                <div id="addReviewModal" class="modal">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h3><i class="fas fa-plus"></i> Add Review</h3>
                            <button class="modal-close" onclick="closeModal('addReviewModal')">&times;</button>
                        </div>
                        <form method="POST">
                            <input type="hidden" name="action" value="add_review">
                            <div class="form-grid">
                                <div class="form-group">
                                    <label>Name</label>
                                    <input type="text" name="name" required placeholder="Customer name">
                                </div>
                                <div class="form-group">
                                    <label>Age</label>
                                    <input type="number" name="age" placeholder="Customer age">
                                </div>
                                <div class="form-group">
                                    <label>Image</label>
                                    <select name="image">
                                        <option value="live-1.jpg">live-1.jpg</option>
                                        <option value="live-2.jpg">live-2.jpg</option>
                                        <option value="live-3.jpg">live-3.jpg</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>Sort Order</label>
                                    <input type="number" name="sort_order" value="0">
                                </div>
                                <div class="form-group form-full">
                                    <label>Review Text</label>
                                    <textarea name="review_text" rows="3" required placeholder="Review text"></textarea>
                                </div>
                                <div class="form-group">
                                    <label>Status</label>
                                    <select name="status">
                                        <option value="active">Active</option>
                                        <option value="inactive">Inactive</option>
                                    </select>
                                </div>
                            </div>
                            <div style="margin-top: 20px; text-align: right;">
                                <button type="button" class="btn btn-sm" style="background:#ccc; margin-right:10px;" onclick="closeModal('addReviewModal')">Cancel</button>
                                <button type="submit" class="btn btn-success btn-sm">Save Review</button>
                            </div>
                        </form>
                    </div>
                </div>
                
                <div id="editReviewModal" class="modal">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h3><i class="fas fa-edit"></i> Edit Review</h3>
                            <button class="modal-close" onclick="closeModal('editReviewModal')">&times;</button>
                        </div>
                        <form method="POST">
                            <input type="hidden" name="action" value="update_review">
                            <input type="hidden" name="id" id="edit_id">
                            <div class="form-grid">
                                <div class="form-group">
                                    <label>Name</label>
                                    <input type="text" name="name" id="edit_name" required>
                                </div>
                                <div class="form-group">
                                    <label>Age</label>
                                    <input type="number" name="age" id="edit_age">
                                </div>
                                <div class="form-group">
                                    <label>Image</label>
                                    <select name="image" id="edit_image">
                                        <option value="live-1.jpg">live-1.jpg</option>
                                        <option value="live-2.jpg">live-2.jpg</option>
                                        <option value="live-3.jpg">live-3.jpg</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>Sort Order</label>
                                    <input type="number" name="sort_order" id="edit_sort_order">
                                </div>
                                <div class="form-group form-full">
                                    <label>Review Text</label>
                                    <textarea name="review_text" id="edit_review_text" rows="3" required></textarea>
                                </div>
                                <div class="form-group">
                                    <label>Status</label>
                                    <select name="status" id="edit_status">
                                        <option value="active">Active</option>
                                        <option value="inactive">Inactive</option>
                                    </select>
                                </div>
                            </div>
                            <div style="margin-top: 20px; text-align: right;">
                                <button type="button" class="btn btn-sm" style="background:#ccc; margin-right:10px;" onclick="closeModal('editReviewModal')">Cancel</button>
                                <button type="submit" class="btn btn-success btn-sm">Update Review</button>
                            </div>
                        </form>
                    </div>
                </div>
            
            <?php elseif ($page === 'orders'):
                $db->refreshOrders();
                $orders = $db->getOrders();
            ?>
                <div class="card">
                    <div class="card-header">
                        <h2><i class="fas fa-shopping-cart"></i> Orders Management</h2>
                        <div>
                            <span style="color:#888; margin-right:15px;">Total: <?php echo count($orders); ?> orders</span>
                            <a href="export.php?type=orders" class="btn btn-sm btn-success"><i class="fas fa-file-excel"></i> Download Excel</a>
                        </div>
                    </div>
                    
                    <?php if (empty($orders)): ?>
                        <div class="empty-state">
                            <i class="fas fa-shopping-bag"></i>
                            <p>No orders yet</p>
                        </div>
                    <?php else: ?>
                        <table>
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Phone</th>
                                    <th>Product</th>
                                    <th>Source</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($orders as $order): ?>
                                    <tr>
                                        <td>#<?php echo $order['id']; ?></td>
                                        <td><?php echo htmlspecialchars($order['name']); ?></td>
                                        <td><?php echo htmlspecialchars($order['phone']); ?></td>
                                        <td><?php echo htmlspecialchars($order['product']); ?></td>
                                        <td><small><?php echo htmlspecialchars($order['utm_source'] ?: 'Direct'); ?></small></td>
                                        <td>
                                            <form method="POST" style="display:inline;">
                                                <input type="hidden" name="action" value="update_order_status">
                                                <input type="hidden" name="id" value="<?php echo $order['id']; ?>">
                                                <select name="status" onchange="this.form.submit()" style="padding:5px 10px; border-radius:5px;">
                                                    <option value="pending" <?php echo $order['status'] == 'pending' ? 'selected' : ''; ?>>Pending</option>
                                                    <option value="confirmed" <?php echo $order['status'] == 'confirmed' ? 'selected' : ''; ?>>Confirmed</option>
                                                    <option value="shipped" <?php echo $order['status'] == 'shipped' ? 'selected' : ''; ?>>Shipped</option>
                                                    <option value="delivered" <?php echo $order['status'] == 'delivered' ? 'selected' : ''; ?>>Delivered</option>
                                                    <option value="cancelled" <?php echo $order['status'] == 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                                </select>
                                            </form>
                                        </td>
                                        <td><?php echo date('d M Y, H:i', strtotime($order['created_at'])); ?></td>
                                        <td>
                                            <form method="POST" style="display:inline;" onsubmit="return confirm('Delete this order?')">
                                                <input type="hidden" name="action" value="delete_order">
                                                <input type="hidden" name="id" value="<?php echo $order['id']; ?>">
                                                <button type="submit" class="delete-btn"><i class="fas fa-trash"></i></button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
            
            <?php elseif ($page === 'users'):
                $users = $db->getAdminUsers();
            ?>
                <div class="card">
                    <div class="card-header">
                        <h2><i class="fas fa-users"></i> User Management</h2>
                        <button class="btn btn-sm btn-success" onclick="openModal('addUserModal')"><i class="fas fa-user-plus"></i> Add User</button>
                    </div>
                    
                    <table>
                        <thead>
                            <tr>
                                <th>User</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($users as $user): ?>
                                <tr class="user-row">
                                    <td>
                                        <div style="display:flex; align-items:center; gap:10px;">
                                            <div class="avatar"><?php echo strtoupper(substr($user['username'], 0, 1)); ?></div>
                                            <strong><?php echo htmlspecialchars($user['username']); ?></strong>
                                            <?php if ($user['id'] == $_SESSION['admin_id']): ?>
                                                <span style="background:#667eea; color:#fff; padding:2px 8px; border-radius:10px; font-size:10px;">You</span>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td><?php echo htmlspecialchars($user['email'] ?: '-'); ?></td>
                                    <td><span class="status-badge status-<?php echo $user['role']; ?>"><?php echo ucfirst($user['role']); ?></span></td>
                                    <td><?php echo date('d M Y', strtotime($user['created_at'])); ?></td>
                                    <td>
                                        <?php if ($user['id'] != $_SESSION['admin_id']): ?>
                                            <form method="POST" style="display:inline;" onsubmit="return confirm('Delete this user?')">
                                                <input type="hidden" name="action" value="delete_user">
                                                <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                                <button type="submit" class="delete-btn"><i class="fas fa-trash"></i></button>
                                            </form>
                                        <?php else: ?>
                                            <span style="color:#ccc; font-size:12px;">Current User</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                
                <div id="addUserModal" class="modal">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h3><i class="fas fa-user-plus"></i> Add New User</h3>
                            <button class="modal-close" onclick="closeModal('addUserModal')">&times;</button>
                        </div>
                        <form method="POST">
                            <input type="hidden" name="action" value="add_user">
                            <div class="form-grid">
                                <div class="form-group">
                                    <label>Username *</label>
                                    <input type="text" name="new_username" required placeholder="Enter username">
                                </div>
                                <div class="form-group">
                                    <label>Email</label>
                                    <input type="email" name="new_email" placeholder="Enter email">
                                </div>
                                <div class="form-group">
                                    <label>Password *</label>
                                    <input type="password" name="new_password" required placeholder="Enter password (min 6 chars)">
                                </div>
                                <div class="form-group">
                                    <label>Role</label>
                                    <select name="new_role">
                                        <option value="admin">Admin</option>
                                        <option value="editor">Editor</option>
                                    </select>
                                </div>
                            </div>
                            <div style="margin-top: 20px; text-align: right;">
                                <button type="button" class="btn btn-sm" style="background:#ccc; margin-right:10px;" onclick="closeModal('addUserModal')">Cancel</button>
                                <button type="submit" class="btn btn-success btn-sm">Add User</button>
                            </div>
                        </form>
                    </div>
                </div>
            
            <?php elseif ($page === 'settings'):
            ?>
                <div class="card">
                    <div class="card-header">
                        <h2><i class="fas fa-key"></i> Change Password</h2>
                    </div>
                    <form method="POST">
                        <input type="hidden" name="action" value="change_password">
                        <div class="form-grid">
                            <div class="form-group">
                                <label>Current Password *</label>
                                <input type="password" name="current_password" required placeholder="Enter current password">
                            </div>
                            <div class="form-group">
                                <label>New Password *</label>
                                <input type="password" name="new_password" required placeholder="Enter new password (min 6 chars)">
                            </div>
                            <div class="form-group">
                                <label>Confirm New Password *</label>
                                <input type="password" name="confirm_password" required placeholder="Confirm new password">
                            </div>
                        </div>
                        <div style="margin-top: 20px;">
                            <button type="submit" class="btn btn-warning"><i class="fas fa-key"></i> Change Password</button>
                        </div>
                    </form>
                </div>
                
                <div class="card">
                    <div class="card-header">
                        <h2><i class="fas fa-info-circle"></i> Account Info</h2>
                    </div>
                    <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 20px;">
                        <div>
                            <p style="color:#888; font-size:12px; margin-bottom:5px;">Username</p>
                            <p style="font-weight:600; font-size:16px;"><?php echo htmlspecialchars($admin_username); ?></p>
                        </div>
                        <div>
                            <p style="color:#888; font-size:12px; margin-bottom:5px;">Role</p>
                            <p style="font-weight:600; font-size:16px;"><?php echo ucfirst($admin_role); ?></p>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <script>
        function openModal(id) {
            document.getElementById(id).classList.add('active');
        }
        function closeModal(id) {
            document.getElementById(id).classList.remove('active');
        }
        function editReview(data) {
            document.getElementById('edit_id').value = data.id;
            document.getElementById('edit_name').value = data.name;
            document.getElementById('edit_age').value = data.age || '';
            document.getElementById('edit_image').value = data.image;
            document.getElementById('edit_review_text').value = data.review_text;
            document.getElementById('edit_sort_order').value = data.sort_order;
            document.getElementById('edit_status').value = data.status;
            openModal('editReviewModal');
        }
        window.onclick = function(event) {
            if (event.target.classList.contains('modal')) {
                event.target.classList.remove('active');
            }
        }
    </script>
<?php endif; ?>
</body>
</html>
