<?php
require_once __DIR__ . '/conexion.php';
require_once __DIR__ . '/config/modulos.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['id_usuario'])) {
    $_SESSION['redirect_tras_login'] = $_SERVER['REQUEST_URI'];
    header("Location: login.php?error=debe_loguearse");
    exit;
}

$id_tarjeta = (int)($_GET['id'] ?? 0);
$id_usuario = $_SESSION['id_usuario'];

if (!$id_tarjeta) {
    header("Location: dashboard.php");
    exit;
}

// Verificar propiedad y obtener datos
try {
    $stmt = $pdo->prepare("
        SELECT t.*, te.nombre AS nombre_evento
        FROM tarjetas t
        JOIN tipos_evento te ON t.tipo_evento_id = te.id_tipos_evento
        WHERE t.id_tarjeta = ? AND t.id_cliente = ?
    ");
    $stmt->execute([$id_tarjeta, $id_usuario]);
    $tarjeta = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$tarjeta) {
        header("Location: dashboard.php?error=no_autorizado");
        exit;
    }

    // Si ya está pagada, redirigir al dashboard
    if ($tarjeta['estado_pago'] === 'pagado') {
        header("Location: dashboard.php?msg=ya_pagada");
        exit;
    }

    // Obtener costos desde la BD
    $stmt_costos = $pdo->query("SELECT modulo_key, precio FROM costos");
    $costos = [];
    while ($row = $stmt_costos->fetch(PDO::FETCH_ASSOC)) {
        $costos[$row['modulo_key']] = (float)$row['precio'];
    }

} catch (PDOException $e) {
    error_log('Error en pagar.php: ' . $e->getMessage());
    header("Location: dashboard.php?error=error_carga");
    exit;
}

// Calcular total con TODOS los módulos activos
$precio_base = $costos['base'] ?? 3000;
$total       = $precio_base;
$detalle     = [];

foreach (modulos_columnas_bd() as $columna) {
    if (!empty($tarjeta[$columna]) && isset($costos[$columna]) && $costos[$columna] > 0) {
        $total += $costos[$columna];
        $modulo = modulos_config()[$columna];
        $detalle[] = [
            'nombre' => $modulo['titulo'],
            'precio' => $costos[$columna],
        ];
    }
}

$nombre_tarjeta = htmlspecialchars($tarjeta['nombres_portada'] ?: 'Sin título');
$tipo_evento    = ucfirst($tarjeta['nombre_evento'] ?? '');
$fecha_fmt      = $tarjeta['fecha_evento']
    ? date('d/m/Y', strtotime($tarjeta['fecha_evento']))
    : 'Sin definir';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resumen de Pago — Momentia</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Ms+Madi&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/global.css">
    <link rel="stylesheet" href="css/formularios.css">
    <style>
        /* ── Chip de contexto ── */
        .pago-contexto {
            background: #f8fffe;
            border: 1px solid #c8e6d4;
            border-radius: 10px;
            padding: 14px 18px;
            margin-bottom: 28px;
            font-size: 0.88rem;
            color: #344e67;
        }
        .pago-contexto strong { color: #1a6b3c; display: block; margin-bottom: 2px; }
        .pago-contexto span   { color: #7a6e67; font-size: 0.82rem; }

        /* ── Tabla de desglose ── */
        .pago-desglose {
            width: 100%;
            border: 1px solid #e8ddd2;
            border-radius: 10px;
            overflow: hidden;
            margin-bottom: 24px;
        }
        .pago-desglose-header {
            background: #fdfbf8;
            padding: 12px 18px;
            font-size: 0.72rem;
            font-weight: 600;
            color: #94a3b8;
            text-transform: uppercase;
            letter-spacing: 1px;
            border-bottom: 1px solid #e8ddd2;
            display: flex;
            justify-content: space-between;
        }
        .pago-desglose-fila {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px 18px;
            border-bottom: 1px solid #f0ece7;
            font-size: 0.9rem;
            color: #344e67;
        }
        .pago-desglose-fila:last-child { border-bottom: none; }
        .pago-desglose-fila.base { font-weight: 600; }
        .pago-desglose-fila .precio {
            font-family: 'Playfair Display', Georgia, serif;
            font-size: 1rem;
            color: #2c3e50;
        }
        .pago-desglose-total {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 16px 18px;
            background: #fdfbf8;
            border-top: 2px solid var(--color-principal);
        }
        .pago-desglose-total .label {
            font-weight: 700;
            font-size: 0.95rem;
            color: #344e67;
        }
        .pago-desglose-total .monto {
            font-family: 'Playfair Display', Georgia, serif;
            font-size: 1.6rem;
            font-weight: 600;
            color: var(--color-principal);
        }

        /* ── Métodos de pago ── */
        .pago-metodos {
            display: flex;
            flex-direction: column;
            gap: 12px;
            margin-top: 4px;
        }
        .pago-metodo-btn {
            width: 100%;
            padding: 16px 20px;
            border-radius: 10px;
            border: 1.5px solid;
            font-family: inherit;
            font-size: 0.92rem;
            font-weight: 600;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 12px;
            transition: all 0.2s ease;
            text-decoration: none;
        }
        .btn-mp {
            background: #e8f4fd;
            border-color: #009ee3;
            color: #007ab8;
        }
        .btn-mp:hover {
            background: #009ee3;
            color: #ffffff;
            border-color: #009ee3;
        }
        .btn-transferencia {
            background: #fdfbf8;
            border-color: var(--color-principal);
            color: #8e7356;
        }
        .btn-transferencia:hover {
            background: var(--color-principal);
            color: #ffffff;
            border-color: var(--color-principal);
        }
        .pago-metodo-btn svg { flex-shrink: 0; }
        .pago-metodo-info { text-align: left; }
        .pago-metodo-info small {
            display: block;
            font-size: 0.76rem;
            font-weight: 400;
            opacity: 0.75;
            margin-top: 2px;
        }

        /* ── Botón volver ── */
        .btn-volver-dash {
            display: block;
            text-align: center;
            color: #94a3b8;
            font-size: 0.85rem;
            text-decoration: none;
            margin-top: 16px;
        }
        .btn-volver-dash:hover { color: var(--color-principal); }
    </style>
</head>
<body>

<?php include_once 'header.php'; ?>

<main class="form-container">
    <div class="form-card">

        <h1>Resumen de Pago</h1>
        <p class="subtitle">Revisá el detalle de tu invitación antes de continuar.</p>

        <!-- Chip de contexto -->
        <div class="pago-contexto">
            <strong><?php echo $nombre_tarjeta; ?></strong>
            <span><?php echo $tipo_evento; ?> · <?php echo $fecha_fmt; ?></span>
        </div>

        <!-- Desglose de costos -->
        <div class="pago-desglose">
            <div class="pago-desglose-header">
                <span>Detalle</span>
                <span>Precio</span>
            </div>

            <!-- Base -->
            <div class="pago-desglose-fila base">
                <span>Tarjeta base</span>
                <span class="precio">$<?php echo number_format($precio_base, 0, ',', '.'); ?></span>
            </div>

            <!-- Módulos activos -->
            <?php foreach ($detalle as $item): ?>
                <div class="pago-desglose-fila">
                    <span><?php echo htmlspecialchars($item['nombre']); ?></span>
                    <span class="precio">+ $<?php echo number_format($item['precio'], 0, ',', '.'); ?></span>
                </div>
            <?php endforeach; ?>

            <!-- Total -->
            <div class="pago-desglose-total">
                <span class="label">Total</span>
                <span class="monto">$<?php echo number_format($total, 0, ',', '.'); ?></span>
            </div>
        </div>

        <!-- Métodos de pago -->
        <fieldset>
            <legend>¿Cómo querés pagar?</legend>
            <div class="pago-metodos">

                <form action="procesar_pago.php" method="POST" style="margin:0;">
                    <input type="hidden" name="id_tarjeta" value="<?php echo $id_tarjeta; ?>">
                    <input type="hidden" name="monto"      value="<?php echo $total; ?>">
                    <input type="hidden" name="metodo"     value="mercadopago">
                    <button type="submit" class="pago-metodo-btn btn-mp">
                        <svg viewBox="0 0 24 24" fill="currentColor" width="22" height="22" aria-hidden="true">
                            <path d="M12 0C5.373 0 0 5.373 0 12s5.373 12 12 12 12-5.373 12-12S18.627 0 12 0zm5.562 8.248l-1.97 9.33c-.145.658-.537.818-1.084.508l-3-2.21-1.447 1.394c-.16.16-.295.295-.605.295l.213-3.053 5.56-5.023c.242-.213-.054-.333-.373-.12l-6.871 4.326-2.962-.924c-.643-.204-.657-.643.136-.953l11.57-4.461c.537-.194 1.006.131.833.891z"/>
                        </svg>
                        <div class="pago-metodo-info">
                            Pagar con Mercado Pago
                            <small>Débito, crédito o dinero en cuenta</small>
                        </div>
                    </button>
                </form>

                <form action="procesar_pago.php" method="POST" style="margin:0;">
                    <input type="hidden" name="id_tarjeta" value="<?php echo $id_tarjeta; ?>">
                    <input type="hidden" name="monto"      value="<?php echo $total; ?>">
                    <input type="hidden" name="metodo"     value="transferencia">
                    <button type="submit" class="pago-metodo-btn btn-transferencia">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" width="22" height="22" aria-hidden="true">
                            <rect x="1" y="4" width="22" height="16" rx="2" ry="2"/>
                            <line x1="1" y1="10" x2="23" y2="10"/>
                        </svg>
                        <div class="pago-metodo-info">
                            Pagar por Transferencia
                            <small>Alias o CBU — enviás el comprobante</small>
                        </div>
                    </button>
                </form>

            </div>
        </fieldset>

        <a href="dashboard.php" class="btn-volver-dash">← Volver al Panel</a>

    </div>
</main>

<?php include_once 'footer.php'; ?>

</body>
</html>