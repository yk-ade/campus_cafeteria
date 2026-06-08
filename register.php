<?php
require_once 'includes/db.php';
require_once 'includes/functions.php';

if (is_logged_in()) {
    header("Location: " . qb_url('dashboard.php'));
    exit();
}

$message = "";
$messageType = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $fullName = trim($_POST['full_name'] ?? '');
    $matricNo = trim($_POST['matric_no'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $confirmPassword = trim($_POST['confirm_password'] ?? '');

    if ($fullName === '' || $matricNo === '' || $email === '' || $phone === '' || $password === '' || $confirmPassword === '') {
        $message = "Please fill in all fields.";
        $messageType = "error";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Please enter a valid email address.";
        $messageType = "error";
    } elseif ($password !== $confirmPassword) {
        $message = "Passwords do not match.";
        $messageType = "error";
    } elseif (strlen($password) < 6) {
        $message = "Password must be at least 6 characters long.";
        $messageType = "error";
    } else {
        $checkSql = "SELECT id FROM users WHERE email = ? OR matric_no = ?";
        $checkStmt = $conn->prepare($checkSql);
        $checkStmt->bind_param("ss", $email, $matricNo);
        $checkStmt->execute();
        $checkStmt->store_result();

        if ($checkStmt->num_rows > 0) {
            $message = "An account with this email or matric number already exists.";
            $messageType = "error";
        } else {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            $insertSql = "INSERT INTO users (full_name, matric_no, email, phone, password, role) VALUES (?, ?, ?, ?, ?, 'user')";
            $insertStmt = $conn->prepare($insertSql);
            $insertStmt->bind_param("sssss", $fullName, $matricNo, $email, $phone, $hashedPassword);

            if ($insertStmt->execute()) {
                $message = "Account created successfully. You can now log in.";
                $messageType = "success";
            } else {
                $message = "Something went wrong. Please try again.";
                $messageType = "error";
            }
        }
    }
}

include 'includes/header.php';
?>

<section class="auth-page">
    <div class="auth-container auth-container-wide">
        <div class="auth-card">
            <div class="auth-logo">
                <h2>Rectem Cafeteria</h2>
            </div>
            <h1>Create Student Account</h1>
            <p class="auth-subtext">Register once, then place orders, track progress, and manage your cafeteria activity from one dashboard.</p>

            <?php if ($message !== ''): ?>
                <div class="alert <?php echo $messageType; ?>"><?php echo h($message); ?></div>
            <?php endif; ?>

            <form method="POST" class="auth-form">
                <div class="form-group">
                    <input type="text" name="full_name" placeholder="Full Name" required>
                </div>
                <div class="form-group">
                    <input type="text" name="matric_no" placeholder="Matric Number" required>
                </div>
                <div class="form-group">
                    <input type="email" name="email" placeholder="Email Address" required>
                </div>
                <div class="form-group">
                    <input type="text" name="phone" placeholder="Phone Number" required>
                </div>
                <div class="form-group">
                    <input type="password" name="password" placeholder="Password" required>
                </div>
                <div class="form-group">
                    <input type="password" name="confirm_password" placeholder="Confirm Password" required>
                </div>
                <button type="submit" class="btn btn-primary">Create Account</button>
            </form>

            <p class="auth-switch">Already have an account? <a href="<?php echo qb_url('login.php'); ?>">Login</a></p>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>