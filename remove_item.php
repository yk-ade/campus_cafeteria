<?php
require_once 'includes/db.php';
require_once 'includes/functions.php';

require_login();

$userId = (int) $_SESSION['user_id'];
$cartId = (int) ($_GET['id'] ?? 0);

if ($cartId > 0) {
    $sql = "DELETE FROM cart WHERE id = ? AND user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $cartId, $userId);
    $stmt->execute();
}

header("Location: " . qb_url('cart.php'));
exit();
