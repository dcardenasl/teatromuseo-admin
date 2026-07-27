<?= view('components/display/admin_page_header', [
    'backUrl' => route_to('admin.museum.techniques'),
    'backLabel' => 'App.back',
    'eyebrow' => 'Museum.techniques_title',
    'title' => 'Museum.techniques_create',
]) ?>

<form method="post" action="<?= route_to('admin.museum.techniques.store') ?>" class="grid grid-cols-1 gap-6 lg:grid-cols-3">
    <?= csrf_field() ?>

    <div class="lg:col-span-2">
        <section class="bg-white border border-gray-200 rounded-xl shadow-sm p-5">
            <h3 class="text-lg font-semibold text-gray-900"><?= esc(lang('Museum.techniques_create')) ?></h3>
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

        <?= view('components/form/textarea', [
            'name' => 'summary',
            'label' => 'Museum.field_summary',
            'required' => false,
            'value' => $item['summary'] ?? '',
            'placeholder' => 'Museum.field_summary_placeholder',
            'help' => 'Museum.field_summary_help',
            'errors' => $errors ?? []
        ]) ?>

        <?= view('components/form/text', [
            'name' => 'video_url',
            'label' => 'Museum.field_video_url',
            'required' => false,
            'value' => $item['video_url'] ?? '',
            'placeholder' => 'Museum.field_video_url_placeholder',
            'help' => 'Museum.field_video_url_help',
            'errors' => $errors ?? []
        ]) ?>

        <?= view('components/form/number', [
            'name' => 'pdf_file_id',
            'label' => 'Museum.field_pdf_file_id',
            'required' => false,
            'value' => $item['pdf_file_id'] ?? '',
            'placeholder' => 'Museum.field_pdf_file_id_placeholder',
            'help' => 'Museum.field_pdf_file_id_help',
            'errors' => $errors ?? []
        ]) ?>
            </div>
        </section>
    </div>

    <aside class="space-y-6">
        <?= view('components/display/admin_actions_panel', [
            'content' => '<button type="submit" class="' . esc(action_button_class('primary'), 'attr') . '">' . esc(lang('App.create')) . '</button>'
                . '<a href="' . esc(route_to('admin.museum.techniques'), 'attr') . '" class="' . esc(action_button_class(), 'attr') . '">' . esc(lang('App.cancel')) . '</a>',
        ]) ?>
    </aside>
</form>
