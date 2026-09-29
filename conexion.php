<?php
// conexion.php
// La contraseña de producción se lee de una variable de entorno,
// nunca hardcodeada en el código fuente.
//
// En InfinityFree o tu hosting, definí la variable de entorno
// desde el panel de control o en un archivo .env (ver abajo).
// En local (XAMPP) no hace falta: detectamos el entorno automáticamente.

$http_host   = $_SERVER['HTTP_HOST'] ?? '';
$server_name = $_SERVER['SERVER_NAME'] ?? '';
$es_local = (
    $http_host === 'localhost' ||
    strpos($http_host, 'localhost:') === 0 ||
    strpos($http_host, '127.0.0.1') !== false ||
    $server_name === 'localhost'
);

if (getenv('DB_HOST')) {
    // Entorno explícito (Docker, CI, hosting con variables): manda la configuración por variables.
    // El detalle de errores solo se muestra si APP_ENV=development.
    $host     = getenv('DB_HOST');
    $db       = getenv('DB_NAME') ?: 'tarjeta_virtual';
    $user     = getenv('DB_USER') ?: 'root';
    $pass     = getenv('DB_PASS') !== false ? getenv('DB_PASS') : '';
    $es_local = getenv('APP_ENV') === 'development';
} elseif ($es_local) {
    $host = 'localhost';
    $db   = 'tarjeta_virtual';
    $user = 'root';
    $pass = '';
} else {
    // Producción: leer credenciales de variables de entorno
    // Nunca escribir la contraseña real acá.
    $host = getenv('DB_HOST') ?: 'sql301.infinityfree.com';
    $db   = getenv('DB_NAME') ?: 'if0_39676777_tarjeta_virtual';
    $user = getenv('DB_USER') ?: 'if0_39676777';
    $pass = getenv('DB_PASS') ?: ''; // vacío hasta que configures la variable
}

try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$db;charset=utf8mb4",
        $user,
        $pass,
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false, // Prepared statements reales
        ]
    );
} catch (PDOException $e) {
    // Nunca mostrar el error real al visitante en producción
    error_log('Error de conexión BD: ' . $e->getMessage());
    if ($es_local) {
        // En local sí mostramos el detalle para debug
        die('<pre style="color:red">Error de conexión: ' . htmlspecialchars($e->getMessage()) . '</pre>');
    } else {
        die('<p style="text-align:center;margin-top:80px;color:#666;">Servicio temporalmente no disponible. Intentá en unos minutos.</p>');
    }
}