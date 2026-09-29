<?php
require_once __DIR__ . '/conexion.php';
session_start();

$ids = $_POST['tarjetas_seleccionadas'] ?? [];

if (empty($ids)) {
    die("No seleccionaste ninguna tarjeta.");
}

// 1. Obtenemos los costos de la BD
$query_costos = $pdo->query("SELECT modulo_key, precio FROM costos");
$costos = $query_costos->fetchAll(PDO::FETCH_KEY_PAIR);

// 2. Buscamos solo las tarjetas seleccionadas
$placeholders = implode(',', array_fill(0, count($ids), '?'));
$stmt = $pdo->prepare("SELECT * FROM tarjetas WHERE id_tarjeta IN ($placeholders) AND id_cliente = ?");
$params = array_merge($ids, [$_SESSION['id_usuario']]);
$stmt->execute($params);
$tarjetas_a_pagar = $stmt->fetchAll();

// 3. Calculamos el total de la suma de las tarjetas seleccionadas
$total_final = 0;
foreach ($tarjetas_a_pagar as $t) {
    $total_final += $costos['base'];
    // Sumar módulos (usando los nombres de tus columnas reales)
    if ($t['mod_cuenta_regresiva']) $total_final += $costos['mod_cuenta_regresiva'];
    if ($t['mod_musica']) $total_final += $costos['mod_musica'];
    // ... repetir para el resto
}
?>

<div class="resumen">
    <h3>Total a abonar: $<?php echo number_format($total_final, 2, ',', '.'); ?></h3>
    <p>Por favor, realiza la transferencia a la siguiente cuenta:</p>
    <p>CBU: XXXXXXXXXXXXXXX</p>
    <p>Alias: TU.ALIAS.DE.BANCO</p>
    
    <form action="confirmar_pago.php" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="monto" value="<?php echo $total_final; ?>">
        <label>Subir comprobante de transferencia:</label>
        <input type="file" name="comprobante" required>
        <button type="submit">Enviar Comprobante</button>
    </form>
</div>