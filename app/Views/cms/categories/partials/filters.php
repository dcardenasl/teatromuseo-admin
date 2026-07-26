<?php /** @var array $limitOptions */ ?>

<div class="mt-3 grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-3">
    <div class="xl:col-span-2">
        <label class="<?= esc(filter_label_class()) ?>"><?= lang('App.search') ?></label>
        <input type="text" name="search" value="<?= esc((string) request()->getGet('search')) ?>"
            placeholder="<?= esc(lang('Categories.categories_search_placeholder')) ?>"
            class="<?= esc(filter_input_class()) ?>" data-table-debounce="350">
    </div>
    <div>
        <label class="<?= esc(filter_label_class()) ?>"><?= lang('Categories.field_collection_id') ?></label>
        <select name="collection_id" class="<?= esc(filter_input_class()) ?>">
            <option value=""><?= esc(lang('App.all')) ?></option>
            <?php $selected_collection_id = (string) request()->getGet('collection_id'); ?>
            <?php foreach (($collections ?? []) as $optValue => $optLabel): ?>
                <option value="<?= esc((string) $optValue, 'attr') ?>" <?= $selected_collection_id === (string) $optValue ? 'selected' : '' ?>><?= esc((string) $optLabel) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div>
        <label class="<?= esc(filter_label_class()) ?>"><?= lang('Categories.field_parent_id') ?></label>
        <select name="parent_id" class="<?= esc(filter_input_class()) ?>">
            <option value=""><?= esc(lang('App.all')) ?></option>
            <?php $selected_parent_id = (string) request()->getGet('parent_id'); ?>
            <?php foreach (($categories ?? []) as $optValue => $optLabel): ?>
                <option value="<?= esc((string) $optValue, 'attr') ?>" <?= $selected_parent_id === (string) $optValue ? 'selected' : '' ?>><?= esc((string) $optLabel) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <?= view('layouts/partials/filter_limit', ['limitOptions' => $limitOptions ?? [10, 25, 50, 100]]) ?>
</div>
