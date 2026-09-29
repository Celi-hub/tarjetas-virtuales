<?php
require_once __DIR__ . '/conexion.php';
require_once __DIR__ . '/config/modulos.php';
require_once __DIR__ . '/includes/campos_formulario.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['id_usuario'])) {
    $_SESSION['redirect_tras_login'] = $_SERVER['REQUEST_URI'];
    header('Location: login.php?error=debe_loguearse');
    exit;
}

// 1. Filtrado seguro de parámetros de entrada
$id_tarjeta = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id_tarjeta) {
    header('Location: dashboard.php?error=tarjeta_invalida');
    exit;
}

try {
    // 2. Extracción de datos de la tarjeta
    $stmt = $pdo->prepare('
        SELECT t.*, te.id_tipos_evento, te.nombre AS nombre_evento
        FROM tarjetas t
        JOIN tipos_evento te ON t.tipo_evento_id = te.id_tipos_evento
        WHERE t.id_tarjeta = ? AND t.id_cliente = ?
    ');
    $stmt->execute([$id_tarjeta, $_SESSION['id_usuario']]);
    $tarjeta = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$tarjeta) {
        header('Location: dashboard.php?error=tarjeta_no_encontrada');
        exit;
    }

    // 3. Extracción de frases sugeridas
    $stmtFrases = $pdo->prepare('
        SELECT id_frase, texto_frase 
        FROM frases_sugeridas 
        WHERE tipo_evento_id = ? 
        ORDER BY id_frase 
        LIMIT 3
    ');
    $stmtFrases->execute([$tarjeta['id_tipos_evento']]);
    $frases_sugeridas = $stmtFrases->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    error_log('Error en completar_datos: ' . $e->getMessage());
    header('Location: dashboard.php?error=error_carga');
    exit;
}

// 4. Preparación de variables de entorno y estado (Lógica de Negocio)
$secciones_modulos = campos_formulario_por_tarjeta($tarjeta);
$usa_rsvp_interno  = tarjeta_tiene_rsvp_interno($tarjeta);
$usa_rsvp_whatsapp = tarjeta_muestra_rsvp_whatsapp($tarjeta);

// Determinación del estado de la frase (Evita lógica compleja en la vista)
$frase_guardada = $tarjeta['frase_portada'] ?? '';
$frases_textos  = array_column($frases_sugeridas, 'texto_frase');
$es_custom      = ($frase_guardada !== '' && !in_array($frase_guardada, $frases_textos, true));

// Módulos activos para el resumen
$modulos_activos_nombres = [];
foreach (modulos_config() as $col => $mod) {
    if (!empty($tarjeta[$col]) && empty($mod['incluido_base'])) {
        $modulos_activos_nombres[] = $mod['titulo'];
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Paso 2: Completá los datos — Momentia</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Ms+Madi&family=Cormorant+Garamond:ital,wght@1,300;1,400&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/global.css">
    <link rel="stylesheet" href="css/formularios.css">
</head>
<body>
<?php include_once 'header.php'; ?>

<main class="form-container">
    <div class="form-card">

        <!-- BARRA DE PROGRESO -->
        <div class="wizard-progress" role="navigation" aria-label="Pasos del formulario">
            <div class="wizard-step completado">
                <div class="wizard-step-circle" aria-hidden="true">✓</div>
                <span class="wizard-step-label">Configurar</span>
            </div>
            <div class="wizard-connector completado"></div>
            <div class="wizard-step activo" aria-current="step">
                <div class="wizard-step-circle" aria-hidden="true">2</div>
                <span class="wizard-step-label">Cargar datos</span>
            </div>
            <div class="wizard-connector"></div>
            <div class="wizard-step">
                <div class="wizard-step-circle" aria-hidden="true">✓</div>
                <span class="wizard-step-label">¡Lista!</span>
            </div>
        </div>

        <h1>Completá los datos</h1>
        <p class="subtitle">Ingresá la información de tu evento. Los campos marcados con * son obligatorios.</p>

        <?php if (!empty($modulos_activos_nombres)): ?>
            <div class="modulos-activos-resumen">
                <strong>Módulos que activaste en el Paso 1:</strong>
                <ul>
                    <?php foreach ($modulos_activos_nombres as $nombre): ?>
                        <li><?php echo htmlspecialchars($nombre); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form action="guardar_tarjeta.php" method="POST" enctype="multipart/form-data" id="formPaso2">
            <input type="hidden" name="id_tarjeta" value="<?php echo htmlspecialchars((string)$id_tarjeta); ?>">
            <input type="hidden" name="frase_portada" id="frase_portada_hidden" value="<?php echo htmlspecialchars($frase_guardada); ?>">

            <!-- ── DATOS DEL EVENTO ── -->
            <fieldset>
                <legend>Datos del Evento</legend>
                <?php foreach (campos_base_config() as $campo): ?>
                    <?php render_campo_formulario($campo, $tarjeta[$campo['name']] ?? null); ?>
                <?php endforeach; ?>
            </fieldset>

            <!-- ── FRASE DE PORTADA ── -->
            <?php if (!empty($frases_sugeridas)): ?>
            <fieldset>
                <legend>Frase de Portada</legend>
                <p class="form-help-text">
                    Elegí la frase que abrirá tu invitación, antes de los nombres. Es lo primero que verán tus invitados.
                </p>

                <div class="frase-selector">
                    <?php foreach ($frases_sugeridas as $frase): 
                        $is_checked = ($frase_guardada === $frase['texto_frase']);
                    ?>
                        <label class="frase-opcion">
                            <input
                                type="radio"
                                name="_frase_opcion"
                                value="<?php echo htmlspecialchars($frase['texto_frase']); ?>"
                                class="radio-frase"
                                <?php echo $is_checked ? 'checked' : ''; ?>
                            >
                            <span class="frase-opcion-label">
                                <?php echo htmlspecialchars($frase['texto_frase']); ?>
                            </span>
                        </label>
                    <?php endforeach; ?>

                    <!-- Opción: escribir propia -->
                    <div class="frase-custom-wrap">
                        <label class="frase-custom-toggle <?php echo $es_custom ? 'activo' : ''; ?>" id="toggle-custom">
                            <input
                                type="radio"
                                name="_frase_opcion"
                                value="__custom__"
                                class="radio-frase"
                                id="radio-custom"
                                <?php echo $es_custom ? 'checked' : ''; ?>
                            >
                            ✏️ Escribir mi propia frase
                        </label>
                        <div id="frase-custom-input" class="<?php echo $es_custom ? 'visible' : ''; ?>">
                            <textarea
                                id="frase_custom_texto"
                                placeholder="Escribí la frase que querés mostrar en tu invitación..."
                                maxlength="200"
                            ><?php echo $es_custom ? htmlspecialchars($frase_guardada) : ''; ?></textarea>
                        </div>
                    </div>
                </div>

                <!-- Preview -->
                <div class="frase-preview <?php echo $frase_guardada !== '' ? 'visible' : ''; ?>" id="frase-preview">
                    <span class="frase-preview-label">Vista previa</span>
                    <p class="frase-preview-texto" id="frase-preview-texto">
                        <?php echo htmlspecialchars($frase_guardada); ?>
                    </p>
                </div>
            </fieldset>
            <?php endif; ?>

            <!-- ── CONFIRMACIÓN DE ASISTENCIA ── -->
<fieldset>
    <legend>Confirmación de Asistencia</legend>
    <?php if ($usa_rsvp_interno): ?>
        <p class="form-success-text">
            ✔ Módulo RSVP activado: tus invitados confirmarán directamente en la tarjeta.
        </p>
    <?php elseif ($usa_rsvp_whatsapp): ?>
        <p class="form-help-text">
            Los invitados confirmarán por WhatsApp. Ingresá el número que van a usar.
        </p>
        <?php render_campo_formulario([
            'name'        => 'telefono_whatsapp',
            'label'       => 'Número de WhatsApp *',
            'type'        => 'tel',
            'required'    => true,
            'placeholder' => '+54 9 351 000 0000',
        ], $tarjeta['telefono_whatsapp'] ?? null); ?>
    <?php endif; ?>

    <?php render_campo_formulario([
        'name'     => 'fecha_limite_rsvp',
        'label'    => 'Fecha límite para confirmar (opcional)',
        'type'     => 'date',
        'required' => false,
    ], $tarjeta['fecha_limite_rsvp'] ?? null); ?>
</fieldset>

            <!-- ── MÓDULOS OPCIONALES ── -->
            <?php foreach ($secciones_modulos as $seccion):
                if (in_array($seccion['modulo'], ['mod_rsvp_whatsapp', 'mod_rsvp_interno'], true)) continue;
            ?>
                <fieldset>
                    <legend><?php echo htmlspecialchars($seccion['legend']); ?></legend>
                    <?php foreach ($seccion['campos'] as $campo): ?>
                        <?php render_campo_formulario($campo, $tarjeta[$campo['name']] ?? null); ?>
                    <?php endforeach; ?>
                </fieldset>
            <?php endforeach; ?>

            <div class="btn-submit-wrapper">
                <button type="submit" class="btn-submit btn-centered">
                    Guardar y ver mi invitación ✓
                </button>
                <br>
                <a href="personalizar.php" class="btn-volver">← Volver al Paso 1</a>
            </div>
        </form>
    </div>
</main>

<?php include_once 'footer.php'; ?>

<script src="js/tarjeta-formulario.js"></script>
</body>
</html>