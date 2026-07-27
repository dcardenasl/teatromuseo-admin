<?= view('components/display/admin_page_header', [
    'backUrl' => route_to('admin.events.events'),
    'backLabel' => 'App.back',
    'eyebrow' => 'Events.events_title',
    'title' => 'Events.events_create',
]) ?>

<form method="post" action="<?= route_to('admin.events.events.store') ?>" class="grid grid-cols-1 gap-6 lg:grid-cols-3">
    <?= csrf_field() ?>

    <div class="lg:col-span-2">
        <section class="bg-white border border-gray-200 rounded-xl shadow-sm p-5">
            <h3 class="text-lg font-semibold text-gray-900"><?= esc(lang('Events.events_create')) ?></h3>
            <div class="mt-4 space-y-4">

        <?= view('components/form/text', [
            'name' => 'uuid',
            'label' => 'Events.field_uuid',
            'required' => true,
            'value' => $item['uuid'] ?? '',
            'placeholder' => 'Events.field_uuid_placeholder',
            'help' => 'Events.field_uuid_help',
            'errors' => $errors ?? []
        ]) ?>

        <?= view('components/form/text', [
            'name' => 'title',
            'label' => 'Events.field_title',
            'required' => true,
            'value' => $item['title'] ?? '',
            'placeholder' => 'Events.field_title_placeholder',
            'help' => 'Events.field_title_help',
            'errors' => $errors ?? []
        ]) ?>

        <?= view('components/form/select', [
            'name' => 'event_type',
            'label' => 'Events.field_event_type',
            'required' => true,
            'placeholder' => 'Events.field_event_type_placeholder',
            'help' => 'Events.field_event_type_help',
            'options' => [
                'function' => 'Function',
                'festival' => 'Festival',
                'course' => 'Course',
                'workshop' => 'Workshop',
                'other' => 'Other'
            ],
            'value' => $item['event_type'] ?? '',
            'errors' => $errors ?? []
        ]) ?>

        <?= view('components/form/textarea', [
            'name' => 'description',
            'label' => 'Events.field_description',
            'required' => true,
            'value' => $item['description'] ?? '',
            'placeholder' => 'Events.field_description_placeholder',
            'help' => 'Events.field_description_help',
            'errors' => $errors ?? []
        ]) ?>

        <?= view('components/form/select', [
            'name' => 'status',
            'label' => 'Events.field_status',
            'required' => true,
            'placeholder' => 'Events.field_status_placeholder',
            'help' => 'Events.field_status_help',
            'options' => [
                'draft' => 'Draft',
                'published' => 'Published',
                'cancelled' => 'Cancelled'
            ],
            'value' => $item['status'] ?? '',
            'errors' => $errors ?? []
        ]) ?>
            </div>
        </section>
    </div>

    <aside class="space-y-6">
        <?= view('components/display/admin_actions_panel', [
            'content' => '<button type="submit" class="' . esc(action_button_class('primary'), 'attr') . '">' . esc(lang('App.create')) . '</button>'
                . '<a href="' . esc(route_to('admin.events.events'), 'attr') . '" class="' . esc(action_button_class(), 'attr') . '">' . esc(lang('App.cancel')) . '</a>',
        ]) ?>
    </aside>
</form>
