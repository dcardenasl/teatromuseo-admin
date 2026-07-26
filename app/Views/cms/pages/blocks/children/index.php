<?php
$page        = $page        ?? [];
$parentBlock = $parentBlock ?? [];
$parentType  = $parentType  ?? [];
$children    = $children    ?? [];
$blockTypes  = $blockTypes  ?? [];
$ownerType   = $ownerType   ?? 'page';
$ownerLabel  = $ownerLabel  ?? 'Página';
$ownerBlocksRoute = $ownerBlocksRoute ?? 'admin.cms.pages.blocks';
$ownerCreateRoute = $ownerCreateRoute ?? 'admin.cms.pages.blocks.create';
$ownerChildrenReorderRoute = $ownerChildrenReorderRoute ?? 'admin.cms.pages.blocks.children.reorder';
$childLabel  = $childLabel  ?? 'Diapositiva';
$languages   = $languages   ?? [];
$blockTranslationStatus = $blockTranslationStatus ?? [];

$pageId     = (string) ($page['id'] ?? '');
$instanceId = (string) ($parentBlock['id'] ?? '');
$reorderUrl = route_to($ownerChildrenReorderRoute, $pageId, $instanceId);

$childLabelPlural = lang('Pages.' . ($ownerType === 'entry' ? 'child_label_subblock_plural' : 'child_label_slide_plural'));
?>
<div class="mb-4 flex items-center justify-between">
    <a href="<?= route_to($ownerBlocksRoute, $pageId) ?>" class="text-sm text-brand-600 hover:text-brand-700">
        &larr; <?= esc(lang('Pages.children_back_to_blocks', [$page['title'] ?? $ownerLabel])) ?>
    </a>
    <a href="<?= route_to($ownerCreateRoute, $pageId) ?>?parent_instance_id=<?= esc($instanceId) ?>"
       class="<?= esc(action_button_class('primary')) ?>">
        <?= ui_icon('plus', 'h-4 w-4 mr-1') ?> <?= esc(lang('Pages.children_add_button', [$childLabel])) ?>
    </a>
</div>

<section class="bg-white border border-gray-200 rounded-xl shadow-sm p-6"
         x-data="blockSorter('<?= esc($reorderUrl) ?>')">

    <div class="flex flex-col gap-3 border-b border-gray-100 pb-4 mb-6 sm:flex-row sm:items-center sm:justify-between">
        <div class="min-w-0">
                <h3 class="text-xl font-bold text-gray-900">
                <?= ui_icon($parentType['icon'] ?? 'gallery-horizontal', 'h-5 w-5 inline-block mr-2 text-brand-600') ?>
                <?= esc(lang('Pages.children_section_title', [$childLabelPlural, $parentType['name'] ?? lang('Pages.blocks_section_title')])) ?>
            </h3>
            <p class="text-sm text-gray-500 mt-1"><?= esc($ownerLabel) ?>: <strong><?= esc($page['title'] ?? '') ?></strong></p>
        </div>
        <div class="flex items-center gap-3">
            <span x-show="saving" class="flex items-center gap-1.5 text-xs text-gray-500">
                <svg class="w-3.5 h-3.5 animate-spin" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/>
                </svg>
                <?= esc(lang('Pages.blocks_saving')) ?>
            </span>
            <span x-show="saved" x-cloak class="flex items-center gap-1 text-xs text-green-600 font-medium">
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/>
                </svg>
                <?= esc(lang('Pages.blocks_saved')) ?>
            </span>
            <?php if (!empty($children)): ?>
            <button type="button"
                    x-show="dirty && !saving"
                    x-cloak
                    @click="saveOrder()"
                    class="inline-flex items-center gap-1.5 rounded-lg px-3 py-1.5 text-xs font-semibold bg-brand-600 text-white hover:bg-brand-700 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:ring-offset-1 transition-colors shadow-sm">
                <?= ui_icon('save', 'h-3.5 w-3.5') ?>
                <?= esc(lang('Pages.blocks_save_order')) ?>
            </button>
            <?php endif; ?>
        </div>
    </div>

    <?php if (empty($children)): ?>
        <div class="text-center py-12 border border-dashed border-gray-200 rounded-xl">
            <?= ui_icon('image', 'h-10 w-10 text-gray-300 mx-auto mb-3') ?>
            <p class="text-sm text-gray-500 font-medium"><?= esc(lang('Pages.children_empty_title', [strtolower($ownerLabel), strtolower($childLabelPlural)])) ?></p>
            <p class="text-xs text-gray-400 mt-1"><?= esc(lang('Pages.children_empty_desc', [$childLabel])) ?></p>
        </div>
    <?php else: ?>
        <div data-sortable-list class="space-y-3">
            <?php foreach ($children as $child):
                $childType = $blockTypes[$child['block_id']] ?? [];
                $isActive  = (bool) ($child['is_active'] ?? true);
                $childId   = (string) $child['id'];

                // Get first translation's heading for preview
                $previewText = '';
                foreach ($child['translations'] ?? [] as $t) {
                    $bd = is_array($t['block_data'] ?? null) ? $t['block_data'] : [];
                    if (!empty($bd['heading'])) {
                        $previewText = $bd['heading'];
                        break;
                    }
                }
                ?>
            <div data-block-id="<?= esc($childId) ?>"
                 class="flex flex-col gap-3 p-4 border border-gray-200 rounded-xl bg-gray-50/50 hover:bg-gray-50 transition-colors group lg:flex-row lg:items-center">

                <!-- Drag handle -->
                <div data-drag-handle
                     class="hidden lg:flex cursor-grab active:cursor-grabbing shrink-0 text-gray-300 hover:text-gray-500 transition-colors select-none"
                     title="<?= esc(lang('Pages.blocks_drag_handle_title'), 'attr') ?>">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M7 2a2 2 0 1 1-4 0 2 2 0 0 1 4 0zm6 0a2 2 0 1 1-4 0 2 2 0 0 1 4 0zM7 9a2 2 0 1 1-4 0 2 2 0 0 1 4 0zm6 0a2 2 0 1 1-4 0 2 2 0 0 1 4 0zM7 16a2 2 0 1 1-4 0 2 2 0 0 1 4 0zm6 0a2 2 0 1 1-4 0 2 2 0 0 1 4 0z"/>
                    </svg>
                </div>

                <!-- Slide preview image -->
                <?php
                $previewImg = '';
                foreach ($child['translations'] ?? [] as $t) {
                    $bd = is_array($t['block_data'] ?? null) ? $t['block_data'] : [];
                    if (is_array($bd['image'] ?? null) && ! empty($bd['image']['url'])) {
                        $previewImg = (string) $bd['image']['url'];
                        break;
                    }
                }
                $blockConfig = is_array($child['block_config'] ?? null) ? $child['block_config'] : [];
                $collectionKey = $blockConfig['collection_key'] ?? null;
                $matchedCollectionId = ($collectionKey !== null && isset($collectionsMap[(string) $collectionKey]))
                    ? $collectionsMap[(string) $collectionKey]
                    : null;
                ?>
                <?php if ($previewImg !== ''): ?>
                    <div class="shrink-0 w-16 h-10 rounded overflow-hidden border border-gray-200">
                        <img src="<?= esc($previewImg) ?>" alt="" class="w-full h-full object-cover">
                    </div>
                <?php else: ?>
                    <div class="bg-brand-50 text-brand-700 p-2.5 rounded-lg border border-brand-100 shrink-0">
                        <i data-lucide="image" class="w-5 h-5"></i>
                    </div>
                <?php endif; ?>

                <!-- Info -->
                <div class="flex-1 min-w-0">
                    <h4 class="font-semibold text-gray-900 text-sm truncate">
                        <?= esc($previewText !== '' ? $previewText : lang('Pages.children_untitled', [$childLabel])) ?>
                    </h4>
                    <p class="text-xs text-gray-500 font-mono mt-0.5">
                        <?= esc(lang('Pages.children_item_order_label', [strtolower($childLabel), (string) ($child['sort_order'] ?? '')])) ?>
                    </p>
                </div>

                <div class="grid grid-cols-2 gap-2 lg:hidden">
                    <button type="button"
                            @click="moveUp('<?= esc($childId, 'js') ?>')"
                            class="<?= esc(action_button_class('neutral')) ?> w-full justify-center">
                        <?= ui_icon('chevron-up', 'h-3.5 w-3.5') ?>
                        <?= esc(lang('App.move_up')) ?>
                    </button>
                    <button type="button"
                            @click="moveDown('<?= esc($childId, 'js') ?>')"
                            class="<?= esc(action_button_class('neutral')) ?> w-full justify-center">
                        <?= ui_icon('chevron-down', 'h-3.5 w-3.5') ?>
                        <?= esc(lang('App.move_down')) ?>
                    </button>
                </div>

                <!-- Status badge -->
                <div class="shrink-0">
                    <?php if ($isActive): ?>
                        <span class="inline-flex items-center rounded-full bg-green-50 px-2 py-1 text-xs font-medium text-green-700 ring-1 ring-inset ring-green-600/20"><?= esc(lang('Pages.blocks_status_active')) ?></span>
                    <?php else: ?>
                        <span class="inline-flex items-center rounded-full bg-gray-50 px-2 py-1 text-xs font-medium text-gray-600 ring-1 ring-inset ring-gray-500/10"><?= esc(lang('Pages.blocks_status_inactive')) ?></span>
                    <?php endif; ?>
                </div>

                <!-- Translation status badges -->
                <?= view('components/table/block_translation_badges', [
                    'languages'        => $languages,
                    'statusByLanguage' => $blockTranslationStatus[$childId] ?? [],
                    'editUrl'          => route_to($ownerType === 'entry' ? 'admin.cms.entries.blocks.edit' : 'admin.cms.pages.blocks.edit', $pageId, $childId),
                ]) ?>

                <!-- Actions -->
                <div class="flex w-full flex-wrap items-center gap-2 shrink-0 lg:w-auto lg:justify-end">
                    <?php if (!empty($matchedCollectionId)): ?>
                    <a href="<?= route_to('admin.cms.entries') . '?collection_id=' . $matchedCollectionId ?>"
                       class="<?= esc(action_button_class('primary')) ?> py-1 px-2.5 text-xs inline-flex items-center gap-1">
                        <?= ui_icon('list', 'h-3.5 w-3.5') ?>
                        <?= esc(lang('Pages.blocks_action_collection_entries')) ?>
                    </a>
                    <a href="<?= route_to('admin.cms.entries.create') . '?collection_id=' . $matchedCollectionId ?>"
                       class="<?= esc(action_button_class('neutral')) ?> py-1 px-2.5 text-xs inline-flex items-center gap-1">
                        <?= ui_icon('plus', 'h-3.5 w-3.5') ?>
                        <?= esc(lang('Pages.blocks_action_new_entry')) ?>
                    </a>
                    <?php endif; ?>
                    <a href="<?= route_to($ownerType === 'entry' ? 'admin.cms.entries.blocks.edit' : 'admin.cms.pages.blocks.edit', $pageId, $childId) ?>"
                       class="<?= esc(action_button_class('neutral')) ?> py-1 px-2.5 text-xs">
                        <?= esc(lang('Pages.blocks_action_edit')) ?>
                    </a>
                    <form method="post"
                          action="<?= route_to($ownerType === 'entry' ? 'admin.cms.entries.blocks.delete' : 'admin.cms.pages.blocks.delete', $pageId, $childId) ?>"
                          x-data
                          @submit.prevent="$store.confirm.show('<?= esc(confirm_delete_message($previewText !== '' ? $previewText : ($childLabel . ' ' . $childId)), 'js') ?>', () => $el.submit())">
                        <?= csrf_field() ?>
                        <button type="submit" class="<?= esc(action_button_class('danger')) ?> py-1 px-2.5 text-xs">
                            <?= esc(lang('Pages.blocks_action_delete')) ?>
                        </button>
                    </form>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <p class="hidden lg:flex text-xs text-gray-400 mt-4 items-center gap-1.5">
            <svg class="w-3.5 h-3.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 7.5 7.5 3m0 0L12 7.5M7.5 3v13.5m13.5 0L16.5 21m0 0L12 16.5m4.5 4.5V7.5"/>
            </svg>
            <?= esc(lang('Pages.blocks_drag_hint')) ?>
        </p>
        <p class="lg:hidden text-xs text-gray-400 mt-4 flex items-center gap-1.5">
            <?= ui_icon('arrow-up-down', 'h-3.5 w-3.5 shrink-0') ?>
            <?= esc(lang('Pages.blocks_reorder_hint_mobile')) ?>
        </p>
    <?php endif; ?>
</section>
