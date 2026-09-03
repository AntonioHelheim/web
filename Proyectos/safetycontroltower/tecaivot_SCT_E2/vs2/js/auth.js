/**
 * ==========================================================
 * AUTH.JS
 * SAFETY CONTROL TOWER
 *
 * Flujo de login sin contraseña, en 2 pasos:
 *   1) El usuario ingresa su correo -> se le envía un código de 6 dígitos.
 *   2) El usuario ingresa el código -> si es válido, se crea la sesión.
 * ==========================================================
 */

document.addEventListener("DOMContentLoaded", function () {

    const form = document.getElementById("loginForm");

    if (!form) {
        return;
    }

    /* ==========================================================
       ELEMENTOS
    ========================================================== */

    const stepEmailDiv   = document.getElementById("loginStepEmail");
    const stepCodeDiv    = document.getElementById("loginStepCode");

    const emailInput     = document.getElementById("loginEmail");
    const emailError     = document.getElementById("loginEmailError");

    const codeInput      = document.getElementById("loginCode");
    const codeError      = document.getElementById("loginCodeError");
    const stepCodeEmail  = document.getElementById("loginStepCodeEmail");

    const sendCodeBtn    = document.getElementById("loginSendCode");
    const verifyCodeBtn  = document.getElementById("loginVerifyCode");
    const resendBtn      = document.getElementById("loginResendCode");
    const changeEmailBtn = document.getElementById("loginChangeEmail");

    const alertBox       = document.getElementById("loginAlert");
    const csrfInput      = document.getElementById("csrfToken");

    if (!stepEmailDiv || !stepCodeDiv || !emailInput || !codeInput || !alertBox) {
        console.error("AUTH.JS: Faltan elementos requeridos del formulario de login.");
        return;
    }

    let currentStep = "email";
    let verifiedEmail = "";
    let resendCooldownTimer = null;


    /* ==========================================================
       ALERTAS
    ========================================================== */

    function showAlert(message, variant) {
        variant = variant || "error";
        alertBox.textContent = message;
        alertBox.classList.remove("d-none", "login-alert--error", "login-alert--warning", "login-alert--success");
        alertBox.classList.add("login-alert--" + variant);
    }

    function hideAlert() {
        alertBox.classList.add("d-none");
        alertBox.classList.remove("login-alert--error", "login-alert--warning", "login-alert--success");
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


    /* ==========================================================
       ESTADO DE CARGA DE UN BOTÓN
    ========================================================== */

    function setLoading(button, isLoading) {
        if (!button) {
            return;
        }
        button.disabled = isLoading;
        const label = button.querySelector(".btn-label");
        const icon = button.querySelector(".btn-icon");
        const spinner = button.querySelector(".spinner-border");
        if (label) label.classList.toggle("d-none", isLoading);
        if (icon) icon.classList.toggle("d-none", isLoading);
        if (spinner) spinner.classList.toggle("d-none", !isLoading);
    }


    /* ==========================================================
       CAMBIO DE PASO
    ========================================================== */

    function goToCodeStep(email) {
        currentStep = "code";
        verifiedEmail = email;
        stepEmailDiv.classList.add("d-none");
        stepCodeDiv.classList.remove("d-none");
        if (stepCodeEmail) {
            stepCodeEmail.textContent = email;
        }
        codeInput.value = "";
        clearFieldError(codeInput, codeError);
        startResendCooldown(30);
        codeInput.focus();
    }

    function goToEmailStep() {
        currentStep = "email";
        verifiedEmail = "";
        stepCodeDiv.classList.add("d-none");
        stepEmailDiv.classList.remove("d-none");
        clearFieldError(codeInput, codeError);
        hideAlert();
        stopResendCooldown();
    }

    function startResendCooldown(seconds) {
        stopResendCooldown();
        if (!resendBtn) {
            return;
        }
        let remaining = seconds;
        resendBtn.disabled = true;
        const label = resendBtn.textContent;
        resendBtn.dataset.originalLabel = resendBtn.dataset.originalLabel || label;
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


    /* ==========================================================
       LLAMADA AL BACKEND
    ========================================================== */

    async function llamarLogin(payload) {
        const response = await fetch(form.action, {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-Requested-With": "XMLHttpRequest",
            },
            body: JSON.stringify(payload),
        });

        let data;
        try {
            data = await response.json();
        } catch (jsonError) {
            throw new Error("Respuesta inválida del servidor.");
        }

        return { status: response.status, data: data };
    }


    /* ==========================================================
       SOLICITAR CÓDIGO (paso 1)
    ========================================================== */

    async function solicitarCodigo(email, triggerButton) {
        hideAlert();

        if (!emailInput.checkValidity()) {
            showFieldError(emailInput, emailError, emailInput.validationMessage);
            return;
        }
        clearFieldError(emailInput, emailError);

        setLoading(triggerButton, true);

        try {
            const { status, data } = await llamarLogin({
                action: "request_code",
                email: email,
                csrf_token: csrfInput ? csrfInput.value : "",
            });

            if (status === 429) {
                showAlert(data.message || "Demasiadas solicitudes. Intenta más tarde.", "warning");
                return;
            }

            if (!data.success) {
                showAlert(data.message || "No se pudo enviar el código. Intenta nuevamente.", "error");
                return;
            }

            showAlert(data.message || "Te enviamos un código a tu correo.", "success");
            goToCodeStep(email);

        } catch (error) {
            console.error("AUTH.JS:", error);
            showAlert("No se pudo conectar con el servidor. Intenta nuevamente.", "error");
        } finally {
            setLoading(triggerButton, false);
        }
    }


    /* ==========================================================
       VERIFICAR CÓDIGO (paso 2)
    ========================================================== */

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

            if (status === 429) {
                showAlert(data.message || "Demasiados intentos. Solicita un nuevo código.", "warning");
                return;
            }

            showAlert(data.message || "Código inválido o expirado.", "error");

        } catch (error) {
            console.error("AUTH.JS:", error);
            showAlert("No se pudo conectar con el servidor. Intenta nuevamente.", "error");
        } finally {
            setLoading(verifyCodeBtn, false);
        }
    }


    /* ==========================================================
       EVENTOS
    ========================================================== */

    form.addEventListener("submit", function (event) {
        event.preventDefault();

        if (currentStep === "email") {
            solicitarCodigo(emailInput.value.trim(), sendCodeBtn);
        } else {
            verificarCodigo(codeInput.value.trim());
        }
    });

    if (resendBtn) {
        resendBtn.addEventListener("click", function () {
            if (resendBtn.disabled) {
                return;
            }
            solicitarCodigo(verifiedEmail, resendBtn);
        });
    }

    if (changeEmailBtn) {
        changeEmailBtn.addEventListener("click", goToEmailStep);
    }

    emailInput.addEventListener("input", function () {
        if (emailInput.classList.contains("is-invalid")) {
            clearFieldError(emailInput, emailError);
        }
        hideAlert();
    });

    codeInput.addEventListener("input", function () {
        // Solo dígitos, máximo 6
        codeInput.value = codeInput.value.replace(/\D/g, "").slice(0, 6);
        if (codeInput.classList.contains("is-invalid")) {
            clearFieldError(codeInput, codeError);
        }
        hideAlert();
    });

    /* Reinicia siempre al paso 1 cuando el modal se vuelve a abrir */
    const loginModalEl = document.getElementById("loginModal");
    if (loginModalEl) {
        loginModalEl.addEventListener("hidden.bs.modal", function () {
            goToEmailStep();
            emailInput.value = "";
        });
    }

});