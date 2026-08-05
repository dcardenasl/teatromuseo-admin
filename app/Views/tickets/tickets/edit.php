<?php $item = $item ?? []; ?>
<?php $ticketLabel = ! empty($item['holder_name']) ? $item['holder_name'] : (! empty($item['holder_email']) ? $item['holder_email'] : lang('Tickets.tickets_details')); ?>
<?= view('components/display/admin_page_header', [
    'backUrl' => route_to('admin.tickets.tickets'),
    'backLabel' => 'App.back',
    'eyebrow' => 'Tickets.tickets_title',
    'title' => 'Tickets.tickets_edit',
]) ?>

<form id="delete-item-form" method="post" action="<?= route_to('admin.tickets.tickets.delete', (string) ($item['id'] ?? '')) ?>" x-data @submit.prevent="$store.confirm.show('<?= esc(confirm_delete_message($ticketLabel), 'js') ?>', () => $el.submit())">
    <?= csrf_field() ?>
</form>

<form method="post" action="<?= route_to('admin.tickets.tickets.update', (string) ($item['id'] ?? '')) ?>" class="grid grid-cols-1 gap-6 lg:grid-cols-3">
    <?= csrf_field() ?>

    <div class="lg:col-span-2">
        <section class="bg-white border border-gray-200 rounded-xl shadow-sm p-5">
            <h3 class="text-lg font-semibold text-gray-900"><?= esc(lang('Tickets.tickets_edit')) ?></h3>
            <div class="mt-4 space-y-4">

        <?= view('components/form/relation', [
            'name' => 'booking_id',
            'label' => 'Tickets.field_booking_id',
            'required' => true,
            'options' => $bookings ?? [],
            'placeholder' => 'Tickets.field_booking_id_placeholder',
            'help' => 'Tickets.field_booking_id_help',
            'value' => $item['booking_id'] ?? '',
            'errors' => $errors ?? []
        ]) ?>

        <?= view('components/form/relation', [
            'name' => 'ticket_type_id',
            'label' => 'Tickets.field_ticket_type_id',
            'required' => true,
            'options' => $ticketTypes ?? [],
            'placeholder' => 'Tickets.field_ticket_type_id_placeholder',
            'help' => 'Tickets.field_ticket_type_id_help',
            'value' => $item['ticket_type_id'] ?? '',
            'errors' => $errors ?? []
        ]) ?>

        <?= view('components/form/text', [
            'name' => 'holder_name',
            'label' => 'Tickets.field_holder_name',
            'required' => true,
            'value' => $item['holder_name'] ?? '',
            'placeholder' => 'Tickets.field_holder_name_placeholder',
            'help' => 'Tickets.field_holder_name_help',
            'maxlength' => 255,
            'errors' => $errors ?? []
        ]) ?>

        <?= view('components/form/text', [
            'name' => 'holder_email',
            'label' => 'Tickets.field_holder_email',
            'required' => true,
            'value' => $item['holder_email'] ?? '',
            'placeholder' => 'Tickets.field_holder_email_placeholder',
            'help' => 'Tickets.field_holder_email_help',
            'maxlength' => 255,
            'errors' => $errors ?? []
        ]) ?>

        <?= view('components/form/select', [
            'name' => 'status',
            'label' => 'Tickets.field_status',
            'required' => true,
            'placeholder' => 'Tickets.field_status_placeholder',
            'help' => 'Tickets.field_status_help',
            'options' => [
                'valid' => lang('Tickets.option_status_valid'),
                'used' => lang('Tickets.option_status_used'),
                'void' => lang('Tickets.option_status_void')
            ],
            'value' => $item['status'] ?? '',
            'errors' => $errors ?? []
        ]) ?>

        <?= view('components/form/datetime', [
            'name' => 'checked_in_at',
            'label' => 'Tickets.field_checked_in_at',
            'required' => false,
            'value' => $item['checked_in_at'] ?? '',
            'placeholder' => 'Tickets.field_checked_in_at_placeholder',
            'help' => 'Tickets.field_checked_in_at_help',
            'errors' => $errors ?? []
        ]) ?>
            </div>
        </section>
    </div>

    <aside class="space-y-6">
        <?= view('components/display/admin_actions_panel', [
            'content' => '<button type="submit" class="' . esc(action_button_class('primary'), 'attr') . '">' . esc(lang('App.update')) . '</button>'
                . '<a href="' . esc(route_to('admin.tickets.tickets'), 'attr') . '" class="' . esc(action_button_class(), 'attr') . '">' . esc(lang('App.cancel')) . '</a>',
            'dangerContent' => '<button type="submit" form="delete-item-form" class="' . esc(action_button_class('danger'), 'attr') . '">'
                . ui_icon('trash', 'h-3.5 w-3.5') . esc(lang('App.delete')) . '</button>',
        ]) ?>
    </aside>
</form>
