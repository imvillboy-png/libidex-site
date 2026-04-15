<?php
require_once '../config/database.php';
require_once '../includes/auth.php';
require_once '../includes/helpers.php';

requireLogin();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize($_POST['name'] ?? '');
    $name_hindi = sanitize($_POST['name_hindi'] ?? '');
    $description = sanitize($_POST['description'] ?? '');
    $description_hindi = sanitize($_POST['description_hindi'] ?? '');
    $price = floatval($_POST['price'] ?? 0);
    $old_price = !empty($_POST['old_price']) ? floatval($_POST['old_price']) : null;
    $stock = intval($_POST['stock'] ?? 100);
    $status = sanitize($_POST['status'] ?? 'active');
    
    if (empty($name) || empty($price)) {
        $error = 'Name and price are required.';
    } else {
        $pdo = getDB();
        
        $imagePath = null;
        $imageSecondaryPath = null;
        
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $uploadResult = uploadImage($_FILES['image'], '../images/');
            if ($uploadResult['success']) {
                $imagePath = $uploadResult['filename'];
                copy('../images/' . $imagePath, '../uploads/' . $imagePath);
            }
        }
        
        if (isset($_FILES['image_secondary']) && $_FILES['image_secondary']['error'] === UPLOAD_ERR_OK) {
            $uploadResult = uploadImage($_FILES['image_secondary'], '../images/');
            if ($uploadResult['success']) {
                $imageSecondaryPath = $uploadResult['filename'];
                copy('../images/' . $imageSecondaryPath, '../uploads/' . $imageSecondaryPath);
            }
        }
        
        $stmt = $pdo->prepare("INSERT INTO products (name, name_hindi, description, description_hindi, price, old_price, image, image_secondary, stock, status) 
                               VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        
        try {
            $stmt->execute([$name, $name_hindi, $description, $description_hindi, $price, $old_price, $imagePath, $imageSecondaryPath, $stock, $status]);
            setFlash('success', 'Product created successfully!');
            header('Location: index.php');
            exit;
        } catch (PDOException $e) {
            $error = 'Failed to create product. Please try again.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Product - Libidex Admin</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>
    <div class="wrapper">
        <aside class="sidebar">
            <div class="sidebar-logo">
                <h2>Libidex Admin</h2>
            </div>
            <ul class="sidebar-nav">
                <li class="nav-item">
                    <a href="../dashboard.php" class="nav-link">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                        </svg>
                        Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a href="index.php" class="nav-link active">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                        </svg>
                        Products
                    </a>
                </li>
                <li class="nav-item">
                    <a href="../orders/" class="nav-link">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                        </svg>
                        Orders
                    </a>
                </li>
                <li class="nav-item">
                    <a href="../reviews/" class="nav-link">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
                        </svg>
                        Reviews
                    </a>
                </li>
                <li class="nav-item">
                    <a href="../users/" class="nav-link">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                        </svg>
                        Users
                    </a>
                </li>
                <li class="nav-item">
                    <a href="../settings.php" class="nav-link">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                        Settings
                    </a>
                </li>
            </ul>
        </aside>
        
        <main class="main-content">
            <div class="header">
                <h1>Add New Product</h1>
                <div class="header-actions">
                    <div class="user-info">
                        <div class="user-avatar"><?php echo strtoupper(substr($_SESSION['admin_username'], 0, 1)); ?></div>
                        <span><?php echo htmlspecialchars($_SESSION['admin_username']); ?></span>
                    </div>
                    <a href="../logout.php" class="btn btn-logout">Logout</a>
                </div>
            </div>
            
            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <div class="card">
                <div class="card-header">
                    <h2>Product Information</h2>
                    <a href="index.php" class="btn btn-sm btn-back">← Back to Products</a>
                </div>
                <div class="card-body">
                    <form method="POST" enctype="multipart/form-data">
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="name">Product Name (English) *</label>
                                <input type="text" id="name" name="name" class="form-control" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="name_hindi">Product Name (Hindi)</label>
                                <input type="text" id="name_hindi" name="name_hindi" class="form-control">
                            </div>
                            
                            <div class="form-group full-width">
                                <label for="description">Description (English)</label>
                                <textarea id="description" name="description" class="form-control" rows="4"></textarea>
                            </div>
                            
                            <div class="form-group full-width">
                                <label for="description_hindi">Description (Hindi)</label>
                                <textarea id="description_hindi" name="description_hindi" class="form-control" rows="4"></textarea>
                            </div>
                            
                            <div class="form-group">
                                <label for="price">Price (INR) *</label>
                                <input type="number" id="price" name="price" class="form-control" step="0.01" min="0" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="old_price">Old Price (INR)</label>
                                <input type="number" id="old_price" name="old_price" class="form-control" step="0.01" min="0">
                            </div>
                            
                            <div class="form-group">
                                <label for="stock">Stock</label>
                                <input type="number" id="stock" name="stock" class="form-control" value="100" min="0">
                            </div>
                            
                            <div class="form-group">
                                <label for="status">Status</label>
                                <select id="status" name="status" class="form-control">
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label for="image">Main Image</label>
                                <input type="file" id="image" name="image" class="form-control" accept="image/*">
                                <small style="color: var(--gray-500);">Max size: 5MB. Formats: JPEG, PNG, GIF, WebP</small>
                            </div>
                            
                            <div class="form-group">
                                <label for="image_secondary">Secondary Image</label>
                                <input type="file" id="image_secondary" name="image_secondary" class="form-control" accept="image/*">
                                <small style="color: var(--gray-500);">Optional secondary product image</small>
                            </div>
                        </div>
                        
                        <div class="btn-group">
                            <button type="submit" class="btn btn-primary">Create Product</button>
                            <a href="index.php" class="btn btn-back">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
