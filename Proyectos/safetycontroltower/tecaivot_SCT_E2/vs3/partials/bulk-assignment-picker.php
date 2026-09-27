<?php
/**
 * Selector múltiple accesible para asignaciones SCT.
 *
 * El <select multiple> fuente permanece como estado canónico para el JS del
 * módulo. Este partial sólo añade una interfaz de búsqueda/checkboxes encima,
 * sin cambiar las APIs ni el modelo de datos.
 */
if (!function_exists('sctBulkAssignmentPicker')) {
    function sctBulkAssignmentPicker($sourceId, $options = [])
    {
        $search = isset($options['search']) ? (string) $options['search'] : (function_exists('t') ? t('activity_builder_search_people') : 'Buscar personas');
        $selectAll = isset($options['select_all']) ? (string) $options['select_all'] : (function_exists('t') ? t('activity_builder_select_all') : 'Seleccionar visibles');
        $clear = isset($options['clear']) ? (string) $options['clear'] : (function_exists('t') ? t('activity_builder_clear') : 'Limpiar');
        $selected = isset($options['selected']) ? (string) $options['selected'] : (function_exists('t') ? t('activity_builder_selected') : 'seleccionados');
        $empty = isset($options['empty']) ? (string) $options['empty'] : (function_exists('t') ? t('activity_builder_no_people') : 'No hay personas disponibles.');
        $hint = isset($options['hint']) ? (string) $options['hint'] : (function_exists('t') ? t('activity_builder_bulk_hint') : 'Puedes seleccionar una o varias personas.');
        $label = isset($options['label']) ? (string) $options['label'] : '';
        $esc = static function ($value) {
            return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
        };
        ?>
        <div class="sct-bulk-picker"
             data-sct-bulk-picker
             data-source-id="<?= $esc($sourceId) ?>"
             data-selected-label="<?= $esc($selected) ?>"
             data-empty-label="<?= $esc($empty) ?>">
            <?php if ($label !== ''): ?>
                <div class="sct-bulk-picker__label"><?= $esc($label) ?></div>
            <?php endif; ?>
            <div class="sct-bulk-picker__toolbar">
                <label class="sct-bulk-picker__search">
                    <i class="bi bi-search" aria-hidden="true"></i>
                    <span class="visually-hidden"><?= $esc($search) ?></span>
                    <input type="search" data-bulk-search autocomplete="off" placeholder="<?= $esc($search) ?>">
                </label>
                <button type="button" class="btn btn-outline-custom btn-sm" data-bulk-select-all><?= $esc($selectAll) ?></button>
                <button type="button" class="btn btn-outline-custom btn-sm" data-bulk-clear><?= $esc($clear) ?></button>
            </div>
            <div class="sct-bulk-picker__summary" aria-live="polite">
                <span data-bulk-count>0 <?= $esc($selected) ?></span>
                <small><?= $esc($hint) ?></small>
            </div>
            <div class="sct-bulk-picker__list" data-bulk-list role="group" aria-label="<?= $esc($label !== '' ? $label : $search) ?>"></div>
        </div>
        <?php
    }
}
