<?php
/**
 * lib/repositorios/ProyectoRepository.php
 * Todas las consultas SQL de la entidad "projects" (proyectos), agrupadas
 * en un solo lugar, siguiendo la misma plantilla que ya dejó
 * EmpresaRepository.php. Incluye además la relación con trabajadores
 * (tabla puente worker_projects), ya que "asociar trabajadores a un
 * proyecto" es parte del mismo módulo.
 *
 * IMPORTANTE: las funciones de este archivo que devuelven datos de
 * "workers" son de solo lectura (buscar / listar), pensadas para que el
 * módulo de Proyectos pueda asociar trabajadores ya existentes. La
 * gestión completa de la ficha de trabajador (alta, edición, foto, etc.)
 * todavía no está construida — cuando se construya el módulo de
 * Trabajadores, ese será el lugar para el WorkerRepository.php completo;
 * este archivo no debe crecer para cubrir ese alcance.
 */

/**
 * Lista proyectos activos de una empresa.
 */
function proyectoListarPorEmpresa(PDO $pdo, int $idCompany): array
{
    $stmt = $pdo->prepare(
        'SELECT id_project, id_company, name, description, state, date_create, last_update
         FROM projects
         WHERE id_company = :id_company AND state = 1
         ORDER BY name'
    );
    $stmt->execute(['id_company' => $idCompany]);

    return $stmt->fetchAll();
}

function proyectoObtenerPorId(PDO $pdo, int $idProject): ?array
{
    $stmt = $pdo->prepare('SELECT * FROM projects WHERE id_project = :id_project LIMIT 1');
    $stmt->execute(['id_project' => $idProject]);
    $row = $stmt->fetch();

    return $row ?: null;
}

/**
 * Evita duplicar el nombre de un proyecto dentro de la misma empresa
 * (no hay restricción UNIQUE en la tabla, así que se valida a mano).
 */
function proyectoExisteNombreEnEmpresa(PDO $pdo, int $idCompany, string $name, ?int $idProjectExcluir = null): bool
{
    $sql = 'SELECT id_project FROM projects WHERE id_company = :id_company AND name = :name AND state = 1';
    $params = ['id_company' => $idCompany, 'name' => $name];

    if ($idProjectExcluir !== null) {
        $sql .= ' AND id_project != :id_project_excluir';
        $params['id_project_excluir'] = $idProjectExcluir;
    }

    $stmt = $pdo->prepare($sql . ' LIMIT 1');
    $stmt->execute($params);

    return (bool) $stmt->fetch();
}

function proyectoCrear(PDO $pdo, array $datos, string $creadoPor): int
{
    $stmt = $pdo->prepare(
        'INSERT INTO projects (id_company, name, description, state, created_by, date_create, last_update)
         VALUES (:id_company, :name, :description, 1, :created_by, NOW(), NOW())'
    );
    $stmt->execute([
        'id_company'  => $datos['id_company'],
        'name'        => $datos['name'],
        'description' => $datos['description'] !== '' ? $datos['description'] : null,
        'created_by'  => $creadoPor,
    ]);

    return (int) $pdo->lastInsertId();
}

function proyectoActualizar(PDO $pdo, int $idProject, array $datos): bool
{
    $stmt = $pdo->prepare(
        'UPDATE projects
         SET name = :name, description = :description, last_update = NOW()
         WHERE id_project = :id_project'
    );

    return $stmt->execute([
        'name'        => $datos['name'],
        'description' => $datos['description'] !== '' ? $datos['description'] : null,
        'id_project'  => $idProject,
    ]);
}

/**
 * Baja lógica (state = 0), nunca DELETE físico — mismo criterio que
 * empresaDesactivar(): mantiene trazabilidad y no rompe las FK de
 * worker_projects / security_events / programs que ya dependen de
 * projects.
 */
function proyectoCambiarEstado(PDO $pdo, int $idProject, int $nuevoEstado): bool
{
    $stmt = $pdo->prepare('UPDATE projects SET state = :state, last_update = NOW() WHERE id_project = :id_project');
    return $stmt->execute(['state' => $nuevoEstado, 'id_project' => $idProject]);
}

/* =========================================================
   ASOCIACIÓN TRABAJADOR <-> PROYECTO (worker_projects)
   ========================================================= */

/**
 * Trabajadores ya asociados a un proyecto (para listarlos en la ficha
 * del proyecto). Trae los datos básicos del trabajador, no toda la ficha.
 */
function proyectoListarTrabajadoresAsociados(PDO $pdo, int $idProject): array
{
    $stmt = $pdo->prepare(
        'SELECT w.id_worker, w.rut, w.name, w.lastname, w.position, w.state AS worker_state, wp.date_create AS fecha_asociacion
         FROM worker_projects wp
         INNER JOIN workers w ON w.id_worker = wp.id_worker
         WHERE wp.id_project = :id_project
         ORDER BY w.lastname, w.name'
    );
    $stmt->execute(['id_project' => $idProject]);

    return $stmt->fetchAll();
}

/**
 * Busca trabajadores ACTIVOS de una empresa que todavía no estén
 * asociados a este proyecto, por RUT o nombre/apellido (para el buscador
 * de "agregar trabajador" en la ficha del proyecto).
 *
 * Nota: hoy la tabla workers está vacía porque el módulo de Trabajadores
 * todavía no se construye — esta función queda lista y probada para el
 * momento en que sí haya datos, no requiere cambios cuando eso pase.
 */
function proyectoBuscarTrabajadoresDisponibles(PDO $pdo, int $idCompany, int $idProject, string $busqueda): array
{
    $like = '%' . $busqueda . '%';

    $stmt = $pdo->prepare(
        'SELECT w.id_worker, w.rut, w.name, w.lastname, w.position
         FROM workers w
         WHERE w.id_company = :id_company
           AND w.state = 1
           AND (w.rut LIKE :busqueda1 OR w.name LIKE :busqueda2 OR w.lastname LIKE :busqueda3)
           AND NOT EXISTS (
               SELECT 1 FROM worker_projects wp
               WHERE wp.id_worker = w.id_worker AND wp.id_project = :id_project
           )
         ORDER BY w.lastname, w.name
         LIMIT 20'
    );
    $stmt->execute([
        'id_company' => $idCompany,
        'busqueda1'  => $like,
        'busqueda2'  => $like,
        'busqueda3'  => $like,
        'id_project' => $idProject,
    ]);

    return $stmt->fetchAll();
}

function proyectoTrabajadorYaAsociado(PDO $pdo, int $idProject, int $idWorker): bool
{
    $stmt = $pdo->prepare(
        'SELECT 1 FROM worker_projects WHERE id_project = :id_project AND id_worker = :id_worker LIMIT 1'
    );
    $stmt->execute(['id_project' => $idProject, 'id_worker' => $idWorker]);

    return (bool) $stmt->fetch();
}

/**
 * worker_projects no tiene columna "state": es una tabla puente pura, así
 * que asociar/desasociar es INSERT/DELETE directo, no baja lógica.
 */
function proyectoAsociarTrabajador(PDO $pdo, int $idProject, int $idWorker): bool
{
    $stmt = $pdo->prepare(
        'INSERT INTO worker_projects (id_worker, id_project, date_create) VALUES (:id_worker, :id_project, NOW())'
    );

    return $stmt->execute(['id_worker' => $idWorker, 'id_project' => $idProject]);
}

function proyectoDesasociarTrabajador(PDO $pdo, int $idProject, int $idWorker): bool
{
    $stmt = $pdo->prepare(
        'DELETE FROM worker_projects WHERE id_project = :id_project AND id_worker = :id_worker'
    );

    return $stmt->execute(['id_project' => $idProject, 'id_worker' => $idWorker]);
}

/**
 * Verifica que un id_worker exista, esté activo, y pertenezca a la misma
 * empresa que el proyecto — evita asociar trabajadores de otra empresa
 * por error (o por un id manipulado a mano en el request).
 */
function proyectoTrabajadorPerteneceAEmpresa(PDO $pdo, int $idWorker, int $idCompany): bool
{
    $stmt = $pdo->prepare(
        'SELECT 1 FROM workers WHERE id_worker = :id_worker AND id_company = :id_company AND state = 1 LIMIT 1'
    );
    $stmt->execute(['id_worker' => $idWorker, 'id_company' => $idCompany]);

    return (bool) $stmt->fetch();
}
