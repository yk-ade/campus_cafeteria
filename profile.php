<?php
require_once 'includes/functions.php';
require_login();
include 'includes/header.php';
?>

<section class="order-history-section">
    <div class="container">
        <h1 class="section-title">My Profile</h1>
        <p class="section-subtitle">Your current session details and account role within the cafeteria system.</p>

        <div class="checkout-form-box" style="max-width:760px; margin:0 auto;">
            <div class="profile-grid">
                <div class="why-card">
                    <h3>Full Name</h3>
                    <p><?php echo h($_SESSION['full_name'] ?? ''); ?></p>
                </div>
                <div class="why-card">
                    <h3>Email</h3>
                    <p><?php echo h($_SESSION['email'] ?? ''); ?></p>
                </div>
                <div class="why-card">
                    <h3>Matric Number</h3>
                    <p><?php echo h($_SESSION['matric_no'] ?? 'Not assigned'); ?></p>
                </div>
                <div class="why-card">
                    <h3>Role</h3>
                    <p><?php echo h($_SESSION['role'] ?? 'user'); ?></p>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>