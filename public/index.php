<?php
/**
 * Module: FSMS Entry Point
 * Purpose: Main application gateway that routes to login or dashboard
 * Reference: Task 2b System Design - Application Entry
 * Author: WIL Student
 */

// Initialize application (error handling, session, database, etc.)
require_once __DIR__ . '/../app/helpers/bootstrap.php';

/**
 * HZ-ENTRY-001
 * Purpose: Route users based on authentication status
 * Flow: Check session -> Redirect to appropriate page
 */

try {
    // Check if user is already logged in
    if (isUserLoggedIn()) {
        // Redirect to dashboard
        header("Location: ../app/views/dashboard.php");
        exit();
    } else {
        // Redirect to login page
        header("Location: ../app/views/login.php");
        exit();
    }
} catch (Exception $e) {
    // Log error and show user-friendly message
    logMessage("Entry point error: " . $e->getMessage(), 'ERROR');
    header("Location: ../app/views/login.php?error=system_error");
    exit();
}
?>
