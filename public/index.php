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
    // Route: Handle login form submission
    if (isset($_REQUEST['action']) && $_REQUEST['action'] === 'login') {
        require_once __DIR__ . '/../app/controllers/AuthController.php';
        exit();
    }

    // Route: Handle logout
    if (isset($_REQUEST['action']) && $_REQUEST['action'] === 'logout') {
        require_once __DIR__ . '/../app/controllers/AuthController.php';
        exit();
    }

    // Route: Handle registration
    if (isset($_REQUEST['action']) && $_REQUEST['action'] === 'register') {
        require_once __DIR__ . '/../app/controllers/AuthController.php';
        exit();
    }

    // Check if user is already logged in
    if (isUserLoggedIn()) {
        // Redirect donors to their own dashboard
        $role = strtolower((string)($_SESSION['role'] ?? ''));
        if ($role === 'donor') {
            header("Location: /controllers/DonorController.php?action=dashboard");
            exit;
        }
        // Serve operational dashboard view directly
        require_once __DIR__ . '/../app/views/dashboard.php';
        exit();
    } else {
        // Serve login view directly
        require_once __DIR__ . '/../app/views/login.php';
        exit();
    }
} catch (Exception $e) {
    // Log error and show user-friendly message
    logMessage("Entry point error: " . $e->getMessage(), 'ERROR');
    $error = "An error occurred. Please try again.";
    require_once __DIR__ . '/../app/views/login.php';
    exit();
}
?>
