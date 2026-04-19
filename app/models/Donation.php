<?php
/**
 * File: Donation.php
 * Purpose: Data layer for donation management (CRUD operations, tracking, and analytics)
 * Author: FSMS Development Agent
 * Architecture Layer: Domain (Model)
 * Reference: MVC Pattern (Satzinger, Jackson & Burd, 2014)
 * 
 * Reference Code Base:
 * [1] PHP PDO Best Practices (PHP Manual, 2025)
 * [2] MVC Architecture Pattern (Satzinger et al., 2014)
 * [3] Database Normalization Principles (Codd, 1970)
 */

require_once __DIR__ . "/../config/database.php";

class Donation {
    private $pdo;
    private $table = 'Donations';

    /**
     * HZ-DON-001: Constructor - Initialize database connection
     * Ensures PDO connection is available for all operations
     * 
     * @param PDO $pdo Database connection object (dependency injection)
     */
    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    /**
     * HZ-DON-002: Create new donation record
     * Validates input and inserts donation into database
     * Supports multiple donation types: cash, food, supplies, other
     * 
     * @param array $data Donation data
     * @return array Result with success status and donation ID
     */
    public function createDonation($data) {
        try {
            $stmt = $this->pdo->prepare(
                "INSERT INTO {$this->table} (DonorName, DonorEmail, DonationType, Amount, Description, DonationDate)
                 VALUES (?, ?, ?, ?, ?, ?)"
            );

            $result = $stmt->execute([
                htmlspecialchars($data['DonorName']),
                htmlspecialchars($data['DonorEmail'] ?? ''),
                htmlspecialchars($data['DonationType']),
                isset($data['Amount']) ? (float)$data['Amount'] : 0,
                htmlspecialchars($data['Description'] ?? ''),
                $data['DonationDate']
            ]);

            if ($result) {
                return [
                    'success' => true,
                    'message' => 'Donation recorded successfully',
                    'id' => $this->pdo->lastInsertId()
                ];
            }

            return [
                'success' => false,
                'message' => 'Failed to record donation'
            ];
        } catch (PDOException $e) {
            return [
                'success' => false,
                'message' => 'Database error: ' . $e->getMessage()
            ];
        }
    }

    /**
     * HZ-DON-003: Retrieve all donations with pagination
     * Supports optional filtering and sorting
     * Calculates donation statistics
     * 
     * @param int $page Pagination page number (default: 1)
     * @param int $limit Records per page (default: 20)
     * @return array Donations with pagination info
     */
    public function getAllDonations($page = 1, $limit = 20) {
        try {
            $offset = ($page - 1) * $limit;

            // Get total count
            $countStmt = $this->pdo->query("SELECT COUNT(*) as total FROM {$this->table}");
            $totalRecords = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];
            $totalPages = ceil($totalRecords / $limit);

            // Get paginated records
            $stmt = $this->pdo->prepare(
                "SELECT * FROM {$this->table} 
                 ORDER BY DonationDate DESC, DonationID DESC 
                 LIMIT ? OFFSET ?"
            );
            $stmt->bindParam(1, $limit, PDO::PARAM_INT);
            $stmt->bindParam(2, $offset, PDO::PARAM_INT);
            $stmt->execute();

            return [
                'data' => $stmt->fetchAll(PDO::FETCH_ASSOC),
                'pagination' => [
                    'current_page' => $page,
                    'total_pages' => $totalPages,
                    'total_records' => $totalRecords,
                    'per_page' => $limit
                ]
            ];
        } catch (PDOException $e) {
            return [
                'data' => [],
                'error' => 'Database error: ' . $e->getMessage()
            ];
        }
    }

    /**
     * HZ-DON-004: Retrieve specific donation by ID
     * Includes detailed information about the donation
     * 
     * @param int $id Donation ID
     * @return array|false Donation data or false if not found
     */
    public function getDonationById($id) {
        try {
            $stmt = $this->pdo->prepare(
                "SELECT * FROM {$this->table} 
                 WHERE DonationID = ?"
            );
            $stmt->execute([(int)$id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('Database error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * HZ-DON-005: Update donation record
     * Validates data and updates existing record
     * 
     * @param int $id Donation ID
     * @param array $data Updated donation data
     * @return array Result with success status and message
     */
    public function updateDonation($id, $data) {
        try {
            $stmt = $this->pdo->prepare(
                "UPDATE {$this->table} 
                 SET DonorName = ?, DonorEmail = ?, DonationType = ?, Amount = ?, Description = ?, DonationDate = ?
                 WHERE DonationID = ?"
            );

            $result = $stmt->execute([
                htmlspecialchars($data['DonorName']),
                htmlspecialchars($data['DonorEmail'] ?? ''),
                htmlspecialchars($data['DonationType']),
                isset($data['Amount']) ? (float)$data['Amount'] : 0,
                htmlspecialchars($data['Description'] ?? ''),
                $data['DonationDate'],
                (int)$id
            ]);

            if ($result && $stmt->rowCount() > 0) {
                return [
                    'success' => true,
                    'message' => 'Donation updated successfully'
                ];
            }

            return [
                'success' => false,
                'message' => 'Failed to update donation or donation not found'
            ];
        } catch (PDOException $e) {
            return [
                'success' => false,
                'message' => 'Database error: ' . $e->getMessage()
            ];
        }
    }

    /**
     * HZ-DON-006: Delete donation record
     * Only admin users should have permission to delete
     * Consider archiving for audit trails
     * 
     * @param int $id Donation ID
     * @return array Result with success status and message
     */
    public function deleteDonation($id) {
        try {
            $stmt = $this->pdo->prepare("DELETE FROM {$this->table} WHERE DonationID = ?");
            $result = $stmt->execute([(int)$id]);

            if ($result && $stmt->rowCount() > 0) {
                return [
                    'success' => true,
                    'message' => 'Donation deleted successfully'
                ];
            }

            return [
                'success' => false,
                'message' => 'Failed to delete donation or donation not found'
            ];
        } catch (PDOException $e) {
            return [
                'success' => false,
                'message' => 'Database error: ' . $e->getMessage()
            ];
        }
    }

    /**
     * HZ-DON-007: Get donation summary statistics
     * Used for dashboard and reporting
     * Calculates total donations, count by type, donor information
     * 
     * @return array Donation statistics
     */
    public function getDonationSummary() {
        try {
            $stmt = $this->pdo->query(
                "SELECT 
                    COUNT(*) as total_donations,
                    SUM(CASE WHEN DonationType = 'cash' THEN Amount ELSE 0 END) as total_cash,
                    COUNT(CASE WHEN DonationType = 'cash' THEN 1 END) as cash_donations,
                    COUNT(CASE WHEN DonationType = 'food' THEN 1 END) as food_donations,
                    COUNT(CASE WHEN DonationType = 'supplies' THEN 1 END) as supplies_donations,
                    COUNT(CASE WHEN DonationType = 'other' THEN 1 END) as other_donations,
                    COUNT(DISTINCT DonorName) as unique_donors
                 FROM {$this->table}"
            );
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('Database error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * HZ-DON-008: Get donations by type
     * Filters donations based on donation type
     * 
     * @param string $type Donation type (cash, food, supplies, other)
     * @return array Filtered donations
     */
    public function getDonationsByType($type) {
        try {
            $stmt = $this->pdo->prepare(
                "SELECT * FROM {$this->table}
                 WHERE DonationType = ?
                 ORDER BY DonationDate DESC"
            );
            $stmt->execute([htmlspecialchars($type)]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('Database error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * HZ-DON-009: Get donations by date range
     * Used for period-based reporting and analysis
     * 
     * @param string $startDate Start date (YYYY-MM-DD)
     * @param string $endDate End date (YYYY-MM-DD)
     * @return array Donations within date range
     */
    public function getDonationsByDateRange($startDate, $endDate) {
        try {
            $stmt = $this->pdo->prepare(
                "SELECT * FROM {$this->table}
                 WHERE DATE(DonationDate) BETWEEN ? AND ?
                 ORDER BY DonationDate DESC"
            );
            $stmt->execute([$startDate, $endDate]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('Database error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * HZ-DON-010: Search donations
     * Supports partial matching for flexible searches
     * Uses LOWER() for case-insensitive comparison
     * 
     * @param string $searchTerm Search query
     * @return array Matching donations
     */
    public function searchDonations($searchTerm) {
        try {
            $searchTerm = '%' . htmlspecialchars($searchTerm) . '%';
            $stmt = $this->pdo->prepare(
                "SELECT * FROM {$this->table}
                 WHERE LOWER(DonorName) LIKE LOWER(?) 
                    OR LOWER(DonorEmail) LIKE LOWER(?)
                    OR LOWER(Description) LIKE LOWER(?)
                 ORDER BY DonationDate DESC"
            );
            $stmt->execute([$searchTerm, $searchTerm, $searchTerm]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('Database error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * HZ-DON-011: Get top donors
     * Identifies most generous donors for appreciation
     * Useful for recognition and stewardship programs
     * 
     * @param int $limit Number of top donors to return (default: 10)
     * @return array Top donors with contribution amounts
     */
    public function getTopDonors($limit = 10) {
        try {
            $stmt = $this->pdo->prepare(
                "SELECT 
                    DonorName,
                    DonorEmail,
                    COUNT(*) as donation_count,
                    SUM(Amount) as total_amount,
                    MAX(DonationDate) as last_donation_date
                 FROM {$this->table}
                 WHERE Amount > 0
                 GROUP BY DonorName, DonorEmail
                 ORDER BY total_amount DESC
                 LIMIT ?"
            );
            $stmt->bindParam(1, $limit, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('Database error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * HZ-DON-012: Get cash donation total for period
     * Used for financial reporting and reconciliation
     * 
     * @param string $startDate Start date (YYYY-MM-DD)
     * @param string $endDate End date (YYYY-MM-DD)
     * @return float Total cash donations in period
     */
    public function getCashTotalByPeriod($startDate, $endDate) {
        try {
            $stmt = $this->pdo->prepare(
                "SELECT SUM(Amount) as total
                 FROM {$this->table}
                 WHERE DonationType = 'cash' AND DATE(DonationDate) BETWEEN ? AND ?"
            );
            $stmt->execute([$startDate, $endDate]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['total'] ?? 0;
        } catch (PDOException $e) {
            error_log('Database error: ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * HZ-DON-011: Get recent donations for dashboard display
     * Returns the most recent donations with item details
     *
     * @param int $limit Number of recent donations to return
     * @return array Recent donations
     */
    public function getRecentDonations($limit = 5) {
        try {
            $stmt = $this->pdo->prepare(
                "SELECT DonationID, ItemName, Quantity, Unit, DonorName, Source, DonationDate, date_received
                 FROM {$this->table}
                 ORDER BY DonationDate DESC
                 LIMIT ?"
            );
            $stmt->bindParam(1, $limit, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('Database error: ' . $e->getMessage());
            return [];
        }
    }
}
