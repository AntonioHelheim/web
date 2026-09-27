<?php
require_once __DIR__ . '/common.php';

require_once __DIR__ . '/../../partials/info-tip.php';
requireCapabilityPage($pdo, 'induction.view', '../../acceso-denegado.php');

aplicarCabecerasSeguridad();
$embeddedActivityMode = isset($_GET['embedded']) && (string) $_GET['embedded'] === '1';
if ($embeddedActivityMode) {
    // El HUB Mi espacio sólo puede embeber actividades desde el mismo origen.
    header('X-Frame-Options: SAMEORIGIN');
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$csrfTokenEscaped = htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8');
$userEmail = htmlspecialchars($_SESSION['user_email'] ?? '', ENT_QUOTES, 'UTF-8');
$assetVersionEscaped = htmlspecialchars($ASSET_VERSION, ENT_QUOTES, 'UTF-8');
$directActivityMode = filter_input(INPUT_GET, 'start', FILTER_VALIDATE_INT) > 0;

$jsKeys = [
    'my_induction_error_response','my_induction_loading','my_induction_load_error','my_induction_empty',
    'my_induction_status_pending','my_induction_status_approved','my_induction_status_failed',
    'my_induction_status_overdue','my_induction_status_in_progress','my_induction_status_completed_approved','my_induction_status_completed_failed',
    'activities_filter_all','worker_status_overdue','worker_status_in_progress','worker_status_approved','worker_status_failed',
    'my_induction_no_attempts','my_induction_take_course','my_induction_download_certificate',
    'my_induction_due_prefix','my_induction_attempts_used_prefix','my_induction_detail_loading',
    'my_induction_detail_load_error','my_induction_answer_all_required','my_induction_submit_error',
    'my_induction_passed','my_induction_failed_with_attempts','my_induction_failed_no_attempts',
    'my_induction_eval_kicker','my_induction_question_of','my_induction_answered_of','my_induction_previous',
    'my_induction_next','my_induction_choose_to_continue','my_induction_confirm_submit','my_induction_support_material','my_induction_course_materials_title','my_induction_course_materials_intro',
    'my_induction_overdue_badge','my_induction_overdue_warning',
    'activity_save_draft','activity_exit','activity_draft_saved','activity_draft_restored',
    'activity_unavailable_title','activity_unavailable_text','activity_back_to_list',
    'activity_complete_before_submit','activity_confirm_submit','activity_submit_late','activity_confirm_submit_late','my_induction_send_answers',
];
$jsStrings = [];
foreach ($jsKeys as $k) $jsStrings[$k] = t($k);
?>
<!DOCTYPE html>
<html lang="<?= htmlspecialchars(idiomaActual(), ENT_QUOTES, 'UTF-8') ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= tt('my_induction_page_title') ?></title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet"
          integrity="sha384-LN+7fdVzj6u52u30Kp6M/trliBMCMKTyK833zpbD+pXdCLuTusPj697FH4R/5mcr" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <!-- build: <?= $assetVersionEscaped ?> -->
    <link rel="stylesheet" href="../../css/style.css?v=<?= $assetVersionEscaped ?>-p56">
<link rel="stylesheet" href="../../css/sct-main-sections.css?v=20260923-p79">
    <link rel="stylesheet" href="../../css/induccion.css?v=20260922-p56">


</head>
<body class="sct-module-page<?= $directActivityMode ? ' sct-activity-mode' : '' ?><?= $embeddedActivityMode ? ' sct-embedded-activity' : '' ?>">

    <div class="container sct-main-shell sct-personal-module-shell" data-csrf-token="<?php echo $csrfTokenEscaped; ?>" data-activity-user="<?= htmlspecialchars(hash('sha256', (string) ($_SESSION['user_email'] ?? '')), ENT_QUOTES, 'UTF-8') ?>">

        <?php
        $sctNavbarBasePath = '../../';
        $sctNavbarBackHref = '../usuarios/gestiones.php';
        if (!$embeddedActivityMode) { require __DIR__ . '/../../partials/app-navbar.php'; }
        ?>

        <section class="welcome-hero sct-main-hero sct-personal-section-hero">
            <div class="sct-main-hero__copy">
                <span class="section-label sct-main-pill"><i class="bi bi-mortarboard" aria-hidden="true"></i><?= htmlspecialchars(t('mgmt_filter_personal'), ENT_QUOTES, 'UTF-8') ?></span>
                <h1 class="section-title sct-main-title mb-0"><?= tt('my_induction_title') ?></h1>
                <p class="sct-page-intro sct-main-intro"><?= htmlspecialchars(t('my_induction_intro') . ' ' . t('users_session_as') . ' ' . ($_SESSION['user_email'] ?? ''), ENT_QUOTES, 'UTF-8') ?></p>
            </div>
        </section>
<?php $sctModuleMode = 'personal'; require __DIR__ . '/../../partials/module-context.php'; ?>
<?php $sctPersonalNavActive = 'induction'; $sctPersonalNavBasePath = '../'; require __DIR__ . '/../../partials/personal-activity-nav.php'; ?>

        <section class="quick-links induction-personal-shell">

            <div id="myInductionBrowse" class="sct-activity-browse<?= $directActivityMode ? ' d-none' : '' ?>"<?= $directActivityMode ? ' hidden aria-hidden="true"' : '' ?>>
                <div id="misAlert" class="alert d-none mb-3" role="alert" aria-live="polite"></div>

                <div id="misStatus" class="alert alert-info" role="status"><?= tt('my_induction_loading') ?></div>

                <div id="misLista" class="d-none induction-course-list" data-sct-status-filter-ready="1"></div>
            </div>

            <!-- Evaluación guiada: una pregunta por vez para reducir carga visual -->
            <div id="rendirPanel" class="feature-card induction-evaluation d-none" aria-live="polite">
                <header class="induction-evaluation__header">
                    <div>
                        <span class="induction-evaluation__eyebrow"><?= tt('my_induction_eval_kicker') ?></span>
                        <h2 id="rendirTitulo">-</h2>
                    </div>
                    <?php if (!$embeddedActivityMode): ?>
                    <button type="button" id="rendirCerrarBtn" class="btn btn-outline-custom btn-sm"><i class="bi bi-box-arrow-left"></i> <?= tt('activity_exit') ?></button>
                    <?php endif; ?>
                </header>

                <div class="induction-evaluation__progress">
                    <div class="induction-evaluation__progress-row">
                        <span id="rendirPasoTexto">-</span>
                        <span id="rendirRespondidasTexto">-</span>
                    </div>
                    <div class="induction-progress-track" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0">
                        <div id="rendirProgresoBar" class="induction-progress-bar"></div>
                    </div>
                </div>

                <div class="induction-evaluation__body">
                    <div id="rendirAlert" class="alert d-none mb-2" role="alert" aria-live="assertive"></div>
                    <div id="rendirPreguntas"></div>
                </div>

                <div class="induction-evaluation__actions">
                    <button type="button" id="rendirAnteriorBtn" class="btn btn-outline-custom"><?= tt('my_induction_previous') ?></button>
                    <button type="button" id="rendirGuardarBtn" class="btn btn-outline-custom"><i class="bi bi-save"></i> <?= tt('activity_save_draft') ?></button>
                    <button type="button" id="rendirSiguienteBtn" class="btn btn-primary-custom"><?= tt('my_induction_next') ?></button>
                    <button type="button" id="rendirEnviarBtn" class="btn btn-primary-custom d-none"><?= tt('my_induction_send_answers') ?></button>
                </div>
            </div>

        </section>

    </div>

    <script id="myInductionI18n" type="application/json"><?= json_encode($jsStrings, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../../js/sct-activity-session.js?v=20260922-p56"></script>
    <script src="../../js/induccion-mis.js?v=<?= $assetVersionEscaped ?>-p56"></script>
    <script src="../../js/lang-switcher.js?v=<?= $assetVersionEscaped ?>"></script>

    <?php if (!$embeddedActivityMode) { require __DIR__ . '/../../partials/app-footer.php'; } ?>

<script src="../../js/sct-module-ui.js?v=20260920-p43"></script>
</body>
</html>
