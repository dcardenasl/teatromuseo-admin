<?php
/**
 * @var string $title
 * @var string $csrfName
 * @var string $csrfToken
 */
$csrfName  ??= csrf_token();
$csrfToken ??= csrf_hash();
?>
<div class="max-w-6xl mx-auto space-y-6" x-data="wizard()" x-init="init()">

    <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div class="space-y-1">
                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-gray-500"><?= lang('Wizard.home_heading') ?></p>
                <h1 class="text-2xl font-semibold text-gray-900"><?= lang('Wizard.home_heading') ?></h1>
                <p class="text-sm text-gray-600"><?= lang('Wizard.structure_intro') ?></p>
            </div>
            <a href="<?= site_url('dashboard') ?>" class="btn-secondary"><?= lang('Wizard.btn_back_panel') ?></a>
        </div>
    </div>

    <!-- Loading screen -->
    <div x-show="screen === 'loading'" x-cloak class="rounded-xl border border-gray-200 bg-white p-10 text-center shadow-sm">
        <div class="animate-spin rounded-full h-10 w-10 border-b-2 border-brand-600 mx-auto mb-4"></div>
        <p class="text-sm text-gray-500"><?= lang('Wizard.loading') ?></p>
    </div>

    <!-- Error screen -->
    <div x-show="screen === 'error'" x-cloak class="rounded-xl border border-red-200 bg-red-50 p-6 shadow-sm">
        <p class="text-sm font-medium text-red-700" x-text="errorMsg"></p>
        <div class="mt-4 flex flex-wrap gap-3">
            <button @click="init()" class="btn-secondary"><?= lang('Wizard.btn_retry') ?></button>
            <button @click="goHome()" class="btn-primary"><?= lang('Wizard.btn_back_panel') ?></button>
        </div>
    </div>

    <!-- ── Home screen ── -->
    <?= view('cms/wizard/_partials/home') ?>

    <!-- ── Entry creation flow (A screens) ── -->
    <?= view('cms/wizard/_partials/entry_wizard') ?>

    <!-- ── Page selection (B1) ── -->
    <?= view('cms/wizard/_partials/page_list') ?>

    <!-- ── Page layout / block tree (B2) ── -->
    <?= view('cms/wizard/_partials/page_layout') ?>

    <!-- ── Block type catalog (new) ── -->
    <?= view('cms/wizard/_partials/block_catalog') ?>

    <!-- ── Block editor (B3) ── -->
    <?= view('cms/wizard/_partials/block_edit') ?>

    <!-- ── Block saved (B4) ── -->
    <?= view('cms/wizard/_partials/block_saved') ?>

    <!-- ── Menu edit (C screens) ── -->
    <?= view('cms/wizard/_partials/menu_edit') ?>

</div>

<?php
// Boot config for the wizard() Alpine component (src/js/components/wizard/index.js),
// bundled into public/assets/js/app.js. Mirrors the window.__componentConfig pattern
// already used in layouts/partials/head.php — data only, no logic lives here.
$wizardBootJson = json_encode([
    'csrfName'      => $csrfName,
    'csrfToken'     => $csrfToken,
    'wizardBase'    => site_url('admin/cms/wizard'),
    'adminCmsBase'  => site_url('admin/cms'),
    'publicSiteUrl' => rtrim((string) env('PUBLIC_SITE_URL'), '/'),
    'translateUrl'  => route_to('admin.cms.translate'),
    'strings' => [
        'step_of'                   => lang('Wizard.step_of'),
        'page_fallback'             => lang('Wizard.page_fallback'),
        'menu_fallback'             => lang('Wizard.menu_fallback'),
        'delete_confirm'            => lang('Wizard.delete_item_confirm'),
        'add_content_desc'          => lang('Wizard.add_content_desc'),
        'add_content_desc_empty'    => lang('Wizard.add_content_desc_empty'),
        'error_no_pages'            => lang('Wizard.error_no_pages'),
        'error_no_menus'            => lang('Wizard.error_no_menus'),
        'error_blocks_load'         => lang('Wizard.error_blocks_load'),
        'error_items_load'          => lang('Wizard.error_items_load'),
        'error_block_save'          => lang('Wizard.error_block_save'),
        'error_item_save'           => lang('Wizard.error_item_save'),
        'error_item_delete'         => lang('Wizard.error_item_delete'),
        'error_upload'              => lang('Wizard.error_upload'),
        'error_publish'             => lang('Wizard.error_publish'),
        'error_load'                => lang('Wizard.error_load'),
        'error_block_type_missing'  => lang('Wizard.error_block_type_missing'),
        'error_block_delete'        => lang('Wizard.error_block_delete'),
        'error_upload_failed'       => lang('Wizard.error_upload_failed'),
        'error_collection_required' => lang('Entries.collection_not_exists'),
        'add_child'                 => lang('Wizard.add_child'),
        'add_block'                 => lang('Wizard.add_block'),
        'block_fallback'            => lang('Wizard.block_fallback'),
        'content_fallback'          => lang('Wizard.content_fallback'),
        'owner_label_entry'         => lang('Pages.owner_label_entry'),
        'owner_label_page'          => lang('Pages.owner_label_page'),
        'blocks_description_entry'  => lang('Wizard.blocks_description_entry'),
        'blocks_description_page'   => lang('Wizard.blocks_description_page'),
        'no_blocks_entry'           => lang('Wizard.no_blocks_entry'),
        'no_blocks_page'            => lang('Wizard.no_blocks_page'),
        'wizard_structure_languages_translate_error'  => lang('Wizard.wizard_structure_languages_translate_error'),
        'wizard_structure_page_default_title'         => lang('Wizard.wizard_structure_page_default_title'),
        'wizard_content_review_translation_partial'   => lang('Wizard.wizard_content_review_translation_partial'),
        'wizard_content_confirm_translations_loading' => lang('Wizard.wizard_content_confirm_translations_loading'),
        'default_step1_title'  => lang('Wizard.default_step1_title'),
        'default_step1_hint'   => lang('Wizard.default_step1_hint'),
        'default_field_title'  => lang('Wizard.default_field_title'),
        'error_title_required' => lang('Wizard.default_field_title'),
        'default_step2_title'  => lang('Wizard.default_step2_title'),
        'default_step2_hint'   => lang('Wizard.default_step2_hint'),
        'default_field_image'  => lang('Wizard.default_field_image'),
        'default_step3_title'  => lang('Wizard.default_step3_title'),
        'default_step3_hint'   => lang('Wizard.default_step3_hint'),
        'default_field_excerpt' => lang('Wizard.default_field_excerpt'),
    ],
], JSON_THROW_ON_ERROR);
?>
<script <?= csp_script_nonce() ?>>
  window.__wizardBoot = <?= $wizardBootJson ?>;
</script>
