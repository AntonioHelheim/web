/**
 * ==========================================================
 * AUTH.JS
 * SAFETY CONTROL TOWER
 *
 * Responsabilidades:
 * - Login
 * - Validación del formulario
 * - CSRF
 * - Mostrar / ocultar contraseña
 * - Estados del botón
 * - Manejo de errores
 * ==========================================================
 */

document.addEventListener("DOMContentLoaded", function () {

    const form = document.getElementById("loginForm");

    if (!form) {
        return;
    }


    /* ==========================================================
       ELEMENTOS DEL FORMULARIO
    ========================================================== */

    const emailInput =
        document.getElementById("loginEmail");

    const passwordInput =
        document.getElementById("loginPassword");

    const emailError =
        document.getElementById("loginEmailError");

    const passwordError =
        document.getElementById("loginPasswordError");

    const togglePassword =
        document.getElementById("togglePassword");

    const submitBtn =
        document.getElementById("loginSubmit");

    const alertBox =
        document.getElementById("loginAlert");

    const rememberInput =
        document.getElementById("remember");

    const csrfInput =
        form.querySelector('[name="csrf_token"]');


    /* ==========================================================
       VALIDACIÓN DE ELEMENTOS
    ========================================================== */

    if (
        !emailInput ||
        !passwordInput ||
        !submitBtn ||
        !alertBox
    ) {
        console.error(
            "AUTH.JS: Faltan elementos requeridos del formulario de login."
        );

        return;
    }


    /* ==========================================================
       MOSTRAR / OCULTAR CONTRASEÑA
    ========================================================== */

    if (togglePassword) {

        togglePassword.addEventListener(
            "click",
            function () {

                const isPassword =
                    passwordInput.type === "password";

                passwordInput.type =
                    isPassword ? "text" : "password";


                /*
                 * Actualizar estado accesible
                 */

                togglePassword.setAttribute(
                    "aria-pressed",
                    String(isPassword)
                );

                togglePassword.setAttribute(
                    "aria-label",
                    isPassword
                        ? "Ocultar contraseña"
                        : "Mostrar contraseña"
                );


                /*
                 * Actualizar icono
                 */

                const icon =
                    togglePassword.querySelector("i");

                if (icon) {

                    icon.classList.toggle(
                        "bi-eye",
                        !isPassword
                    );

                    icon.classList.toggle(
                        "bi-eye-slash",
                        isPassword
                    );

                }

            }
        );

    }


    /* ==========================================================
       MOSTRAR / OCULTAR ERROR DE CAMPO
    ========================================================== */

    function showFieldError(
        input,
        errorElement,
        show
    ) {

        input.classList.toggle(
            "is-invalid",
            show
        );

        if (errorElement) {

            errorElement.classList.toggle(
                "d-none",
                !show
            );

        }

    }


    /* ==========================================================
       VALIDAR FORMULARIO
    ========================================================== */

    function validateForm() {

        let valid = true;


        /*
         * Email
         */

        const emailValid =
            emailInput.checkValidity();

        showFieldError(
            emailInput,
            emailError,
            !emailValid
        );

        if (!emailValid) {
            valid = false;
        }


        /*
         * Password
         */

        const passwordValid =
            passwordInput.checkValidity();

        showFieldError(
            passwordInput,
            passwordError,
            !passwordValid
        );

        if (!passwordValid) {
            valid = false;
        }


        return valid;

    }


    /* ==========================================================
       ESTADO DE CARGA
    ========================================================== */

    function setLoading(isLoading) {

        submitBtn.disabled =
            isLoading;


        const label =
            submitBtn.querySelector(".btn-label");

        const icon =
            submitBtn.querySelector(".btn-icon");

        const spinner =
            submitBtn.querySelector(".spinner-border");


        if (label) {

            label.classList.toggle(
                "d-none",
                isLoading
            );

        }


        if (icon) {

            icon.classList.toggle(
                "d-none",
                isLoading
            );

        }


        if (spinner) {

            spinner.classList.toggle(
                "d-none",
                !isLoading
            );

        }

    }


    /* ==========================================================
       MOSTRAR ALERTA
    ========================================================== */

    function showAlert(
        message,
        variant = "error"
    ) {

        alertBox.textContent =
            message;

        alertBox.classList.remove(
            "d-none",
            "login-alert--error",
            "login-alert--warning"
        );

        alertBox.classList.add(
            "login-alert--" + variant
        );

    }


    /* ==========================================================
       OCULTAR ALERTA
    ========================================================== */

    function hideAlert() {

        alertBox.classList.add(
            "d-none"
        );

        alertBox.classList.remove(
            "login-alert--error",
            "login-alert--warning"
        );

        alertBox.textContent = "";

    }


    /* ==========================================================
       LIMPIAR ERROR AL ESCRIBIR
    ========================================================== */

    [
        emailInput,
        passwordInput
    ].forEach(function (input) {

        input.addEventListener(
            "input",
            function () {

                if (
                    input.classList.contains(
                        "is-invalid"
                    )
                ) {

                    validateForm();

                }

                hideAlert();

            }
        );

    });


    /* ==========================================================
       SUBMIT LOGIN
    ========================================================== */

    form.addEventListener(
        "submit",
        async function (event) {

            event.preventDefault();


            /*
             * Limpiar mensajes anteriores
             */

            hideAlert();


            /*
             * Validar formulario
             */

            if (!validateForm()) {

                return;

            }


            /*
             * Activar estado de carga
             */

            setLoading(true);


            try {

                /*
                 * Construir payload.
                 *
                 * IMPORTANTE:
                 * El CSRF se envía explícitamente.
                 */

                const payload = {

                    email:
                        emailInput.value.trim(),

                    password:
                        passwordInput.value,

                    remember:
                        !!(
                            rememberInput &&
                            rememberInput.checked
                        ),

                    csrf_token:
                        csrfInput
                            ? csrfInput.value
                            : ""

                };


                /*
                 * Enviar solicitud
                 */

                const response =
                    await fetch(
                        form.action,
                        {
                            method: "POST",

                            headers: {
                                "Content-Type":
                                    "application/json",

                                "X-Requested-With":
                                    "XMLHttpRequest"
                            },

                            body:
                                JSON.stringify(
                                    payload
                                )
                        }
                    );


                /*
                 * Intentar interpretar JSON
                 */

                let data;

                try {

                    data =
                        await response.json();

                } catch (jsonError) {

                    throw new Error(
                        "Respuesta inválida del servidor."
                    );

                }


                /* ==================================================
                   LOGIN CORRECTO
                ================================================== */

                if (data.success) {

                    window.location.href =
                        data.redirect ||
                        "bienvenida.php";

                    return;

                }


                /* ==================================================
                   RATE LIMIT
                ================================================== */

                if (response.status === 429) {

                    showAlert(
                        data.message ||
                        "Demasiados intentos fallidos. Intenta más tarde.",
                        "warning"
                    );


                    submitBtn.disabled =
                        true;


                    setTimeout(
                        function () {

                            submitBtn.disabled =
                                false;

                        },
                        30000
                    );


                    return;

                }


                /* ==================================================
                   LOGIN RECHAZADO
                ================================================== */

                showAlert(
                    data.message ||
                    "Correo o contraseña incorrectos.",
                    "error"
                );

            } catch (error) {

                console.error(
                    "AUTH.JS:",
                    error
                );


                showAlert(
                    "No se pudo conectar con el servidor. Intenta nuevamente.",
                    "error"
                );

            } finally {

                setLoading(false);

            }

        }
    );

});