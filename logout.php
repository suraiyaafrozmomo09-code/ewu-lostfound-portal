<?php
// Start session
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Destroy all session data
session_unset();
session_destroy();

// Redirect to home with logout message
header("Location: index.php?message=Logged out successfully");
exit;
?>