<?php
session_start();
// Si ya hay sesión activa, saltamos directo al juego.
if (!empty($_SESSION['user_id'])) {
    header('Location: game.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
<title>Bifrost - Entrar</title>
<link href="https://fonts.googleapis.com/css2?family=Press+Start+2P&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/style.css">
</head>
<body>

<div class="gb-shell">
  <h1 class="gb-title">BIFROST</h1>

  <form class="gb-form" id="login-form">
    <label for="login-username">Usuario</label>
    <input id="login-username" name="username" autocomplete="username" required>
    <label for="login-password">Contraseña</label>
    <input id="login-password" name="password" type="password" autocomplete="current-password" required>
    <button type="submit">Entrar</button>
    <div class="gb-error" id="login-error"></div>
  </form>

  <form class="gb-form" id="register-form" style="display:none">
    <label for="reg-username">Elige un usuario</label>
    <input id="reg-username" name="username" required>
    <label for="reg-password">Elige una contraseña</label>
    <input id="reg-password" name="password" type="password" required>
    <button type="submit">Crear cuenta</button>
    <div class="gb-error" id="register-error"></div>
  </form>

  <div class="gb-switch" id="switch-link">¿No tienes cuenta? Crear una</div>

  <footer class="gb-footer">
    Desarrollado por <a href="https://helheim.cl" target="_blank" rel="noopener noreferrer">helheim.cl</a>
  </footer>
</div>

<script>
const loginForm = document.getElementById('login-form');
const registerForm = document.getElementById('register-form');
const switchLink = document.getElementById('switch-link');
let showingLogin = true;

switchLink.addEventListener('click', () => {
  showingLogin = !showingLogin;
  loginForm.style.display = showingLogin ? 'flex' : 'none';
  registerForm.style.display = showingLogin ? 'none' : 'flex';
  switchLink.textContent = showingLogin ? '¿No tienes cuenta? Crear una' : '¿Ya tienes cuenta? Entrar';
});

async function postJSON(url, body) {
  const res = await fetch(url, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    credentials: 'include',
    body: JSON.stringify(body)
  });
  const data = await res.json();
  if (!res.ok) throw new Error(data.error || 'Error inesperado');
  return data;
}

loginForm.addEventListener('submit', async (e) => {
  e.preventDefault();
  const errorBox = document.getElementById('login-error');
  errorBox.textContent = '';
  try {
    await postJSON('api/login.php', {
      username: document.getElementById('login-username').value,
      password: document.getElementById('login-password').value
    });
    window.location.href = 'game.php';
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
      password: document.getElementById('reg-password').value
    });
    window.location.href = 'game.php';
  } catch (err) {
    errorBox.textContent = err.message;
  }
});
</script>

</body>
</html>
