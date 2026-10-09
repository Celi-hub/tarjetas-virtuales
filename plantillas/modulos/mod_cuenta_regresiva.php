<?php
/** @var array $tarjeta */
/** @var array $t */

if (empty($tarjeta['mod_cuenta_regresiva']) || empty($tarjeta['fecha_evento'])) return;

require_once __DIR__ . '/../../helpers/svg_helpers.php';

// El objetivo se calcula en la zona horaria del evento (la misma que usa el link de
// Google Calendar en mod_ubi_calen.php), así el invitado ve la cuenta correcta
// aunque abra la tarjeta desde otro huso horario.
try {
    $zona_cr     = new DateTimeZone('America/Argentina/Cordoba');
    $objetivo_cr = new DateTimeImmutable($tarjeta['fecha_evento'] . ' ' . ($tarjeta['hora_evento'] ?: '00:00:00'), $zona_cr);
} catch (Exception $e) {
    return;
}

$restante_cr = $objetivo_cr->getTimestamp() - time();

// Pasado más de un día del evento, ya no hay nada que contar.
if ($restante_cr < -86400) return;

$segundos_cr = max(0, $restante_cr);
$valores_cr  = [
    'dias'     => intdiv($segundos_cr, 86400),
    'horas'    => intdiv($segundos_cr % 86400, 3600),
    'minutos'  => intdiv($segundos_cr % 3600, 60),
    'segundos' => $segundos_cr % 60,
];
$etiquetas_cr = ['dias' => 'Días', 'horas' => 'Horas', 'minutos' => 'Minutos', 'segundos' => 'Segundos'];

$txt_titulo_cr = $t['titulo_cuenta_regresiva'] ?? 'Falta muy poco';
$txt_fin_cr    = $t['cuenta_regresiva_fin']    ?? '¡Llegó el gran día!';
$id_cr         = 'cuenta_' . (int)$tarjeta['id_tarjeta'];
?>
<section class="modulo-fullscreen modulo-cuenta-regresiva">
    <div class="modulo-contenido">

        <div class="icono-modulo">
            <?php momentia_render_icono_modulo('cuenta_regresiva.svg'); ?>
        </div>

        <span class="eyebrow modulo-subtitulo">Cuenta regresiva</span>
        <h3 class="modulo-titulo"><?php echo htmlspecialchars($txt_titulo_cr); ?></h3>

        <div
            id="<?php echo $id_cr; ?>"
            class="cuenta-regresiva"
            role="timer"
            data-objetivo="<?php echo $objetivo_cr->getTimestamp() * 1000; ?>"
            <?php echo $segundos_cr === 0 ? 'hidden' : ''; ?>
        >
            <?php foreach ($valores_cr as $unidad_cr => $valor_cr): ?>
                <div class="cuenta-unidad">
                    <span class="cuenta-numero" data-unidad="<?php echo $unidad_cr; ?>"><?php echo str_pad((string)$valor_cr, 2, '0', STR_PAD_LEFT); ?></span>
                    <span class="cuenta-etiqueta"><?php echo $etiquetas_cr[$unidad_cr]; ?></span>
                </div>
            <?php endforeach; ?>
        </div>

        <p id="<?php echo $id_cr; ?>_fin" class="cuenta-fin" <?php echo $segundos_cr === 0 ? '' : 'hidden'; ?>>
            <?php echo htmlspecialchars($txt_fin_cr); ?>
        </p>

    </div>
</section>

<script>
(function () {
    var reloj = document.getElementById('<?php echo $id_cr; ?>');
    var fin   = document.getElementById('<?php echo $id_cr; ?>_fin');
    if (!reloj || reloj.hidden) return;

    var objetivo = parseInt(reloj.getAttribute('data-objetivo'), 10);
    var celdas   = {};
    reloj.querySelectorAll('[data-unidad]').forEach(function (el) {
        celdas[el.getAttribute('data-unidad')] = el;
    });

    function dosDigitos(n) { return n < 10 ? '0' + n : String(n); }

    function pintar(unidad, valor) {
        var texto = dosDigitos(valor);
        if (celdas[unidad].textContent !== texto) celdas[unidad].textContent = texto;
    }

    var intervalo = null;

    // Se recalcula desde el reloj del sistema en cada tick (sin acumular desvíos
    // si el navegador frena el intervalo con la pestaña en segundo plano).
    function actualizar() {
        var restante = Math.max(0, Math.floor((objetivo - Date.now()) / 1000));
        pintar('dias',     Math.floor(restante / 86400));
        pintar('horas',    Math.floor((restante % 86400) / 3600));
        pintar('minutos',  Math.floor((restante % 3600) / 60));
        pintar('segundos', restante % 60);

        if (restante === 0) {
            clearInterval(intervalo);
            reloj.hidden = true;
            fin.hidden   = false;
        }
    }

    actualizar();
    intervalo = setInterval(actualizar, 1000);
})();
</script>
