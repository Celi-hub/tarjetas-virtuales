<?php
// logout.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Guardar el nombre para el mensaje de despedida (opcional)
$nombre = $_SESSION['nombre_usuario'] ?? null;

// Destruir sesión de forma segura
$_SESSION = [];
if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(), '',
        time() - 42000,
        $params['path'],
        $params['domain'],
        $params['secure'],
        $params['httponly']
    );
}
session_destroy();

// Redirigir al inicio con mensaje opcional
header("Location: index.php" . ($nombre ? "?adios=1" : ""));
exit;