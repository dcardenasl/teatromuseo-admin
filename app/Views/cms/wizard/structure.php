<?php
/**
 * @var string $title
 * @var string $csrfName
 * @var string $csrfToken
 */
$csrfName  ??= csrf_token();
$csrfToken ??= csrf_hash();

$wizardLanguages = array_values(array_filter($languages ?? [], static fn ($language): bool => is_array($language)));
$wizardDefaultLanguage = null;
$wizardTranslationLanguages = [];

foreach ($wizardLanguages as $language) {
    if (! is_array($language)) {
        continue;
    }

    if ($wizardDefaultLanguage === null && ! empty($language['is_default'])) {
        $wizardDefaultLanguage = $language;
        continue;
    }

    $wizardTranslationLanguages[] = $language;
}

if ($wizardDefaultLanguage === null && $wizardLanguages !== []) {
    $wizardDefaultLanguage = $wizardLanguages[0];
    $wizardTranslationLanguages = array_slice($wizardLanguages, 1);
}

$wizardDefaultLanguageId = (int) ($wizardDefaultLanguage['id'] ?? 0);
$wizardDefaultLanguageCode = strtoupper((string) ($wizardDefaultLanguage['code'] ?? ''));
$wizardDefaultLanguageLabel = (string) ($wizardDefaultLanguage['label'] ?? $wizardDefaultLanguage['name'] ?? $wizardDefaultLanguageCode);
?>
<div class="max-w-6xl mx-auto space-y-6" x-data="structureWizard()" x-init="init()" @slug-availability-changed.window="onSlugAvailabilityChanged($event)">
    <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div class="space-y-1">
                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-gray-500"><?= esc(lang('Wizard.structure_sidebar_label')) ?></p>
                <h1 class="text-2xl font-semibold text-gray-900"><?= esc(lang('Wizard.structure_heading')) ?></h1>
                <p class="max-w-3xl text-sm text-gray-600"><?= esc(lang('Wizard.structure_intro')) ?></p>
            </div>
            <a href="<?= site_url('dashboard') ?>" class="btn-secondary"><?= esc(lang('Wizard.btn_back_panel')) ?></a>
        </div>
    </div>

    <div x-show="screen === 'loading'" x-cloak class="rounded-xl border border-gray-200 bg-white p-10 text-center shadow-sm">
        <div class="animate-spin rounded-full h-10 w-10 border-b-2 border-brand-600 mx-auto mb-4"></div>
        <p class="text-sm text-gray-500"><?= esc(lang('Wizard.loading')) ?></p>
    </div>

    <div x-show="screen === 'error'" x-cloak class="rounded-xl border border-red-200 bg-red-50 p-6 shadow-sm">
        <p class="text-sm font-semibold text-red-700" x-text="errorMsg"></p>
        <div class="mt-4 flex flex-wrap gap-3">
            <button type="button" @click="init()" class="btn-secondary"><?= esc(lang('Wizard.btn_retry')) ?></button>
            <a href="<?= route_to('admin.cms.wizard') ?>" class="btn-primary"><?= esc(lang('Wizard.btn_back_panel')) ?></a>
        </div>
    </div>

    <div x-show="screen === 'home'" x-cloak class="space-y-6">
        <div class="grid gap-4 md:grid-cols-3">
            <button type="button" @click="start('collection')" class="flex min-h-[140px] flex-col items-start justify-between gap-4 rounded-xl border border-gray-200 bg-white p-5 text-left shadow-sm transition hover:border-brand-300 hover:shadow-md focus:outline-none focus:ring-2 focus:ring-brand-500">
                <span class="text-3xl">🗂️</span>
                <span class="space-y-1">
                    <span class="block text-sm font-semibold text-gray-900"><?= esc(lang('Wizard.create_collection')) ?></span>
                    <span class="block text-xs text-gray-500"><?= esc(lang('Wizard.create_collection_desc')) ?></span>
                </span>
            </button>
            <button type="button" @click="start('page')" class="flex min-h-[140px] flex-col items-start justify-between gap-4 rounded-xl border border-gray-200 bg-white p-5 text-left shadow-sm transition hover:border-brand-300 hover:shadow-md focus:outline-none focus:ring-2 focus:ring-brand-500">
                <span class="text-3xl">📄</span>
                <span class="space-y-1">
                    <span class="block text-sm font-semibold text-gray-900"><?= esc(lang('Wizard.create_page')) ?></span>
                    <span class="block text-xs text-gray-500"><?= esc(lang('Wizard.create_page_desc')) ?></span>
                </span>
            </button>
            <button type="button" @click="start('menu')" class="flex min-h-[140px] flex-col items-start justify-between gap-4 rounded-xl border border-gray-200 bg-white p-5 text-left shadow-sm transition hover:border-brand-300 hover:shadow-md focus:outline-none focus:ring-2 focus:ring-brand-500">
                <span class="text-3xl">🧭</span>
                <span class="space-y-1">
                    <span class="block text-sm font-semibold text-gray-900"><?= esc(lang('Wizard.create_menu')) ?></span>
                    <span class="block text-xs text-gray-500"><?= esc(lang('Wizard.create_menu_desc')) ?></span>
                </span>
            </button>
        </div>
    </div>

    <div x-show="screen === 'collection'" x-cloak class="space-y-6">
        <div class="flex items-center gap-3 text-sm text-gray-500">
            <button type="button" @click="screen = 'home'" class="rounded-lg border border-gray-300 bg-white px-3 py-1.5 font-semibold text-gray-700"><?= esc(lang('App.back')) ?></button>
            <span x-text="stepLabel()"></span>
        </div>
        <div class="h-2 overflow-hidden rounded-full bg-gray-200">
            <div class="h-2 rounded-full bg-brand-600 transition-all" :style="`width: ${Math.round((collectionStep / 2) * 100)}%`"></div>
        </div>
        <div class="grid gap-6 lg:grid-cols-12">
            <aside class="lg:col-span-3" x-show="collectionStep === 1" x-cloak>
                <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-gray-500"><?= esc(lang('Wizard.wizard_structure_step1')) ?></p>
                    <div class="mt-4 space-y-2">
                        <template x-for="option in config?.collection_types ?? []" :key="option.key">
                            <button type="button" @click="selectCollectionType(option.key)" :class="form.collection_type === option.key ? 'border-brand-500 bg-brand-50 text-brand-800' : 'border-gray-200 bg-white text-gray-700'" class="w-full rounded-xl border p-3 text-left transition">
                                <div class="font-semibold" x-text="option.label"></div>
                            </button>
                        </template>
                    </div>
                </div>
            </aside>
            <main class="lg:col-span-9">
                <form @submit.prevent="submitCollection()" class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm space-y-6">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-gray-500"><?= esc(lang('Wizard.wizard_structure_step1')) ?></p>
                        <h2 class="mt-1 text-2xl font-bold text-gray-900"><?= esc(lang('Wizard.create_collection')) ?></h2>
                        <p class="mt-1 text-sm text-gray-600"><?= esc(lang('Wizard.wizard_structure_collection_review_intro')) ?></p>
                    </div>
                    <template x-if="collectionStep === 1">
                        <div class="space-y-4">
                            <div x-show="collectionErrors.step1" x-cloak class="rounded-xl border border-red-200 bg-red-50 p-3 text-sm text-red-700" x-text="collectionErrors.step1"></div>
                            <div class="grid gap-4 md:grid-cols-2">
                                <?= view('components/forms/text_input', ['name' => 'collection_name', 'label' => lang('Wizard.wizard_structure_field_name'), 'type' => 'text', 'class' => 'block md:col-span-2', 'attrs' => 'x-model="form.name"']) ?>
                                <?= view('components/form/slug', [
                                    'name' => 'collection_slug_base',
                                    'label' => 'Wizard.wizard_structure_field_slug_base',
                                    'sourceId' => '#collection_name',
                                    'checkUrl' => route_to('admin.cms.collections.check_slug'),
                                    'required' => true,
                                    'help' => 'Wizard.wizard_structure_slug_help',
                                    'invalidMessage' => lang('Wizard.wizard_structure_slug_invalid'),
                                    'attrs' => 'x-model="form.slug_base" @input="form.collection_key = form.slug_base; validateCollectionSlug()" @blur="validateCollectionSlug(true)"',
                                ]) ?>
                                <p x-show="collectionErrors.slug_base" x-cloak class="md:col-span-2 -mt-2 text-sm text-red-600" x-text="collectionErrors.slug_base"></p>
                            </div>
                        </div>
                    </template>
                    <div x-show="collectionStep === 2" class="space-y-4 rounded-2xl border border-gray-200 bg-gray-50 p-4 text-sm text-gray-600">
                        <div>
                            <p class="font-semibold text-gray-900 mb-1"><?= esc(lang('Wizard.wizard_structure_final_summary')) ?></p>
                            <p><strong><?= esc(lang('Wizard.wizard_structure_summary_name')) ?>:</strong> <span x-text="form.name || '—'"></span></p>
                            <p><strong><?= esc(lang('Wizard.wizard_structure_summary_internal_slug')) ?>:</strong> <span x-text="form.collection_key || '—'"></span></p>
                            <p><strong><?= esc(lang('Wizard.wizard_structure_summary_type')) ?>:</strong> <span x-text="collectionTypeLabel()"></span></p>
                        </div>
                        <div class="rounded-xl border border-gray-200 bg-white p-4">
                            <div class="flex flex-wrap items-start justify-between gap-3">
                                <div>
                                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-gray-500"><?= esc(lang('Wizard.wizard_structure_preset_summary_title')) ?></p>
                                    <h3 class="mt-1 text-base font-semibold text-gray-900"><?= esc(lang('Wizard.wizard_structure_preset_blocks_title')) ?></h3>
                                </div>
                                <div class="flex flex-wrap gap-2">
                                    <button type="button" @click="collectionPreset && (usePreset = true)" :disabled="!collectionPreset" :class="usePreset && collectionPreset ? 'border-brand-500 bg-brand-50 text-brand-800' : 'border-gray-200 bg-white text-gray-700 disabled:opacity-50 disabled:cursor-not-allowed'" class="rounded-lg border px-3 py-1.5 text-sm font-semibold transition">
                                        <?= esc(lang('Wizard.wizard_structure_preset_apply')) ?>
                                    </button>
                                    <button type="button" @click="usePreset = false" :class="!usePreset ? 'border-brand-500 bg-brand-50 text-brand-800' : 'border-gray-200 bg-white text-gray-700'" class="rounded-lg border px-3 py-1.5 text-sm font-semibold transition">
                                        <?= esc(lang('Wizard.wizard_structure_preset_skip')) ?>
                                    </button>
                                </div>
                            </div>
                            <div x-show="collectionPresetMissingBlocks().length > 0" x-cloak class="mt-4 rounded-xl border border-amber-200 bg-amber-50 p-3 text-sm text-amber-800">
                                <span x-text="'<?= esc(lang('Wizard.wizard_structure_preset_missing_blocks'), 'js') ?>'.replace('%s', collectionPresetMissingBlocks().join(', '))"></span>
                            </div>
                            <template x-if="collectionPresetBlocks().length > 0">
                                <div class="mt-4 space-y-3">
                                    <template x-for="block in collectionPresetBlocks()" :key="block.sort_order + '-' + block.block_key">
                                        <div class="rounded-xl border border-gray-200 bg-gray-50 p-3">
                                            <div class="flex flex-wrap items-center justify-between gap-2">
                                                <div>
                                                    <p class="font-semibold text-gray-900" x-text="block.label || block.block_key"></p>
                                                    <p class="text-xs text-gray-500" x-text="block.block_key"></p>
                                                </div>
                                                <div class="flex flex-wrap gap-2 text-[11px] font-semibold uppercase tracking-[0.14em]">
                                                    <span :class="block.required ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-600'" class="rounded-full px-2 py-1" x-text="block.required ? '<?= esc(lang('Wizard.wizard_structure_preset_block_required'), 'js') ?>' : '<?= esc(lang('Wizard.wizard_structure_preset_block_optional'), 'js') ?>'"></span>
                                                    <span :class="block.locked ? 'bg-amber-100 text-amber-800' : 'bg-gray-100 text-gray-600'" class="rounded-full px-2 py-1" x-text="block.locked ? '<?= esc(lang('Wizard.wizard_structure_preset_block_locked'), 'js') ?>' : '<?= esc(lang('Wizard.wizard_structure_preset_block_editable'), 'js') ?>'"></span>
                                                </div>
                                            </div>
                                            <p class="mt-2 text-sm text-gray-600" x-text="block.help_text || '—'"></p>
                                        </div>
                                    </template>
                                </div>
                            </template>
                            <p class="mt-4 text-xs leading-5 text-gray-500">
                                <?= esc(lang('Wizard.wizard_structure_preset_legend')) ?>
                            </p>
                            <div x-show="collectionPresetBlocks().length === 0" class="mt-4 rounded-xl border border-dashed border-gray-300 bg-gray-50 p-3 text-sm text-gray-500">
                                <?= esc(lang('Wizard.wizard_structure_preset_empty_hint')) ?>
                            </div>
                        </div>
                        <div class="rounded-xl border border-gray-200 bg-white p-4 text-sm text-gray-600">
                            <p><strong>Estado:</strong> <span x-text="usePreset && collectionPreset ? 'Preset activo' : 'Sin preset'"></span></p>
                        </div>
                        <?php if ($wizardLanguages !== []): ?>
                            <div class="relative overflow-hidden rounded-xl border border-gray-200 bg-white p-4" :aria-busy="collectionTranslationBusy() ? 'true' : 'false'">
                                <div class="flex flex-wrap items-start justify-between gap-3">
                                    <div>
                                        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-gray-500"><?= esc(lang('Wizard.wizard_structure_languages_section_title')) ?></p>
                                        <h3 class="mt-1 text-base font-semibold text-gray-900"><?= esc(lang('Wizard.wizard_structure_languages_proposals_title')) ?></h3>
                                        <p class="mt-2 text-sm text-gray-600"><?= esc(lang('Wizard.wizard_structure_languages_section_help')) ?></p>
                                    </div>
                                    <div class="rounded-full bg-brand-50 px-3 py-1 text-xs font-semibold uppercase tracking-[0.14em] text-brand-700">
                                        <?= esc(lang('Wizard.wizard_structure_languages_base_label')) ?>: <span x-text="defaultCollectionLanguageLabel()"><?= esc($wizardDefaultLanguageLabel ?: '—') ?></span>
                                    </div>
                                </div>

                                <?php if ($wizardTranslationLanguages !== []): ?>
                                    <div class="mt-4 space-y-4" :class="collectionTranslationBusy() ? 'opacity-35 pointer-events-none select-none' : ''">
                                        <?php foreach ($wizardTranslationLanguages as $translationIndex => $language): ?>
                                            <?php
                                                $languageId = (int) ($language['id'] ?? 0);
                                            $languageCode = strtoupper((string) ($language['code'] ?? ''));
                                            $languageLabel = (string) ($language['label'] ?? $language['name'] ?? $languageCode ?: ('#' . $languageId));
                                            $translationFieldPrefix = "collectionTranslationRows[{$translationIndex}]";
                                            ?>
                                            <div class="rounded-xl border border-gray-200 bg-gray-50 p-4 transition" :class="collectionTranslations[<?= $translationIndex ?>]?.included ? 'opacity-100' : 'opacity-60'">
                                                <div class="flex flex-wrap items-start justify-between gap-3">
                                                    <div>
                                                        <p class="text-sm font-semibold text-gray-900"><?= esc($languageLabel) ?></p>
                                                        <p class="text-xs text-gray-500"><?= esc($languageCode ?: ('#' . $languageId)) ?></p>
                                                    </div>
                                                    <div class="flex flex-wrap items-center gap-2">
                                                        <label class="inline-flex items-center gap-2 rounded-full border border-gray-200 bg-white px-3 py-1.5 text-xs font-semibold text-gray-700">
                                                            <input type="checkbox" x-model="collectionTranslations[<?= $translationIndex ?>].included" :disabled="collectionTranslationBusy()" class="rounded border-gray-300 text-brand-600 focus:ring-brand-500 disabled:cursor-not-allowed disabled:opacity-50">
                                                            <span><?= esc(lang('Wizard.wizard_structure_language_include')) ?></span>
                                                        </label>
                                                        <button type="button" @click="translateCollectionLanguage(<?= $translationIndex ?>)" :disabled="collectionTranslationBusy() || !collectionTranslations[<?= $translationIndex ?>].included" class="rounded-lg border border-brand-200 bg-brand-50 px-3 py-1.5 text-xs font-semibold text-brand-700 hover:bg-brand-100 disabled:cursor-not-allowed disabled:opacity-50">
                                                            <span x-show="!collectionTranslationBusy()"><?= esc(lang('Wizard.wizard_structure_language_translate')) ?></span>
                                                            <span x-show="collectionTranslationBusy()" x-cloak><?= esc(lang('Wizard.wizard_structure_language_translating')) ?></span>
                                                        </button>
                                                    </div>
                                                </div>

                                                <div class="mt-4 grid gap-4 md:grid-cols-2">
                                                    <input type="hidden" id="collection_translation_language_<?= $translationIndex ?>" name="<?= esc($translationFieldPrefix) ?>[language_id]" value="<?= esc($languageId) ?>">

                                                    <?= view('components/forms/text_input', [
                                                        'name' => "collection_translation_name_{$translationIndex}",
                                                        'label' => lang('Wizard.wizard_structure_translation_name_label'),
                                                        'type' => 'text',
                                                        'class' => 'block',
                                                        'attrs' => ':disabled="collectionTranslationBusy() || !collectionTranslations[' . $translationIndex . '].included" x-model="collectionTranslations[' . $translationIndex . '].name" @input="clearCollectionTranslationError(' . $translationIndex . ')"',
                                                    ]) ?>

                                                    <?= view('components/form/slug', [
                                                        'name' => "collection_translation_slug_{$translationIndex}",
                                                        'label' => 'Wizard.wizard_structure_translation_slug_label',
                                                        'sourceId' => '#collection_translation_name_' . $translationIndex,
                                                        'checkUrl' => route_to('admin.cms.collections.check_slug'),
                                                        'required' => false,
                                                        'languageSelector' => '#collection_translation_language_' . $translationIndex,
                                                        'help' => 'Wizard.wizard_structure_translation_slug_help',
                                                        'attrs' => ':disabled="collectionTranslationBusy() || !collectionTranslations[' . $translationIndex . '].included" x-model="collectionTranslations[' . $translationIndex . '].slug" @input="clearCollectionTranslationError(' . $translationIndex . ')"',
                                                    ]) ?>
                                                </div>

                                                <p class="mt-3 text-xs text-gray-500" x-show="collectionTranslations[<?= $translationIndex ?>].included" x-cloak><?= esc(lang('Wizard.wizard_structure_translation_hint')) ?></p>
                                                <p class="mt-3 text-xs text-red-600" x-show="collectionTranslations[<?= $translationIndex ?>].error" x-cloak x-text="collectionTranslations[<?= $translationIndex ?>].error"></p>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                    <div x-show="collectionTranslationBusy()" x-cloak class="absolute inset-0 z-10 flex items-center justify-center bg-white/75 px-6 py-8 backdrop-blur-[1px]" aria-live="polite">
                                        <div class="w-full max-w-xl rounded-2xl border border-brand-200 bg-brand-50 px-5 py-4 text-brand-800 shadow-lg">
                                            <div class="flex items-start gap-4">
                                                <div class="mt-0.5 flex h-11 w-11 items-center justify-center rounded-full bg-white text-brand-600 shadow-sm">
                                                    <svg class="h-5 w-5 animate-spin" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                                        <circle cx="12" cy="12" r="9" class="opacity-20" stroke="currentColor" stroke-width="3"></circle>
                                                        <path d="M21 12a9 9 0 0 1-9 9" class="opacity-90" stroke="currentColor" stroke-linecap="round" stroke-width="3"></path>
                                                    </svg>
                                                </div>
                                                <div class="min-w-0 flex-1">
                                                    <p class="text-base font-semibold"><?= esc(lang('Wizard.wizard_structure_languages_busy_title')) ?></p>
                                                    <p class="mt-1 text-sm leading-6 text-brand-700">
                                                        <?= esc(lang('Wizard.wizard_structure_languages_busy_body_short')) ?>
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php else: ?>
                                    <div class="mt-4 rounded-xl border border-dashed border-gray-300 bg-gray-50 p-4 text-sm text-gray-500">
                                        <?= esc(lang('Wizard.wizard_structure_languages_no_extra')) ?>
                                    </div>
                                <?php endif; ?>

                                <?php if ($wizardTranslationLanguages !== []): ?>
                                    <p x-show="collectionTranslationError" x-cloak class="mt-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700" x-text="collectionTranslationError"></p>
                                    <div class="mt-4 flex flex-wrap items-center justify-between gap-3">
                                        <p class="text-xs text-gray-500"><?= esc(lang('Wizard.wizard_structure_languages_section_footer')) ?></p>
                                        <button type="button" @click="translateAllCollectionLanguages()" :disabled="collectionTranslationBusy()" class="rounded-lg border border-brand-200 bg-brand-50 px-4 py-2 text-sm font-semibold text-brand-700 hover:bg-brand-100 disabled:cursor-not-allowed disabled:opacity-50">
                                            <span x-show="!collectionTranslationBusy()"><?= esc(lang('Wizard.wizard_structure_language_translate_all')) ?></span>
                                            <span x-show="collectionTranslationBusy()" x-cloak><?= esc(lang('Wizard.wizard_structure_language_translating_all')) ?></span>
                                        </button>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="flex flex-wrap gap-3">
                        <button type="button" @click="prevCollectionStep()" class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700" :disabled="collectionStep === 1"><?= esc(lang('Wizard.wizard_structure_prev')) ?></button>
                        <button type="button" @click="nextCollectionStep()" x-show="collectionStep < 2" class="btn-secondary text-sm" :disabled="!canAdvanceCollectionStep()"><?= esc(lang('Wizard.wizard_structure_next')) ?></button>
                        <button type="submit" x-show="collectionStep === 2" class="btn-primary text-sm" :disabled="saving || collectionTranslationBusy() || !canSubmitCollection()">
                            <span x-show="!saving"><?= esc(lang('Wizard.wizard_structure_create')) ?></span>
                            <span x-show="saving"><?= esc(lang('Wizard.wizard_structure_creating')) ?></span>
                        </button>
                        <button type="button" @click="screen = 'home'" class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700"><?= esc(lang('Wizard.btn_back_panel')) ?></button>
                    </div>
                </form>
            </main>
        </div>
    </div>

    <div x-show="screen === 'collection-success'" x-cloak class="space-y-6">
        <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
            <div class="flex items-start gap-4">
                <div class="flex h-12 w-12 items-center justify-center rounded-full bg-green-50 text-2xl text-green-700">✅</div>
                <div class="min-w-0 flex-1">
                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-green-700"><?= esc(lang('Wizard.wizard_structure_completion_kicker')) ?></p>
                    <h2 class="mt-2 text-3xl font-bold text-gray-900"><?= esc(lang('Wizard.wizard_structure_completion_title')) ?></h2>
                    <p class="mt-3 max-w-3xl text-sm text-gray-600"><?= esc(lang('Wizard.wizard_structure_completion_body')) ?></p>
                </div>
                <span class="rounded-full bg-green-100 px-3 py-1 text-xs font-semibold uppercase tracking-[0.14em] text-green-800"><?= esc(lang('Wizard.wizard_structure_completion_ready_badge')) ?></span>
            </div>

            <div class="mt-6 rounded-2xl border border-gray-200 bg-gray-50 p-5">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.16em] text-gray-500"><?= esc(lang('Wizard.wizard_structure_completion_created_title')) ?></p>
                        <div class="mt-3 grid gap-2 text-sm text-gray-700">
                            <p><strong><?= esc(lang('Wizard.wizard_structure_summary_name')) ?>:</strong> <span x-text="collectionCompleted?.name || '—'"></span></p>
                            <p><strong><?= esc(lang('Wizard.wizard_structure_summary_internal_slug')) ?>:</strong> <span x-text="collectionCompleted?.slug || '—'"></span></p>
                            <p><strong><?= esc(lang('Wizard.wizard_structure_summary_type')) ?>:</strong> <span x-text="collectionTypeLabel(collectionCompleted?.type)"></span></p>
                        </div>
                    </div>
                    <p class="max-w-md text-sm text-gray-600"><?= esc(lang('Wizard.wizard_structure_completion_hint_body_short')) ?></p>
                </div>
            </div>

            <div class="mt-6">
                <p class="text-sm font-semibold text-gray-900"><?= esc(lang('Wizard.wizard_structure_completion_next_steps_title')) ?></p>
                <div class="mt-3 grid gap-3 md:grid-cols-3">
                    <a :href="collectionEntryCreateUrl()" class="flex items-center justify-between gap-3 rounded-xl border border-green-200 bg-white px-4 py-3 text-sm font-semibold text-green-800 shadow-sm hover:border-green-300 hover:bg-green-50">
                        <span><?= esc(lang('Wizard.wizard_structure_create_first_entry')) ?></span>
                        <span aria-hidden="true">→</span>
                    </a>
                    <a :href="collectionDetailUrl()" class="flex items-center justify-between gap-3 rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm font-semibold text-gray-700 shadow-sm hover:border-gray-300 hover:bg-gray-50">
                        <span><?= esc(lang('Wizard.wizard_structure_go_detail')) ?></span>
                        <span aria-hidden="true">→</span>
                    </a>
                    <a href="<?= route_to('admin.cms.collections') ?>" class="flex items-center justify-between gap-3 rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm font-semibold text-gray-700 shadow-sm hover:border-gray-300 hover:bg-gray-50">
                        <span><?= esc(lang('Wizard.wizard_structure_go_collections')) ?></span>
                        <span aria-hidden="true">→</span>
                    </a>
                </div>
                <div class="mt-4 flex flex-wrap gap-3">
                    <button type="button" @click="resetCollectionFlow()" class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700"><?= esc(lang('Wizard.wizard_structure_create_another')) ?></button>
                    <a href="<?= route_to('admin.cms.wizard') ?>" class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700"><?= esc(lang('Wizard.btn_back_panel')) ?></a>
                </div>
            </div>
        </div>
    </div>

    <div x-show="screen === 'page'" x-cloak class="space-y-6">
        <div class="flex items-center gap-3 text-sm text-gray-500">
            <button type="button" @click="screen = 'home'" class="rounded-lg border border-gray-300 bg-white px-3 py-1.5 font-semibold text-gray-700"><?= esc(lang('App.back')) ?></button>
        </div>
        <form @submit.prevent="submitPage()" class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm space-y-6">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-gray-500"><?= esc(lang('Wizard.create_page')) ?></p>
                <h2 class="mt-1 text-2xl font-bold text-gray-900"><?= esc(lang('Wizard.create_page')) ?></h2>
                <p class="mt-1 text-sm text-gray-600"><?= esc(lang('Wizard.wizard_structure_page_review_intro')) ?></p>
            </div>
            <div class="grid gap-4 md:grid-cols-2">
                <label class="block">
                    <span class="mb-1 block text-sm font-medium text-gray-700"><?= esc(lang('Wizard.wizard_structure_page_type')) ?></span>
                    <select x-model="page.page_type" class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-900 shadow-sm transition focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500">
                        <template x-for="option in config?.page_types ?? []" :key="option.key">
                            <option :value="option.key" x-text="option.label"></option>
                        </template>
                    </select>
                </label>
                <?= view('components/forms/text_input', ['name' => 'page_title', 'label' => lang('Wizard.wizard_structure_page_title'), 'type' => 'text', 'class' => 'block', 'attrs' => 'x-model="page.title"']) ?>
                <?= view('components/forms/text_input', ['name' => 'page_slug', 'label' => lang('Wizard.wizard_structure_page_slug'), 'type' => 'text', 'class' => 'block', 'attrs' => 'x-model="page.slug"']) ?>
            </div>
            <div class="rounded-2xl border border-gray-200 bg-gray-50 p-4 text-sm text-gray-600">
                <p class="font-semibold text-gray-900 mb-3"><?= esc(lang('Wizard.wizard_structure_final_summary')) ?></p>
                <p><strong><?= esc(lang('Wizard.wizard_structure_page_type')) ?>:</strong> <span x-text="pageTypeLabel()"></span></p>
                <p><strong><?= esc(lang('Wizard.wizard_structure_page_title')) ?>:</strong> <span x-text="page.title || '—'"></span></p>
                <p><strong><?= esc(lang('Wizard.wizard_structure_page_slug')) ?>:</strong> <span x-text="page.slug || '—'"></span></p>
            </div>
            <div class="flex flex-wrap gap-3"><button type="submit" class="btn-primary text-sm"><?= esc(lang('Wizard.create_page')) ?></button><button type="button" @click="screen='home'" class="btn-secondary text-sm"><?= esc(lang('Wizard.btn_back_panel')) ?></button></div>
        </form>
        <div x-show="message" x-cloak class="rounded-xl border border-green-200 bg-green-50 p-4 text-sm text-green-800 shadow-sm" x-text="message"></div>
        <div x-show="errorMsg" x-cloak class="rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-800 shadow-sm" x-text="errorMsg"></div>
    </div>

    <div x-show="screen === 'menu'" x-cloak class="space-y-6">
        <div class="flex items-center gap-3 text-sm text-gray-500"><button type="button" @click="screen='home'" class="rounded-lg border border-gray-300 bg-white px-3 py-1.5 font-semibold text-gray-700"><?= esc(lang('App.back')) ?></button></div>
        <form @submit.prevent="submitMenu()" class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm space-y-6">
            <div><p class="text-xs font-semibold uppercase tracking-[0.18em] text-gray-500"><?= esc(lang('Wizard.create_menu')) ?></p><h2 class="mt-1 text-2xl font-bold text-gray-900"><?= esc(lang('Wizard.create_menu')) ?></h2></div>
            <div class="grid gap-4 md:grid-cols-2">
                <?= view('components/forms/text_input', ['name' => 'menu_key', 'label' => lang('Wizard.wizard_structure_menu_key'), 'type' => 'text', 'class' => 'block', 'attrs' => 'x-model="menu.menu_key"']) ?>
                <?= view('components/forms/text_input', ['name' => 'menu_location', 'label' => lang('Wizard.wizard_structure_menu_location'), 'type' => 'text', 'class' => 'block', 'attrs' => 'x-model="menu.location"']) ?>
                <label class="flex items-center gap-3 rounded-xl border border-gray-200 p-4"><input type="checkbox" x-model="menu.is_active" class="rounded border-gray-300"><span class="text-sm font-semibold text-gray-900"><?= esc(lang('Wizard.wizard_structure_menu_active')) ?></span></label>
                <?= view('components/forms/text_input', ['name' => 'menu_name', 'label' => lang('Wizard.wizard_structure_menu_name'), 'type' => 'text', 'class' => 'block', 'attrs' => 'x-model="menu.name"']) ?>
            </div>
            <div class="flex flex-wrap gap-3"><button type="submit" class="btn-primary text-sm"><?= esc(lang('Wizard.create_menu')) ?></button><button type="button" @click="screen='home'" class="btn-secondary text-sm"><?= esc(lang('Wizard.btn_back_panel')) ?></button></div>
        </form>
        <div x-show="message" x-cloak class="rounded-xl border border-green-200 bg-green-50 p-4 text-sm text-green-800 shadow-sm" x-text="message"></div>
        <div x-show="errorMsg" x-cloak class="rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-800 shadow-sm" x-text="errorMsg"></div>
    </div>
</div>

<?php
// Boot config for the structureWizard() Alpine component
// (src/js/components/wizard/structureIndex.js), bundled into
// public/assets/js/app.js. Mirrors the window.__componentConfig pattern
// already used in layouts/partials/head.php — data only, no logic lives here.
$structureWizardBootJson = json_encode([
    'csrfName'    => $csrfName,
    'csrfToken'   => $csrfToken,
    'translateUrl' => route_to('admin.cms.translate'),
    'collectionTranslationLanguages' => $wizardTranslationLanguages,
    'collectionDefaultLanguage'      => $wizardDefaultLanguage,
    'routes' => [
        'config'           => route_to('admin.cms.wizard.structure.config'),
        'createCollection' => route_to('admin.cms.wizard.structure.create_collection'),
        'createPage'       => route_to('admin.cms.wizard.structure.create_page'),
        'createMenu'       => route_to('admin.cms.wizard.structure.create_menu'),
        'collections'      => route_to('admin.cms.collections'),
        'entriesCreate'    => route_to('admin.cms.entries.create'),
        'pages'            => route_to('admin.cms.pages'),
        'menus'            => route_to('admin.cms.menus'),
    ],
    'strings' => [
        'step_of' => lang('Wizard.step_of'),
        'wizard_structure_error_load'                 => lang('Wizard.wizard_structure_error_load'),
        'wizard_structure_languages_translate_error'   => lang('Wizard.wizard_structure_languages_translate_error'),
        'wizard_structure_translation_source_missing'  => lang('Wizard.wizard_structure_translation_source_missing'),
        'wizard_structure_translation_required'        => lang('Wizard.wizard_structure_translation_required'),
        'wizard_structure_translation_review'          => lang('Wizard.wizard_structure_translation_review'),
        'wizard_structure_slug_checking'                => lang('Wizard.wizard_structure_slug_checking'),
        'wizard_structure_step1_error'                  => lang('Wizard.wizard_structure_step1_error'),
        'wizard_structure_slug_unavailable'             => lang('Wizard.wizard_structure_slug_unavailable'),
        'wizard_structure_error_collection'             => lang('Wizard.wizard_structure_error_collection'),
        'wizard_structure_error_collection_missing_id'  => lang('Wizard.wizard_structure_error_collection_missing_id'),
        'wizard_structure_page_default_title'           => lang('Wizard.wizard_structure_page_default_title'),
        'wizard_structure_error_page'                   => lang('Wizard.wizard_structure_error_page'),
        'wizard_structure_page_created'                 => lang('Wizard.wizard_structure_page_created'),
        'wizard_structure_menu_default_key'             => lang('Wizard.wizard_structure_menu_default_key'),
        'wizard_structure_menu_default_name'            => lang('Wizard.wizard_structure_menu_default_name'),
        'wizard_structure_error_menu'                   => lang('Wizard.wizard_structure_error_menu'),
        'wizard_structure_menu_created'                 => lang('Wizard.wizard_structure_menu_created'),
    ],
], JSON_THROW_ON_ERROR);
?>
<script <?= csp_script_nonce() ?>>
  window.__structureWizardBoot = <?= $structureWizardBootJson ?>;
</script>
