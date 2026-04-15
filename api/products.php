<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

$db_file = __DIR__ . '/../data.db';

$defaultProduct = [
    'id' => 1,
    'name' => 'Libidex',
    'name_hindi' => 'अपनी क्षमता को उजागर करें और अपना आत्मविश्वास पुनः प्राप्त करें!',
    'description' => 'पुरुषों का स्वास्थ्य पूर्ण और ऊर्जावान जीवन की कुंजी है।',
    'description_hindi' => 'पुरुषों का स्वास्थ्य पूर्ण और ऊर्जावान जीवन की कुंजी है।',
    'price' => 2490,
    'old_price' => 4980,
    'image' => 'product-1.png',
    'image_secondary' => 'product-2.png',
    'stock' => 100,
    'status' => 'active'
];

try {
    if (file_exists($db_file)) {
        $pdo = new PDO("sqlite:$db_file");
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        $stmt = $pdo->query("SELECT * FROM products WHERE status = 'active' LIMIT 1");
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
