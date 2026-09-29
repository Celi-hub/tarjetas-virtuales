<?php
if (!isset($tarjeta) || empty($tarjeta['id_tarjeta'])) return;

require_once __DIR__ . '/../../helpers/svg_helpers.php';

$id_tarjeta = (int)$tarjeta['id_tarjeta'];

try {
    $stmtConfig = $pdo->prepare("SELECT * FROM config_pagos WHERE tarjeta_id = ?");
    $stmtConfig->execute([$id_tarjeta]);
    $config_pago = $stmtConfig->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $config_pago = false;
}

if (!$config_pago) return;

$tarifas = [];
if (!empty($config_pago['tarifas'])) {
    $tarifas = json_decode($config_pago['tarifas'], true);
    if (!is_array($tarifas)) {
        $tarifas = [];
        preg_match_all('/([^:]+):\s*\$?([0-9.]+)/i', $config_pago['tarifas'], $matches, PREG_SET_ORDER);
        foreach ($matches as $match) {
            $tarifas[] = ['nombre' => trim($match[1]), 'monto' => (float)$match[2]];
        }
    }
}

$form_id = 'form_pago_' . $id_tarjeta;
?>
<section class="modulo-fullscreen modulo-pagar-evento">
    <div class="modulo-contenido">
        <div class="mod-pagar-evento">
            <div class="icono-modulo">
                <?php momentia_render_icono_modulo('pagar_evento.svg'); ?>
            </div>

            <span class="pago-titulo-eyebrow eyebrow">Entrada / Inscripción</span>
            <h3 class="modulo-titulo">Reservá tu lugar</h3>

            <?php if (!empty($config_pago['mensaje'])): ?>
                <p class="modulo-texto"><?php echo nl2br(htmlspecialchars($config_pago['mensaje'])); ?></p>
            <?php endif; ?>

            <?php if (!empty($tarifas)): ?>
                <div class="pago-tarifas">
                    <span class="pago-tarifas-titulo">Valores</span>
                    <?php foreach ($tarifas as $tarifa): ?>
                        <div class="pago-tarifa-item">
                            <span class="pago-tarifa-nombre"><?php echo htmlspecialchars($tarifa['nombre']); ?></span>
                            <span class="pago-tarifa-monto">$<?php echo number_format($tarifa['monto'], 0, ',', '.'); ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <!-- Formulario de pago -->
            <div id="form-cont-<?php echo $form_id; ?>" class="pago-form">
                <form id="<?php echo $form_id; ?>" enctype="multipart/form-data">
                    <input type="hidden" name="tarjeta_id" value="<?php echo $id_tarjeta; ?>">

                    <div class="campo-form">
                        <label for="concepto_<?php echo $form_id; ?>">¿Qué vas a pagar?</label>
                        <textarea id="concepto_<?php echo $form_id; ?>" name="concepto" rows="2" placeholder="Ej: 2 Adultos y 1 Menor" required></textarea>
                    </div>

                    <div class="campo-form">
                        <label for="monto_<?php echo $form_id; ?>">Monto total a transferir ($)</label>
                        <input type="number" id="monto_<?php echo $form_id; ?>" name="monto" placeholder="Ej: 12000" required step="1">
                    </div>

                    <div class="campo-form">
                        <label for="nombre_<?php echo $form_id; ?>">Tu nombre y apellido</label>
                        <input type="text" id="nombre_<?php echo $form_id; ?>" name="nombre_invitado" placeholder="Nombre completo" required>
                    </div>

                    <button type="button" id="btn_banco_<?php echo $form_id; ?>" class="btn btn--gold pago-btn-banco">
                        Ver datos para transferir
                    </button>

                    <div id="bloque_banco_<?php echo $form_id; ?>" class="pago-banco-bloque">
                        <p class="pago-banco-datos">
                            <?php echo nl2br(htmlspecialchars($config_pago['datos_bancarios'])); ?>
                        </p>
                        <label for="file_<?php echo $form_id; ?>" class="pago-file-label">Adjuntar comprobante (imagen o PDF)</label>
                        <input type="file" name="comprobante" id="file_<?php echo $form_id; ?>" accept="image/*,.pdf" class="pago-file-input">
                        <p id="err_<?php echo $form_id; ?>" class="pago-error" hidden></p>
                        <button type="submit" id="btn_submit_<?php echo $form_id; ?>" class="btn btn--gold pago-btn-submit">
                            Confirmar e informar pago
                        </button>
                    </div>

                    <div class="pago-mp-section">
                        <span class="pago-mp-label">Otras opciones de pago</span>
                        <?php if (!empty($config_pago['link_mp'])): ?>
                            <a href="<?php echo htmlspecialchars($config_pago['link_mp']); ?>" target="_blank" class="btn btn--mp">
                                Pagar con Mercado Pago
                            </a>
                        <?php else: ?>
                            <span class="btn btn--mp btn--mp-disabled">Mercado Pago — próximamente</span>
                        <?php endif; ?>
                    </div>
                </form>
            </div>

            <!-- Éxito — Fix 3: con botón nuevo pago -->
            <div id="exito_<?php echo $form_id; ?>" class="estado-mensaje pago-exito" hidden>
                <span class="estado-icono">✓</span>
                <h4 class="estado-titulo">¡Comprobante enviado!</h4>
                <p class="estado-texto">Tu pago está pendiente de confirmación por el organizador.</p>

                <div class="pago-exito-nuevo">
                    <p class="pago-exito-ayuda">
                        ¿Querés informar otro pago?
                    </p>
                    <button type="button" class="btn btn--secundario" id="btn_nuevo_pago_<?php echo $form_id; ?>">
                        Informar otro pago
                    </button>
                </div>
            </div>

        </div>
    </div>
</section>

<script>
(function () {
    var formId    = '<?php echo $form_id; ?>';
    var f         = document.getElementById(formId);
    var formCont  = document.getElementById('form-cont-' + formId);
    var exito     = document.getElementById('exito_' + formId);
    var err       = document.getElementById('err_' + formId);
    var btnBanco  = document.getElementById('btn_banco_' + formId);
    var bloqueBanco = document.getElementById('bloque_banco_' + formId);
    var fileInput = document.getElementById('file_' + formId);
    var btnSubmit = document.getElementById('btn_submit_' + formId);
    var btnNuevo  = document.getElementById('btn_nuevo_pago_' + formId);

    if (!f) return;

    // Mostrar/ocultar bloque bancario
    btnBanco.addEventListener('click', function () {
        var visible = bloqueBanco.classList.toggle('visible');
        fileInput.required = visible;
    });

    // Enviar comprobante
    f.addEventListener('submit', function (e) {
        e.preventDefault();
        btnSubmit.disabled    = true;
        btnSubmit.textContent = 'Enviando...';
        err.hidden = true;
        err.textContent = '';

        fetch('guardar_pago.php', { method: 'POST', body: new FormData(f) })
            .then(function (r) {
                if (!r.ok) throw new Error('Error de conexión.');
                return r.json();
            })
            .then(function (d) {
                if (d.success) {
                    formCont.hidden = true;
                    exito.hidden    = false;
                } else {
                    throw new Error(d.message || 'Error al enviar.');
                }
            })
            .catch(function (error) {
                err.textContent = error.message;
                err.hidden = false;
                btnSubmit.disabled    = false;
                btnSubmit.textContent = 'Confirmar e informar pago';
            });
    });

    // Fix 3: reiniciar formulario para nuevo pago
    if (btnNuevo) {
        btnNuevo.addEventListener('click', function () {
            f.reset();
            bloqueBanco.classList.remove('visible');
            fileInput.required     = false;
            err.hidden = true;
            err.textContent = '';
            btnSubmit.disabled     = false;
            btnSubmit.textContent  = 'Confirmar e informar pago';
            exito.hidden = true;
            formCont.hidden = false;
            // Scroll al inicio del módulo
            formCont.closest('section').scrollIntoView({ behavior: 'smooth', block: 'start' });
        });
    }
})();
</script>