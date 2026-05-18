<?php
require_once '../includes/db.php';
require_once '../includes/functions.php';
require_staff_or_admin();

if (isset($_POST['order_id'], $_POST['status'])) {
    $orderId = (int) $_POST['order_id'];
    $status = trim($_POST['status']);
    $allowed = ['Pending','Confirmed','Preparing','Ready for Pickup','Out for Delivery','Delivered','Completed','Cancelled'];
    if (in_array($status, $allowed, true)) {
        $stmt = $conn->prepare("UPDATE orders SET order_status = ? WHERE id = ?");
        $stmt->bind_param("si", $status, $orderId);
        $stmt->execute();
    }
}

header("Location: " . qb_url('staff/orders.php'));
exit();