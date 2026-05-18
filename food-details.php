<?php
require_once 'includes/db.php';
require_once 'includes/functions.php';

if (!isset($_GET['id'])) {
    header("Location: " . qb_url('menu.php'));
    exit();
}

$itemId = (int) $_GET['id'];
$sql = "SELECT menu_items.*, categories.category_name
        FROM menu_items
        INNER JOIN categories ON menu_items.category_id = categories.id
        WHERE menu_items.id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $itemId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows !== 1) {
    header("Location: " . qb_url('menu.php'));
    exit();
}

$item = $result->fetch_assoc();
$suggestions = combo_suggestions($conn, $itemId, 2);

include 'includes/header.php';
?>

<section class="page-banner">
    <div class="container">
        <div class="page-banner-inner">
            <h1 class="section-title">Meal Details</h1>
            <p class="section-subtitle" style="margin-bottom:0;">View meal details and add a smart combo to your cart.</p>
        </div>
    </div>
</section>

<section>
    <div class="container">
        <div class="food-details-wrapper">
            <div class="food-details-image">
                <img src="<?php echo qb_url('assets/images/foods/' . $item['image']); ?>" alt="<?php echo h($item['item_name']); ?>">
            </div>

            <div class="food-details-content">
                <span class="food-tag"><?php echo h($item['category_name']); ?></span>
                <h2><?php echo h($item['item_name']); ?></h2>
                <p class="food-price">₦<?php echo number_format((float) $item['price'], 2); ?></p>
                <p class="food-description"><?php echo h($item['description']); ?></p>

                <div class="food-meta">
                    <p><strong>Category:</strong> <?php echo h($item['category_name']); ?></p>
                    <p><strong>Status:</strong> <?php echo h($item['availability_status']); ?></p>
                    <p><strong>Estimated prep:</strong> <?php echo estimate_ready_minutes($conn, is_logged_in() ? (int)$_SESSION['user_id'] : null); ?> minutes</p>
                </div>

<?php
// Fetch toppings
$toppingsSql = "SELECT menu_items.id, menu_items.item_name, menu_items.price 
                FROM menu_items 
                INNER JOIN categories ON menu_items.category_id = categories.id 
                WHERE categories.category_name = 'Toppings' AND menu_items.availability_status = 'Available'";
$toppingsResult = $conn->query($toppingsSql);
$toppings = [];
if ($toppingsResult) {
    while ($row = $toppingsResult->fetch_assoc()) {
        $toppings[] = $row;
    }
}
?>

                <form action="<?php echo qb_url('add_to_cart.php'); ?>" method="POST" class="add-to-cart-form">
                    <input type="hidden" name="item_id" value="<?php echo $item['id']; ?>">
                    
                    <?php if (!empty($toppings)): ?>
                        <div class="toppings-section" style="margin-top: 20px; margin-bottom: 20px;">
                            <h4 style="margin-bottom: 10px; font-family: 'Poppins', sans-serif;">Add Toppings (Optional)</h4>
                            <div class="toppings-grid" style="display: flex; flex-direction: column; gap: 8px;">
                                <?php foreach ($toppings as $topping): ?>
                                    <label class="topping-label" style="display: flex; align-items: center; gap: 10px; cursor: pointer; padding: 10px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px;">
                                        <input type="checkbox" name="toppings[]" value="<?php echo $topping['id']; ?>" style="accent-color: var(--primary); width: 18px; height: 18px;">
                                        <span style="flex: 1;"><?php echo h($topping['item_name']); ?></span>
                                        <strong style="color: var(--primary);">+₦<?php echo number_format((float)$topping['price'], 0); ?></strong>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <div class="food-buttons">
                        <button type="submit" class="btn btn-primary">Add to Cart</button>
                        <a href="<?php echo qb_url('menu.php'); ?>" class="btn btn-light">Back to Menu</a>
                    </div>
                </form>

                <?php if ($suggestions): ?>
                    <div class="ai-inline-card" style="margin-top:18px;">
                        <span class="dashboard-kicker">Suggested combo</span>
                        <p>Students often add one of these with this meal.</p>
                        <div class="mini-meal-grid">
                            <?php foreach ($suggestions as $suggestion): ?>
                                <a href="<?php echo qb_url('food-details.php?id=' . $suggestion['id']); ?>" class="mini-meal-card">
                                    <img src="<?php echo qb_url('assets/images/foods/' . $suggestion['image']); ?>" alt="<?php echo h($suggestion['item_name']); ?>">
                                    <strong><?php echo h($suggestion['item_name']); ?></strong>
                                    <span>₦<?php echo number_format((float)$suggestion['price'], 2); ?></span>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>