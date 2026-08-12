<?php $item = $item ?? []; ?>
<?php $bookingLabel = ! empty($item['guest_email']) ? lang('Bookings.bookings_title') . ' · ' . $item['guest_email'] : lang('Bookings.bookings_details'); ?>
<?= view('components/display/admin_page_header', [
    'backUrl' => route_to('admin.bookings.bookings'),
    'backLabel' => 'App.back',
    'eyebrow' => 'Bookings.bookings_title',
    'title' => 'Bookings.bookings_edit',
]) ?>

<?php if (has_permission('event.bookings.delete')): ?>
    <form id="delete-item-form" method="post" action="<?= route_to('admin.bookings.bookings.delete', (string) ($item['id'] ?? '')) ?>" x-data @submit.prevent="$store.confirm.show('<?= esc(confirm_delete_message($bookingLabel), 'js') ?>', () => $el.submit())">
        <?= csrf_field() ?>
    </form>
<?php endif; ?>

<form method="post" action="<?= route_to('admin.bookings.bookings.update', (string) ($item['id'] ?? '')) ?>" class="grid grid-cols-1 gap-6 lg:grid-cols-3">
    <?= csrf_field() ?>

    <div class="lg:col-span-2">
        <section class="bg-white border border-gray-200 rounded-xl shadow-sm p-5">
            <h3 class="text-lg font-semibold text-gray-900"><?= esc(lang('Bookings.bookings_edit')) ?></h3>
            <div class="mt-4 space-y-4">

        <?= view('components/form/number', [
            'name' => 'user_id',
            'label' => 'Bookings.field_user_id',
            'required' => false,
            'value' => $item['user_id'] ?? '',
            'placeholder' => 'Bookings.field_user_id_placeholder',
            'help' => 'Bookings.field_user_id_help',
            'errors' => $errors ?? []
        ]) ?>

        <?= view('components/form/text', [
            'name' => 'guest_email',
            'label' => 'Bookings.field_guest_email',
            'required' => false,
            'value' => $item['guest_email'] ?? '',
            'placeholder' => 'Bookings.field_guest_email_placeholder',
            'help' => 'Bookings.field_guest_email_help',
            'maxlength' => 255,
            'errors' => $errors ?? []
        ]) ?>

        <?= view('components/form/decimal', [
            'name' => 'total_amount',
            'label' => 'Bookings.field_total_amount',
            'required' => true,
            'value' => $item['total_amount'] ?? '',
            'placeholder' => 'Bookings.field_total_amount_placeholder',
            'help' => 'Bookings.field_total_amount_help',
            'errors' => $errors ?? []
        ]) ?>

        <?= view('components/form/select', [
            'name' => 'status',
            'label' => 'Bookings.field_status',
            'required' => true,
            'placeholder' => 'Bookings.field_status_placeholder',
            'help' => 'Bookings.field_status_help',
            'options' => [
                'pending' => lang('Bookings.option_status_pending'),
                'confirmed' => lang('Bookings.option_status_confirmed'),
                'cancelled' => lang('Bookings.option_status_cancelled'),
                'expired' => lang('Bookings.option_status_expired')
            ],
            'value' => $item['status'] ?? '',
            'errors' => $errors ?? []
        ]) ?>

        <?= view('components/form/datetime', [
            'name' => 'reserved_until',
            'label' => 'Bookings.field_reserved_until',
            'required' => false,
            'value' => $item['reserved_until'] ?? '',
            'placeholder' => 'Bookings.field_reserved_until_placeholder',
            'help' => 'Bookings.field_reserved_until_help',
            'errors' => $errors ?? []
        ]) ?>
            </div>
        </section>
    </div>

    <aside class="space-y-6">
        <?= view('components/display/admin_actions_panel', [
            'content' => '<button type="submit" class="' . esc(action_button_class('primary'), 'attr') . '">' . esc(lang('App.update')) . '</button>'
                . '<a href="' . esc(route_to('admin.bookings.bookings'), 'attr') . '" class="' . esc(action_button_class(), 'attr') . '">' . esc(lang('App.cancel')) . '</a>',
            'dangerContent' => has_permission('event.bookings.delete') ? '<button type="submit" form="delete-item-form" class="' . esc(action_button_class('danger'), 'attr') . '">'
                . ui_icon('trash', 'h-3.5 w-3.5') . esc(lang('App.delete')) . '</button>' : '',
        ]) ?>
    </aside>
</form>
