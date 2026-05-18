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

if (!$order || $order['payment_method'] !== 'Demo Card Payment') {
    header("Location: " . qb_url('order-history.php'));
    exit();
}

$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cardName = trim($_POST['card_name'] ?? '');
    $cardNumber = trim($_POST['card_number'] ?? '');
    $expiryDate = trim($_POST['expiry_date'] ?? '');
    $cvv = trim($_POST['cvv'] ?? '');

    if ($cardName === '' || $cardNumber === '' || $expiryDate === '' || $cvv === '') {
        $message = 'Please fill in all card details.';
        $messageType = 'error';
    } elseif (strlen(preg_replace('/\s+/', '', $cardNumber)) < 16) {
        $message = 'Please enter a valid 16-digit card number.';
        $messageType = 'error';
    } elseif (strlen($cvv) < 3) {
        $message = 'Please enter a valid CVV.';
        $messageType = 'error';
    } else {
        // Demo: mark payment as paid
        $updatePayment = $conn->prepare("UPDATE payments SET payment_status = 'Paid' WHERE order_id = ?");
        $updatePayment->bind_param("i", $orderId);
        $updatePayment->execute();

        set_flash('success', 'Card payment processed successfully! Your order is now in the kitchen queue.');
        header("Location: " . qb_url('order-success.php?order_id=' . $orderId));
        exit();
    }
}

include 'includes/header.php';
?>

<section class="checkout-section">
    <div class="container">
        <h1 class="section-title">Card Payment</h1>
        <p class="section-subtitle">Enter your card details to complete payment for Order #<?php echo $orderId; ?></p>

        <?php if ($message !== ''): ?>
            <div class="alert <?php echo $messageType; ?>"><?php echo h($message); ?></div>
        <?php endif; ?>

        <div class="checkout-wrapper">
            <div class="checkout-form-box">
                <form method="POST" class="checkout-form" id="cardPaymentForm">
                    <div class="payment-card-visual">
                        <div class="card-visual-top">
                            <span class="card-chip"></span>
                            <span class="card-type">VISA</span>
                        </div>
                        <div class="card-number-display" id="cardNumberDisplay">•••• •••• •••• ••••</div>
                        <div class="card-visual-bottom">
                            <div>
                                <span class="card-label">CARD HOLDER</span>
                                <span class="card-holder-display" id="cardHolderDisplay">YOUR NAME</span>
                            </div>
                            <div>
                                <span class="card-label">EXPIRES</span>
                                <span class="card-expiry-display" id="cardExpiryDisplay">MM/YY</span>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Cardholder Name <span class="required-star">*</span></label>
                        <input type="text" name="card_name" id="cardNameInput" placeholder="Name on card" required>
                    </div>

                    <div class="form-group">
                        <label>Card Number <span class="required-star">*</span></label>
                        <input type="text" name="card_number" id="cardNumberInput" placeholder="1234 5678 9012 3456" maxlength="19" required>
                    </div>

                    <div class="checkout-toggle">
                        <div class="form-group">
                            <label>Expiry Date <span class="required-star">*</span></label>
                            <input type="text" name="expiry_date" id="cardExpiryInput" placeholder="MM/YY" maxlength="5" required>
                        </div>
                        <div class="form-group">
                            <label>CVV <span class="required-star">*</span></label>
                            <input type="text" name="cvv" placeholder="123" maxlength="4" required>
                        </div>
                    </div>

                    <div class="reservation-info-banner">
                        <strong>Demo Payment</strong>
                        <p>This is a demonstration. No real card will be charged. Enter any valid-format details to proceed.</p>
                    </div>

                    <button type="submit" class="btn btn-primary">Pay ₦<?php echo number_format((float) $order['total_amount'], 2); ?></button>
                </form>
            </div>

            <div class="checkout-summary-box">
                <h2 class="panel-title checkout-summary-heading">Payment Summary</h2>

                <div class="summary-row">
                    <span>Order ID</span>
                    <span>#<?php echo $orderId; ?></span>
                </div>

                <div class="summary-row">
                    <span>Payment Method</span>
                    <span>Card Payment</span>
                </div>

                <div class="summary-row total-row">
                    <span>Amount Due</span>
                    <span>₦<?php echo number_format((float) $order['total_amount'], 2); ?></span>
                </div>

                <div class="reservation-info-banner" style="margin-top:18px;">
                    <strong>Secure Checkout</strong>
                    <p>Your payment details are processed securely for this demo transaction.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const nameInput = document.getElementById('cardNameInput');
    const numberInput = document.getElementById('cardNumberInput');
    const expiryInput = document.getElementById('cardExpiryInput');

    const nameDisplay = document.getElementById('cardHolderDisplay');
    const numberDisplay = document.getElementById('cardNumberDisplay');
    const expiryDisplay = document.getElementById('cardExpiryDisplay');

    if (nameInput && nameDisplay) {
        nameInput.addEventListener('input', () => {
            nameDisplay.textContent = nameInput.value.toUpperCase() || 'YOUR NAME';
        });
    }

    if (numberInput && numberDisplay) {
        numberInput.addEventListener('input', (e) => {
            let val = e.target.value.replace(/\D/g, '').substring(0, 16);
            let formatted = val.replace(/(.{4})/g, '$1 ').trim();
            e.target.value = formatted;
            numberDisplay.textContent = formatted || '•••• •••• •••• ••••';
        });
    }

    if (expiryInput && expiryDisplay) {
        expiryInput.addEventListener('input', (e) => {
            let val = e.target.value.replace(/\D/g, '').substring(0, 4);
            if (val.length >= 3) {
                val = val.substring(0, 2) + '/' + val.substring(2);
            }
            e.target.value = val;
            expiryDisplay.textContent = val || 'MM/YY';
        });
    }
});
</script>

<?php include 'includes/footer.php'; ?>
