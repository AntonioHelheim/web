<?php
if (!function_exists('sctActivityBuilderFlow')) {
    function sctActivityBuilderFlow(array $anchors = [], array $overrides = [])
    {
        $steps = [
            ['definition', 'activity_builder_step_definition', 'activity_builder_step_definition_text'],
            ['content', 'activity_builder_step_content', 'activity_builder_step_content_text'],
            ['assignment', 'activity_builder_step_assignment', 'activity_builder_step_assignment_text'],
            ['tracking', 'activity_builder_step_tracking', 'activity_builder_step_tracking_text'],
        ];
        ?>
        <nav class="sct-builder-flow" aria-label="<?= htmlspecialchars(t('activity_builder_title'), ENT_QUOTES, 'UTF-8') ?>">
            <?php foreach ($steps as $i => $step): ?>
                <?php
                $key = $step[0];
                $href = isset($anchors[$key]) && $anchors[$key] !== '' ? '#' . ltrim((string) $anchors[$key], '#') : '';
                $tag = $href !== '' ? 'a' : 'div';
                $labelKey = isset($overrides[$key]['label']) ? (string) $overrides[$key]['label'] : $step[1];
                $textKey = isset($overrides[$key]['text']) ? (string) $overrides[$key]['text'] : $step[2];
                ?>
                <<?= $tag ?> class="sct-builder-flow__step"<?= $href !== '' ? ' href="' . htmlspecialchars($href, ENT_QUOTES, 'UTF-8') . '"' : '' ?>>
                    <span class="sct-builder-flow__index"><?= $i + 1 ?></span>
                    <span class="sct-builder-flow__copy">
                        <strong><?= htmlspecialchars(t($labelKey), ENT_QUOTES, 'UTF-8') ?></strong>
                        <small><?= htmlspecialchars(t($textKey), ENT_QUOTES, 'UTF-8') ?></small>
                    </span>
                </<?= $tag ?>>
            <?php endforeach; ?>
        </nav>
        <?php
    }
}
