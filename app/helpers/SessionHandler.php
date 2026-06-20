<?php
/**
 * Module: Session & Authentication Middleware
 * Purpose: Verify user session and enforce authentication on protected pages
 * Reference: Task 2b System Design Section 4.1 - Session Management
 * Author: WIL Student
 */

// Keep sessions in a writable temporary directory instead of the default XAMPP location.
$sessionRoot = rtrim(sys_get_temp_dir(), '/\\') . '/fsms-sessions';
if (!is_dir($sessionRoot)) {
    @mkdir($sessionRoot, 0777, true);
}
if (is_dir($sessionRoot) && is_writable($sessionRoot)) {
    session_save_path($sessionRoot);
}

// Only start session if not already started (prevents double-start with bootstrap.php)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * HZ-AUTH-MIDDLEWARE-001
 * Purpose: Check if user is authenticated
 */
function isUserLoggedIn()
{
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * HZ-AUTH-MIDDLEWARE-002
 * Purpose: Verify user session and redirect to login if not authenticated
 */
function requireLogin()
{
    if (!isUserLoggedIn()) {
        // Adjust the path to a relative location for proper redirection.
        header('Location: ../views/login.php');
        exit();
    }
}

/**
 * HZ-AUTH-MIDDLEWARE-003
 * Purpose: Get current logged-in user's information
 * Returns: Array with user_id, username, email, role or null if not authenticated
 */
function getCurrentUser()
{
    if (!isUserLoggedIn()) {
        return null;
    }
    return array(
        'user_id' => $_SESSION['user_id'],
        'username' => $_SESSION['username'],
        'email' => $_SESSION['email'],
        'role' => $_SESSION['role']
    );
}

/**
 * HZ-AUTH-MIDDLEWARE-004
 * Purpose: Check if current user has a specific role
 */
function hasRole($role)
{
    if (!isUserLoggedIn()) {
        return false;
    }
    return $_SESSION['role'] === $role;
}

/**
 * HZ-AUTH-MIDDLEWARE-005
 * Purpose: Get user's display name (username)
 */
function getUserDisplayName()
{
    if (!isUserLoggedIn()) {
        return null;
    }
    return $_SESSION['username'];
}

/**
 * HZ-AUTH-LOGOUT-001
 * Purpose: Safely logout user and destroy session
 */
function logoutUser()
{
    $_SESSION = array();
    session_destroy();
}
?>
