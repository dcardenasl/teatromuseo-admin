<?php
/**
 * Reusable multilingual settings panel.
 *
 * @var string $title
 * @var string $description
 * @var string|null $badge
 * @var string $languageHelp
 * @var string $translateAllLabel
 * @var string $translateFromDefaultLabel
 * @var string $translatingLabel
 * @var string $translateUrl
 * @var int $activeLanguageId
 * @var string $defaultLanguageCode
 * @var array<int, array<string, mixed>> $translationLanguages
 * @var array<int, array<int, array<string, mixed>>> $rowsByLanguage
 * @var array<int, array<int, array<string, mixed>>> $translateTargetsByLanguageId
 * @var array<int, array<string, mixed>> $translateTargets
 */

$title = (string) ($title ?? '');
$description = (string) ($description ?? '');
$badge = isset($badge) ? (string) $badge : null;
$languageHelp = (string) ($languageHelp ?? '');
$translateAllLabel = (string) ($translateAllLabel ?? '');
$translateFromDefaultLabel = (string) ($translateFromDefaultLabel ?? '');
$translatingLabel = (string) ($translatingLabel ?? '');
$translateUrl = (string) ($translateUrl ?? '');
$activeLanguageId = isset($activeLanguageId) && is_numeric($activeLanguageId) ? (int) $activeLanguageId : 0;
$defaultLanguageCode = strtoupper((string) ($defaultLanguageCode ?? ''));
$translationLanguages = is_array($translationLanguages ?? null) ? $translationLanguages : [];
$rowsByLanguage = is_array($rowsByLanguage ?? null) ? $rowsByLanguage : [];
$translateTargetsByLanguageId = is_array($translateTargetsByLanguageId ?? null) ? $translateTargetsByLanguageId : [];
$translateTargets = is_array($translateTargets ?? null) ? $translateTargets : [];
?>

<section class="rounded-xl border border-gray-200 bg-white shadow-sm"
         x-data="langTabs(<?= $activeLanguageId ?>, '<?= esc($translateUrl, 'attr') ?>', '<?= esc($defaultLanguageCode, 'attr') ?>')">
    <div class="flex flex-col gap-4 border-b border-gray-100 px-5 py-4 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <h3 class="text-sm font-semibold uppercase tracking-wider text-gray-700"><?= esc($title) ?></h3>
            <?php if ($description !== ''): ?>
                <p class="mt-1 text-xs text-gray-500"><?= esc($description) ?></p>
            <?php endif; ?>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <?php if ($badge !== null && trim($badge) !== ''): ?>
                <span class="inline-flex items-center rounded-full bg-gray-50 px-2.5 py-1 text-[11px] font-semibold uppercase tracking-[0.14em] text-gray-600">
                    <?= esc($badge) ?>
                </span>
            <?php endif; ?>
            <?php if ($translateTargets !== []): ?>
                <button type="button"
                    @click="autoTranslateAll(<?= esc(json_encode($translateTargets, JSON_THROW_ON_ERROR), 'attr') ?>)"
                    :disabled="translating || translatingAll"
                    class="inline-flex items-center gap-1.5 rounded-md border border-brand-200 bg-brand-50 px-3 py-1.5 text-xs text-brand-600 transition-colors hover:bg-brand-100 hover:text-brand-700 disabled:opacity-50">
                    <span x-show="!translatingAll"><?= ui_icon('languages', 'h-3.5 w-3.5') ?> <?= esc($translateAllLabel) ?></span>
                    <span x-show="translatingAll" x-cloak><?= ui_icon('loader', 'h-3.5 w-3.5 animate-spin') ?> <span x-text="translateAllProgress"></span></span>
                </button>
            <?php endif; ?>
        </div>
    </div>

    <div class="px-5 py-4">
        <div class="flex items-center justify-between gap-3 border-b border-gray-200 pb-3">
            <div class="flex flex-wrap gap-0.5" role="tablist">
                <?php foreach ($translationLanguages as $language): ?>
                    <?php $languageId = (int) ($language['id'] ?? 0); ?>
                    <button type="button"
                        role="tab"
                        @click="setTab(<?= $languageId ?>)"
                        :aria-selected="isActive(<?= $languageId ?>)"
                        :class="isActive(<?= $languageId ?>) ? 'border-brand-600 text-brand-700 bg-brand-50/40' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                        class="px-4 py-2 text-sm font-medium border-b-2 -mb-px transition-colors inline-flex items-center gap-1.5">
                        <span><?= esc(strtoupper((string) ($language['code'] ?? ''))) ?></span>
                        <?php if (!empty($language['is_default'])): ?>
                            <span class="text-brand-500">★</span>
                        <?php endif; ?>
                    </button>
                <?php endforeach; ?>
            </div>
        </div>

        <p x-show="translateError !== ''" x-text="translateError" x-cloak class="mt-4 rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-xs text-red-600"></p>

        <?php foreach ($translationLanguages as $language): ?>
            <?php
            $languageId = (int) ($language['id'] ?? 0);
            $languageCode = strtoupper((string) ($language['code'] ?? ''));
            $languageName = trim((string) ($language['native_name'] ?? $language['name'] ?? $languageCode));
            $isDefault = !empty($language['is_default']);
            $rows = $rowsByLanguage[$languageId] ?? [];
            ?>
            <div x-show="isActive(<?= $languageId ?>)" class="mt-4 space-y-4" x-cloak>
                <div class="flex flex-wrap items-start justify-between gap-3 rounded-2xl border border-brand-200 bg-brand-50/60 px-4 py-3">
                    <div>
                        <p class="text-sm font-semibold text-gray-900">
                            <?= esc($languageName) ?>
                            <?php if ($isDefault): ?>
                                <span class="text-xs font-normal text-brand-600 ml-1.5 uppercase tracking-wider">(Predeterminado)</span>
                            <?php endif; ?>
                        </p>
                        <?php if ($languageHelp !== ''): ?>
                            <p class="mt-1 text-xs text-gray-600"><?= esc($languageHelp) ?></p>
                        <?php endif; ?>
                    </div>
                    <?php if (!$isDefault): ?>
                        <button type="button"
                            @click="autoTranslate('<?= esc($languageCode, 'attr') ?>', <?= esc(json_encode($translateTargetsByLanguageId[$languageId] ?? [], JSON_THROW_ON_ERROR), 'attr') ?>)"
                            :disabled="translating"
                            class="inline-flex items-center gap-1.5 rounded-md border border-brand-200 bg-white px-3 py-1.5 text-xs text-brand-600 transition-colors hover:bg-brand-100 hover:text-brand-700 disabled:opacity-50">
                            <span x-show="!translating"><?= ui_icon('languages', 'h-3.5 w-3.5') ?> <?= esc($translateFromDefaultLabel) ?></span>
                            <span x-show="translating" x-cloak><?= ui_icon('loader', 'h-3.5 w-3.5 animate-spin') ?> <?= esc($translatingLabel) ?></span>
                        </button>
                    <?php endif; ?>
                </div>

                <div class="grid grid-cols-1 gap-4">
                    <?php foreach ($rows as $row): ?>
                        <?php
                        $fieldId = (string) ($row['id'] ?? '');
                        $fieldName = (string) ($row['name'] ?? '');
                        $fieldLabel = (string) ($row['label'] ?? '');
                        $fieldHelp = (string) ($row['help'] ?? '');
                        $fieldValue = (string) ($row['value'] ?? '');
                        $fieldPlaceholder = (string) ($row['placeholder'] ?? '');
                        $fieldInputType = (string) ($row['inputType'] ?? 'text');
                        $fieldReadonly = ! empty($row['readonly']);
                        ?>
                        <div class="rounded-xl border border-gray-100 bg-white px-4 py-4 shadow-sm">
                            <div class="flex flex-wrap items-center justify-between gap-2">
                                <label class="text-sm font-medium text-gray-700" for="<?= esc($fieldId) ?>">
                                    <?= esc($fieldLabel) ?>
                                </label>
                                <span class="inline-flex items-center rounded-full bg-gray-50 px-2 py-0.5 text-[11px] font-semibold uppercase tracking-[0.14em] text-gray-500">
                                    <?= esc($languageCode) ?>
                                </span>
                            </div>
                            <?php if ($fieldHelp !== ''): ?>
                                <p class="mt-1 text-xs text-gray-400"><?= esc($fieldHelp) ?></p>
                            <?php endif; ?>

                            <div class="mt-3">
                                <?php if (in_array($fieldInputType, ['textarea', 'richtext'], true)): ?>
                                    <textarea id="<?= esc($fieldId) ?>"
                                              name="<?= esc($fieldName) ?>"
                                              rows="4"
                                              placeholder="<?= esc($fieldPlaceholder) ?>"
                                              class="form-input text-sm"
                                              <?= $fieldReadonly ? 'readonly' : '' ?>><?= esc($fieldValue) ?></textarea>
                                <?php elseif ($fieldInputType === 'code'): ?>
                                    <textarea id="<?= esc($fieldId) ?>"
                                              name="<?= esc($fieldName) ?>"
                                              rows="5"
                                              placeholder="<?= esc($fieldPlaceholder) ?>"
                                              class="form-input font-mono text-sm bg-gray-950 text-green-400 border-gray-700"
                                              <?= $fieldReadonly ? 'readonly' : '' ?>><?= esc($fieldValue) ?></textarea>
                                <?php else: ?>
                                    <input id="<?= esc($fieldId) ?>"
                                           type="text"
                                           name="<?= esc($fieldName) ?>"
                                           value="<?= esc($fieldValue) ?>"
                                           placeholder="<?= esc($fieldPlaceholder) ?>"
                                           class="form-input text-sm"
                                           <?= $fieldReadonly ? 'readonly' : '' ?>>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</section>
