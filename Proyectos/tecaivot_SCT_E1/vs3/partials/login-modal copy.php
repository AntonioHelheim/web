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

                    <h2 id="loginModalLabel">
                        Bienvenido de vuelta
                    </h2>

                    <p>
                        Ingresa a tu cuenta para acceder a tu plataforma.
                    </p>

                    <form id="loginForm">

                        <div class="mb-3">

                            <label for="loginEmail">
                                Correo electrónico
                            </label>

                            <input
                                type="email"
                                class="form-control"
                                id="loginEmail"
                                placeholder="correo@empresa.cl"
                                autocomplete="email"
                                required>

                        </div>


                        <div class="mb-3">

                            <label for="loginPassword">
                                Contraseña
                            </label>

                            <div class="password-wrapper">

                                <input
                                    type="password"
                                    class="form-control"
                                    id="loginPassword"
                                    placeholder="••••••••"
                                    autocomplete="current-password"
                                    required>

                                <button
                                    type="button"
                                    id="togglePassword"
                                    class="password-toggle"
                                    aria-label="Mostrar u ocultar contraseña">

                                    <i class="bi bi-eye"></i>

                                </button>

                            </div>

                        </div>


                        <div class="d-flex justify-content-between mb-4">

                            <div class="form-check">

                                <input
                                    class="form-check-input"
                                    type="checkbox"
                                    id="remember">

                                <label
                                    class="form-check-label"
                                    for="remember">

                                    Recordarme

                                </label>

                            </div>

                            <a href="#">
                                ¿Olvidaste tu contraseña?
                            </a>

                        </div>


                        <button
                            type="submit"
                            class="btn btn-primary-custom w-100">

                            Iniciar sesión

                            <i class="bi bi-arrow-right"></i>

                        </button>

                    </form>

                    <div class="login-help">

                        ¿Aún no eres cliente?

                        <a href="#contacto" data-bs-dismiss="modal">
                            Solicita información
                        </a>

                    </div>

                </div>

            </div>

        </div>

    </div>


