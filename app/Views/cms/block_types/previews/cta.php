<?php
/** @var array<string, mixed> $config */
/** @var array<string, mixed> $data */
$heading  = esc($data['heading'] ?? '¿Listo para comenzar?');
$text     = esc($data['text'] ?? '');
$label    = esc($data['label'] ?? 'Comenzar ahora');
$url      = esc($data['url'] ?? '#');
$cssClass = esc($config['css_class'] ?? '');
$variant  = $config['variant'] ?? 'blue';

$bgClass = match($variant) {
    'dark'  => 'bg-gray-900 text-white',
    'light' => 'bg-gray-100 text-gray-900',
    default => 'bg-blue-600 text-white',
};
$btnClass = match($variant) {
    'dark'  => 'bg-white text-gray-900 hover:bg-gray-100',
    'light' => 'bg-blue-600 text-white hover:bg-blue-700',
    default => 'bg-white text-blue-700 hover:bg-blue-50',
};
?>
<div class="<?= $bgClass ?> py-10 px-6 text-center rounded-lg <?= $cssClass ?>">
    <h2 class="text-2xl font-bold mb-2"><?= $heading ?></h2>
    <?php if ($text): ?>
        <p class="mb-6 opacity-90"><?= $text ?></p>
    <?php endif; ?>
    <a href="<?= $url ?>" class="inline-block <?= $btnClass ?> font-semibold px-8 py-3 rounded-lg transition-colors">
        <?= $label ?>
    </a>
</div>
