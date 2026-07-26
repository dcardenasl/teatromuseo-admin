<?php

/**
 * Compact per-language translation-status badges for a single block instance.
 *
 * Unlike translation_badges.php (JS-driven, fixed required-field list per
 * resource table), block required fields vary per block type/schema, so the
 * status is computed server-side by the domain's translation-audit engine
 * and simply rendered here — same shape/purpose as
 * components/table/translation_status_panel.php, scaled down to a list-item
 * badge cluster.
 *
 * @var array<int, array<string, mixed>> $languages Active languages (id, code, is_default)
 * @var array<string, array<string, mixed>> $statusByLanguage Keyed by language code -> {status, detail}
 * @var string $editUrl Base URL (no query string) to this block's editor; '' renders static (non-clickable) badges
 */

use App\Modules\Cms\Support\TranslationStatus;

$languages = $languages ?? [];
$statusByLanguage = $statusByLanguage ?? [];
$editUrl = $editUrl ?? '';

if ($languages === [] || $statusByLanguage === []) {
    return;
}
?>
<div class="flex items-center gap-1" role="list" aria-label="<?= esc(lang('Pages.blocks_translation_badges_label'), 'attr') ?>">
    <?php foreach ($languages as $lang):
        $langId = (int) ($lang['id'] ?? 0);
        $langCode = strtolower((string) ($lang['code'] ?? ''));
        if ($langId <= 0 || $langCode === '' || ! isset($statusByLanguage[$langCode])) {
            continue;
        }

        $status = (string) ($statusByLanguage[$langCode]['status'] ?? 'missing');
        $badgeUrl = $editUrl !== '' ? TranslationStatus::editUrl($editUrl, $langId) : '';
        $label = strtoupper($langCode) . ': ' . lang('Translations.status_' . $status);
        $badgeClasses = 'inline-flex items-center justify-center font-bold px-1.5 py-0.5 rounded text-[10px] border border-transparent transition '
            . TranslationStatus::badgeClasses($status, 'pill');
        ?>
        <?php if ($badgeUrl !== ''): ?>
            <a role="listitem" href="<?= esc($badgeUrl) ?>" title="<?= esc($label, 'attr') ?>" class="<?= esc($badgeClasses, 'attr') ?> hover:opacity-75">
                <?= esc(strtoupper($langCode)) ?>
            </a>
        <?php else: ?>
            <span role="listitem" title="<?= esc($label, 'attr') ?>" class="<?= esc($badgeClasses, 'attr') ?>">
                <?= esc(strtoupper($langCode)) ?>
            </span>
        <?php endif; ?>
    <?php endforeach; ?>
</div>
