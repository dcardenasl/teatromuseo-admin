<?php
$languages = is_array($languages ?? null) ? $languages : [];
$translations = is_array($item['translations'] ?? null) ? $item['translations'] : [];
$defaultLangId = (int) ($defaultLangId ?? 0);
$defaultLangCode = (string) ($defaultLangCode ?? '');
$focusLangId = (int) ($focusLangId ?? 0);
$initialTabId = $focusLangId > 0 ? $focusLangId : $defaultLangId;
$defaultLangIndex = 0;
foreach ($languages as $languageIndex => $language) {
    if ((int) ($language['id'] ?? 0) === $defaultLangId) {
        $defaultLangIndex = (int) $languageIndex;
        break;
    }
}
$translateTargets = is_array($translateTargets ?? null) ? $translateTargets : [];
$translateUrl = route_to('admin.cms.translate');
?>

<?php if ($languages !== []): ?>
<section class="rounded-xl border border-gray-200 bg-gray-50/60 p-4">
    <div class="mb-4">
        <h3 class="text-sm font-semibold text-gray-900"><?= esc(lang('Menus.menus_translations_title')) ?></h3>
        <p class="mt-1 text-xs text-gray-500"><?= esc(lang('Menus.menus_translations_help')) ?></p>
    </div>

    <div x-data="langTabs(<?= $initialTabId ?>, '<?= esc($translateUrl, 'attr') ?>', '<?= esc($defaultLangCode, 'attr') ?>')">
        <div class="mb-4 flex items-center justify-between gap-3 border-b border-gray-200">
            <div class="flex gap-0.5" role="tablist">
                <?php foreach ($languages as $language): ?>
                    <button type="button" role="tab"
                        @click="setTab(<?= (int) $language['id'] ?>)"
                        :aria-selected="isActive(<?= (int) $language['id'] ?>)"
                        :class="isActive(<?= (int) $language['id'] ?>) ? 'border-brand-600 text-brand-700 bg-brand-50/40' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                        class="-mb-px border-b-2 px-4 py-2 text-sm font-medium transition-colors">
                        <?= esc(strtoupper((string) $language['code'])) ?>
                        <?php if (! empty($language['is_default'])): ?><span class="ml-1 text-brand-400">★</span><?php endif; ?>
                    </button>
                <?php endforeach; ?>
            </div>
            <?php if ($translateTargets !== []): ?>
                <?php $copyMappings = cms_translation_copy_mappings(['name'], $languages, $defaultLangIndex); ?>
                <button type="button" @click="copyDefaultToAll(<?= esc(json_encode($copyMappings, JSON_THROW_ON_ERROR), 'attr') ?>, '<?= esc(lang('Translations.confirm_copy_default'), 'js') ?>')" class="inline-flex items-center gap-1.5 text-xs text-gray-700 border border-gray-300 rounded px-3 py-1.5 bg-white hover:bg-gray-50"><?= ui_icon('copy', 'h-3.5 w-3.5') ?> <?= esc(lang('Translations.action_copy_default')) ?></button>
                <?= view('layouts/partials/translate_button', ['translateTargets' => $translateTargets]) ?>
            <?php endif; ?>
        </div>

        <p x-show="translateError !== ''" x-text="translateError" x-cloak class="mb-3 rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-xs text-red-600"></p>

        <?php foreach ($languages as $index => $language): ?>
            <?php
            $translation = [];
            foreach ($translations as $candidate) {
                if (is_array($candidate) && (int) ($candidate['language_id'] ?? 0) === (int) $language['id']) {
                    $translation = $candidate;
                    break;
                }
            }
            $field = "translations[{$index}][name]";
            ?>
            <div x-show="isActive(<?= (int) $language['id'] ?>)">
                <input type="hidden" name="translations[<?= $index ?>][language_id]" value="<?= esc($language['id']) ?>">
                <?= view('components/form/text', [
                    'name' => $field,
                    'label' => 'Menus.menus_translation_name_label',
                    'required' => ! empty($language['is_default']),
                    'placeholder' => 'Menus.menus_translation_name_placeholder',
                    'help' => 'Menus.menus_translation_name_help',
                    'value' => old("translations.{$index}.name", $translation['name'] ?? ''),
                    'maxlength' => 150,
                    'errors' => $errors ?? [],
                ]) ?>
            </div>
        <?php endforeach; ?>
    </div>
</section>
<?php endif; ?>
