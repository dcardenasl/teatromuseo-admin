<?= view('components/display/admin_page_header', [
    'backUrl' => route_to('admin.tickettypes.ticket_types'),
    'backLabel' => 'App.back',
    'eyebrow' => 'TicketTypes.ticket_types_title',
    'title' => 'TicketTypes.ticket_types_create',
]) ?>

<form method="post" action="<?= route_to('admin.tickettypes.ticket_types.store') ?>" class="grid grid-cols-1 gap-6 lg:grid-cols-3">
    <?= csrf_field() ?>

    <div class="lg:col-span-2">
        <section class="bg-white border border-gray-200 rounded-xl shadow-sm p-5">
            <h3 class="text-lg font-semibold text-gray-900"><?= esc(lang('TicketTypes.ticket_types_create')) ?></h3>
            <div class="mt-4 space-y-4">

        <?= view('components/form/relation', [
            'name' => 'event_id',
            'label' => 'TicketTypes.field_event_id',
            'required' => true,
            'options' => $events ?? [],
            'placeholder' => 'TicketTypes.field_event_id_placeholder',
            'help' => 'TicketTypes.field_event_id_help',
            'value' => $item['event_id'] ?? '',
            'errors' => $errors ?? []
        ]) ?>

        <?= view('components/form/relation', [
            'name' => 'occurrence_id',
            'label' => 'TicketTypes.field_occurrence_id',
            'required' => false,
            'options' => $occurrences ?? [],
            'placeholder' => 'TicketTypes.field_occurrence_id_placeholder',
            'help' => 'TicketTypes.field_occurrence_id_help',
            'value' => $item['occurrence_id'] ?? '',
            'errors' => $errors ?? []
        ]) ?>

        <?= view('components/form/text', [
            'name' => 'name',
            'label' => 'TicketTypes.field_name',
            'required' => true,
            'value' => $item['name'] ?? '',
            'placeholder' => 'TicketTypes.field_name_placeholder',
            'help' => 'TicketTypes.field_name_help',
            'maxlength' => 255,
            'errors' => $errors ?? []
        ]) ?>

        <?= view('components/form/decimal', [
            'name' => 'price',
            'label' => 'TicketTypes.field_price',
            'required' => true,
            'value' => $item['price'] ?? '',
            'placeholder' => 'TicketTypes.field_price_placeholder',
            'help' => 'TicketTypes.field_price_help',
            'errors' => $errors ?? []
        ]) ?>

        <?= view('components/form/number', [
            'name' => 'capacity',
            'label' => 'TicketTypes.field_capacity',
            'required' => true,
            'value' => $item['capacity'] ?? '',
            'placeholder' => 'TicketTypes.field_capacity_placeholder',
            'help' => 'TicketTypes.field_capacity_help',
            'errors' => $errors ?? []
        ]) ?>

        <?= view('components/form/number', [
            'name' => 'available_spots',
            'label' => 'TicketTypes.field_available_spots',
            'required' => true,
            'value' => $item['available_spots'] ?? '',
            'placeholder' => 'TicketTypes.field_available_spots_placeholder',
            'help' => 'TicketTypes.field_available_spots_help',
            'errors' => $errors ?? []
        ]) ?>

        <?= view('components/form/datetime', [
            'name' => 'sales_start',
            'label' => 'TicketTypes.field_sales_start',
            'required' => true,
            'value' => $item['sales_start'] ?? '',
            'placeholder' => 'TicketTypes.field_sales_start_placeholder',
            'help' => 'TicketTypes.field_sales_start_help',
            'errors' => $errors ?? []
        ]) ?>

        <?= view('components/form/datetime', [
            'name' => 'sales_end',
            'label' => 'TicketTypes.field_sales_end',
            'required' => true,
            'value' => $item['sales_end'] ?? '',
            'placeholder' => 'TicketTypes.field_sales_end_placeholder',
            'help' => 'TicketTypes.field_sales_end_help',
            'errors' => $errors ?? []
        ]) ?>
            </div>
        </section>
    </div>

    <aside class="space-y-6">
        <?= view('components/display/admin_actions_panel', [
            'content' => '<button type="submit" class="' . esc(action_button_class('primary'), 'attr') . '">' . esc(lang('App.create')) . '</button>'
                . '<a href="' . esc(route_to('admin.tickettypes.ticket_types'), 'attr') . '" class="' . esc(action_button_class(), 'attr') . '">' . esc(lang('App.cancel')) . '</a>',
        ]) ?>
    </aside>
</form>
