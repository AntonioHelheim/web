<?php
require __DIR__ . '/common.php';
require __DIR__ . '/../../lib/repositorios/InduccionRepository.php';

requireCapability($pdo, 'audits.create');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    responderJSON(false, null, 'Método no permitido.', 405);
}

$input = json_decode(file_get_contents('php://input'), true);
if (!is_array($input)) {
    $input = $_POST;
}
requireCsrfToken($input);

$idCompany = auditoriaResolveCompanyId($pdo, isset($input['id_company']) ? (int) $input['id_company'] : null);
$name = trim((string) ($input['name'] ?? ''));
$description = trim((string) ($input['description'] ?? ''));
$attemptsAllowed = filter_var($input['attempts_allowed'] ?? null, FILTER_VALIDATE_INT);
$approvalPercentage = filter_var($input['approval_percentage'] ?? null, FILTER_VALIDATE_INT);
$from = DateTime::createFromFormat('Y-m-d', (string) ($input['effective_date_from'] ?? ''));
$until = DateTime::createFromFormat('Y-m-d', (string) ($input['effective_date_until'] ?? ''));

if ($name === '' || $description === '') {
    responderJSON(false, null, 'Nombre y descripción son obligatorios.', 400);
}
if (sctTextLength($name) > 50 || sctTextLength($description) > 255) {
    responderJSON(false, null, 'Nombre o descripción exceden el largo permitido.', 400);
}
if ($attemptsAllowed === false || $attemptsAllowed < 1 || $attemptsAllowed > 20) {
    responderJSON(false, null, 'Los intentos permitidos deben estar entre 1 y 20.', 400);
}
if ($approvalPercentage === false || $approvalPercentage < 1 || $approvalPercentage > 100) {
    responderJSON(false, null, 'El porcentaje mínimo debe estar entre 1 y 100.', 400);
}
if (!$from || !$until || $until < $from) {
    responderJSON(false, null, 'El rango de vigencia no es válido.', 400);
}

try {
    $stmt = $pdo->prepare(
        'SELECT 1 FROM company_test
         WHERE id_company = :id_company AND type = :type AND name = :name AND state = 1
         LIMIT 1'
    );
    $stmt->execute(['id_company' => $idCompany, 'type' => AUDITORIA_TIPO, 'name' => $name]);
    if ($stmt->fetchColumn()) {
        responderJSON(false, null, 'Ya existe una auditoría activa con ese nombre en la empresa.', 409);
    }

    $id = cursoCrear($pdo, [
        'id_company' => $idCompany,
        'name' => $name,
        'type' => AUDITORIA_TIPO,
        'description' => $description,
        'attempts_allowed' => $attemptsAllowed,
        'approval_percentage' => $approvalPercentage,
        'effective_date_from' => $from->format('Y-m-d 00:00:00'),
        'effective_date_until' => $until->format('Y-m-d 23:59:59'),
    ], (string) currentUserId());

    auditTrailLogChanges($pdo, $idCompany, 'auditorias', 'company_test', $id, [], [
        'name'=>$name,'type'=>AUDITORIA_TIPO,'description'=>$description,'attempts_allowed'=>$attemptsAllowed,
        'approval_percentage'=>$approvalPercentage,'effective_date_from'=>$from->format('Y-m-d 00:00:00'),
        'effective_date_until'=>$until->format('Y-m-d 23:59:59'),'state'=>1
    ], 'create', $name);
    responderJSON(true, ['id_test' => $id], 'Auditoría creada correctamente.', 201);
} catch (PDOException $e) {
    error_log('api/auditorias/auditorias-crear.php: ' . $e->getMessage());
    responderJSON(false, null, 'No se pudo crear la auditoría.', 500);
}
