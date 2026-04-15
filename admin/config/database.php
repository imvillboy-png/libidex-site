<?php
function getenv($key) {
    $value = isset($_ENV[$key]) ? $_ENV[$key] : (isset($_SERVER[$key]) ? $_SERVER[$key] : false);
    return $value;
}

$db_type = getenv('DB_TYPE') ?: 'sqlite';
$db_file = __DIR__ . '/../data.db';

class Database {
    private static $instance = null;
    private $pdo;

    private function __construct() {
        $db_type = getenv('DB_TYPE') ?: 'sqlite';
        $db_file = __DIR__ . '/../data.db';
        
        if ($db_type === 'pgsql') {
            $host = getenv('DB_HOST') ?: 'localhost';
            $port = getenv('DB_PORT') ?: '5432';
            $dbname = getenv('DB_NAME') ?: 'libidex_db';
            $username = getenv('DB_USER') ?: 'libidex_db_user';
            $password = getenv('DB_PASS') ?: '';
            
            try {
                $dsn = "pgsql:host=$host;port=$port;dbname=$dbname";
                $this->pdo = new PDO($dsn, $username, $password);
            } catch (PDOException $e) {
                $this->pdo = new PDO("sqlite:$db_file");
            }
        } else {
            $this->pdo = new PDO("sqlite:$db_file");
        }
        
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getConnection() {
        return $this->pdo;
    }
}

function getDB() {
    return Database::getInstance()->getConnection();
}

function initDB() {
    $pdo = getDB();
    $db_type = getenv('DB_TYPE') ?: 'sqlite';
    
    if ($db_type === 'pgsql') {
        $pdo->exec("CREATE TABLE IF NOT EXISTS products (
            id SERIAL PRIMARY KEY,
            name TEXT DEFAULT 'Libidex',
            name_hindi TEXT,
            description TEXT,
            description_hindi TEXT,
            price REAL DEFAULT 2490.00,
            old_price REAL,
            image TEXT DEFAULT 'product-1.png',
            image_secondary TEXT DEFAULT 'product-2.png',
            status TEXT DEFAULT 'active',
            stock INTEGER DEFAULT 100,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )");
        
        $pdo->exec("CREATE TABLE IF NOT EXISTS orders (
            id SERIAL PRIMARY KEY,
            name TEXT,
            phone TEXT,
            country TEXT DEFAULT 'IN',
            clickid TEXT,
            utm_campaign TEXT,
            utm_content TEXT,
            utm_medium TEXT,
            utm_source TEXT,
            product TEXT DEFAULT 'Libidex',
            status TEXT DEFAULT 'pending',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )");
        
        $pdo->exec("CREATE TABLE IF NOT EXISTS reviews (
            id SERIAL PRIMARY KEY,
            name TEXT,
            age INTEGER,
            image TEXT DEFAULT 'live-1.jpg',
            review_text TEXT,
            status TEXT DEFAULT 'active',
            sort_order INTEGER DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )");
        
        $pdo->exec("CREATE TABLE IF NOT EXISTS admin_users (
            id SERIAL PRIMARY KEY,
            username TEXT UNIQUE NOT NULL,
            password TEXT NOT NULL,
            email TEXT,
            role TEXT DEFAULT 'admin',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )");
    } else {
        $pdo->exec("CREATE TABLE IF NOT EXISTS products (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT DEFAULT 'Libidex',
            name_hindi TEXT,
            description TEXT,
            description_hindi TEXT,
            price REAL DEFAULT 2490.00,
            old_price REAL,
            image TEXT DEFAULT 'product-1.png',
            image_secondary TEXT DEFAULT 'product-2.png',
            status TEXT DEFAULT 'active',
            stock INTEGER DEFAULT 100,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )");
        
        $pdo->exec("CREATE TABLE IF NOT EXISTS orders (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT,
            phone TEXT,
            country TEXT DEFAULT 'IN',
            clickid TEXT,
            utm_campaign TEXT,
            utm_content TEXT,
            utm_medium TEXT,
            utm_source TEXT,
            product TEXT DEFAULT 'Libidex',
            status TEXT DEFAULT 'pending',
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )");
        
        $pdo->exec("CREATE TABLE IF NOT EXISTS reviews (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT,
            age INTEGER,
            image TEXT DEFAULT 'live-1.jpg',
            review_text TEXT,
            status TEXT DEFAULT 'active',
            sort_order INTEGER DEFAULT 0,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )");
        
        $pdo->exec("CREATE TABLE IF NOT EXISTS admin_users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            username TEXT UNIQUE NOT NULL,
            password TEXT NOT NULL,
            email TEXT,
            role TEXT DEFAULT 'admin',
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )");
    }
}

function sanitize($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}
