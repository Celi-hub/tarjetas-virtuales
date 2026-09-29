<?php
/** @var array $tarjeta */

if (!isset($tarjeta) || empty($tarjeta['id_tarjeta'])) return;

$estilo_lt = $tarjeta['linea_tiempo_estilo'] ?? 'vertical';

try {
    $stmtLt = $pdo->prepare("SELECT * FROM linea_tiempo_hitos WHERE tarjeta_id = ? ORDER BY orden ASC, id_hito ASC");
    $stmtLt->execute([$tarjeta['id_tarjeta']]);
    $hitos = $stmtLt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $hitos = [];
}

if (empty($hitos)) return;
?>
<section class="modulo-fullscreen modulo-linea-tiempo">
    <div class="modulo-contenido">

        <span class="eyebrow modulo-subtitulo">Nuestra Historia</span>
        <h3 class="lt-titulo-principal modulo-titulo">
            <?php echo $estilo_lt === 'horizontal'
                ? 'Momentos que nos trajeron hasta acá'
                : 'Imágenes que cuentan nuestra historia'; ?>
        </h3>

        <div class="mod-linea-tiempo <?php echo htmlspecialchars($estilo_lt); ?>">
            <div class="lt-contenedor" id="lt-contenedor">
                <?php foreach ($hitos as $i => $hito): ?>
                    <div class="lt-item lt-item-anim" style="--delay: <?php echo $i * 0.1; ?>s">
                        <div class="lt-punto"></div>

                        <?php if (!empty($hito['fecha_anio'])): ?>
                            <div class="lt-fecha"><?php echo htmlspecialchars($hito['fecha_anio']); ?></div>
                        <?php endif; ?>

                        <?php if (!empty($hito['foto'])): ?>
                            <div class="lt-foto">
                                <img
                                    src="uploads/hitos/<?php echo htmlspecialchars($hito['foto']); ?>"
                                    alt="<?php echo htmlspecialchars($hito['titulo'] ?? ''); ?>"
                                    loading="lazy"
                                >
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($hito['titulo'])): ?>
                            <h4 class="lt-item-titulo"><?php echo htmlspecialchars($hito['titulo']); ?></h4>
                        <?php endif; ?>

                        <?php if (!empty($hito['descripcion'])): ?>
                            <p class="lt-descripcion"><?php echo nl2br(htmlspecialchars($hito['descripcion'])); ?></p>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>

            <?php if ($estilo_lt === 'horizontal' && count($hitos) > 1): ?>
                <!-- Flechas de navegación para versión horizontal -->
                <div class="lt-nav">
                    <button class="lt-nav-btn" id="lt-prev" data-lt-nav="-1" aria-label="Anterior">‹</button>
                    <button class="lt-nav-btn" id="lt-next" data-lt-nav="1"  aria-label="Siguiente">›</button>
                </div>
            <?php endif; ?>
        </div>

    </div>
</section>

<script>
(function () {
    // Animación de entrada con IntersectionObserver
    var items = document.querySelectorAll('.lt-item-anim');
    if ('IntersectionObserver' in window) {
        var obs = new IntersectionObserver(function(entries) {
            entries.forEach(function(entry) {
                if (entry.isIntersecting) {
                    entry.target.classList.add('visible');
                    obs.unobserve(entry.target);
                }
            });
        }, { threshold: 0.15 });
        items.forEach(function(el) { obs.observe(el); });
    } else {
        items.forEach(function(el) { el.classList.add('visible'); });
    }
})();

// Navegación horizontal (sin handlers inline)
(function () {
    var contenedor = document.getElementById('lt-contenedor');
    if (!contenedor) return;

    var botonesNav = document.querySelectorAll('.lt-nav-btn[data-lt-nav]');
    if (!botonesNav.length) return;

    function anchoPaso() {
        var primerItem = contenedor.querySelector('.lt-item');
        if (!primerItem) return 280;
        var estilos = window.getComputedStyle(contenedor);
        var gap = parseFloat(estilos.columnGap || estilos.gap || '0') || 24;
        return primerItem.getBoundingClientRect().width + gap;
    }

    function actualizarEstadoBotones() {
        var btnPrev = document.getElementById('lt-prev');
        var btnNext = document.getElementById('lt-next');
        if (!btnPrev || !btnNext) return;

        var maxScroll = contenedor.scrollWidth - contenedor.clientWidth;
        var scrollLeft = contenedor.scrollLeft;

        btnPrev.disabled = scrollLeft <= 4;
        btnNext.disabled = scrollLeft >= (maxScroll - 4);
    }

    botonesNav.forEach(function (btn) {
        btn.addEventListener('click', function () {
            var dir = parseInt(btn.getAttribute('data-lt-nav') || '0', 10);
            if (!dir) return;
            contenedor.scrollBy({ left: dir * anchoPaso(), behavior: 'smooth' });
        });
    });

    contenedor.addEventListener('scroll', actualizarEstadoBotones, { passive: true });
    window.addEventListener('resize', actualizarEstadoBotones);
    actualizarEstadoBotones();
})();
</script>