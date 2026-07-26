<?php
/** @var array<string, mixed> $config */
/** @var array<string, mixed> $data */
$title = $data['title'] ?? '';
$content = $data['content'] ?? '';
?>
<div class="border border-slate-200 bg-white rounded-lg p-3">
    <div class="text-[10px] font-bold text-violet-500 uppercase mb-1">Pestaña Individual</div>
    <div class="text-xs font-bold text-slate-800 mb-1">
        <?= $title !== '' ? esc($title) : 'Pestaña sin título' ?>
    </div>
    <?php if ($content !== ''): ?>
        <div class="text-[10px] text-slate-500 border-t border-slate-50 pt-2 line-clamp-2">
            <?= $content // Rich text output?>
        </div>
    <?php endif; ?>
</div>
