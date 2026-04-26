<?php
/**
 * Module: Messages Controller
 * Purpose: Handle internal messaging system between staff/admin users
 * Reference: HZ-MSG-CTRL-001 to HZ-MSG-CTRL-010
 * Author: WIL Student
 * Security: Requires authentication, admin/staff access only
 */

require_once __DIR__ . "/../../config/database.php";
require_once __DIR__ . "/../helpers/SessionHandler.php";
require_once __DIR__ . "/../models/Message.php";
require_once __DIR__ . "/../models/ActivityLog.php";

// Require login and appropriate role
requireLogin();
$currentUser = getCurrentUser();
if (!in_array($currentUser['role'], ['admin', 'staff', 'volunteer'])) {
    header("Location: ../../views/dashboard.php");
    exit;
}

$action = isset($_GET['action']) ? $_GET['action'] : 'inbox';

// Initialize database connection and models
$database = new Database();
$db = $database->getConnection();
$message = new Message($db);
$activityLog = new ActivityLog($db);

switch ($action) {
    // HZ-MSG-CTRL-001: Display inbox messages
    case 'inbox':
        showInbox();
        break;

    // HZ-MSG-CTRL-002: Display sent messages
    case 'sent':
        showSent();
        break;

    // HZ-MSG-CTRL-003: Show compose message form
    case 'compose':
        showComposeForm();
        break;

    // HZ-MSG-CTRL-004: Process message sending
    case 'send':
        sendMessage();
        break;

    // HZ-MSG-CTRL-005: View specific message
    case 'view':
        viewMessage();
        break;

    // HZ-MSG-CTRL-006: Mark message as read (AJAX)
    case 'mark-read':
        markAsRead();
        break;

    // HZ-MSG-CTRL-007: Delete message
    case 'delete':
        deleteMessage();
        break;

    // HZ-MSG-CTRL-008: Search messages
    case 'search':
        searchMessages();
        break;

    // HZ-MSG-CTRL-009: Get unread count (AJAX)
    case 'unread-count':
        getUnreadCount();
        break;

    // Default to inbox
    default:
        showInbox();
        break;
}

/**
 * HZ-MSG-CTRL-001: Display inbox with pagination
 * Shows messages received by current user
 */
function showInbox()
{
    global $message, $currentUser;

    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $limit = 20;
    $offset = ($page - 1) * $limit;

    $messages = $message->getInboxMessages($currentUser['id'], $limit, $offset);
    $unreadCount = $message->getUnreadCount($currentUser['id']);

    // Log activity
    global $activityLog;
    $activityLog->logActivity($currentUser['id'], 'Viewed inbox', 'Messages');

    require_once __DIR__ . "/../views/messages/inbox.php";
}

/**
 * HZ-MSG-CTRL-002: Display sent messages with pagination
 * Shows messages sent by current user
 */
function showSent()
{
    global $message, $currentUser;

    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $limit = 20;
    $offset = ($page - 1) * $limit;

    $messages = $message->getSentMessages($currentUser['id'], $limit, $offset);

    // Log activity
    global $activityLog;
    $activityLog->logActivity($currentUser['id'], 'Viewed sent messages', 'Messages');

    require_once __DIR__ . "/../views/messages/sent.php";
}

/**
 * HZ-MSG-CTRL-003: Show compose message form
 * Displays form to send new message
 */
function showComposeForm()
{
    global $message, $currentUser;

    $recipients = $message->getAvailableRecipients();
    $replyTo = isset($_GET['reply']) ? (int)$_GET['reply'] : null;
    $replyMessage = null;

    // If replying, get original message
    if ($replyTo) {
        $replyMessage = $message->getMessageById($replyTo, $currentUser['id']);
        if (!$replyMessage) {
            header("Location: MessageController.php?action=inbox");
            exit;
        }
    }

    require_once __DIR__ . "/../views/messages/compose.php";
}

/**
 * HZ-MSG-CTRL-004: Process message sending
 * Handles POST request to send new message
 */
function sendMessage()
{
    global $message, $currentUser;

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header("Location: MessageController.php?action=compose");
        exit;
    }

    $recipientId = isset($_POST['recipient_id']) ? (int)$_POST['recipient_id'] : 0;
    $subject = isset($_POST['subject']) ? trim($_POST['subject']) : '';
    $content = isset($_POST['content']) ? trim($_POST['content']) : '';

    // Validate input
    if (empty($recipientId) || empty($subject) || empty($content)) {
        $_SESSION['error'] = "All fields are required.";
        header("Location: MessageController.php?action=compose");
        exit;
    }

    // Send message
    $messageId = $message->sendMessage($currentUser['id'], $recipientId, $subject, $content);

    if ($messageId) {
        // Log activity
        global $activityLog;
        $activityLog->logActivity($currentUser['id'], 'Sent message', 'Messages', $messageId);

        $_SESSION['success'] = "Message sent successfully.";
        header("Location: MessageController.php?action=sent");
    } else {
        $_SESSION['error'] = "Failed to send message. Please try again.";
        header("Location: MessageController.php?action=compose");
    }
    exit;
}

/**
 * HZ-MSG-CTRL-005: View specific message
 * Displays full message content and marks as read
 */
function viewMessage()
{
    global $message, $currentUser;

    $messageId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

    if (!$messageId) {
        header("Location: MessageController.php?action=inbox");
        exit;
    }

    $msg = $message->getMessageById($messageId, $currentUser['id']);

    if (!$msg) {
        $_SESSION['error'] = "Message not found or access denied.";
        header("Location: MessageController.php?action=inbox");
        exit;
    }

    // Mark as read if recipient is current user
    if ($msg['RecipientID'] == $currentUser['id'] && !$msg['IsRead']) {
        $message->markAsRead($messageId, $currentUser['id']);
    }

    // Log activity
    global $activityLog;
    $activityLog->logActivity($currentUser['id'], 'Viewed message', 'Messages', $messageId);

    require_once __DIR__ . "/../views/messages/view.php";
}

/**
 * HZ-MSG-CTRL-006: Mark message as read (AJAX)
 * AJAX endpoint to mark message as read without page reload
 */
function markAsRead()
{
    global $message, $currentUser;

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        exit;
    }

    $messageId = isset($_POST['message_id']) ? (int)$_POST['message_id'] : 0;

    if (!$messageId) {
        http_response_code(400);
        exit;
    }

    $result = $message->markAsRead($messageId, $currentUser['id']);

    if ($result) {
        echo json_encode(['success' => true]);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false]);
    }
    exit;
}

/**
 * HZ-MSG-CTRL-007: Delete message
 * Removes message from user's view (full delete for simplicity)
 */
function deleteMessage()
{
    global $message, $currentUser;

    $messageId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

    if (!$messageId) {
        header("Location: MessageController.php?action=inbox");
        exit;
    }

    $result = $message->deleteMessage($messageId, $currentUser['id']);

    if ($result) {
        // Log activity
        global $activityLog;
        $activityLog->logActivity($currentUser['id'], 'Deleted message', 'Messages', $messageId);

        $_SESSION['success'] = "Message deleted successfully.";
    } else {
        $_SESSION['error'] = "Failed to delete message.";
    }

    // Redirect back to appropriate view
    $from = isset($_GET['from']) ? $_GET['from'] : 'inbox';
    header("Location: MessageController.php?action=" . $from);
    exit;
}

/**
 * HZ-MSG-CTRL-008: Search messages
 * Search through user's messages by subject/content
 */
function searchMessages()
{
    global $message, $currentUser;

    $searchTerm = isset($_GET['q']) ? trim($_GET['q']) : '';

    if (empty($searchTerm)) {
        header("Location: MessageController.php?action=inbox");
        exit;
    }

    $messages = $message->searchMessages($currentUser['id'], $searchTerm);

    // Log activity
    global $activityLog;
    $activityLog->logActivity($currentUser['id'], 'Searched messages', 'Messages', null, "Search term: $searchTerm");

    require_once __DIR__ . "/../views/messages/search.php";
}

/**
 * HZ-MSG-CTRL-009: Get unread count (AJAX)
 * Returns JSON with unread message count for current user
 */
function getUnreadCount()
{
    global $message, $currentUser;

    $count = $message->getUnreadCount($currentUser['id']);

    header('Content-Type: application/json');
    echo json_encode(['unread_count' => $count]);
    exit;
}