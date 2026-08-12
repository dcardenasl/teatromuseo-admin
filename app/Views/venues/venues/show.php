<?php $venue = $venue ?? []; ?>

<?php if (! empty($error)): ?>
    <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-5">
        <p class="text-sm text-red-600"><?= esc($error) ?></p>
    </div>
<?php elseif (! empty($venue)): ?>
    <?php $itemId = (string) ($venue['id'] ?? ''); ?>

    <?= view('components/display/admin_page_header', [
        'backUrl' => route_to('admin.venues.venues'),
        'backLabel' => 'Venues.venues_title',
        'eyebrow' => 'Venues.venues_details',
        'title' => (string) ($venue['name'] ?? $venue['title'] ?? $venue['id'] ?? lang('Venues.venues_details')),
    ]) ?>

    <?php ob_start(); ?>
    <section class="bg-white border border-gray-200 rounded-xl shadow-sm">
        <div class="border-b border-gray-100 px-5 py-4">
            <h3 class="text-sm font-semibold uppercase tracking-wider text-gray-700"><?= esc(lang('Venues.venues_details')) ?></h3>
        </div>
        <dl class="divide-y divide-gray-100">
            <?= view('components/display/field_row', [
                'label' => 'Venues.field_name',
                'value' => $venue['name'] ?? '—'
            ]) ?>
            <?= view('components/display/field_row', [
                'label' => 'Venues.field_slug',
                'value' => $venue['slug'] ?? '—'
            ]) ?>
            <?= view('components/display/field_row', [
                'label' => 'Venues.field_description',
                'value' => $venue['description'] ?? '—'
            ]) ?>
            <?= view('components/display/field_row', [
                'label' => 'Venues.field_capacity',
                'value' => $venue['capacity'] ?? '—'
            ]) ?>
            <?= view('components/display/field_row', [
                'label' => 'Venues.field_is_active',
                'value' => $venue['is_active'] ?? '—'
            ]) ?>
            <?= view('components/display/field_row', [
                'label' => 'TableColumns.created_at',
                'value' => $venue['created_at'] ?? '—',
            ]) ?>
        </dl>
    </section>
    <?php $mainContent = ob_get_clean(); ?>

    <?php ob_start(); ?>
    <?php if (has_permission('event.venues.write')): ?>
        <a href="<?= route_to('admin.venues.venues.edit', $itemId) ?>" class="<?= esc(action_button_class('primary')) ?>"><?= lang('App.edit') ?></a>
    <?php endif; ?>

    <?php $actionsContent = ob_get_clean(); ?>

    <?php $dangerContent = ''; ?>
    <?php if (has_permission('event.venues.delete')): ?>
        <?php ob_start(); ?>
        <form method="post" action="<?= route_to('admin.venues.venues.delete', $itemId) ?>" x-data @submit.prevent="$store.confirm.show('<?= esc(confirm_delete_message($venue['name'] ?? $venue['title'] ?? $venue['id'] ?? null), 'js') ?>', () => $el.submit())">
            <?= csrf_field() ?>
            <button type="submit" class="<?= esc(action_button_class('danger')) ?>">
                <?= ui_icon('trash', 'h-3.5 w-3.5') ?>
                <?= esc(lang('App.delete')) ?>
            </button>
        </form>
        <?php $dangerContent = ob_get_clean(); ?>
    <?php endif; ?>

    <?= view('components/display/admin_resource_layout', [
        'main' => $mainContent,
        'aside' => view('components/display/admin_actions_panel', [
            'content' => $actionsContent,
            'dangerContent' => $dangerContent,
        ]),
    ]) ?>
<?php endif; ?>
