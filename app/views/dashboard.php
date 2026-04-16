<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - FSMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background-color: #f5f7fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .navbar {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        .navbar-brand {
            font-weight: 700;
            font-size: 20px;
            color: white !important;
        }
        .nav-link {
            color: rgba(255, 255, 255, 0.8) !important;
            transition: color 0.3s ease;
        }
        .nav-link:hover {
            color: white !important;
        }
        .dashboard-container {
            padding: 40px 20px;
        }
        .welcome-card {
            background: white;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            margin-bottom: 30px;
            border-left: 5px solid #667eea;
        }
        .welcome-card h2 {
            color: #333;
            margin-bottom: 10px;
        }
        .welcome-card p {
            color: #666;
            margin: 0;
        }
        .feature-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-top: 30px;
        }
        .feature-card {
            background: white;
            border-radius: 10px;
            padding: 25px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            text-align: center;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .feature-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
        }
        .feature-card i {
            font-size: 40px;
            color: #667eea;
            margin-bottom: 15px;
        }
        .feature-card h5 {
            color: #333;
            margin-bottom: 10px;
            font-weight: 600;
        }
        .feature-card p {
            color: #666;
            font-size: 14px;
            margin: 0;
        }
        .user-info {
            color: white !important;
            font-size: 14px;
        }
        .logout-btn {
            background-color: rgba(255, 255, 255, 0.2);
            border: 1px solid rgba(255, 255, 255, 0.3);
            color: white;
            padding: 6px 15px;
            border-radius: 5px;
            text-decoration: none;
            transition: all 0.3s ease;
            margin-left: 10px;
        }
        .logout-btn:hover {
            background-color: rgba(255, 255, 255, 0.3);
            color: white;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <?php 
    // HZ-AUTH-DASHBOARD-001: Require authentication to view this page
    require_once "../helpers/SessionHandler.php";
    requireLogin();
    
    $user = getCurrentUser();
    ?>

    <!-- Navigation Bar -->
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="../views/dashboard.php">
                <i class="fas fa-hand-holding-heart"></i> FSMS
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="#"><i class="fas fa-home"></i> Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#"><i class="fas fa-users"></i> Beneficiaries</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#"><i class="fas fa-chart-bar"></i> Reports</a>
                    </li>
                    <li class="nav-item">
                        <span class="user-info">
                            <i class="fas fa-user-circle"></i> <?php echo htmlspecialchars($user['username']); ?>
                            <a href="../controllers/AuthController.php?action=logout" class="logout-btn">Logout</a>
                        </span>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="dashboard-container">
        <div class="container-fluid">
            <!-- Welcome Card -->
            <!-- HZ-AUTH-DASHBOARD-002: Personalized welcome message -->
            <div class="welcome-card">
                <h2>Welcome back, <?php echo htmlspecialchars($user['username']); ?>!</h2>
                <p>You are logged in as a <strong><?php echo ucfirst(htmlspecialchars($user['role'])); ?></strong></p>
            </div>

            <!-- Feature Cards -->
            <h3 class="mb-4">Available Functions</h3>
            <div class="feature-grid">
                <!-- HZ-AUTH-DASHBOARD-003: Placeholder cards for system modules -->
                <div class="feature-card">
                    <i class="fas fa-id-card"></i>
                    <h5>Beneficiary Management</h5>
                    <p>Register and manage beneficiary records</p>
                </div>

                <div class="feature-card">
                    <i class="fas fa-clipboard-list"></i>
                    <h5>Attendance Tracking</h5>
                    <p>Record daily attendance for feeding sessions</p>
                </div>

                <div class="feature-card">
                    <i class="fas fa-gift"></i>
                    <h5>Donation Management</h5>
                    <p>Track and manage donations</p>
                </div>

                <div class="feature-card">
                    <i class="fas fa-boxes"></i>
                    <h5>Food Stock</h5>
                    <p>Monitor food inventory levels</p>
                </div>

                <a href="../controllers/VolunteerController.php?action=list" class="feature-card" style="text-decoration: none; color: inherit;">
                    <i class="fas fa-users"></i>
                    <h5>Volunteer Scheduling</h5>
                    <p>Manage volunteer assignments</p>
                </a>

                <div class="feature-card">
                    <i class="fas fa-chart-pie"></i>
                    <h5>Reports</h5>
                    <p>View system reports and analytics</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-light text-center py-4 mt-5">
        <p class="text-muted mb-0">&copy; 2026 Tharimpepe Feeding Scheme Management System. All rights reserved.</p>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
