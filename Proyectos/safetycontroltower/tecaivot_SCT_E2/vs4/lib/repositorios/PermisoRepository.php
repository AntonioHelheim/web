<?php
/**
 * Safety Control Tower - Permisos granulares (Etapa 3)
 *
 * Capa de acceso a datos para permissions / role_permissions.
 */

function permisoListarEmpresas(PDO $pdo): array
{
    $stmt = $pdo->query(
        'SELECT id_company, razon_social, state
         FROM company
         WHERE state = 1
         ORDER BY razon_social ASC, id_company ASC'
    );
    return $stmt->fetchAll();
}

function permisoListarRolesEmpresa(PDO $pdo, int $idCompany): array
{
    $stmt = $pdo->prepare(
        'SELECT id_role_group, id_company, name, description, state
         FROM users_role_group
         WHERE id_company = :id_company
           AND state = 1
         ORDER BY id_role_group ASC'
    );
    $stmt->execute(['id_company' => $idCompany]);

    $rows = [];
    foreach ($stmt->fetchAll() as $row) {
        $canonical = canonicalRoleName((string) ($row['name'] ?? ''));
        if (!isset(SCT_ROLE_POLICY[$canonical])) {
            continue;
        }
        $row['canonical_name'] = $canonical;
        $row['display_name'] = roleDisplayLabel($canonical);
        $rows[] = $row;
    }

    usort($rows, static function (array $a, array $b): int {
        $la = roleLevelFromName((string) $a['canonical_name']) ?? PHP_INT_MAX;
        $lb = roleLevelFromName((string) $b['canonical_name']) ?? PHP_INT_MAX;
        return $la <=> $lb;
    });
    return $rows;
}

function permisoObtenerRol(PDO $pdo, int $idRoleGroup): ?array
{
    $stmt = $pdo->prepare(
        'SELECT id_role_group, id_company, name, description, state
         FROM users_role_group
         WHERE id_role_group = :id_role_group
           AND state = 1
         LIMIT 1'
    );
    $stmt->execute(['id_role_group' => $idRoleGroup]);
    $row = $stmt->fetch();
    if (!$row) return null;

    $canonical = canonicalRoleName((string) ($row['name'] ?? ''));
    if (!isset(SCT_ROLE_POLICY[$canonical])) return null;
    $row['canonical_name'] = $canonical;
    $row['display_name'] = roleDisplayLabel($canonical);
    return $row;
}

function permisoListarCatalogo(PDO $pdo): array
{
    $stmt = $pdo->prepare(
        "SELECT id_permission, code, description
         FROM permissions
         WHERE code <> :marker
           AND code NOT IN ('companies.manage_all','users.manage','workers.manage','projects.manage','centers.manage','events.manage','induction.manage','audits.manage','self_assessments.manage','dynamic_forms.manage','protocols.manage')
         ORDER BY code ASC"
    );
    $stmt->execute(['marker' => 'system.permissions.enabled']);
    return $stmt->fetchAll();
}

function permisoListarIdsRol(PDO $pdo, int $idRoleGroup): array
{
    $stmt = $pdo->prepare(
        "SELECT rp.id_permission
         FROM role_permissions rp
         INNER JOIN permissions p ON p.id_permission = rp.id_permission
         WHERE rp.id_role_group = :id_role_group
           AND p.code <> :marker
           AND p.code NOT IN ('companies.manage_all','users.manage','workers.manage','projects.manage','centers.manage','events.manage','induction.manage','audits.manage','self_assessments.manage','dynamic_forms.manage','protocols.manage')
         ORDER BY rp.id_permission ASC"
    );
    $stmt->execute([
        'id_role_group' => $idRoleGroup,
        'marker' => 'system.permissions.enabled',
    ]);
    return array_map('intval', array_column($stmt->fetchAll(), 'id_permission'));
}

function permisoIdsValidos(PDO $pdo, array $ids): array
{
    $ids = array_values(array_unique(array_filter(array_map('intval', $ids), static function (int $id): bool {
        return $id > 0;
    })));
    if (!$ids) return [];

    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $stmt = $pdo->prepare(
        'SELECT id_permission
         FROM permissions
         WHERE id_permission IN (' . $placeholders . ')
           AND code <> ?'
    );
    $params = $ids;
    $params[] = 'system.permissions.enabled';
    $stmt->execute($params);
    return array_map('intval', array_column($stmt->fetchAll(), 'id_permission'));
}


function permisoCodigosPorIds(PDO $pdo, array $ids): array
{
    $ids = array_values(array_unique(array_filter(array_map('intval', $ids), static function (int $id): bool {
        return $id > 0;
    })));
    if (!$ids) return [];
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $stmt = $pdo->prepare('SELECT id_permission, code FROM permissions WHERE id_permission IN (' . $placeholders . ')');
    $stmt->execute($ids);
    $out = [];
    foreach ($stmt->fetchAll() as $row) {
        $out[(int)$row['id_permission']] = (string)$row['code'];
    }
    return $out;
}

function permisoIdPorCodigo(PDO $pdo, string $code): ?int
{
    $stmt = $pdo->prepare('SELECT id_permission FROM permissions WHERE code = :code LIMIT 1');
    $stmt->execute(['code' => $code]);
    $id = $stmt->fetchColumn();
    return $id === false ? null : (int) $id;
}

/**
 * Reemplaza la matriz de un rol en una sola transacción.
 * El rol administrador_completo conserva siempre permisos de recuperación
 * para evitar bloquear la administración global desde la propia UI.
 */
function permisoGuardarMatrizRol(PDO $pdo, int $idRoleGroup, array $permissionIds, string $actor): array
{
    $role = permisoObtenerRol($pdo, $idRoleGroup);
    if (!$role) {
        throw new RuntimeException('El rol seleccionado no existe o no está activo.');
    }

    $validIds = permisoIdsValidos($pdo, $permissionIds);
    if ((string) $role['canonical_name'] === 'administrador_completo') {
        foreach (['permissions.manage', 'companies.view_all', 'dashboard.global', 'dashboard.view'] as $code) {
            $id = permisoIdPorCodigo($pdo, $code);
            if ($id !== null) $validIds[] = $id;
        }
        $validIds = array_values(array_unique($validIds));
    }

    $pdo->beginTransaction();
    try {
        $stmtDelete = $pdo->prepare('DELETE FROM role_permissions WHERE id_role_group = :id_role_group');
        $stmtDelete->execute(['id_role_group' => $idRoleGroup]);

        if ($validIds) {
            $stmtInsert = $pdo->prepare(
                'INSERT INTO role_permissions (id_role_group, id_permission)
                 VALUES (:id_role_group, :id_permission)'
            );
            foreach ($validIds as $idPermission) {
                $stmtInsert->execute([
                    'id_role_group' => $idRoleGroup,
                    'id_permission' => $idPermission,
                ]);
            }
        }

        $pdo->commit();
        return [
            'id_role_group' => $idRoleGroup,
            'permissions_count' => count($validIds),
            'updated_by' => $actor,
        ];
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        throw $e;
    }
}
