<?= view('components/display/admin_page_header', [
    'backUrl' => route_to('admin.museum.categories'),
    'backLabel' => 'App.back',
    'eyebrow' => 'Museum.categories_title',
    'title' => 'Museum.categories_create',
]) ?>

<form method="post" action="<?= route_to('admin.museum.categories.store') ?>" class="grid grid-cols-1 gap-6 lg:grid-cols-3">
    <?= csrf_field() ?>

    <div class="lg:col-span-2">
        <section class="bg-white border border-gray-200 rounded-xl shadow-sm p-5">
            <h3 class="text-lg font-semibold text-gray-900"><?= esc(lang('Museum.categories_create')) ?></h3>
            <div class="mt-4 space-y-4">

        <?= view('components/form/text', [
            'name' => 'name',
            'label' => 'Museum.field_name',
            'required' => true,
            'value' => $item['name'] ?? '',
            'placeholder' => 'Museum.field_name_placeholder',
            'help' => 'Museum.field_name_help',
            'errors' => $errors ?? []
        ]) ?>

        <?= view('components/form/text', [
            'name' => 'slug',
            'label' => 'Museum.field_slug',
            'required' => true,
            'value' => $item['slug'] ?? '',
            'placeholder' => 'Museum.field_slug_placeholder',
            'help' => 'Museum.field_slug_help',
            'errors' => $errors ?? []
        ]) ?>

        <?= view('components/form/text', [
            'name' => 'icon',
            'label' => 'Museum.field_icon',
            'required' => false,
            'value' => $item['icon'] ?? '',
            'placeholder' => 'Museum.field_icon_placeholder',
            'help' => 'Museum.field_icon_help',
            'errors' => $errors ?? []
        ]) ?>

        <?= view('components/form/textarea', [
            'name' => 'short_description',
            'label' => 'Museum.field_short_description',
            'required' => false,
            'value' => $item['short_description'] ?? '',
            'placeholder' => 'Museum.field_short_description_placeholder',
            'help' => 'Museum.field_short_description_help',
            'errors' => $errors ?? []
        ]) ?>
            </div>
        </section>
    </div>

    <aside class="space-y-6">
        <?= view('components/display/admin_actions_panel', [
            'content' => '<button type="submit" class="' . esc(action_button_class('primary'), 'attr') . '">' . esc(lang('App.create')) . '</button>'
                . '<a href="' . esc(route_to('admin.museum.categories'), 'attr') . '" class="' . esc(action_button_class(), 'attr') . '">' . esc(lang('App.cancel')) . '</a>',
        ]) ?>
    </aside>
</form>
