<?php
/** @var array<string, mixed> $config */
/** @var array<string, mixed> $data */
$heading         = esc($data['heading'] ?? 'Título de la página');
$subheading      = esc($data['subheading'] ?? '');
$breadcrumbLabel = esc($data['breadcrumb_label'] ?? 'Inicio');
$breadcrumbUrl   = esc($data['breadcrumb_url'] ?? '/');
$bgColor         = esc($config['bg_color'] ?? 'bg-gray-100');
$cssClass        = esc($config['css_class'] ?? '');
?>
<section class="<?= $bgColor ?> py-10 <?= $cssClass ?>">
    <div class="max-w-5xl mx-auto px-4">
        <!-- Breadcrumb -->
        <?php if ($breadcrumbLabel): ?>
            <nav class="flex items-center gap-2 text-xs text-gray-500 mb-4">
                <a href="<?= $breadcrumbUrl ?>" class="hover:text-gray-700"><?= $breadcrumbLabel ?></a>
                <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m9 5 7 7-7 7"/>
                </svg>
                <span class="text-gray-700"><?= $heading ?></span>
            </nav>
        <?php endif; ?>

        <h1 class="text-3xl font-bold text-gray-900"><?= $heading ?></h1>
        <?php if ($subheading): ?>
            <p class="text-gray-600 mt-2"><?= $subheading ?></p>
        <?php endif; ?>
    </div>
</section>
