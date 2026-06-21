<?php
$pageTitle = 'Compose Message';
require_once __DIR__ . "/../includes/layout-header.php";
?>

    <div class="container-fluid">
        <div class="fsms-page-header">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1><i class="fas fa-edit me-3"></i>Compose Message</h1>
                    <p class="mb-0 mt-2">Send a message to staff or volunteers</p>
                </div>
                <div>
                    <a href="MessageController.php?action=inbox" class="btn btn-light">
                        <i class="fas fa-arrow-left me-2"></i>Back to Inbox
                    </a>
                </div>
            </div>
        </div>

        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="compose-card">
                    <?php if (isset($_SESSION['error'])): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="MessageController.php?action=send">
                        <div class="mb-4">
                            <label for="recipient_id" class="form-label">
                                <i class="fas fa-user me-2"></i>Recipient
                            </label>
                            <select name="recipient_id" id="recipient_id" class="form-select" required>
                                <option value="">Select recipient...</option>
                                <?php foreach ($recipients as $recipient): ?>
                                    <?php if ($recipient['UserID'] != $currentUser['id']): ?>
                                        <option value="<?php echo $recipient['UserID']; ?>"
                                                <?php echo (isset($replyMessage) && $replyMessage['SenderID'] == $recipient['UserID']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($recipient['Username']); ?>
                                            (<?php echo ucfirst($recipient['Role']); ?>)
                                        </option>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="mb-4">
                            <label for="subject" class="form-label">
                                <i class="fas fa-heading me-2"></i>Subject
                            </label>
                            <input type="text" name="subject" id="subject" class="form-control"
                                   placeholder="Enter message subject..."
                                   value="<?php echo isset($replyMessage) ? 'Re: ' . htmlspecialchars($replyMessage['Subject']) : ''; ?>"
                                   required maxlength="200">
                        </div>

                        <div class="mb-4">
                            <label for="content" class="form-label">
                                <i class="fas fa-comment me-2"></i>Message
                            </label>
                            <textarea name="content" id="content" class="form-control" rows="8"
                                      placeholder="Type your message here..."
                                      required><?php
                                if (isset($replyMessage)) {
                                    echo "\n\n--- Original Message ---\nFrom: " . htmlspecialchars($replyMessage['SenderName']) . "\nDate: " . date('M j, Y g:i A', strtotime($replyMessage['SentAt'])) . "\n\n" . htmlspecialchars($replyMessage['Content']);
                                }
                            ?></textarea>
                        </div>

                        <div class="d-flex justify-content-between align-items-center">
                            <div class="text-muted">
                                <small>
                                    <i class="fas fa-info-circle me-1"></i>
                                    Messages are sent instantly and cannot be recalled.
                                </small>
                            </div>
                            <div>
                                <a href="MessageController.php?action=inbox" class="btn btn-outline-secondary me-2">
                                    <i class="fas fa-times me-1"></i>Cancel
                                </a>
                                <button type="submit" class="btn btn-send">
                                    <i class="fas fa-paper-plane me-2"></i>Send Message
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Form validation
        document.querySelector('form').addEventListener('submit', function(e) {
            const recipient = document.getElementById('recipient_id').value;
            const subject = document.getElementById('subject').value.trim();
            const content = document.getElementById('content').value.trim();

            if (!recipient) {
                e.preventDefault();
                alert('Please select a recipient.');
                return;
            }

            if (!subject) {
                e.preventDefault();
                alert('Please enter a subject.');
                return;
            }

            if (!content) {
                e.preventDefault();
                alert('Please enter a message.');
                return;
            }

            // Show loading state
            const submitBtn = e.target.querySelector('.btn-send');
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Sending...';
        });

        // Auto-resize textarea
        document.getElementById('content').addEventListener('input', function() {
            this.style.height = 'auto';
            this.style.height = this.scrollHeight + 'px';
        });
    </script>
<?php require_once __DIR__ . "/../includes/layout-footer.php"; ?>
