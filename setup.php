<?php
header('Content-Type: text/plain; charset=utf-8');

$host = getenv('DB_HOST') ?: 'dpg-d7ftuuernols73e56vc0-a.oregon-postgres.render.com';
$port = getenv('DB_PORT') ?: '5432';
$dbname = getenv('DB_NAME') ?: 'libidex_db';
$username = getenv('DB_USER') ?: 'libidex_db_user';
$password = getenv('DB_PASS') ?: '';

if (empty($password)) {
    echo "Error: DB_PASS not set. Please set database password in Render dashboard.";
    exit;
}

try {
    $dsn = "pgsql:host=$host;port=$port;dbname=$dbname";
    $pdo = new PDO($dsn, $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "Connected to database successfully!\n\n";
    
    $sql = "
    CREATE TABLE IF NOT EXISTS products (
        id SERIAL PRIMARY KEY,
        name VARCHAR(200) NOT NULL DEFAULT 'Libidex',
        name_hindi VARCHAR(200),
        description TEXT,
        description_hindi TEXT,
        price DECIMAL(10,2) NOT NULL DEFAULT 2490.00,
        old_price DECIMAL(10,2),
        image VARCHAR(255) DEFAULT 'product-1.png',
        image_secondary VARCHAR(255) DEFAULT 'product-2.png',
        status VARCHAR(20) DEFAULT 'active',
        stock INT DEFAULT 100,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    );
    
    CREATE TABLE IF NOT EXISTS orders (
        id SERIAL PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        phone VARCHAR(20) NOT NULL,
        country VARCHAR(10) DEFAULT 'IN',
        clickid VARCHAR(255),
        utm_campaign VARCHAR(255),
        utm_content VARCHAR(255),
        utm_medium VARCHAR(255),
        utm_source VARCHAR(255),
        product VARCHAR(255) DEFAULT 'Libidex',
        status VARCHAR(50) DEFAULT 'pending',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    );
    
    CREATE TABLE IF NOT EXISTS reviews (
        id SERIAL PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        age INT,
        image VARCHAR(255) DEFAULT 'live-1.jpg',
        review_text TEXT NOT NULL,
        status VARCHAR(20) DEFAULT 'active',
        sort_order INT DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    );
    
    INSERT INTO products (name, name_hindi, description, description_hindi, price, old_price, image, status) 
    VALUES ('Libidex', 'अपनी क्षमता को उजागर करें', 'Natural male enhancement capsules', 'पुरुषों के स्वास्थ्य के लिए प्राकृतिक कैप्सूल', 2490.00, 4980.00, 'product-1.png', 'active')
    ON CONFLICT DO NOTHING;
    
    INSERT INTO reviews (name, age, image, review_text, status, sort_order) VALUES
    ('राजेश शर्मा', 42, 'live-1.jpg', 'बहुत बढ़िया उत्पाद! 2 हफ्ते में ही असर दिखा।', 'active', 1),
    ('अमित वर्मा', 38, 'live-2.jpg', 'आत्मविश्वास वापस आ गया। Highly recommended!', 'active', 2),
    ('संजय पटेल', 45, 'live-3.jpg', 'पत्नी भी खुश हैं। अब कोई परेशानी नहीं।', 'active', 3)
    ON CONFLICT DO NOTHING;
    ";
    
    $pdo->exec($sql);
    
    echo "Tables created successfully!\n";
    echo "- products table\n";
    echo "- orders table\n";
    echo "- reviews table\n";
    echo "- Default data inserted\n";
    
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
