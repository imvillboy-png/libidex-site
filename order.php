<?php
header('Content-Type: application/json; charset=utf-8');

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

if (strlen($phone) < 10) {
    echo json_encode(['success' => false, 'message' => 'कृपया एक वैध फ़ोन नंबर दर्ज करें।']);
    exit;
}

$apiUrl = 'https://libidex-site.onrender.com/api/orders.php';

$orderData = json_encode([
    'name' => $name,
    'phone' => $phone,
    'country' => $country,
    'product' => $product,
    'clickid' => $clickid,
    'utm_campaign' => $utm_campaign,
    'utm_source' => $utm_source
]);

$ch = curl_init($apiUrl);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $orderData);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode === 200 || $httpCode === 201) {
    $result = json_decode($response, true);
    if ($result && isset($result['success'])) {
        echo json_encode(['success' => true, 'message' => 'आपका ऑर्डर सफलतापूर्वक प्राप्त हो गया है। जल्द ही हम आपसे संपर्क करेंगे।']);
    } else {
        echo json_encode(['success' => true, 'message' => 'आपका ऑर्डर सफलतापूर्वक प्राप्त हो गया है। जल्द ही हम आपसे संपर्क करेंगे।']);
    }
} else {
    echo json_encode(['success' => true, 'message' => 'आपका ऑर्डर सफलतापूर्वक प्राप्त हो गया है। जल्द ही हम आपसे संपर्क करेंगे।']);
}
?>
