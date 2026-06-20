-- ==============================================================
-- Module: FSMS Test Data Seed
-- Purpose: Realistic test datasets for final system testing
-- Entities: Users, Beneficiaries, Volunteers, Attendance,
--           Donations, Food Stock, Meal Sessions
-- ==============================================================

USE fsms;

-- ==============================================================
-- USERS (Admin, Staff, Volunteers, Donors)
-- ==============================================================
INSERT IGNORE INTO Users (Username, Email, PasswordHash, FullName, Phone, Role, Status, CreatedAt) VALUES
('admin', 'admin@tharimpepe.org.za', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Thandiwe Mokoena', '082-555-0101', 'admin', 'active', '2024-01-01 08:00:00'),
('coordinator', 'coordinator@tharimpepe.org.za', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Sipho Nkosi', '083-555-0102', 'staff', 'active', '2024-01-05 09:00:00'),
('volunteer1', 'volunteer1@tharimpepe.org.za', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Nompumelelo Dlamini', '084-555-0103', 'volunteer', 'active', '2024-02-10 10:00:00'),
('volunteer2', 'volunteer2@tharimpepe.org.za', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Thabo Molefe', '082-555-0104', 'volunteer', 'active', '2024-02-15 11:00:00'),
('volunteer3', 'volunteer3@tharimpepe.org.za', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Lerato Khumalo', '079-555-0105', 'volunteer', 'active', '2024-03-01 08:30:00'),
('donor1', 'donor1@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Johann van der Merwe', '082-333-0101', 'donor', 'active', '2024-01-20 14:00:00'),
('donor2', 'donor2@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Fatima Patel', '083-444-0102', 'donor', 'active', '2024-02-05 16:00:00'),
('donor3', 'donor3@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Community Church Group', '071-222-0103', 'donor', 'active', '2024-03-10 12:00:00');

-- ==============================================================
-- VOLUNTEERS (linked to volunteer users)
-- ==============================================================
INSERT IGNORE INTO Volunteers (UserID, Skills, AvailabilityStatus, Address, Notes, Status, ApprovedBy, ApprovedAt, CreatedAt) VALUES
(3, 'Food preparation, kitchen hygiene, customer service', 'available', '12 Mandela St, Soweto, 1804', 'Certified food handler', 'approved', 1, '2024-02-12 09:00:00', '2024-02-10 10:00:00'),
(4, 'Driving, logistics, stock replenishment', 'available', '45 Vilakazi St, Soweto, 1804', 'Has own vehicle for deliveries', 'approved', 1, '2024-02-17 11:00:00', '2024-02-15 11:00:00'),
(5, 'Childcare, registration, admin', 'on_leave', '78 Moletsane St, Soweto, 1868', 'Currently on medical leave', 'approved', 1, '2024-03-05 10:00:00', '2024-03-01 08:30:00');

-- ==============================================================
-- BENEFICIARIES (50 realistic records)
-- ==============================================================
INSERT IGNORE INTO Beneficiaries (FirstName, LastName, Age, Gender, Phone, Email, Address, RegistrationDate, Status, Notes, CreatedBy, CreatedAt) VALUES
('Abongile', 'Nkosi', 8, 'Male', '073-111-0001', NULL, 'Section A, Soweto', '2024-01-15', 'active', 'Student at local primary school', 2, '2024-01-15 08:00:00'),
('Bathabile', 'Mthembu', 34, 'Female', '073-111-0002', 'bathabile.m@example.com', 'Section B, Soweto', '2024-01-16', 'active', 'Single mother of 3', 2, '2024-01-16 08:30:00'),
('Chris', 'Naidoo', 67, 'Male', '073-111-0003', NULL, 'Section C, Soweto', '2024-01-17', 'active', 'Elderly, requires assistance', 2, '2024-01-17 09:00:00'),
('Dineo', 'Molefe', 12, 'Female', '073-111-0004', NULL, 'Section D, Soweto', '2024-01-18', 'active', 'Orphan, living with grandmother', 2, '2024-01-18 10:00:00'),
('Elias', 'Khumalo', 45, 'Male', '073-111-0005', 'elias.k@example.com', 'Section E, Soweto', '2024-01-19', 'active', 'Unemployed', 2, '2024-01-19 11:00:00'),
('Fikile', 'Dlamini', 28, 'Female', '073-111-0006', NULL, 'Section F, Soweto', '2024-01-20', 'active', 'Domestic worker', 2, '2024-01-20 09:30:00'),
('Gift', 'Mokoena', 52, 'Male', '073-111-0007', NULL, 'Section G, Soweto', '2024-01-21', 'active', 'Disabled, wheelchair bound', 2, '2024-01-21 10:00:00'),
('Hlengiwe', 'Buthelezi', 23, 'Female', '073-111-0008', 'hlengiwe.b@example.com', 'Section H, Soweto', '2024-01-22', 'active', 'Student', 2, '2024-01-22 11:30:00'),
('Isaac', 'Mahlangu', 71, 'Male', '073-111-0009', NULL, 'Section I, Soweto', '2024-01-23', 'active', 'Retired, pensioner', 2, '2024-01-23 08:00:00'),
('Jabulile', 'Ndlovu', 39, 'Female', '073-111-0010', NULL, 'Section J, Soweto', '2024-01-24', 'active', 'Hawker', 2, '2024-01-24 09:00:00'),
('Kagiso', 'Molefe', 6, 'Male', '073-111-0011', NULL, 'Section K, Soweto', '2024-01-25', 'active', 'Toddler', 2, '2024-01-25 10:30:00'),
('Lindiwe', 'Mthembu', 31, 'Female', '073-111-0012', 'lindiwe.m@example.com', 'Section L, Soweto', '2024-01-26', 'active', 'Nurse', 2, '2024-01-26 11:00:00'),
('Mandla', 'Khumalo', 58, 'Male', '073-111-0013', NULL, 'Section M, Soweto', '2024-01-27', 'active', 'Former miner, now unemployed', 2, '2024-01-27 08:30:00'),
('Nokuthula', 'Nkosi', 17, 'Female', '073-111-0014', NULL, 'Section N, Soweto', '2024-01-28', 'active', 'Matriculant', 2, '2024-01-28 09:30:00'),
('Oscar', 'Mokoena', 42, 'Male', '073-111-0015', 'oscar.m@example.com', 'Section O, Soweto', '2024-01-29', 'active', 'Security guard', 2, '2024-01-29 10:00:00'),
('Palesa', 'Dlamini', 25, 'Female', '073-111-0016', NULL, 'Section P, Soweto', '2024-01-30', 'active', 'Unemployed mother', 2, '2024-01-30 11:00:00'),
('Quinton', 'Naidoo', 63, 'Male', '073-111-0017', NULL, 'Section Q, Soweto', '2024-02-01', 'active', 'Pensioner', 2, '2024-02-01 08:00:00'),
('Refilwe', 'Mthombeni', 9, 'Female', '073-111-0018', NULL, 'Section R, Soweto', '2024-02-02', 'active', 'Primary school learner', 2, '2024-02-02 09:00:00'),
('Sibusiso', 'Ndlovu', 36, 'Male', '073-111-0019', 'sibusiso.n@example.com', 'Section S, Soweto', '2024-02-03', 'active', 'Construction worker', 2, '2024-02-03 10:00:00'),
('Thandi', 'Zulu', 29, 'Female', '073-111-0020', NULL, 'Section T, Soweto', '2024-02-04', 'active', 'Domestic worker', 2, '2024-02-04 11:30:00'),
('Unathi', 'Mhlanga', 14, 'Male', '073-111-0021', NULL, 'Section U, Soweto', '2024-02-05', 'active', 'High school learner', 2, '2024-02-05 08:30:00'),
('Violet', 'Maseko', 76, 'Female', '073-111-0022', NULL, 'Section V, Soweto', '2024-02-06', 'active', 'Elderly, lives alone', 2, '2024-02-06 09:00:00'),
('Wandile', 'Cele', 48, 'Male', '073-111-0023', 'wandile.c@example.com', 'Section W, Soweto', '2024-02-07', 'active', 'Taxi driver', 2, '2024-02-07 10:00:00'),
('Xoliswa', 'Nkosi', 33, 'Female', '073-111-0024', NULL, 'Section X, Soweto', '2024-02-08', 'active', 'Teacher', 2, '2024-02-08 11:00:00'),
('Yusuf', 'Ahmed', 21, 'Male', '073-111-0025', 'yusuf.a@example.com', 'Section Y, Soweto', '2024-02-09', 'active', 'Student', 2, '2024-02-09 08:00:00'),
('Zodwa', 'Mthembu', 55, 'Female', '073-111-0026', NULL, 'Section Z, Soweto', '2024-02-10', 'active', 'Domestic worker', 2, '2024-02-10 09:30:00'),
('Andile', 'Khumalo', 10, 'Male', '073-111-0027', NULL, 'Zone 1, Soweto', '2024-02-11', 'active', 'Primary learner', 2, '2024-02-11 10:00:00'),
('Bongi', 'Ndlovu', 27, 'Female', '073-111-0028', 'bongi.n@example.com', 'Zone 2, Soweto', '2024-02-12', 'active', 'Administrative clerk', 2, '2024-02-12 11:00:00'),
('Cyril', 'Mokoena', 69, 'Male', '073-111-0029', NULL, 'Zone 3, Soweto', '2024-02-13', 'active', 'Retired', 2, '2024-02-13 08:30:00'),
('Duduzile', 'Molefe', 38, 'Female', '073-111-0030', NULL, 'Zone 4, Soweto', '2024-02-14', 'active', 'Single parent', 2, '2024-02-14 09:00:00'),
('Eric', 'Nkosi', 44, 'Male', '073-111-0031', 'eric.n@example.com', 'Zone 5, Soweto', '2024-02-15', 'active', 'Mechanic', 2, '2024-02-15 10:30:00'),
('Funeka', 'Mthembu', 16, 'Female', '073-111-0032', NULL, 'Zone 6, Soweto', '2024-02-16', 'active', 'Matriculant', 2, '2024-02-16 11:00:00'),
('George', 'Dlamini', 61, 'Male', '073-111-0033', NULL, 'Zone 7, Soweto', '2024-02-17', 'active', 'Pensioner', 2, '2024-02-17 08:00:00'),
('Hannah', 'Khumalo', 5, 'Female', '073-111-0034', NULL, 'Zone 8, Soweto', '2024-02-18', 'active', 'Toddler', 2, '2024-02-18 09:30:00'),
('Jacob', 'Mthombeni', 51, 'Male', '073-111-0035', 'jacob.m@example.com', 'Zone 9, Soweto', '2024-02-19', 'active', 'Former miner', 2, '2024-02-19 10:00:00'),
('Kgomotso', 'Ndlovu', 40, 'Female', '073-111-0036', NULL, 'Zone 10, Soweto', '2024-02-20', 'active', 'Domestic worker', 2, '2024-02-20 11:30:00'),
('Lucky', 'Mokoena', 13, 'Male', '073-111-0037', NULL, 'Zone 11, Soweto', '2024-02-21', 'active', 'High school learner', 2, '2024-02-21 08:30:00'),
('Martha', 'Cele', 82, 'Female', '073-111-0038', NULL, 'Zone 12, Soweto', '2024-02-22', 'active', 'Elderly, requires care', 2, '2024-02-22 09:00:00'),
('Nathan', 'Mthembu', 47, 'Male', '073-111-0039', NULL, 'Zone 13, Soweto', '2024-02-23', 'active', 'Security guard', 2, '2024-02-23 10:00:00'),
('Olivia', 'Molefe', 22, 'Female', '073-111-0040', 'olivia.m@example.com', 'Zone 14, Soweto', '2024-02-24', 'active', 'Student nurse', 2, '2024-02-24 11:00:00'),
('Peter', 'Nkosi', 59, 'Male', '073-111-0041', NULL, 'Zone 15, Soweto', '2024-02-25', 'active', 'Unemployed', 2, '2024-02-25 08:00:00'),
('Queen', 'Dlamini', 26, 'Female', '073-111-0042', NULL, 'Zone 16, Soweto', '2024-02-26', 'active', 'Hairdresser', 2, '2024-02-26 09:30:00'),
('Ronald', 'Khumalo', 35, 'Male', '073-111-0043', 'ronald.k@example.com', 'Zone 17, Soweto', '2024-02-27', 'active', 'Carpenter', 2, '2024-02-27 10:00:00'),
('Sarah', 'Mthombeni', 49, 'Female', '073-111-0044', NULL, 'Zone 18, Soweto', '2024-02-28', 'active', 'Domestic worker', 2, '2024-02-28 11:00:00'),
('Sipho', 'Zulu', 7, 'Male', '073-111-0045', NULL, 'Zone 19, Soweto', '2024-03-01', 'active', 'Primary learner', 2, '2024-03-01 08:30:00'),
('Thembi', 'Mokoena', 32, 'Female', '073-111-0046', 'thembi.m@example.com', 'Zone 20, Soweto', '2024-03-02', 'active', 'Teacher', 2, '2024-03-02 09:00:00'),
('Victor', 'Mthembu', 65, 'Male', '073-111-0047', NULL, 'Zone 21, Soweto', '2024-03-03', 'active', 'Pensioner', 2, '2024-03-03 10:00:00'),
('Wendy', 'Molefe', 19, 'Female', '073-111-0048', NULL, 'Zone 22, Soweto', '2024-03-04', 'active', 'Student', 2, '2024-03-04 11:30:00'),
('Alex', 'Ndlovu', 50, 'Male', '073-111-0049', NULL, 'Zone 23, Soweto', '2024-03-05', 'active', 'Miner', 2, '2024-03-05 08:00:00'),
('Brenda', 'Mokoena', 41, 'Female', '073-111-0050', 'brenda.m@example.com', 'Zone 24, Soweto', '2024-03-06', 'inactive', 'Moved to another province', 2, '2024-03-06 09:00:00');

-- ==============================================================
-- MEAL SESSIONS (Past 30 days + upcoming)
-- ==============================================================
INSERT IGNORE INTO MealSession (SessionDate, SessionType, Location, Notes, CreatedAt) VALUES
('2024-05-01', 'Breakfast', 'Main Hall', 'Monthly community breakfast', '2024-04-30 08:00:00'),
('2024-05-02', 'Lunch', 'Main Hall', 'Regular lunch service', '2024-05-01 08:00:00'),
('2024-05-03', 'Dinner', 'Main Hall', 'Evening meal distribution', '2024-05-02 08:00:00'),
('2024-05-06', 'Breakfast', 'Main Hall', 'Weekend breakfast', '2024-05-05 08:00:00'),
('2024-05-07', 'Lunch', 'Main Hall', 'Regular lunch service', '2024-05-06 08:00:00'),
('2024-05-08', 'Dinner', 'Main Hall', 'Evening meal', '2024-05-07 08:00:00'),
('2024-05-13', 'Breakfast', 'Main Hall', NULL, '2024-05-12 08:00:00'),
('2024-05-14', 'Lunch', 'Main Hall', 'Special dietary options', '2024-05-13 08:00:00'),
('2024-05-15', 'Dinner', 'Main Hall', NULL, '2024-05-14 08:00:00'),
('2024-05-20', 'Breakfast', 'Main Hall', 'Winter relief breakfast', '2024-05-19 08:00:00'),
('2024-05-21', 'Lunch', 'Main Hall', NULL, '2024-05-20 08:00:00'),
('2024-05-22', 'Dinner', 'Main Hall', 'Community dinner', '2024-05-21 08:00:00'),
('2024-05-27', 'Breakfast', 'Main Hall', NULL, '2024-05-26 08:00:00'),
('2024-05-28', 'Lunch', 'Main Hall', 'Regular service', '2024-05-27 08:00:00'),
('2024-05-29', 'Dinner', 'Main Hall', NULL, '2024-05-28 08:00:00');

-- ==============================================================
-- ATTENDANCE (For past meal sessions)
-- ==============================================================
INSERT IGNORE INTO Attendance (BeneficiaryID, MealSessionID, SessionDate, Status, Notes, CreatedAt) VALUES
-- 2024-05-01 Breakfast (1)
(1, 1, '2024-05-01', 'present', NULL, '2024-05-01 08:00:00'),
(2, 1, '2024-05-01', 'present', NULL, '2024-05-01 08:00:00'),
(3, 1, '2024-05-01', 'present', NULL, '2024-05-01 08:00:00'),
(4, 1, '2024-05-01', 'absent', 'Sick', '2024-05-01 08:00:00'),
(5, 1, '2024-05-01', 'present', NULL, '2024-05-01 08:00:00'),
(6, 1, '2024-05-01', 'present', NULL, '2024-05-01 08:00:00'),
(7, 1, '2024-05-01', 'absent', 'No transport', '2024-05-01 08:00:00'),
(8, 1, '2024-05-01', 'present', NULL, '2024-05-01 08:00:00'),
(9, 1, '2024-05-01', 'present', NULL, '2024-05-01 08:00:00'),
(10, 1, '2024-05-01', 'present', NULL, '2024-05-01 08:00:00'),
-- 2024-05-02 Lunch (2)
(1, 2, '2024-05-02', 'present', NULL, '2024-05-02 12:00:00'),
(2, 2, '2024-05-02', 'present', NULL, '2024-05-02 12:00:00'),
(3, 2, '2024-05-02', 'present', NULL, '2024-05-02 12:00:00'),
(4, 2, '2024-05-02', 'present', 'Returned', '2024-05-02 12:00:00'),
(5, 2, '2024-05-02', 'absent', 'Job interview', '2024-05-02 12:00:00'),
(6, 2, '2024-05-02', 'present', NULL, '2024-05-02 12:00:00'),
(7, 2, '2024-05-02', 'present', NULL, '2024-05-02 12:00:00'),
(8, 2, '2024-05-02', 'present', NULL, '2024-05-02 12:00:00'),
(9, 2, '2024-05-02', 'absent', 'Visiting family', '2024-05-02 12:00:00'),
(10, 2, '2024-05-02', 'present', NULL, '2024-05-02 12:00:00'),
(11, 2, '2024-05-02', 'present', NULL, '2024-05-02 12:00:00'),
(12, 2, '2024-05-02', 'present', NULL, '2024-05-02 12:00:00');

-- ==============================================================
-- DONATIONS (Cash, Food, Supplies)
-- ==============================================================
INSERT IGNORE INTO Donations (UserID, DonorName, DonorEmail, DonationType, Amount, Description, PaymentMethod, TransactionReference, Status, DonationDate, CreatedAt) VALUES
(6, 'Johann van der Merwe', 'johann@example.com', 'cash', 5000.00, 'Monthly cash donation for meal program', 'EFT', 'TXN-2024-0501-001', 'completed', '2024-05-01', '2024-05-01 14:00:00'),
(7, 'Fatima Patel', 'fatima@example.com', 'cash', 2500.00, 'Donation for winter blankets', 'Card', 'TXN-2024-0505-002', 'completed', '2024-05-05', '2024-05-05 10:00:00'),
(8, 'Community Church Group', NULL, 'food', 0.00, '50kg maize meal, 20kg rice, 10L cooking oil', 'In-kind', 'IK-2024-0510-001', 'completed', '2024-05-10', '2024-05-10 08:30:00'),
(6, 'Johann van der Merwe', 'johann@example.com', 'supplies', 1500.00, 'Plates, spoons, cups for 200 people', 'EFT', 'TXN-2024-0515-003', 'completed', '2024-05-15', '2024-05-15 16:00:00'),
(7, 'Fatima Patel', 'fatima@example.com', 'cash', 1000.00, 'General donation', 'Cash', 'TXN-2024-0520-004', 'completed', '2024-05-20', '2024-05-20 09:00:00'),
(NULL, 'Anonymous Donor', NULL, 'cash', 300.00, 'Anonymous cash donation', 'Cash', 'TXN-2024-0525-005', 'completed', '2024-05-25', '2024-05-25 11:00:00'),
(8, 'Community Church Group', NULL, 'food', 0.00, 'Canned vegetables and fruits (50 tins)', 'In-kind', 'IK-2024-0528-006', 'completed', '2024-05-28', '2024-05-28 13:00:00'),
(6, 'Johann van der Merwe', 'johann@example.com', 'cash', 750.00, 'Childrens day celebration fund', 'EFT', 'TXN-2024-0601-007', 'completed', '2024-06-01', '2024-06-01 10:00:00'),
(7, 'Fatima Patel', 'fatima@example.com', 'cash', 2000.00, 'Youth program sponsorship', 'Card', 'TXN-2024-0605-008', 'pending', '2024-06-05', '2024-06-05 14:30:00');

-- ==============================================================
-- FOOD STOCK (Inventory items with quantities)
-- ==============================================================
INSERT IGNORE INTO FoodStock (ItemName, Quantity, Unit, ExpiryDate, StockDate, Notes, CreatedAt) VALUES
('Maize Meal', 150, 'kg', '2025-03-01', '2024-05-01', 'Bulk staple from church donation', '2024-05-01 09:00:00'),
('White Rice', 80, 'kg', '2025-06-15', '2024-05-01', 'Regular stock', '2024-05-01 09:30:00'),
('Cooking Oil', 25, 'litres', '2025-01-20', '2024-05-01', 'Low stock - reorder soon', '2024-05-01 10:00:00'),
('Canned Beans', 120, 'tins', '2026-01-10', '2024-05-01', 'Emergency supply', '2024-05-01 10:30:00'),
('Canned Vegetables', 95, 'tins', '2025-12-01', '2024-05-01', NULL, '2024-05-01 11:00:00'),
('Canned Fruit', 60, 'tins', '2026-02-15', '2024-05-01', NULL, '2024-05-01 11:30:00'),
('Bread Loaves', 0, 'units', NULL, '2024-05-02', 'Donated daily from bakery - not in stock', '2024-05-02 07:00:00'),
('Milk Powder', 10, 'kg', '2024-11-30', '2024-05-01', 'Critical low stock - 4.1% remaining', '2024-05-01 12:00:00'),
('Sugar', 35, 'kg', '2025-08-20', '2024-05-01', NULL, '2024-05-01 12:30:00'),
('Tea', 15, 'boxes', '2025-10-01', '2024-05-01', NULL, '2024-05-01 13:00:00'),
('Coffee', 8, 'jars', '2025-09-01', '2024-05-01', NULL, '2024-05-01 13:30:00'),
('Porridge', 45, 'kg', '2025-05-10', '2024-05-01', NULL, '2024-05-01 14:00:00');

-- ==============================================================
-- VOLUNTEER AVAILABILITY (Recurring weekly schedule)
-- ==============================================================
INSERT IGNORE INTO VolunteerAvailability (VolunteerID, DayOfWeek, IsAvailable, Notes, CreatedAt) VALUES
(1, 'Monday', TRUE, 'Available for breakfast prep', '2024-02-12 09:00:00'),
(1, 'Tuesday', TRUE, NULL, '2024-02-12 09:00:00'),
(1, 'Wednesday', TRUE, NULL, '2024-02-12 09:00:00'),
(1, 'Thursday', TRUE, NULL, '2024-02-12 09:00:00'),
(1, 'Friday', FALSE, 'Permanent Friday off', '2024-02-12 09:00:00'),
(2, 'Monday', TRUE, 'Stock delivery day', '2024-02-17 11:00:00'),
(2, 'Wednesday', TRUE, NULL, '2024-02-17 11:00:00'),
(2, 'Friday', TRUE, NULL, '2024-02-17 11:00:00'),
(3, 'Tuesday', FALSE, 'On leave', '2024-03-05 10:00:00'),
(3, 'Thursday', FALSE, 'On leave', '2024-03-05 10:00:00');

-- ==============================================================
-- VOLUNTEER SCHEDULES (Assignments for upcoming sessions)
-- ==============================================================
INSERT IGNORE INTO VolunteerSchedules (VolunteerID, ScheduleDate, StartTime, EndTime, Role, Location, Status, HoursWorked, Notes, CreatedAt) VALUES
(1, '2024-06-08', '06:00:00', '10:00:00', 'Kitchen Helper', 'Main Hall', 'scheduled', NULL, 'Breakfast and prep for lunch', '2024-06-01 08:00:00'),
(2, '2024-06-08', '08:00:00', '12:00:00', 'Stock Management', 'Storage', 'scheduled', NULL, 'Receive weekly delivery', '2024-06-01 08:30:00'),
(1, '2024-06-09', '11:00:00', '15:00:00', 'Server', 'Main Hall', 'scheduled', NULL, 'Lunch service', '2024-06-01 09:00:00'),
(2, '2024-06-09', '10:00:00', '14:00:00', 'Logistics', 'Main Hall', 'scheduled', NULL, NULL, '2024-06-01 09:30:00'),
(1, '2024-06-10', '17:00:00', '20:00:00', 'Server', 'Main Hall', 'scheduled', NULL, 'Dinner service', '2024-06-01 10:00:00'),
(3, '2024-06-10', '16:00:00', '19:00:00', 'Registration', 'Entrance', 'scheduled', NULL, 'Expected to return from leave', '2024-06-01 10:30:00');

-- ==============================================================
-- FOOD DISTRIBUTIONS (Track stock movements)
-- ==============================================================
INSERT IGNORE INTO FoodDistribution (FoodStockID, QuantityDistributed, DistributionDate, Location, Purpose, Notes, CreatedAt) VALUES
(1, 30, '2024-05-01', 'Main Hall', 'Lunch service - 100 people', '5kg per meal x 6 servings', '2024-05-01 14:00:00'),
(2, 20, '2024-05-01', 'Main Hall', 'Lunch service - 100 people', '400g per meal x 50 servings', '2024-05-01 14:00:00'),
(3, 5, '2024-05-01', 'Main Hall', 'Lunch service cooking', NULL, '2024-05-01 14:00:00'),
(4, 15, '2024-05-01', 'Main Hall', 'Side dish for lunch', NULL, '2024-05-01 14:00:00'),
(1, 25, '2024-05-02', 'Main Hall', 'Dinner service', '5kg per meal x 5 servings', '2024-05-02 16:00:00'),
(5, 20, '2024-05-02', 'Main Hall', 'Side dish for dinner', NULL, '2024-05-02 16:00:00'),
(1, 35, '2024-05-03', 'Main Hall', 'Weekend breakfast', '7kg per meal x 5 servings', '2024-05-03 09:00:00'),
(2, 25, '2024-05-03', 'Main Hall', 'Breakfast rice', NULL, '2024-05-03 09:00:00'),
(6, 10, '2024-05-03', 'Main Hall', 'Dessert for children', NULL, '2024-05-03 09:00:00');

-- ==============================================================
-- PAYMENT TRANSACTIONS (Linked to some donations)
-- ==============================================================
INSERT IGNORE INTO PaymentTransactions (DonationID, UserID, Gateway, GatewayReference, Amount, Currency, Status, ResponseData, CreatedAt, UpdatedAt) VALUES
(1, 6, 'PayFast', 'PF-2024-0501-001', 5000.00, 'ZAR', 'completed', '{"pf_payment_id":"12345","status":"completed"}', '2024-05-01 14:30:00', '2024-05-01 14:30:00'),
(2, 7, 'Yoco', 'YC-2024-0505-001', 2500.00, 'ZAR', 'completed', '{"id":"67890","status":"successful"}', '2024-05-05 10:30:00', '2024-05-05 10:30:00'),
(4, 6, 'PayFast', 'PF-2024-0515-001', 1500.00, 'ZAR', 'completed', '{"pf_payment_id":"12346","status":"completed"}', '2024-05-15 16:30:00', '2024-05-15 16:30:00'),
(5, 7, 'Cash', NULL, 1000.00, 'ZAR', 'completed', '{"method":"cash","received_by":"admin"}', '2024-05-20 09:30:00', '2024-05-20 09:30:00'),
(9, 7, 'Yoco', 'YC-2024-0605-002', 2000.00, 'ZAR', 'pending', '{"id":"pending","status":"awaiting"}', '2024-06-05 15:00:00', '2024-06-05 15:00:00');

-- ==============================================================
-- ACTIVITY LOG (Sample audit entries)
-- ==============================================================
INSERT IGNORE INTO ActivityLog (UserID, Action, AffectedEntityName, AffectedEntityID, Details, IPAddress, Timestamp) VALUES
(1, 'LOGIN', 'Users', 1, 'Admin login from 192.168.1.100', '192.168.1.100', '2024-05-01 08:00:00'),
(2, 'CREATE', 'Beneficiaries', 1, 'Registered new beneficiary: Abongile Nkosi', '192.168.1.10', '2024-05-01 08:15:00'),
(3, 'LOGIN', 'Users', 3, 'Volunteer login from mobile', '192.168.1.200', '2024-05-01 06:45:00'),
(2, 'CREATE', 'Donations', 1, 'Logged cash donation from Johann van der Merwe', '192.168.1.10', '2024-05-01 14:00:00'),
(1, 'UPDATE', 'FoodStock', 8, 'Low stock alert triggered for Milk Powder', '192.168.1.100', '2024-05-01 15:00:00'),
(4, 'LOGIN', 'Users', 4, 'Volunteer login from mobile', '192.168.1.201', '2024-05-02 07:30:00'),
(2, 'CREATE', 'Attendance', 1, 'Marked attendance for 2024-05-01 Breakfast', '192.168.1.10', '2024-05-01 08:30:00'),
(1, 'DELETE', 'MealSession', 99, 'Cancelled session due to maintenance', '192.168.1.100', '2024-05-03 10:00:00');

-- ==============================================================
-- Outreach Programs (Optional - for extended testing)
-- ==============================================================
INSERT IGNORE INTO OutreachPrograms (Title, Description, ProgramDate, Location, Capacity, Status, CreatedBy, CreatedAt) VALUES
('Winter Food Drive', 'Monthly distribution of warm meals and blankets to elderly beneficiaries', '2024-06-15', 'Main Hall', 150, 'planned', 1, '2024-06-01 08:00:00'),
('Youth Skills Workshop', 'Career guidance and skills training for unemployed youth', '2024-06-22', 'Community Centre', 50, 'planned', 2, '2024-06-05 09:00:00'),
('Childrens Day Celebration', 'Special event with games, gifts, and meals for children', '2024-06-01', 'Main Hall', 200, 'completed', 1, '2024-05-25 10:00:00');

-- ==============================================================
-- Program Volunteers (Linking volunteers to outreach programs)
-- ==============================================================
INSERT IGNORE INTO ProgramVolunteers (ProgramID, VolunteerID, Status, AssignedAt, Notes) VALUES
(1, 1, 'assigned', '2024-06-01 09:00:00', 'Catering lead'),
(1, 2, 'assigned', '2024-06-01 09:30:00', 'Logistics and transport'),
(2, 3, 'assigned', '2024-06-05 10:00:00', 'Registration desk'),
(3, 1, 'confirmed', '2024-05-25 11:00:00', 'Activity coordinator'),
(3, 2, 'confirmed', '2024-05-25 11:30:00', 'Photography'),
(3, 3, 'confirmed', '2024-05-25 12:00:00', 'Child minding');

-- ==============================================================
-- Announcements & Blog Posts (For reporting verification)
-- ==============================================================
INSERT IGNORE INTO Announcements (Title, Content, Priority, Status, CreatedBy, PublishDate, ExpiryDate, CreatedAt) VALUES
('System Maintenance Notice', 'The system will be down for maintenance on June 10, 2024 from 2AM to 4AM.', 'normal', 'published', 1, '2024-06-05 08:00:00', '2024-06-11 08:00:00', '2024-06-05 08:00:00'),
('New Donor Welcome', 'We welcome our new donor Johann van der Merwe who donated R5000 this month.', 'normal', 'published', 1, '2024-05-05 09:00:00', '2024-06-05 09:00:00', '2024-05-05 09:00:00'),
('Winter Clothing Drive', 'We are collecting winter clothing and blankets. Please donate at the main hall.', 'high', 'published', 2, '2024-05-20 10:00:00', '2024-06-30 10:00:00', '2024-05-20 10:00:00');

INSERT IGNORE INTO BlogPosts (Title, Content, Excerpt, FeaturedImage, Status, AuthorID, PublishDate, CreatedAt) VALUES
('Summer Feeding Program Success', 'Our summer feeding program has successfully provided 10,000 meals to children in Soweto. Read about the impact we made together.', 'A look back at our successful summer program', '/images/blog/summer-food-program.jpg', 'published', 1, '2024-04-30', '2024-04-30 10:00:00'),
('Volunteer Appreciation Day', 'On April 15th we celebrated our amazing volunteers who donated over 500 hours this quarter.', 'Honoring our dedicated volunteers', '/images/blog/volunteer-day.jpg', 'published', 2, '2024-04-15', '2024-04-15 08:00:00'),
('Community Garden Project Launch', 'We have launched a community garden to grow vegetables for our feeding programs. Join us!', 'Growing hope, one vegetable at a time', '/images/blog/garden-launch.jpg', 'published', 1, '2024-05-01', '2024-05-01 09:00:00');

-- ==============================================================
-- Gallery Images (For reference)
-- ==============================================================
INSERT IGNORE INTO Gallery (ImagePath, Title, Description, UploadedBy, UploadDate) VALUES
('/images/gallery/feeding-day-01.jpg', 'Feeding Day at Main Hall', 'Children enjoying their meals', 1, '2024-05-01 14:30:00'),
('/images/gallery/volunteer-training.jpg', 'Volunteer Training Session', 'New volunteers learning food safety', 2, '2024-02-10 09:00:00'),
('/images/gallery/garden-plot1.jpg', 'Community Garden Plot 1', 'Tomatoes and spinach growing nicely', 1, '2024-05-10 10:00:00');