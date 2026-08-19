# TASKS — teatromuseo-admin

> Trabajo abierto del Admin. Seguimiento cross-repo:
> [`../TASKS.md`](../TASKS.md). Cierres históricos:
> [`TASKS_ARCHIVE.md`](TASKS_ARCHIVE.md).

## ✅ Completadas

- [x] **ADM-BFF-11 — Carga no bloqueante del detalle de archivos.** Cerrada
  2026-08-19. La vista entrega metadata y preview sin esperar el fan-out de
  usos; la verificación se carga una vez contra el BFF, actualiza el borrado
  sólo con snapshot completo y vacío, y usa la URL directa del archivo cuando
  no hay variante. Verificado con `composer quality` (829 tests, 3.442
  assertions, 1 skip) y 106 tests JS.

- [x] **CMS-EDITOR-02 — Flujos y guards de Admin para `cms-editor`.** Cerrada
  2026-08-18. Entries, collections, redirects y forms separan `.write` de
  `.admin` para eliminación; bloques de entries y traducciones de archivos
  usan los permisos correctos; Files quedó protegido por `files.read/write`;
  sidebar y wizard ocultan acciones fuera del alcance. Verificado con
  `composer quality` (826 tests, 3.437 asserts, 1 skip).

- [x] **ADM-BFF-01 — Consumir `analytics`/`translations` del snapshot BFF.**
  Cerrada 2026-08-17. `DashboardDataService` traduce las nuevas fuentes,
  incrementa la versión de caché y conserva sus estados; ambos widgets leen
  el snapshot común y ya no disparan llamadas directas al CMS. Verificado con
  `composer test` y `composer quality` (798 tests, 3.312 assertions, 1
  skipped; PHPStan, CS-Fixer, i18n y fixture policy verdes).

- [x] **ADM-BFF-02 — Verificación e2e + retiro acotado.** Cerrada
  2026-08-17. `rg` confirma cero referencias directas a
  `AnalyticsApiService`/`TranslationAuditApiService` desde el Dashboard;
  `CLAUDE.md` documenta que ambos widgets comparten el snapshot BFF y que
  los servicios se conservan para sus pantallas independientes. El smoke
  visual se intentó en `localhost:8182`, pero el entorno aislado bloqueó la
  conexión al proceso local; las suites y quality del Admin quedaron verdes.

- [x] **ADM-DASH-03 — Cliente BFF autenticado.** Cerrada 2026-08-16.
  `BffApiClient::getAdminDashboard()` reenvía el bearer a
  `/api/v1/me/admin-dashboard`, con `BFF_API_BASE_URL` configurado en local y
  ejemplo; verificado con 1 test y 10 asserts.

- [x] **ADM-DASH-04 — Migrar `DashboardDataService::read()`.** Cerrada
  2026-08-16. El servicio usa una única lectura BFF, conserva el shape
  `sections/source.state`, cache/lock/stale/cooldown y sus tests; verificado
  con 786 tests / 2763 asserts y quality verde.

- [x] **ADM-DASH-05 — Verificación end-to-end y retiro del código viejo.**
  Cerrada 2026-08-16. Dashboard visual verificado con stack completo y datos
  previos; al detener Eventos solo esa sección quedó no disponible, se restauró
  el proceso y se retiró el wiring legacy del dashboard en commit separado.

- [x] **ADM-DASH-06 — Documentación.** Cerrada 2026-08-16. `CLAUDE.md`
  documenta `BFF_API_BASE_URL`, `/me/admin-dashboard`, el seam administrativo
  `AdminRead` SELECT-only del BFF, responsabilidades BFF / Admin y el
  comportamiento stale/unavailable.

- [x] **ADM-DASH-02 — Dashboard cross-domain y datos confiables** — cerrada
  2026-08-11. El dashboard ahora consume Hub, CMS, Catálogo y Eventos desde
  una lectura cacheada versionada, muestra actividad cross-domain y representa
  cada fuente no disponible como estado explícito, nunca como cero.

- [x] **ADM-DASH-01 — Dashboard administrativo resistente** — cerrada
  2026-08-11. Entrega agregada y protegida contra fan-out, reintentos y
  carreras de caché. Rollout y smoke de producción documentados en
  [`docs/DEPLOYMENT.md`](docs/DEPLOYMENT.md).

- [x] **ADM-SEC-01 — Autorización declarativa en módulos Event y Catalog** —
  cerrada 2026-08-12. Los módulos operativos usan autenticación más permiso
  explícito por endpoint; se retiraron los checks duplicados, se alineó la UI y
  se añadieron regresiones funcionales y una guarda arquitectónica.

- [x] **FRONT-01b — Propiedad de namespaces de idioma.** Cerrada 2026-08-16.
  Se eliminaron las colisiones entre catálogos raíz y modulares, se separaron
  `Pages`, `Blocks` y `ContentTranslations`, se normalizaron los consumidores y
  se añadió una guarda de unicidad de namespaces con paridad `es`/`en`.

- [x] **ADM-TABLEUX-01 — Plan de vistas de tabla y densidad.** Cerrada
  2026-08-16. Registrado
  [`docs/plan/2026-08-16-plan-admin-vistas-tabla-densidad.md`](../docs/plan/2026-08-16-plan-admin-vistas-tabla-densidad.md)
  y las tareas `ADM-TABLEUX-02..09` para su ejecución, tras verificar contra
  el código real que Files ya implementa un `viewMode` local duplicable
  y que la única vía sin `!important` para la densidad es
  una regla CSS fuera de `@layer`.

- [x] **ADM-TABLEUX-02 — Estado compartido `viewMode`/`density`.** Cerrada
  2026-08-16. `remoteTableFactory` centraliza preferencias por módulo en
  `sessionStorage`, con validación, tolerancia a almacenamiento no disponible
  y regresiones unitarias JS.

- [x] **ADM-TABLEUX-03 — CSS de densidad sin `!important`.** Cerrada
  2026-08-16. Se añadieron reglas compacta/amplia fuera de `@layer`; `md`
  conserva el padding existente y no se introdujo una prioridad forzada.

- [x] **ADM-TABLEUX-04 — Flags en `table_toolbar.php`.** Cerrada 2026-08-16.
  El parcial acepta controles de vista y densidad apagados por defecto, y solo
  renderiza cada grupo cuando la vista lo solicita explícitamente.

- [x] **ADM-TABLEUX-05 — Migrar Files al estado compartido.** Cerrada
  2026-08-16. Files conserva su galería de miniaturas, pero toma `viewMode` y
  `density` de `remoteTableFactory` con clave propia por módulo.

- [x] **ADM-TABLEUX-06 — Vista de tarjetas de Users.** Cerrada 2026-08-16.
  Users declara una tarjeta con nombre completo, estado, email, roles, fecha y
  acciones explícitas, sin detectar campos por heurística.

- [x] **ADM-TABLEUX-07 — Densidad en los 30 módulos restantes.** Cerrada
  2026-08-16. Cada listado conserva su tabla, recibe densidad compartida,
  toolbar explícito y mode aislado; no se habilitaron tarjetas fuera de
  Users.

- [x] **ADM-TABLEUX-08 — Verificación e2e.** Cerrada 2026-08-16.
  `composer quality` pasó con 798 tests / 3317 asserts; los tests JS pasaron
  con 101 casos y ESLint quedó verde. El smoke visual real en `localhost:8182`
  verificó Files, Users, Audit y Roles: cambio de densidad, persistencia al
  recargar la misma pestaña y valores por defecto en una pestaña nueva.
- [x] **ADM-TABLEUX-09 — Documentación.** Cerrada 2026-08-16. Actualizados
  `docs/FRONTEND.md`, `docs/es/FRONTEND.md`, `docs/COMPONENTS.md` y el
  Consistency Contract de `CLAUDE.md` con el patrón `remoteTable`, `mode`,
  preferencias por pestaña, flags del toolbar y mapeos explícitos de tarjetas.

## 🟡 Próximo

### Lecturas compuestas del Admin vía BFF (2026-08-16) — ver `../docs/plan/2026-08-16-plan-admin-lecturas-compuestas-via-bff.md`

Contraparte Admin de `teatromuseo-bff/TASKS.md` (`BFF-ADMINREAD-05..14`). No
empezar la mitad Admin de una feature hasta que su mitad BFF esté cerrada y
verificada — mismo orden que `BFF-DASH`/`ADM-DASH`. Los retiros de código
legado (`AnalyticsApiService`, llamadas directas de `TranslationAuditApiService`,
adapters directos de Event) solo proceden después de confirmar con `rg` que
no quedan otros consumidores; las escrituras de cada dominio siguen yendo
directas a su dominio propietario en todos los casos.

**Feature 1 — Dashboard: widgets de analytics y traducciones completos**
(depende de `BFF-ADMINREAD-05/06`)


**Feature 2 — Analytics administrativo compuesto**
(depende de `BFF-ADMINREAD-07/08`)

## ✅ Completadas

- [x] **ADM-BFF-03 — `BffApiClient::getAdminAnalytics()`.** Cerrada
  2026-08-17. Se añadió el método autenticado con período y retry budget, y
  `AnalyticsController` ahora traduce el aggregate versionado del BFF al
  shape existente de las vistas mediante un único adapter local. La prueba
  de flujo confirma una sola llamada BFF por carga.

- [x] **ADM-BFF-04 — Verificación e2e + retiro de `AnalyticsApiService`.**
  Cerrada 2026-08-17. `rg` confirma cero consumidores del servicio y se
  retiraron su factory y archivo; Analytics conserva solo el cliente BFF.
  La prueba de flujo valida payload equivalente con una llamada, y quedaron
  verdes `composer test` y `composer quality` (800 tests, 3.328 assertions,
  1 skipped; PHPStan, CS-Fixer, i18n y fixture policy verdes). El smoke visual
  en `localhost:8182` se intentó, pero el entorno aislado bloqueó la conexión.

**Feature 3 — Usos de archivos cross-domain**
(depende de `BFF-ADMINREAD-09/10/11` — en particular, no arrancar hasta que
`BFF-ADMINREAD-09` confirme el diseño de deduplicación)

- [x] **ADM-BFF-05 — Adapter BFF en `FileApiService`.** Cerrada 2026-08-17.
  `FileApiService::usages()` hace una única llamada a
  `/api/v1/me/admin-files/{fileId}/usages`; se retiró su `DomainApiClient` y
  el BFF conserva la deduplicación estable de usos CMS con contexto.
- [x] **ADM-BFF-06 — Verificación e2e.** Cerrada 2026-08-17. La suite
  funcional cubre la respuesta incompleta y bloquea el borrado hasta completar
  las fuentes; `composer quality` quedó verde (804 tests, 3.347 assertions,
  1 skipped; PHPStan, CS-Fixer, i18n y fixture policy verdes). El smoke visual
  real en `localhost:8182` se intentó, pero el entorno aislado bloqueó la
  conexión al proceso local.

**Feature 4 — Lookups administrativos de Event**
(depende de `BFF-ADMINREAD-12/13`)

- [x] **ADM-BFF-07 — Adapter BFF de lookups + migrar Occurrence.** Cerrada
  2026-08-17. Se añadió `EventLookupBffAdapter`; Occurrence carga `events` y
  `venues` en un único bundle BFF por request y conserva `create/update/delete`
  directos al Event domain. `composer quality` quedó verde (809 tests, 3.371
  assertions, 1 skipped; PHPStan, CS-Fixer, i18n y fixture policy verdes).

- [x] **ADM-BFF-08 — Migrar `ticket_type`, `ticket`, `booking`,
  `event_reference`.** Cerrada 2026-08-17. Los cuatro controladores consumen
  sus bundles BFF (`ticket_type`, `ticket`, `booking`, `event_reference`) con
  una lectura por pantalla; las escrituras permanecen directas al Event
  domain.
- [x] **ADM-BFF-09 — Retiro + verificación.** Cerrada 2026-08-17. `rg` confirma
  cero consumidores y se retiraron `events()`/`venues()`/`occurrences()`/
  `bookings()`/`ticketTypes()` de los servicios y contratos directos. La UI
  distingue catálogo vacío exitoso (sin alerta) de fuente BFF no disponible
  (alerta ámbar); la diferencia está cubierta por `OccurrenceFlowTest`.
  `composer quality` quedó verde (810 tests, 3.376 assertions, 1 skipped;
  PHPStan, CS-Fixer, i18n y fixture policy verdes). El smoke visual real en
  `localhost:8182` se intentó, pero el entorno aislado bloqueó la conexión.

**Feature 5 — Bootstrap de editores CMS**

- [x] **ADM-BFF-10 — Consumo de bootstraps CMS aprobados.** Cerrada
  2026-08-17 tras la medición runtime de `BFF-ADMINREAD-14`. Entry, Page, Menu
  e Identity consumen un adapter BFF por pantalla con fallback directo; las
  escrituras no cambiaron. BlockInstance consume el workspace BFF para páginas
  y entradas en sus pantallas de lectura; Wizard consume `wizard-bootstrap`
  con fallback directo.

### Saneamiento arquitectónico — cierres 2026-08-17

- [x] **CFG-02 — `.env.example` alineado con el runtime.** Se reconstruyó el
  ejemplo desde las variables realmente leídas por los clientes Hub, dominio,
  BFF, Web, sesión, caché, CSP y límites operativos; se conservaron aliases
  legacy explícitos como compatibilidad.
- [x] **CFG-05 — Quality gate endurecido.** `composer quality` conserva el
  test suite, `phpunit.xml.dist` falla ante warnings y deprecations, y el gate
  completo quedó verde con 814 tests, 3.386 aserciones y 1 skip.
- [x] **CFG-06 — Pre-push verificable.** El hook activo usa `.husky`, delega en
  `scripts/check-pre-push.sh` y ejecuta quality, lint JS y tests JS; el hook
  alternativo `pre-push` quedó alineado.
- [x] **FRONT-01c — Previews con i18n.** Las cadenas visibles de las previews
  se movieron a `BlockPreview` con paridad `es`/`en`; `i18n-check` quedó verde.
- [x] **FRONT-01d — Lógica frontend fuera de vistas.** Builders, formularios,
  timers, errores y preview de bloques viven en módulos ES registrados en el
  bundle; las vistas conservan solo datos declarativos y no agregan scripts
  inline nuevos. Lint, 21 archivos/104 tests JS, build y contratos de vistas
  quedaron verdes.

### Dashboard vía BFF — cerrado 2026-08-16

El dashboard conserva su caché, lock y degradación por fuente en el Admin,
pero ahora hace una sola lectura autenticada a
`/api/v1/me/admin-dashboard`; el BFF concentra el resumen del Hub y las
lecturas directas, permission-aware y SELECT-only de CMS, Catálogo y Eventos.
Las tareas `BFF-DASH-01..06` y `ADM-DASH-03..06` quedaron cerradas y
verificadas; el detalle está en
`../docs/plan/2026-08-16-plan-admin-dashboard-via-bff.md`.
### Saneamiento arquitectónico heredado (prioridad 2)

- [ ] **FRONT-01e** — Unificar los modismos de autorización de los módulos.
- [ ] **FRONT-01f** — Unificar rutas de vistas y parciales repetidos.
- [ ] **FRONT-01g** — Migrar Alpine/CSP sin dejar un estado mixto ni relajar
  `script-src`/`style-src`; requiere migración y QA de las vistas.
- [ ] **DEAD-02** — Eliminar archivos rastreados sin consumidor, plantillas de
  entorno divergentes y componentes duplicados, tras verificar referencias.
- [ ] **DOC-01** — Eliminar la deriva de nombres starter/builder restante.
- [ ] **TRN-006** — Definir estados editoriales por idioma, relación con
  `status` y roles/permisos antes de migrar o construir UI.
- [ ] **ADM-DEP-002** — Actualizar `lint-staged` cuando el baseline Node 22
  permita ejecutar la migración con `npm audit`.

### Dependencias y conflictos

- El Admin no bloquea QA PublicRead, pero `FRONT-02`/cache invalidation y
  cualquier cambio en clientes de dominio deben conservar los scopes y contratos
  usados por Web.
- `FRONT-01g` es una migración transversal de frontend: no mezclarla con un fix
  puntual de CSP ni desplegarla parcialmente.
- `TRN-006` es diseño de producto; no crear migraciones/UI hasta que exista la
  decisión de estados y permisos.
- `DEAD-02` requiere comprobar referencias antes de borrar; el cutover público
  no autoriza eliminar archivos del Admin por proximidad temporal.

## 🏗️ Contratos de arquitectura

- El Admin usa `ApiClient` para el Hub y clientes de dominio para sus dominios;
  no accede a bases de datos de otras aplicaciones.
- Controllers delgados, permisos declarativos y cadenas localizadas.
- La sesión debe liberar el lock antes de ejecutar trabajo HTTP paralelo.
