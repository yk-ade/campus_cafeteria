<?php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/functions.php';

$currentPage = current_page_name();
$loggedIn    = is_logged_in();
$adminUser   = is_admin();
$staffUser   = is_staff();
$studentUser = is_student();
$cartCount   = cart_item_count($conn);

// Count uncompleted orders for admin/staff badge
$pendingOrderCount = 0;
if ($loggedIn && ($adminUser || $staffUser)) {
    $poResult = $conn->query("SELECT COUNT(*) AS cnt FROM orders WHERE order_status IN ('Pending','Confirmed','Preparing','Ready for Pickup','Out for Delivery')");
    $pendingOrderCount = (int)($poResult->fetch_assoc()['cnt'] ?? 0);
}

$logoTarget = qb_url('index.php');
if ($loggedIn) {
    if ($adminUser)       $logoTarget = qb_url('admin/dashboard.php');
    elseif ($staffUser)   $logoTarget = qb_url('staff/dashboard.php');
    else                  $logoTarget = qb_url('dashboard.php');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Campus Cafeteria – Smart ordering for campus life</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&family=Roboto:wght@400;500;700&display=swap">
    <link rel="stylesheet" href="<?php echo qb_url('assets/css/style.css'); ?>">
</head>
<body>

<?php if ($loggedIn && $adminUser): ?>
<!-- ================================================================
     ADMIN SIDEBAR
     ================================================================ -->
<div class="sidebar-overlay" id="sidebarOverlay"></div>
<div class="student-app">
    <aside class="student-sidebar admin-sidebar-theme" id="studentSidebar">
        <div class="sidebar-header">
            <a href="<?php echo $logoTarget; ?>" class="sidebar-logo-link">
                <div class="sidebar-logo-icon">C</div>
                <div class="sidebar-logo-text">
                    <strong>Campus Cafeteria</strong>
                    <span>Admin Panel</span>
                </div>
            </a>
        </div>
        <div class="sidebar-user-card">
            <div class="sidebar-avatar admin-avatar"><?php echo strtoupper(mb_substr($_SESSION['full_name'] ?? 'A', 0, 1)); ?></div>
            <div class="sidebar-user-info">
                <strong><?php echo h($_SESSION['full_name'] ?? 'Admin'); ?></strong>
                <span>Administrator</span>
            </div>
        </div>
        <nav class="sidebar-nav">
            <p class="sidebar-section-label">Overview</p>
            <ul class="sidebar-nav-list">
                <li>
                    <a href="<?php echo qb_url('admin/dashboard.php'); ?>" class="sidebar-link <?php echo ($currentPage==='dashboard.php')?'active':''; ?>">
                        <svg class="sidebar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg>
                        Dashboard
                    </a>
                </li>

            </ul>

            <p class="sidebar-section-label">Orders</p>
            <ul class="sidebar-nav-list">
                <li>
                    <a href="<?php echo qb_url('admin/orders.php'); ?>" class="sidebar-link <?php echo ($currentPage==='orders.php')?'active':''; ?>">
                        <svg class="sidebar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
                        Manage Orders
                        <?php if ($pendingOrderCount > 0): ?>
                            <span class="sidebar-badge"><?php echo $pendingOrderCount; ?></span>
                        <?php endif; ?>
                    </a>
                </li>
                <li>
                    <a href="<?php echo qb_url('admin/reservations.php'); ?>" class="sidebar-link <?php echo ($currentPage==='reservations.php')?'active':''; ?>">
                        <svg class="sidebar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                        Reservations
                    </a>
                </li>
            </ul>

            <p class="sidebar-section-label">Menu</p>
            <ul class="sidebar-nav-list">
                <li>
                    <a href="<?php echo qb_url('admin/menu.php'); ?>" class="sidebar-link <?php echo ($currentPage==='menu.php')?'active':''; ?>">
                        <svg class="sidebar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 8h1a4 4 0 0 1 0 8h-1"/><path d="M2 8h16v9a4 4 0 0 1-4 4H6a4 4 0 0 1-4-4V8z"/><line x1="6" y1="1" x2="6" y2="4"/><line x1="10" y1="1" x2="10" y2="4"/><line x1="14" y1="1" x2="14" y2="4"/></svg>
                        Manage Menu
                    </a>
                </li>
                <li>
                    <a href="<?php echo qb_url('admin/add-menu.php'); ?>" class="sidebar-link <?php echo ($currentPage==='add-menu.php')?'active':''; ?>">
                        <svg class="sidebar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="16"/><line x1="8" y1="12" x2="16" y2="12"/></svg>
                        Add Dish
                    </a>
                </li>
            </ul>

            <p class="sidebar-section-label">Users</p>
            <ul class="sidebar-nav-list">
                <li>
                    <a href="<?php echo qb_url('admin/users.php'); ?>" class="sidebar-link <?php echo ($currentPage==='users.php')?'active':''; ?>">
                        <svg class="sidebar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                        Manage Users
                    </a>
                </li>
            </ul>
        </nav>
        <div class="sidebar-bottom">
            <a href="<?php echo qb_url('logout.php'); ?>" class="sidebar-logout-btn">
                <svg class="sidebar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                Logout
            </a>
        </div>
    </aside>
    <div class="student-content" id="studentContent">
        <div class="student-topbar">
            <button class="sidebar-hamburger" id="sidebarToggle" aria-label="Toggle sidebar">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
            </button>
            <a href="<?php echo $logoTarget; ?>" class="topbar-logo-link">
                <div class="logo-icon" style="width:34px;height:34px;font-size:.95rem;">C</div>
                <strong>Admin Panel</strong>
            </a>
            <div class="topbar-right">
                <span class="user-greeting-topbar">Hi, <?php echo h($_SESSION['full_name'] ?? 'Admin'); ?></span>
            </div>
        </div>

<?php elseif ($loggedIn && $staffUser): ?>
<!-- ================================================================
     STAFF SIDEBAR
     ================================================================ -->
<div class="sidebar-overlay" id="sidebarOverlay"></div>
<div class="student-app">
    <aside class="student-sidebar staff-sidebar-theme" id="studentSidebar">
        <div class="sidebar-header">
            <a href="<?php echo $logoTarget; ?>" class="sidebar-logo-link">
                <div class="sidebar-logo-icon">C</div>
                <div class="sidebar-logo-text">
                    <strong>Campus Cafeteria</strong>
                    <span>Kitchen Staff</span>
                </div>
            </a>
        </div>
        <div class="sidebar-user-card">
            <div class="sidebar-avatar staff-avatar"><?php echo strtoupper(mb_substr($_SESSION['full_name'] ?? 'S', 0, 1)); ?></div>
            <div class="sidebar-user-info">
                <strong><?php echo h($_SESSION['full_name'] ?? 'Staff'); ?></strong>
                <span>Kitchen Staff</span>
            </div>
        </div>
        <nav class="sidebar-nav">
            <p class="sidebar-section-label">Overview</p>
            <ul class="sidebar-nav-list">
                <li>
                    <a href="<?php echo qb_url('staff/dashboard.php'); ?>" class="sidebar-link <?php echo ($currentPage==='dashboard.php')?'active':''; ?>">
                        <svg class="sidebar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg>
                        Kitchen Dashboard
                    </a>
                </li>
            </ul>

            <p class="sidebar-section-label">Orders</p>
            <ul class="sidebar-nav-list">
                <li>
                    <a href="<?php echo qb_url('staff/orders.php'); ?>" class="sidebar-link <?php echo ($currentPage==='orders.php')?'active':''; ?>">
                        <svg class="sidebar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
                        Order Queue
                        <?php if ($pendingOrderCount > 0): ?>
                            <span class="sidebar-badge"><?php echo $pendingOrderCount; ?></span>
                        <?php endif; ?>
                    </a>
                </li>
            </ul>

            <?php if (is_admin()): ?>
            <p class="sidebar-section-label">Admin</p>
            <ul class="sidebar-nav-list">
                <li>
                    <a href="<?php echo qb_url('admin/dashboard.php'); ?>" class="sidebar-link">
                        <svg class="sidebar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                        Admin Dashboard
                    </a>
                </li>
            </ul>
            <?php endif; ?>
        </nav>
        <div class="sidebar-bottom">
            <a href="<?php echo qb_url('logout.php'); ?>" class="sidebar-logout-btn">
                <svg class="sidebar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                Logout
            </a>
        </div>
    </aside>
    <div class="student-content" id="studentContent">
        <div class="student-topbar">
            <button class="sidebar-hamburger" id="sidebarToggle" aria-label="Toggle sidebar">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
            </button>
            <a href="<?php echo $logoTarget; ?>" class="topbar-logo-link">
                <div class="logo-icon" style="width:34px;height:34px;font-size:.95rem;">C</div>
                <strong>Kitchen Staff</strong>
            </a>
            <div class="topbar-right">
                <span class="user-greeting-topbar">Hi, <?php echo h($_SESSION['full_name'] ?? 'Staff'); ?></span>
            </div>
        </div>

<?php elseif ($loggedIn && $studentUser): ?>
<!-- ================================================================
     STUDENT SIDEBAR
     ================================================================ -->
<div class="sidebar-overlay" id="sidebarOverlay"></div>
<div class="student-app">
    <aside class="student-sidebar" id="studentSidebar">
        <div class="sidebar-header">
            <a href="<?php echo $logoTarget; ?>" class="sidebar-logo-link">
                <div class="sidebar-logo-icon">C</div>
                <div class="sidebar-logo-text">
                    <strong>Campus Cafeteria</strong>
                    <span>Student Portal</span>
                </div>
            </a>
        </div>
        <div class="sidebar-user-card">
            <div class="sidebar-avatar"><?php echo strtoupper(mb_substr($_SESSION['full_name'] ?? 'S', 0, 1)); ?></div>
            <div class="sidebar-user-info">
                <strong><?php echo h($_SESSION['full_name'] ?? 'Student'); ?></strong>
                <span><?php echo h($_SESSION['matric_no'] ?? 'Student Account'); ?></span>
            </div>
        </div>
        <nav class="sidebar-nav">
            <p class="sidebar-section-label">Overview</p>
            <ul class="sidebar-nav-list">
                <li>
                    <a href="<?php echo qb_url('dashboard.php'); ?>" class="sidebar-link <?php echo ($currentPage==='dashboard.php')?'active':''; ?>">
                        <svg class="sidebar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg>
                        Dashboard
                    </a>
                </li>
                <li>
                    <a href="<?php echo qb_url('profile.php'); ?>" class="sidebar-link <?php echo ($currentPage==='profile.php')?'active':''; ?>">
                        <svg class="sidebar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="8" r="4"/><path d="M4 20c0-4 3.6-7 8-7s8 3 8 7"/></svg>
                        My Profile
                    </a>
                </li>
            </ul>
            <p class="sidebar-section-label">Food &amp; Orders</p>
            <ul class="sidebar-nav-list">
                <li>
                    <a href="<?php echo qb_url('menu.php'); ?>" class="sidebar-link <?php echo ($currentPage==='menu.php')?'active':''; ?>">
                        <svg class="sidebar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 8h1a4 4 0 0 1 0 8h-1"/><path d="M2 8h16v9a4 4 0 0 1-4 4H6a4 4 0 0 1-4-4V8z"/><line x1="6" y1="1" x2="6" y2="4"/><line x1="10" y1="1" x2="10" y2="4"/><line x1="14" y1="1" x2="14" y2="4"/></svg>
                        Browse Menu
                    </a>
                </li>
                <li>
                    <a href="<?php echo qb_url('cart.php'); ?>" class="sidebar-link <?php echo ($currentPage==='cart.php')?'active':''; ?>">
                        <svg class="sidebar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg>
                        My Cart
                        <?php if ($cartCount > 0): ?>
                            <span class="sidebar-badge"><?php echo $cartCount; ?></span>
                        <?php endif; ?>
                    </a>
                </li>
                <li>
                    <a href="<?php echo qb_url('order-history.php'); ?>" class="sidebar-link <?php echo ($currentPage==='order-history.php')?'active':''; ?>">
                        <svg class="sidebar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
                        Order History
                    </a>
                </li>
                <li>
                    <a href="<?php echo qb_url('order-tracking.php'); ?>" class="sidebar-link <?php echo ($currentPage==='order-tracking.php')?'active':''; ?>">
                        <svg class="sidebar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="10" r="3"/><path d="M12 2a8 8 0 0 0-8 8c0 5.4 7 12 8 12s8-6.6 8-12a8 8 0 0 0-8-8z"/></svg>
                        Track Order
                    </a>
                </li>
                <li>
                    <a href="<?php echo qb_url('checkout.php'); ?>" class="sidebar-link <?php echo ($currentPage==='checkout.php')?'active':''; ?>">
                        <svg class="sidebar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 12 20 22 4 22 4 12"/><rect x="2" y="7" width="20" height="5"/><path d="M12 22V7"/><path d="M12 7H7.5a2.5 2.5 0 0 1 0-5C11 2 12 7 12 7z"/><path d="M12 7h4.5a2.5 2.5 0 0 0 0-5C13 2 12 7 12 7z"/></svg>
                        Checkout
                    </a>
                </li>
            </ul>
            <p class="sidebar-section-label">Bookings</p>
            <ul class="sidebar-nav-list">
                <li>
                    <a href="<?php echo qb_url('reservation.php'); ?>" class="sidebar-link <?php echo ($currentPage==='reservation.php')?'active':''; ?>">
                        <svg class="sidebar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                        Reservations
                    </a>
                </li>
            </ul>
            <p class="sidebar-section-label">Help</p>
            <ul class="sidebar-nav-list">
                <li>
                    <a href="<?php echo qb_url('about.php'); ?>" class="sidebar-link <?php echo ($currentPage==='about.php')?'active':''; ?>">
                        <svg class="sidebar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                        About
                    </a>
                </li>
                <li>
                    <a href="<?php echo qb_url('contact.php'); ?>" class="sidebar-link <?php echo ($currentPage==='contact.php')?'active':''; ?>">
                        <svg class="sidebar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 12 19.79 19.79 0 0 1 1.62 3.33 2 2 0 0 1 3.6 1.18h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L7.91 8.77a16 16 0 0 0 6 6l.92-.91a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 21.73 16z"/></svg>
                        Contact
                    </a>
                </li>
            </ul>
        </nav>
        <div class="sidebar-bottom">
            <a href="<?php echo qb_url('logout.php'); ?>" class="sidebar-logout-btn">
                <svg class="sidebar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                Logout
            </a>
        </div>
    </aside>
    <div class="student-content" id="studentContent">
        <div class="student-topbar">
            <button class="sidebar-hamburger" id="sidebarToggle" aria-label="Toggle sidebar">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
            </button>
            <a href="<?php echo $logoTarget; ?>" class="topbar-logo-link">
                <div class="logo-icon" style="width:34px;height:34px;font-size:.95rem;">C</div>
                <strong>Campus Cafeteria</strong>
            </a>
            <div class="topbar-right">
                <a href="<?php echo qb_url('cart.php'); ?>" class="topbar-cart-btn" title="Cart">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg>
                    <?php if ($cartCount > 0): ?><span class="cart-badge"><?php echo $cartCount; ?></span><?php endif; ?>
                </a>
            </div>
        </div>

<?php else: ?>
<!-- ================================================================
     PUBLIC HEADER (not logged in)
     ================================================================ -->
<header class="site-header">
    <div class="container nav-container">
        <a href="<?php echo $logoTarget; ?>" class="logo">
            <div class="logo-icon">C</div>
            <div class="logo-text">
                <h2>Campus Cafeteria</h2>
                <p>Fast meals. Smarter campus service.</p>
            </div>
        </a>
        <nav class="navbar">
            <ul class="nav-links">
                <li><a href="<?php echo qb_url('index.php'); ?>"       class="<?php echo ($currentPage==='index.php')?'active':''; ?>">Home</a></li>
                <li><a href="<?php echo qb_url('menu.php'); ?>"        class="<?php echo ($currentPage==='menu.php')?'active':''; ?>">Menu</a></li>
                <li><a href="<?php echo qb_url('about.php'); ?>"       class="<?php echo ($currentPage==='about.php')?'active':''; ?>">About</a></li>
                <li><a href="<?php echo qb_url('reservation.php'); ?>" class="<?php echo ($currentPage==='reservation.php')?'active':''; ?>">Reservation</a></li>
                <li><a href="<?php echo qb_url('contact.php'); ?>"     class="<?php echo ($currentPage==='contact.php')?'active':''; ?>">Contact</a></li>
            </ul>
        </nav>
        <div class="nav-actions">
            <a href="<?php echo qb_url('login.php'); ?>"    class="btn btn-light">Login</a>
            <a href="<?php echo qb_url('register.php'); ?>" class="btn btn-primary">Create Account</a>
        </div>
    </div>
</header>
<?php endif; ?>
