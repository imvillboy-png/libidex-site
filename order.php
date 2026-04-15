<?php
header('Content-Type: application/json; charset=utf-8');

$db_type = getenv('DB_TYPE') ?: 'sqlite';
$db_file = __DIR__ . '/data.db';

function getDB() {
    global $db_type, $db_file;
    
    if ($db_type === 'pgsql') {
        $host = getenv('DB_HOST') ?: 'dpg-d7ftuuernols73e56vc0-a.oregon-postgres.render.com';
        $port = getenv('DB_PORT') ?: '5432';
        $dbname = getenv('DB_NAME') ?: 'libidex_db';
        $username = getenv('DB_USER') ?: 'libidex_db_user';
        $password = getenv('DB_PASS') ?: '905313';
        
        $dsn = "pgsql:host=$host;port=$port;dbname=$dbname";
        $pdo = new PDO($dsn, $username, $password);
    } else {
        $pdo = new PDO("sqlite:$db_file");
    }
    
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    return $pdo;
}

$name = isset($_POST['name']) ? trim($_POST['name']) : '';
$phone = isset($_POST['phone']) ? trim($_POST['phone']) : '';
$country = isset($_POST['country']) ? trim($_POST['country']) : 'IN';
$clickid = isset($_POST['clickid']) ? trim($_POST['clickid']) : '';
$utm_campaign = isset($_POST['utm_campaign']) ? trim($_POST['utm_campaign']) : '';
$utm_content = isset($_POST['utm_content']) ? trim($_POST['utm_content']) : '';
$utm_medium = isset($_POST['utm_medium']) ? trim($_POST['utm_medium']) : '';
$utm_source = isset($_POST['utm_source']) ? trim($_POST['utm_source']) : '';
$product = isset($_POST['product']) ? trim($_POST['product']) : 'Libidex';

if (empty($name) || empty($phone)) {
    echo json_encode(['success' => false, 'message' => 'कृपया सभी आवश्यक फ़ील्ड भरें।']);
    exit;
}

if (!preg_match('/^\+91[0-9]{10}$/', $phone)) {
    echo json_encode(['success' => false, 'message' => 'कृपया एक वैध फ़ोन नंबर दर्ज करें।']);
    exit;
}

try {
    $pdo = getDB();
    
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
    
    echo json_encode(['success' => true, 'message' => 'आपका ऑर्डर सफलतापूर्वक प्राप्त हो गया है। जल्द ही हम आपसे संपर्क करेंगे।']);
    
} catch (PDOException $e) {
    error_log("Database Error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'सिस्टम में त्रुटि। कृपया बाद में पुनः प्रयास करें।']);
}
?>
