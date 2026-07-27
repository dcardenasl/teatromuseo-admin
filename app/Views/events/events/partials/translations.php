<?php

$languages = is_array($languages ?? null) ? array_values($languages) : [];
$defaultLangIndex = (int) ($defaultLangIndex ?? 0);
$defaultLangCode = (string) ($defaultLangCode ?? ($languages[$defaultLangIndex]['code'] ?? ''));
$translationValues = is_array($translations ?? null) ? $translations : [];
$defaultTranslation = $translationValues[$defaultLangCode] ?? [];

$translationLabel = static function (string $key, string $fallback): string {
    $value = lang('Events.' . $key);
    return is_string($value) && ! str_starts_with($value, 'Events.') ? $value : $fallback;
};
?>

<section class="rounded-xl border border-gray-200 bg-gray-50/60 p-4">
    <div class="mb-4">
        <h4 class="text-sm font-semibold text-gray-900">
            <?= esc($translationLabel('translations_title', 'Translations')) ?>
        </h4>
        <p class="mt-1 text-xs text-gray-500">
            <?= esc($translationLabel('translations_help', 'Content languages come from the active CMS language registry.')) ?>
        </p>
    </div>

    <div
        x-data="{ active: '<?= esc($defaultLangCode, 'js') ?>' }"
        data-default-translation-index="<?= esc((string) $defaultLangIndex, 'attr') ?>"
    >
        <div class="mb-4 flex flex-wrap gap-1 border-b border-gray-200" role="tablist">
            <?php foreach ($languages as $language): ?>
                <?php
                $code = strtolower((string) ($language['code'] ?? ''));
                if ($code === '') {
                    continue;
                }
                $tabLabel = (string) ($language['native_name'] ?? $language['name'] ?? strtoupper($code));
                ?>
                <button
                    type="button"
                    role="tab"
                    @click="active = '<?= esc($code, 'js') ?>'"
                    :aria-selected="active === '<?= esc($code, 'js') ?>'"
                    :class="active === '<?= esc($code, 'js') ?>' ? 'border-brand-600 text-brand-700 bg-brand-50/40' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                    class="-mb-px border-b-2 px-3 py-2 text-sm font-medium transition-colors"
                >
                    <?= esc(strtoupper($code)) ?>
                    <span class="ml-1 text-xs font-normal text-gray-400"><?= esc($tabLabel) ?></span>
                    <?php if (! empty($language['is_default'])): ?><span class="ml-1 text-brand-400">★</span><?php endif; ?>
                </button>
            <?php endforeach; ?>
        </div>

        <?php foreach ($languages as $index => $language): ?>
            <?php
            $code = strtolower((string) ($language['code'] ?? ''));
            if ($code === '') {
                continue;
            }
            $row = $translationValues[$code] ?? [];
            $isDefault = $index === $defaultLangIndex;
            $titleValue = (string) ($row['title'] ?? ($isDefault ? ($item['title'] ?? '') : ''));
            $descriptionValue = (string) ($row['description'] ?? ($isDefault ? ($item['description'] ?? '') : ''));
            ?>
            <div x-show="active === '<?= esc($code, 'js') ?>'" class="space-y-4" <?= $isDefault ? '' : 'x-cloak' ?>>
                <input type="hidden" name="translations[<?= (int) $index ?>][locale]" value="<?= esc($code, 'attr') ?>">

                <?= view('components/form/text', [
                    'name' => "translations[{$index}][title]",
                    'label' => 'Events.translation_title_label',
                    'required' => $isDefault,
                    'readonly' => false,
                    'value' => old("translations.{$index}.title", $titleValue),
                    'placeholder' => 'Events.field_title_placeholder',
                    'help' => $isDefault ? 'Events.field_title_help' : 'Events.translation_optional_help',
                    'errors' => $errors ?? [],
                ]) ?>

                <?= view('components/form/textarea', [
                    'name' => "translations[{$index}][description]",
                    'label' => 'Events.translation_description_label',
                    'required' => $isDefault,
                    'value' => old("translations.{$index}.description", $descriptionValue),
                    'placeholder' => 'Events.field_description_placeholder',
                    'help' => $isDefault ? 'Events.field_description_help' : 'Events.translation_optional_help',
                    'errors' => $errors ?? [],
                ]) ?>
            </div>
        <?php endforeach; ?>

        <input type="hidden" name="title" value="<?= esc((string) ($defaultTranslation['title'] ?? ($item['title'] ?? '')), 'attr') ?>">
        <input type="hidden" name="description" value="<?= esc((string) ($defaultTranslation['description'] ?? ($item['description'] ?? '')), 'attr') ?>">
    </div>
</section>

<script>
document.addEventListener('submit', function (event) {
    const form = event.target;
    if (!(form instanceof HTMLFormElement)) return;

    const wrapper = form.querySelector('[data-default-translation-index]');
    if (!wrapper) return;

    const index = wrapper.dataset.defaultTranslationIndex;
    const title = form.querySelector(`[name="translations[${index}][title]"]`);
    const description = form.querySelector(`[name="translations[${index}][description]"]`);
    const rootTitle = form.querySelector('[name="title"]');
    const rootDescription = form.querySelector('[name="description"]');

    if (title && rootTitle) rootTitle.value = title.value;
    if (description && rootDescription) rootDescription.value = description.value;
}, true);
</script>
