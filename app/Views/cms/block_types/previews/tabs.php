<?php
/** @var array<string, mixed> $config */
/** @var array<string, mixed> $data */
$layout = esc($config['layout'] ?? 'horizontal');
$cssClass = esc($config['css_class'] ?? '');
?>
<div class="border border-dashed border-violet-300 bg-violet-50/20 rounded-xl p-4 <?= $cssClass ?>">
    <div class="flex items-center justify-between mb-3 border-b border-violet-100 pb-2">
        <span class="text-xs font-semibold text-violet-600 uppercase tracking-wider flex items-center gap-1.5">
            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-folder"><path d="M4 20h16a2 2 0 0 0 2-2V8a2 2 0 0 0-2-2h-7.93a2 2 0 0 1-1.66-.9l-.82-1.2A2 2 0 0 0 7.93 3H4a2 2 0 0 0-2 2v13a2 2 0 0 0 2 2z"/></svg>
            Pestañas (Contenedor)
        </span>
        <span class="text-[10px] bg-slate-100 text-slate-700 px-2 py-0.5 rounded-full font-medium">Layout: <?= esc($layout) ?></span>
    </div>

    <div class="<?= $layout === 'vertical' ? 'flex gap-3' : 'space-y-3' ?>">
        <!-- Headers Preview -->
        <div class="<?= $layout === 'vertical' ? 'w-1/4 border-r border-slate-100 pr-2 flex flex-col gap-1' : 'flex gap-1.5 border-b border-slate-100 pb-2 overflow-x-auto' ?>">
            <span class="text-xs bg-primary text-white px-3 py-1.5 rounded-lg text-center font-medium">Pestaña 1</span>
            <span class="text-xs bg-slate-100 text-slate-600 px-3 py-1.5 rounded-lg text-center font-medium">Pestaña 2</span>
        </div>

        <!-- Content Preview -->
        <div class="flex-1 border border-slate-200 bg-white rounded-lg p-3 text-xs text-slate-500">
            <div class="font-bold text-slate-700 mb-1">Contenido de pestaña activa</div>
            <p>Se mostrará el texto o HTML enriquecido del bloque pestaña seleccionado.</p>
        </div>
    </div>
    <p class="text-[10px] text-slate-400 mt-3 text-center italic">Agrega bloques del tipo "Pestaña Individual" como hijos.</p>
</div>
