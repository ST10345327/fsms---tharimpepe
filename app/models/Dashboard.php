<?php
/**
 * Module: Dashboard Model
 * Purpose: Retrieve system-wide analytics and KPI data
 * Reference: Dashboard Enhancements - Cross-module statistics
 * Hazard ID: HZ-DASH-*
 */

require_once __DIR__ . "/../../config/database.php";

class Dashboard {
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
     * Return a row value when present, otherwise fall back to a default.
     * Keeps the dashboard model compatible with older PHP versions.
     */
    private function rowValue($row, $key, $default = 0) {
        if (is_array($row) && isset($row[$key])) {
            return $row[$key];
        }

        return $default;
    }

    /**
     * HZ-DASH-001: Get all system statistics for dashboard overview
     */
    public function getSystemStats() {
        $stats = [];
        
        // Active beneficiaries
        $stmt = $this->pdo->query("SELECT COUNT(*) as count FROM beneficiaries WHERE Status = 'active'");
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $stats['active_beneficiaries'] = $this->rowValue($row, 'count', 0);
        
        // Total beneficiaries
        $stmt = $this->pdo->query("SELECT COUNT(*) as count FROM beneficiaries");
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $stats['total_beneficiaries'] = $this->rowValue($row, 'count', 0);
        
        // Active volunteers (using AvailabilityStatus)
        $stmt = $this->pdo->query("SELECT COUNT(*) as count FROM volunteers WHERE AvailabilityStatus = 'available'");
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $stats['active_volunteers'] = $this->rowValue($row, 'count', 0);
        
        // Total volunteers
        $stmt = $this->pdo->query("SELECT COUNT(*) as count FROM volunteers");
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $stats['total_volunteers'] = $this->rowValue($row, 'count', 0);
        
        // Total system users (using Status)
        $stmt = $this->pdo->query("SELECT COUNT(*) as count FROM users WHERE Status = 'active'");
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $stats['system_users'] = $this->rowValue($row, 'count', 0);
        
        return $stats;
    }

    /**
     * HZ-DASH-002: Get feeding program statistics
     */
    public function getFeedingStats() {
        $stats = [];
        
        // Today's attendance
        $today = date('Y-m-d');
        $stmt = $this->pdo->prepare("SELECT COUNT(*) as count FROM attendance WHERE DATE(SessionDate) = ?");
        $stmt->execute([$today]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $stats['today_attendance'] = $this->rowValue($row, 'count', 0);
        
        // This week's sessions
        $startOfWeek = date('Y-m-d', strtotime('monday this week'));
        $endOfWeek = date('Y-m-d', strtotime('sunday this week'));
        $stmt = $this->pdo->prepare("SELECT COUNT(*) as count FROM attendance WHERE SessionDate BETWEEN ? AND ?");
        $stmt->execute([$startOfWeek, $endOfWeek]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $stats['weekly_attendance'] = $this->rowValue($row, 'count', 0);
        
        // This month's sessions
        $startOfMonth = date('Y-m-01');
        $endOfMonth = date('Y-m-t');
        $stmt = $this->pdo->prepare("SELECT COUNT(*) as count FROM attendance WHERE SessionDate BETWEEN ? AND ?");
        $stmt->execute([$startOfMonth, $endOfMonth]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $stats['monthly_attendance'] = $this->rowValue($row, 'count', 0);
        
        return $stats;
    }

    /**
     * HZ-DASH-003: Get food stock status
     */
    public function getFoodStockStatus() {
        $stats = [];
        
        // Total items in stock
        $stmt = $this->pdo->query("SELECT COUNT(*) as count FROM foodstock WHERE QuantityRemaining > 0");
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $stats['items_in_stock'] = $this->rowValue($row, 'count', 0);
        
        // Low stock items (below threshold of 5 units)
        $stmt = $this->pdo->query("SELECT COUNT(*) as count FROM foodstock WHERE QuantityRemaining <= 5 AND QuantityRemaining > 0");
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $stats['low_stock_items'] = $this->rowValue($row, 'count', 0);
        
        // Expired items
        $today = date('Y-m-d');
        $stmt = $this->pdo->prepare("SELECT COUNT(*) as count FROM foodstock WHERE ExpiryDate < ? AND QuantityRemaining > 0");
        $stmt->execute([$today]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $stats['expired_items'] = $this->rowValue($row, 'count', 0);
        
        // Total items count (value metric not available in schema)
        $stmt = $this->pdo->query("SELECT COUNT(*) as total_items FROM foodstock WHERE QuantityRemaining > 0");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $stats['total_stock_value'] = (float)$this->rowValue($result, 'total_items', 0);
        
        return $stats;
    }

    /**
     * HZ-DASH-004: Get donation statistics
     */
    public function getDonationStats() {
        $stats = [];
        
        // Total donors
        $stmt = $this->pdo->query("SELECT COUNT(DISTINCT DonorID) as count FROM donations");
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $stats['total_donors'] = $this->rowValue($row, 'count', 0);
        
        // This month's donations
        $startOfMonth = date('Y-m-01');
        $endOfMonth = date('Y-m-t');
        $stmt = $this->pdo->prepare("SELECT COUNT(*) as count, SUM(Amount) as total FROM donations WHERE DonationDate BETWEEN ? AND ?");
        $stmt->execute([$startOfMonth, $endOfMonth]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $stats['monthly_donations_count'] = $this->rowValue($result, 'count', 0);
        $stats['monthly_donations_amount'] = (float)$this->rowValue($result, 'total', 0);
        
        // This year's donations
        $startOfYear = date('Y-01-01');
        $endOfYear = date('Y-12-31');
        $stmt = $this->pdo->prepare("SELECT SUM(Amount) as total FROM donations WHERE DonationDate BETWEEN ? AND ?");
        $stmt->execute([$startOfYear, $endOfYear]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $stats['yearly_donations'] = (float)$this->rowValue($result, 'total', 0);
        
        return $stats;
    }

    /**
     * HZ-DASH-005: Get volunteer scheduling statistics
     */
    public function getSchedulingStats() {
        $stats = [];
        
        // Total scheduled shifts
        $stmt = $this->pdo->query("SELECT COUNT(*) as count FROM volunteerschedules");
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $stats['total_shifts'] = $this->rowValue($row, 'count', 0);
        
        // Today's scheduled shifts
        $today = date('Y-m-d');
        $stmt = $this->pdo->prepare("SELECT COUNT(*) as count FROM volunteerschedules WHERE ScheduleDate = ?");
        $stmt->execute([$today]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $stats['today_shifts'] = $this->rowValue($row, 'count', 0);
        
        // Upcoming shifts (next 7 days)
        $futureDate = date('Y-m-d', strtotime('+7 days'));
        $stmt = $this->pdo->prepare("SELECT COUNT(*) as count FROM volunteerschedules WHERE ScheduleDate BETWEEN ? AND ? AND STATUS = 'scheduled'");
        $stmt->execute([$today, $futureDate]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $stats['upcoming_shifts'] = $this->rowValue($row, 'count', 0);
        
        // Completed schedules this month
        $startOfMonth = date('Y-m-01');
        $endOfMonth = date('Y-m-t');
        $stmt = $this->pdo->prepare("SELECT COUNT(*) as count, SUM(HoursWorked) as total_hours FROM volunteerschedules WHERE ScheduleDate BETWEEN ? AND ? AND STATUS = 'completed'");
        $stmt->execute([$startOfMonth, $endOfMonth]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $stats['completed_schedules'] = $this->rowValue($result, 'count', 0);
        $stats['volunteer_hours_month'] = (float)$this->rowValue($result, 'total_hours', 0);
        
        return $stats;
    }

    /**
     * HZ-DASH-006: Get recent activities
     */
    public function getRecentActivities($limit = 10) {
        $stmt = $this->pdo->prepare("
            SELECT ActivityID, UserID, ActivityType, Description, Timestamp, 
                   (SELECT Username FROM Users WHERE UserID = ActivityLog.UserID) as username
            FROM ActivityLog 
            ORDER BY Timestamp DESC 
            LIMIT ?
        ");
        $stmt->execute([$limit]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * HZ-DASH-007: Get top donors
     */
    public function getTopDonors($limit = 5) {
        $stmt = $this->pdo->prepare("
            SELECT DonorID, DonorName, SUM(Amount) as total_donated, COUNT(*) as donation_count
            FROM Donations
            GROUP BY DonorID, DonorName
            ORDER BY total_donated DESC
            LIMIT ?
        ");
        $stmt->execute([$limit]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * HZ-DASH-008: Get volunteer performance
     */
    public function getVolunteerPerformance($limit = 5) {
        $stmt = $this->pdo->prepare("
            SELECT v.VolunteerID, v.FullName, 
                   COUNT(vs.ScheduleID) as total_shifts,
                   SUM(CASE WHEN vs.Status = 'completed' THEN 1 ELSE 0 END) as completed_shifts,
                   SUM(vs.HoursWorked) as total_hours
            FROM Volunteers v
            LEFT JOIN VolunteerSchedules vs ON v.VolunteerID = vs.VolunteerID
            GROUP BY v.VolunteerID, v.FullName
            ORDER BY total_hours DESC
            LIMIT ?
        ");
        $stmt->execute([$limit]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * HZ-DASH-009: Get beneficiary trend (last 30 days)
     */
    public function getBeneficiaryTrend() {
        $stmt = $this->pdo->prepare("
            SELECT DATE(SessionDate) as date, COUNT(DISTINCT BeneficiaryID) as beneficiary_count
            FROM Attendance
            WHERE SessionDate >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
            GROUP BY DATE(SessionDate)
            ORDER BY date ASC
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * HZ-DASH-010: Get attendance by role distribution
     */
    public function getAttendanceByRole() {
        $stmt = $this->pdo->query("
            SELECT b.Role, COUNT(*) as count
            FROM Attendance a
            JOIN Beneficiaries b ON a.BeneficiaryID = b.BeneficiaryID
            WHERE a.SessionDate >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
            GROUP BY b.Role
            ORDER BY count DESC
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * HZ-DASH-011: Get donation sources distribution
     */
    public function getDonationSources() {
        $stmt = $this->pdo->query("
            SELECT DonationType, COUNT(*) as count, SUM(Amount) as total_amount
            FROM Donations
            WHERE DonationDate >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
            GROUP BY DonationType
            ORDER BY total_amount DESC
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * HZ-DASH-012: Get key performance indicators (KPIs)
     */
    public function getKPIs() {
        $kpis = [];
        
        // Average attendance per session
        $stmt = $this->pdo->query("
            SELECT AVG(daily_attendance) as avg_attendance
            FROM (
                SELECT COUNT(*) as daily_attendance
                FROM Attendance
                WHERE SessionDate >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
                GROUP BY SessionDate
            ) as daily_stats
        ");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $kpis['avg_attendance_per_session'] = round((float)$this->rowValue($result, 'avg_attendance', 0), 2);
        
        // Volunteer utilization rate
        $stmt = $this->pdo->query("
            SELECT 
                (SELECT COUNT(*) FROM VolunteerSchedules WHERE Status = 'completed') as completed,
                (SELECT COUNT(*) FROM VolunteerSchedules) as total
        ");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $total = $this->rowValue($result, 'total', 0);
        $completed = $this->rowValue($result, 'completed', 0);
        $kpis['volunteer_utilization_rate'] = $total > 0 ? round(($completed / $total) * 100, 1) : 0;
        
        // Donation fulfillment ratio
        $stmt = $this->pdo->query("
            SELECT 
                (SELECT SUM(Amount) FROM Donations WHERE DATE(DonationDate) = CURDATE()) as today_donations,
                (SELECT SUM(Amount) FROM Donations WHERE DATE(DonationDate) = DATE_SUB(CURDATE(), INTERVAL 1 DAY)) as yesterday_donations
        ");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $today = (float)$this->rowValue($result, 'today_donations', 0);
        $yesterday = (float)$this->rowValue($result, 'yesterday_donations', 0);
        $kpis['daily_donation_trend_percent'] = $yesterday > 0 ? round((($today - $yesterday) / $yesterday) * 100, 1) : 0;
        
        return $kpis;
    }
}
?>
