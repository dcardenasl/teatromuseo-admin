<?php /* Wizard — B3: Block editor (edit existing + create new) */ ?>

<!-- ── SCREEN: BLOCK EDIT (B3) ── -->
<div x-show="screen === 'block-edit'" x-cloak>

    <!-- Breadcrumb -->
    <div class="flex flex-wrap items-center gap-2 mb-5">
        <button @click="editMode === 'create' ? screen = 'block-catalog' : screen = 'page-blocks'"
                class="text-sm text-gray-500 hover:text-gray-700"><?= lang('Wizard.btn_back') ?></button>
        <span class="text-gray-300">/</span>
        <div>
            <h2 class="text-lg font-semibold text-gray-900" x-text="blockEditTitle()"></h2>
            <p class="text-xs text-gray-500 mt-0.5" x-show="editParentBlock">
                <?= lang('Wizard.block_edit_child_of') ?>
                <span x-text="blockLabel(editParentBlock, 0)" class="font-medium"></span>
            </p>
            <span x-show="editMode === 'create'"
                  class="inline-block text-xs bg-green-50 text-green-600 border border-green-200 rounded px-1.5 py-0.5 mt-1">
                <?= lang('Wizard.block_edit_new_badge') ?>
            </span>
        </div>
    </div>

    <!-- No fields notice -->
    <template x-if="blockFields().length === 0">
        <p class="text-sm text-gray-400 py-6 text-center"><?= lang('Wizard.no_block_fields') ?></p>
    </template>

    <!-- Field list -->
    <template x-for="field in blockFields()" :key="field.key">
        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-1"
                   x-text="field.label + (field.required ? '<?= lang('Wizard.required_suffix') ?>' : '')"></label>

            <!-- string / text -->
            <template x-if="field.uiType === 'text'">
                <input type="text"
                       x-model="blockEditData[field.key]"
                class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-900 shadow-sm transition focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500" />
            </template>

            <!-- date -->
            <template x-if="field.uiType === 'date'">
                <input type="date"
                       x-model="blockEditData[field.key]"
                class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-900 shadow-sm transition focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500" />
            </template>

            <!-- number -->
            <template x-if="field.uiType === 'number'">
                <input type="number"
                       x-model="blockEditData[field.key]"
                class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-900 shadow-sm transition focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500" />
            </template>

            <!-- boolean (select) -->
            <template x-if="field.uiType === 'boolean'">
                <select x-model="blockEditData[field.key]"
                class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-900 shadow-sm transition focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500">
                    <option value="1"><?= lang('Wizard.bool_yes') ?></option>
                    <option value="0"><?= lang('Wizard.bool_no') ?></option>
                </select>
            </template>

            <!-- select -->
            <template x-if="field.uiType === 'select'">
                <select x-model="blockEditData[field.key]"
                class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-900 shadow-sm transition focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500">
                    <template x-for="opt in (field.options || [])" :key="opt">
                        <option :value="opt" x-text="opt"></option>
                    </template>
                </select>
            </template>

            <!-- image upload -->
            <template x-if="field.uiType === 'image'">
                <div>
                    <template x-if="blockEditData[field.key + '_url']">
                        <div class="relative inline-block">
                            <img :src="blockEditData[field.key + '_url']"
                                 class="rounded-lg max-h-48 object-cover border border-gray-200" />
                            <button type="button"
                                    @click="blockEditData[field.key + '_file_id'] = null; blockEditData[field.key + '_url'] = null"
                                    class="absolute top-2 right-2 bg-red-500 text-white rounded-full w-6 h-6 text-xs flex items-center justify-center hover:bg-red-600">✕</button>
                        </div>
                    </template>
                    <template x-if="!blockEditData[field.key + '_url']">
                        <label :class="{'opacity-60': uploading}"
                               class="flex flex-col items-center justify-center gap-2 rounded-xl border-2 border-dashed border-gray-300 bg-gray-50 p-6 cursor-pointer hover:border-brand-400 hover:bg-brand-50 transition-colors">
                            <span class="text-4xl">📷</span>
                            <span class="text-sm text-gray-500"><?= lang('Wizard.upload_image') ?></span>
                            <span class="text-xs text-gray-400"><?= lang('Wizard.upload_click_hint') ?></span>
                            <span x-show="uploading" class="text-xs text-brand-600"><?= lang('Wizard.upload_uploading') ?></span>
                            <input type="file" accept="image/*" class="hidden"
                                   @change="uploadBlockImage(field, $event.target.files[0])" />
                        </label>
                    </template>
                    <p x-show="uploadError" class="mt-1 text-xs text-red-600" x-text="uploadError"></p>
                </div>
            </template>

            <!-- media reference (shared asset, lives in block_config) -->
            <template x-if="field.uiType === 'media_reference'">
                <div>
                    <template x-if="blockEditConfig[field.key]?.url">
                        <div class="relative inline-block">
                            <img :src="blockEditConfig[field.key].url"
                                 class="rounded-lg max-h-48 object-cover border border-gray-200" />
                            <button type="button"
                                    @click="clearBlockMediaReference(field)"
                                    class="absolute top-2 right-2 bg-red-500 text-white rounded-full w-6 h-6 text-xs flex items-center justify-center hover:bg-red-600">✕</button>
                        </div>
                    </template>
                    <template x-if="!blockEditConfig[field.key]?.url">
                        <label :class="{'opacity-60': uploading}"
                               class="flex flex-col items-center justify-center gap-2 rounded-xl border-2 border-dashed border-gray-300 bg-gray-50 p-6 cursor-pointer hover:border-brand-400 hover:bg-brand-50 transition-colors">
                            <span class="text-4xl">📷</span>
                            <span class="text-sm text-gray-500"><?= lang('Wizard.upload_image') ?></span>
                            <span class="text-xs text-gray-400"><?= lang('Wizard.upload_click_hint') ?></span>
                            <span x-show="uploading" class="text-xs text-brand-600"><?= lang('Wizard.upload_uploading') ?></span>
                            <input type="file" :accept="(field.accept || 'image') + '/*'" class="hidden"
                                   @change="uploadBlockMediaReference(field, $event.target.files[0])" />
                        </label>
                    </template>
                    <p x-show="uploadError" class="mt-1 text-xs text-red-600" x-text="uploadError"></p>
                </div>
            </template>

            <!-- richtext / textarea / url / fallback -->
            <template x-if="field.uiType === 'richtext'">
                <div :data-wizard-richtext-field="field.key"
                     :data-field-key="field.key"
                     x-data="richTextEditor(blockEditData[field.key] || '', '')"
                     x-init="init()"
                    class="border border-gray-300 rounded-lg overflow-hidden bg-white focus-within:ring-2 focus-within:ring-brand-500 focus-within:border-brand-500 transition-shadow">
                    <?= view('partials/richtext_toolbar') ?>
                    <div x-ref="editorEl" class="richtext-content px-3 py-2.5 min-h-[130px] text-sm text-gray-800 cursor-text"></div>
                    <input type="hidden" x-ref="hiddenInput">
                </div>
            </template>

            <template x-if="field.uiType === 'textarea'">
                <textarea x-model="blockEditData[field.key]"
                          rows="3"
                          class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-900 shadow-sm transition focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500"></textarea>
            </template>

            <!-- url -->
            <template x-if="field.uiType === 'url'">
                <input type="url"
                       x-model="blockEditData[field.key]"
                       placeholder="https://"
                       class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-900 shadow-sm transition focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500" />
            </template>
        </div>
    </template>

    <p x-show="blockSaveError" class="text-red-600 text-sm mt-2" x-text="blockSaveError"></p>

    <div class="flex gap-3 mt-6">
        <button @click="editMode === 'create' ? screen = 'block-catalog' : screen = 'page-blocks'"
                class="btn-secondary"><?= lang('Wizard.btn_back') ?></button>
        <button @click="saveBlock()" :disabled="blockSaving" class="btn-primary">
            <template x-if="editMode === 'create'">
                <span>
                    <span x-show="!blockSaving"><?= lang('Wizard.btn_create_block') ?></span>
                    <span x-show="blockSaving"><?= lang('Wizard.btn_saving') ?></span>
                </span>
            </template>
            <template x-if="editMode !== 'create'">
                <span>
                    <span x-show="!blockSaving"><?= lang('Wizard.btn_save_block') ?></span>
                    <span x-show="blockSaving"><?= lang('Wizard.btn_saving') ?></span>
                </span>
            </template>
        </button>
    </div>
</div>
