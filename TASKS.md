# TASKS — teatromuseo-admin

> Trabajo abierto del Admin. Seguimiento cross-repo:
> [`../TASKS.md`](../TASKS.md). Cierres históricos:
> [`TASKS_ARCHIVE.md`](TASKS_ARCHIVE.md).

## ✅ Completadas

- [x] **ADM-DASH-02 — Dashboard cross-domain y datos confiables** — cerrada
  2026-08-11. El dashboard ahora consume Hub, CMS, Catálogo y Eventos desde
  una lectura cacheada versionada, muestra actividad cross-domain y representa
  cada fuente no disponible como estado explícito, nunca como cero.

- [x] **ADM-DASH-01 — Dashboard administrativo resistente** — cerrada
  2026-08-11. Entrega agregada y protegida contra fan-out, reintentos y
  carreras de caché. Rollout y smoke de producción documentados en
  [`docs/DEPLOYMENT.md`](docs/DEPLOYMENT.md).

## 🔴 En progreso

## 🟡 Próximo

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
