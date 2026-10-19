-- ============================================================
-- Tharimpepe Feeding Scheme Management System (FSMS)
-- Development / Demo Seed Data
-- ============================================================
-- This script is intended for local development/demo use.
-- It targets the existing project schema and does not create, alter,
-- drop, or delete tables or columns.
--
-- Notes:
--   * No existing rows are deleted.
--   * The existing admin row from schema.sql is not recreated.
--   * Demo user accounts use the bcrypt hash already supplied by
--     schema.sql. The schema comment identifies its development
--     password as: admin123. Do NOT use this password in production.
--   * Most inserts use NOT EXISTS / duplicate-safe logic so the
--     script can be run again without intentionally creating
--     duplicate demo records.
-- ============================================================

USE fsms;

START TRANSACTION;

-- ------------------------------------------------------------
-- 1. DEMO USERS
-- ------------------------------------------------------------
INSERT INTO Users
    (Username, Email, PasswordHash, FullName, Phone, Role, Status)
VALUES
    ('demo_admin',    'demo.admin@fsms.local',
     '$2y$10$92IXUNpkjOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
     'Tharimpepe Demo Admin', '0710000001', 'admin', 'active'),
    ('demo_volunteer1', 'volunteer1@fsms.local',
     '$2y$10$92IXUNpkjOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
     'Kagiso Molefe', '0710000002', 'volunteer', 'active'),
    ('demo_volunteer2', 'volunteer2@fsms.local',
     '$2y$10$92IXUNpkjOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
     'Naledi Mokoena', '0710000003', 'volunteer', 'active'),
    ('demo_staff', 'staff@fsms.local',
     '$2y$10$92IXUNpkjOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
     'Thabo Ndlovu', '0710000004', 'staff', 'active'),
    ('demo_donor', 'donor@fsms.local',
     '$2y$10$92IXUNpkjOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
     'Community Supporter', '0710000005', 'donor', 'active')
ON DUPLICATE KEY UPDATE Username = Username;

-- ------------------------------------------------------------
-- 2. VOLUNTEERS
-- ------------------------------------------------------------
INSERT INTO Volunteers
    (UserID, Address, AvailabilityStatus)
SELECT u.UserID, 'Tharimpepe Community, Gauteng', 'available'
FROM Users u
WHERE u.Username = 'demo_volunteer1'
  AND NOT EXISTS (
      SELECT 1 FROM Volunteers v WHERE v.UserID = u.UserID
  );

INSERT INTO Volunteers
    (UserID, Address, AvailabilityStatus)
SELECT u.UserID, 'Tharimpepe Community, Gauteng', 'available'
FROM Users u
WHERE u.Username = 'demo_volunteer2'
  AND NOT EXISTS (
      SELECT 1 FROM Volunteers v WHERE v.UserID = u.UserID
  );

-- ------------------------------------------------------------
-- 3. BENEFICIARIES
-- ------------------------------------------------------------
INSERT INTO Beneficiaries
    (FirstName, LastName, Age, Gender, Phone, Email, Address,
     RegistrationDate, Status, Notes)
SELECT 'Lerato', 'Molefe', 12, 'Female', '0721000001',
       'lerato.molefe@example.local',
       'Tharimpepe Community, Gauteng', '2026-08-05',
       'active', 'Registered for community meal support.'
WHERE NOT EXISTS (
    SELECT 1 FROM Beneficiaries
    WHERE FirstName = 'Lerato'
      AND LastName = 'Molefe'
      AND Phone = '0721000001'
);

INSERT INTO Beneficiaries
    (FirstName, LastName, Age, Gender, Phone, Email, Address,
     RegistrationDate, Status, Notes)
SELECT 'Kabelo', 'Mokoena', 15, 'Male', '0721000002',
       'kabelo.mokoena@example.local',
       'Tharimpepe Community, Gauteng', '2026-08-07',
       'active', 'Regular beneficiary.'
WHERE NOT EXISTS (
    SELECT 1 FROM Beneficiaries
    WHERE FirstName = 'Kabelo'
      AND LastName = 'Mokoena'
      AND Phone = '0721000002'
);

INSERT INTO Beneficiaries
    (FirstName, LastName, Age, Gender, Phone, Email, Address,
     RegistrationDate, Status, Notes)
SELECT 'Amogelang', 'Dube', 10, 'Female', '0721000003',
       'amogelang.dube@example.local',
       'Tharimpepe Community, Gauteng', '2026-08-10',
       'active', 'Registered during community outreach.'
WHERE NOT EXISTS (
    SELECT 1 FROM Beneficiaries
    WHERE FirstName = 'Amogelang'
      AND LastName = 'Dube'
      AND Phone = '0721000003'
);

INSERT INTO Beneficiaries
    (FirstName, LastName, Age, Gender, Phone, Email, Address,
     RegistrationDate, Status, Notes)
SELECT 'Neo', 'Radebe', 14, 'Male', '0721000004',
       'neo.radebe@example.local',
       'Tharimpepe Community, Gauteng', '2026-08-12',
       'active', 'Receives meals through the feeding scheme.'
WHERE NOT EXISTS (
    SELECT 1 FROM Beneficiaries
    WHERE FirstName = 'Neo'
      AND LastName = 'Radebe'
      AND Phone = '0721000004'
);

INSERT INTO Beneficiaries
    (FirstName, LastName, Age, Gender, Phone, Email, Address,
     RegistrationDate, Status, Notes)
SELECT 'Boitumelo', 'Seema', 11, 'Female', '0721000005',
       'boitumelo.seema@example.local',
       'Tharimpepe Community, Gauteng', '2026-08-15',
       'active', 'Regular meal programme participant.'
WHERE NOT EXISTS (
    SELECT 1 FROM Beneficiaries
    WHERE FirstName = 'Boitumelo'
      AND LastName = 'Seema'
      AND Phone = '0721000005'
);

-- ------------------------------------------------------------
-- 4. MEAL SESSIONS
-- ------------------------------------------------------------
INSERT INTO MealSession (SessionDate, SessionType, Location, Notes)
SELECT '2026-09-01', 'Lunch', 'Tharimpepe Community Hall',
       'Community lunch service.'
WHERE NOT EXISTS (
    SELECT 1 FROM MealSession
    WHERE SessionDate = '2026-09-01'
      AND SessionType = 'Lunch'
      AND Location = 'Tharimpepe Community Hall'
);

INSERT INTO MealSession (SessionDate, SessionType, Location, Notes)
SELECT '2026-09-03', 'Lunch', 'Tharimpepe Community Hall',
       'Community lunch service.'
WHERE NOT EXISTS (
    SELECT 1 FROM MealSession
    WHERE SessionDate = '2026-09-03'
      AND SessionType = 'Lunch'
      AND Location = 'Tharimpepe Community Hall'
);

INSERT INTO MealSession (SessionDate, SessionType, Location, Notes)
SELECT '2026-09-05', 'Lunch', 'Tharimpepe Community Hall',
       'Saturday meal distribution.'
WHERE NOT EXISTS (
    SELECT 1 FROM MealSession
    WHERE SessionDate = '2026-09-05'
      AND SessionType = 'Lunch'
      AND Location = 'Tharimpepe Community Hall'
);

INSERT INTO MealSession (SessionDate, SessionType, Location, Notes)
SELECT '2026-09-08', 'Lunch', 'Tharimpepe Community Hall',
       'Regular feeding programme session.'
WHERE NOT EXISTS (
    SELECT 1 FROM MealSession
    WHERE SessionDate = '2026-09-08'
      AND SessionType = 'Lunch'
      AND Location = 'Tharimpepe Community Hall'
);

-- ------------------------------------------------------------
-- 5. ATTENDANCE
-- ------------------------------------------------------------
INSERT INTO Attendance
    (BeneficiaryID, MealSessionID, SessionDate, Status, Notes)
SELECT b.BeneficiaryID, m.MealSessionID, m.SessionDate, 'present',
       'Attended scheduled meal session.'
FROM Beneficiaries b
JOIN MealSession m
  ON m.SessionDate = '2026-09-01'
 AND m.SessionType = 'Lunch'
 AND m.Location = 'Tharimpepe Community Hall'
WHERE b.Phone = '0721000001'
  AND NOT EXISTS (
      SELECT 1 FROM Attendance a
      WHERE a.BeneficiaryID = b.BeneficiaryID
        AND a.MealSessionID = m.MealSessionID
  );

INSERT INTO Attendance
    (BeneficiaryID, MealSessionID, SessionDate, Status, Notes)
SELECT b.BeneficiaryID, m.MealSessionID, m.SessionDate, 'present',
       'Attended scheduled meal session.'
FROM Beneficiaries b
JOIN MealSession m
  ON m.SessionDate = '2026-09-01'
 AND m.SessionType = 'Lunch'
 AND m.Location = 'Tharimpepe Community Hall'
WHERE b.Phone = '0721000002'
  AND NOT EXISTS (
      SELECT 1 FROM Attendance a
      WHERE a.BeneficiaryID = b.BeneficiaryID
        AND a.MealSessionID = m.MealSessionID
  );

INSERT INTO Attendance
    (BeneficiaryID, MealSessionID, SessionDate, Status, Notes)
SELECT b.BeneficiaryID, m.MealSessionID, m.SessionDate, 'present',
       'Attended scheduled meal session.'
FROM Beneficiaries b
JOIN MealSession m
  ON m.SessionDate = '2026-09-01'
 AND m.SessionType = 'Lunch'
 AND m.Location = 'Tharimpepe Community Hall'
WHERE b.Phone = '0721000003'
  AND NOT EXISTS (
      SELECT 1 FROM Attendance a
      WHERE a.BeneficiaryID = b.BeneficiaryID
        AND a.MealSessionID = m.MealSessionID
  );

INSERT INTO Attendance
    (BeneficiaryID, MealSessionID, SessionDate, Status, Notes)
SELECT b.BeneficiaryID, m.MealSessionID, m.SessionDate, 'absent',
       'Did not attend this session.'
FROM Beneficiaries b
JOIN MealSession m
  ON m.SessionDate = '2026-09-01'
 AND m.SessionType = 'Lunch'
 AND m.Location = 'Tharimpepe Community Hall'
WHERE b.Phone = '0721000004'
  AND NOT EXISTS (
      SELECT 1 FROM Attendance a
      WHERE a.BeneficiaryID = b.BeneficiaryID
        AND a.MealSessionID = m.MealSessionID
  );

INSERT INTO Attendance
    (BeneficiaryID, MealSessionID, SessionDate, Status, Notes)
SELECT b.BeneficiaryID, m.MealSessionID, m.SessionDate, 'present',
       'Attended Saturday meal distribution.'
FROM Beneficiaries b
JOIN MealSession m
  ON m.SessionDate = '2026-09-05'
 AND m.SessionType = 'Lunch'
 AND m.Location = 'Tharimpepe Community Hall'
WHERE b.Phone = '0721000005'
  AND NOT EXISTS (
      SELECT 1 FROM Attendance a
      WHERE a.BeneficiaryID = b.BeneficiaryID
        AND a.MealSessionID = m.MealSessionID
  );

-- ------------------------------------------------------------
-- 6. DONATIONS
-- ------------------------------------------------------------
INSERT INTO Donations
    (DonorName, DonorEmail, DonationType, Amount, Description, DonationDate)
SELECT 'Tharimpepe Community Support Group',
       'support@tharimpepe.example.local',
       'cash', 1500.00,
       'Cash contribution towards meal programme supplies.',
       '2026-08-25'
WHERE NOT EXISTS (
    SELECT 1 FROM Donations
    WHERE DonorEmail = 'support@tharimpepe.example.local'
      AND DonationDate = '2026-08-25'
      AND Amount = 1500.00
);

INSERT INTO Donations
    (DonorName, DonorEmail, DonationType, Amount, Description, DonationDate)
SELECT 'Local Grocery Partners',
       'grocery@partners.example.local',
       'food', 0.00,
       'Rice, maize meal and canned food donation.',
       '2026-08-28'
WHERE NOT EXISTS (
    SELECT 1 FROM Donations
    WHERE DonorEmail = 'grocery@partners.example.local'
      AND DonationDate = '2026-08-28'
      AND Description = 'Rice, maize meal and canned food donation.'
);

INSERT INTO Donations
    (DonorName, DonorEmail, DonationType, Amount, Description, DonationDate)
SELECT 'Community Youth Initiative',
       'youth@initiative.example.local',
       'supplies', 0.00,
       'Disposable containers and serving supplies.',
       '2026-09-02'
WHERE NOT EXISTS (
    SELECT 1 FROM Donations
    WHERE DonorEmail = 'youth@initiative.example.local'
      AND DonationDate = '2026-09-02'
      AND Description = 'Disposable containers and serving supplies.'
);

INSERT INTO Donations
    (DonorName, DonorEmail, DonationType, Amount, Description, DonationDate)
SELECT 'Anonymous Community Donor',
       NULL,
       'cash', 750.00,
       'General feeding scheme contribution.',
       '2026-09-04'
WHERE NOT EXISTS (
    SELECT 1 FROM Donations
    WHERE DonorName = 'Anonymous Community Donor'
      AND DonationDate = '2026-09-04'
      AND Amount = 750.00
);

-- ------------------------------------------------------------
-- 7. FOOD STOCK
-- ------------------------------------------------------------
INSERT INTO FoodStock
    (ItemName, Quantity, Unit, ExpiryDate, StockDate, Notes)
SELECT 'Maize Meal', 50, 'kg', '2027-03-31', '2026-08-20',
       'Dry goods for meal preparation.'
WHERE NOT EXISTS (
    SELECT 1 FROM FoodStock
    WHERE ItemName = 'Maize Meal' AND StockDate = '2026-08-20'
);

INSERT INTO FoodStock
    (ItemName, Quantity, Unit, ExpiryDate, StockDate, Notes)
SELECT 'Rice', 35, 'kg', '2027-02-28', '2026-08-22',
       'Stored in dry food area.'
WHERE NOT EXISTS (
    SELECT 1 FROM FoodStock
    WHERE ItemName = 'Rice' AND StockDate = '2026-08-22'
);

INSERT INTO FoodStock
    (ItemName, Quantity, Unit, ExpiryDate, StockDate, Notes)
SELECT 'Canned Beans', 60, 'cans', '2028-01-31', '2026-08-25',
       'Shelf-stable protein source.'
WHERE NOT EXISTS (
    SELECT 1 FROM FoodStock
    WHERE ItemName = 'Canned Beans' AND StockDate = '2026-08-25'
);

INSERT INTO FoodStock
    (ItemName, Quantity, Unit, ExpiryDate, StockDate, Notes)
SELECT 'Cooking Oil', 20, 'litres', '2027-06-30', '2026-08-27',
       'Used for community meal preparation.'
WHERE NOT EXISTS (
    SELECT 1 FROM FoodStock
    WHERE ItemName = 'Cooking Oil' AND StockDate = '2026-08-27'
);

INSERT INTO FoodStock
    (ItemName, Quantity, Unit, ExpiryDate, StockDate, Notes)
SELECT 'Canned Vegetables', 45, 'cans', '2028-02-29', '2026-08-29',
       'Vegetable stock for meal preparation.'
WHERE NOT EXISTS (
    SELECT 1 FROM FoodStock
    WHERE ItemName = 'Canned Vegetables' AND StockDate = '2026-08-29'
);

-- ------------------------------------------------------------
-- 8. VOLUNTEER SCHEDULES
-- ------------------------------------------------------------
INSERT INTO VolunteerSchedules
    (VolunteerID, ScheduleDate, StartTime, EndTime, Location, Role,
     Status, HoursWorked, Notes)
SELECT v.VolunteerID, '2026-09-01', '08:00:00', '12:00:00',
       'Tharimpepe Community Hall', 'Meal Preparation',
       'completed', 4.00, 'Prepared and assisted with lunch service.'
FROM Volunteers v
JOIN Users u ON u.UserID = v.UserID
WHERE u.Username = 'demo_volunteer1'
  AND NOT EXISTS (
      SELECT 1 FROM VolunteerSchedules s
      WHERE s.VolunteerID = v.VolunteerID
        AND s.ScheduleDate = '2026-09-01'
        AND s.StartTime = '08:00:00'
  );

INSERT INTO VolunteerSchedules
    (VolunteerID, ScheduleDate, StartTime, EndTime, Location, Role,
     Status, HoursWorked, Notes)
SELECT v.VolunteerID, '2026-09-03', '09:00:00', '13:00:00',
       'Tharimpepe Community Hall', 'Meal Distribution',
       'completed', 4.00, 'Assisted with beneficiary meal distribution.'
FROM Volunteers v
JOIN Users u ON u.UserID = v.UserID
WHERE u.Username = 'demo_volunteer2'
  AND NOT EXISTS (
      SELECT 1 FROM VolunteerSchedules s
      WHERE s.VolunteerID = v.VolunteerID
        AND s.ScheduleDate = '2026-09-03'
        AND s.StartTime = '09:00:00'
  );

INSERT INTO VolunteerSchedules
    (VolunteerID, ScheduleDate, StartTime, EndTime, Location, Role,
     Status, HoursWorked, Notes)
SELECT v.VolunteerID, '2026-09-05', '08:30:00', '12:30:00',
       'Tharimpepe Community Hall', 'Serving',
       'scheduled', 0.00, 'Scheduled for Saturday meal service.'
FROM Volunteers v
JOIN Users u ON u.UserID = v.UserID
WHERE u.Username = 'demo_volunteer1'
  AND NOT EXISTS (
      SELECT 1 FROM VolunteerSchedules s
      WHERE s.VolunteerID = v.VolunteerID
        AND s.ScheduleDate = '2026-09-05'
        AND s.StartTime = '08:30:00'
  );

INSERT INTO VolunteerSchedules
    (VolunteerID, ScheduleDate, StartTime, EndTime, Location, Role,
     Status, HoursWorked, Notes)
SELECT v.VolunteerID, '2026-09-08', '09:00:00', '13:00:00',
       'Tharimpepe Community Hall', 'Stock Assistance',
       'scheduled', 0.00, 'Scheduled to assist with stock preparation.'
FROM Volunteers v
JOIN Users u ON u.UserID = v.UserID
WHERE u.Username = 'demo_volunteer2'
  AND NOT EXISTS (
      SELECT 1 FROM VolunteerSchedules s
      WHERE s.VolunteerID = v.VolunteerID
        AND s.ScheduleDate = '2026-09-08'
        AND s.StartTime = '09:00:00'
  );

-- ------------------------------------------------------------
-- The current database does not contain a VolunteerAvailability table,
-- so availability is represented by Volunteers.AvailabilityStatus only.

-- ------------------------------------------------------------
-- 10. MESSAGES
-- ------------------------------------------------------------
INSERT INTO Messages
    (SenderID, RecipientID, Subject, Content, IsRead)
SELECT s.UserID, r.UserID,
       'Welcome to the FSMS demo',
       'Welcome to the Tharimpepe Feeding Scheme Management System demonstration environment.',
       0
FROM Users s
JOIN Users r
  ON r.Username = 'demo_volunteer1'
WHERE s.Username = 'demo_admin'
  AND NOT EXISTS (
      SELECT 1 FROM Messages m
      WHERE m.SenderID = s.UserID
        AND m.RecipientID = r.UserID
        AND m.Subject = 'Welcome to the FSMS demo'
  );

INSERT INTO Messages
    (SenderID, RecipientID, Subject, Content, IsRead)
SELECT s.UserID, r.UserID,
       'Saturday meal session',
       'Please review the Saturday volunteer schedule before the meal session.',
       0
FROM Users s
JOIN Users r
  ON r.Username = 'demo_volunteer2'
WHERE s.Username = 'demo_admin'
  AND NOT EXISTS (
      SELECT 1 FROM Messages m
      WHERE m.SenderID = s.UserID
        AND m.RecipientID = r.UserID
        AND m.Subject = 'Saturday meal session'
  );

INSERT INTO Messages
    (SenderID, RecipientID, Subject, Content, IsRead)
SELECT s.UserID, r.UserID,
       'Stock update',
       'The latest food stock entries have been recorded for the demo data.',
       1
FROM Users s
JOIN Users r
  ON r.Username = 'demo_staff'
WHERE s.Username = 'demo_admin'
  AND NOT EXISTS (
      SELECT 1 FROM Messages m
      WHERE m.SenderID = s.UserID
        AND m.RecipientID = r.UserID
        AND m.Subject = 'Stock update'
  );

-- ------------------------------------------------------------
-- 11. BLOG POSTS
-- ------------------------------------------------------------
INSERT INTO BlogPosts
    (Title, Content, AuthorID, PublishDate)
SELECT 'Tharimpepe Feeding Scheme Community Update',
       'The feeding scheme continues to coordinate meal services, volunteers and food resources for the community.',
       u.UserID, '2026-08-30'
FROM Users u
WHERE u.Username = 'demo_admin'
  AND NOT EXISTS (
      SELECT 1 FROM BlogPosts b
      WHERE b.Title = 'Tharimpepe Feeding Scheme Community Update'
  );

INSERT INTO BlogPosts
    (Title, Content, AuthorID, PublishDate)
SELECT 'Volunteer Programme Update',
       'Volunteer schedules are being coordinated to support meal preparation and distribution activities.',
       u.UserID, '2026-09-02'
FROM Users u
WHERE u.Username = 'demo_admin'
  AND NOT EXISTS (
      SELECT 1 FROM BlogPosts b
      WHERE b.Title = 'Volunteer Programme Update'
  );

INSERT INTO BlogPosts
    (Title, Content, AuthorID, PublishDate)
SELECT 'Community Food Support',
       'Recent donations have helped maintain food and serving supplies for upcoming meal sessions.',
       u.UserID, '2026-09-06'
FROM Users u
WHERE u.Username = 'demo_admin'
  AND NOT EXISTS (
      SELECT 1 FROM BlogPosts b
      WHERE b.Title = 'Community Food Support'
  );

-- ------------------------------------------------------------
-- 12. GALLERY
-- ------------------------------------------------------------
INSERT INTO Gallery
    (ImagePath, Title, Description, UploadedBy)
SELECT 'assets/images/community/meal-service-01.jpg',
       'Community Meal Service',
       'Demonstration gallery entry for a community meal service.',
       u.UserID
FROM Users u
WHERE u.Username = 'demo_admin'
  AND NOT EXISTS (
      SELECT 1 FROM Gallery g
      WHERE g.ImagePath = 'assets/images/community/meal-service-01.jpg'
  );

INSERT INTO Gallery
    (ImagePath, Title, Description, UploadedBy)
SELECT 'assets/images/community/food-preparation-01.jpg',
       'Food Preparation',
       'Demonstration gallery entry showing food preparation activities.',
       u.UserID
FROM Users u
WHERE u.Username = 'demo_admin'
  AND NOT EXISTS (
      SELECT 1 FROM Gallery g
      WHERE g.ImagePath = 'assets/images/community/food-preparation-01.jpg'
  );

INSERT INTO Gallery
    (ImagePath, Title, Description, UploadedBy)
SELECT 'assets/images/community/volunteer-team-01.jpg',
       'Volunteer Team',
       'Demonstration gallery entry for volunteer activities.',
       u.UserID
FROM Users u
WHERE u.Username = 'demo_admin'
  AND NOT EXISTS (
      SELECT 1 FROM Gallery g
      WHERE g.ImagePath = 'assets/images/community/volunteer-team-01.jpg'
  );

-- ------------------------------------------------------------
-- 13. ACTIVITY LOG
-- ------------------------------------------------------------
INSERT INTO ActivityLog
    (UserID, Action, AffectedEntityName, AffectedEntityID, Details)
SELECT u.UserID, 'Created', 'Beneficiaries', b.BeneficiaryID,
       'Registered beneficiary for the feeding scheme.'
FROM Users u
JOIN Beneficiaries b ON b.Phone = '0721000001'
WHERE u.Username = 'demo_admin'
  AND NOT EXISTS (
      SELECT 1 FROM ActivityLog a
      WHERE a.UserID = u.UserID
        AND a.Action = 'Created'
        AND a.AffectedEntityName = 'Beneficiaries'
        AND a.AffectedEntityID = b.BeneficiaryID
        AND a.Details = 'Registered beneficiary for the feeding scheme.'
  );

INSERT INTO ActivityLog
    (UserID, Action, AffectedEntityName, AffectedEntityID, Details)
SELECT u.UserID, 'Created', 'FoodStock', f.FoodStockID,
       'Added food stock item for demonstration.'
FROM Users u
JOIN FoodStock f
  ON f.ItemName = 'Maize Meal'
 AND f.StockDate = '2026-08-20'
WHERE u.Username = 'demo_admin'
  AND NOT EXISTS (
      SELECT 1 FROM ActivityLog a
      WHERE a.UserID = u.UserID
        AND a.Action = 'Created'
        AND a.AffectedEntityName = 'FoodStock'
        AND a.AffectedEntityID = f.FoodStockID
        AND a.Details = 'Added food stock item for demonstration.'
  );

INSERT INTO ActivityLog
    (UserID, Action, AffectedEntityName, AffectedEntityID, Details)
SELECT u.UserID, 'Created', 'Donations', d.DonationID,
       'Recorded community donation for demonstration.'
FROM Users u
JOIN Donations d
  ON d.DonorEmail = 'support@tharimpepe.example.local'
 AND d.DonationDate = '2026-08-25'
WHERE u.Username = 'demo_admin'
  AND NOT EXISTS (
      SELECT 1 FROM ActivityLog a
      WHERE a.UserID = u.UserID
        AND a.Action = 'Created'
        AND a.AffectedEntityName = 'Donations'
        AND a.AffectedEntityID = d.DonationID
        AND a.Details = 'Recorded community donation for demonstration.'
  );

COMMIT;

-- ============================================================
-- END OF SEED DATA
-- ============================================================
