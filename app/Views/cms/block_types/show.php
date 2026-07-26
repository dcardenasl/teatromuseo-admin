<?php
$blockType = $blockType ?? [];
/** @var list<array{resource: string, resource_id: int, role: string, label: string|null, context: array{owner_type: string, owner_id: int}, edit_url: string|null}> $usages */
$usages = $usages ?? [];
?>

<?php if (! empty($error)): ?>
    <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-5">
        <p class="text-sm text-red-600"><?= esc($error) ?></p>
    </div>
<?php elseif (! empty($blockType)): ?>
    <?php $itemId = (string) ($blockType['id'] ?? ''); ?>

    <?= view('components/display/admin_page_header', [
        'backUrl' => route_to('admin.cms.block_types'),
        'backLabel' => 'BlockTypes.block_types_title',
        'eyebrow' => 'BlockTypes.block_types_details',
        'title' => (string) ($blockType['name'] ?? $blockType['block_key'] ?? '—'),
        'subtitle' => (string) ($blockType['description'] ?? ''),
        'badge' => view('components/table/boolean_cell', ['value' => $blockType['is_active'] ?? false]),
    ]) ?>

    <?php ob_start(); ?>
    <section class="bg-white border border-gray-200 rounded-xl shadow-sm p-5">
        <h3 class="text-lg font-semibold text-gray-900"><?= lang('BlockTypes.block_types_details') ?></h3>
        <dl class="mt-4 divide-y divide-gray-100 text-sm">
            <?= view('components/display/field_row', [
                'label' => 'BlockTypes.field_block_key',
                'value' => $blockType['block_key'] ?? '—'
            ]) ?>
            <?= view('components/display/field_row', [
                'label' => 'BlockTypes.field_name',
                'value' => $blockType['name'] ?? '—'
            ]) ?>
            <?= view('components/display/field_row', [
                'label' => 'BlockTypes.field_description',
                'value' => $blockType['description'] ?? '—'
            ]) ?>
        </dl>
    </section>

    <section class="bg-white border border-gray-200 rounded-xl shadow-sm p-5">
        <h3 class="text-lg font-semibold text-gray-900"><?= lang('BlockTypes.field_schema_definition') ?></h3>
        <pre class="mt-4 bg-gray-50 text-gray-900 font-mono text-xs p-4 rounded-lg overflow-x-auto border border-gray-200"><?= esc(is_array($blockType['schema_definition']) ? json_encode($blockType['schema_definition'], JSON_PRETTY_PRINT) : (json_encode(json_decode($blockType['schema_definition'] ?? '{}'), JSON_PRETTY_PRINT) ?: '{}')) ?></pre>
    </section>

    <section class="bg-white border border-gray-200 rounded-xl shadow-sm p-5">
        <h3 class="text-lg font-semibold text-gray-900"><?= lang('BlockTypes.where_used') ?></h3>
        <?php if ($usages === []): ?>
            <p class="mt-3 text-sm text-gray-500"><?= esc(lang('BlockTypes.where_used_empty')) ?></p>
        <?php else: ?>
            <ul class="mt-3 divide-y divide-gray-100">
                <?php foreach ($usages as $usage): ?>
                    <?php
                    $ownerType = (string) ($usage['context']['owner_type'] ?? '');
                    $ownerId   = (int) ($usage['context']['owner_id'] ?? 0);
                    $editUrl   = (string) ($usage['edit_url'] ?? '');
                    $label     = (string) ($usage['label'] ?? '');
                    $ownerLabel = $ownerType === 'page' ? lang('BlockTypes.usage_page') : lang('BlockTypes.usage_entry');
                    ?>
                    <li class="py-2 flex items-center justify-between gap-3 text-sm">
                        <div class="min-w-0">
                            <?php if ($editUrl !== ''): ?>
                                <a href="<?= esc($editUrl) ?>" class="font-medium text-brand-600 hover:underline truncate block"><?= esc($label !== '' ? $label : ('#' . $ownerId)) ?></a>
                            <?php else: ?>
                                <p class="font-medium text-gray-900 truncate"><?= esc($label !== '' ? $label : ('#' . $ownerId)) ?></p>
                            <?php endif; ?>
                            <p class="text-xs text-gray-500"><?= esc($ownerLabel) ?> #<?= esc((string) $ownerId) ?></p>
                        </div>
                        <span class="text-xs text-gray-400 uppercase shrink-0"><?= esc((string) ($usage['resource_id'] ?? '')) ?></span>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </section>
    <?php $mainContent = ob_get_clean(); ?>

    <?php ob_start(); ?>
    <?= view('components/display/admin_meta_panel', [
        'title' => 'BlockTypes.block_types_details',
        'items' => [
            ['label' => 'BlockTypes.field_category', 'value' => $blockType['category'] ?? '—'],
            ['label' => 'BlockTypes.field_icon', 'value' => $blockType['icon'] ?? '—'],
            ['label' => 'BlockTypes.field_sort_order', 'value' => $blockType['sort_order'] ?? '—'],
            ['label' => 'TableColumns.created_at', 'value' => (string) ($blockType['created_at'] ?? '-')],
        ],
    ]) ?>

    <?= view('components/display/admin_meta_panel', [
        'title' => 'BlockTypes.field_supports_pages',
        'items' => [
            ['label' => 'BlockTypes.field_is_active', 'value' => view('components/table/boolean_cell', ['value' => $blockType['is_active'] ?? false]), 'isHtml' => true],
            ['label' => 'BlockTypes.field_supports_pages', 'value' => view('components/table/boolean_cell', ['value' => $blockType['supports_pages'] ?? false]), 'isHtml' => true],
            ['label' => 'BlockTypes.field_supports_entries', 'value' => view('components/table/boolean_cell', ['value' => $blockType['supports_entries'] ?? false]), 'isHtml' => true],
            ['label' => 'BlockTypes.field_is_container', 'value' => view('components/table/boolean_cell', ['value' => $blockType['is_container'] ?? false]), 'isHtml' => true],
        ],
    ]) ?>

    <?php ob_start(); ?>
    <?php if (has_permission('cms.blocks.write')): ?>
        <a href="<?= route_to('admin.cms.block_types.edit', $itemId) ?>" class="<?= esc(action_button_class('primary')) ?> w-full justify-center text-center">
            <?= ui_icon('pencil', 'h-3.5 w-3.5') ?>
            <?= lang('App.edit') ?>
        </a>
    <?php endif; ?>
    <?php $actionsContent = ob_get_clean(); ?>

    <?php ob_start(); ?>
    <?php if (has_permission('cms.blocks.write')): ?>
        <?php if ($usages === []): ?>
            <form method="post" action="<?= route_to('admin.cms.block_types.delete', $itemId) ?>" x-data @submit.prevent="$store.confirm.show('<?= esc(confirm_delete_message($blockType['name'] ?? $blockType['block_key'] ?? null), 'js') ?>', () => $el.submit())">
                <?= csrf_field() ?>
                <button type="submit" class="<?= esc(action_button_class('danger')) ?> w-full justify-center">
                    <?= ui_icon('trash', 'h-3.5 w-3.5') ?>
                    <?= esc(lang('App.delete')) ?>
                </button>
            </form>
        <?php else: ?>
            <button type="button" class="<?= esc(action_button_class('danger')) ?> w-full justify-center opacity-50 cursor-not-allowed" disabled title="<?= esc(lang('BlockTypes.cannot_delete_in_use')) ?>">
                <?= ui_icon('trash', 'h-3.5 w-3.5') ?>
                <?= esc(lang('App.delete')) ?>
            </button>
        <?php endif; ?>
    <?php endif; ?>
    <?php $dangerContent = ob_get_clean(); ?>

    <?= view('components/display/admin_actions_panel', [
        'content' => $actionsContent,
        'dangerContent' => $dangerContent,
    ]) ?>
    <?php $asideContent = ob_get_clean(); ?>

    <?= view('components/display/admin_resource_layout', [
        'main' => $mainContent,
        'aside' => $asideContent,
    ]) ?>
<?php endif; ?>
