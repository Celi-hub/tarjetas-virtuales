<?php
require_once __DIR__ . '/conexion.php';

// Configurar el encabezado para devolver JSON
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método no permitido.']);
    exit;
}

$tarjeta_id = isset($_POST['tarjeta_id']) ? (int)$_POST['tarjeta_id'] : 0;
$nombre = isset($_POST['nombre']) ? trim(strip_tags($_POST['nombre'])) : '';
$mensaje = isset($_POST['mensaje']) ? trim(strip_tags($_POST['mensaje'])) : '';

if ($tarjeta_id === 0 || empty($nombre) || empty($mensaje)) {
    echo json_encode(['success' => false, 'message' => 'Por favor, completá todos los campos.']);
    exit;
}

try {
    // 1. Verificamos la configuración de moderación de la tarjeta
    $stmtMod = $pdo->prepare("SELECT muro_moderacion FROM tarjetas WHERE id_tarjeta = ?");
    $stmtMod->execute([$tarjeta_id]);
    $tarjeta = $stmtMod->fetch(PDO::FETCH_ASSOC);

    if (!$tarjeta) {
        echo json_encode(['success' => false, 'message' => 'Tarjeta no válida.']);
        exit;
    }

    // 2. Definimos el estado inicial
    // Si muro_moderacion es 1 (Manual), entra como 'pendiente'. Si es 0, 'aprobado'.
    $estado = ((int)$tarjeta['muro_moderacion'] === 1) ? 'pendiente' : 'aprobado';

    // 3. Insertamos el mensaje
    $stmtIns = $pdo->prepare("INSERT INTO muro_deseos (tarjeta_id, nombre, mensaje, estado) VALUES (?, ?, ?, ?)");
    $stmtIns->execute([$tarjeta_id, $nombre, $mensaje, $estado]);

    echo json_encode(['success' => true, 'estado' => $estado]);

} catch (PDOException $e) {
    // Captura de errores de Base de Datos
    echo json_encode(['success' => false, 'message' => 'Error interno: ' . $e->getMessage()]);
}