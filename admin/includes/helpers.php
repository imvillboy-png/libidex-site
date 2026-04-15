<?php
function sanitize($input) {
    if (is_array($input)) {
        return array_map('sanitize', $input);
    }
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

function generateToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verifyToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

function setFlash($type, $message) {
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function getFlash() {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

function formatDate($date) {
    return date('d M Y, h:i A', strtotime($date));
}

function formatPrice($price) {
    return number_format($price, 2) . ' INR';
}

function redirect($url) {
    header('Location: ' . $url);
    exit;
}

function uploadImage($file, $targetDir = '../images/') {
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    
    if (!in_array($file['type'], $allowedTypes)) {
        return ['success' => false, 'message' => 'Invalid file type. Only JPEG, PNG, GIF, and WebP are allowed.'];
    }
    
    if ($file['size'] > 5 * 1024 * 1024) {
        return ['success' => false, 'message' => 'File size must be less than 5MB.'];
    }
    
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = time() . '_' . uniqid() . '.' . $extension;
    $targetPath = $targetDir . $filename;
    
    if (move_uploaded_file($file['tmp_name'], $targetPath)) {
        return ['success' => true, 'filename' => $filename, 'path' => $targetPath];
    }
    
    return ['success' => false, 'message' => 'Failed to upload file.'];
}

function deleteImage($filename, $dir = '../images/') {
    $path = $dir . $filename;
    if (file_exists($path)) {
        return unlink($path);
    }
    return false;
}

function getOrderStatusBadge($status) {
    $badges = [
        'pending' => '<span class="badge badge-pending">Pending</span>',
        'processing' => '<span class="badge badge-processing">Processing</span>',
        'shipped' => '<span class="badge badge-shipped">Shipped</span>',
        'delivered' => '<span class="badge badge-delivered">Delivered</span>',
        'cancelled' => '<span class="badge badge-cancelled">Cancelled</span>'
    ];
    return $badges[$status] ?? $status;
}
