<?php
require_once __DIR__ . '/conexion.php';


if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1. Verificar seguridad: ¿Está logueado?
if (!isset($_SESSION['id_usuario'])) {
    header("Location: login.php");
    exit;
}

// 2. Obtener el ID de la tarjeta
$id_tarjeta = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id_tarjeta <= 0) {
    header("Location: dashboard.php?error=id_invalido");
    exit;
}

try {
    // 3. Validar propiedad: Borramos solo si la tarjeta pertenece a este cliente
    // Esto evita que un usuario intente borrar tarjetas de otro usuario cambiando el ID en la URL
    $stmt = $pdo->prepare("DELETE FROM tarjetas WHERE id_tarjeta = ? AND id_cliente = ?");
    $stmt->execute([$id_tarjeta, $_SESSION['id_usuario']]);

    // 4. Verificar si se eliminó algo
    if ($stmt->rowCount() > 0) {
        // Éxito: volvemos al dashboard con un mensaje
        header("Location: dashboard.php?msg=eliminada");
    } else {
        // No se borró nada (quizás el ID no era suyo o ya no existe)
        header("Location: dashboard.php?error=no_autorizado");
    }
} catch (PDOException $e) {
    error_log('Error al eliminar: ' . $e->getMessage());
    die('Error al eliminar la tarjeta.');
}
exit;