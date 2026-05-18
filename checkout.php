<?php
require_once 'includes/db.php';
require_once 'includes/functions.php';
require_login();

if (!is_student()) {
    header("Location: " . qb_url('index.php'));
    exit();
}

$userId = (int) $_SESSION['user_id'];

$locations = $conn->query("SELECT location_name FROM campus_locations ORDER BY location_name ASC");

$sql = "SELECT cart.id AS cart_id, cart.quantity, menu_items.id AS menu_item_id, menu_items.item_name, menu_items.price
        FROM cart
        INNER JOIN menu_items ON cart.menu_item_id = menu_items.id
        WHERE cart.user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();

$cartItems = [];
$subtotal = 0;

while ($row = $result->fetch_assoc()) {
    $row['row_total'] = (float) $row['price'] * (int) $row['quantity'];
    $subtotal += $row['row_total'];
    $cartItems[] = $row;
}

if (!$cartItems) {
    header("Location: " . qb_url('cart.php'));
    exit();
}

$deliveryFee = 1000;
$readyEstimate = estimate_ready_minutes($conn, $userId);
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $deliveryMethod = trim($_POST['delivery_method'] ?? '');
    $paymentMethod = trim($_POST['payment_method'] ?? '');
    $deliveryAddress = trim($_POST['delivery_address'] ?? '');
    $campusLocation = trim($_POST['campus_location'] ?? '');
    $specialRequest = trim($_POST['special_request'] ?? '');

    // Only add delivery fee for Campus Delivery
    $appliedDeliveryFee = ($deliveryMethod === 'Campus Delivery') ? $deliveryFee : 0;
    $total = $subtotal + $appliedDeliveryFee;

    if ($deliveryMethod === '' || $paymentMethod === '') {
        $message = 'Please complete all required fields.';
        $messageType = 'error';
    } elseif ($deliveryMethod === 'Campus Delivery' && $campusLocation === '') {
        $message = 'Please choose a campus location for delivery.';
        $messageType = 'error';
    } else {
        $addressToStore = $deliveryMethod === 'Pickup'
            ? 'Pickup Counter'
            : trim($campusLocation . ($deliveryAddress !== '' ? ' - ' . $deliveryAddress : ''));

        $token = 'CC' . str_pad((string) random_int(1, 9999), 4, '0', STR_PAD_LEFT);

        $orderSql = "INSERT INTO orders (user_id, total_amount, delivery_method, payment_method, order_status, delivery_address, order_token, special_request)
                     VALUES (?, ?, ?, ?, 'Pending', ?, ?, ?)";
        $orderStmt = $conn->prepare($orderSql);
        $orderStmt->bind_param("idsssss", $userId, $total, $deliveryMethod, $paymentMethod, $addressToStore, $token, $specialRequest);

        if ($orderStmt->execute()) {
            $orderId = $conn->insert_id;

            foreach ($cartItems as $item) {
                $itemSql = "INSERT INTO order_items (order_id, menu_item_id, quantity, price) VALUES (?, ?, ?, ?)";
                $itemStmt = $conn->prepare($itemSql);
                $itemStmt->bind_param("iiid", $orderId, $item['menu_item_id'], $item['quantity'], $item['price']);
                $itemStmt->execute();
            }

            $paymentSql = "INSERT INTO payments (order_id, payment_method, amount, payment_status) VALUES (?, ?, ?, 'Pending')";
            $paymentStmt = $conn->prepare($paymentSql);
            $paymentStmt->bind_param("isd", $orderId, $paymentMethod, $total);
            $paymentStmt->execute();

            $clearSql = "DELETE FROM cart WHERE user_id = ?";
            $clearStmt = $conn->prepare($clearSql);
            $clearStmt->bind_param("i", $userId);
            $clearStmt->execute();

            // Route to the correct payment page based on method
            if ($paymentMethod === 'Demo Card Payment') {
                header("Location: " . qb_url('pay-card.php?order_id=' . $orderId));
            } elseif ($paymentMethod === 'Bank Transfer') {
                header("Location: " . qb_url('pay-transfer.php?order_id=' . $orderId));
            } else {
                header("Location: " . qb_url('order-success.php?order_id=' . $orderId));
            }
            exit();
        } else {
            $message = 'Unable to place order right now.';
            $messageType = 'error';
        }
    }
}

// Default total for display (Pickup selected by default = no delivery fee)
$displayTotal = $subtotal;

include 'includes/header.php';
?>

<section class="checkout-section">
    <div class="container">
        <h1 class="section-title">Checkout</h1>
        <p class="section-subtitle">Complete your order details for pickup or campus delivery.</p>

        <?php if ($message !== ''): ?>
            <div class="alert <?php echo $messageType; ?>"><?php echo h($message); ?></div>
        <?php endif; ?>

        <div class="checkout-wrapper">
            <div class="checkout-form-box">
                <form method="POST" class="checkout-form" id="checkoutForm">
                    <div class="form-group">
                        <label>Delivery Method <span class="required-star">*</span></label>
                        <div class="delivery-slider-wrap">
                            <input type="hidden" name="delivery_method" id="deliveryMethodInput" value="Pickup">
                            <div class="delivery-slider" id="deliverySlider">
                                <div class="delivery-slider-thumb" id="deliverySliderThumb"></div>
                                <button type="button" class="delivery-slider-option active" data-value="Pickup">Pickup</button>
                                <button type="button" class="delivery-slider-option" data-value="Campus Delivery">Campus Delivery</button>
                            </div>
                        </div>
                    </div>

                    <div class="form-group" data-campus-location>
                        <label>Campus Location <span class="required-star">*</span></label>
                        <select name="campus_location">
                            <option value="">Select location</option>
                            <?php while ($loc = $locations->fetch_assoc()): ?>
                                <option value="<?php echo h($loc['location_name']); ?>"><?php echo h($loc['location_name']); ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <div class="form-group" data-address-group>
                        <label>Extra Delivery Direction</label>
                        <textarea name="delivery_address" rows="3" placeholder="Optional extra direction for hostel block or faculty area"></textarea>
                    </div>

                    <div class="form-group">
                        <label>Payment Method <span class="required-star">*</span></label>
                        <select name="payment_method" required>
                            <option value="">Select payment method</option>
                            <option value="Demo Card Payment">Demo Card Payment</option>
                            <option value="Bank Transfer">Bank Transfer</option>
                            <option value="Pay on Pickup">Pay on Pickup</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Special Requests or Allergies</label>
                        <textarea name="special_request" rows="3" placeholder="E.g. no onions, extra spicy, nut allergy, pack separately..."></textarea>
                    </div>

                    <div class="checkout-confirm-box">
                        <label class="checkout-confirm-label">
                            <input type="checkbox" id="confirmOrder" required>
                            <span>I confirm my order details are correct and I agree to the cafeteria's pickup/delivery policy.</span>
                        </label>
                    </div>

                    <button type="submit" class="btn btn-primary" id="placeOrderBtn">Place Order</button>
                </form>
            </div>

            <div class="checkout-summary-box">
                <div class="ai-inline-card checkout-ai-card">
                    <span class="dashboard-kicker">Estimated ready time</span>
                    <h3 class="checkout-ai-time"><?php echo $readyEstimate; ?> minutes</h3>
                    <p class="checkout-ai-note">The estimate reacts to cart size and current prep load.</p>
                </div>

                <h2 class="panel-title checkout-summary-heading">Order Summary</h2>

                <?php foreach ($cartItems as $item): ?>
                    <div class="checkout-item-row">
                        <div>
                            <strong><?php echo h($item['item_name']); ?></strong>
                            <p>Qty: <?php echo (int) $item['quantity']; ?></p>
                        </div>
                        <span>₦<?php echo number_format((float) $item['row_total'], 2); ?></span>
                    </div>
                <?php endforeach; ?>

                <div class="summary-row">
                    <span>Subtotal</span>
                    <span>₦<?php echo number_format((float) $subtotal, 2); ?></span>
                </div>

                <div class="summary-row" id="deliveryFeeRow" style="display:none;">
                    <span>Campus Delivery Fee</span>
                    <span>₦<?php echo number_format((float) $deliveryFee, 2); ?></span>
                </div>

                <div class="summary-row total-row">
                    <span>Total</span>
                    <span id="orderTotal">₦<?php echo number_format((float) $displayTotal, 2); ?></span>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>