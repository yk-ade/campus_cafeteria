<?php
require_once 'includes/db.php';
require_once 'includes/functions.php';

require_login();

$userId = (int) $_SESSION['user_id'];

if (isset($_GET['cart_id'], $_GET['action'])) {
    $cartId = (int) $_GET['cart_id'];
    $action = trim($_GET['action']);

    $checkSql = "SELECT quantity FROM cart WHERE id = ? AND user_id = ?";
    $checkStmt = $conn->prepare($checkSql);
    $checkStmt->bind_param("ii", $cartId, $userId);
    $checkStmt->execute();
    $result = $checkStmt->get_result();

    if ($result->num_rows === 1) {
        $current = $result->fetch_assoc();
        $quantity = (int) $current['quantity'];

        if ($action === 'increase') {
            $quantity++;
        } elseif ($action === 'decrease' && $quantity > 1) {
            $quantity--;
        }

        $updateSql = "UPDATE cart SET quantity = ? WHERE id = ? AND user_id = ?";
        $updateStmt = $conn->prepare($updateSql);
        $updateStmt->bind_param("iii", $quantity, $cartId, $userId);
        $updateStmt->execute();
    }
}

header("Location: " . qb_url('cart.php'));
exit();
