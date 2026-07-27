<?php $occurrence = $occurrence ?? []; ?>

<?php if (! empty($error)): ?>
    <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-5">
        <p class="text-sm text-red-600"><?= esc($error) ?></p>
    </div>
<?php elseif (! empty($occurrence)): ?>
    <?php $itemId = (string) ($occurrence['id'] ?? ''); ?>

    <?= view('components/display/admin_page_header', [
        'backUrl' => route_to('admin.occurrences.occurrences'),
        'backLabel' => 'Occurrences.occurrences_title',
        'eyebrow' => 'Occurrences.occurrences_details',
        'title' => (string) ($occurrence['name'] ?? $occurrence['title'] ?? $occurrence['id'] ?? lang('Occurrences.occurrences_details')),
    ]) ?>

    <?php ob_start(); ?>
    <section class="bg-white border border-gray-200 rounded-xl shadow-sm">
        <div class="border-b border-gray-100 px-5 py-4">
            <h3 class="text-sm font-semibold uppercase tracking-wider text-gray-700"><?= esc(lang('Occurrences.occurrences_details')) ?></h3>
        </div>
        <dl class="divide-y divide-gray-100">
            <?= view('components/display/field_row', [
                'label' => 'Occurrences.field_event_id',
                'value' => ($events[(string) ($occurrence['event_id'] ?? '')] ?? ($occurrence['event_id'] ?? '—'))
            ]) ?>
            <?= view('components/display/field_row', [
                'label' => 'Occurrences.field_venue_id',
                'value' => ($venues[(string) ($occurrence['venue_id'] ?? '')] ?? ($occurrence['venue_id'] ?? '—'))
            ]) ?>
            <?= view('components/display/field_row', [
                'label' => 'Occurrences.field_start_time',
                'value' => $occurrence['start_time'] ?? '—'
            ]) ?>
            <?= view('components/display/field_row', [
                'label' => 'Occurrences.field_end_time',
                'value' => $occurrence['end_time'] ?? '—'
            ]) ?>
            <?= view('components/display/field_row', [
                'label' => 'Occurrences.field_status',
                'value' => $occurrence['status'] ?? '—'
            ]) ?>
            <?= view('components/display/field_row', [
                'label' => 'Occurrences.field_capacity',
                'value' => $occurrence['capacity'] ?? '—'
            ]) ?>
            <?= view('components/display/field_row', [
                'label' => 'Occurrences.field_available_spots',
                'value' => $occurrence['available_spots'] ?? '—'
            ]) ?>
            <?= view('components/display/field_row', [
                'label' => 'TableColumns.created_at',
                'value' => $occurrence['created_at'] ?? '—',
            ]) ?>
        </dl>
    </section>
    <?php $mainContent = ob_get_clean(); ?>

    <?php ob_start(); ?>
    <a href="<?= route_to('admin.occurrences.occurrences.edit', $itemId) ?>" class="<?= esc(action_button_class('primary')) ?>"><?= lang('App.edit') ?></a>

    <?php $actionsContent = ob_get_clean(); ?>

    <?php ob_start(); ?>
    <form method="post" action="<?= route_to('admin.occurrences.occurrences.delete', $itemId) ?>" x-data @submit.prevent="$store.confirm.show('<?= esc(confirm_delete_message($occurrence['name'] ?? $occurrence['title'] ?? $occurrence['id'] ?? null), 'js') ?>', () => $el.submit())">
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
