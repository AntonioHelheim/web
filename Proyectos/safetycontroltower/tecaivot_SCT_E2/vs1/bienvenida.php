<?php
require __DIR__ . '/session_bootstrap.php';
require __DIR__ . '/lib/db.php';
require __DIR__ . '/lib/auth.php';
require __DIR__ . '/lib/repositorios/EmpresaRepository.php';

// Carga el sistema de i18n tras iniciar la sesión
require_once __DIR__ . '/i18n.php';

// Si no hay sesión activa, no se puede ver esta página (redirige, no es un endpoint JSON).
requireLoginPage();

aplicarCabecerasSeguridad();

$userEmail = htmlspecialchars($_SESSION['user_email'] ?? '', ENT_QUOTES, 'UTF-8');

$perfil = currentUserProfile($pdo);
$nombrePila = $perfil['name'] ?? ucfirst(explode('@', $_SESSION['user_email'] ?? 'usuario')[0]);
$nombrePila = capitalizarNombre($nombrePila);
$nombreCompleto = trim(($perfil['name'] ?? '') . ' ' . ($perfil['lastname'] ?? ''));
if ($nombreCompleto === '') {
    $nombreCompleto = $nombrePila;
}
$nombreCompleto = capitalizarNombre($nombreCompleto);
$displayName = htmlspecialchars($nombrePila, ENT_QUOTES, 'UTF-8');
$displayFullName = htmlspecialchars($nombreCompleto, ENT_QUOTES, 'UTF-8');

// Iniciales para el avatar (nombre + apellido si existen, si no las 2
// primeras letras del correo).
$iniciales = '';
if (!empty($perfil['name'])) {
    $iniciales .= mb_substr($perfil['name'], 0, 1);
}
if (!empty($perfil['lastname'])) {
    $iniciales .= mb_substr($perfil['lastname'], 0, 1);
}
if ($iniciales === '') {
    $iniciales = mb_substr($_SESSION['user_email'] ?? 'US', 0, 2);
}
$iniciales = htmlspecialchars(mb_strtoupper($iniciales), ENT_QUOTES, 'UTF-8');

// Rol y contexto de empresa: misma lógica de roles que ya usan
// Proyectos/Centros/Trabajadores/Eventos/Inducción (lib/auth.php), para
// que lo que se muestra acá siempre sea consistente con lo que la cuenta
// realmente puede hacer en el resto del sistema.
$roles = currentUserRoles($pdo);
$rolPrincipal = primaryRoleName($roles);
$rolEtiqueta = $rolPrincipal ? roleDisplayLabel($rolPrincipal) : null;
$esSuperAdmin = in_array('administrador_completo', $roles, true);

$empresaNombre = null;
if (!$esSuperAdmin && !empty($perfil['id_company'])) {
    $empresa = empresaObtenerPorId($pdo, (int) $perfil['id_company']);
    if ($empresa) {
        $empresaNombre = capitalizarNombre($empresa['razon_social']);
    }
}

// Saludo según la hora del día (utilizando las claves i18n)
$hour = (int) date('G');
if ($hour < 12) {
    $greeting = t('welcome_greeting_morning');
} elseif ($hour < 19) {
    $greeting = t('welcome_greeting_afternoon');
} else {
    $greeting = t('welcome_greeting_evening');
}
?>
<!DOCTYPE html>
<html lang="<?php echo htmlspecialchars(idiomaActual(), ENT_QUOTES, 'UTF-8'); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars(t('welcome_page_title'), ENT_QUOTES, 'UTF-8'); ?></title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet"
          integrity="sha384-LN+7fdVzj6u52u30Kp6M/trliBMCMKTyK833zpbD+pXdCLuTusPj697FH4R/5mcr" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <!-- build: <?= htmlspecialchars($ASSET_VERSION, ENT_QUOTES, 'UTF-8') ?> -->
    <link rel="stylesheet" href="css/style.css?v=<?= htmlspecialchars($ASSET_VERSION, ENT_QUOTES, 'UTF-8') ?>">

    <style>
        body { background: var(--background); }

        .app-shell { max-width: 1100px; margin: 0 auto; padding: 0 24px; }

        /* ---------- Topbar: identidad + empresa/rol + salir ---------- */
        .app-topbar {
            display: flex; justify-content: space-between; align-items: center;
            padding: 20px 0; margin-bottom: 8px;
        }
        .app-brand { display: flex; align-items: center; gap: 10px; }
        .app-brand .brand-symbol img { height: 32px; }
        .app-brand strong { display: block; font-size: 15px; color: var(--primary-darkest); line-height: 1; }
        .app-brand small { display: block; margin-top: 3px; font-size: 10px; letter-spacing: 2px; color: var(--primary); }

        .app-identity { display: flex; align-items: center; gap: 10px; }
        .app-identity-info { text-align: right; line-height: 1.25; }
        .app-identity-info strong { display: block; font-size: 13px; color: var(--text); }
        .app-identity-info span { font-size: 12px; color: var(--text-secondary); }
        .app-avatar {
            width: 40px; height: 40px; border-radius: 50%;
            background: linear-gradient(135deg, var(--primary), var(--primary-darker));
            color: white; display: flex; align-items: center; justify-content: center;
            font-weight: 700; font-size: 14px; flex-shrink: 0;
        }
        .app-icon-btn {
            width: 40px; height: 40px; border-radius: 50%;
            border: 1px solid var(--border); background: white; color: var(--text-secondary);
            display: flex; align-items: center; justify-content: center; font-size: 16px;
            transition: var(--transition-smooth); flex-shrink: 0;
        }
        .app-icon-btn:hover { background: var(--background-soft); color: var(--primary-dark); border-color: var(--primary); }

        /* ---------- Hero / contexto ---------- */
        .app-hero { padding: 40px 0 8px; }
        .app-hero h1 { color: var(--primary-darkest); font-size: clamp(28px, 3.4vw, 38px); font-weight: 800; letter-spacing: -1px; }

        .context-pill {
            display: inline-flex; align-items: center; flex-wrap: wrap; gap: 6px 8px;
            margin-top: 12px; padding: 8px 18px 8px 8px;
            background: white; border: 1px solid var(--border); border-radius: 999px;
            font-size: 13px; color: var(--text-secondary); max-width: 100%;
        }
        .context-pill .dot {
            width: 26px; height: 26px; border-radius: 50%;
            background: rgba(0,163,244,0.12); color: var(--primary-dark);
            display: flex; align-items: center; justify-content: center; font-size: 13px; flex-shrink: 0;
        }
        .context-pill.super .dot { background: rgba(253,197,0,0.18); color: var(--accent-dark); }
        .context-pill strong { color: var(--text); font-weight: 700; }

        /* ---------- Acciones ---------- */
        .app-actions { margin-top: 36px; margin-bottom: 40px; }

        .action-primary {
            display: flex; align-items: center; justify-content: space-between; gap: 24px;
            padding: 32px 34px;
            border-radius: var(--radius-lg);
            background: linear-gradient(135deg, var(--primary-darker), var(--primary-darkest));
            color: white;
            text-decoration: none;
            box-shadow: 0 20px 45px rgba(0, 34, 89, 0.22);
            transition: var(--transition-smooth);
        }
        .action-primary:hover { transform: translateY(-3px); box-shadow: 0 24px 55px rgba(0, 34, 89, 0.3); }
        .action-primary .txt h2 { font-size: 21px; font-weight: 800; margin-bottom: 6px; color: white; }
        .action-primary .txt p { font-size: 13.5px; color: rgba(255,255,255,0.75); margin: 0; max-width: 460px; }
        .action-primary .go {
            width: 46px; height: 46px; border-radius: 50%; flex-shrink: 0;
            background: rgba(255,255,255,0.14); display: flex; align-items: center; justify-content: center;
            font-size: 18px; transition: var(--transition-smooth);
        }
        .action-primary:hover .go { background: var(--accent); color: var(--primary-darkest); transform: translateX(4px); }

        .action-secondary-row { display: flex; gap: 16px; margin-top: 16px; }
        .action-secondary {
            flex: 1; display: flex; align-items: center; gap: 14px;
            padding: 18px 20px; border-radius: var(--radius-md);
            background: white; border: 1px solid var(--border);
            text-decoration: none; transition: var(--transition-smooth);
        }
        .action-secondary:hover { border-color: var(--primary); box-shadow: var(--shadow-sm); }
        .action-secondary .ic {
            width: 40px; height: 40px; border-radius: 12px; flex-shrink: 0;
            background: rgba(0,163,244,0.1); color: var(--primary-dark);
            display: flex; align-items: center; justify-content: center; font-size: 17px;
        }
        .action-secondary h3 { font-size: 14.5px; font-weight: 700; color: var(--text); margin: 0; }
        .action-secondary p { font-size: 12.5px; color: var(--text-secondary); margin: 2px 0 0; }

        .app-footer { text-align: center; padding: 20px 0 48px; color: var(--text-secondary); font-size: 13px; }
        .app-footer a { color: var(--primary-dark); font-weight: 600; }

        @media (max-width: 575.98px) {
            .app-identity-info { display: none; }
            .action-secondary-row { flex-direction: column; }
            .action-primary { flex-direction: column; align-items: flex-start; gap: 18px; }
            .action-primary .go { align-self: flex-end; }
        }
    </style>
</head>
<body>

    <div class="app-shell">

        <div class="app-topbar">
            <div class="app-brand">
                <div class="brand-symbol">
                    <img src="./images/logos/Logo-SCT-white.png" alt="Safety Control Tower">
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
                <div class="app-avatar" title="<?php echo $userEmail; ?>"><?php echo $iniciales; ?></div>
                <a href="logout.php" class="app-icon-btn" title="<?php echo htmlspecialchars(t('welcome_logout'), ENT_QUOTES, 'UTF-8'); ?>" aria-label="<?php echo htmlspecialchars(t('welcome_logout'), ENT_QUOTES, 'UTF-8'); ?>">
                    <i class="bi bi-box-arrow-right"></i>
                </a>
            </div>
        </div>

        <section class="app-hero">
            <h1><?php echo $greeting; ?>, <?php echo $displayName; ?></h1>

            <?php if ($rolEtiqueta): ?>
            <div class="context-pill<?php echo $esSuperAdmin ? ' super' : ''; ?>">
                <span class="dot"><i class="bi <?php echo $esSuperAdmin ? 'bi-stars' : 'bi-building'; ?>"></i></span>
                <span class="pill-text">
                    <strong><?php echo htmlspecialchars($rolEtiqueta, ENT_QUOTES, 'UTF-8'); ?></strong>
                    <?php if ($esSuperAdmin): ?>
                        — acceso a todas las empresas
                    <?php elseif ($empresaNombre): ?>
                        — <?php echo htmlspecialchars($empresaNombre, ENT_QUOTES, 'UTF-8'); ?>
                    <?php endif; ?>
                </span>
            </div>
            <?php endif; ?>
        </section>

        <section class="app-actions">
            <a href="./api/dashboard/dashboard.php" class="action-primary" aria-label="Ir al Panel General">
                <div class="txt">
                    <h2><?php echo htmlspecialchars(t('welcome_card_panel_title'), ENT_QUOTES, 'UTF-8'); ?></h2>
                    <p><?php echo htmlspecialchars(t('welcome_card_panel_text'), ENT_QUOTES, 'UTF-8'); ?></p>
                </div>
                <span class="go"><i class="bi bi-arrow-right"></i></span>
            </a>

            <div class="action-secondary-row">
                <a href="./api/usuarios/gestiones.php" class="action-secondary" aria-label="Ir a Gestiones">
                    <span class="ic"><i class="bi bi-clipboard-check"></i></span>
                    <div>
                        <h3><?php echo htmlspecialchars(t('welcome_card_gestiones_title'), ENT_QUOTES, 'UTF-8'); ?></h3>
                        <p><?php echo htmlspecialchars(t('welcome_card_gestiones_text'), ENT_QUOTES, 'UTF-8'); ?></p>
                    </div>
                </a>

                <div class="action-secondary" style="cursor: default;">
                    <span class="ic"><i class="bi bi-bell"></i></span>
                    <div>
                        <h3><?php echo htmlspecialchars(t('welcome_card_alertas_title'), ENT_QUOTES, 'UTF-8'); ?></h3>
                        <p><?php echo htmlspecialchars(t('welcome_card_alertas_text'), ENT_QUOTES, 'UTF-8'); ?></p>
                    </div>
                </div>
            </div>
        </section>

        <section class="app-footer">
            <?php echo htmlspecialchars(t('welcome_support_question'), ENT_QUOTES, 'UTF-8'); ?>
            <a href="mailto:contacto@safetycontroltower.cl"><?php echo htmlspecialchars(t('welcome_support_link'), ENT_QUOTES, 'UTF-8'); ?></a>
        </section>

    </div>

</body>
</html>
