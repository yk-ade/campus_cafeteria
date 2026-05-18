<?php
require_once 'includes/db.php';
require_once 'includes/functions.php';

$search = trim($_GET['search'] ?? '');
$category = trim($_GET['category'] ?? '');

$categoriesResult = $conn->query("SELECT category_name FROM categories ORDER BY category_name ASC");

$sql = "SELECT menu_items.*, categories.category_name
        FROM menu_items
        INNER JOIN categories ON menu_items.category_id = categories.id
        WHERE menu_items.availability_status = 'Available'";

$params = [];
$types = '';

if ($search !== '') {
    $sql .= " AND (menu_items.item_name LIKE ? OR menu_items.description LIKE ?)";
    $like = '%' . $search . '%';
    $params[] = $like;
    $params[] = $like;
    $types .= 'ss';
}

if ($category !== '') {
    $sql .= " AND categories.category_name = ?";
    $params[] = $category;
    $types .= 's';
}

$sql .= " ORDER BY menu_items.id DESC";

$stmt = $conn->prepare($sql);
if ($types !== '') {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

include 'includes/header.php';
?>

<section class="page-banner">
    <div class="container">
        <div class="page-banner-inner">
            <h1 class="section-title">Our Menu</h1>
            <p class="section-subtitle" style="margin-bottom:0;">Browse, search, and filter meals before adding them to your cart.</p>
        </div>
    </div>
</section>

<section>
    <div class="container">
        <form method="GET" class="menu-toolbar">
            <div class="search-box">
                <input type="text" name="search" placeholder="Search meals..." value="<?php echo h($search); ?>">
            </div>
            <button type="submit" class="btn btn-primary">Search</button>
        </form>

        <div class="category-chip-row">
            <a href="<?php echo qb_url('menu.php'); ?>" class="category-chip <?php echo ($category === '') ? 'active' : ''; ?>">All</a>
            <?php while ($cat = $categoriesResult->fetch_assoc()): ?>
                <a href="<?php echo qb_url('menu.php?category=' . urlencode($cat['category_name'])); ?>" class="category-chip <?php echo ($category === $cat['category_name']) ? 'active' : ''; ?>">
                    <?php echo h($cat['category_name']); ?>
                </a>
            <?php endwhile; ?>
        </div>

        <div class="meal-grid">
            <?php if ($result->num_rows > 0): ?>
                <?php while ($item = $result->fetch_assoc()): ?>
                    <div class="meal-card">
                        <img src="<?php echo qb_url('assets/images/foods/' . $item['image']); ?>" alt="<?php echo h($item['item_name']); ?>">
                        <div class="meal-content">
                            <div class="meal-meta"><?php echo h($item['category_name']); ?></div>
                            <h3><?php echo h($item['item_name']); ?></h3>
                            <p><?php echo h($item['description']); ?></p>
                            <div class="meal-bottom">
                                <span class="meal-price">₦<?php echo number_format((float) $item['price'], 2); ?></span>
                            </div>

                            <div class="inline-actions">
                                <a href="<?php echo qb_url('add_to_cart.php?id=' . $item['id']); ?>" class="btn btn-primary btn-sm">Add to Cart</a>
                                <a href="<?php echo qb_url('food-details.php?id=' . $item['id']); ?>" class="btn btn-light btn-sm">View Details</a>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="empty-state-box">
                    <h2>No menu items found</h2>
                    <p>Try another search or category filter.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
