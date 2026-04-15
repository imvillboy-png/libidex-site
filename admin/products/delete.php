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
$stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
$stmt->execute([$id]);
$product = $stmt->fetch();

if ($product) {
    if ($product['image']) {
        deleteImage($product['image'], '../images/');
    }
    if ($product['image_secondary']) {
        deleteImage($product['image_secondary'], '../images/');
    }
    
    $deleteStmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
    $deleteStmt->execute([$id]);
}

setFlash('success', 'Product deleted successfully!');
header('Location: index.php');
exit;
