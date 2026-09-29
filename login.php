<?php
// login.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Si ya está logueado, lo mandamos directo al panel
if (isset($_SESSION['id_usuario'])) {
    header("Location: dashboard.php");
    exit;
}

// Mensaje de error proveniente de validar_login.php
$error = '';
$tipos_error = [
    'credenciales'    => 'El email o la contraseña no son correctos. Revisalos e intentá de nuevo.',
    'debe_loguearse'  => 'Necesitás iniciar sesión para acceder a esa sección.',
    'sesion_expirada' => 'Tu sesión expiró. Por favor, ingresá nuevamente.',
];
if (isset($_GET['error']) && array_key_exists($_GET['error'], $tipos_error)) {
    $error = $tipos_error[$_GET['error']];
}

// Email pre-cargado si viene de un redirect (mejor UX)
$email_previo = htmlspecialchars($_GET['email'] ?? '');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión — Momentia</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Ms+Madi&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/global.css">
    <link rel="stylesheet" href="css/formularios.css">
    <style>
        body {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        .auth-page-wrapper {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px 20px;
            background: linear-gradient(135deg, #fdfbf7 0%, #f5f0e6 100%);
        }
        .auth-card {
            background: #ffffff;
            padding: 40px 35px;
            border-radius: 20px;
            box-shadow: 0 10px 40px rgba(166, 139, 109, 0.12);
            width: 100%;
            max-width: 400px;
            border-top: 4px solid var(--color-principal);
        }
        .auth-card-header {
            text-align: center;
            margin-bottom: 28px;
        }
        .auth-card-header h1 {
            font-size: 1.6rem;
            color: #2c3e50;
            margin: 0 0 6px 0;
        }
        .auth-card-header p {
            color: #7a6e67;
            font-size: 0.95rem;
            margin: 0;
        }
        .alert-error {
            background: #fdf2f2;
            border: 1px solid #f5c6c6;
            border-left: 4px solid #e74c3c;
            color: #922b21;
            padding: 12px 15px;
            border-radius: 8px;
            font-size: 0.9rem;
            margin-bottom: 20px;
            line-height: 1.5;
        }
        .alert-info {
            background: #fdf8ef;
            border: 1px solid #f0ddb8;
            border-left: 4px solid var(--color-principal);
            color: #7a5c2e;
            padding: 12px 15px;
            border-radius: 8px;
            font-size: 0.9rem;
            margin-bottom: 20px;
        }
        .input-wrapper {
            position: relative;
        }
        .input-wrapper input {
            width: 100%;
            box-sizing: border-box;
            padding: 12px 14px;
            border: 1.5px solid #ddd;
            border-radius: 10px;
            font-size: 1rem;
            font-family: inherit;
            transition: border-color 0.25s, box-shadow 0.25s;
            background: #fdfcfb;
        }
        .input-wrapper input:focus {
            outline: none;
            border-color: var(--color-principal);
            box-shadow: 0 0 0 3px rgba(166, 139, 109, 0.12);
            background: #ffffff;
        }
        /* Ojo para mostrar/ocultar contraseña */
        .btn-toggle-pass {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            cursor: pointer;
            color: #94a3b8;
            font-size: 1.1rem;
            padding: 0;
            line-height: 1;
            transition: color 0.2s;
        }
        .btn-toggle-pass:hover { color: var(--color-principal); }

        .forgot-link {
            display: block;
            text-align: right;
            font-size: 0.82rem;
            color: #94a3b8;
            text-decoration: none;
            margin-top: 6px;
            transition: color 0.2s;
        }
        .forgot-link:hover { color: var(--color-principal); }

        .btn-auth {
            width: 100%;
            background: var(--color-principal);
            color: white;
            border: none;
            padding: 13px;
            border-radius: 25px;
            cursor: pointer;
            font-size: 1rem;
            font-weight: bold;
            letter-spacing: 0.5px;
            margin-top: 10px;
            transition: background 0.2s, transform 0.15s;
        }
        .btn-auth:hover {
            background: #8e7356;
            transform: translateY(-1px);
        }
        .btn-auth:active { transform: translateY(0); }

        .auth-footer-links {
            text-align: center;
            margin-top: 22px;
            font-size: 0.9rem;
            color: #7a6e67;
        }
        .auth-footer-links a {
            color: var(--color-principal);
            font-weight: bold;
            text-decoration: none;
        }
        .auth-footer-links a:hover { text-decoration: underline; }

        .divider {
            display: flex;
            align-items: center;
            gap: 10px;
            margin: 20px 0;
            color: #c5b9b0;
            font-size: 0.8rem;
        }
        .divider::before, .divider::after {
            content: '';
            flex: 1;
            height: 1px;
            background: #e8e0d8;
        }
    </style>
</head>
<body>

    <?php include_once 'header.php'; ?>

    <div class="auth-page-wrapper">
        <div class="auth-card">

            <div class="auth-card-header">
                <h1>Bienvenido/a de nuevo</h1>
                <p>Ingresá para gestionar tus invitaciones</p>
            </div>

            <?php if ($error): ?>
                <div class="alert-error" role="alert">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <?php if (isset($_GET['registro']) && $_GET['registro'] === 'ok'): ?>
                <div class="alert-info" role="status">
                    ¡Cuenta creada con éxito! Ya podés iniciar sesión.
                </div>
            <?php endif; ?>

            <form action="validar_login.php" method="POST" novalidate>

                <div class="form-group">
                    <label for="email">Email</label>
                    <div class="input-wrapper">
                        <input
                            type="email"
                            id="email"
                            name="email"
                            value="<?php echo $email_previo; ?>"
                            placeholder="tu@email.com"
                            autocomplete="email"
                            required
                        >
                    </div>
                </div>

                <div class="form-group">
                    <label for="password">Contraseña</label>
                    <div class="input-wrapper">
                        <input
                            type="password"
                            id="password"
                            name="password"
                            placeholder="••••••••"
                            autocomplete="current-password"
                            required
                        >
                        <button
                            type="button"
                            class="btn-toggle-pass"
                            onclick="togglePassword()"
                            aria-label="Mostrar u ocultar contraseña"
                            title="Mostrar/ocultar contraseña"
                        >👁</button>
                    </div>
                    <a href="recuperar_pass.php" class="forgot-link">¿Olvidaste tu contraseña?</a>
                </div>

                <button type="submit" class="btn-auth">Entrar</button>
            </form>

            <div class="divider">o</div>

            <div class="auth-footer-links">
                ¿Todavía no tenés cuenta?
                <a href="registro.php">Registrate gratis</a>
            </div>

        </div>
    </div>

    <?php include_once 'footer.php'; ?>

    <script>
        function togglePassword() {
            const campo = document.getElementById('password');
            const btn   = document.querySelector('.btn-toggle-pass');
            if (campo.type === 'password') {
                campo.type = 'text';
                btn.title = 'Ocultar contraseña';
            } else {
                campo.type = 'password';
                btn.title = 'Mostrar contraseña';
            }
        }
    </script>

</body>
</html>