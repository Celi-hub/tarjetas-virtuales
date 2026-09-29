<?php
error_reporting(0);
require_once __DIR__ . '/conexion.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método no válido']);
    exit;
}

$tarjeta_id = intval($_POST['tarjeta_id'] ?? 0);
$estado = $_POST['estado'] ?? '';
$nombre = trim($_POST['nombre'] ?? '');
$telefono = trim($_POST['telefono'] ?? '');
$acompanantes = intval($_POST['acompanantes'] ?? 0);
$mensaje = trim($_POST['mensaje'] ?? '');
$nombres_acompanantes = $_POST['nombres_acompanantes'] ?? [];

// Eliminamos el campo email de las validaciones y variables
if (empty($estado) || empty($nombre) || $tarjeta_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Completá los campos obligatorios']);
    exit;
}

// Ya no existe 'pendiente'
if (!in_array($estado, ['confirmado', 'ausente'])) {
    echo json_encode(['success' => false, 'message' => 'Estado no válido']);
    exit;
}

// Si está ausente, forzamos los acompañantes a 0 para mantener la DB limpia
if ($estado === 'ausente') {
    $acompanantes = 0;
    $nombres_acompanantes = [];
}

try {
    // Iniciamos la transacción
    $pdo->beginTransaction();

    // 1. Insertamos al titular (omitimos la columna email que ahora queda NULL)
    $stmt = $pdo->prepare("
        INSERT INTO rsvp_respuestas 
        (tarjeta_id, estado, nombre, telefono, cantidad_acompanantes, mensaje, fecha_respuesta) 
        VALUES (?, ?, ?, ?, ?, ?, NOW())
    ");
    
    $stmt->execute([$tarjeta_id, $estado, $nombre, $telefono, $acompanantes, $mensaje]);
    
    // Obtenemos el ID exacto que le asignó MySQL a este titular
    $id_rsvp = $pdo->lastInsertId();

    // 2. Insertamos a los acompañantes
    if ($estado === 'confirmado' && $acompanantes > 0 && !empty($nombres_acompanantes)) {
        $stmtAcomp = $pdo->prepare("
            INSERT INTO rsvp_acompanantes (id_rsvp, nombre_acompanante) 
            VALUES (?, ?)
        ");
        
        foreach ($nombres_acompanantes as $nombre_acomp) {
            $nombre_acomp = trim($nombre_acomp);
            if (!empty($nombre_acomp)) {
                $stmtAcomp->execute([$id_rsvp, $nombre_acomp]);
            }
        }
    }

    // Si todo salió bien, guardamos definitivamente en ambas tablas
    $pdo->commit();
    echo json_encode(['success' => true]);

} catch (PDOException $e) {
    // Si hay algún error, revertimos todos los cambios
    $pdo->rollBack();
    echo json_encode(['success' => false, 'message' => 'Error BD: ' . $e->getMessage()]);
}