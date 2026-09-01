<?php
$title ??= '';
$subtitle ??= null;
$actionsView ??= null;
$showViewToggle = (bool) ($showViewToggle ?? false);
$showDensityToggle = (bool) ($showDensityToggle ?? false);
$toggleButtonClass = 'inline-flex items-center justify-center gap-1.5 rounded-md border px-2.5 py-1.5 text-xs font-medium transition focus:outline-none focus:ring-2 focus:ring-brand-500';
?>
<div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
    <div>
        <?php if ($title !== ''): ?>
            <h3 class="text-lg font-semibold text-gray-900"><?= esc($title) ?></h3>
        <?php endif; ?>
        <?php if (is_string($subtitle) && $subtitle !== ''): ?>
            <p class="mt-1 text-sm text-gray-500"><?= esc($subtitle) ?></p>
        <?php endif; ?>
    </div>

    <?php if ($showViewToggle || $showDensityToggle || (is_string($actionsView) && $actionsView !== '')): ?>
        <div class="flex flex-wrap items-center justify-end gap-2">
            <?php if ($showViewToggle): ?>
                <div class="inline-flex items-center gap-1" role="group" aria-label="<?= esc(lang('App.table_view')) ?>">
                    <button type="button"
                            @click="setViewMode('table')"
                            :class="viewMode === 'table' ? 'border-brand-600 bg-brand-600 text-white' : 'border-gray-200 bg-white text-gray-600 hover:bg-gray-50'"
                            :aria-pressed="viewMode === 'table'"
                            class="<?= esc($toggleButtonClass) ?>"
                            title="<?= esc(lang('App.view_table')) ?>"
                            aria-label="<?= esc(lang('App.view_table')) ?>">
                        <?= ui_icon('list', 'h-4 w-4') ?>
                        <span class="hidden sm:inline"><?= esc(lang('App.view_table')) ?></span>
                    </button>
                    <button type="button"
                            @click="setViewMode('grid')"
                            :class="viewMode === 'grid' ? 'border-brand-600 bg-brand-600 text-white' : 'border-gray-200 bg-white text-gray-600 hover:bg-gray-50'"
                            :aria-pressed="viewMode === 'grid'"
                            class="<?= esc($toggleButtonClass) ?>"
                            title="<?= esc(lang('App.view_grid')) ?>"
                            aria-label="<?= esc(lang('App.view_grid')) ?>">
                        <?= ui_icon('grid', 'h-4 w-4') ?>
                        <span class="hidden sm:inline"><?= esc(lang('App.view_grid')) ?></span>
                    </button>
                </div>
            <?php endif; ?>

            <?php if ($showDensityToggle): ?>
                <div class="inline-flex items-center gap-1" role="group" aria-label="<?= esc(lang('App.table_density')) ?>">
                    <?php foreach (['sm' => 'App.density_sm', 'md' => 'App.density_md', 'lg' => 'App.density_lg'] as $density => $labelKey): ?>
                        <button type="button"
                                @click="setDensity('<?= esc($density) ?>')"
                                :class="density === '<?= esc($density) ?>' ? 'border-brand-600 bg-brand-600 text-white' : 'border-gray-200 bg-white text-gray-600 hover:bg-gray-50'"
                                :aria-pressed="density === '<?= esc($density) ?>'"
                                class="<?= esc($toggleButtonClass) ?>"
                                title="<?= esc(lang($labelKey)) ?>"
                                aria-label="<?= esc(lang($labelKey)) ?>">
                            <?= esc(strtoupper($density)) ?>
                        </button>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <?php if (is_string($actionsView) && $actionsView !== ''): ?>
                <div class="flex items-center gap-2">
                    <?= $this->include($actionsView) ?>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>
