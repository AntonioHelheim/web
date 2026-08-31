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
     LOGIN MODAL — flujo de 2 pasos: correo -> código enviado por email
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
                 LOGIN ALERT
            ====================================================== -->

            <div
                id="loginAlert"
                class="login-alert d-none"
                role="alert"
                aria-live="polite">
            </div>


            <form id="loginForm" method="post" action="login.php" novalidate>

                <!-- CSRF -->
                <input
                    type="hidden"
                    name="csrf_token"
                    id="csrfToken"
                    value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">

                <!-- data-step indica en qué paso está el formulario; lo usa auth.js -->
                <input type="hidden" id="loginStepInput" value="email">


                <!-- =====================================================
                     PASO 1 — CORREO
                ====================================================== -->

                <div id="loginStepEmail">

                    <h2 id="loginModalLabel">
                        <?php echo htmlspecialchars(t('login_title'), ENT_QUOTES, 'UTF-8'); ?>
                    </h2>

                    <p>
                        <?php echo htmlspecialchars(t('login_subtitle'), ENT_QUOTES, 'UTF-8'); ?>
                    </p>

                    <div class="mb-3">

                        <label for="loginEmail">
                            <?php echo htmlspecialchars(t('login_email_label'), ENT_QUOTES, 'UTF-8'); ?>
                        </label>

                        <input
                            type="email"
                            class="form-control"
                            id="loginEmail"
                            name="email"
                            placeholder="<?php echo htmlspecialchars(t('login_email_placeholder'), ENT_QUOTES, 'UTF-8'); ?>"
                            autocomplete="email"
                            maxlength="150"
                            aria-describedby="loginEmailError"
                            required>

                        <div
                            id="loginEmailError"
                            class="field-error d-none"
                            aria-live="polite">
                        </div>

                    </div>

                    <button
                        type="submit"
                        id="loginSendCode"
                        class="btn btn-primary-custom w-100">

                        <span class="btn-label">
                            <?php echo htmlspecialchars(t('login_send_code_button'), ENT_QUOTES, 'UTF-8'); ?>
                        </span>

                        <i class="bi bi-arrow-right btn-icon"></i>

                        <span
                            class="spinner-border spinner-border-sm d-none"
                            role="status"
                            aria-hidden="true">
                        </span>

                    </button>

                </div>


                <!-- =====================================================
                     PASO 2 — CÓDIGO (oculto hasta enviar el correo)
                ====================================================== -->

                <div id="loginStepCode" class="d-none">

                    <h2>
                        <?php echo htmlspecialchars(t('login_step2_title'), ENT_QUOTES, 'UTF-8'); ?>
                    </h2>

                    <p>
                        <?php echo htmlspecialchars(t('login_step2_subtitle'), ENT_QUOTES, 'UTF-8'); ?>
                        <strong id="loginStepCodeEmail"></strong>
                    </p>

                    <div class="mb-3">

                        <label for="loginCode">
                            <?php echo htmlspecialchars(t('login_code_label'), ENT_QUOTES, 'UTF-8'); ?>
                        </label>

                        <input
                            type="text"
                            class="form-control login-code-input"
                            id="loginCode"
                            name="code"
                            inputmode="numeric"
                            pattern="[0-9]*"
                            maxlength="6"
                            placeholder="<?php echo htmlspecialchars(t('login_code_placeholder'), ENT_QUOTES, 'UTF-8'); ?>"
                            aria-describedby="loginCodeError"
                            autocomplete="one-time-code">

                        <div
                            id="loginCodeError"
                            class="field-error d-none"
                            aria-live="polite">
                        </div>

                    </div>

                    <button
                        type="submit"
                        id="loginVerifyCode"
                        class="btn btn-primary-custom w-100">

                        <span class="btn-label">
                            <?php echo htmlspecialchars(t('login_verify_button'), ENT_QUOTES, 'UTF-8'); ?>
                        </span>

                        <i class="bi bi-arrow-right btn-icon"></i>

                        <span
                            class="spinner-border spinner-border-sm d-none"
                            role="status"
                            aria-hidden="true">
                        </span>

                    </button>

                    <div class="login-step-actions d-flex justify-content-between mt-3">

                        <button type="button" id="loginResendCode" class="btn-link-plain">
                            <?php echo htmlspecialchars(t('login_resend_code'), ENT_QUOTES, 'UTF-8'); ?>
                        </button>

                        <button type="button" id="loginChangeEmail" class="btn-link-plain">
                            <?php echo htmlspecialchars(t('login_change_email'), ENT_QUOTES, 'UTF-8'); ?>
                        </button>

                    </div>

                </div>

            </form>


            <!-- =====================================================
                 HELP
            ====================================================== -->

            <div class="login-help">

                <?php echo htmlspecialchars(t('login_help_text'), ENT_QUOTES, 'UTF-8'); ?>

                <a href="#contacto" data-bs-dismiss="modal">
                    <?php echo htmlspecialchars(t('login_help_link'), ENT_QUOTES, 'UTF-8'); ?>
                </a>

            </div>

        </div>

    </div>

</div>