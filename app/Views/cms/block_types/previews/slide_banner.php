<?php
/** @var array<string, mixed> $config */
/** @var array<string, mixed> $data */
$image = is_array($config['image'] ?? null) ? $config['image'] : (is_array($data['image'] ?? null) ? $data['image'] : []);
$imageUrl = $image['url'] ?? '';
$heading = $data['heading'] ?? '';
$subtitle = $data['subtitle'] ?? '';
$ctaLabel = $data['cta_label'] ?? '';
?>
<div class="border border-slate-200 bg-white rounded-lg p-4 flex gap-4 items-center">
    <div class="h-16 w-24 bg-slate-100 rounded flex-shrink-0 flex items-center justify-center text-slate-400 overflow-hidden">
        <?php if ($imageUrl !== ''): ?>
            <img src="<?= esc($imageUrl) ?>" class="h-full w-full object-cover" />
        <?php else: ?>
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-image"><rect width="18" height="18" x="3" y="3" rx="2" ry="2"/><circle cx="9" cy="9" r="2"/><path d="m21 15-3.086-3.086a2 2 0 0 0-2.828 0L6 21"/></svg>
        <?php endif; ?>
    </div>
    <div class="flex-grow min-w-0">
        <div class="text-[10px] font-bold text-violet-500 uppercase mb-1">Diapositiva (Slide)</div>
        <h4 class="text-xs font-bold text-slate-800 truncate"><?= $heading !== '' ? esc($heading) : 'Sin título' ?></h4>
        <?php if ($subtitle !== ''): ?>
            <p class="text-[10px] text-slate-500 truncate"><?= esc($subtitle) ?></p>
        <?php endif; ?>
        <?php if ($ctaLabel !== ''): ?>
            <span class="inline-block mt-1 text-[9px] bg-slate-100 text-slate-700 px-1.5 py-0.5 rounded font-medium">
                Botón: <?= esc($ctaLabel) ?>
            </span>
        <?php endif; ?>
    </div>
</div>
