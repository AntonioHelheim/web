<?php
require __DIR__ . '/session_bootstrap.php';
require __DIR__ . '/lib/db.php';
require __DIR__ . '/lib/auth.php';
require __DIR__ . '/lib/repositorios/EmpresaRepository.php';
require __DIR__ . '/lib/repositorios/LogrosRepository.php';
require_once __DIR__ . '/app/Navigation/NavigationRegistry.php';
require_once __DIR__ . '/app/Evaluations/UserEvaluationRepository.php';
require_once __DIR__ . '/app/Home/AdminPortalRegistry.php';
require_once __DIR__ . '/app/Home/AdminPortalRepository.php';
require_once __DIR__ . '/lib/repositorios/SaludRepository.php';
require_once __DIR__ . '/i18n.php';

requireLoginPage();
aplicarCabecerasSeguridad();
if (empty($_SESSION['csrf_token'])) $_SESSION['csrf_token'] = bin2hex(random_bytes(32));

$userEmailRaw = (string)(currentUserId() ?? '');
$perfil = currentUserProfile($pdo) ?: [];
$roles = currentUserRoles($pdo);
$rolPrincipal = primaryRoleName($roles);
if ($rolPrincipal === null) { header('Location: acceso-denegado.php'); exit; }
$rolEtiqueta = roleDisplayLabel($rolPrincipal);
$esSuperUsuario = $rolPrincipal === 'superusuario';
$esUsuarioPerfil = $rolPrincipal === 'usuario';
$esAdministradorPerfil = $rolPrincipal === 'administrador_cliente';
$esJefaturaPerfil = $rolPrincipal === 'jefatura';
$usaPortalAdministrador = $esAdministradorPerfil || $esJefaturaPerfil;

/*
 * E3 VS2:
 * Jefatura conserva el portal operativo (resumen/sitio/cursos), pero queda
 * fuera del alcance de Gestión de Usuarios y Gestión de Empresas.
 */
$puedeGestionarUsuariosEmpresas = $esAdministradorPerfil;
// E3 VS2:
// - Usuario y Administrador Cliente utilizan el mismo modal de Datos de usuario.
// - El endpoint de salud autoriza al Administrador Cliente únicamente sobre
//   su propio registro autenticado; no concede acceso clínico a terceros.
// - perfil-completo.php permanece en Gestión de Usuarios para administrar
//   cuentas de la misma empresa según la política organizacional.

$nombrePila = $perfil['name'] ?? ucfirst(explode('@',$userEmailRaw !== '' ? $userEmailRaw : 'usuario')[0]);
$nombrePila = capitalizarNombre((string)$nombrePila);
$nombreCompleto = trim((string)($perfil['name'] ?? '') . ' ' . (string)($perfil['lastname'] ?? ''));
if ($nombreCompleto === '') $nombreCompleto = $nombrePila;
$nombreCompleto = capitalizarNombre($nombreCompleto);
$iniciales = '';
if (!empty($perfil['name'])) $iniciales .= sctTextSubstr((string)$perfil['name'],0,1);
if (!empty($perfil['lastname'])) $iniciales .= sctTextSubstr((string)$perfil['lastname'],0,1);
if ($iniciales === '') $iniciales = sctTextSubstr($userEmailRaw !== '' ? $userEmailRaw : 'US',0,2);
$iniciales = sctTextUpper($iniciales);
$profilePhoto = (string)($perfil['profile_photo_path'] ?? '');

$empresaNombre = null; $empresaLogoSrc = '';
if (!$esSuperUsuario && !empty($perfil['id_company'])) {
    $empresa = empresaObtenerPorId($pdo,(int)$perfil['id_company']);
    if ($empresa) {
        $empresaNombre = capitalizarNombre((string)$empresa['razon_social']);
        $logo = trim((string)($empresa['logo_path'] ?? ''));
        if ($logo !== '') $empresaLogoSrc = preg_match('~^(?:https?:)?//|^data:|^/~i',$logo) ? $logo : './'.ltrim($logo,'./');
    }
}
$hour=(int)date('G');
$greeting=$hour<12?t('welcome_greeting_morning'):($hour<19?t('welcome_greeting_afternoon'):t('welcome_greeting_evening'));
$logros = logrosPorModulo($pdo,$userEmailRaw);

$directModules = SctNavigationRegistry::direct($pdo,'./');
$userOperationalModules = [];

if ($esUsuarioPerfil) {
    $directModules = [];
    $userOperationalModules = SctNavigationRegistry::userOperationalModules($pdo,'./');
}

if (!$esUsuarioPerfil) {
    $directModules = array_values(array_filter(
        $directModules,
        static fn(array $item): bool => ($item['id'] ?? '') !== 'my_data'
    ));
}

$managementModules = SctNavigationRegistry::management($pdo,'./');

$adminOverviewModules = [];
$adminSiteCatalog = [];
$adminProjectCoverage = ['total'=>0,'assigned'=>0,'pending'=>0];
$adminProjectsByName = [];
$adminUserScopeSeed = [
    'gbus'=>[],
    'sites'=>[],
    'projects'=>[],
];
$adminCreateOrgSeed = [
    'jefaturas'=>[],
    'gerencias'=>[],
    'roles'=>[],
    'default_jefatura'=>$esJefaturaPerfil ? $userEmailRaw : '',
];

if ($usaPortalAdministrador) {
    $adminOverviewModules = SctAdminPortalRegistry::overviewModules();
    try {
        $adminPortalRepo = new SctAdminPortalRepository($pdo);
        $adminCompanyId = (int)($perfil['id_company'] ?? 0);

        /*
         * E3 VS2 — catálogos organizacionales para creación desde Jefatura.
         * Cada consulta está aislada para no provocar HTTP 500.
         * No se filtran por GBU, Sitio, Proyecto ni asignación previa.
         */
        try {
            $stmt=$pdo->prepare(
                "SELECT DISTINCT
                    u.id_users,
                    u.name,
                    u.lastname
                 FROM users u
                 INNER JOIN users_role ur
                   ON ur.id_users=u.id_users
                  AND ur.state=1
                 INNER JOIN users_role_group rg
                   ON rg.id_role_group=ur.id_role_group
                  AND rg.state=1
                 WHERE u.id_company=:company_id_jef
                   AND u.state=1
                   AND LOWER(TRIM(rg.name)) LIKE '%jefatura%'
                 ORDER BY u.name,u.lastname,u.id_users"
            );
            $stmt->execute([
                'company_id_jef'=>$adminCompanyId,
            ]);
            $adminCreateOrgSeed['jefaturas']=$stmt->fetchAll() ?: [];
        } catch (Throwable $e) {
            error_log('bienvenida jefaturas: '.$e->getMessage());
        }

        try {
            $stmt=$pdo->prepare(
                "SELECT DISTINCT
                    u.id_users,
                    u.name,
                    u.lastname
                 FROM users u
                 INNER JOIN users_role ur
                   ON ur.id_users=u.id_users
                  AND ur.state=1
                 INNER JOIN users_role_group rg
                   ON rg.id_role_group=ur.id_role_group
                  AND rg.state=1
                 WHERE u.id_company=:company_id_ger
                   AND u.state=1
                   AND (
                       LOWER(TRIM(rg.name)) LIKE '%gerente%'
                       OR LOWER(TRIM(rg.name)) LIKE '%gerencia%'
                       OR LOWER(TRIM(rg.name))='cliente'
                   )
                 ORDER BY u.name,u.lastname,u.id_users"
            );
            $stmt->execute([
                'company_id_ger'=>$adminCompanyId,
            ]);
            $adminCreateOrgSeed['gerencias']=$stmt->fetchAll() ?: [];
        } catch (Throwable $e) {
            error_log('bienvenida gerencias: '.$e->getMessage());
        }

        try {
            $stmt=$pdo->prepare(
                "SELECT DISTINCT
                    rg.id_role_group,
                    rg.name
                 FROM users_role_group rg
                 WHERE rg.state=1
                   AND (
                       rg.id_company=:role_company
                       OR EXISTS (
                           SELECT 1
                           FROM users_role ur_scope
                           INNER JOIN users u_scope
                             ON u_scope.id_users=ur_scope.id_users
                            AND u_scope.id_company=:role_user_company
                           WHERE ur_scope.id_role_group=rg.id_role_group
                             AND ur_scope.state=1
                       )
                   )
                   AND (
                       LOWER(TRIM(rg.name)) LIKE '%usuario%'
                       OR LOWER(TRIM(rg.name)) LIKE '%trabajador%'
                       OR LOWER(TRIM(rg.name)) LIKE '%paramedico%'
                       OR LOWER(TRIM(rg.name)) LIKE '%paramédico%'
                       OR LOWER(TRIM(rg.name)) LIKE '%contratista%'
                   )
                   AND LOWER(TRIM(rg.name)) NOT LIKE '%jefatura%'
                   AND LOWER(TRIM(rg.name)) NOT LIKE '%gerente%'
                   AND LOWER(TRIM(rg.name)) NOT LIKE '%gerencia%'
                   AND LOWER(TRIM(rg.name)) NOT LIKE '%administrador%'
                   AND LOWER(TRIM(rg.name)) NOT LIKE '%superusuario%'
                   AND LOWER(TRIM(rg.name)) NOT LIKE '%super user%'
                 ORDER BY rg.name"
            );
            $stmt->execute([
                'role_company'=>$adminCompanyId,
                'role_user_company'=>$adminCompanyId,
            ]);

            foreach ($stmt->fetchAll() ?: [] as $row) {
                $roleName=mb_strtolower(
                    trim((string)($row['name'] ?? '')),
                    'UTF-8'
                );

                if (
                    str_contains($roleName,'paramedico')
                    || str_contains($roleName,'paramédico')
                ) {
                    $level=6;
                } elseif (str_contains($roleName,'contratista')) {
                    $level=7;
                } else {
                    $level=5;
                }

                $adminCreateOrgSeed['roles'][]=[
                    'id_role_group'=>(int)$row['id_role_group'],
                    'name'=>(string)$row['name'],
                    'access_level'=>$level,
                    'label'=>(string)$row['name'],
                ];
            }
        } catch (Throwable $e) {
            error_log('bienvenida role groups: '.$e->getMessage());
        }

        $adminSiteCatalog = $adminPortalRepo->activeSiteCatalog($adminCompanyId);
        $adminProjectsByName = $adminPortalRepo->activeProjectsByName($adminCompanyId);
        $adminProjectCoverage = $adminPortalRepo->projectAssignmentCoverage($adminCompanyId);

        foreach ($adminSiteCatalog as $scopeGbu=>$scopeSites) {
            $adminUserScopeSeed['gbus'][]=(string)$scopeGbu;

            foreach ($scopeSites as $scopeSite) {
                $scopeSiteName=trim((string)($scopeSite['site_name'] ?? ''));

                if ($scopeSiteName!=='') {
                    $adminUserScopeSeed['sites'][]=[
                        'gbu'=>(string)$scopeGbu,
                        'site'=>$scopeSiteName,
                    ];
                }

                foreach (($scopeSite['projects'] ?? []) as $scopeProject) {
                    $adminUserScopeSeed['projects'][]=[
                        'id_project'=>(int)($scopeProject['id_project'] ?? 0),
                        'name'=>(string)($scopeProject['name'] ?? ''),
                        'gbu'=>(string)$scopeGbu,
                        'site'=>$scopeSiteName,
                    ];
                }
            }
        }
    } catch (Throwable $e) {
        $adminSiteCatalog = [];
        $adminProjectsByName = [];
        $adminProjectCoverage = ['total'=>0,'assigned'=>0,'pending'=>0];
        $adminUserScopeSeed = [
            'gbus'=>[],
            'sites'=>[],
            'projects'=>[],
        ];
    }
}

$latestEvaluation = null;
try {
    $latestEvaluation = (new SctUserEvaluationRepository($pdo))
        ->latestCompletedForUser($userEmailRaw);
} catch (Throwable $e) {
    $latestEvaluation = null;
}

$latestEvaluationTone = 'neutral';
$latestEvaluationOutcome = null;

if ($latestEvaluation) {
    $latestStatus = strtolower((string)($latestEvaluation['status'] ?? ''));
    $latestPercentage = $latestEvaluation['result_percentage'];

    if ($latestStatus === 'approved') {
        $latestEvaluationTone = 'success';
        $latestEvaluationOutcome = t('evaluations_status_approved');
    } elseif ($latestStatus === 'failed') {
        $latestEvaluationTone = 'danger';
        $latestEvaluationOutcome = t('evaluations_status_failed');
    } elseif ($latestPercentage !== null) {
        $latestEvaluationTone = (float)$latestPercentage >= 60 ? 'success' : 'danger';
        $latestEvaluationOutcome = $latestEvaluationTone === 'success'
            ? t('evaluations_status_approved')
            : t('evaluations_status_failed');
    }
}
?>
<!DOCTYPE html>
<html lang="<?= htmlspecialchars(idiomaActual(),ENT_QUOTES,'UTF-8') ?>" class="sct-app-frontend">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
<title><?= htmlspecialchars(t('welcome_page_title'),ENT_QUOTES,'UTF-8') ?></title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-LN+7fdVzj6u52u30Kp6M/trliBMCMKTyK833zpbD+pXdCLuTusPj697FH4R/5mcr" crossorigin="anonymous">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
<link rel="stylesheet" href="css/style.css?v=<?= htmlspecialchars(SCT_ASSET_VERSION,ENT_QUOTES,'UTF-8') ?>">
<script>try{if(localStorage.getItem('sct-theme')==='dark')document.documentElement.classList.add('sct-theme-dark');}catch(e){}</script>
</head>
<body class="worker-home-page">
<?php $sctNavbarBasePath='./'; require __DIR__.'/partials/app-navbar.php'; ?>
<main class="worker-home">
  <section class="worker-home__greeting" id="admin-home-start" aria-labelledby="welcomeTitle">
    <div class="worker-home__greeting-copy"><span class="worker-home__eyebrow"><?= htmlspecialchars($rolEtiqueta,ENT_QUOTES,'UTF-8') ?></span><h1 id="welcomeTitle"><?= htmlspecialchars($greeting,ENT_QUOTES,'UTF-8') ?>, <?= htmlspecialchars($nombrePila,ENT_QUOTES,'UTF-8') ?></h1><p><?= htmlspecialchars($empresaNombre ?: 'Safety Control Tower',ENT_QUOTES,'UTF-8') ?></p></div>
    <?php if($empresaLogoSrc!==''): ?><img class="worker-home__company-logo" src="<?= htmlspecialchars($empresaLogoSrc,ENT_QUOTES,'UTF-8') ?>" alt="<?= htmlspecialchars($empresaNombre ?: 'Empresa',ENT_QUOTES,'UTF-8') ?>"><?php endif; ?>
  </section>

  <section class="worker-module-section worker-user-shortcuts" id="admin-user-shortcuts" aria-label="<?= htmlspecialchars(t('home_user_shortcuts'),ENT_QUOTES,'UTF-8') ?>">
    <div class="worker-module-grid worker-module-grid--user-shortcuts">

      <?php if(currentUserHasCapability($pdo,'events.view')): ?>
      <a
        class="home-module-card home-module-card--user-shortcut"
        data-brand="cyan"
        href="api/eventos/gestion-eventos.php?mine=1"
      >
        <span class="home-module-card__main">
          <span class="home-module-card__icon"><i class="bi bi-journal-text"></i></span>
          <span>
            <h3><?= htmlspecialchars(t('home_my_reports'),ENT_QUOTES,'UTF-8') ?></h3>
            <small><?= htmlspecialchars(t('home_my_reports_intro'),ENT_QUOTES,'UTF-8') ?></small>
          </span>
        </span>
        <i class="bi bi-arrow-right home-module-card__arrow"></i>
      </a>
      <?php else: ?>
      <div
        class="home-module-card home-module-card--user-shortcut is-disabled"
        data-brand="cyan"
        aria-disabled="true"
      >
        <span class="home-module-card__main">
          <span class="home-module-card__icon"><i class="bi bi-journal-text"></i></span>
          <span>
            <h3><?= htmlspecialchars(t('home_my_reports'),ENT_QUOTES,'UTF-8') ?></h3>
            <small><?= htmlspecialchars(t('home_no_events_permission'),ENT_QUOTES,'UTF-8') ?></small>
          </span>
        </span>
      </div>
      <?php endif; ?>

      <a
        class="home-module-card home-module-card--user-shortcut home-module-card--evaluation"
        data-brand="gold"
        href="api/evaluaciones/mis-evaluaciones.php"
      >
        <span class="home-module-card__main">
          <span class="home-module-card__icon"><i class="bi bi-clipboard2-check"></i></span>
          <span class="user-shortcut-evaluation">
            <h3><?= htmlspecialchars(t('home_my_evaluations'),ENT_QUOTES,'UTF-8') ?></h3>

            <?php if($latestEvaluation): ?>
              <?php
              $latestName = !empty($latestEvaluation['name_key'])
                  ? t((string)$latestEvaluation['name_key'])
                  : (string)$latestEvaluation['name'];
              $latestResult = $latestEvaluation['result_percentage'];
              ?>
              <small><?= htmlspecialchars(t('home_latest_evaluation'),ENT_QUOTES,'UTF-8') ?></small>
              <strong class="user-shortcut-evaluation__name"><?= htmlspecialchars($latestName,ENT_QUOTES,'UTF-8') ?></strong>

              <span class="user-shortcut-evaluation__result is-<?= htmlspecialchars($latestEvaluationTone,ENT_QUOTES,'UTF-8') ?>">
                <?php if($latestResult!==null): ?>
                  <strong><?= htmlspecialchars(number_format((float)$latestResult,1),ENT_QUOTES,'UTF-8') ?>%</strong>
                <?php endif; ?>
                <?php if($latestEvaluationOutcome): ?>
                  <small><?= htmlspecialchars($latestEvaluationOutcome,ENT_QUOTES,'UTF-8') ?></small>
                <?php endif; ?>
              </span>
            <?php else: ?>
              <small><?= htmlspecialchars(t('home_no_completed_evaluations'),ENT_QUOTES,'UTF-8') ?></small>
            <?php endif; ?>
          </span>
        </span>
        <i class="bi bi-arrow-right home-module-card__arrow"></i>
      </a>

      <?php if($esUsuarioPerfil || $esAdministradorPerfil || $esJefaturaPerfil): ?>
      <button
        class="home-module-card home-module-card--user-shortcut home-module-card--button"
        data-brand="navy"
        type="button"
        data-open-health-form
      >
        <span class="home-module-card__main">
          <span class="home-module-card__icon"><i class="bi bi-person-vcard"></i></span>
          <span>
            <h3><?= htmlspecialchars(t('home_user_data'),ENT_QUOTES,'UTF-8') ?></h3>
            <small><?= htmlspecialchars(t('home_user_data_edit_intro'),ENT_QUOTES,'UTF-8') ?></small>
          </span>
        </span>
        <i class="bi bi-pencil-square home-module-card__arrow"></i>
      </button>
      <?php else: ?>
      <a
        class="home-module-card home-module-card--user-shortcut"
        data-brand="navy"
        href="api/usuarios/mi-perfil.php"
      >
        <span class="home-module-card__main">
          <span class="home-module-card__icon"><i class="bi bi-person-vcard"></i></span>
          <span>
            <h3><?= htmlspecialchars(t('home_user_data'),ENT_QUOTES,'UTF-8') ?></h3>
            <small><?= htmlspecialchars(t('home_user_data_general_intro'),ENT_QUOTES,'UTF-8') ?></small>
          </span>
        </span>
        <i class="bi bi-pencil-square home-module-card__arrow"></i>
      </a>
      <?php endif; ?>

    </div>
  </section>

  <?php if($usaPortalAdministrador): ?>
  <section class="worker-module-section admin-portal" id="admin-portal" data-admin-portal>
    <div class="worker-section-head">
      <div>
        <h2><?= htmlspecialchars(t('admin_portal_title'),ENT_QUOTES,'UTF-8') ?></h2>
        <p><?= htmlspecialchars(t('admin_portal_intro'),ENT_QUOTES,'UTF-8') ?></p>
      </div>
    </div>

    <div class="admin-portal-mode-grid" role="group" aria-label="<?= htmlspecialchars(t('admin_portal_title'),ENT_QUOTES,'UTF-8') ?>">
      <button type="button" class="admin-portal-mode-card" data-tone="cyan" data-admin-panel-target="overview" aria-expanded="false">
        <span class="admin-portal-mode-card__icon"><i class="bi bi-grid-1x2"></i></span>
        <span><strong><?= htmlspecialchars(t('admin_portal_overview'),ENT_QUOTES,'UTF-8') ?></strong><small><?= htmlspecialchars(t('admin_portal_overview_intro'),ENT_QUOTES,'UTF-8') ?></small></span>
        <i class="bi bi-chevron-down"></i>
      </button>
      <button type="button" class="admin-portal-mode-card" data-tone="violet" data-admin-panel-target="site" aria-expanded="false">
        <span class="admin-portal-mode-card__icon"><i class="bi bi-geo-alt"></i></span>
        <span><strong><?= htmlspecialchars(t('admin_portal_site_access'),ENT_QUOTES,'UTF-8') ?></strong><small><?= htmlspecialchars(t('admin_portal_site_access_intro'),ENT_QUOTES,'UTF-8') ?></small></span>
        <i class="bi bi-chevron-down"></i>
      </button>
      <button type="button" class="admin-portal-mode-card" data-tone="gold" data-admin-panel-target="courses" aria-expanded="false">
        <span class="admin-portal-mode-card__icon"><i class="bi bi-mortarboard"></i></span>
        <span><strong><?= htmlspecialchars(t('admin_portal_courses_active'),ENT_QUOTES,'UTF-8') ?></strong><small><?= htmlspecialchars(t('admin_portal_courses_active_intro'),ENT_QUOTES,'UTF-8') ?></small></span>
        <i class="bi bi-chevron-down"></i>
      </button>
      <?php if($puedeGestionarUsuariosEmpresas): ?>
      <button type="button" class="admin-portal-mode-card" data-tone="green" data-admin-section-target="users" aria-expanded="false">
        <span class="admin-portal-mode-card__icon"><i class="bi bi-people"></i></span>
        <span><strong><?= htmlspecialchars(t('admin_users_section_title'),ENT_QUOTES,'UTF-8') ?></strong><small><?= htmlspecialchars(t('admin_users_collapsed_intro'),ENT_QUOTES,'UTF-8') ?></small></span>
        <i class="bi bi-chevron-down"></i>
      </button>
      <button type="button" class="admin-portal-mode-card" data-tone="navy" data-admin-section-target="maintainers" aria-expanded="false">
        <span class="admin-portal-mode-card__icon"><i class="bi bi-sliders"></i></span>
        <span><strong><?= htmlspecialchars(t('admin_maintainers_title'),ENT_QUOTES,'UTF-8') ?></strong><small><?= htmlspecialchars(t('admin_maintainers_collapsed_intro'),ENT_QUOTES,'UTF-8') ?></small></span>
        <i class="bi bi-chevron-down"></i>
      </button>
      <?php endif; ?>
    </div>

    <div class="admin-portal-panel" data-admin-panel="overview" hidden>
      <div class="admin-portal-panel__head">
        <div><span class="section-label"><?= htmlspecialchars(t('admin_portal_overview'),ENT_QUOTES,'UTF-8') ?></span><h3><?= htmlspecialchars(t('admin_portal_overview_menu'),ENT_QUOTES,'UTF-8') ?></h3><p><?= htmlspecialchars(t('admin_portal_overview_macro_note'),ENT_QUOTES,'UTF-8') ?></p></div>
      </div>
      <div class="admin-overview-grid">
        <?php foreach($adminOverviewModules as $module): ?>
          <?php
          $allowed = true;
          if (!empty($module['cap'])) {
              $allowed = currentUserHasCapability($pdo,(string)$module['cap']);
          }
          $available = $allowed && !empty($module['href']);
          ?>
          <?php if($available): ?>
          <a class="admin-overview-card" data-tone="<?= htmlspecialchars($module['tone'],ENT_QUOTES,'UTF-8') ?>" href="<?= htmlspecialchars((string)$module['href'],ENT_QUOTES,'UTF-8') ?>">
            <span class="admin-overview-card__icon"><i class="bi <?= htmlspecialchars($module['icon'],ENT_QUOTES,'UTF-8') ?>"></i></span>
            <strong><?= htmlspecialchars(t((string)$module['label_key']),ENT_QUOTES,'UTF-8') ?></strong>
            <i class="bi bi-arrow-right"></i>
          </a>
          <?php else: ?>
          <button type="button" class="admin-overview-card is-disabled" data-tone="<?= htmlspecialchars($module['tone'],ENT_QUOTES,'UTF-8') ?>" disabled>
            <span class="admin-overview-card__icon"><i class="bi <?= htmlspecialchars($module['icon'],ENT_QUOTES,'UTF-8') ?>"></i></span>
            <strong><?= htmlspecialchars(t((string)$module['label_key']),ENT_QUOTES,'UTF-8') ?></strong>
            <small><?= htmlspecialchars(t('home_coming_soon'),ENT_QUOTES,'UTF-8') ?></small>
          </button>
          <?php endif; ?>
        <?php endforeach; ?>
      </div>
    </div>

    <div class="admin-portal-panel" data-admin-panel="site" hidden>
      <div class="admin-portal-panel__head">
        <div><span class="section-label"><?= htmlspecialchars(t('admin_portal_site_access'),ENT_QUOTES,'UTF-8') ?></span><h3><?= htmlspecialchars(t('admin_portal_site_select_title'),ENT_QUOTES,'UTF-8') ?></h3><p><?= htmlspecialchars(t('admin_portal_site_rule'),ENT_QUOTES,'UTF-8') ?></p></div>
      </div>

      <div class="admin-project-coverage">
        <div data-tone="navy"><span><i class="bi bi-people"></i><?= htmlspecialchars(t('admin_portal_users_active'),ENT_QUOTES,'UTF-8') ?></span><strong><?= (int)$adminProjectCoverage['total'] ?></strong></div>
        <div data-tone="green"><span><i class="bi bi-diagram-3"></i><?= htmlspecialchars(t('admin_portal_users_with_project'),ENT_QUOTES,'UTF-8') ?></span><strong><?= (int)$adminProjectCoverage['assigned'] ?></strong></div>
        <div data-tone="gold" class="<?= (int)$adminProjectCoverage['pending']>0 ? 'has-warning' : '' ?>"><span><i class="bi bi-exclamation-triangle"></i><?= htmlspecialchars(t('admin_portal_users_without_project'),ENT_QUOTES,'UTF-8') ?></span><strong><?= (int)$adminProjectCoverage['pending'] ?></strong></div>
      </div>

      <div class="admin-gbu-selector" role="group" aria-label="GBU">
        <?php
        $gbuTones = ['R&F'=>'cyan','NETWORK'=>'violet','GEN'=>'gold'];
        foreach(array_keys($adminSiteCatalog) as $gbu):
        ?>
          <button
            type="button"
            class="admin-gbu-button"
            data-tone="<?= htmlspecialchars($gbuTones[$gbu] ?? 'navy',ENT_QUOTES,'UTF-8') ?>"
            data-gbu-target="<?= htmlspecialchars($gbu,ENT_QUOTES,'UTF-8') ?>"
            aria-pressed="false"
          >
            <span class="admin-gbu-button__icon"><i class="bi bi-building-gear"></i></span>
            <span><?= htmlspecialchars($gbu,ENT_QUOTES,'UTF-8') ?></span>
          </button>
        <?php endforeach; ?>
      </div>

      <div class="admin-gbu-panels">
        <?php foreach($adminSiteCatalog as $gbu=>$sites): ?>
        <div class="admin-gbu-panel" data-gbu-panel="<?= htmlspecialchars($gbu,ENT_QUOTES,'UTF-8') ?>" hidden>
          <div class="admin-gbu-panel__title"><strong><?= htmlspecialchars($gbu,ENT_QUOTES,'UTF-8') ?></strong><span><?= htmlspecialchars(t('admin_portal_sites'),ENT_QUOTES,'UTF-8') ?></span></div>
          <?php if(!$sites): ?>
            <div class="admin-portal-empty"><i class="bi bi-info-circle"></i><span><?= htmlspecialchars(t('admin_portal_sites_pending'),ENT_QUOTES,'UTF-8') ?></span></div>
          <?php else: ?>
            <div class="admin-site-grid admin-site-grid--database">
              <?php foreach($sites as $site): ?>
                <article class="admin-site-group">
                  <div class="admin-site-group__head">
                    <span><i class="bi bi-pin-map"></i><?= htmlspecialchars((string)$site['site_name'],ENT_QUOTES,'UTF-8') ?></span>
                    <small><?= count($site['projects']) ?> <?= htmlspecialchars(t('admin_portal_projects'),ENT_QUOTES,'UTF-8') ?></small>
                  </div>
                  <div class="admin-site-group__projects">
                    <?php foreach($site['projects'] as $project): ?>
                    <button
                      type="button"
                      class="admin-site-button"
                      data-site-select
                      data-site-gbu="<?= htmlspecialchars($gbu,ENT_QUOTES,'UTF-8') ?>"
                      data-site-name="<?= htmlspecialchars((string)$site['site_name'],ENT_QUOTES,'UTF-8') ?>"
                      data-project-id="<?= (int)$project['id_project'] ?>"
                      aria-pressed="false"
                    >
                      <span><i class="bi bi-diagram-3"></i><?= htmlspecialchars((string)$project['name'],ENT_QUOTES,'UTF-8') ?></span>
                      <small>
                        <span class="admin-site-button__counts">
                          <span><i class="bi bi-person-workspace"></i><?= (int)$project['worker_count'] ?> <?= htmlspecialchars(t('admin_portal_site_workers'),ENT_QUOTES,'UTF-8') ?></span>
                          <span><i class="bi bi-person-check"></i><?= (int)$project['user_count'] ?> <?= htmlspecialchars(t('admin_portal_site_users'),ENT_QUOTES,'UTF-8') ?></span>
                        </span>
                      </small>
                    </button>
                    <?php endforeach; ?>
                  </div>
                </article>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>
        </div>
        <?php endforeach; ?>
      </div>

      <div class="admin-selected-site d-none" data-selected-site-wrap>
        <span><?= htmlspecialchars(t('admin_portal_selected_scope'),ENT_QUOTES,'UTF-8') ?></span>
        <strong data-selected-site></strong>
      </div>

      <div class="admin-site-modules d-none" data-site-modules>
        <div class="admin-site-modules__head">
          <span class="section-label"><?= htmlspecialchars(t('admin_portal_site_modules_label'),ENT_QUOTES,'UTF-8') ?></span>
          <h4><?= htmlspecialchars(t('admin_portal_site_modules_title'),ENT_QUOTES,'UTF-8') ?></h4>
          <p><?= htmlspecialchars(t('admin_portal_site_modules_intro'),ENT_QUOTES,'UTF-8') ?></p>
        </div>

        <div class="admin-overview-grid admin-overview-grid--site">
          <?php foreach($adminOverviewModules as $module): ?>
            <?php
            $allowed = true;
            if (!empty($module['cap'])) {
                $allowed = currentUserHasCapability($pdo,(string)$module['cap']);
            }
            $available = $allowed && !empty($module['href']);
            ?>
            <?php if($available): ?>
            <a
              class="admin-overview-card admin-site-module-card"
              data-tone="<?= htmlspecialchars($module['tone'],ENT_QUOTES,'UTF-8') ?>"
              data-site-module
              data-module-id="<?= htmlspecialchars((string)$module['id'],ENT_QUOTES,'UTF-8') ?>"
              href="#"
            >
              <span class="admin-overview-card__icon"><i class="bi <?= htmlspecialchars($module['icon'],ENT_QUOTES,'UTF-8') ?>"></i></span>
              <strong><?= htmlspecialchars(t((string)$module['label_key']),ENT_QUOTES,'UTF-8') ?></strong>
              <i class="bi bi-arrow-right"></i>
            </a>
            <?php else: ?>
            <button
              type="button"
              class="admin-overview-card is-disabled"
              data-tone="<?= htmlspecialchars($module['tone'],ENT_QUOTES,'UTF-8') ?>"
              disabled
            >
              <span class="admin-overview-card__icon"><i class="bi <?= htmlspecialchars($module['icon'],ENT_QUOTES,'UTF-8') ?>"></i></span>
              <strong><?= htmlspecialchars(t((string)$module['label_key']),ENT_QUOTES,'UTF-8') ?></strong>
              <small><?= htmlspecialchars(t('home_coming_soon'),ENT_QUOTES,'UTF-8') ?></small>
            </button>
            <?php endif; ?>
          <?php endforeach; ?>
        </div>
      </div>
    </div>

    <div class="admin-portal-panel" data-admin-panel="courses" hidden>
      <div class="admin-portal-panel__head">
        <div>
          <span class="section-label"><?= htmlspecialchars(t('admin_portal_courses_active'),ENT_QUOTES,'UTF-8') ?></span>
          <h3><?= htmlspecialchars(t('admin_courses_options_title'),ENT_QUOTES,'UTF-8') ?></h3>
          <p><?= htmlspecialchars(t('admin_courses_options_intro'),ENT_QUOTES,'UTF-8') ?></p>
        </div>
      </div>

      <div class="admin-user-mode-grid admin-course-option-grid" role="group" aria-label="<?= htmlspecialchars(t('admin_courses_options_title'),ENT_QUOTES,'UTF-8') ?>">
        <button
          type="button"
          class="admin-user-mode-card admin-course-option-card is-disabled"
          data-tone="cyan"
          disabled
          aria-disabled="true"
        >
          <span class="admin-user-mode-card__icon"><i class="bi bi-mortarboard"></i></span>
          <span>
            <strong><?= htmlspecialchars(t('admin_courses_my_courses'),ENT_QUOTES,'UTF-8') ?></strong>
            <small><?= htmlspecialchars(t('admin_courses_my_courses_help'),ENT_QUOTES,'UTF-8') ?></small>
          </span>
          <span class="admin-course-option-card__status"><?= htmlspecialchars(t('home_coming_soon'),ENT_QUOTES,'UTF-8') ?></span>
        </button>

        <button
          type="button"
          class="admin-user-mode-card admin-course-option-card is-disabled"
          data-tone="violet"
          disabled
          aria-disabled="true"
        >
          <span class="admin-user-mode-card__icon"><i class="bi bi-people"></i></span>
          <span>
            <strong><?= htmlspecialchars(t('admin_courses_workers_status'),ENT_QUOTES,'UTF-8') ?></strong>
            <small><?= htmlspecialchars(t('admin_courses_workers_status_help'),ENT_QUOTES,'UTF-8') ?></small>
          </span>
          <span class="admin-course-option-card__status"><?= htmlspecialchars(t('home_coming_soon'),ENT_QUOTES,'UTF-8') ?></span>
        </button>
      </div>
    </div>
  </section>

  <?php if($puedeGestionarUsuariosEmpresas): ?>
  <section class="worker-module-section admin-user-management" id="admin-user-management" data-admin-section="users" data-admin-user-management hidden data-csrf="<?= htmlspecialchars((string)$_SESSION['csrf_token'],ENT_QUOTES,'UTF-8') ?>">
    <div class="worker-section-head admin-user-management__head">
      <div>
        <span class="section-label"><?= htmlspecialchars(t('admin_users_section_eyebrow'),ENT_QUOTES,'UTF-8') ?></span>
        <h2><?= htmlspecialchars(t('admin_users_section_title'),ENT_QUOTES,'UTF-8') ?></h2>
        <p><?= htmlspecialchars(
            $esJefaturaPerfil
                ? t('jefatura_users_section_intro')
                : t('admin_users_section_intro'),
            ENT_QUOTES,
            'UTF-8'
        ) ?></p>
        <?php if($esJefaturaPerfil): ?>
        <div class="admin-scope-notice">
          <i class="bi bi-shield-lock"></i>
          <span>
            <?= htmlspecialchars(t('jefatura_scope_notice'),ENT_QUOTES,'UTF-8') ?>
            <br>
            <?= htmlspecialchars(t('jefatura_scope_filters_help'),ENT_QUOTES,'UTF-8') ?>
            <br>
            <?= htmlspecialchars(t('jefatura_search_scope_fallback'),ENT_QUOTES,'UTF-8') ?>
            <br>
            <?= htmlspecialchars(t('jefatura_scope_seed_help'),ENT_QUOTES,'UTF-8') ?>
            <br>
            <?= htmlspecialchars(t('jefatura_company_scope_write'),ENT_QUOTES,'UTF-8') ?>
          </span>
        </div>
        <?php endif; ?>
      </div>
    </div>

    <div class="admin-user-mode-grid" role="group" aria-label="<?= htmlspecialchars(t('admin_users_section_title'),ENT_QUOTES,'UTF-8') ?>">
      <button type="button" class="admin-user-mode-card" data-tone="cyan" data-admin-user-mode="search" aria-expanded="false">
        <span class="admin-user-mode-card__icon"><i class="bi bi-search"></i></span>
        <span><strong><?= htmlspecialchars(t('admin_users_search_manage_title'),ENT_QUOTES,'UTF-8') ?></strong><small><?= htmlspecialchars(t('admin_users_search_manage_help'),ENT_QUOTES,'UTF-8') ?></small></span>
        <i class="bi bi-chevron-down"></i>
      </button>
      <button type="button" class="admin-user-mode-card" data-tone="green" data-admin-user-mode="create" data-admin-new-person aria-expanded="false">
        <span class="admin-user-mode-card__icon"><i class="bi bi-person-plus"></i></span>
        <span><strong><?= htmlspecialchars(t('admin_users_new_person'),ENT_QUOTES,'UTF-8') ?></strong><small><?= htmlspecialchars(t('admin_users_new_person_help'),ENT_QUOTES,'UTF-8') ?></small></span>
        <i class="bi bi-chevron-down"></i>
      </button>
    </div>

    <div class="admin-user-search-panel" data-admin-user-search-panel hidden>
    <div class="admin-user-filter-shell">
      <div class="admin-user-filter-grid">
        <div class="admin-user-filter-card" data-tone="cyan">
          <span class="admin-user-filter-card__icon"><i class="bi bi-building-gear"></i></span>
          <label for="adminUserGbu"><?= htmlspecialchars(t('admin_users_filter_gbu'),ENT_QUOTES,'UTF-8') ?></label>
          <select id="adminUserGbu" class="form-select"><option value=""><?= htmlspecialchars(t('common_all'),ENT_QUOTES,'UTF-8') ?></option></select>
        </div>
        <div class="admin-user-filter-card" data-tone="violet">
          <span class="admin-user-filter-card__icon"><i class="bi bi-geo-alt"></i></span>
          <label for="adminUserSite"><?= htmlspecialchars(t('admin_users_filter_site'),ENT_QUOTES,'UTF-8') ?></label>
          <select id="adminUserSite" class="form-select"><option value=""><?= htmlspecialchars(t('common_all'),ENT_QUOTES,'UTF-8') ?></option></select>
        </div>
        <div class="admin-user-filter-card" data-tone="gold">
          <span class="admin-user-filter-card__icon"><i class="bi bi-diagram-3"></i></span>
          <label for="adminUserProject"><?= htmlspecialchars(t('admin_users_filter_project'),ENT_QUOTES,'UTF-8') ?></label>
          <select id="adminUserProject" class="form-select"><option value=""><?= htmlspecialchars(t('common_all'),ENT_QUOTES,'UTF-8') ?></option></select>
        </div>
      </div>

      <div class="admin-user-search-row">
        <div class="admin-user-search-field">
          <i class="bi bi-search"></i>
          <input id="adminUserSearch" class="form-control" type="search" autocomplete="off" placeholder="<?= htmlspecialchars(t('admin_users_search_placeholder'),ENT_QUOTES,'UTF-8') ?>">
        </div>
        <button id="adminUserSearchBtn" class="btn btn-primary-custom" type="button"><i class="bi bi-search"></i> <?= htmlspecialchars(t('admin_users_search'),ENT_QUOTES,'UTF-8') ?></button>
        <button id="adminUserResetBtn" class="btn btn-outline-custom" type="button"><i class="bi bi-arrow-counterclockwise"></i> <?= htmlspecialchars(t('admin_users_reset'),ENT_QUOTES,'UTF-8') ?></button>
      </div>
    </div>

    <div id="adminUserStatus" class="admin-user-status"><?= htmlspecialchars(t('admin_users_idle'),ENT_QUOTES,'UTF-8') ?></div>
    <div id="adminUserResults" class="admin-user-results"></div>
    </div>

    <div class="admin-create-person" data-admin-create-person hidden>
      <div class="admin-create-person__head">
        <div>
          <span class="section-label"><?= htmlspecialchars(t('admin_users_new_person'),ENT_QUOTES,'UTF-8') ?></span>
          <h3><?= htmlspecialchars(t('admin_users_new_person_title'),ENT_QUOTES,'UTF-8') ?></h3>
          <p><?= htmlspecialchars(t('admin_users_new_person_intro'),ENT_QUOTES,'UTF-8') ?></p>
        <?php if($esJefaturaPerfil): ?>
        <small class="admin-create-scope-note">
          <i class="bi bi-geo-alt"></i>
          <?= htmlspecialchars(t('jefatura_create_defaults_help'),ENT_QUOTES,'UTF-8') ?>
          <br>
          <?= htmlspecialchars(t('jefatura_create_scope_ready'),ENT_QUOTES,'UTF-8') ?>
        </small>
        <?php endif; ?>
        </div>
        <button type="button" class="btn btn-outline-custom" data-admin-new-person-close><i class="bi bi-x-lg"></i> <?= htmlspecialchars(t('common_cancel'),ENT_QUOTES,'UTF-8') ?></button>
      </div>
      <form data-admin-new-person-form>
        <div class="admin-create-required-note">
          <i class="bi bi-asterisk"></i>
          <span><?= htmlspecialchars(t('admin_users_required_note'),ENT_QUOTES,'UTF-8') ?></span>
        </div>
        <div class="admin-create-scope-grid">
          <div class="admin-user-filter-card is-current" data-tone="cyan" data-create-step="gbu">
            <span class="admin-user-filter-card__icon"><i class="bi bi-building-gear"></i></span>
            <label><?= htmlspecialchars(t('admin_users_filter_gbu'),ENT_QUOTES,'UTF-8') ?></label>
            <select class="form-select" name="gbu" data-create-gbu required></select>
            <small class="admin-create-step-help" data-create-step-help="gbu"><?= htmlspecialchars(t('admin_users_step_gbu'),ENT_QUOTES,'UTF-8') ?></small>
          </div>
          <div class="admin-user-filter-card is-locked" data-tone="violet" data-create-step="site">
            <span class="admin-user-filter-card__icon"><i class="bi bi-geo-alt"></i></span>
            <label><?= htmlspecialchars(t('admin_users_filter_site'),ENT_QUOTES,'UTF-8') ?></label>
            <select class="form-select" name="site" data-create-site required disabled></select>
            <small class="admin-create-step-help" data-create-step-help="site"><?= htmlspecialchars(t('admin_users_step_site'),ENT_QUOTES,'UTF-8') ?></small>
          </div>
          <div class="admin-user-filter-card is-locked" data-tone="gold" data-create-step="project">
            <span class="admin-user-filter-card__icon"><i class="bi bi-diagram-3"></i></span>
            <label><?= htmlspecialchars(t('admin_users_filter_project'),ENT_QUOTES,'UTF-8') ?></label>
            <select class="form-select" name="id_project" data-create-project required disabled></select>
            <small class="admin-create-step-help" data-create-step-help="project"><?= htmlspecialchars(t('admin_users_step_project'),ENT_QUOTES,'UTF-8') ?></small>
          </div>
        </div>
        <div class="admin-create-mode">
          <label class="admin-create-mode__option"><input type="radio" name="record_type" value="worker" checked><span><i class="bi bi-person-vcard"></i><strong><?= htmlspecialchars(t('admin_users_worker_only'),ENT_QUOTES,'UTF-8') ?></strong><small><?= htmlspecialchars(t('admin_users_worker_only_help'),ENT_QUOTES,'UTF-8') ?></small></span></label>
          <label class="admin-create-mode__option"><input type="radio" name="record_type" value="user"><span><i class="bi bi-person-lock"></i><strong><?= htmlspecialchars(t('admin_users_worker_user'),ENT_QUOTES,'UTF-8') ?></strong><small><?= htmlspecialchars(t('admin_users_worker_user_help'),ENT_QUOTES,'UTF-8') ?></small></span></label>
        </div>
        <div class="admin-profile-block" data-tone="cyan">
          <div class="admin-profile-block__head"><i class="bi bi-person-vcard"></i><div><h2><?= htmlspecialchars(t('admin_user_profile_identity'),ENT_QUOTES,'UTF-8') ?></h2><p><?= htmlspecialchars(t('admin_user_profile_identity_intro'),ENT_QUOTES,'UTF-8') ?></p></div></div>
          <div class="row g-3">
            <div class="col-12 col-md-6"><label class="form-label"><?= htmlspecialchars(t('profile_name'),ENT_QUOTES,'UTF-8') ?></label><input class="form-control" name="name" maxlength="50" required></div>
            <div class="col-12 col-md-6"><label class="form-label"><?= htmlspecialchars(t('profile_lastname'),ENT_QUOTES,'UTF-8') ?></label><input class="form-control" name="lastname" maxlength="50"></div>
            <div class="col-12 col-md-6"><label class="form-label">RUT</label><input class="form-control" name="rut" maxlength="10" placeholder="12345678-9"></div>
            <div class="col-12 col-md-6 d-none" data-create-email-wrap>
              <label class="form-label"><?= htmlspecialchars(t('users_email'),ENT_QUOTES,'UTF-8') ?></label>
              <input class="form-control" type="email" name="email" maxlength="150" data-create-email disabled>
              <small class="form-text"><?= htmlspecialchars(t('admin_users_email_user_only'),ENT_QUOTES,'UTF-8') ?></small>
            </div>
            <div class="col-12 col-md-6"><label class="form-label"><?= htmlspecialchars(t('profile_phone'),ENT_QUOTES,'UTF-8') ?></label><input class="form-control" name="phone" maxlength="20"></div>
            <div class="col-12 col-md-6" data-create-user-only><label class="form-label"><?= htmlspecialchars(t('profile_language'),ENT_QUOTES,'UTF-8') ?></label><select class="form-select" name="language"><?php foreach(idiomasDisponiblesConNombre() as $code=>$label): ?><option value="<?= htmlspecialchars($code,ENT_QUOTES,'UTF-8') ?>"><?= htmlspecialchars($label,ENT_QUOTES,'UTF-8') ?></option><?php endforeach; ?></select></div>
          </div>
        </div>
        <div class="admin-profile-block admin-user-org-block" data-tone="violet">
          <div class="admin-profile-block__head">
            <i class="bi bi-diagram-3"></i>
            <div>
              <h2><?= htmlspecialchars(t('admin_user_org_title'),ENT_QUOTES,'UTF-8') ?></h2>
              <p><?= htmlspecialchars(t('admin_user_org_intro'),ENT_QUOTES,'UTF-8') ?></p>
            </div>
          </div>
          <div class="row g-3">
            <div class="col-12 col-md-6 col-xl-3">
              <label class="form-label"><?= htmlspecialchars(t('admin_user_profile_position'),ENT_QUOTES,'UTF-8') ?></label>
              <?php if($esJefaturaPerfil): ?>
              <input
                class="form-control"
                type="text"
                name="position"
                maxlength="100"
                data-create-position
                placeholder="<?= htmlspecialchars(t('jefatura_position_placeholder'),ENT_QUOTES,'UTF-8') ?>"
              >
              <?php else: ?>
              <select class="form-select" name="position" data-create-position></select>
              <?php endif; ?>
            </div>
            <div class="col-12 col-md-6 col-xl-3" data-create-user-only>
              <label class="form-label"><?= htmlspecialchars(t('admin_users_profile_group'),ENT_QUOTES,'UTF-8') ?></label>
              <select class="form-select" name="id_role_group" data-create-role></select>
              <?php if($esJefaturaPerfil): ?>
              <small class="form-text">
                <?= htmlspecialchars(t('jefatura_profile_group_help'),ENT_QUOTES,'UTF-8') ?>
              </small>
              <?php endif; ?>
            </div>
            <div class="col-12 col-md-6 col-xl-3">
              <label class="form-label"><?= htmlspecialchars(t('admin_users_jefatura'),ENT_QUOTES,'UTF-8') ?></label>
              <?php if($esJefaturaPerfil): ?>
              <input
                class="form-control mb-2"
                type="search"
                maxlength="150"
                data-create-jefatura-search
                placeholder="<?= htmlspecialchars(t('jefatura_search_placeholder'),ENT_QUOTES,'UTF-8') ?>"
                autocomplete="off"
              >
              <?php endif; ?>
              <select class="form-select" name="id_jefatura" data-create-jefatura></select>
              <small class="form-text" data-create-hierarchy-help="jefatura"></small>
            </div>
            <div class="col-12 col-md-6 col-xl-3" data-create-user-only>
              <label class="form-label"><?= htmlspecialchars(t('admin_users_gerencia'),ENT_QUOTES,'UTF-8') ?></label>
              <select class="form-select" name="id_gerencia" data-create-gerencia></select>
              <small class="form-text" data-create-hierarchy-help="gerencia"></small>
              <?php if($esJefaturaPerfil): ?>
              <small class="form-text d-block mt-1">
                <?= htmlspecialchars(t('jefatura_company_org_catalog_help'),ENT_QUOTES,'UTF-8') ?>
                <br>
                <?= htmlspecialchars(t('jefatura_org_lists_companywide'),ENT_QUOTES,'UTF-8') ?>
              </small>
              <?php endif; ?>
            </div>
          </div>
        </div>

        <div class="admin-profile-block" data-tone="green" data-create-user-only>
          <div class="admin-profile-block__head"><i class="bi bi-heart-pulse"></i><div><h2><?= htmlspecialchars(t('admin_user_profile_health_affiliation'),ENT_QUOTES,'UTF-8') ?></h2><p><?= htmlspecialchars(t('admin_user_profile_health_affiliation_intro'),ENT_QUOTES,'UTF-8') ?></p></div></div>
          <div class="row g-3">
            <div class="col-12 col-md-6"><label class="form-label"><?= htmlspecialchars(t('admin_user_profile_mutual'),ENT_QUOTES,'UTF-8') ?></label><select class="form-select" name="mutual_code"><option value="">—</option><option value="achs">ACHS — Asociación Chilena de Seguridad</option><option value="isl">ISL</option><option value="ist">IST</option><option value="mutual">Mutual de Seguridad</option></select></div>
            <div class="col-12 col-md-6"><label class="form-label"><?= htmlspecialchars(t('admin_user_profile_experience'),ENT_QUOTES,'UTF-8') ?></label><input class="form-control" type="number" min="0" max="80" name="years_experience_current_role"></div>
            <div class="col-12 col-md-6"><label class="form-label"><?= htmlspecialchars(t('admin_user_profile_contractor_flag'),ENT_QUOTES,'UTF-8') ?></label><select class="form-select" name="hired_by_contractor" data-create-contractor-flag><option value="no"><?= htmlspecialchars(t('common_no'),ENT_QUOTES,'UTF-8') ?></option><option value="yes"><?= htmlspecialchars(t('common_yes'),ENT_QUOTES,'UTF-8') ?></option></select></div>
            <div class="col-12 col-md-6 d-none" data-create-contractor-wrap><label class="form-label"><?= htmlspecialchars(t('admin_user_profile_contractor'),ENT_QUOTES,'UTF-8') ?></label><select class="form-select" name="id_contractor_company" data-create-contractor></select></div>
          </div>
        </div>
        <div class="admin-profile-block" data-tone="gold" data-create-user-only>
          <div class="admin-profile-block__head"><i class="bi bi-telephone-plus"></i><div><h2><?= htmlspecialchars(t('admin_user_profile_emergency'),ENT_QUOTES,'UTF-8') ?></h2><p><?= htmlspecialchars(t('admin_user_profile_emergency_intro'),ENT_QUOTES,'UTF-8') ?></p></div></div>
          <div class="row g-3">
            <div class="col-12 col-md-5"><label class="form-label"><?= htmlspecialchars(t('admin_user_profile_emergency_name'),ENT_QUOTES,'UTF-8') ?></label><input class="form-control" name="emergency_contact_name" maxlength="150"></div>
            <div class="col-12 col-md-4"><label class="form-label"><?= htmlspecialchars(t('admin_user_profile_emergency_phone'),ENT_QUOTES,'UTF-8') ?></label><input class="form-control" name="emergency_contact_phone" maxlength="30"></div>
            <div class="col-12 col-md-3"><label class="form-label"><?= htmlspecialchars(t('admin_user_profile_emergency_relation'),ENT_QUOTES,'UTF-8') ?></label><select class="form-select" name="emergency_contact_relation"><option value="">—</option><?php foreach(['partner','direct_family','friend','other'] as $rel): ?><option value="<?= htmlspecialchars($rel,ENT_QUOTES,'UTF-8') ?>"><?= htmlspecialchars(t('profile_relation_'.$rel),ENT_QUOTES,'UTF-8') ?></option><?php endforeach; ?></select></div>
          </div>
        </div>
        <div class="admin-create-person__footer"><div class="admin-create-person__message d-none" data-create-message role="status" aria-live="polite"></div><button type="submit" class="btn btn-primary-custom"><i class="bi bi-check2-circle"></i> <?= htmlspecialchars(t('admin_users_create'),ENT_QUOTES,'UTF-8') ?></button></div>
      </form>
    </div>

  </section>

  <section class="worker-module-section admin-maintainers admin-company-management" id="admin-maintainers" data-admin-section="maintainers" data-admin-company-management data-company-role="<?= htmlspecialchars($rolPrincipal,ENT_QUOTES,'UTF-8') ?>" data-csrf="<?= htmlspecialchars((string)$_SESSION['csrf_token'],ENT_QUOTES,'UTF-8') ?>" hidden>
    <div class="worker-section-head">
      <div>
        <span class="section-label"><?= htmlspecialchars(t('company_manage_eyebrow'),ENT_QUOTES,'UTF-8') ?></span>
        <h2><?= htmlspecialchars(t('admin_maintainers_title'),ENT_QUOTES,'UTF-8') ?></h2>
        <p><?= htmlspecialchars(t('company_manage_intro'),ENT_QUOTES,'UTF-8') ?></p>
      </div>
    </div>

    <div class="admin-user-mode-grid admin-company-mode-grid">
      <button type="button" class="admin-user-mode-card" data-tone="cyan" data-company-mode="company" aria-expanded="false">
        <span class="admin-user-mode-card__icon"><i class="bi bi-building"></i></span>
        <span><strong><?= htmlspecialchars(t('company_manage_company_title'),ENT_QUOTES,'UTF-8') ?></strong><small><?= htmlspecialchars(t('company_manage_company_help'),ENT_QUOTES,'UTF-8') ?></small></span>
        <i class="bi bi-chevron-down"></i>
      </button>
      <button type="button" class="admin-user-mode-card" data-tone="violet" data-company-mode="centers" aria-expanded="false">
        <span class="admin-user-mode-card__icon"><i class="bi bi-geo"></i></span>
        <span><strong><?= htmlspecialchars(t('company_manage_centers_title'),ENT_QUOTES,'UTF-8') ?></strong><small><?= htmlspecialchars(t('company_manage_centers_help'),ENT_QUOTES,'UTF-8') ?></small></span>
        <i class="bi bi-chevron-down"></i>
      </button>
      <button type="button" class="admin-user-mode-card" data-tone="green" data-company-mode="scope" aria-expanded="false">
        <span class="admin-user-mode-card__icon"><i class="bi bi-diagram-3"></i></span>
        <span><strong><?= htmlspecialchars(t('company_manage_scope_title'),ENT_QUOTES,'UTF-8') ?></strong><small><?= htmlspecialchars(t('company_manage_scope_help'),ENT_QUOTES,'UTF-8') ?></small></span>
        <i class="bi bi-chevron-down"></i>
      </button>
    </div>

    <div class="admin-company-panel" data-company-panel="company" hidden>
      <form data-company-form>
        <div class="admin-profile-block" data-tone="cyan">
          <div class="admin-profile-block__head"><i class="bi bi-building"></i><div><h2><?= htmlspecialchars(t('company_manage_company_title'),ENT_QUOTES,'UTF-8') ?></h2><p><?= htmlspecialchars(t('company_manage_company_help'),ENT_QUOTES,'UTF-8') ?></p></div></div>
          <div class="row g-3">
            <div class="col-12 col-md-4"><label class="form-label"><?= htmlspecialchars(t('company_manage_rut'),ENT_QUOTES,'UTF-8') ?></label><input class="form-control" name="rut" required></div>
            <div class="col-12 col-md-8"><label class="form-label"><?= htmlspecialchars(t('company_manage_name'),ENT_QUOTES,'UTF-8') ?></label><input class="form-control" name="razon_social" maxlength="150" required></div>
            <div class="col-12 col-md-8"><label class="form-label"><?= htmlspecialchars(t('company_manage_matrix_address'),ENT_QUOTES,'UTF-8') ?></label><input class="form-control" name="address" maxlength="255" required></div>
            <div class="col-12 col-md-4"><label class="form-label"><?= htmlspecialchars(t('company_manage_email'),ENT_QUOTES,'UTF-8') ?></label><input class="form-control" type="email" name="email" maxlength="50" required></div>
            <div class="col-12">
              <label class="admin-company-switch">
                <input type="checkbox" name="is_contractor" value="1">
                <span><strong><?= htmlspecialchars(t('company_manage_contractor'),ENT_QUOTES,'UTF-8') ?></strong><small><?= htmlspecialchars(t('company_manage_contractor_help'),ENT_QUOTES,'UTF-8') ?></small></span>
              </label>
            </div>
          </div>
        </div>
        <div class="admin-company-summary" data-company-profile-summary></div>
        <div class="admin-profile-actions"><button class="btn btn-primary-custom" type="submit"><i class="bi bi-check2-circle"></i> <?= htmlspecialchars(t('profile_save'),ENT_QUOTES,'UTF-8') ?></button></div>
      </form>
    </div>

    <div class="admin-company-panel" data-company-panel="centers" hidden>
      <div class="admin-company-toolbar">
        <div><h3><?= htmlspecialchars(t('company_manage_centers_title'),ENT_QUOTES,'UTF-8') ?></h3><p><?= htmlspecialchars(t('company_manage_centers_intro'),ENT_QUOTES,'UTF-8') ?></p></div>
        <button class="btn btn-primary-custom" type="button" data-new-center><i class="bi bi-plus-lg"></i> <?= htmlspecialchars(t('company_manage_new_center'),ENT_QUOTES,'UTF-8') ?></button>
      </div>
      <div class="admin-company-list" data-centers-list></div>
      <form class="admin-company-editor" data-center-form hidden>
        <input type="hidden" name="id_company_center">
        <div class="row g-3">
          <div class="col-12 col-md-5"><label class="form-label"><?= htmlspecialchars(t('company_manage_center_name'),ENT_QUOTES,'UTF-8') ?></label><input class="form-control" name="name" maxlength="50" required></div>
          <div class="col-12 col-md-7"><label class="form-label"><?= htmlspecialchars(t('company_manage_center_address'),ENT_QUOTES,'UTF-8') ?></label><input class="form-control" name="address" maxlength="255" required></div>
          <div class="col-12"><label class="form-label"><?= htmlspecialchars(t('company_manage_description'),ENT_QUOTES,'UTF-8') ?></label><textarea class="form-control" name="description" maxlength="255" rows="2"></textarea></div>
          <div class="col-12 col-md-4"><label class="form-label"><?= htmlspecialchars(t('company_manage_state'),ENT_QUOTES,'UTF-8') ?></label><select class="form-select" name="state"><option value="1"><?= htmlspecialchars(t('company_manage_active'),ENT_QUOTES,'UTF-8') ?></option><option value="0"><?= htmlspecialchars(t('company_manage_inactive'),ENT_QUOTES,'UTF-8') ?></option></select></div>
        </div>
        <div class="admin-profile-actions"><button type="button" class="btn btn-outline-custom" data-center-cancel><?= htmlspecialchars(t('common_cancel'),ENT_QUOTES,'UTF-8') ?></button><button type="submit" class="btn btn-primary-custom"><?= htmlspecialchars(t('profile_save'),ENT_QUOTES,'UTF-8') ?></button></div>
      </form>
    </div>

    <div class="admin-company-panel" data-company-panel="scope" hidden>
      <div class="admin-company-toolbar">
        <div>
          <h3><?= htmlspecialchars(t('company_manage_scope_title'),ENT_QUOTES,'UTF-8') ?></h3>
          <p><?= htmlspecialchars(t('company_manage_scope_intro'),ENT_QUOTES,'UTF-8') ?></p>
        </div>
      </div>

      <div class="admin-company-scope-stats" data-company-scope-stats></div>
      <div class="admin-company-list admin-company-scope-list" data-projects-list></div>

      <div class="admin-create-required-note">
        <i class="bi bi-asterisk"></i>
        <span><?= htmlspecialchars(t('admin_users_required_note'),ENT_QUOTES,'UTF-8') ?></span>
      </div>

      <div class="admin-company-scope-selector">
        <div class="admin-company-scope-field" data-tone="cyan">
          <label class="form-label" for="companyScopeGbu"><?= htmlspecialchars(t('admin_users_filter_gbu'),ENT_QUOTES,'UTF-8') ?></label>
          <select class="form-select" id="companyScopeGbu" data-company-gbu required></select>
          <input class="form-control mt-2" type="text" maxlength="20" data-company-gbu-new placeholder="<?= htmlspecialchars(t('company_manage_new_gbu_placeholder'),ENT_QUOTES,'UTF-8') ?>" hidden>
        </div>

        <div class="admin-company-scope-field" data-tone="violet">
          <label class="form-label" for="companyScopeSite"><?= htmlspecialchars(t('admin_users_filter_site'),ENT_QUOTES,'UTF-8') ?></label>
          <select class="form-select" id="companyScopeSite" data-company-site required disabled></select>
          <input class="form-control mt-2" type="text" maxlength="150" data-company-site-new placeholder="<?= htmlspecialchars(t('company_manage_new_site_placeholder'),ENT_QUOTES,'UTF-8') ?>" hidden>
        </div>

        <div class="admin-company-scope-field" data-tone="green">
          <label class="form-label" for="companyScopeProject"><?= htmlspecialchars(t('admin_users_filter_project'),ENT_QUOTES,'UTF-8') ?></label>
          <select class="form-select" id="companyScopeProject" data-company-project required disabled></select>
          <input class="form-control mt-2" type="text" maxlength="150" data-company-project-new placeholder="<?= htmlspecialchars(t('company_manage_new_project_placeholder'),ENT_QUOTES,'UTF-8') ?>" hidden>
        </div>
      </div>

      <div class="admin-company-gbu-actions admin-company-scope-actions">
        <button type="button" class="btn btn-outline-custom" data-edit-gbu disabled>
          <i class="bi bi-pencil-square"></i>
          <?= htmlspecialchars(t('company_manage_edit_gbu'),ENT_QUOTES,'UTF-8') ?>
        </button>
        <button type="button" class="btn btn-outline-danger-custom" data-delete-gbu disabled>
          <i class="bi bi-trash3"></i>
          <?= htmlspecialchars(t('company_manage_delete_gbu'),ENT_QUOTES,'UTF-8') ?>
        </button>

        <button type="button" class="btn btn-outline-custom" data-edit-site disabled>
          <i class="bi bi-pencil-square"></i>
          <?= htmlspecialchars(t('company_manage_edit_site'),ENT_QUOTES,'UTF-8') ?>
        </button>
        <button type="button" class="btn btn-outline-danger-custom" data-delete-site disabled>
          <i class="bi bi-trash3"></i>
          <?= htmlspecialchars(t('company_manage_delete_site'),ENT_QUOTES,'UTF-8') ?>
        </button>

        <button type="button" class="btn btn-outline-custom" data-edit-project disabled>
          <i class="bi bi-pencil-square"></i>
          <?= htmlspecialchars(t('company_manage_edit_project'),ENT_QUOTES,'UTF-8') ?>
        </button>
        <button type="button" class="btn btn-outline-danger-custom" data-delete-project disabled>
          <i class="bi bi-trash3"></i>
          <?= htmlspecialchars(t('company_manage_delete_project'),ENT_QUOTES,'UTF-8') ?>
        </button>
      </div>

      <form class="admin-company-editor" data-project-form hidden>
        <input type="hidden" name="id_project">
        <input type="hidden" name="gbu_code">
        <input type="hidden" name="site_name">

        <div class="row g-3">
          <div class="col-12">
            <div class="admin-company-scope-summary" data-company-scope-summary></div>
          </div>
          <div class="col-12">
            <label class="form-label"><?= htmlspecialchars(t('company_manage_description'),ENT_QUOTES,'UTF-8') ?></label>
            <textarea class="form-control" name="description" maxlength="255" rows="2"></textarea>
          </div>
          <div class="col-12 col-md-4">
            <label class="form-label"><?= htmlspecialchars(t('company_manage_state'),ENT_QUOTES,'UTF-8') ?></label>
            <select class="form-select" name="state">
              <option value="1"><?= htmlspecialchars(t('company_manage_active'),ENT_QUOTES,'UTF-8') ?></option>
              <option value="0"><?= htmlspecialchars(t('company_manage_inactive'),ENT_QUOTES,'UTF-8') ?></option>
            </select>
          </div>
        </div>

        <div class="admin-profile-actions">
          <button type="button" class="btn btn-outline-custom" data-project-cancel>
            <?= htmlspecialchars(t('common_cancel'),ENT_QUOTES,'UTF-8') ?>
          </button>
          <button type="submit" class="btn btn-primary-custom">
            <i class="bi bi-check2-circle"></i>
            <?= htmlspecialchars(t('profile_save'),ENT_QUOTES,'UTF-8') ?>
          </button>
        </div>
      </form>

      <div class="admin-company-gbu-editor" data-site-editor hidden>
        <div>
          <label class="form-label"><?= htmlspecialchars(t('company_manage_new_site_name'),ENT_QUOTES,'UTF-8') ?></label>
          <input class="form-control" type="text" maxlength="150" data-site-rename-input required>
        </div>
        <div class="admin-profile-actions">
          <button type="button" class="btn btn-outline-custom" data-site-rename-cancel><?= htmlspecialchars(t('common_cancel'),ENT_QUOTES,'UTF-8') ?></button>
          <button type="button" class="btn btn-primary-custom" data-site-rename-save><?= htmlspecialchars(t('profile_save'),ENT_QUOTES,'UTF-8') ?></button>
        </div>
      </div>

      <div class="admin-company-gbu-editor" data-gbu-editor hidden>
        <div>
          <label class="form-label"><?= htmlspecialchars(t('company_manage_new_gbu_name'),ENT_QUOTES,'UTF-8') ?></label>
          <input class="form-control" type="text" maxlength="20" data-gbu-rename-input required>
        </div>
        <div class="admin-profile-actions">
          <button type="button" class="btn btn-outline-custom" data-gbu-rename-cancel><?= htmlspecialchars(t('common_cancel'),ENT_QUOTES,'UTF-8') ?></button>
          <button type="button" class="btn btn-primary-custom" data-gbu-rename-save><?= htmlspecialchars(t('profile_save'),ENT_QUOTES,'UTF-8') ?></button>
        </div>
      </div>
    </div>

    <div class="admin-user-status" data-company-status><?= htmlspecialchars(t('company_manage_idle'),ENT_QUOTES,'UTF-8') ?></div>
  </section>
  <?php endif; // puedeGestionarUsuariosEmpresas ?>
  <?php endif; ?>

  <?php if($esUsuarioPerfil): ?>
  <section class="worker-module-section" id="modulos">
    <div class="worker-section-head">
      <div>
        <h2><?= htmlspecialchars(t('home_user_modules_title'),ENT_QUOTES,'UTF-8') ?></h2>
        <p><?= htmlspecialchars(t('home_user_modules_intro'),ENT_QUOTES,'UTF-8') ?></p>
      </div>
    </div>

    <div class="worker-module-grid worker-module-grid--operational">
      <?php foreach($userOperationalModules as $item): ?>
        <?php if(!empty($item['disabled'])): ?>
          <div class="home-module-card is-disabled" data-brand="<?= htmlspecialchars($item['brand'],ENT_QUOTES,'UTF-8') ?>" aria-disabled="true">
            <span class="home-module-card__badge"><?= htmlspecialchars(t('home_coming_soon'),ENT_QUOTES,'UTF-8') ?></span>
            <span class="home-module-card__main">
              <span class="home-module-card__icon"><i class="bi <?= htmlspecialchars($item['icon'],ENT_QUOTES,'UTF-8') ?>"></i></span>
              <h3><?= htmlspecialchars($item['label'],ENT_QUOTES,'UTF-8') ?></h3>
            </span>
          </div>
        <?php else: ?>
          <a class="home-module-card" data-brand="<?= htmlspecialchars($item['brand'],ENT_QUOTES,'UTF-8') ?>" href="<?= htmlspecialchars($item['href'],ENT_QUOTES,'UTF-8') ?>">
            <span class="home-module-card__main">
              <span class="home-module-card__icon"><i class="bi <?= htmlspecialchars($item['icon'],ENT_QUOTES,'UTF-8') ?>"></i></span>
              <h3><?= htmlspecialchars($item['label'],ENT_QUOTES,'UTF-8') ?></h3>
            </span>
            <i class="bi bi-arrow-right home-module-card__arrow"></i>
          </a>
        <?php endif; ?>
      <?php endforeach; ?>
    </div>
  </section>
  <?php endif; ?>

  <?php if(!$esUsuarioPerfil && !$usaPortalAdministrador): ?>
  <section class="worker-module-section"><div class="worker-section-head"><div><h2><?= htmlspecialchars(t('home_direct_access'),ENT_QUOTES,'UTF-8') ?></h2><p><?= htmlspecialchars(t('welcome_intro'),ENT_QUOTES,'UTF-8') ?></p></div></div>
    <div class="worker-module-grid">
      <?php foreach($directModules as $item): ?>
        <?php if(!empty($item['disabled'])): ?>
        <div class="home-module-card is-disabled" data-brand="<?= htmlspecialchars($item['brand'],ENT_QUOTES,'UTF-8') ?>" aria-disabled="true"><span class="home-module-card__badge"><?= htmlspecialchars(t('home_coming_soon'),ENT_QUOTES,'UTF-8') ?></span><span class="home-module-card__main"><span class="home-module-card__icon"><i class="bi <?= htmlspecialchars($item['icon'],ENT_QUOTES,'UTF-8') ?>"></i></span><h3><?= htmlspecialchars($item['label'],ENT_QUOTES,'UTF-8') ?></h3></span></div>
        <?php else: ?>
        <a class="home-module-card" data-brand="<?= htmlspecialchars($item['brand'],ENT_QUOTES,'UTF-8') ?>" href="<?= htmlspecialchars($item['href'],ENT_QUOTES,'UTF-8') ?>"><span class="home-module-card__main"><span class="home-module-card__icon"><i class="bi <?= htmlspecialchars($item['icon'],ENT_QUOTES,'UTF-8') ?>"></i></span><h3><?= htmlspecialchars($item['label'],ENT_QUOTES,'UTF-8') ?></h3></span><i class="bi bi-arrow-right home-module-card__arrow"></i></a>
        <?php endif; ?>
      <?php endforeach; ?>
    </div>
  </section>
  <?php endif; ?>

  <?php if(!$esUsuarioPerfil && !$usaPortalAdministrador && $managementModules): ?><section class="worker-module-section"><div class="worker-section-head"><div><h2><?= htmlspecialchars(t('mgmt_title'),ENT_QUOTES,'UTF-8') ?></h2></div></div><div class="management-module-grid"><?php foreach($managementModules as $module): ?><a class="management-module-card" data-tone="<?= htmlspecialchars($module['tone'],ENT_QUOTES,'UTF-8') ?>" href="<?= htmlspecialchars($module['href'],ENT_QUOTES,'UTF-8') ?>"><span class="home-module-card__icon"><i class="bi <?= htmlspecialchars($module['icon'],ENT_QUOTES,'UTF-8') ?>"></i></span><div><h3><?= htmlspecialchars($module['label'],ENT_QUOTES,'UTF-8') ?></h3><p><?= htmlspecialchars($module['description'] ?? '',ENT_QUOTES,'UTF-8') ?></p></div><i class="bi bi-arrow-right"></i></a><?php endforeach; ?></div></section><?php endif; ?>
</main>

<?php if($usaPortalAdministrador): ?>
<script id="adminUserScopeSeed" type="application/json"><?= json_encode(
    $adminUserScopeSeed,
    JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES
) ?></script>
<script id="adminCreateOrgSeed" type="application/json"><?= json_encode(
    $adminCreateOrgSeed,
    JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES
) ?></script>
<script id="adminUserI18n" type="application/json"><?= json_encode([
    'idle'=>t('admin_users_idle'),
    'empty'=>t('admin_users_empty'),
    'found'=>t('admin_users_found'),
    'no_role'=>t('role_none'),
    'active'=>t('common_active'),
    'inactive'=>t('common_inactive'),
    'no_gbu'=>t('admin_users_no_gbu'),
    'no_site'=>t('admin_users_no_site'),
    'no_project'=>t('admin_users_no_project'),
    'no_supervisor'=>t('admin_users_no_jefatura'),
    'edit'=>t('common_edit'),
    'activate'=>t('users_action_activate'),
    'deactivate'=>t('users_action_deactivate'),
    'send_access'=>t('admin_users_send_access'),
    'loading_filters'=>t('admin_users_loading_filters'),
    'filter_error'=>t('admin_users_filter_error'),
    'scope_catalog_empty'=>t('jefatura_scope_catalog_empty'),
    'scope_required'=>t('admin_users_scope_required'),
    'searching'=>t('admin_users_searching'),
    'search_error'=>t('admin_users_search_error'),
    'select'=>t('admin_users_select_option'),
    'create_options_error'=>t('admin_users_create_options_error'),
    'org_catalog_missing'=>t('jefatura_org_catalog_missing'),
    'missing_jefaturas'=>t('jefatura_org_missing_jefaturas'),
    'missing_roles'=>t('jefatura_org_missing_roles'),
    'missing_gerencias'=>t('jefatura_org_missing_gerencias'),
    'create_error'=>t('admin_users_create_error'),
    'create_http_error'=>t('admin_users_create_http_error'),
    'created_worker_confirm'=>t('admin_users_created_worker_confirm'),
    'created_user_confirm'=>t('admin_users_created_user_confirm'),
    'step_wait_gbu'=>t('admin_users_step_wait_gbu'),
    'step_wait_site'=>t('admin_users_step_wait_site'),
    'step_wait_project'=>t('admin_users_step_wait_project'),
    'step_jefatura_required'=>t('admin_users_step_jefatura_required'),
    'step_jefatura_optional'=>t('admin_users_step_jefatura_optional'),
    'step_gerencia_required'=>t('admin_users_step_gerencia_required'),
    'step_gerencia_optional'=>t('admin_users_step_gerencia_optional'),
    'delete_access'=>t('admin_users_delete_access'),
    'delete_confirm'=>t('admin_users_delete_confirm'),
    'delete_error'=>t('admin_users_delete_error'),
], JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES) ?></script>
<script id="adminPortalNavConfig" type="application/json"><?= json_encode(array_values(array_filter([
    [
        'id' => 'home',
        'label' => t('nav_home'),
        'icon' => 'bi-house-door',
        'href' => '#admin-home-start',
    ],
    [
        'id' => 'reports',
        'label' => t('home_my_reports'),
        'icon' => 'bi-journal-text',
        'href' => 'api/eventos/gestion-eventos.php?mine=1',
    ],
    [
        'id' => 'evaluations',
        'label' => t('home_my_evaluations'),
        'icon' => 'bi-clipboard2-check',
        'href' => 'api/evaluaciones/mis-evaluaciones.php',
    ],
    [
        'id' => 'user-data',
        'label' => t('home_user_data'),
        'icon' => 'bi-person-vcard',
        'href' => $esAdministradorPerfil
            ? 'api/usuarios/perfil-completo.php'
            : 'api/usuarios/mi-perfil.php',
    ],
    [
        'id' => 'overview',
        'label' => t('admin_portal_overview'),
        'icon' => 'bi-grid-1x2',
        'href' => '#admin-portal',
        'panel' => 'overview',
    ],
    [
        'id' => 'site',
        'label' => t('admin_portal_site_access'),
        'icon' => 'bi-geo-alt',
        'href' => '#admin-portal',
        'panel' => 'site',
    ],
    [
        'id' => 'courses',
        'label' => t('admin_portal_courses_active'),
        'icon' => 'bi-mortarboard',
        'href' => '#admin-portal',
        'panel' => 'courses',
    ],
    $puedeGestionarUsuariosEmpresas ? [
        'id' => 'maintainers',
        'label' => t('admin_maintainers_title'),
        'icon' => 'bi-sliders',
        'href' => '#admin-maintainers',
        'section' => 'maintainers',
    ] : null,
], static fn($item): bool => is_array($item))), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?></script>
<?php endif; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>
<?php
if ($esUsuarioPerfil || $esAdministradorPerfil || $esJefaturaPerfil) {
    $healthFormForced=false;
    $healthBasePath='./';
    require __DIR__.'/partials/health-form-modal.php';
}
?>
<?php if($esUsuarioPerfil || $esAdministradorPerfil || $esJefaturaPerfil): ?>
<script>
window.SCT_SELF_ORG_I18N = <?= json_encode([
    'title'=>t('self_org_title'),
    'intro'=>t('self_org_intro'),
    'location'=>t('self_org_location'),
    'matrix'=>t('self_org_matrix'),
    'center'=>t('self_org_center'),
    'gbu'=>t('admin_users_filter_gbu'),
    'site'=>t('admin_users_filter_site'),
    'project'=>t('admin_users_filter_project'),
    'optional'=>t('self_org_optional'),
    'required'=>t('self_org_scope_required'),
    'saveError'=>t('self_org_save_error'),
], JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES) ?>;
</script>
<script src="js/self-org-profile.js?v=<?= htmlspecialchars(SCT_ASSET_VERSION,ENT_QUOTES,'UTF-8') ?>"></script>
<?php endif; ?>
<?php if($usaPortalAdministrador): ?>
<script src="js/admin-portal.js?v=<?= htmlspecialchars(SCT_ASSET_VERSION,ENT_QUOTES,'UTF-8') ?>"></script>
<?php endif; ?>

<?php if($puedeGestionarUsuariosEmpresas): ?>
<script src="js/admin-user-management.js?v=<?= htmlspecialchars(SCT_ASSET_VERSION,ENT_QUOTES,'UTF-8') ?>"></script>
<script>
window.SCT_COMPANY_I18N = <?= json_encode([
    'gbus'=>t('company_manage_total_gbus'),
    'sites'=>t('company_manage_total_sites'),
    'projects'=>t('company_manage_total_projects'),
    'deleteSiteConfirm'=>t('company_manage_delete_site_confirm'),
    'deleteProjectConfirm'=>t('company_manage_delete_project_confirm'),
], JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES) ?>;
</script>
<script src="js/admin-company-management.js?v=<?= htmlspecialchars(SCT_ASSET_VERSION,ENT_QUOTES,'UTF-8') ?>"></script>
<?php endif; ?>
</body></html>
