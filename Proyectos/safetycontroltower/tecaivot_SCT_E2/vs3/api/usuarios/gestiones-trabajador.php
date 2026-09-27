<?php
$workerMgmtAsset = htmlspecialchars($ASSET_VERSION . '-p54', ENT_QUOTES, 'UTF-8');
?>
<!DOCTYPE html>
<html lang="<?= htmlspecialchars(idiomaActual(), ENT_QUOTES, 'UTF-8') ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars(t('worker_management_page_title'), ENT_QUOTES, 'UTF-8') ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-LN+7fdVzj6u52u30Kp6M/trliBMCMKTyK833zpbD+pXdCLuTusPj697FH4R/5mcr" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../../css/style.css?v=<?= $workerMgmtAsset ?>">
    <link rel="stylesheet" href="../../css/sct-main-sections.css?v=20260923-p79">
    <link rel="stylesheet" href="../../css/gestiones.css?v=<?= $workerMgmtAsset ?>">
</head>
<body class="management-hub-page management-hub-page--worker sct-role-trabajador">
<a class="management-skip-link" href="#worker-management-main"><?= htmlspecialchars(t('welcome_skip_to_content'), ENT_QUOTES, 'UTF-8') ?></a>

<?php
$sctNavbarBasePath = '../../';
$sctNavbarBackHref = '../../bienvenida.php';
$sctNavbarBackLabel = t('nav_home');
$sctNavbarBackIcon = 'bi-arrow-left';
require __DIR__ . '/../../partials/app-navbar.php';
?>

<main id="worker-management-main" class="management-shell management-shell--worker sct-main-shell">
    <header class="management-hero sct-main-hero" aria-labelledby="worker-management-title">
        <div class="sct-main-hero__copy">
            <span class="sct-main-pill"><i class="bi bi-grid-1x2-fill" aria-hidden="true"></i><span><?= htmlspecialchars(t('worker_management_kicker'), ENT_QUOTES, 'UTF-8') ?></span></span>
            <h1 id="worker-management-title" class="sct-main-title"><?= htmlspecialchars(t('worker_management_title'), ENT_QUOTES, 'UTF-8') ?></h1>
            <p class="sct-main-intro"><?= htmlspecialchars(t('worker_management_intro'), ENT_QUOTES, 'UTF-8') ?></p>
        </div>
    </header>

    <div class="management-directory management-directory--worker">
        <section class="management-module-section management-module-section--personal" aria-labelledby="worker-management-access-title">
            <header class="management-section-head management-section-head--directory">
                <span class="management-section-head__icon" aria-hidden="true"><i class="bi bi-person-check"></i></span>
                <div class="management-section-head__copy">
                    <div class="sct-title-with-help management-heading-block">
                        <h2 id="worker-management-access-title"><?= htmlspecialchars(t('worker_management_access_title'), ENT_QUOTES, 'UTF-8') ?></h2>
                        <?= sctInfoTip(t('worker_management_access_help'), t('info_more_label')) ?>
                    </div>
                    <p><?= htmlspecialchars(t('worker_management_access_intro'), ENT_QUOTES, 'UTF-8') ?></p>
                </div>
            </header>

            <div class="management-module-grid management-module-grid--worker">
                <a class="management-module-card management-module-card--cyan" href="./mis-actividades.php">
                    <div class="management-module-card__top"><span class="management-module-card__icon" aria-hidden="true"><i class="bi bi-grid-1x2"></i></span><span class="management-module-card__arrow" aria-hidden="true"><i class="bi bi-arrow-up-right"></i></span></div>
                    <div class="management-module-card__body"><span class="management-module-card__tag"><?= htmlspecialchars(t('worker_activities_label'), ENT_QUOTES, 'UTF-8') ?></span><h3><?= htmlspecialchars(t('worker_space_title'), ENT_QUOTES, 'UTF-8') ?></h3><p><?= htmlspecialchars(t('worker_management_space_text'), ENT_QUOTES, 'UTF-8') ?></p></div>
                    <span class="management-module-card__cta"><?= htmlspecialchars(t('worker_open_space'), ENT_QUOTES, 'UTF-8') ?><i class="bi bi-arrow-right" aria-hidden="true"></i></span>
                </a>

                <a class="management-module-card management-module-card--navy" href="./gestion-usuarios.php">
                    <div class="management-module-card__top"><span class="management-module-card__icon" aria-hidden="true"><i class="bi bi-person-gear"></i></span><span class="management-module-card__arrow" aria-hidden="true"><i class="bi bi-arrow-up-right"></i></span></div>
                    <div class="management-module-card__body"><span class="management-module-card__tag"><?= htmlspecialchars(t('worker_my_info_label'), ENT_QUOTES, 'UTF-8') ?></span><h3><?= htmlspecialchars(t('worker_management_profile_title'), ENT_QUOTES, 'UTF-8') ?></h3><p><?= htmlspecialchars(t('worker_management_profile_text'), ENT_QUOTES, 'UTF-8') ?></p></div>
                    <span class="management-module-card__cta"><?= htmlspecialchars(t('worker_manage_info'), ENT_QUOTES, 'UTF-8') ?><i class="bi bi-arrow-right" aria-hidden="true"></i></span>
                </a>
            </div>
        </section>
    </div>
</main>

<?php require __DIR__ . '/../../partials/app-footer.php'; ?>
</body>
</html>
