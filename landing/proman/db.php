<?php
function getenv($key) {
    $value = isset($_ENV[$key]) ? $_ENV[$key] : (isset($_SERVER[$key]) ? $_SERVER[$key] : false);
    return $value;
}

$db_type = getenv('DB_TYPE') ?: 'pgsql';
$db_file = __DIR__ . '/../../data.db';

function getDB() {
    global $db_type, $db_file;
    
    if ($db_type === 'pgsql') {
        $host = getenv('DB_HOST') ?: 'dpg-d7g70hl8nd3s73a7jcag-a.oregon-postgres.render.com';
        $port = getenv('DB_PORT') ?: '5432';
        $dbname = getenv('DB_NAME') ?: 'libidex_db_npch';
        $username = getenv('DB_USER') ?: 'libidex_db_user';
        $password = getenv('DB_PASS') ?: 'Libidex2024!';
        
        $dsn = "pgsql:host=$host;port=$port;dbname=$dbname";
        $pdo = new PDO($dsn, $username, $password);
    } else {
        $pdo = new PDO("sqlite:$db_file");
    }
    
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    return $pdo;
}

function sanitize($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}
