<?php
require_once '../config/database.php';
require_once '../includes/auth.php';
require_once '../includes/helpers.php';

requireLogin();

$db = getDB();
$db->refreshOrders();

if (isset($_GET['export']) && $_GET['export'] === 'excel') {
    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment; filename="orders_' . date('Y-m-d') . '.xls"');
    header('Pragma: no-cache');
    header('Expires: 0');
    
    echo '<table border="1">';
    echo '<tr><th>ID</th><th>Product</th><th>Name</th><th>Phone</th><th>Country</th><th>Status</th><th>Date</th></tr>';
    
    $orders = $db->getOrders();
    
    foreach ($orders as $order) {
        echo '<tr>';
        echo '<td>' . $order['id'] . '</td>';
        echo '<td>' . htmlspecialchars($order['product'] ?: 'Libidex') . '</td>';
        echo '<td>' . htmlspecialchars($order['name']) . '</td>';
        echo '<td>' . htmlspecialchars($order['phone']) . '</td>';
        echo '<td>' . htmlspecialchars($order['country']) . '</td>';
        echo '<td>' . ucfirst($order['status']) . '</td>';
        echo '<td>' . $order['created_at'] . '</td>';
        echo '</tr>';
    }
    
    echo '</table>';
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['bulk_delete'])) {
        $ids = isset($_POST['selected_ids']) ? $_POST['selected_ids'] : [];
        if (!empty($ids)) {
            foreach ($ids as $id) {
                $db->deleteOrder($id);
            }
            setFlash('success', count($ids) . ' orders deleted successfully!');
        }
        header('Location: index.php');
        exit;
    }
    
    if (isset($_POST['bulk_status'])) {
        $ids = isset($_POST['selected_ids']) ? $_POST['selected_ids'] : [];
        $newStatus = sanitize($_POST['new_status'] ?? '');
        $validStatuses = ['pending', 'processing', 'shipped', 'delivered', 'cancelled'];
        
        if (!empty($ids) && in_array($newStatus, $validStatuses)) {
            foreach ($ids as $id) {
                $db->updateOrderStatus($id, $newStatus);
            }
            setFlash('success', count($ids) . ' orders updated successfully!');
        }
        header('Location: index.php');
        exit;
    }
}

$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$perPage = 20;

$statusFilter = isset($_GET['status']) ? sanitize($_GET['status']) : '';
$searchFilter = isset($_GET['search']) ? sanitize($_GET['search']) : '';
$productFilter = isset($_GET['product']) ? sanitize($_GET['product']) : '';

$allOrders = $db->getOrders();

$filteredOrders = array_filter($allOrders, function($order) use ($statusFilter, $searchFilter, $productFilter) {
    if ($statusFilter && $statusFilter !== 'all' && $order['status'] !== $statusFilter) return false;
    if ($productFilter && ($order['product'] ?? '') !== $productFilter) return false;
    if ($searchFilter) {
        $search = strtolower($searchFilter);
        $name = strtolower($order['name'] ?? '');
        $phone = strtolower($order['phone'] ?? '');
        if (strpos($name, $search) === false && strpos($phone, $search) === false) return false;
    }
    return true;
});

$totalOrders = count($filteredOrders);
$totalPages = ceil($totalOrders / $perPage);
$offset = ($page - 1) * $perPage;
$orders = array_slice(array_values($filteredOrders), $offset, $perPage);

$statusCounts = [
    'all' => count($allOrders),
    'pending' => count(array_filter($allOrders, fn($o) => ($o['status'] ?? '') === 'pending')),
    'processing' => count(array_filter($allOrders, fn($o) => ($o['status'] ?? '') === 'processing')),
    'shipped' => count(array_filter($allOrders, fn($o) => ($o['status'] ?? '') === 'shipped')),
    'delivered' => count(array_filter($allOrders, fn($o) => ($o['status'] ?? '') === 'delivered')),
    'cancelled' => count(array_filter($allOrders, fn($o) => ($o['status'] ?? '') === 'cancelled')),
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Orders - Libidex Admin</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
    <style>
        .bulk-actions { display: none; align-items: center; gap: 16px; padding: 16px 20px; background: var(--gray-50); border-radius: 12px; margin-bottom: 20px; }
        .bulk-actions.active { display: flex; }
        .bulk-actions .selected-count { font-weight: 600; color: var(--gray-700); }
        .checkbox-cell { width: 40px; }
        input[type="checkbox"] { width: 18px; height: 18px; cursor: pointer; accent-color: var(--primary); }
    </style>
</head>
<body>
    <div class="wrapper">
        <aside class="sidebar">
            <div class="sidebar-logo"><h2>Libidex</h2></div>
            <ul class="sidebar-nav">
                <li class="nav-item"><a href="../dashboard.php" class="nav-link">Dashboard</a></li>
                <li class="nav-item"><a href="index.php" class="nav-link active">Orders</a></li>
                <li class="nav-item"><a href="../products/" class="nav-link">Products</a></li>
                <li class="nav-item"><a href="../reviews/" class="nav-link">Reviews</a></li>
                <li class="nav-item"><a href="../users/" class="nav-link">Users</a></li>
                <li class="nav-item"><a href="../settings.php" class="nav-link">Settings</a></li>
            </ul>
        </aside>
        
        <main class="main-content">
            <div class="header">
                <h1>Orders</h1>
                <div class="header-actions">
                    <a href="index.php?export=excel" class="btn btn-success">Download Excel</a>
                    <div class="user-info">
                        <div class="user-avatar"><?php echo strtoupper(substr($_SESSION['admin_username'], 0, 1)); ?></div>
                    </div>
                    <a href="../logout.php" class="btn btn-logout">Logout</a>
                </div>
            </div>
            
            <?php $flash = getFlash(); if ($flash): ?>
                <div class="alert alert-<?php echo $flash['type'] === 'success' ? 'success' : 'error'; ?>">
                    <?php echo $flash['message']; ?>
                </div>
            <?php endif; ?>
            
            <div class="card" style="margin-bottom: 24px;">
                <div class="card-body" style="padding: 16px 24px;">
                    <form method="GET" style="display: flex; gap: 12px; align-items: center;">
                        <input type="text" name="search" class="form-control" placeholder="Search by name or phone..." value="<?php echo htmlspecialchars($searchFilter); ?>" style="width: 250px;">
                        <select name="product" class="form-control" style="width: 150px;">
                            <option value="">All Products</option>
                            <option value="Libidex" <?php echo $productFilter === 'Libidex' ? 'selected' : ''; ?>>Libidex</option>
                            <option value="Proman" <?php echo $productFilter === 'Proman' ? 'selected' : ''; ?>>Proman</option>
                        </select>
                        <select name="status" class="form-control" style="width: 150px;">
                            <option value="">All Status</option>
                            <option value="pending" <?php echo $statusFilter === 'pending' ? 'selected' : ''; ?>>Pending</option>
                            <option value="processing" <?php echo $statusFilter === 'processing' ? 'selected' : ''; ?>>Processing</option>
                            <option value="shipped" <?php echo $statusFilter === 'shipped' ? 'selected' : ''; ?>>Shipped</option>
                            <option value="delivered" <?php echo $statusFilter === 'delivered' ? 'selected' : ''; ?>>Delivered</option>
                            <option value="cancelled" <?php echo $statusFilter === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                        </select>
                        <button type="submit" class="btn btn-primary" style="padding: 10px 16px;">Filter</button>
                        <a href="index.php" class="btn btn-back" style="padding: 10px 16px;">Clear</a>
                    </form>
                </div>
            </div>
            
            <div style="display: flex; gap: 12px; margin-bottom: 24px; flex-wrap: wrap;">
                <a href="?status=all" class="btn <?php echo !$statusFilter || $statusFilter === 'all' ? 'btn-primary' : 'btn-back'; ?>">All (<?php echo $statusCounts['all']; ?>)</a>
                <a href="?status=pending" class="btn <?php echo $statusFilter === 'pending' ? 'btn-primary' : 'btn-back'; ?>">Pending (<?php echo $statusCounts['pending']; ?>)</a>
                <a href="?status=processing" class="btn <?php echo $statusFilter === 'processing' ? 'btn-primary' : 'btn-back'; ?>">Processing (<?php echo $statusCounts['processing']; ?>)</a>
                <a href="?status=shipped" class="btn <?php echo $statusFilter === 'shipped' ? 'btn-primary' : 'btn-back'; ?>">Shipped (<?php echo $statusCounts['shipped']; ?>)</a>
                <a href="?status=delivered" class="btn <?php echo $statusFilter === 'delivered' ? 'btn-primary' : 'btn-back'; ?>">Delivered (<?php echo $statusCounts['delivered']; ?>)</a>
                <a href="?status=cancelled" class="btn <?php echo $statusFilter === 'cancelled' ? 'btn-primary' : 'btn-back'; ?>">Cancelled (<?php echo $statusCounts['cancelled']; ?>)</a>
            </div>
            
            <form method="POST" id="bulkForm">
                <div class="bulk-actions" id="bulkActions">
                    <span class="selected-count"><span id="selectedCount">0</span> orders selected</span>
                    <select name="new_status" class="form-control" style="width: 150px;">
                        <option value="">Change Status</option>
                        <option value="pending">Pending</option>
                        <option value="processing">Processing</option>
                        <option value="shipped">Shipped</option>
                        <option value="delivered">Delivered</option>
                        <option value="cancelled">Cancelled</option>
                    </select>
                    <button type="submit" name="bulk_status" class="btn btn-sm btn-add">Update Status</button>
                    <button type="submit" name="bulk_delete" class="btn btn-sm btn-delete" onclick="return confirm('Delete selected orders?')">Delete Selected</button>
                </div>
                
                <div class="card">
                    <div class="card-header">
                        <h2>All Orders (<?php echo $totalOrders; ?>)</h2>
                        <a href="index.php?export=excel" class="btn btn-sm btn-success">Export Excel</a>
                    </div>
                    <div class="card-body">
                        <?php if (empty($orders)): ?>
                            <div class="empty-state">
                                <p>No orders found.</p>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table>
                                    <thead>
                                        <tr>
                                            <th class="checkbox-cell"><input type="checkbox" id="selectAll" onchange="toggleSelectAll()"></th>
                                            <th>ID</th>
                                            <th>Product</th>
                                            <th>Customer</th>
                                            <th>Phone</th>
                                            <th>Status</th>
                                            <th>Date</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($orders as $order): ?>
                                            <tr>
                                                <td class="checkbox-cell"><input type="checkbox" name="selected_ids[]" value="<?php echo $order['id']; ?>" onchange="updateBulkActions()"></td>
                                                <td>#<?php echo substr($order['id'], -6); ?></td>
                                                <td><span class="badge <?php echo ($order['product'] ?? '') === 'Proman' ? 'badge-product' : 'badge-libidex'; ?>"><?php echo htmlspecialchars($order['product'] ?: 'Libidex'); ?></span></td>
                                                <td><?php echo htmlspecialchars($order['name']); ?></td>
                                                <td><a href="tel:<?php echo htmlspecialchars($order['phone']); ?>"><?php echo htmlspecialchars($order['phone']); ?></a></td>
                                                <td><?php echo getOrderStatusBadge($order['status']); ?></td>
                                                <td><?php echo formatDate($order['created_at']); ?></td>
                                                <td>
                                                    <a href="view.php?id=<?php echo urlencode($order['id']); ?>" class="btn btn-sm btn-view">View</a>
                                                    <a href="delete.php?id=<?php echo urlencode($order['id']); ?>" class="btn btn-sm btn-delete" onclick="return confirm('Delete this order?')">Delete</a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </form>
        </main>
    </div>
    
    <script>
        function toggleSelectAll() {
            const checkboxes = document.querySelectorAll('input[name="selected_ids[]"]');
            checkboxes.forEach(cb => cb.checked = document.getElementById('selectAll').checked);
            updateBulkActions();
        }
        function updateBulkActions() {
            const selectedCount = document.querySelectorAll('input[name="selected_ids[]"]:checked').length;
            document.getElementById('selectedCount').textContent = selectedCount;
            document.getElementById('bulkActions').classList.toggle('active', selectedCount > 0);
        }
    </script>
</body>
</html>
