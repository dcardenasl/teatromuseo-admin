<?php
/** @var array<string, mixed> $config */
/** @var array<string, mixed> $data */
$title = $data['title'] ?? '';
$content = $data['content'] ?? '';
$isOpen = filter_var($config['is_open'] ?? false, FILTER_VALIDATE_BOOL);
?>
<div class="border border-slate-200 bg-white rounded-lg p-3">
    <div class="text-[10px] font-bold text-violet-500 uppercase mb-1">Elemento de Acordeón</div>
    <div class="flex justify-between items-center text-xs font-bold text-slate-800">
        <span><?= $title !== '' ? esc($title) : 'Título vacío' ?></span>
        <span class="text-[9px] px-1.5 py-0.5 rounded font-medium <?= $isOpen ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-600' ?>">
            <?= $isOpen ? 'Abierta' : 'Cerrada' ?>
        </span>
    </div>
    <?php if ($content !== ''): ?>
        <div class="text-[10px] text-slate-500 mt-2 border-t border-slate-50 pt-2 line-clamp-3">
            <?= $content // Rich text output?>
        </div>
    <?php endif; ?>
</div>
