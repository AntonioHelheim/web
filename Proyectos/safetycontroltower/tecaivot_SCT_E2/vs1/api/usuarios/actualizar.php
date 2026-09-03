<?php
require __DIR__ . '/common.php';
require_once __DIR__ . '/../../i18n.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    usuariosJsonResponse(405, false, 'Método no permitido.');
}

$input = usuariosReadJsonInput();
usuariosRequireCsrf($input);

$idUsers = strtolower(trim((string) ($input['id_users'] ?? '')));
$name = trim((string) ($input['name'] ?? ''));
$lastname = trim((string) ($input['lastname'] ?? ''));
$rut = trim((string) ($input['rut'] ?? ''));
$rawLang = strtolower(trim((string) ($input['language'] ?? 'es')));
if ($rawLang === 'esp') {
    $rawLang = 'es';
}
$language = in_array($rawLang, IDIOMAS_DISPONIBLES, true) ? $rawLang : IDIOMA_POR_DEFECTO;
$roleGroupId = (int) ($input['id_role_group'] ?? 0);
$requestedCompanyId = isset($input['id_company']) ? (int) $input['id_company'] : 0;

if ($idUsers === '' || !filter_var($idUsers, FILTER_VALIDATE_EMAIL)) {
    usuariosJsonResponse(400, false, 'Usuario objetivo inválido.');
}

if (mb_strlen($idUsers) > 50 || mb_strlen($name) > 50 || mb_strlen($lastname) > 50 || mb_strlen($rut) > 10 || mb_strlen($language) > 11) {
    usuariosJsonResponse(400, false, 'Uno o más campos superan el largo permitido.');
}

try {
    $context = usuariosRequireAccessContext($pdo);
    $targetUser = usuariosFindUserWithAccess($pdo, $idUsers);

    if (!$targetUser) {
        usuariosJsonResponse(404, false, 'Usuario no encontrado.');
    }

    $target = [
        'id_users' => (string) $targetUser['id_users'],
        'id_company' => (int) $targetUser['id_company'],
        'access_level' => $targetUser['access_level'],
    ];

    $isSelf = (string) $idUsers === (string) $context['session_user_id'];
    $actorLevel = (int) $context['actor_level'];

    if ($actorLevel === 5) {
        if (!$isSelf) {
            usuariosJsonResponse(403, false, 'Solo puedes editar tu propia cuenta.');
        }

        if ($name === '' || $lastname === '' || $language === '') {
            usuariosJsonResponse(400, false, 'Nombre, apellido e idioma son obligatorios.');
        }

        $updateSelfSql = '
            UPDATE users
            SET
                name = :name,
                lastname = :lastname,
                language = :language,
                last_update = NOW()
            WHERE id_users = :id_users
            LIMIT 1
        ';

        $updateSelfStmt = $pdo->prepare($updateSelfSql);
        $updateSelfStmt->execute([
            'name' => $name,
            'lastname' => $lastname,
            'language' => $language,
            'id_users' => $idUsers,
        ]);

        usuariosJsonResponse(200, true, 'Perfil actualizado correctamente.');
    }

    if ($name === '' || $lastname === '' || $rut === '' || $language === '') {
        usuariosJsonResponse(400, false, 'Nombre, apellido, RUT e idioma son obligatorios.');
    }

    if (!usuariosCanManageTarget($context, $target)) {
        usuariosJsonResponse(403, false, 'No tienes permisos para editar este usuario.');
    }

    $targetCompanyId = (int) $targetUser['id_company'];

    if ($actorLevel === 1) {
        if ($requestedCompanyId > 0) {
            $company = usuariosFindCompany($pdo, $requestedCompanyId);
            if (!$company || (int) $company['state'] !== 1) {
                usuariosJsonResponse(400, false, 'La empresa seleccionada no está disponible.');
            }

            $targetCompanyId = $requestedCompanyId;
        }
    } else {
        if ($requestedCompanyId > 0 && $requestedCompanyId !== $targetCompanyId) {
            usuariosJsonResponse(403, false, 'No puedes mover usuarios a otra empresa.');
        }

        if ($targetCompanyId !== (int) $context['company_id']) {
            usuariosJsonResponse(403, false, 'No puedes editar usuarios de otra empresa.');
        }
    }

    if ($roleGroupId <= 0) {
        usuariosJsonResponse(400, false, 'Debes seleccionar un rol válido.');
    }

    $role = usuariosFindActiveRole($pdo, $targetCompanyId, $roleGroupId);

    if (!$role) {
        usuariosJsonResponse(400, false, 'El rol seleccionado no está disponible para la empresa objetivo.');
    }

    $newRoleLevel = usuariosRoleLevelFromName((string) $role['name']);
    if (!usuariosCanAssignLevel($context, $newRoleLevel)) {
        usuariosJsonResponse(403, false, 'No tienes permisos para asignar ese nivel de acceso.');
    }

    if ($isSelf && $actorLevel !== 1) {
        $currentRoleId = isset($targetUser['primary_role_id']) ? (int) $targetUser['primary_role_id'] : 0;
        if ($currentRoleId > 0 && $currentRoleId !== $roleGroupId) {
            usuariosJsonResponse(400, false, 'No puedes cambiar tu propio rol desde esta interfaz.');
        }

        if ($targetCompanyId !== (int) $targetUser['id_company']) {
            usuariosJsonResponse(400, false, 'No puedes cambiar tu empresa desde esta interfaz.');
        }
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
        LIMIT 1
    ';

    $updateUserStmt = $pdo->prepare($updateUserSql);
    $updateUserStmt->execute([
        'name' => $name,
        'lastname' => $lastname,
        'rut' => $rut,
        'language' => $language,
        'id_users' => $idUsers,
    ]);

    if ($targetCompanyId !== (int) $targetUser['id_company']) {
        $moveStmt = $pdo->prepare('UPDATE users SET id_company = :id_company, last_update = NOW() WHERE id_users = :id_users LIMIT 1');
        $moveStmt->execute([
            'id_company' => $targetCompanyId,
            'id_users' => $idUsers,
        ]);
    }

    $deactivateSql = '
        UPDATE users_role ur
        INNER JOIN users_role_group urg
            ON urg.id_role_group = ur.id_role_group
        SET
            ur.state = 0,
            ur.last_update = NOW()
        WHERE ur.id_users = :id_users
          AND ur.state = 1
                    AND urg.id_company = :role_company_id
          AND ur.id_role_group <> :id_role_group
    ';

    $deactivateStmt = $pdo->prepare($deactivateSql);
    $deactivateStmt->execute([
        'id_users' => $idUsers,
        'role_company_id' => $targetCompanyId,
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

    error_log('api/usuarios/actualizar.php: ' . $e->getMessage());
    usuariosJsonResponse(500, false, 'Error al actualizar usuario.');
}
