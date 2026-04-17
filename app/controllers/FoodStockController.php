<?php
/**
 * File: FoodStockController.php
 * Purpose: Workflow controller for food stock inventory management
 * Author: FSMS Development Agent
 * Architecture Layer: Domain (Controller)
 * Reference: MVC Pattern (Satzinger, Jackson & Burd, 2014)
 * 
 * Reference Code Base:
 * [1] MVC Controller Pattern (Satzinger et al., 2014)
 * [2] PHP Session Management (PHP Manual, 2025)
 * [3] Web Security Best Practices (OWASP, 2025)
 */

require_once __DIR__ . "/../helpers/SessionHandler.php";
require_once __DIR__ . "/../models/FoodStock.php";
require_once __DIR__ . "/../models/ActivityLog.php";
require_once __DIR__ . "/../config/database.php";

// HZ-FOOD-CTRL-001: Require user authentication and authorization
requireLogin();
$currentUser = getCurrentUser();

// Initialize database connection and model
$pdo = getDBConnection();
$foodStockModel = new FoodStock($pdo);

// Get action from request
$action = $_GET['action'] ?? 'list';

// Initialize response variables
$pageTitle = 'Food Stock Management';
$error = '';
$success = '';
$data = [];

// Route requests to appropriate actions
switch ($action) {
    case 'list':
        handleListAction();
        break;
    case 'create':
        handleCreateAction();
        break;
    case 'view':
        handleViewAction();
        break;
    case 'edit':
        handleEditAction();
        break;
    case 'delete':
        handleDeleteAction();
        break;
    case 'low-stock':
        handleLowStockAction();
        break;
    case 'expired':
        handleExpiredAction();
        break;
    case 'distribute':
        handleDistributeAction();
        break;
    case 'report':
        handleReportAction();
        break;
    default:
        handleListAction();
}

/**
 * HZ-FOOD-CTRL-002: List all food stock items
 * Retrieves paginated list with filtering options
 * Displays low-stock warnings
 */
function handleListAction() {
    global $foodStockModel, $pageTitle, $error, $success;

    $pageTitle = 'Food Stock List';
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $searchTerm = $_GET['search'] ?? '';

    // Get stock data
    if (!empty($searchTerm)) {
        $stockData = $foodStockModel->searchStock($searchTerm);
        $result = [
            'data' => $stockData,
            'pagination' => ['current_page' => 1, 'total_pages' => 1, 'total_records' => count($stockData)]
        ];
    } else {
        $result = $foodStockModel->getAllStock($page);
    }

    // Get low stock items for alerts
    $lowStockItems = $foodStockModel->getLowStockItems();

    // Load view
    $stockItems = $result['data'] ?? [];
    $pagination = $result['pagination'] ?? [];
    include __DIR__ . "/../views/food_stock/list.php";
}

/**
 * HZ-FOOD-CTRL-003: Create new food stock item
 * Handles GET (form display) and POST (form submission)
 * Validates input and creates record
 */
function handleCreateAction() {
    global $foodStockModel, $pageTitle, $error, $success;

    $pageTitle = 'Add Food Stock Item';

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // HZ-FOOD-CTRL-003a: Validate CSRF token
        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
            $error = 'Security validation failed. Please try again.';
        } else {
            // Validate required fields
            if (empty($_POST['ItemName']) || empty($_POST['Quantity']) || empty($_POST['Unit'])) {
                $error = 'Please fill in all required fields.';
            } else if ((int)$_POST['Quantity'] < 0) {
                $error = 'Quantity cannot be negative.';
            } else {
                // Create stock item
                $result = $foodStockModel->createStock($_POST);
                if ($result['success']) {
                    $stockId = $result['data']['StockID'] ?? null;
                    if ($stockId) {
                        ActivityLog::log($GLOBALS['currentUser']['user_id'], 'create_food_stock', 'FoodStock', $stockId, "Added food stock: {$_POST['ItemName']} - Quantity: {$_POST['Quantity']} {$_POST['Unit']}");
                    }
                    $success = $result['message'];
                    // Redirect to list
                    header('Location: FoodStockController.php?action=list');
                    exit;
                } else {
                    $error = $result['message'];
                }
            }
        }
    }

    // Load create view
    include __DIR__ . "/../views/food_stock/create.php";
}

/**
 * HZ-FOOD-CTRL-004: View specific food stock item
 * Displays detailed item information with expiry status
 */
function handleViewAction() {
    global $foodStockModel, $pageTitle, $error;

    if (empty($_GET['id'])) {
        $error = 'Invalid food stock item ID.';
        handleListAction();
        return;
    }

    $stockItem = $foodStockModel->getStockById((int)$_GET['id']);
    if (!$stockItem) {
        $error = 'Food stock item not found.';
        handleListAction();
        return;
    }

    $pageTitle = 'Food Stock Item: ' . htmlspecialchars($stockItem['ItemName']);
    include __DIR__ . "/../views/food_stock/view.php";
}

/**
 * HZ-FOOD-CTRL-005: Edit food stock item
 * Handles GET (form display) and POST (form submission)
 */
function handleEditAction() {
    global $foodStockModel, $pageTitle, $error, $success;

    if (empty($_GET['id'])) {
        $error = 'Invalid food stock item ID.';
        handleListAction();
        return;
    }

    $stockItem = $foodStockModel->getStockById((int)$_GET['id']);
    if (!$stockItem) {
        $error = 'Food stock item not found.';
        handleListAction();
        return;
    }

    $pageTitle = 'Edit Food Stock Item';

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Validate CSRF token
        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
            $error = 'Security validation failed. Please try again.';
        } else {
            // Validate required fields
            if (empty($_POST['ItemName']) || empty($_POST['Quantity']) || empty($_POST['Unit'])) {
                $error = 'Please fill in all required fields.';
            } else if ((int)$_POST['Quantity'] < 0) {
                $error = 'Quantity cannot be negative.';
            } else {
                // Update stock item
                $result = $foodStockModel->updateStock((int)$_GET['id'], $_POST);
                if ($result['success']) {
                    ActivityLog::log($GLOBALS['currentUser']['user_id'], 'update_food_stock', 'FoodStock', (int)$_GET['id'], "Updated food stock: {$_POST['ItemName']} - Quantity: {$_POST['Quantity']} {$_POST['Unit']}");
                    $success = $result['message'];
                    // Refresh stock item data
                    $stockItem = $foodStockModel->getStockById((int)$_GET['id']);
                } else {
                    $error = $result['message'];
                }
            }
        }
    }

    // Load edit view
    include __DIR__ . "/../views/food_stock/edit.php";
}

/**
 * HZ-FOOD-CTRL-006: Delete food stock item
 * Requires admin role for security
 * Asks for confirmation before deletion
 */
function handleDeleteAction() {
    global $foodStockModel, $error, $success;

    // HZ-FOOD-CTRL-006a: Check admin authorization
    if ($GLOBALS['currentUser']['role'] !== 'admin') {
        $error = 'You do not have permission to delete food stock items.';
        handleListAction();
        return;
    }

    if (empty($_GET['id'])) {
        $error = 'Invalid food stock item ID.';
        handleListAction();
        return;
    }

    $stockItem = $foodStockModel->getStockById((int)$_GET['id']);
    if (!$stockItem) {
        $error = 'Food stock item not found.';
        handleListAction();
        return;
    }

    // Confirm deletion with POST request
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
            $error = 'Security validation failed. Please try again.';
        } else {
            $result = $foodStockModel->deleteStock((int)$_GET['id']);
            if ($result['success']) {
                ActivityLog::log($GLOBALS['currentUser']['user_id'], 'delete_food_stock', 'FoodStock', (int)$_GET['id'], "Deleted food stock item");
                $success = $result['message'];
                header('Location: FoodStockController.php?action=list');
                exit;
            } else {
                $error = $result['message'];
            }
        }
    }

    // Show delete confirmation view
    include __DIR__ . "/../views/food_stock/delete.php";
}

/**
 * HZ-FOOD-CTRL-007: Display low stock items
 * Shows items that need reordering
 * Critical for inventory management
 */
function handleLowStockAction() {
    global $foodStockModel, $pageTitle;

    $pageTitle = 'Low Stock Items';
    $lowStockItems = $foodStockModel->getLowStockItems();

    include __DIR__ . "/../views/food_stock/low_stock.php";
}

/**
 * HZ-FOOD-CTRL-008: Display expired items
 * Critical for food safety and compliance
 */
function handleExpiredAction() {
    global $foodStockModel, $pageTitle;

    $pageTitle = 'Expired Items';
    $expiredItems = $foodStockModel->getExpiredStock();

    include __DIR__ . "/../views/food_stock/expired.php";
}

/**
 * HZ-FOOD-CTRL-009: Distribute food from stock
 * Decreases quantity when food is distributed
 * Updates stock and maintains audit trail
 */
function handleDistributeAction() {
    global $foodStockModel, $pageTitle, $error, $success;

    if (empty($_GET['id'])) {
        $error = 'Invalid food stock item ID.';
        handleListAction();
        return;
    }

    $stockItem = $foodStockModel->getStockById((int)$_GET['id']);
    if (!$stockItem) {
        $error = 'Food stock item not found.';
        handleListAction();
        return;
    }

    $pageTitle = 'Distribute Food Item';

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Validate CSRF token
        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
            $error = 'Security validation failed. Please try again.';
        } else if (empty($_POST['quantity']) || (int)$_POST['quantity'] <= 0) {
            $error = 'Please enter a valid quantity to distribute.';
        } else if ((int)$_POST['quantity'] > $stockItem['Quantity']) {
            $error = 'Cannot distribute more than available quantity (' . $stockItem['Quantity'] . ' ' . htmlspecialchars($stockItem['Unit']) . ').';
        } else {
            // Update quantity
            $result = $foodStockModel->updateQuantity((int)$_GET['id'], (int)$_POST['quantity'], 'distribute');
            if ($result['success']) {
                ActivityLog::log($GLOBALS['currentUser']['user_id'], 'distribute_food_stock', 'FoodStock', (int)$_GET['id'], "Distributed {$_POST['quantity']} {$stockItem['Unit']}. Remaining: {$result['new_quantity']}");
                $success = 'Successfully distributed ' . (int)$_POST['quantity'] . ' ' . htmlspecialchars($stockItem['Unit']) . '. Remaining: ' . $result['new_quantity'];
                // Refresh item
                $stockItem = $foodStockModel->getStockById((int)$_GET['id']);
            } else {
                $error = $result['message'];
            }
        }
    }

    // Load distribute view
    include __DIR__ . "/../views/food_stock/distribute.php";
}

/**
 * HZ-FOOD-CTRL-010: Generate stock reports
 * Shows statistics and analytics
 * Includes charts and export functionality
 */
function handleReportAction() {
    global $foodStockModel, $pageTitle;

    $pageTitle = 'Food Stock Report';

    // Get filter parameters
    $startDate = $_GET['start_date'] ?? date('Y-m-01');
    $endDate = $_GET['end_date'] ?? date('Y-m-d');

    // Get report data
    $summary = $foodStockModel->getStockSummary();
    $stockByRange = $foodStockModel->getStockByDateRange($startDate, $endDate);
    $lowStockItems = $foodStockModel->getLowStockItems();
    $expiredItems = $foodStockModel->getExpiredStock();
    $allStock = $foodStockModel->getAllStock(1, 1000)['data'];

    include __DIR__ . "/../views/food_stock/report.php";
}
