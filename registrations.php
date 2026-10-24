<?php
require_once __DIR__ . '/includes/bootstrap.php';
require_once __DIR__ . '/includes/layout.php';
require_once __DIR__ . '/classes/registrations.php';
require_once __DIR__ . '/classes/participants.php';
require_once __DIR__ . '/classes/events.php';

require_login();

$registrations = (new Registrations($conn))->getAll();
$participants = (new Participants($conn))->getAll();
$events = (new Events($conn))->getAll();
$totalRegistrations = count($registrations);
$canRegister = $participants !== [] && $events !== [];

render_head('Registrations');
render_nav('registrations');
?>
<div class="page-wrap">
    <?php render_flash(); ?>

    <div class="page-header">
        <div>
            <div class="eyebrow">tail -n 100 /registrations.log</div>
            <h1 class="page-title">Registrations<span class="cursor"></span></h1>
            <div class="page-sub"><span class="dot"></span><?= $totalRegistrations ?> record(s) found</div>
        </div>
        <div class="page-actions">
            <button type="button" class="btn-add" data-bs-toggle="modal" data-bs-target="#addRegistration" <?= $canRegister ? '' : 'disabled' ?>>
                <i class="bi bi-plus-lg"></i> New registration
            </button>
        </div>
    </div>

    <?php if (!$canRegister): ?>
        <div class="alert-terminal alert-info" role="alert">
            <span class="alert-prompt">[..]</span>
            <span>
                Add at least one <a href="<?= e(url('participants.php')) ?>">participant</a>
                and one <a href="<?= e(url('events.php')) ?>">event</a> before logging registrations.
            </span>
        </div>
    <?php endif; ?>

    <div class="table-card">
        <div class="table-responsive">
            <table class="table table-dark align-middle mb-0">
                <thead>
                    <tr>
                        <th scope="col">ID</th>
                        <th scope="col">Participant</th>
                        <th scope="col">Event</th>
                        <th scope="col">Event date</th>
                        <th scope="col">Registered</th>
                        <th scope="col">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($registrations === []): ?>
                        <tr>
                            <td colspan="6">
                                <div class="empty-state">
                                    <div class="empty-prompt">no records</div>
                                    No registrations yet. Log the first participant for an event.
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>

                    <?php foreach ($registrations as $registration):
                        $registrationId = (int) $registration->registration_id;
                        $participantName = $registration->first_name . ' ' . $registration->last_name;
                        ?>
                        <tr>
                            <th scope="row" class="id-cell"><?= e(format_id($registrationId)) ?></th>
                            <td>
                                <div class="participant-cell">
                                    <div class="avatar-chip"><?= e(initials($participantName)) ?></div>
                                    <div>
                                        <div class="participant-name"><?= e($participantName) ?></div>
                                        <div class="event-meta"><?= e($registration->email ?: 'no email') ?></div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="participant-name"><?= e($registration->event_name ?: 'Event removed') ?></span>
                                <div class="event-meta"><?= e($registration->organizer_name ?: 'Unassigned organizer') ?></div>
                            </td>
                            <td class="date-cell"><?= e(format_date($registration->event_date)) ?></td>
                            <td class="date-cell"><?= e(format_datetime($registration->registration_date)) ?></td>
                            <td>
                                <button type="button" class="action-btn delete" data-bs-toggle="modal" data-bs-target="#deleteRegistration<?= $registrationId ?>" title="Delete" aria-label="Delete registration">
                                    <i class="bi bi-trash3"></i>
                                </button>

                                <?php include 'modals/deleteRegistration.php'; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include 'modals/addRegistration.php'; ?>

<?php render_footer(); ?>
