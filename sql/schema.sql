-- ==============================================================
-- Module: Feeding Scheme Management System (FSMS) Database
-- Purpose: Complete database schema for FSMS
-- Reference: Task 2b System Design - Database Entity Design
-- Author: WIL Student
-- Database: MySQL 8.0
-- ==============================================================

-- Create database
CREATE DATABASE IF NOT EXISTS fsms;
USE fsms;

-- ==============================================================
-- HZ-USER-TABLE-001
-- Purpose: Store user account information for authentication
-- Entity: Users (from ERD)
-- Fields: UserID (PK), Username, Email, PasswordHash, Role, CreatedAt
-- ==============================================================
CREATE TABLE IF NOT EXISTS Users (
    UserID INT AUTO_INCREMENT PRIMARY KEY,
    Username VARCHAR(50) UNIQUE NOT NULL,
    Email VARCHAR(100) UNIQUE NOT NULL,
    PasswordHash VARCHAR(255) NOT NULL,
    Role ENUM('admin', 'volunteer', 'donor', 'staff') DEFAULT 'volunteer',
    CreatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UpdatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    IsActive BOOLEAN DEFAULT TRUE
);

-- ==============================================================
-- HZ-VOL-TABLE-002
-- Purpose: Store volunteer profile information linked to Users
-- Entity: Volunteers (from ERD)
-- Fields: VolunteerID (PK), UserID (FK), FirstName, LastName, Phone, etc.
-- ==============================================================
CREATE TABLE IF NOT EXISTS Volunteers (
    VolunteerID INT AUTO_INCREMENT PRIMARY KEY,
    UserID INT NOT NULL,
    FirstName VARCHAR(100) NOT NULL,
    LastName VARCHAR(100) NOT NULL,
    Phone VARCHAR(15),
    Address TEXT,
    AvailabilityStatus ENUM('available', 'unavailable', 'on_leave') DEFAULT 'available',
    CreatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (UserID) REFERENCES Users(UserID) ON DELETE CASCADE
);

-- ==============================================================
-- HZ-BEN-TABLE-003
-- Purpose: Store beneficiary information for meal distribution tracking
-- Entity: Beneficiaries (from ERD)
-- Fields: BeneficiaryID (PK), FirstName, LastName, Age, Gender, Phone, Email, Address, RegistrationDate, Status, Notes, CreatedAt, UpdatedAt
-- ==============================================================
CREATE TABLE IF NOT EXISTS Beneficiaries (
    BeneficiaryID INT AUTO_INCREMENT PRIMARY KEY,
    FirstName VARCHAR(100) NOT NULL,
    LastName VARCHAR(100) NOT NULL,
    Age INT,
    Gender ENUM('Male', 'Female', 'Other'),
    Phone VARCHAR(15),
    Email VARCHAR(100),
    Address TEXT,
    RegistrationDate DATE NOT NULL,
    Status ENUM('active', 'inactive', 'suspended') DEFAULT 'active',
    Notes TEXT,
    CreatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UpdatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- ==============================================================
-- HZ-ATT-TABLE-004
-- Purpose: Track daily attendance of beneficiaries at feeding sessions
-- Entity: Attendance (from ERD)
-- Fields: AttendanceID (PK), BeneficiaryID (FK), SessionDate, Status
-- ==============================================================
CREATE TABLE IF NOT EXISTS Attendance (
    AttendanceID INT AUTO_INCREMENT PRIMARY KEY,
    BeneficiaryID INT NOT NULL,
    SessionDate DATE NOT NULL,
    Status ENUM('present', 'absent', 'marked') DEFAULT 'present',
    Notes TEXT,
    CreatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (BeneficiaryID) REFERENCES Beneficiaries(BeneficiaryID) ON DELETE CASCADE
);

-- ==============================================================
-- HZ-DON-TABLE-005
-- Purpose: Track all donations received (cash, food, supplies)
-- Entity: Donations (from ERD)
-- Fields: DonationID (PK), DonorName, Amount, Type, Date, etc.
-- ==============================================================
CREATE TABLE IF NOT EXISTS Donations (
    DonationID INT AUTO_INCREMENT PRIMARY KEY,
    DonorName VARCHAR(150) NOT NULL,
    DonorEmail VARCHAR(100),
    DonationType ENUM('cash', 'food', 'supplies', 'other') DEFAULT 'cash',
    Amount DECIMAL(10, 2),
    Description TEXT,
    DonationDate DATE NOT NULL,
    CreatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ==============================================================
-- HZ-STOCK-TABLE-006
-- Purpose: Track food inventory and stock levels
-- Entity: FoodStock (from ERD)
-- Fields: FoodStockID (PK), ItemName, Quantity, ExpiryDate, etc.
-- ==============================================================
CREATE TABLE IF NOT EXISTS FoodStock (
    FoodStockID INT AUTO_INCREMENT PRIMARY KEY,
    ItemName VARCHAR(150) NOT NULL,
    Quantity INT NOT NULL DEFAULT 0,
    Unit VARCHAR(50),
    ExpiryDate DATE,
    StockDate DATE NOT NULL,
    Notes TEXT,
    CreatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UpdatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- ==============================================================
-- HZ-MSG-TABLE-007
-- Purpose: Store system messages and communications
-- Entity: Messages (from ERD)
-- Fields: MessageID (PK), SenderID (FK), RecipientID (FK), Content, etc.
-- ==============================================================
CREATE TABLE IF NOT EXISTS Messages (
    MessageID INT AUTO_INCREMENT PRIMARY KEY,
    SenderID INT NOT NULL,
    RecipientID INT,
    Subject VARCHAR(200),
    Content TEXT NOT NULL,
    IsRead BOOLEAN DEFAULT FALSE,
    SentAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (SenderID) REFERENCES Users(UserID) ON DELETE CASCADE,
    FOREIGN KEY (RecipientID) REFERENCES Users(UserID) ON DELETE SET NULL
);

-- ==============================================================
-- HZ-BLOG-TABLE-008
-- Purpose: Store blog posts and announcements for community engagement
-- Entity: BlogPosts (from ERD)
-- Fields: BlogPostID (PK), Title, Content, AuthorID (FK), PublishDate
-- ==============================================================
CREATE TABLE IF NOT EXISTS BlogPosts (
    BlogPostID INT AUTO_INCREMENT PRIMARY KEY,
    Title VARCHAR(255) NOT NULL,
    Content TEXT NOT NULL,
    AuthorID INT NOT NULL,
    PublishDate DATE NOT NULL,
    UpdatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (AuthorID) REFERENCES Users(UserID) ON DELETE CASCADE
);

-- ==============================================================
-- HZ-GAL-TABLE-009
-- Purpose: Store gallery images and media assets
-- Entity: Gallery (from ERD)
-- Fields: GalleryID (PK), ImagePath, Title, UploadDate, etc.
-- ==============================================================
CREATE TABLE IF NOT EXISTS Gallery (
    GalleryID INT AUTO_INCREMENT PRIMARY KEY,
    ImagePath VARCHAR(255) NOT NULL,
    Title VARCHAR(200),
    Description TEXT,
    UploadedBy INT,
    UploadDate TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (UploadedBy) REFERENCES Users(UserID) ON DELETE SET NULL
);

-- ==============================================================
-- Create Index for Performance Optimization
-- ==============================================================
-- Note: UNIQUE constraints automatically create indexes, so we skip idx_username and idx_email
CREATE INDEX idx_volunteer_user ON Volunteers(UserID);
CREATE INDEX idx_attendance_beneficiary ON Attendance(BeneficiaryID);
CREATE INDEX idx_attendance_date ON Attendance(SessionDate);
CREATE INDEX idx_donation_date ON Donations(DonationDate);
-- Note: Messages table indexes commented out as table may not exist in current schema
-- CREATE INDEX idx_message_sender ON Messages(SenderID);
-- CREATE INDEX idx_message_recipient ON Messages(RecipientID);

-- ==============================================================
-- Sample Admin Account (Password: admin123 - hashed with password_hash PHP function)
-- ==============================================================
-- INSERT INTO Users (Username, Email, PasswordHash, Role) 
-- VALUES ('admin', 'admin@fsms.local', '$2y$10$...hash...', 'admin');
