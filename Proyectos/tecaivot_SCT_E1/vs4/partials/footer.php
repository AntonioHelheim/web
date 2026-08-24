<!-- =====================================================
         FOOTER
    ====================================================== -->

<footer class="footer">

    <div class="container">

        <div class="row gy-4">

            <div class="col-lg-5">

                <div class="brand-wrapper footer-brand">

                    <img
                        src="images/logos/Logo_Tecaivot_principal-white.svg"
                        alt="Tecaivot"
                        class="brand-logo">

                </div>

                <p class="footer-description">

                    <?php echo htmlspecialchars(t('footer_description'), ENT_QUOTES, 'UTF-8'); ?>

                </p>

            </div>


                <div class="col-6 col-lg-2">

                    <h6><?php echo htmlspecialchars(t('footer_col_empresa'), ENT_QUOTES, 'UTF-8'); ?></h6>

                    <ul>

                        <li>
                            <a href="#nosotros">
                                <?php echo htmlspecialchars(t('nav_nosotros'), ENT_QUOTES, 'UTF-8'); ?>
                            </a>
                        </li>

                        <li>
                            <a href="#contacto">
                                <?php echo htmlspecialchars(t('nav_contacto'), ENT_QUOTES, 'UTF-8'); ?>
                            </a>
                        </li>

                    </ul>

                </div>


                <div class="col-6 col-lg-2">

                    <h6><?php echo htmlspecialchars(t('footer_col_productos'), ENT_QUOTES, 'UTF-8'); ?></h6>

                    <ul>

                        <li>
                            <a href="#productos">
                                <?php echo htmlspecialchars(t('product_sct_name'), ENT_QUOTES, 'UTF-8'); ?>
                            </a>
                        </li>

                    </ul>

                </div>


                <div class="col-lg-3">

                    <h6><?php echo htmlspecialchars(t('footer_login_prompt'), ENT_QUOTES, 'UTF-8'); ?></h6>

                    <p>
                        <?php echo htmlspecialchars(t('footer_login_text'), ENT_QUOTES, 'UTF-8'); ?>
                    </p>

                    <button
                        class="btn btn-footer-login"
                        data-bs-toggle="modal"
                        data-bs-target="#loginModal">

                        <?php echo htmlspecialchars(t('footer_login_button'), ENT_QUOTES, 'UTF-8'); ?>

                        <i class="bi bi-box-arrow-in-right"></i>

                    </button>

                </div>

            </div>


            <hr>


            <div class="footer-bottom">

                <span>
                    © <span id="currentYear"></span> Tecaivot.
                    <?php echo htmlspecialchars(t('footer_rights'), ENT_QUOTES, 'UTF-8'); ?>
                </span>

            </div>

        </div>

    </footer>