<?php $category = $category ?? []; ?>

<?php if (! empty($error)): ?>
    <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-5">
        <p class="text-sm text-red-600"><?= esc($error) ?></p>
    </div>
<?php elseif (! empty($category)): ?>
    <?php $itemId = (string) ($category['id'] ?? ''); ?>

    <?= view('components/display/admin_page_header', [
        'backUrl' => route_to('admin.museum.categories'),
        'backLabel' => 'Museum.categories_title',
        'eyebrow' => 'Museum.categories_details',
        'title' => (string) ($category['name'] ?? $category['title'] ?? $category['id'] ?? lang('Museum.categories_details')),
    ]) ?>

    <?php ob_start(); ?>
    <section class="bg-white border border-gray-200 rounded-xl shadow-sm">
        <div class="border-b border-gray-100 px-5 py-4">
            <h3 class="text-sm font-semibold uppercase tracking-wider text-gray-700"><?= esc(lang('Museum.categories_details')) ?></h3>
        </div>
        <dl class="divide-y divide-gray-100">
            <?= view('components/display/field_row', [
                'label' => 'Museum.field_name',
                'value' => $category['name'] ?? '—'
            ]) ?>
            <?= view('components/display/field_row', [
                'label' => 'Museum.field_slug',
                'value' => $category['slug'] ?? '—'
            ]) ?>
            <?= view('components/display/field_row', [
                'label' => 'Museum.field_icon',
                'value' => $category['icon'] ?? '—'
            ]) ?>
            <?= view('components/display/field_row', [
                'label' => 'Museum.field_short_description',
                'value' => $category['short_description'] ?? '—'
            ]) ?>
            <?= view('components/display/field_row', [
                'label' => 'TableColumns.created_at',
                'value' => $category['created_at'] ?? '—',
            ]) ?>
        </dl>
    </section>
    <?php $mainContent = ob_get_clean(); ?>

    <?php ob_start(); ?>
    <a href="<?= route_to('admin.museum.categories.edit', $itemId) ?>" class="<?= esc(action_button_class('primary')) ?>"><?= lang('App.edit') ?></a>

                <a href="<?= route_to('admin.museum.categories.reorder') ?>" class="<?= esc(action_button_class('neutral')) ?>">
                    <?= ui_icon('layers', 'h-3.5 w-3.5') ?>
                    <?= esc(lang('Museum.field_sort_order') ?? lang('App.reorder')) ?>
                </a>
    <?php $actionsContent = ob_get_clean(); ?>

    <?php ob_start(); ?>
    <form method="post" action="<?= route_to('admin.museum.categories.delete', $itemId) ?>" x-data @submit.prevent="$store.confirm.show('<?= esc(confirm_delete_message($category['name'] ?? $category['title'] ?? $category['id'] ?? null), 'js') ?>', () => $el.submit())">
        <?= csrf_field() ?>
        <button type="submit" class="<?= esc(action_button_class('danger')) ?>">
            <?= ui_icon('trash', 'h-3.5 w-3.5') ?>
            <?= esc(lang('App.delete')) ?>
        </button>
    </form>
    <?php $dangerContent = ob_get_clean(); ?>

    <?= view('components/display/admin_resource_layout', [
        'main' => $mainContent,
        'aside' => view('components/display/admin_actions_panel', [
            'content' => $actionsContent,
            'dangerContent' => $dangerContent,
        ]),
    ]) ?>
<?php endif; ?>
