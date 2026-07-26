<?php
/** @var array<string, mixed> $config */
/** @var array<string, mixed> $data */
$photo = is_array($config['photo'] ?? null) ? $config['photo'] : (is_array($data['photo'] ?? null) ? $data['photo'] : []);
$photoUrl = $photo['url'] ?? '';
$name = $data['name'] ?? '';
$position = $data['position'] ?? '';
$bio = $data['bio'] ?? '';
?>
<div class="border border-slate-200 bg-white rounded-lg p-3 flex gap-3 items-center">
    <div class="h-12 w-12 bg-slate-50 rounded-full flex-shrink-0 flex items-center justify-center text-slate-400 overflow-hidden border border-slate-100">
        <?php if ($photoUrl !== ''): ?>
            <img src="<?= esc($photoUrl) ?>" class="h-full w-full object-cover" />
        <?php else: ?>
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-user"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
        <?php endif; ?>
    </div>
    <div class="flex-grow min-w-0">
        <div class="text-[10px] font-bold text-violet-500 uppercase mb-0.5">Miembro del Equipo</div>
        <h4 class="text-xs font-bold text-slate-800 truncate"><?= $name !== '' ? esc($name) : 'Nombre sin definir' ?></h4>
        <?php if ($position !== ''): ?>
            <p class="text-[10px] text-slate-500 truncate"><?= esc($position) ?></p>
        <?php endif; ?>
        <?php if ($bio !== ''): ?>
            <p class="text-[10px] text-slate-400 line-clamp-2 mt-0.5"><?= esc($bio) ?></p>
        <?php endif; ?>
    </div>
</div>
