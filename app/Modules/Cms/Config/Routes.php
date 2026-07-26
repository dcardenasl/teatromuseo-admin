<?php

declare(strict_types=1);

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

$routes->group('admin/cms', ['filter' => ['auth', 'admin']], static function (RouteCollection $routes): void {
    // Wizard — content creation assistant (must be before any (:segment) routes)
    $routes->get('wizard', '\App\Modules\Cms\Controllers\WizardController::index', ['as' => 'admin.cms.wizard',        'filter' => 'permission:cms.entries.read']);
    $routes->get('wizard/config', '\App\Modules\Cms\Controllers\WizardController::config', ['as' => 'admin.cms.wizard.config',  'filter' => 'permission:cms.entries.read']);
    $routes->post('wizard/publish', '\App\Modules\Cms\Controllers\WizardController::publish', ['as' => 'admin.cms.wizard.publish', 'filter' => 'permission:cms.entries.write']);
    $routes->post('wizard/upload', '\App\Modules\Cms\Controllers\WizardController::uploadImage', ['as' => 'admin.cms.wizard.upload',  'filter' => 'permission:cms.entries.write']);
    // wizard/structure/* intentionally has no route-level `permission:` filter: access needs an
    // OR of cms.pages.write / cms.menus.write / cms.collections.write (which resource within the
    // wizard is being built varies per-request), and the `permission:<code>` filter only accepts
    // one code. Each action enforces its own permission(s) in-code instead — see
    // StructureWizardController::index()/config() (OR of all three) and
    // createPage()/createMenu()/createCollection() (the single matching write permission).
    $routes->get('wizard/structure', '\App\Modules\Cms\Controllers\StructureWizardController::index', ['as' => 'admin.cms.wizard.structure']);
    $routes->get('wizard/structure/config', '\App\Modules\Cms\Controllers\StructureWizardController::config', ['as' => 'admin.cms.wizard.structure.config']);
    $routes->post('wizard/structure/create-collection', '\App\Modules\Cms\Controllers\StructureWizardController::createCollection', ['as' => 'admin.cms.wizard.structure.create_collection']);
    $routes->post('wizard/structure/create-page', '\App\Modules\Cms\Controllers\StructureWizardController::createPage', ['as' => 'admin.cms.wizard.structure.create_page']);
    $routes->post('wizard/structure/create-menu', '\App\Modules\Cms\Controllers\StructureWizardController::createMenu', ['as' => 'admin.cms.wizard.structure.create_menu']);
    // Wizard — Edit page (WIZ-007)
    $routes->get('wizard/pages/(:num)/blocks', '\App\Modules\Cms\Controllers\WizardController::pageBlocks/$1', ['as' => 'admin.cms.wizard.page-blocks',       'filter' => 'permission:cms.pages.read']);
    $routes->post('wizard/pages/(:num)/blocks', '\App\Modules\Cms\Controllers\WizardController::createBlock/$1', ['as' => 'admin.cms.wizard.create-block',      'filter' => 'permission:cms.pages.write']);
    $routes->post('wizard/pages/(:num)/blocks/(:num)', '\App\Modules\Cms\Controllers\WizardController::updateBlock/$1/$2', ['as' => 'admin.cms.wizard.update-block',       'filter' => 'permission:cms.pages.write']);
    $routes->post('wizard/pages/(:num)/blocks/(:num)/delete', '\App\Modules\Cms\Controllers\WizardController::deleteBlock/$1/$2', ['as' => 'admin.cms.wizard.delete-block',      'filter' => 'permission:cms.pages.write']);
    // Wizard — Entry blocks review/edit
    $routes->get('wizard/entries/(:num)/blocks', '\App\Modules\Cms\Controllers\WizardController::entryBlocks/$1', ['as' => 'admin.cms.wizard.entry-blocks',      'filter' => 'permission:cms.entries.read']);
    $routes->post('wizard/entries/(:num)/blocks', '\App\Modules\Cms\Controllers\WizardController::createEntryBlock/$1', ['as' => 'admin.cms.wizard.create-entry-block', 'filter' => 'permission:cms.entries.write']);
    $routes->post('wizard/entries/(:num)/blocks/(:num)', '\App\Modules\Cms\Controllers\WizardController::updateEntryBlock/$1/$2', ['as' => 'admin.cms.wizard.update-entry-block', 'filter' => 'permission:cms.entries.write']);
    $routes->post('wizard/entries/(:num)/blocks/(:num)/delete', '\App\Modules\Cms\Controllers\WizardController::deleteEntryBlock/$1/$2', ['as' => 'admin.cms.wizard.delete-entry-block', 'filter' => 'permission:cms.entries.write']);
    // Wizard — Edit menu (WIZ-008)
    $routes->get('wizard/menus/(:num)/items', '\App\Modules\Cms\Controllers\WizardController::menuItems/$1', ['as' => 'admin.cms.wizard.menu-items',         'filter' => 'permission:cms.menus.read']);
    $routes->post('wizard/menus/(:num)/items', '\App\Modules\Cms\Controllers\WizardController::addMenuItem/$1', ['as' => 'admin.cms.wizard.add-menu-item',      'filter' => 'permission:cms.menus.write']);
    $routes->post('wizard/menus/items/(:num)/delete', '\App\Modules\Cms\Controllers\WizardController::deleteMenuItem/$1', ['as' => 'admin.cms.wizard.delete-menu-item',   'filter' => 'permission:cms.menus.write']);
    $routes->post('wizard/menus/items/(:num)', '\App\Modules\Cms\Controllers\WizardController::updateMenuItem/$1', ['as' => 'admin.cms.wizard.update-menu-item',   'filter' => 'permission:cms.menus.write']);

    // File Translations
    $routes->get('files/(:num)/translations', '\\App\\Modules\\Cms\\Controllers\\FileTranslationController::edit/$1', ['as' => 'admin.cms.file_translations.edit',   'filter' => 'permission:cms.pages.write']);
    $routes->post('files/(:num)/translations', '\\App\\Modules\\Cms\\Controllers\\FileTranslationController::update/$1', ['as' => 'admin.cms.file_translations.update', 'filter' => 'permission:cms.pages.write']);

    // Language
    $routes->get('languages', '\\App\\Modules\\Cms\\Controllers\\LanguageController::index', ['as' => 'admin.cms.languages', 'filter' => 'permission:cms.languages.read']);
    $routes->get('languages/data', '\\App\\Modules\\Cms\\Controllers\\LanguageController::data', ['as' => 'admin.cms.languages.data', 'filter' => 'permission:cms.languages.read']);
    $routes->get('languages/create', '\\App\\Modules\\Cms\\Controllers\\LanguageController::create', ['as' => 'admin.cms.languages.create', 'filter' => 'permission:cms.languages.write']);
    $routes->get('languages/reorder', '\\App\\Modules\\Cms\\Controllers\\LanguageController::reorder', ['as' => 'admin.cms.languages.reorder', 'filter' => 'permission:cms.languages.write']);
    $routes->post('languages/reorder', '\\App\\Modules\\Cms\\Controllers\\LanguageController::saveOrder', ['as' => 'admin.cms.languages.save_order', 'filter' => 'permission:cms.languages.write']);
    $routes->post('languages', '\\App\\Modules\\Cms\\Controllers\\LanguageController::store', ['as' => 'admin.cms.languages.store', 'filter' => 'permission:cms.languages.write']);
    $routes->get('languages/(:segment)', '\\App\\Modules\\Cms\\Controllers\\LanguageController::show/$1', ['as' => 'admin.cms.languages.show', 'filter' => 'permission:cms.languages.read']);
    $routes->get('languages/(:segment)/edit', '\\App\\Modules\\Cms\\Controllers\\LanguageController::edit/$1', ['as' => 'admin.cms.languages.edit', 'filter' => 'permission:cms.languages.write']);
    $routes->post('languages/(:segment)', '\\App\\Modules\\Cms\\Controllers\\LanguageController::update/$1', ['as' => 'admin.cms.languages.update', 'filter' => 'permission:cms.languages.write']);
    $routes->post('languages/(:segment)/delete', '\\App\\Modules\\Cms\\Controllers\\LanguageController::delete/$1', ['as' => 'admin.cms.languages.delete', 'filter' => 'permission:cms.languages.write']);
    $routes->post('languages/(:segment)/set-default', '\\App\\Modules\\Cms\\Controllers\\LanguageController::setDefault/$1', ['as' => 'admin.cms.languages.set_default', 'filter' => 'permission:cms.languages.write']);



    // Site Identity
    $routes->get('site-identity', '\App\Modules\Cms\Controllers\SiteIdentityController::show', ['as' => 'admin.cms.site_identity', 'filter' => 'permission:cms.settings.write']);
    $routes->post('site-identity', '\App\Modules\Cms\Controllers\SiteIdentityController::update', ['as' => 'admin.cms.site_identity.update', 'filter' => 'permission:cms.settings.write']);

    // Setting
    $routes->get('settings', '\App\Modules\Cms\Controllers\SettingController::index', ['as' => 'admin.cms.settings', 'filter' => 'permission:cms.settings.read']);
    $routes->get('settings/data', '\App\Modules\Cms\Controllers\SettingController::data', ['as' => 'admin.cms.settings.data', 'filter' => 'permission:cms.settings.read']);
    $routes->get('settings/create', '\App\Modules\Cms\Controllers\SettingController::create', ['as' => 'admin.cms.settings.create', 'filter' => 'permission:cms.settings.write']);
    $routes->post('settings', '\App\Modules\Cms\Controllers\SettingController::store', ['as' => 'admin.cms.settings.store', 'filter' => 'permission:cms.settings.write']);
    $routes->get('settings/(:segment)', '\App\Modules\Cms\Controllers\SettingController::show/$1', ['as' => 'admin.cms.settings.show', 'filter' => 'permission:cms.settings.read']);
    $routes->get('settings/(:segment)/edit', '\App\Modules\Cms\Controllers\SettingController::edit/$1', ['as' => 'admin.cms.settings.edit', 'filter' => 'permission:cms.settings.write']);
    $routes->post('settings/(:segment)', '\App\Modules\Cms\Controllers\SettingController::update/$1', ['as' => 'admin.cms.settings.update', 'filter' => 'permission:cms.settings.write']);
    $routes->post('settings/(:segment)/delete', '\App\Modules\Cms\Controllers\SettingController::delete/$1', ['as' => 'admin.cms.settings.delete', 'filter' => 'permission:cms.settings.write']);

    // Translate proxy (DeepL)
    // Translation calls are expensive (external provider); throttle this
    // read explicitly while normal Admin GET requests remain unrestricted.
    $routes->get('translate', '\App\Modules\Cms\Controllers\TranslateController::translate', ['as' => 'admin.cms.translate', 'filter' => 'ratelimit:300,60']);

    // Translation Auditing
    $routes->get('translations/audit', '\App\Modules\Cms\Controllers\TranslationAuditController::index', ['as' => 'admin.cms.translations.audit', 'filter' => 'permission:cms.languages.read']);
    $routes->get('translations/audit/data', '\App\Modules\Cms\Controllers\TranslationAuditController::data', ['as' => 'admin.cms.translations.audit.data', 'filter' => 'permission:cms.languages.read']);

    // Page
    $routes->get('pages', '\App\Modules\Cms\Controllers\PageController::index', ['as' => 'admin.cms.pages', 'filter' => 'permission:cms.pages.read']);
    $routes->get('pages/data', '\App\Modules\Cms\Controllers\PageController::data', ['as' => 'admin.cms.pages.data', 'filter' => 'permission:cms.pages.read']);
    $routes->get('pages/check-slug', '\App\Modules\Cms\Controllers\PageController::checkSlug', ['as' => 'admin.cms.pages.check_slug', 'filter' => 'permission:cms.pages.read']);
    $routes->get('pages/create', '\App\Modules\Cms\Controllers\PageController::create', ['as' => 'admin.cms.pages.create', 'filter' => 'permission:cms.pages.write']);
    $routes->get('pages/reorder', '\App\Modules\Cms\Controllers\PageController::reorder', ['as' => 'admin.cms.pages.reorder', 'filter' => 'permission:cms.pages.write']);
    $routes->post('pages/reorder', '\App\Modules\Cms\Controllers\PageController::saveOrder', ['as' => 'admin.cms.pages.save_order', 'filter' => 'permission:cms.pages.write']);
    $routes->post('pages', '\App\Modules\Cms\Controllers\PageController::store', ['as' => 'admin.cms.pages.store', 'filter' => 'permission:cms.pages.write']);
    $routes->get('pages/(:segment)', '\App\Modules\Cms\Controllers\PageController::show/$1', ['as' => 'admin.cms.pages.show', 'filter' => 'permission:cms.pages.read']);
    $routes->get('pages/(:segment)/edit', '\App\Modules\Cms\Controllers\PageController::edit/$1', ['as' => 'admin.cms.pages.edit', 'filter' => 'permission:cms.pages.write']);
    $routes->post('pages/(:segment)', '\App\Modules\Cms\Controllers\PageController::update/$1', ['as' => 'admin.cms.pages.update', 'filter' => 'permission:cms.pages.write']);
    $routes->post('pages/(:segment)/delete', '\App\Modules\Cms\Controllers\PageController::delete/$1', ['as' => 'admin.cms.pages.delete', 'filter' => 'permission:cms.pages.write']);
    $routes->post('pages/(:segment)/publish', '\App\Modules\Cms\Controllers\PageController::publish/$1', ['as' => 'admin.cms.pages.publish', 'filter' => 'permission:cms.pages.write']);
    $routes->post('pages/(:segment)/archive', '\App\Modules\Cms\Controllers\PageController::archive/$1', ['as' => 'admin.cms.pages.archive', 'filter' => 'permission:cms.pages.write']);

    // Page Blocks Builder
    $routes->get('pages/(:num)/blocks', '\App\Modules\Cms\Controllers\BlockInstanceController::index/$1', ['as' => 'admin.cms.pages.blocks', 'filter' => 'permission:cms.pages.read']);
    $routes->get('pages/(:num)/blocks/create', '\App\Modules\Cms\Controllers\BlockInstanceController::create/$1', ['as' => 'admin.cms.pages.blocks.create', 'filter' => 'permission:cms.pages.write']);
    $routes->post('pages/(:num)/blocks/store', '\App\Modules\Cms\Controllers\BlockInstanceController::store/$1', ['as' => 'admin.cms.pages.blocks.store', 'filter' => 'permission:cms.pages.write']);
    $routes->get('pages/(:num)/blocks/(:num)/edit', '\App\Modules\Cms\Controllers\BlockInstanceController::edit/$1/$2', ['as' => 'admin.cms.pages.blocks.edit', 'filter' => 'permission:cms.pages.write']);
    $routes->post('pages/(:num)/blocks/(:num)', '\App\Modules\Cms\Controllers\BlockInstanceController::update/$1/$2', ['as' => 'admin.cms.pages.blocks.update', 'filter' => 'permission:cms.pages.write']);
    $routes->post('pages/(:num)/blocks/(:num)/delete', '\App\Modules\Cms\Controllers\BlockInstanceController::delete/$1/$2', ['as' => 'admin.cms.pages.blocks.delete', 'filter' => 'permission:cms.pages.write']);
    $routes->post('pages/(:num)/blocks/reorder', '\App\Modules\Cms\Controllers\BlockInstanceController::reorder/$1', ['as' => 'admin.cms.pages.blocks.reorder', 'filter' => 'permission:cms.pages.write']);

    // Child block management (for container blocks like hero_slider)
    $routes->get('pages/(:num)/blocks/(:num)/children', '\App\Modules\Cms\Controllers\BlockInstanceController::children/$1/$2', ['as' => 'admin.cms.pages.blocks.children', 'filter' => 'permission:cms.pages.read']);
    $routes->post('pages/(:num)/blocks/(:num)/children/reorder', '\App\Modules\Cms\Controllers\BlockInstanceController::reorderChildren/$1/$2', ['as' => 'admin.cms.pages.blocks.children.reorder', 'filter' => 'permission:cms.pages.write']);

    // Entry Blocks Builder
    $routes->get('entries/(:num)/blocks', '\App\Modules\Cms\Controllers\BlockInstanceController::index/$1', ['as' => 'admin.cms.entries.blocks', 'filter' => 'permission:cms.entries.read']);
    $routes->get('entries/(:num)/blocks/create', '\App\Modules\Cms\Controllers\BlockInstanceController::create/$1', ['as' => 'admin.cms.entries.blocks.create', 'filter' => 'permission:cms.entries.write']);
    $routes->post('entries/(:num)/blocks/store', '\App\Modules\Cms\Controllers\BlockInstanceController::store/$1', ['as' => 'admin.cms.entries.blocks.store', 'filter' => 'permission:cms.entries.write']);
    $routes->get('entries/(:num)/blocks/(:num)/edit', '\App\Modules\Cms\Controllers\BlockInstanceController::edit/$1/$2', ['as' => 'admin.cms.entries.blocks.edit', 'filter' => 'permission:cms.entries.write']);
    $routes->post('entries/(:num)/blocks/(:num)', '\App\Modules\Cms\Controllers\BlockInstanceController::update/$1/$2', ['as' => 'admin.cms.entries.blocks.update', 'filter' => 'permission:cms.entries.write']);
    $routes->post('entries/(:num)/blocks/(:num)/delete', '\App\Modules\Cms\Controllers\BlockInstanceController::delete/$1/$2', ['as' => 'admin.cms.entries.blocks.delete', 'filter' => 'permission:cms.entries.write']);
    $routes->post('entries/(:num)/blocks/reorder', '\App\Modules\Cms\Controllers\BlockInstanceController::reorder/$1', ['as' => 'admin.cms.entries.blocks.reorder', 'filter' => 'permission:cms.entries.write']);

    // Entry child block management
    $routes->get('entries/(:num)/blocks/(:num)/children', '\App\Modules\Cms\Controllers\BlockInstanceController::children/$1/$2', ['as' => 'admin.cms.entries.blocks.children', 'filter' => 'permission:cms.entries.read']);
    $routes->post('entries/(:num)/blocks/(:num)/children/reorder', '\App\Modules\Cms\Controllers\BlockInstanceController::reorderChildren/$1/$2', ['as' => 'admin.cms.entries.blocks.children.reorder', 'filter' => 'permission:cms.entries.write']);

    // Menu
    $routes->get('menus', '\App\Modules\Cms\Controllers\MenuController::index', ['as' => 'admin.cms.menus', 'filter' => 'permission:cms.menus.read']);
    $routes->get('menus/data', '\App\Modules\Cms\Controllers\MenuController::data', ['as' => 'admin.cms.menus.data', 'filter' => 'permission:cms.menus.read']);
    $routes->get('menus/category-options', '\App\Modules\Cms\Controllers\MenuController::getCategoryUrlOptions', ['as' => 'admin.cms.menus.category_options', 'filter' => 'permission:cms.menus.read']);
    $routes->get('menus/create', '\App\Modules\Cms\Controllers\MenuController::create', ['as' => 'admin.cms.menus.create', 'filter' => 'permission:cms.menus.write']);
    $routes->post('menus', '\App\Modules\Cms\Controllers\MenuController::store', ['as' => 'admin.cms.menus.store', 'filter' => 'permission:cms.menus.write']);
    $routes->get('menus/(:segment)', '\App\Modules\Cms\Controllers\MenuController::show/$1', ['as' => 'admin.cms.menus.show', 'filter' => 'permission:cms.menus.read']);
    $routes->get('menus/(:segment)/edit', '\App\Modules\Cms\Controllers\MenuController::edit/$1', ['as' => 'admin.cms.menus.edit', 'filter' => 'permission:cms.menus.write']);
    $routes->post('menus/(:segment)', '\App\Modules\Cms\Controllers\MenuController::update/$1', ['as' => 'admin.cms.menus.update', 'filter' => 'permission:cms.menus.write']);
    $routes->post('menus/(:segment)/delete', '\App\Modules\Cms\Controllers\MenuController::delete/$1', ['as' => 'admin.cms.menus.delete', 'filter' => 'permission:cms.menus.write']);

    // MenuItem
    $routes->get('menus/(:num)/items/create', '\App\Modules\Cms\Controllers\MenuController::createItem/$1', ['as' => 'admin.cms.menus.items.create', 'filter' => 'permission:cms.menus.write']);
    $routes->post('menus/(:num)/items', '\App\Modules\Cms\Controllers\MenuController::storeItem/$1', ['as' => 'admin.cms.menus.items.store', 'filter' => 'permission:cms.menus.write']);
    $routes->get('menus/(:num)/items/(:num)/edit', '\App\Modules\Cms\Controllers\MenuController::editItem/$1/$2', ['as' => 'admin.cms.menus.items.edit', 'filter' => 'permission:cms.menus.write']);
    $routes->post('menus/(:num)/items/(:num)', '\App\Modules\Cms\Controllers\MenuController::updateItem/$1/$2', ['as' => 'admin.cms.menus.items.update', 'filter' => 'permission:cms.menus.write']);
    $routes->post('menus/(:num)/items/(:num)/delete', '\App\Modules\Cms\Controllers\MenuController::deleteItem/$1/$2', ['as' => 'admin.cms.menus.items.delete', 'filter' => 'permission:cms.menus.write']);
    $routes->get('menus/(:num)/items/reorder', '\App\Modules\Cms\Controllers\MenuController::reorderItems/$1', ['as' => 'admin.cms.menus.items.reorder', 'filter' => 'permission:cms.menus.write']);
    $routes->post('menus/(:num)/items/reorder', '\App\Modules\Cms\Controllers\MenuController::saveItemsOrder/$1', ['as' => 'admin.cms.menus.items.save_order', 'filter' => 'permission:cms.menus.write']);

    // Block Preview (AJAX — must be before block-types to avoid segment collision)
    $routes->post('blocks/preview', '\App\Modules\Cms\Controllers\BlockPreviewController::preview', ['as' => 'admin.cms.blocks.preview', 'filter' => 'permission:cms.pages.read']);
    $routes->get('blocks/entries', '\App\Modules\Cms\Controllers\BlockInstanceController::entryOptions', ['as' => 'admin.cms.blocks.entries', 'filter' => 'permission:cms.entries.read']);

    // BlockType
    $routes->get('block-types', '\App\Modules\Cms\Controllers\BlockTypeController::index', ['as' => 'admin.cms.block_types', 'filter' => 'permission:cms.blocks.read']);
    $routes->get('block-types/data', '\App\Modules\Cms\Controllers\BlockTypeController::data', ['as' => 'admin.cms.block_types.data', 'filter' => 'permission:cms.blocks.read']);
    $routes->post('block-types/refresh-cache', '\App\Modules\Cms\Controllers\BlockTypeController::refreshCache', ['as' => 'admin.cms.block_types.refresh_cache', 'filter' => 'permission:cms.blocks.read']);
    $routes->get('block-types/create', '\App\Modules\Cms\Controllers\BlockTypeController::create', ['as' => 'admin.cms.block_types.create', 'filter' => 'permission:cms.blocks.write']);
    $routes->post('block-types', '\App\Modules\Cms\Controllers\BlockTypeController::store', ['as' => 'admin.cms.block_types.store', 'filter' => 'permission:cms.blocks.write']);
    $routes->get('block-types/(:segment)', '\App\Modules\Cms\Controllers\BlockTypeController::show/$1', ['as' => 'admin.cms.block_types.show', 'filter' => 'permission:cms.blocks.read']);
    $routes->get('block-types/(:segment)/edit', '\App\Modules\Cms\Controllers\BlockTypeController::edit/$1', ['as' => 'admin.cms.block_types.edit', 'filter' => 'permission:cms.blocks.write']);
    $routes->post('block-types/(:segment)', '\App\Modules\Cms\Controllers\BlockTypeController::update/$1', ['as' => 'admin.cms.block_types.update', 'filter' => 'permission:cms.blocks.write']);
    $routes->post('block-types/(:segment)/delete', '\App\Modules\Cms\Controllers\BlockTypeController::delete/$1', ['as' => 'admin.cms.block_types.delete', 'filter' => 'permission:cms.blocks.write']);

    // Collection
    $routes->get('collections', '\App\Modules\Cms\Controllers\CollectionController::index', ['as' => 'admin.cms.collections', 'filter' => 'permission:cms.collections.read']);
    $routes->get('collections/data', '\App\Modules\Cms\Controllers\CollectionController::data', ['as' => 'admin.cms.collections.data', 'filter' => 'permission:cms.collections.read']);
    $routes->get('collections/create', '\App\Modules\Cms\Controllers\CollectionController::create', ['as' => 'admin.cms.collections.create', 'filter' => 'permission:cms.collections.write']);
    $routes->get('collections/check-slug', '\App\Modules\Cms\Controllers\CollectionController::checkSlug', ['as' => 'admin.cms.collections.check_slug', 'filter' => 'permission:cms.collections.read']);
    $routes->post('collections', '\App\Modules\Cms\Controllers\CollectionController::store', ['as' => 'admin.cms.collections.store', 'filter' => 'permission:cms.collections.write']);
    $routes->get('collections/(:segment)', '\App\Modules\Cms\Controllers\CollectionController::show/$1', ['as' => 'admin.cms.collections.show', 'filter' => 'permission:cms.collections.read']);
    $routes->get('collections/(:segment)/edit', '\App\Modules\Cms\Controllers\CollectionController::edit/$1', ['as' => 'admin.cms.collections.edit', 'filter' => 'permission:cms.collections.write']);
    $routes->get('collections/(:segment)/structure', '\App\Modules\Cms\Controllers\CollectionController::structure/$1', ['as' => 'admin.cms.collections.structure', 'filter' => 'permission:cms.collections.write']);
    $routes->post('collections/(:segment)', '\App\Modules\Cms\Controllers\CollectionController::update/$1', ['as' => 'admin.cms.collections.update', 'filter' => 'permission:cms.collections.write']);
    $routes->post('collections/(:segment)/structure', '\App\Modules\Cms\Controllers\CollectionController::updateStructure/$1', ['as' => 'admin.cms.collections.update_structure', 'filter' => 'permission:cms.collections.write']);
    $routes->post('collections/(:segment)/delete', '\App\Modules\Cms\Controllers\CollectionController::delete/$1', ['as' => 'admin.cms.collections.delete', 'filter' => 'permission:cms.collections.write']);

    // Entry
    $routes->get('entries', '\App\Modules\Cms\Controllers\EntryController::index', ['as' => 'admin.cms.entries', 'filter' => 'permission:cms.entries.read']);
    $routes->get('entries/data', '\App\Modules\Cms\Controllers\EntryController::data', ['as' => 'admin.cms.entries.data', 'filter' => 'permission:cms.entries.read']);
    $routes->get('entries/check-slug', '\App\Modules\Cms\Controllers\EntryController::checkSlug', ['as' => 'admin.cms.entries.check_slug', 'filter' => 'permission:cms.entries.read']);
    $routes->get('entries/create', '\App\Modules\Cms\Controllers\EntryController::create', ['as' => 'admin.cms.entries.create', 'filter' => 'permission:cms.entries.write']);
    $routes->get('entries/reorder', '\App\Modules\Cms\Controllers\EntryController::reorder', ['as' => 'admin.cms.entries.reorder', 'filter' => 'permission:cms.entries.write']);
    $routes->post('entries/reorder', '\App\Modules\Cms\Controllers\EntryController::saveOrder', ['as' => 'admin.cms.entries.save_order', 'filter' => 'permission:cms.entries.write']);
    $routes->post('entries', '\App\Modules\Cms\Controllers\EntryController::store', ['as' => 'admin.cms.entries.store', 'filter' => 'permission:cms.entries.write']);
    $routes->get('entries/(:segment)', '\App\Modules\Cms\Controllers\EntryController::show/$1', ['as' => 'admin.cms.entries.show', 'filter' => 'permission:cms.entries.read']);
    $routes->get('entries/(:segment)/edit', '\App\Modules\Cms\Controllers\EntryController::edit/$1', ['as' => 'admin.cms.entries.edit', 'filter' => 'permission:cms.entries.write']);
    $routes->post('entries/(:segment)', '\App\Modules\Cms\Controllers\EntryController::update/$1', ['as' => 'admin.cms.entries.update', 'filter' => 'permission:cms.entries.write']);
    $routes->post('entries/(:segment)/delete', '\App\Modules\Cms\Controllers\EntryController::delete/$1', ['as' => 'admin.cms.entries.delete', 'filter' => 'permission:cms.entries.write']);
    $routes->post('entries/(:segment)/publish', '\App\Modules\Cms\Controllers\EntryController::publish/$1', ['as' => 'admin.cms.entries.publish', 'filter' => 'permission:cms.entries.write']);
    $routes->post('entries/(:segment)/archive', '\App\Modules\Cms\Controllers\EntryController::archive/$1', ['as' => 'admin.cms.entries.archive', 'filter' => 'permission:cms.entries.write']);

    // Category
    $routes->get('categories', '\App\Modules\Cms\Controllers\CategoryController::index', ['as' => 'admin.cms.categories', 'filter' => 'permission:cms.categories.read']);
    $routes->get('categories/data', '\App\Modules\Cms\Controllers\CategoryController::data', ['as' => 'admin.cms.categories.data', 'filter' => 'permission:cms.categories.read']);
    $routes->get('categories/check-slug', '\App\Modules\Cms\Controllers\CategoryController::checkSlug', ['as' => 'admin.cms.categories.check_slug', 'filter' => 'permission:cms.categories.read']);
    $routes->get('categories/create', '\App\Modules\Cms\Controllers\CategoryController::create', ['as' => 'admin.cms.categories.create', 'filter' => 'permission:cms.categories.write']);
    $routes->get('categories/reorder', '\App\Modules\Cms\Controllers\CategoryController::reorder', ['as' => 'admin.cms.categories.reorder', 'filter' => 'permission:cms.categories.write']);
    $routes->post('categories/reorder', '\App\Modules\Cms\Controllers\CategoryController::saveOrder', ['as' => 'admin.cms.categories.save_order', 'filter' => 'permission:cms.categories.write']);
    $routes->post('categories', '\App\Modules\Cms\Controllers\CategoryController::store', ['as' => 'admin.cms.categories.store', 'filter' => 'permission:cms.categories.write']);
    $routes->get('categories/(:segment)', '\App\Modules\Cms\Controllers\CategoryController::show/$1', ['as' => 'admin.cms.categories.show', 'filter' => 'permission:cms.categories.read']);
    $routes->get('categories/(:segment)/edit', '\App\Modules\Cms\Controllers\CategoryController::edit/$1', ['as' => 'admin.cms.categories.edit', 'filter' => 'permission:cms.categories.write']);
    $routes->post('categories/(:segment)', '\App\Modules\Cms\Controllers\CategoryController::update/$1', ['as' => 'admin.cms.categories.update', 'filter' => 'permission:cms.categories.write']);
    $routes->post('categories/(:segment)/delete', '\App\Modules\Cms\Controllers\CategoryController::delete/$1', ['as' => 'admin.cms.categories.delete', 'filter' => 'permission:cms.categories.write']);

    // Tag
    $routes->get('tags', '\App\Modules\Cms\Controllers\TagController::index', ['as' => 'admin.cms.tags', 'filter' => 'permission:cms.tags.read']);
    $routes->get('tags/data', '\App\Modules\Cms\Controllers\TagController::data', ['as' => 'admin.cms.tags.data', 'filter' => 'permission:cms.tags.read']);
    $routes->get('tags/create', '\App\Modules\Cms\Controllers\TagController::create', ['as' => 'admin.cms.tags.create', 'filter' => 'permission:cms.tags.write']);
    $routes->post('tags', '\App\Modules\Cms\Controllers\TagController::store', ['as' => 'admin.cms.tags.store', 'filter' => 'permission:cms.tags.write']);
    $routes->get('tags/(:segment)', '\App\Modules\Cms\Controllers\TagController::show/$1', ['as' => 'admin.cms.tags.show', 'filter' => 'permission:cms.tags.read']);
    $routes->get('tags/(:segment)/edit', '\App\Modules\Cms\Controllers\TagController::edit/$1', ['as' => 'admin.cms.tags.edit', 'filter' => 'permission:cms.tags.write']);
    $routes->post('tags/(:segment)', '\App\Modules\Cms\Controllers\TagController::update/$1', ['as' => 'admin.cms.tags.update', 'filter' => 'permission:cms.tags.write']);
    $routes->post('tags/(:segment)/delete', '\App\Modules\Cms\Controllers\TagController::delete/$1', ['as' => 'admin.cms.tags.delete', 'filter' => 'permission:cms.tags.write']);

    // Form Submissions
    $routes->get('form-submissions', '\App\Modules\Cms\Controllers\FormSubmissionController::index', ['as' => 'admin.cms.form_submissions', 'filter' => 'permission:cms.submissions.read']);
    $routes->get('form-submissions/data', '\App\Modules\Cms\Controllers\FormSubmissionController::data', ['as' => 'admin.cms.form_submissions.data', 'filter' => 'permission:cms.submissions.read']);
    $routes->get('form-submissions/(:num)', '\App\Modules\Cms\Controllers\FormSubmissionController::show/$1', ['as' => 'admin.cms.form_submissions.show', 'filter' => 'permission:cms.submissions.read']);
    $routes->post('form-submissions/(:num)/status', '\App\Modules\Cms\Controllers\FormSubmissionController::updateStatus/$1', ['as' => 'admin.cms.form_submissions.update_status', 'filter' => 'permission:cms.submissions.write']);

    // Redirect
    $routes->get('redirects', '\App\Modules\Cms\Controllers\RedirectController::index', ['as' => 'admin.cms.redirects', 'filter' => 'permission:cms.redirects.read']);
    $routes->get('redirects/data', '\App\Modules\Cms\Controllers\RedirectController::data', ['as' => 'admin.cms.redirects.data', 'filter' => 'permission:cms.redirects.read']);
    $routes->get('redirects/create', '\App\Modules\Cms\Controllers\RedirectController::create', ['as' => 'admin.cms.redirects.create', 'filter' => 'permission:cms.redirects.write']);
    $routes->post('redirects', '\App\Modules\Cms\Controllers\RedirectController::store', ['as' => 'admin.cms.redirects.store', 'filter' => 'permission:cms.redirects.write']);
    $routes->get('redirects/(:segment)', '\App\Modules\Cms\Controllers\RedirectController::show/$1', ['as' => 'admin.cms.redirects.show', 'filter' => 'permission:cms.redirects.read']);
    $routes->get('redirects/(:segment)/edit', '\App\Modules\Cms\Controllers\RedirectController::edit/$1', ['as' => 'admin.cms.redirects.edit', 'filter' => 'permission:cms.redirects.write']);
    $routes->post('redirects/(:segment)', '\App\Modules\Cms\Controllers\RedirectController::update/$1', ['as' => 'admin.cms.redirects.update', 'filter' => 'permission:cms.redirects.write']);
    $routes->post('redirects/(:segment)/delete', '\App\Modules\Cms\Controllers\RedirectController::delete/$1', ['as' => 'admin.cms.redirects.delete', 'filter' => 'permission:cms.redirects.write']);
    $routes->get('redirects/export', '\App\Modules\Cms\Controllers\RedirectController::exportCsv', ['as' => 'admin.cms.redirects.export_csv', 'filter' => 'permission:cms.redirects.read']);
    $routes->post('redirects/import', '\App\Modules\Cms\Controllers\RedirectController::importCsv', ['as' => 'admin.cms.redirects.import_csv', 'filter' => 'permission:cms.redirects.write']);

    // Forms (dynamic form builder)
    $routes->get('forms', '\App\Modules\Cms\Controllers\FormController::index', ['as' => 'admin.cms.forms', 'filter' => 'permission:cms.forms.read']);
    $routes->get('forms/data', '\App\Modules\Cms\Controllers\FormController::data', ['as' => 'admin.cms.forms.data', 'filter' => 'permission:cms.forms.read']);
    $routes->get('forms/create', '\App\Modules\Cms\Controllers\FormController::create', ['as' => 'admin.cms.forms.create', 'filter' => 'permission:cms.forms.write']);
    $routes->post('forms', '\App\Modules\Cms\Controllers\FormController::store', ['as' => 'admin.cms.forms.store', 'filter' => 'permission:cms.forms.write']);
    $routes->get('forms/(:num)', '\App\Modules\Cms\Controllers\FormController::show/$1', ['as' => 'admin.cms.forms.show', 'filter' => 'permission:cms.forms.read']);
    $routes->get('forms/(:num)/edit', '\App\Modules\Cms\Controllers\FormController::edit/$1', ['as' => 'admin.cms.forms.edit', 'filter' => 'permission:cms.forms.write']);
    $routes->post('forms/(:num)', '\App\Modules\Cms\Controllers\FormController::update/$1', ['as' => 'admin.cms.forms.update', 'filter' => 'permission:cms.forms.write']);
    $routes->post('forms/(:num)/delete', '\App\Modules\Cms\Controllers\FormController::delete/$1', ['as' => 'admin.cms.forms.delete', 'filter' => 'permission:cms.forms.admin']);
    // Field AJAX endpoints
    $routes->post('forms/(:num)/fields', '\App\Modules\Cms\Controllers\FormController::storeField/$1', ['as' => 'admin.cms.forms.fields.store', 'filter' => 'permission:cms.forms.write']);
    $routes->post('forms/(:num)/fields/(:num)/update', '\App\Modules\Cms\Controllers\FormController::updateField/$1/$2', ['as' => 'admin.cms.forms.fields.update', 'filter' => 'permission:cms.forms.write']);
    $routes->post('forms/(:num)/fields/(:num)/delete', '\App\Modules\Cms\Controllers\FormController::deleteField/$1/$2', ['as' => 'admin.cms.forms.fields.delete', 'filter' => 'permission:cms.forms.write']);
    $routes->post('forms/(:num)/fields/reorder', '\App\Modules\Cms\Controllers\FormController::reorderFields/$1', ['as' => 'admin.cms.forms.fields.reorder', 'filter' => 'permission:cms.forms.write']);
});
