<?php
/**
 * Module: Volunteer Management & Scheduling
 * Purpose: Data layer for volunteer CRUD operations and schedule management
 * Reference: Task 2b System Design Section 4.2 - Volunteer Entity
 * Author: WIL Student
 * Entity: Volunteers (MySQL table)
 */

class Volunteer
{
    private $conn;
    private $table = "Volunteers";

    public function __construct($db)
    {
        $this->conn = $db;
    }

    /**
     * HZ-VOL-001
     * Purpose: Get all volunteers with optional filtering
     * Table: Volunteers (joined with Users)
     * Returns: Array of volunteer records
     * Pagination: Supports LIMIT and OFFSET
     */
    public function getAllVolunteers($limit = 10, $offset = 0, $status = null, $searchTerm = null)
    {
        $query = "SELECT v.VolunteerID, v.UserID, u.Username, u.Email, u.FullName,
                         SUBSTRING_INDEX(COALESCE(u.FullName, ''), ' ', 1) AS FirstName,
                         SUBSTRING_INDEX(COALESCE(u.FullName, ''), ' ', -1) AS LastName,
                         u.Phone, v.Skills, v.Address, v.Notes, v.AvailabilityStatus,
                         v.Status AS VolunteerStatus, u.Status AS UserStatus, v.CreatedAt
                  FROM " . $this->table . " v
                  INNER JOIN Users u ON v.UserID = u.UserID
                   WHERE u.Status = 'active' AND v.Status = 'approved'";

        // Filter by availability status if provided
        if ($status && in_array($status, ['available', 'unavailable', 'on_leave'])) {
            $query .= " AND v.AvailabilityStatus = :status";
        }

        if (!empty($searchTerm)) {
            $query .= " AND (u.FullName LIKE :search1 OR u.Phone LIKE :search2 OR u.Email LIKE :search3 OR v.Skills LIKE :search4 OR v.Address LIKE :search5)";
        }

        $query .= " ORDER BY v.CreatedAt DESC LIMIT :limit OFFSET :offset";

        $stmt = $this->conn->prepare($query);

        if ($status && in_array($status, ['available', 'unavailable', 'on_leave'])) {
            $stmt->bindParam(":status", $status);
        }

        if (!empty($searchTerm)) {
            $search = "%{$searchTerm}%";
            $stmt->bindParam(":search1", $search);
            $stmt->bindParam(":search2", $search);
            $stmt->bindParam(":search3", $search);
            $stmt->bindParam(":search4", $search);
            $stmt->bindParam(":search5", $search);
        }

        $stmt->bindParam(":limit", $limit, PDO::PARAM_INT);
        $stmt->bindParam(":offset", $offset, PDO::PARAM_INT);

        if ($stmt->execute()) {
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        return [];
    }

    /**
     * HZ-VOL-001A
     * Purpose: Get volunteer count for pagination/filtering
     */
    public function getVolunteerCount($status = null, $searchTerm = null)
    {
        $query = "SELECT COUNT(*) AS total
                  FROM " . $this->table . " v
                  INNER JOIN Users u ON v.UserID = u.UserID
                  WHERE u.Status = 'active' AND v.Status = 'approved'";

        if ($status && in_array($status, ['available', 'unavailable', 'on_leave'])) {
            $query .= " AND v.AvailabilityStatus = :status";
        }

        if (!empty($searchTerm)) {
            $query .= " AND (u.FullName LIKE :search1 OR u.Phone LIKE :search2 OR u.Email LIKE :search3 OR v.Skills LIKE :search4 OR v.Address LIKE :search5)";
        }

        $stmt = $this->conn->prepare($query);

        if ($status && in_array($status, ['available', 'unavailable', 'on_leave'])) {
            $stmt->bindParam(":status", $status);
        }

        if (!empty($searchTerm)) {
            $search = "%{$searchTerm}%";
            $stmt->bindParam(":search1", $search);
            $stmt->bindParam(":search2", $search);
            $stmt->bindParam(":search3", $search);
            $stmt->bindParam(":search4", $search);
            $stmt->bindParam(":search5", $search);
        }

        if ($stmt->execute()) {
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return (int)($result['total'] ?? 0);
        }

        return 0;
    }

    /**
     * HZ-VOL-001B
     * Purpose: Get all matching volunteers for export
     */
    public function getVolunteersForExport($status = null, $searchTerm = null)
    {
        $query = "SELECT v.VolunteerID, u.Username, u.Email, u.FullName,
                         u.Phone, v.Skills, v.Address, v.Notes, v.AvailabilityStatus, v.Status AS VolunteerStatus, v.CreatedAt
                  FROM " . $this->table . " v
                  INNER JOIN Users u ON v.UserID = u.UserID
                  WHERE u.Status = 'active' AND v.Status = 'approved'";

        if ($status && in_array($status, ['available', 'unavailable', 'on_leave'])) {
            $query .= " AND v.AvailabilityStatus = :status";
        }

        if (!empty($searchTerm)) {
            $query .= " AND (u.FullName LIKE :search1 OR u.Phone LIKE :search2 OR u.Email LIKE :search3 OR v.Skills LIKE :search4 OR v.Address LIKE :search5)";
        }

        $query .= " ORDER BY v.CreatedAt DESC";

        $stmt = $this->conn->prepare($query);

        if ($status && in_array($status, ['available', 'unavailable', 'on_leave'])) {
            $stmt->bindParam(":status", $status);
        }

        if (!empty($searchTerm)) {
            $search = "%{$searchTerm}%";
            $stmt->bindParam(":search1", $search);
            $stmt->bindParam(":search2", $search);
            $stmt->bindParam(":search3", $search);
            $stmt->bindParam(":search4", $search);
            $stmt->bindParam(":search5", $search);
        }

        if ($stmt->execute()) {
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        return [];
    }

    /**
     * HZ-VOL-002
     * Purpose: Get single volunteer by VolunteerID
     * Table: Volunteers, Users
     * Returns: Volunteer record with user data or false
     */
    public function getVolunteerById($volunteerId)
    {
        $query = "SELECT v.VolunteerID, v.UserID, u.Username, u.Email, u.Role, u.FullName,
                         SUBSTRING_INDEX(COALESCE(u.FullName, ''), ' ', 1) AS FirstName,
                         SUBSTRING_INDEX(COALESCE(u.FullName, ''), ' ', -1) AS LastName,
                         u.Phone, v.Skills, v.Address, v.Notes, v.AvailabilityStatus,
                         v.Status AS VolunteerStatus, u.Status AS UserStatus, v.CreatedAt
                  FROM " . $this->table . " v
                  INNER JOIN Users u ON v.UserID = u.UserID
                  WHERE v.VolunteerID = :volunteer_id
                  LIMIT 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":volunteer_id", $volunteerId);

        if ($stmt->execute()) {
            return $stmt->fetch(PDO::FETCH_ASSOC);
        }

        return false;
    }

    /**
     * HZ-VOL-003
     * Purpose: Get volunteer profile by UserID
     * Table: Volunteers, Users
     * Returns: Volunteer record or false
     * Security: Parameterized query
     */
    public function getVolunteerByUserId($userId)
    {
        $query = "SELECT v.VolunteerID, v.UserID, u.Username, u.Email, u.FullName,
                         SUBSTRING_INDEX(COALESCE(u.FullName, ''), ' ', 1) AS FirstName,
                         SUBSTRING_INDEX(COALESCE(u.FullName, ''), ' ', -1) AS LastName,
                         u.Phone, v.Skills, v.Address, v.Notes, v.AvailabilityStatus,
                         v.Status AS VolunteerStatus, u.Status AS UserStatus, v.CreatedAt
                  FROM " . $this->table . " v
                  INNER JOIN Users u ON v.UserID = u.UserID
                  WHERE v.UserID = :user_id
                  LIMIT 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":user_id", $userId);

        if ($stmt->execute()) {
            return $stmt->fetch(PDO::FETCH_ASSOC);
        }

        return false;
    }

    /**
     * HZ-VOL-004
     * Purpose: Create new volunteer profile linked to user account
     * Table: Volunteers
     * Returns: VolunteerID on success, false on failure
     */
    public function createVolunteer($userId, $firstName, $lastName, $phone, $address = null, $profileStatus = 'pending', $availabilityStatus = 'unavailable')
    {
        $fullName = trim($firstName . ' ' . $lastName);

        // Validation: Check if volunteer already exists for this user
        if ($this->getVolunteerByUserId($userId)) {
            throw new Exception("Volunteer profile already exists for this user");
        }

        if (empty($fullName)) {
            throw new Exception("Full name is required");
        }

        // Validation: Phone format (basic)
        if (!empty($phone) && !preg_match('/^[0-9\s\-\+\(\)]{10,}$/', $phone)) {
            throw new Exception("Invalid phone number format");
        }

        $ownTransaction = !$this->conn->inTransaction();
        if ($ownTransaction) {
            $this->conn->beginTransaction();
        }

        try {
            $userUpdate = "UPDATE Users SET FullName = :full_name, Phone = :phone WHERE UserID = :user_id";
            $userStmt = $this->conn->prepare($userUpdate);
            $userStmt->bindParam(":full_name", $fullName);
            $userStmt->bindParam(":phone", $phone);
            $userStmt->bindParam(":user_id", $userId);
            $userStmt->execute();

            $query = "INSERT INTO " . $this->table . " 
                      (UserID, Skills, Address, Notes, AvailabilityStatus, Status) 
                      VALUES (:user_id, :skills, :address, :notes, :availability_status, :profile_status)";

            $stmt = $this->conn->prepare($query);
            $skills = null;
            $notes = null;
            $stmt->bindParam(":user_id", $userId);
            $stmt->bindParam(":skills", $skills);
            $stmt->bindParam(":address", $address);
            $stmt->bindParam(":notes", $notes);
            $stmt->bindParam(":availability_status", $availabilityStatus);
            $stmt->bindParam(":profile_status", $profileStatus);

            if ($stmt->execute()) {
                if ($ownTransaction) {
                    $this->conn->commit();
                }
                return $this->conn->lastInsertId();
            }

            if ($ownTransaction) {
                $this->conn->rollBack();
            }
            return false;
        } catch (Exception $e) {
            if ($ownTransaction && $this->conn->inTransaction()) {
                $this->conn->rollBack();
            }
            throw $e;
        }
    }

    /**
     * HZ-VOL-005
     * Purpose: Update volunteer profile information
     * Table: Volunteers
     * Returns: true on success, false on failure
     * Security: Parameterized query
     */
    public function updateVolunteer($volunteerId, $firstName, $lastName, $phone, $address, $status)
    {
        $fullName = trim($firstName . ' ' . $lastName);

        // Validation: Phone format
        if (!empty($phone) && !preg_match('/^[0-9\s\-\+\(\)]{10,}$/', $phone)) {
            throw new Exception("Invalid phone number format");
        }

        // Validation: Status must be valid
        if (!in_array($status, ['available', 'unavailable', 'on_leave'])) {
            throw new Exception("Invalid availability status");
        }

        $volunteer = $this->getVolunteerById($volunteerId);
        if (!$volunteer) {
            throw new Exception("Volunteer not found");
        }

        $ownTransaction = !$this->conn->inTransaction();
        if ($ownTransaction) {
            $this->conn->beginTransaction();
        }

        try {
            $userQuery = "UPDATE Users SET FullName = :full_name, Phone = :phone WHERE UserID = :user_id";
            $userStmt = $this->conn->prepare($userQuery);
            $userStmt->bindParam(":full_name", $fullName);
            $userStmt->bindParam(":phone", $phone);
            $userStmt->bindParam(":user_id", $volunteer['UserID']);
            $userStmt->execute();

            $query = "UPDATE " . $this->table . " 
                      SET Address = :address, 
                          AvailabilityStatus = :status 
                      WHERE VolunteerID = :volunteer_id";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":address", $address);
            $stmt->bindParam(":status", $status);
            $stmt->bindParam(":volunteer_id", $volunteerId);

            $result = $stmt->execute();
            if ($result) {
                if ($ownTransaction) {
                    $this->conn->commit();
                }
            } else {
                if ($ownTransaction) {
                    $this->conn->rollBack();
                }
            }
            return $result;
        } catch (Exception $e) {
            if ($ownTransaction && $this->conn->inTransaction()) {
                $this->conn->rollBack();
            }
            throw $e;
        }
    }

    /**
     * HZ-VOL-006
     * Purpose: Update volunteer availability status
     * Table: Volunteers
     * Returns: true on success, false on failure
     * Status: available, unavailable, on_leave
     */
    public function updateAvailabilityStatus($volunteerId, $status)
    {
        if (!in_array($status, ['available', 'unavailable', 'on_leave'])) {
            throw new Exception("Invalid availability status");
        }

        $query = "UPDATE " . $this->table . " 
                  SET AvailabilityStatus = :status 
                  WHERE VolunteerID = :volunteer_id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":status", $status);
        $stmt->bindParam(":volunteer_id", $volunteerId);

        return $stmt->execute();
    }

    /**
     * HZ-VOL-007
     * Purpose: Get count of volunteers by availability status
     * Table: Volunteers
     * Returns: Array with status counts
     */
    public function getVolunteerCountByStatus()
    {
        $query = "SELECT AvailabilityStatus, COUNT(*) as count 
                  FROM " . $this->table . " 
                  GROUP BY AvailabilityStatus";

        $stmt = $this->conn->prepare($query);

        if ($stmt->execute()) {
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $counts = ['available' => 0, 'unavailable' => 0, 'on_leave' => 0];

            foreach ($results as $row) {
                $counts[$row['AvailabilityStatus']] = $row['count'];
            }

            return $counts;
        }

        return ['available' => 0, 'unavailable' => 0, 'on_leave' => 0];
    }

    /**
     * HZ-VOL-008
     * Purpose: Get available volunteers (for assignment)
     * Table: Volunteers
     * Returns: Array of available volunteers
     */
    public function getAvailableVolunteers()
    {
        $query = "SELECT v.VolunteerID, v.UserID, u.Username, u.FullName,
                         u.Phone
                  FROM " . $this->table . " v
                  INNER JOIN Users u ON v.UserID = u.UserID
                   WHERE v.AvailabilityStatus = 'available'
                   AND u.Status = 'active'
                   AND v.Status = 'approved'
                   ORDER BY u.FullName ASC";

        $stmt = $this->conn->prepare($query);

        if ($stmt->execute()) {
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        return [];
    }

    /**
     * HZ-VOL-009
     * Purpose: Search volunteers by name or phone
     * Table: Volunteers
     * Returns: Array of matching volunteer records
     */
    public function searchVolunteers($searchTerm)
    {
        $searchTerm = "%{$searchTerm}%";

        $query = "SELECT v.VolunteerID, v.UserID, u.Username, u.Email, u.FullName,
                         SUBSTRING_INDEX(COALESCE(u.FullName, ''), ' ', 1) AS FirstName,
                         SUBSTRING_INDEX(COALESCE(u.FullName, ''), ' ', -1) AS LastName,
                         u.Phone, v.Skills, v.Address, v.Notes, v.AvailabilityStatus, v.Status AS VolunteerStatus, v.CreatedAt
                  FROM " . $this->table . " v
                  INNER JOIN Users u ON v.UserID = u.UserID
                   WHERE (u.FullName LIKE :search1 OR u.Phone LIKE :search2 OR u.Email LIKE :search3 OR v.Skills LIKE :search4 OR v.Address LIKE :search5)
                   AND u.Status = 'active'
                   AND v.Status = 'approved'
                   ORDER BY u.FullName ASC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":search1", $searchTerm);
        $stmt->bindParam(":search2", $searchTerm);
        $stmt->bindParam(":search3", $searchTerm);
        $stmt->bindParam(":search4", $searchTerm);
        $stmt->bindParam(":search5", $searchTerm);

        if ($stmt->execute()) {
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        return [];
    }

    /**
     * HZ-VOL-010
     * Purpose: Delete volunteer profile (soft delete via user deactivation)
     * Table: Users
     * Returns: true on success, false on failure
     * Note: Uses soft delete - deactivates associated user account
     */
    public function deleteVolunteer($volunteerId)
    {
        // Get the volunteer to find associated user
        $volunteer = $this->getVolunteerById($volunteerId);

        if (!$volunteer) {
            throw new Exception("Volunteer not found");
        }

        $ownTransaction = !$this->conn->inTransaction();
        if ($ownTransaction) {
            $this->conn->beginTransaction();
        }

        try {
            $query = "UPDATE Users SET Status = 'inactive' WHERE UserID = :user_id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":user_id", $volunteer['UserID']);
            $stmt->execute();

            $volunteerQuery = "UPDATE " . $this->table . " SET Status = 'inactive', AvailabilityStatus = 'unavailable' WHERE VolunteerID = :volunteer_id";
            $volunteerStmt = $this->conn->prepare($volunteerQuery);
            $volunteerStmt->bindParam(":volunteer_id", $volunteerId);
            $result = $volunteerStmt->execute();

            if ($result) {
                if ($ownTransaction) {
                    $this->conn->commit();
                }
            } else {
                if ($ownTransaction) {
                    $this->conn->rollBack();
                }
            }

            return $result;
        } catch (Exception $e) {
            if ($ownTransaction && $this->conn->inTransaction()) {
                $this->conn->rollBack();
            }
            throw $e;
        }
    }

    /**
     * HZ-VOL-011
     * Purpose: Get count of active volunteers for dashboard
     * Table: Volunteers, Users
     * Returns: Integer count of active volunteers
     */
    public function getActiveCount()
    {
        $query = "SELECT COUNT(*) as active_count
                  FROM " . $this->table . " v
                  INNER JOIN Users u ON v.UserID = u.UserID
                  WHERE v.AvailabilityStatus = 'available'
                   AND u.Status = 'active'
                   AND v.Status = 'approved'";

        $stmt = $this->conn->prepare($query);

        if ($stmt->execute()) {
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return (int)$result['active_count'];
        }

        return 0;
    }

    /**
     * HZ-VOL-012
     * Purpose: Get today's volunteer schedule for dashboard
     * Table: VolunteerSchedule
     * Returns: Array of today's scheduled slots
     */
    public function getTodaySchedule()
    {
        $today = date('l'); // Get day name (Monday, Tuesday, etc.)

        $query = "SELECT vs.TimeSlot, vs.Role, GROUP_CONCAT(u.FullName SEPARATOR ', ') as volunteers, vs.Status
                  FROM VolunteerSchedule vs
                  LEFT JOIN Volunteers v ON vs.VolunteerID = v.VolunteerID
                  LEFT JOIN Users u ON v.UserID = u.UserID
                  WHERE vs.DayOfWeek = :day
                  GROUP BY vs.ScheduleID, vs.TimeSlot, vs.Role, vs.Status
                  ORDER BY vs.TimeSlot ASC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":day", $today);

        if ($stmt->execute()) {
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        return [];
    }

    /**
     * HZ-VOL-013
     * Purpose: Get weekly volunteer schedule for dashboard
     * Table: VolunteerSchedule
     * Returns: Array of weekly schedule with volunteer assignments
     */
    public function getWeeklySchedule()
    {
        $query = "SELECT vs.DayOfWeek, vs.TimeSlot, vs.Role,
                         GROUP_CONCAT(u.FullName SEPARATOR ', ') as volunteers
                  FROM VolunteerSchedule vs
                  LEFT JOIN Volunteers v ON vs.VolunteerID = v.VolunteerID
                  LEFT JOIN Users u ON v.UserID = u.UserID
                  GROUP BY vs.DayOfWeek, vs.TimeSlot, vs.Role
                  ORDER BY FIELD(vs.DayOfWeek, 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'),
                           vs.TimeSlot ASC";

        $stmt = $this->conn->prepare($query);

        if ($stmt->execute()) {
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Group by volunteer for dashboard display
            $schedule = [];
            foreach ($results as $row) {
                $volunteerName = $row['volunteers'] ?: 'Unassigned';
                $day = strtolower($row['DayOfWeek']);

                if (!isset($schedule[$volunteerName])) {
                    $schedule[$volunteerName] = [
                        'first_name' => explode(' ', $volunteerName)[0],
                        'last_name' => explode(' ', $volunteerName)[1] ?? '',
                        'role' => $row['Role'],
                        'monday' => null,
                        'tuesday' => null,
                        'wednesday' => null,
                        'thursday' => null,
                        'friday' => null,
                        'saturday' => null,
                        'sunday' => null
                    ];
                }

                $schedule[$volunteerName][$day] = $row['TimeSlot'];
            }

            return array_values($schedule);
        }

        return [];
    }
}
?>
