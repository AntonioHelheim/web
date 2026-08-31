<?php
/**
 * lib/repositorios/EmpresaRepository.php
 * Todas las consultas SQL de la entidad "company" (empresas), agrupadas en
 * un solo lugar. No es un ORM: son funciones simples que reciben el $pdo
 * ya conectado. El objetivo es que "traer las empresas activas de esta
 * compañía" se escriba una sola vez, no con variaciones sutiles repetidas
 * en cada endpoint.
 *
 * Este archivo es la plantilla a copiar para TrabajadorRepository,
 * EventoRepository, etc. cuando se construyan esos módulos.
 */

/**
 * Lista empresas activas. Si se pasa $idCompanyFiltro, devuelve solo esa
 * empresa (para roles cliente/trabajador, que no deben ver otras).
 */
function empresaListar(PDO $pdo, ?int $idCompanyFiltro = null): array
{
    if ($idCompanyFiltro !== null) {
        $stmt = $pdo->prepare(
            'SELECT id_company, rut, razon_social, address, email, state, date_create
             FROM company
             WHERE id_company = :id_company AND state = 1'
        );
        $stmt->execute(['id_company' => $idCompanyFiltro]);
    } else {
        $stmt = $pdo->query(
            'SELECT id_company, rut, razon_social, address, email, state, date_create
             FROM company
             WHERE state = 1
             ORDER BY razon_social'
        );
    }

    return $stmt->fetchAll();
}

function empresaObtenerPorId(PDO $pdo, int $idCompany): ?array
{
    $stmt = $pdo->prepare('SELECT * FROM company WHERE id_company = :id_company LIMIT 1');
    $stmt->execute(['id_company' => $idCompany]);
    $row = $stmt->fetch();

    return $row ?: null;
}

function empresaObtenerPorRut(PDO $pdo, string $rut): ?array
{
    $stmt = $pdo->prepare('SELECT * FROM company WHERE rut = :rut LIMIT 1');
    $stmt->execute(['rut' => $rut]);
    $row = $stmt->fetch();

    return $row ?: null;
}

function empresaCrear(PDO $pdo, array $datos, string $creadoPor): int
{
    $stmt = $pdo->prepare(
        'INSERT INTO company (rut, razon_social, address, email, state, created_by, date_create, last_update)
         VALUES (:rut, :razon_social, :address, :email, 1, :created_by, NOW(), NOW())'
    );
    $stmt->execute([
        'rut'          => $datos['rut'],
        'razon_social' => $datos['razon_social'],
        'address'      => $datos['address'],
        'email'        => $datos['email'],
        'created_by'   => $creadoPor,
    ]);

    return (int) $pdo->lastInsertId();
}

function empresaActualizar(PDO $pdo, int $idCompany, array $datos): bool
{
    $stmt = $pdo->prepare(
        'UPDATE company
         SET razon_social = :razon_social, address = :address, email = :email, last_update = NOW()
         WHERE id_company = :id_company'
    );

    return $stmt->execute([
        'razon_social' => $datos['razon_social'],
        'address'      => $datos['address'],
        'email'        => $datos['email'],
        'id_company'   => $idCompany,
    ]);
}

/**
 * Baja lógica (state = 0), nunca DELETE físico — mantiene la trazabilidad
 * y no rompe las llaves foráneas de las tablas que ya dependen de company.
 */
function empresaDesactivar(PDO $pdo, int $idCompany): bool
{
    $stmt = $pdo->prepare('UPDATE company SET state = 0, last_update = NOW() WHERE id_company = :id_company');
    return $stmt->execute(['id_company' => $idCompany]);
}