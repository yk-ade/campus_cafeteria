<?php
require_once 'includes/db.php';
require_once 'includes/functions.php';

if (is_logged_in()) {
    if (is_admin()) {
        header("Location: " . qb_url('admin/dashboard.php'));
    } elseif (is_staff()) {
        header("Location: " . qb_url('staff/dashboard.php'));
    } else {
        header("Location: " . qb_url('dashboard.php'));
    }
    exit();
}

$message = "";
$messageType = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $identifier = trim($_POST['identifier'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if ($identifier === '' || $password === '') {
        $message = "Please fill in all fields.";
        $messageType = "error";
    } else {
        $sql = "SELECT id, full_name, email, matric_no, password, role FROM users WHERE email = ? OR matric_no = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $identifier, $identifier);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows !== 1) {
            $message = "No account found with that email or matric number.";
            $messageType = "error";
        } else {
            $user = $result->fetch_assoc();

            if (!password_verify($password, $user['password'])) {
                $message = "Incorrect password.";
                $messageType = "error";
            } else {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['full_name'] = $user['full_name'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['matric_no'] = $user['matric_no'];
                $_SESSION['role'] = $user['role'];

                if ($user['role'] === 'admin') {
                    header("Location: " . qb_url('admin/dashboard.php'));
                } elseif ($user['role'] === 'staff') {
                    header("Location: " . qb_url('staff/dashboard.php'));
                } else {
                    header("Location: " . qb_url('dashboard.php'));
                }
                exit();
            }
        }
    }
}

include 'includes/header.php';
?>

<section class="auth-page">
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-logo">
                <h2>Campus Cafeteria</h2>
            </div>
            <h1>Welcome Back</h1>
            <p class="auth-subtext">Login to continue ordering meals, tracking requests, or managing cafeteria operations.</p>

            <?php if ($message !== ''): ?>
                <div class="alert <?php echo $messageType; ?>"><?php echo h($message); ?></div>
            <?php endif; ?>

            <form method="POST" class="auth-form">
                <div class="form-group">
                    <input type="text" name="identifier" placeholder="Email Address or Matric Number" required>
                </div>
                <div class="form-group">
                    <input type="password" name="password" placeholder="Password" required>
                </div>
                <button type="submit" class="btn btn-primary">Login</button>
            </form>

            <p class="auth-switch">Don’t have an account? <a href="<?php echo qb_url('register.php'); ?>">Create Account</a></p>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>