<?php
/**
 * ==========================================================
 * FOOTER INTERNO SCT — P3
 * ==========================================================
 * Pie institucional compartido para pantallas autenticadas.
 * Versión simple y profesional.
 * ==========================================================
 */

$sctFooterSponsorLabel = function_exists('t') ? t('footer_project_sponsor') : 'Sponsor del proyecto';
$sctFooterDeveloperLabel = function_exists('t') ? t('footer_developer_company') : 'Empresa desarrolladora';
$sctFooterSupportQuestion = function_exists('t') ? t('welcome_support_question') : '¿Necesitas ayuda?';
$sctFooterSupportText = function_exists('t') ? t('welcome_support_text') : 'Podemos orientarte en el uso de la plataforma.';
$sctFooterSupportLink = function_exists('t') ? t('welcome_support_link') : 'Contactar soporte';
?>
<footer class="sct-app-footer sct-partial-app-footer" aria-label="Safety Control Tower">
    <div class="sct-app-footer__inner">
        <div class="sct-app-footer__primary">
            <div class="sct-app-footer__brand">
                <span class="sct-app-footer__mark" aria-hidden="true"><i class="bi bi-shield-check"></i></span>
                <strong>Safety Control Tower</strong>
            </div>

            <div class="sct-app-footer__support">
                <span class="sct-app-footer__support-icon" aria-hidden="true"><i class="bi bi-life-preserver"></i></span>
                <span class="sct-app-footer__support-copy">
                    <strong><?= htmlspecialchars($sctFooterSupportQuestion, ENT_QUOTES, 'UTF-8') ?></strong>
                    <small><?= htmlspecialchars($sctFooterSupportText, ENT_QUOTES, 'UTF-8') ?></small>
                </span>
                <a href="mailto:contacto@safetycontroltower.cl" class="sct-app-footer__support-link">
                    <?= htmlspecialchars($sctFooterSupportLink, ENT_QUOTES, 'UTF-8') ?>
                    <i class="bi bi-arrow-right" aria-hidden="true"></i>
                </a>
            </div>
        </div>

        <div class="sct-app-footer__meta">
            <div class="sct-app-footer__credits">
                <span class="sct-app-footer__credit">
                    <span><?= htmlspecialchars($sctFooterSponsorLabel, ENT_QUOTES, 'UTF-8') ?>:</span>
                    <a href="https://tecaivot.cl/" target="_blank" rel="noopener noreferrer">Tecaivot.cl</a>
                </span>

                <span class="sct-app-footer__separator" aria-hidden="true">•</span>

                <span class="sct-app-footer__credit">
                    <span><?= htmlspecialchars($sctFooterDeveloperLabel, ENT_QUOTES, 'UTF-8') ?>:</span>
                    <a href="https://helheim.cl/" target="_blank" rel="noopener noreferrer">Helheim.cl</a>
                </span>
            </div>
        </div>
    </div>
</footer>

<?php $sctFooterBasePath = isset($sctNavbarBasePath) ? (string) $sctNavbarBasePath : './'; ?>
<script src="<?= htmlspecialchars($sctFooterBasePath, ENT_QUOTES, 'UTF-8') ?>js/sct-collapsible.js?v=20260923-p74"></script>
