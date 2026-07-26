window.ADMIN_GUIDE_MODULES = [
  {
    slug: 'escritorio', number: '01', group: 'Inicio y cuenta', title: 'Escritorio / Dashboard', nav: 'Escritorio', route: '/dashboard', image: '01-escritorio.png',
    objective: 'Orientarte sobre el estado general del proyecto y llevarte rápidamente a las áreas que requieren atención. Es una pantalla de diagnóstico y navegación, no un editor.',
    fields: [
      ['Usuarios y archivos totales', 'Cantidad de cuentas y activos administrados.', 'Úsalos como indicadores; abre el módulo correspondiente para operar.'],
      ['Resumen de contenido', 'Contadores de páginas, entradas, colecciones, menús, categorías, etiquetas, formularios y envíos.', 'Cada contador funciona como punto de entrada al detalle.'],
      ['Traducciones', 'Cobertura por idioma y pendientes de traducción.', 'Entra en Auditoría de Traducciones cuando haya faltantes.'],
      ['Salud del sistema', 'Hub API, Dominio y Sitio Web Público.', 'Si un servicio está fuera de línea, no publiques hasta entender la causa.'],
      ['Actividad y últimos archivos', 'Eventos recientes, métricas y activos agregados.', 'Sirven para reconocer cambios recientes, no para editarlos desde aquí.']
    ],
    steps: ['Inicia sesión por el host correcto.', 'Comprueba que Hub API, Dominio y Sitio Web Público estén en línea.', 'Revisa los indicadores de traducción y actividad.', 'Abre el contador o tarjeta relacionada con la tarea que necesitas.', 'Si algo está fuera de lo normal, registra la hora y consulta Auditoría antes de cambiar datos.'],
    can: ['Consultar el estado general.', 'Usar contadores y tarjetas como accesos directos.', 'Detectar si conviene detener una publicación.'],
    avoid: ['No crear, editar ni eliminar contenido desde esta pantalla.', 'No interpretar un contador como prueba de que una URL pública funciona.'],
    verify: ['El estado de los tres servicios principales es correcto.', 'La tarea que vas a realizar tiene un módulo de destino claro.', 'Si había una alerta, quedó registrada para seguimiento.'],
    permissions: 'El acceso al escritorio depende de la sesión y de los módulos que tu rol tenga habilitados.', related: ['auditoria', 'metricas', 'analytics', 'traducciones']
  },
  {
    slug: 'perfil', number: '02', group: 'Inicio y cuenta', title: 'Perfil', nav: 'Perfil', route: '/profile', image: '02-perfil.png',
    objective: 'Mantener tus datos personales y ejecutar acciones básicas de seguridad de tu propia cuenta.',
    fields: [
      ['Foto de perfil / Subir foto', 'Avatar que se muestra en el panel.', 'Usa una imagen apropiada y no un archivo confidencial.'],
      ['Nombre y apellido', 'Identidad visible en el Admin y en la auditoría.', 'Mantén el nombre reconocible para que otras personas sepan quién opera.'],
      ['Guardar cambios', 'Persiste los datos personales editados.', 'Comprueba el mensaje de confirmación antes de cerrar.'],
      ['Enviar enlace de reset', 'Inicia el restablecimiento de contraseña por email.', 'Verifica el destinatario y no solicites enlaces para otra persona.'],
      ['Reenviar verificación', 'Envía nuevamente el correo de verificación.', 'Úsalo solo si el estado de la cuenta sigue pendiente.']
    ],
    steps: ['Abre Perfil desde el menú superior.', 'Revisa nombre, apellido, email y estado.', 'Actualiza solo los datos propios que correspondan.', 'Guarda y verifica la confirmación.', 'Si necesitas recuperar acceso, usa el flujo de reset y no compartas el enlace recibido.'],
    can: ['Actualizar tu información personal.', 'Cambiar la foto de perfil.', 'Solicitar verificación o restablecimiento de tu cuenta.'],
    avoid: ['No editar datos de otra persona desde tu perfil.', 'No enviar enlaces de reset por chat o correo sin protección.'],
    verify: ['El nombre actualizado aparece en la cabecera.', 'El email de verificación llega al destinatario correcto.', 'La sesión sigue activa después de guardar.'],
    permissions: 'Usa el permiso personal <code>self.access</code>; no requiere administrar usuarios.', related: ['usuarios', 'auditoria']
  },
  {
    slug: 'archivos', number: '03', group: 'Inicio y cuenta', title: 'Archivos', nav: 'Archivos', route: '/files', image: '03-archivos.png',
    objective: 'Gestionar la biblioteca central que reutilizan páginas, entradas, bloques e identidad del sitio.',
    fields: [
      ['Subir Nuevo Archivo', 'Incorpora un original a la biblioteca.', 'Revisa formato, peso, nombre y posibles duplicados.'],
      ['Buscar y filtros', 'Filtra por texto, categoría, fecha, tamaño y tipo: imágenes, documentos, video o audio.', 'Combina pocos filtros y límpialos si la lista queda vacía.'],
      ['Ver / Descargar', 'Consulta metadatos o descarga el original.', 'Descargar no crea una copia administrada dentro del CMS.'],
      ['Variantes', 'Versiones derivadas que el sistema puede generar para distintos usos.', 'Comprueba en el detalle que existan y que abran correctamente; sus dimensiones dependen del archivo y de la configuración.'],
      ['Alt, pie y crédito', 'Metadatos editoriales y de accesibilidad.', 'El texto alternativo describe el contenido o función, no repite el nombre del archivo.'],
      ['Usado en / Papelera', 'Referencias y recuperación del archivo.', 'Revisa dependencias antes de eliminar definitivamente.']
    ],
    steps: ['Pulsa Subir Nuevo Archivo, selecciona el archivo y espera la confirmación.', 'Abre el detalle del archivo para revisar categoría, tipo, tamaño, dimensiones y variantes.', 'Edita allí el texto alternativo, pie de foto o crédito cuando corresponda.', 'Revisa Usado en antes de reutilizarlo o retirarlo.', 'Inserta el archivo desde Biblioteca en el bloque o contenido que lo necesite.', 'Si necesitas retirarlo, usa la papelera cuando esté disponible y deja la eliminación definitiva para una decisión explícita.'],
    can: ['Subir, consultar, descargar y organizar activos.', 'Editar metadatos.', 'Restaurar desde papelera cuando la instalación lo permita.'],
    avoid: ['No borrar definitivamente sin confirmar referencias.', 'No usar nombres de archivo como texto alternativo.', 'No subir contraseñas, API keys ni documentos personales innecesarios.'],
    verify: ['El archivo aparece en la biblioteca y en el tipo correcto.', 'Las variantes existen y abren correctamente.', 'La página o bloque donde se usa muestra la imagen esperada.'],
    permissions: 'Normalmente requiere <code>files.read</code> para consultar y <code>files.write</code> para subir o editar.', related: ['paginas', 'entradas', 'bloques', 'identidad-sitio']
  },
  {
    slug: 'usuarios', number: '04', group: 'Administración, observabilidad y acceso', title: 'Usuarios', nav: 'Usuarios', route: '/admin/users', image: '04-usuarios.png',
    objective: 'Crear cuentas y asignar a cada persona el mínimo de roles necesario para su trabajo.',
    fields: [
      ['Nombre y apellido', 'Identidad de la persona.', 'Cada uno requiere entre 2 y 100 caracteres.'],
      ['Email', 'Identificador y canal de acceso.', 'Es obligatorio y debe tener formato de email válido.'],
      ['Estado', 'Situación de la cuenta.', 'Confirma si la cuenta está activa, pendiente o deshabilitada.'],
      ['Roles', 'Conjunto de capacidades.', 'Usa el rol mínimo; si queda vacío al crear se aplica <code>user</code>.'],
      ['Administrar roles / Auditoría', 'Acciones sobre la cuenta y su historial.', 'Consulta antes de ampliar o retirar acceso.']
    ],
    steps: ['Abre Usuarios y busca primero si la persona ya existe.', 'Pulsa Nuevo usuario y completa nombre, apellido y email.', 'Asigna el rol mínimo según la matriz.', 'Guarda y confirma que el estado sea el esperado.', 'Pide a la persona iniciar sesión y verificar su acceso.', 'Revisa Auditoría si el cambio era sensible.'],
    can: ['Crear y editar cuentas.', 'Asignar roles autorizados.', 'Consultar actividad y retirar acceso según permiso.'],
    avoid: ['No crear duplicados por no haber buscado primero.', 'No asignar superadmin por comodidad.', 'No compartir una cuenta entre varias personas.'],
    verify: ['El email es único y correcto.', 'La persona ve solo los módulos que necesita.', 'El rol queda documentado y la auditoría registra el cambio.'],
    permissions: 'Crear/editar requiere <code>users.write</code>; las operaciones reservadas dependen de <code>users.admin</code> o del acceso superadministrador.', related: ['roles', 'roles-permisos', 'auditoria', 'perfil']
  },
  {
    slug: 'auditoria', number: '05', group: 'Administración, observabilidad y acceso', title: 'Auditoría', nav: 'Auditoría', route: '/admin/audit', image: '05-auditoria.png',
    objective: 'Reconstruir quién hizo qué, cuándo, desde dónde y con qué resultado.',
    fields: [
      ['ID', 'Identificador del evento.', 'Guárdalo cuando escales un incidente.'],
      ['Usuario y acción', 'Persona y operación registrada.', 'Filtra por usuario o acción para acotar la investigación.'],
      ['Entidad', 'Registro o módulo afectado.', 'Relaciona el evento con la página, entrada, rol o archivo concreto.'],
      ['IP y fecha', 'Origen y momento de la operación.', 'Usa la hora del servidor como referencia del incidente.'],
      ['Resultado y severidad', 'Éxito/error y nivel de importancia.', 'No confundas un evento informativo con un fallo operativo.'],
      ['Ver', 'Detalle completo del evento.', 'No borres registros durante una investigación.']
    ],
    steps: ['Define qué cambio buscas y su intervalo aproximado.', 'Filtra por usuario, acción, resultado o severidad.', 'Abre Ver en el evento relevante.', 'Anota ID, fecha, entidad y resultado.', 'Correlaciona con el estado actual del recurso.', 'Escala la evidencia sin enviar secretos.'],
    can: ['Consultar eventos y filtrar el historial.', 'Usar el detalle para soporte y recuperación.', 'Comparar cambios antes y después de una publicación.'],
    avoid: ['No usar la auditoría para inferir intención sin revisar el contexto.', 'No eliminar eventos para limpiar la pantalla.', 'No incluir tokens o credenciales en un reporte.'],
    verify: ['La búsqueda devuelve el evento esperado.', 'El ID y el usuario coinciden con la operación.', 'La corrección posterior también queda registrada.'],
    permissions: 'Consulta con <code>audit.read</code>. La retención y eliminación de eventos no se deben asumir desde el panel.', related: ['usuarios', 'configuracion']
  },
  {
    slug: 'api-keys', number: '06', group: 'Administración, observabilidad y acceso', title: 'API Keys', nav: 'API Keys', route: '/admin/api-keys', image: '06-api-keys.png',
    objective: 'Emitir credenciales para consumidores de API y limitar su uso.',
    fields: [
      ['Nombre', 'Etiqueta del consumidor.', 'Describe sistema o integración; no pongas el secreto aquí. Máximo 100 caracteres.'],
      ['Límite de peticiones / ventana', 'Cuota y periodo de contabilización.', 'Los límites numéricos deben ser naturales y mayores que cero.'],
      ['Límite por usuario / por IP', 'Topes adicionales por persona u origen.', 'Úsalos para evitar abuso sin bloquear el caso normal.'],
      ['Activo', 'Habilita o deshabilita la credencial.', 'Se revisa en el detalle y se cambia al editar; desactivar conserva el registro para auditoría.'],
      ['Prefijo / detalle', 'Identifica la key sin mostrar el secreto completo.', 'Guarda el secreto una sola vez en un gestor seguro.']
    ],
    steps: ['Confirma qué aplicación necesita la credencial.', 'Crea la key con un nombre identificable y límites razonables.', 'Guarda el secreto en un gestor seguro, nunca en el manual.', 'Prueba una llamada controlada.', 'Revisa Métricas para comprobar volumen y errores.', 'Desactiva y rota ante sospecha de exposición.'],
    can: ['Crear, consultar, limitar y desactivar credenciales autorizadas.', 'Revisar consumo mediante Métricas.'],
    avoid: ['No copiar el secreto a tickets, capturas o repositorios.', 'No aumentar límites para ocultar un consumo anómalo.', 'No mantener keys de integraciones retiradas.'],
    verify: ['El consumidor puede autenticarse sin exceder la cuota.', 'El secreto no aparece en la pantalla después de creado.', 'La key tiene propietario y fecha de revisión.'],
    permissions: 'Normalmente requiere <code>apikeys.read</code> y <code>apikeys.write</code>.', related: ['metricas', 'auditoria', 'usuarios']
  },
  {
    slug: 'metricas', number: '07', group: 'Administración, observabilidad y acceso', title: 'Métricas / Salud', nav: 'Métricas', route: '/admin/metrics', image: '07-metricas.png',
    objective: 'Verificar disponibilidad, rendimiento y cumplimiento de objetivos de servicio del API.',
    fields: [
      ['Period', 'Intervalo de medición.', 'Compara periodos equivalentes; no uses solo el valor instantáneo.'],
      ['Aplicar Filtros', 'Actualiza los indicadores.', 'Espera a que terminen de cargar antes de interpretar el resultado.'],
      ['Peticiones totales', 'Volumen de llamadas.', 'Un aumento puede ser tráfico normal o una integración mal configurada.'],
      ['Tiempo promedio / disponibilidad', 'Rendimiento y continuidad.', 'Compara contra el SLO y el mismo periodo anterior.'],
      ['Peticiones exitosas y tendencias', 'Distribución del resultado.', 'Busca patrones, no un error aislado.']
    ],
    steps: ['Elige el periodo.', 'Aplica filtros.', 'Lee total, tiempo promedio, disponibilidad y éxito.', 'Compara la tendencia con el periodo anterior.', 'Cruza un pico con Auditoría y API Keys.', 'Escala si el problema persiste o afecta al sitio público.'],
    can: ['Consultar salud y volumen.', 'Detectar degradaciones y límites alcanzados.'],
    avoid: ['No cambiar contenido para resolver una caída técnica.', 'No tomar una métrica aislada como diagnóstico completo.'],
    verify: ['El periodo seleccionado se refleja en los indicadores.', 'La tabla SLO carga sin error.', 'La acción de seguimiento queda clara.'],
    permissions: 'Consulta con <code>metrics.read</code>.', related: ['analytics', 'api-keys', 'escritorio', 'auditoria']
  },
  {
    slug: 'analytics', number: '08', group: 'Administración, observabilidad y acceso', title: 'Analytics', nav: 'Analytics', route: '/admin/analytics', image: '08-analytics.png',
    objective: 'Entender cómo se usa el sitio público para priorizar mejoras de contenido y navegación.',
    fields: [
      ['Periodo', 'Ventana de análisis.', 'Compara periodos similares para no confundir estacionalidad con tendencia.'],
      ['Visitas y visitantes únicos', 'Volumen de uso.', 'No equivalen automáticamente a usuarios registrados.'],
      ['Páginas más visitadas', 'Contenido con más demanda.', 'Úsalo para priorizar revisión, traducción o rendimiento.'],
      ['Referrers', 'Origen de las visitas.', 'Ayudan a detectar campañas, enlaces rotos o tráfico inesperado.'],
      ['Dispositivos', 'Distribución de escritorio/móvil y otros.', 'Comprueba la experiencia móvil de las páginas prioritarias.']
    ],
    steps: ['Selecciona el periodo.', 'Aplica o actualiza la consulta.', 'Lee volumen, páginas, origen y dispositivos.', 'Elige una mejora concreta basada en el dato.', 'Comprueba el cambio en la página pública.', 'Registra la decisión si afecta publicación o estructura.'],
    can: ['Consultar comportamiento público.', 'Detectar páginas prioritarias y posibles problemas de navegación.'],
    avoid: ['No publicar contenido solo porque tiene muchas visitas.', 'No tratar analytics como auditoría de usuarios del Admin.'],
    verify: ['El periodo corresponde al informe.', 'La página prioritaria se puede abrir públicamente.', 'La decisión tomada tiene un indicador de seguimiento.'],
    permissions: 'Consulta con <code>analytics.read</code>.', related: ['metricas', 'paginas', 'menus']
  },
  {
    slug: 'roles', number: '09', group: 'Administración, observabilidad y acceso', title: 'Roles', nav: 'Roles', route: '/admin/iam/roles', image: '09-roles.png',
    objective: 'Definir grupos de capacidades que luego se asignan a personas.',
    fields: [
      ['Aplicación', 'Contexto al que pertenece el rol.', 'Selecciona el contexto correcto; no mezcles aplicaciones.'],
      ['Código', 'Identificador técnico estable.', 'Requiere 2–100 caracteres; no lo cambies si está en uso.'],
      ['Nombre', 'Etiqueta comprensible para personas.', 'Requiere 2–100 caracteres.'],
      ['Descripción', 'Alcance del rol.', 'Máximo 500 caracteres; explica para quién es y qué no incluye.'],
      ['Permisos', 'Capacidades asociadas.', 'Al guardar, la selección completa reemplaza la anterior.']
    ],
    steps: ['Define la tarea y el alcance del rol.', 'Busca un rol parecido antes de crear otro.', 'Completa aplicación, código, nombre y descripción.', 'Selecciona permisos mínimos.', 'Revisa de nuevo todas las casillas antes de guardar.', 'Prueba el rol con una cuenta controlada.'],
    can: ['Crear y editar roles autorizados.', 'Consultar permisos asociados.'],
    avoid: ['No crear roles duplicados por nombres similares.', 'No usar <code>admin</code> como solución universal.', 'No guardar sin revisar la selección completa.'],
    verify: ['El código describe un rol estable.', 'La descripción explica el límite del acceso.', 'Una cuenta de prueba ve exactamente lo esperado.'],
    permissions: 'La administración de roles pertenece al área IAM y puede requerir acceso de superadministrador.', related: ['roles-permisos', 'permisos', 'usuarios']
  },
  {
    slug: 'roles-permisos', number: '10', group: 'Administración, observabilidad y acceso', title: 'Roles × permisos', nav: 'Roles × permisos', route: '/admin/iam/role-permissions', image: '10-roles-permisos.png',
    objective: 'Asignar el conjunto exacto de permisos de cada rol.',
    fields: [
      ['Selector de rol', 'Rol cuyas capacidades estás editando.', 'Confirma el nombre antes de tocar casillas.'],
      ['Grupos de recurso', 'Pages, Entries, Collections, Menus, Blocks, Users, Files y otros.', 'Trabaja por necesidad de negocio, no por tener todo visible.'],
      ['read', 'Permite consultar.', 'Es el punto de partida para la mayoría de roles.'],
      ['write', 'Permite crear o editar.', 'Añádelo solo a quien modifica ese recurso.'],
      ['admin / superadmin-access', 'Administración completa o acceso reservado.', 'Requiere autorización explícita.'],
      ['Seleccionar todo / Ninguno / Guardar', 'Acciones masivas y persistencia.', 'Guardar aplica la matriz completa del rol.']
    ],
    steps: ['Selecciona el rol exacto.', 'Lee los grupos de recursos.', 'Deja read en las áreas que debe consultar.', 'Agrega write solo donde editará.', 'Evita admin salvo necesidad documentada.', 'Guarda y confirma que desaparezca el aviso de cambios sin guardar.', 'Prueba con una cuenta representativa.'],
    can: ['Leer y ajustar la matriz si tienes autorización.', 'Comparar roles editoriales y operativos.'],
    avoid: ['No marcar todo para resolver un módulo faltante.', 'No quitar permisos sin avisar a la persona afectada.', 'No compartir el resultado sin registrar el cambio.'],
    verify: ['El rol correcto está seleccionado.', 'La matriz refleja la tarea y el principio de mínimo privilegio.', 'El módulo aparece o desaparece como se esperaba en la cuenta de prueba.'],
    permissions: 'Es la pantalla más sensible del panel y puede requerir <code>iam.superadmin-access</code>.', related: ['roles', 'usuarios', 'auditoria']
  },
  {
    slug: 'permisos', number: '11', group: 'Administración, observabilidad y acceso', title: 'Permisos', nav: 'Permisos', route: '/admin/iam/permissions', image: '11-permisos.png',
    objective: 'Consultar o definir las capacidades atómicas que luego se asignan a roles.',
    fields: [
      ['Aplicación', 'Área funcional del permiso.', 'Debe coincidir con la aplicación registrada.'],
      ['Código', 'Nombre técnico del permiso.', 'Máximo 100 caracteres y estable.'],
      ['Recurso', 'Objeto protegido, como pages, entries, users o files.', 'Máximo 50 caracteres.'],
      ['Acción', 'read, write, admin u otra acción autorizada.', 'Máximo 50 caracteres y con convención consistente.'],
      ['Descripción', 'Explicación humana.', 'Máximo 500 caracteres.']
    ],
    steps: ['Confirma que la capacidad no exista ya.', 'Completa aplicación, código, recurso y acción.', 'Describe el efecto real.', 'Guarda solo con autorización IAM.', 'Incluye el permiso en un rol si corresponde.', 'Prueba el resultado en una cuenta controlada.'],
    can: ['Consultar la definición de permisos.', 'Crear o editar si tu función administra IAM.'],
    avoid: ['No crear permisos inventados para una tarea editorial.', 'No renombrar uno que usan roles sin plan de migración.'],
    verify: ['El código es único y comprensible.', 'La acción corresponde al recurso.', 'Los roles dependientes fueron revisados.'],
    permissions: 'Área IAM; normalmente reservada a administradores autorizados.', related: ['roles', 'roles-permisos', 'aplicaciones']
  },
  {
    slug: 'aplicaciones', number: '12', group: 'Administración, observabilidad y acceso', title: 'Aplicaciones', nav: 'Aplicaciones', route: '/admin/iam/applications', image: '12-aplicaciones.png',
    objective: 'Consultar las aplicaciones registradas que sirven como contexto para roles y permisos.',
    fields: [
      ['Código', 'Identificador de la aplicación.', 'Úsalo para reconocer el contexto, no para modificarlo.'],
      ['Nombre', 'Etiqueta visible.', 'Ayuda a una persona no técnica a entender el área.'],
      ['Estado', 'Si la aplicación está disponible.', 'Un estado inactivo puede afectar roles relacionados.'],
      ['Fecha', 'Momento en que la aplicación quedó registrada.', 'Sirve para contexto de auditoría.']
    ],
    steps: ['Abre la lista y localiza la aplicación.', 'Comprueba código, nombre y estado.', 'Relaciona la aplicación con roles/permisos.', 'Si falta una aplicación, escala al equipo técnico.', 'No intentes crearla desde el panel.'],
    can: ['Consultar el catálogo.', 'Usarlo para interpretar el contexto IAM.'],
    avoid: ['No modificar desde esta pantalla: es solo lectura.', 'No asumir que crear un rol crea una aplicación.'],
    verify: ['El contexto de aplicación coincide con el permiso.', 'La diferencia entre aplicación y rol está clara.'],
    permissions: 'Consulta IAM según el rol.', related: ['roles', 'permisos']
  },
  {
    slug: 'wizard-contenido', number: '13', group: 'Asistentes y contenido editorial', title: 'CMS wizard / Asistente de contenido', nav: 'Asistente de contenido', route: '/admin/cms/wizard', image: '13-asistente-contenido.png',
    objective: 'Guiarte para agregar contenido, editar una página o modificar enlaces del menú sin saltar pasos.',
    fields: [
      ['Agregar contenido', 'Inicia una creación guiada.', 'Define primero qué tipo de contenido necesitas.'],
      ['Editar una página', 'Abre una página existente dentro del flujo.', 'Confirma el registro antes de sobrescribir.'],
      ['Editar enlaces del menú', 'Lleva al editor de navegación.', 'Prueba el destino en cada idioma.'],
      ['Anterior / Siguiente', 'Mueve el flujo conservando el contexto.', 'No cierres a mitad sin saber si guardaste.'],
      ['Traducir / Auto-traducir', 'Prepara contenidos por idioma.', 'La propuesta necesita revisión humana.'],
      ['Publicar ahora / Terminar de editar bloques', 'Publica o completa la composición.', 'Publicar es el último paso, no el botón de prueba.']
    ],
    steps: ['Elige la tarea correcta.', 'Completa el idioma base y el identificador.', 'Avanza revisando el resumen.', 'Traduce y corrige las propuestas.', 'Termina la composición de bloques.', 'Guarda como borrador, revisa la vista pública y publica solo al final.'],
    can: ['Seguir un flujo editorial ordenado.', 'Llegar a los editores de contenido y menú.'],
    avoid: ['No pulsar Publicar ahora para probar.', 'No aceptar Auto-traducir sin revisar nombres, SEO y enlaces.'],
    verify: ['El resumen coincide con la intención.', 'La página/entrada aparece en su módulo.', 'El estado final es el que querías.'],
    permissions: 'Depende de los permisos CMS de la operación elegida.', related: ['wizard-estructura', 'paginas', 'entradas', 'menus']
  },
  {
    slug: 'wizard-estructura', number: '14', group: 'Asistentes y contenido editorial', title: 'Asistente de estructura', nav: 'Asistente de estructura', route: '/admin/cms/wizard/structure', image: '14-asistente-estructura.png',
    objective: 'Crear las bases del sitio en el orden correcto: colección, página o menú.',
    fields: [
      ['Crear colección', 'Define un tipo de contenido.', 'Elige un preset solo si coincide con tu caso.'],
      ['Crear página', 'Crea una URL y su estructura inicial.', 'Selecciona el tipo de página adecuado.'],
      ['Crear menú nuevo', 'Crea una navegación para header o footer.', 'Usa claves estables que el tema conozca.'],
      ['Presets', 'Blog, Noticias, Portafolio, Servicios, Otro o sin preset.', 'Un preset acelera el inicio, pero revisa sus valores.'],
      ['Nombre, slug y traducciones', 'Identidad visible y URL.', 'Revisa las propuestas por idioma antes de crear.'],
      ['Resumen', 'Vista previa de lo que se va a crear.', 'Es el último punto para cancelar sin impacto.']
    ],
    steps: ['Elige colección, página o menú.', 'Completa nombre visible y slug.', 'Selecciona preset solo si corresponde.', 'Revisa idiomas y opciones.', 'Lee el resumen completo.', 'Crea y continúa en el módulo de detalle.', 'Prueba el resultado antes de enlazarlo desde un menú.'],
    can: ['Crear estructura base.', 'Reducir errores de orden y relaciones.'],
    avoid: ['No crear colecciones de prueba en producción.', 'No enlazar un menú antes de probar el destino.', 'No cambiar claves internas sin impacto.'],
    verify: ['El recurso aparece en su listado.', 'Las relaciones iniciales son correctas.', 'La URL o clave puede documentarse.'],
    permissions: 'Requiere permisos de escritura en el recurso seleccionado.', related: ['colecciones', 'paginas', 'menus', 'wizard-contenido']
  },
  {
    slug: 'entradas', number: '15', group: 'Asistentes y contenido editorial', title: 'Entradas', nav: 'Entradas', route: '/admin/cms/entries', image: '15-entradas.png',
    objective: 'Crear y mantener registros editoriales dentro de una colección, como noticias, proyectos o servicios.',
    fields: [
      ['Colección', 'Tipo de contenido al que pertenece la entrada.', 'Es obligatorio y debe existir.'],
      ['Estado', 'Borrador, Publicado o Archivado.', 'Controla el ciclo editorial.'],
      ['Destacado', 'Marca la entrada para espacios especiales.', 'No lo uses solo porque la entrada es nueva.'],
      ['Contenido por idioma', 'Título, slug, extracto, imagen, meta título, descripción y Open Graph.', 'ES es el idioma base; completa EN y los demás idiomas activos.'],
      ['Clasificación', 'Categorías y etiquetas.', 'Usa taxonomías existentes y evita duplicados.'],
      ['Fecha y sitemap', 'Publicación/programación, incluir, prioridad y frecuencia.', 'La frecuencia del sitemap no programa publicación.'],
      ['Gestionar Bloques', 'Composición heredada o editable de la colección.', 'Respeta la plantilla y los hijos permitidos.']
    ],
    steps: ['Verifica la colección activa.', 'Crea la entrada y elige colección.', 'Completa datos base en ES.', 'Traduce y revisa cada idioma activo.', 'Clasifica, agrega imagen y completa SEO.', 'Deja en borrador para revisión.', 'Prueba la URL, listado, móvil e idioma.', 'Publica o programa cuando todo esté aprobado.'],
    can: ['Crear, editar, archivar y publicar según permiso.', 'Clasificar y administrar bloques.'],
    avoid: ['No cambiar slug sin revisar redirecciones.', 'No publicar con traducciones críticas faltantes.', 'No marcar destacado para corregir un problema de listado.'],
    verify: ['El estado y colección son correctos.', 'La URL individual y la página de listado funcionan.', 'Imagen, alt text, SEO y traducciones están completos.'],
    permissions: 'Requiere <code>cms.entries.read/write</code> y permisos de bloques/archivos según la acción.', related: ['colecciones', 'categorias', 'etiquetas', 'paginas', 'wizard-contenido']
  },
  {
    slug: 'colecciones', number: '16', group: 'Asistentes y contenido editorial', title: 'Colecciones', nav: 'Colecciones', route: '/admin/cms/collections', image: '16-colecciones.png',
    objective: 'Definir qué tipo de entradas existen y qué comportamiento heredarán sus listados.',
    fields: [
      ['Nombre y slug por idioma', 'Identidad visible y URL del tipo.', 'Completa los idiomas activos.'],
      ['Clave interna y tipo', 'Referencia estable del modelo.', 'Tipo en minúsculas con letras, números, guiones o guiones bajos; máximo 50.'],
      ['Descripción', 'Explica el propósito de la colección.', 'Ayuda a editores y estructura.'],
      ['Activo', 'Habilita la colección.', 'Una colección inactiva no debe recibir contenido operativo.'],
      ['Aprobación, categorías y etiquetas', 'Activa reglas de flujo y clasificación.', 'Enciéndelas solo si el proceso las usará.'],
      ['Orden y sitemap', 'Valores por defecto para entradas/listados.', 'Prioridad decimal y frecuencia válida.'],
      ['Administrar estructura', 'Bloques heredados y pasos del wizard.', 'Cambia la plantilla con plan, porque afecta entradas existentes.']
    ],
    steps: ['Busca si la colección ya existe.', 'Consulta su detalle y referencias.', 'Crea con una clave estable y nombre por idioma.', 'Define reglas de clasificación y sitemap.', 'Configura estructura si corresponde.', 'Crea una entrada de prueba en borrador.', 'Verifica listado y URL antes de publicarla.'],
    can: ['Definir modelos editoriales.', 'Habilitar clasificación y estructura.', 'Consultar entradas vinculadas.'],
    avoid: ['No cambiar la clave interna sin migración.', 'No crear colecciones duplicadas para resolver un filtro.', 'No activar aprobación sin acordar el flujo.'],
    verify: ['La colección tiene propósito y clave claros.', 'Las entradas heredan la estructura correcta.', 'El listado público sabe qué colección mostrar.'],
    permissions: 'Requiere permisos CMS de colecciones; suele ser responsabilidad de <code>cms-admin</code>.', related: ['entradas', 'categorias', 'etiquetas', 'wizard-estructura']
  },
  {
    slug: 'categorias', number: '17', group: 'Asistentes y contenido editorial', title: 'Categorías', nav: 'Categorías', route: '/admin/cms/categories', image: '17-categorias.png',
    objective: 'Clasificar entradas con una jerarquía que puede usarse para filtrar y construir listados.',
    fields: [
      ['Colección', 'Contexto de la categoría.', 'Una categoría pertenece a una colección.'],
      ['Padre', 'Categoría principal de la jerarquía.', 'Déjalo vacío si será raíz.'],
      ['Nombre y slug por idioma', 'Etiqueta y URL de la clasificación.', 'Mantén equivalencia entre idiomas.'],
      ['Activo', 'Hace disponible la categoría.', 'Desactivar afecta filtros y listados.'],
      ['Reordenar', 'Cambia posición dentro de la jerarquía.', 'El orden puede modificar la navegación.']
    ],
    steps: ['Elige la colección correcta.', 'Busca una categoría equivalente antes de crear.', 'Define padre si corresponde.', 'Completa nombre y slug por idioma.', 'Guarda y revisa la jerarquía.', 'Asocia o prueba una entrada antes de publicar el filtro.'],
    can: ['Crear, editar, activar y ordenar categorías autorizadas.', 'Organizar jerarquías.'],
    avoid: ['No borrar una categoría con entradas sin revisar el impacto.', 'No crear una categoría por cada variación de nombre.'],
    verify: ['La categoría aparece en la colección correcta.', 'El filtro/listado devuelve las entradas esperadas.', 'El slug no duplica otro idioma o categoría.'],
    permissions: 'Requiere permisos de categorías y de la colección relacionada.', related: ['colecciones', 'entradas', 'etiquetas']
  },
  {
    slug: 'etiquetas', number: '18', group: 'Asistentes y contenido editorial', title: 'Etiquetas', nav: 'Etiquetas', route: '/admin/cms/tags', image: '18-etiquetas.png',
    objective: 'Crear etiquetas reutilizables para relacionar y filtrar entradas sin jerarquía padre/hijo.',
    fields: [
      ['Nombre por idioma', 'Texto visible de la etiqueta.', 'Usa una forma consistente y clara.'],
      ['Slug por idioma', 'Identificador de la etiqueta.', 'Debe ser legible, estable y no duplicado.'],
      ['Activo', 'Hace disponible la etiqueta.', 'Desactivar puede ocultarla de filtros.'],
      ['Asociación', 'Relación con entradas.', 'Revisa usos antes de retirar o fusionar conceptos.']
    ],
    steps: ['Busca el término antes de crear.', 'Completa nombre y slug por idioma.', 'Guarda activo si se usará en contenido.', 'Asocia una entrada de prueba.', 'Comprueba que el filtro o listado la encuentre.'],
    can: ['Crear, editar y activar etiquetas.', 'Reutilizarlas en entradas.'],
    avoid: ['No crear sinónimos casi idénticos.', 'No cambiar slugs usados sin revisar enlaces y filtros.'],
    verify: ['La etiqueta aparece en el idioma correcto.', 'El listado devuelve contenido relacionado.', 'No hay duplicados semánticos evidentes.'],
    permissions: 'Requiere permisos CMS de etiquetas.', related: ['entradas', 'categorias', 'traducciones']
  },
  {
    slug: 'formularios', number: '19', group: 'Asistentes y contenido editorial', title: 'Formularios Dinámicos', nav: 'Formularios Dinámicos', route: '/admin/cms/forms', image: '19-formularios.png',
    objective: 'Definir formularios públicos, sus campos, validaciones, notificaciones y mensajes por idioma.',
    fields: [
      ['Clave', 'Identificador usado por el bloque <code>form_embed</code>.', 'Debe ser corta, estable y única.'],
      ['Activo', 'Permite que el formulario se renderice.', 'Activo no lo muestra si ninguna página lo inserta.'],
      ['CAPTCHA', 'Protección contra tráfico automatizado.', 'Prueba también la configuración global de reCAPTCHA.'],
      ['Email de notificación', 'Destinatario interno de un envío.', 'Usa una cuenta controlada por el equipo.'],
      ['Respuesta automática / clave email', 'Respuesta al visitante y campo que contiene su email.', 'Usa una clave de campo <code>email</code> válida.'],
      ['Campos', 'Texto, Email, Teléfono, Área de Texto, Desplegable, Opción única, Opción múltiple, Fecha, Número y URL.', 'Define clave, etiqueta, ayuda, opciones, obligatorio y activo.']
    ],
    steps: ['Crea una clave estable.', 'Configura CAPTCHA, notificación y respuesta.', 'Agrega campos en el orden de la conversación.', 'Marca obligatorio solo cuando sea necesario.', 'Traduce etiquetas, placeholders y mensajes.', 'Guarda y confirma que esté activo.', 'Inserta <code>form_embed</code> en una página.', 'Prueba validación, éxito, email y Envíos.'],
    can: ['Crear, editar, activar y probar formularios.', 'Consultar sus envíos con permisos separados.'],
    avoid: ['No renombrar claves usadas en envíos sin coordinar.', 'No probar con datos personales reales.', 'No activar respuesta automática sin revisar el campo email.'],
    verify: ['El bloque selecciona la clave correcta.', 'Los errores aparecen junto al campo.', 'El envío aparece en Envíos y la notificación llega.'],
    permissions: 'Requiere permisos de formularios; los envíos pueden requerir <code>submissions.read/write</code>.', related: ['envios', 'paginas', 'bloques', 'identidad-sitio']
  },
  {
    slug: 'envios', number: '20', group: 'Asistentes y contenido editorial', title: 'Envíos', nav: 'Envíos', route: '/admin/cms/form-submissions', image: '20-envios.png',
    objective: 'Revisar y clasificar las respuestas recibidas desde los formularios públicos.',
    fields: [
      ['Todos', 'Todos los envíos visibles para tu permiso.', 'Usa filtros si la bandeja crece.'],
      ['Nuevo', 'Envíos todavía no revisados.', 'Es la cola de atención inicial.'],
      ['Leído', 'Envíos consultados.', 'Leer no necesariamente significa responder.'],
      ['Respondido', 'Casos con seguimiento.', 'Documenta fuera del panel si el proceso lo requiere.'],
      ['Spam', 'Envíos identificados como no deseados.', 'No borres antes de confirmar el patrón.'],
      ['Archivado', 'Casos retirados de la bandeja activa.', 'Archivar no es lo mismo que responder.']
    ],
    steps: ['Selecciona una pestaña de estado.', 'Abre el envío y verifica formulario, fecha e idioma.', 'Clasifica según el procedimiento del equipo.', 'Responde por el canal autorizado.', 'Archiva o marca spam solo con criterio.', 'No expongas datos personales en capturas o tickets.'],
    can: ['Consultar, clasificar y dar seguimiento a envíos autorizados.', 'Filtrar por estado o formulario.'],
    avoid: ['No tratar “No se encontraron resultados” como fallo.', 'No descargar ni compartir datos sin necesidad.', 'No usar datos de un envío para pruebas.'],
    verify: ['El estado refleja la atención real.', 'El formulario de origen es identificable.', 'La información sensible queda protegida.'],
    permissions: 'La lectura de envíos suele estar separada como <code>submissions.read</code>.', related: ['formularios', 'auditoria']
  },
  {
    slug: 'paginas', number: '21', group: 'Páginas, menús y bloques', title: 'Páginas', nav: 'Páginas', route: '/admin/cms/pages', image: '21-paginas.png',
    objective: 'Crear las URLs y composiciones principales del sitio.',
    fields: [
      ['Tipo', 'Inicio, Genérica, Contacto, Privacidad, Términos, 404, 500, Mantenimiento o Índice de Colección.', 'El tipo describe la función y puede cambiar la plantilla.'],
      ['Estado', 'Borrador, Publicado o Archivado.', 'No confundir con activo de un bloque.'],
      ['Colección, padre y orden', 'Relaciones del árbol o índice.', 'El padre y el orden afectan navegación.'],
      ['Contenido por idioma', 'Título, slug, extracto, meta y Open Graph.', 'Revisa ES, EN y demás idiomas activos.'],
      ['Fechas y sitemap', 'Publicación, programación, inclusión, prioridad y frecuencia.', 'Una frecuencia de sitemap no publica la página.'],
      ['Administrar Bloques', 'Abre el compositor de la página.', 'Revisa orden, hijos, traducciones y vista previa.']
    ],
    steps: ['Busca la página antes de crear otra.', 'Elige tipo y estado Borrador.', 'Completa título, slug, padre y SEO.', 'Traduce los idiomas activos.', 'Administra y ordena bloques.', 'Previsualiza en escritorio y móvil.', 'Prueba la URL y el menú.', 'Publica o programa con aprobación.'],
    can: ['Crear, editar, ordenar, traducir y publicar según permiso.', 'Componer bloques y configurar SEO.'],
    avoid: ['No crear copias para resolver un enlace roto.', 'No cambiar el slug sin redirección si la URL ya es pública.', 'No publicar con un bloque incompleto.'],
    verify: ['La URL funciona en cada idioma preparado.', 'La composición no tiene bloques huérfanos.', 'La página aparece donde debe en el menú/listado.'],
    permissions: 'Requiere <code>cms.pages.read/write</code> y permisos de bloques/archivos usados.', related: ['bloques', 'menus', 'traducciones', 'wizard-contenido']
  },
  {
    slug: 'menus', number: '22', group: 'Páginas, menús y bloques', title: 'Menús', nav: 'Menús', route: '/admin/cms/menus', image: '22-menus.png',
    objective: 'Construir la navegación que conecta al público con páginas, entradas, colecciones y URLs externas.',
    fields: [
      ['Nombres por idioma', 'Etiqueta del menú.', 'Completa los idiomas activos.'],
      ['Clave y ubicación', 'Identificador y zona que el tema consume, por ejemplo <code>header</code>, <code>footer</code> o <code>footer_secondary</code>.', 'No cambies la clave sin confirmar quién la consume.'],
      ['Tipo de enlace', 'Página, entrada, listado de colección, URL personalizada o sin enlace.', 'El tipo determina qué selector aparece. Para categorías, la pantalla puede ayudarte a construir una URL personalizada, pero no es un tipo de enlace separado.'],
      ['Etiqueta y URL', 'Texto visible y ruta por idioma.', 'Prueba la URL manual y sus traducciones.'],
      ['Padre y orden', 'Submenú y posición.', 'Guarda el orden después de reordenar.'],
      ['Target, icono y clase CSS', 'Pestaña destino, icono Lucide y estilo.', 'Usa <code>_self</code> para navegación interna salvo necesidad.'],
      ['Activo', 'Hace visible el elemento.', 'No arregla un destino inexistente.']
    ],
    steps: ['Elige el menú correcto por clave/ubicación.', 'Confirma que esté activo.', 'Crea el elemento y selecciona el tipo de enlace.', 'Elige el destino existente.', 'Completa etiqueta por idioma.', 'Define padre, target e icono si corresponde.', 'Guarda, ordena y pulsa Guardar Orden.', 'Prueba header/footer/footer_secondary cuando existan, escritorio, móvil y todos los idiomas.'],
    can: ['Crear, editar, ordenar y activar enlaces.', 'Construir jerarquías de navegación.'],
    avoid: ['No escribir URL manual si existe un destino seleccionable.', 'No enlazar una página en borrador.', 'No eliminar el destino para corregir un ítem.'],
    verify: ['Cada elemento abre la URL esperada.', 'El árbol no tiene padres incorrectos.', 'La navegación funciona en móvil y por idioma.'],
    permissions: 'Requiere <code>cms.menus.read/write</code>; los destinos requieren sus permisos propios.', related: ['paginas', 'entradas', 'colecciones', 'redirecciones']
  },
  {
    slug: 'bloques', number: '23', group: 'Páginas, menús y bloques', title: 'Tipos de Bloque', nav: 'Tipos de Bloque', route: '/admin/cms/block-types', image: '23-tipos-bloque.png',
    objective: 'Definir las plantillas y campos que aparecen cuando una página o entrada agrega un bloque.',
    fields: [
      ['Clave y nombre', 'Identidad técnica y etiqueta visible.', 'Clave/nombre mínimo 2; no cambies la clave usada.'],
      ['Categoría y descripción', 'Organización y ayuda al editor.', 'Explica el uso recomendado.'],
      ['Icono', 'Referencia visual del catálogo.', 'Usa una opción reconocible.'],
      ['Esquema JSON', 'Campos y configuración de la instancia.', 'Debe ser válido y coherente con el renderizador.'],
      ['Campos traducibles/configuración', 'Qué valores varían por idioma y qué ajustes son globales.', 'No marques como traducible un valor que debe ser único.'],
      ['Soporte y contenedor', 'Páginas, entradas, contenedor e hijos permitidos.', 'Si no soporta el propietario, no aparecerá en el selector.'],
      ['Activo / orden / Refrescar caché', 'Disponibilidad, posición y actualización del catálogo.', 'Refresca después de cambios de catálogo.']
    ],
    steps: ['Consulta el esquema del tipo antes de editar una instancia.', 'Define origen: Manual, Página, Colección, Entrada o Contenedor.', 'Elige plantilla y campos.', 'Configura soporte e hijos permitidos.', 'Guarda y refresca caché si corresponde.', 'Prueba el bloque en una página de borrador.', 'Verifica renderizado y traducciones antes de publicarlo.'],
    can: ['Consultar y administrar el catálogo si tienes rol CMS autorizado.', 'Definir esquemas y relaciones de bloques.'],
    avoid: ['No cambiar <code>block_key</code> sin migración.', 'No habilitar hijos incompatibles.', 'No probar esquemas nuevos directamente en una página publicada.'],
    verify: ['El tipo aparece en el selector correcto.', 'Los campos se renderizan según el esquema.', 'Una instancia existente sigue funcionando.'],
    permissions: 'Requiere <code>cms.blocks.read/write/admin</code> según la acción.', related: ['paginas', 'entradas', 'formularios']
  },
  {
    slug: 'redirecciones', number: '24', group: 'Configuración global y traducciones', title: 'Redirecciones', nav: 'Redirecciones', route: '/admin/cms/redirects', image: '24-redirecciones.png',
    objective: 'Conservar el acceso a una URL antigua cuando el destino cambió.',
    fields: [
      ['Ruta anterior', 'URL que ya no debe usarse.', 'Debe empezar por <code>/</code>; por ejemplo <code>/servicios</code>.'],
      ['Nueva URL', 'Destino actual.', 'Comprueba que exista y no redirija de vuelta.'],
      ['Tipo', '301 permanente o 302 temporal.', 'Elige según la duración real del cambio.'],
      ['Nota', 'Contexto para futuras revisiones.', 'Explica por qué se creó.'],
      ['Activo', 'Habilita la regla.', 'Actívala después de probar en entorno seguro.'],
      ['Importar / Exportar CSV', 'Carga o descarga varias reglas.', 'Valida formato, duplicados y bucles antes de importar.']
    ],
    steps: ['Verifica que la ruta anterior ya no deba servir contenido propio.', 'Confirma el destino nuevo.', 'Elige 301 o 302.', 'Añade una nota.', 'Guarda activa solo después de revisar.', 'Prueba en ventana privada.', 'Audita enlaces internos y menús que todavía usen la ruta vieja.'],
    can: ['Crear, editar, activar, importar y exportar reglas autorizadas.', 'Migrar URLs con seguimiento.'],
    avoid: ['No crear una redirección hacia sí misma.', 'No usar 301 para una prueba temporal.', 'No importar CSV sin validarlo.'],
    verify: ['La ruta antigua lleva al destino esperado.', 'No existe cadena o bucle innecesario.', 'La nota permite entender el motivo.'],
    permissions: 'Requiere permisos CMS de redirecciones.', related: ['menus', 'paginas', 'auditoria']
  },
  {
    slug: 'idiomas', number: '25', group: 'Configuración global y traducciones', title: 'Idiomas', nav: 'Idiomas', route: '/admin/cms/languages', image: '25-idiomas.png',
    objective: 'Controlar qué idiomas ofrece el sitio, cuál es el predeterminado y qué fallback se usa.',
    fields: [
      ['Código', 'Identificador del idioma, como <code>es</code> o <code>en</code>.', 'Debe ser estable y coherente con URLs.'],
      ['Nombre y nombre nativo', 'Etiquetas visibles para el equipo y visitantes.', 'Escribe ambos con claridad.'],
      ['Predeterminado y activo', 'Define el idioma base y si el idioma está disponible.', 'Desactivar es un cambio global; debe existir un único idioma predeterminado.'],
      ['Idioma de reserva', 'Fallback ante contenido faltante.', 'Confirma qué experiencia tendrá la persona.'],
      ['Establecer como predeterminado', 'Acción que convierte este idioma en el idioma base del sitio.', 'Úsala solo después de revisar URLs, menús, traducciones y SEO; solo debe existir uno.'],
      ['Orden / Reordenar', 'Posición de los idiomas en selectores y navegación.', 'El orden puede afectar la experiencia pública.']
    ],
    steps: ['Consulta los idiomas activos actuales.', 'Define código, nombres y estado.', 'Selecciona fallback compatible.', 'Guarda solo si el impacto global está aprobado.', 'Si corresponde, establece predeterminado.', 'Reordena y prueba selector, URLs y menús.', 'Revisa Auditoría de Traducciones.'],
    can: ['Consultar y administrar idiomas con autorización.', 'Definir fallback y orden.'],
    avoid: ['No desactivar un idioma para ocultar traducciones faltantes.', 'No cambiar el predeterminado sin revisar SEO y URLs.', 'No agregar un idioma sin plan de traducción.'],
    verify: ['El selector muestra los idiomas esperados.', 'El fallback es comprensible.', 'Las URLs y menús no quedaron sin traducción.'],
    permissions: 'Requiere <code>cms.languages.read/write/admin</code> según el cambio.', related: ['traducciones', 'identidad-sitio', 'menus', 'paginas']
  },
  {
    slug: 'traducciones', number: '26', group: 'Configuración global y traducciones', title: 'Auditoría de Traducciones', nav: 'Auditoría de Traducciones', route: '/admin/cms/translations/audit', image: '26-auditoria-traducciones.png',
    objective: 'Encontrar contenido faltante por idioma y abrir directamente el editor que debe corregirse.',
    fields: [
      ['Búsqueda', 'Texto para localizar recursos.', 'Empieza por una página, entrada o clave concreta.'],
      ['Idioma', 'Idioma que quieres revisar.', 'Compara con la lista de idiomas activos.'],
      ['Recurso', 'Páginas, menús, colecciones, entradas, categorías, etiquetas, formularios, campos, bloques, ítems y configuración.', 'Filtra por el tipo de objeto.'],
      ['Estado', 'Cobertura o pendiente.', 'Prioriza faltantes que bloquean una publicación.'],
      ['Traducir', 'Abre el editor con el idioma pendiente.', 'Guarda solo después de revisión humana.'],
      ['Auto-traducir', 'Genera propuesta.', 'No revisa tono, nombres, SEO, enlaces ni exactitud.']
    ],
    steps: ['Elige idioma y recurso.', 'Filtra pendientes.', 'Abre Traducir en un registro.', 'Completa o corrige el valor.', 'Guarda y prueba la URL/menú/formulario.', 'Vuelve a auditar hasta limpiar los faltantes críticos.', 'Registra excepciones intencionales.'],
    can: ['Consultar cobertura y abrir editores.', 'Coordinar el trabajo de traducción.'],
    avoid: ['No aceptar automáticamente todas las propuestas.', 'No asumir que traducir texto traduce enlaces o imágenes.', 'No cambiar fallback para ocultar pendientes.'],
    verify: ['El recurso ya no figura como faltante.', 'La traducción se ve en la URL correcta.', 'SEO, CTA, imágenes y navegación coinciden con el idioma.'],
    permissions: 'Requiere lectura y escritura del recurso traducido.', related: ['idiomas', 'paginas', 'entradas', 'identidad-sitio']
  },
  {
    slug: 'identidad-sitio', number: '27', group: 'Configuración global y traducciones', title: 'Identidad del sitio', nav: 'Identidad del sitio', route: '/admin/cms/site-identity', image: '27-identidad-sitio.png',
    objective: 'Administrar la información global de marca, contacto, redes, analytics, reCAPTCHA y pie de página.',
    fields: [
      ['Nombre, título, tagline y descripción', 'Identidad pública del sitio.', 'Revisa longitud, tono y traducción.'],
      ['Contacto, emails y copyright', 'Datos globales y mensajes.', 'No uses cuentas personales sin aprobación.'],
      ['Analytics', 'Identificación/configuración de medición.', 'No pegues secretos en campos públicos.'],
      ['Redes sociales', 'Enlaces globales.', 'Prueba que cada URL tenga destino válido.'],
      ['reCAPTCHA', 'Claves y comportamiento de protección.', 'Coordina con formularios y variables seguras.'],
      ['Logo y favicon', 'Activos de marca seleccionados desde Archivos.', 'Revisa variante, formato, contraste y alt.'],
      ['Traducir todos / Auto-traducir / Guardar', 'Flujo multidioma y persistencia.', 'Revisa propuestas antes de guardar.']
    ],
    steps: ['Identifica la pestaña del idioma activo.', 'Completa valores globales y localizados.', 'Selecciona logo/favicon desde la biblioteca.', 'Revisa redes, analytics y reCAPTCHA.', 'Comprueba mensajes de email y footer.', 'Guarda y valida en el sitio público.', 'Repite por idioma activo.'],
    can: ['Actualizar la identidad global si tienes autorización.', 'Seleccionar activos de marca existentes.'],
    avoid: ['No cambiar reCAPTCHA sin probar formularios.', 'No pegar claves secretas en el contenido.', 'No auto-traducir mensajes legales sin revisión.'],
    verify: ['Marca, footer, redes y metadatos se ven correctamente.', 'Los formularios siguen enviando.', 'El cambio se probó en los idiomas activos.'],
    permissions: 'Requiere permisos de identidad/settings y de archivos para seleccionar activos.', related: ['archivos', 'formularios', 'traducciones', 'configuracion']
  },
  {
    slug: 'configuracion', number: '28', group: 'Configuración global y traducciones', title: 'Configuración', nav: 'Configuración', route: '/admin/cms/settings', image: '28-configuracion.png',
    objective: 'Administrar claves globales que cambian el comportamiento del sitio.',
    fields: [
      ['Clave', 'Identificador que el código consumidor espera.', 'Mínimo 2 caracteres; no inventes una clave sin confirmación técnica.'],
      ['Valor', 'Dato que usará el sitio.', 'Respeta el tipo y el formato esperado.'],
      ['Tipo', 'Texto, Número entero, Booleano, JSON o Archivo.', 'Determina cómo se almacena y valida.'],
      ['Control visual', 'Texto, Área de texto, Texto enriquecido, URL, Email, Teléfono, color, Número, Toggle, imagen, archivo, lista, Código/JSON o Slug.', 'El control cambia la edición, no el contrato técnico.'],
      ['Grupo y página', 'Organización y ubicación del ajuste.', 'Ayudan a encontrarlo.'],
      ['Traducible, opciones y orden', 'Variación por idioma, JSON de opciones y posición.', 'Usa traducible solo si el valor realmente cambia por idioma.'],
      ['Ver / Editar', 'Consulta o modificación.', 'Ver no cambia; Editar sí persiste al guardar.']
    ],
    steps: ['Busca por clave antes de crear.', 'Abre Ver para entender valor, tipo, grupo y descripción.', 'Confirma con el equipo técnico qué código la consume.', 'Edita respetando tipo y JSON válido.', 'Guarda y revisa el efecto en el sitio.', 'Si es traducible, prueba cada idioma.', 'Registra el cambio global.'],
    can: ['Consultar y editar settings autorizados.', 'Organizar configuraciones conocidas.'],
    avoid: ['No crear claves de prueba en producción.', 'No cambiar un Booleano a texto por comodidad.', 'No modificar JSON sin validar estructura.', 'No guardar secretos en una guía o captura.'],
    verify: ['El código consumidor interpreta el valor.', 'La configuración aparece en el grupo correcto.', 'El sitio público conserva comportamiento esperado.'],
    permissions: 'Requiere <code>cms.settings.read/write/admin</code> según la acción.', related: ['identidad-sitio', 'traducciones', 'auditoria']
  }
];

window.ADMIN_GUIDE_NOVICE = {
  escritorio: {
    outcome: 'Usar el Escritorio como tablero de control: entender si el sistema está saludable y elegir el lugar correcto para trabajar.',
    before: ['Entra siempre por <code>http://localhost:8182/</code> y confirma tu nombre en la esquina superior.', 'Ten claro si vienes a revisar, editar, publicar o investigar un problema.', 'Recuerda que los contadores son accesos rápidos; no reemplazan la comprobación del detalle.'],
    steps: ['Mira primero la cabecera: idioma activo, usuario conectado y estado de sesión.', 'Revisa Hub API, Dominio y Sitio Web Público. Si alguno dice fuera de línea, detén una publicación y anota la hora.', 'Lee los contadores de usuarios, archivos, páginas, entradas, formularios y envíos para ubicar la tarea.', 'Abre la tarjeta o contador que corresponda y confirma que el listado destino coincide con la cifra.', 'Revisa traducciones y actividad reciente antes de asumir que un cambio no se aplicó.', 'Si investigas un incidente, conserva la hora, la pantalla y el módulo que abrió el problema.'],
    continue: 'Puedes continuar cuando sabes si el problema es editorial, de permisos, de archivos o técnico. Cada caso tiene un módulo distinto.',
    finish: ['Llegaste al módulo correcto sin editar nada por accidente.', 'Los servicios visibles están en línea o la incidencia quedó registrada.', 'La siguiente acción tiene un responsable y una pantalla de destino.'],
    trouble: ['Si un contador no coincide con un listado, actualiza el listado y revisa sus filtros.', 'Si un servicio está fuera de línea, consulta Métricas y Auditoría antes de reintentar.', 'Si no ves una tarjeta, revisa idioma, permisos y menú colapsado.']
  },
  perfil: {
    outcome: 'Actualizar únicamente tus datos personales y entender qué acciones de seguridad afectan a tu propia cuenta.',
    before: ['Comprueba que estás conectado con tu cuenta y no con una cuenta compartida.', 'Ten acceso al correo que aparece en tu perfil si vas a solicitar verificación o restablecimiento.', 'Prepara una foto y un nombre reconocible si vas a actualizar la identidad visible.'],
    steps: ['Abre Perfil desde el menú superior o desde la tarjeta de bienvenida.', 'Lee nombre, apellido, email y estado antes de editar.', 'Cambia solo los campos que realmente deban actualizarse y revisa la foto antes de subirla.', 'Pulsa Guardar cambios y espera la confirmación; no cierres la pestaña mientras se procesa.', 'Vuelve a mirar la cabecera para comprobar que el nombre cambió.', 'Usa reset o reenviar verificación solo para tu propio correo y conserva el enlace recibido en privado.'],
    continue: 'Si el correo o el estado no son los esperados, no sigas con reset: confirma primero que estás en la cuenta correcta.',
    finish: ['El nombre visible y la foto son los correctos.', 'La sesión sigue activa y el mensaje de guardado fue confirmado.', 'Cualquier correo de seguridad llegó al destinatario correcto.'],
    trouble: ['Si no aparece el cambio, recarga una sola vez y revisa el mensaje de validación.', 'Si no llega un correo, revisa spam y confirma el email antes de pedir otro.', 'Si la foto falla, vuelve a Archivos y comprueba formato, peso y permisos.']
  },
  archivos: {
    outcome: 'Incorporar un archivo reutilizable y dejarlo listo para páginas, entradas o bloques.',
    before: ['Ten el archivo final, no una copia de trabajo con nombre ambiguo.', 'Comprueba que no contiene contraseñas, tokens, datos personales innecesarios o material sin licencia.', 'Define antes dónde lo usarás y qué texto alternativo necesita una persona que no puede ver la imagen.'],
    steps: ['Abre Archivos y busca por nombre antes de subir; así evitas duplicados.', 'Pulsa Subir Archivo y selecciona el archivo desde el panel de carga. Espera la confirmación.', 'Abre Ver para confirmar tipo, tamaño, dimensiones, variantes y referencias de uso.', 'En el detalle completa texto alternativo, pie de foto y crédito cuando corresponda; la pantalla de carga no solicita esos campos.', 'Desde el editor de páginas, entradas o bloques, elige Biblioteca y selecciona este activo en vez de copiar una URL manual.', 'Antes de eliminar, revisa Usado en; si solo quieres retirarlo temporalmente, usa la papelera.'],
    continue: 'No insertes el archivo en una página hasta que la biblioteca lo muestre como procesado y puedas abrir su vista previa.',
    finish: ['El archivo se encuentra por búsqueda y tiene el tipo correcto.', 'La vista previa y las variantes cargan sin error.', 'El texto alternativo describe la función o contenido de la imagen.'],
    trouble: ['Si la lista queda vacía, limpia filtros de fecha, tamaño y categoría.', 'Si una variante no aparece, espera el procesamiento y revisa el detalle antes de volver a subir.', 'Si quieres borrar y aparece Usado en, corrige primero las páginas o bloques que lo referencian.']
  },
  usuarios: {
    outcome: 'Crear o ajustar una cuenta con el rol mínimo necesario, sin entregar más acceso del que la persona requiere.',
    before: ['Busca primero por email; crear dos cuentas para la misma persona complica el acceso y la auditoría.', 'Confirma nombre, apellido, correo y responsabilidad de la persona.', 'Consulta la matriz de roles antes de marcar casillas.'],
    steps: ['En Usuarios usa Buscar y confirma que el email no exista.', 'Pulsa Nuevo usuario y completa Nombre, Apellido y Email; los tres son obligatorios.', 'Marca uno o más roles solo si están autorizados. Si dejas Roles vacío, el sistema asigna <code>user</code>.', 'Pulsa Crear usuario y guarda el resultado, incluido el estado inicial.', 'Pide a la persona probar únicamente el acceso que necesita; no pruebes con tu cuenta superadmin.', 'Si corriges permisos, registra qué rol se asignó y revisa Auditoría.'],
    continue: 'Antes de crear, el correo debe ser único y el rol debe poder explicarse en una frase de negocio.',
    finish: ['La cuenta aparece una sola vez y el email es correcto.', 'El rol visible coincide con el alcance aprobado.', 'La persona puede entrar y no puede operar módulos fuera de su función.'],
    trouble: ['Si el email ya existe, abre esa cuenta en vez de crear otra.', 'Si la persona ve demasiado, retira permisos desde el rol y prueba con una cuenta de control.', 'Si ve demasiado poco, confirma aplicación, rol y permisos antes de marcar superadmin.']
  },
  auditoria: {
    outcome: 'Encontrar evidencia concreta de una operación: quién la hizo, cuándo, sobre qué objeto y con qué resultado.',
    before: ['Define un intervalo aproximado y el recurso afectado.', 'No busques solo por el nombre de la persona: también pueden servir acción, entidad, resultado o severidad.', 'Ten un lugar seguro donde anotar el ID del evento sin copiar secretos.'],
    steps: ['Abre Auditoría y empieza con el filtro más específico que conozcas.', 'Usa Buscar y comprueba cuántos resultados quedan; si son demasiados, reduce fecha o entidad.', 'Abre Ver en el evento que coincida con el registro y revisa ID, usuario, acción, entidad, IP, fecha y resultado.', 'Compara el resultado de auditoría con el estado actual del recurso.', 'Anota el ID y la conclusión; no borres ni edites eventos durante la investigación.', 'Después de corregir, vuelve a consultar para confirmar que la nueva operación también quedó registrada.'],
    continue: 'Un evento aislado no demuestra toda la historia: busca el evento anterior y el posterior cuando investigues una diferencia.',
    finish: ['El evento encontrado coincide con el usuario y momento correctos.', 'La entidad afectada está identificada.', 'La evidencia se compartió sin tokens, contraseñas ni datos personales innecesarios.'],
    trouble: ['Si no hay resultados, limpia filtros y comprueba que buscas en el host correcto.', 'Si hay muchos eventos, empieza por ID, usuario o entidad.', 'Si falta el evento, no lo inventes: registra la ausencia y escala la retención o permisos.']
  },
  'api-keys': {
    outcome: 'Crear una credencial limitada para una integración y dejar claro dónde se guarda, cómo se prueba y cuándo se revoca.',
    before: ['Confirma el sistema consumidor, su responsable y el entorno donde usará la key.', 'Define límites de peticiones, ventana, usuario e IP antes de crear.', 'Ten preparado un gestor seguro: el secreto no debe terminar en un ticket, captura o repositorio.'],
    steps: ['Abre API Keys y pulsa Nueva API key.', 'Completa Nombre con una etiqueta que identifique sistema y propósito; no pongas el secreto en ese campo.', 'Configura Límite de peticiones, Ventana (s), Límite por usuario y Límite por IP con valores positivos y razonables.', 'Pulsa Nueva API key y copia el secreto solo en el gestor seguro cuando la pantalla lo muestre.', 'Prueba una llamada mínima desde el consumidor y confirma respuesta correcta.', 'Observa Métricas y desactiva o rota la key si el consumidor se retira o el secreto se expone.'],
    continue: 'No crees una key para explorar: si solo necesitas consultar, usa una cuenta o permiso de lectura autorizado.',
    finish: ['La integración autentica sin superar su cuota.', 'El secreto quedó fuera de la guía y de las capturas.', 'Existe un responsable y una fecha para revisar o rotar la key.'],
    trouble: ['Si el consumidor recibe 401, revisa host, encabezado y key activa antes de aumentar límites.', 'Si recibe 429, revisa la ventana y el volumen en Métricas.', 'Si se expuso el secreto, desactívalo y crea uno nuevo; no intentes ocultarlo editando la etiqueta.']
  },
  metricas: {
    outcome: 'Distinguir una caída del API, una lentitud y un aumento de tráfico usando el periodo y los indicadores adecuados.',
    before: ['Define qué síntoma observaste y desde qué hora.', 'Elige un periodo comparable, no solo el último minuto.', 'Ten a mano la API key o integración que podría explicar el tráfico.'],
    steps: ['Abre Métricas y selecciona el Periodo.', 'Pulsa Aplicar Filtros y espera a que terminen de actualizarse las tarjetas.', 'Lee peticiones totales, tiempo promedio, disponibilidad, éxito y tendencias como conjunto.', 'Compara con otro periodo equivalente.', 'Cruza picos o errores con API Keys y Auditoría.', 'Documenta la hipótesis y la acción; no cambies contenido para resolver un problema de infraestructura.'],
    continue: 'Solo continúa con una corrección editorial cuando las métricas confirmen que el problema no es del servicio.',
    finish: ['El periodo consultado coincide con el incidente.', 'El indicador anómalo está identificado.', 'La próxima acción es técnica, editorial o de integración y no una mezcla de las tres.'],
    trouble: ['Si las tarjetas no actualizan, vuelve a aplicar filtros y revisa consola solo como diagnóstico técnico.', 'Si el promedio parece normal, mira errores y percentiles o tendencias antes de cerrar el incidente.', 'Si el volumen subió, revisa API Keys antes de culpar al sitio público.']
  },
  analytics: {
    outcome: 'Convertir datos de visitas en una decisión concreta de contenido o navegación, sin confundirlos con usuarios del Admin.',
    before: ['Define la pregunta: qué página, periodo, dispositivo u origen quieres entender.', 'Escoge periodos comparables.', 'Decide qué cambio probarás si aparece una señal clara.'],
    steps: ['Abre Analytics y selecciona el periodo.', 'Pulsa Aplicar Filtros y espera la carga.', 'Revisa visitas, visitantes, páginas, referrers y dispositivos juntos.', 'Selecciona una página o ruta prioritaria.', 'Abre esa página en el sitio público y comprueba que el problema o la oportunidad existe.', 'Aplica un cambio medible y vuelve a comparar en un periodo posterior.'],
    continue: 'Analytics te ayuda a priorizar; no demuestra por sí solo que una cuenta, traducción o publicación sea correcta.',
    finish: ['La pregunta inicial tiene una respuesta basada en un periodo concreto.', 'La página prioritaria fue verificada públicamente.', 'La decisión tiene una métrica para comprobar si funcionó.'],
    trouble: ['Si no hay datos, confirma periodo, filtros y que el sitio esté enviando medición.', 'Si móvil y escritorio difieren, prueba ambos antes de editar bloques.', 'Si una URL recibe tráfico pero está rota, corrige la URL o redirección, no el indicador.']
  },
  roles: {
    outcome: 'Crear un rol comprensible y reusable que represente una función, no una persona concreta.',
    before: ['Escribe la función en una frase: por ejemplo, “edita entradas pero no administra usuarios”.', 'Busca un rol existente parecido antes de crear otro.', 'Ten claro el contexto de Aplicación y el código que debe permanecer estable.'],
    steps: ['Pulsa Nuevo rol desde Roles.', 'Selecciona Aplicación si el rol pertenece a un contexto concreto.', 'Completa Código y Nombre; ambos son obligatorios y deben ser estables.', 'Escribe una Descripción con alcance y límites.', 'Marca solo los Permisos asignados necesarios.', 'Pulsa Crear y revisa el detalle del rol.', 'Asigna el rol a una cuenta de prueba y confirma el menú visible.'],
    continue: 'No continúes si no puedes explicar por qué cada permiso está marcado.',
    finish: ['El código no duplica otro rol.', 'La descripción permite a otra persona saber cuándo usarlo.', 'La cuenta de prueba ve el mínimo de módulos necesario.'],
    trouble: ['Si falta un permiso, revisa Roles × permisos y la aplicación, no crees un segundo rol improvisado.', 'Si el rol da acceso de más, corrige la matriz y vuelve a probar.', 'Si no aparecen permisos, revisa que la aplicación y el rol correspondan.']
  },
  'roles-permisos': {
    outcome: 'Modificar una matriz de acceso sin convertir un problema puntual en acceso total.',
    before: ['Ten el nombre exacto del rol y una tarea de prueba.', 'Anota la matriz actual antes de cambiarla si el rol ya está en uso.', 'Asegura que tienes autorización para administrar IAM.'],
    steps: ['Selecciona el rol correcto en el selector.', 'Lee cada grupo de recurso antes de marcar casillas.', 'Deja read para consulta, agrega write para edición y reserva admin para administración completa.', 'Usa Seleccionar todo o Ninguno solo si el alcance está aprobado y luego revisa la matriz manualmente.', 'Pulsa Guardar y espera que desaparezca el aviso de cambios pendientes.', 'Prueba la cuenta asociada en una ventana separada y revisa menú, listado y acción concreta.', 'Registra el cambio en Auditoría y comunica el nuevo alcance.'],
    continue: 'El botón Guardar aplica la matriz completa; no es una casilla aislada. Revisa también permisos que podrían quedar marcados por defecto.',
    finish: ['El rol seleccionado es el correcto.', 'La cuenta de prueba puede hacer exactamente la tarea acordada.', 'No se agregó superadmin-access para resolver una omisión.'],
    trouble: ['Si la cuenta ve un módulo que no corresponde, retira el permiso del rol y vuelve a iniciar sesión de prueba.', 'Si no ve una acción, diferencia read de write y confirma el permiso base de la aplicación.', 'Si no estás seguro de una casilla, no guardes: consulta la matriz aprobada.']
  },
  permisos: {
    outcome: 'Consultar o crear una capacidad atómica que pueda asignarse a roles con un nombre entendible.',
    before: ['Confirma si el permiso ya existe buscando por código o recurso.', 'Define aplicación, código, recurso y acción antes de crear.', 'No uses esta pantalla para ampliar una cuenta: aquí se define la capacidad, no su asignación final.'],
    steps: ['Busca el código o recurso antes de crear.', 'Abre el detalle y lee aplicación, recurso, acción y descripción.', 'Si creas uno, usa un código estable y una descripción que explique el alcance.', 'Relaciona el permiso con el rol que lo necesita desde Roles o Roles × permisos.', 'Prueba el comportamiento con una cuenta de control.', 'Registra el cambio si afecta una capacidad sensible.'],
    continue: 'Un permiso nuevo no produce acceso por sí solo; debe estar asignado al rol correcto y la cuenta debe tener ese rol.',
    finish: ['No existe un permiso duplicado con otro nombre.', 'La acción está limitada al recurso correcto.', 'La asignación y la prueba quedaron documentadas.'],
    trouble: ['Si el permiso no aparece en la matriz, revisa aplicación y recarga la pantalla.', 'Si una cuenta tiene acceso inesperado, revisa todos sus roles.', 'Si el código no es claro, no lo uses como parche: define la capacidad primero.']
  },
  aplicaciones: {
    outcome: 'Entender en qué aplicación vive un rol o permiso y distinguir un catálogo de contexto de una pantalla operativa.',
    before: ['Ten el código o nombre del contexto que quieres identificar.', 'Recuerda que esta pantalla es de consulta según el estado observado.', 'No intentes resolver un permiso faltante creando una aplicación nueva.'],
    steps: ['Busca la aplicación por nombre o código.', 'Comprueba estado, fecha y contexto.', 'Relaciona esa aplicación con el rol o permiso que estabas revisando.', 'Vuelve a Roles o Permisos para operar el recurso correspondiente.', 'Si el contexto falta o está inactivo, registra el hallazgo y escálalo.'],
    continue: 'La aplicación es el contexto; el rol agrupa permisos y el usuario recibe roles. No son intercambiables.',
    finish: ['El código y el nombre coinciden con el recurso revisado.', 'La operación continuará en Roles, Permisos o Usuarios, no aquí.', 'La diferencia quedó clara para la persona que administra accesos.'],
    trouble: ['Si una aplicación aparece inactiva, no crees roles dependientes hasta confirmar el impacto.', 'Si no encuentras el contexto, busca por código exacto y revisa permisos de lectura.', 'Si la duda es de acceso, vuelve a Roles × permisos.']
  },
  'wizard-contenido': {
    outcome: 'Completar un contenido guiado y llegar a una revisión sin publicar accidentalmente.',
    before: ['Define si quieres Agregar contenido, Editar una página o Editar enlaces del menú.', 'Ten preparado el titular, el resumen, el contenido, idioma base y decisión de publicación.', 'Para una prueba, usa un texto de demostración y termina en revisión; no pulses Publicar ahora.'],
    steps: ['En ¿Qué quieres hacer hoy? elige Agregar contenido, Editar una página o Editar enlaces del menú. Los pasos 1–4 siguientes corresponden a Agregar contenido.', 'Si agregas contenido, elige el tipo visible, por ejemplo Noticias o Portafolio.', 'Paso 1: completa Titular, que es obligatorio, y Resumen. Si Siguiente no avanza, vuelve a revisar el Titular.', 'Paso 2: decide si la Imagen destacada saldrá de Biblioteca o de URL externa; la imagen es opcional y puedes continuar sin ella.', 'Paso 3: escribe el Contenido obligatorio en el editor enriquecido. Usa B, I, H2, H3, listas, cita o enlace solo cuando el contenido lo necesite.', 'Paso 4: completa Texto Alternativo y Pie de Foto si agregas imagen, o pulsa Omitir.', 'En la revisión lee idioma base, slug y traducciones automáticas. Elige Guardar borrador para revisar después; usa Publicar ahora solo con aprobación explícita. Si la colección exige aprobación, el dominio puede dejar la entrada en revisión aunque hayas solicitado publicar.'],
    continue: 'Cada Siguiente debe cambiar el indicador Paso 1 de 4, Paso 2 de 4, Paso 3 de 4 o Paso 4 de 4. Si el paso no cambia, falta un obligatorio o el navegador bloqueó la validación.',
    finish: ['La pantalla de revisión muestra el titular y resumen correctos.', 'La operación final coincide con lo elegido: borrador, publicación solicitada o edición del recurso correspondiente.', 'Si agregaste contenido, la entrada aparece en Entradas; si editaste una página o menú, el cambio se ve en ese recurso. Verifica la URL pública solo cuando el estado permita publicación.'],
    trouble: ['Si Siguiente no avanza en Paso 1, completa Titular; en Paso 3, escribe contenido en el editor.', 'Si la traducción automática no aparece, espera y luego revisa manualmente; no publiques una propuesta sin leerla.', 'Si solo querías probar, abandona desde revisión sin pulsar Guardar borrador ni Publicar ahora.']
  },
  'wizard-estructura': {
    outcome: 'Crear la base de una colección, página o menú entendiendo qué relaciones se van a generar.',
    before: ['Decide si necesitas un modelo de contenido, una URL o una navegación.', 'Busca primero el recurso para no duplicarlo.', 'Si pruebas, detente antes de Crear colección, Crear página o Crear menú.'],
    steps: ['Elige Crear colección, Crear página o Crear menú nuevo.', 'Para colección, elige Blog, Noticias, Portafolio, Servicios u Otro solo si describe el contenido; de lo contrario usa Crear sin preset.', 'Completa Nombre visible y Slug base. En una colección, este valor forma su clave; en una página o menú, la pantalla usa el identificador que corresponda a ese recurso.', 'Pulsa Siguiente y lee el Resumen final antes de crear.', 'Revisa bloques propuestos, tipo de preset e idiomas habilitados.', 'Comprueba las propuestas de traducción y desmarca el idioma que no deba incluirse.', 'Pulsa Crear solo cuando el nombre, identificador, preset y relaciones estén aprobados; después prueba el recurso en su listado.'],
    continue: 'La segunda pantalla es una revisión, no un formulario más. Si el preset propone bloques que no necesitas, vuelve atrás o elige Crear sin preset.',
    finish: ['El recurso aparece en su listado con nombre e identificador correctos.', 'Los idiomas y bloques iniciales coinciden con lo aprobado.', 'La URL o clave se puede usar en páginas, entradas o menús sin improvisar.'],
    trouble: ['Si el slug ya existe, vuelve al primer paso y busca el recurso original.', 'Si una traducción queda vacía, corrígela o desmarca el idioma antes de crear.', 'Si el preset no corresponde, no lo arregles después a ciegas: vuelve y selecciona otro.']
  },
  entradas: {
    outcome: 'Crear o editar una entrada editorial con colección, estado, contenido multidioma, imagen, SEO y bloques coherentes.',
    before: ['Define la colección: en la instalación real aparecen noticias y portafolio.', 'Prepara título, slug, extracto, imagen, categorías, etiquetas y textos por idioma.', 'Decide si el resultado será Borrador, Publicado o Archivado; para trabajar por primera vez usa Borrador.'],
    steps: ['En Entradas busca por título y colección antes de pulsar Nueva Entrada.', 'En el formulario elige Colección y Estado; ambos son obligatorios.', 'Marca Destacado solo si la entrada debe aparecer en espacios destacados.', 'En Traducciones / Contenido completa ES y luego EN u otros idiomas: Título y Slug son obligatorios; Extracto, imagen y SEO se revisan por idioma.', 'Usa Biblioteca para una imagen existente o URL externa solo si el enlace es público y estable.', 'Guarda como Borrador, abre la entrada y revisa Administrar Bloques, Subir/Bajar y Vista previa.', 'Comprueba categorías, etiquetas, URL individual, listado, móvil y traducciones.', 'Publica Entrada o programa solo después de la aprobación editorial; Archivar y Eliminar son acciones distintas y sensibles.'],
    continue: 'No confundir la frecuencia del sitemap con la fecha de publicación: una organiza buscadores, la otra cambia el estado o momento de la entrada. El sitio público solo lista entradas publicadas cuya fecha de programación ya llegó.',
    finish: ['La entrada pertenece a la colección correcta y tiene estado esperado.', 'La vista previa funciona en los idiomas preparados.', 'Los bloques están activos, ordenados y sin contenido de prueba.'],
    trouble: ['Si no aparece en el listado, limpia filtros de colección y estado.', 'Si el slug está ocupado, no lo fuerces: busca la entrada existente o define una redirección.', 'Si el bloque no aparece, revisa Administrar Bloques y los permisos de bloques.']
  },
  colecciones: {
    outcome: 'Definir un modelo editorial reutilizable antes de crear muchas entradas.',
    before: ['Escribe qué representa una entrada de la colección y qué no representa.', 'Confirma la clave interna y revisa si ya existe.', 'Decide si usarás categorías, etiquetas, aprobación, sitemap y estructura de bloques.'],
    steps: ['Busca la colección por nombre y clave.', 'Abre su detalle y revisa idiomas, estado, reglas y entradas vinculadas.', 'Si creas o editas, define nombre, slug, clave interna y descripción.', 'Activa solo las reglas que el equipo realmente usará.', 'Configura bloques heredados y campos antes de cargar contenido.', 'Crea una entrada en Borrador y confirma que hereda la estructura.', 'Prueba listado, URL, filtros y permisos antes de activar operación real.'],
    continue: 'Una colección es un contrato para futuras entradas. Si cambias la clave o la estructura después, puede afectar contenido existente.',
    finish: ['La clave es estable y no duplica otra colección.', 'Las entradas nuevas reciben la estructura esperada.', 'El equipo sabe quién administra el modelo.'],
    trouble: ['Si una entrada no hereda bloques, revisa estructura y permisos antes de editarla manualmente.', 'Si el listado queda vacío, confirma estado publicado y colección seleccionada.', 'Si la colección ya existe, ajusta la original en vez de duplicarla.']
  },
  categorias: {
    outcome: 'Crear una clasificación jerárquica que ayude a filtrar contenido sin romper la colección asociada.',
    before: ['Elige la colección correcta; en el formulario es obligatoria.', 'Busca el nombre y slug antes de crear.', 'Decide si será raíz o hija de una categoría existente.'],
    steps: ['Pulsa Nueva Categoría y selecciona Colección.', 'Elige Categoría Principal solo si debe quedar dentro de una jerarquía.', 'Completa Nombre y Slug en ES y repite en EN si corresponde.', 'Deja Activo solo si el concepto ya está aprobado.', 'Crea y revisa el árbol en la colección.', 'Asocia una entrada de Borrador y prueba el filtro o listado.'],
    continue: 'La categoría solo tiene sentido dentro de una colección; un nombre correcto en la colección equivocada sigue siendo un dato incorrecto.',
    finish: ['La categoría aparece bajo la colección y padre correctos.', 'El slug es único y estable.', 'El filtro devuelve solo las entradas esperadas.'],
    trouble: ['Si no aparece, revisa colección, estado activo y filtro.', 'Si necesitas moverla, comprueba el impacto en entradas y URLs antes de cambiar el padre.', 'Si existe un sinónimo, reutiliza o fusiona el concepto en vez de crear otro.']
  },
  etiquetas: {
    outcome: 'Crear una etiqueta reutilizable y consistente para relacionar entradas sin jerarquía.',
    before: ['Busca el término en todos los idiomas.', 'Define una forma canónica y su slug.', 'Comprueba si el equipo usa categorías para ese concepto; no dupliques funciones.'],
    steps: ['Pulsa Nueva Etiqueta.', 'Completa Nombre y Slug obligatorios en ES.', 'Cambia a EN u otro idioma y completa su traducción si está activo.', 'Deja Activo solo si la etiqueta se usará en contenido.', 'Crea y asóciala a una entrada de Borrador.', 'Prueba el filtro y revisa que no aparezcan etiquetas parecidas.'],
    continue: 'Una etiqueta no tiene padre: si necesitas jerarquía, documenta el concepto como categoría.',
    finish: ['El nombre y slug no duplican otro concepto.', 'La etiqueta aparece en el idioma correcto.', 'El listado devuelve las entradas relacionadas.'],
    trouble: ['Si el listado queda vacío, limpia filtros y confirma que la entrada tenga la etiqueta.', 'Si hay dos etiquetas equivalentes, detén la creación y acuerda una canónica.', 'Si cambias un slug público, revisa enlaces y redirecciones.']
  },
  formularios: {
    outcome: 'Definir un formulario público completo: clave, protección, notificación, respuesta, campos, idiomas y prueba de envío.',
    before: ['Dibuja la conversación que tendrá la persona visitante y qué datos son realmente necesarios.', 'Elige una clave estable como <code>contact</code>; cambiarla puede romper bloques existentes.', 'Ten un correo de notificación de prueba y un correo de respuesta controlado.'],
    steps: ['Pulsa Nuevo Formulario y completa Clave del Formulario y Nombre del Formulario; ambos son obligatorios.', 'Decide Activo y Requerir CAPTCHA. Las claves de reCAPTCHA se gestionan en CMS > Configuración.', 'Configura Email de Notificación solo si alguien debe recibir cada envío.', 'Activa respuesta automática solo después de definir la Clave del campo email, normalmente <code>email</code>.', 'Completa etiquetas, botón, descripción, mensaje de éxito y mensaje de error por idioma.', 'Guarda y abre el detalle para revisar los campos reales: clave, tipo, obligatorio, placeholders y traducciones.', 'Inserta el formulario mediante un bloque Formulario Embebido que use la misma clave.', 'Prueba campo obligatorio, email inválido, envío correcto, mensaje, correo y aparición en Envíos.'],
    continue: 'Un formulario guardado no aparece por sí solo: también debe estar activo y vinculado desde un bloque en una página.',
    finish: ['El bloque usa la clave exacta del formulario.', 'La validación impide errores previsibles y muestra mensajes comprensibles.', 'El envío aparece en Envíos y la notificación llega al destinatario acordado.'],
    trouble: ['Si el bloque no muestra el formulario, revisa clave, activo y caché.', 'Si no llega respuesta automática, confirma que exista un campo email con esa field_key.', 'Si CAPTCHA falla, revisa configuración global y no desactives la protección como solución permanente.']
  },
  envios: {
    outcome: 'Revisar una respuesta real sin perder su estado ni exponer datos personales.',
    before: ['Ten claro qué formulario y periodo buscas.', 'Define quién puede responder y por qué canal.', 'No uses un envío real como dato de prueba en otro formulario.'],
    steps: ['Abre Envíos y elige Todos, Nuevo, Leído, Respondido, Spam o Archivado.', 'Abre un envío y confirma formulario, fecha, idioma y campos recibidos.', 'Lee la respuesta completa antes de cambiar su estado.', 'Responde por el canal autorizado y guarda la referencia fuera del panel si el proceso lo exige.', 'Marca Leído, Respondido, Spam o Archivado según el procedimiento, no según conveniencia.', 'Vuelve al listado y confirma que el envío desapareció o apareció en la pestaña esperada.'],
    continue: 'Leer, responder, archivar y marcar spam son decisiones diferentes; el estado no reemplaza la respuesta humana.',
    finish: ['El estado representa la acción realmente realizada.', 'La persona responsable puede encontrar el envío.', 'Las capturas y reportes no contienen datos personales innecesarios.'],
    trouble: ['Si no hay resultados, limpia la pestaña o filtro de formulario.', 'Si el correo parece spam, revisa patrón y contenido antes de marcarlo.', 'Si falta un envío esperado, comprueba formulario activo, CAPTCHA y auditoría.']
  },
  paginas: {
    outcome: 'Crear una URL pública y su composición sin perder jerarquía, traducciones, SEO ni seguridad de publicación.',
    before: ['Define tipo de página: Inicio, Genérica, Contacto, legal, error, mantenimiento o Índice de Colección.', 'Decide si será raíz o hija y si debe pertenecer a una colección.', 'Prepara título, slug, extracto, SEO, idioma base y bloques iniciales.'],
    steps: ['Busca la página antes de pulsar Nueva Página.', 'Selecciona Tipo de página y Estado; para trabajar sin publicar usa Borrador.', 'Usa Colección cuando la página sea un índice de colección o cuando el diseño lo requiera; elige Página principal cuando deba quedar anidada.', 'Completa Orden si la página participa en una jerarquía.', 'En Traducciones / Contenido completa ES y cambia a EN para revisar título y slug.', 'Abre SEO y Sitemap: una frecuencia de sitemap no programa publicación; revisa meta, inclusión y prioridad.', 'Crea o guarda, entra a Administrar Bloques y ordena la composición.', 'Usa Vista previa, prueba URL y menú en móvil e idiomas; publica o programa solo al final.'],
    continue: 'La página puede existir y aun así no aparecer en el menú: URL, estado, bloque y navegación son piezas separadas. En el sitio público, una página en Borrador o Archivada no se muestra como publicada.',
    finish: ['La URL funciona en cada idioma preparado.', 'La página aparece en el árbol o listado esperado.', 'Los bloques, SEO, menú y estado de publicación coinciden con la aprobación.'],
    trouble: ['Si no aparece en el listado, limpia Estado, Tipo, Padre y búsqueda.', 'Si la URL devuelve error, revisa slug, estado y idioma antes de crear otra página.', 'Si el menú no la muestra, edita el ítem del menú; no cambies la página para forzar la navegación.']
  },
  menus: {
    outcome: 'Crear un menú y luego agregarle ítems que apunten a destinos existentes y probados.',
    before: ['Define si es header, footer o una ubicación secundaria.', 'Confirma clave estable y nombre por idioma.', 'Ten lista de destinos y jerarquía; no empieces escribiendo URLs a mano.'],
    steps: ['Pulsa Nuevo Menú y completa Nombre del menú, Clave del menú y Ubicación; los tres son obligatorios.', 'Deja activo el menú solo cuando esté listo para mostrarse.', 'Abre el detalle y pulsa Nuevo elemento del menú.', 'Completa Etiqueta y elige Tipo de enlace: Página, Entrada, Listado de colección, URL personalizada o Sin enlace.', 'Selecciona el destino y, si corresponde, Elemento padre, destino de pestaña, icono y clase CSS.', 'Guarda el elemento, repite para cada enlace y usa Reordenar para la jerarquía.', 'Pulsa Guardar Orden y prueba header/footer, escritorio, móvil y cada idioma.'],
    continue: 'Guardar el menú y guardar sus ítems son operaciones relacionadas pero separadas; confirma ambas.',
    finish: ['La clave y ubicación son correctas.', 'Cada ítem abre el destino esperado y las etiquetas están traducidas.', 'La jerarquía y el orden se ven igual en la navegación pública.'],
    trouble: ['Si el elemento no aparece, revisa Activo, menú correcto, padre y Guardar Orden.', 'Si el destino está vacío, cambia el Tipo de enlace y completa el selector correspondiente.', 'Si una URL antigua sigue en el menú, corrige el ítem antes de crear una redirección.']
  },
  bloques: {
    outcome: 'Elegir, configurar y comprobar un tipo de bloque sin romper páginas o entradas que ya lo usan.',
    before: ['Identifica el propietario del bloque: Manual, Página, Colección, Entrada o Contenedor.', 'Decide si necesitas un tipo existente o una nueva plantilla.', 'Prueba cambios en una página o entrada de Borrador.'],
    steps: ['En Tipos de Bloque busca la clave antes de crear.', 'Si creas uno, elige primero Origen del contenido; esa selección filtra plantillas.', 'Elige una plantilla como Texto Enriquecido, Formulario Embebido, Imagen, Hero o la que corresponda.', 'Revisa esquema, campos, soporte, hijos permitidos, traducibilidad, activo y orden.', 'Guarda y usa Refrescar caché si el catálogo no refleja el cambio.', 'Abre una página o entrada de Borrador, pulsa Añadir Bloque y confirma que el tipo aparece.', 'Edita la instancia: revisa Configuración, contenido, idioma y Vista previa.', 'Guarda la instancia y prueba renderizado antes de llevarla a una página publicada.'],
    continue: 'El tipo de bloque define la plantilla; la instancia define el contenido. Cambiar uno no equivale a editar el otro.',
    finish: ['El tipo aparece solo donde tiene soporte.', 'La instancia guarda configuración válida y se renderiza.', 'Las páginas existentes conservan su comportamiento esperado.'],
    trouble: ['Si no aparece en el selector, revisa origen, soporte, activo y caché.', 'Si el editor muestra campos inesperados, compara esquema y plantilla.', 'Si una instancia publicada falla, vuelve al esquema anterior o restaura desde una copia aprobada.']
  },
  redirecciones: {
    outcome: 'Migrar una URL sin perder visitantes y sin crear cadenas o bucles.',
    before: ['Confirma que la ruta anterior ya no debe servir contenido.', 'Prueba la nueva URL directamente y verifica idioma, estado y permisos.', 'Decide 301 permanente o 302 temporal.'],
    steps: ['Pulsa Nueva Redirección y completa Ruta anterior con una ruta que empiece por <code>/</code>.', 'Completa Nueva URL con ruta interna o URL externa válida.', 'Añade Nota para explicar origen, motivo y fecha de revisión.', 'Elige tipo 301 o 302 y activa solo después de revisar.', 'Crea y prueba la ruta anterior en ventana privada y en cada host/idioma que corresponda.', 'Revisa menús, páginas y analytics para retirar enlaces que todavía apunten a la ruta vieja.', 'Si importas CSV, valida columnas, duplicados, rutas y bucles antes de importar.'],
    continue: 'Una redirección corrige una ruta antigua; no arregla una página inexistente ni sustituye actualizar el menú.',
    finish: ['La ruta antigua llega una sola vez al destino correcto.', 'No hay bucle ni cadena innecesaria.', 'La nota explica cuándo revisar o retirar la regla.'],
    trouble: ['Si la ruta redirige a sí misma, desactiva la regla y corrige el destino.', 'Si aparece una cadena, actualiza el enlace original y deja un solo salto.', 'Si solo falla un idioma, revisa la ruta traducida y no uses un destino genérico.']
  },
  idiomas: {
    outcome: 'Agregar o ajustar idiomas entendiendo el impacto global en URLs, menús, contenido y fallback.',
    before: ['Confirma el código de localización y plan de traducción.', 'Identifica el idioma predeterminado actual y el fallback.', 'No desactives un idioma para esconder faltantes sin aprobación.'],
    steps: ['Consulta la lista y comprueba cuál está predeterminado y activo.', 'Para crear, completa Código, Nombre y, si corresponde, Nombre nativo.', 'Decide si será el idioma predeterminado o un idioma adicional, y si estará activo.', 'Selecciona Idioma de reserva para el caso de traducción faltante.', 'Crea o guarda y revisa el selector público, URLs y menús.', 'Abre Auditoría de Traducciones y prioriza recursos críticos.', 'Reordena idiomas solo después de comprobar la experiencia pública.'],
    continue: 'Cambiar el idioma predeterminado o el fallback cambia la experiencia de todo el sitio, no solo la pantalla actual.',
    finish: ['Existe un único idioma predeterminado.', 'El selector y las URLs muestran el orden aprobado.', 'El fallback ofrece un contenido entendible y los faltantes están identificados.'],
    trouble: ['Si el idioma no aparece, revisa activo, orden y caché.', 'Si una URL cae al idioma equivocado, comprueba código y fallback.', 'Si faltan traducciones, corrige recursos desde Auditoría en vez de cambiar el idioma global.']
  },
  traducciones: {
    outcome: 'Encontrar faltantes por idioma y corregir el recurso adecuado sin creer que una traducción automática ya está aprobada.',
    before: ['Elige idioma, tipo de recurso y estado pendiente.', 'Define si revisarás texto, slug, SEO, enlace, imagen o configuración.', 'Ten un revisor humano para nombres, legales y llamadas a la acción.'],
    steps: ['Abre Auditoría de Traducciones y usa Búsqueda solo si conoces parte del recurso.', 'Selecciona Idioma y Recurso; empieza por Páginas, Entradas, Menús o Formularios.', 'Filtra pendientes y abre Traducir en el registro concreto.', 'Completa el valor faltante y revisa título, slug, SEO, CTA, enlaces e imágenes.', 'Guarda y abre la URL, menú o formulario en ese idioma.', 'Vuelve a la auditoría y confirma que el registro ya no figura como pendiente.', 'Registra excepciones intencionales, por ejemplo un nombre de marca sin traducir.'],
    continue: 'Traducir el texto no traduce automáticamente un destino, una imagen o una estructura de menú; verifica cada parte.',
    finish: ['El recurso ya no aparece como faltante crítico.', 'La traducción se ve en la URL o pantalla correcta.', 'La propuesta automática fue revisada por una persona.'],
    trouble: ['Si no aparece el recurso, limpia búsqueda y revisa el tipo.', 'Si la pantalla mezcla idiomas, confirma pestaña activa y guarda cada idioma.', 'Si el slug traducido colisiona, elige uno único y revisa redirecciones.']
  },
  'identidad-sitio': {
    outcome: 'Actualizar la marca global y sus mensajes sin romper formularios, SEO, redes o activos por idioma.',
    before: ['Prepara nombre, título, tagline, descripción, contacto, redes, logo y favicon aprobados.', 'Separa valores públicos de claves o secretos técnicos.', 'Define qué campos deben variar por idioma y cuáles deben ser globales.'],
    steps: ['Abre Identidad del sitio y revisa el idioma activo.', 'Completa nombre, título, tagline, descripción, contacto, emails y copyright.', 'Selecciona logo y favicon desde Archivos y comprueba formato, contraste y variante.', 'Revisa redes sociales, analytics y reCAPTCHA; no pegues secretos en campos públicos.', 'Usa Traducir todos los idiomas solo para generar propuestas, no para aprobarlas automáticamente.', 'Guarda y valida header, footer, metadatos, redes y formularios en el sitio público.', 'Repite la comprobación en cada idioma activo.'],
    continue: 'Una modificación aquí puede aparecer en todo el sitio; valida antes de guardar y prueba después en más de una página.',
    finish: ['La marca y contacto visibles son correctos.', 'Logo, favicon, redes y mensajes funcionan.', 'Los formularios siguen protegiéndose y enviando.'],
    trouble: ['Si un activo no aparece, corrige primero Archivos.', 'Si un formulario falla tras cambiar reCAPTCHA, revisa configuración global y claves.', 'Si un idioma queda vacío, corrige su pestaña o vuelve a la traducción anterior aprobada.']
  },
  configuracion: {
    outcome: 'Consultar o cambiar una clave global con el tipo y formato que espera el sistema.',
    before: ['Busca la clave exacta antes de crear otra.', 'Confirma con documentación técnica qué código consume el valor.', 'Haz una copia del valor anterior y define cómo comprobarás el efecto.'],
    steps: ['Usa Buscar y abre Ver para leer clave, valor, tipo, grupo, descripción y si es traducible.', 'Si editas, conserva el tipo: Texto, Número entero, Booleano, JSON o Archivo.', 'Valida JSON y opciones fuera del cambio antes de pegarlo.', 'Revisa si el control visual es texto, URL, email, toggle, imagen, archivo, lista o código.', 'Guarda y prueba el comportamiento concreto del sitio.', 'Si es traducible, cambia de idioma y verifica todos los valores.', 'Registra el cambio global y el valor anterior en el canal autorizado.'],
    continue: 'No crees una clave de prueba para aprender: usa Ver y la guía técnica para entender el contrato primero.',
    finish: ['El valor se interpreta con el tipo esperado.', 'El sitio conserva comportamiento correcto en todos los idiomas.', 'La modificación tiene motivo, responsable y forma de revertirla.'],
    trouble: ['Si el sitio falla después de guardar, restaura el valor anterior aprobado.', 'Si el JSON no valida, revisa comillas, llaves y tipo antes de guardar.', 'Si no sabes qué consume la clave, detén la edición y consulta al equipo técnico.']
  }
};

window.ADMIN_GUIDE_MODULES = window.ADMIN_GUIDE_MODULES.map(module => ({
  ...module,
  novice: window.ADMIN_GUIDE_NOVICE[module.slug] || {}
}));
