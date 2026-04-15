<?php
session_start();

$db_file = __DIR__ . '/../data.db';
$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'login') {
            $username = $_POST['username'] ?? '';
            $password = $_POST['password'] ?? '';
            
            if ($username === 'admin' && $password === 'admin123') {
                $_SESSION['admin_logged_in'] = true;
                header('Location: index.php');
                exit;
            } else {
                $message = 'Invalid credentials!';
                $message_type = 'error';
            }
        } elseif ($_POST['action'] === 'logout') {
            session_destroy();
            header('Location: index.php');
            exit;
        } elseif ($_POST['action'] === 'update_status') {
            $order_id = $_POST['order_id'] ?? 0;
            $new_status = $_POST['new_status'] ?? 'pending';
            
            try {
                $pdo = new PDO("sqlite:$db_file");
                $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                $stmt = $pdo->prepare("UPDATE orders SET status = ? WHERE id = ?");
                $stmt->execute([$new_status, $order_id]);
                $message = 'Status updated successfully!';
                $message_type = 'success';
            } catch (PDOException $e) {
                $message = 'Error updating status';
                $message_type = 'error';
            }
        } elseif ($_POST['action'] === 'delete_order') {
            $order_id = $_POST['order_id'] ?? 0;
            
            try {
                $pdo = new PDO("sqlite:$db_file");
                $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                $stmt = $pdo->prepare("DELETE FROM orders WHERE id = ?");
                $stmt->execute([$order_id]);
                $message = 'Order deleted successfully!';
                $message_type = 'success';
            } catch (PDOException $e) {
                $message = 'Error deleting order';
                $message_type = 'error';
            }
        }
    }
}

$is_logged_in = $_SESSION['admin_logged_in'] ?? false;
$orders = [];

if ($is_logged_in && file_exists($db_file)) {
    try {
        $pdo = new PDO("sqlite:$db_file");
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $orders = $pdo->query("SELECT * FROM orders ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $message = 'Database error: ' . $e->getMessage();
        $message_type = 'error';
    }
}
?>
<!DOCTYPE html>
<html lang="hi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Libidex Admin - Orders</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f5f5f5; }
        .container { max-width: 1200px; margin: 0 auto; padding: 20px; }
        
        .header { background: #333; color: #fff; padding: 15px 20px; display: flex; justify-content: space-between; align-items: center; }
        .header h1 { font-size: 24px; }
        .header a { color: #fff; text-decoration: none; }
        
        .login-box { max-width: 400px; margin: 100px auto; background: #fff; padding: 40px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .login-box h2 { text-align: center; margin-bottom: 30px; color: #333; }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; margin-bottom: 5px; color: #555; }
        .form-group input { width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 5px; font-size: 14px; }
        .btn { background: #28a745; color: #fff; padding: 12px 30px; border: none; border-radius: 5px; cursor: pointer; font-size: 16px; }
        .btn:hover { background: #218838; }
        .btn-danger { background: #dc3545; }
        .btn-danger:hover { background: #c82333; }
        .btn-primary { background: #007bff; }
        .btn-primary:hover { background: #0056b3; }
        
        .message { padding: 15px; margin-bottom: 20px; border-radius: 5px; }
        .message.success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .message.error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        
        .stats { display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; margin-bottom: 30px; }
        .stat-card { background: #fff; padding: 20px; border-radius: 10px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); text-align: center; }
        .stat-card h3 { font-size: 36px; color: #333; }
        .stat-card p { color: #666; margin-top: 5px; }
        .stat-card.pending h3 { color: #ffc107; }
        .stat-card.confirmed h3 { color: #28a745; }
        .stat-card.shipped h3 { color: #007bff; }
        .stat-card.delivered h3 { color: #17a2b8; }
        
        table { width: 100%; background: #fff; border-radius: 10px; overflow: hidden; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        th { background: #333; color: #fff; padding: 15px; text-align: left; }
        td { padding: 15px; border-bottom: 1px solid #eee; }
        tr:last-child td { border-bottom: none; }
        tr:hover { background: #f9f9f9; }
        
        .status-badge { padding: 5px 15px; border-radius: 20px; font-size: 12px; font-weight: bold; }
        .status-pending { background: #ffc107; color: #000; }
        .status-confirmed { background: #28a745; color: #fff; }
        .status-shipped { background: #007bff; color: #fff; }
        .status-delivered { background: #17a2b8; color: #fff; }
        .status-cancelled { background: #dc3545; color: #fff; }
        
        select { padding: 8px; border: 1px solid #ddd; border-radius: 5px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>📦 Libidex Admin</h1>
        <?php if ($is_logged_in): ?>
            <a href="?logout=1">Logout</a>
        <?php endif; ?>
    </div>
    
    <div class="container">
        <?php if ($message): ?>
            <div class="message <?php echo $message_type; ?>"><?php echo $message; ?></div>
        <?php endif; ?>
        
        <?php if (!$is_logged_in): ?>
            <div class="login-box">
                <h2>🔐 Admin Login</h2>
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
                <p style="text-align:center; margin-top:20px; color:#666;">Default: admin / admin123</p>
            </div>
        <?php else: ?>
            <?php
            $total = count($orders);
            $pending = count(array_filter($orders, fn($o) => $o['status'] === 'pending'));
            $confirmed = count(array_filter($orders, fn($o) => $o['status'] === 'confirmed'));
            $shipped = count(array_filter($orders, fn($o) => $o['status'] === 'shipped'));
            $delivered = count(array_filter($orders, fn($o) => $o['status'] === 'delivered'));
            ?>
            
            <div class="stats">
                <div class="stat-card">
                    <h3><?php echo $total; ?></h3>
                    <p>Total Orders</p>
                </div>
                <div class="stat-card pending">
                    <h3><?php echo $pending; ?></h3>
                    <p>Pending</p>
                </div>
                <div class="stat-card confirmed">
                    <h3><?php echo $confirmed; ?></h3>
                    <p>Confirmed</p>
                </div>
                <div class="stat-card delivered">
                    <h3><?php echo $delivered; ?></h3>
                    <p>Delivered</p>
                </div>
            </div>
            
            <?php if (empty($orders)): ?>
                <div class="message">No orders yet!</div>
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
                                <td>
                                    <form method="POST" style="display:inline;">
                                        <input type="hidden" name="action" value="update_status">
                                        <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                        <select name="new_status" onchange="this.form.submit()">
                                            <option value="pending" <?php echo $order['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                            <option value="confirmed" <?php echo $order['status'] === 'confirmed' ? 'selected' : ''; ?>>Confirmed</option>
                                            <option value="shipped" <?php echo $order['status'] === 'shipped' ? 'selected' : ''; ?>>Shipped</option>
                                            <option value="delivered" <?php echo $order['status'] === 'delivered' ? 'selected' : ''; ?>>Delivered</option>
                                            <option value="cancelled" <?php echo $order['status'] === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                        </select>
                                    </form>
                                </td>
                                <td><?php echo $order['created_at']; ?></td>
                                <td>
                                    <form method="POST" style="display:inline;" onsubmit="return confirm('Delete this order?')">
                                        <input type="hidden" name="action" value="delete_order">
                                        <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                        <button type="submit" class="btn btn-danger" style="padding:5px 10px; font-size:12px;">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</body>
</html>
