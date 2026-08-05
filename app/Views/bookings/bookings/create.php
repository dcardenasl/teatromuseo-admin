<?= view('components/display/admin_page_header', [
    'backUrl' => route_to('admin.bookings.bookings'),
    'backLabel' => 'App.back',
    'eyebrow' => 'Bookings.bookings_title',
    'title' => 'Bookings.bookings_create',
]) ?>

<form method="post" action="<?= route_to('admin.bookings.bookings.store') ?>" class="grid grid-cols-1 gap-6 lg:grid-cols-3">
    <?= csrf_field() ?>

    <div class="lg:col-span-2">
        <section class="bg-white border border-gray-200 rounded-xl shadow-sm p-5">
            <h3 class="text-lg font-semibold text-gray-900"><?= esc(lang('Bookings.bookings_create')) ?></h3>
            <div class="mt-4 space-y-4">

        <input type="hidden" name="total_amount" value="0">
        <input type="hidden" name="status" value="pending">

        <?= view('components/form/relation', [
            'name' => 'ticket_type_id',
            'label' => 'Bookings.field_ticket_type_id',
            'required' => true,
            'options' => $ticketTypes ?? [],
            'placeholder' => 'Bookings.field_ticket_type_id_placeholder',
            'help' => 'Bookings.field_ticket_type_id_help',
            'value' => $item['ticket_type_id'] ?? '',
            'errors' => $errors ?? []
        ]) ?>

        <?= view('components/form/number', [
            'name' => 'quantity',
            'label' => 'Bookings.field_quantity',
            'required' => true,
            'value' => $item['quantity'] ?? 1,
            'placeholder' => 'Bookings.field_quantity_placeholder',
            'help' => 'Bookings.field_quantity_help',
            'min' => 1,
            'errors' => $errors ?? []
        ]) ?>

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

        <?= view('components/form/text', [
            'name' => 'holder_name',
            'label' => 'Bookings.field_holder_name',
            'required' => false,
            'value' => $item['holder_name'] ?? '',
            'placeholder' => 'Bookings.field_holder_name_placeholder',
            'help' => 'Bookings.field_holder_name_help',
            'maxlength' => 255,
            'errors' => $errors ?? []
        ]) ?>

        <?= view('components/form/text', [
            'name' => 'holder_email',
            'label' => 'Bookings.field_holder_email',
            'required' => false,
            'value' => $item['holder_email'] ?? '',
            'placeholder' => 'Bookings.field_holder_email_placeholder',
            'help' => 'Bookings.field_holder_email_help',
            'maxlength' => 255,
            'errors' => $errors ?? []
        ]) ?>

            </div>
        </section>
    </div>

    <aside class="space-y-6">
        <?= view('components/display/admin_actions_panel', [
            'content' => '<button type="submit" class="' . esc(action_button_class('primary'), 'attr') . '">' . esc(lang('App.create')) . '</button>'
                . '<a href="' . esc(route_to('admin.bookings.bookings'), 'attr') . '" class="' . esc(action_button_class(), 'attr') . '">' . esc(lang('App.cancel')) . '</a>',
        ]) ?>
    </aside>
</form>
