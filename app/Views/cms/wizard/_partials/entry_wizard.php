<?php /* Wizard — Entry creation flow (A screens): collection-select, steps, confirm, success */ ?>

<!-- ── SCREEN: COLLECTION SELECT ── -->
<div x-show="screen === 'collection-select'" x-cloak>
    <h2 class="text-lg font-semibold text-gray-900 mb-4"><?= lang('Wizard.select_collection') ?></h2>
    <div x-show="(config?.collections ?? []).length === 0"
         class="rounded-xl border border-dashed border-gray-200 bg-white py-10 text-center text-sm text-gray-400 shadow-sm">
        <?= lang('Wizard.no_collections') ?>
    </div>
    <div class="grid grid-cols-1 gap-3 md:grid-cols-2 xl:grid-cols-3">
        <template x-for="col in (config?.collections ?? [])" :key="col.id">
            <button @click="selectCollection(col)"
                    class="flex min-h-[120px] flex-col items-start justify-between gap-3 rounded-xl border border-gray-200 bg-white p-4 text-left shadow-sm transition hover:border-brand-300 hover:shadow-md focus:outline-none focus:ring-2 focus:ring-brand-500">
                <span class="text-2xl" x-text="col.icon || '📄'"></span>
                <span class="space-y-1">
                    <span class="block text-sm font-semibold text-gray-900" x-text="collectionDisplayLabel(col)"></span>
                    <span class="block text-xs text-gray-500 line-clamp-2" x-text="col.description"></span>
                </span>
            </button>
        </template>
    </div>
    <button @click="screen = 'home'" class="mt-4 text-sm text-gray-500 hover:text-gray-700"><?= lang('Wizard.btn_back') ?></button>
</div>

<!-- ── SCREEN: STEPS ── -->
<template x-if="screen === 'steps' && currentStepSchema">
    <div>
        <!-- Progress bar (unified across native fields + block content steps) -->
        <div class="mb-4">
            <div class="flex items-center justify-between text-xs text-gray-400 mb-1">
                <span x-text="unifiedStepLabel()"></span>
                <span x-text="collectionDisplayLabel(selectedCollection)"></span>
            </div>
            <div class="h-2 w-full rounded-full bg-gray-200">
                <div class="h-2 rounded-full bg-brand-600 transition-all"
                     :style="`width: ${unifiedProgressPercent()}%`"></div>
            </div>
        </div>

        <!-- Step header -->
        <h2 class="text-lg font-semibold text-gray-900 mb-1" x-text="currentStepSchema.step_title"></h2>
        <p class="text-sm text-gray-500 mb-5" x-text="currentStepSchema.step_hint"></p>

        <!-- Dynamic fields -->
        <template x-for="field in currentStepSchema.fields" :key="field.key">
            <div class="mb-5">
                <label class="block text-sm font-medium text-gray-700 mb-1"
                       x-text="field.label + (field.required ? '<?= lang('Wizard.required_suffix') ?>' : '')"></label>

                <template x-if="field.type === 'text'">
                    <input type="text" :placeholder="field.placeholder || ''" x-model="formData[field.key]"
                           class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-900 shadow-sm transition focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500" />
                </template>

                <template x-if="field.type === 'textarea'">
                    <textarea :placeholder="field.placeholder || ''" x-model="formData[field.key]" rows="4"
                              class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-900 shadow-sm transition focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500"></textarea>
                </template>

                <template x-if="field.type === 'date'">
                    <input type="date" x-model="formData[field.key]"
                           class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-900 shadow-sm transition focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500" />
                </template>

                <template x-if="field.type === 'select'">
                    <select x-model="formData[field.key]"
                            class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-900 shadow-sm transition focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500">
                        <template x-for="opt in (field.options || [])" :key="opt.value">
                            <option :value="opt.value" x-text="opt.label"></option>
                        </template>
                    </select>
                </template>

                <template x-if="field.type === 'image'">
                    <div x-data="{ mode: (formData[field.key + '_url'] && !formData[field.key + '_id']) ? 'external_url' : 'hub_file' }">
                        <div class="grid grid-cols-2 gap-2 mb-2" role="group">
                            <button type="button" @click="mode = 'hub_file'"
                                    class="rounded-lg border px-3 py-2 text-xs font-medium text-left transition"
                                    :class="mode === 'hub_file' ? 'border-brand-300 bg-brand-50 text-brand-700' : 'border-gray-200 bg-white text-gray-600 hover:bg-gray-50'">
                                <?= lang('Wizard.image_source_library') ?>
                            </button>
                            <button type="button" @click="mode = 'external_url'; formData[field.key + '_id'] = null"
                                    class="rounded-lg border px-3 py-2 text-xs font-medium text-left transition"
                                    :class="mode === 'external_url' ? 'border-brand-300 bg-brand-50 text-brand-700' : 'border-gray-200 bg-white text-gray-600 hover:bg-gray-50'">
                                <?= lang('Wizard.image_source_external') ?>
                            </button>
                        </div>

                        <template x-if="mode === 'hub_file'">
                            <div>
                                <template x-if="formData[field.key + '_url']">
                                    <div class="relative inline-block">
                                        <img :src="formData[field.key + '_url']"
                                             class="rounded-lg max-h-48 object-cover border border-gray-200" />
                                        <button type="button"
                                                @click="formData[field.key + '_id'] = null; formData[field.key + '_url'] = null"
                                                class="absolute top-2 right-2 bg-red-500 text-white rounded-full w-6 h-6 text-xs flex items-center justify-center hover:bg-red-600">✕</button>
                                    </div>
                                </template>
                                <template x-if="!formData[field.key + '_url']">
                                    <label :class="{'opacity-60': uploading}"
                                           class="flex flex-col items-center justify-center gap-2 rounded-xl border-2 border-dashed border-gray-300 bg-gray-50 p-6 cursor-pointer hover:border-brand-400 hover:bg-brand-50 transition-colors">
                                        <span class="text-4xl">📷</span>
                                        <span class="text-sm text-gray-500"><?= lang('Wizard.upload_image') ?></span>
                                        <span class="text-xs text-gray-400"><?= lang('Wizard.upload_click_hint') ?></span>
                                        <span x-show="uploading" class="text-xs text-brand-600"><?= lang('Wizard.upload_uploading') ?></span>
                                        <input type="file" accept="image/*" class="hidden"
                                               @change="uploadImage(field, $event.target.files[0])" />
                                    </label>
                                </template>
                            </div>
                        </template>

                        <template x-if="mode === 'external_url'">
                            <div>
                                <input type="url" x-model="formData[field.key + '_url']"
                                       placeholder="<?= esc(lang('Wizard.image_source_external_placeholder'), 'attr') ?>"
                                       inputmode="url" spellcheck="false"
                                       class="block w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-900 shadow-sm transition focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500" />
                                <template x-if="formData[field.key + '_url']">
                                    <img :src="formData[field.key + '_url']" class="mt-2 rounded-lg max-h-48 object-cover border border-gray-200" />
                                </template>
                            </div>
                        </template>

                        <p x-show="uploadError" class="mt-1 text-xs text-red-600" x-text="uploadError"></p>
                    </div>
                </template>

                <template x-if="field.type === 'rich_text'">
                    <textarea :placeholder="field.placeholder || ''" x-model="formData[field.key]" rows="8"
                              class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm font-mono text-gray-900 shadow-sm transition focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500"></textarea>
                </template>
            </div>
        </template>

        <!-- Navigation -->
        <div class="flex justify-between mt-6">
            <button @click="prevStep()" class="btn-secondary"><?= lang('Wizard.btn_back') ?></button>
            <button @click="nextStep()"
                    :disabled="!canAdvance()"
                    :class="canAdvance() ? 'btn-primary' : 'btn-primary opacity-50 cursor-not-allowed'">
                <span x-show="!isLastUnifiedStep"><?= lang('Wizard.btn_next') ?></span>
                <span x-show="isLastUnifiedStep"><?= lang('Wizard.btn_review') ?></span>
            </button>
        </div>
    </div>
</template>

<!-- ── SCREEN: BLOCK CONTENT STEPS (collection block_template) ── -->
<template x-if="screen === 'block-steps' && blockContentSteps[blockContentStepIndex]">
    <div>
        <!-- Progress bar (unified across native fields + block content steps) -->
        <div class="mb-4">
            <div class="flex items-center justify-between text-xs text-gray-400 mb-1">
                <span x-text="unifiedStepLabel()"></span>
                <span x-text="collectionDisplayLabel(selectedCollection)"></span>
            </div>
            <div class="h-2 w-full rounded-full bg-gray-200">
                <div class="h-2 rounded-full bg-brand-600 transition-all"
                     :style="`width: ${unifiedProgressPercent()}%`"></div>
            </div>
        </div>

        <!-- Step header -->
        <h2 class="text-lg font-semibold text-gray-900 mb-1" x-text="blockContentSteps[blockContentStepIndex].label"></h2>
        <p class="text-sm text-gray-500 mb-1" x-show="blockContentSteps[blockContentStepIndex].help_text" x-text="blockContentSteps[blockContentStepIndex].help_text"></p>
        <p class="text-xs font-medium mb-5"
           :class="blockContentSteps[blockContentStepIndex].required ? 'text-red-500' : 'text-gray-400'"
           x-text="blockContentSteps[blockContentStepIndex].required ? '<?= esc(lang('Wizard.wizard_content_block_required'), 'js') ?>' : '<?= esc(lang('Wizard.wizard_content_block_optional'), 'js') ?>'"></p>

        <!-- No fields notice -->
        <template x-if="blockContentSteps[blockContentStepIndex].fields.length === 0">
            <p class="text-sm text-gray-400 py-6 text-center"><?= lang('Wizard.no_block_fields') ?></p>
        </template>

        <!-- Dynamic fields, reusing the schema-driven rendering from _partials/block_edit.php,
             bound to blockContentDrafts[blockContentStepIndex] instead of blockEditData. -->
        <template x-for="field in blockContentSteps[blockContentStepIndex].fields" :key="field.key">
            <div class="mb-5">
                <label class="block text-sm font-medium text-gray-700 mb-1"
                       x-text="field.label + (field.required ? '<?= lang('Wizard.required_suffix') ?>' : '')"></label>

                <template x-if="field.uiType === 'text'">
                    <input type="text" x-model="blockContentDrafts[blockContentStepIndex][field.key]"
                           class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-900 shadow-sm transition focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500" />
                </template>

                <template x-if="field.uiType === 'date'">
                    <input type="date" x-model="blockContentDrafts[blockContentStepIndex][field.key]"
                           class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-900 shadow-sm transition focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500" />
                </template>

                <template x-if="field.uiType === 'number'">
                    <input type="number" x-model="blockContentDrafts[blockContentStepIndex][field.key]"
                           class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-900 shadow-sm transition focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500" />
                </template>

                <template x-if="field.uiType === 'boolean'">
                    <select x-model="blockContentDrafts[blockContentStepIndex][field.key]"
                            class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-900 shadow-sm transition focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500">
                        <option value="1"><?= lang('Wizard.bool_yes') ?></option>
                        <option value="0"><?= lang('Wizard.bool_no') ?></option>
                    </select>
                </template>

                <template x-if="field.uiType === 'select'">
                    <select x-model="blockContentDrafts[blockContentStepIndex][field.key]"
                            class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-900 shadow-sm transition focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500">
                        <template x-for="opt in (field.options || [])" :key="opt">
                            <option :value="opt" x-text="opt"></option>
                        </template>
                    </select>
                </template>

                <template x-if="field.uiType === 'image'">
                    <div>
                        <template x-if="blockContentDrafts[blockContentStepIndex][field.key + '_url']">
                            <div class="relative inline-block">
                                <img :src="blockContentDrafts[blockContentStepIndex][field.key + '_url']"
                                     class="rounded-lg max-h-48 object-cover border border-gray-200" />
                                <button type="button"
                                        @click="blockContentDrafts[blockContentStepIndex][field.key + '_file_id'] = null; blockContentDrafts[blockContentStepIndex][field.key + '_url'] = null"
                                        class="absolute top-2 right-2 bg-red-500 text-white rounded-full w-6 h-6 text-xs flex items-center justify-center hover:bg-red-600">✕</button>
                            </div>
                        </template>
                        <template x-if="!blockContentDrafts[blockContentStepIndex][field.key + '_url']">
                            <label :class="{'opacity-60': uploading}"
                                   class="flex flex-col items-center justify-center gap-2 rounded-xl border-2 border-dashed border-gray-300 bg-gray-50 p-6 cursor-pointer hover:border-brand-400 hover:bg-brand-50 transition-colors">
                                <span class="text-4xl">📷</span>
                                <span class="text-sm text-gray-500"><?= lang('Wizard.upload_image') ?></span>
                                <span class="text-xs text-gray-400"><?= lang('Wizard.upload_click_hint') ?></span>
                                <span x-show="uploading" class="text-xs text-brand-600"><?= lang('Wizard.upload_uploading') ?></span>
                                <input type="file" accept="image/*" class="hidden"
                                       @change="uploadBlockContentImage(blockContentStepIndex, field, $event.target.files[0])" />
                            </label>
                        </template>
                        <p x-show="uploadError" class="mt-1 text-xs text-red-600" x-text="uploadError"></p>
                    </div>
                </template>

                <!-- media reference (shared asset, lives in block_config — e.g. an "image"
                     or "hero_banner" block type's own picture, distinct from the `image`
                     uiType above which is a translatable schema field). -->
                <template x-if="field.uiType === 'media_reference'">
                    <div>
                        <template x-if="blockContentDrafts[blockContentStepIndex][field.key]?.url">
                            <div class="relative inline-block">
                                <img :src="blockContentDrafts[blockContentStepIndex][field.key].url"
                                     class="rounded-lg max-h-48 object-cover border border-gray-200" />
                                <button type="button"
                                        @click="clearBlockContentMediaReference(blockContentStepIndex, field)"
                                        class="absolute top-2 right-2 bg-red-500 text-white rounded-full w-6 h-6 text-xs flex items-center justify-center hover:bg-red-600">✕</button>
                            </div>
                        </template>
                        <template x-if="!blockContentDrafts[blockContentStepIndex][field.key]?.url">
                            <label :class="{'opacity-60': uploading}"
                                   class="flex flex-col items-center justify-center gap-2 rounded-xl border-2 border-dashed border-gray-300 bg-gray-50 p-6 cursor-pointer hover:border-brand-400 hover:bg-brand-50 transition-colors">
                                <span class="text-4xl">📷</span>
                                <span class="text-sm text-gray-500"><?= lang('Wizard.upload_image') ?></span>
                                <span class="text-xs text-gray-400"><?= lang('Wizard.upload_click_hint') ?></span>
                                <span x-show="uploading" class="text-xs text-brand-600"><?= lang('Wizard.upload_uploading') ?></span>
                                <input type="file" :accept="(field.accept || 'image') + '/*'" class="hidden"
                                       @change="uploadBlockContentMediaReference(blockContentStepIndex, field, $event.target.files[0])" />
                            </label>
                        </template>
                        <p x-show="uploadError" class="mt-1 text-xs text-red-600" x-text="uploadError"></p>
                    </div>
                </template>

                <template x-if="field.uiType === 'richtext'">
                    <div :data-wizard-content-richtext-field="blockContentStepIndex + ':' + field.key"
                         :data-field-key="field.key"
                         x-data="richTextEditor(blockContentDrafts[blockContentStepIndex][field.key] || '', '')"
                         x-init="init()"
                         class="border border-gray-300 rounded-lg overflow-hidden bg-white focus-within:ring-2 focus-within:ring-brand-500 focus-within:border-brand-500 transition-shadow">
                        <?= view('partials/richtext_toolbar') ?>
                        <div x-ref="editorEl" class="richtext-content px-3 py-2.5 min-h-[130px] text-sm text-gray-800 cursor-text"></div>
                        <input type="hidden"
                               x-ref="hiddenInput"
                               @input="syncBlockContentRichTextDraft(blockContentStepIndex, field.key, $event.target.value)">
                    </div>
                </template>

                <template x-if="field.uiType === 'textarea'">
                    <textarea x-model="blockContentDrafts[blockContentStepIndex][field.key]" rows="4"
                              class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-900 shadow-sm transition focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500"></textarea>
                </template>

                <template x-if="field.uiType === 'url'">
                    <input type="url" x-model="blockContentDrafts[blockContentStepIndex][field.key]" placeholder="https://"
                           class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-900 shadow-sm transition focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500" />
                </template>

                <template x-if="field.uiType === 'unsupported' || field.uiType === 'file' || field.uiType === 'datetime'">
                    <p class="rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-sm text-amber-800">
                        <?= lang('Wizard.wizard_content_unsupported_field') ?>
                    </p>
                </template>
            </div>
        </template>

        <!-- Navigation -->
        <div class="flex justify-between mt-6">
            <button @click="prevBlockStep()" class="btn-secondary"><?= lang('Wizard.btn_back') ?></button>
            <div class="flex gap-3">
                <button x-show="!blockContentSteps[blockContentStepIndex].required"
                        @click="skipBlockStep()" class="btn-secondary">
                    <?= lang('Wizard.btn_skip_block') ?>
                </button>
                <button @click="nextBlockStep()"
                        :disabled="!canAdvanceBlockStep()"
                        :class="canAdvanceBlockStep() ? 'btn-primary' : 'btn-primary opacity-50 cursor-not-allowed'">
                    <span x-show="!isLastUnifiedStep"><?= lang('Wizard.btn_next') ?></span>
                    <span x-show="isLastUnifiedStep"><?= lang('Wizard.btn_review') ?></span>
                </button>
            </div>
        </div>
    </div>
</template>

<!-- ── SCREEN: CONFIRM ── -->
<div x-show="screen === 'confirm'" x-cloak class="space-y-6">
    <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div class="space-y-2">
                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-brand-600"><?= lang('Wizard.wizard_content_confirm_badge') ?></p>
                <h2 class="text-2xl font-semibold text-gray-900"><?= lang('Wizard.wizard_content_confirm_title') ?></h2>
                <p class="max-w-3xl text-sm text-gray-600"><?= lang('Wizard.wizard_content_confirm_body') ?></p>
            </div>
            <span class="rounded-full bg-brand-50 px-3 py-1 text-xs font-semibold uppercase tracking-[0.16em] text-brand-700"><?= lang('Wizard.wizard_content_confirm_badge') ?></span>
        </div>

        <div class="mt-6 grid gap-4 lg:grid-cols-[1.05fr_0.95fr]">
            <section class="rounded-2xl border border-gray-200 bg-gray-50 p-5">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.16em] text-gray-500"><?= lang('Wizard.wizard_content_confirm_base_title') ?></p>
                        <p class="mt-1 text-sm text-gray-600"><?= lang('Wizard.wizard_content_confirm_base_help') ?></p>
                    </div>
                    <span class="text-3xl" x-text="selectedCollection?.icon ?? '📄'"></span>
                </div>

                <div class="mt-4 space-y-3 text-sm text-gray-700">
                    <p><strong><?= lang('Wizard.select_collection') ?>:</strong> <span class="ml-1" x-text="collectionDisplayLabel(selectedCollection)"></span></p>
                    <p><strong><?= lang('Wizard.status') ?>:</strong> <span class="ml-1" x-text="formData.status === 'published' ? '<?= esc(lang('Wizard.confirm_status_published'), 'js') ?>' : '<?= esc(lang('Wizard.confirm_status_draft'), 'js') ?>'"></span></p>
                    <template x-for="field in entryReviewPreviewFields()" :key="field.key">
                        <div class="rounded-xl border border-gray-200 bg-white px-4 py-3">
                            <p class="text-xs font-semibold uppercase tracking-[0.14em] text-gray-500" x-text="field.label"></p>
                            <template x-if="field.key === 'featured_image'">
                                <div class="mt-2">
                                    <img x-show="formData[field.key + '_url']" :src="formData[field.key + '_url']" class="max-h-32 rounded-lg border border-gray-200 object-cover" />
                                    <p x-show="!formData[field.key + '_url']" class="text-sm text-gray-400 italic"><?= lang('Wizard.confirm_no_value') ?></p>
                                </div>
                            </template>
                            <template x-if="field.key !== 'featured_image'">
                                <p class="mt-2 text-sm text-gray-800 whitespace-pre-line break-words" x-text="field.value || '<?= esc(lang('Wizard.confirm_no_value'), 'js') ?>'"></p>
                            </template>
                        </div>
                    </template>
                    <p class="text-sm text-gray-500"><?= lang('Wizard.wizard_content_confirm_review_hint') ?></p>
                </div>
            </section>

            <section class="relative overflow-hidden rounded-2xl border border-gray-200 bg-gray-50 p-5">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.16em] text-gray-500"><?= lang('Wizard.wizard_content_confirm_translations_title') ?></p>
                        <p class="mt-1 text-sm text-gray-600"><?= lang('Wizard.wizard_content_confirm_translations_help') ?></p>
                    </div>
                    <div class="rounded-full bg-white px-3 py-1 text-xs font-semibold uppercase tracking-[0.14em] text-gray-700 shadow-sm">
                        <span x-text="entryTranslationRows.length + 1"></span> <?= lang('Wizard.wizard_content_confirm_languages_ready') ?>
                    </div>
                </div>

                <div class="mt-4 space-y-3" :class="entryTranslationBusy() ? 'opacity-35 pointer-events-none select-none' : ''">
                    <div class="rounded-xl border border-brand-200 bg-white px-4 py-3">
                        <p class="text-xs font-semibold uppercase tracking-[0.14em] text-gray-500"><?= lang('Wizard.wizard_content_confirm_translation_auto') ?></p>
                        <div class="mt-2 grid gap-2 text-sm text-gray-700">
                            <p><strong><?= lang('Wizard.wizard_structure_summary_name') ?>:</strong> <span x-text="formData.title || '<?= esc(lang('Wizard.confirm_no_value'), 'js') ?>'"></span></p>
                            <p><strong><?= lang('Wizard.wizard_structure_summary_internal_slug') ?>:</strong> <span x-text="entryBaseSlug()"></span></p>
                        </div>
                    </div>

                    <template x-if="entryTranslationRows.length === 0 && !entryReviewLoading">
                        <div class="rounded-xl border border-dashed border-gray-300 bg-white px-4 py-4 text-sm text-gray-500">
                            <?= lang('Wizard.wizard_content_confirm_translations_empty') ?>
                        </div>
                    </template>

                    <template x-for="row in entryTranslationRows" :key="row.language_id">
                        <div class="rounded-xl border border-gray-200 bg-white p-4">
                            <div class="flex flex-wrap items-start justify-between gap-3">
                                <div>
                                    <p class="text-sm font-semibold text-gray-900" x-text="row.label"></p>
                                    <p class="text-xs text-gray-500" x-text="row.code"></p>
                                </div>
                                <span class="rounded-full bg-brand-50 px-2.5 py-1 text-[11px] font-semibold uppercase tracking-[0.14em] text-brand-700"><?= lang('Wizard.wizard_content_confirm_translation_auto') ?></span>
                            </div>
                            <div class="mt-3 grid gap-3 text-sm text-gray-700">
                                <p><strong><?= lang('Wizard.wizard_structure_summary_name') ?>:</strong> <span class="ml-1" x-text="row.title || '<?= esc(lang('Wizard.confirm_no_value'), 'js') ?>'"></span></p>
                                <p><strong><?= lang('Wizard.wizard_structure_summary_internal_slug') ?>:</strong> <span class="ml-1" x-text="row.slug || '<?= esc(lang('Wizard.confirm_no_value'), 'js') ?>'"></span></p>
                                <p x-show="row.excerpt" x-cloak><strong><?= lang('Wizard.wizard_structure_completion_hint_body_short') ?>:</strong> <span class="ml-1" x-text="row.excerpt"></span></p>
                            </div>
                            <p x-show="row.error" x-cloak class="mt-3 rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-xs text-amber-800" x-text="row.error"></p>
                        </div>
                    </template>
                </div>

                <div x-show="entryReviewLoading" x-cloak class="absolute inset-0 z-10 flex items-center justify-center bg-white/75 px-6 py-8 backdrop-blur-[1px]" aria-live="polite">
                    <div class="w-full max-w-lg rounded-2xl border border-brand-200 bg-brand-50 px-5 py-4 text-brand-800 shadow-lg">
                        <div class="flex items-start gap-4">
                            <div class="mt-0.5 flex h-11 w-11 items-center justify-center rounded-full bg-white text-brand-600 shadow-sm">
                                <svg class="h-5 w-5 animate-spin" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                    <circle cx="12" cy="12" r="9" class="opacity-20" stroke="currentColor" stroke-width="3"></circle>
                                    <path d="M21 12a9 9 0 0 1-9 9" class="opacity-90" stroke="currentColor" stroke-linecap="round" stroke-width="3"></path>
                                </svg>
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="text-base font-semibold"><?= lang('Wizard.wizard_content_confirm_translations_loading') ?></p>
                                <p class="mt-1 text-sm leading-6 text-brand-700"><?= lang('Wizard.wizard_content_confirm_body') ?></p>
                            </div>
                        </div>
                    </div>
                </div>

                <p x-show="entryReviewError" x-cloak class="mt-4 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800" x-text="entryReviewError"></p>
            </section>
        </div>

        <p x-show="publishError" class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700" x-text="publishError"></p>

        <div class="flex flex-wrap gap-3">
            <button @click="formData.status = 'draft'; publish()" class="btn-secondary" :disabled="publishing || entryTranslationBusy()">
                <?= lang('Wizard.btn_draft') ?>
            </button>
            <button @click="formData.status = 'published'; publish()" class="btn-primary" :disabled="publishing || entryTranslationBusy()">
                <span x-show="!publishing"><?= lang('Wizard.btn_publish') ?></span>
                <span x-show="publishing"><?= lang('Wizard.btn_publishing') ?></span>
            </button>
            <button @click="blockContentSteps.length > 0 ? (blockContentStepIndex = blockContentSteps.length - 1, screen = 'block-steps') : (currentStep = steps.length - 1, screen = 'steps')" class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700">
                <?= lang('Wizard.btn_back') ?>
            </button>
        </div>
    </div>
</div>

<!-- ── SCREEN: SUCCESS ── -->
<div x-show="screen === 'success'" x-cloak class="rounded-2xl border border-gray-200 bg-white p-8 shadow-sm">
    <div class="flex flex-wrap items-start justify-between gap-4">
        <div class="space-y-2">
            <p class="text-xs font-semibold uppercase tracking-[0.18em] text-green-600">
                <?= lang('Wizard.wizard_content_success_kicker') ?>
            </p>
            <h2 class="text-2xl font-semibold text-gray-900"
                x-text="formData.status === 'draft' ? '<?= lang('Wizard.success_title_draft') ?>' : '<?= lang('Wizard.success_title') ?>'"></h2>
            <p class="max-w-3xl text-sm text-gray-600">
                <span x-text="formData.title || '<?= esc(lang('Wizard.confirm_no_value'), 'js') ?>'"></span>
                <span x-text="formData.status === 'draft' ? '<?= lang('Wizard.success_subtitle_draft') ?>' : '<?= lang('Wizard.success_subtitle') ?>'"></span>
            </p>
        </div>
        <div class="rounded-full bg-green-50 px-3 py-1 text-xs font-semibold uppercase tracking-[0.16em] text-green-700">
            <?= lang('Wizard.wizard_content_success_ready_badge') ?>
        </div>
    </div>

    <div class="mt-6 grid gap-4 lg:grid-cols-[1.1fr_0.9fr]">
        <section class="rounded-2xl border border-gray-200 bg-gray-50 p-5">
            <p class="text-xs font-semibold uppercase tracking-[0.16em] text-gray-500">
                <?= lang('Wizard.wizard_content_success_next_steps_title') ?>
            </p>
            <div class="mt-4 space-y-3 text-sm text-gray-700">
                <p class="rounded-xl border border-gray-200 bg-white px-4 py-3">
                    <?= lang('Wizard.wizard_content_success_step_detail') ?>
                </p>
                <p x-show="blockContentSteps.length === 0" class="rounded-xl border border-gray-200 bg-white px-4 py-3">
                    <?= lang('Wizard.wizard_content_success_step_blocks') ?>
                </p>
                <p x-show="blockContentSteps.length > 0 && publishBlockWarnings.length === 0" x-cloak
                   class="rounded-xl border border-gray-200 bg-white px-4 py-3">
                    <?= lang('Wizard.wizard_content_success_step_blocks_filled') ?>
                </p>
                <p x-show="publishBlockWarnings.length > 0" x-cloak
                   class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-amber-800">
                    <?= lang('Wizard.wizard_content_success_block_warning_intro') ?>
                    <span class="font-medium" x-text="publishBlockWarnings.map(w => w.label).join(', ')"></span>.
                    <a :href="'<?= site_url('admin/cms/entries') ?>/' + publishedEntry?.id + '/blocks'" class="underline font-medium">
                        <?= lang('Wizard.wizard_content_success_block_warning_link') ?>
                    </a>
                </p>
                <p class="rounded-xl border border-gray-200 bg-white px-4 py-3">
                    <?= lang('Wizard.wizard_content_success_step_more') ?>
                </p>
            </div>
        </section>

        <section class="rounded-2xl border border-gray-200 bg-white p-5">
            <p class="text-xs font-semibold uppercase tracking-[0.16em] text-gray-500">
                <?= lang('Wizard.wizard_content_success_actions_title') ?>
            </p>
            <div class="mt-4 flex flex-col gap-3">
                <a x-show="publishedEntry?.public_url"
                   :href="publishedEntry?.public_url ?? '#'" target="_blank" class="btn-primary justify-center">
                    <?= lang('Wizard.btn_view_site') ?>
                </a>
                <a x-show="publishedEntry?.id"
                   :href="'<?= site_url('admin/cms/entries') ?>/' + publishedEntry?.id + '/edit'"
                   class="btn-secondary justify-center">
                    <?= lang('Wizard.btn_edit_entry') ?>
                </a>
                <button @click="restart()" class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">
                    <?= lang('Wizard.btn_add_more') ?>
                </button>
            </div>
        </section>
    </div>
</div>
