<?php
/**
 * Reglas compartidas de Protocolos MINSAL (Etapa 2).
 */
require_once __DIR__ . '/../../session_bootstrap.php';
require_once __DIR__ . '/../../lib/db.php';
require_once __DIR__ . '/../../lib/auth.php';
require_once __DIR__ . '/../../lib/audit.php';
require_once __DIR__ . '/../../lib/repositorios/ProtocoloRepository.php';
require_once __DIR__ . '/../../lib/repositorios/FormularioRepository.php';

const PROTOCOLO_ASSIGNMENT_STATES = ['activa','suspendida','cerrada','cancelada'];
const PROTOCOLO_RESULTS = ['pendiente_revision','conforme','observado','no_conforme','no_aplica'];
const PROTOCOLO_TRACKING_STATES = ['pendiente','en_curso','completado','cancelado'];
const PROTOCOLO_RECURRENCE_UNITS = ['none','days','weeks','months','years'];

function protocoloIsGlobalAdmin(PDO $pdo): bool
{
    return currentUserHasCapability($pdo, 'companies.view_all');
}

function protocoloRequireGestionApi(PDO $pdo): void
{
    requireCapability($pdo, 'protocols.manage');
}

function protocoloRequireGestionPage(PDO $pdo, string $redirectTo='../../acceso-denegado.php'): void
{
    requireCapabilityPage($pdo, 'protocols.manage', $redirectTo);
}

function protocoloCurrentCompany(PDO $pdo): int
{
    $id = currentUserCompanyId($pdo);
    if (!$id) {
        responderJSON(false, null, 'Tu cuenta no tiene una empresa asociada.', 403);
    }
    return $id;
}

function protocoloResolveCompany(PDO $pdo, ?int $requested): int
{
    if (protocoloIsGlobalAdmin($pdo)) {
        if (!$requested || $requested <= 0) {
            responderJSON(false, null, 'Selecciona una empresa para continuar.', 400);
        }
        return $requested;
    }
    return protocoloCurrentCompany($pdo);
}

function protocoloResolveCatalogOwner(PDO $pdo, ?int $requested): ?int
{
    if (protocoloIsGlobalAdmin($pdo)) {
        return ($requested && $requested > 0) ? $requested : null;
    }
    return protocoloCurrentCompany($pdo);
}

function protocoloMigrationMessage(Throwable $e): bool
{
    return $e->getMessage() === 'MIGRATION_REQUIRED_PROTOCOLS';
}

function protocoloJsonObjectOrNull($raw, string $fieldLabel='Parámetros'): ?string
{
    if ($raw === null) {
        return null;
    }

    // JSON de protocolos representa siempre un objeto clave/valor. Se evita
    // aceptar listas JSON porque parameters y parameter_overrides son mapas de
    // configuración, no colecciones posicionales.
    if (is_array($raw)) {
        if ($raw === []) {
            return '{}';
        }
        if (array_values($raw) === $raw) {
            responderJSON(false, null, $fieldLabel . ' debe ser un objeto JSON, no una lista.', 400);
        }
        $decodedObject = (object) $raw;
    } else {
        $text = trim((string) $raw);
        if ($text === '') {
            return null;
        }
        $decodedObject = json_decode($text);
        if (!is_object($decodedObject) || json_last_error() !== JSON_ERROR_NONE) {
            responderJSON(false, null, $fieldLabel . ' debe contener un objeto JSON válido.', 400);
        }
    }

    $json = json_encode($decodedObject, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    if (!is_string($json)) {
        responderJSON(false, null, 'No fue posible serializar ' . strtolower($fieldLabel) . '.', 400);
    }
    return $json;
}

function protocoloNullableText($value, int $maxLength, string $label): ?string
{
    $value = trim((string) ($value ?? ''));
    if ($value === '') {
        return null;
    }
    if (sctTextLength($value) > $maxLength) {
        responderJSON(false, null, $label . ' admite hasta ' . $maxLength . ' caracteres.', 400);
    }
    return $value;
}

function protocoloDateOrNull($value, string $label): ?string
{
    $value = trim((string) ($value ?? ''));
    if ($value === '') {
        return null;
    }
    $d = DateTime::createFromFormat('Y-m-d', $value);
    if (!$d || $d->format('Y-m-d') !== $value) {
        responderJSON(false, null, $label . ' no es una fecha válida.', 400);
    }
    return $value;
}

function protocoloDateTime(string $value, string $label): string
{
    $value = trim($value);
    $formats = ['Y-m-d\TH:i', 'Y-m-d H:i:s', 'Y-m-d H:i'];
    foreach ($formats as $format) {
        $d = DateTime::createFromFormat($format, $value);
        if ($d && $d->format($format) === $value) {
            return $d->format('Y-m-d H:i:s');
        }
    }
    responderJSON(false, null, $label . ' no es una fecha/hora válida.', 400);
}

function protocoloValidateDefinitionPayload(array $input): array
{
    $code = strtoupper(trim((string) ($input['code'] ?? '')));
    $name = trim((string) ($input['name'] ?? ''));
    $description = protocoloNullableText($input['description'] ?? null, 5000, 'Descripción');
    $version = filter_var($input['version'] ?? 1, FILTER_VALIDATE_INT);
    $authority = protocoloNullableText($input['authority'] ?? null, 100, 'Autoridad');
    $normativeReference = protocoloNullableText(
        $input['normative_reference'] ?? null,
        255,
        'Referencia normativa'
    );
    $sourceUrl = protocoloNullableText($input['source_url'] ?? null, 500, 'Fuente');
    $effectiveFrom = protocoloDateOrNull($input['effective_date_from'] ?? null, 'Vigencia desde');
    $effectiveUntil = protocoloDateOrNull($input['effective_date_until'] ?? null, 'Vigencia hasta');
    $parameters = protocoloJsonObjectOrNull($input['parameters'] ?? null, 'Parámetros');

    if ($code === '' || sctTextLength($code) > 50 || !preg_match('/^[A-Z0-9._-]+$/', $code)) {
        responderJSON(false, null, 'El código es obligatorio y solo admite letras, números, punto, guion y guion bajo.', 400);
    }
    if ($name === '' || sctTextLength($name) > 150) {
        responderJSON(false, null, 'El nombre es obligatorio y admite hasta 150 caracteres.', 400);
    }
    if ($version === false || $version < 1 || $version > 9999) {
        responderJSON(false, null, 'La versión no es válida.', 400);
    }
    if ($sourceUrl !== null) {
        if (!filter_var($sourceUrl, FILTER_VALIDATE_URL)) {
            responderJSON(false, null, 'La URL de la fuente no es válida.', 400);
        }
        $scheme = strtolower((string) parse_url($sourceUrl, PHP_URL_SCHEME));
        if (!in_array($scheme, ['http','https'], true)) {
            responderJSON(false, null, 'La fuente debe usar una URL HTTP o HTTPS.', 400);
        }
    }
    if ($effectiveFrom && $effectiveUntil && $effectiveUntil < $effectiveFrom) {
        responderJSON(false, null, 'La fecha final de vigencia no puede ser anterior a la inicial.', 400);
    }

    return [
        'code' => $code,
        'name' => $name,
        'description' => $description,
        'version' => (int) $version,
        'authority' => $authority,
        'normativeReference' => $normativeReference,
        'sourceUrl' => $sourceUrl,
        'effectiveFrom' => $effectiveFrom,
        'effectiveUntil' => $effectiveUntil,
        'parameters' => $parameters,
    ];
}

function protocoloAssertDefinitionManageable(PDO $pdo, array $protocol, ?int $implementationCompany=null): void
{
    if (protocoloIsGlobalAdmin($pdo)) {
        return;
    }

    $own = protocoloCurrentCompany($pdo);
    $owner = $protocol['id_company'] ?? null;

    if ($owner !== null && (int) $owner === $own) {
        return;
    }

    // Una empresa puede implementar (formularios/asignaciones) un protocolo
    // global, pero nunca editar su definición maestra.
    if ($owner === null && $implementationCompany === $own) {
        return;
    }

    responderJSON(false, null, 'No tienes permisos para administrar este protocolo.', 403);
}

function protocoloAssertDefinitionEditable(PDO $pdo, array $protocol): void
{
    $owner = $protocol['id_company'] ?? null;

    if (!protocoloIsGlobalAdmin($pdo)) {
        if ($owner === null || (int) $owner !== protocoloCurrentCompany($pdo)) {
            responderJSON(false, null, 'Las plantillas globales solo pueden ser editadas por Administrador Completo.', 403);
        }
    }

    $scope = $owner !== null ? (int) $owner : null;
    if (protocoloTieneAsignaciones($pdo, (int) $protocol['id_protocol'], $scope)) {
        responderJSON(
            false,
            null,
            'El protocolo ya tiene asignaciones y su definición quedó bloqueada. Crea una nueva versión para modificarla.',
            409
        );
    }
}

function protocoloImplementationScopeForForm(PDO $pdo, array $protocol, int $company): ?int
{
    $owner = $protocol['id_company'] ?? null;

    if ($owner !== null) {
        if ((int) $owner !== $company) {
            responderJSON(false, null, 'El protocolo pertenece a otra empresa.', 403);
        }
        return $company;
    }

    // Para protocolos globales, una implementación hecha desde una empresa
    // queda explícitamente acotada a esa empresa.
    return $company;
}

function protocoloValidateCompanyEntity(
    PDO $pdo,
    string $table,
    string $idColumn,
    int $id,
    int $company,
    string $label
): void {
    if (!preg_match('/^[A-Za-z0-9_]+$/', $table) || !preg_match('/^[A-Za-z0-9_]+$/', $idColumn)) {
        throw new RuntimeException('Configuración de entidad inválida.');
    }

    $stmt = $pdo->prepare(
        'SELECT COUNT(*) FROM ' . $table
        . ' WHERE ' . $idColumn . '=:id AND id_company=:id_company AND state=1'
    );
    $stmt->execute(['id' => $id, 'id_company' => $company]);

    if ((int) $stmt->fetchColumn() !== 1) {
        responderJSON(false, null, $label . ' no pertenece a la empresa seleccionada o está inactivo.', 400);
    }
}

function protocoloValidateAssignmentPayload(PDO $pdo, array $input, array $protocol, int $company): array
{
    if ((int) ($protocol['state'] ?? 0) !== 1) {
        responderJSON(false, null, 'El protocolo está inactivo y no admite nuevas asignaciones.', 409);
    }

    $idCenter = filter_var($input['id_company_center'] ?? null, FILTER_VALIDATE_INT);
    $idProject = filter_var($input['id_project'] ?? null, FILTER_VALIDATE_INT);
    $idWorker = filter_var($input['id_worker'] ?? null, FILTER_VALIDATE_INT);
    $responsible = trim((string) ($input['responsible_user'] ?? ''));

    $startAt = protocoloDateTime((string) ($input['start_at'] ?? ''), 'Fecha de inicio');
    $nextDueAt = protocoloDateTime((string) ($input['next_due_at'] ?? ''), 'Próximo vencimiento');

    if ($nextDueAt < $startAt) {
        responderJSON(false, null, 'El vencimiento no puede ser anterior a la fecha de inicio.', 400);
    }

    $startDate = substr($startAt, 0, 10);
    $effectiveFrom = (string) ($protocol['effective_date_from'] ?? '');
    $effectiveUntil = (string) ($protocol['effective_date_until'] ?? '');
    if ($effectiveFrom !== '' && $startDate < $effectiveFrom) {
        responderJSON(false, null, 'La asignación no puede comenzar antes de la vigencia del protocolo.', 400);
    }
    if ($effectiveUntil !== '' && $startDate > $effectiveUntil) {
        responderJSON(false, null, 'La asignación comienza después del término de vigencia del protocolo.', 400);
    }

    $unit = strtolower(trim((string) ($input['recurrence_unit'] ?? 'none')));
    if (!in_array($unit, PROTOCOLO_RECURRENCE_UNITS, true)) {
        responderJSON(false, null, 'La recurrencia no es válida.', 400);
    }

    $interval = filter_var($input['recurrence_interval'] ?? null, FILTER_VALIDATE_INT);
    if ($unit === 'none') {
        $interval = null;
    } elseif ($interval === false || $interval < 1 || $interval > 1200) {
        responderJSON(false, null, 'El intervalo de recurrencia debe ser mayor que cero.', 400);
    }

    if ($idCenter) {
        protocoloValidateCompanyEntity($pdo, 'company_center', 'id_company_center', (int) $idCenter, $company, 'El centro');
    }
    if ($idProject) {
        protocoloValidateCompanyEntity($pdo, 'projects', 'id_project', (int) $idProject, $company, 'El proyecto');
    }
    if ($idWorker) {
        protocoloValidateCompanyEntity($pdo, 'workers', 'id_worker', (int) $idWorker, $company, 'El trabajador');
    }

    if ($responsible === '') {
        responderJSON(false, null, 'Selecciona un usuario responsable.', 400);
    }
    $stmt = $pdo->prepare(
        'SELECT COUNT(*) FROM users WHERE id_users=:id_users AND id_company=:id_company AND state=1'
    );
    $stmt->execute([
        'id_users' => $responsible,
        'id_company' => $company,
    ]);
    if ((int) $stmt->fetchColumn() !== 1) {
        responderJSON(false, null, 'El responsable no pertenece a la empresa o está inactivo.', 400);
    }

    $forms = protocoloFormularios($pdo, (int) $protocol['id_protocol'], $company);
    $usable = array_values(array_filter($forms, function ($row) {
        return (int) ($row['form_state'] ?? 0) === 1
            && (int) ($row['field_count'] ?? 0) > 0;
    }));
    if (!$usable) {
        responderJSON(
            false,
            null,
            'Vincula al menos un formulario activo con campos antes de crear una asignación.',
            409
        );
    }

    $overrides = protocoloJsonObjectOrNull(
        $input['parameter_overrides'] ?? null,
        'Parámetros específicos'
    );
    $notes = protocoloNullableText($input['notes'] ?? null, 10000, 'Notas');

    return [
        'idCenter' => $idCenter ? (int) $idCenter : null,
        'idProject' => $idProject ? (int) $idProject : null,
        'idWorker' => $idWorker ? (int) $idWorker : null,
        'responsible' => $responsible,
        'startAt' => $startAt,
        'nextDueAt' => $nextDueAt,
        'unit' => $unit,
        'interval' => $interval !== null ? (int) $interval : null,
        'overrides' => $overrides,
        'notes' => $notes,
    ];
}

function protocoloAssignmentProtocolIsEffective(array $assignment, ?DateTime $when=null): bool
{
    $when = $when ?: new DateTime('now');
    $date = $when->format('Y-m-d');
    $from = trim((string) ($assignment['effective_date_from'] ?? ''));
    $until = trim((string) ($assignment['effective_date_until'] ?? ''));
    if ($from !== '' && $date < $from) return false;
    if ($until !== '' && $date > $until) return false;
    return true;
}

function protocoloUserCanExecuteAssignment(PDO $pdo, array $assignment): bool
{
    $userId = (string) currentUserId();
    if ($userId === (string) ($assignment['responsible_user'] ?? '')) {
        return true;
    }

    $idWorker = (int) ($assignment['id_worker'] ?? 0);
    if ($idWorker <= 0) {
        return false;
    }

    $stmt = $pdo->prepare(
        'SELECT COUNT(*) FROM users '
        . 'WHERE id_users=:id_users AND id_worker=:id_worker AND state=1'
    );
    $stmt->execute([
        'id_users' => $userId,
        'id_worker' => $idWorker,
    ]);
    return (int) $stmt->fetchColumn() === 1;
}

function protocoloNextDueAfterReview(string $unit, ?int $interval): ?string
{
    if ($unit === 'none') {
        return null;
    }

    $interval = max(1, (int) $interval);
    $date = new DateTime('now');

    switch ($unit) {
        case 'days':
            $date->modify('+' . $interval . ' days');
            break;
        case 'weeks':
            $date->modify('+' . $interval . ' weeks');
            break;
        case 'months':
            $date->modify('+' . $interval . ' months');
            break;
        case 'years':
            $date->modify('+' . $interval . ' years');
            break;
        default:
            return null;
    }

    return $date->format('Y-m-d H:i:s');
}

function protocoloAssertAssignmentManageable(PDO $pdo, array $assignment): void
{
    protocoloRequireGestionApi($pdo);

    if (protocoloIsGlobalAdmin($pdo)) {
        return;
    }

    if ((int) ($assignment['id_company'] ?? 0) !== protocoloCurrentCompany($pdo)) {
        responderJSON(false, null, 'La asignación pertenece a otra empresa.', 403);
    }
}
