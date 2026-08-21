# TASKS — teatromuseo-admin

> Trabajo abierto del Admin. Seguimiento cross-repo:
> [`../TASKS.md`](../TASKS.md). Cierres históricos:
> [`TASKS_ARCHIVE.md`](TASKS_ARCHIVE.md).

## ✅ Completadas

_(vacío — cierres hasta 2026-08-19 en [`TASKS_ARCHIVE.md`](TASKS_ARCHIVE.md))_

## 🟡 Próximo

### Autorización editorial por recurso en CMS (2026-08-20) — ver `../docs/plan/2026-08-20-plan-autorizacion-editorial-por-recurso-cms-v2.md`

Depende de `CMS-ACCESS-06` (`teatromuseo-cms-domain`, API de grants) y, para
que los workspaces reflejen el ámbito, de `CMS-ACCESS-07` (`teatromuseo-bff`).

- [ ] **CMS-ACCESS-08 — `CmsAccessApiService` + panel de acceso.** Cliente
  siguiendo el patrón `ResourceApiService` (referencia: `PageApiService`),
  sin DTOs tipados (consistente con `bin/make-module.sh`, que no los genera).
  Selects de usuario vía **`IamLookups::users()`**
  (`app/Modules/Iam/Support/IamLookups.php`) — no `UserApiService`
  directamente, que solo expone `assignableRoles()`. Panel en los `show` de
  páginas y colecciones, reutilizando layout existente; en colecciones
  etiquetado como "Acceso a entradas de la colección" (no editar el
  esquema); en entradas, ámbito heredado de solo lectura, sin ACL local.
  Ocultar panel/edición vía `has_permission('cms.access.read'/'.write')` —
  mismo helper que ya usa el resto del Admin, sin variante nueva. Único
  código genuinamente nuevo: manejo de 409 por `expected_revision`
  (`ApiClient.php` no distingue hoy 403/409 de otros errores). i18n `es`/`en`
  con la convención plana ya usada en `app/Modules/Cms/Language/*/Pages.php`.
  Nota de coordinación con `FRONT-01e` ya dejada más abajo en este archivo.

### Saneamiento arquitectónico heredado (prioridad 2)

- [ ] **FRONT-01e** — Unificar los modismos de autorización de los módulos.
  El panel de acceso editorial de
  [`docs/plan/2026-08-20-plan-autorizacion-editorial-por-recurso-cms-v2.md`](../docs/plan/2026-08-20-plan-autorizacion-editorial-por-recurso-cms-v2.md)
  §11.4 se construye reutilizando al máximo `has_permission()`/`IamLookups`/
  `ResourceApiService` para no sumar un modismo evitable; cuando esta tarea se
  diseñe, debe tomar ese panel como uno de los casos concretos a generalizar,
  no descubrirlo después.
- [ ] **FRONT-01f** — Unificar rutas de vistas y parciales repetidos.
- [ ] **FRONT-01g** — Migrar Alpine/CSP sin dejar un estado mixto ni relajar
  `script-src`/`style-src`; requiere migración y QA de las vistas.
- [ ] **DEAD-02** — Eliminar archivos rastreados sin consumidor, plantillas de
  entorno divergentes y componentes duplicados, tras verificar referencias.
- [ ] **DOC-01** — Eliminar la deriva de nombres starter/builder restante.
- [ ] **TRN-006** — Definir estados editoriales por idioma, relación con
  `status` y roles/permisos antes de migrar o construir UI. **Deslinde:** eje
  distinto del plan de autorización editorial por recurso
  (`../docs/plan/2026-08-20-plan-autorizacion-editorial-por-recurso-cms-v2.md`)
  — TRN-006 es workflow de estado editorial *por idioma* de un recurso ya
  accesible; el plan de autorización decide *qué recurso* es accesible para
  quién. Ninguno bloquea al otro; no fusionar su diseño ni su UI.
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
