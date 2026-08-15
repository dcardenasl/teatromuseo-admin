# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Added

- **Accessible password visibility toggle** — login, register, and reset-password forms now use a
  shared `components/form/password` field with a labeled show/hide button (`passwordToggle` Alpine
  component), keeping visibility state independent per field.
- **Shared server-side field errors** — added a reusable form component for rendering API validation
  messages next to their fields, including nested translation rows and accessible invalid-state
  attributes.
- **Configurable CMS listing projections** — collection blocks can now expose a generated field
  catalog and configure card title, summary, image, date, extra metadata, ordering, and public
  filters for CMS and external event/catalog sources.
- **Public-site cache maintenance** — added a permission-gated maintenance screen to inspect
  invalidation status and trigger a manual cleanup across public cache scopes.
- **Translation audit workbench pagination** — the audit screen now consumes paginated reports,
  separates actionable translations from review warnings, and exposes `outdated` details.
- **Editorial team-member editing** — child-block screens now derive labels and previews from
  the configured child type, including team-member names and photos.

- **CMS translation audits and navigation metadata** — the admin now detects untranslated CMS
  content and preserves semantic block navigation definitions while editing blocks.
- **Event type management** — added administrative CRUD support for event types.
- **File picker manifest consumption** — CMS and file-related screens consume the cached file
  manifest to reduce repeated metadata requests.
- **Semantic slide navigation fields** — slide blocks now expose internal page, event listing,
  catalog listing, collection, and external URL destination controls in create/edit forms.

- **Public site cache invalidation** — `PublicSiteCacheInvalidator` calls the public website's cache webhook after category, technique, collection item, and event writes, so admin edits reflect on the public site without waiting for TTL expiry.
- **Event and collection item detail views** — event show pages now display the public slug and a status badge in the header, and collection item screens clarify `field_status` as publication status (draft/published/archived) instead of the previous conservation-state wording.
- **Language-aware Museum forms and translation status** — category, technique, and collection item screens now use localized slug helpers, translated input labels, and translation status panels on detail pages.
- **CMS menu link target & preset catalog enhancements** — added `target_blank` checkbox and validation in `MenuItemStoreRequest` and `CmsPresetCatalog` for managing external links and dynamic listing page presets in CMS menus.
- **Multilingual translation management in Event and Museum modules** — added i18n translation tabs and input fields in event and collection item forms (`translations.php`), allowing administrative management of localized titles, summaries, and content.
- **Event domain admin modules** — Bookings, Events, Occurrences, EventReferences, TicketTypes
  and Tickets are now fully wired against `EventDomainApiClient`, with consolidated en/es
  translations per module.
- **Legacy collection presets** — added Cms block-type/collection presets for companies, people,
  works, videos, festivals, exhibitions, courses and publications, in preparation for the legacy
  data migration.
- **Museum admin module** — new `App\Modules\Museum` with full CRUD for Categories, Techniques
  and Collection Items, wired to the catalog domain via the new `CatalogDomainApiClient`, plus a
  "Museo (Catálogo)" sidebar section.
- **French and Portuguese** — added to `supportedLocales` and `dateFormats`, with Museum module
  translations for both.
- **Cover and gallery image pickers** — new `fileGalleryField` Alpine component and
  `components/form/file_gallery` view let editors pick multiple images (reusing the existing
  `filePicker` store's multi-select mode) for `gallery_file_ids`; wired into the Events and Museum
  collection item forms, alongside a single-image `cover_file_id` picker on Events.
- **Public slug on collection item detail page** — `museum/collection_items/show.php` now shows
  the resolved public slug in the header subtitle, matching the existing Events pattern.
- **Collapsible sidebar sub-groups** — `bin/register-sidebar.sh` now supports grouping a module's
  sidebar items into collapsible sections; used to regroup Events' 7 flat items into
  "Scheduling"/"Ticketing".

### Fixed

- **`Museum` category/technique reorder routes were shadowed by the dynamic `:id` route** —
  `categories/reorder` and `techniques/reorder` were declared after `categories/(:segment)` /
  `techniques/(:segment)`, so CI4 matched them against `show()` (and `catalog.*.read`) instead
  of `reorder()`/`.update`. Moved both pairs of static routes ahead of the dynamic one, matching
  the pattern already used in `Events` (2026-08-12 audit finding).
- **`Cms::translate` had no permission of its own** — the proxy to Google Translate relied only
  on the broad `admin` section gate, so any user holding one of the 18 broad `AdminAccess`
  permissions could call it regardless of CMS/Museum/Events write access. Added an
  OR-of-permissions check in `TranslateController::hasAnyContentPermission()` (same pattern as
  `StructureWizardController`), and taught `AdminRouteAuthorizationTest` to discover
  admin-gated modules dynamically instead of relying on a hardcoded route-file list, with an
  explicit exemption list for controller-enforced routes (2026-08-12 audit finding).
- **`blocks/preview` proxy sent no shared secret to the public site** — `BlockPreviewController`
  now sends `X-Block-Preview-Key` (from `BLOCK_PREVIEW_KEY`) when calling the public site's
  `/blocks/preview`, matching the key the public site now optionally enforces (2026-08-12 audit
  finding).
- **Event and Catalog admin modules relied on a broad section gate for CRUD authorization** —
  `Bookings`, `EventReferences`, `Events`, `Occurrences`, `TicketTypes`, `Tickets`, `Venues`
  and the `Museum` (catalog) module now enforce an explicit `permission:<code>` filter on every
  route instead of the shared `admin` group filter, so holding any one admin-entry permission no
  longer implicitly grants access to unrelated resources. Sidebar entries and per-view create/edit/
  delete actions were aligned to the same granular permissions, and an architecture test now fails
  the build if a domain route is added without one (ADM-SEC-01).
- **CSP style nonce silently killed `unsafe-inline` on every page** — `layouts/partials/head.php`
  wrapped its `[x-cloak]` rule in an inline `<style <?= csp_style_nonce() ?>>` block, present on
  every page render. Per the CSP spec, a `style-src` directive that carries a nonce makes browsers
  ignore `'unsafe-inline'` in the same directive — and `'unsafe-inline'` was deliberately kept in
  `style-src` (FRONT-01g) so Alpine's `x-show`/`:style` bindings (which mutate `el.style` directly)
  keep working. The nonce quietly broke that everywhere: any inline style Alpine tried to apply was
  blocked (`Applying inline style violates ... 'unsafe-inline' is ignored if ... nonce ... is
  present`). Moved the `[x-cloak]` rule into the compiled stylesheet (`src/css/app.css`) instead —
  no inline `<style>` tag means no nonce, and `style-src` now stays `'self' 'unsafe-inline'` as
  intended on every response.
- **Dashboard widgets overloaded the server on cold cache** — the dashboard fires 7 independent
  widget requests on load, two of which (`widgetSummary`, `widgetHealth`) each fan out into up to
  8 sequential upstream API calls; on a cache-cold load (e.g. right after a cache config change)
  this produced dozens of concurrent outbound requests and could exhaust a small server's CPU/RAM,
  causing unrelated navigation (e.g. to Files) to fail with a server error (2026-08-07 incident).
  Added `createFetchQueue()` (`src/js/utils/fetchQueue.js`) and routed all 7 widget fetches through
  a shared `window.dashboardFetchQueue` capped at 3 concurrent requests.
- **Remote tables fetched their data twice on every page load** — the 28 `remoteTable`-backed
  index views (Users, Files, Audit, IAM, CMS Entries/Pages/Collections/..., Events, Museum, etc.)
  set `x-init="init()"` on the same element as `x-data="remoteTable(...)"`. Alpine already
  auto-invokes an `init()` method found on the `x-data` object, so the explicit `x-init` fired it a
  second time — two identical `GET .../data` requests per table load. Removed the redundant
  `x-init`, in both the shipped views and `bin/make-module.sh`'s view stub so newly scaffolded
  modules don't reintroduce it.
- **File previews blocked by CSP `img-src`** — file URLs returned by the API are absolute and point
  at the API's own origin, which differs from the admin's; `ContentSecurityPolicy` now derives that
  origin from `apiClient.baseUrl`/`API_BASE_URL` and allows it, fixing thumbnails/previews on
  `/files` in production (`admin.teatromuseo.cl` → `api.teatromuseo.cl`).
- **Cross-repo `CmsEnums` coupling** — `MenuItemStoreRequest`/`BlockInstanceController` now use a
  locally owned `App\Support\CmsFieldEnums` instead of a `composer.json` PSR-4 mapping into
  `teatromuseo-cms-domain`'s tree, which silently broke in CI and was never present in the Docker
  image. `.env.example`/`docker-compose.yml` also now point at the current hub (`8180`) and admin
  (`8182`) ports.
- **Occurrence datetimes ignored their event timezone** — `formatDate()` now accepts an optional
  timezone alongside the date value, and datetime-local form inputs normalize API timestamps
  (`Y-m-d H:i:s`) into the format the HTML5 input expects.
- **`Universal` CRUD module** — removed. It gated a generic CRUD over cms-domain behind a plain
  `auth` filter (every other module requires `admin`/`superadmin`/`permission:`), had no service
  layer, no language files, and no PSR-4 registration of its own; the stale `Catalog` PSR-4 entry
  in `app/Config/Autoload.php` that pointed at the same removed tree is gone too.
- **Nested API validation feedback** — `ApiClient` and `BaseWebController` now flatten nested
  `fieldErrors`/`errors` payloads into the dot-notated keys consumed by forms, so authentication,
  CMS, and shared CRUD failures retain actionable field-level messages instead of falling back to
  a generic error.
- **Nested file-picker manifests** — the file picker now unwraps both admin and API response
  envelopes instead of treating a valid cached manifest as empty.
- **Legacy repeater values** — scalar values from older one-field repeater payloads remain
  editable after the block schema is upgraded to structured repeater items.

- **Cache invalidation diagnostics** — manual invalidation now returns remote operation details
  and records its source for the public-site status view.

- **Duplicate form submissions** — UI forms now prevent repeated submissions while the original
  request is being processed.

- **CMS "Bloques de Contenido" load time** — `BlockTypeOptionsResolver` now memoizes collection/form lookups and exposes a lightweight `rawIndexed()` catalog for the read-only block-type list views (`BlockInstanceController::index()`/`children()`), cutting page load from ~3-8s to ~1s.
- **Domain client token refresh** — `DomainApiClient` and `BffApiClient` now delegate `attemptTokenRefresh()` to the hub via the new `SecondaryApiClient` base class, instead of retrying `/auth/refresh` against their own (nonexistent) endpoint and clearing the session on every request.
- **`PUBLIC_SITE_URL` port** — corrected the public website's default/example port (8186 → 8184) in `.env`/`.env.example`/`phpunit.xml.dist` so preview links and cache invalidation hit `teatromuseo-web` instead of the totem app's port.
- **`OccurrenceStoreRequest::venue_id`** — now reads as a nullable int, so an occurrence can be submitted without a venue instead of coercing a missing value to `0`.
- **Validation fallback matching** — `BaseWebController`, `BaseFormRequest`, and CMS translation helpers now resolve the current locale consistently when falling back to generic labels and error messages.
- **`field_row` / `text` form components** — no longer crash when a value is an array or object;
  it is now rendered as JSON instead.
- **Metrics dashboard** — slow-request rows now read `uri`/`response_time` (with a fallback to
  the older `path`/`duration_ms` keys) to match the hub's actual metrics payload shape.
- **File downloads** — binary file bodies are now streamed via `DownloadResponse` instead of
  written directly to the response body, avoiding a CI4 debug-toolbar crash on non-UTF8 content
  in the dev environment.
- **`CatalogDomainApiClient` / `EventDomainApiClient`** — the base-URL fallback read `$this->baseUrl`
  *after* `parent::__construct()` had already overwritten it with the generic domain default,
  so an unset `CATALOG_DOMAIN_API_BASE_URL`/`EVENT_DOMAIN_API_BASE_URL` silently pointed at the
  wrong port instead of each client's own declared default.
- **Museum collection items table** — now displays the resolved category name instead of the raw
  `category_id`, and `is_active`/`show_in_totem` are sent as ints to match the catalog API's
  contract instead of booleans.
- **Museum sort-order save** — category/technique reorder success messages now use a real
  `Museum.sort_order_saved` key instead of an unrelated `Files.*` one.
- **Admin UI locales** — `App::$supportedLocales` now only lists `es`/`en`, the Admin UI's own
  interface languages. `fr`/`pt` were domain content locales, not Admin UI locales, and belonged
  under CMS/domain configuration instead of this application list.

### Changed

- **CMS validation and editor feedback** — request rules now match domain field limits and nested
  translation contracts, while editors, file translations, site identity, and the structure wizard
  preserve submitted values and map server errors back to the correct field or language row.
- **Editorial and block editor terminology** — publication labels now use Editorial consistently,
  listing field guidance is clearer, and block/media previews use more suitable compact layouts.
- **CMS metadata and site identity configuration** — resolved CMS metadata is reused across
  admin views, while site identity settings are organized behind the centralized configuration
  flow.
