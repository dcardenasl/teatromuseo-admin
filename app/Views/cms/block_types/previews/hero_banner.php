<?php
/** @var array<string, mixed> $config */
/** @var array<string, mixed> $data */
$image      = is_array($config['image'] ?? null) ? $config['image'] : (is_array($data['image'] ?? null) ? $data['image'] : []);
$imageUrl   = esc($image['url'] ?? 'https://placehold.co/1200x400/3b82f6/ffffff?text=Hero+Banner');
$alt        = esc($data['alt'] ?? '');
$heading    = esc($data['heading'] ?? 'Título principal');
$subheading = esc($data['subheading'] ?? '');
$ctaLabel   = esc($data['cta_label'] ?? '');
$ctaUrl     = esc($data['cta_url'] ?? '#');
$cssClass   = esc($config['css_class'] ?? '');
?>
<section class="relative h-72 flex items-center justify-center overflow-hidden rounded-lg <?= $cssClass ?>">
    <img src="<?= $imageUrl ?>" alt="<?= $alt ?>" class="absolute inset-0 w-full h-full object-cover" />
    <div class="absolute inset-0 bg-black/40"></div>
    <div class="relative z-10 text-center text-white px-6">
        <h1 class="text-3xl font-bold mb-2"><?= $heading ?></h1>
        <?php if ($subheading): ?>
            <p class="text-lg mb-4 opacity-90"><?= $subheading ?></p>
        <?php endif; ?>
        <?php if ($ctaLabel): ?>
            <a href="<?= $ctaUrl ?>" class="inline-block bg-white text-blue-700 font-semibold px-6 py-2 rounded-lg hover:bg-blue-50 transition-colors">
                <?= $ctaLabel ?>
            </a>
        <?php endif; ?>
    </div>
</section>
