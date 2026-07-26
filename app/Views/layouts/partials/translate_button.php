<?php
/**
 * Translate Automatically Button
 *
 * Reutilizable partial para el botón de traducción automática.
 *
 * Variables esperadas:
 * - $translateTargets (array) - Configuración de traducción generada por buildTranslateTargets()
 * - $showButton (bool, optional) - Si debe mostrarse el botón (default: true si hay targets)
 */

$showButton = $showButton ?? ! empty($translateTargets);
?>

<?php if ($showButton && ! empty($translateTargets)): ?>
<button type="button"
        x-on:click="autoTranslateAll(<?= esc(json_encode($translateTargets), 'attr') ?>)"
        :disabled="translatingAll"
        class="shrink-0 inline-flex items-center gap-1.5 rounded-md border border-gray-300 bg-green-50 px-3 py-1.5 text-xs font-medium text-green-700 shadow-sm hover:bg-green-100 disabled:opacity-50 disabled:cursor-not-allowed transition-colors">
    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" d="m10.5 21 5.25-11.25L21 21m-9-3h7.5M3 5.621c0-.012 0-.024 0-.036V3.75a2.25 2.25 0 0 1 2.25-2.25h15a2.25 2.25 0 0 1 2.25 2.25v13.5A2.25 2.25 0 0 1 20.25 21H3.75A2.25 2.25 0 0 1 1.5 18.75Zm12.621-4.72l-6.89 7.72m0 0l-6.89-7.72m6.89 7.72l6.89-7.72m-6.89 7.72l-6.89 7.72"/>
    </svg>
    <span x-text="translatingAll ? 'Traduciendo...' : 'Traducir automáticamente'"></span>
</button>
<?php endif; ?>
