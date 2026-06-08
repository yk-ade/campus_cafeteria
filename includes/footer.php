<?php
require_once __DIR__ . '/functions.php';
$_isLoggedIn = is_logged_in();
$_isStudent  = $_isLoggedIn && is_student();
$_isAdmin    = $_isLoggedIn && is_admin();
$_isStaff    = $_isLoggedIn && is_staff();
$_hasSidebar = $_isStudent || $_isAdmin || $_isStaff;
?>

<?php if (!$_hasSidebar): ?>
<footer class="site-footer">
    <div class="container footer-grid">
        <div class="footer-box">
            <h3>Rectem Cafeteria</h3>
            <p>A campus-based ordering and cafeteria operations platform built for students, kitchen staff, and administrators.</p>
        </div>
        <div class="footer-box">
            <h4>Quick Links</h4>
            <ul>
                <li><a href="<?php echo qb_url('index.php'); ?>">Home</a></li>
                <li><a href="<?php echo qb_url('menu.php'); ?>">Menu</a></li>
                <li><a href="<?php echo qb_url('reservation.php'); ?>">Reservation</a></li>
                <li><a href="<?php echo qb_url('contact.php'); ?>">Contact</a></li>
            </ul>
        </div>
        <div class="footer-box">
            <h4>Support</h4>
            <p>Email: support@rectemcafeteria.com</p>
            <p>Phone: +234 9167 754 023</p>
            <p>Location: Main Campus Food Court</p>
        </div>
    </div>
    <div class="footer-bottom">
        <p>&copy; <?php echo date('Y'); ?> Rectem Cafeteria. All rights reserved.</p>
    </div>
</footer>
<?php endif; ?>

<?php if ($_hasSidebar): ?>
    <!-- close .student-content and .student-app -->
        <footer class="sidebar-page-footer">
            <p>&copy; <?php echo date('Y'); ?> Rectem Cafeteria &mdash; <a href="<?php echo qb_url('contact.php'); ?>">Support</a></p>
        </footer>
    </div><!-- /.student-content -->
</div><!-- /.student-app -->
<?php endif; ?>

<script src="<?php echo qb_url('assets/js/main.js'); ?>"></script>
</body>
</html>
