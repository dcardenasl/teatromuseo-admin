<?php
/** @var array<string, mixed> $config */
/** @var array<string, mixed> $data */
$variant = (string) ($config['variant'] ?? 'light');
$cssClass = esc($config['css_class'] ?? '');

$containerClass = 'border border-dashed border-sky-300 rounded-xl p-4 ';
$statBoxClass = 'bg-white border border-slate-200';
$textColor = 'text-slate-800';
$numColor = 'text-violet-600';

if ($variant === 'dark') {
    $containerClass .= 'bg-slate-900 text-white';
    $statBoxClass = 'bg-slate-850 border border-slate-800';
    $textColor = 'text-slate-300';
    $numColor = 'text-violet-400';
} elseif ($variant === 'primary') {
    $containerClass .= 'bg-violet-600 text-white';
    $statBoxClass = 'bg-violet-700/50 border border-violet-500';
    $textColor = 'text-violet-100';
    $numColor = 'text-amber-300';
} else {
    $containerClass .= 'bg-sky-50/10';
}
?>
<div class="<?= esc($containerClass) ?> <?= $cssClass ?>">
    <div class="flex items-center justify-between mb-3 border-b border-sky-100/20 pb-2">
        <span class="text-xs font-semibold uppercase tracking-wider flex items-center gap-1.5 <?= $variant === 'light' ? 'text-sky-700' : 'text-white' ?>">
            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-calculator"><rect x="14" y="14" width="4" height="6" rx="1"/><rect x="6" y="14" width="4" height="6" rx="1"/><rect x="6" y="4" width="12" height="6" rx="1"/></svg>
            Grilla de Métricas (Contenedor)
        </span>
        <span class="text-xxs px-2 py-0.5 rounded-full font-medium <?= $variant === 'light' ? 'bg-sky-100 text-sky-800' : 'bg-white/10 text-white' ?>">
            Variante: <?= esc($variant) ?>
        </span>
    </div>
    
    <div class="grid grid-cols-3 gap-3">
        <?php for ($i = 1; $i <= 3; $i++): ?>
            <div class="<?= $statBoxClass ?> rounded-lg p-3 text-center">
                <span class="text-lg md:text-xl font-black block <?= $numColor ?>">99+</span>
                <span class="text-[9px] uppercase tracking-wide font-medium block <?= $textColor ?>">Métrica <?= $i ?></span>
            </div>
        <?php endfor; ?>
    </div>
    
    <p class="text-[10px] mt-3 text-center italic <?= $variant === 'light' ? 'text-slate-400' : 'text-white/40' ?>">Agrega bloques del tipo "Métrica" como hijos.</p>
</div>
