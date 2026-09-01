<?php
/** @var int $fileId */
/** @var list<array<string, mixed>> $languages */
/** @var array<int, array<string, mixed>> $translations keyed by language_id */
$fileId       = $fileId ?? 0;
$languages    = $languages ?? [];
$translations = $translations ?? [];

$defaultLangId = 0;
foreach ($languages as $l) {
    if (! empty($l['is_default'])) {
        $defaultLangId = (int) $l['id'];
        break;
    }
}
?>
<div class="mb-4">
    <a href="<?= route_to('files') ?>/<?= esc((string) $fileId) ?>/show" class="text-sm text-brand-600 hover:text-brand-700">
        &larr; <?= esc(lang('FileTranslations.back_to_file')) ?>
    </a>
</div>

<?php if (empty($languages)): ?>
    <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
        <h3 class="text-lg font-semibold text-gray-900"><?= esc(lang('FileTranslations.page_title')) ?></h3>
        <p class="mt-1 text-sm text-gray-500"><?= esc(lang('FileTranslations.section_intro')) ?></p>
        <p class="mt-4 text-sm text-gray-500"><?= esc(lang('FileTranslations.no_languages')) ?></p>
    </div>
<?php else: ?>
    <?php ob_start(); ?>
    <div x-data="langTabs(<?= $defaultLangId ?>)" class="space-y-4">
        <div class="flex gap-0.5 border-b border-gray-200" role="tablist">
            <?php foreach ($languages as $lang): ?>
                <button type="button"
                    role="tab"
                    @click="setTab(<?= (int) $lang['id'] ?>)"
                    :aria-selected="isActive(<?= (int) $lang['id'] ?>)"
                    :class="isActive(<?= (int) $lang['id'] ?>) ? 'border-brand-600 text-brand-700 bg-brand-50/40' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                    class="px-4 py-2 text-sm font-medium border-b-2 -mb-px transition-colors">
                    <?= esc(strtoupper((string) ($lang['code'] ?? ''))) ?>
                    <?php if (! empty($lang['is_default'])): ?>
                        <span class="ml-1 text-brand-400">★</span>
                    <?php endif; ?>
                </button>
            <?php endforeach; ?>
        </div>

        <?php foreach ($languages as $index => $lang): ?>
            <?php
                $langId  = (int) ($lang['id'] ?? 0);
            $trans   = $translations[$langId] ?? [];
            $existId = isset($trans['id']) ? (string) $trans['id'] : '';
            $fieldPrefix = 'translations.' . $index;
            ?>
            <div x-show="isActive(<?= $langId ?>)" class="space-y-4">
                <input type="hidden" name="translations[<?= $index ?>][language_id]" value="<?= $langId ?>">
                <input type="hidden" name="translations[<?= $index ?>][existing_id]" value="<?= esc($existId) ?>">

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1"><?= esc(lang('FileTranslations.field_alt_text')) ?></label>
                    <input type="text" name="translations[<?= $index ?>][alt_text]" value="<?= esc(old($fieldPrefix . '.alt_text', $trans['alt_text'] ?? '')) ?>" maxlength="255" class="<?= esc(input_class($fieldPrefix . '.alt_text')) ?>" placeholder="<?= esc(lang('FileTranslations.placeholder_alt_text')) ?>" <?= field_aria_attrs($fieldPrefix . '.alt_text') ?>>
                    <?= render_field_error($fieldPrefix . '.alt_text') ?>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1"><?= esc(lang('FileTranslations.field_caption')) ?></label>
                    <input type="text" name="translations[<?= $index ?>][caption]" value="<?= esc(old($fieldPrefix . '.caption', $trans['caption'] ?? '')) ?>" maxlength="500" class="<?= esc(input_class($fieldPrefix . '.caption')) ?>" placeholder="<?= esc(lang('FileTranslations.placeholder_caption')) ?>" <?= field_aria_attrs($fieldPrefix . '.caption') ?>>
                    <?= render_field_error($fieldPrefix . '.caption') ?>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1"><?= esc(lang('FileTranslations.field_title')) ?></label>
                    <input type="text" name="translations[<?= $index ?>][title]" value="<?= esc(old($fieldPrefix . '.title', $trans['title'] ?? '')) ?>" maxlength="255" class="<?= esc(input_class($fieldPrefix . '.title')) ?>" placeholder="<?= esc(lang('FileTranslations.placeholder_title')) ?>" <?= field_aria_attrs($fieldPrefix . '.title') ?>>
                    <?= render_field_error($fieldPrefix . '.title') ?>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1"><?= esc(lang('FileTranslations.field_credit')) ?></label>
                    <input type="text" name="translations[<?= $index ?>][credit]" value="<?= esc(old($fieldPrefix . '.credit', $trans['credit'] ?? '')) ?>" maxlength="255" class="<?= esc(input_class($fieldPrefix . '.credit')) ?>" placeholder="<?= esc(lang('FileTranslations.placeholder_credit')) ?>" <?= field_aria_attrs($fieldPrefix . '.credit') ?>>
                    <?= render_field_error($fieldPrefix . '.credit') ?>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1"><?= esc(lang('FileTranslations.field_description')) ?></label>
                    <textarea name="translations[<?= $index ?>][description]" rows="3" class="<?= esc(input_class($fieldPrefix . '.description')) ?>" placeholder="<?= esc(lang('FileTranslations.placeholder_description')) ?>" <?= field_aria_attrs($fieldPrefix . '.description') ?>><?= esc(old($fieldPrefix . '.description', $trans['description'] ?? '')) ?></textarea>
                    <?= render_field_error($fieldPrefix . '.description') ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    <?php $translationContent = ob_get_clean(); ?>

    <?= view('components/display/form_section', [
        'title' => 'FileTranslations.editor_section',
        'description' => 'FileTranslations.section_intro',
        'content' => '<form method="post" action="' . route_to('admin.cms.file_translations.update', (string) $fileId) . '">'
            . csrf_field()
            . $translationContent
            . '<div class="mt-6 flex items-center gap-3 pt-4 border-t border-gray-100">'
            . '<button type="submit" class="btn-primary">' . esc(lang('App.save')) . '</button>'
            . '<a href="' . route_to('files') . '/' . esc((string) $fileId) . '/show" class="btn-secondary">' . esc(lang('App.cancel')) . '</a>'
            . '</div>'
            . '</form>',
        'bodyClass' => 'space-y-4'
    ]) ?>
<?php endif; ?>
