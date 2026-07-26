# Auditoría de columnas de traducciones

## Seguimiento de implementación — 2026-07-20

- El plan ejecutable quedó documentado en [`../../../docs/plans/2026-07-20-translation-workbench-plan.es.md`](../../../docs/plans/2026-07-20-translation-workbench-plan.es.md) y enlazado desde ambos `TASKS`.
- La auditoría dejó de tener bloques independientes de rutas: ahora usa un resolver único para generar la acción de edición.
- Las acciones incluyen `focus_lang` y `return_to`, por lo que la pantalla de edición puede abrir directamente el idioma pendiente y retornar al workbench.
- Se cubrieron páginas, menús, settings, categorías, tags, colecciones, entradas, formularios, ítems de menú, campos de formulario y bloques de páginas/entradas.
- Validación ejecutada: `npm run lint:js`, `npm run build:js` y `vendor/bin/phpunit --filter TranslationBadgesViewTest --testdox`.

## 1. Objetivo

Verificar que las columnas de traducciones funcionen correctamente en navegación, visualización y cálculo de estado para las tablas CMS del panel admin.

## 2. Entorno

- Admin: `http://localhost:8182`
- Usuario: credenciales del README raíz
- Tablas revisadas: páginas, entradas, colecciones, categorías, etiquetas, formularios, menús y configuraciones.

## 3. Registro del proceso

1. Se inspeccionó la implementación compartida `components/table/translation_badges` y la función Alpine `translationStatus`.
2. Se confirmó que las ocho tablas solicitan `include_translations=1` en sus endpoints.
3. Se probó la navegación de las ocho rutas en el navegador autenticado; todas cargaron sin mensaje de error.
4. Se detectó y corrigió el caso del idioma predeterminado: su contenido vive en los campos base de la fila, no en `row.translations`.
5. Se recompiló JavaScript y se ejecutaron lint JS y lint PHP.

## 4. Hallazgos

### Corregido: idioma base marcado como faltante

- Síntoma: en Configuración, ES aparecía rojo aunque `setting_value` tenía contenido.
- Causa: la validación buscaba ES únicamente dentro de `row.translations`; el API entrega ES como valor canónico.
- Corrección: `translationStatus` recibe el indicador de idioma predeterminado y valida los campos base de `row`.
- Evidencia en vivo: `site_name`, `site_title`, `site_tagline` y `site_description` muestran ES y EN verdes; FR rojo cuando está vacío.

### Estado visual de las tablas

La auditoría de las páginas cargadas mostró badges y estados en todas las rutas:

| Tabla | Badges observados | Completos | Incompletos | Faltantes | Error visible |
|---|---:|---:|---:|---:|---|
| Páginas | 57 | 19 | 0 | 38 | No |
| Entradas | 15 | 10 | 0 | 5 | No |
| Colecciones | 6 | 2 | 0 | 4 | No |
| Categorías | 12 | 8 | 0 | 4 | No |
| Etiquetas | 6 | 4 | 0 | 2 | No |
| Formularios | 6 | 2 | 0 | 4 | No |
| Menús | 9 | 3 | 0 | 6 | No |
| Configuraciones | 21 | 14 | 0 | 7 | No |

Los faltantes observados corresponden a idiomas secundarios sin contenido; no se deben interpretar como errores por sí mismos.

## 5. Correcciones aplicadas

- Se añadió soporte para el idioma base en `src/js/components/remoteTable.js`.
- Se pasó el indicador `is_default` desde el componente PHP de badges.
- Se mantuvo la navegación mediante `?focus_lang={id}`.
- Se recompiló `public/assets/js/app.js`.

## 6. Validaciones ejecutadas

- `npm run build:js` — correcto.
- `npm run lint:js` — correcto.
- `php -l app/Views/components/table/translation_badges.php` — correcto.
- Navegación y carga en las ocho tablas — correctas.

## 7. Recomendaciones UX

- Añadir una leyenda visible: verde = completa, amarillo = incompleta, rojo = faltante.
- Mostrar un resumen por fila, por ejemplo `2/3 idiomas completos`, para evitar depender solo del color.
- Añadir filtros `Con faltantes`, `Incompletas` y `Completas`.
- Usar códigos accesibles junto con color y tooltip; no depender exclusivamente del rojo/verde.
- Cambiar el tooltip de “Traducción faltante” por un mensaje accionable como “Falta contenido en Francés — editar”.
- Considerar un indicador de carga mientras llegan las traducciones, evitando que un estado temporal se confunda con “faltante”.
- Añadir pruebas E2E para: idioma base completo, idioma secundario completo, incompleto, faltante y clic que abre el idioma correcto.

## 8. Pendiente

- La navegación por clic se validó por contrato de código (`editUrl` + `focus_lang`) y por controladores que consumen ese parámetro; conviene automatizar el clic real en Playwright/E2E.
- No se modificaron datos para fabricar estados incompletos; los casos amarillos requieren un registro con campos parcialmente llenos.

## 9. Resumen final

Las ocho columnas revisadas cargan correctamente y reciben traducciones desde el API. El idioma predeterminado se valida desde los campos canónicos del recurso y ya no se marca como faltante en los detalles. La auditoría y el navegador integrado confirmaron que ES queda completo, mientras FR permanece pendiente cuando realmente no tiene contenido. La principal mejora restante es reforzar la UX con leyenda, filtros y resumen por fila.

### Corrección adicional validada el 2026-07-20

El detalle de Configuración no estaba pasando los idiomas activos a la vista; por ello su panel no se renderizaba. `SettingController::show()` ahora carga los idiomas activos y el componente compartido excluye explícitamente el idioma predeterminado de la búsqueda de filas de traducción. Verificación real: `/admin/cms/settings/1` muestra únicamente `FR Faltante (valor)` y abre `/admin/cms/settings/1/edit?focus_lang=3`.

## 10. Recomendaciones de flujo editorial detectadas al navegar

La pantalla `Auditoría de Traducciones` ya es el mejor punto de partida para un flujo editorial, pero sus enlaces `Traducir` navegan a la edición sin enviar el idioma pendiente (`focus_lang`). Además, muestra nombres técnicos como `Page #1 (Type: home)` en vez del título visible. Se recomienda convertirla en una bandeja de trabajo: cada fila debe abrir directamente el idioma pendiente y mostrar el título real, el tipo de recurso y los campos faltantes.

También conviene agregar acciones de lote por idioma —por ejemplo, `Traducir todo lo pendiente en FR`— con selección de registros, progreso y estado `Borrador / En revisión / Publicado`. Una traducción automática, si se agrega, debe quedar como borrador para revisión humana y nunca publicarse silenciosamente.
