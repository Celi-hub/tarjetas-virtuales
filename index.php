<?php
require_once __DIR__ . '/conexion.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$ya_logueado = isset($_SESSION['id_usuario']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Momentia — Invitaciones digitales que emocionan</title>
    <meta name="description" content="Creá invitaciones digitales modernas, interactivas y 100% personalizables para tus momentos más especiales.">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Ms+Madi&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/global.css">
    <link rel="stylesheet" href="css/landing.css">
</head>
<body>

<?php include_once 'header.php'; ?>

<div class="portada-container-flotante">
    <img src="img/miPortada.png" alt="Invitación digital de ejemplo" class="imagen-flotante">
</div>

<section class="hero-section">
    <div class="hero-content">
        <h2>Creá invitaciones digitales que emocionen</h2>
        <h3>¡Porque cada celebración merece un comienzo extraordinario!</h3>
        <p>Modernas, interactivas y 100% personalizables para tus momentos más especiales.</p>
        <?php if ($ya_logueado): ?>
            <a href="dashboard.php" class="btn-hero">Ir a mi Panel</a>
        <?php else: ?>
            <a href="#catalog" class="btn-hero">Conocer las Propuestas</a>
        <?php endif; ?>
    </div>
</section>

<section id="catalog" class="catalog-section">
    <div class="section-title">
        <h3>Nuestras Propuestas</h3>
        <p>Elegí el estilo que mejor se adapte a tu evento y mirá un ejemplo en vivo.</p>
    </div>

    <div class="catalog-container">

        <!-- PLAN BÁSICO -->
        <div class="catalog-card">
            <div class="card-tag">Esencial</div>
            <h4>Plan Básico</h4>
            <p class="card-desc">La tarjeta digital rápida y directa. Incluye diseño visual, datos del evento (fecha, hora), cuenta regresiva y confirmación de asistencia (RSVP) por WhatsApp.</p>
            <div class="card-actions-wrapper">
                <a href="visualizar_tarjeta.php?id=991" target="_blank" class="btn-card-demo">Ver Demo</a>
                <?php if ($ya_logueado): ?>
                    <a href="personalizar.php?plan=basico" class="btn-card-select">Crear con este plan</a>
                <?php else: ?>
                    <a href="registro.php" class="btn-card-select">Empezar gratis</a>
                <?php endif; ?>
            </div>
        </div>

        <!-- PLAN PREMIUM -->
        <div class="catalog-card premium">
            <div class="card-tag highlight">Más Elegido</div>
            <h4>Plan Premium</h4>
            <p class="card-desc">Administrada 100% por vos desde un Panel de Control. Activá módulos a elección: Ubicación (Maps), reporte de confirmados, Cobros, Muro de Deseos y muchos más.</p>
            <div class="card-actions-wrapper">
                <a href="visualizar_tarjeta.php?id=992" target="_blank" class="btn-card-demo">Ver Demo</a>
                <?php if ($ya_logueado): ?>
                    <a href="personalizar.php?plan=premium" class="btn-card-select">Crear con este plan</a>
                <?php else: ?>
                    <a href="registro.php" class="btn-card-select">Empezar gratis</a>
                <?php endif; ?>
            </div>
        </div>

        <!-- PLAN DELUXE (próximamente) -->
        <div class="catalog-card card-proximamente">
            <div class="card-tag card-tag-pronto">En Construcción</div>
            <h4>Plan Deluxe</h4>
            <p class="card-desc">Control VIP para alta exigencia. Enlaces únicos por invitado, validación por DNI/teléfono, confirmación sin duplicados, asignación de mesas digital y escaneo QR en puerta.</p>
            <div class="card-actions-wrapper">
                <span class="btn-card-demo btn-disabled" aria-disabled="true">Pronto</span>
                <span class="btn-card-select btn-disabled" aria-disabled="true">Próximamente</span>
            </div>
        </div>

    </div><!-- /.catalog-container -->
</section>

<!-- SECCIÓN: CÓMO FUNCIONA -->
<section class="como-funciona-section">
    <div class="section-title">
        <h3>¿Cómo funciona?</h3>
        <p>En tres pasos simples tenés tu invitación lista para compartir.</p>
    </div>
    <div class="pasos-container">
        <div class="paso">
            <div class="paso-numero">1</div>
            <h4>Configurás</h4>
            <p>Elegís el tipo de evento, el estilo visual y los módulos que querés incluir.</p>
        </div>
        <div class="paso-conector" aria-hidden="true">→</div>
        <div class="paso">
            <div class="paso-numero">2</div>
            <h4>Personalizás</h4>
            <p>Cargás los datos del evento: nombres, fecha, hora, lugar y más.</p>
        </div>
        <div class="paso-conector" aria-hidden="true">→</div>
        <div class="paso">
            <div class="paso-numero">3</div>
            <h4>Compartís</h4>
            <p>Una vez abonada, compartís el link con tus invitados por WhatsApp o redes.</p>
        </div>
    </div>
</section>

<?php include_once 'footer.php'; ?>

</body>
</html>