<?php
session_start();
require_once __DIR__ . '/config/database.php';

if (!isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in']) {
    header('Location: index.php');
    exit;
}

$db = getDB();
$db->refreshOrders();

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=orders_' . date('Y-m-d') . '.csv');

$output = fopen('php://output', 'w');

fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
fputcsv($output, ['ID', 'Name', 'Phone', 'Product', 'Country', 'UTM Source', 'UTM Campaign', 'Status', 'Date']);

$orders = $db->getOrders();

foreach ($orders as $order) {
    fputcsv($output, [
        $order['id'],
        $order['name'],
        $order['phone'],
        $order['product'],
        $order['country'],
        $order['utm_source'] ?: 'Direct',
        $order['utm_campaign'] ?: '-',
        $order['status'],
        $order['created_at']
    ]);
}

fclose($output);
