<?php
/** @var array $tarjeta */
/** @var array $t */

if (empty($tarjeta['telefono_whatsapp'])) return;

$txt_titulo  = $t['titulo_confirmacion']  ?? 'Confirmá tu Asistencia';
$txt_bajada  = $t['texto_confirmacion']   ?? 'Tu respuesta nos ayuda a organizar mejor el evento.';
$txt_boton   = $t['btn_whatsapp']         ?? 'Confirmar por WhatsApp';

$fecha_limite_texto = null;
if (!empty($tarjeta['fecha_limite_rsvp'])) {
    $meses = [
        1 => 'enero', 2 => 'febrero', 3 => 'marzo', 4 => 'abril',
        5 => 'mayo', 6 => 'junio', 7 => 'julio', 8 => 'agosto',
        9 => 'septiembre', 10 => 'octubre', 11 => 'noviembre', 12 => 'diciembre',
    ];
    $ts = strtotime($tarjeta['fecha_limite_rsvp']);
    if ($ts !== false) {
        $dia  = (int)date('j', $ts);
        $mes  = $meses[(int)date('n', $ts)];
        $anio = date('Y', $ts);
        $fecha_limite_texto = "{$dia} de {$mes} de {$anio}";
    }
}

// Construimos el mensaje base — el invitado lo puede editar antes de enviar
$nombre_evento = htmlspecialchars($tarjeta['nombres_portada'] ?? 'el evento');
$mensaje_base  = urlencode("¡Hola! Te confirmo mi asistencia al evento de {$nombre_evento}.");
$numero_limpio = preg_replace('/[^0-9]/', '', $tarjeta['telefono_whatsapp']);
$link_wa       = "https://wa.me/{$numero_limpio}?text={$mensaje_base}";
?>
<section class="modulo-fullscreen modulo-rsvp-whatsapp">
    <div class="modulo-contenido">

        <h3 class="modulo-titulo"><?php echo htmlspecialchars($txt_titulo); ?></h3>
        <p class="modulo-texto rsvp-subtitle"><?php echo htmlspecialchars($txt_bajada); ?></p>

        <?php if ($fecha_limite_texto): ?>
            <p class="rsvp-fecha-limite">
                📅 Podés confirmar hasta el <strong><?php echo htmlspecialchars($fecha_limite_texto); ?></strong>
            </p>
        <?php endif; ?>

        <!-- Selector Asistiré / No asistiré -->
        <div class="campo-form-opciones rsvp-opciones" id="wa-opciones">
            <div class="rsvp-opcion-item">
                <input type="radio" name="wa_estado" id="wa_si" value="si">
                <label for="wa_si">✓ Asistiré</label>
            </div>
            <div class="rsvp-opcion-item">
                <input type="radio" name="wa_estado" id="wa_no" value="no">
                <label for="wa_no">✕ No asistiré</label>
            </div>
        </div>

        <!-- Bloque de datos (aparece al elegir opción) -->
        <div id="wa-bloque-datos" class="rsvp-datos-wrap" hidden>

            <!-- Nombre -->
            <div class="campo-form">
                <label for="wa-nombre">Tu nombre y apellido *</label>
                <input type="text" id="wa-nombre" placeholder="Ej: Ana García" autocomplete="name">
            </div>

            <!-- Acompañantes (solo si asiste) -->
            <div id="wa-bloque-acomp" hidden>
                <div class="campo-form">
                    <label for="wa-tiene-acomp">¿Venís acompañado/a?</label>
                    <select id="wa-tiene-acomp">
                        <option value="" selected disabled>Seleccioná una opción...</option>
                        <option value="no">No, voy solo/a</option>
                        <option value="si">Sí, voy acompañado/a</option>
                    </select>
                </div>
                <div class="campo-form" id="wa-cant-acomp-wrap" hidden>
                    <label for="wa-cant-acomp">¿Cuántos acompañantes?</label>
                    <select id="wa-cant-acomp">
                        <option value="0" selected disabled>Seleccioná cantidad...</option>
                        <option value="1">1 acompañante</option>
                        <option value="2">2 acompañantes</option>
                        <option value="3">3 acompañantes</option>
                        <option value="4">4 acompañantes</option>
                        <option value="5">5 o más</option>
                    </select>
                </div>
                <div id="wa-nombres-acomp"></div>
            </div>

            <!-- Mensaje opcional -->
            <div class="campo-form">
                <label id="wa-label-mensaje">Mensaje (opcional)</label>
                <textarea id="wa-mensaje" rows="2" placeholder="Restricciones alimenticias u otra consulta..."></textarea>
            </div>

            <!-- Error -->
            <p id="wa-error" class="rsvp-error" hidden></p>

            <!-- Botón WhatsApp -->
            <a href="<?php echo htmlspecialchars($link_wa); ?>" id="wa-btn-enviar" class="btn btn--whatsapp rsvp-btn-enviar" target="_blank" rel="noopener noreferrer">
                <svg viewBox="0 0 24 24" fill="currentColor" width="18" height="18" aria-hidden="true">
                    <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                </svg>
                <?php echo htmlspecialchars($txt_boton); ?>
            </a>
        </div>

    </div>
</section>

<script>
(function () {
    var numero = '<?php echo $numero_limpio; ?>';
    var nombreEvento = '<?php echo addslashes($nombre_evento); ?>';

    var radios        = document.querySelectorAll('input[name="wa_estado"]');
    var bloqueData    = document.getElementById('wa-bloque-datos');
    var bloqueAcomp   = document.getElementById('wa-bloque-acomp');
    var selectAcomp   = document.getElementById('wa-tiene-acomp');
    var cantWrap      = document.getElementById('wa-cant-acomp-wrap');
    var selectCant    = document.getElementById('wa-cant-acomp');
    var nombresWrap   = document.getElementById('wa-nombres-acomp');
    var inputNombre   = document.getElementById('wa-nombre');
    var textarea      = document.getElementById('wa-mensaje');
    var labelMsg      = document.getElementById('wa-label-mensaje');
    var btnEnviar     = document.getElementById('wa-btn-enviar');
    var errorP        = document.getElementById('wa-error');

    var estadoActual  = null;

    // Actualizar link de WhatsApp dinámicamente
    function actualizarLink() {
        var nombre = inputNombre.value.trim();
        var mensaje = textarea.value.trim();
        var estado  = estadoActual;

        if (!nombre) return;

        var partes = [];

        if (estado === 'si') {
            partes.push('¡Hola! Confirmo mi asistencia al evento de ' + nombreEvento + '.');
            partes.push('Mi nombre: ' + nombre + '.');

            var tieneAcomp = selectAcomp.value;
            if (tieneAcomp === 'no') {
                partes.push('Voy solo/a.');
            } else if (tieneAcomp === 'si') {
                var cant = parseInt(selectCant.value) || 0;
                if (cant > 0) {
                    partes.push('Voy con ' + cant + ' acompañante(s).');
                    // Nombres de acompañantes
                    var inputs = nombresWrap.querySelectorAll('input[type="text"]');
                    var nombresAcomp = [];
                    inputs.forEach(function(inp) {
                        if (inp.value.trim()) nombresAcomp.push(inp.value.trim());
                    });
                    if (nombresAcomp.length) {
                        partes.push('Acompañantes: ' + nombresAcomp.join(', ') + '.');
                    }
                }
            }
        } else {
            partes.push('¡Hola! Lamentablemente no voy a poder asistir al evento de ' + nombreEvento + '.');
            partes.push('Mi nombre: ' + nombre + '.');
        }

        if (mensaje) partes.push(mensaje);

        var textoFinal = partes.join(' ');
        btnEnviar.href = 'https://wa.me/' + numero + '?text=' + encodeURIComponent(textoFinal);
    }

    // Click en Asistiré / No asistiré
    radios.forEach(function(radio) {
        radio.addEventListener('change', function() {
            estadoActual = this.value;
            bloqueData.hidden = false;

            if (this.value === 'si') {
                bloqueAcomp.hidden = false;
                textarea.placeholder = 'Restricciones alimenticias u otra consulta...';
                labelMsg.textContent = 'Mensaje (opcional)';
            } else {
                bloqueAcomp.hidden = true;
                selectAcomp.value = '';
                selectCant.value = '0';
                cantWrap.hidden = true;
                nombresWrap.innerHTML = '';
                textarea.placeholder = 'Dejanos un saludo o contanos por qué no podés venir...';
                labelMsg.textContent = 'Saludo o aclaración (opcional)';
            }
            actualizarLink();
        });
    });

    // ¿Venís acompañado?
    selectAcomp.addEventListener('change', function() {
        if (this.value === 'si') {
            cantWrap.hidden = false;
        } else {
            cantWrap.hidden = true;
            selectCant.value = '0';
            nombresWrap.innerHTML = '';
        }
        actualizarLink();
    });

    // Cantidad de acompañantes → campos de nombre
    selectCant.addEventListener('change', function() {
        var cant = parseInt(this.value) || 0;
        nombresWrap.innerHTML = '';
        for (var i = 1; i <= cant; i++) {
            var div = document.createElement('div');
            div.className = 'campo-form';
            div.innerHTML = '<label>Nombre del acompañante ' + i + '</label>'
                          + '<input type="text" placeholder="Nombre y apellido">';
            nombresWrap.appendChild(div);
        }
        actualizarLink();
    });

    // Actualizar link cuando escribe
    inputNombre.addEventListener('input', actualizarLink);
    textarea.addEventListener('input', actualizarLink);
    nombresWrap.addEventListener('input', actualizarLink);

    // Validar antes de abrir WhatsApp
    btnEnviar.addEventListener('click', function(e) {
        errorP.hidden = true;
        errorP.textContent = '';
        if (!estadoActual) {
            e.preventDefault();
            errorP.textContent = 'Por favor, indicá si vas a asistir.';
            errorP.hidden = false;
            return;
        }
        if (!inputNombre.value.trim()) {
            e.preventDefault();
            errorP.textContent = 'Por favor, ingresá tu nombre.';
            errorP.hidden = false;
            return;
        }
        if (estadoActual === 'si' && selectAcomp.value === 'si' && selectCant.value === '0') {
            e.preventDefault();
            errorP.textContent = 'Por favor, indicá cuántos acompañantes vienen.';
            errorP.hidden = false;
            return;
        }
    });

})();
</script>