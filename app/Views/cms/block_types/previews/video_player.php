<?php
/** @var array<string, mixed> $config */
/** @var array<string, mixed> $data */
$videoUrl = $data['video_url'] ?? '';
$poster = is_array($config['poster'] ?? null) ? $config['poster'] : (is_array($data['poster'] ?? null) ? $data['poster'] : []);
$posterUrl = $poster['url'] ?? '';
$heading = $data['heading'] ?? '';
$aspectRatio = $config['aspect_ratio'] ?? '16/9';
$cssClass = esc($config['css_class'] ?? '');

$aspectClass = 'aspect-video';
if ($aspectRatio === '4/3') {
    $aspectClass = 'aspect-[4/3]';
} elseif ($aspectRatio === 'auto') {
    $aspectClass = 'aspect-auto';
}
?>
<div class="border border-dashed border-red-300 bg-red-50/10 rounded-xl p-4 <?= $cssClass ?>">
    <div class="flex items-center justify-between mb-3 border-b border-red-100 pb-2">
        <span class="text-xs font-semibold text-red-700 uppercase tracking-wider flex items-center gap-1.5">
            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-play"><polygon points="5 3 19 12 5 21 5 3"/></svg>
            Reproductor de Video
        </span>
        <span class="text-xxs bg-red-100 text-red-800 px-2 py-0.5 rounded-full font-medium">
            Proporción: <?= esc($aspectRatio) ?>
        </span>
    </div>
    
    <div class="max-w-md mx-auto">
        <?php if ($heading !== ''): ?>
            <h4 class="text-xs font-bold text-slate-800 mb-2 text-center"><?= esc($heading) ?></h4>
        <?php endif; ?>
        
        <div class="relative bg-slate-900 rounded-lg flex flex-col items-center justify-center text-center overflow-hidden <?= $aspectClass ?>" style="min-height: 150px;">
            <?php if ($posterUrl !== ''): ?>
                <img src="<?= esc($posterUrl) ?>" class="absolute inset-0 w-full h-full object-cover opacity-40" />
            <?php endif; ?>
            
            <div class="relative z-10 p-4">
                <div class="h-10 w-10 rounded-full bg-white text-red-600 shadow-md flex items-center justify-center mx-auto mb-2">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="currentColor" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="5 3 19 12 5 21 5 3"/></svg>
                </div>
                <div class="text-[10px] text-white/95 font-medium truncate max-w-xs">
                    <?= $videoUrl !== '' ? esc($videoUrl) : 'Ninguna URL configurada todavía.' ?>
                </div>
            </div>
        </div>
    </div>
</div>
