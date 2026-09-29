<?php
if (!isset($tarjeta) || empty($tarjeta['id_tarjeta'])) return;

$id_tarjeta = (int)$tarjeta['id_tarjeta'];
$moderacion = (int)($tarjeta['muro_moderacion'] ?? 0);
$form_id    = 'form_muro_' . $id_tarjeta;

try {
    $stmtMuro = $pdo->prepare("SELECT nombre, mensaje, fecha_creacion FROM muro_deseos WHERE tarjeta_id = ? AND estado = 'aprobado' ORDER BY fecha_creacion DESC");
    $stmtMuro->execute([$id_tarjeta]);
    $deseos = $stmtMuro->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $deseos = [];
}
?>
<section class="modulo-fullscreen modulo-muro-deseos">
    <div class="modulo-contenido">
        <div class="modulo-seccion mod-muro-deseos">

            <h3 class="muro-titulo modulo-titulo"><?php echo htmlspecialchars($t['muro_titulo'] ?? 'Muro de Deseos'); ?></h3>
            <p class="muro-subtitulo eyebrow" id="sub_<?php echo $form_id; ?>">
                <?php echo htmlspecialchars($t['muro_pregunta'] ?? '¿Querés dejarle un mensaje a los agasajados?'); ?>
            </p>

            <!-- Pregunta inicial -->
            <div id="prompt_<?php echo $form_id; ?>" class="muro-prompt">
                <button type="button" id="btn_si_<?php echo $form_id; ?>" class="btn btn--gold">
                    Sí, dejar mensaje
                </button>
                <button type="button" id="btn_no_<?php echo $form_id; ?>" class="btn btn--secundario">
                    No, gracias
                </button>
            </div>

            <!-- Respuesta negativa -->
            <div id="msg_no_<?php echo $form_id; ?>" class="muro-msg-no" hidden>
                ¡No hay problema! Seguí viendo la tarjeta.
            </div>

            <!-- Formulario -->
            <div class="muro-form-container" id="cont_<?php echo $form_id; ?>" hidden>
                <form id="<?php echo $form_id; ?>" class="form-muro">
                    <input type="hidden" name="tarjeta_id" value="<?php echo $id_tarjeta; ?>">
                    <div class="campo-form">
                        <input type="text" name="nombre" id="nom_<?php echo $form_id; ?>" placeholder="Tu nombre y apellido" required>
                    </div>
                    <div class="campo-form">
                        <textarea name="mensaje" id="msg_<?php echo $form_id; ?>" rows="3" placeholder="<?php echo htmlspecialchars($t['muro_placeholder'] ?? 'Escribí tu mensaje acá...'); ?>" required></textarea>
                    </div>
                    <p id="err_<?php echo $form_id; ?>" class="muro-error" hidden></p>
                    <button type="submit" id="btn_<?php echo $form_id; ?>" class="btn btn--gold">
                        Enviar mensaje
                    </button>
                </form>
            </div>

            <!-- Éxito — Fix 1: con scroll hint -->
            <div id="exito_<?php echo $form_id; ?>" class="estado-mensaje muro-exito" hidden>
                <span class="estado-icono">💌</span>
                <h4 class="estado-titulo">¡Gracias por tu mensaje!</h4>
                <p class="estado-texto">
                    <?php echo $moderacion === 1
                        ? 'Tu mensaje fue enviado y está pendiente de aprobación.'
                        : 'Tu mensaje fue publicado exitosamente.'; ?>
                </p>
                <button type="button" id="btn_otro_<?php echo $form_id; ?>" class="btn btn--secundario muro-btn-otro">
                    Escribir otro mensaje
                </button>

                <!-- Scroll hint -->
                <div class="muro-scroll-hint scroll-hint" aria-hidden="true">
                    <span>Seguí viendo</span>
                    <div class="scroll-arrow"></div>
                </div>
            </div>

            <!-- Mensajes publicados -->
            <?php if (!empty($deseos)): ?>
                <div class="muro-mensajes">
                    <?php foreach ($deseos as $deseo): ?>
                        <div class="muro-item">
                            <strong><?php echo htmlspecialchars($deseo['nombre']); ?></strong>
                            <p><?php echo nl2br(htmlspecialchars($deseo['mensaje'])); ?></p>
                            <small><?php echo date('d/m/Y H:i', strtotime($deseo['fecha_creacion'])); ?></small>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

        </div>
    </div>
</section>

<script>
(function () {
    var formId   = '<?php echo $form_id; ?>';
    var f        = document.getElementById(formId);
    var promptBox= document.getElementById('prompt_' + formId);
    var cont     = document.getElementById('cont_' + formId);
    var msgNo    = document.getElementById('msg_no_' + formId);
    var exito    = document.getElementById('exito_' + formId);
    var err      = document.getElementById('err_' + formId);
    var btn      = document.getElementById('btn_' + formId);
    var btnSi    = document.getElementById('btn_si_' + formId);
    var btnNo    = document.getElementById('btn_no_' + formId);
    var btnOtro  = document.getElementById('btn_otro_' + formId);
    var sub      = document.getElementById('sub_' + formId);

    if (btnSi) {
        btnSi.addEventListener('click', function () {
            promptBox.hidden = true;
            if (sub) sub.hidden = true;
            cont.hidden = false;
        });
    }
    if (btnNo) {
        btnNo.addEventListener('click', function () {
            promptBox.hidden = true;
            if (sub) sub.hidden = true;
            msgNo.hidden = false;
        });
    }

    if (!f) return;

    f.addEventListener('submit', function (e) {
        e.preventDefault();
        btn.disabled    = true;
        btn.textContent = 'Enviando...';
        err.hidden = true;
        err.textContent = '';

        fetch('guardar_deseo.php', { method: 'POST', body: new FormData(f) })
            .then(function (r) {
                if (!r.ok) throw new Error('Error de conexión.');
                return r.json();
            })
            .then(function (d) {
                if (d.success) {
                    cont.hidden  = true;
                    exito.hidden = false;
                    f.reset();
                } else {
                    throw new Error(d.message || 'Error al guardar el mensaje.');
                }
            })
            .catch(function (error) {
                err.textContent = error.message;
                err.hidden = false;
            })
            .finally(function () {
                btn.disabled    = false;
                btn.textContent = 'Enviar mensaje';
            });
    });

    if (btnOtro) {
        btnOtro.addEventListener('click', function () {
            exito.hidden = true;
            cont.hidden  = false;
        });
    }
})();
</script>