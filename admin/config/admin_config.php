<?php
$ADMIN_CONFIG_FILE = __DIR__ . '/admin_config.json';

function getAdminConfig() {
    global $ADMIN_CONFIG_FILE;
    if (file_exists($ADMIN_CONFIG_FILE)) {
        $content = file_get_contents($ADMIN_CONFIG_FILE);
        return json_decode($content, true) ?: [];
    }
    return [];
}

function saveAdminConfig($config) {
    global $ADMIN_CONFIG_FILE;
    file_put_contents($ADMIN_CONFIG_FILE, json_encode($config, JSON_PRETTY_PRINT));
}

function getDefaultAdminHash() {
    return '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi';
}

function getAdminPasswordHash() {
    $config = getAdminConfig();
    if (isset($config['password_hash']) && !empty($config['password_hash'])) {
        return $config['password_hash'];
    }
    return getDefaultAdminHash();
}

function setAdminPasswordHash($hash) {
    $config = getAdminConfig();
    $config['password_hash'] = $hash;
    saveAdminConfig($config);
}
