<!-- =====================================================
         NAVBAR
    ====================================================== -->

    <nav class="navbar navbar-expand-lg fixed-top navbar-sct" id="mainNavbar">

        <div class="container">

            <a class="navbar-brand" href="#inicio">

                <div class="brand-wrapper">

                    <div class="brand-symbol">
                        <img src="./images/logos/Logo-SCT-white.png" alt="Safety Control Tower">
                    </div>

                    <div>
                        <strong>Safety Control</strong>
                        <small>TOWER</small>
                    </div>

                </div>

            </a>

            <button
                class="navbar-toggler"
                type="button"
                data-bs-toggle="collapse"
                data-bs-target="#navbarContent"
                aria-controls="navbarContent"
                aria-expanded="false"
                aria-label="Menu">

                <i class="bi bi-list"></i>

            </button>

            <div class="collapse navbar-collapse" id="navbarContent">

                <ul class="navbar-nav mx-auto mb-2 mb-lg-0">

                    <li class="nav-item">
                        <a class="nav-link" href="#nosotros">
                            <?php echo htmlspecialchars(t('nav_nosotros'), ENT_QUOTES, 'UTF-8'); ?>
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link" href="#productos">
                            <?php echo htmlspecialchars(t('nav_productos'), ENT_QUOTES, 'UTF-8'); ?>
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link" href="#contacto">
                            <?php echo htmlspecialchars(t('nav_contacto'), ENT_QUOTES, 'UTF-8'); ?>
                        </a>
                    </li>

                </ul>

                <div class="navbar-actions d-flex align-items-center gap-2">

                    <!-- Selector de idioma: conserva la página actual, solo cambia ?lang= -->
                    <div class="dropdown lang-switcher">

                        <button
                            class="btn btn-lang dropdown-toggle"
                            type="button"
                            data-bs-toggle="dropdown"
                            aria-expanded="false">

                            <i class="bi bi-globe2"></i>
                            <?php echo strtoupper(idiomaActual()); ?>

                        </button>

                        <ul class="dropdown-menu dropdown-menu-end">

                            <?php foreach (idiomasDisponiblesConNombre() as $codigoIdioma => $nombreIdioma): ?>
                            <li>
                                <a class="dropdown-item <?php echo $codigoIdioma === idiomaActual() ? 'active' : ''; ?>"
                                   href="?lang=<?php echo $codigoIdioma; ?>#inicio">
                                    <?php echo htmlspecialchars($nombreIdioma, ENT_QUOTES, 'UTF-8'); ?>
                                </a>
                            </li>
                            <?php endforeach; ?>

                        </ul>

                    </div>

                    <button
                        class="btn btn-login"
                        data-bs-toggle="modal"
                        data-bs-target="#loginModal">

                        <?php echo htmlspecialchars(t('nav_login'), ENT_QUOTES, 'UTF-8'); ?>

                    </button>

                </div>

            </div>

        </div>

    </nav>