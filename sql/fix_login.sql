-- Fix login error: Add missing ActivityLog table
CREATE TABLE IF NOT EXISTS ActivityLog (
    LogID INT AUTO_INCREMENT PRIMARY KEY,
    UserID INT NOT NULL,
    Action VARCHAR(50) NOT NULL,
    AffectedEntityName VARCHAR(100),
    AffectedEntityID INT,
    Details TEXT,
    Timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (UserID) REFERENCES Users(UserID) ON DELETE CASCADE
);

-- Add sample admin user if not exists (password: admin123)
INSERT IGNORE INTO Users (Username, Email, PasswordHash, Role, Status)
VALUES ('admin', 'admin@fsms.local', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 'active');
</write_to_file>