<?php
/** @var array $tarjeta */

$directorio    = 'uploads/book/' . $tarjeta['id_tarjeta'] . '/';
$fotos         = is_dir($directorio) ? glob($directorio . '*.{jpg,jpeg,png,webp}', GLOB_BRACE) : [];
$fotos         = array_slice($fotos ?: [], 0, 5);

if (empty($tarjeta['mod_book_fotos']) || empty($fotos)) return;

$count    = count($fotos);
$book_txt = trim($tarjeta['book_texto'] ?? '');
?>
<section class="modulo-fullscreen book-fotos">
    <div class="modulo-contenido">

<div class="icono-modulo">
            <?php 
                $ruta_icono = __DIR__ . '/../../img/img_modulos/book_fotos.svg'; 

                if (file_exists($ruta_icono)) {
                    echo file_get_contents($ruta_icono); 
                } else {
                    echo '<!-- Icono no encontrado -->';
                }
            ?>

</div>
        <h3 class="modulo-titulo">Book de Fotos</h3>

        <?php if ($book_txt): ?>
            <p class="modulo-texto"><?php echo htmlspecialchars($book_txt); ?></p>
        <?php endif; ?>

        <div class="galeria-grid grid-<?php echo $count; ?>">
            <?php foreach ($fotos as $foto): ?>
                <div class="foto-item" onclick="abrirLightbox('<?php echo htmlspecialchars($foto); ?>')">
                    <img
                        src="<?php echo htmlspecialchars($foto); ?>"
                        alt="Foto del evento"
                        loading="lazy"
                    >
                </div>
            <?php endforeach; ?>
        </div>

    </div>
</section>

<!-- Lightbox -->
<div id="book-lightbox" onclick="cerrarLightbox()" aria-hidden="true">
    <button class="lightbox-cerrar" onclick="cerrarLightbox()" aria-label="Cerrar">✕</button>
    <img id="book-lightbox-img" src="" alt="Foto ampliada">
</div>

<style>
/* ── Lightbox ── */
#book-lightbox {
    display: none;
    position: fixed; inset: 0; z-index: 9999;
    background: rgba(30,20,15,0.92);
    backdrop-filter: blur(6px);
    align-items: center; justify-content: center;
    cursor: zoom-out;
}
#book-lightbox.activo { display: flex; animation: fadeInLb 0.3s ease; }
@keyframes fadeInLb { from { opacity: 0; } to { opacity: 1; } }

#book-lightbox-img {
    max-width: 92vw; max-height: 88vh;
    object-fit: contain; border-radius: 4px;
    box-shadow: 0 20px 60px rgba(0,0,0,0.5);
    cursor: default;
}
.lightbox-cerrar {
    position: absolute; top: 20px; right: 24px;
    background: none; border: none;
    color: rgba(255,255,255,0.7); font-size: 1.6rem;
    cursor: pointer; line-height: 1; padding: 4px 8px;
    transition: color 0.2s;
}
.lightbox-cerrar:hover { color: #fff; }

/* ── Foto item con hover ── */
.foto-item {
    overflow: hidden; border-radius: 3px; cursor: zoom-in;
    position: relative;
}
.foto-item::after {
    content: '🔍';
    position: absolute; inset: 0;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.4rem;
    background: rgba(201,169,110,0.25);
    opacity: 0; transition: opacity 0.3s;
}
.foto-item:hover::after { opacity: 1; }
.foto-item img {
    width: 100%; height: 100%;
    object-fit: cover; display: block;
    transition: transform 0.5s cubic-bezier(0.25,0.46,0.45,0.94);
}
.foto-item:hover img { transform: scale(1.04); }
</style>

<script>
function abrirLightbox(src) {
    var lb  = document.getElementById('book-lightbox');
    var img = document.getElementById('book-lightbox-img');
    img.src = src;
    lb.classList.add('activo');
    lb.setAttribute('aria-hidden', 'false');
    document.body.style.overflow = 'hidden';
}
function cerrarLightbox() {
    var lb = document.getElementById('book-lightbox');
    lb.classList.remove('activo');
    lb.setAttribute('aria-hidden', 'true');
    document.body.style.overflow = '';
    document.getElementById('book-lightbox-img').src = '';
}
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') cerrarLightbox();
});
// Evitar que el click en la imagen cierre el lightbox
document.getElementById('book-lightbox-img').addEventListener('click', function(e) {
    e.stopPropagation();
});
</script>