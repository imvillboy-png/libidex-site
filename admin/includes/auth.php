<?php
session_start();

function isLoggedIn() {
    return isset($_SESSION['admin_id']) && !empty($_SESSION['admin_id']);
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit;
    }
}

function login($userId, $username) {
    $_SESSION['admin_id'] = $userId;
    $_SESSION['admin_username'] = $username;
    $_SESSION['login_time'] = time();
    $_SESSION['admin_logged_in'] = true;
}

function logout() {
    session_unset();
    session_destroy();
    header('Location: login.php');
    exit;
}

function getCurrentUser() {
    if (isLoggedIn()) {
        return [
            'id' => $_SESSION['admin_id'],
            'username' => $_SESSION['admin_username']
        ];
    }
    return null;
}
