<?php
require_once 'db.php';

$error = '';
$success = false;
$phone = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize($_POST['name'] ?? '');
    $phone = sanitize($_POST['phone'] ?? '');
    $country = sanitize($_POST['country'] ?? 'IN');
    $clickid = sanitize($_POST['clickid'] ?? '');
    $utm_source = sanitize($_POST['utm_source'] ?? '');
    $utm_medium = sanitize($_POST['utm_medium'] ?? '');
    $utm_campaign = sanitize($_POST['utm_campaign'] ?? '');
    $utm_content = sanitize($_POST['utm_content'] ?? '');
    $product = sanitize($_POST['product'] ?? 'Proman');
    
    if (empty($name) || empty($phone)) {
        $error = 'कृपया सभी आवश्यक फ़ील्ड भरें।';
    } else {
        $pdo = getDB();
        
        if ($pdo) {
            try {
                $stmt = $pdo->prepare("INSERT INTO orders (name, phone, country, clickid, utm_source, utm_medium, utm_campaign, utm_content, product, status) 
                                       VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending')");
                $stmt->execute([$name, $phone, $country, $clickid, $utm_source, $utm_medium, $utm_campaign, $utm_content, $product]);
                $success = true;
            } catch (PDOException $e) {
                $error = 'ऑर्डर सेव करने में त्रुटि। कृपया पुनः प्रयास करें।';
            }
        } else {
            $success = true;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="hi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Placed - Proman</title>
    <style>
        * { padding: 0; margin: 0; box-sizing: border-box; }
        body { 
            font-family: Arial, sans-serif; 
            min-height: 100vh; 
            display: flex; 
            align-items: center; 
            justify-content: center;
            background: linear-gradient(135deg, #030e28 0%, #1a2a5e 100%);
            color: #fff;
            text-align: center;
            padding: 20px;
        }
        .success-card {
            background: rgba(255,255,255,0.1);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 50px 40px;
            max-width: 500px;
            border: 2px solid rgba(66, 179, 78, 0.5);
        }
        .success-icon {
            width: 100px;
            height: 100px;
            background: linear-gradient(135deg, #42b34e 0%, #266b2c 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 30px;
        }
        .success-icon svg {
            width: 50px;
            height: 50px;
            fill: white;
        }
        h1 { font-size: 32px; margin-bottom: 15px; }
        p { font-size: 18px; color: rgba(255,255,255,0.8); margin-bottom: 10px; }
        .phone { font-size: 24px; font-weight: bold; color: #00c2ff; margin: 20px 0; }
        .info { background: rgba(0,0,0,0.2); padding: 20px; border-radius: 10px; margin-top: 20px; }
        .info p { font-size: 14px; margin-bottom: 8px; }
        .back-btn {
            display: inline-block;
            margin-top: 30px;
            padding: 15px 40px;
            background: linear-gradient(180deg, #00c2ff 0%, #00c2ff 100%);
            color: #000;
            text-decoration: none;
            border-radius: 10px;
            font-weight: bold;
            font-size: 16px;
        }
        .back-btn:hover { opacity: 0.9; }
        .error-card { border-color: rgba(235, 51, 73, 0.5); }
        .error-icon { background: linear-gradient(135deg, #eb3349 0%, #f45c43 100%); }
    </style>
</head>
<body>
    <?php if ($success): ?>
        <div class="success-card">
            <div class="success-icon">
                <svg viewBox="0 0 24 24"><path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/></svg>
            </div>
            <h1>धन्यवाद! आपका ऑर्डर प्राप्त हो गया</h1>
            <p>हमारी टीम जल्द ही आपसे संपर्क करेगी</p>
            <div class="phone"><?php echo htmlspecialchars($phone); ?></div>
            <p>आपका ऑर्डर नंबर: <?php echo rand(1000, 9999); ?></p>
        </div>
    <?php else: ?>
        <div class="success-card error-card">
            <div class="success-icon error-icon">
                <svg viewBox="0 0 24 24"><path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"/></svg>
            </div>
            <h1>कुछ गलती हो गई</h1>
            <p><?php echo htmlspecialchars($error); ?></p>
            <a href="index.html" class="back-btn">वापस जाएं</a>
        </div>
    <?php endif; ?>
</body>
</html>
