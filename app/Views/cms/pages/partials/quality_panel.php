<?php

$quality = is_array($quality ?? null) ? $quality : [];
$summary = is_array($quality['summary'] ?? null) ? $quality['summary'] : [];
$checks = is_array($quality['checks'] ?? null) ? $quality['checks'] : [];
$status = (string) ($quality['status'] ?? 'unavailable');
$statusLabel = match ($status) {
    'ready' => 'Listo para publicar',
    'warning' => 'Revisión recomendada',
    'blocked' => 'Faltan requisitos',
    default => 'Diagnóstico no disponible',
};
$statusClass = match ($status) {
    'ready' => 'bg-green-50 border-green-200 text-green-800',
    'warning' => 'bg-yellow-50 border-yellow-200 text-yellow-800',
    'blocked' => 'bg-red-50 border-red-200 text-red-800',
    default => 'bg-gray-50 border-gray-200 text-gray-700',
};
$actionChecks = array_values(array_filter($checks, static fn (mixed $check): bool => is_array($check) && ($check['status'] ?? '') !== 'pass'));
?>

<section class="rounded-xl border <?= esc($statusClass) ?> p-4 shadow-sm" aria-labelledby="page-quality-title">
    <div class="flex items-start justify-between gap-3">
        <div>
            <h3 id="page-quality-title" class="text-sm font-semibold">Calidad editorial y SEO</h3>
            <p class="mt-1 text-xs font-medium"><?= esc($statusLabel) ?></p>
        </div>
        <?php if (isset($quality['score'])): ?>
            <span class="text-lg font-bold" title="Puntaje calculado por el CMS"><?= esc((string) $quality['score']) ?>%</span>
        <?php endif; ?>
    </div>

    <?php if ($summary !== []): ?>
        <div class="mt-3 grid grid-cols-3 gap-2 text-center text-xs">
            <div class="rounded-lg bg-white/70 px-2 py-2"><strong class="block text-base"><?= (int) ($summary['errors'] ?? 0) ?></strong>errores</div>
            <div class="rounded-lg bg-white/70 px-2 py-2"><strong class="block text-base"><?= (int) ($summary['warnings'] ?? 0) ?></strong>avisos</div>
            <div class="rounded-lg bg-white/70 px-2 py-2"><strong class="block text-base"><?= (int) ($summary['passed'] ?? 0) ?></strong>correctos</div>
        </div>
    <?php endif; ?>

    <?php if ($actionChecks !== []): ?>
        <ul class="mt-3 space-y-2 text-xs">
            <?php foreach ($actionChecks as $check): ?>
                <?php $isError = ($check['status'] ?? '') === 'fail' && ($check['severity'] ?? '') === 'error'; ?>
                <li class="flex items-start gap-2">
                    <span class="mt-0.5 shrink-0 font-bold <?= $isError ? 'text-red-700' : 'text-yellow-700' ?>" aria-hidden="true"><?= $isError ? '!' : '•' ?></span>
                    <span><?= esc((string) ($check['message'] ?? 'Revisar configuración.')) ?></span>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php elseif ($quality !== []): ?>
        <p class="mt-3 text-xs">La página cumple las reglas declaradas por el CMS.</p>
    <?php else: ?>
        <p class="mt-3 text-xs">No se pudo consultar el diagnóstico del CMS.</p>
    <?php endif; ?>
</section>
