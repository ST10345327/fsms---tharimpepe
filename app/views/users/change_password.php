<?php
$pageTitle = 'Change Password';
require_once __DIR__ . "/../includes/layout-header.php";
?>

    <div class="fsms-page-header">
        <div class="container-fluid">
            <h1><i class="fas fa-lock"></i> Change Password</h1>
            <p class="mb-0 mt-2">Update your account password</p>
        </div>
    </div>

    <div class="container-fluid pt-4 pb-5">
        <div class="row justify-content-center">
            <div class="col-lg-6">
                <div class="form-card">
                    <div class="info-box" id="successMessage">
                        <i class="fas fa-check-circle"></i> Password changed successfully!
                    </div>

                    <form method="POST" action="UserController.php" id="passwordForm">
                        <input type="hidden" name="action" value="change_password">

                        <!-- Password Section -->
                        <div class="form-section">
                            <h5><i class="fas fa-shield-alt"></i> Change Password</h5>
                            
                            <div class="mb-3">
                                <label class="form-label">Current Password <span class="text-danger">*</span></label>
                                <input type="password" class="form-control" name="current_password" required 
                                       placeholder="Enter your current password">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">New Password <span class="text-danger">*</span></label>
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
                                <label class="form-label">Confirm New Password <span class="text-danger">*</span></label>
                                <input type="password" class="form-control" name="confirm_password" required 
                                       placeholder="Re-enter new password">
                                <div id="matchError" class="text-danger mt-2" style="display: none;">
                                    Passwords do not match!
                                </div>
                            </div>
                        </div>

                        <!-- Security Notice -->
                        <div class="alert alert-info mb-4">
                            <h6><i class="fas fa-exclamation-circle"></i> Security Notice</h6>
                            <ul class="mb-0">
                                <li>Choose a strong password that you haven't used before</li>
                                <li>Don't share your password with anyone</li>
                                <li>You will need to log in again after changing your password</li>
                            </ul>
                        </div>

                        <!-- Action Buttons -->
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-success btn-lg flex-grow-1">
                                <i class="fas fa-save"></i> Change Password
                            </button>
                            <a href="UserController.php?action=profile" class="btn btn-outline-secondary btn-lg">
                                <i class="fas fa-times"></i> Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <?php include __DIR__ . "/../includes/footer.php"; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const newPasswordInput = document.getElementById('newPassword');
        const form = document.getElementById('passwordForm');
        
        // Check password requirements
        newPasswordInput.addEventListener('input', function() {
            const password = this.value;
            
            // Length check
            const lengthCheck = password.length >= 8;
            updateRequirement('req-length', lengthCheck);
            
            // Uppercase check
            const upperCheck = /[A-Z]/.test(password);
            updateRequirement('req-upper', upperCheck);
            
            // Lowercase check
            const lowerCheck = /[a-z]/.test(password);
            updateRequirement('req-lower', lowerCheck);
            
            // Number check
            const numberCheck = /[0-9]/.test(password);
            updateRequirement('req-number', numberCheck);
        });
        
        function updateRequirement(id, valid) {
            const elem = document.getElementById(id);
            if (valid) {
                elem.classList.add('valid');
                elem.querySelector('i').className = 'fas fa-check';
            } else {
                elem.classList.remove('valid');
                elem.querySelector('i').className = 'fas fa-times';
            }
        }
        
        // Form submission validation
        form.addEventListener('submit', function(e) {
            const newPassword = document.querySelector('[name="new_password"]').value;
            const confirmPassword = document.querySelector('[name="confirm_password"]').value;
            const matchError = document.getElementById('matchError');
            
            if (newPassword !== confirmPassword) {
                e.preventDefault();
                matchError.style.display = 'block';
                return false;
            }
            matchError.style.display = 'none';
        });
    </script>
<?php require_once __DIR__ . "/../includes/layout-footer.php"; ?>
