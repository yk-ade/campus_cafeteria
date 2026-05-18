<?php
require_once 'includes/db.php';
require_once 'includes/functions.php';

require_login();

$userId = (int) $_SESSION['user_id'];

$sql = "DELETE FROM cart WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userId);
$stmt->execute();

header("Location: " . qb_url('cart.php'));
exit();
