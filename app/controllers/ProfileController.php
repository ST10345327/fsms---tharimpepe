<?php
/**
 * Module: User Profile Controller
 * Purpose: Handle user profile viewing and password changes
 * Reference: HZ-PROF-CTRL-001 to HZ-PROF-CTRL-003
 * Author: WIL Student
 */

// Initialize application with error handling and validation
require_once __DIR__ . "/../helpers/bootstrap.php";

require_once __DIR__ . "/../helpers/SessionHandler.php";
require_once __DIR__ . "/../models/User.php";
require_once __DIR__ . "/../models/ActivityLog.php";
require_once __DIR__ . "/../../config/database.php";

// Require login
requireLogin();
rbacRequirePermission('profile');

$action = isset($_GET['action']) ? $_GET['action'] : 'profile';
$currentUser = getCurrentUser();

try {
    $db = getDBConnection();
    $userModel = new User($db);

    switch ($action) {
        // HZ-PROF-CTRL-001: View user profile
        case 'profile':
            $user = $userModel->getUserById($currentUser['user_id']);
            if (!$user) {
                $_SESSION['error'] = "User not found";
                header("Location: ../views/dashboard.php");
                exit();
            }
            include __DIR__ . "/../views/users/profile.php";
            break;

        // HZ-PROF-CTRL-002: Show change password form
        case 'change_password':
            include __DIR__ . "/../views/users/change_password.php";
            break;

        // HZ-PROF-CTRL-003: Process password change
        case 'update_password':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                header("Location: ProfileController.php?action=change_password");
                exit();
            }

            if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
                $_SESSION['error'] = "Security validation failed. Please try again.";
                header("Location: ProfileController.php?action=change_password");
                exit();
            }

            $currentPassword = $_POST['current_password'] ?? '';
            $newPassword = $_POST['new_password'] ?? '';
            $confirmPassword = $_POST['confirm_password'] ?? '';

            // Validation
            if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
                $_SESSION['error'] = "All fields are required";
                header("Location: ProfileController.php?action=change_password");
                exit();
            }

            if ($newPassword !== $confirmPassword) {
                $_SESSION['error'] = "New passwords do not match";
                header("Location: ProfileController.php?action=change_password");
                exit();
            }

            if (strlen($newPassword) < 6) {
                $_SESSION['error'] = "Password must be at least 6 characters";
                header("Location: ProfileController.php?action=change_password");
                exit();
            }

            // Verify current password
            $user = $userModel->getUserById($currentUser['user_id']);
            if (!password_verify($currentPassword, $user['PasswordHash'])) {
                $_SESSION['error'] = "Current password is incorrect";
                header("Location: ProfileController.php?action=change_password");
                exit();
            }

            // Update password
            if ($userModel->changePassword($currentUser['user_id'], $newPassword)) {
                ActivityLog::log($currentUser['user_id'], 'change_password', 'User', $currentUser['user_id'], "Changed own password");
                $_SESSION['success'] = "Password changed successfully";
                header("Location: ProfileController.php?action=profile");
                exit();
            } else {
                $_SESSION['error'] = "Failed to change password";
                header("Location: ProfileController.php?action=change_password");
                exit();
            }
            break;

        default:
            header("Location: ProfileController.php?action=profile");
            exit();
    }
} catch (Exception $e) {
    $_SESSION['error'] = "Error: " . $e->getMessage();
    header("Location: ../views/dashboard.php");
    exit();
}
?>