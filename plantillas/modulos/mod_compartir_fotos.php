<?php
/** @var array $tarjeta */
/** @var array $t */

if (empty($tarjeta['mod_compartir_fotos']) || empty($tarjeta['link_galeria_externa'])) return;

$txt_titulo = $t['titulo_galeria'] ?? 'Galería Colaborativa';
$txt_texto  = $t['texto_galeria']  ?? '¡Queremos ver el evento a través de tus ojos! Sumá tus fotos y compartí el recuerdo.';
$txt_btn    = $t['btn_galeria']    ?? 'Ver y subir fotos';
?>
<section class="modulo-fullscreen modulo-compartir-fotos">
    <div class="modulo-contenido tarjeta-con-personaje">   
             
            <!-- Imagen decorativa que asoma si es svg -->
            <div class="ilustracion-asomada">
                <?php 
                    $ruta_deco = __DIR__ . '/../../img/trescorazones.svg'; 

                    if (file_exists($ruta_deco)) {
                        echo file_get_contents($ruta_deco); 
                    } else {
                        echo '<!-- Decoración no encontrada -->';
                    }
                ?>
            </div>

            <!-- Imagen decorativa que asoma si fuese png p jpg 
            <img src="img/trescorazones.svg" class="ilustracion-asomada" alt="Deco corazones"> -->
            
            <div class="icono-modulo">
                <?php 
                    $ruta_icono = __DIR__ . '/../../img/img_modulos/fotos.svg'; 

                    if (file_exists($ruta_icono)) {
                        echo file_get_contents($ruta_icono); 
                    } else {
                        echo '<!-- Icono no encontrado -->';
                    }
                ?>
            </div>
        
        <h3 class="modulo-titulo"><?php echo htmlspecialchars($txt_titulo); ?></h3>
        <p class="modulo-texto"><?php echo htmlspecialchars($txt_texto); ?></p>

        <a
            href="<?php echo htmlspecialchars($tarjeta['link_galeria_externa']); ?>"
            target="_blank"
            rel="noopener noreferrer"
            class="btn-accion"
        >
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" width="16" height="16" aria-hidden="true">
                <path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"/>
                <circle cx="12" cy="13" r="4"/>
            </svg>
            <?php echo htmlspecialchars($txt_btn); ?>
        </a>
        
    </div>
</section>