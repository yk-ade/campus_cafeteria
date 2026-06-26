<?php
require_once 'includes/db.php';
require_once 'includes/functions.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['order_id'])) {
    header('Location: ' . qb_url('order-history.php'));
    exit();
}

$orderId = (int) $_POST['order_id'];
$userId = (int) $_SESSION['user_id'];

$stmt = $conn->prepare('SELECT order_status FROM orders WHERE id = ? AND user_id = ? LIMIT 1');
$stmt->bind_param('ii', $orderId, $userId);
$stmt->execute();
$result = $stmt->get_result();
$order = $result->fetch_assoc();

if (!$order) {
    set_flash('success', 'Order not found or you are not authorized to delete it.');
    header('Location: ' . qb_url('order-history.php'));
    exit();
}

$deletableStatuses = ['Pending', 'Confirmed', 'Preparing'];
if (!in_array($order['order_status'], $deletableStatuses, true)) {
    set_flash('success', 'This order cannot be deleted at this stage.');
    header('Location: ' . qb_url('order-history.php'));
    exit();
}

$updateStmt = $conn->prepare("UPDATE orders SET order_status = 'Cancelled' WHERE id = ? AND user_id = ?");
$updateStmt->bind_param('ii', $orderId, $userId);
$success = $updateStmt->execute();

if ($success) {
    set_flash('success', 'Your order has been deleted and admin will see it as deleted.');
} else {
    set_flash('success', 'Unable to delete the order. Please try again.');
}

header('Location: ' . qb_url('order-history.php'));
exit();
