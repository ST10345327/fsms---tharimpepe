<?php
$pageTitle = 'Messages - Inbox';
require_once __DIR__ . "/../includes/layout-header.php";
?>

    <div class="container-fluid">
        <div class="fsms-page-header">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1><i class="fas fa-envelope me-3"></i>Messages</h1>
                    <p class="mb-0 mt-2">Internal communication system</p>
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
                        <a class="nav-link active" href="MessageController.php?action=inbox">
                            <i class="fas fa-inbox me-2"></i>Inbox
                            <?php if ($unreadCount > 0): ?>
                                <span class="badge bg-success ms-2"><?php echo $unreadCount; ?></span>
                            <?php endif; ?>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="MessageController.php?action=sent">
                            <i class="fas fa-paper-plane me-2"></i>Sent
                        </a>
                    </li>
                </ul>

                <!-- Search Section -->
                <div class="search-section">
                    <form method="GET" action="MessageController.php" class="d-flex">
                        <input type="hidden" name="action" value="search">
                        <input type="text" name="q" class="form-control me-2"
                               placeholder="Search messages by subject or content..."
                               value="<?php echo isset($_GET['q']) ? htmlspecialchars($_GET['q']) : ''; ?>">
                        <button type="submit" class="btn btn-outline-primary">
                            <i class="fas fa-search"></i>
                        </button>
                    </form>
                </div>

                <!-- Messages List -->
                <?php if (empty($messages)): ?>
                    <div class="empty-state">
                        <i class="fas fa-envelope-open"></i>
                        <h4>No messages found</h4>
                        <p>Your inbox is empty. <a href="MessageController.php?action=compose">Send your first message</a></p>
                    </div>
                <?php else: ?>
                    <?php foreach ($messages as $msg): ?>
                        <div class="message-card <?php echo !$msg['IsRead'] ? 'unread' : ''; ?>">
                            <div class="d-flex justify-content-between align-items-start">
                                <div class="flex-grow-1">
                                    <div class="message-sender">
                                        From: <?php echo htmlspecialchars($msg['SenderName']); ?>
                                        <?php if (!$msg['IsRead']): ?>
                                            <span class="badge-unread">UNREAD</span>
                                        <?php endif; ?>
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
                                        <?php echo date('M j, Y g:i A', strtotime($msg['SentAt'])); ?>
                                    </div>
                                </div>
                                <div class="action-buttons">
                                    <a href="MessageController.php?action=view&id=<?php echo $msg['MessageID']; ?>"
                                       class="btn btn-sm btn-outline-primary me-2">
                                        <i class="fas fa-eye"></i> View
                                    </a>
                                    <a href="MessageController.php?action=delete&id=<?php echo $msg['MessageID']; ?>&from=inbox"
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Auto-refresh unread count every 30 seconds
        setInterval(function() {
            fetch('MessageController.php?action=unread-count')
                .then(response => response.json())
                .then(data => {
                    const badge = document.querySelector('.badge.bg-success');
                    if (data.unread_count > 0) {
                        if (badge) {
                            badge.textContent = data.unread_count;
                        } else {
                            // Add badge if it doesn't exist
                            const inboxLink = document.querySelector('a[href*="action=inbox"]');
                            if (inboxLink) {
                                const badgeHtml = '<span class="badge bg-success ms-2">' + data.unread_count + '</span>';
                                inboxLink.insertAdjacentHTML('beforeend', badgeHtml);
                            }
                        }
                    } else if (badge) {
                        badge.remove();
                    }
                });
        }, 30000);
    </script>
<?php require_once __DIR__ . "/../includes/layout-footer.php"; ?>
