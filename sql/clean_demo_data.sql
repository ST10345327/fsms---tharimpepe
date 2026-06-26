-- ============================================================
-- Clean Demo Data (preserves admin user only)
-- Usage: mysql -u root fsms < sql/clean_demo_data.sql
-- ============================================================

SET FOREIGN_KEY_CHECKS = 0;

DELETE FROM activitylog;
DELETE FROM auditlogs;
DELETE FROM authtokens;
DELETE FROM volunteerschedules;
DELETE FROM attendance;
DELETE FROM mealsession;
DELETE FROM volunteers;
DELETE FROM beneficiaries;
DELETE FROM donations;
DELETE FROM foodstock;
DELETE FROM messages;
DELETE FROM blogposts;
DELETE FROM gallery;
DELETE FROM users WHERE Role != 'admin';

SET FOREIGN_KEY_CHECKS = 1;
