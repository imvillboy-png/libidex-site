<?php
// Render PostgreSQL Setup Script
// Run this once to initialize the database

error_reporting(E_ALL);
header('Content-Type: text/plain; charset=utf-8');

// Get connection details from environment
$host = getenv('DB_HOST') ?: 'dpg-d7ftuuernols73e56vc0-a.oregon-postgres.render.com';
$port = getenv('DB_PORT') ?: '5432';
$dbname = getenv('DB_NAME') ?: 'libidex_db';
$username = getenv('DB_USER') ?: 'libidex_db_user';
$password = getenv('DB_PASS') ?: '905313';

echo "Connecting to PostgreSQL at $host:$port/$dbname\n";

try {
    $dsn = "pgsql:host=$host;port=$port;dbname=$dbname";
    $pdo = new PDO($dsn, $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "Connected!\n\n";
    
    // Create tables
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
    echo "products table ready\n";
    
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
    echo "orders table ready\n";
    
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
    echo "reviews table ready\n";
    
    $pdo->exec("CREATE TABLE IF NOT EXISTS admin_users (
        id SERIAL PRIMARY KEY,
        username TEXT UNIQUE NOT NULL,
        password TEXT NOT NULL,
        email TEXT,
        role TEXT DEFAULT 'admin',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
    echo "admin_users table ready\n";
    
    // Insert default data
    $count = $pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();
    if ($count == 0) {
        $pdo->exec("INSERT INTO products (name, name_hindi, price, old_price, status) 
        VALUES ('Libidex', 'अपनी क्षमता को उजागर करें', 2490, 4980, 'active')");
        $pdo->exec("INSERT INTO products (name, name_hindi, price, old_price, status) 
        VALUES ('ProMan', 'पुरुषों के लिए शक्ति बढ़ाने वाला', 1990, 3990, 'active')");
        echo "Default products added\n";
    }
    
    $count = $pdo->query("SELECT COUNT(*) FROM reviews")->fetchColumn();
    if ($count == 0) {
        $pdo->exec("INSERT INTO reviews (name, age, review_text, status, sort_order) VALUES
        ('राजेश शर्मा', 42, 'बहुत बढ़िया उत्पाद! मैं बहुत खुश हूं।', 'active', 1),
        ('अमित वर्मा', 38, 'आत्मविश्वास वापस आ गया। धन्यवाद!', 'active', 2),
        ('सुनील पटेल', 45, 'अच्छा प्रोडक्ट है।', 'active', 3)");
        echo "Default reviews added\n";
    }
    
    $count = $pdo->query("SELECT COUNT(*) FROM admin_users")->fetchColumn();
    if ($count == 0) {
        $pdo->exec("INSERT INTO admin_users (username, password, role) 
        VALUES ('admin', '" . password_hash('admin123', PASSWORD_DEFAULT) . "', 'admin')");
        echo "Admin user added (admin/admin123)\n";
    }
    
    echo "\nDatabase setup complete!\n";
    echo "You can now access the admin panel.\n";
    
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "\nNote: Render's free PostgreSQL may not allow external connections.\n";
    echo "Try connecting from within the Render service.\n";
}
