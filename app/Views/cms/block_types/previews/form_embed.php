<?php
/** @var array<string, mixed> $config */
/** @var array<string, mixed> $data */
$heading        = esc($data['heading'] ?? lang('BlockPreview.form.title'));
$submitLabel    = esc($data['submit_label'] ?? lang('BlockPreview.form.submit'));
$showCompany    = ! empty($config['show_company']) && $config['show_company'] !== false && $config['show_company'] !== 'false';
$phonePrefix    = esc($config['phone_prefix'] ?? '');
$cssClass       = esc($config['css_class'] ?? '');
?>
<section class="py-10 <?= $cssClass ?>">
    <div class="max-w-lg mx-auto px-4">
        <?php if ($heading): ?>
            <h2 class="text-2xl font-bold text-gray-900 mb-6"><?= $heading ?></h2>
        <?php endif; ?>

        <form class="space-y-4" onsubmit="return false;">
            <?php if ($showCompany): ?>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1"><?= esc(lang('BlockPreview.form.company')) ?> <span class="text-gray-400 font-normal"><?= esc(lang('BlockPreview.form.optional')) ?></span></label>
                    <input type="text" placeholder="<?= esc(lang('BlockPreview.form.organization')) ?>" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-900 shadow-sm transition focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500" />
                </div>
            <?php endif; ?>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1"><?= esc(lang('BlockPreview.form.name')) ?> <span class="text-red-500">*</span></label>
                <input type="text" placeholder="<?= esc(lang('BlockPreview.form.full_name')) ?>" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-900 shadow-sm transition focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500" />
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1"><?= esc(lang('BlockPreview.form.email')) ?> <span class="text-red-500">*</span></label>
                <input type="email" placeholder="<?= esc(lang('BlockPreview.form.email_placeholder')) ?>" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-900 shadow-sm transition focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500" />
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1"><?= esc(lang('BlockPreview.form.phone')) ?></label>
                <div class="flex gap-2">
                    <?php if ($phonePrefix): ?>
                        <span class="inline-flex items-center px-3 rounded-lg border border-gray-300 bg-gray-50 text-gray-500 text-sm"><?= $phonePrefix ?></span>
                    <?php endif; ?>
                    <input type="tel" placeholder="<?= esc(lang('BlockPreview.form.phone_placeholder')) ?>" class="<?= $phonePrefix ? 'flex-1' : 'w-full' ?> rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-900 shadow-sm transition focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500" />
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1"><?= esc(lang('BlockPreview.form.content')) ?> <span class="text-red-500">*</span></label>
                <textarea rows="5" placeholder="<?= esc(lang('BlockPreview.form.message_placeholder')) ?>" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-900 shadow-sm transition focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 resize-none"></textarea>
            </div>

            <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2.5 px-4 rounded-lg transition-colors text-sm">
                <?= $submitLabel ?>
            </button>
        </form>
    </div>
</section>
