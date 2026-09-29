<?php
require_once __DIR__ . '/conexion.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['id_usuario'])) {
    header('Location: login.php');
    exit;
}

$id_tarjeta = isset($_GET['id']) ? (int)$_GET['id'] : (isset($_POST['id_tarjeta']) ? (int)$_POST['id_tarjeta'] : 0);
$id_usuario = $_SESSION['id_usuario'];

try {
    $stmt = $pdo->prepare('SELECT id_tarjeta, nombres_portada FROM tarjetas WHERE id_tarjeta = ? AND id_cliente = ?');
    $stmt->execute([$id_tarjeta, $id_usuario]);
    $tarjeta = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$tarjeta) {
        die('Error: Tarjeta no encontrada o acceso denegado.');
    }
} catch (PDOException $e) {
    die('Error de BD: ' . $e->getMessage());
}

$mensaje_alerta = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion'])) {
    $accion = $_POST['accion'];
    $id_deseo = (int)$_POST['id_deseo'];

    if ($accion === 'aprobar') {
        $pdo->prepare("UPDATE muro_deseos SET estado = 'aprobado' WHERE id_deseo = ? AND tarjeta_id = ?")->execute([$id_deseo, $id_tarjeta]);
        $mensaje_alerta = "<div class='alerta exito'>Mensaje aprobado y publicado en la tarjeta.</div>";
    } elseif ($accion === 'ocultar') {
        $pdo->prepare("UPDATE muro_deseos SET estado = 'pendiente' WHERE id_deseo = ? AND tarjeta_id = ?")->execute([$id_deseo, $id_tarjeta]);
        $mensaje_alerta = "<div class='alerta info'>Mensaje ocultado (ahora está pendiente).</div>";
    } elseif ($accion === 'eliminar') {
        $pdo->prepare("DELETE FROM muro_deseos WHERE id_deseo = ? AND tarjeta_id = ?")->execute([$id_deseo, $id_tarjeta]);
        $mensaje_alerta = "<div class='alerta exito'>Mensaje eliminado definitivamente.</div>";
    }
}

if (isset($_GET['exportar']) && $_GET['exportar'] === 'csv') {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=mensajes_tarjeta_' . $id_tarjeta . '.csv');
    $output = fopen('php://output', 'w');
    fputcsv($output, ['Fecha', 'Nombre', 'Mensaje', 'Estado']);
    
    $stmtCsv = $pdo->prepare("SELECT fecha_creacion, nombre, mensaje, estado FROM muro_deseos WHERE tarjeta_id = ? ORDER BY fecha_creacion DESC");
    $stmtCsv->execute([$id_tarjeta]);
    while ($row = $stmtCsv->fetch(PDO::FETCH_ASSOC)) {
        fputcsv($output, [$row['fecha_creacion'], $row['nombre'], $row['mensaje'], $row['estado']]);
    }
    fclose($output);
    exit;
}

$stmtMensajes = $pdo->prepare("SELECT * FROM muro_deseos WHERE tarjeta_id = ? ORDER BY fecha_creacion DESC");
$stmtMensajes->execute([$id_tarjeta]);
$mensajes = $stmtMensajes->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestionar Muro - Emociones Digitales</title>
    <link rel="stylesheet" href="css/global.css">
    <link rel="stylesheet" href="css/formularios.css">
</head>
<body>
    <?php include_once 'header.php'; ?>

    <main class="form-container">
        <div class="form-card" style="max-width: 800px;">
            <h1>Muro de Deseos</h1>
            <p class="subtitle">Gestioná los mensajes de: <?= htmlspecialchars($tarjeta['nombres_portada'] ?: 'Sin título') ?></p>
            
            <?= $mensaje_alerta ?>

            <div class="acciones-top">
                <a href="dashboard.php" style="color:#d4af37; text-decoration:none; font-weight:bold;">⬅ Volver al Panel</a>
                <a href="gestionar_deseos.php?id=<?= $id_tarjeta ?>&exportar=csv" class="btn-exportar">📥 Descargar Excel (CSV)</a>
            </div>

            <?php if (empty($mensajes)): ?>
                <p style="text-align: center; color: #7a6e67; margin-top: 40px; padding: 20px; background: #f9f9f9; border-radius: 8px;">Aún no hay mensajes en este muro.</p>
            <?php else: ?>
                <div class="lista-mensajes">
                    <?php foreach ($mensajes as $msj): ?>
                        <div class="msj-item <?= $msj['estado'] ?>">
                            <div class="msj-info">
                                <h4><?= htmlspecialchars($msj['nombre']) ?> 
                                    <span class="estado-badge <?= $msj['estado'] === 'pendiente' ? 'bg-pendiente' : 'bg-aprobado' ?>">
                                        <?= ucfirst($msj['estado']) ?>
                                    </span>
                                </h4>
                                <p><?= nl2br(htmlspecialchars($msj['mensaje'])) ?></p>
                                <small><?= date('d/m/Y H:i', strtotime($msj['fecha_creacion'])) ?></small>
                            </div>
                            <div class="msj-acciones">
                                <form method="POST" style="display:inline;">
                                    <input type="hidden" name="id_tarjeta" value="<?= $id_tarjeta ?>">
                                    <input type="hidden" name="id_deseo" value="<?= $msj['id_deseo'] ?>">
                                    
                                    <?php if ($msj['estado'] === 'pendiente'): ?>
                                        <button type="submit" name="accion" value="aprobar" class="btn-accion btn-aprobar">Aprobar</button>
                                    <?php else: ?>
                                        <button type="submit" name="accion" value="ocultar" class="btn-accion btn-ocultar">Ocultar</button>
                                    <?php endif; ?>
                                </form>

                                <form method="POST" style="display:inline;" onsubmit="return confirm('¿Eliminar este mensaje definitivamente?');">
                                    <input type="hidden" name="id_tarjeta" value="<?= $id_tarjeta ?>">
                                    <input type="hidden" name="id_deseo" value="<?= $msj['id_deseo'] ?>">
                                    <button type="submit" name="accion" value="eliminar" class="btn-accion btn-eliminar">Eliminar</button>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </main>
</body>
</html>