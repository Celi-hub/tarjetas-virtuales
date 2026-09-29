<?php
require_once __DIR__ . '/conexion.php';
require_once __DIR__ . '/config/modulos.php';

$id_tarjeta = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id_tarjeta === 0) {
    die('Tarjeta no especificada.');
}

$stmt = $pdo->prepare('
    SELECT t.*, e.nombre AS nombre_estilo, te.nombre AS nombre_evento
    FROM tarjetas t
    JOIN estilos_visuales e ON t.estilo_visual_id = e.id_estilos_visuales
    LEFT JOIN tipos_evento te ON t.tipo_evento_id = te.id_tipos_evento
    WHERE t.id_tarjeta = ?
');
$stmt->execute([$id_tarjeta]);
$tarjeta = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$tarjeta) {
    die('Tarjeta no encontrada.');
}

$lang           = 'es';
$archivo_idioma = "idiomas/{$lang}.php";
$t              = file_exists($archivo_idioma) ? require $archivo_idioma : [];

// Cargar y unificar los textos según el tipo de evento de la tarjeta
require_once __DIR__ . '/config/textos_evento.php';
if (isset($tarjeta['nombre_evento'])) {
    $textos_evento = textos_para_evento($tarjeta['nombre_evento']);
    $t = array_merge($t, $textos_evento);
}

// Unificar textos personalizados del usuario si existen
if (!empty($tarjeta['textos_personalizados'])) {
    $textos_personalizados = json_decode($tarjeta['textos_personalizados'], true);
    if (is_array($textos_personalizados)) {
        $t = array_merge($t, $textos_personalizados);
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/base_tarjeta.css">
    <link rel="stylesheet" href="css/<?php echo htmlspecialchars($tarjeta['nombre_estilo']); ?>.css">
    <?php if ($tarjeta['nombre_estilo'] === 'divertido'): ?>
        <script src="js/divertido-efectos.js" defer></script>
    <?php endif; ?>
</head>
<body>
<?php
include 'plantillas/modulos/base_portada.php';

foreach (plantillas_activas($tarjeta) as $modulo) {
    include 'plantillas/modulos/' . $modulo['archivo'];
}
?>
</body>
</html>
