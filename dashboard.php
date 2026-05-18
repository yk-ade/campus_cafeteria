<?php
require_once 'includes/db.php';
require_once 'includes/functions.php';
require_login();

if (is_admin()) {
    header('Location: ' . qb_url('admin/dashboard.php'));
    exit();
}
if (is_staff()) {
    header('Location: ' . qb_url('staff/dashboard.php'));
    exit();
}

$userId = (int) $_SESSION['user_id'];

$orderCountStmt = $conn->prepare("SELECT COUNT(*) AS total_orders FROM orders WHERE user_id = ?");
$orderCountStmt->bind_param("i", $userId);
$orderCountStmt->execute();
$orderCount = (int) ($orderCountStmt->get_result()->fetch_assoc()['total_orders'] ?? 0);

$activeOrderStmt = $conn->prepare("SELECT COUNT(*) AS active_orders FROM orders WHERE user_id = ? AND order_status IN ('Pending','Confirmed','Preparing','Ready for Pickup','Out for Delivery')");
$activeOrderStmt->bind_param("i", $userId);
$activeOrderStmt->execute();
$activeOrders = (int) ($activeOrderStmt->get_result()->fetch_assoc()['active_orders'] ?? 0);

$reservationStmt = $conn->prepare("SELECT COUNT(*) AS total_reservations FROM reservations WHERE user_id = ?");
$reservationStmt->bind_param("i", $userId);
$reservationStmt->execute();
$reservationCount = (int) ($reservationStmt->get_result()->fetch_assoc()['total_reservations'] ?? 0);

$recentOrdersStmt = $conn->prepare("SELECT id, total_amount, order_status, delivery_method, created_at FROM orders WHERE user_id = ? ORDER BY created_at DESC LIMIT 5");
$recentOrdersStmt->bind_param("i", $userId);
$recentOrdersStmt->execute();
$recentOrders = $recentOrdersStmt->get_result();

$recommended = recommended_meals($conn, 3);

include 'includes/header.php';
?>

<section class="user-dashboard">
    <div class="container">
        <div class="dashboard-hero">
            <div>
                <span class="dashboard-kicker">Student Dashboard</span>
                <h1 class="dashboard-title"><?php echo h($_SESSION['full_name'] ?? 'Student'); ?></h1>
                <p class="dashboard-copy">Order meals, choose pickup or campus delivery, track progress in real time, and manage your cafeteria activity from one place.</p>
            </div>
            <div class="dashboard-hero-actions">
                <a href="<?php echo qb_url('menu.php'); ?>" class="btn btn-primary">Order Meals</a>
                <a href="<?php echo qb_url('order-tracking.php'); ?>" class="btn btn-light">Track Order</a>
            </div>
        </div>

        <div class="dashboard-cards user-dashboard-cards">
            <div class="dashboard-card">
                <h3>Total Orders</h3>
                <p><?php echo $orderCount; ?></p>
            </div>
            <div class="dashboard-card">
                <h3>Active Orders</h3>
                <p><?php echo $activeOrders; ?></p>
            </div>
            <div class="dashboard-card">
                <h3>Reservations</h3>
                <p><?php echo $reservationCount; ?></p>
            </div>
            <div class="dashboard-card">
                <h3>Cart Items</h3>
                <p><?php echo cart_item_count($conn); ?></p>
            </div>
        </div>

        <div class="dashboard-grid">
            <div class="admin-panel-card">
                <h3 class="panel-title">Recommended for you</h3>
                <p class="panel-copy">AI-assisted meal suggestions are shown only here, where they help you choose faster.</p>
                <div class="mini-meal-grid">
                    <?php foreach ($recommended as $meal): ?>
                        <a href="<?php echo qb_url('food-details.php?id=' . $meal['id']); ?>" class="mini-meal-card">
                            <img src="<?php echo qb_url('assets/images/foods/' . $meal['image']); ?>" alt="<?php echo h($meal['item_name']); ?>">
                            <strong><?php echo h($meal['item_name']); ?></strong>
                            <span><?php echo h($meal['category_name']); ?></span>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="admin-panel-card">
                <h3 class="panel-title">Recent Orders</h3>
                <div class="order-history-table-wrapper compact-table-wrap">
                    <table class="order-history-table compact-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Total</th>
                                <th>Status</th>
                                <th>Method</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($recentOrders->num_rows > 0): ?>
                                <?php while ($order = $recentOrders->fetch_assoc()): ?>
                                    <?php $statusClass = strtolower(str_replace(' ', '-', $order['order_status'])); ?>
                                    <tr>
                                        <td>#<?php echo $order['id']; ?></td>
                                        <td>₦<?php echo number_format((float) $order['total_amount'], 2); ?></td>
                                        <td><span class="status-badge status-<?php echo $statusClass; ?>"><?php echo h($order['order_status']); ?></span></td>
                                        <td><?php echo h($order['delivery_method']); ?></td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr><td colspan="4">No orders yet.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="admin-panel-card panel-card-spaced">
            <h3 class="panel-title">Quick Actions</h3>
            <div class="quick-actions-grid">
                <a href="<?php echo qb_url('menu.php'); ?>" class="quick-action-card">
                    <strong>Browse Menu</strong>
                    <span>Explore available meals and add them to cart.</span>
                </a>
                <a href="<?php echo qb_url('cart.php'); ?>" class="quick-action-card">
                    <strong>Open Cart</strong>
                    <span>Review items, totals, and proceed to checkout.</span>
                </a>
                <a href="<?php echo qb_url('reservation.php'); ?>" class="quick-action-card">
                    <strong>Reserve a Spot</strong>
                    <span>Submit a cafeteria reservation and await approval.</span>
                </a>
                <a href="<?php echo qb_url('profile.php'); ?>" class="quick-action-card">
                    <strong>My Profile</strong>
                    <span>See your account details and campus identity data.</span>
                </a>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>