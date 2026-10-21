<?php
/**
 * Contexto UX común para módulos internos SCT.
 *
 * Esta parcial ya no imprime el antiguo bloque visible "Tu vista".
 * Conserva únicamente metadatos de rol/modo para que la capa UX pueda:
 * - aplicar prioridades visuales por rol;
 * - añadir ayuda contextual (?) a los contenedores;
 * - mantener una única lógica transversal sin duplicar vistas.
 *
 * Variable opcional: $sctModuleMode = manage|personal|execute|report|history|permissions
 */
$sctModuleMode = isset($sctModuleMode) ? (string) $sctModuleMode : 'manage';
$sctModuleRoles = (isset($pdo) && $pdo instanceof PDO && function_exists('currentUserRoles')) ? currentUserRoles($pdo) : [];
$sctModuleRole = function_exists('primaryRoleName') ? (primaryRoleName($sctModuleRoles) ?: 'trabajador') : 'trabajador';

$allowedModes = ['manage', 'personal', 'execute', 'report', 'history', 'permissions'];
if (!in_array($sctModuleMode, $allowedModes, true)) {
    $sctModuleMode = 'manage';
}

$helpKey = 'module_help_' . $sctModuleMode;
$helpTemplate = function_exists('t') ? t($helpKey) : '';
$supportQuestion = function_exists('t') ? t('info_tip_more_doubts') : '¿Tienes más dudas?';
$supportLink = function_exists('t') ? t('info_tip_contact_support') : 'Contacta a soporte';
$infoLabel = function_exists('t') ? t('info_more_label') : 'Más información';
$closeLabel = function_exists('t') ? t('common_close') : 'Cerrar';
?>
<div class="sct-module-context sct-module-context--metadata"
     hidden
     aria-hidden="true"
     data-role="<?= htmlspecialchars($sctModuleRole, ENT_QUOTES, 'UTF-8') ?>"
     data-module-mode="<?= htmlspecialchars($sctModuleMode, ENT_QUOTES, 'UTF-8') ?>"
     data-help-template="<?= htmlspecialchars($helpTemplate, ENT_QUOTES, 'UTF-8') ?>"
     data-support-question="<?= htmlspecialchars($supportQuestion, ENT_QUOTES, 'UTF-8') ?>"
     data-support-link="<?= htmlspecialchars($supportLink, ENT_QUOTES, 'UTF-8') ?>"
     data-info-label="<?= htmlspecialchars($infoLabel, ENT_QUOTES, 'UTF-8') ?>"
     data-close-label="<?= htmlspecialchars($closeLabel, ENT_QUOTES, 'UTF-8') ?>"></div>
