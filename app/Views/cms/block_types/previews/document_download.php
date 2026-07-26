<?php
/** @var array<string, mixed> $config */
/** @var array<string, mixed> $data */

$document = is_array($config['document'] ?? null) ? $config['document'] : (is_array($data['document'] ?? null) ? $data['document'] : []);
$documentUrl = $document['url'] ?? ($data['document_url'] ?? '');
$title = $data['title'] ?? 'Documento de ejemplo';
$description = $data['description'] ?? 'Previsualización del bloque de descarga.';
$buttonLabel = $data['button_label'] ?? 'Descargar';
$ext = strtoupper((string) pathinfo(parse_url((string) $documentUrl, PHP_URL_PATH) ?? '', PATHINFO_EXTENSION));
$ext = $ext !== '' ? $ext : 'DOC';
?>
<div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
    <div class="flex items-start gap-4">
        <div class="rounded-2xl border border-slate-100 bg-slate-50 p-3 text-slate-500">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m.75 12 3 3m0 0 3-3m-3 3v-6m-1.5-9H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9z" />
            </svg>
        </div>
        <div class="min-w-0 flex-1">
            <div class="text-[10px] font-bold uppercase tracking-[0.2em] text-slate-400"><?= esc($ext) ?></div>
            <h4 class="mt-1 text-base font-semibold text-slate-900"><?= esc($title) ?></h4>
            <p class="mt-1 text-sm text-slate-500"><?= esc($description) ?></p>
        </div>
    </div>

    <div class="mt-4 flex items-center justify-between gap-3 rounded-xl bg-slate-50 px-4 py-3">
        <span class="text-xs font-medium text-slate-500"><?= $documentUrl !== '' ? esc($documentUrl) : 'Sin archivo configurado' ?></span>
        <span class="inline-flex items-center rounded-lg bg-violet-600 px-3 py-2 text-xs font-semibold text-white"><?= esc($buttonLabel) ?></span>
    </div>
</div>
