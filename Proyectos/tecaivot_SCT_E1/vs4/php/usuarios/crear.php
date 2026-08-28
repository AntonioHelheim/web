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
$language = strtoupper(trim((string) ($input['language'] ?? 'ESP')));
$password = (string) ($input['password'] ?? '');
$roleGroupId = (int) ($input['id_role_group'] ?? 0);
$requestedCompanyId = isset($input['id_company']) ? (int) $input['id_company'] : 0;

if (!filter_var($idUsers, FILTER_VALIDATE_EMAIL)) {
    usuariosJsonResponse(400, false, 'Debes ingresar un email válido.');
}

if ($name === '' || $lastname === '' || $rut === '' || $language === '') {
    usuariosJsonResponse(400, false, 'Nombre, apellido, RUT e idioma son obligatorios.');
}

if ($roleGroupId <= 0) {
    usuariosJsonResponse(400, false, 'Debes seleccionar un rol válido.');
}

if (mb_strlen($password) < 8) {
    usuariosJsonResponse(400, false, 'La contraseña inicial debe tener al menos 8 caracteres.');
}

if (mb_strlen($idUsers) > 50 || mb_strlen($name) > 50 || mb_strlen($lastname) > 50 || mb_strlen($rut) > 10 || mb_strlen($language) > 11) {
    usuariosJsonResponse(400, false, 'Uno o más campos superan el largo permitido.');
}

try {
    $context = usuariosRequireAccessContext($pdo);

    if (!usuariosCanCreateUsers($context)) {
        usuariosJsonResponse(403, false, 'No tienes permisos para crear usuarios.');
    }

    $targetCompanyId = (int) $context['company_id'];
    if ((int) $context['actor_level'] === 1) {
        if ($requestedCompanyId <= 0) {
            usuariosJsonResponse(400, false, 'Debes seleccionar una empresa válida.');
        }

        $company = usuariosFindCompany($pdo, $requestedCompanyId);
        if (!$company || (int) $company['state'] !== 1) {
            usuariosJsonResponse(400, false, 'La empresa seleccionada no está disponible.');
        }

        $targetCompanyId = $requestedCompanyId;
    }

    $existingStmt = $pdo->prepare('SELECT id_users FROM users WHERE id_users = :id_users LIMIT 1');
    $existingStmt->execute(['id_users' => $idUsers]);

    if ($existingStmt->fetch()) {
        usuariosJsonResponse(409, false, 'El usuario/email ya existe.');
    }

    $role = usuariosFindActiveRole($pdo, $targetCompanyId, $roleGroupId);

    if (!$role) {
        usuariosJsonResponse(400, false, 'El rol seleccionado no está disponible para la empresa objetivo.');
    }

    $targetLevel = usuariosRoleLevelFromName((string) $role['name']);
    if (!usuariosCanAssignLevel($context, $targetLevel)) {
        usuariosJsonResponse(403, false, 'No tienes permisos para asignar ese nivel de acceso.');
    }

    $passwordHash = password_hash($password, PASSWORD_DEFAULT);

    if ($passwordHash === false) {
        usuariosJsonResponse(500, false, 'No se pudo procesar la contraseña.');
    }

    $pdo->beginTransaction();

    $insertUserSql = '
        INSERT INTO users (
            id_users,
            id_company,
            id_worker,
            name,
            lastname,
            rut,
            password_hash,
            state,
            language,
            last_access,
            created_by,
            date_create,
            last_update
        ) VALUES (
            :id_users,
            :id_company,
            NULL,
            :name,
            :lastname,
            :rut,
            :password_hash,
            1,
            :language,
            :last_access,
            :created_by,
            NOW(),
            NOW()
        )
    ';

    $insertUserStmt = $pdo->prepare($insertUserSql);
    $insertUserStmt->execute([
        'id_users' => $idUsers,
        'id_company' => $targetCompanyId,
        'name' => $name,
        'lastname' => $lastname,
        'rut' => $rut,
        'password_hash' => $passwordHash,
        'language' => $language,
        'last_access' => '1970-01-01 00:00:00',
        'created_by' => $context['session_user_id'],
    ]);

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

    $pdo->commit();

    usuariosJsonResponse(200, true, 'Usuario creado correctamente.', [
        'id_users' => $idUsers,
    ]);
} catch (PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    error_log('php/usuarios/crear.php: ' . $e->getMessage());
    usuariosJsonResponse(500, false, 'Error al crear usuario.');
}
