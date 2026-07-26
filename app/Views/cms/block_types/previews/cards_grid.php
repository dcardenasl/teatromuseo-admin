<?php
/** @var array<string, mixed> $config */
/** @var array<string, mixed> $data */
$columnsDesktop = (string) ($config['columns_desktop'] ?? '3');
$variant = (string) ($config['variant'] ?? 'bordered');
$cssClass = esc($config['css_class'] ?? '');

$gridColsClass = 'grid-cols-3';
if ($columnsDesktop === '2') {
    $gridColsClass = 'grid-cols-2';
} elseif ($columnsDesktop === '4') {
    $gridColsClass = 'grid-cols-4';
}

$cardClass = 'rounded-lg p-3 text-left ';
if ($variant === 'bordered') {
    $cardClass .= 'bg-white border border-slate-200 shadow-xs';
} elseif ($variant === 'flat') {
    $cardClass .= 'bg-slate-50 border border-transparent';
} else { // minimal
    $cardClass .= 'bg-transparent border border-dashed border-slate-200';
}
?>
<div class="border border-dashed border-blue-300 bg-blue-50/10 rounded-xl p-4 <?= $cssClass ?>">
    <div class="flex items-center justify-between mb-3 border-b border-blue-100 pb-2">
        <span class="text-xs font-semibold text-blue-600 uppercase tracking-wider flex items-center gap-1.5">
            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-layout-grid"><rect x="3" y="3" width="7" height="9"/><rect x="14" y="3" width="7" height="5"/><rect x="14" y="12" width="7" height="9"/><rect x="3" y="16" width="7" height="5"/></svg>
            Grilla de Tarjetas (Contenedor)
        </span>
        <span class="text-xxs bg-blue-100 text-blue-700 px-2 py-0.5 rounded-full font-medium">
            Columnas: <?= esc($columnsDesktop) ?> | <?= esc($variant) ?>
        </span>
    </div>
    
    <div class="grid gap-3 <?= $gridColsClass ?>">
        <?php for ($i = 1; $i <= (int) $columnsDesktop; $i++): ?>
            <div class="<?= $cardClass ?>">
                <div class="h-6 w-6 rounded bg-violet-100 mb-2 flex items-center justify-center text-[10px] font-bold text-violet-600">★</div>
                <h4 class="text-xs font-bold text-slate-800">Servicio / Card <?= $i ?></h4>
                <p class="text-[10px] text-slate-500 mt-1 line-clamp-2">Detalle o descripción corta de la característica.</p>
            </div>
        <?php endfor; ?>
    </div>
    <p class="text-[10px] text-slate-400 mt-3 text-center italic">Agrega bloques del tipo "Tarjeta" como hijos.</p>
</div>
