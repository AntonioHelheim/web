<?php

require_once __DIR__ . '/../../session_bootstrap.php';
require_once __DIR__ . '/../../lib/db.php';
require_once __DIR__ . '/../../lib/auth.php';
require_once __DIR__ . '/../../i18n.php';
require_once __DIR__ . '/../../app/Evaluations/UserEvaluationRepository.php';

requireLoginPage('../../acceso-denegado.php');
aplicarCabecerasSeguridad();

$userId = (string)currentUserId();
$repository = new SctUserEvaluationRepository($pdo);
$evaluations = $repository->allForUser($userId);

function evaluationStatusLabel(string $status): string
{
    $map = [
        'pending' => 'evaluations_status_pending',
        'in_progress' => 'evaluations_status_in_progress',
        'approved' => 'evaluations_status_approved',
        'failed' => 'evaluations_status_failed',
        'completed' => 'evaluations_status_completed',
    ];

    return t($map[$status] ?? 'evaluations_status_pending');
}

function evaluationTypeLabel(string $type): string
{
    $map = [
        'induccion' => 'evaluations_type_induction',
        'autoevaluacion' => 'evaluations_type_self_assessment',
        'auditoria' => 'evaluations_type_audit',
        'onboarding' => 'evaluations_type_initial',
    ];

    return t($map[$type] ?? 'evaluations_type_other');
}
?>
<!doctype html>
<html lang="<?= htmlspecialchars(idiomaActual(), ENT_QUOTES, 'UTF-8') ?>" class="sct-app-frontend">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title><?= tt('evaluations_page_title') ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../../css/style.css?v=<?= htmlspecialchars(SCT_ASSET_VERSION, ENT_QUOTES, 'UTF-8') ?>">
</head>
<body>
<?php
$sctNavbarBasePath = '../../';
require __DIR__ . '/../../partials/app-navbar.php';
?>

<main class="worker-home">
    <section class="worker-home__greeting">
        <div>
            <span class="worker-home__eyebrow">Safety Control Tower</span>
            <h1><?= tt('evaluations_title') ?></h1>
            <p><?= tt('evaluations_intro') ?></p>
        </div>
    </section>

    <section class="feature-card evaluation-history-card">
        <?php if (!$evaluations): ?>
            <div class="alert alert-info mb-0"><?= tt('evaluations_empty') ?></div>
        <?php else: ?>
            <div class="evaluation-history-list">
                <?php foreach ($evaluations as $evaluation): ?>
                    <?php
                    $name = !empty($evaluation['name_key'])
                        ? t((string)$evaluation['name_key'])
                        : (string)$evaluation['name'];
                    $status = (string)$evaluation['status'];
                    $result = $evaluation['result_percentage'];
                    ?>
                    <article class="evaluation-history-item" data-status="<?= htmlspecialchars($status, ENT_QUOTES, 'UTF-8') ?>">
                        <div class="evaluation-history-item__main">
                            <div class="evaluation-history-item__meta">
                                <span><?= htmlspecialchars(evaluationTypeLabel((string)$evaluation['type']), ENT_QUOTES, 'UTF-8') ?></span>
                                <span>·</span>
                                <span><?= htmlspecialchars(evaluationStatusLabel($status), ENT_QUOTES, 'UTF-8') ?></span>
                            </div>

                            <h2><?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?></h2>

                            <?php if (!empty($evaluation['description'])): ?>
                                <p><?= htmlspecialchars((string)$evaluation['description'], ENT_QUOTES, 'UTF-8') ?></p>
                            <?php endif; ?>

                            <div class="evaluation-history-item__details">
                                <?php if ($result !== null): ?>
                                    <span>
                                        <i class="bi bi-bar-chart"></i>
                                        <?= tt('evaluations_result') ?>:
                                        <strong><?= htmlspecialchars(number_format((float)$result, 1), ENT_QUOTES, 'UTF-8') ?>%</strong>
                                    </span>
                                <?php endif; ?>

                                <?php if ((int)$evaluation['attempts_allowed'] > 0): ?>
                                    <span>
                                        <i class="bi bi-arrow-repeat"></i>
                                        <?= tt('evaluations_attempts') ?>:
                                        <?= (int)$evaluation['attempts_used'] ?>/<?= (int)$evaluation['attempts_allowed'] ?>
                                    </span>
                                <?php endif; ?>

                                <?php if (!empty($evaluation['deadline'])): ?>
                                    <span>
                                        <i class="bi bi-calendar-event"></i>
                                        <?= tt('evaluations_deadline') ?>:
                                        <?= htmlspecialchars(substr((string)$evaluation['deadline'], 0, 10), ENT_QUOTES, 'UTF-8') ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="evaluation-history-item__actions">
                            <?php if (!empty($evaluation['resume_url'])): ?>
                                <a
                                    class="btn btn-primary-custom btn-sm"
                                    href="../../<?= htmlspecialchars((string)$evaluation['resume_url'], ENT_QUOTES, 'UTF-8') ?>"
                                >
                                    <?= tt('evaluations_resume') ?>
                                    <i class="bi bi-arrow-right"></i>
                                </a>
                            <?php else: ?>
                                <span class="evaluation-history-item__status">
                                    <?= htmlspecialchars(evaluationStatusLabel($status), ENT_QUOTES, 'UTF-8') ?>
                                </span>
                            <?php endif; ?>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
