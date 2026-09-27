<?php
/**
 * ==========================================================
 * NAVBAR INTERNA SCT — P3
 * ==========================================================
 * Componente compartido para todas las pantallas autenticadas.
 *
 * Variables opcionales antes del include:
 * - $sctNavbarBasePath   Ruta desde la pantalla actual a la raíz de vs3.
 *                        Ej.: './' en bienvenida.php, '../../' en api/*.
 * - $sctNavbarHomeHref   Destino al hacer clic en la marca.
 * - $sctNavbarBackHref   Destino del botón volver. Null = no mostrar.
 * - $sctNavbarBackLabel  Etiqueta del botón volver.
 * - $sctNavbarBackIcon   Bootstrap Icon del botón volver.
 *
 * La identidad de usuario se resuelve desde la sesión/autorización ya
 * cargada por cada pantalla. No reemplaza ni implementa control de acceso.
 * ==========================================================
 */
require_once __DIR__ . '/../lib/sct-notifications.php';

$sctNavbarBasePath = isset($sctNavbarBasePath) ? (string) $sctNavbarBasePath : './';
$sctNavbarHomeHref = isset($sctNavbarHomeHref) ? (string) $sctNavbarHomeHref : $sctNavbarBasePath . 'bienvenida.php';
$sctNavbarBackHref = isset($sctNavbarBackHref) && $sctNavbarBackHref !== '' ? (string) $sctNavbarBackHref : null;
$sctNavbarBackLabel = isset($sctNavbarBackLabel) ? (string) $sctNavbarBackLabel : (function_exists('t') ? t('mgmt_back') : 'Volver');
$sctNavbarBackIcon = isset($sctNavbarBackIcon) ? (string) $sctNavbarBackIcon : 'bi-arrow-left';
$sctNavbarLogoutLabel = function_exists('t') ? t('common_logout') : 'Cerrar sesión';
$sctNavbarLanguageLabel = function_exists('t') ? t('common_language') : 'Idioma';
$sctNavbarConfirmTitle = function_exists('t') ? t('common_confirm_title') : 'Confirmar acción';
$sctNavbarConfirmAction = function_exists('t') ? t('common_confirm_action') : 'Confirmar';
$sctNavbarCancelLabel = function_exists('t') ? t('common_cancel') : 'Cancelar';
$sctNavbarCloseLabel = function_exists('t') ? t('common_close') : 'Cerrar';
$sctNavbarProfileLabel = function_exists('t') ? t('worker_management_profile_title') : 'Mis datos';
$sctNavbarAssetVersion = (isset($ASSET_VERSION) ? (string) $ASSET_VERSION : '20260922') . '-p56';

$sctNavbarEmail = (string) ($_SESSION['user_email'] ?? '');
$sctNavbarProfile = null;
$sctNavbarRoles = [];

if (isset($pdo) && $pdo instanceof PDO) {
    if (function_exists('currentUserProfile')) {
        $sctNavbarProfile = currentUserProfile($pdo);
    }
    if (function_exists('currentUserRoles')) {
        $sctNavbarRoles = currentUserRoles($pdo);
    }
}

$sctNavbarName = trim((string) ($sctNavbarProfile['name'] ?? '') . ' ' . (string) ($sctNavbarProfile['lastname'] ?? ''));
if ($sctNavbarName === '') {
    $sctNavbarEmailLocal = explode('@', $sctNavbarEmail !== '' ? $sctNavbarEmail : 'usuario')[0];
    $sctNavbarName = ucfirst($sctNavbarEmailLocal);
}
if (function_exists('capitalizarNombre')) {
    $sctNavbarName = capitalizarNombre($sctNavbarName);
}

$sctNavbarRoleName = function_exists('primaryRoleName') ? primaryRoleName($sctNavbarRoles) : null;
$sctNavbarRoleLabel = '';
if ($sctNavbarRoleName) {
    $sctNavbarRoleKey = 'role_' . $sctNavbarRoleName;
    $sctNavbarTranslatedRole = function_exists('t') ? t($sctNavbarRoleKey) : $sctNavbarRoleKey;
    if ($sctNavbarTranslatedRole !== $sctNavbarRoleKey) {
        $sctNavbarRoleLabel = $sctNavbarTranslatedRole;
    } elseif (function_exists('roleDisplayLabel')) {
        $sctNavbarRoleLabel = roleDisplayLabel($sctNavbarRoleName);
    }
}

$sctNavbarInitials = '';
if (!empty($sctNavbarProfile['name']) && function_exists('sctTextSubstr')) {
    $sctNavbarInitials .= sctTextSubstr((string) $sctNavbarProfile['name'], 0, 1);
}
if (!empty($sctNavbarProfile['lastname']) && function_exists('sctTextSubstr')) {
    $sctNavbarInitials .= sctTextSubstr((string) $sctNavbarProfile['lastname'], 0, 1);
}
if ($sctNavbarInitials === '') {
    $fallback = $sctNavbarEmail !== '' ? $sctNavbarEmail : 'US';
    $sctNavbarInitials = function_exists('sctTextSubstr') ? sctTextSubstr($fallback, 0, 2) : substr($fallback, 0, 2);
}
$sctNavbarInitials = function_exists('sctTextUpper') ? sctTextUpper($sctNavbarInitials) : strtoupper($sctNavbarInitials);

$sctNavbarPhotoPath = trim((string) ($sctNavbarProfile['profile_photo_path'] ?? ''));
$sctNavbarPhotoSrc = '';
if ($sctNavbarPhotoPath !== '') {
    if (preg_match('~^(?:https?:)?//|^data:|^/~i', $sctNavbarPhotoPath)) {
        $sctNavbarPhotoSrc = $sctNavbarPhotoPath;
    } else {
        $sctNavbarPhotoSrc = $sctNavbarBasePath . ltrim($sctNavbarPhotoPath, './');
    }
}

$sctNavbarCurrentLang = idiomaActual();
$sctNavbarCurrentLangShort = function_exists('sctTextUpper') ? sctTextUpper((string) $sctNavbarCurrentLang) : strtoupper((string) $sctNavbarCurrentLang);

$sctNavbarNameEsc = htmlspecialchars($sctNavbarName, ENT_QUOTES, 'UTF-8');
$sctNavbarRoleEsc = htmlspecialchars($sctNavbarRoleLabel, ENT_QUOTES, 'UTF-8');
$sctNavbarEmailEsc = htmlspecialchars($sctNavbarEmail, ENT_QUOTES, 'UTF-8');
$sctNavbarCurrentLangEsc = htmlspecialchars($sctNavbarCurrentLangShort, ENT_QUOTES, 'UTF-8');


$sctNavbarLanguageFlagFiles = [
    'es' => 'images/flags/lang-es.svg',
    'en' => 'images/flags/lang-en.svg',
    'pt' => 'images/flags/lang-pt.svg',
    'fr' => 'images/flags/lang-fr.svg',
    'zh' => 'images/flags/lang-zh.svg',
];
$sctNavbarCurrentFlagPath = $sctNavbarLanguageFlagFiles[$sctNavbarCurrentLang] ?? $sctNavbarLanguageFlagFiles['es'];
$sctNavbarFlagVersion = rawurlencode($sctNavbarAssetVersion . '-p28');
$sctNavbarCurrentFlagSrc = $sctNavbarBasePath . $sctNavbarCurrentFlagPath . '?v=' . $sctNavbarFlagVersion;

$sctNavbarShortName = $sctNavbarName;
$spacePos = strpos($sctNavbarName, ' ');
if ($spacePos !== false) {
    $sctNavbarShortName = substr($sctNavbarName, 0, $spacePos);
}
if (function_exists('capitalizarNombre')) {
    $sctNavbarShortName = capitalizarNombre($sctNavbarShortName);
}
$sctNavbarShortNameEsc = htmlspecialchars($sctNavbarShortName, ENT_QUOTES, 'UTF-8');

if (!function_exists('sctNavbarBuildLanguageHref')) {
    function sctNavbarBuildLanguageHref($code)
    {
        $uri = (string) ($_SERVER['REQUEST_URI'] ?? '');
        $path = '';
        $queryParams = [];
        $fragment = '';

        if ($uri !== '') {
            $parts = parse_url($uri);
            $path = isset($parts['path']) ? (string) $parts['path'] : '';
            if (!empty($parts['query'])) {
                parse_str((string) $parts['query'], $queryParams);
            }
            if (!empty($parts['fragment'])) {
                $fragment = (string) $parts['fragment'];
            }
        }

        $queryParams['lang'] = (string) $code;
        $query = http_build_query($queryParams);
        $href = ($path !== '' ? $path : '?') . ($query !== '' ? ('?' . $query) : '');
        if ($fragment !== '') {
            $href .= '#' . $fragment;
        }
        return $href;
    }
}


$sctNavbarMobileLabelsByLang = [
    'es' => ['home' => 'Inicio', 'dashboard' => 'Panel', 'management' => 'Gestiones', 'settings' => 'Configuración'],
    'en' => ['home' => 'Home', 'dashboard' => 'Dashboard', 'management' => 'Manage', 'settings' => 'Settings'],
    'pt' => ['home' => 'Início', 'dashboard' => 'Painel', 'management' => 'Gestões', 'settings' => 'Configurações'],
    'fr' => ['home' => 'Accueil', 'dashboard' => 'Tableau', 'management' => 'Gestion', 'settings' => 'Réglages'],
    'zh' => ['home' => '首页', 'dashboard' => '面板', 'management' => '管理', 'settings' => '设置'],
];
$sctNavbarLangKey = strtolower((string) idiomaActual());
if (!isset($sctNavbarMobileLabelsByLang[$sctNavbarLangKey])) {
    $sctNavbarLangKey = 'es';
}
$sctNavbarMobileLabels = $sctNavbarMobileLabelsByLang[$sctNavbarLangKey];
$sctNavbarSettingsLabel = $sctNavbarMobileLabels['settings'];
$sctNavbarNotificationLabelsByLang = [
    'es' => [
        'title' => 'Notificaciones',
        'mobile' => 'Avisos',
        'empty' => 'No tienes recordatorios pendientes.',
        'courses' => 'Cursos pendientes',
        'evaluations' => 'Evaluaciones pendientes',
        'audits' => 'Auditorías pendientes',
        'due' => 'Próximo vencimiento',
        'view' => 'Ver mis actividades',
    ],
    'en' => [
        'title' => 'Notifications',
        'mobile' => 'Alerts',
        'empty' => 'You have no pending reminders.',
        'courses' => 'Pending courses',
        'evaluations' => 'Pending evaluations',
        'audits' => 'Pending audits',
        'due' => 'Next due date',
        'view' => 'View my activities',
    ],
    'pt' => [
        'title' => 'Notificações',
        'mobile' => 'Avisos',
        'empty' => 'Você não tem lembretes pendentes.',
        'courses' => 'Cursos pendentes',
        'evaluations' => 'Avaliações pendentes',
        'audits' => 'Auditorias pendentes',
        'due' => 'Próximo vencimento',
        'view' => 'Ver minhas atividades',
    ],
    'fr' => [
        'title' => 'Notifications',
        'mobile' => 'Alertes',
        'empty' => 'Vous n’avez aucun rappel en attente.',
        'courses' => 'Cours en attente',
        'evaluations' => 'Évaluations en attente',
        'audits' => 'Audits en attente',
        'due' => 'Prochaine échéance',
        'view' => 'Voir mes activités',
    ],
    'zh' => [
        'title' => '通知',
        'mobile' => '提醒',
        'empty' => '当前没有待处理提醒。',
        'courses' => '待完成课程',
        'evaluations' => '待完成评估',
        'audits' => '待处理审计',
        'due' => '最近截止日期',
        'view' => '查看我的活动',
    ],
];
$sctNavbarNotificationLabels = $sctNavbarNotificationLabelsByLang[$sctNavbarLangKey] ?? $sctNavbarNotificationLabelsByLang['es'];
$sctNavbarDesktopLabelsByLang = [
    'es' => [
        'search_placeholder' => 'Buscar módulos y acciones',
        'search_label' => 'Buscar en esta pantalla',
        'search_empty' => 'No se encontraron coincidencias.',
        'appearance' => 'Cambiar tema',
        'appearance_light' => 'Cambiar a modo claro',
        'appearance_dark' => 'Cambiar a modo oscuro',
        'theme_current_light' => 'Modo claro',
        'theme_current_dark' => 'Modo oscuro',
        'profile' => 'Perfil de usuario',
    ],
    'en' => [
        'search_placeholder' => 'Search modules and actions',
        'search_label' => 'Search this page',
        'search_empty' => 'No matches found.',
        'appearance' => 'Change theme',
        'appearance_light' => 'Switch to light mode',
        'appearance_dark' => 'Switch to dark mode',
        'theme_current_light' => 'Light mode',
        'theme_current_dark' => 'Dark mode',
        'profile' => 'User profile',
    ],
    'pt' => [
        'search_placeholder' => 'Buscar módulos e ações',
        'search_label' => 'Buscar nesta tela',
        'search_empty' => 'Nenhum resultado encontrado.',
        'appearance' => 'Alterar tema',
        'appearance_light' => 'Mudar para modo claro',
        'appearance_dark' => 'Mudar para modo escuro',
        'theme_current_light' => 'Modo claro',
        'theme_current_dark' => 'Modo escuro',
        'profile' => 'Perfil do usuário',
    ],
    'fr' => [
        'search_placeholder' => 'Rechercher modules et actions',
        'search_label' => 'Rechercher dans cette page',
        'search_empty' => 'Aucun résultat trouvé.',
        'appearance' => 'Changer le thème',
        'appearance_light' => 'Passer en mode clair',
        'appearance_dark' => 'Passer en mode sombre',
        'theme_current_light' => 'Mode clair',
        'theme_current_dark' => 'Mode sombre',
        'profile' => 'Profil utilisateur',
    ],
    'zh' => [
        'search_placeholder' => '搜索模块和操作',
        'search_label' => '搜索当前页面',
        'search_empty' => '未找到匹配项。',
        'appearance' => '切换主题',
        'appearance_light' => '切换到浅色模式',
        'appearance_dark' => '切换到深色模式',
        'theme_current_light' => '浅色模式',
        'theme_current_dark' => '深色模式',
        'profile' => '用户资料',
    ],
];
$sctNavbarDesktopLabels = $sctNavbarDesktopLabelsByLang[$sctNavbarLangKey] ?? $sctNavbarDesktopLabelsByLang['es'];
$sctNavbarNotificationItems = [];
$sctNavbarNotificationCount = 0;
$sctNavbarNotificationTargetHref = $sctNavbarHomeHref . '#welcome-activity-title';

if (isset($pdo) && $pdo instanceof PDO && $sctNavbarEmail !== '') {
    $sctNavbarNotificationItems = sctBuildPendingNotifications(
        $pdo,
        $sctNavbarEmail,
        $sctNavbarRoles,
        is_array($sctNavbarProfile) ? $sctNavbarProfile : [],
        $sctNavbarBasePath
    );
    foreach ($sctNavbarNotificationItems as $sctNavbarNotificationItem) {
        $sctNavbarNotificationCount += max(1, (int) ($sctNavbarNotificationItem['count'] ?? 1));
    }
}
$sctNavbarNotificationBadge = $sctNavbarNotificationCount > 99 ? '99+' : (string) $sctNavbarNotificationCount;
$sctNavbarSidebarToggleLabels = [
    'es' => ['collapse' => 'Contraer navegación', 'expand' => 'Expandir navegación'],
    'en' => ['collapse' => 'Collapse navigation', 'expand' => 'Expand navigation'],
    'pt' => ['collapse' => 'Recolher navegação', 'expand' => 'Expandir navegação'],
    'fr' => ['collapse' => 'Réduire la navigation', 'expand' => 'Développer la navigation'],
    'zh' => ['collapse' => '收起导航', 'expand' => '展开导航'],
];
$sctNavbarSidebarToggleLabel = $sctNavbarSidebarToggleLabels[$sctNavbarLangKey] ?? $sctNavbarSidebarToggleLabels['es'];
$sctNavbarDashboardHref = $sctNavbarBasePath . 'api/dashboard/dashboard.php';
$sctNavbarManagementHref = $sctNavbarBasePath . 'api/usuarios/gestiones.php';
$sctNavbarProfileHref = $sctNavbarBasePath . 'api/usuarios/gestion-usuarios.php?self=1';

if ($sctNavbarRoleName === 'trabajador') {
    $sctNavbarNotificationTargetHref = $sctNavbarBasePath . 'api/usuarios/mis-actividades.php';
}

if ($sctNavbarRoleName && $sctNavbarRoleName !== 'trabajador') {
    $sctNavbarNotificationTargetHref = $sctNavbarManagementHref;
    $sctNavbarNotificationLabels['view'] = $sctNavbarMobileLabels['management'];
}

$sctNavbarRequestPath = (string) parse_url((string) ($_SERVER['REQUEST_URI'] ?? ''), PHP_URL_PATH);
$sctNavbarIsHome = substr($sctNavbarRequestPath, -strlen('/bienvenida.php')) === '/bienvenida.php' || basename($sctNavbarRequestPath) === 'bienvenida.php';
$sctNavbarIsDashboard = strpos($sctNavbarRequestPath, '/api/dashboard/') !== false;
$sctNavbarIsManagement = substr($sctNavbarRequestPath, -strlen('/api/usuarios/gestiones.php')) === '/api/usuarios/gestiones.php';
?>
<script>
(function () {
    try {
        var savedTheme = window.localStorage.getItem('sctColorTheme');
        if (savedTheme === 'dark') document.documentElement.classList.add('sct-theme-dark');
    } catch (_) {}
})();
</script>
<nav class="sct-app-navbar sct-app-sidebar sct-partial-app-nav" aria-label="Safety Control Tower"
     data-sct-role="<?= htmlspecialchars((string) ($sctNavbarRoleName ?: 'trabajador'), ENT_QUOTES, 'UTF-8') ?>"
     data-notification-endpoint="<?= htmlspecialchars($sctNavbarBasePath . 'api/usuarios/notificaciones-pendientes.php', ENT_QUOTES, 'UTF-8') ?>"
     data-notification-empty="<?= htmlspecialchars($sctNavbarNotificationLabels['empty'], ENT_QUOTES, 'UTF-8') ?>"
     data-confirm-title="<?= htmlspecialchars($sctNavbarConfirmTitle, ENT_QUOTES, 'UTF-8') ?>"
     data-confirm-action="<?= htmlspecialchars($sctNavbarConfirmAction, ENT_QUOTES, 'UTF-8') ?>"
     data-cancel-label="<?= htmlspecialchars($sctNavbarCancelLabel, ENT_QUOTES, 'UTF-8') ?>"
     data-close-label="<?= htmlspecialchars($sctNavbarCloseLabel, ENT_QUOTES, 'UTF-8') ?>">
    <div class="sct-app-sidebar__inner">
        <a class="sct-app-sidebar__brand" href="<?= htmlspecialchars($sctNavbarHomeHref, ENT_QUOTES, 'UTF-8') ?>" aria-label="Safety Control Tower">
            <span class="sct-app-sidebar__brand-symbol" aria-hidden="true">
                <img src="<?= htmlspecialchars($sctNavbarBasePath . 'images/logos/Logo-SCT-white.png', ENT_QUOTES, 'UTF-8') ?>" alt="">
            </span>
            <span class="sct-app-sidebar__brand-copy">
                <strong>Safety Control</strong>
                <small>TOWER</small>
            </span>
        </a>

        <div class="sct-app-sidebar__identity" title="<?= $sctNavbarEmailEsc ?>">
            <span class="sct-app-sidebar__avatar" aria-hidden="true">
                <?php if ($sctNavbarPhotoSrc !== ''): ?>
                    <img src="<?= htmlspecialchars($sctNavbarPhotoSrc, ENT_QUOTES, 'UTF-8') ?>" alt="">
                <?php else: ?>
                    <?= htmlspecialchars($sctNavbarInitials, ENT_QUOTES, 'UTF-8') ?>
                <?php endif; ?>
            </span>
            <span class="sct-app-sidebar__identity-copy">
                <strong title="<?= $sctNavbarNameEsc ?>"><?= $sctNavbarNameEsc ?></strong>
                <?php if ($sctNavbarRoleLabel !== ''): ?>
                    <small title="<?= $sctNavbarRoleEsc ?>"><?= $sctNavbarRoleEsc ?></small>
                <?php endif; ?>
            </span>
        </div>

        <div class="sct-app-sidebar__nav" aria-label="<?= htmlspecialchars($sctNavbarMobileLabels['home'], ENT_QUOTES, 'UTF-8') ?>">
            <details class="sct-app-notifications sct-app-notifications--sidebar">
                <summary class="sct-app-sidebar__nav-item sct-app-sidebar__notification-item"
                         aria-label="<?= htmlspecialchars($sctNavbarNotificationLabels['title'], ENT_QUOTES, 'UTF-8') ?>"
                         title="<?= htmlspecialchars($sctNavbarNotificationLabels['title'], ENT_QUOTES, 'UTF-8') ?>">
                    <span class="sct-app-sidebar__nav-icon sct-app-notifications__bell" aria-hidden="true">
                        <i class="bi bi-bell"></i>
                        <span class="sct-app-notifications__badge" data-sct-notification-badge<?= $sctNavbarNotificationCount > 0 ? '' : ' hidden' ?>><?= htmlspecialchars($sctNavbarNotificationBadge, ENT_QUOTES, 'UTF-8') ?></span>
                    </span>
                    <span><?= htmlspecialchars($sctNavbarNotificationLabels['title'], ENT_QUOTES, 'UTF-8') ?></span>
                </summary>
                <div class="sct-notification-panel sct-notification-panel--sidebar">
                    <div class="sct-notification-panel__head">
                        <strong><?= htmlspecialchars($sctNavbarNotificationLabels['title'], ENT_QUOTES, 'UTF-8') ?></strong>
                        <span data-sct-notification-head-count<?= $sctNavbarNotificationCount > 0 ? '' : ' hidden' ?>><?= htmlspecialchars($sctNavbarNotificationBadge, ENT_QUOTES, 'UTF-8') ?></span>
                    </div>
                    <div class="sct-notification-panel__list" data-sct-notification-list<?= empty($sctNavbarNotificationItems) ? ' hidden' : '' ?>>
                        <?php foreach ($sctNavbarNotificationItems as $notification): ?>
                            <a href="<?= htmlspecialchars((string) ($notification['href'] ?? $sctNavbarNotificationTargetHref), ENT_QUOTES, 'UTF-8') ?>" class="sct-notification-panel__item">
                                <span class="sct-notification-panel__icon" aria-hidden="true"><i class="bi <?= htmlspecialchars($notification['icon'], ENT_QUOTES, 'UTF-8') ?>"></i></span>
                                <span class="sct-notification-panel__copy">
                                    <strong><?php if (!empty($notification['show_count'])): ?><?= (int) $notification['count'] ?> · <?php endif; ?><?= htmlspecialchars((string) $notification['title'], ENT_QUOTES, 'UTF-8') ?></strong>
                                    <?php if ($notification['meta'] !== ''): ?><small><?= htmlspecialchars($notification['meta'], ENT_QUOTES, 'UTF-8') ?></small><?php endif; ?>
                                </span>
                                <i class="bi bi-chevron-right" aria-hidden="true"></i>
                            </a>
                        <?php endforeach; ?>
                    </div>
                    <p class="sct-notification-panel__empty" data-sct-notification-empty<?= !empty($sctNavbarNotificationItems) ? ' hidden' : '' ?>><?= htmlspecialchars($sctNavbarNotificationLabels['empty'], ENT_QUOTES, 'UTF-8') ?></p>
                    <a href="<?= htmlspecialchars($sctNavbarNotificationTargetHref, ENT_QUOTES, 'UTF-8') ?>" class="sct-notification-panel__footer">
                        <?= htmlspecialchars($sctNavbarNotificationLabels['view'], ENT_QUOTES, 'UTF-8') ?>
                        <i class="bi bi-arrow-right" aria-hidden="true"></i>
                    </a>
                </div>
            </details>

            <a href="<?= htmlspecialchars($sctNavbarHomeHref, ENT_QUOTES, 'UTF-8') ?>"
               class="sct-app-sidebar__nav-item<?= $sctNavbarIsHome ? ' active' : '' ?>"
               aria-label="<?= htmlspecialchars($sctNavbarMobileLabels['home'], ENT_QUOTES, 'UTF-8') ?>"
               title="<?= htmlspecialchars($sctNavbarMobileLabels['home'], ENT_QUOTES, 'UTF-8') ?>"
               <?= $sctNavbarIsHome ? 'aria-current="page"' : '' ?>>
                <span class="sct-app-sidebar__nav-icon" aria-hidden="true"><i class="bi bi-house-door"></i></span>
                <span><?= htmlspecialchars($sctNavbarMobileLabels['home'], ENT_QUOTES, 'UTF-8') ?></span>
            </a>

            <a href="<?= htmlspecialchars($sctNavbarDashboardHref, ENT_QUOTES, 'UTF-8') ?>"
               class="sct-app-sidebar__nav-item<?= $sctNavbarIsDashboard ? ' active' : '' ?>"
               aria-label="<?= htmlspecialchars($sctNavbarMobileLabels['dashboard'], ENT_QUOTES, 'UTF-8') ?>"
               title="<?= htmlspecialchars($sctNavbarMobileLabels['dashboard'], ENT_QUOTES, 'UTF-8') ?>"
               <?= $sctNavbarIsDashboard ? 'aria-current="page"' : '' ?>>
                <span class="sct-app-sidebar__nav-icon" aria-hidden="true"><i class="bi bi-speedometer2"></i></span>
                <span><?= htmlspecialchars($sctNavbarMobileLabels['dashboard'], ENT_QUOTES, 'UTF-8') ?></span>
            </a>

            <a href="<?= htmlspecialchars($sctNavbarManagementHref, ENT_QUOTES, 'UTF-8') ?>"
               class="sct-app-sidebar__nav-item<?= $sctNavbarIsManagement ? ' active' : '' ?>"
               aria-label="<?= htmlspecialchars($sctNavbarMobileLabels['management'], ENT_QUOTES, 'UTF-8') ?>"
               title="<?= htmlspecialchars($sctNavbarMobileLabels['management'], ENT_QUOTES, 'UTF-8') ?>"
               <?= $sctNavbarIsManagement ? 'aria-current="page"' : '' ?>>
                <span class="sct-app-sidebar__nav-icon" aria-hidden="true"><i class="bi bi-grid-1x2"></i></span>
                <span><?= htmlspecialchars($sctNavbarMobileLabels['management'], ENT_QUOTES, 'UTF-8') ?></span>
            </a>

        </div>

        <div class="sct-app-sidebar__bottom">
            <?php if ($sctNavbarBackHref !== null): ?>
                <div class="sct-app-sidebar__back-zone">
                    <a href="<?= htmlspecialchars($sctNavbarBackHref, ENT_QUOTES, 'UTF-8') ?>"
                       class="sct-app-sidebar__bottom-item sct-app-sidebar__back-item"
                       aria-label="<?= htmlspecialchars($sctNavbarBackLabel, ENT_QUOTES, 'UTF-8') ?>"
                       title="<?= htmlspecialchars($sctNavbarBackLabel, ENT_QUOTES, 'UTF-8') ?>">
                        <span class="sct-app-sidebar__bottom-icon" aria-hidden="true"><i class="bi <?= htmlspecialchars($sctNavbarBackIcon, ENT_QUOTES, 'UTF-8') ?>"></i></span>
                        <span><?= htmlspecialchars($sctNavbarBackLabel, ENT_QUOTES, 'UTF-8') ?></span>
                    </a>
                </div>
            <?php endif; ?>

            <details class="sct-language-picker sct-language-picker--sidebar">
                <summary class="sct-app-sidebar__bottom-item"
                         aria-label="<?= htmlspecialchars($sctNavbarLanguageLabel . ': ' . $sctNavbarCurrentLangShort, ENT_QUOTES, 'UTF-8') ?>"
                         title="<?= htmlspecialchars($sctNavbarLanguageLabel, ENT_QUOTES, 'UTF-8') ?>">
                    <img class="sct-language-flag sct-language-flag--sidebar"
                         src="<?= htmlspecialchars($sctNavbarCurrentFlagSrc, ENT_QUOTES, 'UTF-8') ?>"
                         alt="<?= $sctNavbarCurrentLangEsc ?>">
                    <span><?= htmlspecialchars($sctNavbarLanguageLabel, ENT_QUOTES, 'UTF-8') ?></span>
                    <i class="bi bi-chevron-right sct-app-sidebar__bottom-chevron" aria-hidden="true"></i>
                </summary>
                <div class="sct-language-picker__menu sct-language-picker__menu--sidebar" role="menu">
                    <?php foreach (idiomasDisponiblesConNombre() as $code => $sctNavbarLanguageName): ?>
                        <?php
                            $langHref = sctNavbarBuildLanguageHref($code);
                            $flagFile = $sctNavbarLanguageFlagFiles[$code] ?? $sctNavbarLanguageFlagFiles['es'];
                            $flagSrc = $sctNavbarBasePath . $flagFile . '?v=' . $sctNavbarFlagVersion;
                        ?>
                        <a class="sct-language-picker__option<?= $code === idiomaActual() ? ' active' : '' ?>"
                           href="<?= htmlspecialchars($langHref, ENT_QUOTES, 'UTF-8') ?>"
                           role="menuitem">
                            <img class="sct-language-flag"
                                 src="<?= htmlspecialchars($flagSrc, ENT_QUOTES, 'UTF-8') ?>"
                                 alt="<?= htmlspecialchars(strtoupper((string) $code), ENT_QUOTES, 'UTF-8') ?>">
                            <span><?= htmlspecialchars($sctNavbarLanguageName, ENT_QUOTES, 'UTF-8') ?></span>
                            <?php if ($code === idiomaActual()): ?>
                                <i class="bi bi-check2" aria-hidden="true"></i>
                            <?php endif; ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            </details>

            <a href="<?= htmlspecialchars($sctNavbarBasePath . 'logout.php', ENT_QUOTES, 'UTF-8') ?>"
               class="sct-app-sidebar__bottom-item sct-app-sidebar__logout"
               aria-label="<?= htmlspecialchars($sctNavbarLogoutLabel, ENT_QUOTES, 'UTF-8') ?>"
               title="<?= htmlspecialchars($sctNavbarLogoutLabel, ENT_QUOTES, 'UTF-8') ?>">
                <span class="sct-app-sidebar__bottom-icon" aria-hidden="true"><i class="bi bi-box-arrow-right"></i></span>
                <span><?= htmlspecialchars($sctNavbarLogoutLabel, ENT_QUOTES, 'UTF-8') ?></span>
            </a>
        </div>
    </div>

    <button class="sct-app-sidebar__toggle"
            type="button"
            aria-expanded="true"
            aria-label="<?= htmlspecialchars($sctNavbarSidebarToggleLabel['collapse'], ENT_QUOTES, 'UTF-8') ?>"
            title="<?= htmlspecialchars($sctNavbarSidebarToggleLabel['collapse'], ENT_QUOTES, 'UTF-8') ?>"
            data-collapse-label="<?= htmlspecialchars($sctNavbarSidebarToggleLabel['collapse'], ENT_QUOTES, 'UTF-8') ?>"
            data-expand-label="<?= htmlspecialchars($sctNavbarSidebarToggleLabel['expand'], ENT_QUOTES, 'UTF-8') ?>">
        <i class="bi bi-chevron-left" aria-hidden="true"></i>
    </button>
</nav>

<div class="sct-app-desktop-topbar sct-partial-app-nav" aria-label="Safety Control Tower" data-sct-desktop-topbar>
    <div class="sct-app-desktop-topbar__shell">
        <div class="sct-app-desktop-topbar__identity" title="<?= $sctNavbarEmailEsc ?>">
            <strong title="<?= $sctNavbarNameEsc ?>"><?= $sctNavbarNameEsc ?></strong>
            <?php if ($sctNavbarRoleLabel !== ''): ?>
                <small title="<?= $sctNavbarRoleEsc ?>"><?= $sctNavbarRoleEsc ?></small>
            <?php endif; ?>
        </div>

        <div class="sct-app-desktop-search" data-sct-desktop-search>
            <label class="visually-hidden" for="sctDesktopGlobalSearch"><?= htmlspecialchars($sctNavbarDesktopLabels['search_label'], ENT_QUOTES, 'UTF-8') ?></label>
            <span class="sct-app-desktop-search__icon" aria-hidden="true"><i class="bi bi-search"></i></span>
            <input id="sctDesktopGlobalSearch"
                   type="search"
                   autocomplete="off"
                   spellcheck="false"
                   placeholder="<?= htmlspecialchars($sctNavbarDesktopLabels['search_placeholder'], ENT_QUOTES, 'UTF-8') ?>"
                   aria-label="<?= htmlspecialchars($sctNavbarDesktopLabels['search_label'], ENT_QUOTES, 'UTF-8') ?>"
                   aria-expanded="false"
                   aria-controls="sctDesktopSearchResults"
                   data-sct-desktop-search-input>
            <button type="button" class="sct-app-desktop-search__clear" data-sct-desktop-search-clear aria-label="<?= htmlspecialchars($sctNavbarCloseLabel, ENT_QUOTES, 'UTF-8') ?>" hidden><i class="bi bi-x-lg" aria-hidden="true"></i></button>
            <div id="sctDesktopSearchResults" class="sct-app-desktop-search__results" data-sct-desktop-search-results hidden>
                <div class="sct-app-desktop-search__empty" data-sct-desktop-search-empty hidden><?= htmlspecialchars($sctNavbarDesktopLabels['search_empty'], ENT_QUOTES, 'UTF-8') ?></div>
            </div>
        </div>

        <div class="sct-app-desktop-topbar__actions">
            <button type="button"
                    class="sct-app-desktop-topbar__action"
                    data-sct-desktop-appearance
                    aria-pressed="false"
                    aria-label="<?= htmlspecialchars($sctNavbarDesktopLabels['appearance'], ENT_QUOTES, 'UTF-8') ?>"
                    title="<?= htmlspecialchars($sctNavbarDesktopLabels['appearance'], ENT_QUOTES, 'UTF-8') ?>"
                    data-light-label="<?= htmlspecialchars($sctNavbarDesktopLabels['appearance_light'], ENT_QUOTES, 'UTF-8') ?>"
                    data-dark-label="<?= htmlspecialchars($sctNavbarDesktopLabels['appearance_dark'], ENT_QUOTES, 'UTF-8') ?>">
                <i class="bi bi-moon-stars" aria-hidden="true"></i>
            </button>

            <details class="sct-app-notifications sct-app-notifications--desktop-topbar">
                <summary class="sct-app-desktop-topbar__action sct-app-desktop-topbar__notification"
                         aria-label="<?= htmlspecialchars($sctNavbarNotificationLabels['title'], ENT_QUOTES, 'UTF-8') ?>"
                         title="<?= htmlspecialchars($sctNavbarNotificationLabels['title'], ENT_QUOTES, 'UTF-8') ?>">
                    <span class="sct-app-notifications__bell" aria-hidden="true">
                        <i class="bi bi-bell"></i>
                        <span class="sct-app-notifications__badge" data-sct-notification-badge<?= $sctNavbarNotificationCount > 0 ? '' : ' hidden' ?>><?= htmlspecialchars($sctNavbarNotificationBadge, ENT_QUOTES, 'UTF-8') ?></span>
                    </span>
                </summary>
                <div class="sct-notification-panel sct-notification-panel--desktop-topbar">
                    <div class="sct-notification-panel__head">
                        <strong><?= htmlspecialchars($sctNavbarNotificationLabels['title'], ENT_QUOTES, 'UTF-8') ?></strong>
                        <span data-sct-notification-head-count<?= $sctNavbarNotificationCount > 0 ? '' : ' hidden' ?>><?= htmlspecialchars($sctNavbarNotificationBadge, ENT_QUOTES, 'UTF-8') ?></span>
                    </div>
                    <div class="sct-notification-panel__list" data-sct-notification-list<?= empty($sctNavbarNotificationItems) ? ' hidden' : '' ?>>
                        <?php foreach ($sctNavbarNotificationItems as $notification): ?>
                            <a href="<?= htmlspecialchars((string) ($notification['href'] ?? $sctNavbarNotificationTargetHref), ENT_QUOTES, 'UTF-8') ?>" class="sct-notification-panel__item">
                                <span class="sct-notification-panel__icon" aria-hidden="true"><i class="bi <?= htmlspecialchars($notification['icon'], ENT_QUOTES, 'UTF-8') ?>"></i></span>
                                <span class="sct-notification-panel__copy">
                                    <strong><?php if (!empty($notification['show_count'])): ?><?= (int) $notification['count'] ?> · <?php endif; ?><?= htmlspecialchars((string) $notification['title'], ENT_QUOTES, 'UTF-8') ?></strong>
                                    <?php if ($notification['meta'] !== ''): ?><small><?= htmlspecialchars($notification['meta'], ENT_QUOTES, 'UTF-8') ?></small><?php endif; ?>
                                </span>
                                <i class="bi bi-chevron-right" aria-hidden="true"></i>
                            </a>
                        <?php endforeach; ?>
                    </div>
                    <p class="sct-notification-panel__empty" data-sct-notification-empty<?= !empty($sctNavbarNotificationItems) ? ' hidden' : '' ?>><?= htmlspecialchars($sctNavbarNotificationLabels['empty'], ENT_QUOTES, 'UTF-8') ?></p>
                    <a href="<?= htmlspecialchars($sctNavbarNotificationTargetHref, ENT_QUOTES, 'UTF-8') ?>" class="sct-notification-panel__footer">
                        <?= htmlspecialchars($sctNavbarNotificationLabels['view'], ENT_QUOTES, 'UTF-8') ?>
                        <i class="bi bi-arrow-right" aria-hidden="true"></i>
                    </a>
                </div>
            </details>

            <a class="sct-app-desktop-topbar__avatar" href="<?= htmlspecialchars($sctNavbarProfileHref, ENT_QUOTES, 'UTF-8') ?>" title="<?= htmlspecialchars($sctNavbarProfileLabel . ': ' . $sctNavbarName, ENT_QUOTES, 'UTF-8') ?>" aria-label="<?= htmlspecialchars($sctNavbarProfileLabel . ': ' . $sctNavbarName, ENT_QUOTES, 'UTF-8') ?>">
                <?php if ($sctNavbarPhotoSrc !== ''): ?>
                    <img src="<?= htmlspecialchars($sctNavbarPhotoSrc, ENT_QUOTES, 'UTF-8') ?>" alt="">
                <?php else: ?>
                    <?= htmlspecialchars($sctNavbarInitials, ENT_QUOTES, 'UTF-8') ?>
                <?php endif; ?>
            </a>
        </div>
    </div>
</div>

<div class="sct-app-mobile-topbar sct-partial-app-nav" aria-label="Safety Control Tower">
    <div class="sct-app-mobile-topbar__shell">
        <a class="sct-app-mobile-topbar__brand" href="<?= htmlspecialchars($sctNavbarHomeHref, ENT_QUOTES, 'UTF-8') ?>" aria-label="Safety Control Tower">
            <span class="sct-app-mobile-topbar__logo" aria-hidden="true">
                <img src="<?= htmlspecialchars($sctNavbarBasePath . 'images/logos/Logo-SCT-white.png', ENT_QUOTES, 'UTF-8') ?>" alt="">
            </span>
        </a>

        <div class="sct-app-mobile-topbar__profile" title="<?= $sctNavbarEmailEsc ?>">
            <span class="sct-app-mobile-topbar__identity">
                <strong title="<?= $sctNavbarNameEsc ?>"><?= $sctNavbarNameEsc ?></strong>
                <?php if ($sctNavbarRoleLabel !== ''): ?>
                    <small title="<?= $sctNavbarRoleEsc ?>"><?= $sctNavbarRoleEsc ?></small>
                <?php endif; ?>
            </span>
            <a class="sct-app-mobile-topbar__avatar" href="<?= htmlspecialchars($sctNavbarProfileHref, ENT_QUOTES, 'UTF-8') ?>" aria-label="<?= htmlspecialchars($sctNavbarProfileLabel, ENT_QUOTES, 'UTF-8') ?>">
                <?php if ($sctNavbarPhotoSrc !== ''): ?>
                    <img src="<?= htmlspecialchars($sctNavbarPhotoSrc, ENT_QUOTES, 'UTF-8') ?>" alt="">
                <?php else: ?>
                    <?= htmlspecialchars($sctNavbarInitials, ENT_QUOTES, 'UTF-8') ?>
                <?php endif; ?>
            </a>
        </div>

        <details class="sct-app-mobile-notifications sct-app-mobile-notifications--topbar">
            <summary class="sct-app-mobile-topbar__notification-button"
                     aria-label="<?= htmlspecialchars($sctNavbarNotificationLabels['title'], ENT_QUOTES, 'UTF-8') ?>"
                     title="<?= htmlspecialchars($sctNavbarNotificationLabels['title'], ENT_QUOTES, 'UTF-8') ?>">
                <span class="sct-app-notifications__bell" aria-hidden="true">
                    <i class="bi bi-bell"></i>
                    <span class="sct-app-notifications__badge" data-sct-notification-badge<?= $sctNavbarNotificationCount > 0 ? '' : ' hidden' ?>><?= htmlspecialchars($sctNavbarNotificationBadge, ENT_QUOTES, 'UTF-8') ?></span>
                </span>
            </summary>
            <div class="sct-notification-panel sct-notification-panel--topbar">
                <div class="sct-notification-panel__head">
                    <strong><?= htmlspecialchars($sctNavbarNotificationLabels['title'], ENT_QUOTES, 'UTF-8') ?></strong>
                    <span data-sct-notification-head-count<?= $sctNavbarNotificationCount > 0 ? '' : ' hidden' ?>><?= htmlspecialchars($sctNavbarNotificationBadge, ENT_QUOTES, 'UTF-8') ?></span>
                </div>
                <div class="sct-notification-panel__list" data-sct-notification-list<?= empty($sctNavbarNotificationItems) ? ' hidden' : '' ?>>
                    <?php foreach ($sctNavbarNotificationItems as $notification): ?>
                        <a href="<?= htmlspecialchars((string) ($notification['href'] ?? $sctNavbarNotificationTargetHref), ENT_QUOTES, 'UTF-8') ?>" class="sct-notification-panel__item">
                            <span class="sct-notification-panel__icon" aria-hidden="true"><i class="bi <?= htmlspecialchars($notification['icon'], ENT_QUOTES, 'UTF-8') ?>"></i></span>
                            <span class="sct-notification-panel__copy">
                                <strong><?php if (!empty($notification['show_count'])): ?><?= (int) $notification['count'] ?> · <?php endif; ?><?= htmlspecialchars((string) $notification['title'], ENT_QUOTES, 'UTF-8') ?></strong>
                                <?php if ($notification['meta'] !== ''): ?><small><?= htmlspecialchars($notification['meta'], ENT_QUOTES, 'UTF-8') ?></small><?php endif; ?>
                            </span>
                        </a>
                    <?php endforeach; ?>
                </div>
                <p class="sct-notification-panel__empty" data-sct-notification-empty<?= !empty($sctNavbarNotificationItems) ? ' hidden' : '' ?>><?= htmlspecialchars($sctNavbarNotificationLabels['empty'], ENT_QUOTES, 'UTF-8') ?></p>
                <a href="<?= htmlspecialchars($sctNavbarNotificationTargetHref, ENT_QUOTES, 'UTF-8') ?>" class="sct-notification-panel__footer">
                    <?= htmlspecialchars($sctNavbarNotificationLabels['view'], ENT_QUOTES, 'UTF-8') ?>
                    <i class="bi bi-arrow-right" aria-hidden="true"></i>
                </a>
            </div>
        </details>
    </div>
</div>

<nav class="sct-app-mobile-bottomnav sct-partial-app-nav" aria-label="Accesos rápidos Safety Control Tower">
    <div class="sct-app-mobile-bottomnav__shell">
        <div class="sct-app-mobile-settings sct-app-mobile-settings--bottomnav" data-sct-mobile-settings>
            <button type="button"
                    class="sct-app-mobile-bottomnav__item sct-app-mobile-bottomnav__item--settings"
                    data-sct-mobile-settings-trigger
                    aria-label="<?= htmlspecialchars($sctNavbarSettingsLabel, ENT_QUOTES, 'UTF-8') ?>"
                    aria-expanded="false"
                    aria-controls="sctMobileSettingsMenu"
                    title="<?= htmlspecialchars($sctNavbarSettingsLabel, ENT_QUOTES, 'UTF-8') ?>">
                <span class="sct-app-mobile-bottomnav__icon" aria-hidden="true"><i class="bi bi-gear"></i></span>
                <span><?= htmlspecialchars($sctNavbarSettingsLabel, ENT_QUOTES, 'UTF-8') ?></span>
            </button>
            <div id="sctMobileSettingsMenu"
                 class="sct-app-mobile-settings__menu sct-app-mobile-settings__menu--bottomnav"
                 data-sct-mobile-settings-menu
                 role="dialog"
                 aria-modal="false"
                 aria-label="<?= htmlspecialchars($sctNavbarSettingsLabel, ENT_QUOTES, 'UTF-8') ?>"
                 hidden>
                <div class="sct-app-mobile-settings__row sct-app-mobile-settings__row--language">
                    <span class="sct-app-mobile-settings__row-icon" aria-hidden="true"><i class="bi bi-globe2"></i></span>
                    <span class="sct-app-mobile-settings__row-copy">
                        <span class="sct-app-mobile-settings__language-label"><?= htmlspecialchars($sctNavbarLanguageLabel, ENT_QUOTES, 'UTF-8') ?></span>
                        <span class="sct-mobile-language-list" role="list" aria-label="<?= htmlspecialchars($sctNavbarLanguageLabel, ENT_QUOTES, 'UTF-8') ?>">
                            <?php foreach (idiomasDisponiblesConNombre() as $code => $sctNavbarLanguageName): ?>
                                <?php
                                    $langHref = sctNavbarBuildLanguageHref($code);
                                    $flagFile = $sctNavbarLanguageFlagFiles[$code] ?? $sctNavbarLanguageFlagFiles['es'];
                                    $flagSrc = $sctNavbarBasePath . $flagFile . '?v=' . $sctNavbarFlagVersion;
                                ?>
                                <a class="sct-mobile-language-list__item<?= $code === idiomaActual() ? ' active' : '' ?>"
                                   href="<?= htmlspecialchars($langHref, ENT_QUOTES, 'UTF-8') ?>"
                                   role="listitem"
                                   aria-label="<?= htmlspecialchars($sctNavbarLanguageName, ENT_QUOTES, 'UTF-8') ?>">
                                    <img class="sct-language-flag" src="<?= htmlspecialchars($flagSrc, ENT_QUOTES, 'UTF-8') ?>" alt="">
                                    <span><?= htmlspecialchars(strtoupper((string) $code), ENT_QUOTES, 'UTF-8') ?></span>
                                    <?php if ($code === idiomaActual()): ?><i class="bi bi-check2" aria-hidden="true"></i><?php endif; ?>
                                </a>
                            <?php endforeach; ?>
                        </span>
                    </span>
                </div>

                <div class="sct-app-mobile-settings__row sct-app-mobile-settings__row--theme" role="group" aria-label="<?= htmlspecialchars($sctNavbarDesktopLabels['appearance'], ENT_QUOTES, 'UTF-8') ?>">
                    <span class="sct-app-mobile-settings__row-icon" aria-hidden="true"><i class="bi bi-circle-half"></i></span>
                    <span class="sct-app-mobile-settings__row-copy sct-app-mobile-settings__row-copy--theme">
                        <strong><?= htmlspecialchars($sctNavbarDesktopLabels['appearance'], ENT_QUOTES, 'UTF-8') ?></strong>
                        <span class="sct-mobile-theme-choices">
                            <button type="button" class="sct-mobile-theme-choice" data-sct-theme-choice="light" aria-pressed="true">
                                <i class="bi bi-sun" aria-hidden="true"></i>
                                <span><?= htmlspecialchars($sctNavbarDesktopLabels['theme_current_light'], ENT_QUOTES, 'UTF-8') ?></span>
                            </button>
                            <button type="button" class="sct-mobile-theme-choice" data-sct-theme-choice="dark" aria-pressed="false">
                                <i class="bi bi-moon-stars" aria-hidden="true"></i>
                                <span><?= htmlspecialchars($sctNavbarDesktopLabels['theme_current_dark'], ENT_QUOTES, 'UTF-8') ?></span>
                            </button>
                        </span>
                    </span>
                </div>

                <a href="<?= htmlspecialchars($sctNavbarBasePath . 'logout.php', ENT_QUOTES, 'UTF-8') ?>" class="sct-app-mobile-settings__row sct-app-mobile-settings__row--link">
                    <span class="sct-app-mobile-settings__row-icon" aria-hidden="true"><i class="bi bi-box-arrow-right"></i></span>
                    <span class="sct-app-mobile-settings__row-copy">
                        <strong><?= htmlspecialchars($sctNavbarLogoutLabel, ENT_QUOTES, 'UTF-8') ?></strong>
                    </span>
                    <i class="bi bi-chevron-right sct-app-mobile-settings__chevron" aria-hidden="true"></i>
                </a>
            </div>
        </div>

        <a href="<?= htmlspecialchars($sctNavbarHomeHref, ENT_QUOTES, 'UTF-8') ?>" class="sct-app-mobile-bottomnav__item<?= $sctNavbarIsHome ? ' active' : '' ?>">
            <span class="sct-app-mobile-bottomnav__icon" aria-hidden="true"><i class="bi bi-house-door"></i></span>
            <span><?= htmlspecialchars($sctNavbarMobileLabels['home'], ENT_QUOTES, 'UTF-8') ?></span>
        </a>

        <a href="<?= htmlspecialchars($sctNavbarDashboardHref, ENT_QUOTES, 'UTF-8') ?>" class="sct-app-mobile-bottomnav__item<?= $sctNavbarIsDashboard ? ' active' : '' ?>">
            <span class="sct-app-mobile-bottomnav__icon" aria-hidden="true"><i class="bi bi-speedometer2"></i></span>
            <span><?= htmlspecialchars($sctNavbarMobileLabels['dashboard'], ENT_QUOTES, 'UTF-8') ?></span>
        </a>

        <a href="<?= htmlspecialchars($sctNavbarManagementHref, ENT_QUOTES, 'UTF-8') ?>" class="sct-app-mobile-bottomnav__item<?= $sctNavbarIsManagement ? ' active' : '' ?>">
            <span class="sct-app-mobile-bottomnav__icon" aria-hidden="true"><i class="bi bi-grid-1x2"></i></span>
            <span><?= htmlspecialchars($sctNavbarMobileLabels['management'], ENT_QUOTES, 'UTF-8') ?></span>
        </a>

        <?php if ($sctNavbarBackHref !== null): ?>
            <a href="<?= htmlspecialchars($sctNavbarBackHref, ENT_QUOTES, 'UTF-8') ?>" class="sct-app-mobile-bottomnav__item">
                <span class="sct-app-mobile-bottomnav__icon" aria-hidden="true"><i class="bi <?= htmlspecialchars($sctNavbarBackIcon, ENT_QUOTES, 'UTF-8') ?>"></i></span>
                <span><?= htmlspecialchars($sctNavbarBackLabel, ENT_QUOTES, 'UTF-8') ?></span>
            </a>
        <?php endif; ?>
    </div>
</nav>

<script src="<?= htmlspecialchars($sctNavbarBasePath . 'js/sct-ux-system.js?v=' . rawurlencode($sctNavbarAssetVersion), ENT_QUOTES, 'UTF-8') ?>"></script>
<script>
(function () {
    'use strict';

    var root = document.documentElement;
    var topbar = document.querySelector('.sct-app-mobile-topbar');
    var bottomnav = document.querySelector('.sct-app-mobile-bottomnav');
    var settings = document.querySelector('[data-sct-mobile-settings]');
    var settingsTrigger = document.querySelector('[data-sct-mobile-settings-trigger]');
    var settingsMenu = document.querySelector('[data-sct-mobile-settings-menu]');
    var languagePickers = document.querySelectorAll('.sct-language-picker');
    var notificationPickers = document.querySelectorAll('.sct-app-notifications, .sct-app-mobile-notifications');
    var desktopSidebar = document.querySelector('.sct-app-sidebar');
    var desktopSidebarToggle = document.querySelector('.sct-app-sidebar__toggle');
    var desktopLanguagePicker = document.querySelector('.sct-language-picker--sidebar');
    var desktopTopbar = document.querySelector('[data-sct-desktop-topbar]');
    var desktopSearch = document.querySelector('[data-sct-desktop-search]');
    var desktopSearchInput = document.querySelector('[data-sct-desktop-search-input]');
    var desktopSearchResults = document.querySelector('[data-sct-desktop-search-results]');
    var desktopSearchEmpty = document.querySelector('[data-sct-desktop-search-empty]');
    var desktopSearchClear = document.querySelector('[data-sct-desktop-search-clear]');
    var desktopAppearanceButton = document.querySelector('[data-sct-desktop-appearance]');
    var themeToggleButtons = document.querySelectorAll('[data-sct-theme-toggle]');
    var themeChoiceButtons = document.querySelectorAll('[data-sct-theme-choice]');
    var sidebarStorageKey = 'sctDesktopSidebarCollapsed';
    var appearanceStorageKey = 'sctColorTheme';
    var desktopSearchIndex = [];
    var desktopSearchActiveIndex = -1;
    var mobileQueryString = '(max-width: 1180px), (max-width: 1366px) and (max-height: 1100px) and (pointer: coarse), (max-width: 1400px) and (max-height: 700px) and (orientation: landscape) and (pointer: coarse)';
    var mobileQuery = window.matchMedia ? window.matchMedia(mobileQueryString) : null;

    root.classList.add('sct-app-shell-active');

    var notificationEndpoint = desktopSidebar ? (desktopSidebar.getAttribute('data-notification-endpoint') || '') : '';
    var notificationEmptyText = desktopSidebar ? (desktopSidebar.getAttribute('data-notification-empty') || '') : '';
    var notificationRefreshBusy = false;
    var notificationEndpointUrl = '';
    try {
        notificationEndpointUrl = notificationEndpoint ? new URL(notificationEndpoint, window.location.href).toString() : '';
    } catch (_) {
        notificationEndpointUrl = notificationEndpoint;
    }

    function notificationHref(rawHref) {
        if (!rawHref) return '#';
        try {
            return new URL(rawHref, notificationEndpointUrl || window.location.href).toString();
        } catch (_) {
            return rawHref;
        }
    }

    function buildNotificationNode(item) {
        var link = document.createElement('a');
        link.className = 'sct-notification-panel__item';
        link.href = notificationHref(item && item.href ? item.href : '');

        var iconWrap = document.createElement('span');
        iconWrap.className = 'sct-notification-panel__icon';
        iconWrap.setAttribute('aria-hidden', 'true');
        var icon = document.createElement('i');
        icon.className = 'bi ' + (item && item.icon ? item.icon : 'bi-bell');
        iconWrap.appendChild(icon);

        var copy = document.createElement('span');
        copy.className = 'sct-notification-panel__copy';
        var title = document.createElement('strong');
        var prefix = item && item.show_count ? String(Number(item.count || 0)) + ' · ' : '';
        title.textContent = prefix + (item && item.title ? item.title : '');
        copy.appendChild(title);
        if (item && item.meta) {
            var meta = document.createElement('small');
            meta.textContent = item.meta;
            copy.appendChild(meta);
        }

        var arrow = document.createElement('i');
        arrow.className = 'bi bi-chevron-right';
        arrow.setAttribute('aria-hidden', 'true');

        link.appendChild(iconWrap);
        link.appendChild(copy);
        link.appendChild(arrow);
        return link;
    }

    function renderNotifications(data) {
        data = data || {};
        var items = Array.isArray(data.items) ? data.items : [];
        var count = Number(data.count || 0);
        var badge = data.badge != null ? String(data.badge) : String(count);

        document.querySelectorAll('[data-sct-notification-badge]').forEach(function (node) {
            node.textContent = badge;
            node.hidden = count <= 0;
        });
        document.querySelectorAll('[data-sct-notification-head-count]').forEach(function (node) {
            node.textContent = badge;
            node.hidden = count <= 0;
        });
        document.querySelectorAll('[data-sct-notification-list]').forEach(function (list) {
            while (list.firstChild) list.removeChild(list.firstChild);
            items.forEach(function (item) { list.appendChild(buildNotificationNode(item)); });
            list.hidden = items.length === 0;
        });
        document.querySelectorAll('[data-sct-notification-empty]').forEach(function (emptyNode) {
            if (notificationEmptyText) emptyNode.textContent = notificationEmptyText;
            emptyNode.hidden = items.length !== 0;
        });
    }

    function refreshNotifications() {
        if (!notificationEndpointUrl || notificationRefreshBusy || document.hidden) return;
        notificationRefreshBusy = true;
        fetch(notificationEndpointUrl, {
            method: 'GET',
            credentials: 'same-origin',
            cache: 'no-store',
            headers: {'Accept': 'application/json'}
        }).then(function (response) {
            if (!response.ok) throw new Error('notification-refresh');
            return response.json();
        }).then(function (payload) {
            if (payload && payload.success && payload.data) renderNotifications(payload.data);
        }).catch(function () {
            // El HTML inicial sigue siendo válido si una actualización puntual falla.
        }).finally(function () {
            notificationRefreshBusy = false;
        });
    }

    if (notificationEndpointUrl && window.parent === window) {
        window.setInterval(refreshNotifications, 30000);
        document.addEventListener('visibilitychange', function () {
            if (!document.hidden) refreshNotifications();
        });
        window.addEventListener('focus', refreshNotifications);
        window.addEventListener('message', function (event) {
            if (event.origin !== window.location.origin || !event.data) return;
            if (event.data.type === 'sct-activity-updated' || event.data.type === 'sct-activity-exit') {
                refreshNotifications();
            }
        });
        window.addEventListener('sct:activity-updated', refreshNotifications);
    }

    // Si una actividad personal cambió dentro de Mi espacio, al volver con
    // el historial se fuerza una lectura fresca de BD. El estado/scroll de
    // la página se conserva mediante History API antes de abrir la actividad.
    window.addEventListener('pageshow', function () {
        try {
            if (window.sessionStorage.getItem('sct:personal-activity-refresh') === '1') {
                window.sessionStorage.removeItem('sct:personal-activity-refresh');
                window.location.reload();
            }
        } catch (_) {}
    });

    var viewportMeta = document.querySelector('meta[name="viewport"]');
    if (viewportMeta) {
        var viewportContent = viewportMeta.getAttribute('content') || '';
        if (viewportContent.indexOf('viewport-fit=cover') === -1) {
            viewportMeta.setAttribute('content', viewportContent.replace(/\s*,\s*$/, '') + ', viewport-fit=cover');
        }
    }

    function mountMobileBarsAtBody() {
        if (!document.body) return;
        if (topbar && topbar.parentNode !== document.body) {
            document.body.appendChild(topbar);
        }
        if (bottomnav && bottomnav.parentNode !== document.body) {
            document.body.appendChild(bottomnav);
        }
    }

    function viewportMetrics() {
        var vv = window.visualViewport || null;
        var width = vv && vv.width ? vv.width : window.innerWidth;
        var height = vv && vv.height ? vv.height : window.innerHeight;
        var screenWidth = window.screen && window.screen.width ? window.screen.width : width;
        var screenHeight = window.screen && window.screen.height ? window.screen.height : height;

        return {
            width: width,
            height: height,
            shortSide: Math.min(width, height),
            longSide: Math.max(width, height),
            screenShortSide: Math.min(screenWidth, screenHeight),
            screenLongSide: Math.max(screenWidth, screenHeight),
            vv: vv
        };
    }

    function detectMobileAppMode() {
        var m = viewportMetrics();
        var mediaMobile = mobileQuery ? mobileQuery.matches : false;
        var coarsePointer = window.matchMedia ? window.matchMedia('(pointer: coarse)').matches : false;
        var touchCapable = coarsePointer || (navigator.maxTouchPoints && navigator.maxTouchPoints > 0);
        var narrowOrTabletViewport = m.width <= 1180;
        var phoneLikeViewport = touchCapable && m.shortSide <= 600 && m.longSide <= 1400;
        var phoneLikeScreen = touchCapable && m.screenShortSide <= 600 && m.screenLongSide <= 1600;
        var tabletLikeViewport = touchCapable && m.shortSide <= 1024 && m.longSide <= 1366;

        /*
         * P3 v9: el modo app cubre también el rango intermedio de tablet.
         * - <= 1180 px entra directamente al shell app.
         * - iPad/Android tablets grandes en landscape se reconocen por touch
         *   y por su relación de lados, hasta 1366 CSS px.
         * - teléfonos Pro Max / Ultra siguen cubiertos en ambas orientaciones.
         */
        return mediaMobile || narrowOrTabletViewport || tabletLikeViewport || phoneLikeViewport || phoneLikeScreen;
    }

    function syncVisualViewportInsets() {
        var m = viewportMetrics();
        var vv = m.vv;
        var visualBottom = 0;
        var visualTop = 0;

        if (vv) {
            visualTop = Math.max(0, vv.offsetTop || 0);
            visualBottom = Math.max(0, window.innerHeight - (vv.height + visualTop));
        }

        root.style.setProperty('--sct-mobile-vv-top', visualTop + 'px');
        root.style.setProperty('--sct-mobile-vv-bottom', visualBottom + 'px');
    }

    function importantStyle(element, property, value) {
        if (!element) return;
        element.style.setProperty(property, value, 'important');
    }

    function clearForcedBottomNavStyles() {
        if (!bottomnav) return;
        [
            'display', 'position', 'top', 'right', 'bottom', 'left', 'width',
            'max-width', 'margin-left', 'margin-right', 'transform', 'z-index',
            'visibility', 'opacity', 'pointer-events'
        ].forEach(function (property) {
            bottomnav.style.removeProperty(property);
        });
    }

    function forceBottomNavIntoVisualViewport() {
        if (!bottomnav || !detectMobileAppMode()) return;

        mountMobileBarsAtBody();

        /*
         * Esta capa crítica queda en estilos inline para que el dock inferior
         * no dependa de una hoja CSS en caché ni de contextos de apilamiento
         * heredados de los módulos. En iOS/Chrome DevTools usamos el alto del
         * VisualViewport y una coordenada TOP calculada, que es más estable
         * que bottom cuando cambia la barra del navegador.
         */
        importantStyle(bottomnav, 'display', 'block');
        importantStyle(bottomnav, 'position', 'fixed');
        var m = viewportMetrics();
        var tabletLayout = m.width > 767.98 || (m.shortSide > 600 && m.longSide <= 1366);
        var sideInset = tabletLayout ? '1.15rem' : '0.6rem';
        var dockMaxWidth = tabletLayout ? '56rem' : '36rem';

        importantStyle(bottomnav, 'left', sideInset);
        importantStyle(bottomnav, 'right', sideInset);
        importantStyle(bottomnav, 'bottom', 'auto');
        importantStyle(bottomnav, 'width', 'auto');
        importantStyle(bottomnav, 'max-width', dockMaxWidth);
        importantStyle(bottomnav, 'margin-left', 'auto');
        importantStyle(bottomnav, 'margin-right', 'auto');
        importantStyle(bottomnav, 'transform', 'none');
        importantStyle(bottomnav, 'z-index', '2147483646');
        importantStyle(bottomnav, 'visibility', 'visible');
        importantStyle(bottomnav, 'opacity', '1');
        importantStyle(bottomnav, 'pointer-events', 'none');

        var shell = bottomnav.querySelector('.sct-app-mobile-bottomnav__shell');
        if (shell) {
            importantStyle(shell, 'display', 'flex');
            importantStyle(shell, 'visibility', 'visible');
            importantStyle(shell, 'opacity', '1');
            importantStyle(shell, 'pointer-events', 'auto');
        }

        var visualTop = m.vv ? Math.max(0, m.vv.offsetTop || 0) : 0;
        var visibleHeight = Math.max(0, m.height || window.innerHeight || 0);
        var navHeight = bottomnav.getBoundingClientRect().height;

        if (!navHeight || navHeight < 44) {
            navHeight = shell ? Math.max(shell.getBoundingClientRect().height, 64) : 64;
        }

        var bottomGap = tabletLayout ? 16 : (m.width <= 430 ? 10 : 12);
        var calculatedTop = Math.max(visualTop + 8, visualTop + visibleHeight - navHeight - bottomGap);
        importantStyle(bottomnav, 'top', Math.round(calculatedTop) + 'px');
    }

    function positionMobileSettingsMenu() {
        if (!settings || !settings.classList.contains('is-open') || !detectMobileAppMode()) return;
        var menu = settingsMenu;
        var trigger = settingsTrigger;
        if (!menu || !trigger) return;

        var m = viewportMetrics();
        var vv = m.vv;
        var viewportLeft = vv ? Math.max(0, vv.offsetLeft || 0) : 0;
        var viewportTop = vv ? Math.max(0, vv.offsetTop || 0) : 0;
        var viewportRight = viewportLeft + m.width;
        var viewportBottom = viewportTop + m.height;
        var margin = m.width <= 430 ? 8 : 12;
        var gap = 10;
        var maxWidth = Math.max(240, Math.min(360, m.width - (margin * 2)));

        importantStyle(menu, 'position', 'fixed');
        importantStyle(menu, 'width', maxWidth + 'px');
        importantStyle(menu, 'max-width', maxWidth + 'px');
        importantStyle(menu, 'right', 'auto');
        importantStyle(menu, 'bottom', 'auto');
        importantStyle(menu, 'visibility', 'hidden');

        var triggerRect = trigger.getBoundingClientRect();
        var menuRect = menu.getBoundingClientRect();
        var menuHeight = Math.min(menuRect.height || 300, Math.max(180, m.height - (margin * 2)));
        var desiredLeft = triggerRect.left + (triggerRect.width / 2) - (maxWidth / 2);
        var left = Math.min(Math.max(desiredLeft, viewportLeft + margin), viewportRight - maxWidth - margin);
        var top = triggerRect.top - menuHeight - gap;

        if (top < viewportTop + margin) {
            top = triggerRect.bottom + gap;
        }
        if (top + menuHeight > viewportBottom - margin) {
            top = Math.max(viewportTop + margin, viewportBottom - menuHeight - margin);
        }

        importantStyle(menu, 'left', Math.round(left) + 'px');
        importantStyle(menu, 'top', Math.round(top) + 'px');
        importantStyle(menu, 'max-height', Math.max(180, Math.floor(viewportBottom - top - margin)) + 'px');
        importantStyle(menu, 'visibility', 'visible');
    }

    function clearInfoTipPosition(tip) {
        if (!tip) return;
        var bubble = tip.querySelector('.sct-info-tip__bubble');
        if (!bubble) return;
        bubble.classList.remove('is-smart-positioned', 'is-below');
        bubble.style.removeProperty('left');
        bubble.style.removeProperty('top');
        bubble.style.removeProperty('width');
        bubble.style.removeProperty('max-height');
        bubble.style.removeProperty('overflow-y');
        bubble.style.removeProperty('--sct-tip-arrow-left');
    }

    function positionInfoTip(tip) {
        if (!tip) return;
        var button = tip.querySelector('[data-sct-info-tip-button]');
        var bubble = tip.querySelector('.sct-info-tip__bubble');
        if (!button || !bubble) return;

        var m = viewportMetrics();
        var vv = m.vv;
        var viewportLeft = vv ? Math.max(0, vv.offsetLeft || 0) : 0;
        var viewportTop = vv ? Math.max(0, vv.offsetTop || 0) : 0;
        var viewportRight = viewportLeft + m.width;
        var viewportBottom = viewportTop + m.height;
        var margin = m.width < 600 ? 10 : (m.width < 1280 ? 14 : 16);
        var gap = 10;
        var availableWidth = Math.max(180, m.width - (margin * 2));
        var width = Math.min(m.width < 600 ? 320 : 330, availableWidth);
        var maxHeight = Math.max(120, m.height - (margin * 2));

        bubble.classList.add('is-smart-positioned');
        bubble.style.width = Math.round(width) + 'px';
        bubble.style.maxHeight = Math.round(maxHeight) + 'px';
        bubble.style.overflowY = 'auto';
        bubble.style.left = '0px';
        bubble.style.top = '0px';

        var buttonRect = button.getBoundingClientRect();
        var bubbleRect = bubble.getBoundingClientRect();
        var bubbleHeight = Math.min(bubbleRect.height || 120, maxHeight);
        var center = buttonRect.left + (buttonRect.width / 2);
        var maxLeft = Math.max(viewportLeft + margin, viewportRight - width - margin);
        var left = Math.min(Math.max(center - (width / 2), viewportLeft + margin), maxLeft);
        var spaceAbove = buttonRect.top - viewportTop - gap - margin;
        var spaceBelow = viewportBottom - buttonRect.bottom - gap - margin;
        var below = spaceBelow > spaceAbove && spaceAbove < bubbleHeight;
        var top = below ? (buttonRect.bottom + gap) : (buttonRect.top - bubbleHeight - gap);

        if (top < viewportTop + margin) top = viewportTop + margin;
        if (top + bubbleHeight > viewportBottom - margin) {
            top = Math.max(viewportTop + margin, viewportBottom - bubbleHeight - margin);
        }

        bubble.classList.toggle('is-below', below);
        bubble.style.left = Math.round(left) + 'px';
        bubble.style.top = Math.round(top) + 'px';
        bubble.style.setProperty('--sct-tip-arrow-left', Math.round(Math.min(Math.max(center - left, 18), width - 18)) + 'px');
    }

    function repositionOpenInfoTips() {
        document.querySelectorAll('[data-sct-info-tip].is-open').forEach(function (tip) {
            positionInfoTip(tip);
        });
    }

    function setDesktopSidebarCollapsed(collapsed, persist) {
        if (!desktopSidebar || !desktopSidebarToggle) return;

        root.classList.toggle('sct-desktop-sidebar-collapsed', collapsed);
        desktopSidebar.classList.toggle('is-collapsed', collapsed);
        desktopSidebarToggle.setAttribute('aria-expanded', collapsed ? 'false' : 'true');

        var collapseLabel = desktopSidebarToggle.getAttribute('data-collapse-label') || 'Contraer navegación';
        var expandLabel = desktopSidebarToggle.getAttribute('data-expand-label') || 'Expandir navegación';
        var currentLabel = collapsed ? expandLabel : collapseLabel;
        desktopSidebarToggle.setAttribute('aria-label', currentLabel);
        desktopSidebarToggle.setAttribute('title', currentLabel);

        if (collapsed && desktopLanguagePicker && desktopLanguagePicker.open) {
            desktopLanguagePicker.removeAttribute('open');
        }

        if (persist) {
            try {
                window.localStorage.setItem(sidebarStorageKey, collapsed ? '1' : '0');
            } catch (error) {
                /* localStorage puede estar bloqueado por privacidad; no es crítico. */
            }
        }
    }

    function normalizeSearchText(value) {
        var textValue = String(value || '').toLowerCase().trim();
        if (textValue.normalize) {
            textValue = textValue.normalize('NFD').replace(/[\u0300-\u036f]/g, '');
        }
        return textValue.replace(/\s+/g, ' ');
    }

    function closeDesktopSearch() {
        if (!desktopSearchResults || !desktopSearchInput) return;
        desktopSearchResults.hidden = true;
        desktopSearchInput.setAttribute('aria-expanded', 'false');
        desktopSearchActiveIndex = -1;
    }

    function buildDesktopSearchIndex() {
        var seen = Object.create(null);
        var candidates = Array.prototype.slice.call(document.querySelectorAll('a[href]'));
        desktopSearchIndex = [];

        function conciseSearchLabel(link) {
            var explicit = String(link.getAttribute('data-sct-search-label') || '').replace(/\s+/g, ' ').trim();
            if (explicit) return explicit;

            var card = link.closest('.welcome-action-card, .management-module-card, .sct-personal-activity-card, .welcome-course-card, .dashboard-panel, .feature-card');
            var heading = link.querySelector('h1, h2, h3, .sct-main-title, .management-module-card__title, .sct-personal-activity-card__title');
            if (!heading && card) heading = card.querySelector('h1, h2, h3, .sct-main-title, .management-module-card__title, .sct-personal-activity-card__title');
            if (heading) {
                var headingText = String(heading.textContent || '').replace(/\s+/g, ' ').trim();
                if (headingText) return headingText;
            }

            var aria = String(link.getAttribute('aria-label') || '').replace(/\s+/g, ' ').trim();
            if (aria && aria.length <= 72) return aria;

            var visible = String(link.innerText || link.textContent || '').replace(/\s+/g, ' ').trim();
            if (!visible) return '';
            // Evita mostrar descripciones completas o CTAs concatenados en resultados.
            var pieces = visible.split(/(?:\s{2,}|\n|·)/).map(function (part) { return part.trim(); }).filter(Boolean);
            var label = pieces.length ? pieces[0] : visible;
            return label.length > 72 ? label.slice(0, 69).trim() + '…' : label;
        }

        candidates.forEach(function (link) {
            if (!link || link.closest('.sct-app-desktop-topbar')) return;
            if (link.closest('.sct-app-mobile-topbar, .sct-app-mobile-bottomnav')) return;
            if (link.closest('.sct-notification-panel, .sct-language-picker')) return;
            var rawHref = link.getAttribute('href') || '';
            if (!rawHref || rawHref === '#' || /^javascript:/i.test(rawHref) || /logout\.php(?:$|\?)/i.test(rawHref)) return;
            if (link.hidden || link.getAttribute('aria-hidden') === 'true') return;

            var label = conciseSearchLabel(link);
            if (!label || label.length < 2) return;
            var fullText = String(link.innerText || link.textContent || '').replace(/\s+/g, ' ').trim();

            var href;
            try { href = new URL(rawHref, window.location.href).toString(); }
            catch (_) { href = rawHref; }
            var key = href + '|' + normalizeSearchText(label);
            if (seen[key]) return;
            seen[key] = true;

            desktopSearchIndex.push({
                label: label,
                search: normalizeSearchText(label + ' ' + fullText),
                href: href
            });
        });

        // Secciones visibles: usa exactamente el título mostrado en pantalla.
        Array.prototype.slice.call(document.querySelectorAll('main h1[id], main h2[id], main h3[id]')).forEach(function (heading) {
            if (heading.closest('.sct-app-desktop-topbar, .sct-partial-app-nav')) return;
            var label = String(heading.textContent || '').replace(/\s+/g, ' ').trim();
            if (!label || label.length < 2) return;
            if (label.length > 72) label = label.slice(0, 69).trim() + '…';
            var href = window.location.href.split('#')[0] + '#' + encodeURIComponent(heading.id);
            var key = href + '|' + normalizeSearchText(label);
            if (seen[key]) return;
            seen[key] = true;
            desktopSearchIndex.push({ label: label, search: normalizeSearchText(label), href: href });
        });
    }

    function setDesktopSearchActive(index) {
        if (!desktopSearchResults) return;
        var rows = desktopSearchResults.querySelectorAll('.sct-app-desktop-search__result');
        rows.forEach(function (row, rowIndex) {
            var active = rowIndex === index;
            row.classList.toggle('is-active', active);
            if (active) row.setAttribute('aria-current', 'true');
            else row.removeAttribute('aria-current');
        });
        desktopSearchActiveIndex = index;
        if (index >= 0 && rows[index]) rows[index].scrollIntoView({block: 'nearest'});
    }

    function renderDesktopSearch(query) {
        if (!desktopSearchResults || !desktopSearchInput || !desktopSearchEmpty) return;
        var normalized = normalizeSearchText(query);
        desktopSearchResults.querySelectorAll('.sct-app-desktop-search__result').forEach(function (row) { row.remove(); });
        desktopSearchActiveIndex = -1;

        if (!normalized) {
            desktopSearchEmpty.hidden = true;
            closeDesktopSearch();
            return;
        }

        if (!desktopSearchIndex.length) buildDesktopSearchIndex();
        var matches = desktopSearchIndex.filter(function (entry) {
            return entry.search.indexOf(normalized) !== -1;
        }).slice(0, 7);

        matches.forEach(function (entry) {
            var link = document.createElement('a');
            link.className = 'sct-app-desktop-search__result';
            link.href = entry.href;
            link.innerHTML = '<span class="sct-app-desktop-search__result-icon" aria-hidden="true"><i class="bi bi-arrow-return-right"></i></span><span></span>';
            link.querySelector('span:last-child').textContent = entry.label;
            desktopSearchResults.insertBefore(link, desktopSearchEmpty);
        });

        desktopSearchEmpty.hidden = matches.length !== 0;
        desktopSearchResults.hidden = false;
        desktopSearchInput.setAttribute('aria-expanded', 'true');
    }

    function setColorTheme(mode, persist) {
        var dark = mode === 'dark';
        root.classList.toggle('sct-theme-dark', dark);
        root.setAttribute('data-sct-theme', dark ? 'dark' : 'light');

        var toggles = [];
        if (desktopAppearanceButton) toggles.push(desktopAppearanceButton);
        themeToggleButtons.forEach(function (button) { toggles.push(button); });
        toggles.forEach(function (button) {
            button.setAttribute('aria-pressed', dark ? 'true' : 'false');
            var label = dark
                ? (button.getAttribute('data-light-label') || '')
                : (button.getAttribute('data-dark-label') || '');
            if (label) {
                button.setAttribute('title', label);
                button.setAttribute('aria-label', label);
            }
            var icon = button.querySelector('.sct-app-mobile-settings__row-icon i, i');
            if (icon) icon.className = dark ? 'bi bi-sun' : 'bi bi-moon-stars';
        });

        themeChoiceButtons.forEach(function (button) {
            var choice = button.getAttribute('data-sct-theme-choice') || 'light';
            var active = choice === (dark ? 'dark' : 'light');
            button.classList.toggle('is-active', active);
            button.setAttribute('aria-pressed', active ? 'true' : 'false');
        });

        document.querySelectorAll('[data-sct-theme-toggle-label]').forEach(function (node) {
            node.textContent = dark
                ? <?= json_encode($sctNavbarDesktopLabels['theme_current_dark'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>
                : <?= json_encode($sctNavbarDesktopLabels['theme_current_light'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
        });

        if (persist) {
            try { window.localStorage.setItem(appearanceStorageKey, dark ? 'dark' : 'light'); }
            catch (_) {}
        }
        try {
            document.dispatchEvent(new CustomEvent('sct:theme-change', {detail: {theme: dark ? 'dark' : 'light'}}));
        } catch (_) {}
    }

    function initDesktopTopbar() {
        var savedAppearance = 'light';
        try { savedAppearance = window.localStorage.getItem(appearanceStorageKey) || 'light'; }
        catch (_) { savedAppearance = 'light'; }
        setColorTheme(savedAppearance === 'dark' ? 'dark' : 'light', false);

        if (desktopAppearanceButton) {
            desktopAppearanceButton.addEventListener('click', function () {
                setColorTheme(root.classList.contains('sct-theme-dark') ? 'light' : 'dark', true);
            });
        }
        themeToggleButtons.forEach(function (button) {
            button.addEventListener('click', function () {
                setColorTheme(root.classList.contains('sct-theme-dark') ? 'light' : 'dark', true);
            });
        });

        themeChoiceButtons.forEach(function (button) {
            button.addEventListener('click', function () {
                var choice = button.getAttribute('data-sct-theme-choice') === 'dark' ? 'dark' : 'light';
                setColorTheme(choice, true);
            });
        });

        if (!desktopSearch || !desktopSearchInput || !desktopSearchResults) return;
        buildDesktopSearchIndex();

        desktopSearchInput.addEventListener('input', function () {
            if (desktopSearchClear) desktopSearchClear.hidden = !desktopSearchInput.value;
            renderDesktopSearch(desktopSearchInput.value);
        });
        desktopSearchInput.addEventListener('focus', function () {
            buildDesktopSearchIndex();
            if (desktopSearchInput.value) renderDesktopSearch(desktopSearchInput.value);
        });
        desktopSearchInput.addEventListener('keydown', function (event) {
            var rows = desktopSearchResults.querySelectorAll('.sct-app-desktop-search__result');
            if (event.key === 'Escape') {
                closeDesktopSearch();
                desktopSearchInput.blur();
                return;
            }
            if (!rows.length) return;
            if (event.key === 'ArrowDown') {
                event.preventDefault();
                setDesktopSearchActive(Math.min(rows.length - 1, desktopSearchActiveIndex + 1));
            } else if (event.key === 'ArrowUp') {
                event.preventDefault();
                setDesktopSearchActive(Math.max(0, desktopSearchActiveIndex <= 0 ? 0 : desktopSearchActiveIndex - 1));
            } else if (event.key === 'Enter' && desktopSearchActiveIndex >= 0 && rows[desktopSearchActiveIndex]) {
                event.preventDefault();
                window.location.href = rows[desktopSearchActiveIndex].href;
            }
        });
        if (desktopSearchClear) {
            desktopSearchClear.addEventListener('click', function () {
                desktopSearchInput.value = '';
                desktopSearchClear.hidden = true;
                closeDesktopSearch();
                desktopSearchInput.focus();
            });
        }
        document.addEventListener('click', function (event) {
            if (desktopSearch && !desktopSearch.contains(event.target)) closeDesktopSearch();
        });
    }

    function initDesktopSidebar() {
        if (!desktopSidebar || !desktopSidebarToggle) return;

        var collapsed = false;
        try {
            collapsed = window.localStorage.getItem(sidebarStorageKey) === '1';
        } catch (error) {
            collapsed = false;
        }

        setDesktopSidebarCollapsed(collapsed, false);

        desktopSidebarToggle.addEventListener('click', function () {
            var nextCollapsed = !root.classList.contains('sct-desktop-sidebar-collapsed');
            setDesktopSidebarCollapsed(nextCollapsed, true);
        });
    }

    function syncMobileShell() {
        var isMobile = detectMobileAppMode();
        root.classList.toggle('sct-mobile-app-nav-active', isMobile);
        root.classList.add('sct-nav-ready');
        syncVisualViewportInsets();

        if (isMobile) {
            mountMobileBarsAtBody();
            window.requestAnimationFrame(forceBottomNavIntoVisualViewport);
        } else {
            setMobileSettingsOpen(false, false);
            clearForcedBottomNavStyles();
        }
    }

    function initMobileShell() {
        initDesktopSidebar();
        initDesktopTopbar();
        mountMobileBarsAtBody();
        syncMobileShell();
        window.requestAnimationFrame(syncMobileShell);
        window.setTimeout(function () { syncMobileShell(); positionMobileSettingsMenu(); repositionOpenInfoTips(); }, 120);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initMobileShell);
    } else {
        initMobileShell();
    }

    if (mobileQuery) {
        if (typeof mobileQuery.addEventListener === 'function') {
            mobileQuery.addEventListener('change', syncMobileShell);
        } else if (typeof mobileQuery.addListener === 'function') {
            mobileQuery.addListener(syncMobileShell);
        }
    }

    window.addEventListener('resize', function () { syncMobileShell(); positionMobileSettingsMenu(); repositionOpenInfoTips(); });
    window.addEventListener('orientationchange', function () {
        window.setTimeout(syncMobileShell, 120);
    });

    if (window.visualViewport) {
        window.visualViewport.addEventListener('resize', function () { syncMobileShell(); positionMobileSettingsMenu(); repositionOpenInfoTips(); });
        window.visualViewport.addEventListener('scroll', function () {
            syncVisualViewportInsets();
            forceBottomNavIntoVisualViewport();
            positionMobileSettingsMenu();
            repositionOpenInfoTips();
        });
    }

    if (window.ResizeObserver && bottomnav) {
        var bottomNavObserver = new ResizeObserver(function () {
            forceBottomNavIntoVisualViewport();
        });
        bottomNavObserver.observe(bottomnav);
    }

    function isMobileSettingsOpen() {
        return !!(settings && settings.classList.contains('is-open'));
    }

    function setMobileSettingsOpen(open, restoreFocus) {
        if (!settings || !settingsTrigger || !settingsMenu) return;
        settings.classList.toggle('is-open', !!open);
        settingsMenu.classList.toggle('is-open', !!open);
        settingsTrigger.setAttribute('aria-expanded', open ? 'true' : 'false');
        settingsMenu.hidden = !open;
        if (!open) {
            settingsMenu.style.removeProperty('left');
            settingsMenu.style.removeProperty('top');
            settingsMenu.style.removeProperty('width');
            settingsMenu.style.removeProperty('max-width');
            settingsMenu.style.removeProperty('max-height');
            settingsMenu.style.removeProperty('visibility');
            if (restoreFocus && typeof settingsTrigger.focus === 'function') settingsTrigger.focus();
        }
    }

    /*
     * P30: Configuración mobile/tablet deja de depender de <details>.
     * El botón y el panel se controlan explícitamente y el menú se monta en
     * <body> para evitar recortes, stacking-contexts y toggles nativos
     * inconsistentes en iOS/Android/tablets.
     */
    if (settings && settingsTrigger && settingsMenu) {
        if (settingsMenu.parentNode !== document.body) {
            document.body.appendChild(settingsMenu);
        }
        setMobileSettingsOpen(false, false);

        settingsTrigger.addEventListener('click', function (event) {
            event.preventDefault();
            event.stopPropagation();
            if (!detectMobileAppMode()) return;

            var willOpen = !isMobileSettingsOpen();
            setMobileSettingsOpen(willOpen, false);

            if (willOpen) {
                Array.prototype.forEach.call(notificationPickers || [], function (picker) {
                    if (picker && picker.open) picker.removeAttribute('open');
                });
                forceBottomNavIntoVisualViewport();
                window.requestAnimationFrame(positionMobileSettingsMenu);
            }
        });
    }

    document.addEventListener('click', function (event) {
        if (isMobileSettingsOpen()
            && (!settings || !settings.contains(event.target))
            && (!settingsMenu || !settingsMenu.contains(event.target))) {
            setMobileSettingsOpen(false, false);
        }
        if (languagePickers && languagePickers.length) {
            Array.prototype.forEach.call(languagePickers, function (picker) {
                if (picker.open && !picker.contains(event.target)) {
                    picker.removeAttribute('open');
                }
            });
        }
        if (notificationPickers && notificationPickers.length) {
            Array.prototype.forEach.call(notificationPickers, function (picker) {
                if (picker.open && !picker.contains(event.target)) {
                    picker.removeAttribute('open');
                }
            });
        }
    });

    function closeSctInfoTip(tip, restoreFocus) {
        if (!tip) return;
        var button = tip.querySelector('[data-sct-info-tip-button]');
        tip.classList.remove('is-open');
        clearInfoTipPosition(tip);
        if (button) {
            button.setAttribute('aria-expanded', 'false');
            if (restoreFocus && typeof button.focus === 'function') button.focus();
        }
    }

    document.addEventListener('click', function (event) {
        var closeButton = event.target.closest ? event.target.closest('.sct-info-tip__close') : null;
        if (closeButton) {
            event.preventDefault();
            var closeTip = closeButton.closest('[data-sct-info-tip]');
            closeSctInfoTip(closeTip, true);
            return;
        }

        var tipButton = event.target.closest ? event.target.closest('[data-sct-info-tip-button]') : null;
        if (tipButton) {
            event.preventDefault();
            var tip = tipButton.closest('[data-sct-info-tip]');
            document.querySelectorAll('[data-sct-info-tip].is-open').forEach(function (openTip) {
                if (openTip !== tip) closeSctInfoTip(openTip, false);
            });
            if (tip) {
                var willOpen = !tip.classList.contains('is-open');
                if (willOpen) {
                    tip.classList.add('is-open');
                    tipButton.setAttribute('aria-expanded', 'true');
                    window.requestAnimationFrame(function () { positionInfoTip(tip); });
                } else {
                    closeSctInfoTip(tip, false);
                }
            }
            return;
        }

        document.querySelectorAll('[data-sct-info-tip].is-open').forEach(function (openTip) {
            if (!openTip.contains(event.target)) closeSctInfoTip(openTip, false);
        });
    });

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape') {
            document.querySelectorAll('[data-sct-info-tip].is-open').forEach(function (openTip) {
                closeSctInfoTip(openTip, true);
            });
            if (isMobileSettingsOpen()) {
                setMobileSettingsOpen(false, true);
            }
            if (languagePickers && languagePickers.length) {
                Array.prototype.forEach.call(languagePickers, function (picker) {
                    if (picker.open) {
                        picker.removeAttribute('open');
                        var pickerSummary = picker.querySelector('summary');
                        if (pickerSummary) pickerSummary.focus();
                    }
                });
            }
            if (notificationPickers && notificationPickers.length) {
                Array.prototype.forEach.call(notificationPickers, function (picker) {
                    if (picker.open) {
                        picker.removeAttribute('open');
                        var notificationSummary = picker.querySelector('summary');
                        if (notificationSummary) notificationSummary.focus();
                    }
                });
            }
        }
    });
})();
</script>
