# Sistema de Módulos Dinámicos

La arquitectura de Momentia permite escalar las funcionalidades de la invitación virtual activando o desactivando fragmentos de interfaz (SaaS approach).

## Convenciones para Componentes (Frontend)
- Los módulos deben envolverse en `<section class="modulo-fullscreen">`.
- Las interacciones de estado (ej: animaciones scroll) aprovechan `IntersectionObserver`.
- Conservar limpieza en la jerarquía CSS usando convenciones BEM-lite o heredando de variables root.

## ¿Cómo Desarrollar un Nuevo Módulo?
1. **Modificación en BD:** Crear la columna de tipo booleano (`mod_[nombre]`) en la tabla `tarjetas`. Si requiere recolección de datos 1:N, crear la respectiva tabla satélite.
2. **Registro de Configuración:** Declarar el módulo y sus campos editables dentro del array de configuración maestro (`config/modulos.php`).
3. **Creación de la Vista:** Construir el archivo en `plantillas/modulos/mod_[nombre].php`.
4. **Regla Estricta de Renderizado:** La primera línea lógica del archivo de la vista DEBE bloquear la carga si el módulo no está activo:
   ```php
   if (empty($tarjeta['mod_[nombre]'])) return;
   ```
5. **Assets:** los `<script>` propios del módulo van autocontenidos en el archivo del módulo. Los **estilos NO**: van en `css/base_tarjeta.css` (sección 6) usando tokens, para que los 6 temas los hereden. Prohibido `<style>` y `style=""` en `mod_*.php`.
6. **Variables:** no reasignar `$t` (textos) ni `$tarjeta` dentro de un módulo; usar nombres propios en los `foreach`.

## Helpers Compartidos entre Módulos
Cuando dos o más módulos necesitan la misma lógica de PHP (ej. cargar y sanear un ícono SVG), esa lógica **no se declara dentro del `mod_[nombre].php`**. Se extrae a un archivo en `helpers/` y se incluye con `require_once` desde cada módulo que la necesite.

- **Ubicación:** `helpers/[nombre].php` (ej. `helpers/svg_helpers.php`).
- **Inclusión:** `require_once __DIR__ . '/../../helpers/[nombre].php';` desde `plantillas/modulos/mod_*.php`. Usar `require_once`, no una declaración de función inline con `function_exists` — el motor puede incluir varios módulos en la misma request, y `require_once` ya deduplica sin necesidad de ese parche.
- **Convención de nombres:** toda función de un helper compartido lleva el prefijo `momentia_` para evitar colisiones con nombres de otras partes del sistema.

**Helpers existentes:**
- `helpers/foto_portada.php` → `momentia_procesar_foto_portada(array $tarjeta)` (valida tipo real JPG/PNG/WEBP y ≤ 8 MB, guarda en `uploads/portadas/`, borra la anterior, atiende el checkbox `quitar_foto_portada`; devuelve `['cambio','ruta','error']`) y `momentia_url_foto_portada(array $tarjeta)` (ruta pública o `null`). Usado por `guardar_tarjeta.php`, `actualizar_tarjeta_completa.php` y `base_portada.php`.
- `helpers/archivos_tarjeta.php` → `momentia_archivos_de_tarjeta(PDO $pdo, array $tarjeta)` (rutas absolutas existentes del book, hitos, comprobantes y foto de portada; cada ruta = carpeta fija + `basename()`) y `momentia_borrar_archivos_tarjeta(int $id, array $archivos)` (borra y quita la carpeta del book; devuelve la cantidad de fallas). Usado por `eliminar_tarjeta.php`. **Si un módulo nuevo guarda archivos en `uploads/`, hay que sumarlo acá** o quedarán huérfanos al eliminar la tarjeta.
- `helpers/svg_helpers.php` → `momentia_render_icono_modulo(string $nombre_archivo, string $carpeta = 'img_modulos')`: carga un SVG desde `img/{carpeta}/` (`''` = raíz de `img/`), le quita encabezado XML, bloque `<style>` (típico de exportaciones de Corel), y atributos `fill`/`class` propios, para que el color lo controle el CSS del tema activo vía `currentColor`. Usado por `mod_ubi_calen.php`; cualquier módulo nuevo que cargue íconos de archivo debe reutilizarlo.

## Cuenta regresiva (`mod_cuenta_regresiva`)
Sección propia (orden 10, justo después de la portada). Requiere `fecha_evento`; usa `hora_evento` o 00:00. El objetivo se calcula en PHP en la zona `America/Argentina/Cordoba` (la misma del link de Google Calendar) y viaja como timestamp en `data-objetivo`, así la cuenta es correcta desde cualquier huso horario. Los valores iniciales salen renderizados desde el servidor; el JS recalcula contra `Date.now()` cada segundo. Al llegar a cero muestra `$t['cuenta_regresiva_fin']` ("¡Llegó el gran día!"); pasado un día del evento el módulo no se renderiza. Textos: `$t['titulo_cuenta_regresiva']`. Estilos: sección 6.13 de `base_tarjeta.css` (`.cuenta-unidad` es una superficie).

## Módulos sin sección propia
Un módulo puede declarar `'plantilla' => ''` en `config/modulos.php`: se elige, se cobra y tiene campos como cualquier otro, pero `plantillas_activas()` lo saltea porque lo renderiza otra pieza.
- **`mod_foto_portada` (Deluxe):** foto personal de fondo de la portada, disponible en todos los estilos. Campo de tipo `file_portada` (input `foto_portada` + vista previa y checkbox `quitar_foto_portada` en edición, ver `includes/campos_formulario.php`). Lo renderiza `base_portada.php` como `<div class="portada-foto">` y agrega `.portada--con-foto` a la sección; cada tema la ajusta con los tokens `--foto-*` (ver [estilos.md](estilos.md)).
- Los tipos de campo de archivo (`file_book`, `file_portada`) e `info_aviso` no se guardan como texto: están excluidos en `campos_datos_para_tarjeta()` y en `actualizar_tarjeta_completa.php`.
