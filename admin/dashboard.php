<?php
require_once 'admin_auth.php';
requireAdmin();
require_once '../includes/db.php';

// Quick stats
$totalUsers = $conn->query("SELECT COUNT(*) c FROM users")->fetch_assoc()['c'];
$totalProperties = $conn->query("SELECT COUNT(*) c FROM properties")->fetch_assoc()['c'];
$pendingCount = $conn->query("SELECT COUNT(*) c FROM bookings WHERE status = 'pending'")->fetch_assoc()['c'];
$confirmedCount = $conn->query("SELECT COUNT(*) c FROM bookings WHERE status = 'confirmed'")->fetch_assoc()['c'];

// All bookings, newest first, joined with user + property info
$bookings = $conn->query("
    SELECT b.id, b.status, b.created_at,
           u.full_name, u.email, u.phone,
           p.name AS property_name, p.city, p.price
    FROM bookings b
    JOIN users u ON u.id = b.user_id
    JOIN properties p ON p.id = b.property_id
    ORDER BY b.status = 'pending' DESC, b.created_at DESC
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin Dashboard - PG Life</title>
    <link href="../css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://use.fontawesome.com/releases/v5.11.2/css/all.css" rel="stylesheet" />
    <style>
        body { background: #f1f5f9; }
        .admin-header { background: #1e293b; color: white; padding: 15px 0; margin-bottom: 25px; }
        .stat-card { background: white; border-radius: 10px; padding: 20px; text-align: center; box-shadow: 0 1px 4px rgba(0,0,0,0.08); }
        .stat-card .num { font-size: 28px; font-weight: 700; }
        .stat-card .label { color: #64748b; font-size: 13px; }
        .badge-pending { background: #f59e0b; }
        .badge-confirmed { background: #22c55e; }
        .badge-cancelled { background: #ef4444; }
        table { background: white; }
    </style>
</head>
<body>

<div class="admin-header">
    <div class="container d-flex justify-content-between align-items-center">
        <h4 class="mb-0">🏠 PG Life — Admin Dashboard</h4>
        <a href="logout.php" class="btn btn-outline-light btn-sm">Logout</a>
    </div>
</div>

<div class="container">

    <div class="row mb-4">
        <div class="col-md-3 mb-3">
            <div class="stat-card">
                <div class="num"><?= $totalUsers ?></div>
                <div class="label">Total Users</div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="stat-card">
                <div class="num"><?= $totalProperties ?></div>
                <div class="label">Properties</div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="stat-card">
                <div class="num text-warning"><?= $pendingCount ?></div>
                <div class="label">Pending Bookings</div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="stat-card">
                <div class="num text-success"><?= $confirmedCount ?></div>
                <div class="label">Confirmed Bookings</div>
            </div>
        </div>
    </div>

    <h5 class="mb-3">All Booking Requests</h5>

    <div class="table-responsive">
        <table class="table table-bordered align-middle" id="bookingsTable">
            <thead class="thead-light">
                <tr>
                    <th>Student</th>
                    <th>Contact</th>
                    <th>Property</th>
                    <th>City</th>
                    <th>Rent</th>
                    <th>Requested</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($bookings->num_rows === 0): ?>
                    <tr><td colspan="8" class="text-center text-muted py-4">No bookings yet.</td></tr>
                <?php endif; ?>
                <?php while ($b = $bookings->fetch_assoc()): ?>
                    <tr id="row-<?= $b['id'] ?>">
                        <td><?= htmlspecialchars($b['full_name']) ?></td>
                        <td>
                            <?= htmlspecialchars($b['email']) ?><br>
                            <small class="text-muted"><?= htmlspecialchars($b['phone']) ?></small>
                        </td>
                        <td><?= htmlspecialchars($b['property_name']) ?></td>
                        <td><?= htmlspecialchars($b['city']) ?></td>
                        <td>₹<?= number_format($b['price']) ?></td>
                        <td><small><?= date('d M Y, h:i A', strtotime($b['created_at'])) ?></small></td>
                        <td>
                            <span class="badge badge-<?= $b['status'] ?> text-white status-badge" id="status-<?= $b['id'] ?>">
                                <?= ucfirst($b['status']) ?>
                            </span>
                        </td>
                        <td>
                            <?php if ($b['status'] === 'pending'): ?>
                                <button class="btn btn-success btn-sm approve-btn" data-id="<?= $b['id'] ?>">
                                    <i class="fas fa-check"></i> Approve
                                </button>
                                <button class="btn btn-danger btn-sm reject-btn" data-id="<?= $b['id'] ?>">
                                    <i class="fas fa-times"></i> Reject
                                </button>
                            <?php else: ?>
                                <span class="text-muted">—</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<script src="../js/jquery.js"></script>
<script>
function updateBooking(id, status, $row) {
    $.ajax({
        url: 'update_booking.php',
        method: 'POST',
        data: { booking_id: id, status: status },
        dataType: 'json',
        success: function (res) {
            if (res.success) {
                $('#status-' + id)
                    .removeClass('badge-pending badge-confirmed badge-cancelled')
                    .addClass('badge-' + status)
                    .text(status.charAt(0).toUpperCase() + status.slice(1));
                $row.find('.approve-btn, .reject-btn').remove();
                $row.find('td:last').html('<span class="text-muted">—</span>');
            } else {
                alert(res.message || 'Something went wrong.');
            }
        },
        error: function (xhr) {
            alert('Error: ' + xhr.responseText);
        }
    });
}

$(document).on('click', '.approve-btn', function () {
    const id = $(this).data('id');
    updateBooking(id, 'confirmed', $(this).closest('tr'));
});

$(document).on('click', '.reject-btn', function () {
    const id = $(this).data('id');
    if (confirm('Reject this booking request?')) {
        updateBooking(id, 'cancelled', $(this).closest('tr'));
    }
});
</script>

</body>
</html>
