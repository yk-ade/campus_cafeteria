<?php
require_once 'includes/db.php';
require_once 'includes/functions.php';

$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userId = is_logged_in() ? (int) $_SESSION['user_id'] : null;
    $fullName = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $reservationDate = trim($_POST['reservation_date'] ?? '');
    $reservationTime = trim($_POST['reservation_time'] ?? '');
    $guests = (int) ($_POST['guests'] ?? 0);
    $occasion = trim($_POST['occasion'] ?? '');
    $dietaryNotes = trim($_POST['dietary_notes'] ?? '');
    $specialRequest = trim($_POST['special_request'] ?? '');

    // Combine occasion, dietary notes and special request into one field
    $combinedRequest = '';
    if ($occasion !== '') {
        $combinedRequest .= "Occasion: $occasion\n";
    }
    if ($dietaryNotes !== '') {
        $combinedRequest .= "Dietary: $dietaryNotes\n";
    }
    if ($specialRequest !== '') {
        $combinedRequest .= $specialRequest;
    }
    $combinedRequest = trim($combinedRequest);

    if ($fullName === '' || $email === '' || $phone === '' || $reservationDate === '' || $reservationTime === '' || $guests <= 0) {
        $message = 'Please complete all required fields.';
        $messageType = 'error';
    } elseif ($occasion === '') {
        $message = 'Please select the occasion/purpose for your reservation.';
        $messageType = 'error';
    } elseif (strtotime($reservationDate) < strtotime(date('Y-m-d'))) {
        $message = 'Reservation date cannot be in the past.';
        $messageType = 'error';
    } else {
        $sql = "INSERT INTO reservations (user_id, full_name, email, phone, reservation_date, reservation_time, guests, special_request, status)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'Pending')";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("isssssis", $userId, $fullName, $email, $phone, $reservationDate, $reservationTime, $guests, $combinedRequest);

        if ($stmt->execute()) {
            $message = 'Reservation submitted successfully! You will receive confirmation once admin approves it.';
            $messageType = 'success';
        } else {
            $message = 'Unable to submit reservation right now.';
            $messageType = 'error';
        }
    }
}

$defaultName = is_logged_in() ? ($_SESSION['full_name'] ?? '') : '';
$defaultEmail = is_logged_in() ? ($_SESSION['email'] ?? '') : '';

// Fetch reservation history for logged-in users
$reservations = [];
if (is_logged_in()) {
    $userId = (int) $_SESSION['user_id'];
    $histStmt = $conn->prepare("SELECT * FROM reservations WHERE user_id = ? ORDER BY created_at DESC");
    $histStmt->bind_param("i", $userId);
    $histStmt->execute();
    $histResult = $histStmt->get_result();
    while ($row = $histResult->fetch_assoc()) {
        $reservations[] = $row;
    }
}

include 'includes/header.php';
?>

<section class="page-banner">
    <div class="container">
        <div class="page-banner-inner">
            <h1 class="section-title">Reserve a Cafeteria Spot</h1>
            <p class="section-subtitle" style="margin-bottom:0;">Book a seat or service slot in advance and wait for admin confirmation.</p>
        </div>
    </div>
</section>

<?php if (is_logged_in() && !empty($reservations)): ?>
<section style="padding-bottom:0;">
    <div class="container">
        <div class="reservation-tabs">
            <button class="res-tab active" data-tab="new-reservation">New Reservation</button>
            <button class="res-tab" data-tab="reservation-history">My Reservations (<?php echo count($reservations); ?>)</button>
        </div>
    </div>
</section>
<?php endif; ?>

<section id="new-reservation" class="reservation-tab-content active" style="<?php echo (is_logged_in() && !empty($reservations)) ? 'padding-top:0;' : ''; ?>">
    <div class="container">
        <?php if ($message !== ''): ?>
            <div class="alert <?php echo $messageType; ?>"><?php echo h($message); ?></div>
        <?php endif; ?>

        <div class="checkout-form-box" style="max-width: 760px; margin: 0 auto;">
            <div class="reservation-info-banner">
                <strong>Before you reserve</strong>
                <p>Please fill out all required fields below. We need a few details to prepare your spot and ensure everything is ready when you arrive.</p>
            </div>

            <form method="POST" class="checkout-form" id="reservationForm">
                <div class="form-group">
                    <label>Full Name <span class="required-star">*</span></label>
                    <input type="text" name="full_name" value="<?php echo h($defaultName); ?>" required>
                </div>

                <div class="checkout-toggle">
                    <div class="form-group">
                        <label>Email Address <span class="required-star">*</span></label>
                        <input type="email" name="email" value="<?php echo h($defaultEmail); ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Phone Number <span class="required-star">*</span></label>
                        <input type="text" name="phone" required placeholder="+234 ___________">
                    </div>
                </div>

                <div class="checkout-toggle">
                    <div class="form-group">
                        <label>Reservation Date <span class="required-star">*</span></label>
                        <input type="date" name="reservation_date" min="<?php echo date('Y-m-d'); ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Reservation Time <span class="required-star">*</span></label>
                        <input type="time" name="reservation_time" required>
                    </div>
                </div>

                <div class="checkout-toggle">
                    <div class="form-group">
                        <label>Number of Guests <span class="required-star">*</span></label>
                        <input type="number" name="guests" min="1" max="50" required placeholder="How many people?">
                    </div>
                    <div class="form-group">
                        <label>Occasion / Purpose <span class="required-star">*</span></label>
                        <select name="occasion" required>
                            <option value="">Select occasion</option>
                            <option value="Casual Dining">Casual Dining</option>
                            <option value="Study Group">Study Group</option>
                            <option value="Birthday Celebration">Birthday Celebration</option>
                            <option value="Class Meetup">Class Meetup</option>
                            <option value="Club / Society Meeting">Club / Society Meeting</option>
                            <option value="Group Project Session">Group Project Session</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label>Dietary Requirements or Allergies</label>
                    <select name="dietary_notes">
                        <option value="">None</option>
                        <option value="Vegetarian">Vegetarian</option>
                        <option value="Vegan">Vegan</option>
                        <option value="Gluten-free">Gluten-free</option>
                        <option value="Nut allergy">Nut Allergy</option>
                        <option value="Lactose intolerant">Lactose Intolerant</option>
                        <option value="Halal">Halal</option>
                        <option value="Other (see special request)">Other (see special request)</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Special Request or Additional Notes</label>
                    <textarea name="special_request" rows="4" placeholder="E.g. need a power outlet for laptops, wheelchair access, quiet seating area..."></textarea>
                </div>

                <button type="submit" class="btn btn-primary" id="submitReservation">Submit Reservation</button>
            </form>
        </div>
    </div>
</section>

<?php if (is_logged_in() && !empty($reservations)): ?>
<section id="reservation-history" class="reservation-tab-content" style="padding-top:0;">
    <div class="container">
        <div class="reservation-history-wrapper">
            <?php foreach ($reservations as $res): ?>
                <?php $statusClass = strtolower(str_replace(' ', '-', $res['status'])); ?>
                <div class="reservation-card">
                    <div class="reservation-card-header">
                        <div>
                            <span class="reservation-id">#RES-<?php echo $res['id']; ?></span>
                            <span class="status-badge status-<?php echo $statusClass; ?>"><?php echo h($res['status']); ?></span>
                        </div>
                        <span class="reservation-date-badge"><?php echo date("M d, Y", strtotime($res['reservation_date'])); ?></span>
                    </div>
                    <div class="reservation-card-body">
                        <div class="reservation-detail">
                            <span class="detail-label">Time</span>
                            <span class="detail-value"><?php echo date("g:i A", strtotime($res['reservation_time'])); ?></span>
                        </div>
                        <div class="reservation-detail">
                            <span class="detail-label">Guests</span>
                            <span class="detail-value"><?php echo (int) $res['guests']; ?> <?php echo $res['guests'] == 1 ? 'person' : 'people'; ?></span>
                        </div>
                        <div class="reservation-detail">
                            <span class="detail-label">Booked</span>
                            <span class="detail-value"><?php echo date("M d, Y h:i A", strtotime($res['created_at'])); ?></span>
                        </div>
                        <?php if (!empty($res['special_request'])): ?>
                            <div class="reservation-detail full-width">
                                <span class="detail-label">Notes</span>
                                <span class="detail-value"><?php echo nl2br(h($res['special_request'])); ?></span>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>