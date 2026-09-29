<?php
/** @var array $tarjeta */
/** @var array $t */

// Validamos si hay algo que mostrar (datos bancarios o mensaje)
if (!empty($tarjeta['datos_bancarios']) || !empty($tarjeta['mensaje_regalo'])) {
    require_once __DIR__ . '/../../helpers/svg_helpers.php';
    
    // Textos del diccionario para este módulo
    $txt_titulo_regalos = $t['titulo_regalos'] ?? 'Regalos';
    $txt_mensaje_defecto = $t['mensaje_regalos_defecto'] ?? 'Tu presencia es nuestro mejor regalo. Si deseás ayudarnos con nuestra luna de miel, podés hacerlo aquí:';
    $txt_boton_copiar = $t['btn_copiar_datos'] ?? 'Copiar datos bancarios';
    $txt_copiado_ok = $t['msg_copiado_exito'] ?? '¡Copiado!';
    $txt_copiado_error = $t['msg_copiado_error'] ?? 'No se pudo copiar. Copialo manualmente, por favor.';
    
    // Si el cliente escribió un mensaje personalizado, lo usamos. Si no, usamos el del diccionario.
    $mensaje_final = !empty($tarjeta['mensaje_regalo']) ? $tarjeta['mensaje_regalo'] : $txt_mensaje_defecto;
    $mod_id = 'regalos_' . (int)($tarjeta['id_tarjeta'] ?? 0);
?>

<section class="modulo-fullscreen modulo-regalos">
  <div class="modulo-contenido">
    <div class="regalos-contenido">
        
        <!-- Opción A: SVG Inline estilo uniforme 
        <div class="icono-modulo">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2" width="40" height="40" aria-hidden="true">
                <polyline points="20 12 20 22 4 22 4 12"></polyline>
                <rect x="2" y="7" width="20" height="5"></rect>
                <line x1="12" y1="22" x2="12" y2="7"></line>
                <path d="M12 7H7.5a2.5 2.5 0 0 1 0-5C11 2 12 7 12 7z"></path>
                <path d="M12 7h4.5a2.5 2.5 0 0 0 0-5C13 2 12 7 12 7z"></path>
            </svg>
        </div> 
        -->

        <!-- Opción B: Ícono dinámico helper (descomentar para usar en lugar del inline) -->
        <div class="icono-modulo">
            <?php momentia_render_icono_modulo('regalos.svg'); ?>
        </div>
        
        <h3 class="modulo-titulo"><?php echo $txt_titulo_regalos; ?></h3>
        
        <p class="modulo-texto regalos-texto"><?php echo nl2br(htmlspecialchars($mensaje_final)); ?></p>
        
        <?php if (!empty($tarjeta['datos_bancarios'])): ?>
        <div class="caja-vidrio caja-vidrio--centrada regalos-caja-bancaria">
            <p id="texto-banco-<?php echo $mod_id; ?>" class="regalos-datos-texto">
                <?php echo nl2br(htmlspecialchars($tarjeta['datos_bancarios'])); ?>
            </p>
            
            <button
                type="button"
                class="btn btn--copiar regalos-btn-copiar"
                id="btn-copiar-<?php echo $mod_id; ?>"
                data-msg-ok="<?php echo htmlspecialchars($txt_copiado_ok); ?>"
                data-msg-error="<?php echo htmlspecialchars($txt_copiado_error); ?>"
            >
                <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                    <path d="M9 9h10v10H9z"></path>
                    <path d="M5 5h10v2H7v8H5z"></path>
                </svg>
                <?php echo $txt_boton_copiar; ?>
            </button>
            
            <span id="mensaje-copiado-<?php echo $mod_id; ?>" class="regalos-feedback" aria-live="polite" hidden></span>
        </div>
        <?php endif; ?>

        </div>
  </div>
</section>

<script>
    (function () {
        var textoBanco = document.getElementById('texto-banco-<?php echo $mod_id; ?>');
        var botonCopiar = document.getElementById('btn-copiar-<?php echo $mod_id; ?>');
        var mensaje = document.getElementById('mensaje-copiado-<?php echo $mod_id; ?>');
        var timeoutId = null;

        if (!textoBanco || !botonCopiar || !mensaje) return;

        function mostrarFeedback(texto, esError) {
            if (timeoutId) {
                clearTimeout(timeoutId);
            }

            mensaje.textContent = texto;
            mensaje.classList.toggle('is-error', !!esError);
            mensaje.hidden = false;

            timeoutId = setTimeout(function () {
                mensaje.hidden = true;
            }, esError ? 3500 : 2200);
        }

        botonCopiar.addEventListener('click', function () {
            var texto = textoBanco.innerText;
            var okMsg = botonCopiar.getAttribute('data-msg-ok') || '¡Copiado!';
            var errorMsg = botonCopiar.getAttribute('data-msg-error') || 'No se pudo copiar. Copialo manualmente, por favor.';

            if (!navigator.clipboard || typeof navigator.clipboard.writeText !== 'function') {
                mostrarFeedback(errorMsg, true);
                return;
            }

            navigator.clipboard.writeText(texto).then(function () {
                mostrarFeedback(okMsg, false);
            }).catch(function (err) {
                console.error('Error al copiar al portapapeles:', err);
                mostrarFeedback(errorMsg, true);
            });
        });
    })();
</script>

<?php 
} // Fin del IF 
?>