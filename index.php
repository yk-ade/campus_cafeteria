<?php
require_once 'includes/functions.php';

if (is_logged_in()) {
    if (is_admin()) {
        header('Location: ' . qb_url('admin/dashboard.php'));
    } elseif (is_staff()) {
        header('Location: ' . qb_url('staff/dashboard.php'));
    } else {
        header('Location: ' . qb_url('dashboard.php'));
    }
    exit();
}

include 'includes/header.php';
?>

<section class="hero">
    <div class="container hero-content">
        <div class="hero-text">
            <h1>Campus food ordering that feels fast, familiar, and organized.</h1>
            <p>Rectem Cafeteria helps students browse meals, place orders for pickup or campus delivery, track progress in real time, and enjoy a smoother cafeteria experience.</p>
            <div class="hero-actions">
                <a href="<?php echo qb_url('menu.php'); ?>" class="btn btn-primary">Order Food</a>
                <a href="<?php echo qb_url('reservation.php'); ?>" class="btn btn-light">Reserve a Spot</a>
            </div>

            <div class="hero-stats">
                <div class="hero-stat"><strong>Students</strong><span>Order without queue stress</span></div>
                <div class="hero-stat"><strong>Kitchen</strong><span>Tracks live prep queue</span></div>
                <div class="hero-stat"><strong>Admin</strong><span>Manages menu and demand</span></div>
            </div>
        </div>

        <div class="hero-image-card">
            <img src="<?php echo qb_url('assets/images/foods/1779144763_Nigerian_Assorted_Peppered_Meats_-_Party_Style.jfif'); ?>" alt="Rectem Cafeteria hero">
        </div>
    </div>
</section>

<section class="categories">
    <div class="container">
        <h2 class="section-title">Popular Categories</h2>
        <p class="section-subtitle">Built on the same smooth UI logic as Rectem Cafeteria, but adapted for campus food service operations.</p>

        <div class="category-chip-row">
            <a href="<?php echo qb_url('menu.php?category=Breakfast'); ?>" class="category-chip">Breakfast</a>
            <a href="<?php echo qb_url('menu.php?category=Rice%20Meals'); ?>" class="category-chip">Rice Meals</a>
            <a href="<?php echo qb_url('menu.php?category=Fast%20Food'); ?>" class="category-chip">Fast Food</a>
            <a href="<?php echo qb_url('menu.php?category=Drinks'); ?>" class="category-chip">Drinks</a>
            <a href="<?php echo qb_url('menu.php?category=Snacks'); ?>" class="category-chip">Snacks</a>
        </div>
    </div>
</section>

<section>
    <div class="container">
        <h2 class="section-title">Why this system works for a campus</h2>
        <p class="section-subtitle">The platform blends ordering, tracking, kitchen updates, and admin control in one streamlined system.</p>

        <div class="why-grid">
            <div class="why-card">
                <h3>Real Ordering Workflow</h3>
                <p>Students register, order meals, choose pickup or campus delivery, and track progress from their dashboard.</p>
            </div>
            <div class="why-card">
                <h3>Live Status Tracking</h3>
                <p>Kitchen staff update order progress from pending to ready, delivered, or completed.</p>
            </div>
            <div class="why-card">
                <h3>Focused AI Support</h3>
                <p>Meal recommendations, combo suggestions, prep time estimates, and demand insights appear only where they help.</p>
            </div>
        </div>
    </div>
</section>

<section>
    <div class="container">
        <div class="promo-box">
            <h2>Start ordering in a smarter campus flow</h2>
            <p>Students get a dedicated post-login dashboard, staff get a live queue view, and admins manage meals, reservations, and demand from one system.</p>
            <div class="hero-actions" style="justify-content:center;">
                <a href="<?php echo qb_url('register.php'); ?>" class="btn btn-light">Create Account</a>
                <a href="<?php echo qb_url('login.php'); ?>" class="btn btn-secondary">Login</a>
            </div>
        </div>
    </div>
</section>

<section>
    <div class="container">
        <h2 class="section-title">Built for campus convenience</h2>
        <p class="section-subtitle">From lecture breaks to hostel delivery, the experience is designed around how students actually order food.</p>

        <div class="testimonial-grid">
            <div class="testimonial-card">
                <p>“I can place my order before break time and track when it will be ready instead of waiting in line.”</p>
                <h4>— Adebowale YInka </h4>
            </div>
            <div class="testimonial-card">
                <p>“The admin side actually manages meals and order flow, so the project feels like a real campus product.”</p>
                <h4>— Grant kelechi</h4>
            </div>
            <div class="testimonial-card">
                <p>“The kitchen queue and status updates make the real-time tracking believable and practical.”</p>
                <h4>— Staff User</h4>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>