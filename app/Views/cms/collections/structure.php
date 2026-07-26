<?php $item = $item ?? []; ?>
<?php $itemId = (string) ($item['id'] ?? ''); ?>

<div class="mb-4 flex flex-wrap items-center justify-between gap-3">
    <a href="<?= route_to('admin.cms.collections.edit', $itemId) ?>" class="text-sm text-brand-600 hover:text-brand-700">&larr; <?= esc(lang('App.back')) ?></a>
    <a href="<?= route_to('admin.cms.collections.show', $itemId) ?>" class="<?= esc(action_button_class()) ?> text-center">
        <?= esc(lang('Collections.collections_details')) ?>
    </a>
</div>

<?php ob_start(); ?>
<form method="post" action="<?= route_to('admin.cms.collections.update_structure', $itemId) ?>" class="grid grid-cols-1 gap-6 lg:grid-cols-3">
    <?= csrf_field() ?>
    <input type="hidden" name="current_id" value="<?= esc($itemId) ?>">

    <div class="lg:col-span-2 space-y-6">
        <?php
        $templateData = [];
if (is_array($item['block_template'] ?? null)) {
    $templateData = $item['block_template'];
} elseif (is_string($item['block_template'] ?? null) && trim((string) $item['block_template']) !== '') {
    $decodedTemplate = json_decode((string) $item['block_template'], true);
    if (json_last_error() === JSON_ERROR_NONE && is_array($decodedTemplate)) {
        $templateData = $decodedTemplate;
    }
}

$templateBlocks = is_array($templateData['blocks'] ?? null) ? $templateData['blocks'] : [];
?>

        <section class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
            <div class="flex flex-wrap items-start justify-between gap-3">
                <div>
                    <h4 class="text-sm font-semibold text-gray-900"><?= esc(lang('Collections.collections_structure')) ?></h4>
                    <p class="mt-1 text-xs text-gray-500"><?= esc(lang('Collections.collections_structure_help')) ?></p>
                </div>
                <div class="inline-flex items-center gap-2 rounded-lg border border-brand-200 bg-brand-50 px-3 py-1.5 text-xs font-medium text-brand-700">
                    <span><?= esc(lang('Collections.block_template_builder_count')) ?>:</span>
                    <span><?= esc((string) count($templateBlocks)) ?></span>
                </div>
            </div>

            <div class="mt-4 grid gap-3 sm:grid-cols-2">
                <div class="rounded-lg border border-gray-200 bg-gray-50/70 px-4 py-3">
                    <p class="text-[11px] font-semibold uppercase tracking-wide text-gray-500"><?= esc(lang('Collections.field_collection_key')) ?></p>
                    <p class="mt-1 text-sm font-medium text-gray-900"><?= esc($item['collection_key'] ?? '—') ?></p>
                </div>
                <div class="rounded-lg border border-dashed border-gray-200 bg-gray-50/40 px-4 py-3 text-sm text-gray-600">
                    <?= count($templateBlocks) > 0
                ? esc(lang('Collections.collections_structure_has_template'))
                : esc(lang('Collections.collections_structure_empty')) ?>
                </div>
            </div>
        </section>

        <?= view('cms/collections/partials/block_template_editor', [
    'value' => $item['block_template'] ?? null,
    'blockTypes' => $blockTypes ?? [],
    'collectionPresets' => $collectionPresets ?? [],
    'wizardConfig' => $item['wizard_config'] ?? null,
    'errors' => $errors ?? [],
]) ?>
    </div>

    <aside class="order-first space-y-6 lg:order-none">
        <?php ob_start(); ?>
        <button type="submit" data-testid="collection-structure-save" class="<?= esc(action_button_class('primary')) ?> w-full justify-center text-center py-2.5">
            <?= esc(lang('Collections.collections_structure_save')) ?>
        </button>
        <a href="<?= route_to('admin.cms.collections.edit', $itemId) ?>" class="<?= esc(action_button_class()) ?> w-full justify-center text-center py-2.5">
            <?= esc(lang('App.cancel')) ?>
        </a>
        <?php $actionsContent = ob_get_clean(); ?>
        <?= view('components/display/admin_actions_panel', ['content' => $actionsContent]) ?>

        <?php ob_start(); ?>
        <section class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
            <h4 class="text-sm font-semibold text-gray-900"><?= esc(lang('Collections.collections_structure')) ?></h4>
            <p class="mt-2 text-xs leading-5 text-gray-500">
                <?= esc(lang('Collections.collections_structure_help')) ?>
            </p>
        </section>
        <?php $noteContent = ob_get_clean(); ?>
        <?= $noteContent ?>
    </aside>
</form>
<?php $sectionContent = ob_get_clean(); ?>

<?= view('components/display/form_section', [
    'title' => 'Collections.collections_structure',
    'description' => 'Collections.collections_structure_help',
    'content' => $sectionContent,
]) ?>
