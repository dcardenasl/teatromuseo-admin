<?php /** @var array $limitOptions */ ?>

<div class="mt-3 grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-3">
    <div class="xl:col-span-2">
        <label class="<?= esc(filter_label_class()) ?>"><?= lang('App.search') ?></label>
        <input type="text" name="search" value="<?= esc((string) request()->getGet('search')) ?>"
            placeholder="<?= esc(lang('Occurrences.occurrences_search_placeholder')) ?>"
            class="<?= esc(filter_input_class()) ?>" data-table-debounce="350">
    </div>
    <div>
        <label class="<?= esc(filter_label_class()) ?>"><?= lang('Occurrences.field_event_id') ?></label>
        <select name="event_id" class="<?= esc(filter_input_class()) ?>">
            <option value=""><?= esc(lang('App.all')) ?></option>
            <?php $selected_event_id = (string) request()->getGet('event_id'); ?>
            <?php foreach (($$events ?? []) as $optValue => $optLabel): ?>
                <option value="<?= esc((string) $optValue, 'attr') ?>" <?= $selected_event_id === (string) $optValue ? 'selected' : '' ?>><?= esc((string) $optLabel) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div>
        <label class="<?= esc(filter_label_class()) ?>"><?= lang('Occurrences.field_venue_id') ?></label>
        <select name="venue_id" class="<?= esc(filter_input_class()) ?>">
            <option value=""><?= esc(lang('App.all')) ?></option>
            <?php $selected_venue_id = (string) request()->getGet('venue_id'); ?>
            <?php foreach (($$venues ?? []) as $optValue => $optLabel): ?>
                <option value="<?= esc((string) $optValue, 'attr') ?>" <?= $selected_venue_id === (string) $optValue ? 'selected' : '' ?>><?= esc((string) $optLabel) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <?= view('layouts/partials/filter_limit', ['limitOptions' => $limitOptions ?? [10, 25, 50, 100]]) ?>
</div>
