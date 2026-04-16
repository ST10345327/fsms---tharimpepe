<?php
/**
 * Module: User Management Controller
 * Purpose: Handle user CRUD operations and admin functions
 * Reference: HZ-USER-CTRL-001 to HZ-USER-CTRL-012
 * Author: WIL Student
 */

require_once __DIR__ . "/../../config/database.php";
require_once __DIR__ . "/../helpers/SessionHandler.php";
require_once __DIR__ . "/../models/ActivityLog.php";

// Require admin access
requireLogin();
if (getCurrentUser()['role'] !== 'admin') {
    header("Location: ../../views/dashboard.php");
    exit;
}

$action = isset($_GET['action']) ? $_GET['action'] : '';

switch ($action) {
    // HZ-USER-CTRL-001: List all users
    case 'list':
        listUsers();
        break;
    
    // HZ-USER-CTRL-002: Show create user form
    case 'create':
        showCreateForm();
        break;
    
    // HZ-USER-CTRL-003: Process user creation
    case 'store':
        storeUser();
        break;
    
    // HZ-USER-CTRL-004: Show user details
    case 'view':
        viewUser();
        break;
    
    // HZ-USER-CTRL-005: Show edit form
    case 'edit':
        showEditForm();
        break;
    
    // HZ-USER-CTRL-006: Process user update
    case 'update':
        updateUser();
        break;
    
    // HZ-USER-CTRL-007: Delete user confirmation
    case 'delete':
        deleteUserConfirm();
        break;
    
    // HZ-USER-CTRL-008: Process deletion
    case 'destroy':
        destroyUser();
        break;
    
    // HZ-USER-CTRL-009: Role management
    case 'role_management':
        roleManagement();
        break;
    
    // HZ-USER-CTRL-010: Update user role
    case 'update_role':
        updateUserRole();
        break;
    
    // HZ-USER-CTRL-011: Activity logs
    case 'activity_log':
        activityLog();
        break;
    
    // HZ-USER-CTRL-012: User profile
    case 'profile':
        userProfile();
        break;
    
    default:
        listUsers();
}

function listUsers() {
    $limit = 20;
    $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
    $offset = ($page - 1) * $limit;
    
    $filters = [];
    if (isset($_GET['role']) && !empty($_GET['role'])) {
        $filters['role'] = $_GET['role'];
    }
    if (isset($_GET['status']) && !empty($_GET['status'])) {
        $filters['status'] = $_GET['status'];
    }
    if (isset($_GET['search']) && !empty($_GET['search'])) {
        $filters['search'] = $_GET['search'];
    }
    
    $conn = getConnection();
    $query = "SELECT UserID, Username, Email, FullName, Phone, Role, Status, CreatedAt 
              FROM Users WHERE 1=1";
    
    if (!empty($filters['role'])) {
        $query .= " AND Role = '" . $conn->quote($filters['role']) . "'";
    }
    if (!empty($filters['status'])) {
        $query .= " AND Status = '" . $conn->quote($filters['status']) . "'";
    }
    if (!empty($filters['search'])) {
        $search = '%' . $filters['search'] . '%';
        $query .= " AND (Username LIKE '" . $conn->quote($search) . "' OR FullName LIKE '" . $conn->quote($search) . "')";
    }
    
    $query .= " ORDER BY CreatedAt DESC LIMIT :limit OFFSET :offset";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get total count
    $countQuery = "SELECT COUNT(*) FROM Users WHERE 1=1";
    if (!empty($filters['role'])) {
        $countQuery .= " AND Role = '" . $conn->quote($filters['role']) . "'";
    }
    if (!empty($filters['status'])) {
        $countQuery .= " AND Status = '" . $conn->quote($filters['status']) . "'";
    }
    $stmt = $conn->prepare($countQuery);
    $stmt->execute();
    $totalCount = $stmt->fetchColumn();
    $totalPages = ceil($totalCount / $limit);
    
    include __DIR__ . "/../views/users/list.php";
}

function showCreateForm() {
    include __DIR__ . "/../views/users/create.php";
}

function storeUser() {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header("Location: UserController.php?action=list");
        exit;
    }
    
    $username = $_POST['username'] ?? '';
    $email = $_POST['email'] ?? '';
    $fullname = $_POST['fullname'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $role = $_POST['role'] ?? 'staff';
    $password = bin2hex(random_bytes(6)); // Generate temporary password
    
    $conn = getConnection();
    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
    
    try {
        $query = "INSERT INTO Users (Username, Email, Password, FullName, Phone, Role, Status, CreatedAt) 
                  VALUES (:username, :email, :password, :fullname, :phone, :role, 'active', NOW())";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':password', $hashedPassword);
        $stmt->bindParam(':fullname', $fullname);
        $stmt->bindParam(':phone', $phone);
        $stmt->bindParam(':role', $role);
        
        if ($stmt->execute()) {
            $userId = $conn->lastInsertId();
            ActivityLog::log(getCurrentUser()['user_id'], 'create_user', 'User', $userId, "Created user: $username (Temp pwd: $password)");
            
            $_SESSION['success'] = "User created successfully. Temporary password: " . htmlspecialchars($password);
            header("Location: UserController.php?action=list");
            exit;
        }
    } catch (PDOException $e) {
        $_SESSION['error'] = "Error creating user: " . htmlspecialchars($e->getMessage());
    }
    
    header("Location: UserController.php?action=create");
    exit;
}

function viewUser() {
    $userId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    
    $conn = getConnection();
    $query = "SELECT * FROM Users WHERE UserID = :user_id";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':user_id', $userId);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user) {
        header("Location: UserController.php?action=list");
        exit;
    }
    
    // Get recent activity
    $activityQuery = "SELECT * FROM ActivityLog WHERE UserID = :user_id OR AffectedEntityName = 'User' AND AffectedEntityID = :user_id ORDER BY Timestamp DESC LIMIT 10";
    $activityStmt = $conn->prepare($activityQuery);
    $activityStmt->bindParam(':user_id', $userId);
    $activityStmt->execute();
    $activities = $activityStmt->fetchAll(PDO::FETCH_ASSOC);
    
    include __DIR__ . "/../views/users/view.php";
}

function showEditForm() {
    $userId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    
    $conn = getConnection();
    $query = "SELECT * FROM Users WHERE UserID = :user_id";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':user_id', $userId);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user) {
        header("Location: UserController.php?action=list");
        exit;
    }
    
    include __DIR__ . "/../views/users/edit.php";
}

function updateUser() {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header("Location: UserController.php?action=list");
        exit;
    }
    
    $userId = (int)$_POST['id'];
    $email = $_POST['email'] ?? '';
    $fullname = $_POST['fullname'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $status = $_POST['status'] ?? 'active';
    
    $conn = getConnection();
    $query = "UPDATE Users SET Email = :email, FullName = :fullname, Phone = :phone, 
              Status = :status, UpdatedAt = NOW() WHERE UserID = :user_id";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':fullname', $fullname);
    $stmt->bindParam(':phone', $phone);
    $stmt->bindParam(':status', $status);
    $stmt->bindParam(':user_id', $userId);
    
    if ($stmt->execute()) {
        ActivityLog::log(getCurrentUser()['user_id'], 'update_user', 'User', $userId, "Updated user information");
        $_SESSION['success'] = "User updated successfully";
        header("Location: UserController.php?action=view&id=$userId");
        exit;
    }
    
    $_SESSION['error'] = "Error updating user";
    header("Location: UserController.php?action=edit&id=$userId");
    exit;
}

function deleteUserConfirm() {
    $userId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    
    $conn = getConnection();
    $query = "SELECT * FROM Users WHERE UserID = :user_id";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':user_id', $userId);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user) {
        header("Location: UserController.php?action=list");
        exit;
    }
    
    include __DIR__ . "/../views/users/delete_confirm.php";
}

function destroyUser() {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header("Location: UserController.php?action=list");
        exit;
    }
    
    $userId = (int)$_POST['id'];
    $conn = getConnection();
    
    // Soft delete - set to inactive
    $query = "UPDATE Users SET Status = 'inactive', UpdatedAt = NOW() WHERE UserID = :user_id";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':user_id', $userId);
    
    if ($stmt->execute()) {
        ActivityLog::log(getCurrentUser()['user_id'], 'delete_user', 'User', $userId, "Deleted user (soft delete)");
        $_SESSION['success'] = "User deleted successfully";
    }
    
    header("Location: UserController.php?action=list");
    exit;
}

function roleManagement() {
    $conn = getConnection();
    $query = "SELECT * FROM Users ORDER BY Role, FullName ASC";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    include __DIR__ . "/../views/users/role_management.php";
}

function updateUserRole() {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header("Location: UserController.php?action=role_management");
        exit;
    }
    
    $userId = (int)$_POST['user_id'];
    $newRole = $_POST['role'];
    
    $conn = getConnection();
    $query = "UPDATE Users SET Role = :role, UpdatedAt = NOW() WHERE UserID = :user_id";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':role', $newRole);
    $stmt->bindParam(':user_id', $userId);
    
    if ($stmt->execute()) {
        ActivityLog::log(getCurrentUser()['user_id'], 'update_role', 'User', $userId, "Changed role to: $newRole");
        $_SESSION['success'] = "User role updated";
    }
    
    header("Location: UserController.php?action=role_management");
    exit;
}

function activityLog() {
    $limit = 50;
    $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
    $offset = ($page - 1) * $limit;
    
    $conn = getConnection();
    $query = "SELECT al.*, u.Username FROM ActivityLog al 
              LEFT JOIN Users u ON al.UserID = u.UserID 
              ORDER BY al.Timestamp DESC LIMIT :limit OFFSET :offset";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $logs = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get total count
    $countStmt = $conn->prepare("SELECT COUNT(*) FROM ActivityLog");
    $countStmt->execute();
    $totalCount = $countStmt->fetchColumn();
    $totalPages = ceil($totalCount / $limit);
    
    include __DIR__ . "/../views/users/activity_log.php";
}

function userProfile() {
    $userId = getCurrentUser()['user_id'];
    
    $conn = getConnection();
    $query = "SELECT * FROM Users WHERE UserID = :user_id";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':user_id', $userId);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    include __DIR__ . "/../views/users/profile.php";
}
?>
