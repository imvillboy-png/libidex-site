<?php
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

$dataFile = '/tmp/orders_data.json';

function readData() {
    global $dataFile;
    if (file_exists($dataFile)) {
        $content = file_get_contents($dataFile);
        $data = json_decode($content, true);
        return $data ?: ['orders' => []];
    }
    return ['orders' => []];
}

function writeData($data) {
    global $dataFile;
    $dir = dirname($dataFile);
    if (!is_dir($dir)) {
        @mkdir($dir, 0755, true);
    }
    file_put_contents($dataFile, json_encode($data, JSON_PRETTY_PRINT));
}

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $data = readData();
    echo json_encode($data);
    exit;
}

if ($method === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    if (!$input) {
        $input = $_POST;
    }
    
    $name = isset($input['name']) ? trim($input['name']) : '';
    $phone = isset($input['phone']) ? trim($input['phone']) : '';
    $country = isset($input['country']) ? trim($input['country']) : 'IN';
    $product = isset($input['product']) ? trim($input['product']) : 'Libidex';
    $clickid = isset($input['clickid']) ? trim($input['clickid']) : '';
    $utm_campaign = isset($input['utm_campaign']) ? trim($input['utm_campaign']) : '';
    $utm_source = isset($input['utm_source']) ? trim($input['utm_source']) : '';
    
    if (empty($name) || empty($phone)) {
        http_response_code(400);
        echo json_encode(['error' => 'Name and phone required']);
        exit;
    }
    
    $data = readData();
    
    $order = [
        'id' => uniqid(),
        'name' => $name,
        'phone' => $phone,
        'country' => $country,
        'product' => $product,
        'clickid' => $clickid,
        'utm_campaign' => $utm_campaign,
        'utm_source' => $utm_source,
        'status' => 'pending',
        'created_at' => date('Y-m-d H:i:s')
    ];
    
    $data['orders'][] = $order;
    writeData($data);
    
    echo json_encode(['success' => true, 'order' => $order]);
    exit;
}

if ($method === 'PUT') {
    $input = json_decode(file_get_contents('php://input'), true);
    $id = isset($input['id']) ? $input['id'] : '';
    $status = isset($input['status']) ? $input['status'] : '';
    
    if (empty($id) || empty($status)) {
        http_response_code(400);
        echo json_encode(['error' => 'ID and status required']);
        exit;
    }
    
    $data = readData();
    
    foreach ($data['orders'] as &$order) {
        if ($order['id'] === $id) {
            $order['status'] = $status;
            break;
        }
    }
    
    writeData($data);
    echo json_encode(['success' => true]);
    exit;
}

if ($method === 'DELETE') {
    $input = json_decode(file_get_contents('php://input'), true);
    $id = isset($input['id']) ? $input['id'] : '';
    
    if (empty($id)) {
        http_response_code(400);
        echo json_encode(['error' => 'ID required']);
        exit;
    }
    
    $data = readData();
    $data['orders'] = array_filter($data['orders'], function($order) use ($id) {
        return $order['id'] !== $id;
    });
    $data['orders'] = array_values($data['orders']);
    
    writeData($data);
    echo json_encode(['success' => true]);
    exit;
}

http_response_code(405);
echo json_encode(['error' => 'Method not allowed']);
