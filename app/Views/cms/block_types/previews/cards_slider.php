<?php
/** @var array<string, mixed> $config */
/** @var array<string, mixed> $data */
$layout = (string) ($config['layout'] ?? 'slider');
$autoplay = filter_var($config['autoplay'] ?? true, FILTER_VALIDATE_BOOL);
$cssClass = esc($config['css_class'] ?? '');

$isSlider = $layout === 'slider';
?>
<div class="border border-dashed border-amber-300 bg-amber-50/10 rounded-xl p-4 <?= $cssClass ?>">
    <div class="flex items-center justify-between mb-3 border-b border-amber-100 pb-2">
        <span class="text-xs font-semibold text-amber-700 uppercase tracking-wider flex items-center gap-1.5">
            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-message-square"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
            <?= esc(lang('BlockPreview.cards.slider_title')) ?>
        </span>
        <span class="text-xxs bg-amber-100 text-amber-800 px-2 py-0.5 rounded-full font-medium">
            <?= $isSlider
                ? esc(lang('BlockPreview.cards.carousel')) . ' ' . esc(lang($autoplay ? 'BlockPreview.cards.yes' : 'BlockPreview.cards.no')) . ')'
                : esc(lang('BlockPreview.cards.grid')) ?>
        </span>
    </div>
    
    <?php if ($isSlider): ?>
        <div class="bg-white border border-slate-200 rounded-lg p-4 text-center max-w-md mx-auto">
            <div class="flex justify-center gap-1 mb-2 text-amber-400 text-xs">★★★★★</div>
            <p class="text-xs italic text-slate-600">"<?= esc(lang('BlockPreview.cards.quote')) ?>"</p>
            <div class="mt-3 text-[10px] font-bold text-slate-800"><?= esc(lang('BlockPreview.cards.author_context')) ?></div>
            <div class="flex justify-center gap-1 mt-3">
                <span class="h-1.5 w-4 rounded-full bg-amber-500"></span>
                <span class="h-1.5 w-1.5 rounded-full bg-slate-200"></span>
                <span class="h-1.5 w-1.5 rounded-full bg-slate-200"></span>
            </div>
        </div>
    <?php else: ?>
        <div class="grid grid-cols-3 gap-3">
            <?php for ($i = 1; $i <= 3; $i++): ?>
                <div class="bg-white border border-slate-200 rounded-lg p-3">
                    <div class="text-amber-400 text-[9px] mb-1">★★★★★</div>
                    <p class="text-[9px] italic text-slate-500 line-clamp-3">"<?= esc(lang('BlockPreview.cards.grid_quote')) ?>"</p>
                    <div class="mt-2 text-[9px] font-bold text-slate-700"><?= esc(lang('BlockPreview.cards.author')) ?> <?= $i ?></div>
                </div>
            <?php endfor; ?>
        </div>
    <?php endif; ?>
    
    <p class="text-[10px] text-slate-400 mt-3 text-center italic"><?= esc(lang('BlockPreview.cards.slider_child_hint')) ?></p>
</div>
