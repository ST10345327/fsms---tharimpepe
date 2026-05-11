<?php
/**
 * Module: Meal Session Management
 * Purpose: Persist meal-session metadata used by attendance and reporting
 * Reference: Task 2b System Design - Attendance and Meal Distribution
 * Author: WIL Student
 */

class MealSession
{
    private $conn;
    private $table = "MealSession";

    public function __construct($db)
    {
        $this->conn = $db;
    }

    /**
     * HZ-MEAL-001
     * Purpose: Fetch a meal session by identifier
     */
    public function getSessionById($mealSessionId)
    {
        $query = "SELECT * FROM " . $this->table . " WHERE MealSessionID = :meal_session_id LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':meal_session_id', $mealSessionId, PDO::PARAM_INT);

        if ($stmt->execute()) {
            return $stmt->fetch(PDO::FETCH_ASSOC);
        }

        return false;
    }

    /**
     * HZ-MEAL-002
     * Purpose: Fetch all meal sessions for a given date
     */
    public function getSessionsByDate($sessionDate)
    {
        $query = "SELECT * FROM " . $this->table . "
                  WHERE SessionDate = :session_date
                  ORDER BY SessionType ASC, Location ASC, CreatedAt DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':session_date', $sessionDate);

        if ($stmt->execute()) {
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        return [];
    }

    /**
     * HZ-MEAL-003
     * Purpose: Fetch meal sessions for a date range
     */
    public function getSessionsInRange($startDate, $endDate)
    {
        $query = "SELECT * FROM " . $this->table . "
                  WHERE SessionDate BETWEEN :start_date AND :end_date
                  ORDER BY SessionDate DESC, SessionType ASC, Location ASC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':start_date', $startDate);
        $stmt->bindParam(':end_date', $endDate);

        if ($stmt->execute()) {
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        return [];
    }

    /**
     * HZ-MEAL-004
     * Purpose: Create a meal session or reuse the existing one
     */
    public function getOrCreateMealSession($sessionDate, $sessionType = 'meal_distribution', $location = 'Main Hall', $notes = null)
    {
        $existing = $this->findSession($sessionDate, $sessionType, $location);
        if ($existing) {
            return $existing;
        }

        $query = "INSERT INTO " . $this->table . " (SessionDate, SessionType, Location, Notes)
                  VALUES (:session_date, :session_type, :location, :notes)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':session_date', $sessionDate);
        $stmt->bindParam(':session_type', $sessionType);
        $stmt->bindParam(':location', $location);
        $stmt->bindParam(':notes', $notes);

        if ($stmt->execute()) {
            return $this->getSessionById($this->conn->lastInsertId());
        }

        return false;
    }

    /**
     * HZ-MEAL-005
     * Purpose: Update an existing meal session
     */
    public function updateMealSession($mealSessionId, $sessionDate, $sessionType, $location, $notes = null)
    {
        $query = "UPDATE " . $this->table . "
                  SET SessionDate = :session_date,
                      SessionType = :session_type,
                      Location = :location,
                      Notes = :notes
                  WHERE MealSessionID = :meal_session_id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':session_date', $sessionDate);
        $stmt->bindParam(':session_type', $sessionType);
        $stmt->bindParam(':location', $location);
        $stmt->bindParam(':notes', $notes);
        $stmt->bindParam(':meal_session_id', $mealSessionId, PDO::PARAM_INT);

        return $stmt->execute();
    }

    /**
     * HZ-MEAL-006
     * Purpose: Resolve a session by date/type/location
     */
    private function findSession($sessionDate, $sessionType, $location)
    {
        $query = "SELECT * FROM " . $this->table . "
                  WHERE SessionDate = :session_date
                    AND SessionType = :session_type
                    AND Location = :location
                  LIMIT 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':session_date', $sessionDate);
        $stmt->bindParam(':session_type', $sessionType);
        $stmt->bindParam(':location', $location);

        if ($stmt->execute()) {
            return $stmt->fetch(PDO::FETCH_ASSOC);
        }

        return false;
    }
}
?>
