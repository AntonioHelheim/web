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


                <!-- Grilla de productos: hoy solo SCT, pensada para sumar más adelante.
                     Se usa una franja destacada (no la tarjeta de grilla angosta) para
                     que un solo producto no se vea perdido en pantallas anchas. -->
                <div class="row justify-content-center mt-4">

                    <div class="col-12">

                        <div class="product-feature">

                            <div class="product-feature-icon">
                                <img src="./images/logos/Logo-SCT.png" alt="Safety Control Tower">
                            </div>

                            <div class="product-feature-body">

                                <h3><?php echo htmlspecialchars(t('product_sct_name'), ENT_QUOTES, 'UTF-8'); ?></h3>

                                <p class="product-feature-tagline">
                                    <?php echo htmlspecialchars(t('product_sct_tagline'), ENT_QUOTES, 'UTF-8'); ?>
                                </p>

                                <p class="product-feature-desc">
                                    <?php echo htmlspecialchars(t('product_sct_description'), ENT_QUOTES, 'UTF-8'); ?>
                                </p>

                            </div>

                            <div class="product-feature-cta">

                                <a href="#" data-bs-toggle="modal" data-bs-target="#loginModal" class="btn btn-primary-custom">
                                    <?php echo htmlspecialchars(t('product_sct_cta'), ENT_QUOTES, 'UTF-8'); ?>
                                    <i class="bi bi-arrow-right"></i>
                                </a>

                            </div>

                        </div>

                    </div>

                </div>

            </div>

        </section>