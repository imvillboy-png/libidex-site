<?php
$db_type = getenv('DB_TYPE') ?: 'pgsql';
$data_dir = '/tmp/libidex';
$db_file = $data_dir . '/data.db';

if (!is_dir($data_dir)) {
    @mkdir($data_dir, 0755, true);
}

class Database {
    private static $instance = null;
    private $pdo;

    private function __construct() {
        global $db_type, $db_file, $data_dir;
        
        if (!is_dir($data_dir)) {
            @mkdir($data_dir, 0755, true);
        }
        
        $db_type = getenv('DB_TYPE') ?: 'pgsql';
        $db_file = $data_dir . '/data.db';
        
        if ($db_type === 'pgsql') {
            $host = getenv('DB_HOST') ?: 'dpg-d7g70hl8nd3s73a7jcag-a.oregon-postgres.render.com';
            $port = getenv('DB_PORT') ?: '5432';
            $dbname = getenv('DB_NAME') ?: 'libidex_db_npch';
            $username = getenv('DB_USER') ?: 'libidex_db_user';
            $password = getenv('DB_PASS') ?: 'Libidex2024!';
            
            try {
                $dsn = "pgsql:host=$host;port=$port;dbname=$dbname;sslmode=require";
                $this->pdo = new PDO($dsn, $username, $password, array(
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_TIMEOUT => 30
                ));
            } catch (PDOException $e) {
                error_log("PostgreSQL connection failed: " . $e->getMessage());
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
    $db_type = getenv('DB_TYPE') ?: 'pgsql';
    
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
        
        $count = $pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();
        if ($count == 0) {
            $pdo->exec("INSERT INTO products (name, name_hindi, price, old_price, status) 
            VALUES ('Libidex', 'अपनी क्षमता को उजागर करें', 2490, 4980, 'active')");
            $pdo->exec("INSERT INTO products (name, name_hindi, price, old_price, status) 
            VALUES ('ProMan', 'पुरुषों के लिए शक्ति बढ़ाने वाला', 1990, 3990, 'active')");
        }
        
        $count = $pdo->query("SELECT COUNT(*) FROM reviews")->fetchColumn();
        if ($count == 0) {
            $pdo->exec("INSERT INTO reviews (name, age, review_text, status, sort_order) VALUES
            ('राजेश शर्मा', 42, 'बहुत बढ़िया उत्पाद!', 'active', 1),
            ('अमित वर्मा', 38, 'आत्मविश्वास वापस आ गया।', 'active', 2)");
        }
        
        $count = $pdo->query("SELECT COUNT(*) FROM admin_users")->fetchColumn();
        if ($count == 0) {
            $pdo->exec("INSERT INTO admin_users (username, password, role) 
            VALUES ('admin', '" . password_hash('admin123', PASSWORD_DEFAULT) . "', 'admin')");
        }
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
        
        $count = $pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();
        if ($count == 0) {
            $pdo->exec("INSERT INTO products (name, name_hindi, price, old_price, status) 
            VALUES ('Libidex', 'अपनी क्षमता को उजागर करें', 2490, 4980, 'active')");
            $pdo->exec("INSERT INTO products (name, name_hindi, price, old_price, status) 
            VALUES ('ProMan', 'पुरुषों के लिए शक्ति बढ़ाने वाला', 1990, 3990, 'active')");
        }
        
        $count = $pdo->query("SELECT COUNT(*) FROM reviews")->fetchColumn();
        if ($count == 0) {
            $pdo->exec("INSERT INTO reviews (name, age, review_text, status, sort_order) VALUES
            ('राजेश शर्मा', 42, 'बहुत बढ़िया उत्पाद!', 'active', 1),
            ('अमित वर्मा', 38, 'आत्मविश्वास वापस आ गया।', 'active', 2)");
        }
        
        $count = $pdo->query("SELECT COUNT(*) FROM admin_users")->fetchColumn();
        if ($count == 0) {
            $pdo->exec("INSERT INTO admin_users (username, password, role) 
            VALUES ('admin', '" . password_hash('admin123', PASSWORD_DEFAULT) . "', 'admin')");
        }
    }
}

function sanitize($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}
