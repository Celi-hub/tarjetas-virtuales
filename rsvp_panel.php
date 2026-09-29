<?php
require_once __DIR__ . '/conexion.php';
require_once __DIR__ . '/config/modulos.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['id_usuario'])) {
    header("Location: login.php?error=debe_loguearse");
    exit;
}

$id_usuario = $_SESSION['id_usuario'];
$tarjeta_id = isset($_GET['tarjeta']) ? (int)$_GET['tarjeta'] : 0;

if ($tarjeta_id <= 0) {
    header("Location: dashboard.php");
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT * FROM tarjetas WHERE id_tarjeta = ? AND id_cliente = ?");
    $stmt->execute([$tarjeta_id, $id_usuario]);
    $tarjeta = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log('Error en rsvp_panel: ' . $e->getMessage());
    header("Location: dashboard.php?error=error_carga");
    exit;
}

if (!$tarjeta) {
    header("Location: dashboard.php?error=no_autorizado");
    exit;
}
if (empty($tarjeta['mod_rsvp_interno'])) {
    header("Location: dashboard.php?error=modulo_inactivo");
    exit;
}

// Exportar CSV
if (isset($_GET['exportar'])) {
    try {
        $stmt = $pdo->prepare("
            SELECT r.*, GROUP_CONCAT(a.nombre_acompanante SEPARATOR ', ') as nombres_acompanantes
            FROM rsvp_respuestas r
            LEFT JOIN rsvp_acompanantes a ON r.id_rsvp = a.id_rsvp
            WHERE r.tarjeta_id = ?
            GROUP BY r.id_rsvp
            ORDER BY r.fecha_respuesta DESC
        ");
        $stmt->execute([$tarjeta_id]);
        $filas = $stmt->fetchAll(PDO::FETCH_ASSOC);

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="rsvp_' . $tarjeta_id . '.csv"');
        $out = fopen('php://output', 'w');
        fprintf($out, chr(0xEF).chr(0xBB).chr(0xBF)); // BOM UTF-8 para Excel
        fputcsv($out, ['Titular', 'Teléfono', 'Estado', 'Total Lugares', 'Acompañantes', 'Mensaje', 'Fecha']);
        foreach ($filas as $r) {
            $lugares = ($r['estado'] === 'ausente') ? 0 : ($r['cantidad_acompanantes'] + 1);
            fputcsv($out, [
                $r['nombre'], $r['telefono'] ?? '',
                ucfirst($r['estado']), $lugares,
                $r['nombres_acompanantes'] ?? '',
                $r['mensaje'] ?? '', $r['fecha_respuesta'],
            ]);
        }
        fclose($out);
        exit;
    } catch (PDOException $e) {
        error_log('Error exportando RSVP: ' . $e->getMessage());
        header("Location: rsvp_panel.php?tarjeta=$tarjeta_id&error=export");
        exit;
    }
}

// Estadísticas
$estadisticas = ['confirmados' => 0, 'ausentes' => 0, 'respuestas' => 0];
try {
    $stmt = $pdo->prepare("
        SELECT estado,
               COUNT(*) as cantidad_respuestas,
               SUM(cantidad_acompanantes + 1) as lugares
        FROM rsvp_respuestas
        WHERE tarjeta_id = ?
        GROUP BY estado
    ");
    $stmt->execute([$tarjeta_id]);
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $s) {
        $estadisticas['respuestas'] += (int)$s['cantidad_respuestas'];
        if ($s['estado'] === 'confirmado') $estadisticas['confirmados'] = (int)$s['lugares'];
        if ($s['estado'] === 'ausente')    $estadisticas['ausentes']    = (int)$s['lugares'];
    }

    $stmt = $pdo->prepare("
        SELECT r.*, GROUP_CONCAT(a.nombre_acompanante SEPARATOR ', ') as nombres_acompanantes
        FROM rsvp_respuestas r
        LEFT JOIN rsvp_acompanantes a ON r.id_rsvp = a.id_rsvp
        WHERE r.tarjeta_id = ?
        GROUP BY r.id_rsvp
        ORDER BY r.fecha_respuesta DESC
    ");
    $stmt->execute([$tarjeta_id]);
    $respuestas = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log('Error cargando RSVP: ' . $e->getMessage());
    $respuestas = [];
}

$nombre_tarjeta = htmlspecialchars($tarjeta['nombres_portada'] ?: 'Sin título');
$fecha_fmt      = $tarjeta['fecha_evento'] ? date('d/m/Y', strtotime($tarjeta['fecha_evento'])) : '-';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel RSVP — Momentia</title>
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
            border-radius: 10px; padding: 14px 18px; margin-bottom: 28px;
        }
        .panel-contexto strong { color: #1a6b3c; display: block; }
        .panel-contexto span   { color: #7a6e67; font-size: 0.82rem; }

        /* ── Stats ── */
        .rsvp-stats {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 14px;
            margin-bottom: 28px;
        }
        .stat-card {
            border-radius: 12px;
            padding: 18px;
            text-align: center;
            border: 1px solid;
        }
        .stat-card h4 {
            font-size: 0.72rem; font-weight: 600;
            text-transform: uppercase; letter-spacing: 1px;
            margin: 0 0 8px 0;
        }
        .stat-card .numero {
            font-family: 'Playfair Display', Georgia, serif;
            font-size: 2.4rem; font-weight: 600; line-height: 1;
        }
        .stat-confirmados { background: #f0faf4; border-color: #c3e6cb; }
        .stat-confirmados h4 { color: #27ae60; }
        .stat-confirmados .numero { color: #1a6b3c; }
        .stat-ausentes { background: #fdf8ef; border-color: #f0ddb8; }
        .stat-ausentes h4 { color: #e67e22; }
        .stat-ausentes .numero { color: #d35400; }
        .stat-total { background: #f8fafc; border-color: #e2e8f0; }
        .stat-total h4 { color: #64748b; }
        .stat-total .numero { color: #344e67; }

        /* ── Barra de acciones ── */
        .panel-acciones {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 16px;
            flex-wrap: wrap;
            gap: 10px;
        }
        .panel-acciones h2 {
            font-size: 1rem; font-weight: 700;
            color: #344e67; margin: 0;
        }
        .btn-exportar {
            display: inline-flex; align-items: center; gap: 6px;
            background: #f0faf4; color: #27ae60;
            border: 1.5px solid #c3e6cb; border-radius: 8px;
            padding: 9px 16px; font-size: 0.82rem;
            font-weight: 600; text-decoration: none;
            transition: all 0.2s;
        }
        .btn-exportar:hover { background: #27ae60; color: white; border-color: #27ae60; }

        /* ── Tabla ── */
        .tabla-wrap { overflow-x: auto; }
        .rsvp-table {
            width: 100%; border-collapse: collapse;
            font-size: 0.88rem; min-width: 560px;
        }
        .rsvp-table th {
            background: #f8fafc;
            border-bottom: 2px solid #e2e8f0;
            padding: 11px 14px;
            text-align: left;
            font-size: 0.72rem;
            font-weight: 600;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .rsvp-table td {
            padding: 12px 14px;
            border-bottom: 1px solid #f0ece7;
            vertical-align: top;
            color: #344e67;
        }
        .rsvp-table tr:hover td { background: #fdfcfb; }
        .rsvp-table td small { color: #7a6e67; font-size: 0.8rem; }

        /* Badges de estado */
        .badge-confirmado {
            display: inline-block; padding: 3px 10px;
            background: #eafaf1; color: #1a6b3c;
            border: 1px solid #c3e6cb; border-radius: 20px;
            font-size: 0.75rem; font-weight: 600;
        }
        .badge-ausente {
            display: inline-block; padding: 3px 10px;
            background: #fdf6ec; color: #d35400;
            border: 1px solid #f0ddb8; border-radius: 20px;
            font-size: 0.75rem; font-weight: 600;
        }

        /* Empty state */
        .empty-state {
            text-align: center; padding: 50px 20px; color: #7a6e67;
        }
        .empty-state-icon { font-size: 2.5rem; margin-bottom: 12px; }

        /* Botón volver */
        .btn-volver {
            display: inline-block; color: #94a3b8;
            font-size: 0.85rem; text-decoration: none; margin-top: 16px;
        }
        .btn-volver:hover { color: var(--color-principal); }

        @media (max-width: 480px) {
            .rsvp-stats { grid-template-columns: 1fr 1fr; }
            .stat-total { grid-column: span 2; }
        }
    </style>
</head>
<body>
<?php include_once 'header.php'; ?>

<main class="form-container" style="max-width: 860px;">
    <div class="form-card">

        <h1>Panel de Confirmaciones</h1>
        <p class="subtitle">Seguí en tiempo real quién confirmó asistencia a tu evento.</p>

        <!-- Contexto -->
        <div class="panel-contexto">
            <span style="font-size:1.6rem;">📋</span>
            <div>
                <strong><?php echo $nombre_tarjeta; ?></strong>
                <span>Fecha del evento: <?php echo $fecha_fmt; ?></span>
            </div>
        </div>

        <!-- Stats -->
        <div class="rsvp-stats">
            <div class="stat-card stat-confirmados">
                <h4>Confirmados</h4>
                <div class="numero"><?php echo $estadisticas['confirmados']; ?></div>
            </div>
            <div class="stat-card stat-ausentes">
                <h4>No asisten</h4>
                <div class="numero"><?php echo $estadisticas['ausentes']; ?></div>
            </div>
            <div class="stat-card stat-total">
                <h4>Respuestas</h4>
                <div class="numero"><?php echo $estadisticas['respuestas']; ?></div>
            </div>
        </div>

        <?php if (!empty($respuestas)): ?>

            <div class="panel-acciones">
                <h2>Listado de respuestas</h2>
                <a href="?tarjeta=<?php echo $tarjeta_id; ?>&exportar=1" class="btn-exportar">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14">
                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                        <polyline points="7 10 12 15 17 10"/>
                        <line x1="12" y1="15" x2="12" y2="3"/>
                    </svg>
                    Exportar CSV
                </a>
            </div>

            <div class="tabla-wrap">
                <table class="rsvp-table">
                    <thead>
                        <tr>
                            <th>Titular</th>
                            <th>Teléfono</th>
                            <th>Estado</th>
                            <th>Lugares</th>
                            <th>Acompañantes</th>
                            <th>Mensaje</th>
                            <th>Fecha</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($respuestas as $r):
                            $lugares = ($r['estado'] === 'ausente') ? 0 : ($r['cantidad_acompanantes'] + 1);
                        ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($r['nombre']); ?></strong></td>
                                <td><?php echo htmlspecialchars($r['telefono'] ?? '—'); ?></td>
                                <td>
                                    <?php if ($r['estado'] === 'confirmado'): ?>
                                        <span class="badge-confirmado">✓ Asiste</span>
                                    <?php else: ?>
                                        <span class="badge-ausente">✕ No asiste</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo $lugares; ?></td>
                                <td><small><?php echo htmlspecialchars($r['nombres_acompanantes'] ?? '—'); ?></small></td>
                                <td><small><?php echo htmlspecialchars(mb_substr($r['mensaje'] ?? '', 0, 60)); ?><?php echo mb_strlen($r['mensaje'] ?? '') > 60 ? '…' : ''; ?></small></td>
                                <td><small><?php echo date('d/m H:i', strtotime($r['fecha_respuesta'])); ?></small></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

        <?php else: ?>
            <div class="empty-state">
                <div class="empty-state-icon">📭</div>
                <p>Todavía no hay confirmaciones para esta invitación.</p>
            </div>
        <?php endif; ?>

        <a href="dashboard.php" class="btn-volver">← Volver al Panel</a>

    </div>
</main>

<?php include_once 'footer.php'; ?>
</body>
</html>