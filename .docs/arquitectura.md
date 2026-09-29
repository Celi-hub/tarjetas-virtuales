# Arquitectura y Flujos Principales

El proyecto Momentia gestiona la lógica de negocio a través de flujos lineales e inclusiones dinámicas.

## Autenticación y Sesión
- El control de acceso se basa estrictamente en el estado de `$_SESSION['id_usuario']`.
- Archivos clave: `login.php`, `registro.php`, `validar_login.php`, `logout.php`.
- Seguridad: Uso de `password_hash()` (BCRYPT) y `session_regenerate_id()` para prevenir session fixation.

## Flujo de Creación (Wizard SaaS)
La instanciación de un nuevo producto se da en pasos para optimizar la UX:
1. **Configuración Base (`personalizar.php` -> `procesar_paso1.php`):** 
   - El usuario selecciona tipo de evento, estilo visual y módulos deseados (RSVP, línea de tiempo, etc.).
   - Se crea el registro principal en la tabla `tarjetas`.
2. **Carga de Datos (`completar_datos.php` -> `guardar_tarjeta.php` / `actualizar_tarjeta_completa.php`):**
   - Llenado de información detallada (nombres, fechas, textos personalizados, carga de imágenes).
3. **Gestión (`dashboard.php`):** Panel maestro que lista tarjetas (`card-pendiente` o `card-pagada`) y da acceso a sub-paneles de control (RSVP, Pagos, Muro).

## Motor de Renderizado Visual (`visualizar_tarjeta.php`)
- Reconstruye la interfaz iterando sobre configuraciones en BD.
- Carga el diseño de base (`plantillas/modulos/base_portada.php`).
- Carga primero el núcleo (`css/base_tarjeta.css`: estructura, componentes y módulos gobernados por tokens) y luego el tema elegido (`css/{slug}.css`: tokens + decoración). Detalle en [estilos.md](estilos.md).
- El `<body>` recibe `class="tema-{slug}"`; el nombre del estilo se normaliza a slug sin tildes y cae a `elegante` si no existe la hoja.
- **Inclusión Condicional:** Un loop atraviesa las `plantillas_activas($tarjeta)` incluyendo dinámicamente los scripts `mod_*.php` contenidos en la carpeta `plantillas/modulos/`.
