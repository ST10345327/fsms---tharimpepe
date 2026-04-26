<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Messages - FSMS</title>
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
            border-left: 5px solid #ffc107;
            transition: all 0.3s ease;
        }
        .message-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
        }
        .message-sender { font-weight: 600; color: #333; margin-bottom: 5px; }
        .message-subject { font-weight: 500; color: #555; margin-bottom: 8px; }
        .message-preview { color: #666; font-size: 14px; margin-bottom: 10px; }
        .message-meta { font-size: 12px; color: #999; }
        .message-meta i { margin-right: 5px; }
        .action-buttons { margin-top: 15px; }
        .search-info {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            margin-bottom: 20px;
            border-left: 5px solid #667eea;
        }
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #666;
        }
        .empty-state i { font-size: 48px; color: #ddd; margin-bottom: 20px; }
        .highlight { background-color: #fff3cd; padding: 2px 4px; border-radius: 3px; }
    </style>
</head>
<body>
    <?php include __DIR__ . '/../includes/navbar.php'; ?>

    <div class="container-fluid">
        <div class="page-header">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1><i class="fas fa-search me-3"></i>Search Results</h1>
                    <p class="mb-0 mt-2">Messages matching your search</p>
                </div>
                <div>
                    <a href="MessageController.php?action=inbox" class="btn btn-light">
                        <i class="fas fa-arrow-left me-2"></i>Back to Inbox
                    </a>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <!-- Search Info -->
                <div class="search-info">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-info-circle text-primary me-3" style="font-size: 24px;"></i>
                        <div>
                            <h5 class="mb-1">Search Results for "<?php echo htmlspecialchars($searchTerm); ?>"</h5>
                            <p class="mb-0 text-muted">
                                Found <?php echo count($messages); ?> message(s) in your inbox and sent messages
                            </p>
                        </div>
                    </div>
                    <div class="mt-3">
                        <a href="MessageController.php?action=inbox" class="btn btn-outline-primary btn-sm me-2">
                            <i class="fas fa-inbox me-1"></i>New Search
                        </a>
                        <a href="MessageController.php?action=compose" class="btn btn-outline-success btn-sm">
                            <i class="fas fa-plus me-1"></i>Compose Message
                        </a>
                    </div>
                </div>

                <!-- Messages List -->
                <?php if (empty($messages)): ?>
                    <div class="empty-state">
                        <i class="fas fa-search"></i>
                        <h4>No messages found</h4>
                        <p>No messages match your search term "<?php echo htmlspecialchars($searchTerm); ?>". Try different keywords.</p>
                        <a href="MessageController.php?action=inbox" class="btn btn-primary">Back to Inbox</a>
                    </div>
                <?php else: ?>
                    <?php foreach ($messages as $msg): ?>
                        <div class="message-card">
                            <div class="d-flex justify-content-between align-items-start">
                                <div class="flex-grow-1">
                                    <div class="message-sender">
                                        <?php if ($msg['SenderID'] == $currentUser['id']): ?>
                                            To: <?php echo htmlspecialchars($msg['RecipientName']); ?>
                                            <span class="badge bg-success ms-2">Sent</span>
                                        <?php else: ?>
                                            From: <?php echo htmlspecialchars($msg['SenderName']); ?>
                                            <?php if (!$msg['IsRead']): ?>
                                                <span class="badge bg-warning text-dark ms-2">Unread</span>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    </div>
                                    <div class="message-subject">
                                        <a href="MessageController.php?action=view&id=<?php echo $msg['MessageID']; ?>"
                                           class="text-decoration-none text-dark">
                                            <?php
                                            $subject = htmlspecialchars($msg['Subject']);
                                            // Highlight search term in subject
                                            $subject = str_ireplace(htmlspecialchars($searchTerm), '<span class="highlight">' . htmlspecialchars($searchTerm) . '</span>', $subject);
                                            echo $subject;
                                            ?>
                                        </a>
                                    </div>
                                    <div class="message-preview">
                                        <?php
                                        $content = htmlspecialchars($msg['Content']);
                                        // Highlight search term in content
                                        $content = str_ireplace(htmlspecialchars($searchTerm), '<span class="highlight">' . htmlspecialchars($searchTerm) . '</span>', $content);
                                        echo substr($content, 0, 200);
                                        if (strlen($content) > 200) echo '...';
                                        ?>
                                    </div>
                                    <div class="message-meta">
                                        <i class="far fa-clock"></i>
                                        <?php echo date('M j, Y g:i A', strtotime($msg['SentAt'])); ?>
                                        <?php if ($msg['SenderID'] == $currentUser['id']): ?>
                                            <span class="ms-3">
                                                <i class="fas fa-eye"></i>
                                                <?php echo $msg['IsRead'] ? 'Read' : 'Unread'; ?>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="action-buttons">
                                    <a href="MessageController.php?action=view&id=<?php echo $msg['MessageID']; ?>"
                                       class="btn btn-sm btn-outline-primary me-2">
                                        <i class="fas fa-eye"></i> View
                                    </a>
                                    <?php
                                    $from = ($msg['SenderID'] == $currentUser['id']) ? 'sent' : 'inbox';
                                    ?>
                                    <a href="MessageController.php?action=delete&id=<?php echo $msg['MessageID']; ?>&from=search"
                                       class="btn btn-sm btn-outline-danger"
                                       onclick="return confirm('Are you sure you want to delete this message?')">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>