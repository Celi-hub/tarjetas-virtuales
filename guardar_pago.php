<?php
require_once __DIR__ . '/conexion.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método no permitido.']);
    exit;
}

$tarjeta_id = isset($_POST['tarjeta_id']) ? (int)$_POST['tarjeta_id'] : 0;
$nombre = isset($_POST['nombre_invitado']) ? trim(strip_tags($_POST['nombre_invitado'])) : '';
$concepto = isset($_POST['concepto']) ? trim(strip_tags($_POST['concepto'])) : '';
$monto = isset($_POST['monto']) ? (float)$_POST['monto'] : 0;

if (!$tarjeta_id || empty($nombre) || empty($concepto) || $monto <= 0) {
    echo json_encode(['success' => false, 'message' => 'Faltan datos obligatorios o el monto es inválido.']);
    exit;
}

if (!isset($_FILES['comprobante']) || $_FILES['comprobante']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['success' => false, 'message' => 'Error al subir el archivo del comprobante.']);
    exit;
}

$upload_dir = 'uploads/comprobantes/';
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0755, true);
}

$ext = strtolower(pathinfo($_FILES['comprobante']['name'], PATHINFO_EXTENSION));
$allowed_ext = ['jpg', 'jpeg', 'png', 'pdf'];

if (!in_array($ext, $allowed_ext)) {
    echo json_encode(['success' => false, 'message' => 'Formato de archivo no permitido. Solo JPG, PNG o PDF.']);
    exit;
}

$mime = (new finfo(FILEINFO_MIME_TYPE))->file($_FILES['comprobante']['tmp_name']);
if (!in_array($mime, ['image/jpeg', 'image/png', 'application/pdf'], true)) {
    echo json_encode(['success' => false, 'message' => 'El archivo no es una imagen o PDF válido.']);
    exit;
}

$filename = 'pago_' . $tarjeta_id . '_' . time() . '_' . uniqid() . '.' . $ext;
$target_file = $upload_dir . $filename;

try {
    $chk = $pdo->prepare("SELECT 1 FROM tarjetas WHERE id_tarjeta = ?");
    $chk->execute([$tarjeta_id]);
    if (!$chk->fetchColumn()) {
        echo json_encode(['success' => false, 'message' => 'Tarjeta no válida.']);
        exit;
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Error de base de datos.']);
    exit;
}

if (move_uploaded_file($_FILES['comprobante']['tmp_name'], $target_file)) {
    try {
        $stmt = $pdo->prepare("INSERT INTO pagos_evento (tarjeta_id, nombre_invitado, concepto, monto, comprobante_img, estado) VALUES (?, ?, ?, ?, ?, 'pendiente')");
        $stmt->execute([$tarjeta_id, $nombre, $concepto, $monto, $target_file]);
        echo json_encode(['success' => true]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Error de base de datos.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Error al guardar el archivo en el servidor.']);
}