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

    public function __construct(PDO $pdo = null) {
        if ($pdo) {
            $this->pdo = $pdo;
        } else {
            global $pdo;
            $this->pdo = $pdo;
        }
        if (!$this->pdo) {
            $db = new Database();
            $this->pdo = $db->connect();
        }
    }

    /**
     * HZ-RPT-001: Get attendance report with filter options
     */
    public function getAttendanceReport($fromDate = null, $toDate = null, $beneficiaryId = null) {
        $query = "
            SELECT a.AttendanceID, a.BeneficiaryID, a.SessionDate,
                   CONCAT(b.FirstName, ' ', b.LastName) as FullName,
                   b.Age, b.Status as BeneficiaryStatus, a.Status as AttendanceStatus, a.Notes
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
            SELECT d.DonationID, d.DonorName, d.DonorEmail, d.DonationDate,
                   d.Amount, d.DonationType, d.Description
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
            SELECT DonorName, DonorEmail, COUNT(*) as donation_count,
                   SUM(CASE WHEN DonationType = 'cash' THEN Amount ELSE 0 END) as total_amount,
                   MAX(DonationDate) as last_donation
            FROM Donations
            GROUP BY DonorName, DonorEmail
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
                   ExpiryDate,
                   CASE
                       WHEN ExpiryDate < CURDATE() THEN 'expired'
                       WHEN Quantity <= 5 THEN 'low_stock'
                       ELSE 'ok'
                   END as Status,
                   DATEDIFF(ExpiryDate, CURDATE()) as days_until_expiry
            FROM FoodStock
            ORDER BY
                CASE
                    WHEN ExpiryDate < CURDATE() THEN 0
                    WHEN Quantity <= 5 THEN 1
                    ELSE 2
                END ASC,
                ExpiryDate ASC
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * HZ-RPT-005: Get food distribution report
     * Uses FoodStock table to track distribution via quantity changes
     */
    public function getFoodDistributionReport($fromDate = null, $toDate = null) {
        $query = "
            SELECT f.FoodStockID, f.ItemName, f.Quantity as current_quantity,
                   f.Unit, f.StockDate, f.ExpiryDate, f.Notes
            FROM FoodStock f
            WHERE 1=1
        ";

        $params = [];
        if ($fromDate) {
            $query .= " AND f.StockDate >= ?";
            $params[] = $fromDate;
        }
        if ($toDate) {
            $query .= " AND f.StockDate <= ?";
            $params[] = $toDate;
        }

        $query .= " ORDER BY f.StockDate DESC, f.ItemName ASC";

        $stmt = $this->pdo->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * HZ-RPT-006: Get volunteer performance report
     */
    public function getVolunteerPerformanceReport() {
        $stmt = $this->pdo->query("
            SELECT v.VolunteerID,
                   u.FullName,
                   u.Email,
                   u.Phone,
                   v.AvailabilityStatus as Status,
                   COUNT(vs.ScheduleID) as total_shifts,
                   SUM(CASE WHEN vs.Status = 'completed' THEN 1 ELSE 0 END) as completed_shifts,
                   SUM(CASE WHEN vs.Status = 'cancelled' THEN 1 ELSE 0 END) as cancelled_shifts
            FROM Volunteers v
            LEFT JOIN Users u ON v.UserID = u.UserID
            LEFT JOIN VolunteerSchedules vs ON v.VolunteerID = vs.VolunteerID
            WHERE u.Status = 'active' AND v.Status = 'approved'
            GROUP BY v.VolunteerID, u.FullName, u.Email, u.Phone, v.AvailabilityStatus
            ORDER BY completed_shifts DESC
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * HZ-RPT-007: Get volunteer schedule report
     */
    public function getVolunteerScheduleReport($fromDate = null, $toDate = null, $status = null) {
        $query = "
            SELECT vs.ScheduleID, vs.VolunteerID,
                   u.FullName,
                   vs.ScheduleDate, vs.StartTime, vs.EndTime,
                   vs.Role, vs.Location, vs.Status, vs.Notes, vs.HoursWorked
            FROM VolunteerSchedules vs
            JOIN Volunteers v ON vs.VolunteerID = v.VolunteerID
            LEFT JOIN Users u ON v.UserID = u.UserID
            WHERE u.Status = 'active' AND v.Status = 'approved'
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

        $query .= " ORDER BY vs.ScheduleDate DESC, vs.ScheduleID DESC";

        $stmt = $this->pdo->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * HZ-RPT-008: Get beneficiary report
     */
    public function getBeneficiaryReport($statusFilter = null) {
        $query = "
            SELECT BeneficiaryID,
                   CONCAT(FirstName, ' ', LastName) as FullName,
                   Age, Gender, Status, Phone, Email, Address,
                   RegistrationDate, Notes
            FROM Beneficiaries
            WHERE 1=1
        ";

        $params = [];
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
    public function getActivityAuditReport($fromDate = null, $toDate = null, $userId = null, $actionType = null) {
        $query = "
            SELECT al.ActivityID, al.UserID, u.Username,
                   CONCAT(u.FullName, '') as FullName,
                   al.Action, al.Details, al.TIMESTAMP as Timestamp,
                   al.AffectedEntityName as Module, al.Description
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
        if ($actionType) {
            $query .= " AND al.Action = ?";
            $params[] = $actionType;
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
        $query = "SELECT COUNT(*) as total_donations, SUM(CASE WHEN DonationType = 'cash' THEN Amount ELSE 0 END) as total_amount FROM Donations WHERE 1=1";
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
        $query = "SELECT COUNT(*) as total_shifts FROM VolunteerSchedules WHERE 1=1";
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

        // Beneficiary counts
        $stmt = $this->pdo->query("SELECT COUNT(*) as total, SUM(CASE WHEN Status = 'active' THEN 1 ELSE 0 END) as active FROM Beneficiaries");
        $summary['beneficiaries'] = $stmt->fetch(PDO::FETCH_ASSOC);

        return $summary;
    }

    /**
     * HZ-RPT-011A: Flatten program summary into single-row array for CSV export
     */
    public function getProgramSummaryExport($fromDate = null, $toDate = null) {
        $data = $this->getProgramSummaryReport($fromDate, $toDate);
        $flat = [];
        foreach ($data as $section => $values) {
            if (is_array($values)) {
                foreach ($values as $key => $val) {
                    $flat[$section . '_' . $key] = $val;
                }
            } else {
                $flat[$section] = $values;
            }
        }
        return [$flat];
    }

    /**
     * HZ-RPT-011: Export data as CSV format with proper Excel formatting
     */
    public function exportAsCSV($data, $filename) {
        header('Content-Type: text/csv; charset=utf-8');
        header("Content-Disposition: attachment; filename={$filename}");

        $output = fopen('php://output', 'w');
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

        if (!empty($data)) {
            $headers = array_keys($data[0]);
            $displayHeaders = array_map(function($h) {
                return ucwords(str_replace('_', ' ', $h));
            }, $headers);
            fputcsv($output, $displayHeaders);
            foreach ($data as $row) {
                $cleanRow = [];
                foreach ($headers as $h) {
                    $val = $row[$h] ?? '';
                    if (is_numeric($val) && strpos((string)$val, '.') !== false) {
                        $val = number_format((float)$val, 2, '.', '');
                    }
                    $cleanRow[] = $val;
                }
                fputcsv($output, $cleanRow);
            }
        }

        fclose($output);
    }

    /**
     * HZ-RPT-011B: Export data as styled HTML table (.xls) for Excel
     */
    public function exportAsXLS($data, $filename, $title = 'Report') {
        header('Content-Type: application/vnd.ms-excel; charset=utf-8');
        header("Content-Disposition: attachment; filename={$filename}");

        echo chr(0xEF).chr(0xBB).chr(0xBF);

        echo '<html><head><meta charset="UTF-8">';
        echo '<style>
            body { font-family: Calibri, Arial, sans-serif; font-size: 11pt; color: #111827; padding: 20px; }
            h1 { font-size: 18pt; color: #1b3a5c; margin-bottom: 6px; }
            .subtitle { font-size: 10pt; color: #6b7280; margin-bottom: 20px; }
            table { border-collapse: collapse; width: 100%; }
            th { background: #1b3a5c; color: #fff; padding: 8px 10px; text-align: left; font-weight: 700; font-size: 10pt; border: 1px solid #1b3a5c; }
            td { padding: 6px 10px; border: 1px solid #d1d5db; font-size: 10pt; }
            tr:nth-child(even) { background: #f9fafb; }
            tr:hover { background: #eef2ff; }
            .numeric { text-align: right; }
        </style></head><body>';
        echo '<h1>' . htmlspecialchars($title) . '</h1>';
        echo '<div class="subtitle">Tharimpepe Feeding Scheme — Generated: ' . date('Y-m-d H:i') . '</div>';
        echo '<table>';

        if (!empty($data)) {
            $headers = array_keys($data[0]);
            echo '<thead><tr>';
            foreach ($headers as $h) {
                echo '<th>' . htmlspecialchars(ucwords(str_replace('_', ' ', $h))) . '</th>';
            }
            echo '</tr></thead><tbody>';
            foreach ($data as $row) {
                echo '<tr>';
                foreach ($headers as $h) {
                    $val = $row[$h] ?? '';
                    $class = is_numeric($val) ? ' class="numeric"' : '';
                    echo '<td' . $class . '>' . htmlspecialchars((string)$val) . '</td>';
                }
                echo '</tr>';
            }
            echo '</tbody>';
        }

        echo '</table></body></html>';
        exit;
    }

    /**
     * HZ-RPT-012: Get monthly financial summary
     */
    public function getMonthlyFinancialSummary($year, $month) {
        $startDate = "$year-$month-01";
        $endDate = date('Y-m-t', strtotime($startDate));

        // Donations (income)
        $stmt = $this->pdo->prepare("
            SELECT
                COALESCE(SUM(CASE WHEN DonationType = 'cash' THEN Amount ELSE 0 END), 0) as total_cash,
                COUNT(*) as donation_count,
                COUNT(DISTINCT DonorName) as unique_donors
            FROM Donations
            WHERE DonationDate BETWEEN ? AND ?
        ");
        $stmt->execute([$startDate, $endDate]);
        $donations = $stmt->fetch(PDO::FETCH_ASSOC);

        // Distribution count (food stock added in period as proxy)
        $stmt = $this->pdo->prepare("
            SELECT COUNT(*) as distribution_count
            FROM FoodStock
            WHERE StockDate BETWEEN ? AND ?
        ");
        $stmt->execute([$startDate, $endDate]);
        $dist = $stmt->fetch(PDO::FETCH_ASSOC);

        // Total active beneficiaries
        $stmt = $this->pdo->query("
            SELECT COUNT(*) as total_beneficiaries
            FROM Beneficiaries WHERE Status = 'active'
        ");
        $bene = $stmt->fetch(PDO::FETCH_ASSOC);

        // Meals served (present attendance records in period)
        $stmt = $this->pdo->prepare("
            SELECT COUNT(*) as total_meals
            FROM Attendance
            WHERE Status = 'present' AND SessionDate BETWEEN ? AND ?
        ");
        $stmt->execute([$startDate, $endDate]);
        $meals = $stmt->fetch(PDO::FETCH_ASSOC);

        // Volunteer hours in period
        $stmt = $this->pdo->prepare("
            SELECT COALESCE(SUM(HoursWorked), 0) as total_volunteer_hours
            FROM VolunteerSchedules
            WHERE STATUS = 'completed' AND ScheduleDate BETWEEN ? AND ?
        ");
        $stmt->execute([$startDate, $endDate]);
        $hours = $stmt->fetch(PDO::FETCH_ASSOC);

        return [
            'period' => "$year-$month",
            'total_income' => (float)$donations['total_cash'],
            'total_expenses' => 0.0,
            'donation_count' => (int)$donations['donation_count'],
            'unique_donors' => (int)$donations['unique_donors'],
            'distribution_count' => (int)$dist['distribution_count'],
            'total_beneficiaries' => (int)$bene['total_beneficiaries'],
            'total_meals' => (int)$meals['total_meals'],
            'total_volunteer_hours' => (float)$hours['total_volunteer_hours'],
        ];
    }
}
?>
