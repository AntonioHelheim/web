<?php
require __DIR__ . '/common.php';
require __DIR__ . '/../../lib/repositorios/InduccionRepository.php';
require __DIR__ . '/../../lib/repositorios/AuditoriaRepository.php';

requireCapability($pdo, 'audits.edit');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    responderJSON(false, null, 'Método no permitido.', 405);
}

$input = json_decode(file_get_contents('php://input'), true);
if (!is_array($input)) $input = $_POST;
requireCsrfToken($input);

$idTest = filter_var($input['id_test'] ?? null, FILTER_VALIDATE_INT);
$name = trim((string) ($input['name'] ?? ''));
$description = trim((string) ($input['description'] ?? ''));
$attemptsAllowed = filter_var($input['attempts_allowed'] ?? null, FILTER_VALIDATE_INT);
$approvalPercentage = filter_var($input['approval_percentage'] ?? null, FILTER_VALIDATE_INT);
$from = DateTime::createFromFormat('Y-m-d', (string) ($input['effective_date_from'] ?? ''));
$until = DateTime::createFromFormat('Y-m-d', (string) ($input['effective_date_until'] ?? ''));

if (!$idTest || $name === '' || $description === '') {
    responderJSON(false, null, 'Datos de auditoría incompletos.', 400);
}
if (sctTextLength($name) > 50 || sctTextLength($description) > 255) {
    responderJSON(false, null, 'Nombre o descripción exceden el largo permitido.', 400);
}
if ($attemptsAllowed === false || $attemptsAllowed < 1 || $attemptsAllowed > 20 ||
    $approvalPercentage === false || $approvalPercentage < 1 || $approvalPercentage > 100) {
    responderJSON(false, null, 'Parámetros de evaluación no válidos.', 400);
}
if (!$from || !$until || $until < $from) {
    responderJSON(false, null, 'El rango de vigencia no es válido.', 400);
}

try {
    $test = cursoObtenerPorId($pdo, $idTest);
    if (!$test) responderJSON(false, null, 'Auditoría no encontrada.', 404);
    auditoriaAssertTest($test);
    auditoriaAssertCompanyAccess($pdo, (int) $test['id_company']);
    if (auditoriaTestTieneAsignaciones($pdo, $idTest)) {
        responderJSON(false, null, 'Esta auditoría ya tiene ejecuciones asignadas. Para preservar la trazabilidad, crea una nueva auditoría si necesitas cambiar su definición.', 409);
    }

    $stmt = $pdo->prepare(
        'SELECT 1 FROM company_test
         WHERE id_company = :id_company AND type = :type AND name = :name
           AND state = 1 AND id_test <> :id_test
         LIMIT 1'
    );
    $stmt->execute([
        'id_company' => (int) $test['id_company'],
        'type' => AUDITORIA_TIPO,
        'name' => $name,
        'id_test' => $idTest,
    ]);
    if ($stmt->fetchColumn()) {
        responderJSON(false, null, 'Ya existe otra auditoría activa con ese nombre.', 409);
    }

    cursoActualizar($pdo, $idTest, [
        'name' => $name,
        'description' => $description,
        'attempts_allowed' => $attemptsAllowed,
        'approval_percentage' => $approvalPercentage,
        'effective_date_from' => $from->format('Y-m-d 00:00:00'),
        'effective_date_until' => $until->format('Y-m-d 23:59:59'),
    ]);

    auditTrailLogChanges($pdo, (int)$test['id_company'], 'auditorias', 'company_test', $idTest, $test, [
        'name'=>$name,'description'=>$description,'attempts_allowed'=>$attemptsAllowed,'approval_percentage'=>$approvalPercentage,
        'effective_date_from'=>$from->format('Y-m-d 00:00:00'),'effective_date_until'=>$until->format('Y-m-d 23:59:59')
    ], 'update', $name, ['name','description','attempts_allowed','approval_percentage','effective_date_from','effective_date_until']);
    responderJSON(true, ['id_test' => $idTest], 'Auditoría actualizada.');
} catch (PDOException $e) {
    error_log('api/auditorias/auditorias-editar.php: ' . $e->getMessage());
    responderJSON(false, null, 'No se pudo actualizar la auditoría.', 500);
}
