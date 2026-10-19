<?php
/**
 * Integration tests for core models using in-memory SQLite (FSMS_TEST_SQLITE=1)
 * Run: FSMS_TEST_SQLITE=1 php tests\run_all_tests.php
 */

require_once __DIR__ . '/TestCase.php';
require_once __DIR__ . '/../app/helpers/bootstrap.php';
require_once __DIR__ . '/../app/models/Beneficiary.php';
require_once __DIR__ . '/../app/models/Donation.php';
require_once __DIR__ . '/../app/models/Attendance.php';
require_once __DIR__ . '/../app/models/Volunteer.php';

class BeneficiaryModelTest extends DatabaseTestCase
{
    protected $beneficiaryModel;

    public function setUp()
    {
        // Initialize in-memory DB from parent
        parent::setUp();

        // Create Beneficiaries table for SQLite tests
        $create = "CREATE TABLE IF NOT EXISTS Beneficiaries (
            BeneficiaryID INTEGER PRIMARY KEY AUTOINCREMENT,
            FirstName TEXT NOT NULL,
            LastName TEXT NOT NULL,
            Age INTEGER,
            Gender TEXT,
            Phone TEXT,
            Email TEXT,
            Address TEXT,
            RegistrationDate TEXT NOT NULL,
            Status TEXT DEFAULT 'active',
            Notes TEXT,
            CreatedAt TEXT DEFAULT CURRENT_TIMESTAMP,
            UpdatedAt TEXT DEFAULT CURRENT_TIMESTAMP
        );";

        $this->getConnection()->exec($create);

        $this->beneficiaryModel = new Beneficiary($this->getConnection());
    }

    public function testCreateGetUpdateDelete()
    {
        $first = 'John';
        $last = 'Doe';
        $regdate = date('Y-m-d');
        $age = 30;
        $gender = 'Male';
        $phone = '555-0000';
        $email = 'johndoe@example.com';
        $address = '123 Test St';
        $notes = 'Test note';

        // Create
        $id = $this->beneficiaryModel->createBeneficiary($first, $last, $regdate, $age, $gender, $phone, $email, $address, $notes);
        $this->assertNotNull($id, 'Create should return id');

        // Get
        $rec = $this->beneficiaryModel->getBeneficiaryById($id);
        $this->assertNotNull($rec, 'Should retrieve beneficiary by id');
        $this->assertEquals($email, $rec['Email'], 'Email should match');

        // Update
        $newNotes = 'Updated note';
        $ok = $this->beneficiaryModel->updateBeneficiary($id, $first, $last, $age, $gender, $phone, $email, $address, $regdate, 'active', $newNotes);
        $this->assertTrue($ok, 'Update should return true');

        $rec2 = $this->beneficiaryModel->getBeneficiaryById($id);
        $this->assertEquals($newNotes, $rec2['Notes'], 'Notes should be updated');

        // Delete
        $del = $this->beneficiaryModel->deleteBeneficiary($id);
        $this->assertTrue($del, 'Delete should return true');

        $rec3 = $this->beneficiaryModel->getBeneficiaryById($id);
        $this->assertFalse($rec3, 'Record should no longer exist');
    }

    public function testCountsAndSearch()
    {
        // Create multiple beneficiaries
        $this->beneficiaryModel->createBeneficiary('A', 'One', '2025-01-01');
        $this->beneficiaryModel->createBeneficiary('B', 'Two', '2025-02-02');
        $this->beneficiaryModel->createBeneficiary('C', 'Three', '2025-03-03');

        $total = $this->beneficiaryModel->getTotalCount();
        $this->assertEquals(3, $total, 'Total count should be 3');

        $results = $this->beneficiaryModel->searchBeneficiaries('Two');
        $this->assertEquals(1, count($results), 'Search should return matching record');
    }
}

class DonationModelTest extends DatabaseTestCase
{
    protected $donationModel;

    public function setUp()
    {
        parent::setUp();

        $create = "CREATE TABLE IF NOT EXISTS Donations (
            DonationID INTEGER PRIMARY KEY AUTOINCREMENT,
            DonorName TEXT NOT NULL,
            DonorEmail TEXT,
            DonationType TEXT,
            Amount REAL,
            Description TEXT,
            DonationDate TEXT NOT NULL,
            CreatedAt TEXT DEFAULT CURRENT_TIMESTAMP
        );";

        $this->getConnection()->exec($create);
        $this->donationModel = new Donation($this->getConnection());
    }

    public function testCreateAndList()
    {
        $payload = [
            'DonorName' => 'Alice',
            'DonorEmail' => 'alice@example.com',
            'DonationType' => 'cash',
            'Amount' => 100.00,
            'Description' => 'Test',
            'DonationDate' => '2025-06-01'
        ];

        $res = $this->donationModel->createDonation($payload);
        $this->assertNotNull($res['id'] ?? null, 'Donation create should return id');

        $page = $this->donationModel->getAllDonations();
        $this->assertTrue(count($page['data']) >= 1, 'Donations list should include created donation');
    }
}

class AttendanceModelTest extends DatabaseTestCase
{
    protected $attendanceModel;
    protected $beneficiaryModel;

    public function setUp()
    {
        parent::setUp();

        // Create beneficiaries and attendance tables
        $this->getConnection()->exec("CREATE TABLE IF NOT EXISTS Beneficiaries (
            BeneficiaryID INTEGER PRIMARY KEY AUTOINCREMENT,
            FirstName TEXT NOT NULL,
            LastName TEXT NOT NULL,
            Age INTEGER,
            Gender TEXT,
            Phone TEXT,
            Email TEXT,
            Address TEXT,
            RegistrationDate TEXT NOT NULL,
            Status TEXT DEFAULT 'active',
            Notes TEXT,
            CreatedAt TEXT DEFAULT CURRENT_TIMESTAMP,
            UpdatedAt TEXT DEFAULT CURRENT_TIMESTAMP
        );");

        $this->getConnection()->exec("CREATE TABLE IF NOT EXISTS MealSession (
            MealSessionID INTEGER PRIMARY KEY AUTOINCREMENT,
            SessionDate TEXT NOT NULL,
            SessionType TEXT NOT NULL,
            Location TEXT,
            Notes TEXT,
            CreatedAt TEXT DEFAULT CURRENT_TIMESTAMP
        );");

        $this->getConnection()->exec("CREATE TABLE IF NOT EXISTS Attendance (
            AttendanceID INTEGER PRIMARY KEY AUTOINCREMENT,
            BeneficiaryID INTEGER NOT NULL,
            MealSessionID INTEGER,
            SessionDate TEXT NOT NULL,
            Status TEXT DEFAULT 'present',
            Notes TEXT,
            CreatedAt TEXT DEFAULT CURRENT_TIMESTAMP
        );");

        $this->attendanceModel = new Attendance($this->getConnection());
        $this->beneficiaryModel = new Beneficiary($this->getConnection());
    }

    public function testRecordAttendance()
    {
        $bid = $this->beneficiaryModel->createBeneficiary('Attend', 'User', '2025-01-01');

        $aid = $this->attendanceModel->recordAttendance($bid, date('Y-m-d'), 'present', 'On time');
        $this->assertNotNull($aid, 'Attendance record should return id');

        $rows = $this->attendanceModel->getBeneficiaryAttendance($bid);
        $this->assertTrue(count($rows) >= 1, 'Attendance retrieved for beneficiary');
    }
}

// Add more model tests (Volunteer, FoodStock, Reports) similarly as needed.

// Register tests when run directly
if (php_sapi_name() === 'cli' && realpath($_SERVER['SCRIPT_FILENAME'] ?? '') === __FILE__) {
    $runner = new TestRunner();
    $runner->addTest(new BeneficiaryModelTest());
    $runner->addTest(new DonationModelTest());
    $runner->addTest(new AttendanceModelTest());
    $runner->printReport();
}

?>
