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

if ($id == $_SESSION['admin_id']) {
    setFlash('error', 'You cannot delete your own account!');
    header('Location: index.php');
    exit;
}

$pdo = getDB();

$stmt = $pdo->prepare("SELECT id FROM admin_users WHERE id = ?");
$stmt->execute([$id]);

if (!$stmt->fetch()) {
    header('Location: index.php');
    exit;
}

$stmt = $pdo->prepare("DELETE FROM admin_users WHERE id = ?");
$stmt->execute([$id]);

setFlash('success', 'User deleted successfully!');
header('Location: index.php');
exit;
