<?php $setting = $setting ?? []; ?>
<div class="mb-4">
    <a href="<?= route_to('admin.cms.settings') ?>" class="text-sm text-brand-600 hover:text-brand-700">&larr; <?= lang('Settings.settings_title') ?></a>
</div>

<?php if (! empty($error)): ?>
    <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-5">
        <p class="text-sm text-red-600"><?= esc($error) ?></p>
    </div>
<?php elseif (! empty($setting)): ?>
    <?php $itemId = (string) ($setting['id'] ?? ''); ?>
    <?php $isTrans = ! empty($setting['is_translatable']); ?>
    <?php if ($isTrans): ?>
        <?= view('components/table/translation_status_panel', ['languages' => $languages ?? [], 'translations' => $setting['translations'] ?? [], 'requiredFields' => ['setting_value'], 'sourceFields' => $setting, 'sourceUpdatedAt' => $setting['updated_at'] ?? null, 'editUrlTemplate' => route_to('admin.cms.settings.edit', $itemId)]) ?>
    <?php endif; ?>

    <div class="space-y-6">
        <!-- Top Title and Actions -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white border border-gray-200 rounded-xl shadow-sm p-6">
            <div>
                <span class="text-xs font-semibold uppercase tracking-wider text-gray-400">Configuración</span>
                <h2 class="text-xl font-bold text-gray-900 mt-0.5"><?= esc($setting['setting_key'] ?? '—') ?></h2>
            </div>
            <div class="flex items-center gap-2">
                <?php if (has_permission('cms.settings.write')): ?>
                    <a href="<?= route_to('admin.cms.settings.edit', $itemId) ?>" class="<?= esc(action_button_class('primary')) ?>">
                        <?= esc(lang('App.edit')) ?>
                    </a>
                    
                    <form method="post" action="<?= route_to('admin.cms.settings.delete', $itemId) ?>" x-data @submit.prevent="$store.confirm.show('<?= esc(confirm_delete_message($setting['setting_key'] ?? $setting['setting_group'] ?? null), 'js') ?>', () => $el.submit())">
                        <?= csrf_field() ?>
                        <button type="submit" class="<?= esc(action_button_class('danger')) ?>">
                            <?= ui_icon('trash', 'h-3.5 w-3.5') ?>
                            <?= esc(lang('App.delete')) ?>
                        </button>
                    </form>
                <?php endif; ?>
            </div>
        </div>

        <!-- Properties Card -->
        <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-6 space-y-4">
            <h3 class="text-sm font-bold uppercase tracking-wider text-gray-400 border-b border-gray-100 pb-3">Propiedades del Sistema</h3>
            
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 text-sm">
                <div>
                    <dt class="text-xs font-semibold uppercase tracking-wider text-gray-400"><?= lang('Settings.field_setting_type') ?></dt>
                    <dd class="mt-1.5">
                        <span class="inline-flex items-center rounded-md bg-gray-50 px-2.5 py-1 text-xs font-semibold text-gray-600 ring-1 ring-inset ring-gray-500/10">
                            <?= esc($setting['setting_type'] ?? 'string') ?>
                        </span>
                    </dd>
                </div>
                <div>
                    <dt class="text-xs font-semibold uppercase tracking-wider text-gray-400"><?= lang('Settings.field_setting_group') ?></dt>
                    <dd class="mt-2 text-sm text-gray-900 font-medium"><?= esc($setting['setting_group'] ?? '—') ?></dd>
                </div>
                <div>
                    <dt class="text-xs font-semibold uppercase tracking-wider text-gray-400"><?= lang('Settings.field_is_translatable') ?></dt>
                    <dd class="mt-1.5">
                        <span class="<?= $isTrans ? 'bg-green-50 text-green-700 border-green-200' : 'bg-red-50 text-red-700 border-red-200' ?> inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-bold border">
                            <?= $isTrans ? lang('App.yes') : lang('App.no') ?>
                        </span>
                    </dd>
                </div>
                <div>
                    <dt class="text-xs font-semibold uppercase tracking-wider text-gray-400"><?= lang('TableColumns.created_at') ?></dt>
                    <dd class="mt-2 text-sm text-gray-900"><?= esc((string) ($setting['created_at'] ?? '-')) ?></dd>
                </div>
            </div>
            
            <?php if (!empty($setting['description'])): ?>
                <div class="border-t border-gray-100 pt-4">
                    <dt class="text-xs font-semibold uppercase tracking-wider text-gray-400"><?= lang('Settings.field_description') ?></dt>
                    <dd class="mt-1.5 text-sm text-gray-600 italic"><?= esc($setting['description']) ?></dd>
                </div>
            <?php endif; ?>
        </div>

        <!-- Value / Translations Card -->
        <div class="space-y-6">
            <!-- Static Value Card -->
            <?php if (!$isTrans): ?>
                <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-6 space-y-4">
                    <div class="border-b border-gray-100 pb-3 flex items-center justify-between">
                        <h3 class="text-sm font-bold uppercase tracking-wider text-gray-400"><?= lang('Settings.field_setting_value') ?></h3>
                        <span class="inline-flex items-center rounded-md bg-blue-50 px-2 py-1 text-xs font-semibold text-blue-700 ring-1 ring-inset ring-blue-700/10">Estático</span>
                    </div>

                    <div class="p-4 bg-gray-50 rounded-xl border border-gray-100 font-medium text-gray-900">
                        <?php if (($setting['setting_type'] ?? '') === 'bool'): ?>
                            <span class="<?= ($setting['setting_value'] === '1' || $setting['setting_value'] === 'true' || $setting['setting_value'] === true) ? 'text-green-700 bg-green-50 border-green-200' : 'text-red-700 bg-red-50 border-red-200' ?> inline-flex items-center rounded-full px-2.5 py-1 text-xs font-bold border">
                                <?= ($setting['setting_value'] === '1' || $setting['setting_value'] === 'true' || $setting['setting_value'] === true) ? lang('App.yes') : lang('App.no') ?>
                            </span>
                        <?php elseif (($setting['setting_type'] ?? '') === 'json'): ?>
                            <pre class="font-mono text-xs bg-white p-3 rounded-lg border overflow-x-auto text-gray-800"><?= esc(json_encode(json_decode($setting['setting_value'] ?? '{}'), JSON_PRETTY_PRINT)) ?></pre>
                        <?php else: ?>
                            <div class="text-sm break-all"><?= esc($setting['setting_value'] ?? '—') ?></div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Translations Value Card -->
            <?php if ($isTrans && !empty($setting['translations'])): ?>
                <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-6 space-y-4">
                    <div class="border-b border-gray-100 pb-3 flex items-center justify-between">
                        <h3 class="text-sm font-bold uppercase tracking-wider text-gray-400"><?= esc(lang('Settings.settings_translations')) ?></h3>
                        <span class="inline-flex items-center rounded-md bg-green-50 px-2 py-1 text-xs font-semibold text-green-700 ring-1 ring-inset ring-green-600/20">Traducible</span>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <?php foreach ($setting['translations'] as $t): ?>
                            <div class="p-4 bg-gray-50 rounded-xl border border-gray-100 space-y-2">
                                <div class="flex items-center gap-2">
                                    <span class="inline-flex items-center justify-center font-bold px-2 py-0.5 rounded bg-blue-50 text-blue-700 text-xs border border-blue-200">
                                        <?= esc(strtoupper($t['language_code'] ?? '')) ?>
                                    </span>
                                    <span class="text-xs font-bold text-gray-700"><?= esc($t['language_name'] ?? '') ?></span>
                                </div>
                                <div class="font-medium text-gray-900 pt-1">
                                    <?php if (($setting['setting_type'] ?? '') === 'bool'): ?>
                                        <span class="<?= ($t['setting_value'] === '1' || $t['setting_value'] === 'true' || $t['setting_value'] === true) ? 'text-green-700 bg-green-50' : 'text-red-700 bg-red-50' ?> inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold">
                                            <?= ($t['setting_value'] === '1' || $t['setting_value'] === 'true' || $t['setting_value'] === true) ? lang('App.yes') : lang('App.no') ?>
                                        </span>
                                    <?php elseif (($setting['setting_type'] ?? '') === 'json'): ?>
                                        <pre class="font-mono text-xs bg-white p-3 rounded-lg border overflow-x-auto text-gray-800"><?= esc(json_encode(json_decode($t['setting_value'] ?? '{}'), JSON_PRETTY_PRINT)) ?></pre>
                                    <?php else: ?>
                                        <div class="text-sm break-all font-medium text-gray-800"><?= esc($t['setting_value'] ?? '—') ?></div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
<?php endif; ?>
