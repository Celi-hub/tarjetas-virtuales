<?php
// unificar_rutas.php
$archivos = [
    'actualizar_tarjeta_completa.php',
    'configurar_pagos.php',
    'guardar_playlist.php',
    'guardar_pago.php',
    'guardar_deseo.php',
    'gestionar_pagos.php',
    'editar_tarjeta.php',
    'gestionar_hitos.php',
    'dashboard.php',
    'gestionar_deseos.php',
    'eliminar_tarjeta.php',
    'guardar_tarjeta.php',
    'guardar_rsvp.php',
    'index.php',
    'pagar_seleccionadas.php',
    'pagar.php',
    'personalizar.php',
    'procesar_paso1.php',
    'visualizar_tarjeta.php',
    'validar_login.php',
    'playlist_panel.php',
    'procesar_registro.php',
    'rsvp_panel.php'
];

foreach ($archivos as $archivo) {
    if (!file_exists($archivo)) {
        echo "El archivo $archivo no existe.\n";
        continue;
    }
    
    $content = file_get_contents($archivo);
    $original = $content;
    
    // Reemplazar require_once 'conexion.php';
    $content = preg_replace(
        '/require_once\s+[\'"]conexion\.php[\'"]\s*;/',
        "require_once __DIR__ . '/conexion.php';",
        $content
    );
    
    // Reemplazar require 'conexion.php';
    $content = preg_replace(
        '/require\s+[\'"]conexion\.php[\'"]\s*;/',
        "require_once __DIR__ . '/conexion.php';",
        $content
    );

    if ($content !== $original) {
        file_put_contents($archivo, $content);
        echo "Unificado conexion.php en: $archivo\n";
    } else {
        echo "Sin cambios necesarios en: $archivo\n";
    }
}
