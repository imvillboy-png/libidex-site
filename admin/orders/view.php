<?php
require_once '../config/database.php';
require_once '../includes/auth.php';
require_once '../includes/helpers.php';

requireLogin();

$id = $_GET['id'] ?? '';
if (!$id) {
    header('Location: index.php');
    exit;
}

$db = getDB();
$db->refreshOrders();

$allOrders = $db->getOrders();
$order = null;
foreach ($allOrders as $o) {
    if ($o['id'] === $id) {
        $order = $o;
        break;
    }
}

if (!$order) {
    header('Location: index.php');
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $status = sanitize($_POST['status'] ?? '');
    $validStatuses = ['pending', 'processing', 'shipped', 'delivered', 'cancelled'];
    if (!in_array($status, $validStatuses)) {
        $error = 'Invalid status selected.';
    } else {
        $db->updateOrderStatus($id, $status);
        $success = 'Order status updated successfully!';
        
        $db->refreshOrders();
        $allOrders = $db->getOrders();
        foreach ($allOrders as $o) {
            if ($o['id'] === $id) {
                $order = $o;
                break;
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
    <title>Order #<?php echo substr($order['id'], -6); ?> - Libidex Admin</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>
    <div class="wrapper">
        <aside class="sidebar">
            <div class="sidebar-logo"><h2>Libidex Admin</h2></div>
            <ul class="sidebar-nav">
                <li class="nav-item"><a href="../dashboard.php" class="nav-link">Dashboard</a></li>
                <li class="nav-item"><a href="index.php" class="nav-link active">Orders</a></li>
                <li class="nav-item"><a href="../products/" class="nav-link">Products</a></li>
                <li class="nav-item"><a href="../reviews/" class="nav-link">Reviews</a></li>
                <li class="nav-item"><a href="../users/" class="nav-link">Users</a></li>
            </ul>
        </aside>
        
        <main class="main-content">
            <div class="header">
                <h1>Order #<?php echo substr($order['id'], -6); ?></h1>
                <div class="header-actions">
                    <a href="../logout.php" class="btn btn-logout">Logout</a>
                </div>
            </div>
            
            <a href="index.php" class="back-link">&larr; Back to Orders</a>
            
            <?php if ($error): ?><div class="alert alert-error"><?php echo $error; ?></div><?php endif; ?>
            <?php if ($success): ?><div class="alert alert-success"><?php echo $success; ?></div><?php endif; ?>
            
            <div class="card" style="margin-bottom: 24px;">
                <div class="card-header">
                    <h2>Order Information</h2>
                    <div style="display: flex; gap: 12px; align-items: center;">
                        <?php echo getOrderStatusBadge($order['status']); ?>
                        <a href="delete.php?id=<?php echo urlencode($id); ?>" class="btn btn-sm btn-delete" onclick="return confirm('Delete this order?')">Delete Order</a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="order-detail-grid">
                        <div class="order-detail-item"><label>Order ID</label><span>#<?php echo substr($order['id'], -6); ?></span></div>
                        <div class="order-detail-item"><label>Product</label><span><strong><?php echo htmlspecialchars($order['product'] ?: 'Libidex'); ?></strong></span></div>
                        <div class="order-detail-item"><label>Customer Name</label><span><?php echo htmlspecialchars($order['name']); ?></span></div>
                        <div class="order-detail-item"><label>Phone</label><span><?php echo htmlspecialchars($order['phone']); ?></span></div>
                        <div class="order-detail-item"><label>Country</label><span><?php echo htmlspecialchars($order['country']); ?></span></div>
                        <div class="order-detail-item"><label>Date</label><span><?php echo formatDate($order['created_at']); ?></span></div>
                        <?php if ($order['utm_source']): ?>
                        <div class="order-detail-item"><label>UTM Source</label><span><?php echo htmlspecialchars($order['utm_source']); ?></span></div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header"><h2>Update Status</h2></div>
                <div class="card-body">
                    <form method="POST">
                        <div class="form-group">
                            <label for="status">Order Status</label>
                            <select id="status" name="status" class="form-control">
                                <option value="pending" <?php echo $order['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                <option value="processing" <?php echo $order['status'] === 'processing' ? 'selected' : ''; ?>>Processing</option>
                                <option value="shipped" <?php echo $order['status'] === 'shipped' ? 'selected' : ''; ?>>Shipped</option>
                                <option value="delivered" <?php echo $order['status'] === 'delivered' ? 'selected' : ''; ?>>Delivered</option>
                                <option value="cancelled" <?php echo $order['status'] === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary">Update Status</button>
                    </form>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
