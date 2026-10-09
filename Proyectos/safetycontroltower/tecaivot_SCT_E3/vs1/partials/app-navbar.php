<?php
/** Safety Control Tower — navbar superior compartido E3-VS1. */
if (!isset($sctNavbarBasePath) || !is_string($sctNavbarBasePath) || $sctNavbarBasePath === '') $sctNavbarBasePath = './';
if (substr($sctNavbarBasePath, -1) !== '/') $sctNavbarBasePath .= '/';
if (empty($_SESSION['csrf_token'])) $_SESSION['csrf_token'] = bin2hex(random_bytes(32));

$sctNavEmail = (string)($_SESSION['user_email'] ?? '');
$sctNavProfile = [];
if (isset($perfil) && is_array($perfil)) $sctNavProfile = $perfil;
elseif (isset($pdo) && $pdo instanceof PDO && function_exists('currentUserProfile')) {
    try { $tmp = currentUserProfile($pdo); if (is_array($tmp)) $sctNavProfile = $tmp; } catch (Throwable $e) { $sctNavProfile = []; }
}
$sctNavName = trim((string)($sctNavProfile['name'] ?? '') . ' ' . (string)($sctNavProfile['lastname'] ?? ''));
if ($sctNavName === '') $sctNavName = $sctNavEmail !== '' ? ucfirst((string)explode('@',$sctNavEmail)[0]) : (function_exists('t') ? t('users_col_user') : 'User');
if (function_exists('capitalizarNombre')) $sctNavName = capitalizarNombre($sctNavName);

$sctNavRoles = [];
if (isset($roles) && is_array($roles)) $sctNavRoles = $roles;
elseif (isset($pdo) && $pdo instanceof PDO && function_exists('currentUserRoles')) {
    try { $tmp = currentUserRoles($pdo); if (is_array($tmp)) $sctNavRoles = $tmp; } catch (Throwable $e) { $sctNavRoles = []; }
}
$sctNavRole = function_exists('primaryRoleName') ? (string)(primaryRoleName($sctNavRoles) ?? '') : '';
$sctNavRoleLabel = $sctNavRole !== '' && function_exists('roleDisplayLabel') ? roleDisplayLabel($sctNavRole) : 'Safety Control Tower';

$sctNavInitials = '';
foreach (preg_split('/\s+/u', trim($sctNavName)) ?: [] as $part) {
    if ($part === '') continue;
    $sctNavInitials .= function_exists('sctTextSubstr') ? sctTextSubstr($part,0,1) : substr($part,0,1);
    if (strlen($sctNavInitials) >= 2) break;
}
if ($sctNavInitials === '') $sctNavInitials = 'US';
$sctNavInitials = function_exists('sctTextUpper') ? sctTextUpper($sctNavInitials) : strtoupper($sctNavInitials);

$sctNavPhoto = (string)($sctNavProfile['profile_photo_path'] ?? '');
$sctNavPhotoSrc = '';
if ($sctNavPhoto !== '') {
    $sctNavPhotoSrc = preg_match('~^(?:https?:)?//|^data:|^/~i',$sctNavPhoto) ? $sctNavPhoto : $sctNavbarBasePath . ltrim($sctNavPhoto,'./');
}

if (!function_exists('sctLangHref')) {
    function sctLangHref($code) {
        $uri = (string)($_SERVER['REQUEST_URI'] ?? '');
        $parts = parse_url($uri); $path = (string)($parts['path'] ?? ''); $query = [];
        if (!empty($parts['query'])) parse_str((string)$parts['query'],$query);
        $query['lang'] = (string)$code;
        return ($path !== '' ? $path : '?') . '?' . http_build_query($query);
    }
}
$flags = ['es'=>'lang-es.svg','en'=>'lang-en.svg','pt'=>'lang-pt.svg','fr'=>'lang-fr.svg','zh'=>'lang-zh.svg'];
$langNames = ['es'=>'Español','en'=>'English','pt'=>'Português','fr'=>'Français','zh'=>'中文'];
$sctNavLang = function_exists('idiomaActual') ? idiomaActual() : 'es';

require_once __DIR__ . '/../app/Navigation/NavigationRegistry.php';
require_once __DIR__ . '/../app/Config/MutualityOptions.php';
$navbarNavigation = ($pdo instanceof PDO)
    ? SctNavigationRegistry::navbar($pdo,$sctNavRole,$sctNavbarBasePath)
    : ['primary'=>[],'management'=>[]];

$directNavItems = $navbarNavigation['primary'] ?? [];
$managementNavItems = $navbarNavigation['management'] ?? [];

$mutualOptions = SctMutualityOptions::OPTIONS;
$currentMutual = strtolower((string)($sctNavProfile['mutual_code'] ?? ''));
$currentMutualInfo = $mutualOptions[$currentMutual] ?? null;
?>
<header class="sct-top-navbar" aria-label="Safety Control Tower">
  <div class="sct-top-navbar__inner">
    <div class="sct-top-navbar__left">
      <a class="sct-top-navbar__brand" href="<?= htmlspecialchars($sctNavbarBasePath.'bienvenida.php',ENT_QUOTES,'UTF-8') ?>">
        <span class="sct-top-navbar__logo"><img src="<?= htmlspecialchars($sctNavbarBasePath.'images/logos/Logo-SCT.png',ENT_QUOTES,'UTF-8') ?>" alt="Safety Control Tower"></span>
        <span class="sct-top-navbar__brand-copy"><strong>Safety Control</strong><small>TOWER</small></span>
      </a>
      <div class="sct-mutual" data-current-mutual="<?= htmlspecialchars($currentMutual,ENT_QUOTES,'UTF-8') ?>" data-sct-mutual data-endpoint="<?= htmlspecialchars($sctNavbarBasePath.'api/usuarios/mutualidad.php',ENT_QUOTES,'UTF-8') ?>" data-csrf="<?= htmlspecialchars($_SESSION['csrf_token'],ENT_QUOTES,'UTF-8') ?>">
        <button class="sct-mutual__trigger" type="button" aria-haspopup="menu" aria-expanded="false" data-sct-mutual-trigger>
          <span class="sct-mutual__logo" data-sct-mutual-logo-wrap>
            <?php if($currentMutualInfo): ?><img data-sct-mutual-logo src="<?= htmlspecialchars($currentMutualInfo['logo'],ENT_QUOTES,'UTF-8') ?>" alt="<?= htmlspecialchars($currentMutualInfo['sigla'],ENT_QUOTES,'UTF-8') ?>"><?php else: ?><i class="bi bi-shield-check" data-sct-mutual-placeholder></i><?php endif; ?>
          </span>
          <span class="sct-mutual__label" data-sct-mutual-label><?= $currentMutualInfo ? htmlspecialchars($currentMutualInfo['name'],ENT_QUOTES,'UTF-8') : htmlspecialchars(t('nav_mutuality'),ENT_QUOTES,'UTF-8') ?></span>
          <i class="bi bi-chevron-down"></i>
        </button>
        <div class="sct-mutual__menu" role="menu" hidden data-sct-mutual-menu>
          <?php foreach ($mutualOptions as $code=>$opt): ?>
          <button type="button" role="menuitemradio" aria-checked="<?= $currentMutual===$code?'true':'false' ?>" data-mutual-code="<?= htmlspecialchars($code,ENT_QUOTES,'UTF-8') ?>" data-mutual-sigla="<?= htmlspecialchars($opt['sigla'],ENT_QUOTES,'UTF-8') ?>" data-mutual-name="<?= htmlspecialchars($opt['name'],ENT_QUOTES,'UTF-8') ?>" data-mutual-logo="<?= htmlspecialchars($opt['logo'],ENT_QUOTES,'UTF-8') ?>">
            <span class="sct-mutual__option-logo"><img src="<?= htmlspecialchars($opt['logo'],ENT_QUOTES,'UTF-8') ?>" alt="<?= htmlspecialchars($opt['sigla'],ENT_QUOTES,'UTF-8') ?>" loading="lazy"></span>
            <span><?= htmlspecialchars($opt['name'],ENT_QUOTES,'UTF-8') ?></span><i class="bi bi-check2"></i>
          </button>
          <?php endforeach; ?>
        </div>
        <span class="sct-mutual__status" role="status" aria-live="polite" data-sct-mutual-status></span>
      </div>
    </div>

    <div class="sct-top-navbar__right">
      <div class="sct-top-navbar__identity" title="<?= htmlspecialchars($sctNavEmail,ENT_QUOTES,'UTF-8') ?>">
        <span class="sct-top-navbar__identity-copy"><strong><?= htmlspecialchars($sctNavName,ENT_QUOTES,'UTF-8') ?></strong><small><?= htmlspecialchars($sctNavRoleLabel,ENT_QUOTES,'UTF-8') ?></small></span>
        <span class="sct-top-navbar__avatar"><?php if($sctNavPhotoSrc!==''): ?><img src="<?= htmlspecialchars($sctNavPhotoSrc,ENT_QUOTES,'UTF-8') ?>" alt=""><?php else: ?><?= htmlspecialchars($sctNavInitials,ENT_QUOTES,'UTF-8') ?><?php endif; ?></span>
      </div>
      <button class="sct-menu-toggle" type="button" aria-expanded="false" aria-controls="sctMainMenu" aria-label="<?= htmlspecialchars(t('nav_menu'),ENT_QUOTES,'UTF-8') ?>" data-sct-menu-toggle><i class="bi bi-list"></i></button>
    </div>
  </div>
  <aside id="sctMainMenu" class="sct-main-menu" aria-hidden="true" data-sct-main-menu>
    <div class="sct-main-menu__scroll">
      <div class="sct-main-menu__section-title"><?= htmlspecialchars(t('home_direct_access'),ENT_QUOTES,'UTF-8') ?></div>
      <nav class="sct-main-menu__nav sct-main-menu__nav--direct">
        <?php foreach($directNavItems as $item): ?>
          <?php if(!empty($item['disabled'])): ?>
          <span class="sct-main-menu__direct-item is-disabled" data-brand="<?= htmlspecialchars($item['brand'],ENT_QUOTES,'UTF-8') ?>" aria-disabled="true"><i class="bi <?= htmlspecialchars($item['icon'],ENT_QUOTES,'UTF-8') ?>"></i><span><?= htmlspecialchars($item['label'],ENT_QUOTES,'UTF-8') ?></span><small><?= htmlspecialchars(t('home_coming_soon'),ENT_QUOTES,'UTF-8') ?></small></span>
          <?php else: ?>
          <a class="sct-main-menu__direct-item" data-brand="<?= htmlspecialchars($item['brand'],ENT_QUOTES,'UTF-8') ?>" href="<?= htmlspecialchars($item['href'],ENT_QUOTES,'UTF-8') ?>" data-sct-menu-link><i class="bi <?= htmlspecialchars($item['icon'],ENT_QUOTES,'UTF-8') ?>"></i><span><?= htmlspecialchars($item['label'],ENT_QUOTES,'UTF-8') ?></span><i class="bi bi-chevron-right sct-main-menu__direct-arrow"></i></a>
          <?php endif; ?>
        <?php endforeach; ?>
      </nav>
      <?php if($managementNavItems): ?>
      <div class="sct-main-menu__section-title sct-main-menu__section-title--management"><?= htmlspecialchars(t('mgmt_title'),ENT_QUOTES,'UTF-8') ?></div>
      <nav class="sct-main-menu__nav">
        <?php foreach($managementNavItems as $item): ?>
        <a href="<?= htmlspecialchars($item['href'],ENT_QUOTES,'UTF-8') ?>" data-sct-menu-link><i class="bi <?= htmlspecialchars($item['icon'],ENT_QUOTES,'UTF-8') ?>"></i><span><?= htmlspecialchars($item['label'],ENT_QUOTES,'UTF-8') ?></span></a>
        <?php endforeach; ?>
      </nav>
      <?php endif; ?>
    </div>
    <div class="sct-main-menu__footer">
      <div class="sct-main-menu__section-title"><?= htmlspecialchars(t('nav_configuration'),ENT_QUOTES,'UTF-8') ?></div>
      <button type="button" class="sct-main-menu__row" data-sct-theme><span class="sct-settings-menu__row-icon"><i class="bi bi-moon-stars"></i></span><span><strong><?= htmlspecialchars(t('nav_appearance'),ENT_QUOTES,'UTF-8') ?></strong><small><?= htmlspecialchars(t('nav_theme_action'),ENT_QUOTES,'UTF-8') ?></small></span></button>
      <div class="sct-main-menu__language"><span><i class="bi bi-globe2"></i><?= htmlspecialchars(t('nav_language'),ENT_QUOTES,'UTF-8') ?></span><div><?php foreach($langNames as $code=>$name): ?><a class="<?= $code===$sctNavLang?'active':'' ?>" href="<?= htmlspecialchars(sctLangHref($code),ENT_QUOTES,'UTF-8') ?>" data-sct-menu-link><img src="<?= htmlspecialchars($sctNavbarBasePath.'images/flags/'.$flags[$code],ENT_QUOTES,'UTF-8') ?>" alt=""><span><?= htmlspecialchars($name,ENT_QUOTES,'UTF-8') ?></span></a><?php endforeach; ?></div></div>
      <a class="sct-main-menu__logout" href="<?= htmlspecialchars($sctNavbarBasePath.'logout.php',ENT_QUOTES,'UTF-8') ?>"><i class="bi bi-box-arrow-right"></i><span><?= htmlspecialchars(t('nav_logout'),ENT_QUOTES,'UTF-8') ?></span></a>
    </div>
  </aside>
  <div class="sct-menu-backdrop" data-sct-menu-backdrop hidden></div>
</header>
<script>window.SCT_NAV_I18N={mutualSaved:<?= json_encode(t('nav_mutuality_saved'),JSON_UNESCAPED_UNICODE) ?>,mutualError:<?= json_encode(t('nav_mutuality_error'),JSON_UNESCAPED_UNICODE) ?>};</script>
<script src="<?= htmlspecialchars($sctNavbarBasePath.'js/app-ui.js?v='.$ASSET_VERSION,ENT_QUOTES,'UTF-8') ?>"></script>
