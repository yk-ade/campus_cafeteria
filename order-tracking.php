<?php
require_once 'includes/db.php';
require_once 'includes/functions.php';
require_login();

if (!is_student()) {
    header("Location: " . qb_url('index.php'));
    exit();
}

$userId = (int) $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT id, total_amount, delivery_method, order_status, created_at
                        FROM orders
                        WHERE user_id = ?
                        ORDER BY created_at DESC
                        LIMIT 1");
$stmt->bind_param("i", $userId);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();

$readyEstimate = estimate_ready_minutes($conn, $userId);

include 'includes/header.php';
?>

<section class="page-banner">
    <div class="container">
        <div class="page-banner-inner">
            <h1 class="section-title">Order Tracking</h1>
            <p class="section-subtitle" style="margin-bottom:0;">Follow your latest order status in real time.</p>
        </div>
    </div>
</section>

<section>
    <div class="container">
        <?php if ($order): ?>
            <?php $statusClass = strtolower(str_replace(' ', '-', $order['order_status'])); ?>
            <div class="checkout-form-box">
                <div class="tracking-summary">
                    <div>
                        <h3 class="panel-title">Current Order</h3>
                        <p><strong>Order ID:</strong> #<?php echo $order['id']; ?></p>
                        <p><strong>Method:</strong> <?php echo h($order['delivery_method']); ?></p>
                        <p><strong>Total:</strong> ₦<?php echo number_format((float) $order['total_amount'], 2); ?></p>
                    </div>
                    <div class="tracking-ai-card">
                        <span class="dashboard-kicker">Estimated ready time</span>
                        <h3><?php echo $readyEstimate; ?> mins</h3>
                        <p>The estimate adjusts to your cart size and current preparation load.</p>
                    </div>
                </div>

                <div class="tracking-status-row">
                    <span class="status-badge status-<?php echo $statusClass; ?>"><?php echo h($order['order_status']); ?></span>
                </div>

                <div class="tracking-timeline">
                    <?php
                    $steps = ['Pending','Confirmed','Preparing','Ready for Pickup','Out for Delivery','Delivered','Completed'];
                    $currentIndex = array_search($order['order_status'], $steps, true);
                    if ($currentIndex === false) {
                        $currentIndex = 0;
                    }
                    foreach ($steps as $index => $step):
                    ?>
                        <div class="tracking-step <?php echo ($index <= $currentIndex) ? 'done' : ''; ?>">
                            <span><?php echo $index + 1; ?></span>
                            <strong><?php echo h($step); ?></strong>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php else: ?>
            <div class="empty-state-box">
                <h2>No active order yet</h2>
                <p>Once you place an order, your live tracking details will appear here.</p>
                <a href="<?php echo qb_url('menu.php'); ?>" class="btn btn-primary">Browse Menu</a>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php include 'includes/footer.php'; ?>