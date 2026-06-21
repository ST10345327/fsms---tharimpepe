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
    private $table = "volunteers";

    public function __construct($db)
    {
        $this->conn = $db;
    }

    private function mapVolunteerRow(array $row): array
    {
        $fullName = trim($row['FullName'] ?? '');
        if ($fullName !== '') {
            $parts = preg_split('/\s+/', $fullName, 2);
            $row['FirstName'] = $parts[0] ?? '';
            $row['LastName'] = $parts[1] ?? '';
        } else {
            $row['FirstName'] = $row['FirstName'] ?? '';
            $row['LastName'] = $row['LastName'] ?? '';
            $row['FullName'] = trim($row['FirstName'] . ' ' . $row['LastName']);
        }

        return $row;
    }

    private function mapVolunteerRows(array $rows): array
    {
        return array_map([$this, 'mapVolunteerRow'], $rows);
    }

    /**
     * HZ-VOL-001
     * Purpose: Get all volunteers with optional filtering
     * Table: Volunteers (joined with Users)
     * Returns: Array of volunteer records
     * Pagination: Supports LIMIT and OFFSET
     */
    public function getAllVolunteers($limit = 10, $offset = 0, $status = null)
    {
        $query = "SELECT v.VolunteerID, v.UserID, u.Username, u.Email, u.FullName, u.Phone,
                         v.Address, v.AvailabilityStatus, v.Status, v.Skills, v.Notes, v.CreatedAt
                  FROM " . $this->table . " v
                  INNER JOIN users u ON v.UserID = u.UserID
                  WHERE u.Status = 'active'";

        if ($status && in_array($status, ['available', 'unavailable', 'on_leave'])) {
            $query .= " AND v.AvailabilityStatus = :status";
        }

        $query .= " ORDER BY u.FullName ASC, u.Username ASC LIMIT :limit OFFSET :offset";

        $stmt = $this->conn->prepare($query);

        if ($status && in_array($status, ['available', 'unavailable', 'on_leave'])) {
            $stmt->bindParam(":status", $status);
        }

        $stmt->bindParam(":limit", $limit, PDO::PARAM_INT);
        $stmt->bindParam(":offset", $offset, PDO::PARAM_INT);

        if ($stmt->execute()) {
            return $this->mapVolunteerRows($stmt->fetchAll(PDO::FETCH_ASSOC));
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
        $query = "SELECT v.VolunteerID, v.UserID, u.Username, u.Email, u.FullName, u.Phone, u.Role,
                         v.Address, v.AvailabilityStatus, v.Status, v.Skills, v.Notes, v.CreatedAt
                  FROM " . $this->table . " v
                  INNER JOIN users u ON v.UserID = u.UserID
                  WHERE v.VolunteerID = :volunteer_id
                  LIMIT 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":volunteer_id", $volunteerId);

        if ($stmt->execute()) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return $row ? $this->mapVolunteerRow($row) : false;
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
        $query = "SELECT v.VolunteerID, v.UserID, u.Username, u.Email, u.FullName, u.Phone,
                         v.Address, v.AvailabilityStatus, v.Status, v.Skills, v.Notes, v.CreatedAt
                  FROM " . $this->table . " v
                  INNER JOIN users u ON v.UserID = u.UserID
                  WHERE v.UserID = :user_id
                  LIMIT 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":user_id", $userId);

        if ($stmt->execute()) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return $row ? $this->mapVolunteerRow($row) : false;
        }

        return false;
    }

    /**
     * HZ-VOL-004
     * Purpose: Create new volunteer profile linked to user account
     * Table: Volunteers
     * Returns: VolunteerID on success, false on failure
     * Validation: FirstName, LastName, Phone required
     */
    public function createVolunteer($userId, $firstName, $lastName, $phone, $address = null)
    {
        if ($this->getVolunteerByUserId($userId)) {
            throw new Exception("Volunteer profile already exists for this user");
        }

        if (empty($firstName) || empty($lastName) || empty($phone)) {
            throw new Exception("First name, last name, and phone are required");
        }

        if (!preg_match('/^[0-9\s\-\+\(\)]{10,}$/', $phone)) {
            throw new Exception("Invalid phone number format");
        }

        $fullName = trim($firstName . ' ' . $lastName);
        $userQuery = "UPDATE users
                      SET FullName = :full_name, Phone = :phone
                      WHERE UserID = :user_id";
        $userStmt = $this->conn->prepare($userQuery);
        $userStmt->bindParam(":full_name", $fullName);
        $userStmt->bindParam(":phone", $phone);
        $userStmt->bindParam(":user_id", $userId);
        $userStmt->execute();

        $query = "INSERT INTO " . $this->table . "
                  (UserID, Address, AvailabilityStatus, Status, CreatedAt)
                  VALUES (:user_id, :address, 'available', 'approved', NOW())";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":user_id", $userId);
        $stmt->bindParam(":address", $address);

        if ($stmt->execute()) {
            return $this->conn->lastInsertId();
        }

        return false;
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
        if (!empty($phone) && !preg_match('/^[0-9\s\-\+\(\)]{10,}$/', $phone)) {
            throw new Exception("Invalid phone number format");
        }

        if (!in_array($status, ['available', 'unavailable', 'on_leave'])) {
            throw new Exception("Invalid availability status");
        }

        $volunteer = $this->getVolunteerById($volunteerId);
        if (!$volunteer) {
            throw new Exception("Volunteer not found");
        }

        $fullName = trim($firstName . ' ' . $lastName);
        $userQuery = "UPDATE users
                      SET FullName = :full_name, Phone = :phone
                      WHERE UserID = :user_id";
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

        return $stmt->execute();
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
        $query = "SELECT v.VolunteerID, v.UserID, u.Username, u.FullName, u.Phone
                  FROM " . $this->table . " v
                  INNER JOIN users u ON v.UserID = u.UserID
                  WHERE v.AvailabilityStatus = 'available'
                  AND u.Status = 'active'
                  AND v.Status IN ('approved', 'pending')
                  ORDER BY u.FullName ASC, u.Username ASC";

        $stmt = $this->conn->prepare($query);

        if ($stmt->execute()) {
            return $this->mapVolunteerRows($stmt->fetchAll(PDO::FETCH_ASSOC));
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

        $query = "SELECT v.VolunteerID, v.UserID, u.Username, u.Email, u.FullName, u.Phone,
                         v.Address, v.AvailabilityStatus, v.Status, v.CreatedAt
                  FROM " . $this->table . " v
                  INNER JOIN users u ON v.UserID = u.UserID
                  WHERE (u.FullName LIKE :search OR u.Phone LIKE :search OR u.Username LIKE :search)
                  AND u.Status = 'active'
                  ORDER BY u.FullName ASC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":search", $searchTerm);

        if ($stmt->execute()) {
            return $this->mapVolunteerRows($stmt->fetchAll(PDO::FETCH_ASSOC));
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
        $volunteer = $this->getVolunteerById($volunteerId);

        if (!$volunteer) {
            throw new Exception("Volunteer not found");
        }

        $query = "UPDATE users SET Status = 'inactive' WHERE UserID = :user_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":user_id", $volunteer['UserID']);

        return $stmt->execute();
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
                  INNER JOIN users u ON v.UserID = u.UserID
                  WHERE v.AvailabilityStatus = 'available'
                  AND u.Status = 'active'";

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
     * Table: VolunteerSchedules
     * Returns: Array of today's scheduled slots
     */
    public function getTodaySchedule()
    {
        $today = date('l');

        $query = "SELECT vs.TimeSlot, vs.Role,
                         GROUP_CONCAT(COALESCE(u.FullName, u.Username) SEPARATOR ', ') as volunteers,
                         vs.Status
                   FROM volunteerschedules vs
                   LEFT JOIN volunteers v ON vs.VolunteerID = v.VolunteerID
                   LEFT JOIN users u ON v.UserID = u.UserID
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
     * Table: VolunteerSchedules
     * Returns: Array of weekly schedule with volunteer assignments
     */
    public function getWeeklySchedule()
    {
        $query = "SELECT vs.DayOfWeek, vs.TimeSlot, vs.Role,
                          GROUP_CONCAT(COALESCE(u.FullName, u.Username) SEPARATOR ', ') as volunteers
                   FROM volunteerschedules vs
                   LEFT JOIN volunteers v ON vs.VolunteerID = v.VolunteerID
                   LEFT JOIN users u ON v.UserID = u.UserID
                   GROUP BY vs.DayOfWeek, vs.TimeSlot, vs.Role
                   ORDER BY FIELD(vs.DayOfWeek, 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'),
                            vs.TimeSlot ASC";

        $stmt = $this->conn->prepare($query);

        if ($stmt->execute()) {
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

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
