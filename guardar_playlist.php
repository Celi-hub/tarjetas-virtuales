<?php
require_once __DIR__ . '/conexion.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método no válido']);
    exit;
}

$tarjeta_id = intval($_POST['tarjeta_id'] ?? 0);
$cancion = trim(strip_tags((string)($_POST['cancion'] ?? '')));
$artista = trim(strip_tags((string)($_POST['artista'] ?? '')));
$nombre_invitado = trim(strip_tags((string)($_POST['nombre_invitado'] ?? '')));

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
    error_log('Error en guardar_playlist: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Error interno. Intentá nuevamente.']);
}