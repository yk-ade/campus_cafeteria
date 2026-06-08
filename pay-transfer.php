<?php
require_once 'includes/db.php';
require_once 'includes/functions.php';
require_login();

if (!is_student()) {
    header("Location: " . qb_url('index.php'));
    exit();
}

$orderId = isset($_GET['order_id']) ? (int) $_GET['order_id'] : 0;
if ($orderId <= 0) {
    header("Location: " . qb_url('dashboard.php'));
    exit();
}

$userId = (int) $_SESSION['user_id'];

// Verify the order belongs to this user
$stmt = $conn->prepare("SELECT id, total_amount, payment_method FROM orders WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $orderId, $userId);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();

if (!$order || $order['payment_method'] !== 'Bank Transfer') {
    header("Location: " . qb_url('order-history.php'));
    exit();
}

// Handle "Already Paid" confirmation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_paid'])) {
    $updatePayment = $conn->prepare("UPDATE payments SET payment_status = 'Pending' WHERE order_id = ?");
    $updatePayment->bind_param("i", $orderId);
    $updatePayment->execute();

    set_flash('success', 'Payment confirmation received! Your order will be processed once the transfer is verified.');
    header("Location: " . qb_url('order-history.php'));
    exit();
}

include 'includes/header.php';
?>

<section class="checkout-section">
    <div class="container">
        <h1 class="section-title">Bank Transfer Payment</h1>
        <p class="section-subtitle">Transfer the exact amount below and click "I've Already Paid" to confirm.</p>

        <div class="bank-transfer-layout">
            <div class="bank-transfer-card">
                <div class="bank-card-header">
                    <span class="bank-card-icon">🏦</span>
                    <h2>Transfer Details</h2>
                </div>

                <div class="bank-detail-grid">
                    <div class="bank-detail-item">
                        <span class="detail-label">Account Number</span>
                        <span class="detail-value bank-account-number" id="accountNumber">9024789388</span>
                        <button type="button" class="copy-btn" onclick="copyAccount()" id="copyBtn">Copy</button>
                    </div>
                    <div class="bank-detail-item">
                        <span class="detail-label">Bank Name</span>
                        <span class="detail-value">Moniepoint</span>
                    </div>
                    <div class="bank-detail-item">
                        <span class="detail-label">Account Name</span>
                        <span class="detail-value">Rectem Cafeteria</span>
                    </div>
                    <div class="bank-detail-item bank-amount-highlight">
                        <span class="detail-label">Amount to Transfer</span>
                        <span class="detail-value bank-amount">₦<?php echo number_format((float) $order['total_amount'], 2); ?></span>
                    </div>
                </div>

                <div class="reservation-info-banner">
                    <strong>Important</strong>
                    <p>Please transfer the exact amount shown above. Use your Order ID <strong>#<?php echo $orderId; ?></strong> as the transfer narration/reference for faster verification.</p>
                </div>

                <form method="POST">
                    <input type="hidden" name="confirm_paid" value="1">
                    <button type="submit" class="btn btn-primary btn-full-width" onclick="return confirm('Are you sure you have completed the bank transfer of ₦<?php echo number_format((float) $order['total_amount'], 2); ?>?')">
                        I've Already Paid
                    </button>
                </form>

                <a href="<?php echo qb_url('order-history.php'); ?>" class="btn btn-light btn-full-width" style="margin-top:12px;">
                    Pay Later — Go to Order History
                </a>
            </div>
        </div>
    </div>
</section>

<script>
function copyAccount() {
    const text = document.getElementById('accountNumber').textContent;
    navigator.clipboard.writeText(text).then(() => {
        const btn = document.getElementById('copyBtn');
        btn.textContent = 'Copied!';
        setTimeout(() => { btn.textContent = 'Copy'; }, 2000);
    });
}
</script>

<?php include 'includes/footer.php'; ?>
