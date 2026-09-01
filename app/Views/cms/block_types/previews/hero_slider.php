<?php
/** @var array<string, mixed> $config */
/** @var array<string, mixed> $data */

// hero_slider is a container: its real content lives in unlimited "slide_banner"
// child block instances (parent_instance_id), not in config/data on this block
// itself. blockPreview.js never sends children to this local fallback route —
// only the public website's BlockPreviewController can mock/resolve children
// (see App\Controllers\BlockPreviewController::getMockChildren() in
// ci4-website-builder-web) — so this fallback cannot honestly render slides.
// Showing a clear message beats faking a hardcoded slide.
?>
<div class="rounded-2xl border border-dashed border-slate-300 bg-slate-50 px-6 py-10 text-center">
    <p class="text-sm font-semibold text-slate-700"><?= esc(lang('BlockPreview.hero.slider_unavailable')) ?></p>
    <p class="mx-auto mt-2 max-w-md text-xs text-slate-500">
        <?= esc(lang('BlockPreview.hero.slider_children')) ?>
        <?= esc(lang('BlockPreview.hero.slider_help')) ?>
    </p>
</div>
