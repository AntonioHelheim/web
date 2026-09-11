<?php
require_once __DIR__ . '/common.php';
require_once __DIR__ . '/../../i18n.php';

requireCapabilityPage($pdo, 'protocols.execute', '../../acceso-denegado.php');
aplicarCabecerasSeguridad();

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$idAssignment = filter_input(INPUT_GET, 'id_assignment', FILTER_VALIDATE_INT);
$assignment = null;
$forms = [];
$error = null;

try {
    protocoloRequireSchema($pdo);
    formularioRequireSchema($pdo);
    if (!$idAssignment) {
        throw new RuntimeException('Asignación no válida.');
    }

    $assignment = protocoloAsignacionObtener($pdo, (int) $idAssignment);
    if (!$assignment) {
        throw new RuntimeException('Asignación no encontrada.');
    }
    if (!protocoloUserCanExecuteAssignment($pdo, $assignment)) {
        throw new RuntimeException('Esta asignación no corresponde a tu cuenta.');
    }
    if ((string) $assignment['state'] !== 'activa' || (int) $assignment['protocol_state'] !== 1) {
        throw new RuntimeException(t('protocols_execution_not_available'));
    }
    if (!protocoloAssignmentProtocolIsEffective($assignment)) {
        throw new RuntimeException(t('protocols_execution_not_available'));
    }
    if (protocoloAsignacionTieneRevisionPendiente($pdo, (int) $idAssignment)) {
        throw new RuntimeException(t('protocols_pending_review'));
    }

    $links = protocoloFormularios($pdo, (int) $assignment['id_protocol'], (int) $assignment['id_company']);
    foreach ($links as $link) {
        if ((int) $link['form_state'] !== 1) continue;
        $fields = formularioCampos($pdo, (int) $link['id_form']);
        if (!$fields) continue;
        $link['fields'] = $fields;
        $forms[] = $link;
    }
    if (!$forms) {
        throw new RuntimeException('El protocolo no tiene formularios activos con campos configurados.');
    }
} catch (Throwable $e) {
    $error = protocoloMigrationMessage($e)
        ? 'Debes aplicar la actualización SQL de Protocolos MINSAL.'
        : $e->getMessage();
}

$csrf = htmlspecialchars((string) $_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8');
$userEmail = htmlspecialchars((string) ($_SESSION['user_email'] ?? ''), ENT_QUOTES, 'UTF-8');

function protocolFieldOptions(array $field): array
{
    $raw = trim((string) ($field['options'] ?? ''));
    if ($raw === '') return [];
    $out = [];
    foreach (explode('|', $raw) as $part) {
        $part = trim($part);
        if ($part !== '' && !in_array($part, $out, true)) $out[] = $part;
    }
    return $out;
}
?>
<!DOCTYPE html>
<html lang="<?= htmlspecialchars(idiomaActual(), ENT_QUOTES, 'UTF-8') ?>">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title><?= htmlspecialchars(t('protocols_execution_title'), ENT_QUOTES, 'UTF-8') ?> - Safety Control Tower</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-LN+7fdVzj6u52u30Kp6M/trliBMCMKTyK833zpbD+pXdCLuTusPj697FH4R/5mcr" crossorigin="anonymous">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
<link rel="stylesheet" href="../../css/style.css?v=<?= htmlspecialchars($ASSET_VERSION, ENT_QUOTES, 'UTF-8') ?>">
<style>
.welcome-topbar{display:flex;justify-content:space-between;align-items:center;gap:1rem;padding:1.25rem 0;border-bottom:1px solid rgba(0,0,0,.08)}.welcome-topbar .brand-symbol img{height:32px}.topbar-actions{display:flex;gap:.5rem;align-items:center;flex-wrap:wrap;justify-content:flex-end}.welcome-hero{padding:2.7rem 0 1.3rem}.welcome-greeting-icon{font-size:2.4rem;color:var(--primary);margin-bottom:.6rem}.form-section{border:1px solid var(--border);border-radius:var(--radius-md);background:#fff;padding:1.2rem;margin-bottom:1rem}.form-section.optional-off{opacity:.65}.question-field{padding:.85rem 0;border-bottom:1px solid var(--border)}.question-field:last-child{border-bottom:0}.required-mark{color:#dc2626}.meta-strip{display:flex;gap:.55rem;flex-wrap:wrap;justify-content:center}.meta-pill{display:inline-flex;align-items:center;gap:.3rem;border-radius:999px;padding:.28rem .6rem;background:rgba(0,163,244,.1);color:var(--primary-dark);font-size:.76rem;font-weight:700}.meta-pill.danger{background:rgba(220,38,38,.1);color:#b91c1c}.submit-bar{position:sticky;bottom:0;z-index:15;background:rgba(248,250,252,.94);backdrop-filter:blur(10px);border-top:1px solid var(--border);padding:1rem 0;margin-top:1rem}@media(max-width:767.98px){.welcome-topbar{align-items:flex-start}.topbar-actions{max-width:72%}.form-section{padding:1rem}}
</style>
</head>
<body>
<div class="container" data-csrf-token="<?= $csrf ?>" data-assignment-id="<?= $assignment ? (int) $assignment['id_protocol_assignment'] : 0 ?>">
<div class="welcome-topbar"><div class="brand-wrapper"><div class="brand-symbol"><img src="../../images/logos/Logo-SCT-white.png" alt="Safety Control Tower"></div></div><div class="topbar-actions"><select id="pageLanguageSelect" class="form-select form-select-sm" aria-label="<?= htmlspecialchars(t('common_language'),ENT_QUOTES,'UTF-8') ?>"><?php foreach(idiomasDisponiblesConNombre() as $code=>$name): ?><option value="<?= htmlspecialchars($code,ENT_QUOTES,'UTF-8') ?>" <?= $code===idiomaActual()?'selected':'' ?>><?= htmlspecialchars($name,ENT_QUOTES,'UTF-8') ?></option><?php endforeach; ?></select><a href="./mis-protocolos.php" class="btn btn-outline-custom btn-sm"><?= htmlspecialchars(t('mgmt_back'),ENT_QUOTES,'UTF-8') ?> <i class="bi bi-arrow-left"></i></a><a href="../../logout.php" class="btn btn-outline-custom btn-sm"><?= htmlspecialchars(t('common_logout'),ENT_QUOTES,'UTF-8') ?> <i class="bi bi-box-arrow-right"></i></a></div></div>

<section class="welcome-hero text-center"><div class="welcome-greeting-icon"><i class="bi bi-clipboard2-pulse"></i></div><span class="section-label">SAFETY CONTROL TOWER</span><h1 class="section-title"><?= htmlspecialchars($assignment['protocol_name'] ?? t('protocols_execution_title'),ENT_QUOTES,'UTF-8') ?></h1><p class="section-description intro-description-centered"><?= htmlspecialchars(t('protocols_execution_intro'),ENT_QUOTES,'UTF-8') ?> <strong><?= $userEmail ?></strong>.</p><?php if ($assignment): ?><div class="meta-strip"><span class="meta-pill"><i class="bi bi-building"></i><?= htmlspecialchars((string)$assignment['company_name'],ENT_QUOTES,'UTF-8') ?></span><span class="meta-pill"><i class="bi bi-calendar-event"></i><?= htmlspecialchars((string)$assignment['next_due_at'],ENT_QUOTES,'UTF-8') ?></span><?php if ((int)$assignment['is_overdue']===1): ?><span class="meta-pill danger"><?= htmlspecialchars(t('protocols_overdue'),ENT_QUOTES,'UTF-8') ?></span><?php endif; ?></div><?php endif; ?></section>

<?php if ($error): ?>
<div class="alert alert-danger"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
<?php else: ?>
<div id="executionAlert" class="alert d-none" role="alert" aria-live="polite"></div>
<form id="protocolExecutionForm" enctype="multipart/form-data" novalidate>
<?php foreach ($forms as $formIndex=>$link): $required=(int)$link['is_required']===1; ?>
<section class="form-section" data-protocol-form="<?= (int)$link['id_protocol_form'] ?>" data-required="<?= $required?'1':'0' ?>">
<div class="d-flex justify-content-between align-items-start gap-3 flex-wrap mb-2"><div><span class="section-label"><?= htmlspecialchars($required?t('protocols_required_form'):t('protocols_optional_form'),ENT_QUOTES,'UTF-8') ?></span><h2 class="h5 mb-1"><?= htmlspecialchars((string)$link['form_name'],ENT_QUOTES,'UTF-8') ?></h2><?php if(!empty($link['form_description'])): ?><p class="text-muted small mb-0"><?= htmlspecialchars((string)$link['form_description'],ENT_QUOTES,'UTF-8') ?></p><?php endif; ?></div><?php if(!$required): ?><div class="form-check form-switch"><input class="form-check-input protocol-form-toggle" type="checkbox" role="switch" id="include_form_<?= (int)$link['id_protocol_form'] ?>" checked><label class="form-check-label" for="include_form_<?= (int)$link['id_protocol_form'] ?>"><?= htmlspecialchars(t('protocols_optional'),ENT_QUOTES,'UTF-8') ?></label></div><?php else: ?><span class="badge text-bg-primary"><?= htmlspecialchars(t('protocols_required'),ENT_QUOTES,'UTF-8') ?></span><?php endif; ?></div>
<div class="protocol-fields">
<?php foreach ($link['fields'] as $field): $id=(int)$field['id_field'];$type=(string)$field['field_type'];$isReq=(int)$field['is_required']===1;$options=protocolFieldOptions($field); ?>
<div class="question-field" data-field-id="<?= $id ?>" data-field-type="<?= htmlspecialchars($type,ENT_QUOTES,'UTF-8') ?>">
<label class="form-label fw-semibold"<?= !in_array($type,['checkbox'],true)?' for="field_'.$id.'"':'' ?>><?= htmlspecialchars((string)$field['label'],ENT_QUOTES,'UTF-8') ?><?php if($isReq): ?> <span class="required-mark">*</span><?php endif; ?></label>
<?php if($type==='text'): ?><textarea id="field_<?= $id ?>" class="form-control protocol-answer" rows="2" data-field-id="<?= $id ?>" data-field-type="text" <?= $isReq?'required':'' ?>></textarea>
<?php elseif($type==='number'): ?><input id="field_<?= $id ?>" type="number" step="any" class="form-control protocol-answer" data-field-id="<?= $id ?>" data-field-type="number" <?= $isReq?'required':'' ?>>
<?php elseif($type==='date'): ?><input id="field_<?= $id ?>" type="date" class="form-control protocol-answer" data-field-id="<?= $id ?>" data-field-type="date" <?= $isReq?'required':'' ?>>
<?php elseif($type==='select'): ?><select id="field_<?= $id ?>" class="form-select protocol-answer" data-field-id="<?= $id ?>" data-field-type="select" <?= $isReq?'required':'' ?>><option value="">—</option><?php foreach($options as $option): ?><option value="<?= htmlspecialchars($option,ENT_QUOTES,'UTF-8') ?>"><?= htmlspecialchars($option,ENT_QUOTES,'UTF-8') ?></option><?php endforeach; ?></select>
<?php elseif($type==='checkbox'): ?><?php foreach($options as $oi=>$option): $cid='field_'.$id.'_'.$oi; ?><div class="form-check"><input id="<?= $cid ?>" type="checkbox" class="form-check-input protocol-checkbox" data-field-id="<?= $id ?>" value="<?= htmlspecialchars($option,ENT_QUOTES,'UTF-8') ?>"><label class="form-check-label" for="<?= $cid ?>"><?= htmlspecialchars($option,ENT_QUOTES,'UTF-8') ?></label></div><?php endforeach; ?>
<?php elseif($type==='file'): ?><input id="field_<?= $id ?>" type="file" name="file_<?= $id ?>" class="form-control protocol-file" data-field-id="<?= $id ?>" accept=".jpg,.jpeg,.png,.webp,.pdf,.txt,.doc,.docx,.xls,.xlsx" <?= $isReq?'required':'' ?>><div class="form-text">JPG, PNG, WebP, PDF, TXT, DOC/DOCX, XLS/XLSX · máx. 5 MB</div>
<?php endif; ?>
</div>
<?php endforeach; ?>
</div>
</section>
<?php endforeach; ?>
<div class="submit-bar"><div class="d-flex justify-content-end gap-2"><a href="./mis-protocolos.php" class="btn btn-outline-custom"><?= htmlspecialchars(t('my_forms_cancel'),ENT_QUOTES,'UTF-8') ?></a><button id="executionSubmit" type="submit" class="btn btn-primary-custom"><i class="bi bi-send"></i> <?= htmlspecialchars(t('protocols_submit_execution'),ENT_QUOTES,'UTF-8') ?></button></div></div>
</form>
<?php endif; ?>
</div>
<script id="protocolExecutionI18n" type="application/json"><?= json_encode(['success'=>t('protocols_execution_success'),'error'=>t('protocols_error_response')],JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES) ?></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>
<script src="../../js/protocolos-ejecutar.js?v=<?= htmlspecialchars($ASSET_VERSION, ENT_QUOTES, 'UTF-8') ?>"></script>
<script src="../../js/lang-switcher.js?v=<?= htmlspecialchars($ASSET_VERSION, ENT_QUOTES, 'UTF-8') ?>"></script>
</body></html>
