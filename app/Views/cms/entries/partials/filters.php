<?php /** @var array $limitOptions */ ?>

<div class="mt-3 grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-3">
    <div class="xl:col-span-2">
        <label class="<?= esc(filter_label_class()) ?>"><?= lang('App.search') ?></label>
        <input type="text" name="search" value="<?= esc((string) request()->getGet('search')) ?>"
            placeholder="<?= esc(lang('Entries.entries_search_placeholder')) ?>"
            class="<?= esc(filter_input_class()) ?>" data-table-debounce="350">
    </div>
    <div>
        <label class="<?= esc(filter_label_class()) ?>"><?= lang('Entries.field_collection_id') ?></label>
        <select name="collection_id" class="<?= esc(filter_input_class()) ?>">
            <option value=""><?= esc(lang('App.all')) ?></option>
            <?php $selected_collection_id = (string) request()->getGet('collection_id'); ?>
            <?php foreach (($collections ?? []) as $optValue => $optLabel): ?>
                <option value="<?= esc((string) $optValue, 'attr') ?>" <?= $selected_collection_id === (string) $optValue ? 'selected' : '' ?>><?= esc((string) $optLabel) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div>
        <label class="<?= esc(filter_label_class()) ?>"><?= lang('Entries.field_status') ?? 'Status' ?></label>
        <select name="status" class="<?= esc(filter_input_class()) ?>">
            <option value=""><?= esc(lang('App.all')) ?></option>
            <?php $selected_status = (string) request()->getGet('status'); ?>
            <option value="draft" <?= $selected_status === 'draft' ? 'selected' : '' ?>><?= esc(lang('Entries.status_draft')) ?></option>
            <option value="published" <?= $selected_status === 'published' ? 'selected' : '' ?>><?= esc(lang('Entries.status_published')) ?></option>
            <option value="archived" <?= $selected_status === 'archived' ? 'selected' : '' ?>><?= esc(lang('Entries.status_archived')) ?></option>
        </select>
    </div>
    <?= view('layouts/partials/filter_limit', ['limitOptions' => $limitOptions ?? [10, 25, 50, 100]]) ?>
</div>
