<?php
// registro.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (isset($_SESSION['id_usuario'])) {
    header("Location: dashboard.php");
    exit;
}

// Errores del procesador de registro
$errores = [
    'email_existe'    => 'Ya existe una cuenta con ese email. ¿Querés <a href="login.php">iniciar sesión</a>?',
    'dni_existe'      => 'Ya existe una cuenta con ese DNI.',
    'pass_corta'      => 'La contraseña debe tener al menos 8 caracteres.',
    'error_generico'  => 'Ocurrió un error al crear la cuenta. Por favor, intentá de nuevo.',
];

$error = '';
if (isset($_GET['error']) && array_key_exists($_GET['error'], $errores)) {
    $error = $errores[$_GET['error']];
}

// Pre-cargar campos si hubo error (para no perder lo que escribió)
$prefill = [
    'nombre'   => htmlspecialchars($_GET['nombre']   ?? ''),
    'apellido' => htmlspecialchars($_GET['apellido'] ?? ''),
    'dni'      => htmlspecialchars($_GET['dni']      ?? ''),
    'telefono' => htmlspecialchars($_GET['telefono'] ?? ''),
    'email'    => htmlspecialchars($_GET['email']    ?? ''),
];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear Cuenta — Momentia</title>
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
            max-width: 460px;
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
        .alert-error a { color: #922b21; font-weight: bold; }

        .form-row-2 {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 14px;
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
        .input-wrapper input.campo-invalido {
            border-color: #e74c3c;
        }
        .msg-campo {
            font-size: 0.78rem;
            color: #e74c3c;
            margin-top: 4px;
            display: none;
        }
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

        /* Indicador de fortaleza de contraseña */
        .pass-strength {
            margin-top: 6px;
            display: flex;
            gap: 4px;
            align-items: center;
        }
        .pass-strength-bar {
            height: 4px;
            flex: 1;
            border-radius: 2px;
            background: #e8e0d8;
            transition: background 0.3s;
        }
        .pass-strength-label {
            font-size: 0.75rem;
            color: #94a3b8;
            min-width: 52px;
            text-align: right;
        }

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

        .terms-text {
            font-size: 0.8rem;
            color: #94a3b8;
            text-align: center;
            margin-top: 14px;
            line-height: 1.5;
        }
        .terms-text a {
            color: var(--color-principal);
            text-decoration: none;
        }

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

        .auth-footer-links {
            text-align: center;
            font-size: 0.9rem;
            color: #7a6e67;
        }
        .auth-footer-links a {
            color: var(--color-principal);
            font-weight: bold;
            text-decoration: none;
        }
        .auth-footer-links a:hover { text-decoration: underline; }

        @media (max-width: 480px) {
            .form-row-2 { grid-template-columns: 1fr; }
            .auth-card { padding: 30px 20px; }
        }
    </style>
</head>
<body>

    <?php include_once 'header.php'; ?>

    <div class="auth-page-wrapper">
        <div class="auth-card">

            <div class="auth-card-header">
                <h1>Creá tu cuenta</h1>
                <p>Es gratis y te lleva menos de un minuto</p>
            </div>

            <?php if ($error): ?>
                <div class="alert-error" role="alert">
                    <?php echo $error; /* ya contiene HTML seguro del array $errores */ ?>
                </div>
            <?php endif; ?>

            <form action="procesar_registro.php" method="POST" id="formRegistro" novalidate>

                <div class="form-row-2">
                    <div class="form-group">
                        <label for="nombre">Nombre</label>
                        <div class="input-wrapper">
                            <input
                                type="text"
                                id="nombre"
                                name="nombre"
                                value="<?php echo $prefill['nombre']; ?>"
                                placeholder="Ana"
                                autocomplete="given-name"
                                required
                            >
                        </div>
                        <span class="msg-campo" id="err-nombre">Ingresá tu nombre</span>
                    </div>
                    <div class="form-group">
                        <label for="apellido">Apellido</label>
                        <div class="input-wrapper">
                            <input
                                type="text"
                                id="apellido"
                                name="apellido"
                                value="<?php echo $prefill['apellido']; ?>"
                                placeholder="García"
                                autocomplete="family-name"
                                required
                            >
                        </div>
                        <span class="msg-campo" id="err-apellido">Ingresá tu apellido</span>
                    </div>
                </div>

                <div class="form-row-2">
                    <div class="form-group">
                        <label for="dni">DNI</label>
                        <div class="input-wrapper">
                            <input
                                type="text"
                                id="dni"
                                name="dni"
                                value="<?php echo $prefill['dni']; ?>"
                                placeholder="Sin puntos"
                                inputmode="numeric"
                                maxlength="8"
                                required
                            >
                        </div>
                        <span class="msg-campo" id="err-dni">DNI inválido (solo números)</span>
                    </div>
                    <div class="form-group">
                        <label for="telefono">Teléfono</label>
                        <div class="input-wrapper">
                            <input
                                type="tel"
                                id="telefono"
                                name="telefono"
                                value="<?php echo $prefill['telefono']; ?>"
                                placeholder="+54 9 351…"
                                autocomplete="tel"
                            >
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="email">Email</label>
                    <div class="input-wrapper">
                        <input
                            type="email"
                            id="email"
                            name="email"
                            value="<?php echo $prefill['email']; ?>"
                            placeholder="tu@email.com"
                            autocomplete="email"
                            required
                        >
                    </div>
                    <span class="msg-campo" id="err-email">Ingresá un email válido</span>
                </div>

                <div class="form-group">
                    <label for="password">Contraseña</label>
                    <div class="input-wrapper">
                        <input
                            type="password"
                            id="password"
                            name="password"
                            placeholder="Mínimo 8 caracteres"
                            autocomplete="new-password"
                            required
                            oninput="evaluarPassword(this.value)"
                        >
                        <button
                            type="button"
                            class="btn-toggle-pass"
                            onclick="togglePassword()"
                            aria-label="Mostrar u ocultar contraseña"
                        >👁</button>
                    </div>
                    <div class="pass-strength" id="passStrength" aria-hidden="true">
                        <div class="pass-strength-bar" id="bar1"></div>
                        <div class="pass-strength-bar" id="bar2"></div>
                        <div class="pass-strength-bar" id="bar3"></div>
                        <div class="pass-strength-bar" id="bar4"></div>
                        <span class="pass-strength-label" id="passLabel"></span>
                    </div>
                    <span class="msg-campo" id="err-pass">Mínimo 8 caracteres</span>
                </div>

                <button type="submit" class="btn-auth" id="btnRegistrar">Crear mi cuenta</button>

                <p class="terms-text">
                    Al registrarte aceptás nuestros
                    <a href="terminos.php">Términos de Uso</a> y
                    <a href="privacidad.php">Política de Privacidad</a>.
                </p>
            </form>

            <div class="divider">o</div>

            <div class="auth-footer-links">
                ¿Ya tenés cuenta? <a href="login.php">Iniciar sesión</a>
            </div>

        </div>
    </div>

    <?php include_once 'footer.php'; ?>

    <script>
        // Mostrar/ocultar contraseña
        function togglePassword() {
            const campo = document.getElementById('password');
            campo.type = campo.type === 'password' ? 'text' : 'password';
        }

        // Indicador de fortaleza de contraseña
        const colores = ['#e74c3c', '#e67e22', '#f1c40f', '#27ae60'];
        const etiquetas = ['Débil', 'Regular', 'Buena', 'Fuerte'];

        function evaluarPassword(val) {
            let puntaje = 0;
            if (val.length >= 8)  puntaje++;
            if (/[A-Z]/.test(val)) puntaje++;
            if (/[0-9]/.test(val)) puntaje++;
            if (/[^A-Za-z0-9]/.test(val)) puntaje++;

            const bars = ['bar1','bar2','bar3','bar4'];
            bars.forEach((id, i) => {
                document.getElementById(id).style.background = i < puntaje
                    ? colores[puntaje - 1]
                    : '#e8e0d8';
            });
            document.getElementById('passLabel').textContent = val.length > 0
                ? etiquetas[puntaje - 1] || ''
                : '';
        }

        // Validación cliente antes de enviar
        document.getElementById('formRegistro').addEventListener('submit', function(e) {
            let ok = true;

            const validar = (id, errId, condicion) => {
                const campo = document.getElementById(id);
                const msg   = document.getElementById(errId);
                if (condicion) {
                    campo.classList.add('campo-invalido');
                    msg.style.display = 'block';
                    ok = false;
                } else {
                    campo.classList.remove('campo-invalido');
                    if (msg) msg.style.display = 'none';
                }
            };

            validar('nombre',   'err-nombre',   !document.getElementById('nombre').value.trim());
            validar('apellido', 'err-apellido', !document.getElementById('apellido').value.trim());
            validar('dni',      'err-dni',      !/^\d{7,8}$/.test(document.getElementById('dni').value.trim()));
            validar('email',    'err-email',    !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(document.getElementById('email').value));
            validar('password', 'err-pass',     document.getElementById('password').value.length < 8);

            if (!ok) e.preventDefault();
        });

        // Limpiar error al escribir
        document.querySelectorAll('input').forEach(input => {
            input.addEventListener('input', function() {
                this.classList.remove('campo-invalido');
                const errId = 'err-' + this.id;
                const msg = document.getElementById(errId);
                if (msg) msg.style.display = 'none';
            });
        });
    </script>

</body>
</html>