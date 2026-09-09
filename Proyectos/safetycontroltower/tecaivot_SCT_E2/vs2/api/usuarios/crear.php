<?php
require __DIR__ . '/common.php';
require_once __DIR__ . '/../../lib/passwords.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    responderJSON(false, null, 'Método no permitido.', 405);
}

$input = usuariosReadJsonInput();
usuariosRequireCsrf($input);

$idUsers = strtolower(trim((string) ($input['id_users'] ?? '')));
$name = trim((string) ($input['name'] ?? ''));
$lastname = trim((string) ($input['lastname'] ?? ''));
$rut = normalizarRutChileno((string) ($input['rut'] ?? ''));
$language = normalizarIdiomaUsuario((string) ($input['language'] ?? IDIOMA_POR_DEFECTO));
$roleGroupId = (int) ($input['id_role_group'] ?? 0);
$requestedCompanyId = (int) ($input['id_company'] ?? 0);

if (!filter_var($idUsers, FILTER_VALIDATE_EMAIL) || mb_strlen($idUsers) > 50) {
    responderJSON(false, null, 'Debes ingresar un email válido.', 400);
}
if ($name === '' || $lastname === '' || $rut === null) {
    responderJSON(false, null, $rut === null ? 'El RUT ingresado no es válido.' : 'Nombre, apellido y RUT son obligatorios.', 400);
}
if (mb_strlen($name) > 50 || mb_strlen($lastname) > 50 || mb_strlen($rut) > 10) {
    responderJSON(false, null, 'Uno o más campos superan el largo permitido.', 400);
}
if ($roleGroupId <= 0) {
    responderJSON(false, null, 'Debes seleccionar un rol válido.', 400);
}

try {
    $context = usuariosRequireAccessContext($pdo);
    if (!authCanCreateUsers($context)) {
        responderJSON(false, null, 'No tienes permisos para crear usuarios.', 403);
    }

    $targetCompanyId = (int) $context['company_id'];
    if (!empty($context['is_global_admin'])) {
        if ($requestedCompanyId <= 0) {
            responderJSON(false, null, 'Debes seleccionar una empresa válida.', 400);
        }
        $company = usuariosFindCompany($pdo, $requestedCompanyId);
        if (!$company || (int) $company['state'] !== 1) {
            responderJSON(false, null, 'La empresa seleccionada no está disponible.', 400);
        }
        $targetCompanyId = $requestedCompanyId;
    }

    $existingStmt = $pdo->prepare('SELECT id_users FROM users WHERE id_users = :id_users LIMIT 1');
    $existingStmt->execute(['id_users' => $idUsers]);
    if ($existingStmt->fetch()) {
        responderJSON(false, null, 'El usuario/email ya existe.', 409);
    }

    $role = usuariosFindActiveRole($pdo, $targetCompanyId, $roleGroupId);
    if (!$role) {
        responderJSON(false, null, 'El rol seleccionado no está disponible para la empresa objetivo.', 400);
    }
    $targetLevel = roleLevelFromName((string) $role['name']);
    if (!authCanAssignUserLevel($context, $targetLevel)) {
        responderJSON(false, null, 'No tienes permisos para asignar ese nivel de acceso.', 403);
    }

    $pdo->beginTransaction();

    $stmt = $pdo->prepare(
        'INSERT INTO users (
            id_users, id_company, id_worker, name, lastname, rut, state, language,
            profile_photo_path, last_access, created_by, date_create, last_update
         ) VALUES (
            :id_users, :id_company, NULL, :name, :lastname, :rut, 1, :language,
            NULL, :last_access, :created_by, NOW(), NOW()
         )'
    );
    $stmt->execute([
        'id_users' => $idUsers,
        'id_company' => $targetCompanyId,
        'name' => $name,
        'lastname' => $lastname,
        'rut' => $rut,
        'language' => $language,
        'last_access' => '1970-01-01 00:00:00',
        'created_by' => $context['session_user_id'],
    ]);

    $credentialStmt = $pdo->prepare(
        'INSERT INTO user_credentials (
            id_users, password_hash, credential_status, password_changed_at,
            legacy_password_invalidated_at, created_at, updated_at
         ) VALUES (
            :id_users, NULL, :credential_status, NULL, NULL, NOW(), NOW()
         )'
    );
    $credentialStmt->execute([
        'id_users' => $idUsers,
        'credential_status' => PASSWORD_CREDENTIAL_PENDING_ACTIVATION,
    ]);

    $roleStmt = $pdo->prepare(
        'INSERT INTO users_role (
            id_users, id_role_group, state, create_by, date_create, last_update
         ) VALUES (
            :id_users, :id_role_group, 1, :create_by, NOW(), NOW()
         )'
    );
    $roleStmt->execute([
        'id_users' => $idUsers,
        'id_role_group' => $roleGroupId,
        'create_by' => $context['session_user_id'],
    ]);

    $auditRequest = auditTrailRequestId();
    auditTrailLogChanges($pdo, $targetCompanyId, 'usuarios', 'users', $idUsers, [], [
        'id_company'=>$targetCompanyId,'name'=>$name,'lastname'=>$lastname,'rut'=>$rut,
        'language'=>$language,'state'=>1,'role'=>(string)$role['name']
    ], 'create', trim($name.' '.$lastname), null, (string)$context['session_user_id'], $auditRequest);

    $pdo->commit();

    $accessDelivery = ['status' => PASSWORD_CREDENTIAL_PENDING_ACTIVATION, 'email_sent' => false];
    $responseMessage = 'Usuario creado correctamente. Debe definir su contraseña antes de iniciar sesión.';

    try {
        $ip = (string) ($_SERVER['REMOTE_ADDR'] ?? 'unknown');
        $tokenData = passwordsCreateToken($pdo, $idUsers, $ip, PASSWORD_ACTIVATION_TTL_MINUTES, PASSWORD_PURPOSE_ACTIVATION);
        if (passwordsIsLocal()) {
            // Se conserva únicamente para depuración; la interfaz no muestra URLs técnicas.
            $accessDelivery['dev_url'] = $tokenData['url'];
            $responseMessage = 'Usuario creado correctamente. En desarrollo puede definir su contraseña desde “¿Olvidaste tu contraseña?”.';
        } else {
            $sent = passwordsSendLinkEmail($idUsers, $tokenData['url'], PASSWORD_PURPOSE_ACTIVATION);
            $accessDelivery['email_sent'] = $sent;
            $responseMessage = $sent
                ? 'Usuario creado correctamente. Se envió el enlace de activación a su correo.'
                : 'Usuario creado correctamente, pero no fue posible enviar el correo. Puede usar “¿Olvidaste tu contraseña?” para generar un nuevo enlace.';
        }
    } catch (Throwable $activationError) {
        error_log('api/usuarios/crear.php activación: ' . $activationError->getMessage());
    }

    responderJSON(true, ['id_users' => $idUsers, 'access' => $accessDelivery], $responseMessage, 201);
} catch (PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log('api/usuarios/crear.php: ' . $e->getMessage());
    responderJSON(false, null, 'Error al crear usuario.', 500);
}
