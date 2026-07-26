<?php

use App\Modules\Cms\Support\TranslationStatus;

$sourceFields = $sourceFields ?? [];
$languages = $languages ?? [];
$translations = $translations ?? [];
$requiredFields = $requiredFields ?? [];
$sourceUpdatedAt = $sourceUpdatedAt ?? null;
$languageStates = [];
$translationByLanguage = [];
foreach ($translations as $translation) {
    $translationByLanguage[(int) ($translation['language_id'] ?? 0)] = $translation;
}
foreach ($languages as $language) {
    $language['_source'] = $sourceFields;
    $languageStates[(int) ($language['id'] ?? 0)] = TranslationStatus::evaluate($language, $translations, $requiredFields, $sourceUpdatedAt);
}
$actionableLanguages = array_values(array_filter($languages, static fn (array $language): bool => ($languageStates[(int) ($language['id'] ?? 0)]['status'] ?? 'missing') !== 'complete'));
?>
<?php if ($actionableLanguages !== []): ?>
    <section class="mt-6 border-t border-red-100 pt-6" aria-labelledby="translation-status-title">
        <div class="flex items-center justify-between gap-3">
            <h4 id="translation-status-title" class="text-sm font-semibold text-red-800"><?= esc(lang('Translations.missing_incomplete')) ?></h4>
            <span class="rounded-full bg-red-100 px-2 py-0.5 text-xs font-semibold text-red-700"><?= count($actionableLanguages) ?></span>
        </div>
        <p class="mt-1 text-xs text-gray-500"><?= esc(lang('Translations.translation_action_required')) ?></p>
        <ul class="mt-3 flex flex-wrap gap-2">
            <?php foreach ($actionableLanguages as $language):
                $languageId = (int) ($language['id'] ?? 0);
                $languageCode = strtoupper((string) ($language['code'] ?? ('#' . $languageId)));
                $editUrl = \App\Modules\Cms\Support\TranslationStatus::editUrl($editUrlTemplate, $languageId);
                $stateData = $languageStates[$languageId] ?? ['status' => 'missing', 'missing_fields' => $requiredFields];
                $state = $stateData['status'];
                $missing = $stateData['missing_fields'];
                $missingLabel = $missing !== [] ? ' (' . implode(', ', array_map(static fn (string $field): string => (string) lang('Translations.field_' . $field), $missing)) . ')' : '';
                $translationUpdatedAt = $translationByLanguage[$languageId]['updated_at'] ?? null;
                ?>
                <li><a href="<?= esc($editUrl) ?>" class="inline-flex flex-wrap items-center gap-2 rounded-lg border <?= \App\Modules\Cms\Support\TranslationStatus::badgeClasses($state, 'action') ?> px-3 py-2 text-xs font-semibold"><span><?= esc($languageCode) ?></span><span><?= esc(lang('Translations.status_' . $state) . $missingLabel) ?></span><?php if ($sourceUpdatedAt !== null): ?><time datetime="<?= esc((string) $sourceUpdatedAt) ?>" class="font-normal opacity-75"><?= esc(lang('Translations.source_updated_at')) ?>: <?= esc((string) $sourceUpdatedAt) ?></time><?php endif; ?><?php if ($translationUpdatedAt !== null): ?><time datetime="<?= esc((string) $translationUpdatedAt) ?>" class="font-normal opacity-75"><?= esc(lang('Translations.translation_updated_at')) ?>: <?= esc((string) $translationUpdatedAt) ?></time><?php endif; ?><span><?= esc(lang('Translations.action_translate')) ?></span></a></li>
            <?php endforeach; ?>
        </ul>
    </section>
<?php endif; ?>
