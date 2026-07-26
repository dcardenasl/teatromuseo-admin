<?php
/** @var array<string, mixed> $config */
/** @var array<string, mixed> $data */

$documents = is_array($data['documents'] ?? null) ? $data['documents'] : [];
if ($documents === []) {
    $documents = [
        [
            'file' => ['source_kind' => 'external_url', 'file_id' => null, 'url' => site_url('assets/docs/policies-handbook-demo.pdf')],
            'title' => 'Documento de ejemplo',
            'description' => 'Previsualización local del bloque de documentos.',
        ],
    ];
}
$layout = (string) ($config['layout'] ?? 'grid_cards');
?>
<div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
    <div class="mb-4 flex items-center justify-between">
        <div>
            <div class="text-xs font-bold uppercase tracking-[0.2em] text-slate-400">Galería de documentos</div>
            <div class="mt-1 text-sm font-semibold text-slate-900"><?= esc($data['title'] ?? 'Documentos') ?></div>
        </div>
        <span class="rounded-full bg-slate-100 px-2.5 py-1 text-[10px] font-semibold text-slate-600"><?= esc($layout) ?></span>
    </div>

    <div class="<?= $layout === 'simple_list' ? 'space-y-2' : 'grid gap-3 sm:grid-cols-2' ?>">
        <?php foreach ($documents as $document): ?>
            <?php
                $url = (string) ($document['file']['url'] ?? '');
            $ext = strtoupper((string) pathinfo(parse_url($url, PHP_URL_PATH) ?? '', PATHINFO_EXTENSION));
            $ext = $ext !== '' ? $ext : 'DOC';
            ?>
            <div class="flex items-center justify-between gap-3 rounded-xl border border-slate-200 bg-slate-50 px-4 py-3">
                <div class="min-w-0">
                    <div class="text-sm font-semibold text-slate-900"><?= esc($document['title'] ?? 'Documento') ?></div>
                    <div class="mt-0.5 text-xs text-slate-500"><?= esc($document['description'] ?? '') ?></div>
                </div>
                <span class="rounded-full border border-slate-200 bg-white px-2 py-1 text-[10px] font-bold text-slate-500"><?= esc($ext) ?></span>
            </div>
        <?php endforeach; ?>
    </div>
</div>
