<?php
require_once '../includes/db.php';
require_once '../includes/functions.php';

require_admin();

$itemId = (int) ($_GET['id'] ?? 0);

if ($itemId > 0) {
    $sql = "DELETE FROM menu_items WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $itemId);
    $stmt->execute();
}

header("Location: " . qb_url('admin/menu.php?deleted=1'));
exit();
