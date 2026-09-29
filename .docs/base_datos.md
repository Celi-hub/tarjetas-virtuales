# Estructura de la Base de Datos

El diseño del esquema está altamente normalizado, orientado hacia la Tercera Forma Normal (3FN). Se prioriza mantener la tabla principal ligera delegando la lógica de los módulos a tablas satélite relacionadas.

## Tablas Core
- **`clientes`**: Identidad de usuario (`id_cliente` PK, `email`, `password`, datos personales).
- **`tarjetas`**: Entidad maestra del producto. 
  - Almacena Foreign Keys básicas (`id_cliente`, `tipo_evento_id`, `estilo_visual_id`).
  - Almacena campos transversales (nombres, fechas, frase de portada).
  - Almacena **Flags de Módulos** (`TINYINT`): Banderas booleanas (ej. `mod_rsvp_interno`, `mod_playlist`) que dictan qué módulos renderizar.

## Tablas Satélite (Módulos)
Diseñadas para almacenar colecciones 1:N o configuraciones específicas. Todas poseen una Foreign Key apuntando a `tarjetas(id_tarjeta)`.
- **`muro_deseos`**: Mensajes de invitados con control de estado (`pendiente` / `aprobado`) regido por `muro_moderacion` de la tarjeta.
- **`rsvp_respuestas` y `rsvp_acompanantes`**: Diseño normalizado 1:N para invitados titulares y el detalle de sus acompañantes.
- **`linea_tiempo_hitos`**: Eventos cronológicos con ordenamiento (`orden`, `fecha_anio`, `foto`).
- **`playlist_sugerencias`**: Canciones (`cancion`, `artista`) recolectadas del público.
- **`pagos_evento`** y **`config_pagos`**: Almacenamiento de JSON de tarifas, comprobantes adjuntos y manejo de estados (aprobado/rechazado).
