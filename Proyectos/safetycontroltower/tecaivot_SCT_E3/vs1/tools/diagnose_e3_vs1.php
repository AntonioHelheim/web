<?php
/**
 * E3-VS1 localhost diagnostic utility.
 * CLI only. Usage:
 * /opt/lampp/bin/php tools/diagnose_e3_vs1.php UsuarioEmpresaDemo@demoSCT.cl
 */
if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

$userId = $argv[1] ?? 'UsuarioEmpresaDemo@demoSCT.cl';
$root = dirname(__DIR__);

echo "Safety Control Tower E3-VS1 diagnostic\n";
echo "PHP: " . PHP_VERSION . "\n";
echo "User: " . $userId . "\n\n";

if (version_compare(PHP_VERSION, '7.4.0', '<')) {
    echo "[FAIL] E3-VS1 hotfix requires PHP >= 7.4.\n";
    exit(2);
}
echo "[OK] PHP syntax target >= 7.4\n";

try {
    require $root . '/session_bootstrap.php';
    require $root . '/lib/db.php';
    require $root . '/app/Auth/RolePolicy.php';
    require $root . '/app/Auth/SessionService.php';
    require $root . '/app/Auth/UserContext.php';
    require $root . '/app/Auth/AuthorizationService.php';

    $auth = new SctAuthorizationService($pdo);

    $stmt = $pdo->prepare("SELECT id_users,id_company,state FROM users WHERE LOWER(id_users)=LOWER(:id) LIMIT 1");
    $stmt->execute(['id'=>$userId]);
    $user = $stmt->fetch();
    if (!$user) {
        echo "[FAIL] User not found.\n";
        exit(3);
    }
    echo "[OK] User exists: company={$user['id_company']} state={$user['state']}\n";

    $roles = $auth->rolesForUser((string)$user['id_users']);
    echo "[INFO] Roles: " . ($roles ? implode(', ', $roles) : '(none)') . "\n";
    if (!$roles) {
        echo "[FAIL] No active recognized E3-VS1 role.\n";
    } else {
        echo "[OK] Active recognized role found.\n";
    }

    $enabled = $auth->permissionsDatabaseEnabled();
    echo "[INFO] RBAC DB authoritative: " . ($enabled ? 'YES' : 'NO') . "\n";

    $caps = $auth->capabilitiesForUser((string)$user['id_users']);
    echo "[INFO] Capabilities (" . count($caps) . "): " . implode(', ', $caps) . "\n";

    $stmt = $pdo->prepare("SELECT version,name,executed_at FROM schema_migrations WHERE version='2026-10-07-e3-vs1' LIMIT 1");
    $stmt->execute();
    $mig = $stmt->fetch();
    echo $mig ? "[OK] Migration marker exists: {$mig['executed_at']}\n" : "[FAIL] Migration marker missing.\n";

    echo "\nDiagnostic completed.\n";
} catch (Throwable $e) {
    echo "[FATAL] " . get_class($e) . ": " . $e->getMessage() . "\n";
    echo $e->getFile() . ":" . $e->getLine() . "\n";
    exit(10);
}
