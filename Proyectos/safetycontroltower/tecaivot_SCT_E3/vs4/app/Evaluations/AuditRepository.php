<?php
/**
 * lib/repositorios/AuditoriaRepository.php
 * Datos propios de la ejecución de auditorías.
 *
 * La definición del cuestionario y sus respuestas viven en el motor común
 * company_test / users_test_*. La tabla audits conserva la cabecera,
 * auditor responsable, resultado resumido, observaciones y trazabilidad.
 */

function auditoriaSchemaReady(PDO $pdo): bool
{
    static $resultado = null;
    if ($resultado !== null) {
        return $resultado;
    }

    $requeridas = ['id_test', 'id_user_test_assigned', 'id_users_auditor', 'status', 'completed_at'];
    try {
        $placeholders = implode(',', array_fill(0, count($requeridas), '?'));
        $stmt = $pdo->prepare(
            'SELECT COLUMN_NAME
             FROM information_schema.COLUMNS
             WHERE TABLE_SCHEMA = DATABASE()
               AND TABLE_NAME = ?
               AND COLUMN_NAME IN (' . $placeholders . ')'
        );
        $stmt->execute(array_merge(['audits'], $requeridas));
        $encontradas = $stmt->fetchAll(PDO::FETCH_COLUMN);
        return $resultado = (count(array_unique($encontradas)) === count($requeridas));
    } catch (Throwable $e) {
        error_log('AuditoriaRepository::auditoriaSchemaReady: ' . $e->getMessage());
        return $resultado = false;
    }
}

function auditoriaRequireSchema(PDO $pdo): void
{
    if (!auditoriaSchemaReady($pdo)) {
        throw new RuntimeException('MIGRATION_REQUIRED_AUDITS');
    }
}

function auditoriaCrearDesdeAsignacion(
    PDO $pdo,
    int $idCompany,
    int $idTest,
    int $idAsignacion,
    string $idAuditor,
    string $creadoPor
): int {
    auditoriaRequireSchema($pdo);

    $stmtUsuario = $pdo->prepare(
        'SELECT id_users, name, lastname
         FROM users
         WHERE id_users = :id_users AND state = 1
         LIMIT 1'
    );
    $stmtUsuario->execute(['id_users' => $idAuditor]);
    $usuario = $stmtUsuario->fetch();
    if (!$usuario) {
        throw new RuntimeException('AUDITOR_NOT_FOUND');
    }

    $nombre = trim((string) $usuario['name'] . ' ' . (string) $usuario['lastname']);
    if ($nombre === '') {
        $nombre = $idAuditor;
    }

    $stmt = $pdo->prepare(
        'INSERT INTO audits
            (id_company, id_test, id_user_test_assigned, id_users_auditor,
             name_auditor, email, score, obs, status, completed_at,
             created_by, date_create, last_update)
         VALUES
            (:id_company, :id_test, :id_user_test_assigned, :id_users_auditor,
             :name_auditor, :email, :score, :obs, :status, NULL,
             :created_by, NOW(), NOW())'
    );
    $stmt->execute([
        'id_company' => $idCompany,
        'id_test' => $idTest,
        'id_user_test_assigned' => $idAsignacion,
        'id_users_auditor' => $idAuditor,
        'name_auditor' => $nombre,
        'email' => $idAuditor,
        'score' => '',
        'obs' => '',
        'status' => 'pendiente',
        'created_by' => $creadoPor,
    ]);

    return (int) $pdo->lastInsertId();
}

function auditoriaObtenerPorAsignacion(PDO $pdo, int $idAsignacion): ?array
{
    auditoriaRequireSchema($pdo);

    $stmt = $pdo->prepare(
        'SELECT *
         FROM audits
         WHERE id_user_test_assigned = :id_asignacion
         ORDER BY id_audits DESC
         LIMIT 1'
    );
    $stmt->execute(['id_asignacion' => $idAsignacion]);
    $row = $stmt->fetch();

    return $row ?: null;
}

function auditoriaListarPorTest(PDO $pdo, int $idTest): array
{
    auditoriaRequireSchema($pdo);

    $stmt = $pdo->prepare(
        'SELECT au.id_audits, au.id_company, au.id_test, au.id_user_test_assigned,
                au.id_users_auditor, au.name_auditor, au.email, au.score, au.obs,
                au.status, au.completed_at, au.date_create, au.last_update,
                a.deadline, a.state AS assignment_state, a.assignamente_date,
                (SELECT COUNT(DISTINCT ans.id_test_try)
                   FROM users_test_answers ans
                  WHERE ans.id_user_test_assigned = a.id_user_test_assigned) AS intentos_usados
         FROM audits au
         INNER JOIN users_test_assigned a
            ON a.id_user_test_assigned = au.id_user_test_assigned
         WHERE au.id_test = :id_test
         ORDER BY au.id_audits DESC'
    );
    $stmt->execute(['id_test' => $idTest]);

    return $stmt->fetchAll();
}


function auditoriaTestTieneAsignaciones(PDO $pdo, int $idTest): bool
{
    $stmt = $pdo->prepare(
        'SELECT 1
         FROM users_test_assigned
         WHERE id_test = :id_test
         LIMIT 1'
    );
    $stmt->execute(['id_test' => $idTest]);

    return (bool) $stmt->fetchColumn();
}

function auditoriaMarcarEnCurso(PDO $pdo, int $idAsignacion): void
{
    auditoriaRequireSchema($pdo);

    $stmt = $pdo->prepare(
        "UPDATE audits
         SET status = CASE WHEN status = 'pendiente' THEN 'en_curso' ELSE status END,
             last_update = NOW()
         WHERE id_user_test_assigned = :id_asignacion"
    );
    $stmt->execute(['id_asignacion' => $idAsignacion]);
}


function auditoriaActualizarProgreso(
    PDO $pdo,
    int $idAsignacion,
    float $porcentaje,
    string $observaciones
): void {
    auditoriaRequireSchema($pdo);

    $stmt = $pdo->prepare(
        "UPDATE audits
         SET score = :score,
             obs = :obs,
             status = 'en_curso',
             last_update = NOW()
         WHERE id_user_test_assigned = :id_asignacion"
    );
    $stmt->execute([
        'score' => number_format($porcentaje, 1, '.', '') . '%',
        'obs' => $observaciones,
        'id_asignacion' => $idAsignacion,
    ]);
}

function auditoriaCompletar(
    PDO $pdo,
    int $idAsignacion,
    float $porcentaje,
    string $observaciones
): void {
    auditoriaRequireSchema($pdo);

    $stmt = $pdo->prepare(
        "UPDATE audits
         SET score = :score,
             obs = :obs,
             status = 'completada',
             completed_at = NOW(),
             last_update = NOW()
         WHERE id_user_test_assigned = :id_asignacion"
    );
    $stmt->execute([
        'score' => number_format($porcentaje, 1, '.', '') . '%',
        'obs' => $observaciones,
        'id_asignacion' => $idAsignacion,
    ]);
}
