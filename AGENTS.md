# AGENTS.md — Convenciones para `teatromuseo-admin`

## Propósito y Límites

Esta es la aplicación **Administrador** (interfaz de usuario renderizada en servidor en el puerto `8182`). Se conecta al Hub (`8180`) y a las APIs de dominio para la orquestación y gestión editorial.

- **Completamente Stateless:** No tiene base de datos local, migraciones ni modelos locales.
- **Autenticación Centralizada:** Las credenciales de usuario se envían al Hub para obtener el JWT. Este token se almacena únicamente en la sesión de PHP (servidor) y se propaga en los headers de autorización de cada petición API.
- **Consumo de APIs vía Clientes:** Toda llamada HTTP debe resolverse a través de `ApiClient` (para el Hub) o `domainApiClient` (para dominios).

---

## Estructura y Capas de la Aplicación

```text
Routes → Modules (Controllers) → Services (BaseApiService / ResourceApiService) → ApiClient / DomainApiClient
```

*   `app/Modules/` — Contiene los módulos funcionales autónomos. Cada uno agrupa:
    *   `Controllers/` — Controladores del módulo (extienden de `BaseWebController`).
    *   `Services/` — Clientes de API encapsulados (extienden de `BaseApiService` o `ResourceApiService`).
    *   `Views/` — Vistas HTML/Blade-like localizadas.
    *   `Requests/` — Validación de peticiones HTTP en servidor.
    *   `Config/Routes.php` — Rutas locales del módulo.
*   `app/Libraries/ApiClient.php` — Cliente base para la API del Hub con autorefresh de JWT.

---

## Guardrails de Consistencia (Tests de Arquitectura)

Para evitar atajos de programación e inyecciones de código que rompan el diseño, el proyecto ejecuta pruebas automáticas en `composer quality` bajo `tests/unit/Architecture/`:

1.  **`StatelessArchitectureTest` (Cero Base de Datos/Modelos):** Escanea `app/` para asegurar que nadie cree o importe modelos locales (`App\Models\`, `CodeIgniter\Model`) ni instancie conexiones a bases de datos (`Database::connect()`).
2.  **`ControllerServiceDependencyTest` (Separación Controlador-Servicio):** Asegura que los controladores del administrador deleguen la comunicación externa en clases de servicio en lugar de llamar al cliente HTTP de bajo nivel directamente.
    *   *Excepciones permitidas:* `WizardController` (para cargar esquemas dinámicos de bloques) y `AuthController` (para limpiar tokens de sesión durante el logout).

---

## Anti-patrones Prohibidos

1.  **Crear base de datos local:** Prohibido crear tablas locales, modelos de datos de CI4 o correr migraciones locales.
2.  **Llamar a la API directamente en el Controlador:** No se debe inyectar el cliente API de bajo nivel directamente en las acciones del controlador; se debe encapsular en un método del servicio del módulo.
3.  **Hardcodear cadenas en las vistas:** Utilizar siempre el helper `lang()` para toda etiqueta o mensaje visible para asegurar la localización.
4.  **Bypassear la validación de FormRequest:** Usar las clases `app/Modules/*/Requests/*` para procesar y filtrar datos antes de enviarlos a las APIs.
