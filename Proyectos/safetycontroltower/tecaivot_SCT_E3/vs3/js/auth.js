/**
 * ==========================================================
 * AUTH.JS — SAFETY CONTROL TOWER
 *
 * Autenticación de doble factor:
 *   1) correo + contraseña
 *   2) código de 6 dígitos enviado por correo
 *
 * Recuperación:
 *   - "¿Olvidaste tu contraseña?" abre el flujo inline cuando JS funciona.
 *   - El mismo control es un enlace real a recuperar-password.php, por lo
 *     que la recuperación sigue funcionando aunque este JS no cargue.
 * ==========================================================
 */

document.addEventListener("DOMContentLoaded", function () {
    const form = document.getElementById("loginForm");
    if (!form) return;

    const stepCredentialsDiv = document.getElementById("loginStepCredentials");
    const stepCodeDiv        = document.getElementById("loginStepCode");
    const stepResetDiv       = document.getElementById("loginStepReset");

    const emailInput         = document.getElementById("loginEmail");
    const emailError         = document.getElementById("loginEmailError");
    const passwordInput      = document.getElementById("loginPassword");
    const passwordError      = document.getElementById("loginPasswordError");
    const passwordToggle     = document.getElementById("loginPasswordToggle");

    const codeInput          = document.getElementById("loginCode");
    const codeError          = document.getElementById("loginCodeError");
    const stepCodeEmail      = document.getElementById("loginStepCodeEmail");
    const displayedCodeBox   = document.getElementById("loginDisplayedCode");
    const displayedCodeValue = document.getElementById("loginDisplayedCodeValue");
    const displayedCodeHelp  = document.getElementById("loginDisplayedCodeHelp");

    const sendCodeBtn        = document.getElementById("loginSendCode");
    const verifyCodeBtn      = document.getElementById("loginVerifyCode");
    const resendBtn          = document.getElementById("loginResendCode");
    const changeEmailBtn     = document.getElementById("loginChangeEmail");
    const forgotPasswordBtn  = document.getElementById("loginForgotPassword");
    const resetEmailInput    = document.getElementById("loginResetEmail");
    const resetEmailError    = document.getElementById("loginResetEmailError");
    const resetSendBtn       = document.getElementById("loginResetSend");
    const resetBackBtn       = document.getElementById("loginResetBack");
    const resetLocalBox      = document.getElementById("loginResetLocalPassword");
    const resetPasswordInput = document.getElementById("loginResetPassword");
    const resetPasswordError = document.getElementById("loginResetPasswordError");
    const resetConfirmInput  = document.getElementById("loginResetPasswordConfirm");
    const resetConfirmError  = document.getElementById("loginResetPasswordConfirmError");
    const resetSaveBtn       = document.getElementById("loginResetSave");

    const alertBox           = document.getElementById("loginAlert");
    const csrfInput          = document.getElementById("csrfToken");
    const languageSelect     = document.getElementById("loginLanguage");
    const passwordRequestUrl = form.dataset.passwordRequestUrl || "password-request.php";
    const passwordUpdateUrl  = form.dataset.passwordUpdateUrl || "password-update.php";
    const localResetReadyMsg = form.dataset.localResetReady || "Define tu nueva contraseña a continuación.";
    const localResetSavedMsg = form.dataset.localResetSaved || "Contraseña actualizada. Ya puedes iniciar sesión.";
    const passwordMismatchMsg = form.dataset.passwordMismatch || "Las contraseñas no coinciden.";

    if (
        !stepCredentialsDiv || !stepCodeDiv || !stepResetDiv ||
        !emailInput || !passwordInput || !codeInput || !alertBox ||
        !sendCodeBtn || !verifyCodeBtn || !resetEmailInput || !resetSendBtn ||
        !resetLocalBox || !resetPasswordInput || !resetConfirmInput || !resetSaveBtn
    ) {
        console.error("AUTH.JS: faltan elementos requeridos del formulario de acceso.");
        return;
    }

    let currentStep = "credentials";
    let verifiedEmail = "";
    let localResetToken = "";
    let resendCooldownTimer = null;

    function showAlert(message, variant) {
        variant = variant || "error";
        alertBox.textContent = message;
        alertBox.classList.remove(
            "d-none",
            "login-alert--error",
            "login-alert--warning",
            "login-alert--success"
        );
        alertBox.classList.add("login-alert--" + variant);
    }

    function hideAlert() {
        alertBox.classList.add("d-none");
        alertBox.classList.remove(
            "login-alert--error",
            "login-alert--warning",
            "login-alert--success"
        );
        alertBox.textContent = "";
    }

    function showFieldError(input, errorElement, message) {
        input.classList.add("is-invalid");
        if (errorElement) {
            errorElement.textContent = message || "";
            errorElement.classList.toggle("d-none", !message);
        }
    }

    function clearFieldError(input, errorElement) {
        input.classList.remove("is-invalid");
        if (errorElement) {
            errorElement.classList.add("d-none");
            errorElement.textContent = "";
        }
    }

    function setLoading(button, isLoading) {
        if (!button) return;
        button.disabled = isLoading;

        const label = button.querySelector(".btn-label");
        const spinner = button.querySelector(".login-spinner");
        if (label) label.classList.toggle("d-none", isLoading);
        if (spinner) spinner.classList.toggle("d-none", !isLoading);
    }

    function stopResendCooldown() {
        if (resendCooldownTimer) {
            clearInterval(resendCooldownTimer);
            resendCooldownTimer = null;
        }

        if (resendBtn) {
            resendBtn.disabled = false;
            if (resendBtn.dataset.originalLabel) {
                resendBtn.textContent = resendBtn.dataset.originalLabel;
            }
        }
    }

    function startResendCooldown(seconds) {
        stopResendCooldown();
        if (!resendBtn) return;

        let remaining = seconds;
        resendBtn.disabled = true;
        resendBtn.dataset.originalLabel = resendBtn.dataset.originalLabel || resendBtn.textContent.trim();
        resendBtn.textContent = resendBtn.dataset.originalLabel + " (" + remaining + "s)";

        resendCooldownTimer = setInterval(function () {
            remaining -= 1;
            if (remaining <= 0) {
                stopResendCooldown();
                return;
            }
            resendBtn.textContent = resendBtn.dataset.originalLabel + " (" + remaining + "s)";
        }, 1000);
    }

    function hideDisplayedCode() {
        if (displayedCodeBox) displayedCodeBox.classList.add("d-none");
        if (displayedCodeValue) displayedCodeValue.textContent = "";
        if (displayedCodeHelp) displayedCodeHelp.dataset.mode = "";
    }

    function showDisplayedCode(code, mode) {
        if (!displayedCodeBox || !displayedCodeValue || !code) return;

        displayedCodeValue.textContent = String(code);
        displayedCodeBox.classList.remove("d-none");
        if (displayedCodeHelp) displayedCodeHelp.dataset.mode = mode || "";
    }

    function goToResetStep() {
        currentStep = "reset";
        verifiedEmail = "";

        stepCredentialsDiv.classList.add("d-none");
        stepCodeDiv.classList.add("d-none");
        stepResetDiv.classList.remove("d-none");

        stopResendCooldown();
        clearFieldError(codeInput, codeError);
        clearFieldError(passwordInput, passwordError);
        clearFieldError(resetEmailInput, resetEmailError);
        clearFieldError(resetPasswordInput, resetPasswordError);
        clearFieldError(resetConfirmInput, resetConfirmError);
        passwordInput.value = "";
        codeInput.value = "";
        resetPasswordInput.value = "";
        resetConfirmInput.value = "";
        localResetToken = "";
        resetLocalBox.classList.add("d-none");
        resetSendBtn.classList.remove("d-none");
        resetEmailInput.readOnly = false;

        resetEmailInput.value = emailInput.value.trim();
        hideDisplayedCode();
        hideAlert();
        resetEmailInput.focus();
    }

    function goToCodeStep(email) {
        currentStep = "code";
        verifiedEmail = email;

        stepCredentialsDiv.classList.add("d-none");
        stepResetDiv.classList.add("d-none");
        stepCodeDiv.classList.remove("d-none");

        if (stepCodeEmail) stepCodeEmail.textContent = email;

        // La contraseña ya fue validada por el servidor. No se conserva.
        passwordInput.value = "";
        codeInput.value = "";
        clearFieldError(passwordInput, passwordError);
        clearFieldError(codeInput, codeError);

        startResendCooldown(30);
        codeInput.focus();
    }

    function goToCredentialsStep(options) {
        options = options || {};
        currentStep = "credentials";
        verifiedEmail = "";

        stepCodeDiv.classList.add("d-none");
        stepResetDiv.classList.add("d-none");
        stepCredentialsDiv.classList.remove("d-none");

        codeInput.value = "";
        passwordInput.value = "";
        resetPasswordInput.value = "";
        resetConfirmInput.value = "";
        localResetToken = "";
        resetLocalBox.classList.add("d-none");
        resetSendBtn.classList.remove("d-none");
        resetEmailInput.readOnly = false;
        hideDisplayedCode();
        clearFieldError(codeInput, codeError);
        clearFieldError(passwordInput, passwordError);
        clearFieldError(resetPasswordInput, resetPasswordError);
        clearFieldError(resetConfirmInput, resetConfirmError);
        stopResendCooldown();

        if (!options.keepAlert) hideAlert();

        if (options.clearEmail) {
            emailInput.value = "";
            emailInput.focus();
        } else {
            passwordInput.focus();
        }
    }

    function goToLocalPasswordStep(token) {
        localResetToken = token;
        currentStep = "reset_password";
        resetEmailInput.readOnly = true;
        resetSendBtn.classList.add("d-none");
        resetLocalBox.classList.remove("d-none");
        resetPasswordInput.value = "";
        resetConfirmInput.value = "";
        clearFieldError(resetPasswordInput, resetPasswordError);
        clearFieldError(resetConfirmInput, resetConfirmError);
        showAlert(localResetReadyMsg, "success");
        resetPasswordInput.focus();
    }

    async function guardarRestablecimientoLocal() {
        hideAlert();

        if (!localResetToken) {
            showAlert("La solicitud ya no es válida. Vuelve a iniciar la recuperación.", "warning");
            goToResetStep();
            return;
        }

        if (!resetPasswordInput.checkValidity()) {
            showFieldError(resetPasswordInput, resetPasswordError, resetPasswordInput.validationMessage);
            return;
        }
        clearFieldError(resetPasswordInput, resetPasswordError);

        if (!resetConfirmInput.checkValidity()) {
            showFieldError(resetConfirmInput, resetConfirmError, resetConfirmInput.validationMessage);
            return;
        }

        if (resetPasswordInput.value !== resetConfirmInput.value) {
            showFieldError(resetConfirmInput, resetConfirmError, passwordMismatchMsg);
            return;
        }
        clearFieldError(resetConfirmInput, resetConfirmError);
        setLoading(resetSaveBtn, true);

        try {
            const response = await fetch(passwordUpdateUrl, {
                method: "POST",
                credentials: "same-origin",
                headers: {
                    "Content-Type": "application/json",
                    "X-Requested-With": "XMLHttpRequest",
                },
                body: JSON.stringify({
                    token: localResetToken,
                    password: resetPasswordInput.value,
                    password_confirm: resetConfirmInput.value,
                    csrf_token: csrfInput ? csrfInput.value : "",
                }),
            });

            const data = await readJsonResponse(response);

            if (!data.success) {
                showAlert(data.message || "No fue posible actualizar la contraseña.", response.status === 403 ? "warning" : "error");
                return;
            }

            const recoveredEmail = (data.data && data.data.email) || resetEmailInput.value.trim();
            if (data.data && data.data.csrf_token && csrfInput) {
                csrfInput.value = data.data.csrf_token;
            }

            emailInput.value = recoveredEmail;
            localResetToken = "";
            goToCredentialsStep({ keepAlert: true });
            showAlert(localResetSavedMsg, "success");
        } catch (error) {
            console.error("AUTH.JS local password update:", error);
            showAlert("No se pudo conectar con el servidor. Intenta nuevamente.", "error");
        } finally {
            setLoading(resetSaveBtn, false);
        }
    }

    async function readJsonResponse(response) {
        try {
            return await response.json();
        } catch (error) {
            throw new Error("Respuesta inválida del servidor.");
        }
    }

    async function llamarLogin(payload) {
        const response = await fetch(form.action, {
            method: "POST",
            credentials: "same-origin",
            headers: {
                "Content-Type": "application/json",
                "X-Requested-With": "XMLHttpRequest",
            },
            body: JSON.stringify(payload),
        });

        return { status: response.status, data: await readJsonResponse(response) };
    }

    async function solicitarRestablecimiento(email) {
        hideAlert();
        resetEmailInput.value = email;

        if (!resetEmailInput.checkValidity()) {
            showFieldError(resetEmailInput, resetEmailError, resetEmailInput.validationMessage);
            return;
        }
        clearFieldError(resetEmailInput, resetEmailError);
        setLoading(resetSendBtn, true);

        try {
            const response = await fetch(passwordRequestUrl, {
                method: "POST",
                credentials: "same-origin",
                headers: {
                    "Content-Type": "application/json",
                    "X-Requested-With": "XMLHttpRequest",
                },
                body: JSON.stringify({
                    email: email,
                    csrf_token: csrfInput ? csrfInput.value : "",
                }),
            });

            const data = await readJsonResponse(response);

            if (response.status === 403) {
                showAlert(data.message || "Tu sesión expiró. Recarga la página.", "warning");
                return;
            }

            if (response.status === 429) {
                showAlert(data.message || "Demasiadas solicitudes. Intenta más tarde.", "warning");
                return;
            }

            if (!data.success) {
                showAlert(data.message || "No fue posible procesar la solicitud.", "error");
                return;
            }

            if (data.data && data.data.local_token) {
                goToLocalPasswordStep(data.data.local_token);
                return;
            }

            showAlert(data.message || "Si la cuenta existe, recibirás un enlace por correo.", "success");
        } catch (error) {
            console.error("AUTH.JS recovery:", error);
            showAlert("No se pudo conectar con el servidor. También puedes usar la página de recuperación.", "error");
        } finally {
            setLoading(resetSendBtn, false);
        }
    }

    async function solicitarCodigo(email, password) {
        hideAlert();

        if (!emailInput.checkValidity()) {
            showFieldError(emailInput, emailError, emailInput.validationMessage);
            return;
        }
        clearFieldError(emailInput, emailError);

        if (!password) {
            showFieldError(passwordInput, passwordError, "Ingresa tu contraseña o utiliza “¿Olvidaste tu contraseña?”.");
            return;
        }
        clearFieldError(passwordInput, passwordError);
        setLoading(sendCodeBtn, true);

        try {
            const { status, data } = await llamarLogin({
                action: "request_code",
                email: email,
                password: password,
                csrf_token: csrfInput ? csrfInput.value : "",
            });

            if (status === 429) {
                showAlert(data.message || "Demasiados intentos. Intenta más tarde.", "warning");
                return;
            }

            if (!data.success) {
                showAlert(
                    data.message || "No fue posible validar las credenciales. Si tu acceso fue migrado, restablece tu contraseña.",
                    "error"
                );
                return;
            }

            showAlert(data.message || "Código enviado.", "success");
            goToCodeStep(email);
            if (data.data && data.data.display_code) {
                showDisplayedCode(data.data.display_code, data.data.display_mode || "");
            } else {
                hideDisplayedCode();
            }
        } catch (error) {
            console.error("AUTH.JS login:", error);
            showAlert("No se pudo conectar con el servidor. Intenta nuevamente.", "error");
        } finally {
            setLoading(sendCodeBtn, false);
        }
    }

    async function reenviarCodigo() {
        if (!verifiedEmail || !resendBtn) return;

        hideAlert();
        resendBtn.disabled = true;

        try {
            const { status, data } = await llamarLogin({
                action: "resend_code",
                email: verifiedEmail,
                csrf_token: csrfInput ? csrfInput.value : "",
            });

            if (status === 401 || status === 403) {
                showAlert(data.message || "Vuelve a validar tus credenciales.", "warning");
                goToCredentialsStep({ keepAlert: true });
                return;
            }

            if (status === 429) {
                showAlert(data.message || "Espera antes de solicitar otro código.", "warning");
                return;
            }

            if (!data.success) {
                showAlert(data.message || "No fue posible reenviar el código.", "error");
                return;
            }

            showAlert(data.message || "Código reenviado.", "success");
            if (data.data && data.data.display_code) {
                showDisplayedCode(data.data.display_code, data.data.display_mode || "");
            } else {
                hideDisplayedCode();
            }
            codeInput.value = "";
            codeInput.focus();
            startResendCooldown(30);
        } catch (error) {
            console.error("AUTH.JS resend:", error);
            showAlert("No se pudo conectar con el servidor. Intenta nuevamente.", "error");
        } finally {
            if (!resendCooldownTimer) resendBtn.disabled = false;
        }
    }

    async function verificarCodigo(code) {
        hideAlert();

        if (!/^\d{6}$/.test(code)) {
            showFieldError(codeInput, codeError, "Ingresa el código de 6 dígitos.");
            return;
        }
        clearFieldError(codeInput, codeError);
        setLoading(verifyCodeBtn, true);

        try {
            const { status, data } = await llamarLogin({
                action: "verify_code",
                email: verifiedEmail,
                code: code,
                csrf_token: csrfInput ? csrfInput.value : "",
            });

            if (data.success) {
                const destino = (data.data && data.data.redirect) || "bienvenida.php";
                window.location.href = destino;
                return;
            }

            if (status === 403) {
                showAlert(data.message || "La validación previa expiró. Ingresa nuevamente.", "warning");
                goToCredentialsStep({ keepAlert: true });
                return;
            }

            if (status === 429) {
                showAlert(data.message || "Demasiados intentos. Vuelve a iniciar el acceso.", "warning");
                return;
            }

            showAlert(data.message || "Código inválido o expirado.", "error");
        } catch (error) {
            console.error("AUTH.JS verify:", error);
            showAlert("No se pudo conectar con el servidor. Intenta nuevamente.", "error");
        } finally {
            setLoading(verifyCodeBtn, false);
        }
    }

    form.addEventListener("submit", function (event) {
        event.preventDefault();

        if (currentStep === "credentials") {
            solicitarCodigo(emailInput.value.trim(), passwordInput.value);
        } else if (currentStep === "reset") {
            solicitarRestablecimiento(resetEmailInput.value.trim());
        } else if (currentStep === "reset_password") {
            guardarRestablecimientoLocal();
        } else {
            verificarCodigo(codeInput.value.trim());
        }
    });

    // IMPORTANTE: este listener debe vivir fuera de stopResendCooldown().
    // La versión anterior lo registraba dentro de esa función, de modo que
    // el primer clic en "¿Olvidaste tu contraseña?" no tenía listener.
    if (forgotPasswordBtn) {
        forgotPasswordBtn.addEventListener("click", function (event) {
            if (forgotPasswordBtn.dataset.inlineReset === "true") {
                event.preventDefault();
                goToResetStep();
            }
        });
    }

    if (resetBackBtn) {
        resetBackBtn.addEventListener("click", function () {
            goToCredentialsStep();
        });
    }

    if (resendBtn) {
        resendBtn.addEventListener("click", function () {
            if (!resendBtn.disabled) reenviarCodigo();
        });
    }

    if (changeEmailBtn) {
        changeEmailBtn.addEventListener("click", function () {
            goToCredentialsStep({ clearEmail: true });
        });
    }

    if (passwordToggle) {
        passwordToggle.addEventListener("click", function () {
            const mostrando = passwordInput.type === "text";
            passwordInput.type = mostrando ? "password" : "text";
            passwordToggle.setAttribute("aria-pressed", mostrando ? "false" : "true");
        });
    }

    emailInput.addEventListener("input", function () {
        if (emailInput.classList.contains("is-invalid")) clearFieldError(emailInput, emailError);
        hideAlert();
    });

    passwordInput.addEventListener("input", function () {
        if (passwordInput.classList.contains("is-invalid")) clearFieldError(passwordInput, passwordError);
        hideAlert();
    });

    resetEmailInput.addEventListener("input", function () {
        if (resetEmailInput.classList.contains("is-invalid")) clearFieldError(resetEmailInput, resetEmailError);
        hideAlert();
    });


    resetPasswordInput.addEventListener("input", function () {
        if (resetPasswordInput.classList.contains("is-invalid")) clearFieldError(resetPasswordInput, resetPasswordError);
        hideAlert();
    });

    resetConfirmInput.addEventListener("input", function () {
        if (resetConfirmInput.classList.contains("is-invalid")) clearFieldError(resetConfirmInput, resetConfirmError);
        hideAlert();
    });

    document.querySelectorAll("[data-reset-password-toggle]").forEach(function (button) {
        button.addEventListener("click", function () {
            const input = document.getElementById(button.getAttribute("data-reset-password-toggle"));
            if (!input) return;
            input.type = input.type === "password" ? "text" : "password";
        });
    });

    codeInput.addEventListener("input", function () {
        codeInput.value = codeInput.value.replace(/\D/g, "").slice(0, 6);
        if (codeInput.classList.contains("is-invalid")) clearFieldError(codeInput, codeError);
        hideAlert();
    });

    if (languageSelect) {
        languageSelect.addEventListener("change", function () {
            const lang = languageSelect.value;
            if (/^(es|en|pt|fr|zh)$/.test(lang)) {
                window.location.href = "?lang=" + encodeURIComponent(lang);
            }
        });
    }
});
