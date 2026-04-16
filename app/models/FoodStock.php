<?php
/**
 * File: FoodStock.php
 * Purpose: Data layer for food stock inventory management (CRUD operations and analytics)
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

class FoodStock {
    private $pdo;
    private $table = 'FoodStock';

    /**
     * HZ-FOOD-001: Constructor - Initialize database connection
     * Ensures PDO connection is available for all operations
     * 
     * @param PDO $pdo Database connection object (dependency injection)
     */
    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    /**
     * HZ-FOOD-002: Create new food stock item
     * Validates input and inserts food stock record into database
     * Uses parameterized queries to prevent SQL injection
     * 
     * @param array $data Stock data (ItemName, Quantity, Unit, ExpiryDate, Notes)
     * @return array Result with success status and message
     */
    public function createStock($data) {
        try {
            $stmt = $this->pdo->prepare(
                "INSERT INTO {$this->table} (ItemName, Quantity, Unit, ExpiryDate, StockDate, Notes)
                 VALUES (?, ?, ?, ?, ?, ?)"
            );

            $result = $stmt->execute([
                htmlspecialchars($data['ItemName']),
                (int)$data['Quantity'],
                htmlspecialchars($data['Unit']),
                !empty($data['ExpiryDate']) ? $data['ExpiryDate'] : null,
                date('Y-m-d'),
                htmlspecialchars($data['Notes'] ?? '')
            ]);

            if ($result) {
                return [
                    'success' => true,
                    'message' => 'Food stock item created successfully',
                    'id' => $this->pdo->lastInsertId()
                ];
            }

            return [
                'success' => false,
                'message' => 'Failed to create food stock item'
            ];
        } catch (PDOException $e) {
            return [
                'success' => false,
                'message' => 'Database error: ' . $e->getMessage()
            ];
        }
    }

    /**
     * HZ-FOOD-003: Retrieve all food stock items
     * Optional filtering by status (low_stock, expired, ok)
     * Implements pagination for performance
     * 
     * @param int $page Pagination page number (default: 1)
     * @param int $limit Records per page (default: 20)
     * @return array Food stock items with pagination info
     */
    public function getAllStock($page = 1, $limit = 20) {
        try {
            $offset = ($page - 1) * $limit;

            // Get total count
            $countStmt = $this->pdo->query("SELECT COUNT(*) as total FROM {$this->table}");
            $totalRecords = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];
            $totalPages = ceil($totalRecords / $limit);

            // Get paginated records
            $stmt = $this->pdo->prepare(
                "SELECT * FROM {$this->table} 
                 ORDER BY ExpiryDate ASC, UpdatedAt DESC 
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
     * HZ-FOOD-004: Retrieve specific food stock item by ID
     * Used for view, edit, and delete operations
     * Includes expiry status calculation
     * 
     * @param int $id Food stock item ID
     * @return array|false Stock item data or false if not found
     */
    public function getStockById($id) {
        try {
            $stmt = $this->pdo->prepare(
                "SELECT *, 
                        DATEDIFF(ExpiryDate, CURDATE()) as days_until_expiry,
                        CASE 
                            WHEN ExpiryDate < CURDATE() THEN 'expired'
                            WHEN DATEDIFF(ExpiryDate, CURDATE()) <= 7 AND ExpiryDate >= CURDATE() THEN 'expiring_soon'
                            ELSE 'ok'
                        END as expiry_status
                 FROM {$this->table} 
                 WHERE FoodStockID = ?"
            );
            $stmt->execute([(int)$id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('Database error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * HZ-FOOD-005: Update food stock item
     * Validates data and updates existing record
     * Maintains data integrity with parameterized queries
     * 
     * @param int $id Food stock item ID
     * @param array $data Updated stock data
     * @return array Result with success status and message
     */
    public function updateStock($id, $data) {
        try {
            $stmt = $this->pdo->prepare(
                "UPDATE {$this->table} 
                 SET ItemName = ?, Quantity = ?, Unit = ?, ExpiryDate = ?, Notes = ?, UpdatedAt = NOW()
                 WHERE FoodStockID = ?"
            );

            $result = $stmt->execute([
                htmlspecialchars($data['ItemName']),
                (int)$data['Quantity'],
                htmlspecialchars($data['Unit']),
                !empty($data['ExpiryDate']) ? $data['ExpiryDate'] : null,
                htmlspecialchars($data['Notes'] ?? ''),
                (int)$id
            ]);

            if ($result && $stmt->rowCount() > 0) {
                return [
                    'success' => true,
                    'message' => 'Food stock item updated successfully'
                ];
            }

            return [
                'success' => false,
                'message' => 'Failed to update food stock item or item not found'
            ];
        } catch (PDOException $e) {
            return [
                'success' => false,
                'message' => 'Database error: ' . $e->getMessage()
            ];
        }
    }

    /**
     * HZ-FOOD-006: Delete food stock item
     * Only admin users should have permission to delete
     * Soft delete consideration: may want to archive instead
     * 
     * @param int $id Food stock item ID
     * @return array Result with success status and message
     */
    public function deleteStock($id) {
        try {
            $stmt = $this->pdo->prepare("DELETE FROM {$this->table} WHERE FoodStockID = ?");
            $result = $stmt->execute([(int)$id]);

            if ($result && $stmt->rowCount() > 0) {
                return [
                    'success' => true,
                    'message' => 'Food stock item deleted successfully'
                ];
            }

            return [
                'success' => false,
                'message' => 'Failed to delete food stock item or item not found'
            ];
        } catch (PDOException $e) {
            return [
                'success' => false,
                'message' => 'Database error: ' . $e->getMessage()
            ];
        }
    }

    /**
     * HZ-FOOD-007: Get low stock items (quantity below threshold)
     * Used for inventory alerts and reordering recommendations
     * Threshold: Items with quantity <= 5 units or expiring within 7 days
     * 
     * @return array Low stock and expiring items
     */
    public function getLowStockItems() {
        try {
            $stmt = $this->pdo->prepare(
                "SELECT *, 
                        DATEDIFF(ExpiryDate, CURDATE()) as days_until_expiry,
                        CASE 
                            WHEN ExpiryDate < CURDATE() THEN 'expired'
                            WHEN DATEDIFF(ExpiryDate, CURDATE()) <= 7 AND ExpiryDate >= CURDATE() THEN 'expiring_soon'
                            ELSE 'ok'
                        END as expiry_status
                 FROM {$this->table}
                 WHERE Quantity <= 5 OR (ExpiryDate IS NOT NULL AND DATEDIFF(ExpiryDate, CURDATE()) <= 7 AND ExpiryDate >= CURDATE())
                 ORDER BY Quantity ASC, ExpiryDate ASC"
            );
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('Database error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * HZ-FOOD-008: Get expired stock items
     * Critical for food safety and compliance
     * Returns all items with ExpiryDate before today
     * 
     * @return array Expired food items
     */
    public function getExpiredStock() {
        try {
            $stmt = $this->pdo->prepare(
                "SELECT *, DATEDIFF(ExpiryDate, CURDATE()) as days_overdue
                 FROM {$this->table}
                 WHERE ExpiryDate < CURDATE()
                 ORDER BY ExpiryDate ASC"
            );
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('Database error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * HZ-FOOD-009: Update stock quantity (distribute/add to stock)
     * Used when food is distributed or new stock is received
     * Supports both increment and decrement operations
     * 
     * @param int $id Food stock item ID
     * @param int $quantity Change in quantity (positive or negative)
     * @param string $operation Type of operation (add/distribute/adjust)
     * @return array Result with success status and updated quantity
     */
    public function updateQuantity($id, $quantity, $operation = 'adjust') {
        try {
            // Validate operation type
            $validOperations = ['add', 'distribute', 'adjust'];
            $operation = in_array($operation, $validOperations) ? $operation : 'adjust';

            // Get current quantity
            $current = $this->getStockById($id);
            if (!$current) {
                return [
                    'success' => false,
                    'message' => 'Food stock item not found'
                ];
            }

            $newQuantity = match ($operation) {
                'add' => $current['Quantity'] + (int)$quantity,
                'distribute' => $current['Quantity'] - (int)$quantity,
                default => (int)$quantity
            };

            // Prevent negative quantities
            if ($newQuantity < 0) {
                return [
                    'success' => false,
                    'message' => 'Operation would result in negative stock quantity'
                ];
            }

            $stmt = $this->pdo->prepare(
                "UPDATE {$this->table} 
                 SET Quantity = ?, UpdatedAt = NOW()
                 WHERE FoodStockID = ?"
            );
            $result = $stmt->execute([(int)$newQuantity, (int)$id]);

            if ($result) {
                return [
                    'success' => true,
                    'message' => ucfirst($operation) . ' operation completed successfully',
                    'new_quantity' => $newQuantity
                ];
            }

            return [
                'success' => false,
                'message' => 'Failed to update stock quantity'
            ];
        } catch (PDOException $e) {
            return [
                'success' => false,
                'message' => 'Database error: ' . $e->getMessage()
            ];
        }
    }

    /**
     * HZ-FOOD-010: Get stock summary statistics
     * Used for dashboard and reporting
     * Provides total items, total quantity, value metrics
     * 
     * @return array Stock statistics
     */
    public function getStockSummary() {
        try {
            $stmt = $this->pdo->query(
                "SELECT 
                    COUNT(*) as total_items,
                    SUM(Quantity) as total_quantity,
                    COUNT(CASE WHEN Quantity <= 5 THEN 1 END) as low_stock_count,
                    COUNT(CASE WHEN ExpiryDate < CURDATE() THEN 1 END) as expired_count,
                    COUNT(CASE WHEN DATEDIFF(ExpiryDate, CURDATE()) <= 7 AND ExpiryDate >= CURDATE() THEN 1 END) as expiring_soon_count
                 FROM {$this->table}"
            );
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('Database error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * HZ-FOOD-011: Search food stock by item name or unit
     * Supports partial matching for flexible searches
     * Uses LOWER() for case-insensitive comparison
     * 
     * @param string $searchTerm Search query
     * @return array Matching food stock items
     */
    public function searchStock($searchTerm) {
        try {
            $searchTerm = '%' . htmlspecialchars($searchTerm) . '%';
            $stmt = $this->pdo->prepare(
                "SELECT *, 
                        DATEDIFF(ExpiryDate, CURDATE()) as days_until_expiry,
                        CASE 
                            WHEN ExpiryDate < CURDATE() THEN 'expired'
                            WHEN DATEDIFF(ExpiryDate, CURDATE()) <= 7 AND ExpiryDate >= CURDATE() THEN 'expiring_soon'
                            ELSE 'ok'
                        END as expiry_status
                 FROM {$this->table}
                 WHERE LOWER(ItemName) LIKE LOWER(?) OR LOWER(Unit) LIKE LOWER(?)
                 ORDER BY UpdatedAt DESC"
            );
            $stmt->execute([$searchTerm, $searchTerm]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('Database error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * HZ-FOOD-012: Get stock items by date range
     * Used for historical analysis and reporting
     * Filters by StockDate or last update date
     * 
     * @param string $startDate Start date (YYYY-MM-DD)
     * @param string $endDate End date (YYYY-MM-DD)
     * @return array Stock items updated within date range
     */
    public function getStockByDateRange($startDate, $endDate) {
        try {
            $stmt = $this->pdo->prepare(
                "SELECT * FROM {$this->table}
                 WHERE DATE(UpdatedAt) BETWEEN ? AND ?
                 ORDER BY UpdatedAt DESC"
            );
            $stmt->execute([$startDate, $endDate]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('Database error: ' . $e->getMessage());
            return [];
        }
    }
}
