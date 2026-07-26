<?php
/** @var list<array<string, mixed>> $languages */
?>

<div class="mb-5 flex items-center gap-3">
    <a href="<?= route_to('admin.cms.forms') ?>" class="text-gray-400 hover:text-gray-600">
        <i data-lucide="arrow-left" class="h-5 w-5"></i>
    </a>
    <h1 class="text-xl font-semibold text-gray-900"><?= lang('Forms.create_title') ?></h1>
</div>

<form method="post" action="<?= route_to('admin.cms.forms.store') ?>" class="space-y-6">
    <?= csrf_field() ?>

    <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
        <h2 class="mb-4 text-base font-semibold text-gray-800"><?= lang('Forms.section_general') ?></h2>
        <div class="grid gap-4 sm:grid-cols-2">
            <div>
                <label class="mb-1.5 block text-sm font-medium text-gray-700"><?= lang('Forms.field_key') ?> <span class="text-red-500">*</span></label>
                <input type="text" name="form_key" required
                       placeholder="contact"
                       pattern="[a-zA-Z0-9_\-]+"
                       class="<?= input_class('form_key') ?> w-full"
                       value="<?= esc(old('form_key', '')) ?>">
                <p class="mt-1 text-xs text-gray-400"><?= lang('Forms.field_key_hint') ?></p>
            </div>
            <div class="flex items-end gap-6">
                <label class="flex items-center gap-2 text-sm">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" value="1" checked class="rounded border-gray-300">
                    <?= lang('Forms.field_active') ?>
                </label>
                <div>
                    <label class="flex items-center gap-2 text-sm">
                        <input type="hidden" name="has_captcha" value="0">
                        <input type="checkbox" name="has_captcha" value="1" class="rounded border-gray-300">
                        <?= lang('Forms.field_captcha') ?>
                    </label>
                    <p class="mt-1 text-xs text-gray-400"><?= lang('Forms.field_captcha_hint') ?></p>
                </div>
            </div>
        </div>

        <div class="mt-4 grid gap-4 sm:grid-cols-2">
            <div>
                <label class="mb-1.5 block text-sm font-medium text-gray-700"><?= lang('Forms.field_notify_email') ?></label>
                <input type="email" name="notify_email" class="<?= input_class('notify_email') ?> w-full" placeholder="admin@example.com" value="<?= esc(old('notify_email', '')) ?>">
                <p class="mt-1 text-xs text-gray-400"><?= lang('Forms.field_notify_email_hint') ?></p>
            </div>
            <div class="space-y-3">
                <label class="flex items-center gap-2 text-sm">
                    <input type="hidden" name="autoreply_enabled" value="0">
                    <input type="checkbox" name="autoreply_enabled" value="1" class="rounded border-gray-300">
                    <?= lang('Forms.field_autoreply') ?>
                </label>
                <div>
                    <label class="mb-1.5 block text-xs font-medium text-gray-600"><?= lang('Forms.field_autoreply_email_field') ?></label>
                    <input type="text" name="autoreply_email_field" class="<?= input_class('autoreply_email_field') ?> w-full text-sm" placeholder="email" value="<?= esc(old('autoreply_email_field', '')) ?>">
                    <p class="mt-1 text-xs text-gray-400"><?= lang('Forms.field_autoreply_email_field_hint') ?></p>
                </div>
            </div>
        </div>
    </div>

        <?php if (!empty($languages)): ?>
            <?php
            $defaultLangId = (int) ($defaultLangId ?? 0);
            $defaultLangCode = (string) ($defaultLangCode ?? '');
            $defaultLangIndex = (int) ($defaultLangIndex ?? 0);
            $translateUrl = route_to('admin.cms.translate');
            ?>
        <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
            <h2 class="mb-4 text-base font-semibold text-gray-800"><?= lang('Forms.section_translations') ?></h2>

            <div x-data="langTabs(<?= $defaultLangId ?>, '<?= esc($translateUrl, 'attr') ?>', '<?= esc($defaultLangCode, 'attr') ?>')">
                <div class="mb-4 flex items-center justify-between border-b border-gray-200">
                    <div class="flex gap-1">
                        <?php foreach ($languages as $lang): ?>
                            <button type="button"
                                    role="tab"
                                    @click="setTab(<?= (int) $lang['id'] ?>)"
                                    :aria-selected="isActive(<?= (int) $lang['id'] ?>)"
                                    :class="isActive(<?= (int) $lang['id'] ?>) ? 'border-brand-600 text-brand-600' : 'border-transparent text-gray-500 hover:text-gray-700'"
                                    class="border-b-2 px-4 py-2 text-sm font-medium transition-colors">
                                <?= esc(strtoupper($lang['code'])) ?>
                            </button>
                        <?php endforeach; ?>
                    </div>
                    <?php if (!empty($translateTargets)): ?>
                    <button type="button"
                        @click="autoTranslateAll(<?= esc(json_encode($translateTargets, JSON_THROW_ON_ERROR), 'attr') ?>)"
                        :disabled="translating || translatingAll"
                        class="inline-flex items-center gap-1.5 text-xs text-brand-600 hover:text-brand-700 border border-brand-200 rounded px-3 py-1.5 bg-brand-50 hover:bg-brand-100 transition-colors disabled:opacity-50">
                        <span x-show="!translatingAll"><?= ui_icon('languages', 'h-3.5 w-3.5') ?> <?= esc(lang('App.translate_all')) ?></span>
                        <span x-show="translatingAll" x-cloak><?= ui_icon('loader', 'h-3.5 w-3.5 animate-spin') ?> <span x-text="translateAllProgress"></span></span>
                    </button>
                    <?php endif; ?>
                </div>

                <!-- Translate error message -->
                <p x-show="translateError !== ''" x-text="translateError" x-cloak class="mb-3 text-xs text-red-600 bg-red-50 border border-red-200 rounded px-3 py-2"></p>

                <?php foreach ($languages as $idx => $language): ?>
                    <?php $isDefault = ((int) $language['id'] === $defaultLangId || !empty($language['is_default'])); ?>
                    <div x-show="isActive(<?= (int) $language['id'] ?>)" class="space-y-4">
                        <input type="hidden" name="translations[<?= $idx ?>][language_id]" value="<?= (int) $language['id'] ?>">
                        <div class="grid gap-4 sm:grid-cols-2">
                            <div>
                                <label class="mb-1.5 block text-sm font-medium text-gray-700"><?= lang('Forms.field_name') ?> <span class="text-red-500">*</span></label>
                                <input type="text" name="translations[<?= $idx ?>][name]" class="<?= input_class('translations.' . $idx . '.name') ?> w-full" <?= $isDefault ? 'required' : '' ?>>
                            </div>
                            <div>
                                <label class="mb-1.5 block text-sm font-medium text-gray-700"><?= lang('Forms.field_submit_label') ?></label>
                                <input type="text" name="translations[<?= $idx ?>][submit_label]" class="<?= input_class('translations.' . $idx . '.submit_label') ?> w-full" value="Enviar">
                            </div>
                        </div>
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-gray-700"><?= lang('Forms.field_description') ?></label>
                            <textarea name="translations[<?= $idx ?>][description]" rows="2" class="<?= input_class('translations.' . $idx . '.description') ?> block w-full resize-none"></textarea>
                        </div>
                        <div class="grid gap-4 sm:grid-cols-2">
                            <div>
                                <label class="mb-1.5 block text-sm font-medium text-gray-700"><?= lang('Forms.field_success_message') ?></label>
                                <textarea name="translations[<?= $idx ?>][success_message]" rows="2" class="<?= input_class('translations.' . $idx . '.success_message') ?> block w-full resize-none"></textarea>
                            </div>
                            <div>
                                <label class="mb-1.5 block text-sm font-medium text-gray-700"><?= lang('Forms.field_error_message') ?></label>
                                <textarea name="translations[<?= $idx ?>][error_message]" rows="2" class="<?= input_class('translations.' . $idx . '.error_message') ?> block w-full resize-none"></textarea>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>

    <div class="flex items-center justify-end gap-3">
        <a href="<?= route_to('admin.cms.forms') ?>" class="btn btn-secondary"><?= lang('Forms.btn_cancel') ?></a>
        <button type="submit" class="btn btn-primary"><?= lang('Forms.btn_save') ?></button>
    </div>
</form>
