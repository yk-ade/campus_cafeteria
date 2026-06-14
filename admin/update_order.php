<?php
require_once '../includes/db.php';
require_once '../includes/functions.php';
require_admin();

if (isset($_POST['order_id']) && isset($_POST['status'])) {
    $orderId = (int) $_POST['order_id'];
    $status = trim($_POST['status']);

    $allowedStatuses = ['Pending','Confirmed','Preparing','Ready for Pickup','Out for Delivery','Delivered','Completed','Cancelled'];

    if (in_array($status, $allowedStatuses, true)) {
        $sql = "UPDATE orders SET order_status = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $status, $orderId);
        $stmt->execute();
    }
}

$redirectUrl = qb_url('admin/orders.php');
if (isset($status)) {
    $redirectUrl .= '?updated=1&order_id=' . $orderId . '&status=' . rawurlencode($status);
}

header("Location: " . $redirectUrl);
exit();