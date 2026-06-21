<?php
/**
 * Module: Attendance Tracking & Management
 * Purpose: Data layer for attendance recording and meal distribution tracking
 * Reference: Task 2b System Design Section 4.4 - Attendance Entity
 * Author: WIL Student
 * Entity: Attendance (MySQL table)
 */

class Attendance
{
    private $conn;
    private $table = "attendance";

    public function __construct($db)
    {
        $this->conn = $db;
    }

    /**
     * HZ-ATT-001
     * Purpose: Get all attendance records with optional filtering
     * Table: Attendance
     * Returns: Array of attendance records with beneficiary details
     * Pagination: Supports LIMIT and OFFSET
     */
    public function getAllAttendance($limit = 50, $offset = 0, $dateFilter = null, $statusFilter = null, $beneficiaryId = null)
    {
        $query = "SELECT a.AttendanceID, a.BeneficiaryID, a.MealSessionID, a.SessionDate, a.Status, a.Notes, a.CreatedAt,
                         ms.SessionType, ms.Location,
                         b.FirstName, b.LastName, b.Age, b.Status as BeneficiaryStatus
                  FROM " . $this->table . " a
                  LEFT JOIN beneficiaries b ON a.BeneficiaryID = b.BeneficiaryID
                  LEFT JOIN mealsession ms ON a.MealSessionID = ms.MealSessionID";

        $conditions = [];
        $params = [];

        if ($dateFilter) {
            $conditions[] = "a.SessionDate = :session_date";
            $params[':session_date'] = $dateFilter;
        }

        if ($statusFilter && in_array($statusFilter, ['present', 'absent', 'marked'])) {
            $conditions[] = "a.Status = :status";
            $params[':status'] = $statusFilter;
        }

        if ($beneficiaryId) {
            $conditions[] = "a.BeneficiaryID = :beneficiary_id";
            $params[':beneficiary_id'] = $beneficiaryId;
        }

        if (!empty($conditions)) {
            $query .= " WHERE " . implode(" AND ", $conditions);
        }

        $query .= " ORDER BY a.SessionDate DESC, a.CreatedAt DESC LIMIT :limit OFFSET :offset";

        $stmt = $this->conn->prepare($query);

        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }

        $stmt->bindParam(":limit", $limit, PDO::PARAM_INT);
        $stmt->bindParam(":offset", $offset, PDO::PARAM_INT);

        if ($stmt->execute()) {
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        return [];
    }

    /**
     * HZ-ATT-002
     * Purpose: Get single attendance record by AttendanceID
     * Table: Attendance
     * Returns: Attendance record with beneficiary and meal-session details or false
     */
    public function getAttendanceById($attendanceId)
    {
        $query = "SELECT a.AttendanceID, a.BeneficiaryID, a.MealSessionID, a.SessionDate, a.Status, a.Notes, a.CreatedAt,
                         ms.SessionType, ms.Location, ms.Notes as MealSessionNotes,
                         b.FirstName, b.LastName, b.Age, b.Gender, b.Phone, b.Email, b.Address, b.RegistrationDate, b.Status as BeneficiaryStatus
                  FROM " . $this->table . " a
                  LEFT JOIN beneficiaries b ON a.BeneficiaryID = b.BeneficiaryID
                  LEFT JOIN mealsession ms ON a.MealSessionID = ms.MealSessionID
                  WHERE a.AttendanceID = :attendance_id
                  LIMIT 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":attendance_id", $attendanceId);

        if ($stmt->execute()) {
            return $stmt->fetch(PDO::FETCH_ASSOC);
        }

        return false;
    }

    /**
     * HZ-ATT-003
     * Purpose: Record new attendance for a beneficiary
     * Table: Attendance
     * Returns: AttendanceID on success, false on failure
     * Validation: Beneficiary exists, date format, status valid
     */
    public function recordAttendance($beneficiaryId, $sessionDate, $status = 'present', $notes = null, $mealSessionId = null)
    {
        if (!$this->beneficiaryExists($beneficiaryId)) {
            throw new Exception("Beneficiary does not exist");
        }

        if (!in_array($status, ['present', 'absent', 'marked'])) {
            throw new Exception("Invalid attendance status");
        }

        if (!strtotime($sessionDate)) {
            throw new Exception("Invalid session date format");
        }

        if ($mealSessionId !== null) {
            $mealSession = $this->getMealSession($mealSessionId);
            if (!$mealSession) {
                throw new Exception("Meal session does not exist");
            }

            if ($mealSession['SessionDate'] !== $sessionDate) {
                throw new Exception("Meal session date does not match attendance date");
            }
        }

        if ($this->attendanceExists($beneficiaryId, $sessionDate)) {
            throw new Exception("Attendance already recorded for this beneficiary on this date");
        }

        $query = "INSERT INTO " . $this->table . "
                  (BeneficiaryID, MealSessionID, SessionDate, Status, Notes)
                  VALUES (:beneficiary_id, :meal_session_id, :session_date, :status, :notes)";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":beneficiary_id", $beneficiaryId);
        $stmt->bindParam(":meal_session_id", $mealSessionId);
        $stmt->bindParam(":session_date", $sessionDate);
        $stmt->bindParam(":status", $status);
        $stmt->bindParam(":notes", $notes);

        if ($stmt->execute()) {
            return $this->conn->lastInsertId();
        }

        return false;
    }

    /**
     * HZ-ATT-004
     * Purpose: Update attendance record
     * Table: Attendance
     * Returns: true on success, false on failure
     * Security: Parameterized query
     */
    public function updateAttendance($attendanceId, $beneficiaryId, $sessionDate, $status, $notes, $mealSessionId = null)
    {
        if (!$this->beneficiaryExists($beneficiaryId)) {
            throw new Exception("Beneficiary does not exist");
        }

        if (!in_array($status, ['present', 'absent', 'marked'])) {
            throw new Exception("Invalid attendance status");
        }

        if (!strtotime($sessionDate)) {
            throw new Exception("Invalid session date format");
        }

        if ($mealSessionId !== null) {
            $mealSession = $this->getMealSession($mealSessionId);
            if (!$mealSession) {
                throw new Exception("Meal session does not exist");
            }

            if ($mealSession['SessionDate'] !== $sessionDate) {
                throw new Exception("Meal session date does not match attendance date");
            }
        }

        $query = "UPDATE " . $this->table . "
                  SET BeneficiaryID = :beneficiary_id,
                      MealSessionID = :meal_session_id,
                      SessionDate = :session_date,
                      Status = :status,
                      Notes = :notes
                  WHERE AttendanceID = :attendance_id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":beneficiary_id", $beneficiaryId);
        $stmt->bindParam(":meal_session_id", $mealSessionId);
        $stmt->bindParam(":session_date", $sessionDate);
        $stmt->bindParam(":status", $status);
        $stmt->bindParam(":notes", $notes);
        $stmt->bindParam(":attendance_id", $attendanceId);

        return $stmt->execute();
    }

    /**
     * HZ-ATT-005
     * Purpose: Delete attendance record
     * Table: Attendance
     * Returns: true on success, false on failure
     */
    public function deleteAttendance($attendanceId)
    {
        $query = "DELETE FROM " . $this->table . " WHERE AttendanceID = :attendance_id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":attendance_id", $attendanceId);

        return $stmt->execute();
    }

    /**
     * HZ-ATT-006
     * Purpose: Get attendance statistics for a date range
     * Table: Attendance
     * Returns: Statistics array with counts by status
     */
    public function getAttendanceStats($startDate, $endDate)
    {
        $query = "SELECT
                     COUNT(DISTINCT COALESCE(MealSessionID, SessionDate)) as total_sessions,
                     SUM(CASE WHEN Status = 'present' THEN 1 ELSE 0 END) as present_count,
                     SUM(CASE WHEN Status = 'absent' THEN 1 ELSE 0 END) as absent_count,
                     SUM(CASE WHEN Status = 'marked' THEN 1 ELSE 0 END) as marked_count,
                     COUNT(DISTINCT BeneficiaryID) as unique_beneficiaries
                  FROM " . $this->table . "
                  WHERE SessionDate BETWEEN :start_date AND :end_date";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":start_date", $startDate);
        $stmt->bindParam(":end_date", $endDate);

        if ($stmt->execute()) {
            return $stmt->fetch(PDO::FETCH_ASSOC);
        }

        return [
            'total_sessions' => 0,
            'present_count' => 0,
            'absent_count' => 0,
            'marked_count' => 0,
            'unique_beneficiaries' => 0
        ];
    }

    /**
     * HZ-ATT-007
     * Purpose: Get attendance records for a specific beneficiary
     * Table: Attendance
     * Returns: Array of attendance records for the beneficiary
     */
    public function getBeneficiaryAttendance($beneficiaryId, $limit = 20)
    {
        $query = "SELECT a.AttendanceID, a.SessionDate, a.Status, a.Notes, a.CreatedAt,
                         ms.SessionType, ms.Location
                  FROM " . $this->table . " a
                  LEFT JOIN mealsession ms ON a.MealSessionID = ms.MealSessionID
                  WHERE a.BeneficiaryID = :beneficiary_id
                  ORDER BY a.SessionDate DESC
                  LIMIT :limit";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":beneficiary_id", $beneficiaryId);
        $stmt->bindParam(":limit", $limit, PDO::PARAM_INT);

        if ($stmt->execute()) {
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        return [];
    }

    /**
     * HZ-ATT-008
     * Purpose: Bulk record attendance for multiple beneficiaries
     * Table: Attendance
     * Returns: Array of results with success/failure status
     */
    public function bulkRecordAttendance($sessionDate, $attendanceData)
    {
        if (!strtotime($sessionDate)) {
            throw new Exception("Invalid session date format");
        }

        $results = [];
        $this->conn->beginTransaction();

        try {
            foreach ($attendanceData as $data) {
                $beneficiaryId = $data['beneficiary_id'];
                $status = isset($data['status']) ? $data['status'] : 'present';
                $notes = isset($data['notes']) ? $data['notes'] : null;
                $mealSessionId = isset($data['meal_session_id']) ? $data['meal_session_id'] : null;

                if ($this->attendanceExists($beneficiaryId, $sessionDate)) {
                    $results[] = [
                        'beneficiary_id' => $beneficiaryId,
                        'success' => false,
                        'message' => 'Attendance already recorded'
                    ];
                    continue;
                }

                $attendanceId = $this->recordAttendance($beneficiaryId, $sessionDate, $status, $notes, $mealSessionId);

                if ($attendanceId) {
                    $results[] = [
                        'beneficiary_id' => $beneficiaryId,
                        'attendance_id' => $attendanceId,
                        'success' => true,
                        'message' => 'Attendance recorded successfully'
                    ];
                } else {
                    $results[] = [
                        'beneficiary_id' => $beneficiaryId,
                        'success' => false,
                        'message' => 'Failed to record attendance'
                    ];
                }
            }

            $this->conn->commit();
            return $results;
        } catch (Exception $e) {
            $this->conn->rollBack();
            throw $e;
        }
    }

    /**
     * HZ-ATT-009
     * Purpose: Get attendance summary for a specific date
     * Table: Attendance
     * Returns: Summary with beneficiary details and attendance status
     */
    public function getDailyAttendanceSummary($sessionDate)
    {
        $query = "SELECT b.BeneficiaryID, b.FirstName, b.LastName, b.Age,
                         COALESCE(a.Status, 'not_recorded') as attendance_status,
                         a.AttendanceID, a.MealSessionID, a.Notes, a.CreatedAt,
                         ms.SessionType, ms.Location
                  FROM beneficiaries b
                  LEFT JOIN " . $this->table . " a ON b.BeneficiaryID = a.BeneficiaryID AND a.SessionDate = :session_date
                  LEFT JOIN mealsession ms ON a.MealSessionID = ms.MealSessionID
                  WHERE b.Status = 'active'
                  ORDER BY b.LastName, b.FirstName";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":session_date", $sessionDate);

        if ($stmt->execute()) {
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        return [];
    }

    /**
     * HZ-ATT-010
     * Purpose: Get attendance report for date range with beneficiary details
     * Table: Attendance
     * Returns: Detailed attendance report
     */
    public function getAttendanceReport($startDate, $endDate, $beneficiaryId = null, $mealSessionId = null, $statusFilter = null)
    {
        $query = "SELECT a.AttendanceID, a.MealSessionID, a.SessionDate, a.Status, a.Notes, a.CreatedAt,
                         ms.SessionType, ms.Location,
                         b.BeneficiaryID, b.FirstName, b.LastName, b.Age, b.Gender
                  FROM " . $this->table . " a
                  LEFT JOIN beneficiaries b ON a.BeneficiaryID = b.BeneficiaryID
                  LEFT JOIN mealsession ms ON a.MealSessionID = ms.MealSessionID
                  WHERE a.SessionDate BETWEEN :start_date AND :end_date";

        $params = [
            ':start_date' => $startDate,
            ':end_date' => $endDate
        ];

        if ($beneficiaryId) {
            $query .= " AND a.BeneficiaryID = :beneficiary_id";
            $params[':beneficiary_id'] = $beneficiaryId;
        }

        if ($mealSessionId) {
            $query .= " AND a.MealSessionID = :meal_session_id";
            $params[':meal_session_id'] = $mealSessionId;
        }

        if ($statusFilter && in_array($statusFilter, ['present', 'absent', 'marked'])) {
            $query .= " AND a.Status = :status";
            $params[':status'] = $statusFilter;
        }

        $query .= " ORDER BY a.SessionDate DESC, b.LastName, b.FirstName";

        $stmt = $this->conn->prepare($query);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }

        if ($stmt->execute()) {
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        return [];
    }

    /**
     * HZ-ATT-011
     * Purpose: Get beneficiary-level summary for the attendance report
     */
    public function getBeneficiaryAttendanceSummary($startDate, $endDate, $beneficiaryId = null, $mealSessionId = null, $statusFilter = null)
    {
        $query = "SELECT b.BeneficiaryID, b.FirstName, b.LastName,
                         COUNT(a.AttendanceID) as total_sessions,
                         SUM(CASE WHEN a.Status = 'present' THEN 1 ELSE 0 END) as present_count,
                         SUM(CASE WHEN a.Status = 'absent' THEN 1 ELSE 0 END) as absent_count,
                         SUM(CASE WHEN a.Status = 'marked' THEN 1 ELSE 0 END) as marked_count
                  FROM beneficiaries b
                  LEFT JOIN " . $this->table . " a
                    ON b.BeneficiaryID = a.BeneficiaryID
                   AND a.SessionDate BETWEEN :start_date AND :end_date";

        $params = [
            ':start_date' => $startDate,
            ':end_date' => $endDate
        ];

        if ($beneficiaryId) {
            $query .= " WHERE b.BeneficiaryID = :beneficiary_id";
            $params[':beneficiary_id'] = $beneficiaryId;
        } else {
            $query .= " WHERE 1=1";
        }

        if ($mealSessionId) {
            $query .= " AND a.MealSessionID = :meal_session_id";
            $params[':meal_session_id'] = $mealSessionId;
        }

        if ($statusFilter && in_array($statusFilter, ['present', 'absent', 'marked'])) {
            $query .= " AND a.Status = :status";
            $params[':status'] = $statusFilter;
        }

        $query .= " GROUP BY b.BeneficiaryID, b.FirstName, b.LastName
                    ORDER BY b.LastName, b.FirstName";

        $stmt = $this->conn->prepare($query);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }

        if ($stmt->execute()) {
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            foreach ($rows as &$row) {
                $total = (int)$row['total_sessions'];
                $present = (int)$row['present_count'];
                $row['attendance_rate'] = $total > 0 ? round(($present / $total) * 100, 1) : 0;
            }

            return $rows;
        }

        return [];
    }

    /**
     * HZ-ATT-012
     * Purpose: Get attendance record for a specific beneficiary on a specific date
     * Table: Attendance
     * Returns: Attendance record or false if not found
     */
    public function getAttendanceByBeneficiaryAndDate($beneficiaryId, $sessionDate)
    {
        $query = "SELECT AttendanceID, BeneficiaryID, MealSessionID, SessionDate, Status, Notes, CreatedAt
                  FROM " . $this->table . "
                  WHERE BeneficiaryID = :beneficiary_id AND SessionDate = :session_date
                  LIMIT 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":beneficiary_id", $beneficiaryId);
        $stmt->bindParam(":session_date", $sessionDate);

        if ($stmt->execute()) {
            return $stmt->fetch(PDO::FETCH_ASSOC);
        }

        return false;
    }

    /**
     * HZ-ATT-013
     * Purpose: Get number of meals served today (present count)
     * Table: Attendance
     * Returns: Integer count of meals served today
     */
    public function getMealsServedToday()
    {
        $today = date('Y-m-d');
        $query = "SELECT COUNT(*) as meals_served
                  FROM " . $this->table . "
                  WHERE SessionDate = :today AND Status = 'present'";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":today", $today);

        if ($stmt->execute()) {
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return (int)$result['meals_served'];
        }

        return 0;
    }

    /**
     * HZ-ATT-014
     * Purpose: Get recent attendance records with beneficiary details
     * Table: Attendance, Beneficiaries
     * Returns: Array of recent attendance records
     */
    public function getRecentAttendance($limit = 5)
    {
        $query = "SELECT a.AttendanceID, a.MealSessionID, a.SessionDate, a.Status, a.CreatedAt,
                         ms.SessionType, ms.Location,
                         b.FirstName, b.LastName, b.Age
                  FROM " . $this->table . " a
                  LEFT JOIN beneficiaries b ON a.BeneficiaryID = b.BeneficiaryID
                  LEFT JOIN mealsession ms ON a.MealSessionID = ms.MealSessionID
                  ORDER BY a.CreatedAt DESC
                  LIMIT :limit";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":limit", $limit, PDO::PARAM_INT);

        if ($stmt->execute()) {
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        return [];
    }

    /**
     * HZ-ATT-015
     * Purpose: Get attendance history with statistics for the last N days
     * Table: Attendance
     * Returns: Array of daily attendance summaries
     */
    public function getAttendanceHistory($days = 7)
    {
        $endDate = date('Y-m-d');
        $startDate = date('Y-m-d', strtotime("-" . ($days - 1) . " days"));

        $query = "SELECT
                     SessionDate,
                     COUNT(*) as total_registered,
                     SUM(CASE WHEN Status = 'present' THEN 1 ELSE 0 END) as present_count,
                     SUM(CASE WHEN Status = 'absent' THEN 1 ELSE 0 END) as absent_count,
                     ROUND((SUM(CASE WHEN Status = 'present' THEN 1 ELSE 0 END) / COUNT(*)) * 100, 1) as percentage
                  FROM " . $this->table . "
                  WHERE SessionDate BETWEEN :start_date AND :end_date
                  GROUP BY SessionDate
                  ORDER BY SessionDate DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":start_date", $startDate);
        $stmt->bindParam(":end_date", $endDate);

        if ($stmt->execute()) {
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        return [];
    }

    /**
     * HZ-ATT-016
     * Purpose: Check if beneficiary exists
     * Table: Beneficiaries
     * Returns: true if beneficiary exists, false otherwise
     */
    private function beneficiaryExists($beneficiaryId)
    {
        $query = "SELECT BeneficiaryID FROM beneficiaries WHERE BeneficiaryID = :beneficiary_id LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":beneficiary_id", $beneficiaryId);

        if ($stmt->execute()) {
            return $stmt->fetch(PDO::FETCH_ASSOC) !== false;
        }

        return false;
    }

    /**
     * HZ-ATT-017
     * Purpose: Check if attendance already exists for beneficiary on date
     * Table: Attendance
     * Returns: true if attendance exists, false otherwise
     */
    private function attendanceExists($beneficiaryId, $sessionDate)
    {
        $query = "SELECT AttendanceID FROM " . $this->table . "
                  WHERE BeneficiaryID = :beneficiary_id AND SessionDate = :session_date LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":beneficiary_id", $beneficiaryId);
        $stmt->bindParam(":session_date", $sessionDate);

        if ($stmt->execute()) {
            return $stmt->fetch(PDO::FETCH_ASSOC) !== false;
        }

        return false;
    }

    /**
     * HZ-ATT-018
     * Purpose: Get meal session row by identifier
     */
    private function getMealSession($mealSessionId)
    {
        $query = "SELECT * FROM mealsession WHERE MealSessionID = :meal_session_id LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':meal_session_id', $mealSessionId, PDO::PARAM_INT);

        if ($stmt->execute()) {
            return $stmt->fetch(PDO::FETCH_ASSOC);
        }

        return false;
    }
}
?>
