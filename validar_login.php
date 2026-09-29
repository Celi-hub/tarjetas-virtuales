<?php
// validar_login.php
session_start();
require_once __DIR__ . '/conexion.php';

// Solo aceptamos POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: login.php');
    exit;
}

$email = trim($_POST['email'] ?? '');
$pass  = $_POST['password'] ?? '';

// Validación mínima antes de tocar la BD
if (empty($email) || empty($pass)) {
    header('Location: login.php?error=credenciales&email=' . urlencode($email));
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT * FROM clientes WHERE email = ? LIMIT 1");
    $stmt->execute([$email]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($usuario && password_verify($pass, $usuario['password'])) {

        // Regenerar ID de sesión para prevenir session fixation
        session_regenerate_id(true);

        $_SESSION['id_usuario']     = $usuario['id_cliente'];
        $_SESSION['nombre_usuario'] = $usuario['nombre_cliente'];
        $_SESSION['apellido']       = $usuario['apellido_cliente'];

        // Si venía de una página protegida, volvemos ahí
        $destino = $_SESSION['redirect_tras_login'] ?? 'dashboard.php';
        unset($_SESSION['redirect_tras_login']);

        header('Location: ' . $destino);
        exit;

    } else {
        // Fallo: volvemos al login con el email pre-cargado pero SIN la contraseña
        header('Location: login.php?error=credenciales&email=' . urlencode($email));
        exit;
    }

} catch (PDOException $e) {
    // En producción loguear el error real; al usuario le mostramos uno genérico
    error_log('Error en validar_login: ' . $e->getMessage());
    header('Location: login.php?error=error_generico');
    exit;
}