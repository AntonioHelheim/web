<!-- =====================================================
             PRODUCTOS
        ====================================================== -->

        <section class="features-section section-padding" id="productos">

            <div class="container">

                <div class="row justify-content-center text-center">

                    <div class="col-lg-8">

                        <span class="section-label">
                            <?php echo htmlspecialchars(t('productos_label'), ENT_QUOTES, 'UTF-8'); ?>
                        </span>

                        <h2 class="section-title">

                            <?php echo htmlspecialchars(t('productos_title_1'), ENT_QUOTES, 'UTF-8'); ?>

                            <span><?php echo htmlspecialchars(t('productos_title_span'), ENT_QUOTES, 'UTF-8'); ?></span>

                        </h2>

                        <p class="section-description">

                            <?php echo htmlspecialchars(t('productos_text'), ENT_QUOTES, 'UTF-8'); ?>

                        </p>

                    </div>

                </div>


                <!-- Grilla de productos: hoy solo SCT, pensada para sumar más adelante -->
                <div class="row g-4 mt-4 justify-content-center">

                    <div class="col-md-6 col-lg-5">

                        <div class="feature-card feature-card-highlight">

                            <div class="feature-icon">
                                <img src="./images/logos/Logo-SCT.svg" alt="Safety Control Tower" style="width: 36px; height: 36px;">
                            </div>

                            <h3><?php echo htmlspecialchars(t('product_sct_name'), ENT_QUOTES, 'UTF-8'); ?></h3>

                            <p>
                                <strong><?php echo htmlspecialchars(t('product_sct_tagline'), ENT_QUOTES, 'UTF-8'); ?></strong><br>
                                <?php echo htmlspecialchars(t('product_sct_description'), ENT_QUOTES, 'UTF-8'); ?>
                            </p>

                            <a href="#" data-bs-toggle="modal" data-bs-target="#loginModal">
                                <?php echo htmlspecialchars(t('product_sct_cta'), ENT_QUOTES, 'UTF-8'); ?>
                                <i class="bi bi-arrow-right"></i>
                            </a>

                        </div>

                    </div>

                </div>

            </div>

        </section>