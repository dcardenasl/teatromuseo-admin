<?php

$quality = is_array($quality ?? null) ? $quality : [];
$summary = is_array($quality['summary'] ?? null) ? $quality['summary'] : [];
$checks = is_array($quality['checks'] ?? null) ? $quality['checks'] : [];
$status = (string) ($quality['status'] ?? 'unavailable');
$statusLabel = match ($status) {
    'ready' => lang('PageQuality.status_ready'),
    'warning' => lang('PageQuality.status_warning'),
    'blocked' => lang('PageQuality.status_blocked'),
    default => lang('PageQuality.status_unavailable'),
};
$statusClass = match ($status) {
    'ready' => 'bg-green-50 border-green-200 text-green-800',
    'warning' => 'bg-yellow-50 border-yellow-200 text-yellow-800',
    'blocked' => 'bg-red-50 border-red-200 text-red-800',
    default => 'bg-gray-50 border-gray-200 text-gray-700',
};
$actionChecks = array_values(array_filter($checks, static fn (mixed $check): bool => is_array($check) && ($check['status'] ?? '') !== 'pass'));
$headingCheck = null;
foreach ($checks as $check) {
    if (is_array($check) && ($check['key'] ?? '') === 'page_heading_owner') {
        $headingCheck = $check;
        break;
    }
}
$headingOwners = is_array($headingCheck['heading_owners'] ?? null) ? $headingCheck['heading_owners'] : [];
$headingOwnerCount = count($headingOwners);
$qualityMessageKeys = [
    'page_not_found' => 'check_page_not_found',
    'field_configured' => 'check_field_configured',
    'default_title_required' => 'check_default_title_required',
    'default_slug_required' => 'check_default_slug_required',
    'translation_title_missing' => 'check_translation_title_missing',
    'translation_slug_missing' => 'check_translation_slug_missing',
    'meta_title_missing' => 'check_meta_title_missing',
    'meta_description_missing' => 'check_meta_description_missing',
    'field_length_valid' => 'check_field_length_valid',
    'meta_title_too_long' => 'check_meta_title_too_long',
    'meta_description_too_long' => 'check_meta_description_too_long',
    'schema_data_invalid' => 'check_schema_data_invalid',
    'schema_data_valid' => 'check_schema_data_valid',
    'og_image_configured' => 'check_og_image_configured',
    'og_image_missing' => 'check_og_image_missing',
    'page_heading_missing' => 'check_page_heading_missing',
    'page_heading_multiple' => 'check_page_heading_multiple',
    'page_heading_configured' => 'check_page_heading_configured',
    'active_blocks_configured' => 'check_active_blocks_configured',
    'active_blocks_missing' => 'check_active_blocks_missing',
    'sitemap_robots_consistent' => 'check_sitemap_robots_consistent',
    'sitemap_robots_inconsistent' => 'check_sitemap_robots_inconsistent',
];
?>

<section class="rounded-xl border <?= esc($statusClass) ?> p-4 shadow-sm" aria-labelledby="page-quality-title">
    <div class="flex items-start justify-between gap-3">
        <div>
            <h3 id="page-quality-title" class="text-sm font-semibold"><?= esc(lang('PageQuality.title')) ?></h3>
            <p class="mt-1 text-xs font-medium"><?= esc($statusLabel) ?></p>
        </div>
        <?php if (isset($quality['score'])): ?>
            <span class="text-lg font-bold" title="<?= esc(lang('PageQuality.score_title')) ?>"><?= esc((string) $quality['score']) ?><?= esc(lang('PageQuality.score_suffix')) ?></span>
        <?php endif; ?>
    </div>

    <?php if ($summary !== []): ?>
        <div class="mt-3 grid grid-cols-3 gap-2 text-center text-xs">
            <div class="rounded-lg bg-white/70 px-2 py-2"><strong class="block text-base"><?= (int) ($summary['errors'] ?? 0) ?></strong><?= esc(lang('PageQuality.summary_errors')) ?></div>
            <div class="rounded-lg bg-white/70 px-2 py-2"><strong class="block text-base"><?= (int) ($summary['warnings'] ?? 0) ?></strong><?= esc(lang('PageQuality.summary_warnings')) ?></div>
            <div class="rounded-lg bg-white/70 px-2 py-2"><strong class="block text-base"><?= (int) ($summary['passed'] ?? 0) ?></strong><?= esc(lang('PageQuality.summary_passed')) ?></div>
        </div>
    <?php endif; ?>

    <?php if (is_array($headingCheck)): ?>
        <?php if (($headingCheck['status'] ?? '') === 'pass'): ?>
            <p class="mt-3 rounded-lg border border-green-200 bg-green-50 px-3 py-2 text-xs text-green-800">
                <strong><?= esc(lang('PageQuality.h1_configured_title')) ?></strong> <?= esc(lang('PageQuality.h1_configured_body')) ?>
            </p>
        <?php elseif ($headingOwnerCount === 0): ?>
            <div class="mt-3 rounded-lg border border-red-300 bg-red-50 px-3 py-3 text-xs text-red-800" role="alert">
                <strong><?= esc(lang('PageQuality.h1_missing_title')) ?></strong>
                <p class="mt-1"><?= esc(lang('PageQuality.h1_missing_body')) ?></p>
            </div>
        <?php else: ?>
            <div class="mt-3 rounded-lg border border-red-300 bg-red-50 px-3 py-3 text-xs text-red-800" role="alert">
                <strong><?= esc(lang('PageQuality.h1_multiple_title')) ?></strong>
                <p class="mt-1"><?= esc(lang('PageQuality.h1_multiple_body')) ?></p>
            </div>
        <?php endif; ?>
    <?php endif; ?>

    <?php if ($actionChecks !== []): ?>
        <ul class="mt-3 space-y-2 text-xs">
            <?php foreach ($actionChecks as $check): ?>
                <?php $isError = ($check['status'] ?? '') === 'fail' && ($check['severity'] ?? '') === 'error'; ?>
                <?php $messageKey = $qualityMessageKeys[(string) ($check['message_key'] ?? '')] ?? 'action_default'; ?>
                <?php $messageParams = is_array($check['message_params'] ?? null) ? $check['message_params'] : []; ?>
                <li class="flex items-start gap-2">
                    <span class="mt-0.5 shrink-0 font-bold <?= $isError ? 'text-red-700' : 'text-yellow-700' ?>" aria-hidden="true"><?= $isError ? '!' : '&#8226;' ?></span>
                    <span><?= esc(lang('PageQuality.' . $messageKey, $messageParams)) ?></span>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php elseif ($quality !== []): ?>
        <p class="mt-3 text-xs"><?= esc(lang('PageQuality.complete')) ?></p>
    <?php else: ?>
        <p class="mt-3 text-xs"><?= esc(lang('PageQuality.unavailable')) ?></p>
    <?php endif; ?>
</section>
