<?php
require_once __DIR__ . '/conexion.php';
require_once __DIR__ . '/config/modulos.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['id_usuario'])) {
    header("Location: login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: dashboard.php');
    exit;
}

$posted_token  = $_POST['csrf_token'] ?? '';
$session_token = $_SESSION['csrf_token_editar_tarjeta'] ?? '';
if (!is_string($posted_token) || !is_string($session_token) || !hash_equals($session_token, $posted_token)) {
    // Invalid CSRF token
    header('Location: editar_tarjeta.php?id=' . (int)($_POST['id_tarjeta'] ?? 0) . '&error=error_csrf');
    exit;
}
unset($_SESSION['csrf_token_editar_tarjeta']);

$id_usuario = $_SESSION['id_usuario'];
$id_tarjeta = (int)($_POST['id_tarjeta'] ?? 0);

if ($id_tarjeta === 0) {
    header('Location: dashboard.php?error=tarjeta_invalida');
    exit;
}

try {
    $stmt = $pdo->prepare('SELECT * FROM tarjetas WHERE id_tarjeta = ? AND id_cliente = ?');
    $stmt->execute([$id_tarjeta, $id_usuario]);
    $tarjeta = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$tarjeta) {
        header('Location: dashboard.php?error=no_autorizado');
        exit;
    }
} catch (PDOException $e) {
    error_log('Error en actualizar_tarjeta_completa: ' . $e->getMessage());
    header('Location: dashboard.php?error=error_carga');
    exit;
}

$campos_set       = [];
$valores          = [];
$modulos_elegidos = $_POST['modulos'] ?? [];
$campos_ya_procesados = []; // evita duplicados en el SET

// ── 1. Módulos (checkboxes) ──
foreach (modulos_columnas_bd() as $columna) {
    $modulo = modulos_config()[$columna];
    if (!empty($modulo['incluido_base'])) {
        $campos_set[] = "$columna = 1";
    } else {
        $campos_set[] = "$columna = ?";
        $valores[]    = in_array($columna, $modulos_elegidos, true) ? 1 : 0;
    }
    $campos_ya_procesados[$columna] = true;
}

// ── 2. Estilo visual ──
if (!empty($_POST['estilo_visual_id'])) {
    $campos_set[] = "estilo_visual_id = ?";
    $valores[]    = (int)$_POST['estilo_visual_id'];
    $campos_ya_procesados['estilo_visual_id'] = true;
}

// ── 3. Frase de portada (campo base, no es módulo) ──
$frase_valor = isset($_POST['frase_portada']) ? trim($_POST['frase_portada']) : '';
$campos_set[] = "frase_portada = ?";
$valores[]    = $frase_valor !== '' ? $frase_valor : null;
$campos_ya_procesados['frase_portada'] = true;

// ── 4. Campos de texto base (nombres, fecha, hora) ──
foreach (campos_base_config() as $campo) {
    $nombre = $campo['name'];
    if (isset($campos_ya_procesados[$nombre])) continue;
    $campos_set[] = "$nombre = ?";
    $valores[]    = (isset($_POST[$nombre]) && trim($_POST[$nombre]) !== '')
                    ? trim($_POST[$nombre]) : null;
    $campos_ya_procesados[$nombre] = true;
}

// ── 5. Campos de texto de módulos ──
foreach (modulos_config() as $columna => $modulo) {
    if (empty($modulo['campos'])) continue;
    foreach ($modulo['campos'] as $campo) {
        $nombre = $campo['name'];
        if (isset($campos_ya_procesados[$nombre])) continue;
        if (in_array(($campo['type'] ?? ''), ['file_book', 'info_aviso'], true)) continue;
        $campos_set[] = "$nombre = ?";
        $valores[]    = (isset($_POST[$nombre]) && trim($_POST[$nombre]) !== '')
                        ? trim($_POST[$nombre]) : null;
        $campos_ya_procesados[$nombre] = true;
    }
}

// ── 6. Book de fotos ──
// El RSVP interno muestra este campo directamente en editar_tarjeta.php,
// por lo que no forma parte de la configuración de módulos anterior.
if (array_key_exists('fecha_limite_rsvp', $_POST)) {
    $campos_set[] = 'fecha_limite_rsvp = ?';
    $valores[]    = trim($_POST['fecha_limite_rsvp']) !== '' ? trim($_POST['fecha_limite_rsvp']) : null;
    $campos_ya_procesados['fecha_limite_rsvp'] = true;
}

if (!empty($_FILES['fotos_book']['name'][0])) {
    $directorio = 'uploads/book/' . $id_tarjeta . '/';
    if (!is_dir($directorio)) {
        mkdir($directorio, 0755, true);
    } else {
        array_map('unlink', glob("$directorio/*.*") ?: []);
    }
    $fotos_subidas = 0;
    foreach ($_FILES['fotos_book']['tmp_name'] as $tmp_name) {
        if ($fotos_subidas >= 5) break;
        if (move_uploaded_file($tmp_name, $directorio . 'foto_' . ($fotos_subidas + 1) . '.jpg')) {
            $fotos_subidas++;
        }
    }
}

// ── 6.5. Textos personalizados ──
$custom_txt_array = $_POST['custom_txt'] ?? [];
$filtered_custom_txt = array_filter($custom_txt_array, function($val) {
    return $val !== null && trim($val) !== '';
});
$campos_set[] = "textos_personalizados = ?";
$valores[]    = !empty($filtered_custom_txt) ? json_encode($filtered_custom_txt, JSON_UNESCAPED_UNICODE) : null;

// ── 7. UPDATE final ──
$valores[] = $id_tarjeta;
$valores[] = $id_usuario;

try {
    $sql  = 'UPDATE tarjetas SET ' . implode(', ', $campos_set) . ' WHERE id_tarjeta = ? AND id_cliente = ?';
    $stmt = $pdo->prepare($sql);
    $stmt->execute($valores);
    header('Location: dashboard.php?mensaje=tarjeta_actualizada');
    exit;
} 
catch (PDOException $e) {
    error_log('Error al actualizar tarjeta: ' . $e->getMessage());
    header('Location: editar_tarjeta.php?id=' . $id_tarjeta . '&error=error_guardado');
    exit;
}