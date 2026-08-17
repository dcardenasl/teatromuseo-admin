<?php

/** @var array<string, mixed> $status */
$status = is_array($status ?? null) ? $status : [];
$lastAutomatic = is_string($status['last_automatic_invalidation_at'] ?? null)
    ? $status['last_automatic_invalidation_at']
    : null;
$lastInvalidation = is_string($status['last_invalidation_at'] ?? null)
    ? $status['last_invalidation_at']
    : null;
$scopes = is_array($status['last_invalidation_scopes'] ?? null) ? $status['last_invalidation_scopes'] : [];
?>

<section class="bg-white border border-gray-200 rounded-xl shadow-sm p-5">
    <?= view('layouts/partials/table_toolbar', [
        'title' => lang('System.cache_title'),
        'subtitle' => lang('System.cache_subtitle'),
    ]) ?>

    <?php if (is_string($statusError ?? null) && $statusError !== ''): ?>
        <div class="mt-5 rounded-lg border border-amber-200 bg-amber-50 p-4 text-sm text-amber-800">
            <?= esc($statusError) ?>
        </div>
    <?php endif; ?>

    <div class="mt-6 grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-4">
        <article class="rounded-xl border border-gray-200 bg-gray-50 p-4" x-data="cacheElapsed('<?= esc((string) ($lastAutomatic ?? ''), 'attr') ?>', <?= esc(json_encode(['calculating' => lang('System.cache_calculating'), 'elapsedPrefix' => lang('System.cache_elapsed_prefix')], JSON_THROW_ON_ERROR), 'attr') ?>)" x-init="start()">
            <p class="text-sm text-gray-500"><?= esc(lang('System.cache_last_automatic')) ?></p>
            <p class="mt-2 text-lg font-semibold text-gray-900">
                <?= $lastAutomatic !== null ? esc(format_date($lastAutomatic)) : esc(lang('System.cache_not_available')) ?>
            </p>
            <p class="mt-1 text-xs text-gray-500" x-text="label">
                <?= $lastAutomatic !== null ? esc(lang('System.cache_calculating')) : '' ?>
            </p>
        </article>

        <article class="rounded-xl border border-gray-200 bg-gray-50 p-4">
            <p class="text-sm text-gray-500"><?= esc(lang('System.cache_last_operation')) ?></p>
            <p class="mt-2 text-lg font-semibold text-gray-900">
                <?= $lastInvalidation !== null ? esc(format_date($lastInvalidation)) : esc(lang('System.cache_not_available')) ?>
            </p>
            <p class="mt-1 text-xs text-gray-500"><?= esc((string) ($status['last_invalidation_source'] ?? lang('System.cache_unknown'))) ?></p>
        </article>

        <article class="rounded-xl border border-gray-200 bg-gray-50 p-4">
            <p class="text-sm text-gray-500"><?= esc(lang('System.cache_deleted')) ?></p>
            <p class="mt-2 text-2xl font-semibold text-gray-900"><?= esc(number_format((int) ($status['last_deleted'] ?? 0))) ?></p>
            <p class="mt-1 text-xs text-gray-500"><?= esc(lang('System.cache_last_operation_help')) ?></p>
        </article>

        <article class="rounded-xl border border-gray-200 bg-gray-50 p-4">
            <p class="text-sm text-gray-500"><?= esc(lang('System.cache_backend')) ?></p>
            <p class="mt-2 text-lg font-semibold text-gray-900"><?= esc((string) ($status['handler'] ?? lang('System.cache_unknown'))) ?></p>
            <p class="mt-1 text-xs text-gray-500"><?= ($status['configured'] ?? false) ? esc(lang('System.cache_configured')) : esc(lang('System.cache_not_configured')) ?></p>
        </article>
    </div>

    <div class="mt-6 flex flex-col gap-4 rounded-xl border border-gray-200 p-4 md:flex-row md:items-center md:justify-between">
        <div>
            <h4 class="font-semibold text-gray-900"><?= esc(lang('System.cache_manual_title')) ?></h4>
            <p class="mt-1 text-sm text-gray-500"><?= esc(lang('System.cache_manual_help')) ?></p>
            <?php if ($scopes !== []): ?>
                <p class="mt-2 text-xs text-gray-500"><?= esc(lang('System.cache_scopes')) ?>: <?= esc(implode(', ', array_map('strval', $scopes))) ?></p>
            <?php endif; ?>
        </div>
        <?php if (has_permission('system.public-cache.invalidate')): ?>
            <form method="post" action="<?= esc(route_to('admin.system.cache.invalidate')) ?>" class="shrink-0">
                <?= csrf_field() ?>
                <button type="submit"
                        class="<?= esc(action_button_class('primary')) ?>"
                        data-confirm-message="<?= esc(lang('System.cache_confirm'), 'attr') ?>">
                    <?= ui_icon('refresh-cw', 'h-4 w-4') ?>
                    <?= esc(lang('System.cache_invalidate_button')) ?>
                </button>
            </form>
        <?php endif; ?>
    </div>
</section>
