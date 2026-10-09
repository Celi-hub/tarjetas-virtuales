<?php
require_once __DIR__ . '/conexion.php';
require_once __DIR__ . '/config/modulos.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['id_usuario'])) {
    $_SESSION['redirect_tras_login'] = 'dashboard.php';
    header("Location: login.php?error=debe_loguearse");
    exit;
}

$id_usuario     = $_SESSION['id_usuario'];
$nombre_usuario = $_SESSION['nombre_usuario'] ?? $_SESSION['nombre'] ?? 'Usuario';

try {
    $sql = "SELECT t.*, t.id_tarjeta, t.nombres_portada, t.fecha_evento, e.nombre AS evento_nombre
            FROM tarjetas t
            INNER JOIN tipos_evento e ON t.tipo_evento_id = e.id_tipos_evento
            WHERE t.id_cliente = ?
            ORDER BY t.id_tarjeta DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id_usuario]);
    $mis_tarjetas = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log('Error en dashboard.php: ' . $e->getMessage());
    $mis_tarjetas = [];
    $error_carga  = true;
}

// Token para el formulario de eliminación (lo valida eliminar_tarjeta.php)
if (empty($_SESSION['csrf_token_eliminar_tarjeta'])) {
    $_SESSION['csrf_token_eliminar_tarjeta'] = bin2hex(random_bytes(32));
}
$csrf_eliminar = $_SESSION['csrf_token_eliminar_tarjeta'];

// Qué se pierde al eliminar cada tarjeta: el modal lo muestra como advertencia.
// Consulta aparte para que, si falla, el panel siga cargando (el modal cae a un aviso genérico).
$datos_por_tarjeta = [];
if (!empty($mis_tarjetas)) {
    try {
        $stmtDatos = $pdo->prepare("
            SELECT t.id_tarjeta,
                   (SELECT COUNT(*) FROM rsvp_respuestas      WHERE tarjeta_id = t.id_tarjeta) AS rsvp,
                   (SELECT COUNT(*) FROM muro_deseos          WHERE tarjeta_id = t.id_tarjeta) AS muro,
                   (SELECT COUNT(*) FROM playlist_sugerencias WHERE tarjeta_id = t.id_tarjeta) AS playlist,
                   (SELECT COUNT(*) FROM pagos_evento         WHERE tarjeta_id = t.id_tarjeta) AS pagos,
                   (SELECT COUNT(*) FROM linea_tiempo_hitos   WHERE tarjeta_id = t.id_tarjeta) AS hitos
            FROM tarjetas t
            WHERE t.id_cliente = ?
        ");
        $stmtDatos->execute([$id_usuario]);
        foreach ($stmtDatos->fetchAll(PDO::FETCH_ASSOC) as $fila) {
            $datos_por_tarjeta[(int)$fila['id_tarjeta']] = $fila;
        }
    } catch (PDOException $e) {
        error_log('Error al contar datos de tarjetas en dashboard.php: ' . $e->getMessage());
    }
}

// Mapeo de íconos SVG inline (accesibles, escalables, sin dependencias)
function icono_svg(string $nombre, string $clase = ''): string {
    $iconos = [
        'ver'      => '<svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>',
        'editar'   => '<svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>',
        'eliminar' => '<svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/></svg>',
        'pagar'    => '<svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="1" y="4" width="22" height="16" rx="2" ry="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>',
        'compartir'=> '<svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><line x1="8.59" y1="13.51" x2="15.42" y2="17.49"/><line x1="15.41" y1="6.51" x2="8.59" y2="10.49"/></svg>',
        'rsvp'     => '<svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>',
        'muro'     => '<svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>',
        'hitos'    => '<svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="12" y1="20" x2="12" y2="10"/><line x1="18" y1="20" x2="18" y2="4"/><line x1="6" y1="20" x2="6" y2="16"/></svg>',
        'config'   => '<svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83-2.83l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 2.83-2.83l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>',
        'nuevo'    => '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>',
    ];
    $attrs = $clase ? ' class="' . htmlspecialchars($clase) . '"' : '';
    return $iconos[$nombre] ?? '';
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi Panel — Momentia</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Ms+Madi&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/global.css">
    <link rel="stylesheet" href="css/dashboard.css">
</head>
<body>

<?php include_once 'header.php'; ?>

<!-- Modal de confirmación de eliminación (reemplaza el confirm() del browser) -->
<div class="modal-overlay" id="modalEliminar" role="dialog" aria-modal="true" aria-labelledby="modalTitulo" aria-describedby="modalDescripcion">
    <form class="modal-box" method="POST" action="eliminar_tarjeta.php" id="modalEliminarForm">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_eliminar); ?>">
        <input type="hidden" name="id_tarjeta" id="modalIdTarjeta" value="">

        <div class="modal-icon">
            <?php echo icono_svg('eliminar'); ?>
        </div>
        <h3 id="modalTitulo">¿Eliminás esta invitación?</h3>
        <p id="modalDescripcion">Esta acción no se puede deshacer.</p>

        <div class="modal-riesgos">
            <p class="modal-riesgos-titulo">Se va a borrar para siempre:</p>
            <ul id="modalRiesgos"></ul>
            <p class="modal-riesgo-destacado" id="modalRiesgoActiva" hidden>
                Esta invitación ya está <strong>activa</strong>: el link que compartiste con tus invitados va a dejar de funcionar.
            </p>
            <p class="modal-riesgo-destacado" id="modalRiesgoPagos" hidden>
                Tiene <strong>comprobantes de pago</strong> de tus invitados. Si los necesitás, descargalos desde "Pagos Recibidos" antes de eliminar.
            </p>
        </div>

        <label class="modal-confirmacion">
            <input type="checkbox" name="confirmo_eliminacion" value="1" id="modalConfirmo">
            Entiendo que no se puede recuperar
        </label>

        <div class="modal-actions">
            <button type="button" class="btn-modal-cancelar" id="modalCancelar">Cancelar</button>
            <button type="submit" class="btn-modal-confirmar" id="modalConfirmar" disabled>Sí, eliminar</button>
        </div>
    </form>
</div>

<div class="dashboard-body">
    <div class="dashboard-wrapper">

        <!-- ALERTAS DEL SISTEMA (usando clases en vez de inline styles) -->
        <?php
        $alertas = [
            'eliminada'          => ['tipo' => 'exito',    'texto' => 'La invitación y todos sus archivos fueron eliminados correctamente.'],
            'eliminada_con_avisos'=> ['tipo' => 'error',   'texto' => 'La invitación fue eliminada, pero algunos archivos no se pudieron borrar del servidor. Quedó registrado para revisarlo.'],
            'sin_confirmar'      => ['tipo' => 'error',    'texto' => 'Para eliminar una invitación tenés que confirmar que entendés que no se puede recuperar.'],
            'error_csrf'         => ['tipo' => 'error',    'texto' => 'La sesión del formulario expiró. Recargá la página e intentá de nuevo.'],
            'no_autorizado'      => ['tipo' => 'error',    'texto' => 'No se encontró esa invitación o no tenés permiso para eliminarla.'],
            'id_invalido'        => ['tipo' => 'error',    'texto' => 'No se encontró esa invitación.'],
            'error_eliminar'     => ['tipo' => 'error',    'texto' => 'No pudimos eliminar la invitación. No se borró nada; intentá de nuevo.'],
            'tarjeta_creada'     => ['tipo' => 'exito',    'texto' => '¡Tu invitación se creó con éxito! Ya podés completar los datos.'],
            'tarjeta_actualizada'=> ['tipo' => 'exito',    'texto' => '¡Tu invitación se actualizó con éxito!'],
            'foto_no_guardada'   => ['tipo' => 'error',    'texto' => 'Guardamos tus datos, pero la foto de portada no se pudo subir. Usá una imagen JPG, PNG o WEBP de hasta 8 MB.'],
            'tarjeta_invalida'   => ['tipo' => 'error',    'texto' => 'No se encontró esa invitación.'],
            'error_carga'        => ['tipo' => 'error',    'texto' => 'Hubo un problema al cargar tus invitaciones. Intentá de nuevo.'],
        ];

        // Compatibilidad con param viejo 'msg' y nuevo 'mensaje'
        $param = $_GET['mensaje'] ?? $_GET['msg'] ?? $_GET['error'] ?? null;
        if (is_string($param) && isset($alertas[$param])):
            $alerta = $alertas[$param];
        ?>
            <div class="dash-alerta dash-alerta-<?php echo $alerta['tipo']; ?>" role="alert">
                <?php echo htmlspecialchars($alerta['texto']); ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($error_carga)): ?>
            <div class="dash-alerta dash-alerta-error" role="alert">
                No pudimos cargar tus invitaciones. Por favor, recargá la página.
            </div>
        <?php endif; ?>

        <!-- ENCABEZADO DEL PANEL -->
        <header class="dashboard-header">
            <h1>¡Hola, <?php echo htmlspecialchars($nombre_usuario); ?>!</h1>
            <p>Desde acá gestionás todas tus invitaciones</p>
        </header>

        <!-- ACCIONES PRINCIPALES -->
        <div class="center-actions">
            <a href="index.php" class="btn-ini-cerrar">← Inicio</a>
            <a href="personalizar.php" class="btn-create-new">
                <?php echo icono_svg('nuevo'); ?> Crear nueva invitación
            </a>
        </div>

        <!-- ESTADO VACÍO -->
        <?php if (empty($mis_tarjetas)): ?>
            <div class="empty-state">
                <div class="empty-state-icon">✉️</div>
                <h3>Todavía no tenés invitaciones</h3>
                <p>Creá tu primera invitación digital en menos de 5 minutos.</p>
                <a href="personalizar.php" class="btn-create-new" style="display:inline-flex;margin-top:16px;">
                    <?php echo icono_svg('nuevo'); ?> Crear mi primera invitación
                </a>
            </div>

        <?php else: ?>

            <h2 class="section-title">Mis Invitaciones <span class="badge-count"><?php echo count($mis_tarjetas); ?></span></h2>

            <section class="cards-grid" aria-label="Lista de invitaciones">
                <?php foreach ($mis_tarjetas as $tarjeta):
                    $es_pagada   = ($tarjeta['estado_pago'] === 'pagado');
                    $id          = (int)$tarjeta['id_tarjeta'];
                    $titulo      = htmlspecialchars($tarjeta['nombres_portada'] ?: 'Sin título');
                    $tipo        = ucfirst($tarjeta['evento_nombre']);
                    $fecha_raw   = $tarjeta['fecha_evento'];
                    $fecha_fmt   = $fecha_raw ? date('d/m/Y', strtotime($fecha_raw)) : null;

                    // Días restantes para el evento
                    $dias_resto  = null;
                    if ($fecha_raw) {
                        $hoy       = new DateTime('today');
                        $evento_dt = new DateTime($fecha_raw);
                        $diff      = (int)$hoy->diff($evento_dt)->days;
                        $dias_resto= $evento_dt >= $hoy ? $diff : -$diff;
                    }
                ?>
                    <article class="invitation-card <?php echo $es_pagada ? 'card-pagada' : 'card-pendiente'; ?>"
                             aria-label="Invitación: <?php echo $titulo; ?>">

                        <!-- CHIP DE ESTADO -->
                        <div class="card-estado-chip <?php echo $es_pagada ? 'chip-pagado' : 'chip-pendiente'; ?>">
                            <?php if ($es_pagada): ?>
                                <span class="chip-dot"></span> Activa
                            <?php else: ?>
                                <span class="chip-dot"></span> Pendiente de pago
                            <?php endif; ?>
                        </div>

                        <!-- CUERPO PRINCIPAL -->
                        <div class="card-body">
                            <div class="card-meta">
                                <span class="card-tipo"><?php echo $tipo; ?></span>
                                <?php if ($dias_resto !== null): ?>
                                    <span class="card-dias <?php echo $dias_resto < 0 ? 'dias-pasado' : ($dias_resto <= 7 ? 'dias-urgente' : ''); ?>">
                                        <?php if ($dias_resto < 0): ?>
                                            Hace <?php echo abs($dias_resto); ?> días
                                        <?php elseif ($dias_resto === 0): ?>
                                            ¡Hoy!
                                        <?php else: ?>
                                            Faltan <?php echo $dias_resto; ?> días
                                        <?php endif; ?>
                                    </span>
                                <?php endif; ?>
                            </div>

                            <h3 class="card-titulo"><?php echo $titulo; ?></h3>

                            <?php if ($fecha_fmt): ?>
                                <p class="card-fecha">📅 <?php echo $fecha_fmt; ?></p>
                            <?php endif; ?>
                        </div>

                        <!-- ACCIONES PRIMARIAS -->
                        <div class="card-acciones-primarias">
                            <a href="visualizar_tarjeta.php?id=<?php echo $id; ?>"
                               target="_blank"
                               class="action-btn btn-view"
                               title="Ver invitación">
                                <?php echo icono_svg('ver'); ?> Ver
                            </a>
                            <a href="editar_tarjeta.php?id=<?php echo $id; ?>"
                               class="action-btn btn-view"
                               title="Editar invitación">
                                <?php echo icono_svg('editar'); ?> Editar
                            </a>
                            <?php if (!$es_pagada): ?>
                                <a href="pagar.php?id=<?php echo $id; ?>"
                                   class="action-btn btn-pagar"
                                   title="Pagar esta invitación">
                                    <?php echo icono_svg('pagar'); ?> Pagar
                                </a>
                            <?php endif; ?>
                            <?php if ($es_pagada): ?>
                                <a href="compartir.php?id=<?php echo $id; ?>"
                                   class="action-btn btn-share"
                                   title="Compartir invitación">
                                    <?php echo icono_svg('compartir'); ?> Compartir
                                </a>
                            <?php endif; ?>
                            <?php
                            // Resumen de lo que se pierde (null = no se pudo contar: el modal avisa en genérico)
                            $datos_tarjeta = $datos_por_tarjeta[$id] ?? null;
                            if ($datos_tarjeta !== null) {
                                $datos_tarjeta['fotos'] = count(glob(__DIR__ . '/uploads/book/' . $id . '/*') ?: [])
                                                        + (empty($tarjeta['foto_portada']) ? 0 : 1);
                            }
                            ?>
                            <button
                                class="action-btn btn-eliminar"
                                data-eliminar-id="<?php echo $id; ?>"
                                data-eliminar-titulo="<?php echo $titulo; ?>"
                                data-eliminar-activa="<?php echo $es_pagada ? '1' : '0'; ?>"
                                <?php if ($datos_tarjeta !== null): ?>
                                data-eliminar-datos="<?php echo htmlspecialchars(json_encode([
                                    'rsvp'     => (int)$datos_tarjeta['rsvp'],
                                    'muro'     => (int)$datos_tarjeta['muro'],
                                    'playlist' => (int)$datos_tarjeta['playlist'],
                                    'pagos'    => (int)$datos_tarjeta['pagos'],
                                    'hitos'    => (int)$datos_tarjeta['hitos'],
                                    'fotos'    => (int)$datos_tarjeta['fotos'],
                                ])); ?>"
                                <?php endif; ?>
                                title="Eliminar invitación"
                                type="button"
                            >
                                <?php echo icono_svg('eliminar'); ?> Eliminar
                            </button>
                        </div>

                        <!-- ACCIONES DE MÓDULOS (solo si tiene módulos activos) -->
                        <?php
                        $tiene_modulos = !empty($tarjeta['mod_rsvp_interno'])
                                      || !empty($tarjeta['mod_linea_tiempo'])
                                      || !empty($tarjeta['mod_muro_deseos'])
                                      || !empty($tarjeta['mod_pagar_evento']);
                        if ($tiene_modulos):
                        ?>
                            <div class="card-footer">
                                <span class="footer-label">Gestionar módulos:</span>
                                <div class="footer-btns">
                                    <?php if (!empty($tarjeta['mod_rsvp_interno'])): ?>
                                        <a href="rsvp_panel.php?tarjeta=<?php echo $id; ?>"
                                           class="action-btn btn-config" title="Panel de confirmaciones">
                                            <?php echo icono_svg('rsvp'); ?> RSVP
                                        </a>
                                    <?php endif; ?>
                                    <?php if (!empty($tarjeta['mod_linea_tiempo'])): ?>
                                        <a href="gestionar_hitos.php?id=<?php echo $id; ?>"
                                           class="action-btn btn-config" title="Gestionar hitos">
                                            <?php echo icono_svg('hitos'); ?> Hitos
                                        </a>
                                    <?php endif; ?>
                                    <?php if (!empty($tarjeta['mod_muro_deseos'])): ?>
                                        <a href="gestionar_deseos.php?id=<?php echo $id; ?>"
                                           class="action-btn btn-config" title="Muro de deseos">
                                            <?php echo icono_svg('muro'); ?> Muro
                                        </a>
                                    <?php endif; ?>
                                    <?php if (!empty($tarjeta['mod_pagar_evento'])): ?>
                                        <a href="configurar_pagos.php?id=<?php echo $id; ?>"
                                           class="action-btn btn-config" title="Configurar pagos del evento">
                                            <?php echo icono_svg('config'); ?> Config. Pagos
                                        </a>
                                        <a href="gestionar_pagos.php?id=<?php echo $id; ?>"
                                           class="action-btn btn-config" title="Ver pagos recibidos">
                                            <?php echo icono_svg('pagar'); ?> Pagos Recibidos
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endif; ?>

                    </article>
                <?php endforeach; ?>
            </section>

        <?php endif; ?>

    </div><!-- /.dashboard-wrapper -->
</div><!-- /.dashboard-body -->

<?php include_once 'footer.php'; ?>

<script>
// ── Modal de eliminación ──
(function () {
    const overlay    = document.getElementById('modalEliminar');
    const form       = document.getElementById('modalEliminarForm');
    const inputId    = document.getElementById('modalIdTarjeta');
    const desc       = document.getElementById('modalDescripcion');
    const lista      = document.getElementById('modalRiesgos');
    const avisoActiva= document.getElementById('modalRiesgoActiva');
    const avisoPagos = document.getElementById('modalRiesgoPagos');
    const confirmo   = document.getElementById('modalConfirmo');
    const btnOk      = document.getElementById('modalConfirmar');
    const btnCancel  = document.getElementById('modalCancelar');
    let ultimoFoco   = null;

    // [clave, singular, plural]
    const etiquetas = [
        ['rsvp',     'confirmación de asistencia',  'confirmaciones de asistencia'],
        ['muro',     'mensaje del muro de deseos',  'mensajes del muro de deseos'],
        ['playlist', 'canción sugerida',            'canciones sugeridas'],
        ['pagos',    'pago informado (con su comprobante)', 'pagos informados (con sus comprobantes)'],
        ['hitos',    'hito de la línea de tiempo (con su foto)', 'hitos de la línea de tiempo (con sus fotos)'],
        ['fotos',    'foto subida (book y portada)', 'fotos subidas (book y portada)'],
    ];

    function agregarItem(texto) {
        const li = document.createElement('li');
        li.textContent = texto;
        lista.appendChild(li);
    }

    function abrir(btn) {
        const titulo = btn.dataset.eliminarTitulo || 'esta invitación';
        let datos = null;
        try { datos = btn.dataset.eliminarDatos ? JSON.parse(btn.dataset.eliminarDatos) : null; } catch (e) {}

        inputId.value    = btn.dataset.eliminarId;
        desc.textContent = '¿Querés eliminar "' + titulo + '"? Esta acción no se puede deshacer.';

        lista.textContent = '';
        agregarItem('La invitación con todos sus textos y módulos');
        if (datos) {
            etiquetas.forEach(function (e) {
                const n = datos[e[0]] || 0;
                if (n > 0) agregarItem(n + ' ' + (n === 1 ? e[1] : e[2]));
            });
        } else {
            agregarItem('Todas las respuestas, mensajes, pagos y fotos asociadas');
        }

        avisoActiva.hidden = btn.dataset.eliminarActiva !== '1';
        avisoPagos.hidden  = !(datos && datos.pagos > 0);

        confirmo.checked = false;
        btnOk.disabled   = true;
        btnOk.textContent = 'Sí, eliminar';

        ultimoFoco = btn;
        overlay.classList.add('activo');
        btnCancel.focus();   // foco en la opción segura
    }

    function cerrar() {
        if (!overlay.classList.contains('activo')) return;
        overlay.classList.remove('activo');
        if (ultimoFoco) ultimoFoco.focus();
    }

    document.querySelectorAll('[data-eliminar-id]').forEach(function (btn) {
        btn.addEventListener('click', function () { abrir(btn); });
    });

    confirmo.addEventListener('change', function () { btnOk.disabled = !confirmo.checked; });

    form.addEventListener('submit', function (e) {
        if (!confirmo.checked) { e.preventDefault(); return; }
        btnOk.disabled    = true;     // evita doble envío
        btnOk.textContent = 'Eliminando...';
    });

    btnCancel.addEventListener('click', cerrar);
    overlay.addEventListener('click', function (e) { if (e.target === overlay) cerrar(); });
    document.addEventListener('keydown', function (e) { if (e.key === 'Escape') cerrar(); });
})();
</script>

</body>
</html>