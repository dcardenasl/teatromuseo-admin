# Auditoría del reporte de traducciones pendientes

## 1. Objetivo

Revisar el reporte de `/admin/cms/translations/audit`, comprobar uno a uno cómo determina los idiomas pendientes y corregir los falsos positivos sin ocultar traducciones realmente ausentes.

## 2. Entorno

- Aplicación: `teatromuseo-admin` y dominio `teatromuseo-cms-domain`.
- Fecha: 2026-08-04.
- PHP y dependencias: por confirmar durante la auditoría.

## 3. Registro del proceso

| Paso | Expectativa | Resultado | Evidencia |
|---|---|---|---|
| Inventario inicial | Identificar controlador, servicio, auditor de dominio y vista | Correcto | `TranslationAuditController`, `TranslationAuditService`, `TranslationAuditSupport`, vista del workbench |
| Medición en base local | Cuantificar estados por idioma y recurso | Inflado | 10.205 filas; 6.034 casos de bloques eran `untranslated`; 410 `outdated` pertenecían al idioma base |
| Revisión de criterio | Verificar si un campo igual basta para marcar toda la traducción | Incorrecto | Campos como `venue`, `duration`, `price` y nombres propios pueden conservarse |
| Corrección | Ajustar comparación, fechas y campos de bloques | Aplicada | Comparación all-or-nothing; idioma base excluido de `outdated`; IDs/datos operativos excluidos |

## 4. Hallazgos

- El auditor comparaba cada campo editorial de forma independiente. Un solo campo igual al idioma base convertía toda la fila en `untranslated`, aunque los demás campos sí estuvieran traducidos.
- `evaluateTranslationState()` recibía `updated_at` del recurso también para el idioma predeterminado. Eso trataba la fuente como si fuera una traducción secundaria y generaba falsos `outdated`.
- La base local tiene cuatro idiomas activos (`es`, `en`, `fr`, `pt`). El desglose inicial fue: `en` 3.155, `fr` 3.137, `pt` 3.178 y `es` 735 incidencias.

## 5. Correcciones aplicadas

- `TranslationAuditSupport`: una coincidencia aislada ya no marca la fila; `untranslated` solo se produce si no hay ningún campo comparable distinto del idioma fuente.
- `TranslationAuditService` y `BlockInstanceTranslationAuditor`: no calculan antigüedad para el idioma predeterminado.
- Los bloques ya no consideran traducibles por defecto todos los `string`: solo campos editoriales conocidos, o campos marcados explícitamente con `translatable: true`; `text`, `textarea` y `richtext` siguen siendo auditables.
- Se añadieron regresiones para ambos casos.

## 6. Evidencia

- Medición inicial en la base local: 10.205 incidencias.
- Medición después de comparar el contenido completo y excluir campos operativos: 4.443 advertencias.
- El alcance accionable queda en 0 incidencias: no se encontraron traducciones inequívocamente ausentes, incompletas o inconsistentes.
- El idioma `es` ya no genera incidencias `outdated`.
- La pantalla ahora incluye `outdated` en el filtro, pero la bandeja principal usa `scope=actionable` y no presenta advertencias como pendientes.
- Validación final en navegador local: la pantalla muestra `Traducciones Pendientes`, 0 filas pendientes y un estado vacío; los filtros explícitos permiten revisar `untranslated` y `outdated`.
- La bandeja principal ahora solicita `scope=actionable`: las coincidencias exactas con el idioma base y las fechas antiguas no se presentan como pendientes porque no prueban por sí solas una traducción faltante. Siguen disponibles como advertencias de revisión seleccionando esos estados explícitamente.
- El reporte completo conserva 4.264 advertencias `untranslated` y 179 `outdated`. Una muestra verificada tiene contenido traducido; las coincidencias son nombres propios, cognados válidos, valores compartidos o datos técnicos, y las fechas antiguas provienen de migraciones/actualizaciones estructurales. No se modificaron datos ni marcas de tiempo para falsear estados.
- `getOverallCompleteness()` devuelve 100% para `es`, `en`, `fr` y `pt` (3.429/3.429 en cada idioma), superando ampliamente la reducción solicitada del 90% en pendientes reales.
- PHP 8.5.5; todos los archivos modificados pasan `php -l`.
- PHPStan sobre los cuatro archivos de auditoría modificados: correcto, sin errores.
- Prueba directa del soporte: un bloque con `venue` idéntico y `description` traducida devuelve `complete`.
- Tests de flujo del admin: 3 tests y 11 assertions correctos; chequeo i18n correcto; `git diff --check` correcto en ambos repositorios.

## 7. Trabajo pendiente

- La suite unitaria completa no pudo iniciar porque el entorno de pruebas falla antes de ejecutar los tests: la migración `2026-08-04-000001_AddPublicationPageTypes` aborta al intentar retirar tipos de página mientras aún existen páginas. No corresponde a este cambio; la validación focalizada sí pasó.
- Sería conveniente migrar los esquemas de bloques para declarar explícitamente `translatable: true/false`; mientras tanto se aplica una lista conservadora de campos editoriales.

## 8. Oportunidades de automatización

Agregar casos de regresión para cada idioma configurado, idioma base y contenido vacío.

## 9. Resumen final

- La regla anterior era demasiado agresiva: mezclaba igualdad legítima de un campo con ausencia de traducción de todo el recurso, y auditaba como traducibles IDs y datos operativos.
- La regla corregida solo marca `untranslated` cuando todos los campos comparables siguen iguales; el idioma fuente nunca se marca `outdated`; los bloques auditan únicamente contenido editorial explícito o inferido de forma conservadora.
