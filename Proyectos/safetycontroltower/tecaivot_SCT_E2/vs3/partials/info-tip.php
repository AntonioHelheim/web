<?php
/**
 * Burbuja contextual reutilizable SCT.
 * Uso: <?= sctInfoTip('Texto de ayuda', 'Más información') ?>
 */
if (!function_exists('sctInfoTip')) {
    function sctInfoTip($text, $label = 'Más información')
    {
        $textEsc = htmlspecialchars((string) $text, ENT_QUOTES, 'UTF-8');
        $labelEsc = htmlspecialchars((string) $label, ENT_QUOTES, 'UTF-8');
        $tipTranslation = static function ($key, $fallback) {
            if (!function_exists('t')) return $fallback;
            $translated = (string) t($key);
            return ($translated === '' || $translated === $key) ? $fallback : $translated;
        };
        $supportQuestion = $tipTranslation('info_tip_more_doubts', '¿Tienes más dudas?');
        $closeLabel = $tipTranslation('common_close', 'Cerrar');
        $supportLink = $tipTranslation('info_tip_contact_support', 'Contacta a soporte');
        $supportQuestionEsc = htmlspecialchars((string) $supportQuestion, ENT_QUOTES, 'UTF-8');
        $closeLabelEsc = htmlspecialchars((string) $closeLabel, ENT_QUOTES, 'UTF-8');
        $supportLinkEsc = htmlspecialchars((string) $supportLink, ENT_QUOTES, 'UTF-8');
        try {
            $id = 'sct-info-' . bin2hex(random_bytes(4));
        } catch (Throwable $e) {
            $id = 'sct-info-' . str_replace('.', '-', uniqid('', true));
        }

        return '<span class="sct-info-tip" data-sct-info-tip>'
            . '<button type="button" class="sct-info-tip__button" data-sct-info-tip-button aria-expanded="false" aria-controls="' . $id . '" aria-label="' . $labelEsc . '">?</button>'
            . '<span class="sct-info-tip__bubble" id="' . $id . '" role="dialog" aria-modal="false">'
            . '<button type="button" class="sct-info-tip__close" aria-label="' . $closeLabelEsc . '"><i class="bi bi-x-lg" aria-hidden="true"></i></button>'
            . '<span class="sct-info-tip__text">' . $textEsc . '</span>'
            . '<span class="sct-info-tip__support"><strong>' . $supportQuestionEsc . '</strong> '
            . '<a href="mailto:contacto@safetycontroltower.cl">' . $supportLinkEsc . '</a></span>'
            . '</span>'
            . '</span>';
    }
}
