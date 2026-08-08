# TASKS — teatromuseo-admin

> Fuente de verdad para trabajo abierto en este repositorio.
> Los entregables cerrados están en [`TASKS_ARCHIVE.md`](TASKS_ARCHIVE.md).
> Seguimiento global: [`../TASKS.md`](../TASKS.md).
> Tracker depurado el 2026-07-21; no se conservan notas de conversación ni bitácoras de participantes.

## 🔴 En progreso

*(vacío)*

## 🟡 Próximo

> Saneamiento arquitectónico — auditoría del 2026-08-05.
> **Contexto, evidencia y rutas exactas:** [`../docs/plan/2026-08-05-saneamiento-arquitectonico.md`](../docs/plan/2026-08-05-saneamiento-arquitectonico.md)
> Orden y dependencias cross-repo: [`../TASKS.md`](../TASKS.md)

### Fase 1 — Seguridad

### Fase 2 — Configuración y CI

- [ ] **CFG-02 — El `.env.example` documenta 9 variables y el código lee 90.** Es esencialmente
  ficción. Reconstruirlo desde las claves reales: `API_BASE_URL`, `DOMAIN_API_BASE_URL`,
  `PUBLIC_SITE_URL`, `CMS_PREVIEW_SECRET`, `API_APP_KEY`, `BFF_API_APP_KEY`,
  `CATALOG_DOMAIN_API_KEY`, `EVENT_DOMAIN_API_KEY`, todos los `*ApiClient.*`, `MAINTENANCE_MODE`,
  `SESSION_DRIVER`. Nota: los 3 valores correctos viven hoy en un archivo `env` **no rastreado**.
- [ ] **CFG-05 — `composer quality` no ejecuta tests** (los deja en un script `ci` aparte),
  contradiciendo el `CLAUDE.md` raíz. Alinear con la política única de la flota.
  `phpunit.xml.dist` es además el más permisivo: `failOnWarning="false"` y
  `failOnDeprecation="false"`.
- [ ] **CFG-06 — El `pre-push` está instalado pero muerto.** `core.hooksPath = .husky/_` hace que
  git ignore `.git/hooks/pre-push`, y existe `.husky/_/pre-push` como shim **sin `.husky/pre-push`
  detrás**. Solo hay `.husky/pre-commit`.
- [x] ~~CFG-08~~ — **completado (2026-08-07).** `php-cs-fixer` actualizado a `^3.95` en `composer.json`.

### Fase 3 — Extracción a `ci4-api-core`

- [x] ~~CORE-04~~ — **completado 2026-08-06.** Ver Completadas. (`composer.json:127`
  `sync-swagger` ya se había retirado en un pase anterior, junto con `SEC-07`.)

### Fase 6 — Frontend y docs

- [x] ~~FRONT-01a~~ — **completado (2026-08-07).** Tres mecanismos HTTP que se saltaban `ApiClient`:
  `TranslateController.php:35-48` ya no usa `curl_init` crudo con user-agent de Chrome falsificado —
  ahora usa `Config\Services::curlrequest()` con `try/catch` y user-agent real
  (`TeatroMuseoAdmin/1.0`). `PublicSiteCacheInvalidator.php`'s 25 líneas de construcción de
  `CURLRequest` duplicadas dos veces (`invalidateWithResult()` y `status()`) — extraídas a un
  `buildClient()` privado usando `Config\Services::curlrequest()`, mismo patrón que
  `TranslateController`. `BlockPreviewController.php:32` ya usaba `curlrequest()` correctamente
  (try/catch, timeout, fallback local) — el "conocimiento incrustado de la ruta" (`/blocks/preview`)
  es inherente a cualquier cliente HTTP que llama a una ruta fija de otra app, no un defecto real;
  no se toca.
- [ ] **FRONT-01b — Seis namespaces de idioma definidos dos veces** (global + módulo) con claves
  solapadas: `Pages` (57 vs 216 líneas, **12 claves colisionando**), `Collections`, `Forms`,
  `FormSubmissions`, `Profile`, `Auth`. Los valores coinciden hoy, así que es deriva latente, no un
  fallo activo. Definir qué archivo posee qué clave.
- [ ] **FRONT-01c — 172 cadenas incrustadas** fuera de los archivos de idioma, concentradas en
  `app/Views/cms/block_types/previews/` (27 archivos). Viola el contrato de consistencia del propio
  `CLAUDE.md` de este repo.
- [ ] **FRONT-01d — ~1.100 líneas de `<script>` en línea** con lógica de negocio
  (`cms/collections/partials/block_template_editor.php` ~424, `cms/pages/blocks/create.php` ~330,
  `layouts/partials/head.php` ~138, `cms/pages/blocks/_listing_projection.php` ~94), ninguna cubierta
  por el build de esbuild que la app ya tiene.
- [ ] **FRONT-01e — Cinco modismos de autorización distintos** entre módulos: grupo `['auth','admin']`
  solo · grupo + `permission:` por ruta · `['auth']` + `permission:` · `['auth','superadmin']` ·
  `['auth']` a secas (Universal, se elimina en SEC-04). Unificar.
- [ ] **FRONT-01f — Convención de rutas de vista partida.** Los módulos antiguos son planos
  (`Views/users/index.php`), los generados anidan dos veces con un solo hijo
  (`Views/venues/venues/index.php`), y ahí mismo deriva el nombrado
  (`eventreferences/event_references/`, `tickettypes/ticket_types/`).
  Consolidar además los 31 `partials/filters.php` y 26 `partials/toolbar_actions.php` con el mismo
  esqueleto en un componente declarativo.
- [x] ~~FRONT-02~~ — **completado (2026-08-07).** `app/Libraries/PublicSiteCacheInvalidator.php:203`
  (`normalizeScopes()`) ahora valida contra una constante `VALID_SCOPES` y rechaza scopes desconocidos
  con log de advertencia en vez de no-op silencioso. Nota: la falta de invalidación real en
  event-domain/catalog-domain (`Occurrences`, `Venues`, `Tickets`, `TicketTypes`, `Bookings`,
  `EventReferences`) no se tocó — sigue pendiente, ver Completadas para detalle exacto de lo resuelto.
- [ ] **DEAD-02 — Archivos rastreados que no sirven a nada:** `default.php` (16 KB, es una **página
  de aparcamiento de Hostinger**), `swagger_contract.json` (232 KB del contrato del hub, no lo lee
  nadie), dos plantillas de entorno divergentes (`env` declara 16 claves `database.*` en una app
  **sin base de datos**), 9 componentes de vista sin usar
  (`components/table/{image,text,badge,date,number}_cell.php`, `table/toolbar.php`, `form/radio.php`,
  `form/translatable_image.php`, `display/confirm_modal.php`), y el directorio `components/forms/`
  (con "s") que duplica `components/form/` con un único archivo — una errata que se quedó.
- [ ] **DOC-01 — Deriva documental residual:** `CLAUDE.md` sigue mencionando `ci4-website-builder*`
  en 5 sitios (el commit `3c50e73` corrigió parte, no todo). **Corrección (2026-08-07): `AGENTS.md`
  no faltaba** — ya existía (creado en `96f3931`) con contenido detallado en español; el batch de
  esta sesión lo había sobrescrito por una versión más corta en inglés que perdía contenido real
  (lista de anti-patrones, detalle de los tests de arquitectura) — revertido, ver Completadas.
- [ ] **FRONT-01g — CSP relajado con `'unsafe-eval'` + `'unsafe-inline'` (style-src) por la build
  estándar de Alpine.js.** `app/Config/ContentSecurityPolicy.php` (constructor, `scriptSrc`/`styleSrc`)
  agrega ambas directivas porque `node_modules/alpinejs/dist/cdn.min.js` (copiado a
  `public/assets/vendor/alpine.min.js` por `scripts/build-vendor.js`) evalúa expresiones `x-data`/`x-on`
  inline vía `new Function()` (necesita `unsafe-eval`) y `x-show` togglea visibilidad escribiendo
  `el.style.display` directamente (se trata como estilo inline, necesita `unsafe-inline` en `style-src`
  incluso con nonce — los nonces no cubren mutaciones de `CSSStyleDeclaration` vía JS). Detectado el
  2026-08-07 al desplegar a `admin.teatromuseo.cl`: sin estas directivas, todo `x-data` falla en
  silencio (spinner de login que nunca resuelve `isLoading`) o el navegador bloquea el toggle de estilo.
  **Fix real (no aplicado aún):** migrar a `@alpinejs/csp` (build que compila expresiones sin `eval`,
  pero solo soporta componentes registrados vía `Alpine.data()`, no objetos `x-data="{...}"` inline) y
  reemplazar todo `x-show`/`:style` por `x-bind:class` con clases Tailwind reales (`hidden`) en vez de
  estilo inline. Afecta **589 ocurrencias de `x-show`/`x-data="{`/`x-bind:style`/`:style=` en 109
  vistas** (`grep -rn` en `app/Views`, ver conteo completo por archivo si se retoma). No es un cambio de
  una línea: requiere reescribir cada componente inline, rebuild (`npm run build`), y QA de las 109
  pantallas — se pospone deliberadamente en vez de hacerse a medias vía FTP. El tótem (`teatromuseo-totem-ci4`)
  no tiene este problema porque no usa Alpine.js (JS vanilla), no porque lo haya resuelto.
  **Intento revertido el 2026-08-07:** un segundo generador de CSP hardcodeado en
  `app/Filters/SecurityHeadersFilter.php` que en realidad *aflojaba* la política (agregaba
  `unsafe-inline` a `script-src`, que antes no estaba, más allowlists externas no documentadas),
  compitiendo en silencio con `ContentSecurityPolicy.php` — revertido por ser un debilitamiento neto de
  seguridad, no el fix real. Se mantiene de ese intento solo `ContentSecurityPolicy.php`'s
  `autoNonce = false` (legítimo: permite nonces selectivos vía `csp_script_nonce()` en vistas puntuales,
  ver `system/cache.php`, en vez de forzar nonce global) y el fix puntual de `system/cache.php` — ninguno
  de los dos resuelve FRONT-01g por sí solo.
  **Análisis de alcance (2026-08-07):** confirmado `grep -rlE "x-show=|x-data=\"\{|x-bind:style|:style="
  app/Views/` → 109 archivos exactos. Muestra real (`layouts/partials/sidebar.php`, top offender
  `cms/pages/blocks/create.php` con 41 ocurrencias): el patrón dominante es
  `x-data="{ open: <?= $phpBooleanExpr ?> }"` — un objeto inline con un valor **dinámico inyectado
  desde PHP en cada render** (no una constante estática), más `x-show="open"` para el toggle. La
  build CSP de Alpine (`@alpinejs/csp`) no evalúa expresiones libres: cada componente debe
  registrarse vía `Alpine.data('nombre', (arg) => ({...}))` en un script externo (no inline), y la
  vista pasa el valor dinámico como argumento de invocación (`x-data="navGroup(true)"`), no como
  objeto literal — la build CSP sí permite llamadas a funciones registradas con argumentos simples,
  solo prohíbe expresiones/objetos arbitrarios sin registrar. Es un patrón claro y replicable, pero
  mecánico y extenso: 109 vistas con formas distintas (algunas con múltiples `x-data` por vista,
  valores dinámicos variados). **No se ejecuta en esta sesión** — se pospone deliberadamente,
  consistente con lo que este mismo ítem ya decía antes de este análisis; hacerlo a medias dejaría
  un estado mixto (algunas vistas CSP-safe, otras no) sin una CSP realmente más estricta todavía,
  que es peor que no tocarlo. Próximo paso real: registrar todos los componentes recurrentes
  (nav-group toggle, modales, etc.) como factories `Alpine.data()` en un archivo compartido nuevo
  (`src/js/alpine-components.js`), luego migrar vista por vista empezando por `layouts/partials/`
  (compartidas, mayor apalancamiento) antes que las 41 ocurrencias aisladas en `create.php`.
- [x] ~~FRONT-01h~~ — **completado (2026-08-07).** Implementado `closeSessionSafely()` en los 7 métodos de widget de `DashboardController` y resguardado `clearSessionAuth()` en `ApiClient`. Los widgets del dashboard se procesan en paralelo liberando el lock de sesión inmediatamente tras consumir las APIs.

### TRN-006 — Estados editoriales, permisos y controles de publicación

- [ ] Decidir el modelo de estados por idioma (`in_review`, `approved`, `published`).
- [ ] Definir la relación con `status` de páginas y entradas.
- [ ] Definir roles/permisos de aprobación antes de implementar migraciones, servicios y UI.

## ✅ Completadas

- **Incidente de producción — 508 "Límite de Procesos" al entrar a Files mientras el dashboard
  carga (2026-08-07):** `admin` y el hub (`teatromuseo-api`) comparten la misma cuenta de hosting
  compartido (mismo `ftp.teatromuseo.cl`), por lo tanto el mismo cupo de procesos concurrentes.
  `app/Views/files/partials/list_section.php` y `trash.php` renderizan un `<img>` por fila que cae
  a un endpoint PHP por-archivo (`/files/{id}/view`, vía `FileController::serveFile()`) cuando el
  archivo no tiene `variants.sm.url` generado — esto ocurre solo para imágenes reales sin variante
  (los archivos no-imagen ya usaban un ícono local sin red). Con 25 filas por página (default), eso
  puede disparar hasta 25 requests PHP concurrentes solo en miniaturas, sumado a los widgets del
  dashboard que aún no terminaron de cerrar su proceso al navegar. Fix: `loading="lazy"` +
  `decoding="async"` en las 3 miniaturas afectadas (tabla y grilla de `files/index`, tabla de
  `files/trash`; el picker de archivos ya lo tenía). El picker de archivos compartido
  (`file_picker_modal.php`) ya tenía este atributo. La causa raíz más profunda (muchas imágenes sin
  `variants.sm` generado) sigue sin resolver — requeriría un backfill de variantes, fuera de alcance
  de este fix.
- **FRONT-01h — Race condition de session-lock en widgets del dashboard (2026-08-07):** los 7
  métodos `widget*()` de `DashboardController` ahora llaman `closeSessionSafely()` como última línea,
  después de que todo el trabajo de `ApiClient` (incluyendo rutas de error/refresh) terminó;
  `ApiClient::clearSessionAuth()` se protege con `session_status() === PHP_SESSION_ACTIVE`. Evita
  el bug de login roto del intento anterior (filtro `SessionCloseFilter`, revertido en `bd530d8`).
  **Bug encontrado y corregido en esta sesión (2026-08-07):** el guard original envolvía tanto
  `session->remove()` como `session->regenerate(true)`, pero `session_status()` (nativo de PHP) no
  refleja de forma fiable el estado de sesión de CI4 — en tests de feature (y potencialmente en
  producción según el driver) reportaba inactivo aunque la sesión de CI4 seguía siendo válida,
  dejando `remove()` sin ejecutarse nunca. Esto rompía logout: `access_token` quedaba en sesión tras
  cerrar sesión (`ApiClientTest::testClearSessionAuthRemovesAllAuthenticationKeys` y los 3 tests de
  `AuthLogoutFlowTest` fallaban). El crash original solo venía de `regenerate(true)`, no de
  `remove()` — el guard ahora protege solo `regenerate(true)`; `remove()` corre siempre. 760/760
  tests pasan tras el fix.
  que cerraba la sesión demasiado pronto y hacía explotar `session->regenerate(true)` en el camino
  de refresh fallido.
- **FRONT-02 — Invalidación de caché sin validar (parcial, 2026-08-07):**
  `PublicSiteCacheInvalidator::normalizeScopes()` ahora valida contra una constante `VALID_SCOPES` y
  rechaza scopes desconocidos con log de advertencia en vez de no-op silencioso. El hueco real de
  invalidación en event-domain/catalog-domain (sin job equivalente al de CMS) no se tocó.
- **FRONT-01a — Mecanismos HTTP que se saltaban `ApiClient` (completado, 2026-08-07):**
  `TranslateController.php:35-48` ya no usa `curl_init` crudo con user-agent de Chrome falsificado —
  usa `Config\Services::curlrequest()` con `try/catch` real. `PublicSiteCacheInvalidator.php`
  extrae la construcción de `CURLRequest` (duplicada en `invalidateWithResult()`/`status()`) a un
  `buildClient()` privado. `BlockPreviewController.php:32` revisado — ya usaba `curlrequest()`
  correctamente, sin defecto real que corregir.
- **CFG-08 — `php-cs-fixer` desalineado de la flota (2026-08-07):** actualizado de `^3.47.1` a
  `^3.95` en `composer.json`, igualando al resto de repos.
- **Corrección de proceso (2026-08-07):** el batch de esa fecha había sobrescrito `AGENTS.md`
  (que ya existía, `96f3931`) con una versión más corta en inglés, perdiendo la lista de
  anti-patrones y el detalle de los tests de arquitectura del original en español. Restaurado el
  contenido original. DOC-01 sigue abierto (ver Próximo) — nunca trató de "AGENTS.md faltante", el
  ítem real es la deriva documental residual en `CLAUDE.md`.
- **CFG-02 — `.env.example` desalineado del código real (2026-08-07):** la mayoría de las claves
  ya estaban correctas (`API_BASE_URL`, `PUBLIC_SITE_URL`, `CMS_PREVIEW_SECRET`, `API_APP_KEY`,
  `BFF_API_APP_KEY`, `CATALOG_DOMAIN_API_KEY`, `EVENT_DOMAIN_API_KEY`), pero quedaban dos bugs
  reales: (1) el bloque "CMS Domain App" documentaba `CMS_DOMAIN_API_BASE_URL`/`CMS_DOMAIN_API_KEY`,
  variables ficticias que ningún archivo de `app/Config/` lee — `app/Config/DomainApiClient.php`
  (el cliente HTTP genérico que en este repo enruta *todos* los servicios del módulo Cms, ver
  `app/Config/Services.php:116-143`) en realidad lee `DOMAIN_API_BASE_URL`/`DOMAIN_API_APP_KEY`
  (namespace `domainApiClient.*`), confirmado también por el `env` no rastreado (línea 107). Se
  corrigieron las dos líneas al nombre real. (2) `SESSION_DRIVER` (forma corta que
  `app/Config/Session.php:148` lee directo vía `getenv()`/`env()`) no estaba documentada — solo el
  `session.driver` con FQCN; se agregó junto a las 3 variantes (file/redis/database). Sin cambios
  en el archivo `env` no rastreado (es de referencia, no se toca).
- **CORE-04 — Roto el acoplamiento PSR-4 hacia `teatromuseo-cms-domain` (2026-08-06):**
  supera el arreglo parcial de `SEC-07` de abajo — en vez de mantener el checkout cruzado
  correctamente apuntado, se eliminó por completo. El admin solo usaba 2 de las 6 constantes de
  `App\Libraries\Cms\CmsEnums` (`MENU_LINK_TYPES` en `MenuItemStoreRequest`,
  `NON_TRANSLATABLE_TYPES` en `BlockInstanceController`). Se creó `App\Support\CmsFieldEnums` con
  exactamente esas dos (siguiendo el patrón ya existente de `App\Support\CatalogOptions`), se quitó
  el mapeo `"App\\Libraries\\Cms\\": "../teatromuseo-cms-domain/app/Libraries/Cms/"` de
  `composer.json`, y se retiró el paso de CI que clonaba el repo hermano. La imagen Docker
  (`COPY app ./app`) ya contiene todo lo que el admin necesita — antes, no. Duplicación aceptada
  a propósito: son solo 2 constantes de validación cliente-side, el mismo trade-off que cualquier
  front/back desacoplado asume; mantener sincronizado a mano si cms-domain cambia esas listas.
  Verificado: `composer quality` (PHPStan + CS) ✅, 758 tests / 2.634 assertions ✅, incluidos los
  25 tests de `MenuItem`/`BlockInstance` que ejercitan las constantes migradas.

- **SEC-07 + CFG-01 — CI y puertos canónicos (2026-08-05):** el workflow clona
  `teatromuseo-cms-domain` en `../teatromuseo-cms-domain` y valida `CmsEnums.php`; se retiró el
  script muerto de Swagger y se alinearon `.env.example`, Compose y el cliente de dominio con
  admin `8182`, hub `8180` y CMS `8190`. Verificado: `composer quality` y 758 tests / 2.634
  assertions.

- **SEC-04 — Eliminar el módulo `Universal` (2026-08-05):** se retiraron sus rutas, controlador y
  vistas, junto con el mapeo PSR-4 muerto de `Catalog`. Se añadió regresión que confirma que la ruta
  `/admin/universal/pages` ya no existe. `composer quality` ✅.

- **PERF-BLOCKS-001 — "Bloques de Contenido" tardaba 3-8s en cargar, ahora ~1s (2026-08-02):**
  Reporte: navegar a `/admin/cms/pages/{id}/blocks` se sentía "muy lento". Dos causas
  independientes, ambas en el hot path de 63 tipos de bloque activos:
  (1) `BlockTypeOptionsResolver::resolve()` refetchaba por HTTP la lista completa de
  colecciones activas y de formularios activos **una vez por cada tipo de bloque** que
  las usara (sin memoización, a diferencia de `pagesForIds()`/`entriesForIds()` que sí la
  tenían) — con ~15-20 de los 63 tipos referenciando colecciones, eso eran 15-20+
  round-trips redundantes al CMS domain pidiendo el mismo dato. Fix: memoicé colecciones
  y formularios en la misma clase, siguiendo el patrón ya existente — ahora se piden como
  máximo una vez por request.
  (2) La causa mayor: `BlockInstanceController::index()` y `children()` — vistas de
  **solo lectura** que solo renderizan `name`/`icon`/`block_key`/`category`/`description`/
  `is_container` (verificado leyendo `blocks/index.php` y `blocks/children/index.php`) —
  llamaban al `resolve()` **completo** igual, pagando por toda la hidratación de opciones
  de formulario (colecciones, formularios, páginas, entradas, entry-references) sin usar
  nada de eso. `edit()` ya usaba el patrón correcto y liviano (`augment()` sobre un solo
  tipo); solo faltaba aplicarlo a las vistas de lista. Fix: nuevo método
  `BlockTypeOptionsResolver::rawIndexed()` — catálogo crudo sin ninguna llamada extra —
  usado ahora en `index()`/`children()`.
  Medido en vivo (Chrome real, sesión ya logueada, `performance.getEntriesByType('navigation')`):
  TTFB ~3.9s (cache-miss del catálogo) / ~2.7s (cache-hit) antes del segundo fix → ~520ms
  TTFB / ~970ms total después, incluso con la suite de tests completa compitiendo por CPU
  en background. Sin deuda técnica: mismo patrón de memoización ya usado en la clase,
  mismo patrón `augment()`/vista-liviana ya usado en `edit()` — solo faltaba aplicarlos
  consistentemente.
  Cobertura nueva: 5 tests en `tests/unit/Modules/Cms/Services/BlockTypeOptionsResolverTest.php`
  (incluye uno que prueba que `rawIndexed()` nunca llama a las APIs de formularios/colecciones/
  páginas/entradas).
  Verificado: `composer analyse` ✅ · `composer format:check` ✅ · 740/740 tests ✅ (735 previos
  + 5 nuevos, 1 skip preexistente sin relación) · verificado visualmente en
  `/admin/cms/pages/12/blocks` (4 bloques, badges de idioma y botones Editar/Eliminar intactos).

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
