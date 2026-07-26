<?php /** @var array $limitOptions */ ?>

<div class="mt-3 grid grid-cols-1 md:grid-cols-3 xl:grid-cols-4 gap-3">
    <div class="md:col-span-2">
        <label class="<?= esc(filter_label_class()) ?>"><?= lang('App.search') ?></label>
        <input type="text" name="search" value="<?= esc((string) request()->getGet('search')) ?>"
            placeholder="<?= esc(lang('Menus.menus_search_placeholder')) ?>"
            class="<?= esc(filter_input_class()) ?>" data-table-debounce="350">
    </div>

    <div>
        <label class="<?= esc(filter_label_class()) ?>"><?= lang('Menus.menus_filter_is_active') ?></label>
        <select name="is_active" class="<?= esc(filter_input_class()) ?>">
            <option value=""><?= esc(lang('Menus.menus_filter_all_statuses')) ?></option>
            <option value="1" <?= request()->getGet('is_active') === '1' ? 'selected' : '' ?>><?= esc(lang('Menus.field_is_active_on')) ?></option>
            <option value="0" <?= request()->getGet('is_active') === '0' ? 'selected' : '' ?>><?= esc(lang('Menus.field_is_active_off')) ?></option>
        </select>
    </div>

    <?= view('layouts/partials/filter_limit', ['limitOptions' => $limitOptions ?? [10, 25, 50, 100]]) ?>
</div>
