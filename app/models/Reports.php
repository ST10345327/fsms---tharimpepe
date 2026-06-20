<?php
/**
 * Module: Reports Model
 * Purpose: Generate comprehensive reports across all modules
 * Reference: Enhanced Reports Module - Cross-module reporting
 * Hazard ID: HZ-RPT-*
 */

require_once __DIR__ . "/../../config/database.php";

class Reports {
    private $pdo;

    public function __construct(PDO $db = null) {
        if ($db instanceof PDO) {
            $this->pdo = $db;
            return;
        }

        global $pdo;
        if ($pdo instanceof PDO) {
            $this->pdo = $pdo;
            return;
        }

        $this->pdo = getConnection();
    }

    /**
     * HZ-RPT-001: Get attendance report with filter options
     */
    public function getAttendanceReport($fromDate = null, $toDate = null, $beneficiaryId = null) {
        $query = "
            SELECT a.AttendanceID, a.BeneficiaryID, a.SessionDate, 
                   CONCAT(b.FirstName, ' ', b.LastName) as FullName, a.Status, a.Notes
            FROM Attendance a
            JOIN Beneficiaries b ON a.BeneficiaryID = b.BeneficiaryID
            WHERE 1=1
        ";
        
        $params = [];
        if ($fromDate) {
            $query .= " AND a.SessionDate >= ?";
            $params[] = $fromDate;
        }
        if ($toDate) {
            $query .= " AND a.SessionDate <= ?";
            $params[] = $toDate;
        }
        if ($beneficiaryId) {
            $query .= " AND a.BeneficiaryID = ?";
            $params[] = $beneficiaryId;
        }
        
        $query .= " ORDER BY a.SessionDate DESC, b.LastName ASC";
        
        $stmt = $this->pdo->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * HZ-RPT-002: Get donation report with summary
     */
    public function getDonationReport($fromDate = null, $toDate = null, $donationType = null) {
        $query = "
            SELECT d.DonationID, d.DonorID, d.DonorName, d.DonationDate, 
                   d.Amount, d.DonationType, d.Notes
            FROM Donations d
            WHERE 1=1
        ";
        
        $params = [];
        if ($fromDate) {
            $query .= " AND d.DonationDate >= ?";
            $params[] = $fromDate;
        }
        if ($toDate) {
            $query .= " AND d.DonationDate <= ?";
            $params[] = $toDate;
        }
        if ($donationType) {
            $query .= " AND d.DonationType = ?";
            $params[] = $donationType;
        }
        
        $query .= " ORDER BY d.DonationDate DESC";
        
        $stmt = $this->pdo->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * HZ-RPT-003: Get donor summary report
     */
    public function getDonorSummaryReport() {
        $stmt = $this->pdo->query("
            SELECT DonorName, COUNT(*) as donation_count, 
                   SUM(Amount) as total_amount, MAX(DonationDate) as last_donation
            FROM Donations
            GROUP BY DonorName
            ORDER BY total_amount DESC
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * HZ-RPT-004: Get food stock status report
     */
    public function getFoodStockReport() {
        $stmt = $this->pdo->query("
            SELECT FoodStockID, ItemName, Quantity, Unit,
                   ExpiryDate, StockDate, Notes
            FROM FoodStock
            ORDER BY Quantity ASC, ExpiryDate ASC
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * HZ-RPT-005: Get food distribution report
     */
    public function getFoodDistributionReport($fromDate = null, $toDate = null) {
        try {
            $check = $this->pdo->query("SHOW TABLES LIKE 'FoodDistribution'");
            if ($check->rowCount() === 0) {
                return [];
            }
            
            $query = "
                SELECT fd.DistributionID, fd.FoodStockID, f.ItemName, 
                       fd.QuantityDistributed, fd.DistributionDate, 
                       fd.Location, fd.Purpose, fd.Notes
                FROM FoodDistribution fd
                JOIN FoodStock f ON fd.FoodStockID = f.FoodStockID
                WHERE 1=1
            ";
            
            $params = [];
            if ($fromDate) {
                $query .= " AND fd.DistributionDate >= ?";
                $params[] = $fromDate;
            }
            if ($toDate) {
                $query .= " AND fd.DistributionDate <= ?";
                $params[] = $toDate;
            }
            
            $query .= " ORDER BY fd.DistributionDate DESC";
            
            $stmt = $this->pdo->prepare($query);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Reports::getFoodDistributionReport - " . $e->getMessage());
            return [];
        }
    }

    /**
     * HZ-RPT-006: Get volunteer performance report
     */
    public function getVolunteerPerformanceReport() {
        try {
            $check = $this->pdo->query("SHOW TABLES LIKE 'VolunteerSchedules'");
            if ($check->rowCount() === 0) {
                return [];
            }
            
            $stmt = $this->pdo->query("
                SELECT v.VolunteerID, CONCAT(v.FirstName, ' ', v.LastName) as FullName, v.Phone, v.AvailabilityStatus as Status,
                       COUNT(vs.ScheduleID) as total_shifts,
                       SUM(CASE WHEN vs.Status = 'completed' THEN 1 ELSE 0 END) as completed_shifts,
                       SUM(vs.HoursWorked) as total_hours,
                       SUM(CASE WHEN vs.Status = 'cancelled' THEN 1 ELSE 0 END) as cancelled_shifts,
                       SUM(CASE WHEN vs.Status = 'no-show' THEN 1 ELSE 0 END) as no_show_shifts
                FROM Volunteers v
                LEFT JOIN VolunteerSchedules vs ON v.VolunteerID = vs.VolunteerID
                GROUP BY v.VolunteerID, v.FirstName, v.LastName, v.Phone, v.AvailabilityStatus
                ORDER BY total_hours DESC
            ");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Reports::getVolunteerPerformanceReport - " . $e->getMessage());
            return [];
        }
    }

    /**
     * HZ-RPT-007: Get volunteer schedule report
     */
    public function getVolunteerScheduleReport($fromDate = null, $toDate = null, $status = null) {
        try {
            $check = $this->pdo->query("SHOW TABLES LIKE 'VolunteerSchedules'");
            if ($check->rowCount() === 0) {
                return [];
            }
            
            $query = "
                SELECT vs.ScheduleID, vs.VolunteerID, CONCAT(v.FirstName, ' ', v.LastName) as FullName, v.Phone,
                       vs.ScheduleDate, vs.StartTime, vs.EndTime, 
                       vs.Role, vs.Location, vs.Status, vs.HoursWorked, vs.Notes
                FROM VolunteerSchedules vs
                JOIN Volunteers v ON vs.VolunteerID = v.VolunteerID
                WHERE 1=1
            ";
            
            $params = [];
            if ($fromDate) {
                $query .= " AND vs.ScheduleDate >= ?";
                $params[] = $fromDate;
            }
            if ($toDate) {
                $query .= " AND vs.ScheduleDate <= ?";
                $params[] = $toDate;
            }
            if ($status) {
                $query .= " AND vs.Status = ?";
                $params[] = $status;
            }
            
            $query .= " ORDER BY vs.ScheduleDate DESC, v.LastName ASC";
            
            $stmt = $this->pdo->prepare($query);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Reports::getVolunteerScheduleReport - " . $e->getMessage());
            return [];
        }
    }

    /**
     * HZ-RPT-008: Get beneficiary report
     */
    public function getBeneficiaryReport($roleFilter = null, $statusFilter = null) {
        $query = "
            SELECT BeneficiaryID, FirstName, LastName, Age, Gender, ContactPerson, ContactPhone, RegistrationDate,Status
            FROM Beneficiaries
            WHERE 1=1
        ";
        
        $params = [];
        if ($roleFilter) {
            $query .= " AND Role = ?";
            $params[] = $roleFilter;
        }
        if ($statusFilter) {
            $query .= " AND Status = ?";
            $params[] = $statusFilter;
        }
        
        $query .= " ORDER BY LastName ASC, FirstName ASC";
        
        $stmt = $this->pdo->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * HZ-RPT-009: Get user activity audit report
     */
    public function getActivityAuditReport($fromDate = null, $toDate = null, $userId = null, $activityType = null) {
        $query = "
            SELECT al.ActivityID, al.UserID, u.Username, al.Action as ActivityType,
                   al.Details as Description, al.Timestamp, al.AffectedEntityName, al.AffectedEntityID
            FROM ActivityLog al
            JOIN Users u ON al.UserID = u.UserID
            WHERE 1=1
        ";
        
        $params = [];
        if ($fromDate) {
            $query .= " AND al.Timestamp >= ?";
            $params[] = $fromDate;
        }
        if ($toDate) {
            $query .= " AND al.Timestamp <= ?";
            $params[] = $toDate;
        }
        if ($userId) {
            $query .= " AND al.UserID = ?";
            $params[] = $userId;
        }
        if ($activityType) {
            $query .= " AND al.Action = ?";
            $params[] = $activityType;
        }
        
        $query .= " ORDER BY al.Timestamp DESC";
        
        $stmt = $this->pdo->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * HZ-RPT-010: Get comprehensive program summary report
     */
    public function getProgramSummaryReport($fromDate = null, $toDate = null) {
        $summary = [];
        
        // Attendance summary
        $query = "SELECT COUNT(*) as total, COUNT(DISTINCT BeneficiaryID) as unique_beneficiaries FROM Attendance WHERE 1=1";
        $params = [];
        if ($fromDate) {
            $query .= " AND SessionDate >= ?";
            $params[] = $fromDate;
        }
        if ($toDate) {
            $query .= " AND SessionDate <= ?";
            $params[] = $toDate;
        }
        $stmt = $this->pdo->prepare($query);
        $stmt->execute($params);
        $summary['attendance'] = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Donation summary
        $query = "SELECT COUNT(*) as total_donations, SUM(Amount) as total_amount FROM Donations WHERE 1=1";
        $params = [];
        if ($fromDate) {
            $query .= " AND DonationDate >= ?";
            $params[] = $fromDate;
        }
        if ($toDate) {
            $query .= " AND DonationDate <= ?";
            $params[] = $toDate;
        }
        $stmt = $this->pdo->prepare($query);
        $stmt->execute($params);
        $summary['donations'] = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Volunteer summary
        $query = "SELECT COUNT(*) as total_shifts, SUM(HoursWorked) as total_hours FROM VolunteerSchedules WHERE 1=1";
        $params = [];
        if ($fromDate) {
            $query .= " AND ScheduleDate >= ?";
            $params[] = $fromDate;
        }
        if ($toDate) {
            $query .= " AND ScheduleDate <= ?";
            $params[] = $toDate;
        }
        $stmt = $this->pdo->prepare($query);
        $stmt->execute($params);
        $summary['volunteers'] = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Inventory summary
        $stmt = $this->pdo->query("SELECT COUNT(*) as total_items, SUM(Quantity) as total_quantity FROM FoodStock");
        $summary['inventory'] = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $summary;
    }

    /**
     * HZ-RPT-011: Export data as CSV format
     */
    public function exportAsCSV($data, $filename) {
        header('Content-Type: text/csv; charset=utf-8');
        header("Content-Disposition: attachment; filename={$filename}");
        
        $output = fopen('php://output', 'w');
        
        if (!empty($data)) {
            fputcsv($output, array_keys($data[0]));
            foreach ($data as $row) {
                fputcsv($output, $row);
            }
        }
        
        fclose($output);
    }

    /**
     * HZ-RPT-012: Get monthly financial summary
     */
    public function getMonthlyFinancialSummary($year, $month) {
        $startDate = "$year-$month-01";
        $endDate = date('Y-m-t', strtotime($startDate));
        
        $stmt = $this->pdo->prepare("
            SELECT 
                SUM(Amount) as total_donations,
                COUNT(*) as donation_count,
                AVG(Amount) as avg_donation
            FROM Donations
            WHERE DonationDate BETWEEN ? AND ?
        ");
        $stmt->execute([$startDate, $endDate]);
        $donations = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $stmt = $this->pdo->prepare("
            SELECT 
                SUM(QuantityDistributed * 
                    (SELECT UnitCost FROM FoodStock WHERE FoodStockID = FoodDistribution.FoodStockID)) as total_distributed_cost
            FROM FoodDistribution
            WHERE DistributionDate BETWEEN ? AND ?
        ");
        $stmt->execute([$startDate, $endDate]);
        $distribution = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return [
            'period' => "$year-$month",
            'donations' => $donations,
            'distribution_cost' => $distribution['total_distributed_cost'] ?? 0
        ];
    }
}
?>
