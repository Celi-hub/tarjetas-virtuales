<?php
require_once __DIR__ . '/conexion.php';
require_once __DIR__ . '/config/modulos.php';
require_once __DIR__ . '/config/textos_evento.php';
require_once __DIR__ . '/includes/campos_formulario.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['id_usuario'])) {
    $_SESSION['redirect_tras_login'] = $_SERVER['REQUEST_URI'];
    header('Location: login.php?error=debe_loguearse');
    exit;
}

$id_tarjeta = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id_tarjeta) {
    header('Location: dashboard.php?error=tarjeta_invalida');
    exit;
}

try {
    $stmt = $pdo->prepare('
        SELECT t.*, te.nombre AS nombre_evento
        FROM tarjetas t
        JOIN tipos_evento te ON t.tipo_evento_id = te.id_tipos_evento
        WHERE t.id_tarjeta = ? AND t.id_cliente = ?
    ');
    $stmt->execute([$id_tarjeta, $_SESSION['id_usuario']]);
    $tarjeta = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$tarjeta) {
        header('Location: dashboard.php?error=no_autorizado');
        exit;
    }

    $nombre_evento_slug  = mb_strtolower($tarjeta['nombre_evento']);
    $nombre_evento_human = ucfirst($tarjeta['nombre_evento']);

    $textos_defecto = textos_para_evento($nombre_evento_slug);
    $textos_personalizados = !empty($tarjeta['textos_personalizados'])
        ? (json_decode($tarjeta['textos_personalizados'], true) ?: [])
        : [];

    $estilos_visuales = $pdo->query('SELECT id_estilos_visuales, nombre FROM estilos_visuales ORDER BY nombre')
        ->fetchAll(PDO::FETCH_ASSOC);

    $modulos_permitidos = modulos_para_evento($nombre_evento_slug);
    $secciones_modulos  = secciones_modulos_editables($modulos_permitidos, $tarjeta);
    $usa_rsvp_interno   = tarjeta_tiene_rsvp_interno($tarjeta);
    $usa_rsvp_whatsapp  = tarjeta_muestra_rsvp_whatsapp($tarjeta);

    $frases_sugeridas = obtener_frases_sugeridas($pdo, $tarjeta['tipo_evento_id']);
    $frase_guardada   = $tarjeta['frase_portada'] ?? '';
    $es_custom        = frase_es_custom($frase_guardada, $frases_sugeridas);

    // CSRF token para este formulario
    if (empty($_SESSION['csrf_token_editar_tarjeta'])) {
        $_SESSION['csrf_token_editar_tarjeta'] = bin2hex(random_bytes(32));
    }
    $csrf_token = $_SESSION['csrf_token_editar_tarjeta'];

} catch (PDOException $e) {
    error_log('Error en editar_tarjeta: ' . $e->getMessage());
    header('Location: dashboard.php?error=error_carga');
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Invitación — Momentia</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Ms+Madi&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/global.css">
    <link rel="stylesheet" href="css/formularios.css">
</head>
<body>
<?php include_once 'header.php'; ?>

<main class="form-container">
    <div class="form-card">

        <?php if (($_GET['error'] ?? '') === 'error_guardado'): ?>
            <div role="alert" class="alert-error">
                No se pudieron guardar los cambios. Revisá los datos e intentá nuevamente.
            </div>
        <?php endif; ?>

        <div class="editar-contexto">
            <span class="editar-contexto-icono">✏️</span>
            <div>
                <strong>Editando: <?php echo htmlspecialchars($tarjeta['nombres_portada'] ?: 'Sin título'); ?></strong>
                <span>Evento: <?php echo htmlspecialchars($nombre_evento_human); ?> · Podés cambiar el estilo, los módulos y los datos.</span>
            </div>
        </div>

        <form action="actualizar_tarjeta_completa.php" method="POST" enctype="multipart/form-data" novalidate>
            <input type="hidden" name="id_tarjeta" value="<?php echo htmlspecialchars((string)$id_tarjeta); ?>">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">

            <!-- ── ESTILO VISUAL ── -->
            <fieldset>
                <legend>Estilo de la Tarjeta</legend>
                <div class="form-group">
                    <label for="estilo_visual">¿Cómo querés que se vea?</label>
                    <select name="estilo_visual_id" id="estilo_visual" required>
                        <?php foreach ($estilos_visuales as $estilo): ?>
                            <option
                                value="<?php echo htmlspecialchars($estilo['id_estilos_visuales']); ?>"
                                <?php echo ($estilo['id_estilos_visuales'] == $tarjeta['estilo_visual_id']) ? 'selected' : ''; ?>
                            >
                                <?php echo htmlspecialchars(ucfirst($estilo['nombre'])); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </fieldset>

            <!-- ── MÓDULOS INCLUIDOS ── -->
            <fieldset>
                <legend>Incluido en todas las invitaciones</legend>
                <p class="form-help-text">Estos módulos vienen activos sin costo adicional.</p>
                <div class="modules-grid">
                    <?php foreach (modulos_incluidos_base() as $columna => $modulo):
                        if (empty($modulo['visible_paso1'])) continue;
                    ?>
                        <label class="module-option module-incluido">
                            <input type="checkbox" checked disabled aria-readonly="true">
                            <div class="module-text">
                                <strong><?php echo htmlspecialchars($modulo['titulo']); ?></strong>
                                <small><?php echo htmlspecialchars($modulo['descripcion']); ?></small>
                                <span class="badge-incluido">✔ Incluido</span>
                            </div>
                        </label>
                    <?php endforeach; ?>
                </div>
            </fieldset>

            <!-- ── MÓDULOS OPCIONALES ── -->
            <fieldset>
                <legend>Módulos adicionales (a la carta)</legend>
                <p class="form-help-text">Tildá los que querés activar. Los campos aparecen debajo de cada uno.</p>
                <div class="modules-grid">
                    <?php foreach ($secciones_modulos as $seccion): ?>
                        <label
                            class="module-option <?php echo $seccion['activo'] ? 'seleccionado' : ''; ?>"
                            id="label_<?php echo htmlspecialchars($seccion['columna']); ?>"
                        >
                            <input
                                type="checkbox"
                                name="modulos[]"
                                value="<?php echo htmlspecialchars($seccion['columna']); ?>"
                                class="checkbox-modulo"
                                data-modulo="<?php echo htmlspecialchars($seccion['columna']); ?>"
                                <?php echo $seccion['activo'] ? 'checked' : ''; ?>
                            >
                            <div class="module-text">
                                <strong><?php echo htmlspecialchars($seccion['titulo']); ?></strong>
                                <small><?php echo htmlspecialchars($seccion['descripcion']); ?></small>
                            </div>
                        </label>
                    <?php endforeach; ?>
                </div>

                <div id="contenedor-campos-modulos">
                    <?php foreach ($secciones_modulos as $seccion):
                        if (empty($seccion['campos'])) continue;
                    ?>
                        <div
                            class="fieldset-modulo <?php echo $seccion['activo'] ? '' : 'oculto'; ?>"
                            id="fieldset_<?php echo htmlspecialchars($seccion['columna']); ?>"
                        >
                            <legend class="fieldset-modulo-titulo"><?php echo htmlspecialchars($seccion['titulo']); ?></legend>
                            <?php foreach ($seccion['campos'] as $campo): ?>
                                <?php render_campo_formulario($campo, $tarjeta[$campo['name']] ?? null); ?>
                            <?php endforeach; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </fieldset>

            <!-- ── FRASE DE PORTADA ── -->
            <?php if (!empty($frases_sugeridas)): ?>
            <fieldset>
                <legend>Frase de Portada</legend>
                <p class="form-help-text">Es lo primero que verán tus invitados al abrir la tarjeta.</p>
                <input type="hidden" name="frase_portada" id="frase_portada_hidden" value="<?php echo htmlspecialchars($frase_guardada); ?>">
                <div class="frase-selector">
                    <?php foreach ($frases_sugeridas as $frase):
                        $checked = ($frase_guardada === $frase['texto_frase']); ?>
                        <label class="frase-opcion">
                            <input type="radio" name="_frase_opcion" value="<?php echo htmlspecialchars($frase['texto_frase']); ?>" class="radio-frase" <?php echo $checked ? 'checked' : ''; ?>>
                            <span class="frase-opcion-label"><?php echo htmlspecialchars($frase['texto_frase']); ?></span>
                        </label>
                    <?php endforeach; ?>
                    <div class="frase-custom-wrap">
                        <label class="frase-custom-toggle <?php echo $es_custom ? 'activo' : ''; ?>" id="toggle-custom">
                            <input type="radio" name="_frase_opcion" value="__custom__" class="radio-frase" id="radio-custom" <?php echo $es_custom ? 'checked' : ''; ?>>
                            ✏️ Escribir mi propia frase
                        </label>
                        <div id="frase-custom-input" class="<?php echo $es_custom ? 'visible' : ''; ?>">
                            <textarea id="frase_custom_texto" name="frase_custom_texto" placeholder="Escribí la frase que querés mostrar..." maxlength="200"><?php echo $es_custom ? htmlspecialchars($frase_guardada) : ''; ?></textarea>
                        </div>
                    </div>
                </div>
                <div class="frase-preview <?php echo $frase_guardada !== '' ? 'visible' : ''; ?>" id="frase-preview">
                    <span class="frase-preview-label">Vista previa</span>
                    <p class="frase-preview-texto" id="frase-preview-texto"><?php echo htmlspecialchars($frase_guardada); ?></p>
                </div>
            </fieldset>
            <?php endif; ?>

            <!-- ── DATOS DEL EVENTO ── -->
            <fieldset>
                <legend>Datos del Evento</legend>
                <?php foreach (campos_base_config() as $campo): ?>
                    <?php render_campo_formulario($campo, $tarjeta[$campo['name']] ?? null); ?>
                <?php endforeach; ?>
            </fieldset>

            <!-- ── CONFIRMACIÓN DE ASISTENCIA ── -->
            <fieldset>
                <legend>Confirmación de Asistencia</legend>

                <div id="rsvp-interno-bloque" class="<?php echo $usa_rsvp_interno ? '' : 'oculto'; ?>">
                    <p class="form-success-text">✔ Módulo RSVP activado: tus invitados confirmarán dentro de la tarjeta.</p>
                </div>

                <div id="rsvp-whatsapp-bloque" class="<?php echo $usa_rsvp_interno ? 'oculto' : ''; ?>">
                    <p class="form-help-text">Los invitados confirmarán por WhatsApp. Ingresá el número que van a usar.</p>
                    <?php render_campo_formulario([
                        'name'        => 'telefono_whatsapp',
                        'label'       => 'Número de WhatsApp *',
                        'type'        => 'tel',
                        'required'    => true,
                        'placeholder' => '+54 9 351 000 0000',
                    ], $tarjeta['telefono_whatsapp'] ?? null); ?>
                </div>

                <?php render_campo_formulario([
                    'name'     => 'fecha_limite_rsvp',
                    'label'    => 'Fecha límite para confirmar (opcional)',
                    'type'     => 'date',
                    'required' => false,
                ], $tarjeta['fecha_limite_rsvp'] ?? null); ?>
            </fieldset>

            <!-- ── PERSONALIZAR TEXTOS Y TÍTULOS ── -->
            <fieldset>
                <legend>Personalizar Textos y Títulos (Opcional)</legend>
                <p class="form-help-text">
                    Podés cambiar los títulos y frases por defecto de tu invitación para darle un tono de voz único
                    (ej: tutear o hablar de usted, o usar frases divertidas). Si los dejás vacíos, se usarán los textos por defecto del evento.
                </p>

                <?php
                $campos_texto_personalizable = [
                    'titulo_rsvp'        => 'Título del módulo de Asistencia (RSVP)',
                    'subtitulo_rsvp'     => 'Subtítulo del módulo de Asistencia (RSVP)',
                    'muro_titulo'        => 'Título del Muro de Mensajes / Deseos',
                    'muro_pregunta'      => 'Pregunta o invitación del Muro',
                    'titulo_regalos'     => 'Título de la sección de Regalos / Regalo en efectivo',
                    'titulo_playlist'    => 'Título de la sección de Playlist / Música',
                    'subtitulo_playlist' => 'Subtítulo de la sección de Playlist / Música',
                ];
                foreach ($campos_texto_personalizable as $clave => $label):
                    render_campo_formulario([
                        'name'        => "custom_txt[$clave]",
                        'label'       => $label,
                        'type'        => 'text',
                        'placeholder' => $textos_defecto[$clave] ?? '',
                    ], $textos_personalizados[$clave] ?? '');
                endforeach;

                render_campo_formulario([
                    'name'        => 'custom_txt[msg_regalos_defecto]',
                    'label'       => 'Mensaje de Regalos',
                    'type'        => 'textarea',
                    'rows'        => 2,
                    'placeholder' => $textos_defecto['msg_regalos_defecto'] ?? '',
                ], $textos_personalizados['msg_regalos_defecto'] ?? '');
                ?>
            </fieldset>

            <!-- ── BOTONES ── -->
            <div class="editar-footer-btns">
                <a href="dashboard.php" class="btn-cancelar">Cancelar</a>
                <button type="submit" class="btn-submit">Guardar cambios ✓</button>
            </div>
        </form>
    </div>
</main>

<?php include_once 'footer.php'; ?>

<script src="js/tarjeta-formulario.js"></script>
<script src="js/editar_tarjeta.js"></script>
</body>
</html>