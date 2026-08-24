<?php
/**
 * lib/auth.php
 * Control de sesión y de acceso por rol, centralizado.
 *
 * La tabla users_role / users_role_group ya existía en la base de datos,
 * pero hasta ahora ningún endpoint la usaba para restringir acceso. Este
 * archivo es el único lugar donde se resuelve "¿qué rol(es) tiene el
 * usuario actual?" y "¿puede hacer esto?" — así cada módulo nuevo llama
 * a requireRole() en vez de reimplementar su propia validación.
 *
 * Requiere que session_bootstrap.php ya se haya incluido antes (usa
 * $_SESSION). Este archivo se encarga de traer lib/response.php por su
 * cuenta — no hace falta que quien lo use se acuerde de incluir ambos.
 */

require_once __DIR__ . '/response.php';

/**
 * Corta la ejecución con 401 si no hay sesión activa.
 * Para usar en endpoints de /api/ (responde JSON).
 */
function requireLogin(): void
{
    if (empty($_SESSION['logged_in'])) {
        responderJSON(false, null, 'Debes iniciar sesión para continuar.', 401);
    }
}

/**
 * Para páginas HTML normales (no endpoints /api/): si no hay sesión activa,
 * redirige a acceso-denegado.php en vez de devolver JSON.
 * Uso: en bienvenida.php y cualquier página interna futura, primera línea
 * después de session_bootstrap.php:
 *   require __DIR__ . '/lib/auth.php';
 *   requireLoginPage();
 */
function requireLoginPage(string $redirectTo = 'acceso-denegado.php'): void
{
    if (empty($_SESSION['logged_in'])) {
        header('Location: ' . $redirectTo);
        exit;
    }
}

/**
 * id_users (correo) de la cuenta actualmente autenticada, o null.
 */
function currentUserId(): ?string
{
    return $_SESSION['user_id'] ?? null;
}

/**
 * Nombres de rol (ej. ['administrador']) que tiene la cuenta actual,
 * consultados una sola vez por request (con caché en memoria).
 */
function currentUserRoles(PDO $pdo): array
{
    static $cache = null;
    if ($cache !== null) {
        return $cache;
    }

    $idUsers = currentUserId();
    if (!$idUsers) {
        return $cache = [];
    }

    $stmt = $pdo->prepare(
        'SELECT g.name
         FROM users_role ur
         JOIN users_role_group g ON g.id_role_group = ur.id_role_group
         WHERE ur.id_users = :id_users
           AND ur.state = 1
           AND g.state = 1'
    );
    $stmt->execute(['id_users' => $idUsers]);

    return $cache = array_column($stmt->fetchAll(), 'name');
}

/**
 * id_company de la cuenta actual, o null si no tiene una asociada.
 */
function currentUserCompanyId(PDO $pdo): ?int
{
    $idUsers = currentUserId();
    if (!$idUsers) {
        return null;
    }

    $stmt = $pdo->prepare('SELECT id_company FROM users WHERE id_users = :id_users LIMIT 1');
    $stmt->execute(['id_users' => $idUsers]);
    $row = $stmt->fetch();

    return $row ? (int) $row['id_company'] : null;
}

/**
 * Corta la ejecución con 401 (sin sesión) o 403 (sin el rol requerido) si
 * la cuenta actual no tiene ninguno de los roles permitidos.
 *
 *   requireRole($pdo, ['administrador']);                 // solo admin
 *   requireRole($pdo, ['administrador', 'cliente']);       // admin o cliente
 */
function requireRole(PDO $pdo, array $rolesPermitidos): void
{
    requireLogin();

    $rolesUsuario = currentUserRoles($pdo);
    $tieneAcceso = count(array_intersect($rolesUsuario, $rolesPermitidos)) > 0;

    if (!$tieneAcceso) {
        responderJSON(false, null, 'No tienes permisos para realizar esta acción.', 403);
    }
}

/**
 * Igual que requireRole(), pero para páginas HTML: redirige en vez de
 * responder JSON. Pensado para páginas internas futuras restringidas a un
 * rol específico (ej. una pantalla de administración).
 */
function requireRolePage(PDO $pdo, array $rolesPermitidos, string $redirectTo = 'acceso-denegado.php'): void
{
    requireLoginPage($redirectTo);

    $rolesUsuario = currentUserRoles($pdo);
    $tieneAcceso = count(array_intersect($rolesUsuario, $rolesPermitidos)) > 0;

    if (!$tieneAcceso) {
        header('Location: ' . $redirectTo);
        exit;
    }
}