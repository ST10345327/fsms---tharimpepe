<?php $pageTitle = 'Volunteers'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Volunteers - FSMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="/assets/css/fsms-ui.css">
    <style>
        .volunteer-page { padding: 30px; }
        .tabs {
            border-bottom: 1px solid #dfe3e8;
            display: flex;
            gap: 32px;
            margin-bottom: 30px;
        }
        .tab-link {
            border-bottom: 2px solid transparent;
            color: #334155;
            font-size: 20px;
            font-weight: 700;
            padding: 16px 20px 12px;
            text-decoration: none;
        }
        .tab-link.active {
            border-color: #1b3a5c;
            color: #071326;
        }
        .section-head {
            align-items: center;
            display: flex;
            justify-content: space-between;
            margin-bottom: 28px;
        }
        .section-head h2 {
            color: #071326;
            font-size: 24px;
            font-weight: 700;
            margin: 0;
        }
        .assign-btn {
            background: #1b3a5c;
            border: 0;
            border-radius: 10px;
            color: #fff;
            font-size: 20px;
            font-weight: 700;
            min-height: 50px;
            padding: 10px 22px;
            text-decoration: none;
        }
        .assign-btn:hover { color: #fff; background: #2e4a6c; }
        .week-grid {
            display: grid;
            gap: 20px;
            grid-template-columns: repeat(4, minmax(0, 1fr));
        }
        .day-card {
            background: #fff;
            border: 1px solid #dfe3e8;
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(15, 23, 42, 0.16);
            min-height: 148px;
            padding: 24px 20px;
        }
        .day-title {
            align-items: flex-start;
            display: flex;
            gap: 12px;
            margin-bottom: 16px;
        }
        .day-title i { color: #475569; margin-top: 4px; }
        .day-title strong { color: #071326; display: block; font-size: 18px; line-height: 1.1; }
        .day-title span { color: #334155; font-size: 14px; }
        .volunteer-chip {
            align-items: center;
            background: #eaf2ff;
            border-radius: 10px;
            color: #005cff;
            display: flex;
            gap: 10px;
            margin-top: 10px;
            min-height: 44px;
            padding: 10px 16px;
        }
        .empty-day {
            color: #8b93a1;
            font-style: italic;
            margin-top: 22px;
        }
        @media (max-width: 1200px) { .week-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
        @media (max-width: 700px) { .week-grid { grid-template-columns: 1fr; } }
    </style>
</head>
<body>
    <?php include __DIR__ . "/../includes/navbar.php"; ?>

    <main class="container-fluid volunteer-page">
        <nav class="tabs" aria-label="Volunteer tabs">
            <a class="tab-link active" href="VolunteerScheduleController.php?action=list">Schedule View</a>
            <a class="tab-link" href="VolunteerController.php?action=list">Volunteers List</a>
        </nav>

        <div class="section-head">
            <h2>Weekly Schedule</h2>
            <a class="assign-btn" href="VolunteerScheduleController.php?action=create">
                <i class="fas fa-plus me-2" aria-hidden="true"></i>Assign Volunteer
            </a>
        </div>

        <?php
        $week = [
            ['Monday', '2026-05-05', ['Sarah Johnson', 'Mike Williams']],
            ['Tuesday', '2026-05-06', ['Lisa Brown']],
            ['Wednesday', '2026-05-07', ['Sarah Johnson', 'David Miller']],
            ['Thursday', '2026-05-08', ['Mike Williams', 'Lisa Brown']],
            ['Friday', '2026-05-09', ['Sarah Johnson']],
            ['Saturday', '2026-05-10', []],
            ['Sunday', '2026-05-11', []],
        ];
        ?>

        <section class="week-grid">
            <?php foreach ($week as $day): ?>
                <article class="day-card">
                    <div class="day-title">
                        <i class="far fa-calendar" aria-hidden="true"></i>
                        <div>
                            <strong><?php echo $day[0]; ?></strong>
                            <span><?php echo $day[1]; ?></span>
                        </div>
                    </div>
                    <?php if (!empty($day[2])): ?>
                        <?php foreach ($day[2] as $volunteerName): ?>
                            <div class="volunteer-chip">
                                <i class="fas fa-users" aria-hidden="true"></i>
                                <span><?php echo htmlspecialchars($volunteerName); ?></span>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="empty-day">No volunteers assigned</div>
                    <?php endif; ?>
                </article>
            <?php endforeach; ?>
        </section>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
