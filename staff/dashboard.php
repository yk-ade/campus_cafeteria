<?php
require_once '../includes/db.php';
require_once '../includes/functions.php';
require_staff_or_admin();

$queueSql = "SELECT COUNT(*) AS total FROM orders WHERE order_status IN ('Pending','Confirmed','Preparing')";
$queue = (int) ($conn->query($queueSql)->fetch_assoc()['total'] ?? 0);

$readySql = "SELECT COUNT(*) AS total FROM orders WHERE order_status = 'Ready for Pickup'";
$ready = (int) ($conn->query($readySql)->fetch_assoc()['total'] ?? 0);

$deliverySql = "SELECT COUNT(*) AS total FROM orders WHERE order_status = 'Out for Delivery'";
$delivery = (int) ($conn->query($deliverySql)->fetch_assoc()['total'] ?? 0);

$completedSql = "SELECT COUNT(*) AS total FROM orders WHERE order_status = 'Completed'";
$completed = (int) ($conn->query($completedSql)->fetch_assoc()['total'] ?? 0);

$prepEstimate = estimate_ready_minutes($conn, null);

include '../includes/header.php';
?>

<section class="admin-dashboard">
    <div class="container">
        <h1 class="section-title">Kitchen Dashboard</h1>
        <p class="section-subtitle">Handle the live order queue, preparation flow, and readiness updates.</p>

        <div class="admin-top-actions">
            <a href="<?php echo qb_url('staff/orders.php'); ?>" class="btn btn-primary">Open Order Queue</a>
            <?php if (is_admin()): ?>
                <a href="<?php echo qb_url('admin/dashboard.php'); ?>" class="btn btn-light">Admin Dashboard</a>
            <?php endif; ?>
        </div>

        <div class="dashboard-cards">
            <div class="dashboard-card"><h3>Incoming Queue</h3><p><?php echo $queue; ?></p></div>
            <div class="dashboard-card"><h3>Ready for Pickup</h3><p><?php echo $ready; ?></p></div>
            <div class="dashboard-card"><h3>Out for Delivery</h3><p><?php echo $delivery; ?></p></div>
            <div class="dashboard-card"><h3>Completed</h3><p><?php echo $completed; ?></p></div>
        </div>

        <div class="admin-panel-card">
            <h3 class="panel-title">Preparation Load Insight</h3>
            <div class="ai-inline-card">
                <span class="dashboard-kicker">Estimated prep load</span>
                <h3 style="font-family:'Poppins',sans-serif; margin:6px 0;"><?php echo $prepEstimate; ?> minutes average</h3>
                <p>Use the queue view to move orders from confirmed to preparing, ready, delivered, and completed at the right pace.</p>
            </div>
        </div>
    </div>
</section>

<?php include '../includes/footer.php'; ?>