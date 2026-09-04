<?php
/**
 * lib/repositorios/DashboardRepository.php
 * Indicadores agregados para el Dashboard básico. No tiene tabla propia
 * — todo se calcula al vuelo con COUNT/SUM sobre las tablas que ya
 * construyeron los demás módulos (projects, workers, security_events,
 * users_test_assigned...). Es una capa de lectura pura, sin efectos
 * secundarios, pensada para poder cachear más adelante si hiciera
 * falta sin tener que tocar el resto del sistema.
 */

function dashboardTotalEmpresas(PDO $pdo): int
{
    $stmt = $pdo->query('SELECT COUNT(*) FROM company WHERE state = 1');
    return (int) $stmt->fetchColumn();
}

function dashboardTotalProyectos(PDO $pdo, int $idCompany): int
{
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM projects WHERE id_company = :id AND state = 1');
    $stmt->execute(['id' => $idCompany]);
    return (int) $stmt->fetchColumn();
}

function dashboardTotalCentros(PDO $pdo, int $idCompany): int
{
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM company_center WHERE id_company = :id AND state = 1');
    $stmt->execute(['id' => $idCompany]);
    return (int) $stmt->fetchColumn();
}

function dashboardTotalTrabajadores(PDO $pdo, int $idCompany): int
{
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM workers WHERE id_company = :id AND state = 1');
    $stmt->execute(['id' => $idCompany]);
    return (int) $stmt->fetchColumn();
}

/**
 * Indicadores de inducción: cuántas asignaciones hay por estado
 * (Pendiente/Aprobado/Reprobado) y la tasa de cumplimiento (aprobadas
 * sobre el total asignado, sin contar las que siguen pendientes con
 * intentos disponibles — esas todavía no tienen un resultado definitivo).
 */
function dashboardIndicadoresInduccion(PDO $pdo, int $idCompany): array
{
    $stmt = $pdo->prepare(
        'SELECT a.state, COUNT(*) AS cantidad
         FROM users_test_assigned a
         INNER JOIN company_test t ON t.id_test = a.id_test
         WHERE a.id_company = :id_company AND t.type = \'induccion\'
         GROUP BY a.state'
    );
    $stmt->execute(['id_company' => $idCompany]);

    $conteos = ['pendientes' => 0, 'aprobados' => 0, 'reprobados' => 0];
    foreach ($stmt->fetchAll() as $fila) {
        if ((int) $fila['state'] === 1) $conteos['pendientes'] = (int) $fila['cantidad'];
        if ((int) $fila['state'] === 2) $conteos['aprobados'] = (int) $fila['cantidad'];
        if ((int) $fila['state'] === 3) $conteos['reprobados'] = (int) $fila['cantidad'];
    }

    $totalAsignadas = $conteos['pendientes'] + $conteos['aprobados'] + $conteos['reprobados'];
    $totalConResultado = $conteos['aprobados'] + $conteos['reprobados'];
    $conteos['total_asignadas'] = $totalAsignadas;
    // Tasa de cumplimiento: de las que ya tienen resultado (no las que
    // siguen pendientes), qué porcentaje aprobó. Si nadie terminó
    // todavía, se muestra null en vez de forzar un 0% engañoso.
    $conteos['tasa_cumplimiento'] = $totalConResultado > 0
        ? round(($conteos['aprobados'] / $totalConResultado) * 100, 1)
        : null;

    return $conteos;
}

/**
 * Eventos: total, distribución por estado y por criticidad. Filtro
 * opcional por proyecto (uno de los "filtros básicos" que pide el plan).
 */
function dashboardIndicadoresEventos(PDO $pdo, int $idCompany, ?int $idProject = null): array
{
    $sqlBase = 'FROM security_events WHERE id_company = :id_company AND module = \'seguridad\'';
    $params = ['id_company' => $idCompany];
    if ($idProject) {
        $sqlBase .= ' AND id_project = :id_project';
        $params['id_project'] = $idProject;
    }

    $stmtTotal = $pdo->prepare('SELECT COUNT(*) ' . $sqlBase);
    $stmtTotal->execute($params);
    $total = (int) $stmtTotal->fetchColumn();

    $stmtEstado = $pdo->prepare('SELECT state, COUNT(*) AS cantidad ' . $sqlBase . ' GROUP BY state');
    $stmtEstado->execute($params);
    $porEstado = ['abierto' => 0, 'en_proceso' => 0, 'cerrado' => 0];
    foreach ($stmtEstado->fetchAll() as $fila) {
        if ((int) $fila['state'] === 1) $porEstado['abierto'] = (int) $fila['cantidad'];
        if ((int) $fila['state'] === 2) $porEstado['en_proceso'] = (int) $fila['cantidad'];
        if ((int) $fila['state'] === 3) $porEstado['cerrado'] = (int) $fila['cantidad'];
    }

    $stmtCriticidad = $pdo->prepare('SELECT criticality, COUNT(*) AS cantidad ' . $sqlBase . ' GROUP BY criticality');
    $stmtCriticidad->execute($params);
    $porCriticidad = ['baja' => 0, 'media' => 0, 'alta' => 0, 'critica' => 0];
    foreach ($stmtCriticidad->fetchAll() as $fila) {
        $porCriticidad[$fila['criticality']] = (int) $fila['cantidad'];
    }

    return [
        'total'          => $total,
        'por_estado'     => $porEstado,
        'por_criticidad' => $porCriticidad,
    ];
}
