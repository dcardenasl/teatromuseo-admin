# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Added

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

### Fixed

- **`PUBLIC_SITE_URL` port** — corrected the public website's default/example port (8186 → 8184) in `.env`/`.env.example`/`phpunit.xml.dist` so preview links and cache invalidation hit `teatromuseo-web` instead of the totem app's port.
- **`OccurrenceStoreRequest::venue_id`** — now reads as a nullable int, so an occurrence can be submitted without a venue instead of coercing a missing value to `0`.
- **Validation fallback matching** — `BaseWebController`, `BaseFormRequest`, and CMS translation helpers now resolve the current locale consistently when falling back to generic labels and error messages.
- **`field_row` / `text` form components** — no longer crash when a value is an array or object;
  it is now rendered as JSON instead.
- **`CatalogDomainApiClient` / `EventDomainApiClient`** — the base-URL fallback read `$this->baseUrl`
  *after* `parent::__construct()` had already overwritten it with the generic domain default,
  so an unset `CATALOG_DOMAIN_API_BASE_URL`/`EVENT_DOMAIN_API_BASE_URL` silently pointed at the
  wrong port instead of each client's own declared default.
- **Museum collection items table** — now displays the resolved category name instead of the raw
  `category_id`, and `is_active`/`show_in_totem` are sent as ints to match the catalog API's
  contract instead of booleans.
- **Museum sort-order save** — category/technique reorder success messages now use a real
  `Museum.sort_order_saved` key instead of an unrelated `Files.*` one.
