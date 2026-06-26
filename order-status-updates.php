<?php
require_once 'includes/db.php';
require_once 'includes/functions.php';

require_login();
if (!is_student()) {
    http_response_code(403);
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

header('Content-Type: application/json');

$userId = (int) ($_SESSION['user_id'] ?? 0);
$stmt = $conn->prepare('SELECT id, order_status FROM orders WHERE user_id = ?');
if (!$stmt) {
    http_response_code(500);
    echo json_encode(['error' => 'Unable to query orders']);
    exit();
}

$stmt->bind_param('i', $userId);
$stmt->execute();
$result = $stmt->get_result();
$orders = [];
while ($row = $result->fetch_assoc()) {
    $orders[] = [
        'id' => (int) $row['id'],
        'order_status' => $row['order_status'],
    ];
}

echo json_encode(['orders' => $orders]);
exit();
