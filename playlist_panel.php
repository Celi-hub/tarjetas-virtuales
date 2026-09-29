<?php
require_once __DIR__ . '/conexion.php';
require_once __DIR__ . '/config/modulos.php';

session_start();

if (!isset($_SESSION['id_usuario'])) {
    header("Location: login.php?error=debe_loguearse");
    exit;
}

$id_usuario = $_SESSION['id_usuario'];
$tarjeta_id = isset($_GET['tarjeta']) ? (int)$_GET['tarjeta'] : 0;

if ($tarjeta_id <= 0) {
    die('Error: No se especificó una tarjeta válida.');
}

$stmt = $pdo->prepare("SELECT * FROM tarjetas WHERE id_tarjeta = ? AND id_cliente = ?");
$stmt->execute([$tarjeta_id, $id_usuario]);
$tarjeta = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$tarjeta) {
    die('Tarjeta no encontrada o no tenés permiso.');
}

$stmt = $pdo->prepare("
    SELECT * FROM playlist_sugerencias 
    WHERE tarjeta_id = ? 
    ORDER BY fecha_sugerencia DESC
");
$stmt->execute([$tarjeta_id]);
$sugerencias = $stmt->fetchAll(PDO::FETCH_ASSOC);
$total_sugerencias = count($sugerencias);

if (isset($_GET['exportar']) && $tarjeta_id > 0) {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="playlist_' . $tarjeta_id . '.csv"');
    $output = fopen('php://output', 'w');
    
    fputcsv($output, ['ID', 'Cancion', 'Artista', 'Sugerido por', 'Fecha']);
    
    foreach ($sugerencias as $s) {
        fputcsv($output, [
            $s['id_sugerencia'], 
            $s['cancion'], 
            $s['artista'], 
            !empty($s['nombre_invitado']) ? $s['nombre_invitado'] : 'Anónimo', 
            $s['fecha_sugerencia']
        ]);
    }
    fclose($output);
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Playlist - Emociones Digitales</title>
    <!-- El panel usa el botón .btn-exportar y los tokens de color de la librería de componentes -->
    <link rel="stylesheet" href="css/base_tarjeta.css">
    <link rel="stylesheet" href="css/elegante.css">
    <link rel="stylesheet" href="css/formularios.css">
</head>
<body>
    <main class="form-container">
    <div class="rsvp-panel">
        <a href="dashboard.php" class="volver-link">← Volver al Dashboard</a>
        <h1>Panel de Playlist Colaborativa</h1>
        <p style="text-align:center; color:#7a6e67; margin-bottom: 20px;">
            <strong><?= htmlspecialchars($tarjeta['nombres_portada']) ?></strong> - <?= date('d/m/Y', strtotime($tarjeta['fecha_evento'])) ?>
        </p>
        
        <div class="rsvp-stats">
            <div class="stat-card confirmados">
                <h4>Total Sugerencias</h4>
                <div class="numero"><?= $total_sugerencias ?></div>
            </div>
        </div>
        
        <?php if ($total_sugerencias > 0): ?>
            <a href="?tarjeta=<?= $tarjeta_id ?>&exportar=1" class="btn-exportar">📥 Exportar a Excel</a>
            
            <table class="rsvp-table">
                <thead>
                    <tr>
                        <th>Canción</th>
                        <th>Artista</th>
                        <th>Sugerido por</th>
                        <th>Fecha</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($sugerencias as $s): ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($s['cancion']) ?></strong></td>
                            <td><?= htmlspecialchars($s['artista']) ?></td>
                            <td><small><?= htmlspecialchars(!empty($s['nombre_invitado']) ? $s['nombre_invitado'] : 'Anónimo') ?></small></td>
                            <td><small><?= date('d/m H:i', strtotime($s['fecha_sugerencia'])) ?></small></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p style="text-align:center; color:#7a6e67; margin-top:30px;">Aún no hay canciones sugeridas para esta tarjeta.</p>
        <?php endif; ?>
    </div>
</main>
</body>
</html>