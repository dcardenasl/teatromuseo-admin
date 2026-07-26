<?php
/** @var array<string, mixed> $config */
/** @var array<string, mixed> $data */
$sectionTitle  = $data['section_title'] ?? 'Contenido destacado';
$viewAllLabel  = $data['view_all_label'] ?? 'Ver todo';
$viewAllUrl    = $data['view_all_url'] ?? '#';
$collectionKey = $config['collection_key'] ?? '';
$itemsLimit    = (int) ($config['items_limit'] ?? 3);
$orderBy       = $config['order_by'] ?? 'published_at';
$direction     = $config['order_direction'] ?? 'desc';
$variant       = $config['layout_variant'] ?? 'cards';
$cssClass      = $config['css_class'] ?? '';

$items = [
    ['title' => 'Entrada destacada A', 'excerpt' => 'Resumen breve de una entrada publicada.', 'date' => '10 Jun 2026'],
    ['title' => 'Entrada destacada B', 'excerpt' => 'Texto de apoyo para previsualizar la grilla.', 'date' => '02 Jun 2026'],
    ['title' => 'Entrada destacada C', 'excerpt' => 'Otra tarjeta de ejemplo de la colección.', 'date' => '25 May 2026'],
];
?>
<section class="rounded-xl border border-dashed border-slate-300 bg-slate-50/60 p-4 <?= esc((string) $cssClass) ?>">
    <div class="mb-3 flex items-center justify-between gap-3 border-b border-slate-200 pb-2">
        <div>
            <div class="text-xs font-semibold uppercase tracking-wider text-slate-500">Grilla de Colección</div>
            <div class="text-sm font-bold text-slate-900"><?= esc((string) $sectionTitle) ?></div>
        </div>
        <?php if ($viewAllLabel !== ''): ?>
            <a href="<?= esc((string) $viewAllUrl) ?>" class="text-xs font-medium text-blue-600"><?= esc((string) $viewAllLabel) ?></a>
        <?php endif; ?>
    </div>

    <div class="mb-4 rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-xs text-amber-700">
        Colección: <code><?= esc((string) ($collectionKey ?: 'seleccionar')) ?></code>
        · Límite: <?= esc((string) $itemsLimit) ?>
        · Orden: <?= esc((string) $orderBy) ?> <?= esc((string) $direction) ?>
        · Variante: <?= esc((string) $variant) ?>
    </div>

    <div class="grid grid-cols-1 gap-3 sm:grid-cols-3">
        <?php foreach ($items as $item): ?>
            <article class="rounded-lg border border-slate-200 bg-white p-3 shadow-sm">
                <div class="mb-2 aspect-video rounded bg-slate-100"></div>
                <p class="mb-1 text-[10px] uppercase tracking-wide text-slate-400"><?= esc($item['date']) ?></p>
                <h3 class="text-sm font-semibold leading-tight text-slate-800"><?= esc($item['title']) ?></h3>
                <p class="mt-1 line-clamp-2 text-xs text-slate-500"><?= esc($item['excerpt']) ?></p>
            </article>
        <?php endforeach; ?>
    </div>
</section>
