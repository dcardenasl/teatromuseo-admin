<?php
/** @var array<string, mixed> $config */
/** @var array<string, mixed> $data */
$logo = is_array($config['logo'] ?? null) ? $config['logo'] : (is_array($data['logo'] ?? null) ? $data['logo'] : []);
$logoUrl = $logo['url'] ?? '';
$name = $data['name'] ?? '';
$linkUrl = $data['link_url'] ?? '';
?>
<div class="border border-slate-200 bg-white rounded-lg p-3 flex gap-3 items-center">
    <div class="h-10 w-16 bg-slate-50 rounded flex-shrink-0 flex items-center justify-center text-slate-400 overflow-hidden p-1 border border-slate-100">
        <?php if ($logoUrl !== ''): ?>
            <img src="<?= esc($logoUrl) ?>" class="max-h-full max-w-full object-contain" />
        <?php else: ?>
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-image"><rect width="18" height="18" x="3" y="3" rx="2" ry="2"/><circle cx="9" cy="9" r="2"/><path d="m21 15-3.086-3.086a2 2 0 0 0-2.828 0L6 21"/></svg>
        <?php endif; ?>
    </div>
    <div class="flex-grow min-w-0">
        <div class="text-[10px] font-bold text-violet-500 uppercase mb-0.5">Logo de Auspiciador</div>
        <h4 class="text-xs font-bold text-slate-800 truncate"><?= $name !== '' ? esc($name) : 'Auspiciador sin nombre' ?></h4>
        <?php if ($linkUrl !== ''): ?>
            <span class="text-[8px] text-slate-400 truncate block mt-0.5"><?= esc($linkUrl) ?></span>
        <?php endif; ?>
    </div>
</div>
