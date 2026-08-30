<?php
require __DIR__ . '/session_bootstrap.php';
aplicarCabecerasSeguridad();

// Si ya hay sesión activa, saltamos directo al juego.
if (!empty($_SESSION['user_id'])) {
    header('Location: game.php');
    exit;
}

$sesionExpirada = !empty($_SESSION['session_expired']);
unset($_SESSION['session_expired']);
?>
<!DOCTYPE html>
<!-- Bifrost build: <?= $ASSET_VERSION ?> — esta variable se define en
     session_bootstrap.php (NO en este archivo), para que un solo cambio
     ahí actualice el número de versión en todas las páginas a la vez.
     Si este comentario no cambia después de subir archivos nuevos, el
     navegador (o el hosting) está sirviendo una copia vieja en caché. -->
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
<title>Bifrost</title>
<link href="https://fonts.googleapis.com/css2?family=Press+Start+2P&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/style.css?v=<?= $ASSET_VERSION ?>">
</head>
<body>

<header class="gb-navbar">
  <div class="gb-navbar-brand">BIFROST</div>
  <button type="button" class="gb-btn js-open-login" data-tab="login">Iniciar sesión</button>
</header>

<main>

  <section class="gb-hero">
    <?php if ($sesionExpirada): ?>
      <div class="gb-notice">Tu sesión expiró por inactividad. Vuelve a iniciar sesión.</div>
    <?php endif; ?>

    <h1 class="gb-hero-title">BIFROST</h1>
    <p class="gb-hero-tagline">
      Un mundo 2D estilo Game Boy para explorar, domesticar criaturas y
      jugar con amigos — directo en tu navegador, sin instalar nada.
    </p>
    <div class="gb-hero-actions">
      <button type="button" class="gb-btn js-open-login" data-tab="login">Iniciar sesión</button>
      <button type="button" class="gb-btn gb-btn-ghost js-open-login" data-tab="register">Crear cuenta</button>
    </div>
  </section>

  <section class="gb-section">
    <h2>Sobre el juego</h2>
    <p>
      Bifrost es un juego de rol 2D inspirado en los clásicos de Game Boy:
      explora un pueblo y sus rutas, personaliza tu personaje, y encuéntrate
      con otros jugadores en el mismo mundo compartido — todo corriendo
      como formas dibujadas en el navegador, sin necesitar ninguna
      descarga ni sprites externos.
    </p>
  </section>

  <section class="gb-section">
    <h2>Características</h2>
    <div class="gb-feature-grid">
      <div class="gb-feature-card">
        <h3>Personaliza tu personaje</h3>
        <p>Elige género, color de piel, pelo y ojos — y cámbialos cuando quieras desde el menú del juego.</p>
      </div>
      <div class="gb-feature-card">
        <h3>Mundo compartido</h3>
        <p>Ve a otros jugadores moverse por el mismo mapa casi en tiempo real, sin instalar nada extra.</p>
      </div>
      <div class="gb-feature-card">
        <h3>Batallas por turnos</h3>
        <p>Enfréntate a criaturas salvajes o reta a otro jugador a un duelo — el servidor calcula el resultado.</p>
      </div>
      <div class="gb-feature-card">
        <h3>24 criaturas originales</h3>
        <p>8 tipos propios (fuego, agua, planta, eléctrico, lucha, volador, oscuro y diurno), 3 criaturas cada uno.</p>
      </div>
      <div class="gb-feature-card">
        <h3>Un pueblo y 4 rutas</h3>
        <p>Explora el Pueblo Origen y sus cuatro salidas — una por cada punto cardinal.</p>
      </div>
      <div class="gb-feature-card">
        <h3>Escritorio y móvil</h3>
        <p>La pantalla se ajusta sola al tamaño de tu dispositivo, sin configurar nada.</p>
      </div>
    </div>
  </section>

</main>

<div class="gb-landing-footer-wrap">
  <footer class="gb-footer">
    Desarrollado por <a href="https://helheim.cl" target="_blank" rel="noopener noreferrer">helheim.cl</a>
  </footer>
</div>

<!-- Modal de login/registro. Se abre con los botones "Iniciar sesión" /
     "Crear cuenta" de arriba; el formulario en sí es el mismo login por
     código en 2 pasos de siempre, solo que ahora vive en una ventana
     emergente en vez de estar fijo en la página. -->
<div id="login-modal-overlay" class="gb-overlay" style="display:none;">
  <div class="gb-panel">
    <button type="button" class="gb-modal-close" id="login-modal-close" aria-label="Cerrar">×</button>
    <h1 class="gb-title" style="margin:0 0 10px;">BIFROST</h1>

    <!-- Paso 1 de login: pedir el código -->
    <form class="gb-form" id="request-code-form">
      <label for="login-email">Correo electrónico</label>
      <input id="login-email" name="email" type="email" autocomplete="email" required>
      <button type="submit">Enviar código</button>
      <div class="gb-error" id="request-code-error"></div>
    </form>

    <!-- Paso 2 de login: verificar el código de 6 dígitos -->
    <form class="gb-form" id="verify-code-form" style="display:none">
      <label for="login-code">Código de acceso (revisa tu correo)</label>
      <input id="login-code" name="code" inputmode="numeric" maxlength="6" autocomplete="one-time-code" required>
      <button type="submit">Verificar</button>
      <button type="button" class="gb-btn gb-btn-ghost" id="resend-code-btn" style="width:100%;">Reenviar código</button>
      <div class="gb-switch" id="change-email-link">Cambiar correo</div>
      <div class="gb-error" id="verify-code-error"></div>
      <div class="gb-dev-code" id="dev-code-hint" style="display:none"></div>
    </form>

    <!-- Registro: usuario + correo, sin contraseña (el acceso es por código) -->
    <form class="gb-form" id="register-form" style="display:none">
      <label for="reg-username">Elige un usuario</label>
      <input id="reg-username" name="username" required>
      <label for="reg-email">Tu correo electrónico</label>
      <input id="reg-email" name="email" type="email" autocomplete="email" required>
      <button type="submit">Crear cuenta</button>
      <div class="gb-error" id="register-error"></div>
    </form>

    <div class="gb-switch" id="switch-link">¿No tienes cuenta? Crear una</div>
  </div>
</div>

<script>
// Token CSRF de la sesión actual: acompaña cada POST sensible (login, registro).
const CSRF_TOKEN = "<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>";

const modalOverlay = document.getElementById('login-modal-overlay');
const modalClose = document.getElementById('login-modal-close');
const requestCodeForm = document.getElementById('request-code-form');
const verifyCodeForm = document.getElementById('verify-code-form');
const registerForm = document.getElementById('register-form');
const switchLink = document.getElementById('switch-link');
const resendBtn = document.getElementById('resend-code-btn');
const changeEmailLink = document.getElementById('change-email-link');
let showingLogin = true;
let pendingEmail = '';
let resendCooldownTimer = null;

function showStep(step) {
  requestCodeForm.style.display = step === 'request' ? 'flex' : 'none';
  verifyCodeForm.style.display = step === 'verify' ? 'flex' : 'none';
  registerForm.style.display = step === 'register' ? 'flex' : 'none';
  switchLink.style.display = step === 'verify' ? 'none' : 'block';
  showingLogin = step !== 'register';
  switchLink.textContent = showingLogin ? '¿No tienes cuenta? Crear una' : '¿Ya tienes cuenta? Entrar';
  if (step === 'verify') startResendCooldown(30);
  else stopResendCooldown();
}

// Igual que en el patrón de referencia: tras pedir un código, el botón de
// "Reenviar" queda deshabilitado un rato con una cuenta regresiva, para no
// invitar a golpear el límite de solicitudes sin darse cuenta.
function startResendCooldown(seconds) {
  stopResendCooldown();
  let remaining = seconds;
  resendBtn.disabled = true;
  resendBtn.textContent = `Reenviar código (${remaining}s)`;
  resendCooldownTimer = setInterval(() => {
    remaining -= 1;
    if (remaining <= 0) {
      stopResendCooldown();
      return;
    }
    resendBtn.textContent = `Reenviar código (${remaining}s)`;
  }, 1000);
}

function stopResendCooldown() {
  if (resendCooldownTimer) {
    clearInterval(resendCooldownTimer);
    resendCooldownTimer = null;
  }
  resendBtn.disabled = false;
  resendBtn.textContent = 'Reenviar código';
}

function openModal(tab) {
  modalOverlay.style.display = 'flex';
  showStep(tab === 'register' ? 'register' : 'request');
}

function closeModal() {
  modalOverlay.style.display = 'none';
  stopResendCooldown();
}

document.querySelectorAll('.js-open-login').forEach((btn) => {
  btn.addEventListener('click', () => openModal(btn.dataset.tab));
});

modalClose.addEventListener('click', closeModal);

// Clic en el fondo oscuro (fuera del panel) también cierra el modal.
modalOverlay.addEventListener('click', (e) => {
  if (e.target === modalOverlay) closeModal();
});

// Tecla Escape cierra el modal. A diferencia del menú del juego (Phaser),
// esto es HTML/JS normal sin escenas de por medio, así que no tiene el
// riesgo de "doble disparo" que sí existía ahí — este listener se
// registra una sola vez al cargar la página, nunca de nuevo en respuesta
// a la propia tecla.
document.addEventListener('keydown', (e) => {
  if (e.key === 'Escape' && modalOverlay.style.display !== 'none') closeModal();
});

switchLink.addEventListener('click', () => {
  showStep(showingLogin ? 'register' : 'request');
});

changeEmailLink.addEventListener('click', () => {
  showStep('request');
});

async function postJSON(url, body) {
  const res = await fetch(url, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    credentials: 'include',
    body: JSON.stringify({ ...body, csrf_token: CSRF_TOKEN }),
  });
  const data = await res.json();
  if (!res.ok) throw new Error(data.error || 'Error inesperado');
  return data;
}

function showDevCode(data) {
  const devHint = document.getElementById('dev-code-hint');
  if (data.dev_code) {
    // Solo aparece en localhost: en producción el código nunca viaja
    // en la respuesta, se manda por correo de verdad.
    devHint.textContent = 'Modo desarrollo — tu código es: ' + data.dev_code;
    devHint.style.display = 'block';
    document.getElementById('login-code').value = data.dev_code;
  } else {
    devHint.style.display = 'none';
  }
}

// Compartida entre el paso 1 (primer envío) y el botón "Reenviar código":
// mismo endpoint, mismo manejo de errores y del código de desarrollo.
async function solicitarCodigo(email, errorBox) {
  errorBox.textContent = '';
  try {
    const data = await postJSON('api/login.php', { action: 'request_code', email });
    pendingEmail = email;
    showStep('verify');
    document.getElementById('login-code').value = '';
    document.getElementById('verify-code-error').textContent = '';
    showDevCode(data);
    return true;
  } catch (err) {
    errorBox.textContent = err.message;
    return false;
  }
}

requestCodeForm.addEventListener('submit', async (e) => {
  e.preventDefault();
  const email = document.getElementById('login-email').value.trim();
  await solicitarCodigo(email, document.getElementById('request-code-error'));
});

resendBtn.addEventListener('click', async () => {
  if (resendBtn.disabled || !pendingEmail) return;
  // showStep('verify') dentro de solicitarCodigo() ya reinicia el
  // cronómetro de espera, así que no hace falta repetirlo aquí.
  await solicitarCodigo(pendingEmail, document.getElementById('verify-code-error'));
});

verifyCodeForm.addEventListener('submit', async (e) => {
  e.preventDefault();
  const errorBox = document.getElementById('verify-code-error');
  errorBox.textContent = '';
  const code = document.getElementById('login-code').value.trim();
  try {
    const data = await postJSON('api/login.php', { action: 'verify_code', email: pendingEmail, code });
    window.location.href = data.redirect || 'game.php';
  } catch (err) {
    errorBox.textContent = err.message;
  }
});

registerForm.addEventListener('submit', async (e) => {
  e.preventDefault();
  const errorBox = document.getElementById('register-error');
  errorBox.textContent = '';
  try {
    await postJSON('api/register.php', {
      username: document.getElementById('reg-username').value,
      email: document.getElementById('reg-email').value.trim(),
    });
    // Cuenta creada: pedimos el código de acceso de una vez, sin hacer
    // que el jugador tenga que volver a escribir el correo.
    await solicitarCodigo(document.getElementById('reg-email').value.trim(), errorBox);
  } catch (err) {
    errorBox.textContent = err.message;
  }
});

<?php if ($sesionExpirada): ?>
// La sesión expiró: abrimos el modal de una vez para que no tenga que
// buscar el botón.
openModal('login');
<?php endif; ?>
</script>

</body>
</html>
