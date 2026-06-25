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

    public function __construct($db = null) {
        if ($db instanceof PDO) {
            $this->pdo = $db;
        } else {
            $this->pdo = getConnection();
        }
    }

    /**
     * HZ-DASH-001: Get all system statistics for dashboard overview
     */
    public function getSystemStats() {
        $stats = [];

        // Active beneficiaries
        $stmt = $this->pdo->query("SELECT COUNT(*) as count FROM Beneficiaries WHERE Status = 'active'");
        $stats['active_beneficiaries'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'] ?? 0;

        // Total beneficiaries
        $stmt = $this->pdo->query("SELECT COUNT(*) as count FROM Beneficiaries");
        $stats['total_beneficiaries'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'] ?? 0;

        // Available volunteers (AvailabilityStatus = 'available')
        $stmt = $this->pdo->query("SELECT COUNT(*) as count FROM Volunteers WHERE AvailabilityStatus = 'available'");
        $stats['active_volunteers'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'] ?? 0;

        // Total volunteers
        $stmt = $this->pdo->query("SELECT COUNT(*) as count FROM Volunteers");
        $stats['total_volunteers'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'] ?? 0;

        // Total system users
        $stmt = $this->pdo->query("SELECT COUNT(*) as count FROM Users WHERE Status = 'active'");
        $stats['system_users'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'] ?? 0;

        return $stats;
    }

    /**
     * HZ-DASH-002: Get feeding program statistics
     */
    public function getFeedingStats() {
        $stats = [];

        // Today's attendance
        $today = date('Y-m-d');
        $stmt = $this->pdo->prepare("SELECT COUNT(*) as count FROM Attendance WHERE SessionDate = ?");
        $stmt->execute([$today]);
        $stats['today_attendance'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'] ?? 0;

        // This week's sessions
        $startOfWeek = date('Y-m-d', strtotime('monday this week'));
        $endOfWeek = date('Y-m-d', strtotime('sunday this week'));
        $stmt = $this->pdo->prepare("SELECT COUNT(*) as count FROM Attendance WHERE SessionDate BETWEEN ? AND ?");
        $stmt->execute([$startOfWeek, $endOfWeek]);
        $stats['weekly_attendance'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'] ?? 0;

        // This month's sessions
        $startOfMonth = date('Y-m-01');
        $endOfMonth = date('Y-m-t');
        $stmt = $this->pdo->prepare("SELECT COUNT(*) as count FROM Attendance WHERE SessionDate BETWEEN ? AND ?");
        $stmt->execute([$startOfMonth, $endOfMonth]);
        $stats['monthly_attendance'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'] ?? 0;

        return $stats;
    }

    /**
     * HZ-DASH-003: Get food stock status
     */
    public function getFoodStockStatus() {
        $stats = [];

        // Total items in stock
        $stmt = $this->pdo->query("SELECT COUNT(*) as count FROM FoodStock WHERE Quantity > 0");
        $stats['items_in_stock'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'] ?? 0;

        // Low stock items (Quantity <= 5)
        $stmt = $this->pdo->query("SELECT COUNT(*) as count FROM FoodStock WHERE Quantity > 0 AND Quantity <= 5");
        $stats['low_stock_items'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'] ?? 0;

        // Expired items
        $today = date('Y-m-d');
        $stmt = $this->pdo->prepare("SELECT COUNT(*) as count FROM FoodStock WHERE ExpiryDate < ? AND Quantity > 0");
        $stmt->execute([$today]);
        $stats['expired_items'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'] ?? 0;

        // Total stock value (not available without unit cost)
        $stats['total_stock_value'] = 0;

        return $stats;
    }

    /**
     * HZ-DASH-004: Get donation statistics
     */
    public function getDonationStats() {
        $stats = [];

        // Total donors
        $stmt = $this->pdo->query("SELECT COUNT(DISTINCT DonorName) as count FROM Donations");
        $stats['total_donors'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'] ?? 0;

        // This month's donations
        $startOfMonth = date('Y-m-01');
        $endOfMonth = date('Y-m-t');
        $stmt = $this->pdo->prepare("SELECT COUNT(*) as count, SUM(CASE WHEN DonationType = 'cash' THEN Amount ELSE 0 END) as total FROM Donations WHERE DonationDate BETWEEN ? AND ?");
        $stmt->execute([$startOfMonth, $endOfMonth]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $stats['monthly_donations_count'] = $result['count'] ?? 0;
        $stats['monthly_donations_amount'] = (float)($result['total'] ?? 0);

        // This year's donations
        $startOfYear = date('Y-01-01');
        $endOfYear = date('Y-12-31');
        $stmt = $this->pdo->prepare("SELECT SUM(CASE WHEN DonationType = 'cash' THEN Amount ELSE 0 END) as total FROM Donations WHERE DonationDate BETWEEN ? AND ?");
        $stmt->execute([$startOfYear, $endOfYear]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $stats['yearly_donations'] = (float)($result['total'] ?? 0);

        return $stats;
    }

    /**
     * HZ-DASH-005: Get volunteer scheduling statistics
     */
    public function getSchedulingStats() {
        $stats = [];

        // Total scheduled shifts
        $stmt = $this->pdo->query("SELECT COUNT(*) as count FROM VolunteerSchedules");
        $stats['total_shifts'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'] ?? 0;

        // Today's scheduled shifts
        $today = date('Y-m-d');
        $stmt = $this->pdo->prepare("SELECT COUNT(*) as count FROM VolunteerSchedules WHERE ScheduleDate = ?");
        $stmt->execute([$today]);
        $stats['today_shifts'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'] ?? 0;

        // Upcoming shifts (next 7 days)
        $futureDate = date('Y-m-d', strtotime('+7 days'));
        $stmt = $this->pdo->prepare("SELECT COUNT(*) as count FROM VolunteerSchedules WHERE ScheduleDate BETWEEN ? AND ? AND Status = 'scheduled'");
        $stmt->execute([$today, $futureDate]);
        $stats['upcoming_shifts'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'] ?? 0;

        // Completed schedules this month
        $startOfMonth = date('Y-m-01');
        $endOfMonth = date('Y-m-t');
        $stmt = $this->pdo->prepare("SELECT COUNT(*) as count FROM VolunteerSchedules WHERE ScheduleDate BETWEEN ? AND ? AND Status = 'completed'");
        $stmt->execute([$startOfMonth, $endOfMonth]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $stats['completed_schedules'] = $result['count'] ?? 0;
        $stats['volunteer_hours_month'] = 0;

        return $stats;
    }

    /**
     * HZ-DASH-006: Get recent activities
     */
    public function getRecentActivities($limit = 10) {
        $stmt = $this->pdo->prepare("
            SELECT ActivityID, UserID, Action, Details, Timestamp,
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
            SELECT DonorName, SUM(CASE WHEN DonationType = 'cash' THEN Amount ELSE 0 END) as total_donated, COUNT(*) as donation_count
            FROM Donations
            GROUP BY DonorName
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
            SELECT v.VolunteerID,
                   u.FullName,
                   COUNT(vs.ScheduleID) as total_shifts,
                   SUM(CASE WHEN vs.Status = 'completed' THEN 1 ELSE 0 END) as completed_shifts
            FROM Volunteers v
            LEFT JOIN Users u ON v.UserID = u.UserID
            LEFT JOIN VolunteerSchedules vs ON v.VolunteerID = vs.VolunteerID
            GROUP BY v.VolunteerID, u.FullName
            ORDER BY completed_shifts DESC
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
            SELECT SessionDate as date, COUNT(DISTINCT BeneficiaryID) as beneficiary_count
            FROM Attendance
            WHERE SessionDate >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
            GROUP BY SessionDate
            ORDER BY date ASC
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * HZ-DASH-010: Get attendance by role distribution
     * Note: Beneficiaries table doesn't have a Role column, so this returns by Status instead
     */
    public function getAttendanceByStatus() {
        $stmt = $this->pdo->query("
            SELECT b.Status, COUNT(*) as count
            FROM Attendance a
            JOIN Beneficiaries b ON a.BeneficiaryID = b.BeneficiaryID
            WHERE a.SessionDate >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
            GROUP BY b.Status
            ORDER BY count DESC
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * HZ-DASH-011: Get donation sources distribution
     */
    public function getDonationSources() {
        $stmt = $this->pdo->query("
            SELECT DonationType, COUNT(*) as count, SUM(CASE WHEN DonationType = 'cash' THEN Amount ELSE 0 END) as total_amount
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
        $kpis['avg_attendance_per_session'] = round((float)($result['avg_attendance'] ?? 0), 2);

        // Volunteer utilization rate
        $stmt = $this->pdo->query("
            SELECT
                (SELECT COUNT(*) FROM VolunteerSchedules WHERE Status = 'completed') as completed,
                (SELECT COUNT(*) FROM VolunteerSchedules) as total
        ");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $total = $result['total'] ?? 1;
        $completed = $result['completed'] ?? 0;
        $kpis['volunteer_utilization_rate'] = round(($completed / $total) * 100, 1);

        // Donation trend
        $stmt = $this->pdo->query("
            SELECT
                (SELECT SUM(CASE WHEN DonationType = 'cash' THEN Amount ELSE 0 END) FROM Donations WHERE DonationDate = CURDATE()) as today_donations,
                (SELECT SUM(CASE WHEN DonationType = 'cash' THEN Amount ELSE 0 END) FROM Donations WHERE DonationDate = DATE_SUB(CURDATE(), INTERVAL 1 DAY)) as yesterday_donations
        ");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $today = (float)($result['today_donations'] ?? 0);
        $yesterday = (float)($result['yesterday_donations'] ?? 0);
        $kpis['daily_donation_trend_percent'] = $yesterday > 0 ? round((($today - $yesterday) / $yesterday) * 100, 1) : 0;

        return $kpis;
    }
}
?>
