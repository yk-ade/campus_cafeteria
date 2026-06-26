<?php
require_once 'includes/db.php';
require_once 'includes/functions.php';
require_login();

$userId = (int) $_SESSION['user_id'];
$orderId = isset($_GET['order_id']) ? (int) $_GET['order_id'] : 0;

if (!$orderId) {
    header('Location: ' . qb_url('order-history.php'));
    exit;
}

// Fetch order details
$sql = "SELECT * FROM orders WHERE id = ? AND user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $orderId, $userId);
$stmt->execute();
$order_result = $stmt->get_result();

if ($order_result->num_rows === 0) {
    header('Location: ' . qb_url('order-history.php'));
    exit;
}

$order = $order_result->fetch_assoc();

// Fetch order items
$items_sql = "SELECT oi.*, mi.item_name FROM order_items oi 
              JOIN menu_items mi ON oi.menu_item_id = mi.id 
              WHERE oi.order_id = ?";
$items_stmt = $conn->prepare($items_sql);
$items_stmt->bind_param("i", $orderId);
$items_stmt->execute();
$items_result = $items_stmt->get_result();

// Fetch user details
$user_sql = "SELECT * FROM users WHERE id = ?";
$user_stmt = $conn->prepare($user_sql);
$user_stmt->bind_param("i", $userId);
$user_stmt->execute();
$user_result = $user_stmt->get_result();
$user = $user_result->fetch_assoc();

include 'includes/header.php';
?>

<section class="receipt-section">
    <div class="container">
        <div class="receipt-wrapper">
            <div class="receipt-header">
                <h1>Order Receipt</h1>
                <button class="btn-print" onclick="window.print()">Print Receipt</button>
            </div>

            <div class="receipt-container">
                <!-- Header -->
                <div class="receipt-top">
                    <h2>Rectem Resturant</h2>
                    <p>Receipt for your order</p>
                </div>

                <!-- Order Info -->
                <div class="receipt-section-box">
                    <div class="receipt-row">
                        <span class="label">Order Number:</span>
                        <span class="value">#<?php echo $order['id']; ?></span>
                    </div>
                    <div class="receipt-row">
                        <span class="label">Order Token:</span>
                        <span class="value"><?php echo h($order['order_token'] ?? 'N/A'); ?></span>
                    </div>
                    <div class="receipt-row">
                        <span class="label">Order Date:</span>
                        <span class="value"><?php echo date("M d, Y h:i A", strtotime($order['created_at'])); ?></span>
                    </div>
                    <div class="receipt-row">
                        <span class="label">Order Status:</span>
                        <span class="value">
                            <?php $statusClass = strtolower(str_replace(' ', '-', $order['order_status'])); ?>
                            <span class="status-badge status-<?php echo $statusClass; ?>"><?php echo h($order['order_status']); ?></span>
                        </span>
                    </div>
                </div>

                <!-- Customer Info -->
                <div class="receipt-section-box">
                    <h3>Customer Information</h3>
                    <div class="receipt-row">
                        <span class="label">Name:</span>
                        <span class="value"><?php echo h($user['full_name']); ?></span>
                    </div>
                    <div class="receipt-row">
                        <span class="label">Email:</span>
                        <span class="value"><?php echo h($user['email']); ?></span>
                    </div>
                    <div class="receipt-row">
                        <span class="label">Phone:</span>
                        <span class="value"><?php echo h($user['phone']); ?></span>
                    </div>
                </div>

                <!-- Delivery Info -->
                <div class="receipt-section-box">
                    <h3>Delivery Information</h3>
                    <div class="receipt-row">
                        <span class="label">Delivery Method:</span>
                        <span class="value"><?php echo h($order['delivery_method']); ?></span>
                    </div>
                    <?php if ($order['delivery_address']): ?>
                        <div class="receipt-row">
                            <span class="label">Delivery Address:</span>
                            <span class="value"><?php echo h($order['delivery_address']); ?></span>
                        </div>
                    <?php endif; ?>
                    <div class="receipt-row">
                        <span class="label">Payment Method:</span>
                        <span class="value"><?php echo h($order['payment_method']); ?></span>
                    </div>
                </div>

                <!-- Order Items -->
                <div class="receipt-items">
                    <h3>Order Items</h3>
                    <table class="receipt-table">
                        <thead>
                            <tr>
                                <th>Item</th>
                                <th>Qty</th>
                                <th>Price</th>
                                <th>Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $subtotal = 0;
                            while ($item = $items_result->fetch_assoc()): 
                                $itemTotal = $item['quantity'] * $item['price'];
                                $subtotal += $itemTotal;
                            ?>
                                <tr>
                                    <td><?php echo h($item['item_name']); ?></td>
                                    <td><?php echo $item['quantity']; ?></td>
                                    <td>₦<?php echo number_format((float)$item['price'], 2); ?></td>
                                    <td>₦<?php echo number_format($itemTotal, 2); ?></td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Payment Summary -->
                <div class="receipt-summary">
                    <div class="summary-row">
                        <span>Subtotal:</span>
                        <span>₦<?php echo number_format($subtotal, 2); ?></span>
                    </div>
                    <div class="summary-row total">
                        <span>Total Amount:</span>
                        <span>₦<?php echo number_format((float)$order['total_amount'], 2); ?></span>
                    </div>
                </div>

                <!-- Special Requests -->
                <?php if ($order['special_request']): ?>
                    <div class="receipt-section-box">
                        <h3>Special Requests</h3>
                        <p><?php echo h($order['special_request']); ?></p>
                    </div>
                <?php endif; ?>

                <!-- Footer -->
                <div class="receipt-footer">
                    <p>Thank you for your order!</p>
                    <p>Please present this receipt when picking up your order.</p>
                </div>
            </div>

            <div class="receipt-actions">
                <a href="<?php echo qb_url('order-history.php'); ?>" class="btn btn-light">Back to History</a>
                <a href="<?php echo qb_url('order-tracking.php?order_id=' . $orderId); ?>" class="btn btn-primary">Track Order</a>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
