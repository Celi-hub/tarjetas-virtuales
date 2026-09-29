<?php
require_once __DIR__ . '/conexion.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['id_usuario'])) {
    header("Location: login.php?error=debe_loguearse");
    exit;
}

$id_usuario = $_SESSION['id_usuario'];
$id_tarjeta = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$id_tarjeta) {
    header("Location: dashboard.php");
    exit;
}

// Verificar propiedad
try {
    $stmt = $pdo->prepare("SELECT id_tarjeta, nombres_portada, fecha_evento FROM tarjetas WHERE id_tarjeta = ? AND id_cliente = ?");
    $stmt->execute([$id_tarjeta, $id_usuario]);
    $tarjeta = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$tarjeta) {
        header("Location: dashboard.php?error=no_autorizado");
        exit;
    }
} catch (PDOException $e) {
    error_log('Error en gestionar_pagos: ' . $e->getMessage());
    header("Location: dashboard.php?error=error_carga");
    exit;
}

// Procesar aprobación / rechazo
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion'], $_POST['id_pago'])) {
    $accion   = $_POST['accion'];
    $id_pago  = (int)$_POST['id_pago'];
    if (in_array($accion, ['aprobado', 'rechazado'], true)) {
        try {
            $stmt = $pdo->prepare("UPDATE pagos_evento SET estado = ?, fecha_revision = NOW() WHERE id_pago = ? AND tarjeta_id = ?");
            $stmt->execute([$accion, $id_pago, $id_tarjeta]);
        } catch (PDOException $e) {
            error_log('Error actualizando pago: ' . $e->getMessage());
        }
    }
    header("Location: gestionar_pagos.php?id=$id_tarjeta&msg=$accion");
    exit;
}

// Obtener pagos
try {
    $stmt = $pdo->prepare("SELECT * FROM pagos_evento WHERE tarjeta_id = ? ORDER BY fecha_pago DESC");
    $stmt->execute([$id_tarjeta]);
    $pagos = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log('Error cargando pagos: ' . $e->getMessage());
    $pagos = [];
}

// Totales
$total_aprobado  = array_sum(array_column(array_filter($pagos, fn($p) => $p['estado'] === 'aprobado'),  'monto'));
$total_pendiente = array_sum(array_column(array_filter($pagos, fn($p) => $p['estado'] === 'pendiente'), 'monto'));

$nombre_tarjeta = htmlspecialchars($tarjeta['nombres_portada'] ?: 'Sin título');
$fecha_fmt      = $tarjeta['fecha_evento'] ? date('d/m/Y', strtotime($tarjeta['fecha_evento'])) : '—';

$msg = $_GET['msg'] ?? null;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestionar Pagos — Momentia</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Ms+Madi&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/global.css">
    <link rel="stylesheet" href="css/formularios.css">
    <style>
        /* ── Contexto ── */
        .panel-contexto {
            display: flex; align-items: center; gap: 12px;
            background: #f8fffe; border: 1px solid #c8e6d4;
            border-radius: 10px; padding: 14px 18px; margin-bottom: 24px;
        }
        .panel-contexto strong { color: #1a6b3c; display: block; }
        .panel-contexto span   { color: #7a6e67; font-size: 0.82rem; }

        /* ── Alertas ── */
        .alerta {
            padding: 12px 16px; border-radius: 10px;
            font-size: 0.9rem; margin-bottom: 20px;
            border-left: 4px solid;
        }
        .alerta-exito  { background: #f0faf4; color: #1a6b3c; border-color: #27ae60; }
        .alerta-error  { background: #fdf2f2; color: #922b21; border-color: #e74c3c; }

        /* ── Stats ── */
        .pagos-stats {
            display: grid; grid-template-columns: 1fr 1fr;
            gap: 14px; margin-bottom: 28px;
        }
        .stat-card {
            border-radius: 12px; padding: 18px;
            text-align: center; border: 1px solid;
        }
        .stat-card h4 {
            font-size: 0.72rem; font-weight: 600;
            text-transform: uppercase; letter-spacing: 1px; margin: 0 0 8px 0;
        }
        .stat-card .monto {
            font-family: 'Playfair Display', Georgia, serif;
            font-size: 1.8rem; font-weight: 600; line-height: 1;
        }
        .stat-aprobado  { background: #f0faf4; border-color: #c3e6cb; }
        .stat-aprobado h4 { color: #27ae60; }
        .stat-aprobado .monto { color: #1a6b3c; }
        .stat-pendiente { background: #fdf8ef; border-color: #f0ddb8; }
        .stat-pendiente h4 { color: #e67e22; }
        .stat-pendiente .monto { color: #d35400; }

        /* ── Tabla ── */
        .tabla-wrap { overflow-x: auto; }
        .tabla-pagos {
            width: 100%; border-collapse: collapse;
            font-size: 0.88rem; min-width: 600px;
        }
        .tabla-pagos th {
            background: #f8fafc;
            border-bottom: 2px solid #e2e8f0;
            padding: 11px 14px; text-align: left;
            font-size: 0.72rem; font-weight: 600;
            color: #64748b; text-transform: uppercase; letter-spacing: 0.5px;
        }
        .tabla-pagos td {
            padding: 12px 14px;
            border-bottom: 1px solid #f0ece7;
            vertical-align: middle; color: #344e67;
        }
        .tabla-pagos tr:hover td { background: #fdfcfb; }
        .tabla-pagos td small { color: #7a6e67; font-size: 0.8rem; }

        /* Badges */
        .badge {
            display: inline-block; padding: 3px 10px;
            border-radius: 20px; font-size: 0.75rem; font-weight: 600;
            border: 1px solid;
        }
        .badge-pendiente  { background: #fdf8ef; color: #d35400; border-color: #f0ddb8; }
        .badge-aprobado   { background: #eafaf1; color: #1a6b3c; border-color: #c3e6cb; }
        .badge-rechazado  { background: #fdf2f2; color: #922b21; border-color: #f5c6c6; }

        /* Comprobante */
        .btn-ver-comp {
            display: inline-flex; align-items: center; gap: 5px;
            background: #f1f5f9; color: #475569;
            border: 1px solid #e2e8f0; border-radius: 6px;
            padding: 5px 10px; font-size: 0.78rem; font-weight: 600;
            text-decoration: none; transition: all 0.2s;
        }
        .btn-ver-comp:hover { background: #475569; color: white; border-color: #475569; }

        /* Botones de acción */
        .acciones-form { display: flex; gap: 6px; }
        .btn-aprobar, .btn-rechazar {
            padding: 6px 12px; border: none; border-radius: 6px;
            font-size: 0.78rem; font-weight: 600; cursor: pointer;
            font-family: inherit; transition: all 0.2s;
        }
        .btn-aprobar  { background: #eafaf1; color: #1a6b3c; border: 1px solid #c3e6cb; }
        .btn-aprobar:hover  { background: #27ae60; color: white; border-color: #27ae60; }
        .btn-rechazar { background: #fdf2f2; color: #922b21; border: 1px solid #f5c6c6; }
        .btn-rechazar:hover { background: #e74c3c; color: white; border-color: #e74c3c; }
        .txt-procesado { color: #94a3b8; font-size: 0.8rem; font-style: italic; }

        /* Empty state */
        .empty-state { text-align: center; padding: 50px 20px; color: #7a6e67; }
        .empty-state-icon { font-size: 2.5rem; margin-bottom: 12px; }

        /* Volver */
        .btn-volver {
            display: inline-block; color: #94a3b8;
            font-size: 0.85rem; text-decoration: none; margin-top: 16px;
        }
        .btn-volver:hover { color: var(--color-principal); }
    </style>
</head>
<body>
<?php include_once 'header.php'; ?>

<main class="form-container" style="max-width: 900px;">
    <div class="form-card">

        <h1>Pagos Recibidos</h1>
        <p class="subtitle">Revisá y aprobá los comprobantes que enviaron tus invitados.</p>

        <!-- Alerta post-acción -->
        <?php if ($msg === 'aprobado'): ?>
            <div class="alerta alerta-exito">✓ Pago aprobado correctamente.</div>
        <?php elseif ($msg === 'rechazado'): ?>
            <div class="alerta alerta-error">✕ Pago marcado como rechazado.</div>
        <?php endif; ?>

        <!-- Contexto -->
        <div class="panel-contexto">
            <span style="font-size:1.6rem;">💳</span>
            <div>
                <strong><?php echo $nombre_tarjeta; ?></strong>
                <span>Fecha del evento: <?php echo $fecha_fmt; ?></span>
            </div>
        </div>

        <!-- Totales -->
        <div class="pagos-stats">
            <div class="stat-card stat-aprobado">
                <h4>Total Aprobado</h4>
                <div class="monto">$<?php echo number_format($total_aprobado, 0, ',', '.'); ?></div>
            </div>
            <div class="stat-card stat-pendiente">
                <h4>Pendiente de revisión</h4>
                <div class="monto">$<?php echo number_format($total_pendiente, 0, ',', '.'); ?></div>
            </div>
        </div>

        <?php if (!empty($pagos)): ?>
            <div class="tabla-wrap">
                <table class="tabla-pagos">
                    <thead>
                        <tr>
                            <th>Invitado</th>
                            <th>Detalle / Concepto</th>
                            <th>Monto</th>
                            <th>Comprobante</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($pagos as $pago): ?>
                            <tr>
                                <td>
                                    <strong><?php echo htmlspecialchars($pago['nombre_invitado']); ?></strong>
                                    <?php if (!empty($pago['fecha_pago'])): ?>
                                        <br><small><?php echo date('d/m H:i', strtotime($pago['fecha_pago'])); ?></small>
                                    <?php endif; ?>
                                </td>
                                <td><small><?php echo htmlspecialchars($pago['concepto'] ?? '—'); ?></small></td>
                                <td>
                                    <strong>$<?php echo number_format($pago['monto'], 0, ',', '.'); ?></strong>
                                </td>
                                <td>
                                    <?php if (!empty($pago['comprobante_img'])): ?>
                                        <a href="uploads/comprobantes/<?php echo htmlspecialchars($pago['comprobante_img']); ?>"
                                           target="_blank" class="btn-ver-comp">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="12" height="12">
                                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                                                <circle cx="12" cy="12" r="3"/>
                                            </svg>
                                            Ver
                                        </a>
                                    <?php else: ?>
                                        <small style="color:#94a3b8;">Sin archivo</small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php
                                    $estado = $pago['estado'] ?? 'pendiente';
                                    $labels = ['pendiente' => 'Pendiente', 'aprobado' => '✓ Aprobado', 'rechazado' => '✕ Rechazado'];
                                    ?>
                                    <span class="badge badge-<?php echo $estado; ?>">
                                        <?php echo $labels[$estado] ?? ucfirst($estado); ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($estado === 'pendiente'): ?>
                                        <form method="POST" class="acciones-form">
                                            <input type="hidden" name="id_pago" value="<?php echo (int)$pago['id_pago']; ?>">
                                            <button type="submit" name="accion" value="aprobado"  class="btn-aprobar">Aprobar</button>
                                            <button type="submit" name="accion" value="rechazado" class="btn-rechazar">Rechazar</button>
                                        </form>
                                    <?php else: ?>
                                        <span class="txt-procesado">Procesado</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

        <?php else: ?>
            <div class="empty-state">
                <div class="empty-state-icon">💸</div>
                <p>Todavía no recibiste ningún comprobante de pago.</p>
            </div>
        <?php endif; ?>

        <a href="dashboard.php" class="btn-volver">← Volver al Panel</a>

    </div>
</main>

<?php include_once 'footer.php'; ?>
</body>
</html>