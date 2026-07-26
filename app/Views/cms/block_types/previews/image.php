<?php
/** @var array<string, mixed> $config */
/** @var array<string, mixed> $data */
$image       = is_array($config['image'] ?? null) ? $config['image'] : [];
$url         = esc($image['url'] ?? 'https://placehold.co/800x450/e2e8f0/94a3b8?text=Imagen');
$alt         = esc($data['alt'] ?? '');
$caption     = esc($data['caption'] ?? '');
$cssClass    = esc($config['css_class'] ?? '');
$aspectRatio = $config['aspect_ratio'] ?? 'auto';

$aspectClass = match($aspectRatio) {
    '16/9' => 'aspect-video',
    '4/3'  => 'aspect-4/3',
    '1/1'  => 'aspect-square',
    default => '',
};
?>
<figure class="<?= $cssClass ?>">
    <div class="overflow-hidden rounded-lg <?= $aspectClass ?>">
        <img src="<?= $url ?>" alt="<?= $alt ?>" class="w-full h-full object-cover" />
    </div>
    <?php if ($caption): ?>
        <figcaption class="mt-2 text-sm text-gray-500 text-center"><?= $caption ?></figcaption>
    <?php endif; ?>
</figure>
