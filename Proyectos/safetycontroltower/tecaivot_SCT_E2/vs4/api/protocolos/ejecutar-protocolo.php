<?php
require_once __DIR__ . '/common.php';
require_once __DIR__ . '/../../i18n.php';

requireCapabilityPage($pdo, 'protocols.execute', '../../acceso-denegado.php');
aplicarCabecerasSeguridad();
$embeddedActivityMode = isset($_GET['embedded']) && (string) $_GET['embedded'] === '1';
if ($embeddedActivityMode) {
    // El HUB Mi espacio sólo puede embeber actividades desde el mismo origen.
    header('X-Frame-Options: SAMEORIGIN');
}

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
    if (!protocoloAssignmentHasStarted($assignment)) {
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
<link rel="stylesheet" href="../../css/style.css?v=<?= htmlspecialchars($ASSET_VERSION, ENT_QUOTES, 'UTF-8') ?>&ux=20260920-p32-v51">
<link rel="stylesheet" href="../../css/sct-main-sections.css?v=20260923-p79">
<style>
.form-section{border:1px solid var(--border);border-radius:18px;background:#fff;padding:1.2rem;margin-bottom:1rem}.form-section.optional-off{opacity:.65}.question-field{padding:.85rem 0;border-bottom:1px solid var(--border)}.question-field:last-child{border-bottom:0}.required-mark{color:#AA2424}.submit-bar{position:sticky;bottom:0;z-index:15;background:rgba(248,250,252,.94);-webkit-backdrop-filter:blur(10px);backdrop-filter:blur(10px);border-top:1px solid var(--border);padding:1rem 0;margin-top:1rem}@media(max-width:599.98px){.form-section{padding:.9rem}}
</style>
</head>
<body class="sct-module-page sct-activity-mode sct-protocol-execution-page<?= $embeddedActivityMode ? ' sct-embedded-activity' : '' ?>">
<div class="container sct-main-shell sct-protocol-execution-shell" data-csrf-token="<?= $csrf ?>" data-assignment-id="<?= $assignment ? (int) $assignment['id_protocol_assignment'] : 0 ?>" data-activity-user="<?= htmlspecialchars(hash('sha256', (string) ($_SESSION['user_email'] ?? '')), ENT_QUOTES, 'UTF-8') ?>">
<?php
        $sctNavbarBasePath = '../../';
        $sctNavbarBackHref = './mis-protocolos.php';
        if (!$embeddedActivityMode) { require __DIR__ . '/../../partials/app-navbar.php'; }
        ?>

        <section class="sct-main-hero sct-protocol-execution-hero" aria-labelledby="protocol-execution-title">
            <div class="sct-main-hero__copy">
                <span class="section-label sct-main-pill"><i class="bi bi-clipboard2-pulse" aria-hidden="true"></i><?= htmlspecialchars(t('mgmt_filter_personal'), ENT_QUOTES, 'UTF-8') ?></span>
                <h1 id="protocol-execution-title" class="section-title sct-main-title"><?= htmlspecialchars($assignment['protocol_name'] ?? t('protocols_execution_title'),ENT_QUOTES,'UTF-8') ?></h1>
                <p class="section-description sct-main-intro"><?= htmlspecialchars(t('protocols_execution_intro'),ENT_QUOTES,'UTF-8') ?> <strong><?= $userEmail ?></strong>.</p>
                <?php if ($assignment): ?>
                    <?php
                        $protocolDueRaw = (string) ($assignment['next_due_at'] ?? '');
                        $protocolDueDisplay = $protocolDueRaw;
                        if ($protocolDueRaw !== '') {
                            $protocolDueTs = strtotime($protocolDueRaw);
                            if ($protocolDueTs !== false) {
                                $protocolDueDisplay = date('d/m/Y H:i:s', $protocolDueTs);
                            }
                        }
                    ?>
                    <div class="sct-protocol-execution-meta" aria-label="<?= htmlspecialchars(t('protocols_assignment_detail'), ENT_QUOTES, 'UTF-8') ?>">
                        <span class="sct-protocol-meta-item">
                            <i class="bi bi-building" aria-hidden="true"></i>
                            <span class="sct-protocol-meta-item__copy"><small><?= htmlspecialchars(t('protocols_company'), ENT_QUOTES, 'UTF-8') ?></small><strong><?= htmlspecialchars((string)$assignment['company_name'],ENT_QUOTES,'UTF-8') ?></strong></span>
                        </span>
                        <span class="sct-protocol-meta-item">
                            <i class="bi bi-calendar-event" aria-hidden="true"></i>
                            <span class="sct-protocol-meta-item__copy"><small><?= htmlspecialchars(t('protocols_due'), ENT_QUOTES, 'UTF-8') ?></small><strong><?= htmlspecialchars($protocolDueDisplay, ENT_QUOTES, 'UTF-8') ?></strong></span>
                        </span>
                        <?php if ((int)$assignment['is_overdue']===1): ?>
                            <span class="sct-protocol-meta-item is-danger"><i class="bi bi-exclamation-triangle" aria-hidden="true"></i><span class="sct-protocol-meta-item__copy"><small><?= htmlspecialchars(t('protocols_due'), ENT_QUOTES, 'UTF-8') ?></small><strong><?= htmlspecialchars(t('protocols_overdue'),ENT_QUOTES,'UTF-8') ?></strong></span></span>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        </section>
<?php $sctModuleMode = 'execute'; require __DIR__ . '/../../partials/module-context.php'; ?>

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
<div class="submit-bar"><div class="sct-activity-actions"><?php if (!$embeddedActivityMode): ?><a href="./mis-protocolos.php" id="executionExit" class="btn btn-outline-custom"><i class="bi bi-box-arrow-left"></i> <?= htmlspecialchars(t('activity_exit'),ENT_QUOTES,'UTF-8') ?></a><?php endif; ?><button id="executionSave" type="button" class="btn btn-outline-custom"><i class="bi bi-save"></i> <?= htmlspecialchars(t('activity_save_draft'),ENT_QUOTES,'UTF-8') ?></button><button id="executionSubmit" type="submit" class="btn btn-primary-custom"><i class="bi bi-send"></i> <?= htmlspecialchars(t('protocols_submit_execution'),ENT_QUOTES,'UTF-8') ?></button></div></div>
</form>
<?php endif; ?>
</div>
<script id="protocolExecutionI18n" type="application/json"><?= json_encode(['success'=>t('protocols_execution_success'),'error'=>t('protocols_error_response'),'save'=>t('activity_draft_saved'),'restore'=>t('activity_draft_restored'),'required'=>t('activity_complete_before_submit'),'confirm'=>t('activity_confirm_submit'),'file'=>t('activity_file_not_saved')],JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES) ?></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>
<script src="../../js/sct-activity-session.js?v=20260920-p32-v51"></script>
<script src="../../js/protocolos-ejecutar.js?v=<?= htmlspecialchars($ASSET_VERSION, ENT_QUOTES, 'UTF-8') ?>"></script>
<script src="../../js/lang-switcher.js?v=<?= htmlspecialchars($ASSET_VERSION, ENT_QUOTES, 'UTF-8') ?>"></script>

    <?php if (!$embeddedActivityMode) { require __DIR__ . '/../../partials/app-footer.php'; } ?>

<script src="../../js/sct-module-ui.js?v=20260920-p43"></script>
</body></html>
