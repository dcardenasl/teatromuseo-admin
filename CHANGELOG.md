# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Added

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
