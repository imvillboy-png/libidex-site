<?php
require_once '../config/database.php';
require_once '../includes/auth.php';
require_once '../includes/helpers.php';

requireLogin();

$pdo = getDB();
$error = '';

$id = intval($_GET['id'] ?? 0);
if (!$id) {
    header('Location: index.php');
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM reviews WHERE id = ?");
$stmt->execute([$id]);
$review = $stmt->fetch();

if (!$review) {
    header('Location: index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize($_POST['name'] ?? '');
    $age = sanitize($_POST['age'] ?? '');
    $review_text = sanitize($_POST['review_text'] ?? '');
    $sort_order = intval($_POST['sort_order'] ?? 0);
    $status = sanitize($_POST['status'] ?? 'active');
    
    if (empty($name) || empty($review_text)) {
        $error = 'Name and review text are required.';
    } else {
        $imagePath = $review['image'];
        
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            if ($review['image']) {
                deleteImage($review['image'], '../images/');
            }
            $uploadResult = uploadImage($_FILES['image'], '../images/');
            if ($uploadResult['success']) {
                $imagePath = $uploadResult['filename'];
            }
        } elseif (isset($_POST['existing_image']) && !empty($_POST['existing_image'])) {
            $imagePath = sanitize($_POST['existing_image']);
        }
        
        $stmt = $pdo->prepare("UPDATE reviews SET name = ?, age = ?, image = ?, review_text = ?, sort_order = ?, status = ? WHERE id = ?");
        
        try {
            $stmt->execute([$name, $age, $imagePath, $review_text, $sort_order, $status, $id]);
            setFlash('success', 'Review updated successfully!');
            header('Location: index.php');
            exit;
        } catch (PDOException $e) {
            $error = 'Failed to update review. Please try again.';
        }
    }
}

$existingImages = ['live-1.jpg', 'live-2.jpg', 'live-3.jpg'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Review - Libidex Admin</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>
    <div class="wrapper">
        <aside class="sidebar">
            <div class="sidebar-logo">
                <h2>Libidex</h2>
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
                    <a href="../products/" class="nav-link">
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
                    <a href="index.php" class="nav-link active">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
                        </svg>
                        Reviews
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
                <h1>Edit Review</h1>
                <div class="header-actions">
                    <div class="user-info">
                        <div class="user-avatar"><?php echo strtoupper(substr($_SESSION['admin_username'], 0, 1)); ?></div>
                        <span><?php echo htmlspecialchars($_SESSION['admin_username']); ?></span>
                    </div>
                    <a href="../logout.php" class="btn btn-logout">Logout</a>
                </div>
            </div>
            
            <a href="index.php" class="back-link">
                <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
                Back to Reviews
            </a>
            
            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <div class="card">
                <div class="card-header">
                    <h2>Review Information</h2>
                </div>
                <div class="card-body">
                    <form method="POST" enctype="multipart/form-data">
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="name">Customer Name *</label>
                                <input type="text" id="name" name="name" class="form-control" value="<?php echo htmlspecialchars($review['name']); ?>" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="age">Age</label>
                                <input type="text" id="age" name="age" class="form-control" value="<?php echo htmlspecialchars($review['age']); ?>" placeholder="e.g., 35 वर्ष">
                            </div>
                            
                            <div class="form-group full-width">
                                <label for="review_text">Review Text *</label>
                                <textarea id="review_text" name="review_text" class="form-control" rows="5" required><?php echo htmlspecialchars($review['review_text']); ?></textarea>
                            </div>
                            
                            <div class="form-group">
                                <label for="sort_order">Display Order</label>
                                <input type="number" id="sort_order" name="sort_order" class="form-control" value="<?php echo $review['sort_order']; ?>" min="0">
                            </div>
                            
                            <div class="form-group">
                                <label for="status">Status</label>
                                <select id="status" name="status" class="form-control">
                                    <option value="active" <?php echo $review['status'] === 'active' ? 'selected' : ''; ?>>Active</option>
                                    <option value="inactive" <?php echo $review['status'] === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                                </select>
                            </div>
                            
                            <div class="form-group full-width">
                                <label for="image">Change Image</label>
                                <input type="file" id="image" name="image" class="form-control" accept="image/*">
                                <small style="color: var(--gray-500);">Max size: 5MB. Formats: JPEG, PNG, GIF, WebP</small>
                                
                                <?php if ($review['image']): ?>
                                    <div style="margin-top: 12px;">
                                        <p style="font-size: 12px; color: var(--gray-500); margin-bottom: 8px;">Current Image:</p>
                                        <img src="../images/<?php echo htmlspecialchars($review['image']); ?>" style="width: 100px; height: 100px; object-fit: cover; border-radius: 8px;">
                                    </div>
                                <?php endif; ?>
                                
                                <div style="margin-top: 16px;">
                                    <label style="font-weight: 500; margin-bottom: 8px; display: block;">Or select existing image:</label>
                                    <div style="display: flex; gap: 12px; flex-wrap: wrap;">
                                        <?php foreach ($existingImages as $img): ?>
                                            <label style="cursor: pointer;">
                                                <input type="radio" name="existing_image" value="<?php echo $img; ?>" <?php echo $review['image'] === $img ? 'checked' : ''; ?> style="display: none;">
                                                <img src="../images/<?php echo $img; ?>" style="width: 80px; height: 80px; object-fit: cover; border-radius: 8px; border: 2px solid <?php echo $review['image'] === $img ? '#4F46E5' : 'transparent'; ?>;">
                                            </label>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="btn-group">
                            <button type="submit" class="btn btn-add">Update Review</button>
                            <a href="index.php" class="btn btn-back">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>
    
    <script>
        document.querySelectorAll('input[name="existing_image"]').forEach(radio => {
            radio.addEventListener('change', function() {
                document.querySelectorAll('input[name="existing_image"] + img').forEach(img => {
                    img.style.borderColor = 'transparent';
                });
                this.nextElementSibling.style.borderColor = '#4F46E5';
            });
        });
    </script>
</body>
</html>
