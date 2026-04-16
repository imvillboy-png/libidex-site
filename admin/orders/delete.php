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
$db->deleteOrder($id);

setFlash('success', 'Order deleted successfully!');
header('Location: index.php');
exit;
