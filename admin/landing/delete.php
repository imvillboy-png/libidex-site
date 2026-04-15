<?php
require_once '../config/database.php';
require_once '../includes/auth.php';

requireLogin();

$id = intval($_GET['id'] ?? 0);
if ($id) {
    $pdo = getDB();
    $stmt = $pdo->prepare("DELETE FROM landing_pages WHERE id = ?");
    $stmt->execute([$id]);
    setFlash('success', 'Landing page deleted!');
}
header('Location: index.php');
exit;
