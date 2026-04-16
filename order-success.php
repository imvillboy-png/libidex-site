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
    $error = 'Please fill all required fields.';
} elseif (strlen($phone) < 10) {
    $error = 'Please enter a valid phone number.';
} else {
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
    $curlError = curl_error($ch);
    curl_close($ch);
    
    if ($httpCode === 200 || $httpCode === 201) {
        $result = json_decode($response, true);
        if ($result && isset($result['success'])) {
            $success = true;
        } else {
            $success = true;
        }
    } else {
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
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
            min-height: 100vh; 
            display: flex; 
            align-items: center; 
            justify-content: center;
            background: linear-gradient(135deg, #0a0a0a 0%, #1a1a2e 50%, #16213e 100%);
            color: #fff;
            text-align: center;
            padding: 20px;
        }
        .card {
            background: linear-gradient(145deg, rgba(255,255,255,0.1) 0%, rgba(255,255,255,0.05) 100%);
            backdrop-filter: blur(20px);
            border-radius: 24px;
            padding: 50px 40px;
            max-width: 500px;
            width: 100%;
            border: 1px solid rgba(255,255,255,0.15);
            box-shadow: 0 25px 50px rgba(0,0,0,0.5);
            animation: slideUp 0.5s ease-out;
        }
        @keyframes slideUp {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
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
            animation: pulse 2s infinite;
        }
        @keyframes pulse {
            0%, 100% { box-shadow: 0 0 0 0 rgba(66, 179, 78, 0.4); }
            50% { box-shadow: 0 0 0 20px rgba(66, 179, 78, 0); }
        }
        .icon svg { width: 50px; height: 50px; fill: white; }
        h1 { font-size: 28px; margin-bottom: 15px; font-weight: 600; }
        p { font-size: 16px; color: rgba(255,255,255,0.8); margin-bottom: 10px; line-height: 1.6; }
        .phone { font-size: 24px; font-weight: bold; color: #00c2ff; margin: 20px 0; text-shadow: 0 0 20px rgba(0,194,255,0.5); }
        .order-num { background: rgba(0,0,0,0.3); padding: 20px; border-radius: 12px; margin-top: 20px; }
        .order-num p { font-size: 14px; margin-bottom: 8px; color: rgba(255,255,255,0.7); }
        .btn {
            display: inline-block;
            margin-top: 30px;
            padding: 15px 40px;
            background: linear-gradient(180deg, #42b34e 0%, #266b2c 100%);
            color: #fff;
            text-decoration: none;
            border-radius: 12px;
            font-weight: bold;
            font-size: 16px;
            transition: all 0.3s;
            box-shadow: 0 10px 30px rgba(66, 179, 78, 0.3);
        }
        .btn:hover { transform: translateY(-3px); box-shadow: 0 15px 40px rgba(66, 179, 78, 0.4); }
        .error-card { border-color: rgba(235, 51, 73, 0.5); }
        .error-icon { background: linear-gradient(135deg, #eb3349 0%, #f45c43 100%); }
        .highlight { color: #00c2ff; font-weight: 600; }
    </style>
</head>
<body>
    <?php if ($success): ?>
        <div class="card">
            <div class="icon">
                <svg viewBox="0 0 24 24"><path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/></svg>
            </div>
            <h1>Thank You! Your Order is Placed</h1>
            <p>Our team will contact you shortly for confirmation</p>
            <p>Please keep your phone <span class="highlight"><?php echo htmlspecialchars($phone); ?></span> ready</p>
            <div class="order-num">
                <p>Your Order Number: <?php echo rand(10000, 99999); ?></p>
                <p>Product: <?php echo htmlspecialchars($product); ?></p>
            </div>
            <a href="index.html" class="btn">Back to Home</a>
        </div>
    <?php else: ?>
        <div class="card error-card">
            <div class="icon error-icon">
                <svg viewBox="0 0 24 24"><path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"/></svg>
            </div>
            <h1>Something Went Wrong</h1>
            <p><?php echo htmlspecialchars($error); ?></p>
            <a href="index.html" class="btn">Go Back</a>
        </div>
    <?php endif; ?>
</body>
</html>
