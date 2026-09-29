<?php
if (!isset($tarjeta) || empty($tarjeta)) return;
$form_id = 'r' . ($tarjeta['id_tarjeta'] ?? 0);
?>

<section class="modulo-fullscreen">
    <div class="modulo-contenido">
        <div class="mod-rsvp-interno" id="mod-<?= $form_id ?>">
            <div id="form-cont-<?= $form_id ?>">
                <h3><?php echo htmlspecialchars($t['titulo_rsvp'] ?? 'Confirmá tu Asistencia'); ?></h3>
                <p class="rsvp-subtitle"><?php echo htmlspecialchars($t['subtitulo_rsvp'] ?? 'Tu respuesta nos ayuda a organizar mejor el evento'); ?></p>
                
                <form id="<?= $form_id ?>">
                    <input type="hidden" name="tarjeta_id" value="<?= $tarjeta['id_tarjeta'] ?? 0 ?>">
                    
                    <div class="rsvp-opciones">
                        <div class="rsvp-opcion">
                            <input type="radio" name="estado" id="rsi_<?= $form_id ?>" value="confirmado" required>
                            <label for="rsi_<?= $form_id ?>">✓ Asistiré</label>
                        </div>
                        <div class="rsvp-opcion">
                            <input type="radio" name="estado" id="rsno_<?= $form_id ?>" value="ausente">
                            <label for="rsno_<?= $form_id ?>">✕ No asistiré</label>
                        </div>
                    </div>
                    
                    <div class="rsvp-campos" id="campos_<?= $form_id ?>">
                        <div class="rsvp-mensaje-error" id="err_<?= $form_id ?>"></div>
                        
                        <div class="rsvp-field">
                            <label for="nom_<?= $form_id ?>">Tu Nombre y Apellido *</label>
                            <input type="text" id="nom_<?= $form_id ?>" name="nombre" required placeholder="Ej: Juan Pérez">
                        </div>
                        
                        <div class="rsvp-field">
                            <label for="tel_<?= $form_id ?>">Teléfono</label>
                            <input type="tel" id="tel_<?= $form_id ?>" name="telefono" placeholder="+54 9...">
                        </div>
                        
                        <div id="bloque_acompanantes_<?= $form_id ?>" style="display:none;">
                            <div class="rsvp-field">
                                <label>¿Venís acompañado?</label>
                                <small style="display:block; color:#7a6e67; margin-bottom:8px; line-height:1.2;">Asegurate de que tus acompañantes estén previstos por el organizador.</small>
                                <select id="tiene_acomp_<?= $form_id ?>">
                                    <option value="" selected disabled>Seleccioná una opción...</option>
                                    <option value="no">No, voy solo/a</option>
                                    <option value="si">Sí, voy acompañado/a</option>
                                </select>
                            </div>
                            
                            <div class="rsvp-field" id="cant_acomp_container_<?= $form_id ?>" style="display:none;">
                                <label for="aco_<?= $form_id ?>">¿Cuántos acompañantes vienen con vos?</label>
                                <select id="aco_<?= $form_id ?>" name="acompanantes">
                                    <option value="0">Seleccionar cantidad...</option>
                                    <option value="1">1 acompañante</option>
                                    <option value="2">2 acompañantes</option>
                                    <option value="3">3 acompañantes</option>
                                    <option value="4">4 acompañantes</option>
                                    <option value="5">5 o más acompañantes</option>
                                </select>
                            </div>
                            
                            <div id="nombres_acomp_container_<?= $form_id ?>"></div>
                        </div>
                        
                        <div class="rsvp-field">
                            <label for="men_<?= $form_id ?>" id="lbl_men_<?= $form_id ?>">Mensaje (opcional)</label>
                            <textarea id="men_<?= $form_id ?>" name="mensaje" placeholder="Alguna aclaración que quieras enviarnos?"></textarea>
                        </div>
                        
                        <button type="submit" class="rsvp-submit" id="btn_<?= $form_id ?>">Enviar Confirmación</button>
                    </div>
                </form>
            </div>
            
            <div class="rsvp-mensaje-exito" id="exit_<?= $form_id ?>" style="display:none;">
                <div class="icono-exito">🎉</div>
                <h4>¡Gracias por responder!</h4>
                <p>Hemos registrado tu respuesta correctamente.</p>
            </div>
        </div>
    </div>
</section>

<script>
(function(){
    var formId = '<?= $form_id ?>';
    var f = document.getElementById(formId);
    var c = document.getElementById('campos_'+formId);
    var ex = document.getElementById('exit_'+formId);
    var fc = document.getElementById('form-cont-'+formId);
    var err = document.getElementById('err_'+formId);
    var btn = document.getElementById('btn_'+formId);
    var textarea = document.getElementById('men_'+formId);
    
    var bloqueAcomp = document.getElementById('bloque_acompanantes_'+formId);
    var selectTieneAcomp = document.getElementById('tiene_acomp_'+formId);
    var cantAcompContainer = document.getElementById('cant_acomp_container_'+formId);
    var selectCantAcomp = document.getElementById('aco_'+formId);
    var nombresAcompContainer = document.getElementById('nombres_acomp_container_'+formId);
    
    if(!f||!c||!ex||!fc||!err||!btn) return;
    
    // Control de Estado (Asistiré / No asistiré)
    f.querySelectorAll('input[name="estado"]').forEach(function(r){
        r.addEventListener('change',function(){
            c.classList.add('visible');
            
            if(this.value === 'ausente') {
                textarea.placeholder = "Dejanos un saludo o contanos por qué no podés venir...";
                bloqueAcomp.style.display = 'none'; 
                // Reiniciamos valores de acompañantes si pone ausente
                selectTieneAcomp.value = '';
                selectCantAcomp.value = '0';
                cantAcompContainer.style.display = 'none';
                nombresAcompContainer.innerHTML = '';
            } else {
                textarea.placeholder = "Restricciones alimenticias u otra consulta...";
                bloqueAcomp.style.display = 'block';
            }
        });
    });
    
    // Control de "¿Venís acompañado?"
    selectTieneAcomp.addEventListener('change', function() {
        if(this.value === 'si') {
            cantAcompContainer.style.display = 'block';
        } else {
            cantAcompContainer.style.display = 'none';
            selectCantAcomp.value = "0";
            nombresAcompContainer.innerHTML = '';
        }
    });
    
    // Generación dinámica de campos de nombres
    selectCantAcomp.addEventListener('change', function() {
        var cantidad = parseInt(this.value) || 0;
        nombresAcompContainer.innerHTML = ''; // Limpiamos si cambia el número
        
        for(var i = 1; i <= cantidad; i++) {
            var div = document.createElement('div');
            div.className = 'rsvp-field';
            div.innerHTML = '<label>Nombre del acompañante ' + i + ' *</label>' +
                            '<input type="text" name="nombres_acompanantes[]" required placeholder="Nombre y apellido">';
            nombresAcompContainer.appendChild(div);
        }
    });
    
    // Envío del formulario
    f.addEventListener('submit',function(e){
        e.preventDefault();
        
        // Validación extra por si seleccionó "Sí" pero no eligió cantidad
        if (f.querySelector('input[name="estado"]:checked').value === 'confirmado' && 
            selectTieneAcomp.value === 'si' && 
            selectCantAcomp.value === '0') {
            err.textContent = 'Por favor, indicá cuántos acompañantes vienen con vos.';
            err.classList.add('visible');
            return;
        }
        
        btn.disabled = true;
        btn.textContent = 'Enviando...';
        err.classList.remove('visible');
        
        var fd = new FormData(f);
        fetch('guardar_rsvp.php', { method: 'POST', body: fd })
        .then(function(r){
            if(!r.ok) throw new Error('Error de conexión con el servidor.');
            return r.json();
        })
        .then(function(d){
            if(d.success){
                fc.style.display = 'none';
                ex.style.display = 'block';
            } else {
                err.textContent = d.message || 'Error. Intentá de nuevo.';
                err.classList.add('visible');
                btn.disabled = false;
                btn.textContent = 'Enviar Confirmación';
            }
        })
        .catch(function(error){
            err.textContent = 'Error: ' + error.message;
            err.classList.add('visible');
            btn.disabled = false;
            btn.textContent = 'Enviar Confirmación';
        });
    });
})();
</script>