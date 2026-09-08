<?php
// This file ONLY starts the session - it prints nothing.
// Use this at the top of pages. Use includes/nav_state.php separately
// inside the navbar markup where you actually want the login/logout links shown.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
