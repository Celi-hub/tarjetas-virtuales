<?php
require_once __DIR__ . '/conexion.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
// Usamos el operador ?? para evitar el "Undefined array key" si algo falta
    $nombre   = $_POST['nombre'] ?? '';
    $apellido = $_POST['apellido'] ?? '';
    $dni      = $_POST['dni'] ?? '';
    $tel      = $_POST['telefono'] ?? '';
    $email    = $_POST['email'] ?? '';
    $pass     = $_POST['password'] ?? '';

    $nombre = trim($nombre);
    $apellido = trim($apellido);
    $email = trim($email);
    if ($nombre === '' || $apellido === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($pass) < 8) {
        die("<p style='color:red;font-family:sans-serif;'>Datos inválidos. Revisá el formulario (contraseña de al menos 8 caracteres). <a href='registro.php'>Volver</a></p>");
    }
   
 try {
        // PASO A: Verificar si el email ya existe (Consulta de Lectura)
        $checkEmail = $pdo->prepare("SELECT id_cliente FROM clientes WHERE email = ?");
        $checkEmail->execute([$email]);
     if ($checkEmail->fetch()) {
            // Si el fetch devuelve algo, es que ya existe
            die("<div style='color:red; font-family:sans-serif;'>
                    <h3>¡Atención!</h3>
                    <p>El email <strong>" . htmlspecialchars($email) . "</strong> ya está registrado. 
                    <a href='registro.php'>Intentar con otro</a> o <a href='login.php'>Iniciar Sesión</a>.</p>
                 </div>");
        }
  
    $pass_encriptada = password_hash($pass, PASSWORD_BCRYPT);

        // PASO B: Si llegamos acá, el mail es nuevo. Encriptamos y guardamos.
            $pass_encriptada = password_hash($pass, PASSWORD_BCRYPT);

            $sql = "INSERT INTO clientes (nombre_cliente, apellido_cliente, email, password, dni_cliente, telefono) 
                    VALUES (:nom, :ape, :email, :pass, :dni, :tel)";

            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':nom'   => $nombre,
                ':ape'   => $apellido,
                ':dni'   => $dni,
                ':tel'   => $tel,
                ':email' => $email,
                ':pass'  => $pass_encriptada
            ]);
     //   echo "¡Excelente $nombre registro exitoso! <a href='index.php'>Volver al catálogo de plantillas</a>";

     echo "<div style='color:green; font-family:sans-serif;'>
                <h3>¡Bienvenido/a, " . htmlspecialchars($nombre) . "!</h3>
                <p>Tu registro se completó con éxito.</p>
                <a href='index.php'>Ver catálogo de tarjetas</a>
              </div>";
     
 } catch (PDOException $e) {
        // En producción, esto se guarda en un log oculto, no se le muestra al cliente
        error_log($e->getMessage());
        echo "Lo sentimos, hubo un problema técnico. Por favor, intentá más tarde.";
    }
}
?>