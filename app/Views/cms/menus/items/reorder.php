<?php
// Build indented list for hierarchical display
if (! function_exists('buildIndentedReorderList')) {
    function buildIndentedReorderList(array $items, ?int $parentId = null, int $depth = 0): array
    {
        $list = [];
        foreach ($items as $item) {
            $pid = isset($item['parent_id']) && $item['parent_id'] !== '' ? (int) $item['parent_id'] : null;
            if ($pid === $parentId) {
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
                $prefix = str_repeat('  ', $depth) . ($depth > 0 ? '└─ ' : '');
                $item['indented_label'] = $prefix . ($label ?: 'Untitled');
                $list[] = $item;
                $children = buildIndentedReorderList($items, (int) $item['id'], $depth + 1);
                foreach ($children as $child) {
                    $list[] = $child;
                }
            }
        }
        return $list;
    }
}

$reorderItems = buildIndentedReorderList($items ?? []);
?>

<?= view('components/display/reorder', [
    'items'      => $reorderItems,
    'saveUrl'    => route_to('admin.cms.menus.items.save_order', $menuId),
    'displayKey' => 'indented_label',
    'backUrl'    => route_to('admin.cms.menus.show', $menuId),
    'title'      => $title ?? lang('App.reorder'),
]) ?>
