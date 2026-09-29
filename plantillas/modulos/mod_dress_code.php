<?php
/** @var array $tarjeta */
/** @var array $t */

if (empty($tarjeta['mod_dress_code']) || empty($tarjeta['dress_code_texto'])) return;

$texto        = trim($tarjeta['dress_code_texto']);
$titulo_dress = trim($tarjeta['dress_code_titulo'] ?? $tarjeta['dress_code_tipo'] ?? $tarjeta['tipo_dress_code'] ?? '');
?>
<section class="modulo-fullscreen modulo-dress-code">
    <div class="modulo-contenido">

        <div class="icono-modulo">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2" width="40" height="40" aria-hidden="true">
                <path d="M20.38 3.46L16 2a4 4 0 0 1-8 0L3.62 3.46a2 2 0 0 0-1.34 2.23l.58 3.57a1 1 0 0 0 .99.84H6v10a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V10h2.15a1 1 0 0 0 .99-.84l.58-3.57a2 2 0 0 0-1.34-2.23z"/>
            </svg>
        </div>

        <span class="eyebrow modulo-subtitulo"><?php echo htmlspecialchars($t['subtitulo_vestimenta'] ?? 'Vestimenta'); ?></span>
        <h3 class="modulo-titulo"><?php echo htmlspecialchars($t['titulo_dress_code'] ?? 'Dress Code'); ?></h3>

        <div class="dress-code-texto caja-vidrio caja-vidrio--centrada">
            <?php if (!empty($titulo_dress)): ?>
                <h4 class="dress-code-tipo">
                    <?php echo htmlspecialchars($titulo_dress); ?>
                </h4>
            <?php endif; ?>
            <p><?php echo nl2br(htmlspecialchars($texto)); ?></p>
        </div>

    </div>
</section>