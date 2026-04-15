<?php
$host = 'dpg-d7ftuuernols73e56vc0-a.oregon-postgres.render.com';
$port = '5432';
$dbname = 'libidex_db';
$username = 'libidex_db_user';
$password = '905313';

try {
    $dsn = "pgsql:host=$host;port=$port;dbname=$dbname";
    $pdo = new PDO($dsn, $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "Connected to PostgreSQL!\n";
    
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
    echo "Created products table\n";
    
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
    echo "Created orders table\n";
    
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
    echo "Created reviews table\n";
    
    $pdo->exec("CREATE TABLE IF NOT EXISTS admin_users (
        id SERIAL PRIMARY KEY,
        username TEXT UNIQUE NOT NULL,
        password TEXT NOT NULL,
        email TEXT,
        role TEXT DEFAULT 'admin',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
    echo "Created admin_users table\n";
    
    // Check if products exist
    $check = $pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();
    if ($check == 0) {
        $pdo->exec("INSERT INTO products (name, name_hindi, price, old_price, status) 
        VALUES ('Libidex', 'अपनी क्षमता को उजागर करें', 2490, 4980, 'active')");
        echo "Added Libidex product\n";
    }
    
    $check = $pdo->query("SELECT COUNT(*) FROM reviews")->fetchColumn();
    if ($check == 0) {
        $pdo->exec("INSERT INTO reviews (name, age, review_text, status, sort_order) VALUES
        ('राजेश शर्मा', 42, 'बहुत बढ़िया उत्पाद!', 'active', 1),
        ('अमित वर्मा', 38, 'आत्मविश्वास वापस आ गया।', 'active', 2)");
        echo "Added default reviews\n";
    }
    
    $check = $pdo->query("SELECT COUNT(*) FROM admin_users")->fetchColumn();
    if ($check == 0) {
        $pdo->exec("INSERT INTO admin_users (username, password, role) 
        VALUES ('admin', '" . password_hash('admin123', PASSWORD_DEFAULT) . "', 'admin')");
        echo "Added admin user (admin/admin123)\n";
    }
    
    echo "\nPostgreSQL setup complete!\n";
    
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
