<?php
// header.php
// ¡El primer renglón debe ser siempre esto!
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

?>
<header class="main-header">
    <div class="header-container">

        <a href="index.php" class="brand-wrapper" style="text-decoration: none;">
            <img src="img/momentia.svg" alt="Logo Momentia" class="header-logo">
            <div class="brand-text">
                 <!--<h1 class="brand-name">MOMENTIA</h1> -->
            </div>
        </a>

        <nav class="user-actions" aria-label="Acciones de usuario">
            <?php if (isset($_SESSION['id_usuario'])): ?>

                <?php
                    // Nombre seguro sin fallback hardcodeado
                    $nombre_display = htmlspecialchars(
                        $_SESSION['nombre_usuario'] ?? $_SESSION['nombre'] ?? 'Usuario'
                    );
                ?>
                <div class="dropdown" role="navigation" aria-label="Menú de usuario">
                    <button
                        class="user-welcome dropdown-toggle"
                        aria-haspopup="true"
                        aria-expanded="false"
                        type="button"
                    >
                        Hola, <strong><?php echo $nombre_display; ?></strong>! ▾
                    </button>
                    <div class="dropdown-content" role="menu">
                        <a href="dashboard.php" role="menuitem">
                            <span aria-hidden="true">📋</span> Mi Panel
                        </a>
                        <a href="cambiar_pass.php" role="menuitem">
                            <span aria-hidden="true">🔑</span> Cambiar Contraseña
                        </a>
                        <a href="ajustes.php" role="menuitem">
                            <span aria-hidden="true">⚙️</span> Ajustes
                        </a>
                        <hr style="border: 0; border-top: 1px solid #eee; margin: 5px 0;" role="separator">
                        <a href="logout.php" role="menuitem" class="menu-item-danger">
                            <span aria-hidden="true">🚪</span> Cerrar sesión
                        </a>
                    </div>
                </div>

            <?php else: ?>

                <div class="auth-buttons-wrapper">
                    <a href="login.php" class="btn-login-header">Iniciar sesión</a>
                    <a href="registro.php" class="btn-primary-header">Registrarse</a>
                </div>

            <?php endif; ?>
        </nav>

    </div>
</header>
