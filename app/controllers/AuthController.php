<?php
/**
 * Module: User Authentication & Session Management
 * Purpose: Handles user login, registration, and logout workflows
 * Reference: Task 2b System Design Section 4.1 - Authentication Flow
 * Author: WIL Student
 */

session_start();

require_once __DIR__ . "/../config/database.php";
require_once __DIR__ . "/../models/User.php";

$action = isset($_GET['action']) ? $_GET['action'] : 'login';
$error = "";
$success = "";

try {
    // Initialize database connection
    $database = new Database();
    $db = $database->connect();

    if (!$db) {
        throw new Exception("Database connection failed");
    }

    // Create user model
    $userModel = new User($db);

    /**
     * HZ-AUTH-001
     * Purpose: Handle user login form submission
     * Flow: Validate credentials -> Authenticate -> Create session -> Redirect
     */
    if ($action === 'login' && $_SERVER["REQUEST_METHOD"] === "POST") {
        $username = trim($_POST["username"] ?? "");
        $password = $_POST["password"] ?? "";

        // Validation: Check if fields are empty
        if (empty($username) || empty($password)) {
            $error = "Username and password are required";
        } else {
            // Attempt authentication
            $user = $userModel->authenticate($username, $password);

            if ($user) {
                // HZ-AUTH-002: Create secure session
                $_SESSION["user_id"] = $user["UserID"];
                $_SESSION["username"] = $user["Username"];
                $_SESSION["email"] = $user["Email"];
                $_SESSION["role"] = $user["Role"];
                $_SESSION["login_time"] = time();

                // Redirect to dashboard
                header("Location: ../views/dashboard.php");
                exit();
            } else {
                $error = "Invalid username or password";
            }
        }

        // Return to login form with error message
        $action = 'login';
    }

    /**
     * HZ-AUTH-003
     * Purpose: Handle user registration form submission
     * Flow: Validate input -> Create user account -> Redirect to login
     */
    if ($action === 'register' && $_SERVER["REQUEST_METHOD"] === "POST") {
        $username = trim($_POST["username"] ?? "");
        $email = trim($_POST["email"] ?? "");
        $password = $_POST["password"] ?? "";
        $password_confirm = $_POST["password_confirm"] ?? "";

        // Validation: Check if fields are empty
        if (empty($username) || empty($email) || empty($password) || empty($password_confirm)) {
            $error = "All fields are required";
        }
        // Validation: Password match
        elseif ($password !== $password_confirm) {
            $error = "Passwords do not match";
        }
        // Validation: Username length
        elseif (strlen($username) < 3 || strlen($username) > 50) {
            $error = "Username must be between 3 and 50 characters";
        } else {
            try {
                // Attempt registration in User model
                $userId = $userModel->register($username, $email, $password, 'volunteer');

                if ($userId) {
                    $success = "Account created successfully! Redirecting to login...";
                    // Redirect after 2 seconds
                    header("Refresh: 2; URL=../views/login.php");
                } else {
                    $error = "Registration failed. Please try again.";
                }
            } catch (Exception $e) {
                $error = $e->getMessage();
            }
        }

        // Return to register form with error/success message
        $action = 'register';
    }

    /**
     * HZ-AUTH-004
     * Purpose: Handle user logout and session termination
     * Flow: Destroy session -> Redirect to login
     */
    if ($action === 'logout') {
        // Destroy all session data
        $_SESSION = array();
        session_destroy();

        // Redirect to login page
        header("Location: ../views/login.php?logout=success");
        exit();
    }

} catch (Exception $e) {
    $error = "Error: " . $e->getMessage();
}

// Render appropriate view based on action
if ($action === 'login') {
    include __DIR__ . "/../views/login.php";
} elseif ($action === 'register') {
    include __DIR__ . "/../views/register.php";
}
