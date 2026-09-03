<!-- =====================================================
             NOSOTROS
        ====================================================== -->

        <section class="about-section section-padding" id="nosotros">

            <div class="container">

                <div class="row justify-content-center text-center">

                    <div class="col-lg-8">

                        <span class="section-label">
                            <?php echo htmlspecialchars(t('nosotros_label'), ENT_QUOTES, 'UTF-8'); ?>
                        </span>

                        <h2 class="section-title">

                            <?php echo htmlspecialchars(t('nosotros_title_1'), ENT_QUOTES, 'UTF-8'); ?>

                            <span><?php echo htmlspecialchars(t('nosotros_title_span'), ENT_QUOTES, 'UTF-8'); ?></span>

                        </h2>

                        <p class="section-description">

                            <?php echo htmlspecialchars(t('nosotros_text'), ENT_QUOTES, 'UTF-8'); ?>

                        </p>

                    </div>

                </div>


                <!-- Reutiliza el componente .problem-card ya definido en style.css
                     para mantener consistencia visual con el resto del sitio -->
                <div class="row g-4 mt-4">

                    <div class="col-md-4">

                        <div class="problem-card">

                            <div class="problem-icon">
                                <i class="bi bi-hand-thumbs-up"></i>
                            </div>

                            <h3><?php echo htmlspecialchars(t('nosotros_v1_title'), ENT_QUOTES, 'UTF-8'); ?></h3>

                            <p><?php echo htmlspecialchars(t('nosotros_v1_text'), ENT_QUOTES, 'UTF-8'); ?></p>

                        </div>

                    </div>

                    <div class="col-md-4">

                        <div class="problem-card">

                            <div class="problem-icon">
                                <i class="bi bi-patch-check"></i>
                            </div>

                            <h3><?php echo htmlspecialchars(t('nosotros_v2_title'), ENT_QUOTES, 'UTF-8'); ?></h3>

                            <p><?php echo htmlspecialchars(t('nosotros_v2_text'), ENT_QUOTES, 'UTF-8'); ?></p>

                        </div>

                    </div>

                    <div class="col-md-4">

                        <div class="problem-card">

                            <div class="problem-icon">
                                <i class="bi bi-people"></i>
                            </div>

                            <h3><?php echo htmlspecialchars(t('nosotros_v3_title'), ENT_QUOTES, 'UTF-8'); ?></h3>

                            <p><?php echo htmlspecialchars(t('nosotros_v3_text'), ENT_QUOTES, 'UTF-8'); ?></p>

                        </div>

                    </div>

                </div>

            </div>

        </section>