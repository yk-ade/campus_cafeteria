<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function qb_base_url(): string
{
    static $base = null;
    if ($base !== null) {
        return $base;
    }

    $projectPath = realpath(__DIR__ . '/..');
    $documentRoot = realpath($_SERVER['DOCUMENT_ROOT'] ?? '') ?: '';

    if ($documentRoot && $projectPath && str_starts_with($projectPath, $documentRoot)) {
        $base = str_replace('\\', '/', substr($projectPath, strlen($documentRoot)));
        return $base === '' ? '' : $base;
    }

    return '/rectem_cafeteria';
}

function qb_url(string $path = ''): string
{
    $base = rtrim(qb_base_url(), '/');
    $path = ltrim($path, '/');
    return $path === '' ? ($base ?: '/') : $base . '/' . $path;
}

function h(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function is_logged_in(): bool
{
    return isset($_SESSION['user_id']);
}

function current_page_name(): string
{
    return basename($_SERVER['PHP_SELF'] ?? '');
}

function current_role(): string
{
    return $_SESSION['role'] ?? 'guest';
}

function is_admin(): bool
{
    return current_role() === 'admin';
}

function is_staff(): bool
{
    return current_role() === 'staff';
}

function is_student(): bool
{
    return current_role() === 'user';
}

function cart_item_count(mysqli $conn): int
{
    if (!is_logged_in() || !is_student()) {
        return 0;
    }

    $userId = (int) $_SESSION['user_id'];
    $sql = "SELECT COALESCE(SUM(quantity), 0) AS total_items FROM cart WHERE user_id = ?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        return 0;
    }
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    return (int) ($row['total_items'] ?? 0);
}

function require_login(): void
{
    if (!is_logged_in()) {
        header("Location: " . qb_url('login.php'));
        exit();
    }
}

function require_admin(): void
{
    if (!is_logged_in() || !is_admin()) {
        header("Location: " . qb_url('login.php'));
        exit();
    }
}

function require_staff_or_admin(): void
{
    if (!is_logged_in() || (!is_staff() && !is_admin())) {
        header("Location: " . qb_url('login.php'));
        exit();
    }
}

function recommended_meals(mysqli $conn, int $limit = 3): array
{
    $items = [];

    if (is_student()) {
        $userId = (int) $_SESSION['user_id'];

        $sql = "SELECT m.id, m.item_name, m.price, m.image, c.category_name
                FROM order_items oi
                INNER JOIN orders o ON oi.order_id = o.id
                INNER JOIN menu_items m ON oi.menu_item_id = m.id
                INNER JOIN categories c ON m.category_id = c.id
                WHERE o.user_id = ?
                GROUP BY m.id
                ORDER BY SUM(oi.quantity) DESC, MAX(o.created_at) DESC
                LIMIT ?";
        $stmt = $conn->prepare($sql);
        if ($stmt) {
            $stmt->bind_param("ii", $userId, $limit);
            $stmt->execute();
            $result = $stmt->get_result();
            while ($row = $result->fetch_assoc()) {
                $items[] = $row;
            }
        }
    }

    if (!$items) {
        $hour = (int) date('G');
        if ($hour < 11) {
            $preferred = ['Breakfast', 'Drinks', 'Snacks'];
        } elseif ($hour < 16) {
            $preferred = ['Rice Meals', 'Fast Food', 'Drinks'];
        } else {
            $preferred = ['Fast Food', 'Snacks', 'Drinks'];
        }

        $placeholders = implode(',', array_fill(0, count($preferred), '?'));
        $types = str_repeat('s', count($preferred)) . 'i';
        $sql = "SELECT m.id, m.item_name, m.price, m.image, c.category_name
                FROM menu_items m
                INNER JOIN categories c ON m.category_id = c.id
                WHERE m.availability_status = 'Available' AND c.category_name IN ($placeholders)
                ORDER BY m.id DESC
                LIMIT ?";
        $stmt = $conn->prepare($sql);
        if ($stmt) {
            $params = array_merge($preferred, [$limit]);
            $stmt->bind_param($types, ...$params);
            $stmt->execute();
            $result = $stmt->get_result();
            while ($row = $result->fetch_assoc()) {
                $items[] = $row;
            }
        }
    }

    if (!$items) {
        $stmt = $conn->prepare("SELECT m.id, m.item_name, m.price, m.image, c.category_name
                                FROM menu_items m
                                INNER JOIN categories c ON m.category_id = c.id
                                WHERE m.availability_status='Available'
                                ORDER BY m.id DESC LIMIT ?");
        if ($stmt) {
            $stmt->bind_param("i", $limit);
            $stmt->execute();
            $result = $stmt->get_result();
            while ($row = $result->fetch_assoc()) {
                $items[] = $row;
            }
        }
    }

    return $items;
}

function combo_suggestions(mysqli $conn, int $itemId, int $limit = 2): array
{
    $itemSql = "SELECT m.item_name, c.category_name
                FROM menu_items m
                INNER JOIN categories c ON m.category_id = c.id
                WHERE m.id = ?";
    $stmt = $conn->prepare($itemSql);
    $stmt->bind_param("i", $itemId);
    $stmt->execute();
    $item = $stmt->get_result()->fetch_assoc();

    if (!$item) {
        return [];
    }

    $category = $item['category_name'];
    $suggestCategories = ['Drinks'];
    if (in_array($category, ['Rice Meals', 'Breakfast', 'Fast Food'], true)) {
        $suggestCategories = ['Drinks', 'Snacks'];
    } elseif ($category === 'Drinks') {
        $suggestCategories = ['Snacks', 'Fast Food'];
    }

    $placeholders = implode(',', array_fill(0, count($suggestCategories), '?'));
    $types = str_repeat('s', count($suggestCategories)) . 'ii';
    $sql = "SELECT m.id, m.item_name, m.price, m.image, c.category_name
            FROM menu_items m
            INNER JOIN categories c ON m.category_id = c.id
            WHERE m.availability_status='Available' AND c.category_name IN ($placeholders) AND m.id <> ?
            ORDER BY m.id DESC
            LIMIT ?";
    $stmt = $conn->prepare($sql);
    $params = array_merge($suggestCategories, [$itemId, $limit]);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

function estimate_ready_minutes(mysqli $conn, ?int $userId = null): int
{
    $base = 8;
    if ($userId) {
        $stmt = $conn->prepare("SELECT COALESCE(SUM(quantity),0) AS qty FROM cart WHERE user_id = ?");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $qty = (int) ($stmt->get_result()->fetch_assoc()['qty'] ?? 0);
        $base += max(0, $qty - 1) * 3;
    }

    $queueResult = $conn->query("SELECT COUNT(*) AS q FROM orders WHERE order_status IN ('Pending','Confirmed','Preparing')");
    $queue = (int) ($queueResult->fetch_assoc()['q'] ?? 0);
    $base += min(15, $queue * 2);

    return max(10, $base);
}

function demand_insights(mysqli $conn): array
{
    $topMeal = 'Campus Combo';
    $peakWindow = '12:00 PM - 2:00 PM';
    $stockSignal = 'Drinks demand is rising';

    $mealSql = "SELECT m.item_name, SUM(oi.quantity) AS sold
                FROM order_items oi
                INNER JOIN menu_items m ON oi.menu_item_id = m.id
                GROUP BY m.id
                ORDER BY sold DESC
                LIMIT 1";
    $mealResult = $conn->query($mealSql);
    if ($mealResult && $mealResult->num_rows > 0) {
        $topMeal = $mealResult->fetch_assoc()['item_name'];
    }

    $peakSql = "SELECT HOUR(created_at) AS hr, COUNT(*) AS total
                FROM orders
                GROUP BY HOUR(created_at)
                ORDER BY total DESC
                LIMIT 1";
    $peakResult = $conn->query($peakSql);
    if ($peakResult && $peakResult->num_rows > 0) {
        $hr = (int) $peakResult->fetch_assoc()['hr'];
        $peakWindow = date('g:i A', strtotime(sprintf('%02d:00:00', $hr))) . ' - ' .
                      date('g:i A', strtotime(sprintf('%02d:00:00', min(23, $hr + 2))));
    }

    return [
        'top_meal' => $topMeal,
        'peak_window' => $peakWindow,
        'stock_signal' => $stockSignal,
    ];
}

function set_flash(string $key, string $message): void
{
    $_SESSION['flash_' . $key] = $message;
}

function get_flash(string $key): string
{
    $flashKey = 'flash_' . $key;
    if (isset($_SESSION[$flashKey])) {
        $message = $_SESSION[$flashKey];
        unset($_SESSION[$flashKey]);
        return $message;
    }
    return '';
}
?>