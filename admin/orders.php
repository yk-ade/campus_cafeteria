<?php
require_once '../includes/db.php';
require_once '../includes/functions.php';
require_admin();

$sql = "SELECT orders.*, users.full_name, users.matric_no
        FROM orders
        INNER JOIN users ON orders.user_id = users.id
        ORDER BY orders.created_at DESC";

$result = $conn->query($sql);

// Pre-fetch all order items grouped by order_id
$allItems = [];
$itemsQuery = $conn->query(
    "SELECT oi.order_id, m.item_name, oi.quantity, oi.price
     FROM order_items oi
     INNER JOIN menu_items m ON oi.menu_item_id = m.id
     ORDER BY oi.id ASC"
);
if ($itemsQuery) {
    while ($row = $itemsQuery->fetch_assoc()) {
        $allItems[(int)$row['order_id']][] = $row;
    }
}

include '../includes/header.php';
?>

<section class="order-history-section">
    <div class="container">
        <h1 class="section-title">Manage Orders</h1>
        <p class="section-subtitle">Review all student orders, see exactly what was ordered, and coordinate with the kitchen.</p>

        <?php if ($result && $result->num_rows > 0): ?>
            <div class="order-cards-grid">
                <?php while ($order = $result->fetch_assoc()): ?>
                    <?php $statusClass = strtolower(str_replace(' ', '-', $order['order_status'])); ?>
                    <div class="order-detail-card">
                        <div class="order-card-header">
                            <div class="order-card-id">
                                <strong>#<?php echo $order['id']; ?></strong>
                                <small><?php echo h($order['order_token'] ?? ''); ?></small>
                            </div>
                            <span class="status-badge status-<?php echo $statusClass; ?>"><?php echo h($order['order_status']); ?></span>
                        </div>

                        <div class="order-card-body">
                            <div class="order-card-meta">
                                <div class="order-meta-item">
                                    <span class="detail-label">Student</span>
                                    <span class="detail-value"><?php echo h($order['full_name']); ?></span>
                                </div>
                                <div class="order-meta-item">
                                    <span class="detail-label">Matric No</span>
                                    <span class="detail-value"><?php echo h($order['matric_no']); ?></span>
                                </div>
                                <div class="order-meta-item">
                                    <span class="detail-label">Method</span>
                                    <span class="detail-value"><?php echo h($order['delivery_method']); ?></span>
                                </div>
                                <div class="order-meta-item">
                                    <span class="detail-label">Total</span>
                                    <span class="detail-value" style="color:var(--primary);font-weight:700;">₦<?php echo number_format((float)$order['total_amount'], 2); ?></span>
                                </div>
                                <div class="order-meta-item">
                                    <span class="detail-label">Date</span>
                                    <span class="detail-value"><?php echo date('M d, Y g:i A', strtotime($order['created_at'])); ?></span>
                                </div>
                            </div>

                            <!-- Items ordered -->
                            <div class="order-items-list">
                                <span class="detail-label">Items Ordered</span>
                                <?php
                                $items = $allItems[(int)$order['id']] ?? [];
                                if ($items):
                                ?>
                                    <ul class="ordered-food-list">
                                        <?php foreach ($items as $item): ?>
                                            <li>
                                                <span class="food-item-name"><?php echo h($item['item_name']); ?></span>
                                                <span class="food-item-qty">×<?php echo (int)$item['quantity']; ?></span>
                                                <span class="food-item-price">₦<?php echo number_format((float)$item['price'] * (int)$item['quantity'], 2); ?></span>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                <?php else: ?>
                                    <p class="no-items-note">No item details recorded.</p>
                                <?php endif; ?>
                            </div>

                            <!-- Special request -->
                            <?php if (!empty($order['special_request'])): ?>
                                <div class="order-special-request">
                                    <span class="detail-label">Special Request</span>
                                    <p class="special-request-text"><?php echo nl2br(h($order['special_request'])); ?></p>
                                </div>
                            <?php endif; ?>

                            <!-- Delivery address -->
                            <?php if (!empty($order['delivery_address']) && $order['delivery_address'] !== 'Pickup Counter'): ?>
                                <div class="order-meta-item" style="margin-top:8px;">
                                    <span class="detail-label">Delivery Address</span>
                                    <span class="detail-value"><?php echo h($order['delivery_address']); ?></span>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="order-card-footer">
                            <form action="<?php echo qb_url('admin/update_order.php'); ?>" method="POST" class="order-update-form">
                                <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                <select name="status" required>
                                    <?php
                                    $isPickup = ($order['delivery_method'] === 'Pickup');
                                    if ($isPickup) {
                                        $statuses = ['Pending','Confirmed','Preparing','Ready for Pickup','Completed','Cancelled'];
                                    } else {
                                        $statuses = ['Pending','Confirmed','Preparing','Out for Delivery','Delivered','Completed','Cancelled'];
                                    }
                                    foreach ($statuses as $status):
                                    ?>
                                        <option value="<?php echo h($status); ?>" <?php echo ($order['order_status'] === $status) ? 'selected' : ''; ?>>
                                            <?php echo h($status); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <button type="submit" class="btn btn-primary btn-sm">Update</button>
                            </form>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div class="empty-state-box text-center">
                <h2>No orders yet</h2>
                <p>No orders have been placed by students.</p>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php include '../includes/footer.php'; ?>