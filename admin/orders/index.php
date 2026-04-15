<?php
require_once '../config/database.php';
require_once '../includes/auth.php';
require_once '../includes/helpers.php';

requireLogin();

$pdo = getDB();

if (isset($_GET['export']) && $_GET['export'] === 'excel') {
    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment; filename="orders_' . date('Y-m-d') . '.xls"');
    header('Pragma: no-cache');
    header('Expires: 0');
    
    echo '<table border="1">';
    echo '<tr>
            <th>ID</th>
            <th>Product</th>
            <th>Name</th>
            <th>Phone</th>
            <th>Country</th>
            <th>Status</th>
            <th>Click ID</th>
            <th>UTM Source</th>
            <th>UTM Medium</th>
            <th>UTM Campaign</th>
            <th>UTM Content</th>
            <th>Date</th>
            <th>Notes</th>
          </tr>';
    
    $statusFilter = isset($_GET['status']) ? sanitize($_GET['status']) : '';
    $searchFilter = isset($_GET['search']) ? sanitize($_GET['search']) : '';
    $productFilter = isset($_GET['product']) ? sanitize($_GET['product']) : '';
    
    $whereClause = "1=1";
    $params = [];
    
    if ($statusFilter) {
        $whereClause .= " AND status = ?";
        $params[] = $statusFilter;
    }
    
    if ($productFilter) {
        $whereClause .= " AND product = ?";
        $params[] = $productFilter;
    }
    
    if ($searchFilter) {
        $whereClause .= " AND (name LIKE ? OR phone LIKE ?)";
        $params[] = "%$searchFilter%";
        $params[] = "%$searchFilter%";
    }
    
    $query = "SELECT * FROM orders WHERE $whereClause ORDER BY created_at DESC";
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $orders = $stmt->fetchAll();
    
    foreach ($orders as $order) {
        echo '<tr>';
        echo '<td>' . $order['id'] . '</td>';
        echo '<td>' . htmlspecialchars($order['product'] ?: 'Libidex') . '</td>';
        echo '<td>' . htmlspecialchars($order['name']) . '</td>';
        echo '<td>' . htmlspecialchars($order['phone']) . '</td>';
        echo '<td>' . htmlspecialchars($order['country']) . '</td>';
        echo '<td>' . ucfirst($order['status']) . '</td>';
        echo '<td>' . htmlspecialchars($order['clickid']) . '</td>';
        echo '<td>' . htmlspecialchars($order['utm_source']) . '</td>';
        echo '<td>' . htmlspecialchars($order['utm_medium']) . '</td>';
        echo '<td>' . htmlspecialchars($order['utm_campaign']) . '</td>';
        echo '<td>' . htmlspecialchars($order['utm_content']) . '</td>';
        echo '<td>' . $order['created_at'] . '</td>';
        echo '<td>' . htmlspecialchars($order['notes'] ?? '') . '</td>';
        echo '</tr>';
    }
    
    echo '</table>';
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['bulk_delete'])) {
        $ids = isset($_POST['selected_ids']) ? $_POST['selected_ids'] : [];
        if (!empty($ids)) {
            $placeholders = implode(',', array_fill(0, count($ids), '?'));
            $stmt = $pdo->prepare("DELETE FROM orders WHERE id IN ($placeholders)");
            $stmt->execute($ids);
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
            $placeholders = implode(',', array_fill(0, count($ids), '?'));
            $stmt = $pdo->prepare("UPDATE orders SET status = ? WHERE id IN ($placeholders)");
            $stmt->execute(array_merge([$newStatus], $ids));
            setFlash('success', count($ids) . ' orders updated successfully!');
        }
        header('Location: index.php');
        exit;
    }
}

$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$perPage = 20;
$offset = ($page - 1) * $perPage;

$statusFilter = isset($_GET['status']) ? sanitize($_GET['status']) : '';
$searchFilter = isset($_GET['search']) ? sanitize($_GET['search']) : '';
$productFilter = isset($_GET['product']) ? sanitize($_GET['product']) : '';

$whereClause = "1=1";
$params = [];

if ($statusFilter) {
    $whereClause .= " AND status = ?";
    $params[] = $statusFilter;
}

if ($productFilter) {
    $whereClause .= " AND product = ?";
    $params[] = $productFilter;
}

if ($searchFilter) {
    $whereClause .= " AND (name LIKE ? OR phone LIKE ?)";
    $params[] = "%$searchFilter%";
    $params[] = "%$searchFilter%";
}

$countStmt = $pdo->prepare("SELECT COUNT(*) FROM orders WHERE $whereClause");
$countStmt->execute($params);
$totalOrders = $countStmt->fetchColumn();
$totalPages = ceil($totalOrders / $perPage);

$query = "SELECT * FROM orders WHERE $whereClause ORDER BY created_at DESC LIMIT $perPage OFFSET $offset";
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$orders = $stmt->fetchAll();

$statusCounts = [
    'all' => $pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn(),
    'pending' => $pdo->query("SELECT COUNT(*) FROM orders WHERE status = 'pending'")->fetchColumn(),
    'processing' => $pdo->query("SELECT COUNT(*) FROM orders WHERE status = 'processing'")->fetchColumn(),
    'shipped' => $pdo->query("SELECT COUNT(*) FROM orders WHERE status = 'shipped'")->fetchColumn(),
    'delivered' => $pdo->query("SELECT COUNT(*) FROM orders WHERE status = 'delivered'")->fetchColumn(),
    'cancelled' => $pdo->query("SELECT COUNT(*) FROM orders WHERE status = 'cancelled'")->fetchColumn(),
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
        .bulk-actions {
            display: none;
            align-items: center;
            gap: 16px;
            padding: 16px 20px;
            background: var(--gray-50);
            border-radius: 12px;
            margin-bottom: 20px;
        }
        .bulk-actions.active {
            display: flex;
        }
        .bulk-actions .selected-count {
            font-weight: 600;
            color: var(--gray-700);
        }
        .checkbox-cell {
            width: 40px;
        }
        input[type="checkbox"] {
            width: 18px;
            height: 18px;
            cursor: pointer;
            accent-color: var(--primary);
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <aside class="sidebar">
            <div class="sidebar-logo">
                <h2>Libidex</h2>
            </div>
            <ul class="sidebar-nav">
                <li class="nav-item">
                    <a href="../dashboard.php" class="nav-link">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                        </svg>
                        Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a href="index.php" class="nav-link active">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                        </svg>
                        Orders
                    </a>
                </li>
                <li class="nav-item">
                    <a href="../products/" class="nav-link">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                        </svg>
                        Products
                    </a>
                </li>
                <li class="nav-item">
                    <a href="../landing/" class="nav-link">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"/>
                        </svg>
                        Landing Pages
                    </a>
                </li>
                <li class="nav-item">
                    <a href="../reviews/" class="nav-link">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
                        </svg>
                        Reviews
                    </a>
                </li>
                <li class="nav-item">
                    <a href="../users/" class="nav-link">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                        </svg>
                        Users
                    </a>
                </li>
                <li class="nav-item">
                    <a href="../settings.php" class="nav-link">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                        Settings
                    </a>
                </li>
            </ul>
        </aside>
        
        <main class="main-content">
            <div class="header">
                <h1>Orders</h1>
                <div class="header-actions">
                    <a href="../index.html" target="_blank" class="btn btn-view-site">
                        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                        </svg>
                        View Website
                    </a>
                    <a href="index.php?export=excel<?php echo $statusFilter ? '&status=' . urlencode($statusFilter) : ''; ?><?php echo $searchFilter ? '&search=' . urlencode($searchFilter) : ''; ?><?php echo $productFilter ? '&product=' . urlencode($productFilter) : ''; ?>" class="btn btn-success">
                        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        Download Excel
                    </a>
                    <div class="user-info">
                        <div class="user-avatar"><?php echo strtoupper(substr($_SESSION['admin_username'], 0, 1)); ?></div>
                        <span><?php echo htmlspecialchars($_SESSION['admin_username']); ?></span>
                    </div>
                    <a href="../logout.php" class="btn btn-logout">Logout</a>
                </div>
            </div>
            
            <?php 
            $flash = getFlash();
            if ($flash): ?>
                <div class="alert alert-<?php echo $flash['type'] === 'success' ? 'success' : 'error'; ?>">
                    <?php echo $flash['message']; ?>
                </div>
            <?php endif; ?>
            
            <div class="card" style="margin-bottom: 24px;">
                <div class="card-body" style="padding: 16px 24px;">
                    <div style="display: flex; gap: 12px; flex-wrap: wrap; align-items: center;">
                        <form method="GET" style="display: flex; gap: 12px; align-items: center;">
                            <input type="text" name="search" class="form-control" placeholder="Search by name or phone..." 
                                   value="<?php echo htmlspecialchars($searchFilter); ?>" style="width: 250px;">
                            <select name="product" class="form-control" style="width: 150px;">
                                <option value="">All Products</option>
                                <option value="Libidex" <?php echo ($productFilter ?? '') === 'Libidex' ? 'selected' : ''; ?>>Libidex</option>
                                <option value="Proman" <?php echo ($productFilter ?? '') === 'Proman' ? 'selected' : ''; ?>>Proman</option>
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
            </div>
            
            <div style="display: flex; gap: 12px; margin-bottom: 24px; flex-wrap: wrap;">
                <a href="?status=all<?php echo $searchFilter ? '&search=' . urlencode($searchFilter) : ''; ?><?php echo $productFilter ? '&product=' . urlencode($productFilter) : ''; ?>" class="btn <?php echo !$statusFilter || $statusFilter === 'all' ? 'btn-primary' : 'btn-back'; ?>" style="padding: 8px 16px;">
                    All (<?php echo $statusCounts['all']; ?>)
                </a>
                <a href="?status=pending<?php echo $searchFilter ? '&search=' . urlencode($searchFilter) : ''; ?><?php echo $productFilter ? '&product=' . urlencode($productFilter) : ''; ?>" class="btn <?php echo $statusFilter === 'pending' ? 'btn-primary' : 'btn-back'; ?>" style="padding: 8px 16px;">
                    Pending (<?php echo $statusCounts['pending']; ?>)
                </a>
                <a href="?status=processing<?php echo $searchFilter ? '&search=' . urlencode($searchFilter) : ''; ?><?php echo $productFilter ? '&product=' . urlencode($productFilter) : ''; ?>" class="btn <?php echo $statusFilter === 'processing' ? 'btn-primary' : 'btn-back'; ?>" style="padding: 8px 16px;">
                    Processing (<?php echo $statusCounts['processing']; ?>)
                </a>
                <a href="?status=shipped<?php echo $searchFilter ? '&search=' . urlencode($searchFilter) : ''; ?><?php echo $productFilter ? '&product=' . urlencode($productFilter) : ''; ?>" class="btn <?php echo $statusFilter === 'shipped' ? 'btn-primary' : 'btn-back'; ?>" style="padding: 8px 16px;">
                    Shipped (<?php echo $statusCounts['shipped']; ?>)
                </a>
                <a href="?status=delivered<?php echo $searchFilter ? '&search=' . urlencode($searchFilter) : ''; ?><?php echo $productFilter ? '&product=' . urlencode($productFilter) : ''; ?>" class="btn <?php echo $statusFilter === 'delivered' ? 'btn-primary' : 'btn-back'; ?>" style="padding: 8px 16px;">
                    Delivered (<?php echo $statusCounts['delivered']; ?>)
                </a>
                <a href="?status=cancelled<?php echo $searchFilter ? '&search=' . urlencode($searchFilter) : ''; ?><?php echo $productFilter ? '&product=' . urlencode($productFilter) : ''; ?>" class="btn <?php echo $statusFilter === 'cancelled' ? 'btn-primary' : 'btn-back'; ?>" style="padding: 8px 16px;">
                    Cancelled (<?php echo $statusCounts['cancelled']; ?>)
                </a>
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
                    <button type="submit" name="bulk_delete" class="btn btn-sm btn-delete" onclick="return confirm('Are you sure you want to delete selected orders?')">Delete Selected</button>
                </div>
                
                <div class="card">
                    <div class="card-header">
                        <h2>All Orders (<?php echo $totalOrders; ?>)</h2>
                        <a href="index.php?export=excel<?php echo $statusFilter ? '&status=' . urlencode($statusFilter) : ''; ?><?php echo $searchFilter ? '&search=' . urlencode($searchFilter) : ''; ?><?php echo $productFilter ? '&product=' . urlencode($productFilter) : ''; ?>" class="btn btn-sm btn-success">
                            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            Export Excel
                        </a>
                    </div>
                    <div class="card-body">
                        <?php if (empty($orders)): ?>
                            <div class="empty-state">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                </svg>
                                <p>No orders found.</p>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table>
                                    <thead>
                                        <tr>
                                            <th class="checkbox-cell">
                                                <input type="checkbox" id="selectAll" onchange="toggleSelectAll()">
                                            </th>
                                            <th>ID</th>
                                            <th>Product</th>
                                            <th>Customer</th>
                                            <th>Phone</th>
                                            <th>Country</th>
                                            <th>Status</th>
                                            <th>Date</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($orders as $order): ?>
                                            <tr>
                                                <td class="checkbox-cell">
                                                    <input type="checkbox" name="selected_ids[]" value="<?php echo $order['id']; ?>" onchange="updateBulkActions()">
                                                </td>
                                                <td>#<?php echo $order['id']; ?></td>
                                                <td>
                                                    <span class="badge <?php echo ($order['product'] ?? '') === 'Proman' ? 'badge-product' : 'badge-libidex'; ?>">
                                                        <?php echo htmlspecialchars($order['product'] ?: 'Libidex'); ?>
                                                    </span>
                                                </td>
                                                <td><?php echo htmlspecialchars($order['name']); ?></td>
                                                <td>
                                                    <a href="tel:<?php echo htmlspecialchars($order['phone']); ?>" class="btn-call">
                                                        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                                                        </svg>
                                                        <?php echo htmlspecialchars($order['phone']); ?>
                                                    </a>
                                                </td>
                                                <td><?php echo htmlspecialchars($order['country']); ?></td>
                                                <td><?php echo getOrderStatusBadge($order['status']); ?></td>
                                                <td><?php echo formatDate($order['created_at']); ?></td>
                                                <td>
                                                    <div class="actions">
                                                        <a href="tel:<?php echo htmlspecialchars($order['phone']); ?>" class="btn btn-sm btn-call-small" title="Call Customer">
                                                            <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                                                            </svg>
                                                        </a>
                                                        <a href="view.php?id=<?php echo $order['id']; ?>" class="btn btn-sm btn-view">View</a>
                                                        <a href="delete.php?id=<?php echo $order['id']; ?>" class="btn btn-sm btn-delete" onclick="return confirm('Are you sure you want to delete this order?')">Delete</a>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            
                            <?php if ($totalPages > 1): ?>
                                <div class="pagination">
                                    <?php if ($page > 1): ?>
                                        <a href="?page=<?php echo $page - 1; ?>&status=<?php echo $statusFilter; ?>&search=<?php echo urlencode($searchFilter); ?>&product=<?php echo urlencode($productFilter); ?>">&laquo; Prev</a>
                                    <?php endif; ?>
                                    
                                    <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                                        <a href="?page=<?php echo $i; ?>&status=<?php echo $statusFilter; ?>&search=<?php echo urlencode($searchFilter); ?>&product=<?php echo urlencode($productFilter); ?>" 
                                           class="<?php echo $i === $page ? 'active' : ''; ?>"><?php echo $i; ?></a>
                                    <?php endfor; ?>
                                    
                                    <?php if ($page < $totalPages): ?>
                                        <a href="?page=<?php echo $page + 1; ?>&status=<?php echo $statusFilter; ?>&search=<?php echo urlencode($searchFilter); ?>&product=<?php echo urlencode($productFilter); ?>">Next &raquo;</a>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </form>
        </main>
    </div>
    
    <script>
        function toggleSelectAll() {
            const selectAll = document.getElementById('selectAll');
            const checkboxes = document.querySelectorAll('input[name="selected_ids[]"]');
            checkboxes.forEach(cb => cb.checked = selectAll.checked);
            updateBulkActions();
        }
        
        function updateBulkActions() {
            const checkboxes = document.querySelectorAll('input[name="selected_ids[]"]');
            const selectedCount = document.querySelectorAll('input[name="selected_ids[]"]:checked').length;
            const bulkActions = document.getElementById('bulkActions');
            const countSpan = document.getElementById('selectedCount');
            
            countSpan.textContent = selectedCount;
            
            if (selectedCount > 0) {
                bulkActions.classList.add('active');
            } else {
                bulkActions.classList.remove('active');
            }
        }
    </script>
</body>
</html>
