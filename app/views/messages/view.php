<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Message - FSMS</title>
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
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        .message-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            border-radius: 10px 10px 0 0;
        }
        .message-body {
            padding: 30px;
        }
        .message-meta {
            background: #f8f9fa;
            padding: 15px 30px;
            border-bottom: 1px solid #e9ecef;
        }
        .meta-item {
            display: flex;
            align-items: center;
            margin-bottom: 8px;
        }
        .meta-item i {
            width: 20px;
            margin-right: 10px;
            color: #667eea;
        }
        .message-content {
            line-height: 1.6;
            color: #333;
            white-space: pre-wrap;
        }
        .action-buttons {
            background: #f8f9fa;
            padding: 20px 30px;
            border-top: 1px solid #e9ecef;
            border-radius: 0 0 10px 10px;
        }
        .btn-reply {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
        }
        .btn-reply:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
        }
    </style>
</head>
<body>
    <?php include __DIR__ . '/../includes/navbar.php'; ?>

    <div class="container-fluid">
        <div class="page-header">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1><i class="fas fa-envelope-open me-3"></i>Message Details</h1>
                    <p class="mb-0 mt-2">View message content and details</p>
                </div>
                <div>
                    <a href="MessageController.php?action=inbox" class="btn btn-light">
                        <i class="fas fa-arrow-left me-2"></i>Back to Inbox
                    </a>
                </div>
            </div>
        </div>

        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="message-card">
                    <!-- Message Header -->
                    <div class="message-header">
                        <h4 class="mb-0"><?php echo htmlspecialchars($msg['Subject']); ?></h4>
                    </div>

                    <!-- Message Meta Information -->
                    <div class="message-meta">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="meta-item">
                                    <i class="fas fa-user"></i>
                                    <strong>From:</strong> <?php echo htmlspecialchars($msg['SenderName']); ?>
                                </div>
                                <div class="meta-item">
                                    <i class="fas fa-user-tie"></i>
                                    <strong>To:</strong> <?php echo htmlspecialchars($msg['RecipientName']); ?>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="meta-item">
                                    <i class="fas fa-calendar"></i>
                                    <strong>Date:</strong> <?php echo date('l, F j, Y', strtotime($msg['SentAt'])); ?>
                                </div>
                                <div class="meta-item">
                                    <i class="fas fa-clock"></i>
                                    <strong>Time:</strong> <?php echo date('g:i A', strtotime($msg['SentAt'])); ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Message Content -->
                    <div class="message-body">
                        <div class="message-content">
                            <?php echo nl2br(htmlspecialchars($msg['Content'])); ?>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="action-buttons">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <a href="MessageController.php?action=compose&reply=<?php echo $msg['MessageID']; ?>"
                                   class="btn btn-reply me-2">
                                    <i class="fas fa-reply me-2"></i>Reply
                                </a>
                                <a href="MessageController.php?action=compose"
                                   class="btn btn-outline-primary">
                                    <i class="fas fa-plus me-2"></i>New Message
                                </a>
                            </div>
                            <div>
                                <?php
                                $from = ($msg['SenderID'] == $currentUser['id']) ? 'sent' : 'inbox';
                                ?>
                                <a href="MessageController.php?action=delete&id=<?php echo $msg['MessageID']; ?>&from=<?php echo $from; ?>"
                                   class="btn btn-outline-danger"
                                   onclick="return confirm('Are you sure you want to delete this message?')">
                                    <i class="fas fa-trash me-2"></i>Delete
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Success/Error Messages -->
                <?php if (isset($_SESSION['success'])): ?>
                    <div class="alert alert-success alert-dismissible fade show mt-3" role="alert">
                        <i class="fas fa-check-circle me-2"></i>
                        <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <?php if (isset($_SESSION['error'])): ?>
                    <div class="alert alert-danger alert-dismissible fade show mt-3" role="alert">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>