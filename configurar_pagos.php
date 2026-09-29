<?php
require_once __DIR__ . '/conexion.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['id_usuario'])) {
    header('Location: login.php?error=debe_loguearse');
    exit;
}

$id_tarjeta = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$id_tarjeta) {
    header('Location: dashboard.php');
    exit;
}

// Verificar que la tarjeta pertenezca al usuario
try {
    $stmtCheck = $pdo->prepare("SELECT id_tarjeta, nombres_portada FROM tarjetas WHERE id_tarjeta = ? AND id_cliente = ?");
    $stmtCheck->execute([$id_tarjeta, $_SESSION['id_usuario']]);
    $tarjeta_info = $stmtCheck->fetch(PDO::FETCH_ASSOC);
    if (!$tarjeta_info) {
        header('Location: dashboard.php?error=no_autorizado');
        exit;
    }
} catch (PDOException $e) {
    error_log('Error en configurar_pagos: ' . $e->getMessage());
    header('Location: dashboard.php?error=error_carga');
    exit;
}

$mensaje_exito = null;
$mensaje_error = null;

// Guardar cambios
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tarifas_array = [];
    if (isset($_POST['concepto_nombre']) && is_array($_POST['concepto_nombre'])) {
        foreach ($_POST['concepto_nombre'] as $index => $nombre) {
            $nombre_limpio = trim($nombre);
            $monto_limpio  = isset($_POST['concepto_monto'][$index]) ? (float)$_POST['concepto_monto'][$index] : 0;
            if ($nombre_limpio !== '' && $monto_limpio > 0) {
                $tarifas_array[] = ['nombre' => $nombre_limpio, 'monto' => $monto_limpio];
            }
        }
    }
    $tarifas_json = json_encode($tarifas_array, JSON_UNESCAPED_UNICODE);

    try {
        $stmt = $pdo->prepare("REPLACE INTO config_pagos (tarjeta_id, mensaje, tarifas, datos_bancarios, link_mp) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([
            $id_tarjeta,
            trim($_POST['mensaje'] ?? ''),
            $tarifas_json,
            trim($_POST['datos_bancarios'] ?? ''),
            trim($_POST['link_mp'] ?? ''),
        ]);
        $mensaje_exito = 'Configuración guardada correctamente.';
    } catch (PDOException $e) {
        error_log('Error al guardar config_pagos: ' . $e->getMessage());
        $mensaje_error = 'Ocurrió un error al guardar. Intentá de nuevo.';
    }
}

// Obtener datos actuales
try {
    $stmt = $pdo->prepare("SELECT * FROM config_pagos WHERE tarjeta_id = ?");
    $stmt->execute([$id_tarjeta]);
    $conf = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $conf = [];
}

$tarifas_existentes = [];
if (!empty($conf['tarifas'])) {
    $tarifas_existentes = json_decode($conf['tarifas'], true);
    if (!is_array($tarifas_existentes)) {
        $tarifas_existentes = [];
        $partes = explode('|', $conf['tarifas']);
        foreach ($partes as $parte) {
            $detalle = explode(':', $parte);
            if (count($detalle) === 2) {
                $tarifas_existentes[] = ['nombre' => trim($detalle[0]), 'monto' => (float)trim($detalle[1])];
            }
        }
    }
}
if (empty($tarifas_existentes)) {
    $tarifas_existentes = [['nombre' => '', 'monto' => '']];
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configurar Pagos — Momentia</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Ms+Madi&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/global.css">
    <link rel="stylesheet" href="css/formularios.css">
    <style>
        /* ── Chip de contexto ── */
        .config-contexto {
            display: flex; align-items: center; gap: 10px;
            background: #f8fffe; border: 1px solid #c8e6d4;
            border-radius: 10px; padding: 12px 16px; margin-bottom: 28px;
            font-size: 0.88rem; color: #344e67;
        }
        .config-contexto strong { color: #1a6b3c; }
        .config-contexto span   { color: #7a6e67; font-size: 0.82rem; display: block; margin-top: 1px; }

        /* ── Alertas ── */
        .alerta {
            padding: 12px 16px; border-radius: 10px;
            font-size: 0.9rem; font-weight: 500; margin-bottom: 20px;
            border-left-width: 4px; border-left-style: solid;
        }
        .alerta-exito { background: #f0faf4; color: #1a6b3c; border: 1px solid #c3e6cb; border-left-color: #27ae60; }
        .alerta-error { background: #fdf2f2; color: #922b21; border: 1px solid #f5c6c6; border-left-color: #e74c3c; }

        /* ── Filas de tarifas ── */
        .fila-tarifa {
            display: grid;
            grid-template-columns: 1fr 120px 40px;
            gap: 10px; align-items: center;
            margin-bottom: 10px;
        }
        .fila-tarifa input {
            padding: 10px 12px;
            border: 1.5px solid #ddd; border-radius: 8px;
            font-size: 0.95rem; font-family: inherit;
            transition: border-color 0.2s;
            background: #fdfcfb;
        }
        .fila-tarifa input:focus {
            outline: none; border-color: var(--color-principal);
            box-shadow: 0 0 0 3px rgba(166,139,109,0.1);
        }
        .fila-tarifa input[type="number"] { text-align: right; }

        .btn-eliminar-fila {
            width: 36px; height: 36px;
            background: #fff5f5; color: #e74c3c;
            border: 1.5px solid #fac5c5; border-radius: 8px;
            cursor: pointer; font-size: 1rem;
            display: flex; align-items: center; justify-content: center;
            transition: all 0.2s; flex-shrink: 0;
        }
        .btn-eliminar-fila:hover { background: #e74c3c; color: white; border-color: #e74c3c; }

        .tarifas-header {
            display: grid; grid-template-columns: 1fr 120px 40px;
            gap: 10px; margin-bottom: 8px;
        }
        .tarifas-header span {
            font-size: 0.72rem; font-weight: 600; color: #94a3b8;
            text-transform: uppercase; letter-spacing: 1px; padding: 0 4px;
        }

        .btn-agregar-fila {
            display: inline-flex; align-items: center; gap: 6px;
            padding: 9px 16px;
            background: #f0faf4; color: #27ae60;
            border: 1.5px solid #c3e6cb; border-radius: 8px;
            font-size: 0.85rem; font-weight: 600; cursor: pointer;
            transition: all 0.2s; font-family: inherit; margin-top: 4px;
        }
        .btn-agregar-fila:hover { background: #27ae60; color: white; border-color: #27ae60; }

        /* ── Inputs de texto mejorados ── */
        input[type="text"], textarea {
            width: 100%; box-sizing: border-box;
            padding: 12px 14px;
            border: 1.5px solid #ddd; border-radius: 10px;
            font-size: 1rem; font-family: inherit;
            transition: border-color 0.25s, box-shadow 0.25s;
            background: #fdfcfb;
        }
        input[type="text"]:focus, textarea:focus {
            outline: none; border-color: var(--color-principal);
            box-shadow: 0 0 0 3px rgba(166,139,109,0.12);
            background: #fff;
        }

        /* ── Botones footer ── */
        .config-footer {
            display: flex; gap: 12px; margin-top: 28px;
        }
        .btn-volver {
            flex: 1; text-decoration: none;
            background: #f1f5f9; color: #475569;
            padding: 13px; border-radius: 10px;
            font-weight: 600; text-align: center;
            font-size: 0.92rem; transition: background 0.2s;
        }
        .btn-volver:hover { background: #e2e8f0; }
        .btn-submit { flex: 2; margin: 0; border-radius: 10px; font-size: 0.92rem; }

        /* ── Hint de Mercado Pago ── */
        .mp-hint {
            font-size: 0.8rem; color: #94a3b8; margin-top: 5px; display: block;
        }
    </style>
</head>
<body>
<?php include_once 'header.php'; ?>

<main class="form-container">
    <div class="form-card">

        <!-- Chip de contexto -->
        <div class="config-contexto">
            <span style="font-size:1.4rem;">💳</span>
            <div>
                <strong>Configurando pagos: <?php echo htmlspecialchars($tarjeta_info['nombres_portada'] ?: 'Sin título'); ?></strong>
                <span>Definí las tarifas, el alias/CBU y el mensaje para tus invitados.</span>
            </div>
        </div>

        <?php if ($mensaje_exito): ?>
            <div class="alerta alerta-exito" role="status"><?php echo htmlspecialchars($mensaje_exito); ?></div>
        <?php endif; ?>
        <?php if ($mensaje_error): ?>
            <div class="alerta alerta-error" role="alert"><?php echo htmlspecialchars($mensaje_error); ?></div>
        <?php endif; ?>

        <form method="POST" id="formConfigPagos">

            <!-- Mensaje para invitados -->
            <fieldset>
                <legend>Mensaje para invitados</legend>
                <div class="form-group">
                    <label for="mensaje">Texto introductorio (opcional)</label>
                    <textarea name="mensaje" id="mensaje" rows="3"
                        placeholder="Ej: La tarjeta la podés pagar transfiriendo..."
                    ><?php echo htmlspecialchars($conf['mensaje'] ?? ''); ?></textarea>
                </div>
            </fieldset>

            <!-- Tarifas -->
            <fieldset>
                <legend>Tarifas / Opciones de Inscripción</legend>
                <p style="font-size:0.88rem;color:#7a6e67;margin-bottom:16px;">
                    Ingresá cada categoría con su valor. Podés agregar tantas filas como necesites.
                </p>

                <div class="tarifas-header">
                    <span>Categoría</span>
                    <span style="text-align:right;">Precio ($)</span>
                    <span></span>
                </div>

                <div id="contenedor-tarifas">
                    <?php foreach ($tarifas_existentes as $t): ?>
                        <div class="fila-tarifa">
                            <input type="text"   name="concepto_nombre[]" value="<?php echo htmlspecialchars($t['nombre']); ?>" placeholder="Ej: Adultos">
                            <input type="number" name="concepto_monto[]"  value="<?php echo htmlspecialchars($t['monto'] ?: ''); ?>" placeholder="5000" step="1" min="0">
                            <button type="button" class="btn-eliminar-fila" onclick="eliminarFila(this)" title="Eliminar fila">✕</button>
                        </div>
                    <?php endforeach; ?>
                </div>

                <button type="button" class="btn-agregar-fila" onclick="agregarFila()">
                    + Agregar categoría
                </button>
            </fieldset>

            <!-- Datos bancarios -->
            <fieldset>
                <legend>Datos para Transferencia</legend>
                <div class="form-group">
                    <label for="datos_bancarios">Alias o CBU</label>
                    <textarea name="datos_bancarios" id="datos_bancarios" rows="2"
                        placeholder="Ej: ALIAS.MI.CUENTA"
                    ><?php echo htmlspecialchars($conf['datos_bancarios'] ?? ''); ?></textarea>
                </div>
            </fieldset>

            <!-- Mercado Pago -->
            <fieldset>
                <legend>Mercado Pago (opcional)</legend>
                <div class="form-group">
                    <label for="link_mp">Link de cobro de Mercado Pago</label>
                    <input type="text" name="link_mp" id="link_mp"
                        value="<?php echo htmlspecialchars($conf['link_mp'] ?? ''); ?>"
                        placeholder="https://mpago.la/...">
                    <span class="mp-hint">Si lo dejás vacío, en la tarjeta aparecerá "Próximamente".</span>
                </div>
            </fieldset>

            <div class="config-footer">
                <a href="dashboard.php" class="btn-volver">← Volver al Panel</a>
                <button type="submit" class="btn-submit">Guardar configuración ✓</button>
            </div>

        </form>
    </div>
</main>

<?php include_once 'footer.php'; ?>

<script>
function agregarFila() {
    const contenedor = document.getElementById('contenedor-tarifas');
    const div = document.createElement('div');
    div.className = 'fila-tarifa';
    div.innerHTML = `
        <input type="text"   name="concepto_nombre[]" placeholder="Ej: Menores">
        <input type="number" name="concepto_monto[]"  placeholder="2000" step="1" min="0">
        <button type="button" class="btn-eliminar-fila" onclick="eliminarFila(this)" title="Eliminar fila">✕</button>
    `;
    contenedor.appendChild(div);
    div.querySelector('input').focus();
}

function eliminarFila(btn) {
    const contenedor = document.getElementById('contenedor-tarifas');
    const filas = contenedor.getElementsByClassName('fila-tarifa');
    if (filas.length > 1) {
        btn.closest('.fila-tarifa').remove();
    } else {
        // Si es la última, solo limpia los valores
        const inputs = btn.closest('.fila-tarifa').querySelectorAll('input');
        inputs.forEach(i => i.value = '');
    }
}
</script>
</body>
</html>