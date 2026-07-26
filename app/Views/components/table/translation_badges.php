<?php

/**
 * @var array<int|string, array<string, mixed>> $languages
 * @var list<string>|null $requiredFields Array of field names required for complete status
 */

$requiredFields = $requiredFields ?? ['title', 'slug'];
$languageBadges = [];

foreach ($languages ?? [] as $key => $language) {
    $langArr  = is_array($language) ? $language : ['id' => $key, 'name' => (string) $language];
    $langId   = isset($langArr['id']) && is_numeric($langArr['id']) ? (int) $langArr['id'] : (is_numeric($key) ? (int) $key : 0);

    if ($langId <= 0) {
        continue;
    }

    $langCode  = (string) ($langArr['code'] ?? (is_string($key) && ! is_numeric($key) ? $key : $langId));

    $languageBadges[] = [
        'id'   => $langId,
        'name' => (string) ($langArr['name'] ?? $langArr['label'] ?? $langCode),
        'code' => $langCode,
        'is_default' => ! empty($langArr['is_default']),
    ];
}

$msgMissing    = (string) (lang('Pages.translation_missing') ?: 'Missing translation');
$msgComplete   = (string) (lang('Pages.translation_complete') ?: 'Complete');
$msgIncomplete = (string) (lang('Pages.translation_incomplete') ?: 'Incomplete');
?>

<div class="flex items-center gap-1.5">
    <?php foreach ($languageBadges as $lang): ?>
        <?php
            $langId   = (int) $lang['id'];
        $isDefault = ! empty($lang['is_default']);
        $reqsJson = json_encode($requiredFields, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
        $defaultArg = $isDefault ? 'true' : 'false';
        ?>
        <span 
            class="inline-flex items-center justify-center font-bold px-1.5 py-0.5 rounded text-[10px] border transition cursor-pointer"
            :class="{
                'bg-green-50 text-green-700 border-green-200 hover:bg-green-100': translationStatus(row, <?= $langId ?>, <?= esc($reqsJson, 'attr') ?>, <?= $defaultArg ?>) === 'complete',
                'bg-yellow-50 text-yellow-700 border-yellow-200 hover:bg-yellow-100': translationStatus(row, <?= $langId ?>, <?= esc($reqsJson, 'attr') ?>, <?= $defaultArg ?>) === 'incomplete',
                'bg-red-50 text-red-700 border-red-200 hover:bg-red-100': translationStatus(row, <?= $langId ?>, <?= esc($reqsJson, 'attr') ?>, <?= $defaultArg ?>) === 'missing'
            }"
            :title="'<?= esc($lang['name']) ?>: ' + (translationStatus(row, <?= $langId ?>, <?= esc($reqsJson, 'attr') ?>, <?= $defaultArg ?>) === 'complete' ? '<?= esc($msgComplete, 'js') ?>' : translationStatus(row, <?= $langId ?>, <?= esc($reqsJson, 'attr') ?>, <?= $defaultArg ?>) === 'incomplete' ? '<?= esc($msgIncomplete, 'js') ?>' : '<?= esc($msgMissing, 'js') ?>') + '. Click to edit translation.'"
            @click="window.location.href = translationEditUrl(row.id, <?= $langId ?>)"
        >
            <?= esc(strtoupper($lang['code'])) ?>
        </span>
    <?php endforeach; ?>
</div>
