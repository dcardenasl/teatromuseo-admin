<?= view('components/display/admin_page_header', [
    'backUrl' => route_to('admin.eventreferences.event_references'),
    'backLabel' => 'App.back',
    'eyebrow' => 'EventReferences.event_references_title',
    'title' => 'EventReferences.event_references_create',
]) ?>

<?= view('layouts/partials/event_lookup_status', ['available' => $lookupAvailable ?? true]) ?>

<form method="post" action="<?= route_to('admin.eventreferences.event_references.store') ?>" class="grid grid-cols-1 gap-6 lg:grid-cols-3">
    <?= csrf_field() ?>

    <div class="lg:col-span-2">
        <section class="bg-white border border-gray-200 rounded-xl shadow-sm p-5">
            <h3 class="text-lg font-semibold text-gray-900"><?= esc(lang('EventReferences.event_references_create')) ?></h3>
            <div class="mt-4 space-y-4">

        <?= view('components/form/relation', [
            'name' => 'event_id',
            'label' => 'EventReferences.field_event_id',
            'required' => true,
            'options' => $events ?? [],
            'placeholder' => 'EventReferences.field_event_id_placeholder',
            'help' => 'EventReferences.field_event_id_help',
            'value' => $item['event_id'] ?? '',
            'errors' => $errors ?? []
        ]) ?>

        <?= view('components/form/text', [
            'name' => 'source_system',
            'label' => 'EventReferences.field_source_system',
            'required' => true,
            'value' => $item['source_system'] ?? '',
            'placeholder' => 'EventReferences.field_source_system_placeholder',
            'help' => 'EventReferences.field_source_system_help',
            'maxlength' => 255,
            'errors' => $errors ?? []
        ]) ?>

        <?= view('components/form/text', [
            'name' => 'source_type',
            'label' => 'EventReferences.field_source_type',
            'required' => true,
            'value' => $item['source_type'] ?? '',
            'placeholder' => 'EventReferences.field_source_type_placeholder',
            'help' => 'EventReferences.field_source_type_help',
            'maxlength' => 255,
            'errors' => $errors ?? []
        ]) ?>

        <?= view('components/form/text', [
            'name' => 'source_id',
            'label' => 'EventReferences.field_source_id',
            'required' => true,
            'value' => $item['source_id'] ?? '',
            'placeholder' => 'EventReferences.field_source_id_placeholder',
            'help' => 'EventReferences.field_source_id_help',
            'maxlength' => 255,
            'errors' => $errors ?? []
        ]) ?>

        <?= view('components/form/text', [
            'name' => 'relation',
            'label' => 'EventReferences.field_relation',
            'required' => true,
            'value' => $item['relation'] ?? '',
            'placeholder' => 'EventReferences.field_relation_placeholder',
            'help' => 'EventReferences.field_relation_help',
            'maxlength' => 255,
            'errors' => $errors ?? []
        ]) ?>

        <?= view('components/form/text', [
            'name' => 'metadata',
            'label' => 'EventReferences.field_metadata',
            'required' => false,
            'value' => $item['metadata'] ?? '',
            'placeholder' => 'EventReferences.field_metadata_placeholder',
            'help' => 'EventReferences.field_metadata_help',
            'maxlength' => 255,
            'errors' => $errors ?? []
        ]) ?>
            </div>
        </section>
    </div>

    <aside class="space-y-6">
        <?= view('components/display/admin_actions_panel', [
            'content' => '<button type="submit" class="' . esc(action_button_class('primary'), 'attr') . '">' . esc(lang('App.create')) . '</button>'
                . '<a href="' . esc(route_to('admin.eventreferences.event_references'), 'attr') . '" class="' . esc(action_button_class(), 'attr') . '">' . esc(lang('App.cancel')) . '</a>',
        ]) ?>
    </aside>
</form>
