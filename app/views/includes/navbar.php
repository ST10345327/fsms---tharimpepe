<?php
/**
 * Module: Shared Navigation Component
 * Purpose: Display navigation bar across all pages
 * Reference: Task 2b System Design - UI Layout
 * Author: WIL Student
 */

require_once __DIR__ . "/../../helpers/SessionHandler.php";
$currentUser = getCurrentUser();
?>

<nav class="navbar navbar-expand-lg navbar-dark mb-4">
    <div class="container-fluid">
        <a class="navbar-brand" href="../../views/dashboard.php">
            <i class="fas fa-hand-holding-heart"></i> FSMS
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <a class="nav-link" href="../../views/dashboard.php">
                        <i class="fas fa-home"></i> Home
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="../controllers/VolunteerController.php?action=list">
                        <i class="fas fa-users"></i> Volunteers
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="../controllers/BeneficiaryController.php?action=list">
                        <i class="fas fa-user-friends"></i> Beneficiaries
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="../controllers/AttendanceController.php?action=list">
                        <i class="fas fa-clipboard-check"></i> Attendance
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="../controllers/FoodStockController.php?action=list">
                        <i class="fas fa-boxes"></i> Food Stock
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="../controllers/DonationController.php?action=list">
                        <i class="fas fa-gift"></i> Donations
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="../controllers/VolunteerScheduleController.php?action=list">
                        <i class="fas fa-calendar"></i> Scheduling
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="../controllers/DashboardController.php?action=overview">
                        <i class="fas fa-chart-line"></i> Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="../controllers/ReportsController.php?action=dashboard">
                        <i class="fas fa-file-pdf"></i> Reports
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="../controllers/UserController.php?action=list">
                        <i class="fas fa-users-cog"></i> Users
                    </a>
                </li>
                <li class="nav-item">
                    <span class="navbar-text text-white me-3">
                        <i class="fas fa-user-circle"></i> <?php echo htmlspecialchars($currentUser['username'] ?? 'Guest'); ?>
                    </span>
                    <a href="../../controllers/AuthController.php?action=logout" class="btn btn-outline-light btn-sm">
                        Logout
                    </a>
                </li>
            </ul>
        </div>
    </div>
</nav>
