<?php
/** @var array<string, mixed> $config */
/** @var array<string, mixed> $data */
$title = $data['title'] ?? '';
$message = $data['message'] ?? '';
$type = $config['type'] ?? 'info';
$dismissible = filter_var($config['dismissible'] ?? true, FILTER_VALIDATE_BOOL);

$typeColors = [
    'info' => 'bg-blue-50 border-blue-200 text-blue-800',
    'success' => 'bg-emerald-50 border-emerald-200 text-emerald-800',
    'warning' => 'bg-amber-50 border-amber-200 text-amber-800',
    'danger' => 'bg-rose-50 border-rose-200 text-rose-800',
];
$colorClass = $typeColors[$type] ?? $typeColors['info'];
?>
<div class="border rounded-lg p-3 text-xs flex gap-2 items-start <?= $colorClass ?>">
    <div class="shrink-0 mt-0.5">
        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-alert-circle"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
    </div>
    <div class="flex-1 min-w-0">
        <?php if ($title !== ''): ?>
            <div class="font-bold mb-0.5"><?= esc($title) ?></div>
        <?php endif; ?>
        <div class="opacity-90">
            <?= $message !== '' ? $message : 'Mensaje de alerta vacío...' ?>
        </div>
    </div>
    <?php if ($dismissible): ?>
        <div class="shrink-0 text-slate-400 opacity-60">×</div>
    <?php endif; ?>
</div>
