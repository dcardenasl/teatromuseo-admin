# teatromuseo-admin — Admin Interface Repository Guidelines

> **Responsibility:** Server-rendered Admin UI (CodeIgniter 4 + Tailwind CSS + Alpine.js).
> **Port:** 8182

## Key Architectural Rules

1. **Stateless UI & Auth Boundary:** The Admin interface holds the user JWT in PHP session and forwards calls to the Hub (`ApiClient`) and domain apps (`DomainApiClient`, `EventDomainApiClient`, `CatalogDomainApiClient`).
2. **No Direct DB Access:** The Admin app has no database of its own. All data operations are delegated to backend APIs.
3. **Cache Invalidation:** Content mutations automatically trigger cache invalidation via `PublicSiteCacheInvalidator` to the public web app.
4. **i18n Parity:** Keep english and spanish language keys in sync (`app/Language/en` and `app/Language/es`).
5. **Quality Gates:** Run `composer quality` before committing (PHPStan, CS-Fixer, i18n-check, PHPUnit).
