<?php
$navItemClass = 'flex items-center gap-2 rounded-lg px-3 py-2 text-sm font-medium transition-colors';
$navItemIdleClass = 'text-gray-300 hover:bg-gray-800 hover:text-white';
$navItemActiveClass = 'bg-brand-50 text-brand-700 shadow-sm';
$navSectionLabelClass = 'px-3 pt-4 pb-2 text-[11px] font-semibold uppercase tracking-[0.18em] text-gray-500';
$navGroupButtonClass = 'flex w-full items-center justify-between rounded-lg px-3 py-2 text-left text-sm font-medium text-gray-300 transition-colors hover:bg-gray-800 hover:text-white';
$navGroupBodyClass = 'mt-1 space-y-0.5 border-l border-gray-800/70 pl-3';
$navSubItemClass = 'flex items-center gap-2 rounded-lg px-3 py-2 text-sm transition-colors';
$navSubItemIdleClass = 'text-gray-300 hover:bg-gray-800 hover:text-white';
$navSubItemActiveClass = 'bg-brand-50 text-brand-700 shadow-sm';
?>
<aside id="app-sidebar" class="bg-gray-900 text-gray-200 w-72 fixed inset-y-0 left-0 z-40 transform transition-transform duration-200 md:translate-x-0 flex flex-col"
    :class="{ '-translate-x-full': !sidebarOpen, 'translate-x-0': sidebarOpen }">
    <div class="h-16 px-4 border-b border-gray-800 flex items-center justify-between flex-shrink-0">
        <span class="text-sm uppercase tracking-widest text-gray-400"><?= lang('App.menu') ?></span>
        <button class="md:hidden text-gray-400 hover:text-white" @click="sidebarOpen = false" aria-label="<?= esc(lang('App.close_navigation')) ?>">
            <span aria-hidden="true">x</span>
        </button>
    </div>

    <nav class="p-3 space-y-1 flex-1 overflow-y-auto overscroll-contain">
        <a href="<?= route_to('dashboard') ?>" class="<?= $navItemClass ?> <?= active_nav('dashboard', $navItemActiveClass) ?> <?= url_is('dashboard') ? 'bg-brand-50 text-brand-700 shadow-sm' : $navItemIdleClass ?>">
            <?= ui_icon('dashboard') ?>
            <span><?= lang('App.dashboard') ?></span>
        </a>
        <a href="<?= route_to('profile') ?>" class="<?= $navItemClass ?> <?= active_nav('profile', $navItemActiveClass) ?> <?= url_is('profile') ? 'bg-brand-50 text-brand-700 shadow-sm' : $navItemIdleClass ?>">
            <?= ui_icon('profile') ?>
            <span><?= lang('App.profile') ?></span>
        </a>
        <a href="<?= route_to('files') ?>" class="<?= $navItemClass ?> <?= active_nav('files', $navItemActiveClass) ?> <?= url_is('files') ? 'bg-brand-50 text-brand-700 shadow-sm' : $navItemIdleClass ?>">
            <?= ui_icon('files') ?>
            <span><?= lang('App.files') ?></span>
        </a>

        <?php
            $hasAdminItem = has_permission('users.read') || has_permission('audit.read') || has_permission('apikeys.read') || has_permission('metrics.read') || has_permission('cms.analytics.read');
?>

        <?php if ($hasAdminItem): ?>
            <div class="<?= $navSectionLabelClass ?> mt-2 border-t border-gray-800 pt-4"><?= lang('App.administration') ?></div>
            <?php if (has_permission('users.read')): ?>
                <a href="<?= route_to('admin.users') ?>" class="<?= $navItemClass ?> <?= active_nav('admin/users*', $navItemActiveClass) ?> <?= url_is('admin/users*') ? 'bg-brand-50 text-brand-700 shadow-sm' : $navItemIdleClass ?>">
                    <?= ui_icon('users') ?>
                    <span><?= lang('App.users') ?></span>
                </a>
            <?php endif; ?>
            <?php if (has_permission('audit.read')): ?>
                <a href="<?= route_to('admin.audit') ?>" class="<?= $navItemClass ?> <?= active_nav('admin/audit*', $navItemActiveClass) ?> <?= url_is('admin/audit*') ? 'bg-brand-50 text-brand-700 shadow-sm' : $navItemIdleClass ?>">
                    <?= ui_icon('audit') ?>
                    <span><?= lang('Audit.title') ?></span>
                </a>
            <?php endif; ?>
            <?php if (has_permission('apikeys.read')): ?>
                <a href="<?= route_to('admin.api_keys') ?>" class="<?= $navItemClass ?> <?= active_nav('admin/api-keys*', $navItemActiveClass) ?> <?= url_is('admin/api-keys*') ? 'bg-brand-50 text-brand-700 shadow-sm' : $navItemIdleClass ?>">
                    <?= ui_icon('api_keys') ?>
                    <span><?= lang('App.api_keys') ?></span>
                </a>
            <?php endif; ?>
            <?php if (has_permission('metrics.read')): ?>
                <a href="<?= route_to('admin.metrics') ?>" class="<?= $navItemClass ?> <?= active_nav('admin/metrics', $navItemActiveClass) ?> <?= url_is('admin/metrics') ? 'bg-brand-50 text-brand-700 shadow-sm' : $navItemIdleClass ?>">
                    <?= ui_icon('metrics') ?>
                    <span><?= lang('App.metrics') ?></span>
                </a>
            <?php endif; ?>
            <?php if (has_permission('cms.analytics.read')): ?>
                <a href="<?= route_to('admin.analytics') ?>" class="<?= $navItemClass ?> <?= active_nav('admin/analytics*', $navItemActiveClass) ?> <?= url_is('admin/analytics*') ? 'bg-brand-50 text-brand-700 shadow-sm' : $navItemIdleClass ?>">
                    <?= ui_icon('bar-chart-2') ?>
                    <span><?= lang('Analytics.nav_label') ?></span>
                </a>
            <?php endif; ?>
        <?php endif; ?>

        <?php if (has_permission('iam.superadmin-access')): ?>
            <div class="<?= $navSectionLabelClass ?> mt-2 border-t border-gray-800 pt-4"><?= lang('App.identity_access') ?></div>
            <a href="<?= route_to('admin.iam.roles') ?>" class="<?= $navItemClass ?> <?= active_nav('admin/iam/roles*', $navItemActiveClass) ?> <?= url_is('admin/iam/roles*') ? 'bg-brand-50 text-brand-700 shadow-sm' : $navItemIdleClass ?>">
                <?= ui_icon('shield') ?>
                <span><?= lang('App.roles') ?></span>
            </a>
            <a href="<?= route_to('admin.iam.role_permissions') ?>" class="<?= $navItemClass ?> <?= active_nav('admin/iam/role-permissions*', $navItemActiveClass) ?> <?= url_is('admin/iam/role-permissions*') ? 'bg-brand-50 text-brand-700 shadow-sm' : $navItemIdleClass ?>">
                <?= ui_icon('shield') ?>
                <span><?= lang('Iam.role_permissions_title') ?></span>
            </a>
            <a href="<?= route_to('admin.iam.permissions') ?>" class="<?= $navItemClass ?> <?= active_nav('admin/iam/permissions*', $navItemActiveClass) ?> <?= url_is('admin/iam/permissions*') ? 'bg-brand-50 text-brand-700 shadow-sm' : $navItemIdleClass ?>">
                <?= ui_icon('lock') ?>
                <span><?= lang('App.permissions') ?></span>
            </a>
            <a href="<?= route_to('admin.iam.applications') ?>" class="<?= $navItemClass ?> <?= active_nav('admin/iam/applications*', $navItemActiveClass) ?> <?= url_is('admin/iam/applications*') ? 'bg-brand-50 text-brand-700 shadow-sm' : $navItemIdleClass ?>">
                <?= ui_icon('layers') ?>
                <span><?= lang('Iam.applications_title') ?></span>
            </a>
        <?php endif; ?>
        <?php
    $hasCmsItem = has_permission('cms.languages.read')
        || has_permission('cms.settings.read')
        || has_permission('cms.pages.read')
        || has_permission('cms.menus.read')
        || has_permission('cms.blocks.read')
        || has_permission('cms.collections.read')
        || has_permission('cms.entries.read')
        || has_permission('cms.categories.read')
        || has_permission('cms.tags.read')
        || has_permission('cms.redirects.read')
        || has_permission('cms.submissions.read')
        || has_permission('cms.forms.read');
?>
        <?php if ($hasCmsItem): ?>
            <div class="<?= $navSectionLabelClass ?> mt-2 border-t border-gray-800 pt-4">CMS</div>

            <?php if (has_permission('cms.entries.read')): ?>
                <?php
                $contentWizardActive = url_is('admin/cms/wizard')
                    || url_is('admin/cms/wizard/config*')
                    || url_is('admin/cms/wizard/pages*')
                    || url_is('admin/cms/wizard/entries*')
                    || url_is('admin/cms/wizard/menus*');
                ?>
                <a href="<?= site_url('admin/cms/wizard') ?>" class="<?= $navItemClass ?> <?= $contentWizardActive ? 'bg-brand-50 text-brand-700 shadow-sm' : $navItemIdleClass ?>">
                    <span aria-hidden="true">✨</span>
                    <span><?= lang('Wizard.sidebar_label') ?></span>
                </a>
            <?php endif; ?>

            <?php if (has_permission('cms.pages.write') || has_permission('cms.menus.write') || has_permission('cms.collections.write')): ?>
                <a href="<?= site_url('admin/cms/wizard/structure') ?>" class="<?= $navItemClass ?> <?= url_is('admin/cms/wizard/structure*') ? 'bg-brand-50 text-brand-700 shadow-sm' : $navItemIdleClass ?>">
                    <span aria-hidden="true">🏗️</span>
                    <span><?= lang('Wizard.structure_sidebar_label') ?></span>
                </a>
            <?php endif; ?>

            <?php
            // ── CMS group: Contenido ───────────────────────────────────────────
            // Visible to editors (non-technical): entries, collections, taxonomy, forms
            $hasContentGroup = has_permission('cms.entries.read')
                || has_permission('cms.collections.read')
                || has_permission('cms.categories.read')
                || has_permission('cms.tags.read')
                || has_permission('cms.submissions.read')
                || has_permission('cms.forms.read');
            $contentActive = url_is('admin/cms/entries*')
                || url_is('admin/cms/collections*')
                || url_is('admin/cms/categories*')
                || url_is('admin/cms/tags*')
                || url_is('admin/cms/form-submissions*')
                || url_is('admin/cms/forms*');
            ?>
            <?php if ($hasContentGroup): ?>
            <div x-data="{ open: <?= $contentActive ? 'true' : "localStorage.getItem('cms-g-content') !== 'false'" ?> }" class="space-y-1">
                <button type="button"
                        @click="open = !open; localStorage.setItem('cms-g-content', open)"
                        class="<?= $navGroupButtonClass ?>"
                        :aria-expanded="open">
                    <span class="text-xs font-medium uppercase tracking-wide text-gray-500"><?= lang('App.cms_content') ?></span>
                    <span class="inline-flex items-center justify-center transition-transform duration-200" :class="{ 'rotate-180': open }">
                        <?= ui_icon('chevron-down', 'h-3 w-3 text-gray-500') ?>
                    </span>
                </button>
                <div x-show="open" x-cloak class="<?= $navGroupBodyClass ?>">
                    <?php if (has_permission('cms.entries.read')): ?>
                        <a href="<?= site_url('admin/cms/entries') ?>" class="<?= $navSubItemClass ?> <?= active_nav('admin/cms/entries*', $navSubItemActiveClass) ?> <?= url_is('admin/cms/entries*') ? 'bg-brand-50 text-brand-700 shadow-sm' : $navSubItemIdleClass ?>">
                            <?= ui_icon('cms-entry') ?>
                            <span><?= lang('Entries.entries_title') ?></span>
                        </a>
                    <?php endif; ?>
                    <?php if (has_permission('cms.collections.read')): ?>
                        <a href="<?= site_url('admin/cms/collections') ?>" class="<?= $navSubItemClass ?> <?= active_nav('admin/cms/collections*', $navSubItemActiveClass) ?> <?= url_is('admin/cms/collections*') ? 'bg-brand-50 text-brand-700 shadow-sm' : $navSubItemIdleClass ?>">
                            <?= ui_icon('cms-collection') ?>
                            <span><?= lang('Collections.collections_title') ?></span>
                        </a>
                    <?php endif; ?>
                    <?php if (has_permission('cms.categories.read')): ?>
                        <a href="<?= site_url('admin/cms/categories') ?>" class="<?= $navSubItemClass ?> <?= active_nav('admin/cms/categories*', $navSubItemActiveClass) ?> <?= url_is('admin/cms/categories*') ? 'bg-brand-50 text-brand-700 shadow-sm' : $navSubItemIdleClass ?>">
                            <?= ui_icon('folder-open') ?>
                            <span><?= lang('Categories.categories_title') ?></span>
                        </a>
                    <?php endif; ?>
                    <?php if (has_permission('cms.tags.read')): ?>
                        <a href="<?= site_url('admin/cms/tags') ?>" class="<?= $navSubItemClass ?> <?= active_nav('admin/cms/tags*', $navSubItemActiveClass) ?> <?= url_is('admin/cms/tags*') ? 'bg-brand-50 text-brand-700 shadow-sm' : $navSubItemIdleClass ?>">
                            <?= ui_icon('tag') ?>
                            <span><?= lang('Tags.tags_title') ?></span>
                        </a>
                    <?php endif; ?>
                    <?php if (has_permission('cms.forms.read')): ?>
                        <a href="<?= site_url('admin/cms/forms') ?>" class="<?= $navSubItemClass ?> <?= active_nav('admin/cms/forms*', $navSubItemActiveClass) ?> <?= url_is('admin/cms/forms*') ? 'bg-brand-50 text-brand-700 shadow-sm' : $navSubItemIdleClass ?>">
                            <?= ui_icon('clipboard-list') ?>
                            <span><?= lang('Forms.title') ?></span>
                        </a>
                    <?php endif; ?>
                    <?php if (has_permission('cms.submissions.read')): ?>
                        <a href="<?= site_url('admin/cms/form-submissions') ?>" class="<?= $navSubItemClass ?> <?= active_nav('admin/cms/form-submissions*', $navSubItemActiveClass) ?> <?= url_is('admin/cms/form-submissions*') ? 'bg-brand-50 text-brand-700 shadow-sm' : $navSubItemIdleClass ?>">
                            <?= ui_icon('mail') ?>
                            <span><?= lang('FormSubmissions.sidebar_label') ?></span>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>

            <?php
            // ── CMS group: Estructura ─────────────────────────────────────────
            // Visible to technical users: pages, menus, block types, redirects
            $hasStructureGroup = has_permission('cms.pages.read')
                || has_permission('cms.menus.read')
                || has_permission('cms.blocks.read')
                || has_permission('cms.redirects.read');
            $structureActive = url_is('admin/cms/pages*')
                || url_is('admin/cms/menus*')
                || url_is('admin/cms/block-types*')
                || url_is('admin/cms/redirects*');
            ?>
            <?php if ($hasStructureGroup): ?>
            <div x-data="{ open: <?= $structureActive ? 'true' : "localStorage.getItem('cms-g-structure') !== 'false'" ?> }" class="space-y-1">
                <button type="button"
                        @click="open = !open; localStorage.setItem('cms-g-structure', open)"
                        class="<?= $navGroupButtonClass ?>"
                        :aria-expanded="open">
                    <span class="text-xs font-medium uppercase tracking-wide text-gray-500"><?= lang('App.cms_structure') ?></span>
                    <span class="inline-flex items-center justify-center transition-transform duration-200" :class="{ 'rotate-180': open }">
                        <?= ui_icon('chevron-down', 'h-3 w-3 text-gray-500') ?>
                    </span>
                </button>
                <div x-show="open" x-cloak class="<?= $navGroupBodyClass ?>">
                    <?php if (has_permission('cms.pages.read')): ?>
                        <a href="<?= site_url('admin/cms/pages') ?>" class="<?= $navSubItemClass ?> <?= active_nav('admin/cms/pages*', $navSubItemActiveClass) ?> <?= url_is('admin/cms/pages*') ? 'bg-brand-50 text-brand-700 shadow-sm' : $navSubItemIdleClass ?>">
                            <?= ui_icon('cms-page') ?>
                            <span><?= lang('Pages.pages_title') ?></span>
                        </a>
                    <?php endif; ?>
                    <?php if (has_permission('cms.menus.read')): ?>
                        <a href="<?= site_url('admin/cms/menus') ?>" class="<?= $navSubItemClass ?> <?= active_nav('admin/cms/menus*', $navSubItemActiveClass) ?> <?= url_is('admin/cms/menus*') ? 'bg-brand-50 text-brand-700 shadow-sm' : $navSubItemIdleClass ?>">
                            <?= ui_icon('cms-menu') ?>
                            <span><?= lang('Menus.menus_title') ?></span>
                        </a>
                    <?php endif; ?>
                    <?php if (has_permission('cms.blocks.read')): ?>
                        <a href="<?= site_url('admin/cms/block-types') ?>" class="<?= $navSubItemClass ?> <?= active_nav('admin/cms/block-types*', $navSubItemActiveClass) ?> <?= url_is('admin/cms/block-types*') ? 'bg-brand-50 text-brand-700 shadow-sm' : $navSubItemIdleClass ?>">
                            <?= ui_icon('cms-block-type') ?>
                            <span><?= lang('BlockTypes.block_types_title') ?></span>
                        </a>
                    <?php endif; ?>
                    <?php if (has_permission('cms.redirects.read')): ?>
                        <a href="<?= site_url('admin/cms/redirects') ?>" class="<?= $navSubItemClass ?> <?= active_nav('admin/cms/redirects*', $navSubItemActiveClass) ?> <?= url_is('admin/cms/redirects*') ? 'bg-brand-50 text-brand-700 shadow-sm' : $navSubItemIdleClass ?>">
                            <?= ui_icon('cms-redirect') ?>
                            <span><?= lang('Redirects.redirects_title') ?></span>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>

            <?php
            // ── CMS group: Configuración ──────────────────────────────────────
            $hasConfigGroup = has_permission('cms.languages.read')
                || has_permission('cms.settings.write')
                || has_permission('cms.settings.read');
            $configActive = url_is('admin/cms/languages*')
                || url_is('admin/cms/translations/audit*')
                || url_is('admin/cms/site-identity*')
                || url_is('admin/cms/settings*');
            ?>
            <?php if ($hasConfigGroup): ?>
            <div x-data="{ open: <?= $configActive ? 'true' : "localStorage.getItem('cms-g-config') !== 'false'" ?> }" class="space-y-1">
                <button type="button"
                        @click="open = !open; localStorage.setItem('cms-g-config', open)"
                        class="<?= $navGroupButtonClass ?>"
                        :aria-expanded="open">
                    <span class="text-xs font-medium uppercase tracking-wide text-gray-500"><?= lang('App.cms_configuration') ?></span>
                    <span class="inline-flex items-center justify-center transition-transform duration-200" :class="{ 'rotate-180': open }">
                        <?= ui_icon('chevron-down', 'h-3 w-3 text-gray-500') ?>
                    </span>
                </button>
                <div x-show="open" x-cloak class="<?= $navGroupBodyClass ?>">
                    <?php if (has_permission('cms.languages.read')): ?>
                        <a href="<?= site_url('admin/cms/languages') ?>" class="<?= $navSubItemClass ?> <?= active_nav('admin/cms/languages*', $navSubItemActiveClass) ?> <?= url_is('admin/cms/languages*') ? 'bg-brand-50 text-brand-700 shadow-sm' : $navSubItemIdleClass ?>">
                            <?= ui_icon('cms-language') ?>
                            <span><?= lang('CmsLanguages.languages_title') ?></span>
                        </a>
                        <a href="<?= site_url('admin/cms/translations/audit') ?>" class="<?= $navSubItemClass ?> <?= active_nav('admin/cms/translations/audit*', $navSubItemActiveClass) ?> <?= url_is('admin/cms/translations/audit*') ? 'bg-brand-50 text-brand-700 shadow-sm' : $navSubItemIdleClass ?>">
                            <?= ui_icon('languages') ?>
                            <span><?= lang('Translations.audit_title') ?></span>
                        </a>
                    <?php endif; ?>
                    <?php if (has_permission('cms.settings.write')): ?>
                        <a href="<?= site_url('admin/cms/site-identity') ?>" class="<?= $navSubItemClass ?> <?= active_nav('admin/cms/site-identity*', $navSubItemActiveClass) ?> <?= url_is('admin/cms/site-identity*') ? 'bg-brand-50 text-brand-700 shadow-sm' : $navSubItemIdleClass ?>">
                            <?= ui_icon('image') ?>
                            <span><?= lang('SiteIdentity.sidebar_label') ?></span>
                        </a>
                    <?php endif; ?>
                    <?php if (has_permission('cms.settings.read')): ?>
                        <a href="<?= site_url('admin/cms/settings') ?>" class="<?= $navSubItemClass ?> <?= active_nav('admin/cms/settings*', $navSubItemActiveClass) ?> <?= url_is('admin/cms/settings*') ? 'bg-brand-50 text-brand-700 shadow-sm' : $navSubItemIdleClass ?>">
                            <?= ui_icon('settings') ?>
                            <span><?= lang('Settings.settings_title') ?></span>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>
        <?php endif; ?>
        <!-- START Events -->
        <?php if (has_permission('event.events.read')): ?>
            <div class="pt-3 mt-3 border-t border-gray-800 text-xs uppercase text-gray-500"><?= lang('Events.sidebar_label') ?></div>
            <?php $sidebarActive_events_scheduling = url_is('admin/events/events*') || url_is('admin/venues/venues*') || url_is('admin/occurrences/occurrences*') || url_is('admin/eventreferences/event-references*'); ?>
            <div x-data="{ open: <?= $sidebarActive_events_scheduling ? 'true' : "localStorage.getItem('events-g-scheduling') !== 'false'" ?> }" class="space-y-1">
                <button type="button"
                        @click="open = !open; localStorage.setItem('events-g-scheduling', open)"
                        class="<?= $navGroupButtonClass ?>"
                        :aria-expanded="open">
                    <span class="text-xs font-medium uppercase tracking-wide text-gray-500"><?= lang('Events.sidebar_group_scheduling') ?></span>
                    <span class="inline-flex items-center justify-center transition-transform duration-200" :class="{ 'rotate-180': open }">
                        <?= ui_icon('chevron-down', 'h-3 w-3 text-gray-500') ?>
                    </span>
                </button>
                <div x-show="open" x-cloak class="<?= $navGroupBodyClass ?>">
                    <a href="<?= route_to('admin.events.events') ?>" class="<?= $navSubItemClass ?> <?= active_nav('admin/events/events*', $navSubItemActiveClass) ?> <?= url_is('admin/events/events*') ? 'bg-brand-50 text-brand-700 shadow-sm' : $navSubItemIdleClass ?>">
                        <?= ui_icon('calendar') ?>
                        <span><?= lang('Events.events_title') ?></span>
                    </a>
                    <a href="<?= route_to('admin.venues.venues') ?>" class="<?= $navSubItemClass ?> <?= active_nav('admin/venues/venues*', $navSubItemActiveClass) ?> <?= url_is('admin/venues/venues*') ? 'bg-brand-50 text-brand-700 shadow-sm' : $navSubItemIdleClass ?>">
                        <?= ui_icon('calendar') ?>
                        <span><?= lang('Venues.venues_title') ?></span>
                    </a>
                    <a href="<?= route_to('admin.occurrences.occurrences') ?>" class="<?= $navSubItemClass ?> <?= active_nav('admin/occurrences/occurrences*', $navSubItemActiveClass) ?> <?= url_is('admin/occurrences/occurrences*') ? 'bg-brand-50 text-brand-700 shadow-sm' : $navSubItemIdleClass ?>">
                        <?= ui_icon('calendar') ?>
                        <span><?= lang('Occurrences.occurrences_title') ?></span>
                    </a>
                    <a href="<?= route_to('admin.eventreferences.event_references') ?>" class="<?= $navSubItemClass ?> <?= active_nav('admin/eventreferences/event-references*', $navSubItemActiveClass) ?> <?= url_is('admin/eventreferences/event-references*') ? 'bg-brand-50 text-brand-700 shadow-sm' : $navSubItemIdleClass ?>">
                        <?= ui_icon('calendar') ?>
                        <span><?= lang('EventReferences.event_references_title') ?></span>
                    </a>
                </div>
            </div>
            <?php $sidebarActive_events_ticketing = url_is('admin/tickettypes/ticket-types*') || url_is('admin/bookings/bookings*') || url_is('admin/tickets/tickets*'); ?>
            <div x-data="{ open: <?= $sidebarActive_events_ticketing ? 'true' : "localStorage.getItem('events-g-ticketing') !== 'false'" ?> }" class="space-y-1">
                <button type="button"
                        @click="open = !open; localStorage.setItem('events-g-ticketing', open)"
                        class="<?= $navGroupButtonClass ?>"
                        :aria-expanded="open">
                    <span class="text-xs font-medium uppercase tracking-wide text-gray-500"><?= lang('Events.sidebar_group_ticketing') ?></span>
                    <span class="inline-flex items-center justify-center transition-transform duration-200" :class="{ 'rotate-180': open }">
                        <?= ui_icon('chevron-down', 'h-3 w-3 text-gray-500') ?>
                    </span>
                </button>
                <div x-show="open" x-cloak class="<?= $navGroupBodyClass ?>">
                    <a href="<?= route_to('admin.tickettypes.ticket_types') ?>" class="<?= $navSubItemClass ?> <?= active_nav('admin/tickettypes/ticket-types*', $navSubItemActiveClass) ?> <?= url_is('admin/tickettypes/ticket-types*') ? 'bg-brand-50 text-brand-700 shadow-sm' : $navSubItemIdleClass ?>">
                        <?= ui_icon('calendar') ?>
                        <span><?= lang('TicketTypes.ticket_types_title') ?></span>
                    </a>
                    <a href="<?= route_to('admin.bookings.bookings') ?>" class="<?= $navSubItemClass ?> <?= active_nav('admin/bookings/bookings*', $navSubItemActiveClass) ?> <?= url_is('admin/bookings/bookings*') ? 'bg-brand-50 text-brand-700 shadow-sm' : $navSubItemIdleClass ?>">
                        <?= ui_icon('calendar') ?>
                        <span><?= lang('Bookings.bookings_title') ?></span>
                    </a>
                    <a href="<?= route_to('admin.tickets.tickets') ?>" class="<?= $navSubItemClass ?> <?= active_nav('admin/tickets/tickets*', $navSubItemActiveClass) ?> <?= url_is('admin/tickets/tickets*') ? 'bg-brand-50 text-brand-700 shadow-sm' : $navSubItemIdleClass ?>">
                        <?= ui_icon('calendar') ?>
                        <span><?= lang('Tickets.tickets_title') ?></span>
                    </a>
                </div>
            </div>
        <?php endif; ?>
        <!-- END Events -->
        <!-- START Museum Catalog -->
        <?php
            // Permission codes match the catalog-domain route filters exactly
            // (teatromuseo-catalog-domain/app/Config/Routes/v1/catalog.php):
            // GET collection-items/categories/techniques each require their own
            // catalog.{resource}.read permission — there is no single "museum.read".
            $hasMuseumItem = has_permission('catalog.collectionItem.read')
                || has_permission('catalog.category.read')
                || has_permission('catalog.technique.read');
?>
        <?php if ($hasMuseumItem): ?>
            <div class="pt-3 mt-3 border-t border-gray-800 text-xs uppercase text-gray-500">Museo (Catálogo)</div>
            <?php if (has_permission('catalog.collectionItem.read')): ?>
                <a href="<?= route_to('admin.museum.collection_items') ?>" class="flex items-center gap-2 rounded-lg px-3 py-2 text-sm hover:bg-brand-50 hover:text-brand-700 <?= active_nav('admin/museum/collection-items*') ?>">
                    <?= ui_icon('cms-collection') ?>
                    <span>Fichas de Colección</span>
                </a>
            <?php endif; ?>
            <?php if (has_permission('catalog.category.read')): ?>
                <a href="<?= route_to('admin.museum.categories') ?>" class="flex items-center gap-2 rounded-lg px-3 py-2 text-sm hover:bg-brand-50 hover:text-brand-700 <?= active_nav('admin/museum/categories*') ?>">
                    <?= ui_icon('tag') ?>
                    <span>Categorías</span>
                </a>
            <?php endif; ?>
            <?php if (has_permission('catalog.technique.read')): ?>
                <a href="<?= route_to('admin.museum.techniques') ?>" class="flex items-center gap-2 rounded-lg px-3 py-2 text-sm hover:bg-brand-50 hover:text-brand-700 <?= active_nav('admin/museum/techniques*') ?>">
                    <?= ui_icon('settings') ?>
                    <span>Técnicas</span>
                </a>
            <?php endif; ?>
        <?php endif; ?>
        <!-- END Museum Catalog -->
        <!-- [DYNAMIC_MODULES_ANCHOR] -->
    </nav>
</aside>

<div class="fixed inset-0 bg-black/30 z-30 md:hidden" x-show="sidebarOpen" x-cloak @click="sidebarOpen = false"></div>
<div class="hidden md:block w-72 shrink-0"></div>
