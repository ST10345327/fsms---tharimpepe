<?php
/**
 * Module: User Authentication & Session Management
 * Purpose: Handles user login, registration, and logout workflows
 * Reference: Task 2b System Design Section 4.1 - Authentication Flow
 * Author: WIL Student
 */

// Initialize application with error handling and validation
require_once __DIR__ . "/../../helpers/bootstrap.php";
require_once __DIR__ . "/../models/User.php";

$action = isset($_GET['action']) ? $_GET['action'] : 'login';
$error = "";
$success = "";

try {
    // Get database connection
    $db = getDBConnection();
    
    // Create user model
    $userModel = new User($db);

    /**
     * HZ-AUTH-001
     * Purpose: Handle user login form submission
     * Flow: Validate credentials -> Authenticate -> Create session -> Redirect
     */
    if ($action === 'login' && $_SERVER["REQUEST_METHOD"] === "POST") {
        FormValidator::reset();
        
        // Get and validate input
        $username = FormValidator::getRequired('username', 'Username');
        $password = FormValidator::getOptional('password', '');
        
        // Check required fields
        if (empty($username) || empty($password)) {
            $error = "Username and password are required";
        } else {
            try {
                // Attempt authentication
                $user = $userModel->authenticate($username, $password);

                if ($user) {
                    // HZ-AUTH-002: Create secure session
                    $_SESSION["user_id"] = $user["UserID"];
                    $_SESSION["username"] = $user["Username"];
                    $_SESSION["email"] = $user["Email"];
                    $_SESSION["role"] = $user["Role"];
                    $_SESSION["login_time"] = time();

                    // Log login activity
                    ActivityLog::log($user['UserID'], 'login', 'Users', $user['UserID'], 'User logged in');

                    // Redirect to dashboard
                    header("Location: ../views/dashboard.php");
                    exit();
                } else {
                    $error = "Invalid username or password";
                }
            } catch (AuthenticationException $e) {
                $error = $e->getUserMessage();
            } catch (Exception $e) {
                $error = "An error occurred during login. Please try again.";
                logMessage("Login error: " . $e->getMessage(), 'ERROR');
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
        FormValidator::reset();
        
        try {
            // Get and validate input
            $username = FormValidator::getRequired('username', 'Username');
            $email = FormValidator::getRequired('email', 'Email');
            $password = FormValidator::getRequired('password', 'Password');
            $password_confirm = FormValidator::getOptional('password_confirm', '');

            // Validate formats
            FormValidator::validateUsername($username);
            FormValidator::validateEmail($email);
            FormValidator::validatePassword($password, 'Password');
            
            // Check password match
            if ($password !== $password_confirm) {
                FormValidator::$errors[] = "Passwords do not match";
            }

            if (FormValidator::hasErrors()) {
                throw new ValidationException(FormValidator::getErrors());
            }

            // Attempt registration in User model
            $userId = $userModel->register($username, $email, $password, 'volunteer');

            if ($userId) {
                // Log registration
                ActivityLog::log($userId, 'register', 'Users', $userId, 'New user registered');
                
                $success = "Account created successfully! Redirecting to login...";
                // Redirect after 2 seconds
                header("Refresh: 2; URL=../views/login.php?registered=success");
            } else {
                throw new Exception("Registration failed. Please try again.");
            }

        } catch (ValidationException $e) {
            $error = implode(", ", $e->getErrors());
        } catch (DuplicateException $e) {
            $error = $e->getUserMessage();
        } catch (Exception $e) {
            $error = $e->getMessage();
            logMessage("Registration error: " . $e->getMessage(), 'ERROR');
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
        // Log logout activity before destroying session
        if (isset($_SESSION['user_id'])) {
            ActivityLog::log($_SESSION['user_id'], 'logout', 'Users', $_SESSION['user_id'], 'User logged out');
        }
        
        // Destroy all session data
        $_SESSION = array();
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $params["path"], $params["domain"], $params["secure"], $params["httponly"]);
        }
        session_destroy();

        // Redirect to login page
        header("Location: ../views/login.php?logout=success");
        exit();
    }

} catch (DatabaseException $e) {
    $error = "Database error: " . $e->getUserMessage();
    logMessage($e->getMessage(), 'ERROR');
} catch (Exception $e) {
    $error = "Error: " . $e->getMessage();
    logMessage("Auth controller error: " . $e->getMessage(), 'ERROR');
}

// Render appropriate view based on action
if ($action === 'login') {
    include __DIR__ . "/../views/login.php";
} elseif ($action === 'register') {
    include __DIR__ . "/../views/register.php";
}
