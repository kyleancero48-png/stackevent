<?php
require_once __DIR__ . '/includes/bootstrap.php';
require_once __DIR__ . '/includes/layout.php';
require_once __DIR__ . '/classes/organizers.php';

require_login();

$organizersModel = new Organizers($conn);
$organizers = $organizersModel->getAll();
$totalOrganizers = count($organizers);

render_head('Organizers');
render_nav('organizers');
?>
<div class="page-wrap">
    <?php render_flash(); ?>

    <div class="page-header">
        <div>
            <div class="eyebrow">ps aux | grep organizers</div>
            <h1 class="page-title">Organizers<span class="cursor"></span></h1>
            <div class="page-sub"><span class="dot"></span><?= $totalOrganizers ?> record(s) found</div>
        </div>
        <div class="page-actions">
            <button type="button" class="btn-add" data-bs-toggle="modal" data-bs-target="#addOrganizer">
                <i class="bi bi-plus-lg"></i> Add organizer
            </button>
        </div>
    </div>

    <div class="table-card">
        <div class="table-responsive">
            <table class="table table-dark align-middle mb-0">
                <thead>
                    <tr>
                        <th scope="col">ID</th>
                        <th scope="col">Organizer</th>
                        <th scope="col">Email</th>
                        <th scope="col">Contact</th>
                        <th scope="col">Events</th>
                        <th scope="col">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($organizers === []): ?>
                        <tr>
                            <td colspan="6">
                                <div class="empty-state">
                                    <div class="empty-prompt">no records</div>
                                    No organizers yet. Add the first partner organization.
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>

                    <?php foreach ($organizers as $organizer):
                        $organizerId = (int) $organizer->organizer_id;
                        $organizerName = (string) $organizer->organizer_name;
                        ?>
                        <tr>
                            <th scope="row" class="id-cell"><?= e(format_id($organizerId)) ?></th>
                            <td>
                                <div class="participant-cell">
                                    <div class="avatar-chip"><?= e(initials($organizerName)) ?></div>
                                    <span class="participant-name"><?= e($organizerName) ?></span>
                                </div>
                            </td>
                            <td class="email-cell"><?= e($organizer->email) ?></td>
                            <td class="city-cell"><?= e($organizer->contact_number ?: '—') ?></td>
                            <td><span class="tag tag-accent"><?= (int) $organizer->event_count ?> event(s)</span></td>
                            <td>
                                <button type="button" class="action-btn edit" data-bs-toggle="modal" data-bs-target="#updateOrganizer<?= $organizerId ?>" title="Edit" aria-label="Edit organizer">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <button type="button" class="action-btn delete" data-bs-toggle="modal" data-bs-target="#deleteOrganizer<?= $organizerId ?>" title="Delete" aria-label="Delete organizer">
                                    <i class="bi bi-trash3"></i>
                                </button>

                                <?php include 'modals/updateOrganizer.php'; ?>
                                <?php include 'modals/deleteOrganizer.php'; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include 'modals/addOrganizer.php'; ?>

<?php render_footer(); ?>
