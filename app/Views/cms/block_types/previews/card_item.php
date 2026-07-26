<?php
/** @var array<string, mixed> $config */
/** @var array<string, mixed> $data */
$image = is_array($config['image'] ?? null) ? $config['image'] : (is_array($data['image'] ?? null) ? $data['image'] : []);
$imageUrl = $image['url'] ?? '';
$title = $data['title'] ?? '';
$description = $data['description'] ?? '';
$linkLabel = $data['link_label'] ?? '';
?>
<div class="border border-slate-200 bg-white rounded-lg p-3 flex gap-3 items-start">
    <div class="h-10 w-10 bg-slate-50 rounded flex-shrink-0 flex items-center justify-center text-slate-400 overflow-hidden p-1 border border-slate-100">
        <?php if ($imageUrl !== ''): ?>
            <img src="<?= esc($imageUrl) ?>" class="h-full w-full object-contain" />
        <?php else: ?>
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-credit-card"><rect width="20" height="14" x="2" y="5" rx="2"/><line x1="2" y1="10" x2="22" y2="10"/></svg>
        <?php endif; ?>
    </div>
    <div class="flex-grow min-w-0">
        <div class="text-[10px] font-bold text-violet-500 uppercase mb-0.5">Tarjeta</div>
        <h4 class="text-xs font-bold text-slate-800 truncate"><?= $title !== '' ? esc($title) : 'Sin título' ?></h4>
        <?php if ($description !== ''): ?>
            <p class="text-[10px] text-slate-500 line-clamp-2 mt-0.5"><?= esc($description) ?></p>
        <?php endif; ?>
        <?php if ($linkLabel !== ''): ?>
            <span class="inline-block mt-1 text-[9px] text-violet-600 font-semibold">
                <?= esc($linkLabel) ?> →
            </span>
        <?php endif; ?>
    </div>
</div>
