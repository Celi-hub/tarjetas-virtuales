<?php
/**
 * helpers/archivos_tarjeta.php — Archivos subidos que pertenecen a una tarjeta.
 *
 * Al borrar una tarjeta, la BD limpia sus tablas satélite por ON DELETE CASCADE,
 * pero los archivos en uploads/ quedan huérfanos. Estas funciones los ubican y los borran.
 * Uso: eliminar_tarjeta.php. Primero se listan (antes del DELETE, porque el CASCADE se
 * lleva las filas que dicen qué archivo es de quién) y se borran recién después de
 * confirmar el DELETE en la BD.
 *
 * Cada ruta se arma con una carpeta fija + basename(), así un valor manipulado en la BD
 * nunca puede apuntar fuera de uploads/.
 */

require_once __DIR__ . '/foto_portada.php';

const MOMENTIA_UPLOADS_DIR = __DIR__ . '/../uploads/';

/**
 * Rutas absolutas de todos los archivos que existen en disco para esta tarjeta:
 * book de fotos, fotos de hitos, comprobantes de pago de invitados y foto de portada.
 *
 * @param array $tarjeta Debe traer id_tarjeta y foto_portada.
 * @return string[]
 */
function momentia_archivos_de_tarjeta(PDO $pdo, array $tarjeta): array
{
    $id       = (int)$tarjeta['id_tarjeta'];
    $archivos = glob(MOMENTIA_UPLOADS_DIR . 'book/' . $id . '/*') ?: [];

    $consultas = [
        'hitos/'        => 'SELECT foto FROM linea_tiempo_hitos WHERE tarjeta_id = ?',
        'comprobantes/' => 'SELECT comprobante_img FROM pagos_evento WHERE tarjeta_id = ?',
    ];
    foreach ($consultas as $carpeta => $sql) {
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$id]);
        foreach ($stmt->fetchAll(PDO::FETCH_COLUMN) as $nombre) {
            if ($nombre) $archivos[] = MOMENTIA_UPLOADS_DIR . $carpeta . basename($nombre);
        }
    }

    $portada = $tarjeta['foto_portada'] ?? '';
    if ($portada && strpos($portada, MOMENTIA_FOTO_PORTADA_DIR) === 0) {
        $archivos[] = MOMENTIA_UPLOADS_DIR . 'portadas/' . basename($portada);
    }

    return array_values(array_unique(array_filter($archivos, 'is_file')));
}

/**
 * Borra los archivos listados y la carpeta del book de la tarjeta.
 *
 * @return int Cantidad de archivos/carpetas que no se pudieron borrar (quedan en el log).
 */
function momentia_borrar_archivos_tarjeta(int $id_tarjeta, array $archivos): int
{
    $fallas = 0;
    foreach ($archivos as $archivo) {
        if (is_file($archivo) && !@unlink($archivo)) {
            $fallas++;
            error_log("Tarjeta {$id_tarjeta}: no se pudo borrar {$archivo}");
        }
    }

    $dir_book = MOMENTIA_UPLOADS_DIR . 'book/' . $id_tarjeta;
    if (is_dir($dir_book) && !@rmdir($dir_book)) {
        $fallas++;
        error_log("Tarjeta {$id_tarjeta}: no se pudo borrar la carpeta {$dir_book}");
    }

    return $fallas;
}
