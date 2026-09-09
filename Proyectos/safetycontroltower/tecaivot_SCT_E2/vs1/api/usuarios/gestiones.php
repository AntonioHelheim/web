<?php
require_once __DIR__ . '/../../session_bootstrap.php';
require_once __DIR__ . '/common.php';
require_once __DIR__ . '/../../lib/auth.php';
require_once __DIR__ . '/../../lib/repositorios/EmpresaRepository.php';

// Si no hay sesión activa, no se puede ver esta página.
if (empty($_SESSION['logged_in'])) {
    header('Location: ../../acceso-denegado.php');
    exit;
}

try {
    $accessContext = usuariosRequireAccessContext($pdo);
} catch (PDOException $e) {
    error_log('api/usuarios/gestiones.php: ' . $e->getMessage());
    header('Location: ../../acceso-denegado.php');
    exit;
}

$isGlobalAdmin = ((int) ($accessContext['actor_level'] ?? 0) === 1);

// Roles vía lib/auth.php (el sistema que usan Proyectos/Centros/
// Trabajadores/Eventos/Inducción) — a propósito NO se reutiliza
// $isGlobalAdmin de arriba (basado en el sistema de niveles de
// usuarios/common.php) para no heredar ningún desfase entre ambos
// sistemas en las tarjetas de acá. Ambos coinciden hoy (solo
// administrador_completo cuenta como nivel 1 / admin global), pero cada
// tarjeta se calcula con las MISMAS constantes que usa la página de
// gestión real a la que apunta, así nunca queda una tarjeta visible que
// lleve a un "acceso denegado".
require_once __DIR__ . '/../centros/common.php';
require_once __DIR__ . '/../proyectos/common.php';
require_once __DIR__ . '/../trabajadores/common.php';
require_once __DIR__ . '/../induccion/common.php';

$rolesLibAuth = currentUserRoles($pdo);

$puedeGestionCentros       = count(array_intersect($rolesLibAuth, CENTROS_ROLES_GESTION)) > 0;
$puedeGestionProyectos     = count(array_intersect($rolesLibAuth, PROYECTOS_ROLES_GESTION)) > 0;
$puedeGestionTrabajadores  = count(array_intersect($rolesLibAuth, TRABAJADORES_ROLES_GESTION)) > 0;
$puedeGestionInduccion     = count(array_intersect($rolesLibAuth, INDUCCION_ROLES_GESTION)) > 0;
// Eventos e Inducción (rendir)/Mis Inducciones y Usuarios se mantienen
// visibles para cualquier rol logueado: reportar un evento y rendir un
// curso asignado son acciones abiertas a todos, tal como ya hacen sus
// respectivas páginas (ver comentario en api/eventos/common.php).

$perfil = currentUserProfile($pdo);
$nombreCompleto = trim(($perfil['name'] ?? '') . ' ' . ($perfil['lastname'] ?? ''));
if ($nombreCompleto === '') {
    $nombreCompleto = ucfirst(explode('@', $_SESSION['user_email'] ?? 'usuario')[0]);
}
$nombreCompleto = capitalizarNombre($nombreCompleto);
$displayFullName = htmlspecialchars($nombreCompleto, ENT_QUOTES, 'UTF-8');

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

$rolPrincipal = primaryRoleName($rolesLibAuth);
$rolEtiqueta = $rolPrincipal ? roleDisplayLabel($rolPrincipal) : null;

$esSoloAutogestion = ((int) ($accessContext['actor_level'] ?? 0) === 5);
$usuariosDescripcion = $esSoloAutogestion
    ? 'Revisa y actualiza los datos de tu propia cuenta.'
    : 'Administra cuentas de acceso, estado de usuarios y asignación de roles.';

$empresaNombre = null;
if (!$isGlobalAdmin && !empty($perfil['id_company'])) {
    $empresa = empresaObtenerPorId($pdo, (int) $perfil['id_company']);
    if ($empresa) {
        $empresaNombre = capitalizarNombre($empresa['razon_social']);
    }
}

aplicarCabecerasSeguridad();

$userEmail = htmlspecialchars($_SESSION['user_email'] ?? '', ENT_QUOTES, 'UTF-8');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestiones - Safety Control Tower</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet"
          integrity="sha384-LN+7fdVzj6u52u30Kp6M/trliBMCMKTyK833zpbD+pXdCLuTusPj697FH4R/5mcr" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <!-- build: <?= htmlspecialchars($ASSET_VERSION, ENT_QUOTES, 'UTF-8') ?> -->
    <link rel="stylesheet" href="../../css/style.css?v=<?= htmlspecialchars($ASSET_VERSION, ENT_QUOTES, 'UTF-8') ?>">

    <style>
        body { background: var(--background); }

        .app-shell { max-width: 900px; margin: 0 auto; padding: 0 24px; }

        /* ---------- Topbar (mismo criterio que bienvenida.php) ---------- */
        .app-topbar { display: flex; justify-content: space-between; align-items: center; padding: 20px 0; }
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
            display: flex; align-items: center; justify-content: center; font-size: 15px;
            transition: var(--transition-smooth); flex-shrink: 0;
        }
        .app-icon-btn:hover { background: var(--background-soft); color: var(--primary-dark); border-color: var(--primary); }

        /* ---------- Hero ---------- */
        .app-hero { padding: 28px 0 6px; }
        .app-hero h1 { color: var(--primary-darkest); font-size: clamp(26px, 3.2vw, 34px); font-weight: 800; letter-spacing: -1px; }
        .context-pill {
            display: inline-flex; align-items: center; flex-wrap: wrap; gap: 6px 8px; margin-top: 12px;
            padding: 8px 18px 8px 8px; background: white; border: 1px solid var(--border);
            border-radius: 999px; font-size: 13px; color: var(--text-secondary); max-width: 100%;
        }
        .context-pill .dot {
            width: 26px; height: 26px; border-radius: 50%; background: rgba(0,163,244,0.12); color: var(--primary-dark);
            display: flex; align-items: center; justify-content: center; font-size: 13px; flex-shrink: 0;
        }
        .context-pill.super .dot { background: rgba(253,197,0,0.18); color: var(--accent-dark); }
        .context-pill strong { color: var(--text); font-weight: 700; }

        /* ---------- Grupos ---------- */
        .mgmt-group { margin-top: 34px; }
        .mgmt-group-title {
            font-size: 12.5px; font-weight: 800; letter-spacing: 0.4px;
            color: var(--text-secondary); margin-bottom: 10px; padding-left: 4px;
        }
        .mgmt-list { display: flex; flex-direction: column; gap: 10px; }

        .mgmt-row {
            display: flex; align-items: center; gap: 16px;
            padding: 16px 18px; border-radius: var(--radius-md);
            background: white; border: 1px solid var(--border);
            text-decoration: none; transition: var(--transition-smooth);
        }
        .mgmt-row:hover { border-color: var(--primary); box-shadow: var(--shadow-sm); transform: translateX(2px); }

        .mgmt-row .ic {
            width: 42px; height: 42px; border-radius: 12px; flex-shrink: 0;
            display: flex; align-items: center; justify-content: center; font-size: 18px;
            background: rgba(0,163,244,0.1); color: var(--primary-dark);
        }
        .mgmt-group.access .mgmt-row .ic { background: rgba(124,58,237,0.1); color: #7C3AED; }
        .mgmt-group.platform .mgmt-row .ic { background: rgba(253,197,0,0.18); color: var(--accent-dark); }

        .mgmt-row .txt { flex: 1; min-width: 0; }
        .mgmt-row h3 { font-size: 15px; font-weight: 700; color: var(--text); margin: 0; }
        .mgmt-row p { font-size: 13px; color: var(--text-secondary); margin: 2px 0 0; }

        .mgmt-row .arrow {
            width: 32px; height: 32px; border-radius: 50%; flex-shrink: 0;
            display: flex; align-items: center; justify-content: center; font-size: 14px;
            color: var(--text-secondary); background: var(--background);
            transition: var(--transition-smooth);
        }
        .mgmt-row:hover .arrow { background: var(--primary); color: white; transform: translateX(3px); }

        .app-shell > .mgmt-group:last-child { margin-bottom: 56px; }

        @media (max-width: 575.98px) {
            .app-identity-info { display: none; }
        }
    </style>
</head>
<body>

    <div class="app-shell">

        <div class="app-topbar">
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
                <div class="app-avatar" title="<?php echo $userEmail; ?>"><?php echo $iniciales; ?></div>
                <a href="../../bienvenida.php" class="app-icon-btn" title="Volver" aria-label="Volver">
                    <i class="bi bi-arrow-left"></i>
                </a>
                <a href="../../logout.php" class="app-icon-btn" title="Cerrar sesión" aria-label="Cerrar sesión">
                    <i class="bi bi-box-arrow-right"></i>
                </a>
            </div>
        </div>

        <section class="app-hero">
            <h1>Gestiones</h1>

            <?php if ($rolEtiqueta): ?>
            <div class="context-pill<?php echo $isGlobalAdmin ? ' super' : ''; ?>">
                <span class="dot"><i class="bi <?php echo $isGlobalAdmin ? 'bi-stars' : 'bi-building'; ?>"></i></span>
                <span class="pill-text">
                    <strong><?php echo htmlspecialchars($rolEtiqueta, ENT_QUOTES, 'UTF-8'); ?></strong>
                    <?php if ($isGlobalAdmin): ?>
                        — acceso a todas las empresas
                    <?php elseif ($empresaNombre): ?>
                        — <?php echo htmlspecialchars($empresaNombre, ENT_QUOTES, 'UTF-8'); ?>
                    <?php endif; ?>
                </span>
            </div>
            <?php endif; ?>
        </section>

        <div class="mgmt-group operational">
            <div class="mgmt-group-title">Tu empresa</div>
            <div class="mgmt-list">

                <?php if ($puedeGestionTrabajadores): ?>
                <a href="../trabajadores/gestion-trabajadores.php" class="mgmt-row">
                    <span class="ic"><i class="bi bi-person-badge"></i></span>
                    <div class="txt"><h3>Trabajadores</h3><p>Administra la ficha de los trabajadores de la empresa.</p></div>
                    <span class="arrow"><i class="bi bi-arrow-right"></i></span>
                </a>
                <?php endif; ?>

                <?php if ($puedeGestionProyectos): ?>
                <a href="../proyectos/gestion-proyectos.php" class="mgmt-row">
                    <span class="ic"><i class="bi bi-diagram-3"></i></span>
                    <div class="txt"><h3>Proyectos</h3><p>Registra proyectos por empresa y asocia trabajadores a cada uno.</p></div>
                    <span class="arrow"><i class="bi bi-arrow-right"></i></span>
                </a>
                <?php endif; ?>

                <?php if ($puedeGestionCentros): ?>
                <a href="../centros/gestion-centros.php" class="mgmt-row">
                    <span class="ic"><i class="bi bi-geo-alt"></i></span>
                    <div class="txt"><h3>Centros / Sedes</h3><p>Administra los centros o sedes de trabajo de cada empresa.</p></div>
                    <span class="arrow"><i class="bi bi-arrow-right"></i></span>
                </a>
                <?php endif; ?>

                <a href="../eventos/gestion-eventos.php" class="mgmt-row">
                    <span class="ic"><i class="bi bi-exclamation-triangle"></i></span>
                    <div class="txt"><h3>Eventos e Incidentes</h3><p>Reporta y da seguimiento a eventos de seguridad de tu empresa.</p></div>
                    <span class="arrow"><i class="bi bi-arrow-right"></i></span>
                </a>

                <?php if ($puedeGestionInduccion): ?>
                <a href="../induccion/gestion-induccion.php" class="mgmt-row">
                    <span class="ic"><i class="bi bi-mortarboard"></i></span>
                    <div class="txt"><h3>Inducción — Gestión</h3><p>Administra cursos, preguntas, materiales y asignaciones.</p></div>
                    <span class="arrow"><i class="bi bi-arrow-right"></i></span>
                </a>
                <?php endif; ?>

                <a href="../induccion/mis-induccion.php" class="mgmt-row">
                    <span class="ic"><i class="bi bi-award"></i></span>
                    <div class="txt"><h3>Mis Inducciones</h3><p>Revisa tus cursos asignados, ríndelos y descarga tus certificados.</p></div>
                    <span class="arrow"><i class="bi bi-arrow-right"></i></span>
                </a>

            </div>
        </div>

        <div class="mgmt-group access">
            <div class="mgmt-group-title">Cuentas y acceso</div>
            <div class="mgmt-list">
                <a href="./gestion-usuarios.php" class="mgmt-row">
                    <span class="ic"><i class="bi bi-people"></i></span>
                    <div class="txt"><h3>Usuarios</h3><p><?php echo htmlspecialchars($usuariosDescripcion, ENT_QUOTES, 'UTF-8'); ?></p></div>
                    <span class="arrow"><i class="bi bi-arrow-right"></i></span>
                </a>
            </div>
        </div>

        <?php if ($isGlobalAdmin): ?>
        <div class="mgmt-group platform">
            <div class="mgmt-group-title">Plataforma</div>
            <div class="mgmt-list">
                <a href="../empresas/gestion-empresas.php" class="mgmt-row">
                    <span class="ic"><i class="bi bi-building"></i></span>
                    <div class="txt"><h3>Empresas</h3><p>Registra nuevas empresas y consulta las empresas activas del sistema.</p></div>
                    <span class="arrow"><i class="bi bi-arrow-right"></i></span>
                </a>
            </div>
        </div>
        <?php endif; ?>

    </div>

</body>
</html>
