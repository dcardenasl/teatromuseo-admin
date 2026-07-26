<?php
/** @var array<string, mixed> $config */
/** @var array<string, mixed> $data */
$cssClass = esc($config['css_class'] ?? 'container mx-auto px-4');
$layout   = $config['layout'] ?? 'block';

$innerClass = match($layout) {
    'grid-2'   => 'grid grid-cols-2 gap-4',
    'grid-3'   => 'grid grid-cols-3 gap-4',
    'flex-row' => 'flex flex-row gap-4',
    default    => 'block',
};
?>
<div class="<?= $cssClass ?> border-2 border-dashed border-blue-300 rounded-lg p-4">
    <p class="text-xs text-blue-400 text-center mb-3 font-medium uppercase tracking-wide">
        Contenedor — distribución: <?= esc($layout) ?>
    </p>
    <div class="<?= $innerClass ?>">
        <div class="bg-gray-100 rounded p-4 text-center text-sm text-gray-400">Bloque hijo 1</div>
        <?php if (in_array($layout, ['grid-2', 'grid-3', 'flex-row'], true)): ?>
            <div class="bg-gray-100 rounded p-4 text-center text-sm text-gray-400">Bloque hijo 2</div>
        <?php endif; ?>
        <?php if ($layout === 'grid-3'): ?>
            <div class="bg-gray-100 rounded p-4 text-center text-sm text-gray-400">Bloque hijo 3</div>
        <?php endif; ?>
    </div>
</div>
