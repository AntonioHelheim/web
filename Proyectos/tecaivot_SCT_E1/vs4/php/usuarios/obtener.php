<?php
require __DIR__ . '/common.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    usuariosJsonResponse(405, false, 'Método no permitido.');
}

$userId = trim((string) ($_GET['id_users'] ?? ''));

if ($userId === '') {
    usuariosJsonResponse(400, false, 'Debes indicar el usuario a consultar.');
}

if (mb_strlen($userId) > 50) {
    usuariosJsonResponse(400, false, 'Identificador de usuario inválido.');
}

try {
    $context = usuariosRequireAdminContext($pdo);

    $user = usuariosFindUserInCompany($pdo, $userId, $context['company_id']);

    if (!$user) {
        usuariosJsonResponse(404, false, 'Usuario no encontrado.');
    }

    $data = [
        'id_users' => (string) $user['id_users'],
        'name' => (string) $user['name'],
        'lastname' => (string) $user['lastname'],
        'rut' => (string) $user['rut'],
        'language' => (string) $user['language'],
        'state' => (int) $user['state'],
        'last_access' => $user['last_access'],
        'razon_social' => (string) $user['razon_social'],
        'role_name' => (string) $user['role_name'],
        'primary_role_id' => isset($user['primary_role_id']) ? (int) $user['primary_role_id'] : null,
    ];

    usuariosJsonResponse(200, true, '', $data);
} catch (PDOException $e) {
    error_log('php/usuarios/obtener.php: ' . $e->getMessage());
    usuariosJsonResponse(500, false, 'Error al obtener usuario.');
}
