<div class="mx-auto max-w-6xl space-y-6">
    <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div class="space-y-2">
                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-gray-500">CMS</p>
                <h1 class="text-2xl font-semibold text-gray-900"><?= esc($title ?? lang('Wizard.config_preview_title')) ?></h1>
                <p class="max-w-3xl text-sm text-gray-600"><?= esc(lang('Wizard.config_preview_intro')) ?></p>
            </div>
            <a href="<?= route_to('admin.cms.wizard') ?>" class="btn-secondary"><?= esc(lang('Wizard.config_preview_back')) ?></a>
        </div>
    </div>

    <section class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
        <div class="border-b border-gray-200 px-6 py-4">
            <h2 class="text-sm font-semibold text-gray-900"><?= esc(lang('Wizard.config_preview_payload')) ?></h2>
        </div>
        <pre class="max-h-[70vh] overflow-auto bg-slate-950 p-6 text-xs leading-5 text-slate-100"><?= esc($configJson ?? '{}') ?></pre>
    </section>
</div>
