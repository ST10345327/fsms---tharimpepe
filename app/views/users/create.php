<?php
$pageTitle = 'Create User';
require_once __DIR__ . "/../includes/layout-header.php";
?>

    <div class="fsms-page-header">
        <div class="container-fluid">
            <h1><i class="fas fa-user-plus"></i> Create New User</h1>
            <p class="mb-0 mt-2">Add a new system user account</p>
        </div>
    </div>

    <main class="container-fluid pt-4 pb-5">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <?php if (isset($_SESSION['error'])): ?>
                    <div class="alert alert-danger" role="alert">
                        <i class="fas fa-exclamation-circle me-2"></i> <?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?>
                    </div>
                <?php endif; ?>

                <?php if (isset($_SESSION['success'])): ?>
                    <div class="alert alert-success" role="alert">
                        <i class="fas fa-check-circle me-2"></i> <?php echo htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?>
                    </div>
                <?php endif; ?>

                <div class="form-card">
                    <!-- Info Box -->
                    <div class="info-box">
                        <i class="fas fa-info-circle"></i> A temporary password will be generated for new users. They must change it on first login.
                    </div>

                    <form method="POST" action="UserController.php" class="needs-validation">
                        <input type="hidden" name="action" value="store">

                        <!-- User Credentials Section -->
                        <div class="form-section">
                            <h5><i class="fas fa-user"></i> User Information</h5>
                            
                            <div class="mb-3">
                                <label class="form-label">Username <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="username" required 
                                       placeholder="Enter unique username" minlength="3" maxlength="50">
                                <small class="text-muted">Letters, numbers, and underscores only. Minimum 3 characters.</small>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Full Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="fullname" required 
                                       placeholder="Enter user's full name">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Email Address <span class="text-danger">*</span></label>
                                <input type="email" class="form-control" name="email" required 
                                       placeholder="Enter email address">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Phone Number</label>
                                <input type="tel" class="form-control" name="phone" 
                                       placeholder="Enter phone number (optional)">
                            </div>
                        </div>

                        <!-- Role Assignment Section -->
                        <div class="form-section">
                            <h5><i class="fas fa-shield-alt"></i> Role Assignment</h5>
                            
                            <div class="mb-3">
                                <label class="form-label">User Role <span class="text-danger">*</span></label>
                                <select class="form-select" name="role" required>
                                    <option value="">Select a role</option>
                                    <option value="admin">Admin - Full system access</option>
                                    <option value="staff">Staff - Data management access</option>
                                    <option value="volunteer">Volunteer - Limited access</option>
                                    <option value="donor">Donor - Donation tracking</option>
                                </select>
                                <small class="text-muted d-block mt-2">
                                    <strong>Admin:</strong> Full system control<br>
                                    <strong>Staff:</strong> Can manage data<br>
                                    <strong>Volunteer:</strong> Can record attendance<br>
                                    <strong>Donor:</strong> Can view donation records
                                </small>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-success btn-lg flex-grow-1">
                                <i class="fas fa-save"></i> Create User
                            </button>
                            <a href="UserController.php?action=list" class="btn btn-outline-secondary btn-lg">
                                <i class="fas fa-times"></i> Cancel
                            </a>
                        </div>
                    </form>
                </div>

                <!-- Password Policy Info -->
                <div class="alert alert-info mt-4">
                    <h6><i class="fas fa-lock"></i> Password Policy</h6>
                    <ul class="mb-0">
                        <li>A temporary password will be auto-generated for new users</li>
                        <li>Users must change the password on their first login</li>
                        <li>Passwords are hashed using bcrypt for security</li>
                        <li>Only the user's temporary password will be visible to you during creation</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <?php include __DIR__ . "/../includes/footer.php"; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Form validation
        (function() {
            'use strict';
            const forms = document.querySelectorAll('.needs-validation');
            Array.from(forms).forEach(form => {
                form.addEventListener('submit', event => {
                    if (!form.checkValidity()) {
                        event.preventDefault();
                        event.stopPropagation();
                    }
                    form.classList.add('was-validated');
                }, false);
            });
        })();
    </script>
<?php require_once __DIR__ . "/../includes/layout-footer.php"; ?>
