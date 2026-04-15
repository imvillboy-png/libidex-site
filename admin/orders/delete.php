<?php
require_once '../config/database.php';
require_once '../includes/auth.php';
require_once '../includes/helpers.php';

requireLogin();

$id = intval($_GET['id'] ?? 0);
if (!$id) {
    header('Location: index.php');
    exit;
}

$pdo = getDB();

$stmt = $pdo->prepare("SELECT id FROM orders WHERE id = ?");
$stmt->execute([$id]);
$order = $stmt->fetch();

if ($order) {
    $deleteStmt = $pdo->prepare("DELETE FROM orders WHERE id = ?");
    $deleteStmt->execute([$id]);
    setFlash('success', 'Order deleted successfully!');
} else {
    setFlash('error', 'Order not found.');
}

header('Location: index.php');
exit;
