<?php $ticketType = $ticketType ?? []; ?>

<?php if (! empty($error)): ?>
    <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-5">
        <p class="text-sm text-red-600"><?= esc($error) ?></p>
    </div>
<?php elseif (! empty($ticketType)): ?>
    <?php $itemId = (string) ($ticketType['id'] ?? ''); ?>

    <?= view('components/display/admin_page_header', [
        'backUrl' => route_to('admin.tickettypes.ticket_types'),
        'backLabel' => 'TicketTypes.ticket_types_title',
        'eyebrow' => 'TicketTypes.ticket_types_details',
        'title' => (string) ($ticketType['name'] ?? $ticketType['title'] ?? $ticketType['id'] ?? lang('TicketTypes.ticket_types_details')),
    ]) ?>

    <?php ob_start(); ?>
    <section class="bg-white border border-gray-200 rounded-xl shadow-sm">
        <div class="border-b border-gray-100 px-5 py-4">
            <h3 class="text-sm font-semibold uppercase tracking-wider text-gray-700"><?= esc(lang('TicketTypes.ticket_types_details')) ?></h3>
        </div>
        <dl class="divide-y divide-gray-100">
            <?= view('components/display/field_row', [
                'label' => 'TicketTypes.field_event_id',
                'value' => ($events[(string) ($ticketType['event_id'] ?? '')] ?? ($ticketType['event_id'] ?? '—'))
            ]) ?>
            <?= view('components/display/field_row', [
                'label' => 'TicketTypes.field_occurrence_id',
                'value' => ($occurrences[(string) ($ticketType['occurrence_id'] ?? '')] ?? ($ticketType['occurrence_id'] ?? '—'))
            ]) ?>
            <?= view('components/display/field_row', [
                'label' => 'TicketTypes.field_name',
                'value' => $ticketType['name'] ?? '—'
            ]) ?>
            <?= view('components/display/field_row', [
                'label' => 'TicketTypes.field_price',
                'value' => $ticketType['price'] ?? '—'
            ]) ?>
            <?= view('components/display/field_row', [
                'label' => 'TicketTypes.field_capacity',
                'value' => $ticketType['capacity'] ?? '—'
            ]) ?>
            <?= view('components/display/field_row', [
                'label' => 'TicketTypes.field_available_spots',
                'value' => $ticketType['available_spots'] ?? '—'
            ]) ?>
            <?= view('components/display/field_row', [
                'label' => 'TicketTypes.field_sales_start',
                'value' => $ticketType['sales_start'] ?? '—'
            ]) ?>
            <?= view('components/display/field_row', [
                'label' => 'TicketTypes.field_sales_end',
                'value' => $ticketType['sales_end'] ?? '—'
            ]) ?>
            <?= view('components/display/field_row', [
                'label' => 'TableColumns.created_at',
                'value' => $ticketType['created_at'] ?? '—',
            ]) ?>
        </dl>
    </section>
    <?php $mainContent = ob_get_clean(); ?>

    <?php ob_start(); ?>
    <?php if (has_permission('event.ticket-types.write')): ?>
        <a href="<?= route_to('admin.tickettypes.ticket_types.edit', $itemId) ?>" class="<?= esc(action_button_class('primary')) ?>"><?= lang('App.edit') ?></a>
    <?php endif; ?>

    <?php $actionsContent = ob_get_clean(); ?>

    <?php $dangerContent = ''; ?>
    <?php if (has_permission('event.ticket-types.delete')): ?>
        <?php ob_start(); ?>
        <form method="post" action="<?= route_to('admin.tickettypes.ticket_types.delete', $itemId) ?>" x-data @submit.prevent="$store.confirm.show('<?= esc(confirm_delete_message($ticketType['name'] ?? $ticketType['title'] ?? $ticketType['id'] ?? null), 'js') ?>', () => $el.submit())">
            <?= csrf_field() ?>
            <button type="submit" class="<?= esc(action_button_class('danger')) ?>">
                <?= ui_icon('trash', 'h-3.5 w-3.5') ?>
                <?= esc(lang('App.delete')) ?>
            </button>
        </form>
        <?php $dangerContent = ob_get_clean(); ?>
    <?php endif; ?>

    <?= view('components/display/admin_resource_layout', [
        'main' => $mainContent,
        'aside' => view('components/display/admin_actions_panel', [
            'content' => $actionsContent,
            'dangerContent' => $dangerContent,
        ]),
    ]) ?>
<?php endif; ?>
