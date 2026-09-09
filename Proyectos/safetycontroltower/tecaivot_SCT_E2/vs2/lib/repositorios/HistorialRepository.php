<?php
/** Safety Control Tower - consultas del Historial de cambios. */

function historialListarEmpresas(PDO $pdo): array
{
    $stmt = $pdo->query(
        'SELECT id_company, razon_social, state
         FROM company
         ORDER BY state DESC, razon_social ASC, id_company ASC'
    );
    return $stmt->fetchAll();
}

function historialEmpresaExiste(PDO $pdo, int $idCompany): bool
{
    $stmt = $pdo->prepare('SELECT 1 FROM company WHERE id_company=:id_company LIMIT 1');
    $stmt->execute(['id_company' => $idCompany]);
    return (bool) $stmt->fetchColumn();
}

/** @return array{where:string,params:array<string,mixed>} */
function historialBuildFilter(array $filters): array
{
    $where = ['1=1'];
    $params = [];

    if (!empty($filters['id_company'])) {
        $where[] = 'ch.id_company = :id_company';
        $params['id_company'] = (int) $filters['id_company'];
    }
    if (!empty($filters['module'])) {
        $where[] = 'ch.module = :module';
        $params['module'] = (string) $filters['module'];
    }
    if (!empty($filters['action'])) {
        $where[] = 'ch.action = :action';
        $params['action'] = (string) $filters['action'];
    }
    if (!empty($filters['changed_by'])) {
        $where[] = 'ch.changed_by = :changed_by';
        $params['changed_by'] = (string) $filters['changed_by'];
    }
    if (!empty($filters['date_from'])) {
        $where[] = 'ch.changed_at >= :date_from';
        $params['date_from'] = (string) $filters['date_from'] . ' 00:00:00';
    }
    if (!empty($filters['date_to'])) {
        $where[] = 'ch.changed_at <= :date_to';
        $params['date_to'] = (string) $filters['date_to'] . ' 23:59:59';
    }
    if (!empty($filters['search'])) {
        $where[] = '(ch.record_label LIKE :search1 OR ch.table_name LIKE :search2 OR ch.record_id LIKE :search3 OR ch.field_name LIKE :search4 OR ch.old_value LIKE :search5 OR ch.new_value LIKE :search6 OR ch.changed_by LIKE :search7)';
        $like = '%' . (string) $filters['search'] . '%';
        foreach (range(1, 7) as $i) $params['search' . $i] = $like;
    }

    return ['where' => implode(' AND ', $where), 'params' => $params];
}

function historialContar(PDO $pdo, array $filters): int
{
    $f = historialBuildFilter($filters);
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM change_history ch WHERE ' . $f['where']);
    $stmt->execute($f['params']);
    return (int) $stmt->fetchColumn();
}

function historialListar(PDO $pdo, array $filters, int $limit=50, int $offset=0): array
{
    $limit = max(1, min(100, $limit));
    $offset = max(0, $offset);
    $f = historialBuildFilter($filters);
    $sql =
        'SELECT ch.id_change,ch.id_company,ch.module,ch.action,ch.table_name,ch.record_id,
                ch.record_label,ch.field_name,ch.old_value,ch.new_value,ch.changed_by,
                ch.changed_at,ch.request_id,c.razon_social AS company_name
         FROM change_history ch
         LEFT JOIN company c ON c.id_company=ch.id_company
         WHERE ' . $f['where'] . '
         ORDER BY ch.changed_at DESC,ch.id_change DESC
         LIMIT ' . $limit . ' OFFSET ' . $offset;
    $stmt = $pdo->prepare($sql);
    $stmt->execute($f['params']);
    return $stmt->fetchAll();
}

function historialCatalogos(PDO $pdo, ?int $idCompany): array
{
    $params = [];
    $where = '1=1';
    if ($idCompany !== null) {
        $where = 'id_company = :id_company';
        $params['id_company'] = $idCompany;
    }

    $stmtModules = $pdo->prepare('SELECT DISTINCT module FROM change_history WHERE ' . $where . ' ORDER BY module');
    $stmtModules->execute($params);
    $stmtActors = $pdo->prepare('SELECT DISTINCT changed_by FROM change_history WHERE ' . $where . ' ORDER BY changed_by');
    $stmtActors->execute($params);
    $stmtActions = $pdo->prepare('SELECT DISTINCT action FROM change_history WHERE ' . $where . ' ORDER BY action');
    $stmtActions->execute($params);

    return [
        'modules' => array_values(array_filter(array_map('strval', array_column($stmtModules->fetchAll(), 'module')))),
        'actors' => array_values(array_filter(array_map('strval', array_column($stmtActors->fetchAll(), 'changed_by')))),
        'actions' => array_values(array_filter(array_map('strval', array_column($stmtActions->fetchAll(), 'action')))),
    ];
}

function historialResumen(PDO $pdo, array $filters): array
{
    $f = historialBuildFilter($filters);
    $stmt = $pdo->prepare(
        'SELECT COUNT(*) AS total_rows,
                COUNT(DISTINCT request_id) AS operations,
                COUNT(DISTINCT changed_by) AS actors,
                SUM(CASE WHEN DATE(changed_at)=CURDATE() THEN 1 ELSE 0 END) AS today_rows
         FROM change_history ch
         WHERE ' . $f['where']
    );
    $stmt->execute($f['params']);
    $row = $stmt->fetch() ?: [];
    return [
        'total_rows' => (int) ($row['total_rows'] ?? 0),
        'operations' => (int) ($row['operations'] ?? 0),
        'actors' => (int) ($row['actors'] ?? 0),
        'today_rows' => (int) ($row['today_rows'] ?? 0),
    ];
}
