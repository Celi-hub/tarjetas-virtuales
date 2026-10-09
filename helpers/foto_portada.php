<?php
/**
 * helpers/foto_portada.php — Foto personal de portada (módulo Deluxe mod_foto_portada).
 *
 * La foto se guarda en uploads/portadas/ con un nombre propio (nunca el del cliente)
 * y la ruta relativa queda en tarjetas.foto_portada. La usan:
 *  - guardar_tarjeta.php / actualizar_tarjeta_completa.php  -> momentia_procesar_foto_portada()
 *  - plantillas/modulos/base_portada.php                      -> momentia_url_foto_portada()
 */

const MOMENTIA_FOTO_PORTADA_DIR      = 'uploads/portadas/';
const MOMENTIA_FOTO_PORTADA_MAX_BYTES = 8 * 1024 * 1024;
const MOMENTIA_FOTO_PORTADA_TIPOS    = [
    'image/jpeg' => 'jpg',
    'image/png'  => 'png',
    'image/webp' => 'webp',
];

/**
 * Procesa el input file "foto_portada" y el checkbox "quitar_foto_portada".
 *
 * @param array $tarjeta Fila actual de la tabla tarjetas (para conocer la foto anterior).
 * @return array{cambio: bool, ruta: ?string, error: ?string}
 *         cambio=false -> no tocar la columna; cambio=true -> guardar "ruta" (null = sin foto).
 */
function momentia_procesar_foto_portada(array $tarjeta): array
{
    $anterior = $tarjeta['foto_portada'] ?? null;
    $id       = (int)($tarjeta['id_tarjeta'] ?? 0);
    $archivo  = $_FILES['foto_portada'] ?? null;
    $hay_archivo = is_array($archivo) && ($archivo['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE;

    if (!$hay_archivo) {
        if (!empty($_POST['quitar_foto_portada'])) {
            momentia_borrar_foto_portada($anterior);
            return ['cambio' => true, 'ruta' => null, 'error' => null];
        }
        return ['cambio' => false, 'ruta' => $anterior, 'error' => null];
    }

    if ($archivo['error'] !== UPLOAD_ERR_OK || !is_uploaded_file($archivo['tmp_name'])) {
        return ['cambio' => false, 'ruta' => $anterior, 'error' => 'foto_error_subida'];
    }
    if ($archivo['size'] > MOMENTIA_FOTO_PORTADA_MAX_BYTES) {
        return ['cambio' => false, 'ruta' => $anterior, 'error' => 'foto_muy_pesada'];
    }

    // El tipo se decide por el contenido real del archivo, no por su nombre ni por el navegador.
    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($archivo['tmp_name']);
    if (!isset(MOMENTIA_FOTO_PORTADA_TIPOS[$mime]) || @getimagesize($archivo['tmp_name']) === false) {
        return ['cambio' => false, 'ruta' => $anterior, 'error' => 'foto_formato_invalido'];
    }

    $dir_abs = __DIR__ . '/../' . MOMENTIA_FOTO_PORTADA_DIR;
    if (!is_dir($dir_abs) && !mkdir($dir_abs, 0755, true)) {
        return ['cambio' => false, 'ruta' => $anterior, 'error' => 'foto_error_subida'];
    }

    $nombre = 'portada_' . $id . '_' . bin2hex(random_bytes(6)) . '.' . MOMENTIA_FOTO_PORTADA_TIPOS[$mime];
    if (!move_uploaded_file($archivo['tmp_name'], $dir_abs . $nombre)) {
        return ['cambio' => false, 'ruta' => $anterior, 'error' => 'foto_error_subida'];
    }

    momentia_borrar_foto_portada($anterior);
    return ['cambio' => true, 'ruta' => MOMENTIA_FOTO_PORTADA_DIR . $nombre, 'error' => null];
}

/** Borra una foto de portada anterior, solo si está dentro de uploads/portadas/. */
function momentia_borrar_foto_portada(?string $ruta): void
{
    if (!$ruta || strpos($ruta, MOMENTIA_FOTO_PORTADA_DIR) !== 0) return;
    $abs = __DIR__ . '/../' . MOMENTIA_FOTO_PORTADA_DIR . basename($ruta);
    if (is_file($abs)) @unlink($abs);
}

/**
 * Ruta pública de la foto si la tarjeta tiene el módulo Deluxe activo y el archivo existe.
 */
function momentia_url_foto_portada(array $tarjeta): ?string
{
    if (empty($tarjeta['mod_foto_portada']) || empty($tarjeta['foto_portada'])) return null;
    $nombre = basename($tarjeta['foto_portada']);
    if (!is_file(__DIR__ . '/../' . MOMENTIA_FOTO_PORTADA_DIR . $nombre)) return null;
    return MOMENTIA_FOTO_PORTADA_DIR . $nombre;
}
