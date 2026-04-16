<?php
/**
 * Module: Beneficiary Management & Registration
 * Purpose: Data layer for beneficiary CRUD operations and meal recipient tracking
 * Reference: Task 2b System Design Section 4.3 - Beneficiary Entity
 * Author: WIL Student
 * Entity: Beneficiaries (MySQL table)
 */

class Beneficiary
{
    private $conn;
    private $table = "Beneficiaries";

    public function __construct($db)
    {
        $this->conn = $db;
    }

    /**
     * HZ-BEN-001
     * Purpose: Get all beneficiaries with optional filtering and pagination
     * Table: Beneficiaries
     * Returns: Array of beneficiary records
     * Pagination: Supports LIMIT and OFFSET
     */
    public function getAllBeneficiaries($limit = 10, $offset = 0, $status = null)
    {
        $query = "SELECT BeneficiaryID, FirstName, LastName, Age, Gender, Phone, Email, Address, RegistrationDate, Status, Notes, CreatedAt, UpdatedAt
                  FROM " . $this->table;

        // Filter by status if provided
        if ($status && in_array($status, ['active', 'inactive', 'suspended'])) {
            $query .= " WHERE Status = :status";
        }

        $query .= " ORDER BY RegistrationDate DESC LIMIT :limit OFFSET :offset";

        $stmt = $this->conn->prepare($query);

        if ($status && in_array($status, ['active', 'inactive', 'suspended'])) {
            $stmt->bindParam(":status", $status);
        }

        $stmt->bindParam(":limit", $limit, PDO::PARAM_INT);
        $stmt->bindParam(":offset", $offset, PDO::PARAM_INT);

        if ($stmt->execute()) {
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        return [];
    }

    /**
     * HZ-BEN-002
     * Purpose: Get single beneficiary by BeneficiaryID
     * Table: Beneficiaries
     * Returns: Beneficiary record or false
     */
    public function getBeneficiaryById($beneficiaryId)
    {
        $query = "SELECT BeneficiaryID, FirstName, LastName, Age, RegistrationDate, Status, Notes, CreatedAt
                  FROM " . $this->table . "
                  WHERE BeneficiaryID = :beneficiary_id
                  LIMIT 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":beneficiary_id", $beneficiaryId);

        if ($stmt->execute()) {
            return $stmt->fetch(PDO::FETCH_ASSOC);
        }

        return false;
    }

    /**
     * HZ-BEN-003
     * Purpose: Create new beneficiary record
     * Table: Beneficiaries
     * Returns: BeneficiaryID on success, false on failure
     * Validation: FirstName, LastName, RegistrationDate required
     */
    public function createBeneficiary($firstName, $lastName, $age = null, $gender = null, $phone = null, $email = null, $address = null, $registrationDate, $notes = null)
    {
        // Validation: Required fields
        if (empty($firstName) || empty($lastName) || empty($registrationDate)) {
            throw new Exception("First name, last name, and registration date are required");
        }

        // Validation: Age must be positive if provided
        if ($age !== null && (!is_numeric($age) || $age < 0 || $age > 120)) {
            throw new Exception("Age must be a valid number between 0 and 120");
        }

        // Validation: Gender must be valid if provided
        if ($gender !== null && !in_array($gender, ['Male', 'Female', 'Other'])) {
            throw new Exception("Gender must be Male, Female, or Other");
        }

        // Validation: Email format if provided
        if ($email !== null && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception("Invalid email format");
        }

        // Validation: Registration date format
        if (!strtotime($registrationDate)) {
            throw new Exception("Invalid registration date format");
        }

        $query = "INSERT INTO " . $this->table . "
                  (FirstName, LastName, Age, Gender, Phone, Email, Address, RegistrationDate, Status, Notes)
                  VALUES (:first_name, :last_name, :age, :gender, :phone, :email, :address, :registration_date, 'active', :notes)";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(":first_name", $firstName);
        $stmt->bindParam(":last_name", $lastName);
        $stmt->bindParam(":age", $age);
        $stmt->bindParam(":gender", $gender);
        $stmt->bindParam(":phone", $phone);
        $stmt->bindParam(":email", $email);
        $stmt->bindParam(":address", $address);
        $stmt->bindParam(":registration_date", $registrationDate);
        $stmt->bindParam(":notes", $notes);

        if ($stmt->execute()) {
            return $this->conn->lastInsertId();
        }

        return false;
    }

    /**
     * HZ-BEN-004
     * Purpose: Update beneficiary information
     * Table: Beneficiaries
     * Returns: true on success, false on failure
     * Security: Parameterized query
     */
    public function updateBeneficiary($beneficiaryId, $firstName, $lastName, $age, $gender, $phone, $email, $address, $registrationDate, $status, $notes)
    {
        // Validation: Required fields
        if (empty($firstName) || empty($lastName) || empty($registrationDate)) {
            throw new Exception("First name, last name, and registration date are required");
        }

        // Validation: Age must be positive if provided
        if ($age !== null && (!is_numeric($age) || $age < 0 || $age > 120)) {
            throw new Exception("Age must be a valid number between 0 and 120");
        }

        // Validation: Gender must be valid if provided
        if ($gender !== null && !in_array($gender, ['Male', 'Female', 'Other'])) {
            throw new Exception("Gender must be Male, Female, or Other");
        }

        // Validation: Email format if provided
        if ($email !== null && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception("Invalid email format");
        }

        // Validation: Status must be valid
        if (!in_array($status, ['active', 'inactive', 'suspended'])) {
            throw new Exception("Invalid status");
        }

        // Validation: Registration date format
        if (!strtotime($registrationDate)) {
            throw new Exception("Invalid registration date format");
        }

        $query = "UPDATE " . $this->table . "
                  SET FirstName = :first_name,
                      LastName = :last_name,
                      Age = :age,
                      Gender = :gender,
                      Phone = :phone,
                      Email = :email,
                      Address = :address,
                      RegistrationDate = :registration_date,
                      Status = :status,
                      Notes = :notes
                  WHERE BeneficiaryID = :beneficiary_id";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(":first_name", $firstName);
        $stmt->bindParam(":last_name", $lastName);
        $stmt->bindParam(":age", $age);
        $stmt->bindParam(":gender", $gender);
        $stmt->bindParam(":phone", $phone);
        $stmt->bindParam(":email", $email);
        $stmt->bindParam(":address", $address);
        $stmt->bindParam(":registration_date", $registrationDate);
        $stmt->bindParam(":status", $status);
        $stmt->bindParam(":notes", $notes);
        $stmt->bindParam(":beneficiary_id", $beneficiaryId);

        return $stmt->execute();
    }

    /**
     * HZ-BEN-005
     * Purpose: Update beneficiary status
     * Table: Beneficiaries
     * Returns: true on success, false on failure
     * Status: active, inactive, suspended
     */
    public function updateStatus($beneficiaryId, $status)
    {
        if (!in_array($status, ['active', 'inactive', 'suspended'])) {
            throw new Exception("Invalid status");
        }

        $query = "UPDATE " . $this->table . "
                  SET Status = :status
                  WHERE BeneficiaryID = :beneficiary_id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":status", $status);
        $stmt->bindParam(":beneficiary_id", $beneficiaryId);

        return $stmt->execute();
    }

    /**
     * HZ-BEN-006
     * Purpose: Get count of beneficiaries by status
     * Table: Beneficiaries
     * Returns: Array with status counts
     */
    public function getBeneficiaryCountByStatus()
    {
        $query = "SELECT Status, COUNT(*) as count
                  FROM " . $this->table . "
                  GROUP BY Status";

        $stmt = $this->conn->prepare($query);

        if ($stmt->execute()) {
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $counts = ['active' => 0, 'inactive' => 0, 'suspended' => 0];

            foreach ($results as $row) {
                $counts[$row['Status']] = $row['count'];
            }

            return $counts;
        }

        return ['active' => 0, 'inactive' => 0, 'suspended' => 0];
    }

    /**
     * HZ-BEN-007
     * Purpose: Search beneficiaries by name or notes
     * Table: Beneficiaries
     * Returns: Array of matching beneficiary records
     */
    public function searchBeneficiaries($searchTerm)
    {
        $searchTerm = "%{$searchTerm}%";

        $query = "SELECT BeneficiaryID, FirstName, LastName, Age, RegistrationDate, Status, Notes, CreatedAt
                  FROM " . $this->table . "
                  WHERE FirstName LIKE :search
                     OR LastName LIKE :search
                     OR Notes LIKE :search
                  ORDER BY FirstName ASC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":search", $searchTerm);

        if ($stmt->execute()) {
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        return [];
    }

    /**
     * HZ-BEN-008
     * Purpose: Get beneficiaries registered in a date range
     * Table: Beneficiaries
     * Returns: Array of beneficiaries within date range
     */
    public function getBeneficiariesByDateRange($startDate, $endDate)
    {
        $query = "SELECT BeneficiaryID, FirstName, LastName, Age, RegistrationDate, Status, Notes
                  FROM " . $this->table . "
                  WHERE RegistrationDate BETWEEN :start_date AND :end_date
                  ORDER BY RegistrationDate DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":start_date", $startDate);
        $stmt->bindParam(":end_date", $endDate);

        if ($stmt->execute()) {
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        return [];
    }

    /**
     * HZ-BEN-009
     * Purpose: Get beneficiaries by age range
     * Table: Beneficiaries
     * Returns: Array of beneficiaries within age range
     */
    public function getBeneficiariesByAgeRange($minAge, $maxAge)
    {
        $query = "SELECT BeneficiaryID, FirstName, LastName, Age, RegistrationDate, Status, Notes
                  FROM " . $this->table . "
                  WHERE Age BETWEEN :min_age AND :max_age
                  AND Age IS NOT NULL
                  ORDER BY Age ASC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":min_age", $minAge);
        $stmt->bindParam(":max_age", $maxAge);

        if ($stmt->execute()) {
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        return [];
    }

    /**
     * HZ-BEN-010
     * Purpose: Delete beneficiary record (hard delete - data retention policy)
     * Table: Beneficiaries
     * Returns: true on success, false on failure
     * Note: Hard delete as beneficiary data may need to be permanently removed
     */
    public function deleteBeneficiary($beneficiaryId)
    {
        $query = "DELETE FROM " . $this->table . "
                  WHERE BeneficiaryID = :beneficiary_id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":beneficiary_id", $beneficiaryId);

        return $stmt->execute();
    }

    /**
     * HZ-BEN-011
     * Purpose: Get total count of beneficiaries
     * Table: Beneficiaries
     * Returns: Total count as integer
     */
    public function getTotalCount()
    {
        $query = "SELECT COUNT(*) as total FROM " . $this->table;

        $stmt = $this->conn->prepare($query);

        if ($stmt->execute()) {
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return (int)$result['total'];
        }

        return 0;
    }
}
?>
