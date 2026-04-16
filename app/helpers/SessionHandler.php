<?php
/**
 * Module: Session & Authentication Middleware
 * Purpose: Verify user session and enforce authentication on protected pages
 * Reference: Task 2b System Design Section 4.1 - Session Management
 * Author: WIL Student
 * Usage: Include this file at the top of any protected view
 */

session_start();

/**
 * HZ-AUTH-MIDDLEWARE-001
 * Purpose: Check if user is authenticated
 * Returns: true if authenticated, false otherwise
 * Usage: if (!isUserLoggedIn()) { redirect to login; }
 */
function isUserLoggedIn()
{
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * HZ-AUTH-MIDDLEWARE-002
 * Purpose: Verify user session and redirect to login if not authenticated
 * Terminates execution if user is not logged in
 * Usage: Call at the top of protected pages
 */
function requireLogin()
{
    if (!isUserLoggedIn()) {
        header("Location: login.php");
        exit();
    }
}

/**
 * HZ-AUTH-MIDDLEWARE-003
 * Purpose: Get current logged-in user's information
 * Returns: Array with user_id, username, email, role
 * Returns: null if not authenticated
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
 * Returns: true if user has the role, false otherwise
 * Usage: if (hasRole('admin')) { show admin features; }
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
 * Returns: Username string or null if not logged in
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
