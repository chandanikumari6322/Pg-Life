<?php
if (session_status() === PHP_SESSION_NONE) session_start();
$isLoggedIn = isset($_SESSION['user_id']);

$unreadCount = 0;
if ($isLoggedIn) {
    // Count bookings whose status changed (confirmed/cancelled) but user hasn't seen yet
    require_once __DIR__ . '/db.php';
    $navUnreadStmt = $conn->prepare("SELECT COUNT(*) c FROM bookings WHERE user_id = ? AND notified = 0 AND status != 'pending'");
    $navUnreadStmt->bind_param("i", $_SESSION['user_id']);
    $navUnreadStmt->execute();
    $unreadCount = $navUnreadStmt->get_result()->fetch_assoc()['c'];
    $navUnreadStmt->close();
}
?>
<?php if ($isLoggedIn): ?>
    <li class="nav-item">
        <a class="nav-link" href="my_bookings.php" style="position:relative;">
            <i class="fas fa-bookmark"></i> My Bookings
            <?php if ($unreadCount > 0): ?>
                <span class="badge badge-danger" style="position:relative; top:-8px;"><?= $unreadCount ?></span>
            <?php endif; ?>
        </a>
    </li>
    <div class="nav-vl"></div>
    <li class="nav-item">
        <a class="nav-link" href="#">
            <i class="fas fa-user-circle"></i> Hi, <?= htmlspecialchars($_SESSION['user_name']) ?>
        </a>
    </li>
    <div class="nav-vl"></div>
    <li class="nav-item">
        <a class="nav-link" href="api/logout.php">
            <i class="fas fa-sign-out-alt"></i> Logout
        </a>
    </li>
<?php else: ?>
    <li class="nav-item">
        <a class="nav-link" href="#" data-toggle="modal" data-target="#signup-modal">
            <i class="fas fa-user"></i> Signup
        </a>
    </li>
    <div class="nav-vl"></div>
    <li class="nav-item">
        <a class="nav-link" href="#" data-toggle="modal" data-target="#login-modal">
            <i class="fas fa-sign-in-alt"></i> Login
        </a>
    </li>
<?php endif; ?>
