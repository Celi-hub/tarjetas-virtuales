<?php
/**
 * helpers/svg_helpers.php
 *
 * Funciones para cargar íconos SVG desde /img/img_modulos/ y dejarlos listos
 * para ser coloreados por CSS (currentColor), sin importar cómo vengan
 * exportados (Corel suele meter encoding raro, un <style> propio, y fill/
 * class hardcodeados que pisan la paleta del tema activo).
 *
 * Se incluye UNA sola vez desde el motor de renderizado (visualizar_tarjeta.php),
 * antes de recorrer los módulos activos. Ningún mod_*.php debe declarar su
 * propia versión de esta función.
 */

if (!function_exists('momentia_render_icono_modulo')) {

    /**
     * Imprime el contenido saneado de un SVG ubicado en /img/img_modulos/.
     *
     * @param string $nombre_archivo Ej: 'ubicacion.svg', 'calendario.svg'
     * @param string $carpeta        Subcarpeta de /img/ (por defecto 'img_modulos'; '' para la raíz)
     */
    function momentia_render_icono_modulo(string $nombre_archivo, string $carpeta = 'img_modulos'): void {
        $nombre_archivo = basename($nombre_archivo);
        $carpeta        = $carpeta === '' ? '' : basename($carpeta) . '/';
        $ruta = __DIR__ . '/../img/' . $carpeta . $nombre_archivo;

        if (!file_exists($ruta)) {
            echo '<!-- Ícono no encontrado: ' . htmlspecialchars($nombre_archivo) . ' -->';
            return;
        }

        $svg = file_get_contents($ruta);

        // 1. Normalización de codificación y remoción de cabecera XML / DOCTYPE / comentarios
        $svg = mb_convert_encoding($svg, 'UTF-8', 'UTF-16, UTF-8');
        $svg = preg_replace('/<\?xml.*?\?>/i', '', $svg);
        $svg = preg_replace('/<!DOCTYPE[^>]*>/i', '', $svg);
        $svg = preg_replace('/<!--.*?-->/s', '', $svg);

        // 2. Eliminación del bloque <style> interno exportado por Corel
        $svg = preg_replace('/<style\b[^>]*>.*?<\/style>/is', '', $svg);

        // 3. Eliminación de atributos de color inline y clases asociadas,
        //    para que el color lo defina .icono-modulo svg { fill: currentColor; }
        $svg = preg_replace('/fill="[^"]*"/i', '', $svg);
        $svg = preg_replace('/class="[^"]*"/i', '', $svg);

        echo $svg;
    }
}