<?php
// procesar_paso1.php
require_once __DIR__ . '/conexion.php';
require_once __DIR__ . '/config/modulos.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['id_usuario'])) {
    header('Location: login.php?error=debe_loguearse');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: personalizar.php');
    exit;
}

$id_usuario       = $_SESSION['id_usuario'];
$id_plantilla     = (int)($_POST['id_plantilla'] ?? 1);
$tipo_evento_id   = (int)($_POST['tipo_evento_id'] ?? 0);
$estilo_visual_id = (int)($_POST['estilo_visual_id'] ?? 0);
$modulos_elegidos = $_POST['modulos'] ?? [];

// Validación con redirect limpio (sin die())
if (!$tipo_evento_id || !$estilo_visual_id) {
    header('Location: personalizar.php?error=datos_incompletos');
    exit;
}

$datos_insert = [
    'id_cliente'       => $id_usuario,
    'id_plantilla'     => $id_plantilla,
    'tipo_evento_id'   => $tipo_evento_id,
    'estilo_visual_id' => $estilo_visual_id,
];

foreach (modulos_columnas_bd() as $columna) {
    $modulo = modulos_config()[$columna];

    if (!empty($modulo['incluido_base'])) {
        $datos_insert[$columna] = 1;
        continue;
    }

    $datos_insert[$columna] = in_array($columna, $modulos_elegidos, true) ? 1 : 0;
}

try {
    $columnas     = implode(', ', array_keys($datos_insert));
    $placeholders = implode(', ', array_fill(0, count($datos_insert), '?'));

    $sql  = "INSERT INTO tarjetas ($columnas) VALUES ($placeholders)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(array_values($datos_insert));

    $nuevo_id = $pdo->lastInsertId();
    header('Location: completar_datos.php?id=' . $nuevo_id);
    exit;

} catch (PDOException $e) {
    error_log('Error en procesar_paso1.php: ' . $e->getMessage());
    header('Location: personalizar.php?error=error_guardado');
    exit;
}