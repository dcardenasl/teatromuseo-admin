<?php
/** @var array<string, mixed> $config */
/** @var array<string, mixed> $data */
$cssClass = esc($config['css_class'] ?? '');
?>
<div class="border border-dashed border-violet-300 bg-violet-50/20 rounded-xl p-4 <?= $cssClass ?>">
    <div class="flex items-center justify-between mb-3 border-b border-violet-100 pb-2">
        <span class="text-xs font-semibold text-violet-600 uppercase tracking-wider flex items-center gap-1.5">
            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-help-circle"><circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
            Acordeón (Contenedor)
        </span>
        <span class="text-xxs bg-violet-100 text-violet-700 px-2 py-0.5 rounded-full font-medium">Contenedor</span>
    </div>
    
    <div class="space-y-2">
        <div class="border border-slate-200 bg-white rounded-lg p-3 flex justify-between items-center text-sm text-slate-700 font-medium">
            <span>¿Título de ejemplo 1?</span>
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-slate-400"><path d="m6 9 6 6 6-6"/></svg>
        </div>
        <div class="border border-slate-200 bg-white rounded-lg p-3 text-sm text-slate-700 font-medium">
            <div class="flex justify-between items-center pb-2 border-b border-slate-100">
                <span>¿Título de ejemplo 2 (Abierto)?</span>
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-slate-500 rotate-180"><path d="m6 9 6 6 6-6"/></svg>
            </div>
            <p class="text-xs text-slate-500 mt-2 font-normal">
                Esta es la respuesta correspondiente al bloque hijo. Aquí se mostrará el texto formateado.
            </p>
        </div>
    </div>
    <p class="text-[10px] text-slate-400 mt-3 text-center italic">Agrega bloques del tipo "Elemento de Acordeón" como hijos.</p>
</div>
