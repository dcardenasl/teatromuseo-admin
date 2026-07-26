<?php
/** @var array<string, mixed> $config */
/** @var array<string, mixed> $data */
$content  = block_text_content($data, '<p class="text-gray-400 italic">Sin contenido todavía.</p>');
$content  = html_entity_decode($content, ENT_QUOTES | ENT_HTML5, 'UTF-8');
$cssClass = esc($config['css_class'] ?? '');
?>
<div class="prose max-w-none p-4 border border-dashed border-gray-200 rounded-lg <?= $cssClass ?>">
    <?= $content ?>
</div>
