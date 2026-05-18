<?php
require_once '../includes/db.php';
require_once '../includes/functions.php';
require_admin();

/* ----------------------------------------------------------------
   TODAY-ONLY stats (reset automatically every new calendar day)
   We filter by DATE(created_at) = CURDATE()
   ---------------------------------------------------------------- */
$todayOrdersResult  = $conn->query("SELECT COUNT(*) AS cnt FROM orders WHERE DATE(created_at) = CURDATE()");
$todayOrders        = (int)($todayOrdersResult->fetch_assoc()['cnt'] ?? 0);

$todayRevenueResult = $conn->query("SELECT COALESCE(SUM(total_amount),0) AS rev FROM orders WHERE DATE(created_at) = CURDATE()");
$todayRevenue       = (float)($todayRevenueResult->fetch_assoc()['rev'] ?? 0);

/* ----------------------------------------------------------------
   All-time / persistent stats
   ---------------------------------------------------------------- */
$totalUsersResult   = $conn->query("SELECT COUNT(*) AS cnt FROM users WHERE role='user'");
$totalUsers         = (int)($totalUsersResult->fetch_assoc()['cnt'] ?? 0);

$pendingResult      = $conn->query("SELECT COUNT(*) AS cnt FROM orders WHERE order_status IN ('Pending','Confirmed','Preparing')");
$pendingOrders      = (int)($pendingResult->fetch_assoc()['cnt'] ?? 0);

$reservationResult  = $conn->query("SELECT COUNT(*) AS cnt FROM reservations WHERE DATE(created_at) = CURDATE()");
$todayReservations  = (int)($reservationResult->fetch_assoc()['cnt'] ?? 0);

$insight = demand_insights($conn);

$latestOrders = $conn->query(
    "SELECT orders.id, users.full_name, orders.total_amount, orders.order_status, orders.created_at, orders.delivery_method
     FROM orders
     INNER JOIN users ON orders.user_id = users.id
     ORDER BY orders.created_at DESC LIMIT 7"
);

include '../includes/header.php';
?>

<section class="admin-dashboard">
    <div class="container">

        <div class="admin-page-head">
            <div>
                <h1 class="section-title">Admin Dashboard</h1>
                <p class="section-subtitle">
                    Today is <strong><?php echo date('l, F j, Y'); ?></strong>.
                    Today's orders and revenue figures reset automatically at midnight every day.
                </p>
            </div>
            <div class="admin-top-actions">
                <a href="<?php echo qb_url('admin/orders.php'); ?>"      class="btn btn-primary btn-sm">Manage Orders</a>
                <a href="<?php echo qb_url('admin/menu.php'); ?>"        class="btn btn-light btn-sm">Menu</a>
                <a href="<?php echo qb_url('admin/add-menu.php'); ?>"    class="btn btn-light btn-sm">Add Dish</a>
                <a href="<?php echo qb_url('admin/reservations.php'); ?>" class="btn btn-light btn-sm">Reservations</a>
                <a href="<?php echo qb_url('admin/users.php'); ?>"       class="btn btn-light btn-sm">Users</a>
            </div>
        </div>

        <!-- ── Today's live stats ── -->
        <p class="stats-group-label">Today's Activity <span class="stats-reset-note">(resets at midnight)</span></p>
        <div class="dashboard-cards">
            <div class="dashboard-card today-card">
                <h3>Today's Orders</h3>
                <p><?php echo $todayOrders; ?></p>
            </div>
            <div class="dashboard-card today-card">
                <h3>Today's Revenue</h3>
                <p>₦<?php echo number_format($todayRevenue, 2); ?></p>
            </div>
            <div class="dashboard-card today-card">
                <h3>Today's Reservations</h3>
                <p><?php echo $todayReservations; ?></p>
            </div>
            <div class="dashboard-card today-card">
                <h3>Active Orders</h3>
                <p><?php echo $pendingOrders; ?></p>
            </div>
        </div>

        <!-- ── Persistent stats ── -->
        <p class="stats-group-label" style="margin-top:28px;">Platform Overview</p>
        <div class="dashboard-cards">
            <div class="dashboard-card"><h3>Registered Students</h3><p><?php echo $totalUsers; ?></p></div>
        </div>

        <!-- ── Demand insights + recent orders ── -->
        <div class="dashboard-grid" style="margin-top:28px;">
            <div class="admin-panel-card">
                <h3 class="panel-title">Demand Insights</h3>
                <div class="insight-grid">
                    <div class="insight-card"><span>Trending meal</span>  <strong><?php echo h($insight['top_meal']); ?></strong></div>
                    <div class="insight-card"><span>Peak period</span>    <strong><?php echo h($insight['peak_window']); ?></strong></div>
                    <div class="insight-card"><span>Stock signal</span>   <strong><?php echo h($insight['stock_signal']); ?></strong></div>
                </div>
            </div>

            <div class="admin-panel-card">
                <h3 class="panel-title">Recent Orders</h3>
                <div class="order-history-table-wrapper compact-table-wrap">
                    <table class="order-history-table compact-table">
                        <thead><tr><th>ID</th><th>Student</th><th>Amount</th><th>Status</th></tr></thead>
                        <tbody>
                        <?php if ($latestOrders && $latestOrders->num_rows > 0): ?>
                            <?php while ($o = $latestOrders->fetch_assoc()): $sc = strtolower(str_replace(' ','-',$o['order_status'])); ?>
                            <tr>
                                <td>#<?php echo $o['id']; ?></td>
                                <td><?php echo h($o['full_name']); ?></td>
                                <td>₦<?php echo number_format((float)$o['total_amount'],2); ?></td>
                                <td><span class="status-badge status-<?php echo $sc; ?>"><?php echo h($o['order_status']); ?></span></td>
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

    </div>
</section>

<?php include '../includes/footer.php'; ?>
