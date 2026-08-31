/**
 * auth.js
 * Conecta el formulario #loginForm del modal con login.php.
 * Inclúyelo en tu HTML después de Bootstrap:
 *   <script src="auth.js"></script>
 */
document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('loginForm');
    if (!form) return;

    // Contenedor para mensajes de error (se crea si no existe)
    let errorBox = document.getElementById('loginError');
    if (!errorBox) {
        errorBox = document.createElement('div');
        errorBox.id = 'loginError';
        errorBox.className = 'alert alert-danger d-none mt-2';
        form.prepend(errorBox);
    }

    form.addEventListener('submit', async (e) => {
        e.preventDefault();

        const email = document.getElementById('loginEmail').value.trim();
        const password = document.getElementById('loginPassword').value.trim();
        const submitBtn = form.querySelector('button[type="submit"]');

        errorBox.classList.add('d-none');
        errorBox.textContent = '';

        // Estado de carga
        const originalBtnHTML = submitBtn.innerHTML;
        submitBtn.disabled = true;
        submitBtn.innerHTML = 'Verificando...';

        try {
            const response = await fetch('login.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ email, password })
            });

            const data = await response.json();

            if (data.success) {
                // Login correcto -> página de bienvenida
                window.location.href = data.redirect || 'bienvenida.php';
            } else {
                // Login rechazado -> error inline en el modal (sin redirigir)
                errorBox.textContent = data.message || 'Usuario o contraseña incorrectos.';
                errorBox.classList.remove('d-none');
            }
        } catch (err) {
            errorBox.textContent = 'No se pudo conectar con el servidor. Intenta nuevamente.';
            errorBox.classList.remove('d-none');
        } finally {
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalBtnHTML;
        }
    });
});