# TASKS — teatromuseo-admin

> Trabajo abierto del Admin. Seguimiento cross-repo:
> [`../TASKS.md`](../TASKS.md). Cierres históricos:
> [`TASKS_ARCHIVE.md`](TASKS_ARCHIVE.md).

## ✅ Completadas

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
  documenta `BFF_API_BASE_URL`, `/me/admin-dashboard`, responsabilidades BFF /
  Admin y el comportamiento stale/unavailable.

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

## 🔴 En progreso

_(sin tareas en curso)_

## 🟡 Próximo

### Dashboard vía BFF (propuesta 2026-08-16) — ver `../docs/plan/2026-08-16-plan-admin-dashboard-via-bff.md`

El dashboard ya es un agregador resiliente (`ADM-DASH-01`/`02`) que hace 4
llamadas de dominio por su cuenta (hub/cms/catalog/event) con caché, lock y
degradación por fuente. Este bloque mueve esa orquestación al BFF, que hoy
tiene el patrón (`aggregate()`/introspect) pero ningún consumidor real.
Depende de que `BFF-DASH-01..06` (ver `teatromuseo-bff/TASKS.md`) esté
operativo en local; la Fase B quedó verificada antes de iniciar `ADM-DASH-03`.
### Saneamiento arquitectónico heredado (prioridad 2)

- [ ] **CFG-02** — Reconstruir `.env.example` desde las variables realmente
  leídas por `ApiClient`, `DomainApiClient`, BFF y sesión.
- [ ] **CFG-05** — Hacer que `composer quality` ejecute tests y endurecer
  `phpunit.xml.dist` frente a warnings/deprecations.
- [ ] **CFG-06** — Reparar y verificar la instalación de `pre-push` y el
  `core.hooksPath`.
- [ ] **FRONT-01b** — Resolver la propiedad de los namespaces de idioma
  duplicados y sus 12 colisiones conocidas.
- [ ] **FRONT-01c** — Extraer las cadenas incrustadas de las previews a i18n.
- [ ] **FRONT-01d** — Sacar la lógica de negocio de los `<script>` inline al
  build de frontend.
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
