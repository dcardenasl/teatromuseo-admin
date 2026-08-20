<?php
/**
 * The dashboard is intentionally rendered from one permission-aware snapshot.
 * Each widget is a server-rendered projection of that same snapshot; this view
 * must not start secondary HTTP requests.
 *
 * @var array<string, string> $widgets
 * @var bool $canViewAnalytics
 */
?>
<header class="mb-8">
    <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900"><?= sprintf(lang('Dashboard.welcome_title'), esc($displayName ?? lang('Dashboard.user_fallback'))) ?></h1>
            <p class="text-gray-500 mt-1">
                <?= lang('Dashboard.welcome_subtitle') ?>
                <a href="<?= route_to('profile') ?>" class="inline-flex items-center gap-1 text-brand-600 hover:text-brand-700 font-medium ml-1 transition-colors">
                    <?= ui_icon('edit', 'h-3.5 w-3.5') ?>
                    <?= lang('Dashboard.edit_profile') ?>
                </a>
            </p>
        </div>
    </div>
</header>

<div class="max-w-[1600px] mx-auto space-y-6">
    <section class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <?= $widgets['stats'] ?? '' ?>
    </section>

    <section class="bg-white border border-gray-200 rounded-xl shadow-sm p-5">
        <div class="mb-5">
            <h3 class="text-lg font-semibold text-gray-900"><?= lang('Dashboard.summary_title') ?></h3>
            <p class="text-xs text-gray-500 mt-0.5"><?= lang('Dashboard.summary_desc') ?></p>
        </div>
        <?= $widgets['summary'] ?? '' ?>
    </section>

    <section class="bg-white border border-gray-200 rounded-xl shadow-sm p-5">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h3 class="text-sm font-semibold text-gray-900 uppercase tracking-wider"><?= lang('Dashboard.translations_overview') ?></h3>
                <p class="text-xs text-gray-500 mt-0.5"><?= lang('Dashboard.translations_overview_desc') ?></p>
            </div>
            <a href="<?= route_to('admin.cms.translations.audit') ?>" class="text-xs font-medium text-brand-600 hover:text-brand-700 shrink-0"><?= lang('Dashboard.view_full_audit') ?> &rarr;</a>
        </div>
        <?= $widgets['translations'] ?? '' ?>
    </section>

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
        <div class="xl:col-span-2 space-y-6">
            <section class="bg-white border border-gray-200 rounded-xl shadow-sm p-5">
                <h3 class="text-lg font-semibold text-gray-900 mb-5"><?= lang('Dashboard.recent_activity') ?></h3>
                <?= $widgets['activity'] ?? '' ?>
            </section>
        </div>

        <div class="space-y-6">
            <section class="bg-white border border-gray-200 rounded-xl shadow-sm p-5">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h3 class="text-sm font-semibold text-gray-900 uppercase tracking-wider"><?= lang('Dashboard.analytics_overview') ?></h3>
                        <p class="text-xs text-gray-500 mt-0.5"><?= lang('Dashboard.analytics_overview_desc') ?></p>
                    </div>
                </div>
                <?= $widgets['analytics'] ?? '' ?>
                <?php if ($canViewAnalytics): ?>
                    <a href="<?= route_to('admin.analytics') ?>" class="mt-4 inline-flex items-center gap-1 text-xs font-medium text-brand-600 hover:text-brand-700"><?= lang('Dashboard.view_full_analytics') ?> &rarr;</a>
                <?php endif; ?>
            </section>

            <?= $widgets['health'] ?? '' ?>
        </div>
    </div>

    <section class="bg-white border border-gray-200 rounded-xl shadow-sm p-5">
        <div class="flex items-center justify-between mb-5">
            <h3 class="text-sm font-semibold text-gray-900 uppercase tracking-wider"><?= lang('Dashboard.latest_files') ?></h3>
            <a href="<?= route_to('files') ?>" class="text-xs font-medium text-brand-600 hover:text-brand-700"><?= lang('Dashboard.manage_files') ?> &rarr;</a>
        </div>
        <?= $widgets['recentFiles'] ?? '' ?>
    </section>
</div>
