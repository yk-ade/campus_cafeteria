<?php
require_once '../includes/db.php';
require_once '../includes/functions.php';

require_admin();

$sql = "SELECT menu_items.*, categories.category_name
        FROM menu_items
        INNER JOIN categories ON menu_items.category_id = categories.id
        ORDER BY menu_items.id DESC";
$result = $conn->query($sql);

include '../includes/header.php';
?>

<section class="order-history-section">
    <div class="container">
        <h1 class="section-title">Manage Menu</h1>
        <p class="section-subtitle">Create, update, delete, and control meal availability with image upload support.</p>

        <?php if (isset($_GET['updated'])): ?>
            <div class="alert success">Meal updated successfully.</div>
        <?php endif; ?>
        <?php if (isset($_GET['deleted'])): ?>
            <div class="alert success">Meal deleted successfully.</div>
        <?php endif; ?>

        <div class="admin-top-actions">
            <a href="<?php echo qb_url('admin/add-menu.php'); ?>" class="btn btn-primary">Add New Meal</a>
            <a href="<?php echo qb_url('admin/dashboard.php'); ?>" class="btn btn-light">Back to Dashboard</a>
        </div>

        <div class="order-history-table-wrapper">
            <table class="order-history-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Image</th>
                        <th>Name</th>
                        <th>Category</th>
                        <th>Price</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result && $result->num_rows > 0): ?>
                        <?php while ($item = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo $item['id']; ?></td>
                                <td><img src="<?php echo qb_url('assets/images/foods/' . $item['image']); ?>" alt="<?php echo h($item['item_name']); ?>" class="admin-table-image"></td>
                                <td><?php echo h($item['item_name']); ?></td>
                                <td><?php echo h($item['category_name']); ?></td>
                                <td>₦<?php echo number_format((float) $item['price'], 2); ?></td>
                                <td>
                                    <span class="status-badge <?php echo ($item['availability_status'] === 'Available') ? 'status-available' : 'status-unavailable'; ?>">
                                        <?php echo h($item['availability_status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="admin-action-buttons">
                                        <a href="<?php echo qb_url('admin/edit-menu.php?id=' . $item['id']); ?>" class="btn btn-light btn-sm">Edit</a>
                                        <a href="<?php echo qb_url('admin/delete-menu.php?id=' . $item['id']); ?>" class="btn btn-danger btn-sm" onclick="return confirm('Delete this meal?');">Delete</a>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="7">No menu items found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>

<?php include '../includes/footer.php'; ?>
