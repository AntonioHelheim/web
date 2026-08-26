<?php
require __DIR__ . '/common.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    usuariosJsonResponse(405, false, 'Método no permitido.');
}

try {
    $context = usuariosRequireAdminContext($pdo);

    $sql = '
        SELECT id_role_group, name, description
        FROM users_role_group
        WHERE id_company = :id_company
          AND state = 1
        ORDER BY name ASC
    ';

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        'id_company' => $context['company_id'],
    ]);

    $rows = $stmt->fetchAll();

    $data = array_map(static function (array $row): array {
        return [
            'id_role_group' => (int) $row['id_role_group'],
            'name' => (string) $row['name'],
            'description' => (string) ($row['description'] ?? ''),
        ];
    }, $rows);

    usuariosJsonResponse(200, true, '', $data);
} catch (PDOException $e) {
    error_log('php/usuarios/roles.php: ' . $e->getMessage());
    usuariosJsonResponse(500, false, 'Error al cargar roles.');
}
