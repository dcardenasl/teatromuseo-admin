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

        <?= view('components/form/select', [
            'name' => 'event_type',
            'label' => 'Events.field_event_type',
            'required' => true,
            'placeholder' => 'Events.field_event_type_placeholder',
            'help' => 'Events.field_event_type_help',
            'options' => [
                'function' => lang('Events.option_event_type_function'),
                'festival' => lang('Events.option_event_type_festival'),
                'course' => lang('Events.option_event_type_course'),
                'workshop' => lang('Events.option_event_type_workshop'),
                'other' => lang('Events.option_event_type_other'),
            ],
            'value' => $item['event_type'] ?? '',
            'errors' => $errors ?? []
        ]) ?>

        <?= view('components/form/select', [
            'name' => 'status',
            'label' => 'Events.field_status',
            'required' => true,
            'placeholder' => 'Events.field_status_placeholder',
            'help' => 'Events.field_status_help',
            'options' => [
                'draft' => lang('Events.option_status_draft'),
                'published' => lang('Events.option_status_published'),
                'cancelled' => lang('Events.option_status_cancelled'),
            ],
            'value' => $item['status'] ?? '',
            'errors' => $errors ?? []
        ]) ?>

        <?= view('components/form/file', [
            'name' => 'cover_file_id',
            'label' => 'Events.field_cover_file_id',
            'required' => false,
            'value' => old('cover_file_id', ''),
            'accept' => 'image/*',
            'filterType' => 'image',
            'help' => 'Events.field_cover_file_id_help',
            'errors' => $errors ?? []
        ]) ?>

        <?= view('components/form/file_gallery', [
            'name' => 'gallery_file_ids',
            'label' => 'Events.field_gallery_file_ids',
            'value' => old('gallery_file_ids', ''),
            'accept' => 'image/*',
            'filterType' => 'image',
            'help' => 'Events.field_gallery_file_ids_help',
            'errors' => $errors ?? []
        ]) ?>
            </div>
        </section>

        <?= view('events/events/partials/translations', [
            'languages' => $languages ?? [],
            'defaultLangCode' => $defaultLangCode ?? '',
            'defaultLangIndex' => $defaultLangIndex ?? 0,
            'translations' => $translations ?? [],
            'errors' => $errors ?? [],
        ]) ?>
    </div>

    <aside class="space-y-6">
        <?= view('components/display/admin_actions_panel', [
            'content' => '<button type="submit" class="' . esc(action_button_class('primary'), 'attr') . '">' . esc(lang('App.create')) . '</button>'
                . '<a href="' . esc(route_to('admin.events.events'), 'attr') . '" class="' . esc(action_button_class(), 'attr') . '">' . esc(lang('App.cancel')) . '</a>',
        ]) ?>
    </aside>
</form>
