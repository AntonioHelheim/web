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
$supervisorIds = isset($input['supervisor_ids']) && is_array($input['supervisor_ids'])
    ? $input['supervisor_ids']
    : [];

if (!filter_var($idUsers, FILTER_VALIDATE_EMAIL) || mb_strlen($idUsers) > 50) {
    responderJSON(false, null, 'Usuario objetivo inválido.', 400);
}
if ($name === '' || $lastname === '' || mb_strlen($name) > 50 || mb_strlen($lastname) > 50) {
    responderJSON(false, null, 'Nombre y apellido son obligatorios y no pueden superar 50 caracteres.', 400);
}

try {
    $context = usuariosRequireAccessContext($pdo);
    $repo = usuariosRepository($pdo);
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
    // Si el actor no puede administrar su propio nivel según la política jerárquica,
    // la operación termina aquí y nunca toca RUT, empresa ni rol.
    $canManageTarget = authCanManageUserTarget($context, $target, $pdo);
    if ($isSelf && !$canManageTarget) {
        $repo->updateBasicProfile($idUsers, $name, $lastname, $language);
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
    $currentRoleId = isset($targetUser['primary_role_id'])
        ? (int)$targetUser['primary_role_id']
        : 0;
    $roleChanges = $currentRoleId > 0 && $currentRoleId !== $roleGroupId;

    // Administrar un usuario asignado no exige permiso para reasignar su rol
    // mientras el rol se mantenga sin cambios.
    if ($roleChanges && !authCanAssignUserLevel($context, $newRoleLevel)) {
        responderJSON(false, null, 'No tienes permisos para asignar ese nivel de acceso.', 403);
    }

    if ($isSelf && empty($context['is_global_admin'])) {
        if ($currentRoleId > 0 && $currentRoleId !== $roleGroupId) {
            responderJSON(false, null, 'No puedes cambiar tu propio rol desde esta interfaz.', 400);
        }
        if ($targetCompanyId !== (int) $targetUser['id_company']) {
            responderJSON(false, null, 'No puedes cambiar tu empresa desde esta interfaz.', 400);
        }
    }

    $pdo->beginTransaction();

    $repo->updateManagedProfile($idUsers, $name, $lastname, $rut, $language);

    if ($targetCompanyId !== (int) $targetUser['id_company']) {
        $repo->moveCompany($idUsers, $targetCompanyId);
    }
    if ($roleChanges || $currentRoleId <= 0) {
        $repo->setSoleActiveRole(
            $idUsers,
            $targetCompanyId,
            $roleGroupId,
            (string)$context['session_user_id']
        );
    }

    if (usuariosCanAssignSupervision($context)) {
        $repo->replaceSupervisionAssignments(
            $idUsers,
            $targetCompanyId,
            $newRoleLevel === 1 ? [] : $supervisorIds,
            (string)$context['session_user_id']
        );
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
} catch (InvalidArgumentException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    responderJSON(false, null, $e->getMessage(), 400);
} catch (PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log('api/usuarios/actualizar.php: ' . $e->getMessage());
    responderJSON(false, null, 'Error al actualizar usuario.', 500);
}
