<?php
/** @var array<string, mixed> $config */
/** @var array<string, mixed> $data */

$videos = is_array($data['videos'] ?? null) ? $data['videos'] : [];
if ($videos === []) {
    $videos = [
        [
            'video_url' => 'https://youtu.be/dQw4w9WgXcQ',
            'title' => 'Video de ejemplo',
            'description' => 'Previsualización local del bloque de video gallery.',
            'poster' => ['url' => 'https://placehold.co/600x400/0f172a/ffffff?text=Video'],
        ],
    ];
}
$columns = (string) ($config['columns'] ?? '3');
$columnClass = match ($columns) {
    '2' => 'grid-cols-1 sm:grid-cols-2',
    '4' => 'grid-cols-1 sm:grid-cols-2 lg:grid-cols-4',
    default => 'grid-cols-1 sm:grid-cols-2 md:grid-cols-3',
};
?>
<div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
    <div class="mb-4 flex items-center justify-between">
        <div>
            <div class="text-xs font-bold uppercase tracking-[0.2em] text-slate-400">Galería de videos</div>
            <div class="mt-1 text-sm font-semibold text-slate-900"><?= esc($data['title'] ?? 'Videos') ?></div>
        </div>
        <span class="rounded-full bg-slate-100 px-2.5 py-1 text-[10px] font-semibold text-slate-600"><?= esc($columns) ?> cols</span>
    </div>

    <div class="grid gap-3 <?= $columnClass ?>">
        <?php foreach ($videos as $video): ?>
            <?php
                $poster = is_array($video['poster'] ?? null) ? $video['poster'] : [];
            $posterUrl = $poster['url'] ?? 'https://placehold.co/600x400/1f2937/ffffff?text=Video';
            ?>
            <div class="overflow-hidden rounded-xl border border-slate-200 bg-slate-50">
                <div class="aspect-video">
                    <img src="<?= esc($posterUrl) ?>" alt="<?= esc($video['title'] ?? '') ?>" class="h-full w-full object-cover" />
                </div>
                <div class="p-3">
                    <div class="text-sm font-semibold text-slate-900"><?= esc($video['title'] ?? 'Video') ?></div>
                    <div class="mt-0.5 text-xs text-slate-500"><?= esc($video['description'] ?? '') ?></div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>
