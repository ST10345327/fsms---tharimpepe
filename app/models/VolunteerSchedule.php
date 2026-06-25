<?php
/**
 * Module: Volunteer Scheduling Model
 * Purpose: Handle volunteer shift management, scheduling, and availability
 * Reference: HZ-SCHED-001 to HZ-SCHED-012
 * Database Table: VolunteerSchedules, VolunteerShifts, VolunteerAvailability
 * Author: WIL Student
 */

require_once __DIR__ . "/../../config/database.php";

class VolunteerSchedule {
    // HZ-SCHED-001: Create a new schedule
    public static function createSchedule($volunteerId, $scheduleDate, $startTime, $endTime, $location, $role, $notes = '') {
        $connection = getConnection();
        
        try {
            $query = "INSERT INTO VolunteerSchedules (VolunteerID, ScheduleDate, StartTime, EndTime, Location, Role, Notes, Status, CreatedAt) 
                      VALUES (:volunteer_id, :schedule_date, :start_time, :end_time, :location, :role, :notes, 'scheduled', NOW())";
            $stmt = $connection->prepare($query);
            $stmt->bindParam(':volunteer_id', $volunteerId);
            $stmt->bindParam(':schedule_date', $scheduleDate);
            $stmt->bindParam(':start_time', $startTime);
            $stmt->bindParam(':end_time', $endTime);
            $stmt->bindParam(':location', $location);
            $stmt->bindParam(':role', $role);
            $stmt->bindParam(':notes', $notes);
            
            return $stmt->execute() ? $connection->lastInsertId() : false;
        } catch (PDOException $e) {
            error_log("VolunteerSchedule::createSchedule - " . $e->getMessage());
            return false;
        }
    }

    // HZ-SCHED-002: Get all schedules with filtering
    public static function getAllSchedules($limit = 50, $offset = 0, $filters = []) {
        $connection = getConnection();
        
        try {
$query = "SELECT vs.*, vs.STATUS AS `Status`, u.FullName, u.Phone, u.Email FROM VolunteerSchedules vs 
                      LEFT JOIN Volunteers v ON vs.VolunteerID = v.VolunteerID 
                      LEFT JOIN Users u ON v.UserID = u.UserID
                      WHERE 1=1";
            $params = [];

            if (!empty($filters['status']) && in_array($filters['status'], ['scheduled', 'completed', 'cancelled', 'no-show'], true)) {
                $query .= " AND vs.Status = :status";
                $params[':status'] = $filters['status'];
            }
            if (!empty($filters['volunteer_id'])) {
                $query .= " AND vs.VolunteerID = :volunteer_id";
                $params[':volunteer_id'] = (int)$filters['volunteer_id'];
            }
            if (!empty($filters['from_date'])) {
                $query .= " AND vs.ScheduleDate >= :from_date";
                $params[':from_date'] = $filters['from_date'];
            }
            if (!empty($filters['to_date'])) {
                $query .= " AND vs.ScheduleDate <= :to_date";
                $params[':to_date'] = $filters['to_date'];
            }
            if (!empty($filters['location'])) {
                $query .= " AND vs.Location = :location";
                $params[':location'] = $filters['location'];
            }

            $query .= " ORDER BY vs.ScheduleDate ASC, vs.StartTime ASC LIMIT :limit OFFSET :offset";
            $stmt = $connection->prepare($query);
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("VolunteerSchedule::getAllSchedules - " . $e->getMessage());
            return [];
        }
    }

    // HZ-SCHED-003: Get schedule by ID
    public static function getScheduleById($scheduleId) {
        $connection = getConnection();
        
        try {
$query = "SELECT vs.*, vs.STATUS AS `Status`, u.FullName, u.Phone, u.Email FROM VolunteerSchedules vs 
                      LEFT JOIN Volunteers v ON vs.VolunteerID = v.VolunteerID 
                      LEFT JOIN Users u ON v.UserID = u.UserID
                      WHERE vs.ScheduleID = :schedule_id";
            $stmt = $connection->prepare($query);
            $stmt->bindParam(':schedule_id', $scheduleId);
            $stmt->execute();
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("VolunteerSchedule::getScheduleById - " . $e->getMessage());
            return null;
        }
    }

    // HZ-SCHED-003A: Get schedule count with filtering
    public static function getScheduleCount($filters = [])
    {
        $connection = getConnection();

        try {
            $query = "SELECT COUNT(*) AS total FROM VolunteerSchedules vs WHERE 1=1";
            $params = [];

            if (!empty($filters['status']) && in_array($filters['status'], ['scheduled', 'completed', 'cancelled', 'no-show'], true)) {
                $query .= " AND vs.Status = :status";
                $params[':status'] = $filters['status'];
            }
            if (!empty($filters['volunteer_id'])) {
                $query .= " AND vs.VolunteerID = :volunteer_id";
                $params[':volunteer_id'] = (int)$filters['volunteer_id'];
            }
            if (!empty($filters['from_date'])) {
                $query .= " AND vs.ScheduleDate >= :from_date";
                $params[':from_date'] = $filters['from_date'];
            }
            if (!empty($filters['to_date'])) {
                $query .= " AND vs.ScheduleDate <= :to_date";
                $params[':to_date'] = $filters['to_date'];
            }
            if (!empty($filters['location'])) {
                $query .= " AND vs.Location = :location";
                $params[':location'] = $filters['location'];
            }

            $stmt = $connection->prepare($query);
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            $stmt->execute();

            return (int)$stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log("VolunteerSchedule::getScheduleCount - " . $e->getMessage());
            return 0;
        }
    }

    // HZ-SCHED-004: Update schedule
    public static function updateSchedule($scheduleId, $startTime, $endTime, $location, $role, $status, $notes = '') {
        $connection = getConnection();
        
        try {
            $query = "UPDATE VolunteerSchedules SET StartTime = :start_time, EndTime = :end_time, 
                      Location = :location, Role = :role, Status = :status, Notes = :notes, UpdatedAt = NOW() 
                      WHERE ScheduleID = :schedule_id";
            $stmt = $connection->prepare($query);
            $stmt->bindParam(':start_time', $startTime);
            $stmt->bindParam(':end_time', $endTime);
            $stmt->bindParam(':location', $location);
            $stmt->bindParam(':role', $role);
            $stmt->bindParam(':status', $status);
            $stmt->bindParam(':notes', $notes);
            $stmt->bindParam(':schedule_id', $scheduleId);
            
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("VolunteerSchedule::updateSchedule - " . $e->getMessage());
            return false;
        }
    }

    // HZ-SCHED-005: Delete schedule
    public static function deleteSchedule($scheduleId) {
        $connection = getConnection();
        
        try {
            $query = "DELETE FROM VolunteerSchedules WHERE ScheduleID = :schedule_id";
            $stmt = $connection->prepare($query);
            $stmt->bindParam(':schedule_id', $scheduleId);
            
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("VolunteerSchedule::deleteSchedule - " . $e->getMessage());
            return false;
        }
    }

    // HZ-SCHED-006: Get volunteer availability
    public static function getVolunteerAvailability($volunteerId) {
        $connection = getConnection();
        
        try {
            $query = "SELECT * FROM VolunteerAvailability WHERE VolunteerID = :volunteer_id ORDER BY DayOfWeek ASC";
            $stmt = $connection->prepare($query);
            $stmt->bindParam(':volunteer_id', $volunteerId);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("VolunteerSchedule::getVolunteerAvailability - " . $e->getMessage());
            return [];
        }
    }

    // HZ-SCHED-007: Set volunteer availability
    public static function setAvailability($volunteerId, $dayOfWeek, $isAvailable, $notes = '') {
        $connection = getConnection();
        
        try {
            // Check if record exists
            $checkQuery = "SELECT * FROM VolunteerAvailability WHERE VolunteerID = :volunteer_id AND DayOfWeek = :day";
            $checkStmt = $connection->prepare($checkQuery);
            $checkStmt->bindParam(':volunteer_id', $volunteerId);
            $checkStmt->bindParam(':day', $dayOfWeek);
            $checkStmt->execute();
            
            if ($checkStmt->rowCount() > 0) {
                $updateQuery = "UPDATE VolunteerAvailability SET IsAvailable = :is_available, Notes = :notes WHERE VolunteerID = :volunteer_id AND DayOfWeek = :day";
                $stmt = $connection->prepare($updateQuery);
            } else {
                $updateQuery = "INSERT INTO VolunteerAvailability (VolunteerID, DayOfWeek, IsAvailable, Notes) VALUES (:volunteer_id, :day, :is_available, :notes)";
                $stmt = $connection->prepare($updateQuery);
            }
            
            $stmt->bindParam(':volunteer_id', $volunteerId);
            $stmt->bindParam(':day', $dayOfWeek);
            $stmt->bindParam(':is_available', $isAvailable);
            $stmt->bindParam(':notes', $notes);
            
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("VolunteerSchedule::setAvailability - " . $e->getMessage());
            return false;
        }
    }

    // HZ-SCHED-008: Get schedules by date range
    public static function getSchedulesByDateRange($fromDate, $toDate) {
        $connection = getConnection();
        
        try {
$query = "SELECT vs.*, vs.STATUS AS `Status`, u.FullName, u.Phone FROM VolunteerSchedules vs 
                      LEFT JOIN Volunteers v ON vs.VolunteerID = v.VolunteerID 
                      LEFT JOIN Users u ON v.UserID = u.UserID
                      WHERE vs.ScheduleDate BETWEEN :from_date AND :to_date 
                      ORDER BY vs.ScheduleDate ASC, vs.StartTime ASC";
            $stmt = $connection->prepare($query);
            $stmt->bindParam(':from_date', $fromDate);
            $stmt->bindParam(':to_date', $toDate);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("VolunteerSchedule::getSchedulesByDateRange - " . $e->getMessage());
            return [];
        }
    }

    // HZ-SCHED-009: Get schedules by volunteer
    public static function getVolunteerSchedules($volunteerId) {
        $connection = getConnection();
        
        try {
            $query = "SELECT * FROM VolunteerSchedules WHERE VolunteerID = :volunteer_id 
                      AND ScheduleDate >= CURDATE() 
                      ORDER BY ScheduleDate ASC, StartTime ASC";
            $stmt = $connection->prepare($query);
            $stmt->bindParam(':volunteer_id', $volunteerId);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("VolunteerSchedule::getVolunteerSchedules - " . $e->getMessage());
            return [];
        }
    }

    // HZ-SCHED-010: Get schedule statistics
    public static function getScheduleStats() {
        $connection = getConnection();
        
        try {
            $query = "SELECT 
                        COUNT(*) as total_schedules,
                        SUM(CASE WHEN Status = 'scheduled' THEN 1 ELSE 0 END) as scheduled,
                        SUM(CASE WHEN Status = 'completed' THEN 1 ELSE 0 END) as completed,
                        SUM(CASE WHEN Status = 'cancelled' THEN 1 ELSE 0 END) as cancelled,
                        COUNT(DISTINCT VolunteerID) as total_volunteers,
                        (SELECT COUNT(*) FROM VolunteerSchedules WHERE ScheduleDate = CURDATE()) as today_schedules,
                        COALESCE(SUM(COALESCE(HoursWorked, 0)), 0) as total_hours
                      FROM VolunteerSchedules";
            $stmt = $connection->prepare($query);
            $stmt->execute();
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("VolunteerSchedule::getScheduleStats - " . $e->getMessage());
            return null;
        }
    }

    // HZ-SCHED-011: Mark schedule as completed
    public static function markCompleted($scheduleId, $hoursWorked = null) {
        $connection = getConnection();
        
        try {
            $query = "UPDATE VolunteerSchedules SET Status = 'completed'";
            if ($hoursWorked !== null) {
                $query .= ", HoursWorked = :hours_worked";
            }
            $query .= " WHERE ScheduleID = :schedule_id";
            
            $stmt = $connection->prepare($query);
            if ($hoursWorked !== null) {
                $stmt->bindParam(':hours_worked', $hoursWorked);
            }
            $stmt->bindParam(':schedule_id', $scheduleId);
            
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("VolunteerSchedule::markCompleted - " . $e->getMessage());
            return false;
        }
    }

    // HZ-SCHED-012: Get volunteer hours summary
    public static function getVolunteerHoursSummary($volunteerId, $monthYear = null) {
        $connection = getConnection();
        
        try {
            $query = "SELECT 
                        SUM(HoursWorked) as total_hours,
                        COUNT(CASE WHEN Status = 'completed' THEN 1 END) as completed_shifts,
                        COUNT(CASE WHEN Status = 'scheduled' THEN 1 END) as upcoming_shifts
                      FROM VolunteerSchedules 
                      WHERE VolunteerID = :volunteer_id";
            
            if ($monthYear) {
                $query .= " AND DATE_FORMAT(ScheduleDate, '%Y-%m') = :month_year";
            }
            
            $stmt = $connection->prepare($query);
            $stmt->bindParam(':volunteer_id', $volunteerId);
            if ($monthYear) {
                $stmt->bindParam(':month_year', $monthYear);
            }
            $stmt->execute();
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("VolunteerSchedule::getVolunteerHoursSummary - " . $e->getMessage());
            return null;
        }
    }

    // HZ-SCHED-013: Get volunteer schedule summary for reporting
    public static function getVolunteerScheduleSummary($fromDate = null, $toDate = null, $status = null)
    {
        $connection = getConnection();

        try {
            $query = "SELECT vs.VolunteerID,
                             u.FullName,
                             COUNT(*) as total_schedules,
                             SUM(CASE WHEN vs.Status = 'completed' THEN 1 ELSE 0 END) as completed,
                             COALESCE(SUM(COALESCE(vs.HoursWorked, 0)), 0) as total_hours
                      FROM VolunteerSchedules vs
                      INNER JOIN Volunteers v ON vs.VolunteerID = v.VolunteerID
                      INNER JOIN Users u ON v.UserID = u.UserID
                      WHERE 1=1";

            $params = [];
            if ($fromDate) {
                $query .= " AND vs.ScheduleDate >= :from_date";
                $params[':from_date'] = $fromDate;
            }
            if ($toDate) {
                $query .= " AND vs.ScheduleDate <= :to_date";
                $params[':to_date'] = $toDate;
            }
            if ($status && in_array($status, ['scheduled', 'completed', 'cancelled', 'no-show'], true)) {
                $query .= " AND vs.Status = :status";
                $params[':status'] = $status;
            }

            $query .= " GROUP BY vs.VolunteerID, u.FullName
                        ORDER BY total_hours DESC, completed DESC, u.FullName ASC";

            $stmt = $connection->prepare($query);
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("VolunteerSchedule::getVolunteerScheduleSummary - " . $e->getMessage());
            return [];
        }
    }
}
