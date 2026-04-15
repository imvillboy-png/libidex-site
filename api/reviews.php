<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

$host = getenv('DB_HOST') ?: 'localhost';
$port = getenv('DB_PORT') ?: '5432';
$dbname = getenv('DB_NAME') ?: 'libidex_db';
$username = getenv('DB_USER') ?: 'libidex_db_user';
$password = getenv('DB_PASS') ?: '';

$defaultReviews = [
    [
        'id' => 1,
        'name' => 'राजेश शर्मा',
        'age' => 42,
        'image' => 'live-1.jpg',
        'review_text' => 'बहुत बढ़िया उत्पाद! 2 हफ्ते में ही असर दिखा।'
    ],
    [
        'id' => 2,
        'name' => 'अमित वर्मा',
        'age' => 38,
        'image' => 'live-2.jpg',
        'review_text' => 'आत्मविश्वास वापस आ गया। Highly recommended!'
    ],
    [
        'id' => 3,
        'name' => 'संजय पटेल',
        'age' => 45,
        'image' => 'live-3.jpg',
        'review_text' => 'पत्नी भी खुश हैं। अब कोई परेशानी नहीं।'
    ]
];

try {
    $dsn = "pgsql:host=$host;port=$port;dbname=$dbname";
    $pdo = new PDO($dsn, $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $stmt = $pdo->query("SELECT id, name, age, image, review_text FROM reviews WHERE status = 'active' ORDER BY sort_order ASC, id DESC");
    $reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (!empty($reviews)) {
        echo json_encode([
            'success' => true,
            'reviews' => $reviews
        ]);
    } else {
        echo json_encode([
            'success' => true,
            'reviews' => $defaultReviews
        ]);
    }
    
} catch (PDOException $e) {
    error_log("API Error: " . $e->getMessage());
    echo json_encode([
        'success' => true,
        'reviews' => $defaultReviews
    ]);
}
