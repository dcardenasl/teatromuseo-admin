<?php /* Wizard — B1: Page selection */ ?>

<!-- ── SCREEN: PAGE SELECT (B1) ── -->
<div x-show="screen === 'page-select'" x-cloak class="space-y-4">
    <div class="flex items-center justify-between gap-3">
        <div>
            <h2 class="text-lg font-semibold text-gray-900"><?= lang('Wizard.page_select_heading') ?></h2>
            <p class="text-sm text-gray-500"><?= lang('Wizard.no_pages') ?></p>
        </div>
    </div>
    <div x-show="(config?.pages ?? []).length === 0"
         class="rounded-xl border border-dashed border-gray-200 bg-white py-10 text-center text-sm text-gray-400 shadow-sm">
        <?= lang('Wizard.no_pages') ?>
    </div>
    <div class="grid grid-cols-1 gap-3 md:grid-cols-2 xl:grid-cols-3">
        <template x-for="page in (config?.pages ?? [])" :key="page.id">
            <button @click="selectPage(page)"
                    class="flex min-h-[120px] flex-col items-start justify-between gap-3 rounded-xl border border-gray-200 bg-white p-4 text-left shadow-sm transition hover:border-brand-300 hover:shadow-md focus:outline-none focus:ring-2 focus:ring-brand-500">
                <span class="text-2xl">📄</span>
                <span class="space-y-1">
                    <span class="block text-sm font-semibold text-gray-900" x-text="page.title || page.slug || strings.page_fallback"></span>
                    <span class="block text-xs text-gray-500" x-text="page.slug ? '/' + page.slug : ''"></span>
                </span>
            </button>
        </template>
    </div>
    <button @click="screen = 'home'" class="btn-secondary text-sm"><?= lang('Wizard.btn_back') ?></button>
</div>
