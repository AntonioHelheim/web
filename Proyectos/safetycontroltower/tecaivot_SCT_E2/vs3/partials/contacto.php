<!-- =====================================================
             CONTACTO
        ====================================================== -->

        <section class="contact-section section-padding" id="contacto">

            <div class="container">

                <div class="row g-5">

                    <div class="col-lg-5">

                        <span class="section-label">
                            <?php echo htmlspecialchars(t('contacto_label'), ENT_QUOTES, 'UTF-8'); ?>
                        </span>

                        <h2 class="section-title">

                            <?php echo htmlspecialchars(t('contacto_title_1'), ENT_QUOTES, 'UTF-8'); ?>
                            <span><?php echo htmlspecialchars(t('contacto_title_span'), ENT_QUOTES, 'UTF-8'); ?></span>

                        </h2>

                        <p class="section-description">

                            <?php echo htmlspecialchars(t('contacto_text'), ENT_QUOTES, 'UTF-8'); ?>

                        </p>

                        <div class="contact-info">

                            <div class="contact-info-item">

                                <div class="contact-icon">
                                    <i class="bi bi-envelope"></i>
                                </div>

                                <div>
                                    <small><?php echo htmlspecialchars(t('contacto_email_label'), ENT_QUOTES, 'UTF-8'); ?></small>
                                    <strong>contacto@safetycontroltower.cl</strong>
                                </div>

                            </div>


                            <div class="contact-info-item">

                                <div class="contact-icon">
                                    <i class="bi bi-shield-check"></i>
                                </div>

                                <div>
                                    <small><?php echo htmlspecialchars(t('contacto_empresa_label'), ENT_QUOTES, 'UTF-8'); ?></small>
                                    <strong>Safety Control Tower</strong>
                                </div>

                            </div>

                        </div>

                    </div>


                    <div class="col-lg-7">

                        <div class="contact-form-card">

                            <form id="contactForm">

                                <div class="row g-3">

                                    <div class="col-md-6">

                                        <label for="name">
                                            <?php echo htmlspecialchars(t('form_name_label'), ENT_QUOTES, 'UTF-8'); ?>
                                        </label>

                                        <input
                                            type="text"
                                            id="name"
                                            name="name"
                                            placeholder="<?php echo htmlspecialchars(t('form_name_placeholder'), ENT_QUOTES, 'UTF-8'); ?>"
                                            required>

                                    </div>


                                    <div class="col-md-6">

                                        <label for="company">
                                            <?php echo htmlspecialchars(t('form_company_label'), ENT_QUOTES, 'UTF-8'); ?>
                                        </label>

                                        <input
                                            type="text"
                                            id="company"
                                            name="company"
                                            placeholder="<?php echo htmlspecialchars(t('form_company_placeholder'), ENT_QUOTES, 'UTF-8'); ?>">

                                    </div>


                                    <div class="col-md-6">

                                        <label for="email">
                                            <?php echo htmlspecialchars(t('form_email_label'), ENT_QUOTES, 'UTF-8'); ?>
                                        </label>

                                        <input
                                            type="email"
                                            id="email"
                                            name="email"
                                            placeholder="<?php echo htmlspecialchars(t('form_email_placeholder'), ENT_QUOTES, 'UTF-8'); ?>"
                                            required>

                                    </div>


                                    <div class="col-md-6">

                                        <label for="phone">
                                            <?php echo htmlspecialchars(t('form_phone_label'), ENT_QUOTES, 'UTF-8'); ?>
                                        </label>

                                        <input
                                            type="tel"
                                            id="phone"
                                            name="phone"
                                            placeholder="<?php echo htmlspecialchars(t('form_phone_placeholder'), ENT_QUOTES, 'UTF-8'); ?>">

                                    </div>


                                    <div class="col-12">

                                        <label for="message">
                                            <?php echo htmlspecialchars(t('form_message_label'), ENT_QUOTES, 'UTF-8'); ?>
                                        </label>

                                        <textarea
                                            id="message"
                                            name="message"
                                            rows="4"
                                            placeholder="<?php echo htmlspecialchars(t('form_message_placeholder'), ENT_QUOTES, 'UTF-8'); ?>"></textarea>

                                    </div>


                                    <div class="col-12">

                                        <div class="form-check">

                                            <input
                                                class="form-check-input"
                                                type="checkbox"
                                                id="privacy"
                                                required>

                                            <label
                                                class="form-check-label"
                                                for="privacy">

                                                <?php echo htmlspecialchars(t('form_privacy_text'), ENT_QUOTES, 'UTF-8'); ?>

                                            </label>

                                        </div>

                                    </div>


                                    <div class="col-12">

                                        <button
                                            type="submit"
                                            class="btn btn-primary-custom btn-lg w-100">

                                            <?php echo htmlspecialchars(t('form_submit'), ENT_QUOTES, 'UTF-8'); ?>

                                            <i class="bi bi-arrow-right"></i>

                                        </button>

                                    </div>

                                </div>

                            </form>

                        </div>

                    </div>

                </div>

            </div>

        </section>

    </main>