<?php /* Wizard — B4: Block save confirmation */ ?>

<!-- ── SCREEN: BLOCK SAVE SUCCESS (B4) ── -->
<div x-show="screen === 'block-saved'" x-cloak class="rounded-xl border border-gray-200 bg-white p-8 text-center shadow-sm">
    <div class="text-4xl mb-3">✅</div>
    <h2 class="text-lg font-semibold mb-2 text-gray-900"><?= lang('Wizard.block_saved_title') ?></h2>
    <p class="text-gray-500 text-sm mb-6"><?= lang('Wizard.block_saved_subtitle') ?></p>
    <div class="flex flex-wrap gap-3 items-center justify-center">
        <button @click="screen = 'page-blocks'" class="btn-primary"><?= lang('Wizard.btn_view_blocks') ?></button>
        <button @click="screen = 'home'" class="btn-secondary"><?= lang('Wizard.btn_back_panel') ?></button>
    </div>
</div>
