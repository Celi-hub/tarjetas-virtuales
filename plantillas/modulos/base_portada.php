<?php
/** @var array $tarjeta */

$tiene_fecha = !empty($tarjeta['fecha_evento']);
$fecha_human = '';
if ($tiene_fecha) {
    $dt         = date_create($tarjeta['fecha_evento']);
    $meses      = ['enero','febrero','marzo','abril','mayo','junio',
                   'julio','agosto','septiembre','octubre','noviembre','diciembre'];
    $fecha_human = (int)date_format($dt,'d') . ' de ' .
                   $meses[(int)date_format($dt,'n')-1] . ' de ' .
                   date_format($dt,'Y');
}

$frase_cliente = trim($tarjeta['frase_portada'] ?? '');

// El módulo música ya no tiene pantalla propia: la pregunta "¿con o sin
// música?" se resuelve acá mismo, en la pantalla de misterio. Si el cliente
// no activó el módulo de música o no cargó música, no hay nada que preguntar.
$musica_disponible = !empty($tarjeta['mod_musica']) && !empty($tarjeta['link_musica']);
?>

<!-- 1. PANTALLA DE MISTERIO (+ pregunta de música, si el módulo está activo) -->
<div id="pantalla-misterio" class="misterio-wrapper">
    <div class="misterio-contenido">
        <p class="misterio-texto">Tenemos algo para contarte...</p>

        <?php if ($musica_disponible): ?>
        <div class="misterio-botones">
            <p class="misterio-pregunta">¿Te lo contamos con o sin música?</p>
            <div class="botones-wrap">
                <!-- Botón Con Música: SVG Nota Musical -->
                <button id="btn-con-musica" class="btn-misterio">
                    <svg class="icono-svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M9 18V5l12-2v13"></path>
                        <circle cx="6" cy="18" r="3"></circle>
                        <circle cx="18" cy="16" r="3"></circle>
                    </svg>
                    Con música
                </button>

                <!-- Botón Sin Música: SVG Nota Musical Tachada -->
                <button id="btn-sin-musica" class="btn-misterio">
                    <svg class="icono-svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="1" y1="1" x2="23" y2="23"></line>
                        <path d="M9 9v9a3 3 0 1 1-3-3"></path>
                        <path d="M9 5v1"></path>
                        <path d="M21 16V3l-12 2"></path>
                    </svg>
                    Sin música
                </button>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- 2. PORTADA PRINCIPAL -->
<section id="portada-principal" class="modulo-fullscreen portada-wrapper">
    
    <div class="portada-fondo-animado"></div>

    <div class="portada-marco">
        <div class="modulo-contenido portada-contenido">

            <?php if ($frase_cliente): ?>
                <p class="portada-frase anim-escalonada anim-delay-1"><?php echo htmlspecialchars($frase_cliente); ?></p>
                <div class="portada-linea-decorativa anim-escalonada anim-delay-2"><span>✦</span></div>
            <?php else: ?>
                <div class="portada-linea-decorativa anim-escalonada anim-delay-1" style="margin-bottom:24px;"><span>✦</span></div>
            <?php endif; ?>

            <h1 class="portada-nombre anim-escalonada anim-delay-3">
                <?php echo htmlspecialchars($tarjeta['nombres_portada']); ?>
            </h1>

            <?php if ($tiene_fecha): ?>
                <div class="portada-fecha-wrap anim-escalonada anim-delay-4">
                    <span class="portada-fecha"><?php echo $fecha_human; ?></span>
                </div>
            <?php endif; ?>

        </div>
    </div>

    <div class="portada-scroll-hint scroll-hint anim-escalonada anim-delay-5" aria-hidden="true">
        <span>Deslizá</span>
        <div class="scroll-arrow"></div>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const pantallaMisterio = document.getElementById('pantalla-misterio');
    const portadaPrincipal = document.getElementById('portada-principal');
    const btnConMusica = document.getElementById('btn-con-musica');
    const btnSinMusica = document.getElementById('btn-sin-musica');

    function iniciarExperiencia(conMusica) {
        pantallaMisterio.style.opacity = '0';
        pantallaMisterio.style.pointerEvents = 'none';

        // Activamos la clase que dispara el CSS y las animaciones de golpe
        portadaPrincipal.classList.add('iniciar-magia');

        // Marca global: la tarjeta ya se desbloqueó (la usa, por ejemplo,
        // el ícono flotante de música para empezar a mostrarse)
        document.body.classList.add('experiencia-iniciada');

        // La decisión de reproducir o no queda disponible como evento para
        // que mod_musica.php (si está activo) reaccione, sin acoplar este
        // archivo a ese módulo.
        document.dispatchEvent(new CustomEvent('momentia:musica-decision', {
            detail: { conMusica: !!conMusica }
        }));
    }

    if (btnConMusica && btnSinMusica) {
        btnConMusica.addEventListener('click', () => iniciarExperiencia(true));
        btnSinMusica.addEventListener('click', () => iniciarExperiencia(false));
    } else {
        // No hay módulo de música activo: no hacemos ninguna pregunta,
        // pasamos solos a la portada tras unos segundos.
        setTimeout(() => iniciarExperiencia(false), 3200);
    }

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('visible');
                observer.unobserve(entry.target);
            }
        });
    }, { threshold: 0.12 });

    document.querySelectorAll('.modulo-fullscreen:not(:first-child)').forEach(el => {
        el.classList.add('reveal');
        observer.observe(el);
    });
});
</script>