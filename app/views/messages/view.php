<?php
$pageTitle = 'View Message';
require_once __DIR__ . "/../includes/layout-header.php";
?>

    <div class="container-fluid">
        <div class="fsms-page-header">
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
<?php require_once __DIR__ . "/../includes/layout-footer.php"; ?>
