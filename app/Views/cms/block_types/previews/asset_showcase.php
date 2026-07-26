<?php
/** @var array<string, mixed> $config */
/** @var array<string, mixed> $data */
$layout = (string) ($config['layout'] ?? 'marquee');
$speed = (string) ($config['speed'] ?? 'normal');
$grayscale = filter_var($config['grayscale'] ?? true, FILTER_VALIDATE_BOOL);
$cssClass = esc($config['css_class'] ?? '');
?>
<div class="border border-dashed border-emerald-300 bg-emerald-50/10 rounded-xl p-4 <?= $cssClass ?>">
    <div class="flex items-center justify-between mb-3 border-b border-emerald-100 pb-2">
        <span class="text-xs font-semibold text-emerald-700 uppercase tracking-wider flex items-center gap-1.5">
            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-images"><path d="M18 22H4a2 2 0 0 1-2-2V6"/><path d="M22 18H8a2 2 0 0 1-2-2V2a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2z"/><rect x="10" y="4" width="10" height="10" rx="1"/></svg>
            Vitrina de Activos (Contenedor)
        </span>
        <span class="text-xxs bg-emerald-100 text-emerald-800 px-2 py-0.5 rounded-full font-medium">
            <?= esc($layout) ?> | Velocidad: <?= esc($speed) ?>
        </span>
    </div>
    
    <div class="flex flex-wrap gap-4 items-center justify-center py-2 bg-slate-50/80 rounded border border-slate-200/50">
        <?php for ($i = 1; $i <= 5; $i++): ?>
            <div class="h-6 w-16 bg-slate-300 rounded-sm flex items-center justify-center text-[7px] text-slate-500 font-bold <?= $grayscale ? 'filter grayscale opacity-50' : 'opacity-80' ?>">
                LOGO <?= $i ?>
            </div>
        <?php endfor; ?>
    </div>
    
    <p class="text-[10px] text-slate-400 mt-3 text-center italic">Agrega bloques del tipo "Logo de Auspiciador" como hijos.</p>
</div>
