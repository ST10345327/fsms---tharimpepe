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

    -- Personal information
    FullName VARCHAR(255) DEFAULT NULL,
    Phone VARCHAR(15) DEFAULT NULL,

    -- Status: 'active' or 'inactive' (soft delete)
    Status ENUM('active', 'inactive') DEFAULT 'active',

    -- Role assignment (simplified; for full RBAC see user_roles table)
    Role ENUM('admin', 'volunteer', 'donor', 'staff') DEFAULT 'volunteer',

    -- Audit fields
    CreatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UpdatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CreatedBy INT DEFAULT NULL,
    UpdatedBy INT DEFAULT NULL,

    -- Index for status filtering
    INDEX idx_users_status (Status),
    INDEX idx_users_role (Role)
);

-- ==============================================================
-- HZ-ROLES-TABLE-001
-- Purpose: Define available roles for RBAC
-- Entity: Roles
-- Fields: RoleID (PK), RoleName, Description
-- ==============================================================
CREATE TABLE IF NOT EXISTS Roles (
    RoleID INT AUTO_INCREMENT PRIMARY KEY,
    RoleName VARCHAR(50) UNIQUE NOT NULL,
    Description VARCHAR(255),
    CreatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert default roles
INSERT IGNORE INTO Roles (RoleName, Description) VALUES
    ('admin', 'System administrator with full access'),
    ('volunteer', 'Volunteer with limited access'),
    ('donor', 'Donor who can view donation history'),
    ('staff', 'Staff member with operational access'),
    ('guest', 'Unauthenticated public user');

-- ==============================================================
-- HZ-USER-ROLES-TABLE-001
-- Purpose: Many-to-many user-role assignment for RBAC
-- Entity: UserRoles
-- Fields: UserRoleID (PK), UserID (FK), RoleID (FK)
-- ==============================================================
CREATE TABLE IF NOT EXISTS UserRoles (
    UserRoleID INT AUTO_INCREMENT PRIMARY KEY,
    UserID INT NOT NULL,
    RoleID INT NOT NULL,
    AssignedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    AssignedBy INT DEFAULT NULL,
    FOREIGN KEY (UserID) REFERENCES Users(UserID) ON DELETE CASCADE,
    FOREIGN KEY (RoleID) REFERENCES Roles(RoleID) ON DELETE CASCADE,
    UNIQUE KEY uq_user_role (UserID, RoleID),
    INDEX idx_user_roles_user (UserID),
    INDEX idx_user_roles_role (RoleID)
);

-- ==============================================================
-- HZ-AUTH-TOKENS-TABLE-001
-- Purpose: Store authentication tokens for mobile/API access
-- Entity: AuthTokens
-- Fields: TokenID (PK), UserID (FK), TokenHash, RefreshTokenHash, ExpiresAt, etc.
-- ==============================================================
CREATE TABLE IF NOT EXISTS AuthTokens (
    TokenID INT AUTO_INCREMENT PRIMARY KEY,
    UserID INT NOT NULL,
    TokenHash VARCHAR(64) NOT NULL UNIQUE,
    RefreshTokenHash VARCHAR(64) DEFAULT NULL,
    ExpiresAt DATETIME NOT NULL,
    RefreshExpiresAt DATETIME DEFAULT NULL,
    CreatedAt DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    LastUsedAt DATETIME DEFAULT NULL,
    RevokedAt DATETIME DEFAULT NULL,
    DeviceInfo VARCHAR(255) DEFAULT NULL,
    IPAddress VARCHAR(45) DEFAULT NULL,
    FOREIGN KEY (UserID) REFERENCES Users(UserID) ON DELETE CASCADE,
    INDEX idx_token_hash (TokenHash),
    INDEX idx_refresh_hash (RefreshTokenHash),
    INDEX idx_user_tokens (UserID),
    INDEX idx_token_expiry (ExpiresAt)
);

-- ==============================================================
-- HZ-PASSWORD-RESETS-TABLE-001
-- Purpose: Store password reset tokens
-- Entity: PasswordResets
-- Fields: ResetID (PK), UserID (FK), Token, ExpiresAt, UsedAt
-- ==============================================================
CREATE TABLE IF NOT EXISTS PasswordResets (
    ResetID INT AUTO_INCREMENT PRIMARY KEY,
    UserID INT NOT NULL,
    Token VARCHAR(64) NOT NULL UNIQUE,
    ExpiresAt DATETIME NOT NULL,
    UsedAt DATETIME DEFAULT NULL,
    CreatedAt DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    IPAddress VARCHAR(45) DEFAULT NULL,
    FOREIGN KEY (UserID) REFERENCES Users(UserID) ON DELETE CASCADE,
    INDEX idx_password_reset_token (Token),
    INDEX idx_password_reset_user (UserID)
);

-- ==============================================================
-- HZ-VOL-TABLE-002
-- Purpose: Store volunteer profile information linked to Users
-- Entity: Volunteers (from ERD)
-- Fields: VolunteerID (PK), UserID (FK), Skills, AvailabilityStatus, etc.
-- ==============================================================
CREATE TABLE IF NOT EXISTS Volunteers (
    VolunteerID INT AUTO_INCREMENT PRIMARY KEY,
    UserID INT NOT NULL,
    Skills TEXT DEFAULT NULL,
    AvailabilityStatus ENUM('available', 'unavailable', 'on_leave') DEFAULT 'available',
    Address TEXT DEFAULT NULL,
    Notes TEXT DEFAULT NULL,
    Status ENUM('pending', 'approved', 'rejected', 'inactive') DEFAULT 'pending',
    ApprovedBy INT DEFAULT NULL,
    ApprovedAt DATETIME DEFAULT NULL,
    CreatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UpdatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (UserID) REFERENCES Users(UserID) ON DELETE CASCADE,
    FOREIGN KEY (ApprovedBy) REFERENCES Users(UserID) ON DELETE SET NULL,
    INDEX idx_volunteer_user (UserID),
    INDEX idx_volunteer_status (Status),
    INDEX idx_volunteer_availability (AvailabilityStatus)
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
    CreatedBy INT DEFAULT NULL,
    CreatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UpdatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (CreatedBy) REFERENCES Users(UserID) ON DELETE SET NULL,
    INDEX idx_beneficiary_status (Status),
    INDEX idx_beneficiary_name (LastName, FirstName)
);

-- ==============================================================
-- HZ-MEAL-TABLE-004
-- Purpose: Store meal session metadata for attendance and reporting
-- Entity: MealSession (from ERD)
-- Fields: MealSessionID (PK), SessionDate, SessionType, Location, Notes, CreatedAt
-- ==============================================================
CREATE TABLE IF NOT EXISTS MealSession (
    MealSessionID INT AUTO_INCREMENT PRIMARY KEY,
    SessionDate DATE NOT NULL,
    SessionType VARCHAR(30) NOT NULL,
    Location VARCHAR(100),
    Notes TEXT,
    CreatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_meal_session (SessionDate, SessionType, Location)
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
    MealSessionID INT DEFAULT NULL,
    SessionDate DATE NOT NULL,
    Status ENUM('present', 'absent', 'marked') DEFAULT 'present',
    Notes TEXT,
    CreatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (BeneficiaryID) REFERENCES Beneficiaries(BeneficiaryID) ON DELETE CASCADE,
    FOREIGN KEY (MealSessionID) REFERENCES MealSession(MealSessionID) ON DELETE SET NULL,
    INDEX idx_attendance_beneficiary (BeneficiaryID),
    INDEX idx_attendance_meal_session (MealSessionID),
    INDEX idx_attendance_date (SessionDate),
    UNIQUE KEY uq_attendance (BeneficiaryID, SessionDate, MealSessionID)
);

-- ==============================================================
-- HZ-DON-TABLE-005
-- Purpose: Track all donations received (cash, food, supplies)
-- Entity: Donations (from ERD)
-- Fields: DonationID (PK), UserID (FK), DonorName, Amount, Type, Date, Status, etc.
-- ==============================================================
CREATE TABLE IF NOT EXISTS Donations (
    DonationID INT AUTO_INCREMENT PRIMARY KEY,
    UserID INT DEFAULT NULL,
    DonorName VARCHAR(150) NOT NULL,
    DonorEmail VARCHAR(100),
    DonationType ENUM('cash', 'food', 'supplies', 'other') DEFAULT 'cash',
    Amount DECIMAL(10, 2) DEFAULT NULL,
    Description TEXT,
    PaymentMethod VARCHAR(50) DEFAULT NULL,
    TransactionReference VARCHAR(100) DEFAULT NULL,
    Status ENUM('pending', 'completed', 'failed', 'refunded') DEFAULT 'completed',
    DonationDate DATE NOT NULL,
    CreatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UpdatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (UserID) REFERENCES Users(UserID) ON DELETE SET NULL,
    INDEX idx_donation_user (UserID),
    INDEX idx_donation_date (DonationDate),
    INDEX idx_donation_status (Status),
    INDEX idx_donation_type (DonationType)
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
    UpdatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_stock_name (ItemName),
    INDEX idx_stock_expiry (ExpiryDate)
);

-- ==============================================================
-- HZ-DIST-TABLE-013
-- Purpose: Track food distribution events
-- Entity: FoodDistribution
-- Fields: DistributionID (PK), FoodStockID (FK), QuantityDistributed, DistributionDate, Location, Purpose, Notes
-- ==============================================================
CREATE TABLE IF NOT EXISTS FoodDistribution (
    DistributionID INT AUTO_INCREMENT PRIMARY KEY,
    FoodStockID INT NOT NULL,
    QuantityDistributed INT NOT NULL,
    DistributionDate DATE NOT NULL,
    Location VARCHAR(100),
    Purpose TEXT,
    Notes TEXT,
    CreatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (FoodStockID) REFERENCES FoodStock(FoodStockID) ON DELETE CASCADE,
    INDEX idx_food_distribution_stock (FoodStockID),
    INDEX idx_food_distribution_date (DistributionDate)
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
    RecipientID INT DEFAULT NULL,
    Subject VARCHAR(200),
    Content TEXT NOT NULL,
    IsRead BOOLEAN DEFAULT FALSE,
    ReadAt DATETIME DEFAULT NULL,
    ParentMessageID INT DEFAULT NULL,
    SentAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (SenderID) REFERENCES Users(UserID) ON DELETE CASCADE,
    FOREIGN KEY (RecipientID) REFERENCES Users(UserID) ON DELETE SET NULL,
    FOREIGN KEY (ParentMessageID) REFERENCES Messages(MessageID) ON DELETE SET NULL,
    INDEX idx_message_sender (SenderID),
    INDEX idx_message_recipient (RecipientID),
    INDEX idx_message_read (IsRead),
    INDEX idx_message_subject (Subject)
);

-- ==============================================================
-- HZ-BLOG-TABLE-008
-- Purpose: Store blog posts and announcements for community engagement
-- Entity: BlogPosts (from ERD)
-- Fields: BlogPostID (PK), Title, Content, FeaturedImage, AuthorID (FK), PublishDate
-- ==============================================================
CREATE TABLE IF NOT EXISTS BlogPosts (
    BlogPostID INT AUTO_INCREMENT PRIMARY KEY,
    Title VARCHAR(255) NOT NULL,
    Content TEXT NOT NULL,
    FeaturedImage VARCHAR(255) DEFAULT NULL,
    Excerpt TEXT DEFAULT NULL,
    Status ENUM('draft', 'published', 'archived') DEFAULT 'draft',
    AuthorID INT NOT NULL,
    PublishDate DATE NOT NULL,
    CreatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UpdatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (AuthorID) REFERENCES Users(UserID) ON DELETE CASCADE,
    INDEX idx_blog_author (AuthorID),
    INDEX idx_blog_publish_date (PublishDate),
    INDEX idx_blog_status (Status)
);

-- ==============================================================
-- HZ-ANNOUNCEMENTS-TABLE-001
-- Purpose: Store system announcements (distinct from blog posts)
-- Entity: Announcements
-- Fields: AnnouncementID (PK), Title, Content, Priority, Status, CreatedBy, etc.
-- ==============================================================
CREATE TABLE IF NOT EXISTS Announcements (
    AnnouncementID INT AUTO_INCREMENT PRIMARY KEY,
    Title VARCHAR(255) NOT NULL,
    Content TEXT NOT NULL,
    Priority ENUM('low', 'normal', 'high', 'urgent') DEFAULT 'normal',
    Status ENUM('draft', 'published', 'archived') DEFAULT 'published',
    CreatedBy INT NOT NULL,
    PublishDate DATETIME DEFAULT NULL,
    ExpiryDate DATETIME DEFAULT NULL,
    CreatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UpdatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (CreatedBy) REFERENCES Users(UserID) ON DELETE CASCADE,
    INDEX idx_announcement_status (Status),
    INDEX idx_announcement_priority (Priority),
    INDEX idx_announcement_date (PublishDate)
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
    FOREIGN KEY (UploadedBy) REFERENCES Users(UserID) ON DELETE SET NULL,
    INDEX idx_gallery_uploader (UploadedBy)
);

-- ==============================================================
-- HZ-OUTREACH-TABLE-001
-- Purpose: Manage community outreach programs
-- Entity: OutreachPrograms
-- Fields: ProgramID (PK), Title, Description, Date, Location, Capacity, Status, etc.
-- ==============================================================
CREATE TABLE IF NOT EXISTS OutreachPrograms (
    ProgramID INT AUTO_INCREMENT PRIMARY KEY,
    Title VARCHAR(255) NOT NULL,
    Description TEXT,
    ProgramDate DATE NOT NULL,
    Location VARCHAR(255),
    Capacity INT DEFAULT 0,
    Status ENUM('planned', 'active', 'completed', 'cancelled') DEFAULT 'planned',
    CreatedBy INT NOT NULL,
    CreatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UpdatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (CreatedBy) REFERENCES Users(UserID) ON DELETE CASCADE,
    INDEX idx_outreach_date (ProgramDate),
    INDEX idx_outreach_status (Status),
    INDEX idx_outreach_location (Location)
);

-- ==============================================================
-- HZ-PROG-VOL-TABLE-001
-- Purpose: Track volunteer assignments to outreach programs
-- Entity: ProgramVolunteers
-- Fields: ProgramVolunteerID (PK), ProgramID (FK), VolunteerID (FK), Status, etc.
-- ==============================================================
CREATE TABLE IF NOT EXISTS ProgramVolunteers (
    ProgramVolunteerID INT AUTO_INCREMENT PRIMARY KEY,
    ProgramID INT NOT NULL,
    VolunteerID INT NOT NULL,
    Status ENUM('assigned', 'confirmed', 'attended', 'cancelled', 'no-show') DEFAULT 'assigned',
    AssignedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    Notes TEXT,
    FOREIGN KEY (ProgramID) REFERENCES OutreachPrograms(ProgramID) ON DELETE CASCADE,
    FOREIGN KEY (VolunteerID) REFERENCES Volunteers(VolunteerID) ON DELETE CASCADE,
    UNIQUE KEY uq_program_volunteer (ProgramID, VolunteerID),
    INDEX idx_program_volunteer_program (ProgramID),
    INDEX idx_program_volunteer_volunteer (VolunteerID)
);

-- ==============================================================
-- HZ-VOLA-TABLE-011
-- Purpose: Track volunteer availability by day of week
-- Entity: VolunteerAvailability
-- Fields: VolunteerAvailabilityID (PK), VolunteerID (FK), DayOfWeek, IsAvailable, Notes
-- ==============================================================
CREATE TABLE IF NOT EXISTS VolunteerAvailability (
    VolunteerAvailabilityID INT AUTO_INCREMENT PRIMARY KEY,
    VolunteerID INT NOT NULL,
    DayOfWeek ENUM('Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday') NOT NULL,
    IsAvailable BOOLEAN DEFAULT TRUE,
    Notes TEXT,
    CreatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (VolunteerID) REFERENCES Volunteers(VolunteerID) ON DELETE CASCADE,
    UNIQUE KEY uq_volunteer_day (VolunteerID, DayOfWeek),
    INDEX idx_volunteer_availability (VolunteerID)
);

-- ==============================================================
-- HZ-VOLS-TABLE-012
-- Purpose: Track volunteer shift schedules
-- Entity: VolunteerSchedules
-- Fields: ScheduleID (PK), VolunteerID (FK), ScheduleDate, StartTime, EndTime, Role, Location, Status, HoursWorked, Notes
-- ==============================================================
CREATE TABLE IF NOT EXISTS VolunteerSchedules (
    ScheduleID INT AUTO_INCREMENT PRIMARY KEY,
    VolunteerID INT NOT NULL,
    ScheduleDate DATE NOT NULL,
    StartTime TIME,
    EndTime TIME,
    Role VARCHAR(100),
    Location VARCHAR(100),
    Status ENUM('scheduled', 'completed', 'cancelled', 'no-show') DEFAULT 'scheduled',
    HoursWorked DECIMAL(4, 2),
    Notes TEXT,
    CreatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UpdatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (VolunteerID) REFERENCES Volunteers(VolunteerID) ON DELETE CASCADE,
    INDEX idx_volunteer_schedules_volunteer (VolunteerID),
    INDEX idx_volunteer_schedules_date (ScheduleDate),
    INDEX idx_volunteer_schedules_status (Status)
);

-- ==============================================================
-- HZ-CHATBOT-FAQ-TABLE-001
-- Purpose: Store FAQ data for AI chatbot responses
-- Entity: ChatbotFAQ
-- Fields: FAQID (PK), Question, Answer, Category, Keywords, etc.
-- ==============================================================
CREATE TABLE IF NOT EXISTS ChatbotFAQ (
    FAQID INT AUTO_INCREMENT PRIMARY KEY,
    Question VARCHAR(500) NOT NULL,
    Answer TEXT NOT NULL,
    Category VARCHAR(100) DEFAULT NULL,
    Keywords TEXT DEFAULT NULL,
    Priority INT DEFAULT 0,
    IsActive BOOLEAN DEFAULT TRUE,
    CreatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UpdatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_faq_category (Category),
    INDEX idx_faq_active (IsActive),
    INDEX idx_faq_priority (Priority),
    FULLTEXT INDEX idx_faq_search (Question, Answer, Keywords)
);

-- ==============================================================
-- HZ-PAYMENTS-TABLE-001
-- Purpose: Track payment gateway transactions
-- Entity: PaymentTransactions
-- Fields: PaymentID (PK), DonationID (FK), Gateway, Amount, Status, etc.
-- ==============================================================
CREATE TABLE IF NOT EXISTS PaymentTransactions (
    PaymentID INT AUTO_INCREMENT PRIMARY KEY,
    DonationID INT DEFAULT NULL,
    UserID INT DEFAULT NULL,
    Gateway VARCHAR(50) NOT NULL,
    GatewayReference VARCHAR(255) DEFAULT NULL,
    Amount DECIMAL(10, 2) NOT NULL,
    Currency VARCHAR(3) DEFAULT 'ZAR',
    Status ENUM('pending', 'processing', 'completed', 'failed', 'refunded') DEFAULT 'pending',
    ResponseData TEXT DEFAULT NULL,
    CreatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UpdatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (DonationID) REFERENCES Donations(DonationID) ON DELETE SET NULL,
    FOREIGN KEY (UserID) REFERENCES Users(UserID) ON DELETE SET NULL,
    INDEX idx_payment_donation (DonationID),
    INDEX idx_payment_user (UserID),
    INDEX idx_payment_status (Status),
    INDEX idx_payment_gateway_ref (GatewayReference)
);

-- ==============================================================
-- HZ-LOG-TABLE-010
-- Purpose: Track user actions for audit trail
-- Entity: ActivityLog
-- Fields: ActivityID (PK), UserID (FK), Action, Details, Timestamp, AffectedEntityName, AffectedEntityID
-- ==============================================================
CREATE TABLE IF NOT EXISTS ActivityLog (
    ActivityID INT AUTO_INCREMENT PRIMARY KEY,
    UserID INT NOT NULL,
    Action VARCHAR(100) NOT NULL,
    AffectedEntityName VARCHAR(100),
    AffectedEntityID INT,
    Details TEXT,
    IPAddress VARCHAR(45) DEFAULT NULL,
    UserAgent VARCHAR(500) DEFAULT NULL,
    Timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (UserID) REFERENCES Users(UserID) ON DELETE CASCADE,
    INDEX idx_activity_log_user (UserID),
    INDEX idx_activity_log_timestamp (Timestamp),
    INDEX idx_activity_log_action (Action),
    INDEX idx_activity_log_entity (AffectedEntityName, AffectedEntityID)
);

-- ==============================================================
-- Sample Admin Account (Password: admin123 - hashed with password_hash PHP function)
-- Password hash generated by PHP: password_hash('admin123', PASSWORD_BCRYPT)
-- ==============================================================
-- INSERT INTO Users (Username, Email, PasswordHash, FullName, Role, Status)
-- VALUES ('admin', 'admin@fsms.local', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'System Administrator', 'admin', 'active');