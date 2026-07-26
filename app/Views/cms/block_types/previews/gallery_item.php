<?php
/** @var array<string, mixed> $config */
/** @var array<string, mixed> $data */
$image = is_array($config['image'] ?? null) ? $config['image'] : (is_array($data['image'] ?? null) ? $data['image'] : []);
$imageUrl = $image['url'] ?? '';
$caption = $data['caption'] ?? '';
?>
<div class="border border-slate-200 bg-white rounded-lg p-2 flex items-center gap-3">
    <div class="w-12 h-12 bg-slate-100 rounded overflow-hidden flex-shrink-0 flex items-center justify-center border border-slate-100">
        <?php if ($imageUrl !== ''): ?>
            <img src="<?= esc($imageUrl) ?>" class="w-full h-full object-cover" />
        <?php else: ?>
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-slate-300"><rect width="18" height="18" x="3" y="3" rx="2" ry="2"/><circle cx="9" cy="9" r="2"/><path d="m21 15-3.086-3.086a2 2 0 0 0-2.828 0L6 21"/></svg>
        <?php endif; ?>
    </div>
    <div class="min-w-0 flex-1">
        <div class="text-[10px] font-bold text-violet-500 uppercase">Imagen de Galería</div>
        <div class="text-xs text-slate-700 font-medium truncate">
            <?= $caption !== '' ? esc($caption) : 'Sin leyenda' ?>
        </div>
    </div>
</div>
