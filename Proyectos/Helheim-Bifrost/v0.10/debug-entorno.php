<?php
/**
 * debug-entorno.php
 *
 * Consola de diagnóstico temporal para desarrollo local. Además del
 * chequeo de entorno original, ahora también:
 *  - Verifica la conexión a la base de datos y qué tablas existen.
 *  - Permite comprobar si un correo específico está registrado (sin
 *    tener que adivinar — la API real nunca revela esto, a propósito).
 *  - Prueba en vivo el endpoint real de login (api/login.php), mostrando
 *    la respuesta cruda tal como la vería el navegador.
 *  - Muestra las últimas líneas del log de errores de PHP, si se puede
 *    leer desde aquí.
 *
 * ⚠️ BÓRRALO antes de dejar el proyecto en producción de verdad: revela
 * el host/nombre de tu base de datos y permite consultar si un correo
 * está registrado (útil para depurar, pero no algo que quieras público).
 */
require __DIR__ . '/api/config.php';

// --- 2. Base de datos: conexión y tablas ---
$dbError = null;
$tablasInfo = [];
$tablasEsperadas = ['users', 'saves', 'login_codes', 'login_attempts', 'player_positions', 'battle_challenges', 'pvp_battles', 'species'];
try {
    $pdo = db();
    foreach ($tablasEsperadas as $tabla) {
        try {
            $count = (int) $pdo->query("SELECT COUNT(*) FROM `{$tabla}`")->fetchColumn();
            $tablasInfo[$tabla] = ['existe' => true, 'filas' => $count];
        } catch (PDOException $e) {
            $tablasInfo[$tabla] = ['existe' => false, 'filas' => null];
        }
    }
} catch (Throwable $e) {
    $dbError = $e->getMessage();
}

// --- 3. ¿Ese correo existe? (consulta directa, solo para depurar local) ---
$checkEmail = trim((string) ($_GET['check_email'] ?? ''));
$checkResult = null;
$checkError = null;
if ($checkEmail !== '') {
    try {
        $stmt = db()->prepare('SELECT id, username, email, created_at FROM users WHERE email = ? LIMIT 1');
        $stmt->execute([$checkEmail]);
        $checkResult = $stmt->fetch();
    } catch (PDOException $e) {
        $checkError = $e->getMessage();
    }
}

// --- 5. Log de errores de PHP (mejor esfuerzo) ---
$logPath = ini_get('error_log');
$logTail = null;
if ($logPath && is_readable($logPath)) {
    $lines = @file($logPath);
    if ($lines !== false) {
        $logTail = implode('', array_slice($lines, -30));
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Diagnóstico — Bifrost</title>
<style>
  body { font-family: 'Consolas', 'Courier New', monospace; background: #0f380f; color: #c9dfae; padding: 20px; line-height: 1.5; }
  h1 { color: #fff; font-size: 20px; }
  h2 { color: #9bbc0f; font-size: 15px; margin-top: 32px; border-bottom: 2px solid #306230; padding-bottom: 6px; }
  pre { background: #051c05; color: #9bbc0f; padding: 12px; overflow-x: auto; white-space: pre-wrap; word-break: break-word; border-radius: 4px; }
  table { border-collapse: collapse; margin: 10px 0; width: 100%; max-width: 480px; }
  td, th { border: 1px solid #306230; padding: 5px 10px; text-align: left; font-size: 13px; }
  th { background: #204020; }
  .ok { color: #8bff8b; font-weight: bold; }
  .bad { color: #ff6b6b; font-weight: bold; }
  .muted { opacity: 0.7; }
  input, button { font-family: inherit; padding: 8px; font-size: 13px; }
  input[type=email], input[type=text] { width: 260px; max-width: 60vw; }
  button { cursor: pointer; background: #9bbc0f; border: 2px solid #0f380f; border-radius: 4px; }
  .row { display: flex; gap: 8px; align-items: center; flex-wrap: wrap; margin: 8px 0; }
</style>
</head>
<body>

<h1>🔧 Diagnóstico de Bifrost</h1>
<p class="muted">Herramienta temporal de desarrollo — bórrala antes de producción.</p>

<h2>1. Entorno detectado</h2>
<pre><?php
echo "HTTP_HOST:    " . var_export($_SERVER['HTTP_HOST'] ?? null, true) . "\n";
echo "SERVER_NAME:  " . var_export($_SERVER['SERVER_NAME'] ?? null, true) . "\n";
echo "REMOTE_ADDR:  " . var_export($_SERVER['REMOTE_ADDR'] ?? null, true) . "\n";
$appEnvRaw = getenv('APP_ENV');
echo "getenv('APP_ENV'): " . ($appEnvRaw === false ? 'false (no está seteada)' : var_export($appEnvRaw, true)) . "\n";
echo "FORZAR_ENTORNO_LOCAL: " . (FORZAR_ENTORNO_LOCAL === null ? 'null (automático)' : var_export(FORZAR_ENTORNO_LOCAL, true)) . "\n\n";
echo "RESULTADO: \$isLocal = " . var_export($isLocal, true) . "\n";
echo $isLocal ? "-> Modo DESARROLLO (código en pantalla).\n" : "-> Modo PRODUCCIÓN (código por correo).\n";
?></pre>

<h2>2. Base de datos</h2>
<?php if ($dbError): ?>
  <pre class="bad">Error de conexión: <?= htmlspecialchars($dbError, ENT_QUOTES, 'UTF-8') ?></pre>
<?php else: ?>
  <p><span class="ok">Conexión OK</span> — <?= htmlspecialchars(DB_HOST, ENT_QUOTES, 'UTF-8') ?>:<?= DB_PORT ?>/<?= htmlspecialchars(DB_NAME, ENT_QUOTES, 'UTF-8') ?></p>
  <table>
    <tr><th>Tabla</th><th>¿Existe?</th><th>Filas</th></tr>
    <?php foreach ($tablasInfo as $tabla => $info): ?>
      <tr>
        <td><?= htmlspecialchars($tabla, ENT_QUOTES, 'UTF-8') ?></td>
        <td class="<?= $info['existe'] ? 'ok' : 'bad' ?>"><?= $info['existe'] ? 'Sí' : 'NO — falta importar una migración' ?></td>
        <td><?= $info['existe'] ? $info['filas'] : '-' ?></td>
      </tr>
    <?php endforeach; ?>
  </table>
  <?php if (!$tablasInfo['login_codes']['existe'] || !$tablasInfo['login_attempts']['existe']): ?>
    <p class="bad">Faltan tablas del login por código — importa sql/005_email_login.sql.</p>
  <?php endif; ?>
<?php endif; ?>

<h2>3. ¿Ese correo está registrado?</h2>
<p class="muted">La API real (api/login.php) nunca revela esto a propósito (para no filtrar qué correos existen). Aquí sí, porque es solo para ti en desarrollo.</p>
<form method="get" class="row">
  <input type="email" name="check_email" placeholder="correo@ejemplo.com" value="<?= htmlspecialchars($checkEmail, ENT_QUOTES, 'UTF-8') ?>">
  <button type="submit">Buscar</button>
</form>
<?php if ($checkEmail !== ''): ?>
  <pre><?php
  if ($checkError) {
      echo "Error al consultar: " . htmlspecialchars($checkError, ENT_QUOTES, 'UTF-8');
  } elseif ($checkResult) {
      echo "✅ SÍ está registrado:\n";
      echo "  id: {$checkResult['id']}\n";
      echo "  username: {$checkResult['username']}\n";
      echo "  email: {$checkResult['email']}\n";
      echo "  creado: {$checkResult['created_at']}\n";
  } else {
      echo "❌ NO hay ninguna cuenta con ese correo.\n";
      echo "   Por eso no llega ningún código: la API responde igual\n";
      echo "   \"si el correo está registrado, recibirás un código\" tanto\n";
      echo "   si existe como si no (a propósito, por seguridad).\n";
  }
  ?></pre>
<?php endif; ?>

<h2>4. Probar el login real en vivo</h2>
<p class="muted">Llama directo a api/login.php (el endpoint real que usa el juego) y muestra la respuesta cruda.</p>
<div class="row">
  <input type="email" id="test-email" placeholder="correo@ejemplo.com" value="<?= htmlspecialchars($checkEmail, ENT_QUOTES, 'UTF-8') ?>">
  <button type="button" onclick="probarRequestCode()">Probar "Enviar código"</button>
</div>
<pre id="test-result">(sin probar aún)</pre>

<h2>5. Últimas líneas del log de errores de PHP</h2>
<pre><?php
echo "Ruta configurada (error_log de PHP): " . htmlspecialchars($logPath ?: '(no configurada)', ENT_QUOTES, 'UTF-8') . "\n\n";
if ($logTail !== null) {
    echo $logTail === '' ? '(el archivo existe pero está vacío)' : htmlspecialchars($logTail, ENT_QUOTES, 'UTF-8');
} else {
    echo "No se puede leer desde aquí (normal en varios hostings compartidos).\n";
    echo "Revisa el log de tu panel (cPanel -> \"Errores\") o el de Apache/XAMPP directamente\n";
    echo "(en XAMPP suele estar en apache/logs/error.log dentro de la carpeta de instalación).";
}
?></pre>

<h2>6. Versión de assets</h2>
<pre>$ASSET_VERSION = '<?= htmlspecialchars($ASSET_VERSION, ENT_QUOTES, 'UTF-8') ?>' (definida en session_bootstrap.php)</pre>

<script>
const CSRF_TOKEN = "<?= htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8') ?>";

async function probarRequestCode() {
  const email = document.getElementById('test-email').value.trim();
  const out = document.getElementById('test-result');
  if (!email) { out.textContent = 'Escribe un correo primero.'; return; }
  out.textContent = 'Probando...';
  try {
    const res = await fetch('api/login.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      credentials: 'include',
      body: JSON.stringify({ action: 'request_code', email: email, csrf_token: CSRF_TOKEN }),
    });
    const data = await res.json();
    out.textContent = 'HTTP ' + res.status + '\n\n' + JSON.stringify(data, null, 2);
  } catch (err) {
    out.textContent = 'Error de red: ' + err.message;
  }
}
</script>

<p class="muted">Copia cualquier sección de esta página y compártela si necesitas ayuda — con esto ya no hay que adivinar nada.</p>

</body>
</html>
