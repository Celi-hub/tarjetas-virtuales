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

$frase = trim($tarjeta['frase_portada'] ?? '');
?>
<section class="modulo-fullscreen">
    <div class="modulo-contenido portada-contenido">

        <?php if ($frase): ?>
            <p class="portada-frase"><?php echo htmlspecialchars($frase); ?></p>
            <div class="portada-linea-decorativa"><span>✦</span></div>
        <?php else: ?>
            <div class="portada-linea-decorativa" style="margin-bottom:24px;"><span>✦</span></div>
        <?php endif; ?>

        <h1 class="portada-nombre">
            <?php echo htmlspecialchars($tarjeta['nombres_portada']); ?>
        </h1>

        <?php if ($tiene_fecha): ?>
            <div class="portada-fecha-wrap">
                <span class="portada-fecha"><?php echo $fecha_human; ?></span>
            </div>
        <?php endif; ?>

    </div>

    <div class="portada-scroll-hint" aria-hidden="true">
        <span>Deslizá</span>
        <div class="scroll-arrow"></div>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function () {
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