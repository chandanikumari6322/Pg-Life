<?php
// ============================================
// ADMIN CREDENTIALS - change these before you deploy live!
// ============================================
define('ADMIN_USERNAME', 'admin');
define('ADMIN_PASSWORD', 'admin123');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Call this at the top of any admin page to block non-admins
function requireAdmin() {
    if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
        header("Location: login.php");
        exit;
    }
}
?>
