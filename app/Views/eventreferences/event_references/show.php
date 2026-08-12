<?php $eventReference = $eventReference ?? []; ?>

<?php if (! empty($error)): ?>
    <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-5">
        <p class="text-sm text-red-600"><?= esc($error) ?></p>
    </div>
<?php elseif (! empty($eventReference)): ?>
<?php
    $itemId = (string) ($eventReference['id'] ?? '');
    $eventReferenceEventLabel = $events[(string) ($eventReference['event_id'] ?? '')] ?? lang('EventReferences.event_references_details');
    $eventReferenceLabel = trim($eventReferenceEventLabel . (! empty($eventReference['source_id']) ? ' · ' . $eventReference['source_id'] : ''));
    ?>

    <?= view('components/display/admin_page_header', [
            'backUrl' => route_to('admin.eventreferences.event_references'),
            'backLabel' => 'EventReferences.event_references_title',
            'eyebrow' => 'EventReferences.event_references_details',
            'title' => $eventReferenceLabel,
        ]) ?>

    <?php ob_start(); ?>
    <section class="bg-white border border-gray-200 rounded-xl shadow-sm">
        <div class="border-b border-gray-100 px-5 py-4">
            <h3 class="text-sm font-semibold uppercase tracking-wider text-gray-700"><?= esc(lang('EventReferences.event_references_details')) ?></h3>
        </div>
        <dl class="divide-y divide-gray-100">
            <?= view('components/display/field_row', [
                    'label' => 'EventReferences.field_event_id',
                    'value' => ($events[(string) ($eventReference['event_id'] ?? '')] ?? ($eventReference['event_id'] ?? '—'))
                ]) ?>
            <?= view('components/display/field_row', [
                    'label' => 'EventReferences.field_source_system',
                    'value' => $eventReference['source_system'] ?? '—'
                ]) ?>
            <?= view('components/display/field_row', [
                    'label' => 'EventReferences.field_source_type',
                    'value' => $eventReference['source_type'] ?? '—'
                ]) ?>
            <?= view('components/display/field_row', [
                    'label' => 'EventReferences.field_source_id',
                    'value' => $eventReference['source_id'] ?? '—'
                ]) ?>
            <?= view('components/display/field_row', [
                    'label' => 'EventReferences.field_relation',
                    'value' => $eventReference['relation'] ?? '—'
                ]) ?>
            <?= view('components/display/field_row', [
                    'label' => 'EventReferences.field_metadata',
                    'value' => $eventReference['metadata'] ?? '—'
                ]) ?>
            <?= view('components/display/field_row', [
                    'label' => 'TableColumns.created_at',
                    'value' => $eventReference['created_at'] ?? '—',
                ]) ?>
        </dl>
    </section>
    <?php $mainContent = ob_get_clean(); ?>

    <?php ob_start(); ?>
    <?php if (has_permission('event.event-references.write')): ?>
        <a href="<?= route_to('admin.eventreferences.event_references.edit', $itemId) ?>" class="<?= esc(action_button_class('primary')) ?>"><?= lang('App.edit') ?></a>
    <?php endif; ?>

    <?php $actionsContent = ob_get_clean(); ?>

    <?php $dangerContent = ''; ?>
    <?php if (has_permission('event.event-references.delete')): ?>
        <?php ob_start(); ?>
        <form method="post" action="<?= route_to('admin.eventreferences.event_references.delete', $itemId) ?>" x-data @submit.prevent="$store.confirm.show('<?= esc(confirm_delete_message($eventReferenceLabel), 'js') ?>', () => $el.submit())">
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
