<?php
/**
 * Reusable Tiptap rich text editor partial.
 *
 * Required variables:
 *   $fieldName    string  HTML name attribute for the hidden input (e.g. "translations[0][block_data][content]")
 *   $initialValue string  Initial HTML content
 *
 * Optional variables:
 *   $required     bool    Whether the field is required (default false)
 *   $dynamicName  bool    When true, renders :name="inputName" instead of static name (for Alpine x-for contexts)
 */
$initialValue = (string) ($initialValue ?? '');
$fieldName    = (string) ($fieldName    ?? '');
$required     = (bool)   ($required     ?? false);
$dynamicName  = (bool)   ($dynamicName  ?? false);

$encodedContent  = htmlspecialchars($initialValue, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
$encodedName     = htmlspecialchars($fieldName, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
$jsonContent     = json_encode($initialValue, JSON_HEX_QUOT | JSON_HEX_APOS | JSON_UNESCAPED_UNICODE);
$jsonName        = json_encode($fieldName, JSON_UNESCAPED_UNICODE);
?>
<div x-data='richTextEditor(<?= esc($jsonContent, "attr") ?>, <?= esc($jsonName, "attr") ?>, {
         linkUrlPrompt: <?= esc(json_encode(lang('Labels.link_url_prompt'), JSON_THROW_ON_ERROR), 'attr') ?>,
         placeholder: <?= esc(json_encode(lang('Labels.rich_text_placeholder'), JSON_THROW_ON_ERROR), 'attr') ?>
     })'
     x-init="init()"
     class="border border-gray-300 rounded-lg overflow-hidden focus-within:ring-2 focus-within:ring-brand-500 focus-within:border-brand-500 transition-shadow">

    <?= view('partials/richtext_toolbar') ?>

    <!-- Editable content area -->
    <div x-ref="editorEl"
         class="richtext-content px-3 py-2.5 min-h-[130px] text-sm text-gray-800 cursor-text"></div>

    <!-- Hidden input — carries the HTML value on form submit -->
    <?php if ($dynamicName): ?>
        <input type="hidden" :name="inputName" x-ref="hiddenInput">
    <?php else: ?>
        <input type="hidden" name="<?= $encodedName ?>" x-ref="hiddenInput"
               value="<?= $encodedContent ?>"
               <?= $required ? 'required' : '' ?>>
    <?php endif; ?>
</div>
<p class="mt-1 text-[10px] text-gray-400">Ctrl+B negrita · Ctrl+I cursiva · Ctrl+K enlace</p>
