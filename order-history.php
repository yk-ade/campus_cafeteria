<?php
require_once 'includes/db.php';
require_once 'includes/functions.php';
require_login();

$userId = (int) $_SESSION['user_id'];

$sql = "SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();

include 'includes/header.php';
?>

<section class="order-history-section">
    <div class="container">
        <h1 class="section-title">My Order History</h1>
        <p class="section-subtitle">Review every cafeteria order you have placed, including pickup tokens and delivery method.</p>

        <?php $flashMsg = get_flash('success'); ?>
        <?php if ($flashMsg !== ''): ?>
            <div class="alert success"><?php echo h($flashMsg); ?></div>
        <?php endif; ?>

        <?php if ($result->num_rows > 0): ?>
            <div class="order-history-table-wrapper">
                <table class="order-history-table">
                    <thead>
                        <tr>
                            <th>Order</th>
                            <th>Total</th>
                            <th>Method</th>
                            <th>Status</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($order = $result->fetch_assoc()): ?>
                            <?php $statusClass = strtolower(str_replace(' ', '-', $order['order_status'])); ?>
                            <tr>
                                <td>#<?php echo $order['id']; ?><br><small><?php echo h($order['order_token'] ?? ''); ?></small></td>
                                <td>₦<?php echo number_format((float)$order['total_amount'], 2); ?></td>
                                <td><?php echo h($order['delivery_method']); ?></td>
                                <td><span class="status-badge status-<?php echo $statusClass; ?>"><?php echo h($order['order_status']); ?></span></td>
                                <td><?php echo date("M d, Y h:i A", strtotime($order['created_at'])); ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="empty-state-box">
                <h2>No orders yet</h2>
                <p>You have not placed any cafeteria order yet.</p>
                <a href="<?php echo qb_url('menu.php'); ?>" class="btn btn-primary">Start Ordering</a>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php include 'includes/footer.php'; ?>