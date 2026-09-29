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
- `helpers/svg_helpers.php` → `momentia_render_icono_modulo(string $nombre_archivo, string $carpeta = 'img_modulos')`: carga un SVG desde `img/{carpeta}/` (`''` = raíz de `img/`), le quita encabezado XML, bloque `<style>` (típico de exportaciones de Corel), y atributos `fill`/`class` propios, para que el color lo controle el CSS del tema activo vía `currentColor`. Usado por `mod_ubi_calen.php`; cualquier módulo nuevo que cargue íconos de archivo debe reutilizarlo.