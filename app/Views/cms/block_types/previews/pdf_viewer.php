<?php
/** @var array<string, mixed> $config */
/** @var array<string, mixed> $data */

$pdfFile = is_array($config['pdf_file'] ?? null) ? $config['pdf_file'] : (is_array($data['pdf_file'] ?? null) ? $data['pdf_file'] : []);
$pdfUrl = $pdfFile['url'] ?? site_url('assets/docs/policies-handbook-demo.pdf');
$heading = $data['heading'] ?? 'PDF de ejemplo';
$height = $config['height'] ?? '600px';
?>
<div class="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden">
    <div class="border-b border-slate-100 px-4 py-3">
        <div class="text-xs font-bold uppercase tracking-[0.18em] text-slate-400">Visualizador de PDF</div>
        <div class="mt-1 text-sm font-semibold text-slate-900"><?= esc($heading) ?></div>
    </div>
    <div class="flex items-center justify-center bg-slate-50 px-6 text-center" style="height: <?= esc($height) ?>;">
        <div class="max-w-sm">
            <div class="mx-auto mb-3 flex h-14 w-14 items-center justify-center rounded-2xl bg-red-50 text-red-600">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m.75 12 3 3m0 0 3-3m-3 3v-6m-1.5-9H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9z" />
                </svg>
            </div>
            <p class="text-sm text-slate-600">Previsualización local del PDF.</p>
            <p class="mt-2 break-all text-xs text-slate-400"><?= $pdfUrl !== '' ? esc($pdfUrl) : 'Sin archivo configurado' ?></p>
        </div>
    </div>
</div>
