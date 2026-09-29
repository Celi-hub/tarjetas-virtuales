<?php
/** @var array $tarjeta */
/** @var array $t */

if (empty($tarjeta['mod_ubi_calen']) || empty($tarjeta['direccion_maps'])) return;
if (empty($tarjeta['fecha_evento'])) return;

// Ícono SVG saneado, reutilizado por cualquier módulo (ver helpers/svg_helpers.php)
require_once __DIR__ . '/../../helpers/svg_helpers.php';

$txt_titulo = $t['titulo_ubicacion'] ?? '¿Cómo llegar?';
$txt_btn    = $t['btn_ver_mapa']     ?? 'Ver en Google Maps';

$hora_evento = $tarjeta['hora_evento'] ?? '12:00:00';
$nombre_ev   = htmlspecialchars($tarjeta['nombre_evento'] ?? $tarjeta['nombres_portada'] ?? 'Evento');
$ubicacion   = htmlspecialchars($tarjeta['direccion_maps'] ?? '');

$txt_titulo2 = $t['titulo_calendario']        ?? 'Agendá el evento';
$txt_btn2    = $t['btn_agregar_calendario']   ?? 'Agregar al calendario';

// Fechas para el link de Google Calendar (formato compacto YYYYMMDDTHHIS)
$f_inicio = date('Ymd\THis', strtotime($tarjeta['fecha_evento'] . ' ' . $hora_evento));
$f_fin    = date('Ymd\THis', strtotime($tarjeta['fecha_evento'] . ' ' . $hora_evento . ' +2 hours'));

$url_gcal = "https://calendar.google.com/calendar/render?action=TEMPLATE"
    . "&text=" . urlencode($nombre_ev)
    . "&dates=" . $f_inicio . "/" . $f_fin
    . "&location=" . urlencode($ubicacion)
    . "&ctz=America/Argentina/Cordoba";
?>
<section class="modulo-fullscreen modulo-ubicacion">
    <div class="modulo-contenido">

        <div class="icono-modulo">
            <?php momentia_render_icono_modulo('ubicacion.svg'); ?>
        </div>

        <h3 class="modulo-titulo"><?php echo htmlspecialchars($txt_titulo); ?></h3>
        <p class="modulo-texto">
            Tocá el botón para abrir la ubicación en Google Maps.
        </p>

        <a
            href="<?php echo htmlspecialchars($tarjeta['direccion_maps']); ?>"
            target="_blank"
            rel="noopener noreferrer"
            class="btn btn--gold"
        >
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" width="16" height="16" aria-hidden="true">
                <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/>
                <circle cx="12" cy="10" r="3"/>
            </svg>
            <?php echo htmlspecialchars($txt_btn); ?>
        </a>

        <div class="divisor-decorativo"><span>✦</span></div>

        <div class="icono-modulo">
            <?php momentia_render_icono_modulo('calendario.svg'); ?>
        </div>

        <h3 class="modulo-titulo"><?php echo htmlspecialchars($txt_titulo2); ?></h3>
        <p class="modulo-texto">Guardalo en tu calendario para que no se te pase.</p>

        <a
            href="<?php echo $url_gcal; ?>"
            target="_blank"
            rel="noopener noreferrer"
            class="btn btn--gold"
        >
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" width="16" height="16" aria-hidden="true">
                <rect x="3" y="4" width="18" height="18" rx="2"/>
                <line x1="16" y1="2" x2="16" y2="6"/>
                <line x1="8"  y1="2" x2="8"  y2="6"/>
                <line x1="3"  y1="10" x2="21" y2="10"/>
            </svg>
            <?php echo htmlspecialchars($txt_btn2); ?>
        </a>

    </div>
</section>