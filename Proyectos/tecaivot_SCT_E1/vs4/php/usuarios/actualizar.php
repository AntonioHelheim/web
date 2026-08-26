<?php
require __DIR__ . '/common.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    usuariosJsonResponse(405, false, 'Método no permitido.');
}

$input = usuariosReadJsonInput();
usuariosRequireCsrf($input);

$idUsers = strtolower(trim((string) ($input['id_users'] ?? '')));
$name = trim((string) ($input['name'] ?? ''));
$lastname = trim((string) ($input['lastname'] ?? ''));
$rut = trim((string) ($input['rut'] ?? ''));
$language = strtoupper(trim((string) ($input['language'] ?? '')));
$roleGroupId = (int) ($input['id_role_group'] ?? 0);

if ($idUsers === '' || !filter_var($idUsers, FILTER_VALIDATE_EMAIL)) {
    usuariosJsonResponse(400, false, 'Usuario objetivo inválido.');
}

if ($name === '' || $lastname === '' || $rut === '' || $language === '') {
    usuariosJsonResponse(400, false, 'Nombre, apellido, RUT e idioma son obligatorios.');
}

if ($roleGroupId <= 0) {
    usuariosJsonResponse(400, false, 'Debes seleccionar un rol válido.');
}

if (mb_strlen($idUsers) > 50 || mb_strlen($name) > 50 || mb_strlen($lastname) > 50 || mb_strlen($rut) > 10 || mb_strlen($language) > 11) {
    usuariosJsonResponse(400, false, 'Uno o más campos superan el largo permitido.');
}

try {
    $context = usuariosRequireAdminContext($pdo);

    $role = usuariosFindActiveRole($pdo, $context['company_id'], $roleGroupId);

    if (!$role) {
        usuariosJsonResponse(400, false, 'El rol seleccionado no está disponible para tu empresa.');
    }

    if ($idUsers === $context['session_user_id'] && mb_strtolower((string) $role['name']) !== 'administrador') {
        usuariosJsonResponse(400, false, 'No puedes quitarte tu propio rol administrador desde esta interfaz.');
    }

    $targetUser = usuariosFindUserInCompany($pdo, $idUsers, $context['company_id']);

    if (!$targetUser) {
        usuariosJsonResponse(404, false, 'Usuario no encontrado en tu empresa.');
    }

    $pdo->beginTransaction();

    $updateUserSql = '
        UPDATE users
        SET
            name = :name,
            lastname = :lastname,
            rut = :rut,
            language = :language,
            last_update = NOW()
        WHERE id_users = :id_users
          AND id_company = :id_company
        LIMIT 1
    ';

    $updateUserStmt = $pdo->prepare($updateUserSql);
    $updateUserStmt->execute([
        'name' => $name,
        'lastname' => $lastname,
        'rut' => $rut,
        'language' => $language,
        'id_users' => $idUsers,
        'id_company' => $context['company_id'],
    ]);

    $deactivateSql = '
        UPDATE users_role ur
        INNER JOIN users_role_group urg
            ON urg.id_role_group = ur.id_role_group
        SET
            ur.state = 0,
            ur.last_update = NOW()
        WHERE ur.id_users = :id_users
          AND ur.state = 1
          AND urg.id_company = :id_company
          AND ur.id_role_group <> :id_role_group
    ';

    $deactivateStmt = $pdo->prepare($deactivateSql);
    $deactivateStmt->execute([
        'id_users' => $idUsers,
        'id_company' => $context['company_id'],
        'id_role_group' => $roleGroupId,
    ]);

    $existingRoleSql = '
        SELECT id_users_role
        FROM users_role
        WHERE id_users = :id_users
          AND id_role_group = :id_role_group
        ORDER BY id_users_role DESC
        LIMIT 1
    ';

    $existingRoleStmt = $pdo->prepare($existingRoleSql);
    $existingRoleStmt->execute([
        'id_users' => $idUsers,
        'id_role_group' => $roleGroupId,
    ]);

    $existingRole = $existingRoleStmt->fetch();

    if ($existingRole) {
        $activateRoleStmt = $pdo->prepare('
            UPDATE users_role
            SET state = 1, last_update = NOW()
            WHERE id_users_role = :id_users_role
            LIMIT 1
        ');

        $activateRoleStmt->execute([
            'id_users_role' => (int) $existingRole['id_users_role'],
        ]);
    } else {
        $insertRoleSql = '
            INSERT INTO users_role (
                id_users,
                id_role_group,
                state,
                create_by,
                date_create,
                last_update
            ) VALUES (
                :id_users,
                :id_role_group,
                1,
                :create_by,
                NOW(),
                NOW()
            )
        ';

        $insertRoleStmt = $pdo->prepare($insertRoleSql);
        $insertRoleStmt->execute([
            'id_users' => $idUsers,
            'id_role_group' => $roleGroupId,
            'create_by' => $context['session_user_id'],
        ]);
    }

    $pdo->commit();

    usuariosJsonResponse(200, true, 'Usuario actualizado correctamente.');
} catch (PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    error_log('php/usuarios/actualizar.php: ' . $e->getMessage());
    usuariosJsonResponse(500, false, 'Error al actualizar usuario.');
}
