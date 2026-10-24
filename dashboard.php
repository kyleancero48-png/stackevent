<?php
require_once __DIR__ . '/includes/bootstrap.php';
require_once __DIR__ . '/includes/layout.php';

require_login();

$totalParticipants = (int) $conn->query('SELECT COUNT(*) FROM participants')->fetchColumn();
$totalEvents = (int) $conn->query('SELECT COUNT(*) FROM events')->fetchColumn();
$totalOrganizers = (int) $conn->query('SELECT COUNT(*) FROM organizers')->fetchColumn();
$totalRegistrations = (int) $conn->query('SELECT COUNT(*) FROM registrations')->fetchColumn();
$upcomingCount = (int) $conn->query('SELECT COUNT(*) FROM events WHERE event_date >= CURDATE()')->fetchColumn();

$upcomingEvents = $conn->query(
    'SELECT e.event_id, e.event_name, e.event_date, e.venue, o.organizer_name
     FROM events e
     LEFT JOIN organizers o ON o.organizer_id = e.organizer_id
     WHERE e.event_date >= CURDATE()
     ORDER BY e.event_date ASC
     LIMIT 5'
)->fetchAll();

$recentRegistrations = $conn->query(
    'SELECT r.registration_id, r.registration_date, p.first_name, p.last_name,
            e.event_name, e.event_date
     FROM registrations r
     JOIN participants p ON p.participants_id = r.participant_id
     LEFT JOIN events e ON e.event_id = r.event_id
     ORDER BY r.registration_date DESC, r.registration_id DESC
     LIMIT 5'
)->fetchAll();

render_head('Dashboard');
render_nav('dashboard');
?>
<div class="page-wrap">
    <?php render_flash(); ?>

    <div class="page-header">
        <div>
            <div class="eyebrow">tail -f /var/log/stackevent.log</div>
            <h1 class="page-title">Dashboard<span class="cursor"></span></h1>
            <div class="page-sub"><span class="dot"></span>Live snapshot of the event desk</div>
        </div>
        <div class="page-actions">
            <a href="<?= e(url('registrations.php')) ?>" class="btn-add">
                <i class="bi bi-journal-plus"></i> New registration
            </a>
        </div>
    </div>

    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-label">Participants</div>
            <div class="stat-value"><?= $totalParticipants ?></div>
            <div class="stat-hint">registered profiles</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Events</div>
            <div class="stat-value accent"><?= $totalEvents ?></div>
            <div class="stat-hint"><?= $upcomingCount ?> upcoming</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Organizers</div>
            <div class="stat-value"><?= $totalOrganizers ?></div>
            <div class="stat-hint">event partners</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Registrations</div>
            <div class="stat-value"><?= $totalRegistrations ?></div>
            <div class="stat-hint">seats logged</div>
        </div>
    </div>

    <div class="dash-grid">
        <section class="panel">
            <div class="panel-head">
                <h2 class="panel-title">Upcoming events</h2>
                <a href="<?= e(url('events.php')) ?>" class="panel-link">View all</a>
            </div>
            <div class="panel-body">
                <?php if ($upcomingEvents === []): ?>
                    <div class="empty-state">
                        <div class="empty-prompt">no upcoming events</div>
                        Nothing on the calendar yet. <a href="<?= e(url('events.php')) ?>">Create an event</a>.
                    </div>
                <?php endif; ?>

                <?php foreach ($upcomingEvents as $event): ?>
                    <div class="event-row">
                        <div>
                            <div class="event-name"><?= e($event->event_name) ?></div>
                            <div class="event-meta">
                                <?= e($event->venue ?: 'Venue TBA') ?>
                                <?php if (!empty($event->organizer_name)): ?>
                                    &middot; <?= e($event->organizer_name) ?>
                                <?php endif; ?>
                            </div>
                        </div>
                        <span class="date-chip"><?= e(format_date($event->event_date)) ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>

        <section class="panel">
            <div class="panel-head">
                <h2 class="panel-title">Recent registrations</h2>
                <a href="<?= e(url('registrations.php')) ?>" class="panel-link">View all</a>
            </div>
            <div class="panel-body">
                <?php if ($recentRegistrations === []): ?>
                    <div class="empty-state">
                        <div class="empty-prompt">no registrations yet</div>
                        <a href="<?= e(url('registrations.php')) ?>">Register a participant</a> for an event.
                    </div>
                <?php endif; ?>

                <?php foreach ($recentRegistrations as $registration):
                    $participantName = $registration->first_name . ' ' . $registration->last_name;
                    ?>
                    <div class="event-row">
                        <div class="d-flex align-items-center gap-3">
                            <div class="avatar-chip"><?= e(initials($participantName)) ?></div>
                            <div>
                                <div class="event-name"><?= e($participantName) ?></div>
                                <div class="event-meta">
                                    <?= e($registration->event_name ?: 'Event removed') ?>
                                    &middot; <?= e(format_date($registration->event_date)) ?>
                                </div>
                            </div>
                        </div>
                        <span class="date-chip" title="Registered <?= e(format_datetime($registration->registration_date)) ?>">
                            <?= e(format_date($registration->registration_date)) ?>
                        </span>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>
    </div>
</div>
<?php render_footer(); ?>
