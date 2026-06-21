<?php
$pageTitle = 'Messages - Sent';
require_once __DIR__ . "/../includes/layout-header.php";
?>

    <div class="container-fluid">
        <div class="fsms-page-header">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1><i class="fas fa-paper-plane me-3"></i>Sent Messages</h1>
                    <p class="mb-0 mt-2">Messages you have sent</p>
                </div>
                <div>
                    <a href="MessageController.php?action=compose" class="btn btn-light btn-lg">
                        <i class="fas fa-plus me-2"></i>Compose Message
                    </a>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <!-- Navigation Tabs -->
                <ul class="nav nav-tabs mb-4">
                    <li class="nav-item">
                        <a class="nav-link" href="MessageController.php?action=inbox">
                            <i class="fas fa-inbox me-2"></i>Inbox
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="MessageController.php?action=sent">
                            <i class="fas fa-paper-plane me-2"></i>Sent
                        </a>
                    </li>
                </ul>

                <!-- Search Section -->
                <div class="search-section">
                    <form method="GET" action="MessageController.php" class="d-flex">
                        <input type="hidden" name="action" value="search">
                        <input type="text" name="q" class="form-control me-2"
                               placeholder="Search sent messages by subject or content..."
                               value="<?php echo isset($_GET['q']) ? htmlspecialchars($_GET['q']) : ''; ?>">
                        <button type="submit" class="btn btn-outline-primary">
                            <i class="fas fa-search"></i>
                        </button>
                    </form>
                </div>

                <!-- Messages List -->
                <?php if (empty($messages)): ?>
                    <div class="empty-state">
                        <i class="fas fa-paper-plane"></i>
                        <h4>No sent messages</h4>
                        <p>You haven't sent any messages yet. <a href="MessageController.php?action=compose">Send your first message</a></p>
                    </div>
                <?php else: ?>
                    <?php foreach ($messages as $msg): ?>
                        <div class="message-card">
                            <div class="d-flex justify-content-between align-items-start">
                                <div class="flex-grow-1">
                                    <div class="message-recipient">
                                        To: <?php echo htmlspecialchars($msg['RecipientName']); ?>
                                        <span class="status-indicator <?php echo $msg['IsRead'] ? 'status-read' : 'status-unread'; ?>"
                                              title="<?php echo $msg['IsRead'] ? 'Read' : 'Unread'; ?>"></span>
                                        <small class="text-muted">
                                            (<?php echo $msg['IsRead'] ? 'Read' : 'Unread'; ?>)
                                        </small>
                                    </div>
                                    <div class="message-subject">
                                        <a href="MessageController.php?action=view&id=<?php echo $msg['MessageID']; ?>"
                                           class="text-decoration-none text-dark">
                                            <?php echo htmlspecialchars($msg['Subject']); ?>
                                        </a>
                                    </div>
                                    <div class="message-preview">
                                        <?php
                                        $preview = strip_tags($msg['Content']);
                                        echo htmlspecialchars(substr($preview, 0, 150));
                                        if (strlen($preview) > 150) echo '...';
                                        ?>
                                    </div>
                                    <div class="message-meta">
                                        <i class="far fa-clock"></i>
                                        Sent <?php echo date('M j, Y g:i A', strtotime($msg['SentAt'])); ?>
                                    </div>
                                </div>
                                <div class="action-buttons">
                                    <a href="MessageController.php?action=view&id=<?php echo $msg['MessageID']; ?>"
                                       class="btn btn-sm btn-outline-primary me-2">
                                        <i class="fas fa-eye"></i> View
                                    </a>
                                    <a href="MessageController.php?action=delete&id=<?php echo $msg['MessageID']; ?>&from=sent"
                                       class="btn btn-sm btn-outline-danger"
                                       onclick="return confirm('Are you sure you want to delete this message?')">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>

                    <!-- Pagination would go here if needed -->
                <?php endif; ?>
            </div>
        </div>
    </div>
<?php require_once __DIR__ . "/../includes/layout-footer.php"; ?>
