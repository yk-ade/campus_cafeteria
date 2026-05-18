<?php
require_once 'includes/db.php';
require_once 'includes/functions.php';

require_login();

$userId = (int) $_SESSION['user_id'];
$itemId = isset($_POST['item_id']) ? (int) $_POST['item_id'] : (isset($_GET['id']) ? (int) $_GET['id'] : 0);

if ($itemId <= 0) {
    header("Location: " . qb_url('menu.php'));
    exit();
}

// Function to add a single item to cart
function addItemToCart($conn, $userId, $id) {
    $checkSql = "SELECT id, quantity FROM cart WHERE user_id = ? AND menu_item_id = ?";
    $checkStmt = $conn->prepare($checkSql);
    $checkStmt->bind_param("ii", $userId, $id);
    $checkStmt->execute();
    $result = $checkStmt->get_result();

    if ($result->num_rows > 0) {
        $cartItem = $result->fetch_assoc();
        $newQty = (int) $cartItem['quantity'] + 1;

        $updateSql = "UPDATE cart SET quantity = ? WHERE id = ?";
        $updateStmt = $conn->prepare($updateSql);
        $updateStmt->bind_param("ii", $newQty, $cartItem['id']);
        $updateStmt->execute();
    } else {
        $insertSql = "INSERT INTO cart (user_id, menu_item_id, quantity) VALUES (?, ?, 1)";
        $insertStmt = $conn->prepare($insertSql);
        $insertStmt->bind_param("ii", $userId, $id);
        $insertStmt->execute();
    }
}

// Add main item
addItemToCart($conn, $userId, $itemId);

// Add selected toppings if any
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['toppings']) && is_array($_POST['toppings'])) {
    foreach ($_POST['toppings'] as $toppingId) {
        $tId = (int)$toppingId;
        if ($tId > 0) {
            addItemToCart($conn, $userId, $tId);
        }
    }
}

$back = $_SERVER['HTTP_REFERER'] ?? qb_url('menu.php');
header("Location: " . $back);
exit();
