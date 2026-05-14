<?php $pageTitle = 'Dashboard'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register New Beneficiary - FSMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="/assets/css/fsms-ui.css">
    <style>
        .prototype-page {
            padding: 32px 30px;
        }

        .screen-title {
            align-items: flex-start;
            display: flex;
            gap: 28px;
            margin-bottom: 28px;
        }

        .screen-title a {
            color: #1b3a5c;
            font-size: 24px;
            line-height: 1;
            margin-top: 8px;
            text-decoration: none;
        }

        .screen-title h2 {
            color: #071326;
            font-size: 30px;
            font-weight: 700;
            line-height: 1.15;
            margin: 0 0 4px;
        }

        .screen-title p {
            color: #1f2a44;
            font-size: 18px;
            margin: 0;
        }

        .prototype-form-card {
            background: #fff;
            border: 1px solid #dfe3e8;
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(15, 23, 42, 0.16);
            padding: 32px;
        }

        .form-grid {
            display: grid;
            gap: 28px 30px;
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .form-wide {
            grid-column: 1 / -1;
        }

        .prototype-form-card label {
            color: #111827;
            display: block;
            font-size: 16px;
            font-weight: 700;
            margin-bottom: 10px;
        }

        .prototype-form-card .required {
            color: #ef4444;
        }

        .prototype-form-card .form-control,
        .prototype-form-card .form-select {
            border: 1px solid #cbd5e1;
            border-radius: 10px;
            color: #1b3a5c;
            font-size: 20px;
            min-height: 52px;
            padding: 12px 20px;
        }

        .prototype-form-card .form-control::placeholder {
            color: #8da0ba;
        }

        .prototype-form-card textarea.form-control {
            min-height: 112px;
            resize: vertical;
        }

        .form-actions {
            display: flex;
            gap: 14px;
            justify-content: flex-end;
            margin-top: 30px;
        }

        @media (max-width: 900px) {
            .form-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <?php include __DIR__ . "/../includes/navbar.php"; ?>

    <main class="container-fluid prototype-page">
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger" role="alert"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <div class="screen-title">
            <a href="BeneficiaryController.php?action=list" aria-label="Back to beneficiaries">
                <i class="fas fa-arrow-left" aria-hidden="true"></i>
            </a>
            <div>
                <h2>Register New Beneficiary</h2>
                <p>Complete the form to register a new beneficiary</p>
            </div>
        </div>

        <section class="prototype-form-card">
            <form method="POST" action="BeneficiaryController.php?action=create" id="beneficiaryForm">
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
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
