<?php
require_once 'includes/db.php';
require_once 'includes/functions.php';

require_login();

$userId = (int) $_SESSION['user_id'];

if (!empty($_POST['selected_items']) && is_array($_POST['selected_items'])) {
    foreach ($_POST['selected_items'] as $cartId) {
        $cartId = (int) $cartId;
        $sql = "DELETE FROM cart WHERE id = ? AND user_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $cartId, $userId);
        $stmt->execute();
    }
}

header("Location: " . qb_url('cart.php'));
exit();
