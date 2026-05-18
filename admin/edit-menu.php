<?php
require_once '../includes/db.php';
require_once '../includes/functions.php';

require_admin();

$itemId = (int) ($_GET['id'] ?? 0);
if ($itemId <= 0) {
    header("Location: " . qb_url('admin/menu.php'));
    exit();
}

$categoriesResult = $conn->query("SELECT * FROM categories ORDER BY category_name ASC");

$itemSql = "SELECT * FROM menu_items WHERE id = ?";
$itemStmt = $conn->prepare($itemSql);
$itemStmt->bind_param("i", $itemId);
$itemStmt->execute();
$itemResult = $itemStmt->get_result();

if ($itemResult->num_rows !== 1) {
    header("Location: " . qb_url('admin/menu.php'));
    exit();
}

$item = $itemResult->fetch_assoc();

$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $price = (float) ($_POST['price'] ?? 0);
    $categoryId = (int) ($_POST['category_id'] ?? 0);
    $availabilityStatus = trim($_POST['availability_status'] ?? '');
    $newImageName = $item['image'];

    if ($name === '' || $price <= 0 || $categoryId <= 0 || $availabilityStatus === '') {
        $message = 'Please complete all required fields.';
        $messageType = 'error';
    } else {
        if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
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
                $newImageName = time() . '_' . preg_replace('/[^A-Za-z0-9_\-\.]/', '_', $fileName);
                $uploadPath = __DIR__ . '/../assets/images/foods/' . $newImageName;

                if (!move_uploaded_file($fileTmp, $uploadPath)) {
                    $message = 'Failed to upload image.';
                    $messageType = 'error';
                }
            }
        }

        if ($message === '') {
            $updateSql = "UPDATE menu_items
                          SET category_id = ?, item_name = ?, description = ?, price = ?, image = ?, availability_status = ?
                          WHERE id = ?";
            $updateStmt = $conn->prepare($updateSql);
            $updateStmt->bind_param("issdssi", $categoryId, $name, $description, $price, $newImageName, $availabilityStatus, $itemId);

            if ($updateStmt->execute()) {
                header("Location: " . qb_url('admin/menu.php?updated=1'));
                exit();
            } else {
                $message = 'Failed to update meal.';
                $messageType = 'error';
            }
        }
    }
}

include '../includes/header.php';
?>

<section class="checkout-section">
    <div class="container">
        <h1 class="section-title">Edit Meal</h1>
        <p class="section-subtitle">Update the meal record, availability, and optionally replace the food image.</p>

        <?php if ($message !== ''): ?>
            <div class="alert <?php echo $messageType; ?>"><?php echo h($message); ?></div>
        <?php endif; ?>

        <div class="checkout-form-box" style="max-width: 760px; margin: 0 auto;">
            <form method="POST" enctype="multipart/form-data" class="checkout-form">
                <div class="form-group">
                    <label>Meal Name</label>
                    <input type="text" name="name" value="<?php echo h($item['item_name']); ?>" required>
                </div>

                <div class="form-group">
                    <label>Description</label>
                    <textarea name="description" rows="4"><?php echo h($item['description']); ?></textarea>
                </div>

                <div class="form-group">
                    <label>Price</label>
                    <input type="number" name="price" step="0.01" value="<?php echo h((string) $item['price']); ?>" required>
                </div>

                <div class="form-group">
                    <label>Category</label>
                    <select name="category_id" required>
                        <option value="">Select category</option>
                        <?php while ($cat = $categoriesResult->fetch_assoc()): ?>
                            <option value="<?php echo $cat['id']; ?>" <?php echo ((int) $cat['id'] === (int) $item['category_id']) ? 'selected' : ''; ?>>
                                <?php echo h($cat['category_name']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>Availability</label>
                    <select name="availability_status" required>
                        <option value="Available" <?php echo ($item['availability_status'] === 'Available') ? 'selected' : ''; ?>>Available</option>
                        <option value="Unavailable" <?php echo ($item['availability_status'] === 'Unavailable') ? 'selected' : ''; ?>>Unavailable</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Current Image</label>
                    <div class="admin-image-preview">
                        <img src="<?php echo qb_url('assets/images/foods/' . $item['image']); ?>" alt="<?php echo h($item['item_name']); ?>">
                    </div>
                </div>

                <div class="form-group">
                    <label>Replace Image</label>
                    <input type="file" name="image" accept="image/*">
                </div>

                <div class="admin-form-actions">
                    <button type="submit" class="btn btn-primary">Update Meal</button>
                    <a href="<?php echo qb_url('admin/menu.php'); ?>" class="btn btn-light">Back to Menu</a>
                </div>
            </form>
        </div>
    </div>
</section>

<?php include '../includes/footer.php'; ?>
