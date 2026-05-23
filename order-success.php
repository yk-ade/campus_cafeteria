<?php
require_once 'includes/db.php';
require_once 'includes/functions.php';
require_login();

$orderId = isset($_GET['order_id']) ? (int) $_GET['order_id'] : 0;
$flashMsg = get_flash('success');

include 'includes/header.php';
?>

<section class="success-section">
    <div class="container">
        <div class="success-box">
            <?php if ($flashMsg !== ''): ?>
                <div class="alert success"><?php echo h($flashMsg); ?></div>
            <?php endif; ?>

            <h1>Order placed successfully</h1>
            <p>Your cafeteria order has been created and sent into the live queue.</p>
            <p><strong>Order ID:</strong> #<?php echo $orderId; ?></p>
            <p>You can now track its progress from your tracking page.</p>
            <p> Pls come with your reciept</p>

            <div class="success-buttons">
                <a href="<?php echo qb_url('view-receipt.php?order_id=' . $orderId); ?>" class="btn btn-primary">View Receipt</a>
                <a href="<?php echo qb_url('order-tracking.php'); ?>" class="btn btn-primary">Track Order</a>
                <a href="<?php echo qb_url('order-history.php'); ?>" class="btn btn-light">Order History</a>
                <a href="<?php echo qb_url('dashboard.php'); ?>" class="btn btn-light">Go to Dashboard</a>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>