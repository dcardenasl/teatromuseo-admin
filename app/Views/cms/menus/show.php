<?php
$menu = $menu ?? [];
$pages = $pages ?? [];
$entries = $entries ?? [];
$collections = $collections ?? [];

// Build language name map: id → name
$langMap = [];
foreach ($languages as $lang) {
    if (is_array($lang) && isset($lang['id'])) {
        $langMap[(int) $lang['id']] = $lang['name'] ?? $lang['code'] ?? "Lang #{$lang['id']}";
    }
}
?>

<div class="mb-4 flex items-center gap-2">
    <a href="<?= route_to('admin.cms.menus') ?>" class="text-sm text-brand-600 hover:text-brand-700">&larr; <?= esc(lang('Menus.menus_title')) ?></a>
</div>

<?php if (! empty($error)): ?>
    <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-5">
        <p class="text-sm text-red-600"><?= esc($error) ?></p>
    </div>
<?php elseif (! empty($menu)): ?>
    <?php $itemId = (string) ($menu['id'] ?? ''); ?>
    <?= view('components/table/translation_status_panel', ['languages' => $languages, 'translations' => $menu['translations'] ?? [], 'requiredFields' => ['name'], 'sourceFields' => $menu, 'sourceUpdatedAt' => $menu['updated_at'] ?? null, 'editUrlTemplate' => route_to('admin.cms.menus.edit', $itemId)]) ?>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- Left column: details + translations -->
        <div class="lg:col-span-1 space-y-6 lg:order-2">

            <!-- Menu Details Card -->
            <section class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
                <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100">
                    <div class="flex items-center gap-2">
                        <h3 class="text-base font-semibold text-gray-900"><?= lang('Menus.menus_details') ?></h3>
                        <?php
                        $isActive = ! empty($menu['is_active']);
    $badgeClass = $isActive ? 'bg-green-50 text-green-700 border-green-200' : 'bg-gray-100 text-gray-600 border-gray-200';
    $badgeLabel = $isActive ? lang('Menus.field_is_active_on') : lang('Menus.field_is_active_off');
    ?>
                        <span class="inline-flex items-center rounded-md border px-2 py-0.5 text-xs font-semibold <?= esc($badgeClass) ?>">
                            <?= esc($badgeLabel) ?>
                        </span>
                    </div>
                    <div class="flex items-center gap-2">
                        <?php if (has_permission('cms.menus.write')): ?>
                        <a href="<?= route_to('admin.cms.menus.edit', $itemId) ?>" class="<?= esc(action_button_class()) ?>">
                            <?= ui_icon('pencil', 'h-3.5 w-3.5') ?>
                            <?= lang('App.edit') ?>
                        </a>
                        <form method="post" action="<?= route_to('admin.cms.menus.delete', $itemId) ?>" x-data @submit.prevent="$store.confirm.show('<?= esc(confirm_delete_message($menu['name'] ?? $menu['menu_key'] ?? null), 'js') ?>', () => $el.submit())">
                            <?= csrf_field() ?>
                            <button type="submit" class="<?= esc(action_button_class('danger')) ?>">
                                <?= ui_icon('trash', 'h-3.5 w-3.5') ?>
                                <?= esc(lang('App.delete')) ?>
                            </button>
                        </form>
                        <?php endif; ?>
                    </div>
                </div>

                <dl class="divide-y divide-gray-100 text-sm">
                    <div class="px-5 py-3 grid grid-cols-5 gap-2">
                        <dt class="col-span-2 text-gray-500 font-medium self-center"><?= lang('Menus.field_menu_key') ?></dt>
                        <dd class="col-span-3 text-gray-900 font-mono font-medium"><?= esc($menu['menu_key'] ?? '—') ?></dd>
                    </div>
                    <div class="px-5 py-3 grid grid-cols-5 gap-2">
                        <dt class="col-span-2 text-gray-500 font-medium self-center"><?= lang('Menus.menus_details_location') ?></dt>
                        <dd class="col-span-3">
                            <?php if (! empty($menu['location'])): ?>
                                <span class="inline-flex items-center rounded-md bg-gray-100 px-2 py-0.5 text-xs font-mono text-gray-700"><?= esc($menu['location']) ?></span>
                            <?php else: ?>
                                <span class="text-gray-400">—</span>
                            <?php endif; ?>
                        </dd>
                    </div>
                    <div class="px-5 py-3 grid grid-cols-5 gap-2">
                        <dt class="col-span-2 text-gray-500 font-medium self-center"><?= lang('TableColumns.created_at') ?></dt>
                        <dd class="col-span-3 text-gray-700"><?= esc(format_date($menu['created_at'] ?? null) ?: ($menu['created_at'] ?? '—')) ?></dd>
                    </div>
                    <?php if (! empty($menu['updated_at'])): ?>
                    <div class="px-5 py-3 grid grid-cols-5 gap-2">
                        <dt class="col-span-2 text-gray-500 font-medium self-center"><?= lang('TableColumns.updated_at') ?></dt>
                        <dd class="col-span-3 text-gray-700"><?= esc(format_date($menu['updated_at'] ?? null) ?: ($menu['updated_at'] ?? '—')) ?></dd>
                    </div>
                    <?php endif; ?>
                </dl>
            </section>

            <!-- Translations Card -->
            <section class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
                <div class="px-5 py-3 border-b border-gray-100">
                    <h4 class="text-xs font-bold text-gray-600 uppercase tracking-wider"><?= esc(lang('Menus.menus_translations_title')) ?></h4>
                </div>
                <?php if (! empty($menu['translations']) && is_array($menu['translations'])): ?>
                    <div class="divide-y divide-gray-100">
                        <?php foreach ($menu['translations'] as $t): ?>
                            <?php $langName = $langMap[(int) ($t['language_id'] ?? 0)] ?? "Lang #{$t['language_id']}"; ?>
                            <div class="flex items-center gap-3 px-5 py-3 text-sm">
                                <span class="shrink-0 inline-flex items-center rounded-md bg-brand-50 text-brand-700 border border-brand-100 px-2 py-0.5 text-xs font-semibold font-mono">
                                    <?= esc($langName) ?>
                                </span>
                                <span class="text-gray-900 font-medium truncate"><?= esc($t['name'] ?? '—') ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p class="px-5 py-4 text-xs text-gray-500"><?= esc(lang('Menus.menus_no_translations')) ?></p>
                <?php endif; ?>
            </section>
        </div>

        <!-- Right column: menu items tree -->
        <div class="lg:col-span-2 lg:order-1">
            <section class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
                <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100">
                    <div class="flex items-center gap-2">
                        <h3 class="text-base font-semibold text-gray-900"><?= lang('Menus.menus_items_title') ?></h3>
                        <?php if (! empty($items)): ?>
                            <span class="inline-flex items-center rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-semibold text-gray-600">
                                <?= count($items) ?>
                            </span>
                        <?php endif; ?>
                    </div>
                    <?php if (has_permission('cms.menus.write')): ?>
                    <div class="flex items-center gap-2">
                        <?php if (! empty($items)): ?>
                        <a href="<?= route_to('admin.cms.menus.items.reorder', $itemId) ?>" class="inline-flex items-center gap-1.5 rounded-lg border border-gray-200 bg-gray-50 px-3 py-1.5 text-xs font-semibold text-gray-700 shadow-sm transition hover:bg-gray-100">
                            <?= ui_icon('list', 'h-3.5 w-3.5') ?>
                            <?= lang('App.reorder') ?? 'Reordenar' ?>
                        </a>
                        <?php endif; ?>
                        <a href="<?= route_to('admin.cms.menus.items.create', $itemId) ?>" class="inline-flex items-center gap-1.5 rounded-lg bg-brand-600 px-3 py-1.5 text-xs font-semibold text-white shadow-sm transition hover:bg-brand-700">
                            <?= ui_icon('plus', 'h-3.5 w-3.5') ?>
                            <?= lang('Menus.menus_items_create') ?>
                        </a>
                    </div>
                    <?php endif; ?>
                </div>

                <?php if (empty($items)): ?>
                    <?= view('components/display/empty_state', [
    'title' => 'Menus.menus_items_empty',
    'description' => 'Menus.menus_items_empty_desc',
    'actionUrl' => route_to('admin.cms.menus.items.create', $itemId),
    'actionLabel' => 'Menus.menus_items_create',
                    ]) ?>
                <?php else: ?>
                    <!-- Legend -->
                    <div class="flex items-center gap-4 px-4 py-2 bg-gray-50 border-b border-gray-100 text-xs text-gray-500">
                        <span class="flex items-center gap-1"><?= ui_icon('external-link', 'h-3 w-3') ?> <?= esc(lang('Menus.menus_item_new_tab')) ?></span>
                        <span class="flex items-center gap-1"><span class="inline-block w-2 h-2 rounded-sm bg-blue-200 shrink-0"></span> <?= esc(lang('Menus.items_link_type_page')) ?></span>
                        <span class="flex items-center gap-1"><span class="inline-block w-2 h-2 rounded-sm bg-purple-200 shrink-0"></span> <?= esc(lang('Menus.items_link_type_entry')) ?></span>
                        <span class="flex items-center gap-1"><span class="inline-block w-2 h-2 rounded-sm bg-orange-200 shrink-0"></span> <?= esc(lang('Menus.items_link_type_collection_listing')) ?></span>
                        <span class="flex items-center gap-1"><span class="inline-block w-2 h-2 rounded-sm bg-green-200 shrink-0"></span> <?= esc(lang('Menus.items_link_type_custom_url')) ?></span>
                    </div>

                    <div class="divide-y divide-gray-100">
<?php
        /**
         * @param array<int, array<string, mixed>> $items
         * @param array<string, string> $langMap
         * @param array<string, string> $pages
         * @param array<string, string> $entries
         * @param array<string, string> $collections
         * @param array<int, array<string, mixed>> $languages
         */
        $renderMenuTree = function (array $items, array $langMap, array $pages, array $entries, array $collections, array $languages, ?int $parentId = null, int $depth = 0) use (&$renderMenuTree): void {
            $items = array_filter($items, 'is_array');
            $siblings = array_values(array_filter($items, static function (array $item) use ($parentId): bool {
                $pid = isset($item['parent_id']) && $item['parent_id'] !== '' ? (int) $item['parent_id'] : null;
                return $pid === $parentId;
            }));
            $total = count($siblings);

            foreach ($siblings as $idx => $item) {
                $isLast = ($idx === $total - 1);

                // Resolve label
                $label = $item['label'] ?? '';
                if ($label === '' && ! empty($item['translations']) && is_array($item['translations'])) {
                    foreach ($item['translations'] as $t) {
                        if (! empty($t['label'])) {
                            $label = $t['label'];
                            break;
                        }
                    }
                }

                // Resolve link badge text & color
                $linkBadge = '';
                $linkBadgeClass = 'bg-gray-100 text-gray-500';
                switch ($item['link_type'] ?? '') {
                    case 'page':
                        $name = $pages[(string) ($item['page_id'] ?? '')] ?? null;
                        $suffix = $name ? ': ' . $name : (! empty($item['page_id']) ? ' #' . $item['page_id'] : '');
                        $linkBadge = lang('Menus.items_link_type_page') . $suffix;
                        $linkBadgeClass = 'bg-blue-50 text-blue-700';
                        break;
                    case 'entry':
                        $name = $entries[(string) ($item['entry_id'] ?? '')] ?? null;
                        $suffix = $name ? ': ' . $name : (! empty($item['entry_id']) ? ' #' . $item['entry_id'] : '');
                        $linkBadge = lang('Menus.items_link_type_entry') . $suffix;
                        $linkBadgeClass = 'bg-purple-50 text-purple-700';
                        break;
                    case 'collection_listing':
                        $name = $collections[(string) ($item['collection_id'] ?? '')] ?? null;
                        $suffix = $name ? ': ' . $name : (! empty($item['collection_id']) ? ' #' . $item['collection_id'] : '');
                        $linkBadge = lang('Menus.items_link_type_collection_listing') . $suffix;
                        $linkBadgeClass = 'bg-orange-50 text-orange-700';
                        break;
                    case 'custom_url':
                        $url = '';
                        foreach ($item['translations'] ?? [] as $t) {
                            if (! empty($t['custom_url'])) {
                                $url = $t['custom_url'];
                                break;
                            }
                        }
                        $linkBadge = lang('Menus.items_link_type_custom_url') . ($url ? ': ' . $url : '');
                        $linkBadgeClass = 'bg-green-50 text-green-700';
                        break;
                    default:
                        $linkBadge = lang('Menus.items_link_type_no_link');
                        $linkBadgeClass = 'bg-gray-100 text-gray-500';
                }

                $indentPx = 16 + ($depth * 24);
                $connector = $depth > 0 ? ($isLast ? '└─' : '├─') : '';
                $itemTranslations = is_array($item['translations'] ?? null) ? $item['translations'] : [];
                $itemStates = [];
                foreach ($languages as $language) {
                    $itemStates[] = \App\Modules\Cms\Support\TranslationStatus::evaluate(
                        ['id' => (int) ($language['id'] ?? 0)],
                        $itemTranslations,
                        ['label'],
                        $item['updated_at'] ?? null,
                    )['status'];
                }
                $itemState = in_array('missing', $itemStates, true) ? 'missing' : (in_array('incomplete', $itemStates, true) ? 'incomplete' : (in_array('outdated', $itemStates, true) ? 'outdated' : 'complete'));
                $itemFocusLanguageId = 0;
                foreach ($itemStates as $stateIndex => $state) {
                    if ($state !== 'complete') {
                        $itemFocusLanguageId = (int) ($languages[$stateIndex]['id'] ?? 0);
                        break;
                    }
                }
                ?>
                                    <div class="flex items-center justify-between py-3 pr-4 hover:bg-gray-50/70 transition-colors" style="padding-left: <?= $indentPx ?>px">
                                        <div class="flex items-center gap-2 min-w-0 flex-1">
                                            <?php if ($depth > 0): ?>
                                                <span class="shrink-0 text-gray-300 text-xs font-mono select-none"><?= esc($connector) ?></span>
                                            <?php endif; ?>

                                            <span class="font-medium text-gray-900 truncate"><?= esc($label ?: 'Untitled') ?></span>
                                            <span class="shrink-0 rounded-full px-1.5 py-0.5 text-[10px] font-semibold <?= \App\Modules\Cms\Support\TranslationStatus::badgeClasses($itemState) ?>"><?= esc(lang('Translations.status_' . $itemState)) ?></span>

                                            <span class="shrink-0 inline-flex items-center rounded-md px-1.5 py-0.5 text-[11px] font-medium <?= esc($linkBadgeClass) ?>" title="<?= esc(lang('Menus.items_link_type_label')) ?>">
                                                <?= esc($linkBadge) ?>
                                            </span>

                                            <?php if (($item['link_target'] ?? '_self') === '_blank'): ?>
                                                <span class="shrink-0 text-gray-400" title="<?= esc(lang('Menus.menus_item_new_tab')) ?>">
                                                    <?= ui_icon('external-link', 'h-3 w-3') ?>
                                                </span>
                                            <?php endif; ?>

                                            <?php if (! empty($item['icon'])): ?>
                                                <span class="shrink-0 inline-flex items-center gap-1 text-[11px] bg-brand-50 text-brand-600 px-1.5 py-0.5 rounded font-mono" title="Icon">
                                                    <?= ui_icon('zap', 'h-3 w-3') ?><?= esc($item['icon']) ?>
                                                </span>
                                            <?php endif; ?>

                                            <?php if (! empty($item['css_class'])): ?>
                                                <span class="shrink-0 text-[11px] bg-violet-50 text-violet-600 px-1.5 py-0.5 rounded font-mono" title="CSS class">
                                                    .<?= esc($item['css_class']) ?>
                                                </span>
                                            <?php endif; ?>

                                            <?php if (empty($item['is_active'])): ?>
                                                <span class="shrink-0 text-[10px] font-bold text-red-600 bg-red-50 border border-red-100 px-1.5 py-0.5 rounded uppercase">
                                                    <?= esc(lang('Menus.menus_item_inactive')) ?>
                                                </span>
                                            <?php endif; ?>
                                        </div>

                                        <div class="flex items-center gap-2 shrink-0 ml-3">
                                            <span class="text-[10px] text-gray-400 font-mono" title="<?= esc(lang('Menus.items_sort_order_label')) ?>">#<?= esc((string) ($item['sort_order'] ?? 0)) ?></span>
                                            <?php if (has_permission('cms.menus.write')): ?>
                                            <a href="<?= esc(\App\Modules\Cms\Support\TranslationStatus::editUrl(route_to('admin.cms.menus.items.edit', $item['menu_id'], $item['id']), $itemFocusLanguageId)) ?>" class="px-2.5 py-1 text-xs border border-gray-200 bg-gray-50 text-gray-700 hover:bg-gray-100 rounded-lg shadow-sm transition"><?= esc(lang('Menus.menus_item_edit')) ?></a>
                                            <form method="post" action="<?= route_to('admin.cms.menus.items.delete', $item['menu_id'], $item['id']) ?>" x-data @submit.prevent="$store.confirm.show('<?= esc(confirm_delete_message($label ?: ($item['translations'][0]['label'] ?? null)), 'js') ?>', () => $el.submit())" class="inline">
                                                <?= csrf_field() ?>
                                                <button type="submit" class="px-2.5 py-1 text-xs border border-red-200 bg-red-50 text-red-600 hover:bg-red-100 rounded-lg shadow-sm transition"><?= esc(lang('Menus.menus_item_delete')) ?></button>
                                            </form>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <?php
                $renderMenuTree($items, $langMap, $pages, $entries, $collections, $languages, (int) $item['id'], $depth + 1);
            }
        };

$renderMenuTree($items, $langMap, $pages, $entries, $collections, $languages);
?>
                    </div>
                <?php endif; ?>
            </section>
        </div>
    </div>
<?php endif; ?>
