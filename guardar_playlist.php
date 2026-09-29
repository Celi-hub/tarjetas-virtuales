<?php
error_reporting(0);
require_once __DIR__ . '/conexion.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método no válido']);
    exit;
}

$tarjeta_id = intval($_POST['tarjeta_id'] ?? 0);
$cancion = trim($_POST['cancion'] ?? '');
$artista = trim($_POST['artista'] ?? '');
$nombre_invitado = trim($_POST['nombre_invitado'] ?? '');

if ($tarjeta_id <= 0 || empty($cancion) || empty($artista)) {
    echo json_encode(['success' => false, 'message' => 'Completá los campos obligatorios']);
    exit;
}

try {
    $stmt = $pdo->prepare("
        INSERT INTO playlist_sugerencias 
        (tarjeta_id, cancion, artista, nombre_invitado, fecha_sugerencia) 
        VALUES (?, ?, ?, ?, NOW())
    ");
    
    $stmt->execute([
        $tarjeta_id, 
        $cancion, 
        $artista, 
        !empty($nombre_invitado) ? $nombre_invitado : null
    ]);

    echo json_encode(['success' => true]);

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Error BD: ' . $e->getMessage()]);
}