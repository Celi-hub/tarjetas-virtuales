<?php
/** @var array $tarjeta */

require_once __DIR__ . '/../../helpers/svg_helpers.php';

$directorio    = 'uploads/book/' . (int)$tarjeta['id_tarjeta'] . '/';
$fotos         = is_dir($directorio) ? glob($directorio . '*.{jpg,jpeg,png,webp}', GLOB_BRACE) : [];
$fotos         = array_slice($fotos ?: [], 0, 5);

if (empty($tarjeta['mod_book_fotos']) || empty($fotos)) return;

$count    = count($fotos);
$book_txt = trim($tarjeta['book_texto'] ?? '');
?>
<section class="modulo-fullscreen book-fotos">
    <div class="modulo-contenido">

        <div class="icono-modulo">
            <?php momentia_render_icono_modulo('book_fotos.svg'); ?>
        </div>

        <h3 class="modulo-titulo">Book de Fotos</h3>

        <?php if ($book_txt): ?>
            <p class="modulo-texto"><?php echo htmlspecialchars($book_txt); ?></p>
        <?php endif; ?>

        <div class="galeria-grid grid-<?php echo $count; ?>">
            <?php foreach ($fotos as $i => $foto): ?>
                <button type="button" class="foto-item" data-lightbox="<?php echo htmlspecialchars($foto); ?>" aria-label="Ampliar foto <?php echo $i + 1; ?>">
                    <img
                        src="<?php echo htmlspecialchars($foto); ?>"
                        alt="Foto <?php echo $i + 1; ?> del evento"
                        loading="lazy"
                    >
                </button>
            <?php endforeach; ?>
        </div>

    </div>
</section>

<!-- Lightbox (estilos en base_tarjeta.css, sección 6.12) -->
<div id="book-lightbox" role="dialog" aria-modal="true" aria-label="Foto ampliada" aria-hidden="true">
    <button type="button" class="lightbox-cerrar" aria-label="Cerrar">✕</button>
    <img id="book-lightbox-img" src="" alt="Foto ampliada">
</div>

<script>
(function () {
    var lb  = document.getElementById('book-lightbox');
    var img = document.getElementById('book-lightbox-img');
    var ultimoFoco = null;

    function abrir(src) {
        ultimoFoco = document.activeElement;
        img.src = src;
        lb.classList.add('activo');
        lb.setAttribute('aria-hidden', 'false');
        document.body.style.overflow = 'hidden';
        lb.querySelector('.lightbox-cerrar').focus();
    }
    function cerrar() {
        lb.classList.remove('activo');
        lb.setAttribute('aria-hidden', 'true');
        document.body.style.overflow = '';
        img.removeAttribute('src');
        if (ultimoFoco) ultimoFoco.focus();
    }

    document.querySelectorAll('.foto-item[data-lightbox]').forEach(function (btn) {
        btn.addEventListener('click', function () { abrir(btn.getAttribute('data-lightbox')); });
    });
    lb.addEventListener('click', function (e) { if (e.target !== img) cerrar(); });
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && lb.classList.contains('activo')) cerrar();
    });
})();
</script>
