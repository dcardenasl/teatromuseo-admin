<?php $item = $item ?? []; ?>
<?= view('components/display/admin_page_header', [
    'backUrl' => route_to('admin.venues.venues'),
    'backLabel' => 'App.back',
    'eyebrow' => 'Venues.venues_title',
    'title' => 'Venues.venues_edit',
]) ?>

<form id="delete-item-form" method="post" action="<?= route_to('admin.venues.venues.delete', (string) ($item['id'] ?? '')) ?>" x-data @submit.prevent="$store.confirm.show('<?= esc(confirm_delete_message($item['name'] ?? $item['title'] ?? $item['id'] ?? null), 'js') ?>', () => $el.submit())">
    <?= csrf_field() ?>
</form>

<form method="post" action="<?= route_to('admin.venues.venues.update', (string) ($item['id'] ?? '')) ?>" class="grid grid-cols-1 gap-6 lg:grid-cols-3">
    <?= csrf_field() ?>

    <div class="lg:col-span-2">
        <section class="bg-white border border-gray-200 rounded-xl shadow-sm p-5">
            <h3 class="text-lg font-semibold text-gray-900"><?= esc(lang('Venues.venues_edit')) ?></h3>
            <div class="mt-4 space-y-4">

        <?= view('components/form/text', [
            'name' => 'name',
            'label' => 'Venues.field_name',
            'required' => false,
            'value' => $item['name'] ?? '',
            'placeholder' => 'Venues.field_name_placeholder',
            'help' => 'Venues.field_name_help',
            'maxlength' => 255,
            'errors' => $errors ?? []
        ]) ?>

        <?= view('components/form/text', [
            'name' => 'slug',
            'label' => 'Venues.field_slug',
            'required' => false,
            'value' => $item['slug'] ?? '',
            'placeholder' => 'Venues.field_slug_placeholder',
            'help' => 'Venues.field_slug_help',
            'maxlength' => 255,
            'errors' => $errors ?? []
        ]) ?>

        <?= view('components/form/textarea', [
            'name' => 'description',
            'label' => 'Venues.field_description',
            'required' => false,
            'value' => $item['description'] ?? '',
            'placeholder' => 'Venues.field_description_placeholder',
            'help' => 'Venues.field_description_help',
            'errors' => $errors ?? []
        ]) ?>

        <?= view('components/form/number', [
            'name' => 'capacity',
            'label' => 'Venues.field_capacity',
            'required' => false,
            'value' => $item['capacity'] ?? '',
            'placeholder' => 'Venues.field_capacity_placeholder',
            'help' => 'Venues.field_capacity_help',
            'errors' => $errors ?? []
        ]) ?>

        <?= view('components/form/text', [
            'name' => 'is_active',
            'label' => 'Venues.field_is_active',
            'required' => true,
            'value' => $item['is_active'] ?? '',
            'placeholder' => 'Venues.field_is_active_placeholder',
            'help' => 'Venues.field_is_active_help',
            'errors' => $errors ?? []
        ]) ?>
            </div>
        </section>
    </div>

    <aside class="space-y-6">
        <?= view('components/display/admin_actions_panel', [
            'content' => '<button type="submit" class="' . esc(action_button_class('primary'), 'attr') . '">' . esc(lang('App.update')) . '</button>'
                . '<a href="' . esc(route_to('admin.venues.venues'), 'attr') . '" class="' . esc(action_button_class(), 'attr') . '">' . esc(lang('App.cancel')) . '</a>',
            'dangerContent' => '<button type="submit" form="delete-item-form" class="' . esc(action_button_class('danger'), 'attr') . '">'
                . ui_icon('trash', 'h-3.5 w-3.5') . esc(lang('App.delete')) . '</button>',
        ]) ?>
    </aside>
</form>
