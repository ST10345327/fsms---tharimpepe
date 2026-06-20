<?php
/**
 * Demo Mode Setup - Creates test credentials without requiring MySQL
 * This allows the app to function for demonstration/testing purposes
 */

// Create a simple test credentials file with BCrypt password hashes
$testCredentials = [
    'admin' => [
        'password_hash' => password_hash('admin123', PASSWORD_BCRYPT),
        'email' => 'admin@tharimpepe.local',
        'role' => 'admin',
        'name' => 'Administrator'
    ],
    'volunteer' => [
        'password_hash' => password_hash('vol123', PASSWORD_BCRYPT),
        'email' => 'volunteer@tharimpepe.local', 
        'role' => 'volunteer',
        'name' => 'John Volunteer'
    ],
    'donor' => [
        'password_hash' => password_hash('donor123', PASSWORD_BCRYPT),
        'email' => 'donor@tharimpepe.local',
        'role' => 'donor', 
        'name' => 'Jane Donor'
    ],
    'staff' => [
        'password_hash' => password_hash('staff123', PASSWORD_BCRYPT),
        'email' => 'staff@tharimpepe.local',
        'role' => 'staff',
        'name' => 'Sarah Staff'
    ]
];

// Save to a file
file_put_contents(__DIR__ . '/.demo_users.json', json_encode($testCredentials, JSON_PRETTY_PRINT));

echo "✓ Demo mode credentials saved!\n";
echo "\nTest Login Credentials:\n";
echo "======================\n";
foreach ($testCredentials as $username => $cred) {
    echo "Username: $username\n";
    echo "Password: <bcrypt hash stored securely>\n";
    echo "Role: {$cred['role']}\n\n";
}
?>
