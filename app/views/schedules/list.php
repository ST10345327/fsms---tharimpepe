<?php
$pageTitle = 'Volunteers';
$pageSubtitle = 'Tharimpepe Feeding Scheme';
require_once __DIR__ . "/../includes/layout-header.php";
?>

<nav class="fsms-module-tabs" aria-label="Volunteer views">
    <a class="fsms-module-tab active" href="VolunteerScheduleController.php?action=list" aria-current="page">Schedule View</a>
    <a class="fsms-module-tab" href="VolunteerController.php?action=list">Volunteers List</a>
</nav>

<div class="fsms-section-head">
    <h2>Weekly Schedule</h2>
    <a class="fsms-btn fsms-btn-primary" href="VolunteerScheduleController.php?action=create">
        <i class="fas fa-plus"></i> Assign Volunteer
    </a>
</div>

<?php if (!empty($_SESSION['success'])): ?>
    <div class="fsms-alert fsms-alert-success"><i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?></div>
<?php endif; ?>
<?php if (!empty($_SESSION['error'])): ?>
    <div class="fsms-alert fsms-alert-danger"><i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></div>
<?php endif; ?>

<p class="text-muted" style="font-size:13px;margin:-8px 0 16px;">Week of <?php echo htmlspecialchars($weekLabel ?? date('M d, Y')); ?></p>

<section class="fsms-week-grid" aria-label="Weekly volunteer schedule">
    <?php foreach (($week ?? []) as $day): ?>
        <article class="fsms-day-card">
            <div class="fsms-day-card-title">
                <i class="far fa-calendar" aria-hidden="true"></i>
                <div>
                    <strong><?php echo htmlspecialchars($day[0]); ?></strong>
                    <span><?php echo htmlspecialchars($day[1]); ?></span>
                </div>
            </div>
            <?php if (!empty($day[2])): ?>
                <?php foreach ($day[2] as $volunteerName): ?>
                    <div class="fsms-volunteer-chip">
                        <i class="fas fa-user" aria-hidden="true"></i>
                        <span><?php echo htmlspecialchars($volunteerName); ?></span>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="fsms-day-empty">No volunteers assigned</div>
            <?php endif; ?>
        </article>
    <?php endforeach; ?>
</section>

<?php require_once __DIR__ . "/../includes/layout-footer.php"; ?>
