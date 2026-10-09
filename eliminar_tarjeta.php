<?php
require_once __DIR__ . '/conexion.php';
require_once __DIR__ . '/helpers/archivos_tarjeta.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1. Verificar seguridad: ¿Está logueado?
if (!isset($_SESSION['id_usuario'])) {
    header("Location: login.php");
    exit;
}

// 2. Solo POST desde el modal del dashboard, con su token CSRF.
//    (Antes era un GET: cualquier link o <img> externo podía borrar una tarjeta.)
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: dashboard.php");
    exit;
}

$posted_token  = $_POST['csrf_token'] ?? '';
$session_token = $_SESSION['csrf_token_eliminar_tarjeta'] ?? '';
if (!is_string($posted_token) || $session_token === '' || !hash_equals($session_token, $posted_token)) {
    header("Location: dashboard.php?error=error_csrf");
    exit;
}

// El modal exige marcar "Entiendo que es permanente"; se vuelve a validar acá.
if (empty($_POST['confirmo_eliminacion'])) {
    header("Location: dashboard.php?error=sin_confirmar");
    exit;
}

// 3. Obtener el ID de la tarjeta
$id_tarjeta = isset($_POST['id_tarjeta']) ? (int)$_POST['id_tarjeta'] : 0;

if ($id_tarjeta <= 0) {
    header("Location: dashboard.php?error=id_invalido");
    exit;
}

try {
    // 4. Validar propiedad: solo se borra si la tarjeta pertenece a este cliente
    $stmt = $pdo->prepare("SELECT id_tarjeta, foto_portada FROM tarjetas WHERE id_tarjeta = ? AND id_cliente = ?");
    $stmt->execute([$id_tarjeta, $_SESSION['id_usuario']]);
    $tarjeta = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$tarjeta) {
        header("Location: dashboard.php?error=no_autorizado");
        exit;
    }

    // 5. Listar los archivos ANTES del DELETE: el ON DELETE CASCADE se lleva las
    //    filas (hitos, pagos) que dicen qué archivo pertenece a esta tarjeta.
    $archivos = momentia_archivos_de_tarjeta($pdo, $tarjeta);

    $stmt = $pdo->prepare("DELETE FROM tarjetas WHERE id_tarjeta = ? AND id_cliente = ?");
    $stmt->execute([$id_tarjeta, $_SESSION['id_usuario']]);

    if ($stmt->rowCount() === 0) {
        header("Location: dashboard.php?error=no_autorizado");
        exit;
    }
} catch (PDOException $e) {
    error_log('Error al eliminar: ' . $e->getMessage());
    header("Location: dashboard.php?error=error_eliminar");
    exit;
}

// 6. Recién con la BD confirmada se borran los archivos: si el DELETE fallaba,
//    la tarjeta seguía existiendo con todas sus fotos intactas.
$fallas = momentia_borrar_archivos_tarjeta($id_tarjeta, $archivos);

header("Location: dashboard.php?msg=" . ($fallas === 0 ? 'eliminada' : 'eliminada_con_avisos'));
exit;
