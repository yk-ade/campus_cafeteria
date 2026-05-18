<?php
require_once '../includes/db.php';
require_once '../includes/functions.php';
require_admin();

$result = $conn->query("SELECT id, full_name, matric_no, email, phone, role, created_at FROM users ORDER BY created_at DESC");

include '../includes/header.php';
?>

<section class="order-history-section">
    <div class="container">
        <h1 class="section-title">Manage Users</h1>
        <p class="section-subtitle">View student, staff, and admin accounts registered in the system.</p>

        <div class="page-action-bar">
            <a href="<?php echo qb_url('admin/dashboard.php'); ?>" class="btn btn-light btn-sm">← Back to Dashboard</a>
        </div>

        <div class="order-history-table-wrapper">
            <table class="order-history-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Matric No</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Role</th>
                        <th>Joined</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result && $result->num_rows > 0): ?>
                        <?php while ($user = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo $user['id']; ?></td>
                                <td><?php echo h($user['full_name']); ?></td>
                                <td><?php echo h($user['matric_no'] ?? '—'); ?></td>
                                <td><?php echo h($user['email']); ?></td>
                                <td><?php echo h($user['phone']); ?></td>
                                <td><span class="status-badge status-<?php echo strtolower($user['role']); ?>"><?php echo ucfirst(h($user['role'])); ?></span></td>
                                <td><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="7">No users found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>

<?php include '../includes/footer.php'; ?>