<?php
/**
 * lib/repositorios/InduccionRepository.php
 * Motor de evaluación (company_test) y todo lo que cuelga de él: banco de
 * preguntas, materiales, asignaciones, intentos y certificados.
 *
 * A propósito se construye genérico sobre "cursos" (company_test.type),
 * no solo sobre inducción: el mismo motor se reutiliza en Etapa 2 para
 * autoevaluación y auditoría, tal como ya lo anticipaba el diccionario
 * de base de datos ("motor reutilizado en Etapa 2"). Este módulo de
 * Etapa 1 solo trabaja con type='induccion'; el filtro vive en el
 * endpoint, no hardcodeado en el repositorio, para no tener que tocar
 * este archivo cuando se construya autoevaluación/auditoría.
 *
 * Nota de diseño importante: la tabla `questions` NO tiene id_company
 * (banco de preguntas global, compartido entre empresas). Por eso la
 * gestión de preguntas (crear/editar/dar de baja) es exclusiva de
 * administrador/administrador_completo; el resto de los roles de
 * gestión solo puede elegir preguntas ya existentes para armar el
 * curso de su empresa, no crear preguntas nuevas al vuelo.
 */

/* =========================================================
   CURSOS (company_test)
   ========================================================= */

function cursoListarPorEmpresa(PDO $pdo, int $idCompany, string $tipo): array
{
    $stmt = $pdo->prepare(
        'SELECT t.id_test, t.name, t.type, t.description, t.version, t.state, t.attempts_allowed,
                t.approval_percentage, t.effective_date_from, t.effective_date_until, t.id_company,
                (SELECT COUNT(*) FROM company_test_rel_questions r WHERE r.id_test = t.id_test) AS preguntas_count
         FROM company_test t
         WHERE t.id_company = :id_company AND t.type = :type
         ORDER BY t.name'
    );
    $stmt->execute(['id_company' => $idCompany, 'type' => $tipo]);

    return $stmt->fetchAll();
}

function cursoObtenerPorId(PDO $pdo, int $idTest): ?array
{
    $stmt = $pdo->prepare('SELECT * FROM company_test WHERE id_test = :id LIMIT 1');
    $stmt->execute(['id' => $idTest]);
    $row = $stmt->fetch();

    return $row ?: null;
}

function cursoExisteNombreEnEmpresa(PDO $pdo, int $idCompany, string $name, ?int $idExcluir = null): bool
{
    $sql = 'SELECT id_test FROM company_test WHERE id_company = :id_company AND name = :name AND state = 1';
    $params = ['id_company' => $idCompany, 'name' => $name];

    if ($idExcluir !== null) {
        $sql .= ' AND id_test != :id_excluir';
        $params['id_excluir'] = $idExcluir;
    }

    $stmt = $pdo->prepare($sql . ' LIMIT 1');
    $stmt->execute($params);

    return (bool) $stmt->fetch();
}

function cursoCrear(PDO $pdo, array $datos, string $creadoPor): int
{
    $stmt = $pdo->prepare(
        'INSERT INTO company_test
            (name, type, description, version, state, attempts_allowed, approval_percentage,
             effective_date_from, effective_date_until, created_by, date_created, last_update, id_company)
         VALUES
            (:name, :type, :description, 1, 1, :attempts_allowed, :approval_percentage,
             :effective_date_from, :effective_date_until, :created_by, NOW(), NOW(), :id_company)'
    );
    $stmt->execute([
        'name'                 => $datos['name'],
        'type'                 => $datos['type'],
        'description'          => $datos['description'],
        'attempts_allowed'     => $datos['attempts_allowed'],
        'approval_percentage'  => $datos['approval_percentage'],
        'effective_date_from'  => $datos['effective_date_from'],
        'effective_date_until' => $datos['effective_date_until'],
        'created_by'           => $creadoPor,
        'id_company'           => $datos['id_company'],
    ]);

    return (int) $pdo->lastInsertId();
}

function cursoActualizar(PDO $pdo, int $idTest, array $datos): bool
{
    $stmt = $pdo->prepare(
        'UPDATE company_test
         SET name = :name, description = :description, attempts_allowed = :attempts_allowed,
             approval_percentage = :approval_percentage, effective_date_from = :effective_date_from,
             effective_date_until = :effective_date_until, version = version + 1, last_update = NOW()
         WHERE id_test = :id_test'
    );

    return $stmt->execute([
        'name'                 => $datos['name'],
        'description'          => $datos['description'],
        'attempts_allowed'     => $datos['attempts_allowed'],
        'approval_percentage'  => $datos['approval_percentage'],
        'effective_date_from'  => $datos['effective_date_from'],
        'effective_date_until' => $datos['effective_date_until'],
        'id_test'              => $idTest,
    ]);
}

function cursoCambiarEstado(PDO $pdo, int $idTest, int $nuevoEstado): bool
{
    $stmt = $pdo->prepare('UPDATE company_test SET state = :state, last_update = NOW() WHERE id_test = :id_test');
    return $stmt->execute(['state' => $nuevoEstado, 'id_test' => $idTest]);
}

/* =========================================================
   BANCO DE PREGUNTAS (global, sin id_company)
   ========================================================= */

function preguntaListar(PDO $pdo, string $busqueda = ''): array
{
    $sql = 'SELECT id_questions, question, difficulty, points, state, date_create
            FROM questions WHERE state = 1';
    $params = [];

    if ($busqueda !== '') {
        $sql .= ' AND question LIKE :busqueda';
        $params['busqueda'] = '%' . $busqueda . '%';
    }

    $sql .= ' ORDER BY date_create DESC LIMIT 100';

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    return $stmt->fetchAll();
}

function preguntaObtenerPorId(PDO $pdo, int $idPregunta): ?array
{
    $stmt = $pdo->prepare('SELECT * FROM questions WHERE id_questions = :id LIMIT 1');
    $stmt->execute(['id' => $idPregunta]);
    $pregunta = $stmt->fetch();

    if (!$pregunta) {
        return null;
    }

    $stmtOpciones = $pdo->prepare(
        'SELECT id_questions_options, text_option, is_it_co, add_expl_opt, state
         FROM questions_options WHERE id_questions = :id_questions AND state = 1
         ORDER BY id_questions_options'
    );
    $stmtOpciones->execute(['id_questions' => $idPregunta]);
    $pregunta['opciones'] = $stmtOpciones->fetchAll();

    return $pregunta;
}

/**
 * Crea la pregunta y sus alternativas en una sola transacción: si algo
 * falla a mitad de camino, no queda una pregunta huérfana sin opciones.
 */
function preguntaCrear(PDO $pdo, array $datos, array $opciones, string $creadoPor): int
{
    $pdo->beginTransaction();
    try {
        $stmt = $pdo->prepare(
            'INSERT INTO questions (question, url_add_material, difficulty, points, state, add_expl_question, create_by, date_create, last_update)
             VALUES (:question, :url_add_material, :difficulty, :points, 1, :add_expl_question, :create_by, NOW(), NOW())'
        );
        $stmt->execute([
            'question'          => $datos['question'],
            'url_add_material'  => $datos['url_add_material'],
            'difficulty'        => $datos['difficulty'],
            'points'            => $datos['points'],
            'add_expl_question' => $datos['add_expl_question'],
            'create_by'         => $creadoPor,
        ]);
        $idPregunta = (int) $pdo->lastInsertId();

        $stmtOpcion = $pdo->prepare(
            'INSERT INTO questions_options (id_questions, text_option, is_it_co, add_expl_opt, state, created_by, date_create, last_update)
             VALUES (:id_questions, :text_option, :is_it_co, :add_expl_opt, 1, :created_by, NOW(), NOW())'
        );
        foreach ($opciones as $opcion) {
            $stmtOpcion->execute([
                'id_questions' => $idPregunta,
                'text_option'  => $opcion['text_option'],
                'is_it_co'     => $opcion['is_it_co'] ? 1 : 0,
                'add_expl_opt' => $opcion['add_expl_opt'] ?? '',
                'created_by'   => $creadoPor,
            ]);
        }

        $pdo->commit();
        return $idPregunta;
    } catch (Throwable $e) {
        $pdo->rollBack();
        throw $e;
    }
}

/**
 * Actualiza el enunciado y REEMPLAZA todas las alternativas (da de baja
 * las viejas, inserta las nuevas) — más simple y menos propenso a
 * errores que tratar de diffear opción por opción, y las preguntas no
 * suelen editarse con tanta frecuencia como para que importe.
 */
function preguntaActualizar(PDO $pdo, int $idPregunta, array $datos, array $opciones, string $actualizadoPor): bool
{
    $pdo->beginTransaction();
    try {
        $stmt = $pdo->prepare(
            'UPDATE questions
             SET question = :question, url_add_material = :url_add_material, difficulty = :difficulty,
                 points = :points, add_expl_question = :add_expl_question, last_update = NOW()
             WHERE id_questions = :id_questions'
        );
        $stmt->execute([
            'question'          => $datos['question'],
            'url_add_material'  => $datos['url_add_material'],
            'difficulty'        => $datos['difficulty'],
            'points'            => $datos['points'],
            'add_expl_question' => $datos['add_expl_question'],
            'id_questions'      => $idPregunta,
        ]);

        $pdo->prepare('UPDATE questions_options SET state = 0 WHERE id_questions = :id')
            ->execute(['id' => $idPregunta]);

        $stmtOpcion = $pdo->prepare(
            'INSERT INTO questions_options (id_questions, text_option, is_it_co, add_expl_opt, state, created_by, date_create, last_update)
             VALUES (:id_questions, :text_option, :is_it_co, :add_expl_opt, 1, :created_by, NOW(), NOW())'
        );
        foreach ($opciones as $opcion) {
            $stmtOpcion->execute([
                'id_questions' => $idPregunta,
                'text_option'  => $opcion['text_option'],
                'is_it_co'     => $opcion['is_it_co'] ? 1 : 0,
                'add_expl_opt' => $opcion['add_expl_opt'] ?? '',
                'created_by'   => $actualizadoPor,
            ]);
        }

        $pdo->commit();
        return true;
    } catch (Throwable $e) {
        $pdo->rollBack();
        throw $e;
    }
}

function preguntaCambiarEstado(PDO $pdo, int $idPregunta, int $nuevoEstado): bool
{
    $stmt = $pdo->prepare('UPDATE questions SET state = :state, last_update = NOW() WHERE id_questions = :id');
    return $stmt->execute(['state' => $nuevoEstado, 'id' => $idPregunta]);
}

/* =========================================================
   PREGUNTAS DE UN CURSO (company_test_rel_questions)
   ========================================================= */

function cursoListarPreguntas(PDO $pdo, int $idTest): array
{
    $stmt = $pdo->prepare(
        'SELECT r.id_rel, r.id_question, r.assigned_score, q.question, q.difficulty
         FROM company_test_rel_questions r
         INNER JOIN questions q ON q.id_questions = r.id_question
         WHERE r.id_test = :id_test
         ORDER BY r.id_rel'
    );
    $stmt->execute(['id_test' => $idTest]);

    return $stmt->fetchAll();
}

function cursoPreguntaYaAgregada(PDO $pdo, int $idTest, int $idPregunta): bool
{
    $stmt = $pdo->prepare(
        'SELECT 1 FROM company_test_rel_questions WHERE id_test = :id_test AND id_question = :id_question LIMIT 1'
    );
    $stmt->execute(['id_test' => $idTest, 'id_question' => $idPregunta]);

    return (bool) $stmt->fetch();
}

function cursoAgregarPregunta(PDO $pdo, int $idTest, int $idPregunta, int $puntaje, string $creadoPor): int
{
    $stmt = $pdo->prepare(
        'INSERT INTO company_test_rel_questions (id_test, id_question, assigned_score, created_by, date_create, last_update)
         VALUES (:id_test, :id_question, :assigned_score, :created_by, NOW(), NOW())'
    );
    $stmt->execute([
        'id_test'        => $idTest,
        'id_question'    => $idPregunta,
        'assigned_score' => $puntaje,
        'created_by'     => $creadoPor,
    ]);

    return (int) $pdo->lastInsertId();
}

function cursoQuitarPregunta(PDO $pdo, int $idRel): bool
{
    $stmt = $pdo->prepare('DELETE FROM company_test_rel_questions WHERE id_rel = :id_rel');
    return $stmt->execute(['id_rel' => $idRel]);
}

function cursoObtenerRelPorId(PDO $pdo, int $idRel): ?array
{
    $stmt = $pdo->prepare('SELECT * FROM company_test_rel_questions WHERE id_rel = :id LIMIT 1');
    $stmt->execute(['id' => $idRel]);
    $row = $stmt->fetch();

    return $row ?: null;
}

/**
 * users_test_answers.id_rel tiene FK ON DELETE NO ACTION hacia esta
 * tabla: si ya hay respuestas registradas, un DELETE fallaría con un
 * error de integridad referencial. Se chequea antes para poder devolver
 * un mensaje claro en vez de un error 500 genérico de base de datos.
 */
function cursoPreguntaTieneRespuestas(PDO $pdo, int $idRel): bool
{
    $stmt = $pdo->prepare('SELECT 1 FROM users_test_answers WHERE id_rel = :id_rel LIMIT 1');
    $stmt->execute(['id_rel' => $idRel]);

    return (bool) $stmt->fetch();
}

function cursoPuntajeMaximo(PDO $pdo, int $idTest): int
{
    $stmt = $pdo->prepare('SELECT COALESCE(SUM(assigned_score), 0) AS total FROM company_test_rel_questions WHERE id_test = :id_test');
    $stmt->execute(['id_test' => $idTest]);

    return (int) $stmt->fetchColumn();
}

/* =========================================================
   MATERIALES DE APOYO
   ========================================================= */

function materialListarPorCurso(PDO $pdo, int $idTest): array
{
    $stmt = $pdo->prepare(
        'SELECT id_material, title, material_type, file_path, content_text, sort_order, date_create
         FROM test_materials WHERE id_test = :id_test ORDER BY sort_order, id_material'
    );
    $stmt->execute(['id_test' => $idTest]);

    return $stmt->fetchAll();
}

function materialObtenerPorId(PDO $pdo, int $idMaterial): ?array
{
    $stmt = $pdo->prepare('SELECT * FROM test_materials WHERE id_material = :id LIMIT 1');
    $stmt->execute(['id' => $idMaterial]);
    $row = $stmt->fetch();

    return $row ?: null;
}

function materialCrear(PDO $pdo, array $datos, string $creadoPor): int
{
    $stmt = $pdo->prepare(
        'INSERT INTO test_materials (id_test, title, material_type, file_path, content_text, sort_order, created_by, date_create)
         VALUES (:id_test, :title, :material_type, :file_path, :content_text, :sort_order, :created_by, NOW())'
    );
    $stmt->execute([
        'id_test'        => $datos['id_test'],
        'title'          => $datos['title'],
        'material_type'  => $datos['material_type'],
        'file_path'      => $datos['file_path'] !== '' ? $datos['file_path'] : null,
        'content_text'   => $datos['content_text'] !== '' ? $datos['content_text'] : null,
        'sort_order'     => $datos['sort_order'],
        'created_by'     => $creadoPor,
    ]);

    return (int) $pdo->lastInsertId();
}

function materialEliminar(PDO $pdo, int $idMaterial): bool
{
    // Los materiales no tienen "state" en el esquema (ver diccionario) —
    // a diferencia del resto de las entidades, acá sí es DELETE físico,
    // consistente con que son contenido editable de apoyo, no un
    // registro de negocio que deba conservarse por trazabilidad.
    $stmt = $pdo->prepare('DELETE FROM test_materials WHERE id_material = :id');
    return $stmt->execute(['id' => $idMaterial]);
}

/* =========================================================
   ASIGNACIONES (users_test_assigned)
   Estados: 1 = Pendiente, 2 = Aprobado, 3 = Reprobado (sin intentos)
   ========================================================= */

const ASIGNACION_PENDIENTE = 1;
const ASIGNACION_APROBADA = 2;
const ASIGNACION_REPROBADA = 3;

function asignacionYaExisteActiva(PDO $pdo, int $idTest, string $idUsuario): bool
{
    $stmt = $pdo->prepare(
        'SELECT 1 FROM users_test_assigned
         WHERE id_test = :id_test AND id_users = :id_users AND state IN (1, 2)
         LIMIT 1'
    );
    $stmt->execute(['id_test' => $idTest, 'id_users' => $idUsuario]);

    return (bool) $stmt->fetch();
}

function asignacionCrear(PDO $pdo, int $idTest, string $idUsuario, int $idCompany, string $deadline, string $creadoPor): int
{
    $stmt = $pdo->prepare(
        'INSERT INTO users_test_assigned (id_users, id_test, id_company, assignamente_date, deadline, state, created_by, date_create, last_update)
         VALUES (:id_users, :id_test, :id_company, NOW(), :deadline, 1, :created_by, NOW(), NOW())'
    );
    $stmt->execute([
        'id_users'   => $idUsuario,
        'id_test'    => $idTest,
        'id_company' => $idCompany,
        'deadline'   => $deadline,
        'created_by' => $creadoPor,
    ]);

    return (int) $pdo->lastInsertId();
}

function asignacionObtenerPorId(PDO $pdo, int $idAsignacion): ?array
{
    $stmt = $pdo->prepare('SELECT * FROM users_test_assigned WHERE id_user_test_assigned = :id LIMIT 1');
    $stmt->execute(['id' => $idAsignacion]);
    $row = $stmt->fetch();

    return $row ?: null;
}

function asignacionListarPorCurso(PDO $pdo, int $idTest): array
{
    $stmt = $pdo->prepare(
        'SELECT a.id_user_test_assigned, a.id_users, a.deadline, a.state, a.assignamente_date,
                u.name, u.lastname
         FROM users_test_assigned a
         INNER JOIN users u ON u.id_users = a.id_users
         WHERE a.id_test = :id_test
         ORDER BY a.assignamente_date DESC'
    );
    $stmt->execute(['id_test' => $idTest]);

    return $stmt->fetchAll();
}

function asignacionListarPorUsuario(PDO $pdo, string $idUsuario): array
{
    $stmt = $pdo->prepare(
        'SELECT a.id_user_test_assigned, a.id_test, a.deadline, a.state, a.assignamente_date,
                t.name AS test_name, t.description AS test_description, t.approval_percentage
         FROM users_test_assigned a
         INNER JOIN company_test t ON t.id_test = a.id_test
         WHERE a.id_users = :id_users
         ORDER BY a.state = 1 DESC, a.deadline'
    );
    $stmt->execute(['id_users' => $idUsuario]);

    return $stmt->fetchAll();
}

function asignacionActualizarEstado(PDO $pdo, int $idAsignacion, int $nuevoEstado): bool
{
    $stmt = $pdo->prepare(
        'UPDATE users_test_assigned SET state = :state, last_update = NOW() WHERE id_user_test_assigned = :id'
    );
    return $stmt->execute(['state' => $nuevoEstado, 'id' => $idAsignacion]);
}

/* =========================================================
   INTENTOS Y RESPUESTAS (users_test_answers)
   ========================================================= */

function intentosUsados(PDO $pdo, string $idUsuario, int $idTest): int
{
    $stmt = $pdo->prepare(
        'SELECT COUNT(DISTINCT id_test_try) FROM users_test_answers WHERE id_users = :id_users AND id_test = :id_test'
    );
    $stmt->execute(['id_users' => $idUsuario, 'id_test' => $idTest]);

    return (int) $stmt->fetchColumn();
}

function siguienteIntento(PDO $pdo, string $idUsuario, int $idTest): int
{
    $stmt = $pdo->prepare(
        'SELECT COALESCE(MAX(id_test_try), 0) FROM users_test_answers WHERE id_users = :id_users AND id_test = :id_test'
    );
    $stmt->execute(['id_users' => $idUsuario, 'id_test' => $idTest]);

    return ((int) $stmt->fetchColumn()) + 1;
}

/**
 * Registra todas las respuestas de un intento de una sola vez. No
 * calcula el resultado acá a propósito — eso lo hace
 * calcularResultadoIntento() por separado, para poder registrar primero
 * y calcular después sin mezclar las dos responsabilidades.
 *
 * $respuestas: array de ['id_rel' => int, 'id_question' => int, 'id_questions_options' => int]
 */
function registrarRespuestas(PDO $pdo, string $idUsuario, int $idCompany, int $idTest, int $idTestTry, array $respuestas): void
{
    $stmt = $pdo->prepare(
        'INSERT INTO users_test_answers
            (id_users, id_company, id_test, id_test_try, id_rel, id_question, id_questions_options, date_create, last_update)
         VALUES
            (:id_users, :id_company, :id_test, :id_test_try, :id_rel, :id_question, :id_questions_options, NOW(), NOW())'
    );

    foreach ($respuestas as $respuesta) {
        $stmt->execute([
            'id_users'              => $idUsuario,
            'id_company'            => $idCompany,
            'id_test'               => $idTest,
            'id_test_try'           => $idTestTry,
            'id_rel'                => $respuesta['id_rel'],
            'id_question'           => $respuesta['id_question'],
            'id_questions_options'  => $respuesta['id_questions_options'],
        ]);
    }
}

/**
 * Calcula el puntaje obtenido de un intento ya registrado, comparando
 * cada respuesta contra questions_options.is_it_co. El cálculo se hace
 * siempre server-side a partir de lo guardado en la BD — nunca se
 * confía en un puntaje que venga calculado desde el navegador.
 */
function calcularResultadoIntento(PDO $pdo, string $idUsuario, int $idTest, int $idTestTry): array
{
    $stmt = $pdo->prepare(
        'SELECT a.id_rel, r.assigned_score, o.is_it_co
         FROM users_test_answers a
         INNER JOIN company_test_rel_questions r ON r.id_rel = a.id_rel
         INNER JOIN questions_options o ON o.id_questions_options = a.id_questions_options
         WHERE a.id_users = :id_users AND a.id_test = :id_test AND a.id_test_try = :id_test_try'
    );
    $stmt->execute(['id_users' => $idUsuario, 'id_test' => $idTest, 'id_test_try' => $idTestTry]);
    $respuestas = $stmt->fetchAll();

    $puntajeObtenido = 0;
    foreach ($respuestas as $r) {
        if ((int) $r['is_it_co'] === 1) {
            $puntajeObtenido += (int) $r['assigned_score'];
        }
    }

    $puntajeMaximo = cursoPuntajeMaximo($pdo, $idTest);
    $porcentaje = $puntajeMaximo > 0 ? round(($puntajeObtenido / $puntajeMaximo) * 100, 1) : 0.0;

    return [
        'puntaje_obtenido' => $puntajeObtenido,
        'puntaje_maximo'   => $puntajeMaximo,
        'porcentaje'       => $porcentaje,
        'cantidad_respuestas' => count($respuestas),
    ];
}

/* =========================================================
   CERTIFICADOS
   ========================================================= */

function certificadoCrear(PDO $pdo, int $idAsignacion, string $code, string $filePath, string $creadoPor): int
{
    $stmt = $pdo->prepare(
        'INSERT INTO certificates (id_user_test_assigned, code, file_path, issued_at, state, created_by, date_create, last_update)
         VALUES (:id_user_test_assigned, :code, :file_path, NOW(), 1, :created_by, NOW(), NOW())'
    );
    $stmt->execute([
        'id_user_test_assigned' => $idAsignacion,
        'code'                  => $code,
        'file_path'             => $filePath,
        'created_by'            => $creadoPor,
    ]);

    return (int) $pdo->lastInsertId();
}

function certificadoObtenerPorAsignacion(PDO $pdo, int $idAsignacion): ?array
{
    $stmt = $pdo->prepare(
        'SELECT * FROM certificates WHERE id_user_test_assigned = :id AND state = 1 ORDER BY id_certificate DESC LIMIT 1'
    );
    $stmt->execute(['id' => $idAsignacion]);
    $row = $stmt->fetch();

    return $row ?: null;
}

/* =========================================================
   USUARIOS DE LA EMPRESA (para el selector de "a quién asignar")
   ========================================================= */

function usuariosActivosDeEmpresa(PDO $pdo, int $idCompany): array
{
    $stmt = $pdo->prepare(
        'SELECT id_users, name, lastname FROM users WHERE id_company = :id_company AND state = 1 ORDER BY lastname, name'
    );
    $stmt->execute(['id_company' => $idCompany]);

    return $stmt->fetchAll();
}
