<?php
require_once __DIR__ . '/common.php';

requireCapabilityPage($pdo, 'induction.view', '../../acceso-denegado.php');

aplicarCabecerasSeguridad();

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$csrfTokenEscaped = htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8');
$userEmail = htmlspecialchars($_SESSION['user_email'] ?? '', ENT_QUOTES, 'UTF-8');
$assetVersionEscaped = htmlspecialchars($ASSET_VERSION, ENT_QUOTES, 'UTF-8');

$jsKeys = [
    'my_induction_error_response','my_induction_loading','my_induction_load_error','my_induction_empty',
    'my_induction_status_pending','my_induction_status_approved','my_induction_status_failed',
    'my_induction_no_attempts','my_induction_take_course','my_induction_download_certificate',
    'my_induction_due_prefix','my_induction_attempts_used_prefix','my_induction_detail_loading',
    'my_induction_detail_load_error','my_induction_answer_all_required','my_induction_submit_error',
    'my_induction_passed','my_induction_failed_with_attempts','my_induction_failed_no_attempts',
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
    <link rel="stylesheet" href="../../css/style.css?v=<?= $assetVersionEscaped ?>">

    <style>
        .welcome-hero { padding: 4rem 0 2rem; }
        .welcome-topbar {
            display: flex; justify-content: space-between; align-items: center;
            padding: 1.25rem 0; border-bottom: 1px solid rgba(0,0,0,0.08);
        }
        .welcome-topbar .brand-symbol img { height: 32px; }
        .topbar-actions { display: flex; gap: 0.5rem; align-items: center; }
        .welcome-greeting-icon { font-size: 2.5rem; color: #16a34a; margin-bottom: 0.75rem; }
        .quick-links { margin-top: 2rem; margin-bottom: 3rem; }
        .curso-card { border: 1px solid var(--border); border-radius: 16px; padding: 1.25rem; margin-bottom: 1rem; }
        .rendir-pregunta { border-bottom: 1px solid var(--border); padding-bottom: 1rem; margin-bottom: 1rem; }
        .rendir-pregunta:last-child { border-bottom: none; }
    </style>
</head>
<body>

    <div class="container" data-csrf-token="<?php echo $csrfTokenEscaped; ?>">

        <div class="welcome-topbar">
            <div class="brand-wrapper">
                <div class="brand-symbol">
                    <img src="../../images/logos/Logo-SCT-white.png" alt="Safety Control Tower">
                </div>
            </div>
            <div class="topbar-actions">
                <select id="pageLanguageSelect" class="form-select form-select-sm language-select" aria-label="<?= tt('common_language') ?>">
                    <?php foreach (idiomasDisponiblesConNombre() as $code => $name): ?>
                        <option value="<?= htmlspecialchars($code, ENT_QUOTES, 'UTF-8') ?>" <?= $code === idiomaActual() ? 'selected' : '' ?>><?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?></option>
                    <?php endforeach; ?>
                </select>
                <a href="../usuarios/gestiones.php" class="btn btn-outline-custom btn-sm"><?= tt('mgmt_back') ?> <i class="bi bi-arrow-left"></i></a>
                <a href="../../logout.php" class="btn btn-outline-custom btn-sm"><?= tt('common_logout') ?> <i class="bi bi-box-arrow-right"></i></a>
            </div>
        </div>

        <section class="welcome-hero text-center">
            <div class="welcome-greeting-icon"><i class="bi bi-mortarboard"></i></div>
            <span class="section-label">SAFETY CONTROL TOWER</span>
            <h1 class="section-title"><?= tt('my_induction_title') ?></h1>
            <p class="section-description intro-description-centered">
                <?= tt('my_induction_intro') ?> <?= tt('users_session_as') ?> <strong><?php echo $userEmail; ?></strong>.
            </p>
        </section>

        <section class="quick-links">

            <div id="misAlert" class="alert d-none mb-3" role="alert" aria-live="polite"></div>

            <div id="misStatus" class="alert alert-info" role="status"><?= tt('my_induction_loading') ?></div>

            <div id="misLista" class="d-none"></div>

            <!-- Panel para rendir el curso -->
            <div id="rendirPanel" class="feature-card mt-4 d-none">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <h2 class="h5 mb-0" id="rendirTitulo">-</h2>
                    <button type="button" id="rendirCerrarBtn" class="btn btn-outline-custom btn-sm"><?= tt('my_induction_close') ?></button>
                </div>
                <div id="rendirAlert" class="alert d-none" role="alert" aria-live="polite"></div>
                <div id="rendirPreguntas"></div>
                <button type="button" id="rendirEnviarBtn" class="btn btn-primary-custom mt-2"><?= tt('my_induction_send_answers') ?></button>
            </div>

        </section>

    </div>

    <script id="myInductionI18n" type="application/json"><?= json_encode($jsStrings, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../../js/induccion-mis.js?v=<?= $assetVersionEscaped ?>"></script>
    <script src="../../js/lang-switcher.js?v=<?= $assetVersionEscaped ?>"></script>
</body>
</html>
