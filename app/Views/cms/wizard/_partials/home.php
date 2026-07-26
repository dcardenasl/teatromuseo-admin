<?php /* Wizard — Home screen */ ?>

<!-- ── SCREEN: HOME ── -->
<div x-show="screen === 'home'" x-cloak class="space-y-6">
    <div x-show="draft" x-cloak class="rounded-xl border border-amber-200 bg-amber-50 p-4 flex flex-wrap items-center justify-between gap-3">
        <div>
            <p class="text-sm font-semibold text-amber-900"><?= lang('Wizard.draft_banner_title') ?></p>
            <p class="text-xs text-amber-700" x-text="draft ? new Date(draft.savedAt).toLocaleString() : ''"></p>
        </div>
        <div class="flex flex-wrap gap-2">
            <button @click="resumeDraft()" class="btn-primary text-sm"><?= lang('Wizard.draft_continue') ?></button>
            <button @click="discardDraft()" class="btn-secondary text-sm"><?= lang('Wizard.draft_discard') ?></button>
        </div>
    </div>
    <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
        <button @click="goAddContent()"
                class="flex min-h-[140px] flex-col items-start justify-between gap-4 rounded-xl border border-gray-200 bg-white p-5 text-left shadow-sm transition hover:border-brand-300 hover:shadow-md focus:outline-none focus:ring-2 focus:ring-brand-500">
            <span class="text-3xl">📝</span>
            <span class="space-y-1">
                <span class="block text-sm font-semibold text-gray-900"><?= lang('Wizard.add_content') ?></span>
                <span class="block text-xs text-gray-500" x-text="addContentDesc()"></span>
            </span>
        </button>
        <button @click="goEditPage()"
                class="flex min-h-[140px] flex-col items-start justify-between gap-4 rounded-xl border border-gray-200 bg-white p-5 text-left shadow-sm transition hover:border-brand-300 hover:shadow-md focus:outline-none focus:ring-2 focus:ring-brand-500">
            <span class="text-3xl">✏️</span>
            <span class="space-y-1">
                <span class="block text-sm font-semibold text-gray-900"><?= lang('Wizard.edit_page') ?></span>
                <span class="block text-xs text-gray-500"><?= lang('Wizard.edit_page_desc') ?></span>
            </span>
        </button>
        <button @click="goEditMenu()"
                class="flex min-h-[140px] flex-col items-start justify-between gap-4 rounded-xl border border-gray-200 bg-white p-5 text-left shadow-sm transition hover:border-brand-300 hover:shadow-md focus:outline-none focus:ring-2 focus:ring-brand-500">
            <span class="text-3xl">🔗</span>
            <span class="space-y-1">
                <span class="block text-sm font-semibold text-gray-900"><?= lang('Wizard.edit_menu') ?></span>
                <span class="block text-xs text-gray-500"><?= lang('Wizard.edit_menu_desc') ?></span>
            </span>
        </button>
    </div>
</div>
