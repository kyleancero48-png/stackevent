<?php
require_once __DIR__ . '/includes/bootstrap.php';
require_once __DIR__ . '/includes/layout.php';
require_once __DIR__ . '/classes/events.php';
require_once __DIR__ . '/classes/organizers.php';

require_login();

$eventsModel = new Events($conn);
$events = $eventsModel->getAll();
$organizers = (new Organizers($conn))->getAll();
$totalEvents = count($events);
$today = date('Y-m-d');
$upcomingCount = count(array_filter($events, static fn ($event) => (string) $event->event_date >= $today));

render_head('Events');
render_nav('events');
?>
<div class="page-wrap">
    <?php render_flash(); ?>

    <div class="page-header">
        <div>
            <div class="eyebrow">crontab -l /events</div>
            <h1 class="page-title">Events<span class="cursor"></span></h1>
            <div class="page-sub"><span class="dot"></span><?= $totalEvents ?> record(s) found &middot; <?= $upcomingCount ?> upcoming</div>
        </div>
        <div class="page-actions">
            <button type="button" class="btn-add" data-bs-toggle="modal" data-bs-target="#addEvent">
                <i class="bi bi-plus-lg"></i> Add event
            </button>
        </div>
    </div>

    <div class="table-card">
        <div class="table-responsive">
            <table class="table table-dark align-middle mb-0">
                <thead>
                    <tr>
                        <th scope="col">ID</th>
                        <th scope="col">Event</th>
                        <th scope="col">Venue</th>
                        <th scope="col">Date</th>
                        <th scope="col">Organizer</th>
                        <th scope="col">Registrations</th>
                        <th scope="col">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($events === []): ?>
                        <tr>
                            <td colspan="7">
                                <div class="empty-state">
                                    <div class="empty-prompt">no records</div>
                                    No events yet. Add the first event to the calendar.
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>

                    <?php foreach ($events as $event):
                        $eventId = (int) $event->event_id;
                        $isPast = (string) $event->event_date < $today;
                        ?>
                        <tr>
                            <th scope="row" class="id-cell"><?= e(format_id($eventId)) ?></th>
                            <td>
                                <span class="participant-name"><?= e($event->event_name) ?></span>
                                <?php if ($isPast): ?>
                                    <span class="tag ms-1">past</span>
                                <?php endif; ?>
                            </td>
                            <td class="venue-cell"><?= e($event->venue ?: '—') ?></td>
                            <td class="date-cell"><?= e(format_date($event->event_date)) ?></td>
                            <td class="city-cell"><?= e($event->organizer_name ?: 'Unassigned') ?></td>
                            <td><span class="tag tag-accent"><?= (int) $event->registration_count ?> reg</span></td>
                            <td>
                                <button type="button" class="action-btn edit" data-bs-toggle="modal" data-bs-target="#updateEvent<?= $eventId ?>" title="Edit" aria-label="Edit event">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <button type="button" class="action-btn delete" data-bs-toggle="modal" data-bs-target="#deleteEvent<?= $eventId ?>" title="Delete" aria-label="Delete event">
                                    <i class="bi bi-trash3"></i>
                                </button>

                                <?php include 'modals/updateEvent.php'; ?>
                                <?php include 'modals/deleteEvent.php'; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include 'modals/addEvent.php'; ?>

<?php render_footer(); ?>
