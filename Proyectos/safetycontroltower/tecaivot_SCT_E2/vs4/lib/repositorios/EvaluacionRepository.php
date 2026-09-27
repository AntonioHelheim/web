<?php
/**
 * lib/repositorios/EvaluacionRepository.php
 *
 * Capa común para las evaluaciones recurrentes de Etapa 2
 * (auditorías y autoevaluaciones). Reutiliza company_test, questions,
 * company_test_rel_questions, users_test_assigned y users_test_answers,
 * pero vincula cada intento a una asignación concreta mediante
 * users_test_answers.id_user_test_assigned.
 *
 * Esto evita que los intentos de una auditoría histórica se mezclen con
 * una nueva asignación del mismo template al mismo usuario.
 */

function evaluacionSchemaAsignacionEnRespuestasDisponible(PDO $pdo): bool
{
    static $resultado = null;
    if ($resultado !== null) {
        return $resultado;
    }

    try {
        $stmt = $pdo->prepare(
            'SELECT COUNT(*)
             FROM information_schema.COLUMNS
             WHERE TABLE_SCHEMA = DATABASE()
               AND TABLE_NAME = :tabla
               AND COLUMN_NAME = :columna'
        );
        $stmt->execute([
            'tabla' => 'users_test_answers',
            'columna' => 'id_user_test_assigned',
        ]);
        return $resultado = ((int) $stmt->fetchColumn() > 0);
    } catch (Throwable $e) {
        error_log('EvaluacionRepository::evaluacionSchemaAsignacionEnRespuestasDisponible: ' . $e->getMessage());
        return $resultado = false;
    }
}

function evaluacionRequireSchemaAsignacion(PDO $pdo): void
{
    if (!evaluacionSchemaAsignacionEnRespuestasDisponible($pdo)) {
        throw new RuntimeException('MIGRATION_REQUIRED_E2_EVALUATIONS');
    }
}

function evaluacionAsignacionPendienteExiste(PDO $pdo, int $idTest, string $idUsuario): bool
{
    $stmt = $pdo->prepare(
        'SELECT 1
         FROM users_test_assigned
         WHERE id_test = :id_test
           AND id_users = :id_users
           AND state = 1
         LIMIT 1'
    );
    $stmt->execute([
        'id_test' => $idTest,
        'id_users' => $idUsuario,
    ]);

    return (bool) $stmt->fetchColumn();
}

function evaluacionIntentosUsadosPorAsignacion(PDO $pdo, int $idAsignacion): int
{
    evaluacionRequireSchemaAsignacion($pdo);

    $stmt = $pdo->prepare(
        'SELECT COUNT(DISTINCT id_test_try)
         FROM users_test_answers
         WHERE id_user_test_assigned = :id_asignacion'
    );
    $stmt->execute(['id_asignacion' => $idAsignacion]);

    return (int) $stmt->fetchColumn();
}

function evaluacionSiguienteIntentoPorAsignacion(PDO $pdo, int $idAsignacion): int
{
    evaluacionRequireSchemaAsignacion($pdo);

    $stmt = $pdo->prepare(
        'SELECT COALESCE(MAX(id_test_try), 0)
         FROM users_test_answers
         WHERE id_user_test_assigned = :id_asignacion'
    );
    $stmt->execute(['id_asignacion' => $idAsignacion]);

    return ((int) $stmt->fetchColumn()) + 1;
}

/**
 * $respuestas: [['id_rel'=>int,'id_question'=>int,'id_questions_options'=>int], ...]
 */
function evaluacionRegistrarRespuestasPorAsignacion(
    PDO $pdo,
    int $idAsignacion,
    string $idUsuario,
    int $idCompany,
    int $idTest,
    int $idTestTry,
    array $respuestas
): void {
    evaluacionRequireSchemaAsignacion($pdo);

    $stmt = $pdo->prepare(
        'INSERT INTO users_test_answers
            (id_users, id_company, id_test, id_user_test_assigned, id_test_try,
             id_rel, id_question, id_questions_options, date_create, last_update)
         VALUES
            (:id_users, :id_company, :id_test, :id_user_test_assigned, :id_test_try,
             :id_rel, :id_question, :id_questions_options, NOW(), NOW())'
    );

    foreach ($respuestas as $respuesta) {
        $stmt->execute([
            'id_users' => $idUsuario,
            'id_company' => $idCompany,
            'id_test' => $idTest,
            'id_user_test_assigned' => $idAsignacion,
            'id_test_try' => $idTestTry,
            'id_rel' => $respuesta['id_rel'],
            'id_question' => $respuesta['id_question'],
            'id_questions_options' => $respuesta['id_questions_options'],
        ]);
    }
}

function evaluacionCalcularResultadoPorAsignacion(
    PDO $pdo,
    int $idAsignacion,
    int $idTestTry,
    int $puntajeMaximo
): array {
    evaluacionRequireSchemaAsignacion($pdo);

    $stmt = $pdo->prepare(
        'SELECT r.assigned_score, o.is_it_co
         FROM users_test_answers a
         INNER JOIN company_test_rel_questions r ON r.id_rel = a.id_rel
         INNER JOIN questions_options o ON o.id_questions_options = a.id_questions_options
         WHERE a.id_user_test_assigned = :id_asignacion
           AND a.id_test_try = :id_test_try'
    );
    $stmt->execute([
        'id_asignacion' => $idAsignacion,
        'id_test_try' => $idTestTry,
    ]);
    $respuestas = $stmt->fetchAll();

    $puntajeObtenido = 0;
    foreach ($respuestas as $respuesta) {
        if ((int) $respuesta['is_it_co'] === 1) {
            $puntajeObtenido += (int) $respuesta['assigned_score'];
        }
    }

    $porcentaje = $puntajeMaximo > 0
        ? round(($puntajeObtenido / $puntajeMaximo) * 100, 1)
        : 0.0;

    return [
        'puntaje_obtenido' => $puntajeObtenido,
        'puntaje_maximo' => $puntajeMaximo,
        'porcentaje' => $porcentaje,
        'cantidad_respuestas' => count($respuestas),
    ];
}

function evaluacionListarAsignacionesUsuarioPorTipo(PDO $pdo, string $idUsuario, string $tipo): array
{
    evaluacionRequireSchemaAsignacion($pdo);

    $stmt = $pdo->prepare(
        'SELECT a.id_user_test_assigned, a.id_test, a.id_company, a.deadline, a.state,
                a.assignamente_date, t.name AS test_name, t.description AS test_description,
                t.approval_percentage, t.attempts_allowed,
                (SELECT COUNT(DISTINCT ans.id_test_try)
                   FROM users_test_answers ans
                  WHERE ans.id_user_test_assigned = a.id_user_test_assigned) AS intentos_usados
         FROM users_test_assigned a
         INNER JOIN company_test t ON t.id_test = a.id_test
         WHERE a.id_users = :id_users
           AND t.type = :tipo
         ORDER BY a.state = 1 DESC, a.deadline DESC, a.id_user_test_assigned DESC'
    );
    $stmt->execute([
        'id_users' => $idUsuario,
        'tipo' => $tipo,
    ]);

    return $stmt->fetchAll();
}

/** Devuelve true si un template de evaluación ya posee al menos una asignación. */
function evaluacionTestTieneAsignaciones(PDO $pdo, int $idTest): bool
{
    $stmt = $pdo->prepare('SELECT 1 FROM users_test_assigned WHERE id_test = :id_test LIMIT 1');
    $stmt->execute(['id_test' => $idTest]);
    return (bool) $stmt->fetchColumn();
}

/**
 * Resultado del último intento de una asignación. El porcentaje se calcula
 * nuevamente desde las respuestas y el puntaje configurado; no se confía en
 * valores enviados por el navegador.
 */
function evaluacionResultadoUltimoIntentoPorAsignacion(PDO $pdo, int $idAsignacion, int $idTest): ?array
{
    evaluacionRequireSchemaAsignacion($pdo);
    $stmt = $pdo->prepare('SELECT MAX(id_test_try) FROM users_test_answers WHERE id_user_test_assigned = :id_asignacion');
    $stmt->execute(['id_asignacion' => $idAsignacion]);
    $ultimo = (int) $stmt->fetchColumn();
    if ($ultimo <= 0) return null;

    $stmtMax = $pdo->prepare('SELECT COALESCE(SUM(assigned_score),0) FROM company_test_rel_questions WHERE id_test = :id_test');
    $stmtMax->execute(['id_test' => $idTest]);
    $maximo = (int) $stmtMax->fetchColumn();
    $resultado = evaluacionCalcularResultadoPorAsignacion($pdo, $idAsignacion, $ultimo, $maximo);
    $resultado['id_test_try'] = $ultimo;
    return $resultado;
}

/** Listado administrativo genérico de asignaciones de una evaluación. */
function evaluacionListarAsignacionesPorTest(PDO $pdo, int $idTest): array
{
    evaluacionRequireSchemaAsignacion($pdo);
    $stmt = $pdo->prepare(
        'SELECT a.id_user_test_assigned, a.id_users, a.id_test, a.id_company,
                a.assignamente_date, a.deadline, a.state, u.name, u.lastname,
                (SELECT COUNT(DISTINCT ans.id_test_try)
                   FROM users_test_answers ans
                  WHERE ans.id_user_test_assigned = a.id_user_test_assigned) AS intentos_usados
         FROM users_test_assigned a
         INNER JOIN users u ON u.id_users = a.id_users
         WHERE a.id_test = :id_test
         ORDER BY a.id_user_test_assigned DESC'
    );
    $stmt->execute(['id_test' => $idTest]);
    $rows = $stmt->fetchAll();
    foreach ($rows as &$row) {
        $ultimo = evaluacionResultadoUltimoIntentoPorAsignacion($pdo, (int)$row['id_user_test_assigned'], $idTest);
        $row['porcentaje_ultimo'] = $ultimo['porcentaje'] ?? null;
    }
    unset($row);
    return $rows;
}

