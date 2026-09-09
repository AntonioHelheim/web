<?php
require_once __DIR__ . '/../../session_bootstrap.php';
require_once __DIR__ . '/../../lib/db.php';
require_once __DIR__ . '/../../lib/auth.php';
require_once __DIR__ . '/../../lib/repositorios/EmpresaRepository.php';
require_once __DIR__ . '/../../i18n.php';

requireLoginPage('../../acceso-denegado.php');
$accessContext = resolveCurrentUserAccessContext($pdo);
if ($accessContext === null) {
    header('Location: ../../acceso-denegado.php');
    exit;
}

$isGlobalAdmin = (bool) $accessContext['is_global_admin'];
$puedeGestionCentros = currentUserHasCapability($pdo, 'centers.manage');
$puedeGestionProyectos = currentUserHasCapability($pdo, 'projects.manage');
$puedeGestionTrabajadores = currentUserHasCapability($pdo, 'workers.manage');
$puedeGestionInduccion = currentUserHasCapability($pdo, 'induction.manage');
$puedeGestionAuditorias = currentUserHasCapability($pdo, 'audits.manage');
$puedeGestionAutoevaluaciones = currentUserHasCapability($pdo, 'self_assessments.manage');
$puedeGestionFormularios = currentUserHasCapability($pdo, 'dynamic_forms.manage');
$puedeGestionProtocolos = currentUserHasCapability($pdo, 'protocols.manage');
$puedeGestionPermisos = currentUserHasCapability($pdo, 'permissions.manage');
$puedeVerHistorial = currentUserHasCapability($pdo, 'change_history.view');
$puedeVerProgramas = currentUserHasCapability($pdo, 'programs.view');

$perfil = $accessContext['profile'];
$nombreCompleto = trim(($perfil['name'] ?? '') . ' ' . ($perfil['lastname'] ?? ''));
if ($nombreCompleto === '') {
    $nombreCompleto = ucfirst(explode('@', $_SESSION['user_email'] ?? 'usuario')[0]);
}
$nombreCompleto = capitalizarNombre($nombreCompleto);
$displayFullName = htmlspecialchars($nombreCompleto, ENT_QUOTES, 'UTF-8');

$iniciales = '';
if (!empty($perfil['name'])) $iniciales .= sctTextSubstr((string) $perfil['name'], 0, 1);
if (!empty($perfil['lastname'])) $iniciales .= sctTextSubstr((string) $perfil['lastname'], 0, 1);
if ($iniciales === '') $iniciales = sctTextSubstr((string) ($_SESSION['user_email'] ?? 'US'), 0, 2);
$iniciales = htmlspecialchars(sctTextUpper($iniciales), ENT_QUOTES, 'UTF-8');
$profilePhoto = (string) ($perfil['profile_photo_path'] ?? '');

$rolPrincipal = (string) $accessContext['primary_role'];
$rolEtiqueta = roleDisplayLabel($rolPrincipal);
$esSoloAutogestion = ((int) $accessContext['actor_level'] === 5);
$usuariosDescripcion = $esSoloAutogestion
    ? t('mgmt_users_desc_self')
    : t('mgmt_users_desc_admin');

$empresaNombre = null;
if (!$isGlobalAdmin && !empty($perfil['id_company'])) {
    $empresa = empresaObtenerPorId($pdo, (int) $perfil['id_company']);
    if ($empresa) $empresaNombre = capitalizarNombre($empresa['razon_social']);
}

aplicarCabecerasSeguridad();
$userEmail = htmlspecialchars($_SESSION['user_email'] ?? '', ENT_QUOTES, 'UTF-8');

$langSwitcherStrings = [
    'title' => t('common_confirm_language_title'),
    'text' => t('common_confirm_language_text'),
    'confirm' => t('common_confirm'),
    'cancel' => t('common_cancel'),
    'updated' => t('common_language_updated'),
];
?>
<!DOCTYPE html>
<html lang="<?php echo htmlspecialchars(idiomaActual(), ENT_QUOTES, 'UTF-8'); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars(t('mgmt_page_title'), ENT_QUOTES, 'UTF-8') ?></title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet"
          integrity="sha384-LN+7fdVzj6u52u30Kp6M/trliBMCMKTyK833zpbD+pXdCLuTusPj697FH4R/5mcr" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <!-- build: <?= htmlspecialchars($ASSET_VERSION, ENT_QUOTES, 'UTF-8') ?> -->
    <link rel="stylesheet" href="../../css/style.css?v=<?= htmlspecialchars($ASSET_VERSION, ENT_QUOTES, 'UTF-8') ?>">

    <style>
        body {
            background:
                radial-gradient(circle at 10% 0%, rgba(0,163,244,.07), transparent 28rem),
                var(--background);
            min-height: 100vh;
        }

        .app-shell {
            width: min(1180px, 100%);
            margin: 0 auto;
            padding: 0 24px 64px;
        }

        /* ---------- Topbar ---------- */
        .app-topbar {
            position: sticky;
            top: 0;
            z-index: 30;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 16px;
            padding: 16px 0;
            background: color-mix(in srgb, var(--background) 88%, transparent);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
        }

        .app-brand {
            display: flex;
            align-items: center;
            gap: 10px;
            min-width: 0;
        }

        .brand-symbol {
            width: 42px;
            height: 42px;
            border-radius: 12px;
            background: linear-gradient(145deg, var(--primary-darkest), var(--primary-darker));
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 8px 22px rgba(0, 64, 96, .14);
            flex-shrink: 0;
        }

        .app-brand .brand-symbol img {
            width: 28px;
            height: 28px;
            object-fit: contain;
        }

        .app-brand strong {
            display: block;
            font-size: 15px;
            color: var(--primary-darkest);
            line-height: 1;
            white-space: nowrap;
        }

        .app-brand small {
            display: block;
            margin-top: 4px;
            font-size: 10px;
            letter-spacing: 2px;
            color: var(--primary);
        }

        .app-identity {
            display: flex;
            align-items: center;
            gap: 9px;
            min-width: 0;
        }

        .app-identity-info {
            text-align: right;
            line-height: 1.25;
            min-width: 0;
        }

        .app-identity-info strong {
            display: block;
            max-width: 230px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            font-size: 13px;
            color: var(--text);
        }

        .app-identity-info span {
            font-size: 12px;
            color: var(--text-secondary);
        }

        .app-avatar,
        .app-icon-btn {
            width: 40px;
            height: 40px;
            flex-shrink: 0;
        }

        .language-select {
            min-width: 110px;
            height: 40px;
            flex-shrink: 0;
        }

        .app-avatar {
            border-radius: 50%;
            overflow: hidden;
            background: linear-gradient(135deg, var(--primary), var(--primary-darker));
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 800;
            font-size: 14px;
            box-shadow: 0 6px 18px rgba(0, 122, 183, .16);
        }

        .app-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .app-icon-btn {
            border-radius: 50%;
            border: 1px solid var(--border);
            background: rgba(255,255,255,.92);
            color: var(--text-secondary);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 15px;
            text-decoration: none;
            transition: var(--transition-smooth);
        }

        .app-icon-btn:hover,
        .app-icon-btn:focus-visible {
            background: var(--primary);
            color: #fff;
            border-color: var(--primary);
            outline: none;
            transform: translateY(-1px);
        }

        /* ---------- Hero ---------- */
        .app-hero {
            display: grid;
            grid-template-columns: minmax(0, 1fr) auto;
            gap: 24px;
            align-items: end;
            padding: 34px 0 12px;
        }

        .hero-kicker {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            margin-bottom: 8px;
            color: var(--primary-dark);
            font-size: 11px;
            font-weight: 800;
            letter-spacing: 1.3px;
            text-transform: uppercase;
        }

        .app-hero h1 {
            margin: 0;
            color: var(--primary-darkest);
            font-size: clamp(30px, 4vw, 44px);
            font-weight: 850;
            letter-spacing: -1.5px;
            line-height: 1.05;
        }

        .hero-copy {
            max-width: 650px;
            margin: 10px 0 0;
            color: var(--text-secondary);
            font-size: 14px;
            line-height: 1.55;
        }

        .context-pill {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            max-width: 360px;
            padding: 8px 14px 8px 8px;
            background: rgba(255,255,255,.92);
            border: 1px solid var(--border);
            border-radius: 999px;
            color: var(--text-secondary);
            font-size: 12px;
            box-shadow: 0 8px 24px rgba(31, 45, 61, .05);
        }

        .context-pill .dot {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            background: rgba(0,163,244,.12);
            color: var(--primary-dark);
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .context-pill.super .dot {
            background: rgba(253,197,0,.20);
            color: var(--accent-dark);
        }

        .context-pill .pill-text {
            min-width: 0;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .context-pill strong {
            color: var(--text);
            font-weight: 800;
        }

        /* ---------- Secciones ---------- */
        .module-section {
            margin-top: 34px;
        }

        .module-section-head {
            display: flex;
            align-items: flex-end;
            justify-content: space-between;
            gap: 16px;
            margin-bottom: 12px;
            padding: 0 2px;
        }

        .module-section-title-wrap {
            display: flex;
            align-items: center;
            gap: 10px;
            min-width: 0;
        }

        .section-symbol {
            width: 34px;
            height: 34px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            background: rgba(0,163,244,.10);
            color: var(--primary-dark);
            font-size: 15px;
        }

        .module-section.learning .section-symbol {
            background: rgba(124,58,237,.10);
            color: #7C3AED;
        }

        .module-section.admin .section-symbol {
            background: rgba(253,197,0,.18);
            color: var(--accent-dark);
        }

        .module-section h2 {
            margin: 0;
            color: var(--primary-darkest);
            font-size: 17px;
            font-weight: 800;
            letter-spacing: -.25px;
        }

        .module-section-head p {
            margin: 2px 0 0;
            color: var(--text-secondary);
            font-size: 12px;
        }

        .module-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 14px;
        }

        /* ---------- Cards ---------- */
        .module-card {
            position: relative;
            min-width: 0;
            min-height: 142px;
            display: flex;
            flex-direction: column;
            padding: 17px;
            overflow: hidden;
            border: 1px solid var(--border);
            border-radius: 18px;
            background: rgba(255,255,255,.96);
            color: inherit;
            text-decoration: none;
            box-shadow: 0 8px 24px rgba(31,45,61,.035);
            transition: border-color .18s ease, box-shadow .18s ease, transform .18s ease;
        }

        .module-card::after {
            content: "";
            position: absolute;
            right: -36px;
            bottom: -48px;
            width: 112px;
            height: 112px;
            border-radius: 50%;
            background: rgba(0,163,244,.045);
            pointer-events: none;
        }

        .module-card:hover,
        .module-card:focus-visible {
            border-color: color-mix(in srgb, var(--primary) 60%, var(--border));
            box-shadow: 0 14px 34px rgba(31,45,61,.09);
            transform: translateY(-2px);
            outline: none;
        }

        .module-card.manage::after {
            background: rgba(124,58,237,.045);
        }

        .module-card.personal::after {
            background: rgba(14,165,233,.055);
        }

        .module-card.admin-card::after {
            background: rgba(253,197,0,.08);
        }

        .module-card-top {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 12px;
        }

        .module-icon {
            width: 44px;
            height: 44px;
            border-radius: 13px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            background: rgba(0,163,244,.11);
            color: var(--primary-dark);
            font-size: 19px;
        }

        .module-card.manage .module-icon {
            background: rgba(124,58,237,.10);
            color: #7C3AED;
        }

        .module-card.personal .module-icon {
            background: rgba(14,165,233,.10);
            color: #0284C7;
        }

        .module-card.admin-card .module-icon {
            background: rgba(253,197,0,.18);
            color: var(--accent-dark);
        }

        .module-arrow {
            width: 31px;
            height: 31px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            background: var(--background);
            color: var(--text-secondary);
            font-size: 13px;
            transition: var(--transition-smooth);
        }

        .module-card:hover .module-arrow,
        .module-card:focus-visible .module-arrow {
            background: var(--primary);
            color: #fff;
            transform: translateX(2px);
        }

        .module-card-body {
            position: relative;
            z-index: 1;
            margin-top: 15px;
            min-width: 0;
        }

        .module-tag {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            margin-bottom: 5px;
            color: var(--text-secondary);
            font-size: 10px;
            font-weight: 800;
            letter-spacing: .7px;
            text-transform: uppercase;
        }

        .module-card h3 {
            margin: 0;
            color: var(--text);
            font-size: 15px;
            font-weight: 800;
            line-height: 1.25;
            letter-spacing: -.15px;
        }

        .module-card p {
            margin: 6px 0 0;
            color: var(--text-secondary);
            font-size: 12.5px;
            line-height: 1.45;
        }

        /* ---------- Tablet ---------- */
        @media (max-width: 991.98px) {
            .app-shell {
                width: min(820px, 100%);
            }

            .app-hero {
                grid-template-columns: 1fr;
                align-items: start;
                gap: 14px;
            }

            .context-pill {
                max-width: 100%;
                width: fit-content;
            }

            .module-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        /* ---------- Móvil ---------- */
        @media (max-width: 575.98px) {
            body {
                background:
                    linear-gradient(180deg, rgba(0,163,244,.055), transparent 230px),
                    var(--background);
            }

            .app-shell {
                padding: 0 13px 36px;
            }

            .app-topbar {
                padding: 11px 0;
                gap: 8px;
            }

            .brand-symbol {
                width: 38px;
                height: 38px;
                border-radius: 11px;
            }

            .app-brand .brand-symbol img {
                width: 25px;
                height: 25px;
            }

            .app-brand strong {
                font-size: 13px;
            }

            .app-brand small {
                font-size: 9px;
                letter-spacing: 1.5px;
            }

            .app-identity {
                gap: 6px;
            }

            .app-identity-info {
                display: none;
            }

            .app-avatar,
            .app-icon-btn {
                width: 36px;
                height: 36px;
            }

            .app-avatar {
                font-size: 12px;
            }

            .app-hero {
                padding: 24px 0 4px;
                gap: 12px;
            }

            .hero-kicker {
                margin-bottom: 6px;
                font-size: 10px;
            }

            .app-hero h1 {
                font-size: 31px;
                letter-spacing: -1px;
            }

            .hero-copy {
                margin-top: 8px;
                font-size: 13px;
                line-height: 1.45;
            }

            .context-pill {
                width: 100%;
                border-radius: 14px;
                padding: 8px 10px 8px 8px;
            }

            .context-pill .pill-text {
                white-space: normal;
                line-height: 1.25;
            }

            .module-section {
                margin-top: 27px;
            }

            .module-section-head {
                margin-bottom: 10px;
            }

            .section-symbol {
                width: 31px;
                height: 31px;
                border-radius: 9px;
                font-size: 14px;
            }

            .module-section h2 {
                font-size: 15px;
            }

            .module-section-head p {
                font-size: 11px;
            }

            /*
             * En móvil el menú se transforma en una cuadrícula compacta.
             * Las descripciones se ocultan para evitar tarjetas demasiado altas;
             * el título, icono y tipo de acceso siguen visibles.
             */
            .module-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
                gap: 9px;
            }

            .module-card {
                min-height: 134px;
                padding: 13px;
                border-radius: 15px;
            }

            .module-card:hover {
                transform: none;
            }

            .module-icon {
                width: 39px;
                height: 39px;
                border-radius: 11px;
                font-size: 17px;
            }

            .module-arrow {
                width: 28px;
                height: 28px;
                font-size: 12px;
            }

            .module-card-body {
                margin-top: 12px;
            }

            .module-tag {
                margin-bottom: 4px;
                font-size: 9px;
                letter-spacing: .55px;
            }

            .module-card h3 {
                font-size: 13px;
                line-height: 1.22;
            }

            .module-card p {
                display: none;
            }
        }

        /* Teléfonos muy angostos: una tarjeta por fila. */
        @media (max-width: 359.98px) {
            .app-brand > div:last-child {
                display: none;
            }

            .module-grid {
                grid-template-columns: 1fr;
            }

            .module-card {
                min-height: 108px;
            }

            .module-card p {
                display: block;
                font-size: 11.5px;
            }
        }

        @media (prefers-reduced-motion: reduce) {
            .module-card,
            .module-arrow,
            .app-icon-btn {
                transition: none;
            }

            .module-card:hover,
            .module-card:focus-visible {
                transform: none;
            }
        }
    </style>
</head>
<body>

<div class="app-shell">

    <header class="app-topbar">
        <div class="app-brand">
            <div class="brand-symbol">
                <img src="../../images/logos/Logo-SCT-white.png" alt="Safety Control Tower">
            </div>
            <div>
                <strong>Safety Control</strong>
                <small>TOWER</small>
            </div>
        </div>

        <div class="app-identity">
            <div class="app-identity-info">
                <strong title="<?php echo $displayFullName; ?>"><?php echo $displayFullName; ?></strong>
                <?php if ($rolEtiqueta): ?>
                    <span><?php echo htmlspecialchars($rolEtiqueta, ENT_QUOTES, 'UTF-8'); ?></span>
                <?php endif; ?>
            </div>

            <div class="app-avatar" title="<?php echo $userEmail; ?>">
                <?php if ($profilePhoto !== ''): ?>
                    <img src="../../<?php echo htmlspecialchars($profilePhoto, ENT_QUOTES, 'UTF-8'); ?>" alt="">
                <?php else: ?>
                    <?php echo $iniciales; ?>
                <?php endif; ?>
            </div>

            <select id="pageLanguageSelect" class="form-select form-select-sm language-select" aria-label="<?php echo htmlspecialchars(t('common_language'), ENT_QUOTES, 'UTF-8'); ?>">
                <?php foreach (idiomasDisponiblesConNombre() as $code => $name): ?>
                    <option value="<?php echo htmlspecialchars($code, ENT_QUOTES, 'UTF-8'); ?>" <?php echo $code === idiomaActual() ? 'selected' : ''; ?>><?php echo htmlspecialchars($name, ENT_QUOTES, 'UTF-8'); ?></option>
                <?php endforeach; ?>
            </select>
            <a href="../../bienvenida.php" class="app-icon-btn" title="<?php echo htmlspecialchars(t('mgmt_back_icon'), ENT_QUOTES, 'UTF-8'); ?>" aria-label="<?php echo htmlspecialchars(t('mgmt_back_icon'), ENT_QUOTES, 'UTF-8'); ?>">
                <i class="bi bi-arrow-left"></i>
            </a>
            <a href="../../logout.php" class="app-icon-btn" title="<?php echo htmlspecialchars(t('common_logout'), ENT_QUOTES, 'UTF-8'); ?>" aria-label="<?php echo htmlspecialchars(t('common_logout'), ENT_QUOTES, 'UTF-8'); ?>">
                <i class="bi bi-box-arrow-right"></i>
            </a>
        </div>
    </header>

    <section class="app-hero">
        <div>
            <div class="hero-kicker">
                <i class="bi bi-grid-1x2-fill"></i>
                <?php echo htmlspecialchars(t('mgmt_hero_kicker'), ENT_QUOTES, 'UTF-8'); ?>
            </div>
            <h1><?php echo htmlspecialchars(t('mgmt_hero_title'), ENT_QUOTES, 'UTF-8'); ?></h1>
            <p class="hero-copy">
                <?php echo htmlspecialchars(t('mgmt_hero_copy'), ENT_QUOTES, 'UTF-8'); ?>
            </p>
        </div>

        <?php if ($rolEtiqueta): ?>
            <div class="context-pill<?php echo $isGlobalAdmin ? ' super' : ''; ?>">
                <span class="dot">
                    <i class="bi <?php echo $isGlobalAdmin ? 'bi-stars' : 'bi-building'; ?>"></i>
                </span>
                <span class="pill-text">
                    <strong><?php echo htmlspecialchars($rolEtiqueta, ENT_QUOTES, 'UTF-8'); ?></strong>
                    <?php if ($isGlobalAdmin): ?>
                        — <?php echo htmlspecialchars(t('mgmt_pill_all_companies'), ENT_QUOTES, 'UTF-8'); ?>
                    <?php elseif ($empresaNombre): ?>
                        — <?php echo htmlspecialchars($empresaNombre, ENT_QUOTES, 'UTF-8'); ?>
                    <?php endif; ?>
                </span>
            </div>
        <?php endif; ?>
    </section>

    <!-- ======================================================
         OPERACIÓN
         ====================================================== -->
    <section class="module-section operational" aria-labelledby="section-operacion">
        <div class="module-section-head">
            <div class="module-section-title-wrap">
                <span class="section-symbol"><i class="bi bi-briefcase"></i></span>
                <div>
                    <h2 id="section-operacion"><?php echo htmlspecialchars(t('mgmt_section_operational_title'), ENT_QUOTES, 'UTF-8'); ?></h2>
                    <p><?php echo htmlspecialchars(t('mgmt_section_operational_text'), ENT_QUOTES, 'UTF-8'); ?></p>
                </div>
            </div>
        </div>

        <div class="module-grid">

            <?php if ($puedeGestionTrabajadores): ?>
                <a href="../trabajadores/gestion-trabajadores.php" class="module-card">
                    <div class="module-card-top">
                        <span class="module-icon"><i class="bi bi-person-badge"></i></span>
                        <span class="module-arrow"><i class="bi bi-arrow-right"></i></span>
                    </div>
                    <div class="module-card-body">
                        <span class="module-tag"><i class="bi bi-gear"></i> <?php echo htmlspecialchars(t('mgmt_tag_management'), ENT_QUOTES, 'UTF-8'); ?></span>
                        <h3><?php echo htmlspecialchars(t('mgmt_workers_title'), ENT_QUOTES, 'UTF-8'); ?></h3>
                        <p><?php echo htmlspecialchars(t('mgmt_workers_text'), ENT_QUOTES, 'UTF-8'); ?></p>
                    </div>
                </a>
            <?php endif; ?>

            <?php if ($puedeGestionProyectos): ?>
                <a href="../proyectos/gestion-proyectos.php" class="module-card">
                    <div class="module-card-top">
                        <span class="module-icon"><i class="bi bi-diagram-3"></i></span>
                        <span class="module-arrow"><i class="bi bi-arrow-right"></i></span>
                    </div>
                    <div class="module-card-body">
                        <span class="module-tag"><i class="bi bi-gear"></i> <?php echo htmlspecialchars(t('mgmt_tag_management'), ENT_QUOTES, 'UTF-8'); ?></span>
                        <h3><?php echo htmlspecialchars(t('mgmt_projects_title'), ENT_QUOTES, 'UTF-8'); ?></h3>
                        <p><?php echo htmlspecialchars(t('mgmt_projects_text'), ENT_QUOTES, 'UTF-8'); ?></p>
                    </div>
                </a>
            <?php endif; ?>

            <?php if ($puedeGestionCentros): ?>
                <a href="../centros/gestion-centros.php" class="module-card">
                    <div class="module-card-top">
                        <span class="module-icon"><i class="bi bi-geo-alt"></i></span>
                        <span class="module-arrow"><i class="bi bi-arrow-right"></i></span>
                    </div>
                    <div class="module-card-body">
                        <span class="module-tag"><i class="bi bi-gear"></i> <?php echo htmlspecialchars(t('mgmt_tag_management'), ENT_QUOTES, 'UTF-8'); ?></span>
                        <h3><?php echo htmlspecialchars(t('mgmt_centers_title'), ENT_QUOTES, 'UTF-8'); ?></h3>
                        <p><?php echo htmlspecialchars(t('mgmt_centers_text'), ENT_QUOTES, 'UTF-8'); ?></p>
                    </div>
                </a>
            <?php endif; ?>

            <?php if ($puedeVerProgramas): ?>
                <a href="../programas/gestion-programas.php" class="module-card">
                    <div class="module-card-top">
                        <span class="module-icon"><i class="bi bi-calendar3-range"></i></span>
                        <span class="module-arrow"><i class="bi bi-arrow-right"></i></span>
                    </div>
                    <div class="module-card-body">
                        <span class="module-tag"><i class="bi bi-graph-up-arrow"></i> <?php echo htmlspecialchars(t('mgmt_tag_stage3'), ENT_QUOTES, 'UTF-8'); ?></span>
                        <h3><?php echo htmlspecialchars(t('mgmt_programs_title'), ENT_QUOTES, 'UTF-8'); ?></h3>
                        <p><?php echo htmlspecialchars(t('mgmt_programs_text'), ENT_QUOTES, 'UTF-8'); ?></p>
                    </div>
                </a>
            <?php endif; ?>

            <a href="../eventos/gestion-eventos.php" class="module-card">
                <div class="module-card-top">
                    <span class="module-icon"><i class="bi bi-exclamation-triangle"></i></span>
                    <span class="module-arrow"><i class="bi bi-arrow-right"></i></span>
                </div>
                <div class="module-card-body">
                    <span class="module-tag"><i class="bi bi-shield-check"></i> <?php echo htmlspecialchars(t('mgmt_tag_security'), ENT_QUOTES, 'UTF-8'); ?></span>
                    <h3><?php echo htmlspecialchars(t('mgmt_events_title'), ENT_QUOTES, 'UTF-8'); ?></h3>
                    <p><?php echo htmlspecialchars(t('mgmt_events_text'), ENT_QUOTES, 'UTF-8'); ?></p>
                </div>
            </a>

        </div>
    </section>

    <!-- ======================================================
         FORMACIÓN Y CONTROL
         ====================================================== -->
    <section class="module-section learning" aria-labelledby="section-formacion">
        <div class="module-section-head">
            <div class="module-section-title-wrap">
                <span class="section-symbol"><i class="bi bi-clipboard2-data"></i></span>
                <div>
                    <h2 id="section-formacion"><?php echo htmlspecialchars(t('mgmt_section_learning_title'), ENT_QUOTES, 'UTF-8'); ?></h2>
                    <p><?php echo htmlspecialchars(t('mgmt_section_learning_text'), ENT_QUOTES, 'UTF-8'); ?></p>
                </div>
            </div>
        </div>

        <div class="module-grid">

            <?php if ($puedeGestionInduccion): ?>
                <a href="../induccion/gestion-induccion.php" class="module-card manage">
                    <div class="module-card-top">
                        <span class="module-icon"><i class="bi bi-mortarboard"></i></span>
                        <span class="module-arrow"><i class="bi bi-arrow-right"></i></span>
                    </div>
                    <div class="module-card-body">
                        <span class="module-tag"><i class="bi bi-gear"></i> <?php echo htmlspecialchars(t('mgmt_tag_management'), ENT_QUOTES, 'UTF-8'); ?></span>
                        <h3><?php echo htmlspecialchars(t('mgmt_induction_title'), ENT_QUOTES, 'UTF-8'); ?></h3>
                        <p><?php echo htmlspecialchars(t('mgmt_induction_text'), ENT_QUOTES, 'UTF-8'); ?></p>
                    </div>
                </a>
            <?php endif; ?>

            <a href="../induccion/mis-induccion.php" class="module-card personal">
                <div class="module-card-top">
                    <span class="module-icon"><i class="bi bi-award"></i></span>
                    <span class="module-arrow"><i class="bi bi-arrow-right"></i></span>
                </div>
                <div class="module-card-body">
                    <span class="module-tag"><i class="bi bi-person"></i> <?php echo htmlspecialchars(t('mgmt_tag_my_space'), ENT_QUOTES, 'UTF-8'); ?></span>
                    <h3><?php echo htmlspecialchars(t('mgmt_my_induction_title'), ENT_QUOTES, 'UTF-8'); ?></h3>
                    <p><?php echo htmlspecialchars(t('mgmt_my_induction_text'), ENT_QUOTES, 'UTF-8'); ?></p>
                </div>
            </a>

            <?php if ($puedeGestionAuditorias): ?>
                <a href="../auditorias/gestion-auditorias.php" class="module-card manage">
                    <div class="module-card-top">
                        <span class="module-icon"><i class="bi bi-clipboard2-check"></i></span>
                        <span class="module-arrow"><i class="bi bi-arrow-right"></i></span>
                    </div>
                    <div class="module-card-body">
                        <span class="module-tag"><i class="bi bi-gear"></i> <?php echo htmlspecialchars(t('mgmt_tag_management'), ENT_QUOTES, 'UTF-8'); ?></span>
                        <h3><?php echo htmlspecialchars(t('mgmt_audits_title'), ENT_QUOTES, 'UTF-8'); ?></h3>
                        <p><?php echo htmlspecialchars(t('mgmt_audits_text'), ENT_QUOTES, 'UTF-8'); ?></p>
                    </div>
                </a>
            <?php endif; ?>

            <a href="../auditorias/mis-auditorias.php" class="module-card personal">
                <div class="module-card-top">
                    <span class="module-icon"><i class="bi bi-clipboard-check"></i></span>
                    <span class="module-arrow"><i class="bi bi-arrow-right"></i></span>
                </div>
                <div class="module-card-body">
                    <span class="module-tag"><i class="bi bi-person"></i> <?php echo htmlspecialchars(t('mgmt_tag_my_space'), ENT_QUOTES, 'UTF-8'); ?></span>
                    <h3><?php echo htmlspecialchars(t('mgmt_my_audits_title'), ENT_QUOTES, 'UTF-8'); ?></h3>
                    <p><?php echo htmlspecialchars(t('mgmt_my_audits_text'), ENT_QUOTES, 'UTF-8'); ?></p>
                </div>
            </a>

            <?php if ($puedeGestionAutoevaluaciones): ?>
                <a href="../autoevaluaciones/gestion-autoevaluaciones.php" class="module-card manage">
                    <div class="module-card-top">
                        <span class="module-icon"><i class="bi bi-ui-checks-grid"></i></span>
                        <span class="module-arrow"><i class="bi bi-arrow-right"></i></span>
                    </div>
                    <div class="module-card-body">
                        <span class="module-tag"><i class="bi bi-gear"></i> <?php echo htmlspecialchars(t('mgmt_tag_management'), ENT_QUOTES, 'UTF-8'); ?></span>
                        <h3><?php echo htmlspecialchars(t('mgmt_self_title'), ENT_QUOTES, 'UTF-8'); ?></h3>
                        <p><?php echo htmlspecialchars(t('mgmt_self_text'), ENT_QUOTES, 'UTF-8'); ?></p>
                    </div>
                </a>
            <?php endif; ?>

            <a href="../autoevaluaciones/mis-autoevaluaciones.php" class="module-card personal">
                <div class="module-card-top">
                    <span class="module-icon"><i class="bi bi-person-check"></i></span>
                    <span class="module-arrow"><i class="bi bi-arrow-right"></i></span>
                </div>
                <div class="module-card-body">
                    <span class="module-tag"><i class="bi bi-person"></i> <?php echo htmlspecialchars(t('mgmt_tag_my_space'), ENT_QUOTES, 'UTF-8'); ?></span>
                    <h3><?php echo htmlspecialchars(t('mgmt_my_self_title'), ENT_QUOTES, 'UTF-8'); ?></h3>
                    <p><?php echo htmlspecialchars(t('mgmt_my_self_text'), ENT_QUOTES, 'UTF-8'); ?></p>
                </div>
            </a>

            <?php if ($puedeGestionFormularios): ?>
                <a href="../formularios/gestion-formularios.php" class="module-card manage">
                    <div class="module-card-top">
                        <span class="module-icon"><i class="bi bi-card-checklist"></i></span>
                        <span class="module-arrow"><i class="bi bi-arrow-right"></i></span>
                    </div>
                    <div class="module-card-body">
                        <span class="module-tag"><i class="bi bi-gear"></i> <?php echo htmlspecialchars(t('mgmt_tag_management'), ENT_QUOTES, 'UTF-8'); ?></span>
                        <h3><?php echo htmlspecialchars(t('mgmt_forms_title'), ENT_QUOTES, 'UTF-8'); ?></h3>
                        <p><?php echo htmlspecialchars(t('mgmt_forms_text'), ENT_QUOTES, 'UTF-8'); ?></p>
                    </div>
                </a>
            <?php endif; ?>

            <a href="../formularios/mis-formularios.php" class="module-card personal">
                <div class="module-card-top">
                    <span class="module-icon"><i class="bi bi-card-text"></i></span>
                    <span class="module-arrow"><i class="bi bi-arrow-right"></i></span>
                </div>
                <div class="module-card-body">
                    <span class="module-tag"><i class="bi bi-person"></i> <?php echo htmlspecialchars(t('mgmt_tag_my_space'), ENT_QUOTES, 'UTF-8'); ?></span>
                    <h3><?php echo htmlspecialchars(t('mgmt_my_forms_title'), ENT_QUOTES, 'UTF-8'); ?></h3>
                    <p><?php echo htmlspecialchars(t('mgmt_my_forms_text'), ENT_QUOTES, 'UTF-8'); ?></p>
                </div>
            </a>

            <?php if ($puedeGestionProtocolos): ?>
                <a href="../protocolos/gestion-protocolos.php" class="module-card manage">
                    <div class="module-card-top">
                        <span class="module-icon"><i class="bi bi-clipboard2-pulse"></i></span>
                        <span class="module-arrow"><i class="bi bi-arrow-right"></i></span>
                    </div>
                    <div class="module-card-body">
                        <span class="module-tag"><i class="bi bi-gear"></i> <?php echo htmlspecialchars(t('mgmt_tag_management'), ENT_QUOTES, 'UTF-8'); ?></span>
                        <h3><?php echo htmlspecialchars(t('mgmt_protocols_title'), ENT_QUOTES, 'UTF-8'); ?></h3>
                        <p><?php echo htmlspecialchars(t('mgmt_protocols_text'), ENT_QUOTES, 'UTF-8'); ?></p>
                    </div>
                </a>
            <?php endif; ?>

            <a href="../protocolos/mis-protocolos.php" class="module-card personal">
                <div class="module-card-top">
                    <span class="module-icon"><i class="bi bi-clipboard2-heart"></i></span>
                    <span class="module-arrow"><i class="bi bi-arrow-right"></i></span>
                </div>
                <div class="module-card-body">
                    <span class="module-tag"><i class="bi bi-person"></i> <?php echo htmlspecialchars(t('mgmt_tag_my_space'), ENT_QUOTES, 'UTF-8'); ?></span>
                    <h3><?php echo htmlspecialchars(t('mgmt_my_protocols_title'), ENT_QUOTES, 'UTF-8'); ?></h3>
                    <p><?php echo htmlspecialchars(t('mgmt_my_protocols_text'), ENT_QUOTES, 'UTF-8'); ?></p>
                </div>
            </a>

        </div>
    </section>

    <!-- ======================================================
         CUENTAS Y PLATAFORMA
         ====================================================== -->
    <section class="module-section admin" aria-labelledby="section-admin">
        <div class="module-section-head">
            <div class="module-section-title-wrap">
                <span class="section-symbol"><i class="bi bi-sliders"></i></span>
                <div>
                    <h2 id="section-admin"><?php echo htmlspecialchars(t('mgmt_section_admin_title'), ENT_QUOTES, 'UTF-8'); ?></h2>
                    <p><?php echo htmlspecialchars($isGlobalAdmin ? t('mgmt_section_admin_text_global') : t('mgmt_section_admin_text'), ENT_QUOTES, 'UTF-8'); ?></p>
                </div>
            </div>
        </div>

        <div class="module-grid">
            <a href="./gestion-usuarios.php" class="module-card admin-card">
                <div class="module-card-top">
                    <span class="module-icon"><i class="bi bi-people"></i></span>
                    <span class="module-arrow"><i class="bi bi-arrow-right"></i></span>
                </div>
                <div class="module-card-body">
                    <span class="module-tag"><i class="bi bi-shield-lock"></i> <?php echo htmlspecialchars(t('mgmt_tag_access'), ENT_QUOTES, 'UTF-8'); ?></span>
                    <h3><?php echo htmlspecialchars(t('mgmt_users_title'), ENT_QUOTES, 'UTF-8'); ?></h3>
                    <p><?php echo htmlspecialchars($usuariosDescripcion, ENT_QUOTES, 'UTF-8'); ?></p>
                </div>
            </a>

            <?php if ($puedeGestionPermisos): ?>
                <a href="../permisos/gestion-permisos.php" class="module-card admin-card">
                    <div class="module-card-top">
                        <span class="module-icon"><i class="bi bi-shield-lock"></i></span>
                        <span class="module-arrow"><i class="bi bi-arrow-right"></i></span>
                    </div>
                    <div class="module-card-body">
                        <span class="module-tag"><i class="bi bi-key"></i> <?php echo htmlspecialchars(t('mgmt_tag_rbac'), ENT_QUOTES, 'UTF-8'); ?></span>
                        <h3><?php echo htmlspecialchars(t('mgmt_permissions_title'), ENT_QUOTES, 'UTF-8'); ?></h3>
                        <p><?php echo htmlspecialchars(t('mgmt_permissions_text'), ENT_QUOTES, 'UTF-8'); ?></p>
                    </div>
                </a>
            <?php endif; ?>

            <?php if ($puedeVerHistorial): ?>
                <a href="../historial/gestion-historial.php" class="module-card admin-card">
                    <div class="module-card-top">
                        <span class="module-icon"><i class="bi bi-clock-history"></i></span>
                        <span class="module-arrow"><i class="bi bi-arrow-right"></i></span>
                    </div>
                    <div class="module-card-body">
                        <span class="module-tag"><i class="bi bi-journal-check"></i> <?php echo htmlspecialchars(t('mgmt_tag_audit_trail'), ENT_QUOTES, 'UTF-8'); ?></span>
                        <h3><?php echo htmlspecialchars(t('mgmt_history_title'), ENT_QUOTES, 'UTF-8'); ?></h3>
                        <p><?php echo htmlspecialchars(t('mgmt_history_text'), ENT_QUOTES, 'UTF-8'); ?></p>
                    </div>
                </a>
            <?php endif; ?>

            <?php if ($isGlobalAdmin): ?>
                <a href="../empresas/gestion-empresas.php" class="module-card admin-card">
                    <div class="module-card-top">
                        <span class="module-icon"><i class="bi bi-building"></i></span>
                        <span class="module-arrow"><i class="bi bi-arrow-right"></i></span>
                    </div>
                    <div class="module-card-body">
                        <span class="module-tag"><i class="bi bi-stars"></i> <?php echo htmlspecialchars(t('mgmt_tag_platform'), ENT_QUOTES, 'UTF-8'); ?></span>
                        <h3><?php echo htmlspecialchars(t('mgmt_companies_title'), ENT_QUOTES, 'UTF-8'); ?></h3>
                        <p><?php echo htmlspecialchars(t('mgmt_companies_text'), ENT_QUOTES, 'UTF-8'); ?></p>
                    </div>
                </a>
            <?php endif; ?>
        </div>
    </section>

</div>

<script>window.SCT_LANG_SWITCHER_I18N = <?php echo json_encode($langSwitcherStrings, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>;</script>
<script src="../../js/lang-switcher.js?v=<?= htmlspecialchars($ASSET_VERSION, ENT_QUOTES, 'UTF-8') ?>"></script>
</body>
</html>
