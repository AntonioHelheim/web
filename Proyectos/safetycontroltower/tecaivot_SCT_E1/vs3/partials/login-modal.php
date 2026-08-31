<?php
/**
 * Genera el token CSRF para el formulario de login si aún no existe
 * en la sesión. Requiere que session_bootstrap.php ya haya iniciado
 * la sesión antes de incluir este partial.
 */
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>

<!-- =====================================================
     LOGIN MODAL
====================================================== -->

<div
    class="modal fade"
    id="loginModal"
    tabindex="-1"
    aria-labelledby="loginModalLabel"
    aria-hidden="true">

    <div class="modal-dialog modal-dialog-centered">

        <div class="modal-content login-modal">

            <button
                type="button"
                class="btn-close"
                data-bs-dismiss="modal"
                aria-label="Cerrar">
            </button>


            <!-- =====================================================
                 BRAND
            ====================================================== -->

            <div class="modal-brand">

                <div class="modal-symbol">

                    <img
                        src="./images/logos/Logo-SCT-white.svg"
                        alt="Safety Control Tower">

                </div>

                <div class="modal-brand-text">

                    <strong>Safety Control</strong>

                    <small>TOWER</small>

                </div>

            </div>


            <!-- =====================================================
                 TITLE
            ====================================================== -->

            <h2 id="loginModalLabel">
                Bienvenido de vuelta
            </h2>

            <p>
                Ingresa a tu cuenta para acceder a tu plataforma.
            </p>


            <!-- =====================================================
                 LOGIN ALERT
            ====================================================== -->

            <div
                id="loginAlert"
                class="login-alert d-none"
                role="alert"
                aria-live="polite">
            </div>


            <!-- =====================================================
                 LOGIN FORM
            ====================================================== -->

            <form
                id="loginForm"
                method="post"
                action="login.php"
                novalidate>

                <!-- CSRF -->

                <input
                    type="hidden"
                    name="csrf_token"
                    value="<?php echo htmlspecialchars(
                        $_SESSION['csrf_token'] ?? '',
                        ENT_QUOTES,
                        'UTF-8'
                    ); ?>">


                <!-- =====================================================
                     EMAIL
                ====================================================== -->

                <div class="mb-3">

                    <label for="loginEmail">
                        Correo electrónico
                    </label>

                    <input
                        type="email"
                        class="form-control"
                        id="loginEmail"
                        name="email"
                        placeholder="correo@empresa.cl"
                        autocomplete="email"
                        maxlength="150"
                        aria-describedby="loginEmailError"
                        required>

                    <div
                        id="loginEmailError"
                        class="field-error d-none"
                        aria-live="polite">

                        Ingresa un correo electrónico válido.

                    </div>

                </div>


                <!-- =====================================================
                     PASSWORD
                ====================================================== -->

                <div class="mb-3">

                    <label for="loginPassword">
                        Contraseña
                    </label>

                    <div class="password-wrapper">

                        <input
                            type="password"
                            class="form-control"
                            id="loginPassword"
                            name="password"
                            placeholder="••••••••"
                            autocomplete="current-password"
                            minlength="8"
                            aria-describedby="loginPasswordError"
                            required>

                        <button
                            type="button"
                            id="togglePassword"
                            class="password-toggle"
                            aria-label="Mostrar contraseña"
                            aria-pressed="false"
                            data-target="loginPassword">

                            <i
                                class="bi bi-eye"
                                aria-hidden="true">
                            </i>

                        </button>

                    </div>

                    <div
                        id="loginPasswordError"
                        class="field-error d-none"
                        aria-live="polite">

                        La contraseña debe tener al menos 8 caracteres.

                    </div>

                </div>


                <!-- =====================================================
                     OPTIONS
                ====================================================== -->

                <div class="d-flex justify-content-between mb-4">

                    <div class="form-check">

                        <input
                            class="form-check-input"
                            type="checkbox"
                            id="remember"
                            name="remember"
                            value="1">

                        <label
                            class="form-check-label"
                            for="remember">

                            Recordarme

                        </label>

                    </div>

                    <a href="recuperar-contrasena.php">
                        ¿Olvidaste tu contraseña?
                    </a>

                </div>


                <!-- =====================================================
                     SUBMIT
                ====================================================== -->

                <button
                    type="submit"
                    id="loginSubmit"
                    class="btn btn-primary-custom w-100">

                    <span class="btn-label">
                        Iniciar sesión
                    </span>

                    <i class="bi bi-arrow-right btn-icon"></i>

                    <span
                        class="spinner-border spinner-border-sm d-none"
                        role="status"
                        aria-hidden="true">
                    </span>

                </button>

            </form>


            <!-- =====================================================
                 HELP
            ====================================================== -->

            <div class="login-help">

                ¿Aún no eres cliente?

                <a
                    href="#contacto"
                    data-bs-dismiss="modal">

                    Solicita información

                </a>

            </div>

        </div>

    </div>

</div>