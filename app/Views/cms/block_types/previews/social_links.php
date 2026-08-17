<?php
/** @var array<string, mixed> $config */
/** @var array<string, mixed> $data */
$heading          = esc($data['heading'] ?? lang('BlockPreview.social.heading'));
$facebookUrl      = $config['facebook_url'] ?? '';
$facebookHandle   = esc($config['facebook_handle'] ?? '');
$instagramUrl     = $config['instagram_url'] ?? '';
$instagramHandle  = esc($config['instagram_handle'] ?? '');
$twitterUrl       = $config['twitter_url'] ?? '';
$youtubeUrl       = $config['youtube_url'] ?? '';
$cssClass         = esc($config['css_class'] ?? '');

$networks = [];
if ($facebookUrl) {
    $networks[] = ['url' => esc($facebookUrl), 'handle' => $facebookHandle ?: lang('BlockPreview.social.facebook'), 'label' => lang('BlockPreview.social.facebook'), 'color' => 'bg-blue-600', 'icon' => '<path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"/>'];
}
if ($instagramUrl) {
    $networks[] = ['url' => esc($instagramUrl), 'handle' => $instagramHandle ?: lang('BlockPreview.social.instagram'), 'label' => lang('BlockPreview.social.instagram'), 'color' => 'bg-pink-600', 'icon' => '<rect width="20" height="20" x="2" y="2" rx="5" ry="5"/><path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"/><line x1="17.5" x2="17.51" y1="6.5" y2="6.5"/>'];
}
if ($twitterUrl) {
    $networks[] = ['url' => esc($twitterUrl), 'handle' => '@twitter', 'label' => lang('BlockPreview.social.twitter'), 'color' => 'bg-gray-900', 'icon' => '<path d="M4 4l16 16M4 20 20 4"/>'];
}
if ($youtubeUrl) {
    $networks[] = ['url' => esc($youtubeUrl), 'handle' => lang('BlockPreview.social.youtube'), 'label' => lang('BlockPreview.social.youtube'), 'color' => 'bg-red-600', 'icon' => '<path d="m22 8-6 4 6 4V8z"/><rect width="14" height="12" x="2" y="6" rx="2" ry="2"/>'];
}
?>
<section class="py-10 <?= $cssClass ?>">
    <div class="max-w-5xl mx-auto px-4 text-center">
        <?php if ($heading): ?>
            <h2 class="text-2xl font-bold text-gray-900 mb-6"><?= $heading ?></h2>
        <?php endif; ?>

        <?php if ($networks !== []): ?>
            <div class="flex flex-wrap justify-center gap-4">
                <?php foreach ($networks as $network): ?>
                    <a href="<?= $network['url'] ?>"
                       target="_blank"
                       rel="noopener noreferrer"
                       class="flex items-center gap-3 px-5 py-3 rounded-xl border border-gray-200 bg-white hover:shadow-md transition-shadow group">
                        <span class="w-8 h-8 rounded-lg <?= $network['color'] ?> flex items-center justify-center">
                            <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <?= $network['icon'] ?>
                            </svg>
                        </span>
                        <div class="text-left">
                            <p class="text-xs text-gray-400"><?= $network['label'] ?></p>
                            <p class="text-sm font-semibold text-gray-800 group-hover:text-blue-600 transition-colors"><?= $network['handle'] ?></p>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="inline-flex flex-wrap justify-center gap-4">
                <div class="flex items-center gap-3 px-5 py-3 rounded-xl border border-gray-200 bg-white">
                    <span class="w-8 h-8 rounded-lg bg-blue-600 flex items-center justify-center">
                        <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 24 24"><path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"/></svg>
                    </span>
                    <div class="text-left">
                        <p class="text-xs text-gray-400"><?= esc(lang('BlockPreview.social.facebook')) ?></p>
                        <p class="text-sm font-semibold text-gray-800"><?= esc(lang('BlockPreview.social.facebook_handle')) ?></p>
                    </div>
                </div>
                <div class="flex items-center gap-3 px-5 py-3 rounded-xl border border-gray-200 bg-white">
                    <span class="w-8 h-8 rounded-lg bg-pink-600 flex items-center justify-center">
                        <svg class="w-4 h-4 text-white" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><rect width="20" height="20" x="2" y="2" rx="5" ry="5"/><path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"/><line x1="17.5" x2="17.51" y1="6.5" y2="6.5"/></svg>
                    </span>
                    <div class="text-left">
                        <p class="text-xs text-gray-400"><?= esc(lang('BlockPreview.social.instagram')) ?></p>
                        <p class="text-sm font-semibold text-gray-800"><?= esc(lang('BlockPreview.social.instagram_handle')) ?></p>
                    </div>
                </div>
            </div>
            <p class="text-xs text-gray-400 mt-4"><?= esc(lang('BlockPreview.social.hint')) ?></p>
        <?php endif; ?>
    </div>
</section>
