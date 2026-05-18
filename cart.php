<?php
require_once 'includes/db.php';
require_once 'includes/functions.php';

require_login();

$userId = (int) $_SESSION['user_id'];

$sql = "SELECT cart.id AS cart_id, cart.quantity, menu_items.id AS menu_item_id, menu_items.item_name, menu_items.description, menu_items.price, menu_items.image
        FROM cart
        INNER JOIN menu_items ON cart.menu_item_id = menu_items.id
        WHERE cart.user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();

$cartItems = [];
$subtotal = 0;

while ($row = $result->fetch_assoc()) {
    $row['row_total'] = (float) $row['price'] * (int) $row['quantity'];
    $subtotal += $row['row_total'];
    $cartItems[] = $row;
}

$total = $subtotal;

include 'includes/header.php';
?>

<section class="cart-section">
    <div class="container">
        <h1 class="section-title">Your Cart</h1>
        <p class="section-subtitle">Review your selected meals, adjust quantities, and continue to checkout.</p>

        <?php if (count($cartItems) > 0): ?>
            <div class="cart-wrapper">
                <div class="cart-items">
                    <form action="<?php echo qb_url('delete_selected_cart.php'); ?>" method="POST">
                        <?php foreach ($cartItems as $item): ?>
                            <div class="cart-item-card">
                                <div class="cart-item-check">
                                    <input type="checkbox" name="selected_items[]" value="<?php echo $item['cart_id']; ?>">
                                </div>

                                <div class="cart-item-image">
                                    <img src="<?php echo qb_url('assets/images/foods/' . $item['image']); ?>" alt="<?php echo h($item['item_name']); ?>">
                                </div>

                                <div class="cart-item-details">
                                    <h3><?php echo h($item['item_name']); ?></h3>
                                    <p><?php echo h($item['description']); ?></p>
                                    <span class="cart-item-price">₦<?php echo number_format((float) $item['price'], 2); ?></span>
                                </div>

                                <div class="cart-item-qty">
                                    <label>Qty</label>
                                    <div class="qty-selector">
                                        <a href="<?php echo qb_url('update_cart.php?cart_id=' . $item['cart_id'] . '&action=decrease'); ?>" class="qty-btn">−</a>
                                        <input type="text" value="<?php echo (int) $item['quantity']; ?>" class="cart-qty-input" readonly>
                                        <a href="<?php echo qb_url('update_cart.php?cart_id=' . $item['cart_id'] . '&action=increase'); ?>" class="qty-btn">+</a>
                                    </div>
                                </div>

                                <div class="cart-item-total">
                                    <p>Total</p>
                                    <strong>₦<?php echo number_format((float) $item['row_total'], 2); ?></strong>
                                    <a href="<?php echo qb_url('remove_item.php?id=' . $item['cart_id']); ?>" class="cart-remove-btn" onclick="return confirm('Remove this item?');">Remove</a>
                                </div>
                            </div>
                        <?php endforeach; ?>

                        <div class="cart-action-row">
                            <button type="submit" class="btn btn-danger">Delete Selected</button>
                            <a href="<?php echo qb_url('clear_cart.php'); ?>" class="btn btn-outline-danger" onclick="return confirm('Are you sure you want to clear the whole cart?');">Clear Cart</a>
                        </div>
                    </form>
                </div>

                <div class="cart-summary">
                    <h2 class="panel-title" style="margin-bottom:20px;">Order Summary</h2>

                    <div class="summary-row">
                        <span>Subtotal</span>
                        <span>₦<?php echo number_format((float) $subtotal, 2); ?></span>
                    </div>

                    <div class="summary-row total-row">
                        <span>Total</span>
                        <span>₦<?php echo number_format((float) $total, 2); ?></span>
                    </div>

                    <p style="font-size:0.85rem; color:var(--muted); margin-top:8px;">Delivery fee applies only if you choose Campus Delivery at checkout.</p>

                    <div class="cart-summary-buttons">
                        <a href="<?php echo qb_url('menu.php'); ?>" class="btn btn-light">Continue Shopping</a>
                        <a href="<?php echo qb_url('checkout.php'); ?>" class="btn btn-primary">Proceed to Checkout</a>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <div class="empty-state-box success-box">
                <h2 style="font-family:'Poppins',sans-serif; margin-bottom:10px;">Your cart is empty</h2>
                <p style="margin-bottom:18px; color:var(--muted);">Add meals from the menu to begin checkout.</p>
                <a href="<?php echo qb_url('menu.php'); ?>" class="btn btn-primary">Go to Menu</a>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
