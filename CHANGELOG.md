# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Added

- **Event domain admin modules** — Bookings, Events, Occurrences, EventReferences, TicketTypes
  and Tickets are now fully wired against `EventDomainApiClient`, with consolidated en/es
  translations per module.
- **Legacy collection presets** — added Cms block-type/collection presets for companies, people,
  works, videos, festivals, exhibitions, courses and publications, in preparation for the legacy
  data migration.
- **Museum admin module** — new `App\Modules\Museum` with full CRUD for Categories, Techniques
  and Collection Items, wired to the catalog domain via the new `CatalogDomainApiClient`, plus a
  "Museo (Catálogo)" sidebar section.

### Fixed

- **`field_row` / `text` form components** — no longer crash when a value is an array or object;
  it is now rendered as JSON instead.
