<?php
// migrations/20260619_ensure_schema.php
// Migration to create missing tables and columns for FSMS application.

require_once __DIR__ . '/../config/database.php'
function runMigration(PDO $pdo) {
    // Create MealSession table if missing
    $pdo->exec("CREATE TABLE IF NOT EXISTS MealSession (\n        MealSessionID INT AUTO_INCREMENT PRIMARY KEY,\n        SessionDate DATE NOT NULL,\n        SessionType VARCHAR(30) NOT NULL,\n        Location VARCHAR(100),\n        Notes TEXT,\n        CreatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,\n        UNIQUE KEY uq_meal_session (SessionDate, SessionType, Location)\n    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

    // Create Attendance table if missing, ensure required columns
    $pdo->exec("CREATE TABLE IF NOT EXISTS Attendance (\n        AttendanceID INT AUTO_INCREMENT PRIMARY KEY,\n        BeneficiaryID INT NOT NULL,\n        MealSessionID INT DEFAULT NULL,\n        SessionDate DATE NOT NULL,\n        Status ENUM('present','absent','marked') DEFAULT 'present',\n        Notes TEXT,\n        CreatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,\n        FOREIGN KEY (BeneficiaryID) REFERENCES Beneficiaries(BeneficiaryID) ON DELETE CASCADE,\n        FOREIGN KEY (MealSessionID) REFERENCES MealSession(MealSessionID) ON DELETE SET NULL,\n        INDEX idx_attendance_beneficiary (BeneficiaryID),\n        INDEX idx_attendance_meal_session (MealSessionID),\n        INDEX idx_attendance_date (SessionDate),\n        UNIQUE KEY uq_attendance (BeneficiaryID, SessionDate, MealSessionID)\n    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

    // Create Donations table if missing
    $pdo->exec("CREATE TABLE IF NOT EXISTS Donations (\n        DonationID INT AUTO_INCREMENT PRIMARY KEY,\n        UserID INT DEFAULT NULL,\n        DonorName VARCHAR(150) NOT NULL,\n        DonorEmail VARCHAR(100),\n        DonationType ENUM('cash','food','supplies','other') DEFAULT 'cash',\n        Amount DECIMAL(10,2) DEFAULT NULL,\n        Description TEXT,\n        PaymentMethod VARCHAR(50) DEFAULT NULL,\n        TransactionReference VARCHAR(100) DEFAULT NULL,\n        Status ENUM('pending','completed','failed','refunded') DEFAULT 'completed',\n        DonationDate DATE NOT NULL,\n        CreatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,\n        UpdatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,\n        FOREIGN KEY (UserID) REFERENCES Users(UserID) ON DELETE SET NULL,\n        INDEX idx_donation_user (UserID),\n        INDEX idx_donation_date (DonationDate),\n        INDEX idx_donation_status (Status),\n        INDEX idx_donation_type (DonationType)\n    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

    // Ensure SessionDate column exists in Attendance (in case table existed without it)
    $stmt = $pdo->query("SHOW COLUMNS FROM Attendance LIKE 'SessionDate'");
    if ($stmt->rowCount() === 0) {
        $pdo->exec("ALTER TABLE Attendance ADD COLUMN SessionDate DATE NOT NULL AFTER MealSessionID");
    }
}

try {
    $db = new Database();
    $pdo = $db->connect();
    if (!$pdo) {
        throw new Exception('Failed to obtain PDO connection');
    }
    runMigration($pdo);
    echo "Migration completed successfully.\n";
} catch (Exception $e) {
    error_log('Migration error: ' . $e->getMessage());
    echo "Migration failed: " . $e->getMessage() . "\n";
}
?>
