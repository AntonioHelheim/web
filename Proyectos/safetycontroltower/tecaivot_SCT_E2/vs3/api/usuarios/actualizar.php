<?php
require __DIR__ . '/common.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    responderJSON(false, null, 'Método no permitido.', 405);
}

$input = usuariosReadJsonInput();
usuariosRequireCsrf($input);

$idUsers = strtolower(trim((string) ($input['id_users'] ?? '')));
$name = trim((string) ($input['name'] ?? ''));
$lastname = trim((string) ($input['lastname'] ?? ''));
$rawRut = (string) ($input['rut'] ?? '');
$rawLang = strtolower(trim((string) ($input['language'] ?? IDIOMA_POR_DEFECTO)));
if ($rawLang === 'esp') {
    $rawLang = 'es';
}
$language = in_array($rawLang, IDIOMAS_DISPONIBLES, true) ? $rawLang : IDIOMA_POR_DEFECTO;
$roleGroupId = (int) ($input['id_role_group'] ?? 0);
$requestedCompanyId = (int) ($input['id_company'] ?? 0);

if (!filter_var($idUsers, FILTER_VALIDATE_EMAIL) || mb_strlen($idUsers) > 50) {
    responderJSON(false, null, 'Usuario objetivo inválido.', 400);
}
if ($name === '' || $lastname === '' || mb_strlen($name) > 50 || mb_strlen($lastname) > 50) {
    responderJSON(false, null, 'Nombre y apellido son obligatorios y no pueden superar 50 caracteres.', 400);
}

try {
    $context = usuariosRequireAccessContext($pdo);
    $targetUser = usuariosFindUserWithAccess($pdo, $idUsers);
    if (!$targetUser) {
        responderJSON(false, null, 'Usuario no encontrado.', 404);
    }

    $target = [
        'id_users' => (string) $targetUser['id_users'],
        'id_company' => (int) $targetUser['id_company'],
        'access_level' => $targetUser['access_level'],
    ];
    $isSelf = $idUsers === (string) $context['session_user_id'];
    $actorLevel = (int) $context['actor_level'];

    // Autogestión: cualquier rol puede actualizar nombre, apellido, idioma y foto.
    // Si el actor no puede administrar su propio nivel (cliente/jefatura/trabajador),
    // la operación termina aquí y nunca toca RUT, empresa ni rol.
    $canManageTarget = authCanManageUserTarget($context, $target);
    if ($isSelf && !$canManageTarget) {
        $stmt = $pdo->prepare(
            'UPDATE users
             SET name = :name, lastname = :lastname, language = :language, last_update = NOW()
             WHERE id_users = :id_users
             LIMIT 1'
        );
        $stmt->execute([
            'name' => $name,
            'lastname' => $lastname,
            'language' => $language,
            'id_users' => $idUsers,
        ]);
        auditTrailLogChanges($pdo, (int)$targetUser['id_company'], 'usuarios', 'users', $idUsers,
            $targetUser, array_merge($targetUser,['name'=>$name,'lastname'=>$lastname,'language'=>$language]),
            'update', trim($name.' '.$lastname), ['name','lastname','language']);
        responderJSON(true, null, 'Perfil actualizado correctamente.');
    }

    if (!$canManageTarget) {
        responderJSON(false, null, 'No tienes permisos para editar este usuario.', 403);
    }

    $rut = normalizarRutChileno($rawRut);
    if ($rut === null) {
        responderJSON(false, null, 'El RUT ingresado no es válido.', 400);
    }

    $targetCompanyId = (int) $targetUser['id_company'];
    if (!empty($context['is_global_admin'])) {
        if ($requestedCompanyId > 0) {
            $company = usuariosFindCompany($pdo, $requestedCompanyId);
            if (!$company || (int) $company['state'] !== 1) {
                responderJSON(false, null, 'La empresa seleccionada no está disponible.', 400);
            }
            $targetCompanyId = $requestedCompanyId;
        }
    } elseif ($requestedCompanyId > 0 && $requestedCompanyId !== $targetCompanyId) {
        responderJSON(false, null, 'No puedes mover usuarios a otra empresa.', 403);
    }

    if ($roleGroupId <= 0) {
        responderJSON(false, null, 'Debes seleccionar un rol válido.', 400);
    }
    $role = usuariosFindActiveRole($pdo, $targetCompanyId, $roleGroupId);
    if (!$role) {
        responderJSON(false, null, 'El rol seleccionado no está disponible para la empresa objetivo.', 400);
    }

    $newRoleLevel = roleLevelFromName((string) $role['name']);
    if (!authCanAssignUserLevel($context, $newRoleLevel)) {
        responderJSON(false, null, 'No tienes permisos para asignar ese nivel de acceso.', 403);
    }

    if ($isSelf && empty($context['is_global_admin'])) {
        $currentRoleId = isset($targetUser['primary_role_id']) ? (int) $targetUser['primary_role_id'] : 0;
        if ($currentRoleId > 0 && $currentRoleId !== $roleGroupId) {
            responderJSON(false, null, 'No puedes cambiar tu propio rol desde esta interfaz.', 400);
        }
        if ($targetCompanyId !== (int) $targetUser['id_company']) {
            responderJSON(false, null, 'No puedes cambiar tu empresa desde esta interfaz.', 400);
        }
    }

    $pdo->beginTransaction();

    $stmt = $pdo->prepare(
        'UPDATE users
         SET name = :name, lastname = :lastname, rut = :rut, language = :language, last_update = NOW()
         WHERE id_users = :id_users
         LIMIT 1'
    );
    $stmt->execute([
        'name' => $name,
        'lastname' => $lastname,
        'rut' => $rut,
        'language' => $language,
        'id_users' => $idUsers,
    ]);

    if ($targetCompanyId !== (int) $targetUser['id_company']) {
        $moveStmt = $pdo->prepare(
            'UPDATE users SET id_company = :id_company, last_update = NOW() WHERE id_users = :id_users LIMIT 1'
        );
        $moveStmt->execute(['id_company' => $targetCompanyId, 'id_users' => $idUsers]);

        // Desactiva roles activos de la empresa anterior para evitar roles huérfanos.
        $oldRolesStmt = $pdo->prepare(
            'UPDATE users_role ur
             INNER JOIN users_role_group urg ON urg.id_role_group = ur.id_role_group
             SET ur.state = 0, ur.last_update = NOW()
             WHERE ur.id_users = :id_users AND ur.state = 1 AND urg.id_company <> :new_company'
        );
        $oldRolesStmt->execute(['id_users' => $idUsers, 'new_company' => $targetCompanyId]);
    }

    $deactivateStmt = $pdo->prepare(
        'UPDATE users_role ur
         INNER JOIN users_role_group urg ON urg.id_role_group = ur.id_role_group
         SET ur.state = 0, ur.last_update = NOW()
         WHERE ur.id_users = :id_users
           AND ur.state = 1
           AND urg.id_company = :role_company_id
           AND ur.id_role_group <> :id_role_group'
    );
    $deactivateStmt->execute([
        'id_users' => $idUsers,
        'role_company_id' => $targetCompanyId,
        'id_role_group' => $roleGroupId,
    ]);

    $existingRoleStmt = $pdo->prepare(
        'SELECT id_users_role FROM users_role
         WHERE id_users = :id_users AND id_role_group = :id_role_group
         ORDER BY id_users_role DESC LIMIT 1'
    );
    $existingRoleStmt->execute(['id_users' => $idUsers, 'id_role_group' => $roleGroupId]);
    $existingRole = $existingRoleStmt->fetch();

    if ($existingRole) {
        $activateRoleStmt = $pdo->prepare(
            'UPDATE users_role SET state = 1, last_update = NOW() WHERE id_users_role = :id_users_role LIMIT 1'
        );
        $activateRoleStmt->execute(['id_users_role' => (int) $existingRole['id_users_role']]);
    } else {
        $insertRoleStmt = $pdo->prepare(
            'INSERT INTO users_role (id_users, id_role_group, state, create_by, date_create, last_update)
             VALUES (:id_users, :id_role_group, 1, :create_by, NOW(), NOW())'
        );
        $insertRoleStmt->execute([
            'id_users' => $idUsers,
            'id_role_group' => $roleGroupId,
            'create_by' => $context['session_user_id'],
        ]);
    }

    $auditRequest = auditTrailRequestId();
    auditTrailLogChanges($pdo, $targetCompanyId, 'usuarios', 'users', $idUsers,
        $targetUser,
        array_merge($targetUser,['name'=>$name,'lastname'=>$lastname,'rut'=>$rut,'language'=>$language,'id_company'=>$targetCompanyId]),
        'update', trim($name.' '.$lastname), ['name','lastname','rut','language','id_company'],
        (string)$context['session_user_id'], $auditRequest);
    $oldRole = (string)($targetUser['primary_role'] ?? $targetUser['role_name'] ?? '');
    $newRole = (string)$role['name'];
    if ($oldRole !== $newRole) {
        auditTrailLogAction($pdo, $targetCompanyId, 'usuarios', 'users_role', $idUsers, 'role', $oldRole, $newRole,
            'assign', trim($name.' '.$lastname), (string)$context['session_user_id'], $auditRequest);
    }

    $pdo->commit();
    responderJSON(true, null, 'Usuario actualizado correctamente.');
} catch (PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log('api/usuarios/actualizar.php: ' . $e->getMessage());
    responderJSON(false, null, 'Error al actualizar usuario.', 500);
}
