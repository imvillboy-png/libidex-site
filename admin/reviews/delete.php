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
$stmt = $pdo->prepare("SELECT * FROM reviews WHERE id = ?");
$stmt->execute([$id]);
$review = $stmt->fetch();

if ($review) {
    if ($review['image']) {
        deleteImage($review['image'], '../images/');
    }
    
    $deleteStmt = $pdo->prepare("DELETE FROM reviews WHERE id = ?");
    $deleteStmt->execute([$id]);
    setFlash('success', 'Review deleted successfully!');
}

header('Location: index.php');
exit;
