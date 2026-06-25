<?php
$pageTitle = 'Change Password';
require_once __DIR__ . '/../includes/layout-header.php';
?>
<style>
    .proto-card {
        background: #fff;
        border: 1px solid #dfe3e8;
        border-radius: 12px;
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.18);
        padding: 32px;
    }
    .proto-card h5 {
        color: #071326;
        font-weight: 700;
        margin-bottom: 20px;
        padding-bottom: 10px;
        border-bottom: 2px solid #e5e7eb;
    }
    .password-requirements {
        background: #f8fafc;
        padding: 16px;
        border-radius: 8px;
        margin-top: 12px;
    }
    .requirement {
        margin-bottom: 6px;
        font-size: 13px;
        color: #475569;
    }
    .requirement i {
        margin-right: 8px;
        width: 16px;
        color: #9ca3af;
    }
    .requirement.valid i {
        color: #059669;
    }
</style>

<?php if (!empty($_SESSION['success'])): ?>
    <div class="alert alert-success alert-dismissible fade show">
        <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($_SESSION['success'] ?? ''); unset($_SESSION['success']); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>
<?php if (!empty($_SESSION['error'])): ?>
    <div class="alert alert-danger alert-dismissible fade show">
        <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($_SESSION['error'] ?? ''); unset($_SESSION['error']); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="row justify-content-center">
    <div class="col-lg-6">
        <div class="proto-card">
            <h5><i class="fas fa-shield-alt" style="color:var(--fsms-navy);margin-right:8px;"></i>Change Password</h5>

            <form method="POST" action="ProfileController.php?action=update_password" id="passwordForm">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? ''); ?>">

                <div class="mb-3">
                    <label class="form-label" style="font-weight:600;">Current Password</label>
                    <input type="password" class="form-control" name="current_password" required
                           placeholder="Enter your current password">
                </div>

                <div class="mb-3">
                    <label class="form-label" style="font-weight:600;">New Password</label>
                    <input type="password" class="form-control" name="new_password" id="newPassword" required
                           placeholder="Enter new password" minlength="8">
                    <div class="password-requirements">
                        <div class="requirement" id="req-length">
                            <i class="fas fa-times"></i> At least 8 characters
                        </div>
                        <div class="requirement" id="req-upper">
                            <i class="fas fa-times"></i> At least one uppercase letter
                        </div>
                        <div class="requirement" id="req-lower">
                            <i class="fas fa-times"></i> At least one lowercase letter
                        </div>
                        <div class="requirement" id="req-number">
                            <i class="fas fa-times"></i> At least one number
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label" style="font-weight:600;">Confirm New Password</label>
                    <input type="password" class="form-control" name="confirm_password" required
                           placeholder="Re-enter new password">
                    <div id="matchError" class="text-danger mt-2" style="display:none;">
                        <i class="fas fa-exclamation-circle"></i> Passwords do not match!
                    </div>
                </div>

                <div class="alert alert-info mb-4" style="border-radius:8px;">
                    <h6 style="font-weight:700;"><i class="fas fa-exclamation-circle"></i> Security Notice</h6>
                    <ul class="mb-0" style="font-size:13px;">
                        <li>Choose a strong password you haven't used before</li>
                        <li>Don't share your password with anyone</li>
                        <li>You'll need to log in again after changing your password</li>
                    </ul>
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn flex-grow-1" style="background:#1b3a5c;color:#fff;border-radius:9px;font-weight:700;padding:10px 24px;">
                        <i class="fas fa-save"></i> Change Password
                    </button>
                    <a href="../controllers/ProfileController.php?action=profile" class="btn btn-outline-secondary" style="border-radius:9px;font-weight:700;padding:10px 24px;">
                        <i class="fas fa-times"></i> Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    var newPasswordInput = document.getElementById('newPassword');
    var form = document.getElementById('passwordForm');

    if (newPasswordInput) {
        newPasswordInput.addEventListener('input', function() {
            var pwd = this.value;
            updateReq('req-length', pwd.length >= 8);
            updateReq('req-upper', /[A-Z]/.test(pwd));
            updateReq('req-lower', /[a-z]/.test(pwd));
            updateReq('req-number', /[0-9]/.test(pwd));
        });
    }

    function updateReq(id, valid) {
        var el = document.getElementById(id);
        if (!el) return;
        if (valid) {
            el.classList.add('valid');
            el.querySelector('i').className = 'fas fa-check';
        } else {
            el.classList.remove('valid');
            el.querySelector('i').className = 'fas fa-times';
        }
    }

    if (form) {
        form.addEventListener('submit', function(e) {
            var np = document.querySelector('[name="new_password"]').value;
            var cp = document.querySelector('[name="confirm_password"]').value;
            var err = document.getElementById('matchError');
            if (np !== cp) {
                e.preventDefault();
                if (err) err.style.display = 'block';
            }
        });
    }
</script>

<?php require_once __DIR__ . '/../includes/layout-footer.php'; ?>
