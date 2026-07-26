<?php
/** @var array<string, mixed> $config */
/** @var array<string, mixed> $data */
$columns = esc($config['columns'] ?? '3');
$gap = esc($config['gap'] ?? 'medium');
?>
<div class="border border-dashed border-violet-300 bg-violet-50/20 rounded-xl p-4">
    <div class="flex items-center justify-between mb-3 border-b border-violet-100 pb-2">
        <span class="text-xs font-semibold text-violet-600 uppercase tracking-wider flex items-center gap-1.5">
            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-images"><path d="M18 22H4a2 2 0 0 1-2-2V6"/><path d="m22 13-1.296-1.296a2.41 2.41 0 0 0-3.408 0L11 18"/><circle cx="12" cy="8" r="2"/><rect width="16" height="16" x="6" y="2" rx="2"/></svg>
            Galería de Imágenes (Contenedor)
        </span>
        <span class="text-xxs bg-violet-100 text-violet-700 px-2 py-0.5 rounded-full font-medium">Grid: <?= $columns ?> cols</span>
    </div>

    <!-- Grid Preview -->
    <div class="grid grid-cols-3 gap-2">
        <div class="aspect-square bg-slate-200 rounded flex items-center justify-center text-[10px] text-slate-400">Imagen 1</div>
        <div class="aspect-square bg-slate-200 rounded flex items-center justify-center text-[10px] text-slate-400">Imagen 2</div>
        <div class="aspect-square bg-slate-200 rounded flex items-center justify-center text-[10px] text-slate-400">Imagen 3</div>
    </div>
    
    <p class="text-[10px] text-slate-400 mt-3 text-center italic">Agrega bloques del tipo "Imagen de Galería" como hijos.</p>
</div>
