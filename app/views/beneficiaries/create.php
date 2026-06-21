<?php
$pageTitle = 'Register New Beneficiary';
require_once __DIR__ . "/../includes/layout-header.php";
?>

<div class="fsms-form-shell">
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger" role="alert"><?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></div>
        <?php elseif (!empty($error)): ?>
            <div class="alert alert-danger" role="alert"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success" role="alert"><?php echo htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?></div>
        <?php elseif (!empty($success)): ?>
            <div class="alert alert-success" role="alert"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>

        <div class="fsms-screen-title">
            <a href="BeneficiaryController.php?action=list" aria-label="Back to beneficiaries">
                <i class="fas fa-arrow-left" aria-hidden="true"></i>
            </a>
            <div>
                <h2>Register New Beneficiary</h2>
                <p>Complete the form to register a new beneficiary</p>
            </div>
        </div>

        <section class="prototype-form-card">
            <form method="POST" action="BeneficiaryController.php" id="beneficiaryForm">
                <input type="hidden" name="action" value="store">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? ''); ?>">
                <input type="hidden" name="registration_date" value="<?php echo date('Y-m-d'); ?>">
                <input type="hidden" name="category" value="<?php echo htmlspecialchars($_POST['category'] ?? 'General'); ?>">

                <div class="form-grid">
                    <div>
                        <label for="firstName">First Name <span class="required">*</span></label>
                        <input type="text" class="form-control" id="firstName" name="first_name"
                               value="<?php echo htmlspecialchars($_POST['first_name'] ?? ''); ?>"
                               placeholder="Enter first name" required maxlength="50">
                    </div>

                    <div>
                        <label for="lastName">Last Name <span class="required">*</span></label>
                        <input type="text" class="form-control" id="lastName" name="last_name"
                               value="<?php echo htmlspecialchars($_POST['last_name'] ?? ''); ?>"
                               placeholder="Enter last name" required maxlength="50">
                    </div>

                    <div>
                        <label for="dateOfBirth">Date of Birth <span class="required">*</span></label>
                        <input type="date" class="form-control" id="dateOfBirth" name="date_of_birth"
                               value="<?php echo htmlspecialchars($_POST['date_of_birth'] ?? ''); ?>"
                               required max="<?php echo date('Y-m-d'); ?>">
                    </div>

                    <div>
                        <label for="gender">Gender <span class="required">*</span></label>
                        <select class="form-select" id="gender" name="gender" required>
                            <option value="">Select gender</option>
                            <?php foreach (['Male', 'Female', 'Other'] as $gender): ?>
                                <option value="<?php echo $gender; ?>" <?php echo ($_POST['gender'] ?? '') === $gender ? 'selected' : ''; ?>>
                                    <?php echo $gender; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div>
                        <label for="guardianName">Guardian Name (Optional)</label>
                        <input type="text" class="form-control" id="guardianName" name="guardian_name"
                               value="<?php echo htmlspecialchars($_POST['guardian_name'] ?? ''); ?>"
                               placeholder="Enter guardian name" maxlength="100">
                    </div>

                    <div>
                        <label for="contactNumber">Contact Number <span class="required">*</span></label>
                        <input type="tel" class="form-control" id="contactNumber" name="contact_number"
                               value="<?php echo htmlspecialchars($_POST['contact_number'] ?? ''); ?>"
                               placeholder="+27 XX XXX XXXX" required maxlength="20">
                    </div>

                    <div class="form-wide">
                        <label for="address">Address</label>
                        <textarea class="form-control" id="address" name="address" rows="3"
                                  placeholder="Enter residential address"><?php echo htmlspecialchars($_POST['address'] ?? ''); ?></textarea>
                    </div>

                    <div class="form-wide">
                        <label for="dietaryNeeds">Dietary Needs (Optional)</label>
                        <textarea class="form-control" id="dietaryNeeds" name="dietary_needs" rows="2"
                                  placeholder="Any allergies or special dietary requirements"><?php echo htmlspecialchars($_POST['dietary_needs'] ?? ''); ?></textarea>
                    </div>
                </div>

                <div class="form-actions">
                    <a href="BeneficiaryController.php?action=list" class="btn btn-outline-secondary px-4">Cancel</a>
                    <button type="submit" class="btn btn-primary px-4">
                        <i class="fas fa-save me-2" aria-hidden="true"></i>Save Beneficiary
                    </button>
                </div>
            </form>
        </section>
</div>
<?php require_once __DIR__ . "/../includes/layout-footer.php"; ?>
