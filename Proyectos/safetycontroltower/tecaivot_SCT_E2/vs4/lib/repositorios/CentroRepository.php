<?php
/**
 * lib/repositorios/CentroRepository.php
 * Todas las consultas SQL de la entidad "company_center" (centros/sedes),
 * agrupadas en un solo lugar, siguiendo la misma plantilla que ya dejó
 * EmpresaRepository.php.
 *
 * A diferencia de "description" en projects (nullable), en company_center
 * la columna description es NOT NULL — se respeta esa restricción acá
 * en vez de "arreglarla" silenciosamente, para no generar un desfase
 * entre lo que valida el código y lo que exige la tabla real.
 */

function centroListarPorEmpresa(PDO $pdo, int $idCompany): array
{
    $stmt = $pdo->prepare(
        'SELECT id_company_center, id_company, name, description, state, date_create, last_update
         FROM company_center
         WHERE id_company = :id_company AND state = 1
         ORDER BY name'
    );
    $stmt->execute(['id_company' => $idCompany]);

    return $stmt->fetchAll();
}

function centroObtenerPorId(PDO $pdo, int $idCompanyCenter): ?array
{
    $stmt = $pdo->prepare('SELECT * FROM company_center WHERE id_company_center = :id LIMIT 1');
    $stmt->execute(['id' => $idCompanyCenter]);
    $row = $stmt->fetch();

    return $row ?: null;
}

function centroExisteNombreEnEmpresa(PDO $pdo, int $idCompany, string $name, ?int $idCentroExcluir = null): bool
{
    $sql = 'SELECT id_company_center FROM company_center WHERE id_company = :id_company AND name = :name AND state = 1';
    $params = ['id_company' => $idCompany, 'name' => $name];

    if ($idCentroExcluir !== null) {
        $sql .= ' AND id_company_center != :id_excluir';
        $params['id_excluir'] = $idCentroExcluir;
    }

    $stmt = $pdo->prepare($sql . ' LIMIT 1');
    $stmt->execute($params);

    return (bool) $stmt->fetch();
}

function centroCrear(PDO $pdo, array $datos, string $creadoPor): int
{
    $stmt = $pdo->prepare(
        'INSERT INTO company_center (id_company, name, description, state, create_by, date_create, last_update)
         VALUES (:id_company, :name, :description, 1, :create_by, NOW(), NOW())'
    );
    $stmt->execute([
        'id_company'  => $datos['id_company'],
        'name'        => $datos['name'],
        'description' => $datos['description'],
        'create_by'   => $creadoPor,
    ]);

    return (int) $pdo->lastInsertId();
}

function centroActualizar(PDO $pdo, int $idCompanyCenter, array $datos): bool
{
    $stmt = $pdo->prepare(
        'UPDATE company_center
         SET name = :name, description = :description, last_update = NOW()
         WHERE id_company_center = :id'
    );

    return $stmt->execute([
        'name'        => $datos['name'],
        'description' => $datos['description'],
        'id'          => $idCompanyCenter,
    ]);
}

/**
 * Baja lógica (state = 0), nunca DELETE físico — security_events depende
 * de company_center vía FK (fk_events_center), así que borrar físicamente
 * un centro con eventos asociados rompería esa relación.
 */
function centroCambiarEstado(PDO $pdo, int $idCompanyCenter, int $nuevoEstado): bool
{
    $stmt = $pdo->prepare(
        'UPDATE company_center SET state = :state, last_update = NOW() WHERE id_company_center = :id'
    );

    return $stmt->execute(['state' => $nuevoEstado, 'id' => $idCompanyCenter]);
}
