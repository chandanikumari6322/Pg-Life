<?php
require_once 'includes/session_init.php';
require_once 'includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Fetch this user's bookings
$stmt = $conn->prepare("
    SELECT b.id, b.status, b.created_at, b.notified,
           p.id AS property_id, p.name, p.city, p.price, p.image_folder
    FROM bookings b
    JOIN properties p ON p.id = b.property_id
    WHERE b.user_id = ?
    ORDER BY b.created_at DESC
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$bookings = $stmt->get_result();
$bookingsList = [];
while ($row = $bookings->fetch_assoc()) {
    $bookingsList[] = $row;
}
$stmt->close();

// Mark all as notified now that the user is viewing this page
$mark = $conn->prepare("UPDATE bookings SET notified = 1 WHERE user_id = ?");
$mark->bind_param("i", $user_id);
$mark->execute();
$mark->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>My Bookings - PG Life</title>
    <link href="css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://use.fontawesome.com/releases/v5.11.2/css/all.css" rel="stylesheet" />
    <link href="css/common.css" rel="stylesheet" />
    <style>
        .booking-card {
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 1px 6px rgba(0,0,0,0.1);
            padding: 20px;
            margin-bottom: 18px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
        }
        .badge-pending { background: #f59e0b; }
        .badge-confirmed { background: #22c55e; }
        .badge-cancelled { background: #ef4444; }
        .status-msg { font-size: 13px; color: #64748b; margin-top: 4px; }
    </style>
</head>
<body>

<div class="header sticky-top">
    <nav class="navbar navbar-expand-md navbar-light">
        <a class="navbar-brand" href="index.php">
            <img src="img/logo.png" />
        </a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#my-navbar">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse justify-content-end" id="my-navbar">
            <ul class="navbar-nav">
                <?php include 'includes/nav_state.php'; ?>
            </ul>
        </div>
    </nav>
</div>

<div class="container mt-4" style="max-width: 800px;">
    <h3 class="mb-4">My Bookings</h3>

    <?php if (empty($bookingsList)): ?>
        <p class="text-muted">You haven't booked any PG yet. <a href="index.php">Browse properties</a></p>
    <?php else: ?>
        <?php foreach ($bookingsList as $b): ?>
            <div class="booking-card">
                <div>
                    <h5 class="mb-1"><?= htmlspecialchars($b['name']) ?></h5>
                    <div class="text-muted"><?= htmlspecialchars($b['city']) ?> · ₹<?= number_format($b['price']) ?>/month</div>
                    <div class="status-msg">
                        Requested on <?= date('d M Y', strtotime($b['created_at'])) ?>
                        <?php if ($b['status'] === 'confirmed'): ?>
                            — 🎉 Your booking has been <strong>approved</strong> by the owner!
                        <?php elseif ($b['status'] === 'cancelled'): ?>
                            — Unfortunately this request was <strong>declined</strong>.
                        <?php else: ?>
                            — Waiting for the owner to respond.
                        <?php endif; ?>
                    </div>
                </div>
                <div class="text-right">
                    <span class="badge badge-<?= $b['status'] ?> text-white p-2 mb-2 d-inline-block">
                        <?= ucfirst($b['status']) ?>
                    </span><br>
                    <a href="property_detail.php?id=<?= $b['property_id'] ?>" class="btn btn-outline-primary btn-sm">View Property</a>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<script src="js/jquery.js"></script>
<script src="js/bootstrap.min.js"></script>
</body>
</html>
