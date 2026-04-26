<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Messages - Sent - FSMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background-color: #f5f7fa; }
        .navbar { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
        .page-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            border-radius: 0;
            margin-bottom: 30px;
        }
        .page-header h1 { margin: 0; font-weight: 700; }
        .message-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            margin-bottom: 15px;
            border-left: 5px solid #28a745;
            transition: all 0.3s ease;
        }
        .message-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
        }
        .message-recipient { font-weight: 600; color: #333; margin-bottom: 5px; }
        .message-subject { font-weight: 500; color: #555; margin-bottom: 8px; }
        .message-preview { color: #666; font-size: 14px; margin-bottom: 10px; }
        .message-meta { font-size: 12px; color: #999; }
        .message-meta i { margin-right: 5px; }
        .action-buttons { margin-top: 15px; }
        .search-section {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            margin-bottom: 20px;
        }
        .nav-tabs .nav-link { color: #666; border-color: #e0e0e0; }
        .nav-tabs .nav-link.active { color: #667eea; border-color: #667eea; }
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #666;
        }
        .empty-state i { font-size: 48px; color: #ddd; margin-bottom: 20px; }
        .status-indicator {
            display: inline-block;
            width: 8px;
            height: 8px;
            border-radius: 50%;
            margin-right: 8px;
        }
        .status-read { background-color: #28a745; }
        .status-unread { background-color: #ffc107; }
    </style>
</head>
<body>
    <?php include __DIR__ . '/../includes/navbar.php'; ?>

    <div class="container-fluid">
        <div class="page-header">
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>