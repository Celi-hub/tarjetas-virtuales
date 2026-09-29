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

// Verificar que la tarjeta le pertenece al usuario logueado
try {
    $stmt = $pdo->prepare('SELECT id_tarjeta FROM tarjetas WHERE id_tarjeta = ? AND id_cliente = ?');
    $stmt->execute([$id_tarjeta, $id_usuario]);
    if (!$stmt->fetch()) {
        die('Error: Tarjeta no encontrada o acceso denegado.');
    }
} catch (PDOException $e) {
    die('Error de BD: ' . $e->getMessage());
}

$mensaje = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'] ?? '';

    if ($accion === 'agregar') {
        $fecha_anio  = trim($_POST['fecha_anio'] ?? '');
        $titulo      = trim($_POST['titulo'] ?? '');
        $descripcion = trim($_POST['descripcion'] ?? '');
        $orden       = (int)($_POST['orden'] ?? 0);
        $foto_nombre = null;

        if (empty($fecha_anio) || empty($titulo)) {
            $mensaje = "<p style='color:red;'>La fecha/año y el título son obligatorios.</p>";
        } else {
            if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
                $dir_destino = __DIR__ . '/uploads/hitos/';
                if (!is_dir($dir_destino)) {
                    mkdir($dir_destino, 0777, true);
                }

                $ext = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));
                $permitidas = ['jpg', 'jpeg', 'png', 'webp'];
                
                if (in_array($ext, $permitidas)) {
                    $foto_nombre = uniqid('lt_') . '.' . $ext;
                    move_uploaded_file($_FILES['foto']['tmp_name'], $dir_destino . $foto_nombre);
                }
            }

            $stmtIns = $pdo->prepare("INSERT INTO linea_tiempo_hitos (tarjeta_id, fecha_anio, titulo, descripcion, foto, orden) VALUES (?, ?, ?, ?, ?, ?)");
            $stmtIns->execute([$id_tarjeta, $fecha_anio, $titulo, $descripcion, $foto_nombre, $orden]);
            $mensaje = "<p style='color:green;'>Hito agregado exitosamente.</p>";
        }
    } elseif ($accion === 'eliminar') {
        $id_hito = (int)$_POST['id_hito'];
        
        $stmtFoto = $pdo->prepare("SELECT foto FROM linea_tiempo_hitos WHERE id_hito = ? AND tarjeta_id = ?");
        $stmtFoto->execute([$id_hito, $id_tarjeta]);
        $hito = $stmtFoto->fetch(PDO::FETCH_ASSOC);

        if ($hito) {
            if (!empty($hito['foto'])) {
                $ruta_foto = __DIR__ . '/uploads/hitos/' . $hito['foto'];
                if (file_exists($ruta_foto)) {
                    unlink($ruta_foto);
                }
            }
            $pdo->prepare("DELETE FROM linea_tiempo_hitos WHERE id_hito = ?")->execute([$id_hito]);
            $mensaje = "<p style='color:green;'>Hito eliminado.</p>";
        }
    }
}

// Obtener hitos actuales
$stmtHitos = $pdo->prepare("SELECT * FROM linea_tiempo_hitos WHERE tarjeta_id = ? ORDER BY orden ASC, id_hito ASC");
$stmtHitos->execute([$id_tarjeta]);
$hitos = $stmtHitos->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestionar Línea de Tiempo - Emociones Digitales</title>
    <link rel="stylesheet" href="css/global.css">
    <link rel="stylesheet" href="css/formularios.css">
    <style>
        .lista-hitos { margin-top: 30px; }
        .hito-item { background: #fffcf9; border: 1px solid #ebdccb; padding: 15px; border-radius: 8px; margin-bottom: 15px; display: flex; align-items: center; justify-content: space-between; }
        .hito-info strong { display: block; color: #344e67; font-size: 1.1rem; }
        .hito-info span { color: #7a6e67; font-size: 0.9rem; }
        .hito-foto { width: 60px; height: 60px; object-fit: cover; border-radius: 5px; margin-right: 15px; }
        .hito-flex { display: flex; align-items: center; }
        .btn-eliminar { background: #e74c3c; color: white; border: none; padding: 8px 12px; border-radius: 5px; cursor: pointer; }
    </style>
</head>
<body>
    <?php include_once 'header.php'; ?>

    <main class="form-container">
        <div class="form-card">
            <h1>Nuestra Historia</h1>
            <p class="subtitle">Agregá los momentos clave (hitos) para tu línea de tiempo.</p>
            
            <?= $mensaje ?>

            <form action="gestionar_hitos.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="accion" value="agregar">
                <input type="hidden" name="id_tarjeta" value="<?= $id_tarjeta ?>">

                <fieldset>
                    <legend>Nuevo Momento</legend>
                    
                    <div class="form-group">
                        <label>Fecha o Año *</label>
                        <input type="text" name="fecha_anio" placeholder="Ej: 2019 o 15 de Marzo" required>
                    </div>

                    <div class="form-group">
                        <label>Título del momento *</label>
                        <input type="text" name="titulo" placeholder="Ej: Nos conocimos" required>
                    </div>

                    <div class="form-group">
                        <label>Breve descripción (opcional)</label>
                        <textarea name="descripcion" rows="2" placeholder="Un pequeño detalle de este recuerdo..."></textarea>
                    </div>

                    <div class="form-group">
                        <label>Foto (opcional)</label>
                        <input type="file" name="foto" accept="image/jpeg, image/png, image/webp">
                    </div>

                    <div class="form-group">
                        <label>Orden (opcional)</label>
                        <input type="number" name="orden" value="0" style="width: 100px;">
                        <small style="color: #7a6e67; font-size: 0.85em;">Un número menor se mostrará primero.</small>
                    </div>
                </fieldset>

                <button type="submit" class="btn-submit">➕ Agregar Momento</button>
            </form>

            <?php if (!empty($hitos)): ?>
                <div class="lista-hitos">
                    <h3>Momentos Guardados</h3>
                    <?php foreach ($hitos as $hito): ?>
                        <div class="hito-item">
                            <div class="hito-flex">
                                <?php if ($hito['foto']): ?>
                                    <img src="uploads/hitos/<?= htmlspecialchars($hito['foto']) ?>" class="hito-foto" alt="Foto">
                                <?php endif; ?>
                                <div class="hito-info">
                                    <strong><?= htmlspecialchars($hito['titulo']) ?></strong>
                                    <span><?= htmlspecialchars($hito['fecha_anio']) ?></span>
                                </div>
                            </div>
                            <form action="gestionar_hitos.php" method="POST" onsubmit="return confirm('¿Seguro que querés eliminar este hito?');">
                                <input type="hidden" name="accion" value="eliminar">
                                <input type="hidden" name="id_tarjeta" value="<?= $id_tarjeta ?>">
                                <input type="hidden" name="id_hito" value="<?= $hito['id_hito'] ?>">
                                <button type="submit" class="btn-eliminar">Eliminar</button>
                            </form>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            
            <a href="dashboard.php" style="display:block; text-align:center; margin-top:20px; color:#d4af37; text-decoration:none;">Volver al Panel Principal</a>
        </div>
    </main>
</body>
</html>