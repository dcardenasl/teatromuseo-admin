<?php
/** @var array<string, mixed> $config */
/** @var array<string, mixed> $data */
$eyebrow = $data['eyebrow'] ?? '';
$title = $data['title'] ?? '';
$body = $data['body'] ?? '';
$metaTitle = $data['meta_title'] ?? '';
$metaDescription = $data['meta_description'] ?? '';
$image = is_array($config['image'] ?? null) ? $config['image'] : (is_array($data['image'] ?? null) ? $data['image'] : []);
$rating = (int) ($data['rating'] ?? 0);
?>
<div class="border border-slate-200 bg-white rounded-lg p-3">
    <div class="text-[10px] font-bold text-violet-500 uppercase mb-1">Tarjeta de Slider</div>
    <?php if (! empty($image['url'])): ?>
        <div class="mb-2 h-12 overflow-hidden rounded bg-slate-100">
            <img src="<?= esc($image['url']) ?>" class="h-full w-full object-cover" />
        </div>
    <?php endif; ?>
    <?php if ($rating > 0): ?>
        <div class="flex gap-0.5 text-amber-400 text-[9px] mb-2">
            <?php for ($i = 0; $i < 5; $i++): ?>
                <span><?= $i < $rating ? '★' : '☆' ?></span>
            <?php endfor; ?>
        </div>
    <?php endif; ?>
    <?php if ($eyebrow !== ''): ?>
        <div class="text-[8px] font-bold uppercase tracking-wide text-slate-400 mb-1"><?= esc($eyebrow) ?></div>
    <?php endif; ?>
    <div class="text-xs font-bold text-slate-800 line-clamp-2"><?= $title !== '' ? esc($title) : 'Tarjeta editorial' ?></div>
    <p class="text-[10px] text-slate-600 line-clamp-3 mt-1">
        <?= $body !== '' ? esc($body) : 'Texto configurable de la tarjeta.' ?>
    </p>
    <?php if ($metaTitle !== '' || $metaDescription !== ''): ?>
        <div class="mt-3 pt-2 border-t border-slate-50">
            <?php if ($metaTitle !== ''): ?>
                <div class="text-[10px] font-bold text-slate-800 truncate"><?= esc($metaTitle) ?></div>
            <?php endif; ?>
            <?php if ($metaDescription !== ''): ?>
                <span class="text-[8px] text-slate-400 block truncate"><?= esc($metaDescription) ?></span>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>
