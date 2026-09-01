<?php
/** @var bool $available */
$available = $available ?? true;
if ($available === true) {
    return;
}
?>

<div class="mb-4 flex items-start gap-3 rounded-lg border border-amber-300 bg-amber-50 p-4 text-sm text-amber-900" role="alert">
    <i data-lucide="triangle-alert" class="mt-0.5 h-4 w-4 shrink-0 text-amber-600" aria-hidden="true"></i>
    <div>
        <strong><?= esc(lang('App.lookup_unavailable_title')) ?></strong>
        <?= esc(lang('App.lookup_unavailable_body')) ?>
    </div>
</div>
