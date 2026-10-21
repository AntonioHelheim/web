<?php
/** Porcentajes de logro mostrados en la bienvenida del trabajador. */
function logrosPorModulo(PDO $pdo, string $idUsuario): array
{
    $result = ['health_safety' => 0, 'estandar_16' => 0, 'ti' => 0, 'medio_ambiente' => 0];
    try {
        $hasModule = false;
        $stmtCol = $pdo->prepare("SHOW COLUMNS FROM company_test LIKE 'module_code'");
        $stmtCol->execute();
        $hasModule = (bool)$stmtCol->fetch();

        $sql = 'SELECT COUNT(DISTINCT a.id_user_test_assigned) AS total,
                       COUNT(DISTINCT CASE WHEN a.state IN (2,3) OR (c.id_certificate IS NOT NULL AND c.state = 1) THEN a.id_user_test_assigned END) AS completed
                FROM users_test_assigned a
                INNER JOIN company_test t ON t.id_test = a.id_test
                LEFT JOIN certificates c ON c.id_user_test_assigned = a.id_user_test_assigned AND c.state = 1
                WHERE a.id_users = :id_users';
        if ($hasModule) $sql .= " AND t.module_code = 'health_safety'";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['id_users' => $idUsuario]);
        $row = $stmt->fetch() ?: [];
        $total = (int)($row['total'] ?? 0);
        $completed = (int)($row['completed'] ?? 0);
        $result['health_safety'] = $total > 0 ? max(0, min(100, (int)round(($completed * 100) / $total))) : 0;
    } catch (Throwable $e) {
        error_log('LogrosRepository::logrosPorModulo: ' . $e->getMessage());
    }
    // Estándar 16, TI y Medio Ambiente se conectarán aquí cuando existan sus fuentes de actividad.
    return $result;
}
