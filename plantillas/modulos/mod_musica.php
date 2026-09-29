<?php
/** @var array $tarjeta */

// Regla estricta de renderizado (ver .docs/modulos.md): si el módulo no está activo
// o no hay música cargada, no hay nada que mostrar.
if (empty($tarjeta['mod_musica']) || empty($tarjeta['link_musica'])) return;

preg_match('%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|.*[?&]v=)|youtu\.be/)([A-Za-z0-9_-]{11})%i', $tarjeta['link_musica'], $match);
$youtube_id = $match[1] ?? null;

if ($youtube_id):
?>
<div id="reproductor-youtube" style="display:none;"></div>

<!-- Ícono flotante y sutil: acompaña todo el recorrido de la tarjeta -->
<button id="btn-control-musica" class="btn-musica-flotante" title="Música de fondo" aria-label="Reproducir o pausar música de fondo">
    <svg id="icono-play" class="icono-musica" viewBox="0 0 24 24">
        <path d="M8 5v14l11-7z" fill="currentColor"/>
    </svg>
    <svg id="icono-pausa" class="icono-musica" viewBox="0 0 24 24" style="display:none;">
        <path d="M6 19h4V5H6v14zm8-14v14h4V5h-4z" fill="currentColor"/>
    </svg>
</button>

<script src="https://www.youtube.com/iframe_api"></script>
<script>
(function() {
    let reproductor = null;
    let reproductorListo = false;
    let musicaActiva = false;
    let reproduccionPendiente = false;

    const btnControl  = document.getElementById('btn-control-musica');
    const iconoPlay    = document.getElementById('icono-play');
    const iconoPausa   = document.getElementById('icono-pausa');

    window.onYouTubeIframeAPIReady = function() {
        reproductor = new YT.Player('reproductor-youtube', {
            height: '0', width: '0',
            videoId: '<?php echo $youtube_id; ?>',
            playerVars: { autoplay: 0, controls: 0, loop: 1, playlist: '<?php echo $youtube_id; ?>' },
            events: {
                onReady: function() {
                    reproductorListo = true;
                    // Si el invitado ya había elegido "con música" antes de
                    // que el reproductor terminara de cargar, arrancamos ahora.
                    if (reproduccionPendiente) reproducir();
                }
            }
        });
    };

    function reproducir() {
        if (!reproductorListo) { reproduccionPendiente = true; return; }
        reproductor.playVideo();
        musicaActiva = true;
        reproduccionPendiente = false;
        iconoPlay.style.display  = 'none';
        iconoPausa.style.display = 'block';
        btnControl.classList.add('reproduciendo');
    }

    function pausar() {
        if (reproductorListo) reproductor.pauseVideo();
        musicaActiva = false;
        iconoPlay.style.display  = 'block';
        iconoPausa.style.display = 'none';
        btnControl.classList.remove('reproduciendo');
    }

    // base_portada.php dispara este evento apenas el invitado elige
    // "con música" o "sin música" en la pantalla de misterio.
    document.addEventListener('momentia:musica-decision', function(e) {
        if (e.detail && e.detail.conMusica) reproducir();
    });

    btnControl.addEventListener('click', function() {
        musicaActiva ? pausar() : reproducir();
    });
})();
</script>
<?php
endif;