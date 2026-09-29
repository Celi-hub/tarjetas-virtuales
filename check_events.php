<?php
require_once 'conexion.php';
try {
    $stmt = $pdo->query("SELECT * FROM tipos_evento");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo $row['id_tipos_evento'] . " - " . $row['nombre'] . "\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
