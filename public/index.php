<?php
/**
 * Module: FSMS Entry Point
 * Purpose: Main application gateway that routes to login or dashboard
 * Reference: Task 2b System Design - Application Entry
 * Author: WIL Student
 */

session_start();

/**
 * HZ-ENTRY-001
 * Purpose: Route users based on authentication status
 * Flow: Check session -> Redirect to appropriate page
 */

// Check if user is already logged in
if (isset($_SESSION['user_id']) && !empty($_SESSION['user_id'])) {
    // Redirect to dashboard
    header("Location: ../app/views/dashboard.php");
    exit();
} else {
    // Redirect to login page
    header("Location: ../app/views/login.php");
    exit();
}
?>
