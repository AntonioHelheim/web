<!-- =====================================================
         HERO
    ====================================================== -->

    <main>

        <section class="hero-section" id="inicio">

            <div class="hero-background"></div>

            <div class="container">

                <div class="row align-items-center justify-content-center gy-5">

                    <div class="col-lg-8 text-center">

                        <div class="hero-content">

                            <div class="eyebrow justify-content-center">

                                <span class="eyebrow-dot"></span>

                                <?php echo htmlspecialchars(t('hero_eyebrow'), ENT_QUOTES, 'UTF-8'); ?>

                            </div>

                            <h1>

                                <?php echo htmlspecialchars(t('hero_title_1'), ENT_QUOTES, 'UTF-8'); ?>

                                <span><?php echo htmlspecialchars(t('hero_title_span'), ENT_QUOTES, 'UTF-8'); ?></span>

                            </h1>

                            <p class="hero-text">

                                <?php echo htmlspecialchars(t('hero_text'), ENT_QUOTES, 'UTF-8'); ?>

                            </p>

                            <div class="hero-buttons justify-content-center">

                                <a
                                    href="#productos"
                                    class="btn btn-primary-custom btn-lg">

                                    <?php echo htmlspecialchars(t('hero_cta_productos'), ENT_QUOTES, 'UTF-8'); ?>

                                    <i class="bi bi-arrow-right"></i>

                                </a>

                                <a
                                    href="#contacto"
                                    class="btn btn-outline-custom btn-lg">

                                    <?php echo htmlspecialchars(t('hero_cta_contacto'), ENT_QUOTES, 'UTF-8'); ?>

                                </a>

                            </div>

                            <div class="hero-trust justify-content-center">

                                <div class="trust-item">
                                    <i class="bi bi-check-circle-fill"></i>
                                    <?php echo htmlspecialchars(t('hero_trust_1'), ENT_QUOTES, 'UTF-8'); ?>
                                </div>

                                <div class="trust-item">
                                    <i class="bi bi-check-circle-fill"></i>
                                    <?php echo htmlspecialchars(t('hero_trust_2'), ENT_QUOTES, 'UTF-8'); ?>
                                </div>

                                <div class="trust-item">
                                    <i class="bi bi-check-circle-fill"></i>
                                    <?php echo htmlspecialchars(t('hero_trust_3'), ENT_QUOTES, 'UTF-8'); ?>
                                </div>

                            </div>

                        </div>

                    </div>

                </div>

            </div>

        </section>