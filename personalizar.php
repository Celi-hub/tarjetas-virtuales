<?php
require_once __DIR__ . '/conexion.php';
require_once __DIR__ . '/config/modulos.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['id_usuario'])) {
    $_SESSION['redirect_tras_login'] = 'personalizar.php';
    header("Location: login.php?error=debe_loguearse");
    exit;
}

try {
    $stmtEventos = $pdo->query("SELECT id_tipos_evento, nombre FROM tipos_evento ORDER BY nombre");
    $tipos_evento = $stmtEventos->fetchAll();

    $stmtEstilos = $pdo->query("SELECT id_estilos_visuales, nombre FROM estilos_visuales ORDER BY nombre");
    $estilos_visuales = $stmtEstilos->fetchAll();
} catch (PDOException $e) {
    header('Location: dashboard.php?error=error_carga');
    exit;
}

$id_plantilla_seleccionada = isset($_GET['id']) ? intval($_GET['id']) : 1;

$reglas_modulos_js = [];
foreach ($tipos_evento as $evento) {
    $slug = mb_strtolower($evento['nombre']);
    $reglas_modulos_js[$slug] = array_keys(modulos_para_evento($slug));
}

$iconos_evento = [
    1         => 'img/tipo_evento/boda.svg',
    2         => 'img/tipo_evento/cumpleaños.svg',
 //   3         => 'img/tipo_evento/egreso.svg',
    4         => 'img/tipo_evento/deportivo.svg',
//    5         => 'img/tipo_evento/corporativo.svg',
   6         => 'img/tipo_evento/bautismo.svg',
  //  11            => 'img/tipo_evento/15_años.svg',
    //12        => 'img/tipo_evento/aniversario.svg',
//    14         => 'img/tipo_evento/inauguracion.svg',
  //  15        => 'img/tipo_evento/despedida.svg',
    //16         => 'img/tipo_evento/reunion_familiar.svg',
//    17        => 'img/tipo_evento/fiesta.svg',
  //  18        => 'img/tipo_evento/premiacion.svg',
    //19         => 'img/tipo_evento/baby_shower.svg',
//    20         => 'img/tipo_evento/revelacion.svg',
  //  21         => 'img/tipo_evento/comunion.svg',
    //22         => 'img/tipo_evento/confirmacion.svg',
];

$precios_modulos = [
    'mod_cuenta_regresiva' => 500,
    'mod_ubi_calen'        => 800,
   /* 'mod_ubicacion'        => 800,
    'mod_calendario'       => 600,*/
    'mod_musica'           => 700,
    'mod_regalos'          => 600,
    'mod_dress_code'       => 500,
    'mod_compartir_fotos'  => 900,
    'mod_book_fotos'       => 1200,
    'mod_rsvp_interno'     => 1500,
    'mod_muro_deseos'      => 1000,
    'mod_pagar_evento'     => 2000,
    'mod_playlist'         => 800,
    'mod_linea_tiempo'     => 1200,
];
$precio_base = 3000;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear Invitación — Momentia</title>
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

        <!-- BARRA DE PROGRESO -->
        <div class="wizard-progress" role="navigation" aria-label="Pasos del formulario">
            <div class="wizard-step activo" id="wp-paso1">
                <div class="wizard-step-circle">1</div>
                <span class="wizard-step-label">Configurar</span>
            </div>
            <div class="wizard-connector" id="wc-1"></div>
            <div class="wizard-step" id="wp-paso2">
                <div class="wizard-step-circle">2</div>
                <span class="wizard-step-label">Cargar datos</span>
            </div>
            <div class="wizard-connector" id="wc-2"></div>
            <div class="wizard-step" id="wp-paso3">
                <div class="wizard-step-circle">✓</div>
                <span class="wizard-step-label">¡Lista!</span>
            </div>
        </div>

        <form action="procesar_paso1.php" method="POST" id="formPaso1">
            <input type="hidden" name="id_plantilla" value="<?php echo $id_plantilla_seleccionada; ?>">
            <input type="hidden" name="tipo_evento_id"   id="hidden_evento_id"  value="">
            <input type="hidden" name="estilo_visual_id" id="hidden_estilo_id"  value="">

            <!-- ══════════════════════════════════════════
                 PASO A — TIPO DE EVENTO
            ══════════════════════════════════════════ -->
            <div class="paso-bloque visible" id="bloque-evento">
                <h2 class="paso-titulo">¿Qué vamos a festejar?</h2>
                <p class="paso-subtitulo">Seleccioná el tipo de evento para empezar.</p>

                <!-- Precio base informativo, discreto -->
                <div class="precio-info-base">
                    Tarjeta base desde <strong>$<?php echo number_format($precio_base, 0, ',', '.'); ?></strong>
                    &mdash; podés sumar módulos a tu gusto después.
                </div>

<div class="evento-grid" role="radiogroup" aria-label="Tipo de evento">
    <?php foreach ($tipos_evento as $evento):
        $id_evento = $evento['id_tipos_evento'];
        // Busca por número de ID. Si no existe, usa un icono genérico que tengas mapeado
        $icono     = $iconos_evento[$id_evento] ?? 'img/tipo_evento/default.svg'; 
    ?>
        <label class="evento-card" id="card_evento_<?php echo $id_evento; ?>">
            <input
                type="radio"
                name="_tipo_evento_visual"
                value="<?php echo $id_evento; ?>"
                data-nombre="<?php echo htmlspecialchars(mb_strtolower($evento['nombre'])); ?>"
            >
            <span class="evento-icono">
                <img src="<?php echo $icono; ?>" alt="<?php echo htmlspecialchars($evento['nombre']); ?>" class="icono-evento-svg">
            </span>
            <span class="evento-nombre"><?php echo ucfirst($evento['nombre']); ?></span>
        </label>
    <?php endforeach; ?>
</div>
            </div>

            <!-- ══════════════════════════════════════════
                 PASO B — ESTILO VISUAL
            ══════════════════════════════════════════ -->
            <div class="paso-bloque" id="bloque-estilo">
                <div class="paso-separador"></div>
                <p class="paso-titulo-confirmado" id="confirmacion-evento">
                    ✔ Tipo de evento seleccionado. Ahora elegí el estilo de tu tarjeta.
                </p>

                <div class="form-group estilo-select-wrapper">
                    <label for="estilo_visual">Estilo visual</label>
                    <select id="estilo_visual" required>
                        <option value="">Seleccioná un estilo...</option>
                        <?php foreach ($estilos_visuales as $estilo): ?>
                            <option value="<?php echo $estilo['id_estilos_visuales']; ?>">
                                <?php echo ucfirst($estilo['nombre']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <!-- ══════════════════════════════════════════
                 PASO C — MÓDULOS
            ══════════════════════════════════════════ -->
            <div class="paso-bloque" id="bloque-modulos">
                <div class="paso-separador"></div>
                <p class="paso-titulo-confirmado">
                    ✔ Estilo seleccionado. Ahora elegí los módulos que incluirás en tu tarjeta.
                </p>

                <!-- Módulos incluidos -->
                <fieldset>
                    <legend>Incluido en todas las invitaciones</legend>
                    <p style="font-size:0.88rem;color:#7a6e67;margin-bottom:14px;">
                        Estos módulos vienen activos sin costo adicional.
                    </p>
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

                <!-- Módulos opcionales -->
                <fieldset style="margin-top: 20px;">
                    <legend>Módulos adicionales (a la carta)</legend>
                    <p style="font-size:0.88rem;color:#7a6e67;margin-bottom:14px;">
                        Activá los que quieras. El precio se actualiza en tiempo real.
                    </p>
                    <div class="modules-grid" id="grid_modulos">
                        <?php foreach (modulos_seleccionables() as $columna => $modulo):
                            $precio = $precios_modulos[$columna] ?? 0;
                        ?>
                            <label
                                class="module-option oculto"
                                id="container_<?php echo $columna; ?>"
                                data-precio="<?php echo $precio; ?>"
                            >
                                <input
                                    type="checkbox"
                                    name="modulos[]"
                                    value="<?php echo $columna; ?>"
                                    class="checkbox-modulo"
                                >
                                <div class="module-text">
                                    <strong><?php echo htmlspecialchars($modulo['titulo']); ?></strong>
                                    <small><?php echo htmlspecialchars($modulo['descripcion']); ?></small>
                                </div>
                                <?php if ($precio > 0): ?>
                                    <span class="module-price">
                                        + $<?php echo number_format($precio, 0, ',', '.'); ?>
                                    </span>
                                <?php endif; ?>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </fieldset>

                <!-- Resumen de precio -->
                <div class="precio-resumen" aria-live="polite">
                    <div>
                        <div class="precio-resumen-monto" id="precio_total">
                            $<?php echo number_format($precio_base, 0, ',', '.'); ?>
                        </div>
                        <div class="precio-resumen-detalle" id="precio_detalle">
                            Tarjeta base · sin módulos adicionales
                        </div>
                    </div>
                </div>

                <!-- Botón siguiente -->
                <div class="btn-siguiente-wrap">
                    <button type="submit" class="btn-submit" id="btn_submit">
                        Siguiente: Cargar datos →
                    </button>
                </div>
            </div>

        </form>
    </div>
</main>

<?php include_once 'footer.php'; ?>

<script>
const reglasModulos  = <?php echo json_encode($reglas_modulos_js, JSON_UNESCAPED_UNICODE); ?>;
const precioBase     = <?php echo $precio_base; ?>;

// Referencias DOM
const hiddenEventoId = document.getElementById('hidden_evento_id');
const hiddenEstiloId = document.getElementById('hidden_estilo_id');
const selectEstilo   = document.getElementById('estilo_visual');
const bloqueEstilo   = document.getElementById('bloque-estilo');
const bloqueModulos  = document.getElementById('bloque-modulos');
const precioTotal    = document.getElementById('precio_total');
const precioDetalle  = document.getElementById('precio_detalle');
const confirmEvento  = document.getElementById('confirmacion-evento');

// ── Utilidades ──
function formatPeso(n) {
    return '$' + n.toLocaleString('es-AR');
}

function scrollSuave(el) {
    setTimeout(() => {
        el.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }, 80);
}

function mostrarBloque(bloque) {
    bloque.classList.add('visible');
    scrollSuave(bloque);
}

// ── Actualizar precio ──
function actualizarPrecio() {
    let total  = precioBase;
    const extras = [];
    document.querySelectorAll('.checkbox-modulo:checked').forEach(cb => {
        const label  = cb.closest('.module-option');
        const precio = parseInt(label?.dataset.precio || '0', 10);
        const nombre = label?.querySelector('strong')?.textContent?.trim() || '';
        if (precio > 0) { total += precio; extras.push(nombre); }
    });
    precioTotal.textContent = formatPeso(total);
    if (extras.length === 0) {
        precioDetalle.textContent = 'Tarjeta base · sin módulos adicionales';
    } else if (extras.length === 1) {
        precioDetalle.textContent = 'Base + ' + extras[0];
    } else {
        precioDetalle.textContent = 'Base + ' + extras.length + ' módulos: ' + extras.join(', ');
    }
}

// ── PASO A: selección de evento ──
document.querySelectorAll('.evento-card input[type="radio"]').forEach(radio => {
    radio.addEventListener('change', function () {
        // Marcar card seleccionada
        document.querySelectorAll('.evento-card').forEach(c => c.classList.remove('seleccionado'));
        this.closest('.evento-card').classList.add('seleccionado');

        // Guardar valor
        hiddenEventoId.value = this.value;
        const nombreEvento   = this.dataset.nombre;

        // Actualizar mensaje de confirmación con el nombre del evento
        const labelEvento = this.closest('.evento-card').querySelector('.evento-nombre').textContent;
        confirmEvento.innerHTML = '✔ <em>' + labelEvento + '</em> seleccionado. Ahora elegí el estilo de tu tarjeta.';

        // Resetear bloque de estilo y módulos si ya estaban visibles
        selectEstilo.value = '';
        hiddenEstiloId.value = '';
        bloqueModulos.classList.remove('visible');

        // Preparar módulos para este evento (ocultar todos, mostrar los permitidos)
        document.querySelectorAll('#grid_modulos .module-option').forEach(m => {
            m.classList.add('oculto');
            const cb = m.querySelector('input[type="checkbox"]');
            if (cb) { cb.checked = false; m.classList.remove('seleccionado'); }
        });
        const permitidos = reglasModulos[nombreEvento] || [];
        permitidos.forEach(col => {
            const el = document.getElementById('container_' + col);
            if (el) el.classList.remove('oculto');
        });

        actualizarPrecio();

        // Mostrar bloque de estilo
        if (!bloqueEstilo.classList.contains('visible')) {
            mostrarBloque(bloqueEstilo);
        } else {
            scrollSuave(bloqueEstilo);
        }
    });
});

// ── PASO B: selección de estilo ──
selectEstilo.addEventListener('change', function () {
    if (!this.value) return;
    hiddenEstiloId.value = this.value;

    if (!bloqueModulos.classList.contains('visible')) {
        mostrarBloque(bloqueModulos);
    } else {
        scrollSuave(bloqueModulos);
    }
});

// ── PASO C: checkboxes de módulos ──
document.querySelectorAll('.checkbox-modulo').forEach(cb => {
    cb.addEventListener('change', function () {
        this.closest('.module-option').classList.toggle('seleccionado', this.checked);
        actualizarPrecio();
    });
});

// ── Validación al enviar ──
document.getElementById('formPaso1').addEventListener('submit', function (e) {
    if (!hiddenEventoId.value || !hiddenEstiloId.value) {
        e.preventDefault();
        alert('Por favor, completá todos los pasos antes de continuar.');
        return;
    }
    // Copiar el valor del select al hidden (por si acaso)
    if (!hiddenEstiloId.value && selectEstilo.value) {
        hiddenEstiloId.value = selectEstilo.value;
    }
});

actualizarPrecio();
</script>
</body>
</html>