<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

$db_file = __DIR__ . '/../../data.db';

$defaultProduct = [
    'id' => 2,
    'name' => 'Proman',
    'name_hindi' => 'पुरुषों के लिए कैप्सूल',
    'price' => 2490,
    'old_price' => 4980
];

try {
    if (file_exists($db_file)) {
        $pdo = new PDO("sqlite:$db_file");
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        $stmt = $pdo->query("SELECT * FROM products WHERE status = 'active' AND (name LIKE '%Proman%' OR id = 2) LIMIT 1");
        $product = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($product) {
            echo json_encode([
                'success' => true,
                'product' => $product
            ]);
        } else {
            echo json_encode([
                'success' => true,
                'product' => $defaultProduct
            ]);
        }
    } else {
        echo json_encode([
            'success' => true,
            'product' => $defaultProduct
        ]);
    }
    
} catch (PDOException $e) {
    error_log("API Error: " . $e->getMessage());
    echo json_encode([
        'success' => true,
        'product' => $defaultProduct
    ]);
}
