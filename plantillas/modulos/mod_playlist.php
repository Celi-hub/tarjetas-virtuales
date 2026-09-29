<?php
/** @var array $tarjeta */

if (!isset($tarjeta) || empty($tarjeta)) return;

require_once __DIR__ . '/../../helpers/svg_helpers.php';

$form_id      = 'pl' . ($tarjeta['id_tarjeta'] ?? 0);
$modo         = $tarjeta['playlist_modo'] ?? 'formulario';
$url_playlist = trim($tarjeta['playlist_url'] ?? '');
?>
<section class="modulo-fullscreen modulo-playlist">
    <div class="modulo-contenido">
        <div class="mod-playlist-interno" id="mod-<?php echo $form_id; ?>">

            <div class="icono-modulo">
                <?php momentia_render_icono_modulo('playlist.svg'); ?>
            </div>

            <span class="eyebrow modulo-subtitulo">Música</span>
            <h3 class="modulo-titulo"><?php echo htmlspecialchars($t['titulo_playlist'] ?? 'Playlist Colaborativa'); ?></h3>
            <p class="modulo-texto"><?php echo htmlspecialchars($t['subtitulo_playlist'] ?? '¡Ayudanos a armar la fiesta! Sugerí las canciones que no pueden faltar.'); ?></p>

            <?php if ($modo === 'enlace' && $url_playlist): ?>

                <!-- Modo enlace externo -->
                <a href="<?php echo htmlspecialchars($url_playlist); ?>" target="_blank" rel="noopener noreferrer" class="btn btn--spotify btn-playlist-spotify">
                    <svg viewBox="0 0 24 24" fill="currentColor" width="16" height="16" aria-hidden="true">
                        <path d="M12 0C5.4 0 0 5.4 0 12s5.4 12 12 12 12-5.4 12-12S18.66 0 12 0zm5.521 17.34c-.24.359-.66.48-1.021.24-2.82-1.74-6.36-2.101-10.561-1.141-.418.122-.779-.179-.899-.539-.12-.421.18-.78.54-.9 4.56-1.021 8.52-.6 11.64 1.32.42.18.479.659.301 1.02zm1.44-3.3c-.301.42-.841.6-1.262.3-3.239-1.98-8.159-2.58-11.939-1.38-.479.12-1.02-.12-1.14-.6-.12-.48.12-1.021.6-1.141C9.6 9.9 15 10.561 18.72 12.84c.361.181.54.78.241 1.2zm.12-3.36C15.24 8.4 8.82 8.16 5.16 9.301c-.6.179-1.2-.181-1.38-.721-.18-.601.18-1.2.72-1.381 4.26-1.26 11.28-1.02 15.721 1.621.539.3.719 1.02.419 1.56-.299.421-1.02.599-1.559.3z"/>
                    </svg>
                    Agregar temas a la playlist
                </a>

            <?php else: ?>

                <!-- Modo formulario interno -->
                <div id="form-cont-<?php echo $form_id; ?>" class="playlist-form-wrap">
                    <form id="<?php echo $form_id; ?>" class="playlist-form">
                        <input type="hidden" name="tarjeta_id" value="<?php echo (int)$tarjeta['id_tarjeta']; ?>">

                        <div class="playlist-campos">
                            <p class="playlist-error" id="err_<?php echo $form_id; ?>"></p>

                            <div class="campo-form">
                                <label for="can_<?php echo $form_id; ?>">Nombre de la Canción *</label>
                                <input type="text" id="can_<?php echo $form_id; ?>" name="cancion" required placeholder="Ej: Dangerously in Love">
                            </div>

                            <div class="campo-form">
                                <label for="art_<?php echo $form_id; ?>">Artista / Banda *</label>
                                <input type="text" id="art_<?php echo $form_id; ?>" name="artista" required placeholder="Ej: Beyoncé">
                            </div>

                            <div class="campo-form">
                                <label for="inv_<?php echo $form_id; ?>">Tu nombre (opcional)</label>
                                <input type="text" id="inv_<?php echo $form_id; ?>" name="nombre_invitado" placeholder="Ej: Primita Celi">
                            </div>

                            <button type="submit" class="btn btn--gold playlist-submit" id="btn_<?php echo $form_id; ?>">
                                Sugerir canción
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Estado de éxito -->
                <div class="estado-mensaje playlist-exito" id="exit_<?php echo $form_id; ?>" hidden>
                    <span class="estado-icono">🎶</span>
                    <h4 class="estado-titulo">¡Canción agregada!</h4>
                    <p class="estado-texto" id="msg_cuenta_<?php echo $form_id; ?>">Gracias por tu sugerencia.</p>
                    <button type="button" class="btn btn--secundario playlist-btn-otra" id="btn_otra_<?php echo $form_id; ?>">
                        Sugerir otra canción
                    </button>
                </div>

            <?php endif; ?>

        </div>
    </div>
</section>

<?php if ($modo !== 'enlace'): ?>
<script>
(function () {
    var formId      = '<?php echo $form_id; ?>';
    var f           = document.getElementById(formId);
    var fc          = document.getElementById('form-cont-' + formId);
    var ex          = document.getElementById('exit_' + formId);
    var err         = document.getElementById('err_' + formId);
    var btn         = document.getElementById('btn_' + formId);
    var btnOtra     = document.getElementById('btn_otra_' + formId);
    var msgCuenta   = document.getElementById('msg_cuenta_' + formId);

    if (!f || !fc || !ex || !err || !btn || !btnOtra) return;

    var sugerencias    = 0;
    var maxSugerencias = 5;

    f.addEventListener('submit', function (e) {
        e.preventDefault();
        btn.disabled    = true;
        btn.textContent = 'Guardando...';
        err.textContent = '';

        // Ruta relativa — funciona en local y en producción
        fetch('guardar_playlist.php', { method: 'POST', body: new FormData(f) })
            .then(function (r) {
                if (!r.ok) throw new Error('Error de conexión.');
                return r.json();
            })
            .then(function (d) {
                if (d.success) {
                    sugerencias++;
                    fc.hidden = true;
                    ex.hidden = false;

                    if (sugerencias >= maxSugerencias) {
                        msgCuenta.textContent  = '¡Alcanzaste el límite de 5 canciones!';
                        btnOtra.hidden = true;
                    } else {
                        msgCuenta.textContent = 'Canción guardada (' + sugerencias + '/' + maxSugerencias + ').';
                        btnOtra.hidden = false;
                    }
                } else {
                    throw new Error(d.message || 'Error al guardar.');
                }
            })
            .catch(function (error) {
                err.textContent = error.message;
                btn.disabled    = false;
                btn.textContent = 'Sugerir canción';
            });
    });

    btnOtra.addEventListener('click', function () {
        document.getElementById('can_' + formId).value = '';
        document.getElementById('art_' + formId).value = '';
        ex.hidden = true;
        fc.hidden = false;
        btnOtra.hidden  = false;
        btn.disabled     = false;
        btn.textContent  = 'Sugerir canción';
    });
})();
</script>
<?php endif; ?>