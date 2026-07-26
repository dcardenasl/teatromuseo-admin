<?php /** @var array $limitOptions */ ?>

<div class="mt-3 grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-3">
    <div>
        <label class="<?= esc(filter_label_class()) ?>"><?= lang('App.search') ?></label>
        <input type="text" name="search" value="<?= esc((string) request()->getGet('search')) ?>"
            placeholder="<?= esc(lang('Pages.pages_search_placeholder')) ?>"
            class="<?= esc(filter_input_class()) ?>" data-table-debounce="350">
    </div>
    <div>
        <label class="<?= esc(filter_label_class()) ?>"><?= lang('Pages.field_parent_id') ?></label>
        <select name="parent_id" class="<?= esc(filter_input_class()) ?>">
            <option value=""><?= esc(lang('App.all')) ?></option>
            <?php $selected_parent_id = (string) request()->getGet('parent_id'); ?>
            <?php foreach (($pages ?? []) as $optValue => $optLabel): ?>
                <option value="<?= esc((string) $optValue, 'attr') ?>" <?= $selected_parent_id === (string) $optValue ? 'selected' : '' ?>><?= esc((string) $optLabel) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div>
        <label class="<?= esc(filter_label_class()) ?>"><?= lang('Pages.field_status') ?></label>
        <select name="status" class="<?= esc(filter_input_class()) ?>">
            <option value=""><?= esc(lang('App.all')) ?></option>
            <?php $selected_status = (string) request()->getGet('status'); ?>
            <option value="draft" <?= $selected_status === 'draft' ? 'selected' : '' ?>><?= esc(lang('Pages.status_draft')) ?></option>
            <option value="published" <?= $selected_status === 'published' ? 'selected' : '' ?>><?= esc(lang('Pages.status_published')) ?></option>
            <option value="archived" <?= $selected_status === 'archived' ? 'selected' : '' ?>><?= esc(lang('Pages.status_archived')) ?></option>
        </select>
    </div>
    <div>
        <label class="<?= esc(filter_label_class()) ?>"><?= lang('Pages.field_page_type') ?></label>
        <select name="page_type" class="<?= esc(filter_input_class()) ?>">
            <option value=""><?= esc(lang('App.all')) ?></option>
            <?php $selected_type = (string) request()->getGet('page_type'); ?>
            <option value="home" <?= $selected_type === 'home' ? 'selected' : '' ?>><?= esc(lang('Pages.page_type_home')) ?></option>
            <option value="generic" <?= $selected_type === 'generic' ? 'selected' : '' ?>><?= esc(lang('Pages.page_type_generic')) ?></option>
            <option value="contact" <?= $selected_type === 'contact' ? 'selected' : '' ?>><?= esc(lang('Pages.page_type_contact')) ?></option>
            <option value="privacy" <?= $selected_type === 'privacy' ? 'selected' : '' ?>><?= esc(lang('Pages.page_type_privacy')) ?></option>
            <option value="terms" <?= $selected_type === 'terms' ? 'selected' : '' ?>><?= esc(lang('Pages.page_type_terms')) ?></option>
            <option value="404" <?= $selected_type === '404' ? 'selected' : '' ?>><?= esc(lang('Pages.page_type_404')) ?></option>
            <option value="500" <?= $selected_type === '500' ? 'selected' : '' ?>><?= esc(lang('Pages.page_type_500')) ?></option>
            <option value="maintenance" <?= $selected_type === 'maintenance' ? 'selected' : '' ?>><?= esc(lang('Pages.page_type_maintenance')) ?></option>
        </select>
    </div>
    <div class="md:col-span-2 xl:col-span-4">
        <?= view('layouts/partials/filter_limit', ['limitOptions' => $limitOptions ?? [10, 25, 50, 100]]) ?>
    </div>
</div>
