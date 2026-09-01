<?php
$eventType = $eventType ?? [];
$languages = $languages ?? [];
?>

<?php if (! empty($error)): ?>
    <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-5"><p class="text-sm text-red-600"><?= esc($error) ?></p></div>
<?php elseif (! empty($eventType)): ?>
    <?php $itemId = (string) ($eventType['id'] ?? ''); ?>

    <?= view('components/display/admin_page_header', [
        'backUrl' => route_to('admin.events.event_types'),
        'backLabel' => 'Events.event_types_title',
        'eyebrow' => 'Events.event_types_details',
        'title' => (string) ($eventType['localized']['name'] ?? $eventType['name'] ?? $eventType['slug'] ?? ''),
    ]) ?>

    <?php if (! empty($languages)): ?>
        <?= view('components/table/translation_status_panel', [
            'languages' => $languages,
            'translations' => $eventType['translations'] ?? [],
            'requiredFields' => ['name', 'slug'],
            'sourceFields' => $eventType,
            'sourceUpdatedAt' => $eventType['updated_at'] ?? null,
            'editUrlTemplate' => route_to('admin.events.event_types.edit', $itemId),
            'canEdit' => has_permission('event.event-types.write'),
        ]) ?>
    <?php endif; ?>

    <?php ob_start(); ?>
    <section class="bg-white border border-gray-200 rounded-xl shadow-sm">
        <dl class="divide-y divide-gray-100">
            <?= view('components/display/field_row', ['label' => 'Events.field_event_type_name', 'value' => $eventType['name'] ?? '—']) ?>
            <?= view('components/display/field_row', ['label' => 'Events.field_is_active', 'value' => ((int) ($eventType['is_active'] ?? 0) === 1 ? lang('App.yes') : lang('App.no'))]) ?>
            <?= view('components/display/field_row', ['label' => 'TableColumns.created_at', 'value' => $eventType['created_at'] ?? '—']) ?>
        </dl>
    </section>
    <?php $mainContent = ob_get_clean(); ?>

    <?php ob_start(); ?>
    <?php if (has_permission('event.event-types.write')): ?>
        <a href="<?= route_to('admin.events.event_types.edit', $itemId) ?>" class="<?= esc(action_button_class('primary')) ?>"><?= lang('App.edit') ?></a>
    <?php endif; ?>
    <?php $actionsContent = ob_get_clean(); ?>

    <?php $dangerContent = ''; ?>
    <?php if (has_permission('event.event-types.delete')): ?>
        <?php ob_start(); ?>
        <form method="post" action="<?= route_to('admin.events.event_types.delete', $itemId) ?>" x-data @submit.prevent="$store.confirm.show('<?= esc(confirm_delete_message($eventType['name'] ?? $eventType['slug'] ?? null), 'js') ?>', () => $el.submit())">
            <?= csrf_field() ?>
            <button type="submit" class="<?= esc(action_button_class('danger')) ?>"><?= ui_icon('trash', 'h-3.5 w-3.5') ?> <?= esc(lang('App.delete')) ?></button>
        </form>
        <?php $dangerContent = ob_get_clean(); ?>
    <?php endif; ?>

    <?= view('components/display/admin_resource_layout', [
        'main' => $mainContent,
        'aside' => view('components/display/admin_actions_panel', ['content' => $actionsContent, 'dangerContent' => $dangerContent]),
    ]) ?>
<?php endif; ?>
