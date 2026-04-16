<?php
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
$dataFile = '/tmp/products_data.json';
function readProductsData() { global $dataFile; if (file_exists($dataFile)) { $c = file_get_contents($dataFile); $d = json_decode($c, true); return $d ?: ['products' => [['id'=>1,'name'=>'Libidex','price'=>2490,'old_price'=>4980,'status'=>'active']]]; } return ['products' => [['id'=>1,'name'=>'Libidex','price'=>2490,'old_price'=>4980,'status'=>'active']]]; }
function writeProductsData($d) { global $dataFile; @file_put_contents($dataFile, json_encode($d)); }
$m = $_SERVER['REQUEST_METHOD'];
if ($m === 'GET') { echo json_encode(readProductsData()); exit; }
if ($m === 'PUT') { $i = json_decode(file_get_contents('php://input'), true) ?: $_POST; $id = intval($i['id'] ?? 0); if ($id <= 0) { http_response_code(400); echo json_encode(['error'=>'ID required']); exit; } $d = readProductsData(); foreach ($d['products'] as &$p) { if ($p['id'] === $id) { $p['name'] = $i['name'] ?? $p['name']; $p['price'] = floatval($i['price'] ?? $p['price']); $p['old_price'] = floatval($i['old_price'] ?? $p['old_price']); $p['status'] = $i['status'] ?? $p['status']; break; } } writeProductsData($d); echo json_encode(['success'=>true]); exit; }
echo json_encode(['error'=>'Method not allowed']);
