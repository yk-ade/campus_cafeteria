<?php
require_once '../includes/db.php';
require_once '../includes/functions.php';
require_admin();

$sql = "SELECT r.*, u.matric_no 
        FROM reservations r 
        LEFT JOIN users u ON r.user_id = u.id 
        ORDER BY r.created_at DESC";
$result = $conn->query($sql);

include '../includes/header.php';
?>

<section class="order-history-section">
    <div class="container">
        <h1 class="section-title">Manage Reservations</h1>
        <p class="section-subtitle">Review every reservation detail — occasion, dietary needs, special requests — and approve or reject.</p>

        <div class="admin-top-actions">
            <a href="<?php echo qb_url('admin/dashboard.php'); ?>" class="btn btn-light">Back to Dashboard</a>
        </div>

        <?php if ($result && $result->num_rows > 0): ?>
            <div class="reservation-history-wrapper">
                <?php while ($res = $result->fetch_assoc()): ?>
                    <?php $statusClass = strtolower($res['status']); ?>
                    <div class="reservation-card">
                        <div class="reservation-card-header">
                            <div>
                                <span class="reservation-id">#RES-<?php echo $res['id']; ?></span>
                                <span class="status-badge status-<?php echo $statusClass; ?>"><?php echo h($res['status']); ?></span>
                            </div>
                            <span class="reservation-date-badge"><?php echo date("M d, Y", strtotime($res['reservation_date'])); ?></span>
                        </div>
                        <div class="reservation-card-body">
                            <div class="reservation-detail">
                                <span class="detail-label">Full Name</span>
                                <span class="detail-value"><?php echo h($res['full_name']); ?></span>
                            </div>
                            <div class="reservation-detail">
                                <span class="detail-label">Email</span>
                                <span class="detail-value"><?php echo h($res['email']); ?></span>
                            </div>
                            <div class="reservation-detail">
                                <span class="detail-label">Phone</span>
                                <span class="detail-value"><?php echo h($res['phone']); ?></span>
                            </div>
                            <?php if (!empty($res['matric_no'])): ?>
                            <div class="reservation-detail">
                                <span class="detail-label">Matric No</span>
                                <span class="detail-value"><?php echo h($res['matric_no']); ?></span>
                            </div>
                            <?php endif; ?>
                            <div class="reservation-detail">
                                <span class="detail-label">Time</span>
                                <span class="detail-value"><?php echo date("g:i A", strtotime($res['reservation_time'])); ?></span>
                            </div>
                            <div class="reservation-detail">
                                <span class="detail-label">Guests</span>
                                <span class="detail-value"><?php echo (int) $res['guests']; ?> <?php echo $res['guests'] == 1 ? 'person' : 'people'; ?></span>
                            </div>
                            <div class="reservation-detail">
                                <span class="detail-label">Submitted</span>
                                <span class="detail-value"><?php echo date("M d, Y h:i A", strtotime($res['created_at'])); ?></span>
                            </div>

                            <?php
                            // Parse the combined special_request field for structured data
                            $specialRequest = $res['special_request'] ?? '';
                            $occasion = '';
                            $dietary = '';
                            $notes = '';

                            if ($specialRequest !== '') {
                                $lines = explode("\n", $specialRequest);
                                $remainingLines = [];
                                foreach ($lines as $line) {
                                    $line = trim($line);
                                    if (str_starts_with($line, 'Occasion:')) {
                                        $occasion = trim(substr($line, 9));
                                    } elseif (str_starts_with($line, 'Dietary:')) {
                                        $dietary = trim(substr($line, 8));
                                    } elseif ($line !== '') {
                                        $remainingLines[] = $line;
                                    }
                                }
                                $notes = implode("\n", $remainingLines);
                            }
                            ?>

                            <?php if ($occasion !== ''): ?>
                            <div class="reservation-detail">
                                <span class="detail-label">Occasion / Purpose</span>
                                <span class="detail-value"><?php echo h($occasion); ?></span>
                            </div>
                            <?php endif; ?>

                            <?php if ($dietary !== ''): ?>
                            <div class="reservation-detail full-width">
                                <span class="detail-label">Dietary Requirements / Allergies</span>
                                <span class="detail-value reservation-highlight"><?php echo h($dietary); ?></span>
                            </div>
                            <?php endif; ?>

                            <?php if ($notes !== ''): ?>
                            <div class="reservation-detail full-width">
                                <span class="detail-label">Special Requests / Additional Notes</span>
                                <span class="detail-value"><?php echo nl2br(h($notes)); ?></span>
                            </div>
                            <?php endif; ?>

                            <?php if ($specialRequest !== '' && $occasion === '' && $dietary === '' && $notes === ''): ?>
                            <!-- Fallback: show raw special_request if it wasn't in structured format -->
                            <div class="reservation-detail full-width">
                                <span class="detail-label">Notes</span>
                                <span class="detail-value"><?php echo nl2br(h($specialRequest)); ?></span>
                            </div>
                            <?php endif; ?>
                        </div>

                        <div class="reservation-card-footer">
                            <form action="<?php echo qb_url('admin/update_reservation.php'); ?>" method="POST" class="inline-actions">
                                <input type="hidden" name="reservation_id" value="<?php echo $res['id']; ?>">
                                <select name="status" required>
                                    <option value="Pending" <?php echo ($res['status'] === 'Pending') ? 'selected' : ''; ?>>Pending</option>
                                    <option value="Approved" <?php echo ($res['status'] === 'Approved') ? 'selected' : ''; ?>>Approved</option>
                                    <option value="Rejected" <?php echo ($res['status'] === 'Rejected') ? 'selected' : ''; ?>>Rejected</option>
                                </select>
                                <button type="submit" class="btn btn-primary btn-sm">Update Status</button>
                            </form>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div class="empty-state-box text-center">
                <h2>No reservations yet</h2>
                <p>No cafeteria reservations have been submitted.</p>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php include '../includes/footer.php'; ?>