<?php

$dataDir = '/tmp/libidex';
$dbFile = $dataDir . '/admin.db';

if (!is_dir($dataDir)) {
    @mkdir($dataDir, 0755, true);
}

class Database {
    private static $instance = null;
    private $pdo;
    private $apiBase = 'https://libidex-site.onrender.com/api/orders.php';
    private $orders = [];

    private function __construct() {
        try {
            $this->pdo = new PDO("sqlite:$GLOBALS[dbFile]");
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->initTables();
        } catch (Exception $e) {
            error_log("Database error: " . $e->getMessage());
        }
        $this->loadOrders();
    }

    private function initTables() {
        $this->pdo->exec("CREATE TABLE IF NOT EXISTS admin_users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            username TEXT UNIQUE NOT NULL,
            password TEXT NOT NULL,
            email TEXT,
            role TEXT DEFAULT 'admin',
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )");
        
        $this->pdo->exec("CREATE TABLE IF NOT EXISTS products (
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
        
        $this->pdo->exec("CREATE TABLE IF NOT EXISTS reviews (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT,
            age INTEGER,
            image TEXT DEFAULT 'live-1.jpg',
            review_text TEXT,
            status TEXT DEFAULT 'active',
            sort_order INTEGER DEFAULT 0,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )");
        
        $count = $this->pdo->query("SELECT COUNT(*) FROM admin_users")->fetchColumn();
        if ($count == 0) {
            $this->pdo->exec("INSERT INTO admin_users (username, password, role) 
            VALUES ('admin', '" . password_hash('admin123', PASSWORD_DEFAULT) . "', 'admin')");
        }
        
        $count = $this->pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();
        if ($count == 0) {
            $this->pdo->exec("INSERT INTO products (name, name_hindi, price, old_price, status) 
            VALUES ('Libidex', 'अपनी क्षमता को उजागर करें', 2490, 4980, 'active')");
            $this->pdo->exec("INSERT INTO products (name, name_hindi, price, old_price, status) 
            VALUES ('ProMan', 'पुरुषों के लिए शक्ति बढ़ाने वाला', 1990, 3990, 'active')");
        }
        
        $count = $this->pdo->query("SELECT COUNT(*) FROM reviews")->fetchColumn();
        if ($count == 0) {
            $this->pdo->exec("INSERT INTO reviews (name, age, review_text, status, sort_order) VALUES
            ('राजेश शर्मा', 42, 'बहुत बढ़िया उत्पाद!', 'active', 1),
            ('अमित वर्मा', 38, 'आत्मविश्वास वापस आ गया।', 'active', 2)");
        }
    }

    private function loadOrders() {
        $ch = curl_init($this->apiBase);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $response = curl_exec($ch);
        curl_close($ch);
        
        if ($response) {
            $data = json_decode($response, true);
            if ($data && isset($data['orders'])) {
                $this->orders = $data['orders'];
            }
        }
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
    
    public function getOrders() {
        return $this->orders;
    }
    
    public function refreshOrders() {
        $this->loadOrders();
    }

    public function updateOrderStatus($id, $status) {
        $ch = curl_init($this->apiBase);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['id' => $id, 'status' => $status]));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_exec($ch);
        curl_close($ch);
        $this->loadOrders();
        return true;
    }

    public function deleteOrder($id) {
        $ch = curl_init($this->apiBase);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['id' => $id]));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_exec($ch);
        curl_close($ch);
        $this->loadOrders();
        return true;
    }

    public function getProducts() {
        return $this->pdo->query("SELECT * FROM products ORDER BY id")->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getProduct($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM products WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    public function updateProduct($data) {
        $stmt = $this->pdo->prepare("UPDATE products SET name=?, name_hindi=?, description=?, description_hindi=?, price=?, old_price=?, image=?, status=?, updated_at=CURRENT_TIMESTAMP WHERE id=?");
        $stmt->execute([
            $data['name'],
            $data['name_hindi'],
            $data['description'],
            $data['description_hindi'],
            floatval($data['price']),
            floatval($data['old_price']),
            $data['image'],
            $data['status'],
            intval($data['id'])
        ]);
        return true;
    }

    public function getReviews() {
        return $this->pdo->query("SELECT * FROM reviews ORDER BY sort_order")->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function addReview($data) {
        $stmt = $this->pdo->prepare("INSERT INTO reviews (name, age, image, review_text, status, sort_order) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $data['name'],
            intval($data['age']),
            $data['image'],
            $data['review_text'],
            $data['status'],
            intval($data['sort_order'])
        ]);
        return true;
    }
    
    public function updateReview($data) {
        $stmt = $this->pdo->prepare("UPDATE reviews SET name=?, age=?, image=?, review_text=?, status=?, sort_order=? WHERE id=?");
        $stmt->execute([
            $data['name'],
            intval($data['age']),
            $data['image'],
            $data['review_text'],
            $data['status'],
            intval($data['sort_order']),
            intval($data['id'])
        ]);
        return true;
    }
    
    public function deleteReview($id) {
        $stmt = $this->pdo->prepare("DELETE FROM reviews WHERE id=?");
        $stmt->execute([intval($id)]);
        return true;
    }

    public function getAdminUser($username) {
        $stmt = $this->pdo->prepare("SELECT * FROM admin_users WHERE username = ?");
        $stmt->execute([$username]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    public function getAdminUsers() {
        return $this->pdo->query("SELECT * FROM admin_users ORDER BY id")->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function addAdminUser($data) {
        $stmt = $this->pdo->prepare("INSERT INTO admin_users (username, password, email, role) VALUES (?, ?, ?, ?)");
        $stmt->execute([
            $data['username'],
            password_hash($data['password'], PASSWORD_DEFAULT),
            $data['email'],
            $data['role']
        ]);
        return true;
    }
    
    public function updatePassword($userId, $newPassword) {
        $stmt = $this->pdo->prepare("UPDATE admin_users SET password = ? WHERE id = ?");
        $stmt->execute([password_hash($newPassword, PASSWORD_DEFAULT), $userId]);
        return true;
    }
    
    public function deleteAdminUser($id) {
        $stmt = $this->pdo->prepare("DELETE FROM admin_users WHERE id = ?");
        $stmt->execute([intval($id)]);
        return true;
    }
}

function getDB() {
    return Database::getInstance();
}

function sanitize($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

function initDB() {
    getDB();
}
