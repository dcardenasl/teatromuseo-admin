<?php /** @var array $limitOptions */ ?>

<div class="mt-3 grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-3">
    <div>
        <label class="<?= esc(filter_label_class()) ?>"><?= lang('App.search') ?></label>
        <input type="text" name="search" value="<?= esc((string) request()->getGet('search')) ?>"
            placeholder="<?= esc(lang('Settings.settings_search_placeholder')) ?>"
            class="<?= esc(filter_input_class()) ?>" data-table-debounce="350">
    </div>

    <div>
        <label class="<?= esc(filter_label_class()) ?>"><?= lang('Settings.field_setting_group') ?></label>
        <select name="setting_group" class="<?= esc(filter_input_class()) ?>">
            <option value=""><?= lang('BlockTypes.filter_all_groups') ?></option>
            <?php foreach (['identity', 'contact', 'integration', 'analytics', 'social'] as $group): ?>
                <option value="<?= esc($group) ?>" <?= request()->getGet('setting_group') === $group ? 'selected' : '' ?>><?= esc(lang('Settings.group_' . $group)) ?></option>
            <?php endforeach ?>
        </select>
    </div>

    <?= view('layouts/partials/filter_limit', ['limitOptions' => $limitOptions ?? [10, 25, 50, 100]]) ?>
</div>
