<?php $ticket = $ticket ?? []; ?>

<?php if (! empty($error)): ?>
    <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-5">
        <p class="text-sm text-red-600"><?= esc($error) ?></p>
    </div>
<?php elseif (! empty($ticket)): ?>
<?php
    $itemId = (string) ($ticket['id'] ?? '');
    $ticketLabel = ! empty($ticket['holder_name'])
        ? $ticket['holder_name']
        : (! empty($ticket['holder_email']) ? $ticket['holder_email'] : lang('Tickets.tickets_details'));
    ?>

    <?= view('components/display/admin_page_header', [
            'backUrl' => route_to('admin.tickets.tickets'),
            'backLabel' => 'Tickets.tickets_title',
            'eyebrow' => 'Tickets.tickets_details',
            'title' => $ticketLabel,
        ]) ?>

    <?= view('layouts/partials/event_lookup_status', ['available' => $lookupAvailable ?? true]) ?>

    <?php ob_start(); ?>
    <section class="bg-white border border-gray-200 rounded-xl shadow-sm">
        <div class="border-b border-gray-100 px-5 py-4">
            <h3 class="text-sm font-semibold uppercase tracking-wider text-gray-700"><?= esc(lang('Tickets.tickets_details')) ?></h3>
        </div>
        <dl class="divide-y divide-gray-100">
            <?= view('components/display/field_row', [
                    'label' => 'Tickets.field_uuid',
                    'value' => $ticket['uuid'] ?? '—'
                ]) ?>
            <?= view('components/display/field_row', [
                    'label' => 'Tickets.field_booking_id',
                    'value' => ($bookings[(string) ($ticket['booking_id'] ?? '')] ?? ($ticket['booking_id'] ?? '—'))
                ]) ?>
            <?= view('components/display/field_row', [
                    'label' => 'Tickets.field_ticket_type_id',
                    'value' => ($ticketTypes[(string) ($ticket['ticket_type_id'] ?? '')] ?? ($ticket['ticket_type_id'] ?? '—'))
                ]) ?>
            <?= view('components/display/field_row', [
                    'label' => 'Tickets.field_holder_name',
                    'value' => $ticket['holder_name'] ?? '—'
                ]) ?>
            <?= view('components/display/field_row', [
                    'label' => 'Tickets.field_holder_email',
                    'value' => $ticket['holder_email'] ?? '—'
                ]) ?>
            <?= view('components/display/field_row', [
                    'label' => 'Tickets.field_qr_code_token',
                    'value' => $ticket['qr_code_token'] ?? '—'
                ]) ?>
            <?= view('components/display/field_row', [
                    'label' => 'Tickets.field_status',
                    'value' => ! empty($ticket['status']) ? '<span class="inline-flex items-center rounded-md bg-gray-50 px-2 py-1 text-xs font-medium text-gray-600 ring-1 ring-inset ring-gray-500/10">' . esc($ticket['status']) . '</span>' : '—',
                    'isHtml' => true
                ]) ?>
            <?= view('components/display/field_row', [
                    'label' => 'Tickets.field_checked_in_at',
                    'value' => $ticket['checked_in_at'] ?? '—'
                ]) ?>
            <?= view('components/display/field_row', [
                    'label' => 'TableColumns.created_at',
                    'value' => $ticket['created_at'] ?? '—',
                ]) ?>
        </dl>
    </section>
    <?php $mainContent = ob_get_clean(); ?>

    <?php ob_start(); ?>
    <?php if (has_permission('event.tickets.write')): ?>
        <a href="<?= route_to('admin.tickets.tickets.edit', $itemId) ?>" class="<?= esc(action_button_class('primary')) ?>"><?= lang('App.edit') ?></a>
    <?php endif; ?>

    <?php $actionsContent = ob_get_clean(); ?>

    <?php $dangerContent = ''; ?>
    <?php if (has_permission('event.tickets.delete')): ?>
        <?php ob_start(); ?>
        <form method="post" action="<?= route_to('admin.tickets.tickets.delete', $itemId) ?>" x-data @submit.prevent="$store.confirm.show('<?= esc(confirm_delete_message($ticketLabel), 'js') ?>', () => $el.submit())">
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
