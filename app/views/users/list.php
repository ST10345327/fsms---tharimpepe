<?php
$pageTitle = 'Users';
$adminCount = 0;
$volunteerCount = 0;
$activeCount = 0;
$inactiveCount = 0;
foreach (($users ?? []) as $listedUser) {
    $role = strtolower($listedUser['Role'] ?? '');
    $status = strtolower($listedUser['Status'] ?? 'active');
    if ($role === 'admin') {
        $adminCount++;
    } elseif ($role === 'volunteer') {
        $volunteerCount++;
    }
    if ($status === 'inactive') {
        $inactiveCount++;
    } else {
        $activeCount++;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Users - FSMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="/assets/css/fsms-ui.css">
    <style>
        .users-page { padding: 30px; }
        .users-header {
            align-items: center;
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
        }
        .users-title {
            align-items: center;
            display: flex;
            gap: 16px;
        }
        .shield-box {
            align-items: center;
            background: #ffedd5;
            border-radius: 12px;
            color: #ff5b00;
            display: inline-flex;
            font-size: 28px;
            height: 60px;
            justify-content: center;
            width: 60px;
        }
        .users-title h2 { font-size: 26px; font-weight: 700; margin: 0 0 4px; }
        .users-title p { color: #334155; font-size: 18px; margin: 0; }
        .add-user-btn {
            background: #1b3a5c;
            border-radius: 10px;
            color: #fff;
            font-size: 20px;
            font-weight: 700;
            min-height: 50px;
            padding: 12px 24px;
            text-decoration: none;
        }
        .add-user-btn:hover { color: #fff; background: #2e4a6c; }
        .table-card,
        .summary-card {
            background: #fff;
            border: 1px solid #dfe3e8;
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(15, 23, 42, 0.16);
            overflow: hidden;
        }
        .prototype-table { margin: 0; }
        .prototype-table thead th {
            background: #f8fafc;
            color: #334155;
            font-size: 14px;
            font-weight: 800;
            padding: 18px 30px;
        }
        .prototype-table tbody td {
            border-color: #e5e7eb;
            color: #1f2a44;
            font-size: 18px;
            padding: 20px 30px;
            vertical-align: middle;
        }
        .role-pill,
        .status-pill {
            border-radius: 999px;
            display: inline-flex;
            font-size: 14px;
            padding: 6px 10px;
        }
        .role-pill.admin { background: #f3e8ff; color: #9b00ff; }
        .role-pill.volunteer { background: #dbeafe; color: #005cff; }
        .status-pill.active { background: #dcfce7; color: #008f35; }
        .status-pill.inactive { background: #f3f4f6; color: #475569; }
        .row-actions { display: flex; gap: 18px; }
        .row-actions a { font-size: 18px; text-decoration: none; }
        .row-actions .edit { color: #0d6efd; }
        .row-actions .delete { color: #ff1f1f; }
        .summary-grid {
            display: grid;
            gap: 30px;
            grid-template-columns: 1fr 1fr;
            margin-top: 30px;
        }
        .summary-card { padding: 30px; }
        .summary-card h3 { font-size: 24px; font-weight: 700; margin-bottom: 24px; }
        .summary-row {
            align-items: center;
            border-radius: 10px;
            display: flex;
            font-size: 20px;
            justify-content: space-between;
            margin-top: 14px;
            padding: 18px 16px;
        }
        .summary-row.purple { background: #faf5ff; }
        .summary-row.blue { background: #eff6ff; }
        .summary-row.green { background: #f0fdf4; }
        .summary-row.gray { background: #f8fafc; }
        .summary-row .purple-text { color: #8f00ff; }
        .summary-row .blue-text { color: #005cff; }
        .summary-row .green-text { color: #00a33a; }
        @media (max-width: 900px) {
            .users-header, .users-title { align-items: flex-start; flex-direction: column; }
            .summary-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
    <?php include __DIR__ . "/../includes/navbar.php"; ?>

    <main class="container-fluid users-page">
        <section class="users-header">
            <div class="users-title">
                <span class="shield-box"><i class="far fa-shield" aria-hidden="true"></i></span>
                <div>
                    <h2>User Management</h2>
                    <p>Admin only - Manage system users and roles</p>
                </div>
            </div>
            <a class="add-user-btn" href="UserController.php?action=create">
                <i class="fas fa-plus me-2" aria-hidden="true"></i>Add User
            </a>
        </section>

        <section class="table-card">
            <table class="table prototype-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>NAME</th>
                        <th>ROLE</th>
                        <th>EMAIL</th>
                        <th>LAST LOGIN</th>
                        <th>STATUS</th>
                        <th>ACTIONS</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($users)): ?>
                        <?php foreach ($users as $user): ?>
                            <?php
                            $role = strtolower($user['Role'] ?? 'volunteer');
                            $status = strtolower($user['Status'] ?? 'active');
                            $name = $user['FullName'] ?? $user['Username'] ?? 'User';
                            ?>
                            <tr>
                                <td><?php echo 'USR-' . str_pad((string)(int)$user['UserID'], 3, '0', STR_PAD_LEFT); ?></td>
                                <td><?php echo htmlspecialchars($name); ?></td>
                                <td><span class="role-pill <?php echo htmlspecialchars($role); ?>"><?php echo $role === 'admin' ? 'Administrator' : ucfirst($role); ?></span></td>
                                <td><?php echo htmlspecialchars($user['Email'] ?? ''); ?></td>
                                <td><?php echo htmlspecialchars($user['LastLogin'] ?? ($user['CreatedAt'] ?? 'N/A')); ?></td>
                                <td><span class="status-pill <?php echo htmlspecialchars($status); ?>"><i class="far fa-circle-check me-1"></i><?php echo ucfirst($status); ?></span></td>
                                <td>
                                    <div class="row-actions">
                                        <a class="edit" href="UserController.php?action=edit&id=<?php echo (int)$user['UserID']; ?>"><i class="far fa-pen-to-square"></i></a>
                                        <a class="delete" href="UserController.php?action=delete&id=<?php echo (int)$user['UserID']; ?>"><i class="far fa-trash-can"></i></a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <?php
                        $sampleUsers = [
                            ['USR-001', 'Admin User', 'admin', 'admin@tharimpepe.org', '2026-04-30 09:15', 'active'],
                            ['USR-002', 'Sarah Johnson', 'volunteer', 'sarah.j@email.com', '2026-04-29 14:30', 'active'],
                            ['USR-003', 'Mike Williams', 'volunteer', 'mike.w@email.com', '2026-04-28 11:00', 'active'],
                            ['USR-004', 'Lisa Brown', 'volunteer', 'lisa.b@email.com', '2026-04-15 16:45', 'inactive'],
                        ];
                        ?>
                        <?php foreach ($sampleUsers as $row): ?>
                            <tr>
                                <td><?php echo $row[0]; ?></td>
                                <td><?php echo $row[1]; ?></td>
                                <td><span class="role-pill <?php echo $row[2]; ?>"><?php echo $row[2] === 'admin' ? 'Administrator' : 'Volunteer'; ?></span></td>
                                <td><?php echo $row[3]; ?></td>
                                <td><?php echo $row[4]; ?></td>
                                <td><span class="status-pill <?php echo $row[5]; ?>"><i class="far <?php echo $row[5] === 'active' ? 'fa-circle-check' : 'fa-circle-xmark'; ?> me-1"></i><?php echo ucfirst($row[5]); ?></span></td>
                                <td><div class="row-actions"><a class="edit" href="#"><i class="far fa-pen-to-square"></i></a><a class="delete" href="#"><i class="far fa-trash-can"></i></a></div></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </section>

        <section class="summary-grid">
            <div class="summary-card">
                <h3>Role Distribution</h3>
                <div class="summary-row purple"><span>Administrators</span><span class="purple-text"><?php echo $adminCount ?: 1; ?></span></div>
                <div class="summary-row blue"><span>Volunteers</span><span class="blue-text"><?php echo $volunteerCount ?: 3; ?></span></div>
            </div>
            <div class="summary-card">
                <h3>Status Overview</h3>
                <div class="summary-row green"><span>Active Users</span><span class="green-text"><?php echo $activeCount ?: 3; ?></span></div>
                <div class="summary-row gray"><span>Inactive Users</span><span><?php echo $inactiveCount ?: 1; ?></span></div>
            </div>
        </section>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
