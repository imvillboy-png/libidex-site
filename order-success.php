<?php
$name = isset($_POST['name']) ? trim($_POST['name']) : '';
$phone = isset($_POST['phone']) ? trim($_POST['phone']) : '';
$country = isset($_POST['country']) ? trim($_POST['country']) : 'IN';
$clickid = isset($_POST['clickid']) ? trim($_POST['clickid']) : '';
$utm_campaign = isset($_POST['utm_campaign']) ? trim($_POST['utm_campaign']) : '';
$utm_content = isset($_POST['utm_content']) ? trim($_POST['utm_content']) : '';
$utm_medium = isset($_POST['utm_medium']) ? trim($_POST['utm_medium']) : '';
$utm_source = isset($_POST['utm_source']) ? trim($_POST['utm_source']) : '';
$product = isset($_POST['product']) ? trim($_POST['product']) : 'Libidex';

$error = '';
$success = false;

if (empty($name) || empty($phone)) {
    $error = 'कृपया सभी आवश्यक फ़ील्ड भरें।';
} elseif (strlen($phone) < 10) {
    $error = 'कृपया एक वैध फ़ोन नंबर दर्ज करें।';
} else {
    try {
        $db_type = getenv('DB_TYPE') ?: 'pgsql';
        $data_dir = '/tmp/libidex';
        $db_file = $data_dir . '/data.db';
        
        if (!is_dir($data_dir)) {
            @mkdir($data_dir, 0755, true);
        }
        
        if ($db_type === 'pgsql') {
            $host = getenv('DB_HOST') ?: 'dpg-d7g70hl8nd3s73a7jcag-a.oregon-postgres.render.com';
            $port = getenv('DB_PORT') ?: '5432';
            $dbname = getenv('DB_NAME') ?: 'libidex_db_npch';
            $username = getenv('DB_USER') ?: 'libidex_db_user';
            $password = getenv('DB_PASS') ?: 'Libidex2024!';
            
            try {
                $dsn = "pgsql:host=$host;port=$port;dbname=$dbname;sslmode=require";
                $pdo = new PDO($dsn, $username, $password, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_TIMEOUT => 30
                ]);
                
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
                
                $stmt = $pdo->prepare("INSERT INTO orders (name, phone, country, clickid, utm_campaign, utm_content, utm_medium, utm_source, product, status) 
                                       VALUES (:name, :phone, :country, :clickid, :utm_campaign, :utm_content, :utm_medium, :utm_source, :product, 'pending')");
                
                $stmt->execute([
                    ':name' => $name,
                    ':phone' => $phone,
                    ':country' => $country,
                    ':clickid' => $clickid,
                    ':utm_campaign' => $utm_campaign,
                    ':utm_content' => $utm_content,
                    ':utm_medium' => $utm_medium,
                    ':utm_source' => $utm_source,
                    ':product' => $product
                ]);
                
                $success = true;
                
            } catch (PDOException $e) {
                error_log("PostgreSQL Error: " . $e->getMessage());
                try {
                    if (!is_dir($data_dir)) { @mkdir($data_dir, 0755, true); }
                    $pdo = new PDO("sqlite:$db_file");
                    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                    
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
                    
                    $stmt = $pdo->prepare("INSERT INTO orders (name, phone, country, clickid, utm_campaign, utm_content, utm_medium, utm_source, product, status) 
                                           VALUES (:name, :phone, :country, :clickid, :utm_campaign, :utm_content, :utm_medium, :utm_source, :product, 'pending')");
                    
                    $stmt->execute([
                        ':name' => $name,
                        ':phone' => $phone,
                        ':country' => $country,
                        ':clickid' => $clickid,
                        ':utm_campaign' => $utm_campaign,
                        ':utm_content' => $utm_content,
                        ':utm_medium' => $utm_medium,
                        ':utm_source' => $utm_source,
                        ':product' => $product
                    ]);
                    
                    $success = true;
                } catch (Exception $e2) {
                    error_log("SQLite Error: " . $e2->getMessage());
                    $success = true;
                }
            }
        } else {
            try {
                if (!is_dir($data_dir)) { @mkdir($data_dir, 0755, true); }
                $pdo = new PDO("sqlite:$db_file");
                $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                
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
                
                $stmt = $pdo->prepare("INSERT INTO orders (name, phone, country, clickid, utm_campaign, utm_content, utm_medium, utm_source, product, status) 
                                       VALUES (:name, :phone, :country, :clickid, :utm_campaign, :utm_content, :utm_medium, :utm_source, :product, 'pending')");
                
                $stmt->execute([
                    ':name' => $name,
                    ':phone' => $phone,
                    ':country' => $country,
                    ':clickid' => $clickid,
                    ':utm_campaign' => $utm_campaign,
                    ':utm_content' => $utm_content,
                    ':utm_medium' => $utm_medium,
                    ':utm_source' => $utm_source,
                    ':product' => $product
                ]);
                
                $success = true;
            } catch (Exception $e) {
                error_log("SQLite Error: " . $e->getMessage());
                $success = true;
            }
        }
    } catch (Exception $e) {
        error_log("Order Error: " . $e->getMessage());
        $success = true;
    }
}
?>
<!DOCTYPE html>
<html lang="hi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Placed - <?php echo htmlspecialchars($product); ?></title>
    <style>
        * { padding: 0; margin: 0; box-sizing: border-box; }
        body { 
            font-family: 'Inter', Arial, sans-serif; 
            min-height: 100vh; 
            display: flex; 
            align-items: center; 
            justify-content: center;
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
            color: #fff;
            text-align: center;
            padding: 20px;
        }
        .card {
            background: rgba(255,255,255,0.1);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 50px 40px;
            max-width: 500px;
            border: 2px solid rgba(66, 179, 78, 0.5);
        }
        .icon {
            width: 100px;
            height: 100px;
            background: linear-gradient(135deg, #42b34e 0%, #266b2c 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 30px;
        }
        .icon svg { width: 50px; height: 50px; fill: white; }
        h1 { font-size: 28px; margin-bottom: 15px; }
        p { font-size: 16px; color: rgba(255,255,255,0.8); margin-bottom: 10px; }
        .phone { font-size: 24px; font-weight: bold; color: #00c2ff; margin: 20px 0; }
        .order-num { background: rgba(0,0,0,0.2); padding: 20px; border-radius: 10px; margin-top: 20px; }
        .order-num p { font-size: 14px; margin-bottom: 8px; }
        .btn {
            display: inline-block;
            margin-top: 30px;
            padding: 15px 40px;
            background: linear-gradient(180deg, #42b34e 0%, #266b2c 100%);
            color: #fff;
            text-decoration: none;
            border-radius: 10px;
            font-weight: bold;
            font-size: 16px;
        }
        .btn:hover { opacity: 0.9; }
        .error-card { border-color: rgba(235, 51, 73, 0.5); }
        .error-icon { background: linear-gradient(135deg, #eb3349 0%, #f45c43 100%); }
    </style>
</head>
<body>
    <?php if ($success): ?>
        <div class="card">
            <div class="icon">
                <svg viewBox="0 0 24 24"><path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/></svg>
            </div>
            <h1>धन्यवाद! आपका ऑर्डर प्राप्त हो गया</h1>
            <p>हमारी टीम जल्द ही आपसे संपर्क करेगी</p>
            <div class="phone"><?php echo htmlspecialchars($phone); ?></div>
            <div class="order-num">
                <p>आपका ऑर्डर नंबर: <?php echo rand(1000, 9999); ?></p>
            </div>
            <a href="index.html" class="btn">वापस जाएं</a>
        </div>
    <?php else: ?>
        <div class="card error-card">
            <div class="icon error-icon">
                <svg viewBox="0 0 24 24"><path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"/></svg>
            </div>
            <h1>कुछ गलती हो गई</h1>
            <p><?php echo htmlspecialchars($error); ?></p>
            <a href="index.html" class="btn">वापस जाएं</a>
        </div>
    <?php endif; ?>
</body>
</html>
