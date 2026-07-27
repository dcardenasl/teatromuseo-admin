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
