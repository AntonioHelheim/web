<?php
/**
 * lib/repositorios/TrabajadorRepository.php
 * Todas las consultas SQL de la entidad "workers" (perfil trabajador),
 * agrupadas en un solo lugar, siguiendo la misma plantilla que
 * EmpresaRepository.php / ProyectoRepository.php.
 *
 * Con este archivo, api/proyectos/trabajadores-buscar.php y
 * trabajadores-asociar.php (que ya estaban construidos esperando esto)
 * empiezan a tener datos reales para encontrar y asociar.
 */

function trabajadorListarPorEmpresa(PDO $pdo, int $idCompany, string $busqueda = '', ?int $filtroEstado = null): array
{
    $sql = 'SELECT id_worker, id_company, rut, name, lastname, email, phone, position, photo_path, state, date_create, last_update
            FROM workers
            WHERE id_company = :id_company';
    $params = ['id_company' => $idCompany];

    if ($busqueda !== '') {
        $sql .= ' AND (rut LIKE :busqueda1 OR name LIKE :busqueda2 OR lastname LIKE :busqueda3 OR position LIKE :busqueda4)';
        $like = '%' . $busqueda . '%';
        $params['busqueda1'] = $like;
        $params['busqueda2'] = $like;
        $params['busqueda3'] = $like;
        $params['busqueda4'] = $like;
    }

    if ($filtroEstado !== null) {
        $sql .= ' AND state = :state';
        $params['state'] = $filtroEstado;
    }

    $sql .= ' ORDER BY lastname, name';

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    return $stmt->fetchAll();
}

function trabajadorObtenerPorId(PDO $pdo, int $idWorker): ?array
{
    $stmt = $pdo->prepare('SELECT * FROM workers WHERE id_worker = :id LIMIT 1');
    $stmt->execute(['id' => $idWorker]);
    $row = $stmt->fetch();

    return $row ?: null;
}

/**
 * Respeta la restricción real de la tabla (uq_worker_rut_company: rut +
 * id_company) — el mismo RUT SÍ puede existir en dos empresas distintas
 * (un trabajador que presta servicios a más de un cliente), lo que no
 * puede repetirse es el mismo RUT dos veces dentro de la misma empresa.
 */
function trabajadorExisteRutEnEmpresa(PDO $pdo, int $idCompany, string $rut, ?int $idWorkerExcluir = null): bool
{
    $sql = 'SELECT id_worker FROM workers WHERE id_company = :id_company AND rut = :rut';
    $params = ['id_company' => $idCompany, 'rut' => $rut];

    if ($idWorkerExcluir !== null) {
        $sql .= ' AND id_worker != :id_excluir';
        $params['id_excluir'] = $idWorkerExcluir;
    }

    $stmt = $pdo->prepare($sql . ' LIMIT 1');
    $stmt->execute($params);

    return (bool) $stmt->fetch();
}

function trabajadorCrear(PDO $pdo, array $datos, string $creadoPor): int
{
    $stmt = $pdo->prepare(
        'INSERT INTO workers (id_company, rut, name, lastname, email, phone, position, state, created_by, date_create, last_update)
         VALUES (:id_company, :rut, :name, :lastname, :email, :phone, :position, 1, :created_by, NOW(), NOW())'
    );
    $stmt->execute([
        'id_company' => $datos['id_company'],
        'rut'        => $datos['rut'],
        'name'       => $datos['name'],
        'lastname'   => $datos['lastname'],
        'email'      => $datos['email'] !== '' ? $datos['email'] : null,
        'phone'      => $datos['phone'] !== '' ? $datos['phone'] : null,
        'position'   => $datos['position'] !== '' ? $datos['position'] : null,
        'created_by' => $creadoPor,
    ]);

    return (int) $pdo->lastInsertId();
}

function trabajadorActualizar(PDO $pdo, int $idWorker, array $datos): bool
{
    $stmt = $pdo->prepare(
        'UPDATE workers
         SET name = :name, lastname = :lastname, email = :email, phone = :phone, position = :position, last_update = NOW()
         WHERE id_worker = :id_worker'
    );

    return $stmt->execute([
        'name'      => $datos['name'],
        'lastname'  => $datos['lastname'],
        'email'     => $datos['email'] !== '' ? $datos['email'] : null,
        'phone'     => $datos['phone'] !== '' ? $datos['phone'] : null,
        'position'  => $datos['position'] !== '' ? $datos['position'] : null,
        'id_worker' => $idWorker,
    ]);
}

/**
 * Baja lógica, nunca DELETE físico — security_events, worker_projects y
 * users (id_worker nullable) pueden depender de un trabajador.
 */
function trabajadorCambiarEstado(PDO $pdo, int $idWorker, int $nuevoEstado): bool
{
    $stmt = $pdo->prepare('UPDATE workers SET state = :state, last_update = NOW() WHERE id_worker = :id_worker');
    return $stmt->execute(['state' => $nuevoEstado, 'id_worker' => $idWorker]);
}

function trabajadorActualizarFoto(PDO $pdo, int $idWorker, string $photoPath): bool
{
    $stmt = $pdo->prepare('UPDATE workers SET photo_path = :photo_path, last_update = NOW() WHERE id_worker = :id_worker');
    return $stmt->execute(['photo_path' => $photoPath, 'id_worker' => $idWorker]);
}
