# Estructura de la Base de Datos

El diseño del esquema está altamente normalizado, orientado hacia la Tercera Forma Normal (3FN). Se prioriza mantener la tabla principal ligera delegando la lógica de los módulos a tablas satélite relacionadas.

## Tablas Core
- **`clientes`**: Identidad de usuario (`id_cliente` PK, `email`, `password`, datos personales).
- **`tarjetas`**: Entidad maestra del producto. 
  - Almacena Foreign Keys básicas (`id_cliente`, `tipo_evento_id`, `estilo_visual_id`).
  - Almacena campos transversales (nombres, fechas, frase de portada).
  - Almacena **Flags de Módulos** (`TINYINT`): Banderas booleanas (ej. `mod_rsvp_interno`, `mod_playlist`) que dictan qué módulos renderizar.
  - **Foto de portada (Deluxe):** `mod_foto_portada` (flag, cobrado vía `costos`) + `foto_portada` (`VARCHAR(255)`, ruta relativa `uploads/portadas/portada_{id}_{hex}.{jpg|png|webp}`). La escribe `helpers/foto_portada.php`; se muestra solo si el flag está activo y el archivo existe.

## Tablas Satélite (Módulos)
Diseñadas para almacenar colecciones 1:N o configuraciones específicas. Todas poseen una Foreign Key apuntando a `tarjetas(id_tarjeta)` con `ON DELETE CASCADE`: borrar la tarjeta borra sus filas. Los **archivos** en `uploads/` no los borra la BD: de eso se ocupa `helpers/archivos_tarjeta.php` (ver flujo de eliminación en [arquitectura.md](arquitectura.md)).
- **Archivos por tarjeta:** `uploads/book/{id}/` (book de fotos), `uploads/hitos/{linea_tiempo_hitos.foto}`, `uploads/comprobantes/` (`pagos_evento.comprobante_img`, se guarda con el prefijo `uploads/comprobantes/`) y `uploads/portadas/` (`tarjetas.foto_portada`).
- **`muro_deseos`**: Mensajes de invitados con control de estado (`pendiente` / `aprobado`) regido por `muro_moderacion` de la tarjeta.
- **`rsvp_respuestas` y `rsvp_acompanantes`**: Diseño normalizado 1:N para invitados titulares y el detalle de sus acompañantes.
- **`linea_tiempo_hitos`**: Eventos cronológicos con ordenamiento (`orden`, `fecha_anio`, `foto`).
- **`playlist_sugerencias`**: Canciones (`cancion`, `artista`) recolectadas del público.
- **`pagos_evento`** y **`config_pagos`**: Almacenamiento de JSON de tarifas, comprobantes adjuntos y manejo de estados (aprobado/rechazado). Alimentan el módulo `mod_pagar_evento` (recaudación entre invitados), **no** el cobro del producto (ver abajo).

## Tablas de Catálogo / Configuración
Datos maestros, sin Foreign Key hacia `tarjetas` (es `tarjetas` la que apunta a ellas).
- **`tipos_evento`**: catálogo de eventos (`id_tipos_evento`, `nombre`, `label_portada`, `placeholder_portada`). `tarjetas.tipo_evento_id` apunta aquí; `config/modulos.php` usa `nombre` (slug) para habilitar módulos según el tipo de evento.
- **`estilos_visuales`**: catálogo de temas (`id_estilos_visuales`, `nombre`). `tarjetas.estilo_visual_id` apunta aquí; el `nombre` se normaliza a slug para resolver el CSS (ver [estilos.md](estilos.md)).
- **`plantillas`**: catálogo de layouts base (`id_plantilla`, `nombre`, `descripcion`). `tarjetas.id_plantilla` apunta aquí.
- **`frases_sugeridas`**: frases de portada sugeridas por tipo de evento (`tipo_evento_id`, `texto_frase`), usadas en el wizard (`personalizar.php`) como ayuda para `frase_portada`.
- **`costos`**: precio de cada módulo (`modulo_key` ↔ columnas `mod_*` de `tarjetas`, más `base`) y `precio`. Es la tabla de precios del **cobro del producto** (no del evento): la usan `pagar.php` y `pagar_seleccionadas.php` para calcular el total a cobrarle al cliente por su tarjeta según los módulos activos (`tarjetas.estado_pago`).
- **`planes`**: solo tiene `id_planes`; no tiene columnas de datos ni se referencia desde el código PHP actual. Placeholder sin uso todavía.

## Migraciones
Los cambios de esquema sobre una base ya creada van en `migraciones/AAAA-MM-DD_descripcion.sql` (ejecutar una vez). `tarjeta_virtual.sql` se mantiene al día para instalaciones nuevas (Docker lo carga al crear el volumen).
- `2026-10-08_foto_portada.sql`: columnas `tarjetas.mod_foto_portada` / `tarjetas.foto_portada` y fila `costos.mod_foto_portada`.
