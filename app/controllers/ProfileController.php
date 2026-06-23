<?php
/**
 * Profile & password management — available to all authenticated roles.
 */

require_once __DIR__ . '/../helpers/Rbac.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/ActivityLog.php';

requireLogin();

$action = $_POST['action'] ?? $_GET['action'] ?? 'profile';

switch ($action) {
    case 'profile':
        rbacRequirePermission('profile');
        showProfile();
        break;
    case 'change_password':
        rbacRequirePermission('change_password');
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            processChangePassword();
        } else {
            showChangePasswordForm();
        }
        break;
    default:
        rbacRequirePermission('profile');
        showProfile();
}

function showProfile()
{
    $userId = getCurrentUser()['user_id'];
    $conn = getConnection();
    $stmt = $conn->prepare('SELECT * FROM users WHERE UserID = :user_id');
    $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        rbacDenyWeb('User account not found.');
    }

    include __DIR__ . '/../views/users/profile.php';
}

function showChangePasswordForm()
{
    include __DIR__ . '/../views/users/change_password.php';
}

function processChangePassword()
{
    $currentUser = getCurrentUser();
    $currentPassword = $_POST['current_password'] ?? '';
    $newPassword = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    if ($newPassword !== $confirmPassword) {
        $_SESSION['error'] = 'New passwords do not match.';
        header('Location: ProfileController.php?action=change_password');
        exit;
    }

    if (strlen($newPassword) < 6) {
        $_SESSION['error'] = 'Password must be at least 6 characters.';
        header('Location: ProfileController.php?action=change_password');
        exit;
    }

    $conn = getConnection();
    $userModel = new User($conn);

    $row = $userModel->getUserById($currentUser['user_id']);
    if (!$row) {
        $_SESSION['error'] = 'User account not found.';
        header('Location: ProfileController.php?action=change_password');
        exit;
    }

    $verifyStmt = $conn->prepare('SELECT PasswordHash FROM users WHERE UserID = :user_id');
    $verifyStmt->execute([':user_id' => $currentUser['user_id']]);
    $hashRow = $verifyStmt->fetch(PDO::FETCH_ASSOC);

    if (!$hashRow || !password_verify($currentPassword, $hashRow['PasswordHash'])) {
        $_SESSION['error'] = 'Current password is incorrect.';
        header('Location: ProfileController.php?action=change_password');
        exit;
    }

    $userModel->changePassword($currentUser['user_id'], $newPassword);
    ActivityLog::log($currentUser['user_id'], 'change_password', 'User', $currentUser['user_id'], 'Password changed');

    $_SESSION['success'] = 'Password changed successfully. Please log in again.';
    header('Location: /controllers/AuthController.php?action=logout');
    exit;
}
