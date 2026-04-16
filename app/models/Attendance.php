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
    private $table = "Attendance";

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
        $query = "SELECT a.AttendanceID, a.BeneficiaryID, a.SessionDate, a.Status, a.Notes, a.CreatedAt,
                         b.FirstName, b.LastName, b.Age, b.Status as BeneficiaryStatus
                  FROM " . $this->table . " a
                  LEFT JOIN Beneficiaries b ON a.BeneficiaryID = b.BeneficiaryID";

        $conditions = [];
        $params = [];

        // Date filter
        if ($dateFilter) {
            $conditions[] = "a.SessionDate = :session_date";
            $params[':session_date'] = $dateFilter;
        }

        // Status filter
        if ($statusFilter && in_array($statusFilter, ['present', 'absent', 'marked'])) {
            $conditions[] = "a.Status = :status";
            $params[':status'] = $statusFilter;
        }

        // Beneficiary filter
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
     * Returns: Attendance record with beneficiary details or false
     */
    public function getAttendanceById($attendanceId)
    {
        $query = "SELECT a.AttendanceID, a.BeneficiaryID, a.SessionDate, a.Status, a.Notes, a.CreatedAt,
                         b.FirstName, b.LastName, b.Age, b.Gender, b.Phone, b.Email, b.Address, b.RegistrationDate, b.Status as BeneficiaryStatus
                  FROM " . $this->table . " a
                  LEFT JOIN Beneficiaries b ON a.BeneficiaryID = b.BeneficiaryID
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
    public function recordAttendance($beneficiaryId, $sessionDate, $status = 'present', $notes = null)
    {
        // Validation: Beneficiary exists
        if (!$this->beneficiaryExists($beneficiaryId)) {
            throw new Exception("Beneficiary does not exist");
        }

        // Validation: Status must be valid
        if (!in_array($status, ['present', 'absent', 'marked'])) {
            throw new Exception("Invalid attendance status");
        }

        // Validation: Session date format
        if (!strtotime($sessionDate)) {
            throw new Exception("Invalid session date format");
        }

        // Check if attendance already exists for this beneficiary on this date
        if ($this->attendanceExists($beneficiaryId, $sessionDate)) {
            throw new Exception("Attendance already recorded for this beneficiary on this date");
        }

        $query = "INSERT INTO " . $this->table . "
                  (BeneficiaryID, SessionDate, Status, Notes)
                  VALUES (:beneficiary_id, :session_date, :status, :notes)";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(":beneficiary_id", $beneficiaryId);
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
    public function updateAttendance($attendanceId, $beneficiaryId, $sessionDate, $status, $notes)
    {
        // Validation: Beneficiary exists
        if (!$this->beneficiaryExists($beneficiaryId)) {
            throw new Exception("Beneficiary does not exist");
        }

        // Validation: Status must be valid
        if (!in_array($status, ['present', 'absent', 'marked'])) {
            throw new Exception("Invalid attendance status");
        }

        // Validation: Session date format
        if (!strtotime($sessionDate)) {
            throw new Exception("Invalid session date format");
        }

        $query = "UPDATE " . $this->table . "
                  SET BeneficiaryID = :beneficiary_id,
                      SessionDate = :session_date,
                      Status = :status,
                      Notes = :notes
                  WHERE AttendanceID = :attendance_id";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(":beneficiary_id", $beneficiaryId);
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
                     COUNT(*) as total_sessions,
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
        $query = "SELECT AttendanceID, SessionDate, Status, Notes, CreatedAt
                  FROM " . $this->table . "
                  WHERE BeneficiaryID = :beneficiary_id
                  ORDER BY SessionDate DESC
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
        // Validation: Session date format
        if (!strtotime($sessionDate)) {
            throw new Exception("Invalid session date format");
        }

        $results = [];
        $this->conn->beginTransaction();

        try {
            foreach ($attendanceData as $data) {
                $beneficiaryId = $data['beneficiary_id'];
                $status = $data['status'] ?? 'present';
                $notes = $data['notes'] ?? null;

                // Check if attendance already exists
                if ($this->attendanceExists($beneficiaryId, $sessionDate)) {
                    $results[] = [
                        'beneficiary_id' => $beneficiaryId,
                        'success' => false,
                        'message' => 'Attendance already recorded'
                    ];
                    continue;
                }

                $attendanceId = $this->recordAttendance($beneficiaryId, $sessionDate, $status, $notes);

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
                         a.AttendanceID, a.Notes, a.CreatedAt
                  FROM Beneficiaries b
                  LEFT JOIN " . $this->table . " a ON b.BeneficiaryID = a.BeneficiaryID AND a.SessionDate = :session_date
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
    public function getAttendanceReport($startDate, $endDate, $beneficiaryId = null)
    {
        $query = "SELECT a.AttendanceID, a.SessionDate, a.Status, a.Notes, a.CreatedAt,
                         b.BeneficiaryID, b.FirstName, b.LastName, b.Age, b.Gender
                  FROM " . $this->table . " a
                  LEFT JOIN Beneficiaries b ON a.BeneficiaryID = b.BeneficiaryID
                  WHERE a.SessionDate BETWEEN :start_date AND :end_date";

        $params = [
            ':start_date' => $startDate,
            ':end_date' => $endDate
        ];

        if ($beneficiaryId) {
            $query .= " AND a.BeneficiaryID = :beneficiary_id";
            $params[':beneficiary_id'] = $beneficiaryId;
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
     * Purpose: Check if beneficiary exists
     * Table: Beneficiaries
     * Returns: true if beneficiary exists, false otherwise
     */
    private function beneficiaryExists($beneficiaryId)
    {
        $query = "SELECT BeneficiaryID FROM Beneficiaries WHERE BeneficiaryID = :beneficiary_id LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":beneficiary_id", $beneficiaryId);

        if ($stmt->execute()) {
            return $stmt->fetch(PDO::FETCH_ASSOC) !== false;
        }

        return false;
    }

    /**
     * HZ-ATT-012
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
}
