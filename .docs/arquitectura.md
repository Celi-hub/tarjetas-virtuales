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
   - Si la tarjeta tiene `mod_foto_portada` (Deluxe), el mismo POST trae el archivo `foto_portada`; ambos scripts lo delegan a `helpers/foto_portada.php`. Si la foto falla (formato/peso) los demás datos se guardan igual y el dashboard muestra el aviso `foto_no_guardada`.
3. **Gestión (`dashboard.php`):** Panel maestro que lista tarjetas (`card-pendiente` o `card-pagada`, según `tarjetas.estado_pago`) y da acceso a sub-paneles de control (RSVP, Pagos, Muro).

## Eliminación de una Tarjeta
1. `dashboard.php` abre un modal que es un `<form method="POST">` hacia `eliminar_tarjeta.php` con `csrf_token` (`$_SESSION['csrf_token_eliminar_tarjeta']`) e `id_tarjeta`.
2. El modal advierte qué se pierde: cuenta RSVP, mensajes del muro, canciones, pagos informados, hitos y fotos (una consulta agregada aparte; si falla, cae a un aviso genérico). Si la tarjeta está pagada avisa que el link compartido dejará de funcionar, y si hay comprobantes sugiere descargarlos antes. El botón queda deshabilitado hasta marcar "Entiendo que no se puede recuperar" (`confirmo_eliminacion`, que el servidor vuelve a validar).
3. `eliminar_tarjeta.php` (solo POST): valida token, confirmación y propiedad (`id_cliente`). **Antes** del `DELETE` lista los archivos con `helpers/archivos_tarjeta.php` (el `ON DELETE CASCADE` se lleva las filas de hitos/pagos que dicen qué archivo es de quién) y **después** de que el `DELETE` se confirmó los borra. Si alguno no se pudo borrar redirige con `msg=eliminada_con_avisos` y lo deja en `error_log`.
4. Las alertas del dashboard leen `?mensaje=`, `?msg=` o `?error=` contra el array `$alertas`.

## Motor de Renderizado Visual (`visualizar_tarjeta.php`)
- Reconstruye la interfaz iterando sobre configuraciones en BD.
- Carga el diseño de base (`plantillas/modulos/base_portada.php`).
- Carga primero el núcleo (`css/base_tarjeta.css`: estructura, componentes y módulos gobernados por tokens) y luego el tema elegido (`css/{slug}.css`: tokens + decoración). Detalle en [estilos.md](estilos.md).
- El `<body>` recibe `class="tema-{slug}"`; el nombre del estilo se normaliza a slug sin tildes y cae a `elegante` si no existe la hoja.
- **Inclusión Condicional:** Un loop atraviesa las `plantillas_activas($tarjeta)` incluyendo dinámicamente los scripts `mod_*.php` contenidos en la carpeta `plantillas/modulos/`.
- **Diccionario de textos (`$t`):** se construye por capas, cada una pisa a la anterior: `idiomas/{lang}.php` (base, hoy solo `es`) → `config/textos_evento.php` según `tipo_evento` de la tarjeta → `tarjetas.textos_personalizados` (JSON con los textos que el cliente personalizó). Ningún módulo debe reasignar `$t` (ver [modulos.md](modulos.md)).

## Flujo de Cobro del Producto (SaaS)
Distinto del módulo `mod_pagar_evento` (recaudación entre invitados de la boda/evento, ver [base_datos.md](base_datos.md)): este flujo es el cobro de Momentia al cliente por la tarjeta que creó.
1. `dashboard.php` enlaza a `pagar.php?id=` para cada tarjeta con `estado_pago != 'pagado'`.
2. `pagar.php` lee la tabla `costos` (precio `base` + precio por cada columna `mod_*` activa en la tarjeta) y muestra el desglose y el total.
3. **Incompleto:** los formularios de `pagar.php` postean a `procesar_pago.php`, que no existe todavía en el repo. `pagar_seleccionadas.php` (pago en lote de varias tarjetas) tampoco está enlazado desde ningún lado y postea a `confirmar_pago.php`, igualmente inexistente. Tratar ambos como flujo a terminar, no como funcionalidad activa.
