<?php
/** @var array<string, mixed> $config */
/** @var array<string, mixed> $data */

$title = trim((string) ($data['title'] ?? 'Mapa embebido'));
$caption = trim((string) ($data['caption'] ?? ''));
$embedUrl = trim((string) ($config['embed_url'] ?? ''));
$height = max(160, (int) ($config['height'] ?? 240));
?>
<div class="rounded-xl border border-slate-200 bg-white p-3">
    <div class="mb-3">
        <div class="text-[10px] font-bold uppercase tracking-wide text-sky-600">Mapa Embebido</div>
        <?php if ($title !== ''): ?>
            <div class="mt-1 text-sm font-semibold text-slate-900"><?= esc($title) ?></div>
        <?php endif; ?>
        <?php if ($caption !== ''): ?>
            <p class="mt-1 text-xs text-slate-500"><?= esc($caption) ?></p>
        <?php endif; ?>
    </div>

    <div class="flex items-center justify-center rounded-lg bg-slate-100 text-slate-400" style="min-height: <?= esc((string) $height) ?>px;">
        <?php if ($embedUrl !== ''): ?>
            <span class="text-xs font-medium">Iframe configurado</span>
        <?php else: ?>
            <span class="text-xs">Configura la URL embed del mapa</span>
        <?php endif; ?>
    </div>
</div>
