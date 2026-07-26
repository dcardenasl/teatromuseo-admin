<?php
/**
 * Shared inline content-blocks panel for owner "show" pages (pages/entries).
 *
 * Required vars:
 * @var array<int, array<string, mixed>> $blocks     Top-level block instances (no parent_instance_id)
 * @var array<int, array<string, mixed>> $blockTypes Block catalog indexed by block_id
 * @var string $itemId    Owner id (page or entry)
 * @var string $ownerType 'page' | 'entry'
 * @var string $writePermission Permission code gating create/edit/delete/reorder actions
 *
 * Renders bare (no outer spacing/border) — the caller decides how it sits
 * relative to surrounding content (e.g. pages/show.php appends it after
 * translations inside the same card with its own border-t/mt-6 wrapper).
 *
 * Optional vars (translation status):
 * @var array<int, array<string, mixed>> $languages Active languages (id, code, is_default)
 * @var array<int|string, array<string, array<string, mixed>>> $blockTranslationStatus Keyed by block instance id -> language code -> {status, detail}
 */

use App\Modules\Cms\Support\BlockOwnerRouting;

$routes    = BlockOwnerRouting::routes($ownerType);
$canWrite  = has_permission($writePermission);
$descKey   = 'blocks_section_desc_' . $ownerType;
$emptyTKey = 'blocks_empty_title_' . $ownerType;
$emptyDKey = 'blocks_empty_desc_' . $ownerType;

$reorderUrl = route_to($routes['reorder'], $itemId);
$languages  = $languages ?? [];
$blockTranslationStatus = $blockTranslationStatus ?? [];
?>
<div>
    <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
        <div class="min-w-0">
            <h4 class="text-base font-semibold text-gray-900">
                <?= esc(lang('Pages.blocks_section_title')) ?>
            </h4>
            <p class="text-sm text-gray-500 mt-0.5">
                <?= esc(lang('Pages.' . $descKey)) ?>
            </p>
        </div>
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-end">
            <a href="<?= route_to($routes['index'], $itemId) ?>"
               class="<?= esc(action_button_class('neutral')) ?> w-full justify-center sm:w-auto">
                <?= ui_icon('layout-template', 'h-3.5 w-3.5') ?>
                <?= esc(lang('Pages.manage_blocks')) ?>
            </a>
            <?php if ($canWrite): ?>
            <a href="<?= route_to($routes['create'], $itemId) ?>"
               class="<?= esc(action_button_class('primary')) ?> w-full justify-center sm:w-auto">
                <?= ui_icon('plus', 'h-3.5 w-3.5') ?>
                <?= esc(lang('Pages.blocks_add')) ?>
            </a>
            <?php endif; ?>
        </div>
    </div>

    <div x-data="blockSorter('<?= esc($reorderUrl) ?>')">
        <!-- Saving indicator -->
        <div class="mb-3 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <span x-show="saving" x-cloak class="text-xs text-gray-400 italic">
                <?= esc(lang('Pages.blocks_saving')) ?>
            </span>
            <span x-show="saved && !saving" x-cloak class="text-xs text-green-600">
                <?= esc(lang('Pages.blocks_saved')) ?>
            </span>
            <button x-show="dirty && !saving" x-cloak type="button"
                    @click="saveOrder()"
                    class="<?= esc(action_button_class('primary')) ?> w-full justify-center !text-xs !py-1.5 !px-3 sm:w-auto">
                <?= ui_icon('save', 'h-3.5 w-3.5') ?>
                <?= esc(lang('Pages.blocks_save_order')) ?>
            </button>
        </div>

        <?php if (empty($blocks)): ?>
            <!-- Empty state -->
            <div class="text-center py-10 border border-dashed border-gray-200 rounded-xl">
                <?= ui_icon('layout-template', 'h-8 w-8 mx-auto text-gray-300 mb-2') ?>
                <p class="text-sm font-medium text-gray-500"><?= esc(lang('Pages.' . $emptyTKey)) ?></p>
                <p class="text-xs text-gray-400 mt-1"><?= esc(lang('Pages.' . $emptyDKey)) ?></p>
            </div>
        <?php else: ?>
            <ul id="block-sortable-list" class="space-y-2">
                <?php foreach ($blocks as $block):
                    $blockTypeData = $blockTypes[(int) ($block['block_id'] ?? 0)] ?? [];
                    $blockTypeName = $blockTypeData['name'] ?? 'Block #' . ($block['block_id'] ?? '?');
                    $isActive      = ! empty($block['is_active']);
                    $blockId       = (string) ($block['id'] ?? '');

                    // Content preview: first non-empty string from the first translation's block_data
                    $previewText = '';
                    $firstTrans  = is_array($block['translations'] ?? null) ? ($block['translations'][0] ?? []) : [];
                    $blockData   = is_array($firstTrans['block_data'] ?? null) ? $firstTrans['block_data'] : [];
                    foreach ($blockData as $val) {
                        if (is_string($val) && trim(strip_tags($val)) !== '') {
                            $previewText = mb_strimwidth(strip_tags($val), 0, 80, '…');
                            break;
                        }
                    }
                    ?>
                <li data-id="<?= esc($blockId) ?>"
                    class="flex flex-col gap-3 bg-white border border-gray-200 rounded-lg px-3 py-2.5 cursor-grab active:cursor-grabbing shadow-sm hover:shadow-md transition-shadow xl:flex-row xl:items-center">
                    <!-- Drag handle -->
                    <span data-drag-handle class="hidden flex-shrink-0 cursor-grab text-gray-300 hover:text-gray-500 xl:flex"
                          title="<?= esc(lang('Pages.blocks_drag_handle_title'), 'attr') ?>">
                        <?= ui_icon('grip-vertical', 'h-4 w-4') ?>
                    </span>

                    <!-- Block info -->
                    <div class="flex-1 min-w-0">
                        <span class="text-xs font-semibold text-gray-700"><?= esc($blockTypeName) ?></span>
                        <?php if ($previewText !== ''): ?>
                            <p class="text-xs text-gray-400 mt-0.5 truncate italic"><?= esc($previewText) ?></p>
                        <?php endif; ?>
                    </div>

                    <!-- Status badge -->
                    <span class="text-xs px-1.5 py-0.5 rounded-full flex-shrink-0 <?= $isActive ? 'bg-green-50 text-green-700' : 'bg-gray-100 text-gray-500' ?>">
                        <?= esc($isActive ? lang('Pages.blocks_status_active') : lang('Pages.blocks_status_inactive')) ?>
                    </span>

                    <!-- Translation status badges -->
                    <?= view('components/table/block_translation_badges', [
                        'languages'        => $languages,
                        'statusByLanguage' => $blockTranslationStatus[$blockId] ?? [],
                        'editUrl'          => route_to($routes['edit'], $itemId, $blockId),
                    ]) ?>

                    <div class="grid grid-cols-2 gap-2 xl:hidden w-full">
                        <button type="button"
                                @click="moveUp('<?= esc($blockId, 'js') ?>')"
                                class="<?= esc(action_button_class('neutral')) ?> w-full justify-center">
                            <?= ui_icon('chevron-up', 'h-3.5 w-3.5') ?>
                            <?= esc(lang('App.move_up')) ?>
                        </button>
                        <button type="button"
                                @click="moveDown('<?= esc($blockId, 'js') ?>')"
                                class="<?= esc(action_button_class('neutral')) ?> w-full justify-center">
                            <?= ui_icon('chevron-down', 'h-3.5 w-3.5') ?>
                            <?= esc(lang('App.move_down')) ?>
                        </button>
                    </div>

                    <!-- Quick actions -->
                    <div class="flex w-full flex-wrap items-center gap-1 flex-shrink-0 xl:w-auto xl:justify-end">
                        <?php if ($canWrite): ?>
                            <?php if (! empty($blockTypeData['is_container'])): ?>
                            <a href="<?= route_to($routes['children'], $itemId, $blockId) ?>"
                               class="<?= esc(action_button_class('neutral')) ?> !text-xs !py-1 !px-2">
                                <?= ui_icon('layers', 'h-3.5 w-3.5') ?>
                                <?= esc(lang('Pages.blocks_action_slides')) ?>
                            </a>
                            <?php endif; ?>
                            <a href="<?= route_to($routes['edit'], $itemId, $blockId) ?>"
                               class="<?= esc(action_button_class('neutral')) ?> !text-xs !py-1 !px-2">
                                <?= ui_icon('pencil', 'h-3 w-3') ?>
                                <?= esc(lang('Pages.blocks_action_edit')) ?>
                            </a>
                            <form method="post" action="<?= route_to($routes['delete'], $itemId, $blockId) ?>"
                                  x-data @submit.prevent="$store.confirm.show('<?= esc(confirm_delete_message($blockTypeData['name'] ?? $blockTypeData['block_key'] ?? $blockId), 'js') ?>', () => $el.submit())">
                                <?= csrf_field() ?>
                                <button type="submit" class="<?= esc(action_button_class('danger')) ?> !text-xs !py-1 !px-2">
                                    <?= ui_icon('trash', 'h-3 w-3') ?>
                                </button>
                            </form>
                        <?php endif; ?>
                    </div>
                </li>
                <?php endforeach; ?>
            </ul>

            <!-- Reorder hint -->
            <?php if ($canWrite): ?>
                <p class="hidden xl:block mt-3 text-xs text-gray-400 text-center">
                    <?= esc(lang('Pages.blocks_drag_hint')) ?>
                </p>
                <p class="xl:hidden mt-3 text-xs text-gray-400 text-center">
                    <?= esc(lang('Pages.blocks_reorder_hint_mobile')) ?>
                </p>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>
