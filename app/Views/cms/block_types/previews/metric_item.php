<?php
/** @var array<string, mixed> $config */
/** @var array<string, mixed> $data */
$number = $data['number'] ?? '';
$label = $data['label'] ?? '';
$prefix = $data['prefix'] ?? '';
$suffix = $data['suffix'] ?? '';
$description = $data['description'] ?? '';
$sourceLabel = $data['source_label'] ?? '';
$icon = $data['icon'] ?? '';
?>
<div class="border border-slate-200 bg-white rounded-lg p-3 flex gap-3 items-center">
    <div class="h-8 w-8 bg-violet-50 text-violet-600 rounded flex-shrink-0 flex items-center justify-center">
        <?php if ($icon !== ''): ?>
            <span class="text-xs font-bold font-mono">[<?= esc(substr($icon, 0, 3)) ?>]</span>
        <?php else: ?>
            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-hash"><line x1="4" y1="9" x2="20" y2="9"/><line x1="4" y1="15" x2="20" y2="15"/><line x1="10" y1="3" x2="8" y2="21"/><line x1="16" y1="3" x2="14" y2="21"/></svg>
        <?php endif; ?>
    </div>
    <div class="flex-grow min-w-0">
        <div class="text-[10px] font-bold text-violet-500 uppercase mb-0.5">Métrica</div>
        <div class="text-xs font-black text-slate-800"><?= esc($prefix) ?><?= $number !== '' ? esc($number) : '0' ?><?= esc($suffix) ?></div>
        <div class="text-[9px] text-slate-500 truncate mt-0.5"><?= $label !== '' ? esc($label) : 'Sin etiqueta' ?></div>
        <?php if ($description !== ''): ?>
            <div class="text-[8px] text-slate-400 truncate mt-0.5"><?= esc($description) ?></div>
        <?php endif; ?>
        <?php if ($sourceLabel !== ''): ?>
            <div class="text-[8px] text-slate-400 truncate mt-0.5">Fuente: <?= esc($sourceLabel) ?></div>
        <?php endif; ?>
    </div>
</div>
