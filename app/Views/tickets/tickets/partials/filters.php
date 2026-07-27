<?php /** @var array $limitOptions */ ?>

<div class="mt-3 grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-3">
    <div class="xl:col-span-2">
        <label class="<?= esc(filter_label_class()) ?>"><?= lang('App.search') ?></label>
        <input type="text" name="search" value="<?= esc((string) request()->getGet('search')) ?>"
            placeholder="<?= esc(lang('Tickets.tickets_search_placeholder')) ?>"
            class="<?= esc(filter_input_class()) ?>" data-table-debounce="350">
    </div>
    <div>
        <label class="<?= esc(filter_label_class()) ?>"><?= lang('Tickets.field_booking_id') ?></label>
        <select name="booking_id" class="<?= esc(filter_input_class()) ?>">
            <option value=""><?= esc(lang('App.all')) ?></option>
            <?php $selected_booking_id = (string) request()->getGet('booking_id'); ?>
            <?php foreach (($$bookings ?? []) as $optValue => $optLabel): ?>
                <option value="<?= esc((string) $optValue, 'attr') ?>" <?= $selected_booking_id === (string) $optValue ? 'selected' : '' ?>><?= esc((string) $optLabel) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div>
        <label class="<?= esc(filter_label_class()) ?>"><?= lang('Tickets.field_ticket_type_id') ?></label>
        <select name="ticket_type_id" class="<?= esc(filter_input_class()) ?>">
            <option value=""><?= esc(lang('App.all')) ?></option>
            <?php $selected_ticket_type_id = (string) request()->getGet('ticket_type_id'); ?>
            <?php foreach (($$ticketTypes ?? []) as $optValue => $optLabel): ?>
                <option value="<?= esc((string) $optValue, 'attr') ?>" <?= $selected_ticket_type_id === (string) $optValue ? 'selected' : '' ?>><?= esc((string) $optLabel) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <?= view('layouts/partials/filter_limit', ['limitOptions' => $limitOptions ?? [10, 25, 50, 100]]) ?>
</div>
