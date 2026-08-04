<?php
$item = $item ?? [];
$languages = $languages ?? [];
$defaultLangId = (int) ($defaultLangId ?? 0);
$defaultLangIndex = (int) ($defaultLangIndex ?? 0);
$defaultLangCode = (string) ($defaultLangCode ?? '');
$translateTargets = $translateTargets ?? [];
$translations = is_array($item['translations'] ?? null) ? $item['translations'] : [];
$translateUrl = route_to('admin.cms.translate');
$translationStats = [];
$defaultTranslation = null;
foreach ($translations as $translation) {
    if (is_array($translation) && (int) ($translation['language_id'] ?? 0) === $defaultLangId) {
        $defaultTranslation = $translation;
        break;
    }
}
foreach ($languages as $language) {
    $language['_source'] = $item;
    $translationStats[(int) ($language['id'] ?? 0)] = \App\Modules\Cms\Support\TranslationStatus::evaluate($language, $translations, ['name', 'slug'], $item['updated_at'] ?? null, $defaultTranslation);
}
?>

<section class="bg-white border border-gray-200 rounded-xl shadow-sm p-5 space-y-4">
    <h3 class="text-sm font-semibold text-gray-900"><?= esc(lang('App.form_core')) ?></h3>
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
        <?= view('components/form/boolean', [
            'name' => 'is_active',
            'label' => 'Events.field_is_active',
            'value' => array_key_exists('is_active', $item) ? ((int) $item['is_active'] === 1) : true,
            'help' => 'Events.field_is_active_help',
        ]) ?>
    </div>
</section>

<?php if (! empty($languages)): ?>
    <input type="hidden" name="default_language_id" value="<?= esc((string) $defaultLangId) ?>">
    <section class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
        <div class="mb-4">
            <h4 class="text-sm font-semibold text-gray-900"><?= esc(lang('Pages.translations_title')) ?></h4>
            <p class="mt-1 text-xs text-gray-500"><?= esc(lang('Events.event_types_translations_help')) ?></p>
        </div>

        <div x-data="langTabs(<?= $defaultLangId ?>, '<?= esc($translateUrl, 'attr') ?>', '<?= esc($defaultLangCode, 'attr') ?>')">
            <div class="flex items-center justify-between border-b border-gray-200 mb-4">
                <div class="flex gap-0.5" role="tablist">
                    <?php foreach ($languages as $language): ?>
                        <button type="button" role="tab" @click="setTab(<?= (int) $language['id'] ?>)" :aria-selected="isActive(<?= (int) $language['id'] ?>)" :class="isActive(<?= (int) $language['id'] ?>) ? 'border-brand-600 text-brand-700 bg-brand-50/40' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'" class="px-4 py-2 text-sm font-medium border-b-2 -mb-px transition-colors">
                            <?= esc(strtoupper((string) $language['code'])) ?><?php if (! empty($language['is_default'])): ?><span class="ml-1 text-brand-400">★</span><?php endif; ?>
                            <?php $tabStatus = $translationStats[(int) $language['id']]['status'] ?? 'missing'; ?>
                            <span class="inline-block w-2 h-2 rounded-full <?= \App\Modules\Cms\Support\TranslationStatus::badgeClasses($tabStatus, 'dot') ?>" title="<?= esc(lang('Translations.status_' . $tabStatus)) ?>"></span>
                        </button>
                    <?php endforeach; ?>
                </div>
                <?php if (! empty($translateTargets)): ?>
                    <?php $copyMappings = cms_translation_copy_mappings(['name', 'slug'], $languages, $defaultLangIndex); ?>
                    <button type="button" @click="copyDefaultToAll(<?= esc(json_encode($copyMappings, JSON_THROW_ON_ERROR), 'attr') ?>, '<?= esc(lang('Translations.confirm_copy_default'), 'js') ?>')" class="shrink-0 inline-flex items-center gap-1.5 text-xs text-gray-700 hover:text-gray-900 border border-gray-300 rounded px-3 py-1.5 bg-white hover:bg-gray-50 transition-colors">
                        <?= ui_icon('copy', 'h-3.5 w-3.5') ?> <?= esc(lang('Translations.action_copy_default')) ?>
                    </button>
                    <button type="button" @click="autoTranslateAll(<?= esc(json_encode($translateTargets, JSON_THROW_ON_ERROR), 'attr') ?>)" :disabled="translating || translatingAll" class="mb-px inline-flex items-center gap-1.5 text-xs text-brand-600 hover:text-brand-700 border border-brand-200 rounded px-3 py-1.5 bg-brand-50 hover:bg-brand-100 transition-colors disabled:opacity-50">
                        <span x-show="!translatingAll"><?= ui_icon('languages', 'h-3.5 w-3.5') ?> <?= esc(lang('App.translate_all')) ?></span>
                        <span x-show="translatingAll" x-cloak><?= ui_icon('loader', 'h-3.5 w-3.5 animate-spin') ?> <span x-text="translateAllProgress"></span></span>
                    </button>
                <?php endif; ?>
            </div>

            <p x-show="translateError !== ''" x-text="translateError" x-cloak class="mb-3 text-xs text-red-600 bg-red-50 border border-red-200 rounded px-3 py-2"></p>

            <?php foreach ($languages as $index => $language): ?>
                <?php
                $languageCode = strtolower((string) ($language['code'] ?? ''));
                $translationValue = [];
                foreach ($translations as $translation) {
                    if (! is_array($translation)) {
                        continue;
                    }
                    if ((int) ($translation['language_id'] ?? 0) === (int) $language['id'] || strtolower((string) ($translation['locale'] ?? $translation['language_code'] ?? '')) === $languageCode) {
                        $translationValue = $translation;
                        break;
                    }
                }
                if ($translationValue === [] && ! empty($language['is_default'])) {
                    $translationValue = [
                        'name' => $item['name'] ?? '',
                        'slug' => is_array($item['slugs'] ?? null) ? ($item['slugs'][$languageCode] ?? '') : '',
                    ];
                }
                ?>
                <div x-show="isActive(<?= (int) $language['id'] ?>)" class="space-y-4">
                    <input type="hidden" name="translations[<?= $index ?>][locale]" value="<?= esc($languageCode) ?>">
                    <input type="hidden" name="translations[<?= $index ?>][language_id]" value="<?= esc((string) $language['id']) ?>">
                    <?= view('components/form/text', [
                        'name' => "translations[{$index}][name]",
                        'label' => 'Events.field_event_type_name',
                        'required' => ! empty($language['is_default']),
                        'value' => old("translations.{$index}.name", $translationValue['name'] ?? ''),
                        'errors' => $errors ?? [],
                    ]) ?>
                    <?= view('components/form/slug', [
                        'name' => "translations[{$index}][slug]",
                        'label' => 'Events.field_event_type_slug',
                        'required' => ! empty($language['is_default']),
                        'sourceId' => sprintf('[name="translations[%d][name]"]', $index),
                        'value' => old("translations.{$index}.slug", $translationValue['slug'] ?? ''),
                        'help' => 'Events.field_event_type_slug_help',
                        'checkUrl' => route_to('admin.events.event_types.check_slug'),
                        'languageSelector' => sprintf('[name="translations[%d][locale]"]', $index),
                        'currentId' => $item['id'] ?? '',
                        'errors' => $errors ?? [],
                    ]) ?>
                </div>
            <?php endforeach; ?>
        </div>
    </section>
<?php endif; ?>
