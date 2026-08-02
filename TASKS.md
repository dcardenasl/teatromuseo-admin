# TASKS — ci4-website-builder-admin

> Fuente de verdad para trabajo abierto en este repositorio.
> Los entregables cerrados están en [`TASKS_ARCHIVE.md`](TASKS_ARCHIVE.md).
> Seguimiento global: [`../TASKS.md`](../TASKS.md).
> Tracker depurado el 2026-07-21; no se conservan notas de conversación ni bitácoras de participantes.

## 🔴 En progreso

*(vacío)*

## 🟡 Próximo

### TRN-006 — Estados editoriales, permisos y controles de publicación

- [ ] Decidir el modelo de estados por idioma (`in_review`, `approved`, `published`).
- [ ] Definir la relación con `status` de páginas y entradas.
- [ ] Definir roles/permisos de aprobación antes de implementar migraciones, servicios y UI.

## ✅ Completadas

- **AUTH-DOMAIN-001 — Token refresh de DomainApiClient/BffApiClient apuntaba al host equivocado (2026-08-02):**
  Reporte: "Páginas" (y por extensión Menús/Tipos de bloque/Redirecciones/Colecciones/Entradas/
  Categorías/Tags/Formularios y todos los módulos de Catalog/Event domain) mostraba
  "Tu sesión expiró" con sesión recién iniciada; el panel DEV exponía el 401 real:
  `Authorization header missing`. Causa raíz: `ApiClient::attemptTokenRefresh()` hace
  `POST {baseUrl}/auth/refresh` contra **su propio** `$this->config->baseUrl` — correcto para
  el Hub, pero `DomainApiClient` (CMS/Catalog/Event, puertos 8190/8191/8193) y `BffApiClient`
  heredaban ese método sin overridearlo, así que el refresh pegaba contra esos hosts, que no
  tienen `/auth/refresh` (por diseño: delegan toda la auth al Hub). El intento fallido disparaba
  `clearSessionAuth()`, borrando `access_token`/`refresh_token`/`user` de la sesión — sesión que
  es compartida por todos los clientes vía `session()` — y el request se reintentaba sin header.
  Fix: nueva clase base `App\Libraries\SecondaryApiClient` (abstracta, extiende `ApiClient`)
  que recibe un `ApiClientInterface` del Hub por constructor y overridea
  `attemptTokenRefresh()` para delegar siempre ahí. `DomainApiClient` y `BffApiClient` ahora
  extienden `SecondaryApiClient` en vez de `ApiClient` directamente; sus constructores reciben
  el hub client (default `service('apiClient')`). `ApiClientInterface` ahora declara
  `attemptTokenRefresh(): bool` explícitamente (ya era público en la implementación, faltaba en
  el contrato). Las 4 factories de `Services.php` (`domainApiClient`, `eventDomainApiClient`,
  `catalogDomainApiClient`, `bffApiClient`) inyectan `static::apiClient()` explícitamente.
  Sin parches ni casos especiales: el Hub sigue siendo la única fuente de refresh para
  cualquier cliente secundario, de forma genérica y reutilizable para futuros domain apps.
  Cobertura nueva: `tests/unit/Libraries/SecondaryApiClientTest.php` (6 tests, verifica
  delegación + que ningún cliente secundario intente refrescar contra su propio host).
  Verificado: `composer analyse` ✅ · `composer format:check` ✅ · 736/736 tests ✅ (730 previos + 6 nuevos, 1 skip preexistente sin relación).

- **GALLERY-001 — Picker múltiple para gallery_file_ids en Events y Museum (2026-07-30):**
  `gallery_file_ids` existía en ambos domains (catalog/event) y en `CollectionItemStoreRequest`,
  pero no había ningún campo de formulario que lo llenara, y `EventStoreRequest` ni siquiera lo
  tenía en su whitelist. Se agregó: componente Alpine `fileGalleryField` (`src/js/components/`,
  registrado en `app.js`) que reutiliza el store `filePicker` ya existente en modo `multi: true`
  (soporte de multi-selección que estaba implementado en `file_picker_modal.php` pero nunca se
  usaba en ningún componente); vista `components/form/file_gallery.php` con grid de miniaturas +
  botón quitar por imagen, guardando CSV de IDs en un input oculto (mismo formato que ya
  consumen `PublicEventController`/`PublicCollectionItemController::resolveMediaFields()`).
  Cableado en `events/events/{create,edit}.php` y `museum/collection_items/{create,edit}.php`;
  `EventStoreRequest` ahora incluye `gallery_file_ids` en fields/rules/payload (antes se
  descartaba en silencio si llegaba por POST). Confirmado además que el web público ya tenía
  `EventItemGalleryViewModel`/`CatalogItemGalleryViewModel` esperando `gallery_images` en este
  shape exacto — quedaba huérfano hasta ahora.
  Verificado: `composer analyse` ✅, `composer format:check` ✅, 726/726 PHPUnit ✅,
  `npm run lint:js` ✅, 83/83 Vitest ✅, verificado visualmente en `/admin/events/events/1/edit`
  y `/admin/museum/collection-items/1/edit` (campo renderiza, picker abre filtrado en imágenes).
  No se hizo upload real de prueba (la herramienta de navegador no soporta subir archivos);
  el flujo de selección múltiple se apoya en `$store.filePicker.confirm()`, código compartido
  y ya cubierto por el modal existente.
  **Fuera de alcance, pendiente de decisión:** invalidación de caché de archivos
  (`invalidateFileMetaCache()` sigue sin ningún llamador en los 3 domains) y el guard de borrado
  del Hub sigue sin visibilidad sobre usages en domain apps — ambos señalados en el audit previo,
  no tocados en esta entrega.

- **EVT-COVER-001 — Selector de imagen de portada en el módulo Events (2026-07-30):**
  `event-domain` ganó soporte de `cover_file_id`/`gallery_file_ids` (ver su propio
  `TASKS.md` EVT-DOM-004). Añadido el campo correspondiente al admin: `EventStoreRequest`
  (heredado por `EventUpdateRequest`) acepta `cover_file_id` vía `postNullableInt()`,
  y `create.php`/`edit.php` reutilizan el componente `components/form/file` +
  `file_picker_field` ya usado en `museum/collection_items` — sin JS nuevo, el picker
  global (`file_picker_modal.php` en el layout) ya cubre cualquier módulo. Lang keys
  `field_cover_file_id*` añadidas en `es`/`en`. Motivado por que `/es/cartelera` en el
  sitio público nunca mostraba imágenes de portada porque no existía ni el campo en la
  BD ni el control de carga en el admin.
  Verificado: `composer analyse` ✅, `composer format:check` ✅, 726/726 tests ✅.
  Pendiente (no incluido en este cambio): backfill de imágenes reales para los eventos
  existentes — es trabajo editorial, no de código.

- **MUS-SLUG-001 — Paridad de slug público en la ficha de Colección del Museo (2026-07-29):**
  `Events` ya mostraba el slug público resuelto en su vista `show`; `museum/collection_items/show.php`
  no lo hacía pese a que el catalog-domain ya lo expone (`CollectionItemResponseDTO::slug`, aditivo,
  vía passthrough genérico de `ResourceApiService`). Añadido al subtítulo de cabecera, solo lectura,
  mismo patrón que Events. Test de feature actualizado con el campo `slug` en el fixture y su
  aserción. `composer quality` ✅.

- **EVT-001 — Integración de módulos admin de event domain:** scaffold + ajustes manuales de
  Bookings, Events, Occurrences, EventReferences, TicketTypes y Tickets (`EventDomainApiClient`,
  rutas, controllers, requests, services, vistas). Auditoría posterior detectó y corrigió archivos
  de idioma duplicados entre `app/Language/` y `app/Modules/*/Language/` para los 7 dominios
  (incluyendo `Venues`, que tenía el mismo problema latente) — consolidados en la ubicación
  canónica del módulo siguiendo el patrón de `Users`.
- Los cambios de `app/Modules/Cms/Services/BlockTypeOptionsResolver.php`, `CmsPresetCatalog.php` y
  `app/Language/{en,es}/Collections.php` en este mismo working tree son trabajo preparatorio para
  la migración de colecciones legacy (ver `LEGACY-001` en `../teatromuseo-api/TASKS.md`), no parte
  de EVT-001 — deben separarse en su propio commit al ejecutar `/commit-flow`.

## ⚪ Backlog

### ADM-DEP-002 — lint-staged 16 → 17

- [ ] Esperar el baseline Node 22 (`>=22.22.1`), actualizar `lint-staged`, ejecutar `npm audit` y
  verificar el hook `pre-commit`.

## 🏗️ Contratos de arquitectura

- **DTO-First:** Controllers y Services intercambian DTOs con contratos explícitos.
- **Controllers delgados:** delegar lógica de negocio a Services y usar `DomainApiClient`.
- **Permisos:** usar códigos separados por punto, por ejemplo `cms.pages.read`.
- **Componentes compartidos:** reutilizar helpers de traducción, estados, formularios y media.
- **i18n:** mantener paridad en `app/Language/en` y `app/Language/es`.
- **Calidad:** cerrar tareas solo con tests, PHPStan/CS-Fixer, i18n y build aplicables en verde.

## 🔧 Referencias

- Plan editorial: [`../docs/plans/2026-07-20-translation-workbench-plan.es.md`](../docs/plans/2026-07-20-translation-workbench-plan.es.md)
- Tracker global: [`../TASKS.md`](../TASKS.md)
- Histórico: [`TASKS_ARCHIVE.md`](TASKS_ARCHIVE.md)
