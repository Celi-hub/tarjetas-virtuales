<?php
require_once __DIR__ . '/conexion.php';
require_once __DIR__ . '/config/modulos.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['id_usuario'])) {
    header('Location: login.php?error=debe_loguearse');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: dashboard.php');
    exit;
}

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
    error_log('Error en guardar_tarjeta: ' . $e->getMessage());
    header('Location: dashboard.php?error=error_carga');
    exit;
}

// Campos base según módulos activos
$campos_permitidos = campos_datos_para_tarjeta($tarjeta);

// Agregar frase_portada explícitamente (no es un módulo, es un campo base)
if (!in_array('frase_portada', $campos_permitidos, true)) {
    $campos_permitidos[] = 'frase_portada';
}

$usaWhatsapp = tarjeta_muestra_rsvp_whatsapp($tarjeta);
if ($usaWhatsapp && empty($_POST['telefono_whatsapp'])) {
    header('Location: completar_datos.php?id=' . $id_tarjeta . '&error=falta_whatsapp');
    exit;
}

$campos_set = [];
$valores    = [];

foreach ($campos_permitidos as $campo) {
    if (isset($_POST[$campo]) && trim($_POST[$campo]) !== '') {
        $campos_set[] = "$campo = ?";
        $valores[]    = trim($_POST[$campo]);
    } else {
        $campos_set[] = "$campo = NULL";
    }
}

// Book de fotos
if (!empty($tarjeta['mod_book_fotos']) && !empty($_FILES['fotos_book']['name'][0])) {
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

$valores[] = $id_tarjeta;
$valores[] = $id_usuario;

try {
    $sql  = 'UPDATE tarjetas SET ' . implode(', ', $campos_set) . ' WHERE id_tarjeta = ? AND id_cliente = ?';
    $stmt = $pdo->prepare($sql);
    $stmt->execute($valores);

    header('Location: dashboard.php?mensaje=tarjeta_creada');
    exit;
} catch (PDOException $e) {
    error_log('Error al guardar tarjeta: ' . $e->getMessage());
    header('Location: completar_datos.php?id=' . $id_tarjeta . '&error=error_guardado');
    exit;
}