<?php
require_once '../includes/db.php';
require_once '../includes/functions.php';

require_admin();

$categoriesResult = $conn->query("SELECT * FROM categories ORDER BY category_name ASC");

$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $price = (float) ($_POST['price'] ?? 0);
    $categoryId = (int) ($_POST['category_id'] ?? 0);

    if ($name === '' || $price <= 0 || $categoryId <= 0) {
        $message = 'Please complete all required fields.';
        $messageType = 'error';
    } elseif (!isset($_FILES['image']) || $_FILES['image']['error'] !== 0) {
        $message = 'Please upload a valid food image.';
        $messageType = 'error';
    } else {
        $allowedExtensions = ['jpg', 'jpeg', 'jfif', 'png', 'webp', 'gif', 'bmp', 'svg', 'tiff', 'tif', 'ico', 'avif', 'heic', 'heif'];
        $fileName = $_FILES['image']['name'];
        $fileTmp = $_FILES['image']['tmp_name'];
        $fileSize = (int) $_FILES['image']['size'];
        $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        if (!in_array($fileExt, $allowedExtensions, true)) {
            $message = 'Unsupported image format. Please upload a common image file.';
            $messageType = 'error';
        } elseif ($fileSize > 5 * 1024 * 1024) {
            $message = 'Image size must not be more than 5MB.';
            $messageType = 'error';
        } else {
            $safeName = time() . '_' . preg_replace('/[^A-Za-z0-9_\-\.]/', '_', $fileName);
            $uploadPath = __DIR__ . '/../assets/images/foods/' . $safeName;

            if (!move_uploaded_file($fileTmp, $uploadPath)) {
                $message = 'Failed to upload image.';
                $messageType = 'error';
            } else {
                $sql = "INSERT INTO menu_items (category_id, item_name, description, price, image, availability_status)
                        VALUES (?, ?, ?, ?, ?, 'Available')";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("issds", $categoryId, $name, $description, $price, $safeName);

                if ($stmt->execute()) {
                    set_flash('success', 'Meal added successfully.');
                    header("Location: " . qb_url('admin/menu.php'));
                    exit();
                } else {
                    $message = 'Database error while adding meal.';
                    $messageType = 'error';
                }
            }
        }
    }
}

include '../includes/header.php';
?>

<section class="checkout-section">
    <div class="container">
        <h1 class="section-title">Add New Meal</h1>
        <p class="section-subtitle">Upload a meal image and save a new menu item in the system.</p>

        <?php if ($flash = get_flash('success')): ?>
            <div class="alert success"><?php echo h($flash); ?></div>
        <?php endif; ?>

        <?php if ($message !== ''): ?>
            <div class="alert <?php echo $messageType; ?>"><?php echo h($message); ?></div>
        <?php endif; ?>

        <div class="checkout-form-box" style="max-width: 760px; margin: 0 auto;">
            <form method="POST" enctype="multipart/form-data" class="checkout-form">
                <div class="form-group">
                    <label>Meal Name</label>
                    <input type="text" name="name" required>
                </div>

                <div class="form-group">
                    <label>Description</label>
                    <textarea name="description" rows="4"></textarea>
                </div>

                <div class="form-group">
                    <label>Price</label>
                    <input type="number" name="price" step="0.01" required>
                </div>

                <div class="form-group">
                    <label>Category</label>
                    <select name="category_id" required>
                        <option value="">Select category</option>
                        <?php while ($cat = $categoriesResult->fetch_assoc()): ?>
                            <option value="<?php echo $cat['id']; ?>"><?php echo h($cat['category_name']); ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>Food Image</label>
                    <input type="file" name="image" accept="image/*" required>
                </div>

                <div class="admin-form-actions">
                    <button type="submit" class="btn btn-primary">Add Meal</button>
                    <a href="<?php echo qb_url('admin/menu.php'); ?>" class="btn btn-light">Back to Menu</a>
                </div>
            </form>
        </div>
    </div>
</section>

<?php include '../includes/footer.php'; ?>
