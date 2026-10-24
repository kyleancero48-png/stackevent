<?php
require_once __DIR__ . '/includes/bootstrap.php';
require_once __DIR__ . '/includes/layout.php';
require_once __DIR__ . '/classes/participants.php';

require_login();

$participantsModel = new Participants($conn);
$participants = $participantsModel->getAll();
$totalParticipants = count($participants);

render_head('Participants');
render_nav('participants');
?>
<div class="page-wrap">
    <?php render_flash(); ?>

    <div class="page-header">
        <div>
            <div class="eyebrow">ls -la /registration_desk</div>
            <h1 class="page-title">Participants<span class="cursor"></span></h1>
            <div class="page-sub"><span class="dot"></span><?= $totalParticipants ?> record(s) found</div>
        </div>
        <div class="page-actions">
            <button type="button" class="btn-add" data-bs-toggle="modal" data-bs-target="#addParticipant">
                <i class="bi bi-plus-lg"></i> Add participant
            </button>
        </div>
    </div>

    <div class="table-card">
        <div class="table-responsive">
            <table class="table table-dark align-middle mb-0">
                <thead>
                    <tr>
                        <th scope="col">ID</th>
                        <th scope="col">Participant</th>
                        <th scope="col">Email</th>
                        <th scope="col">Gender</th>
                        <th scope="col">Birthdate</th>
                        <th scope="col">City</th>
                        <th scope="col">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($participants === []): ?>
                        <tr>
                            <td colspan="7">
                                <div class="empty-state">
                                    <div class="empty-prompt">no records</div>
                                    No participants yet. Add the first one to get started.
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>

                    <?php foreach ($participants as $participant):
                        $participantId = (int) $participant->participants_id;
                        $fullName = $participant->first_name . ' ' . $participant->last_name;
                        $genderValue = (string) ($participant->gender ?? '');
                        $genderLower = strtolower($genderValue);
                        $genderClass = $genderLower === 'female' ? 'badge-female' : ($genderLower === 'male' ? 'badge-male' : 'badge-other');
                        ?>
                        <tr>
                            <th scope="row" class="id-cell"><?= e(format_id($participantId)) ?></th>
                            <td>
                                <div class="participant-cell">
                                    <div class="avatar-chip"><?= e(initials($fullName)) ?></div>
                                    <span class="participant-name"><?= e($fullName) ?></span>
                                </div>
                            </td>
                            <td class="email-cell"><?= e($participant->email ?: '—') ?></td>
                            <td><span class="badge-gender <?= e($genderClass) ?>"><?= e($genderValue !== '' ? $genderValue : 'Unset') ?></span></td>
                            <td class="date-cell"><?= e(format_date($participant->birthdate)) ?></td>
                            <td class="city-cell"><?= e($participant->city ?: '—') ?></td>
                            <td>
                                <button type="button" class="action-btn edit" data-bs-toggle="modal" data-bs-target="#updateParticipant<?= $participantId ?>" title="Edit" aria-label="Edit participant">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <button type="button" class="action-btn delete" data-bs-toggle="modal" data-bs-target="#deleteParticipant<?= $participantId ?>" title="Delete" aria-label="Delete participant">
                                    <i class="bi bi-trash3"></i>
                                </button>

                                <?php include 'modals/updateParticipant.php'; ?>
                                <?php include 'modals/deleteParticipant.php'; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include 'modals/addParticipant.php'; ?>

<?php render_footer(); ?>
