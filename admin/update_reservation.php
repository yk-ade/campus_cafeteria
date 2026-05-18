<?php
require_once '../includes/db.php';
require_once '../includes/functions.php';

require_admin();

if (isset($_POST['reservation_id'], $_POST['status'])) {
    $reservationId = (int) $_POST['reservation_id'];
    $status = trim($_POST['status']);

    $allowed = ['Pending', 'Approved', 'Rejected'];

    if (in_array($status, $allowed, true)) {
        $sql = "UPDATE reservations SET status = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $status, $reservationId);
        $stmt->execute();
    }
}

header("Location: " . qb_url('admin/reservations.php'));
exit();
