<?php
// Activador de errores
ini_set('display_errors', 1);
error_reporting(E_ALL);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión - JuDy Simulator</title>
    <link rel="stylesheet" href="assets/css/styles.css">
    <style>
        .login-container { display: flex; justify-content: center; align-items: center; min-height: calc(100vh - 80px); padding: 20px; }
        .login-box { background: var(--fondo-tarjeta); padding: 40px; border-radius: 12px; border: 1px solid var(--borde-color); width: 100%; max-width: 400px; box-shadow: 0 10px 30px rgba(0,0,0,0.1); }
        .input-group { margin-bottom: 20px; }
        .input-group label { display: block; margin-bottom: 8px; font-weight: 600; }
        .input-group input { width: 100%; padding: 12px; border-radius: 6px; border: 1px solid var(--borde-color); background: var(--fondo-principal); color: var(--letras-principales); font-size: 16px; }
        .error-msg { color: #dc2626; font-size: 14px; margin-bottom: 15px; font-weight: bold; }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="navinicio">
            <a href="index.php" class="logo-container">
                <picture>
                    <source srcset="assets/img/logo_oscuro.jpg" media="(prefers-color-scheme: dark)">
                    <img src="assets/img/logo_blanco.jpg" alt="Logo" class="nav-logo">
                </picture>
                <div class="navtitulo">Ju<span class="navtitulo1">Dy</span> Simulator</div>
            </a>
        </div>
        <ul class="navlinks">
            <li><a href="index.php">Volver al Inicio</a></li>
        </ul>
    </nav>

    <div class="login-container">
        <div class="login-box">
            <h2 style="margin-bottom: 25px; text-align: center;">Acceso al Panel</h2>
            
            <?php
            // Manejo de mensajes de error o éxito
            if(isset($_GET['error'])) {
                if($_GET['error'] == '1') {
                    echo '<p class="error-msg">Matrícula o contraseña incorrectos.</p>';
                } elseif($_GET['error'] == 'pendiente') {
                    echo '<p class="error-msg" style="color: #f59e0b;">Tu cuenta está pendiente de aprobación por el administrador.</p>';
                }
            }
            if(isset($_GET['registro'])) {
                echo '<p style="color: #22c55e; font-weight: bold; margin-bottom:15px;">Registro exitoso. Espera la aprobación por correo.</p>';
            }
            ?>

            <form action="procesar_login.php" method="POST">
                <div class="input-group">
                    <label for="matricula">Matrícula</label>
                    <input type="text" id="matricula" name="matricula" placeholder="Ej. GER-001" required>
                </div>
                <div class="input-group">
                    <label for="password">Contraseña</label>
                    <input type="password" id="password" name="password" placeholder="••••••••" required>
                </div>
                <button type="submit" class="btn-registro" style="width: 100%; border: none; cursor: pointer; font-size: 18px;">Ingresar</button>
            </form>

            <!-- ENLACE PARA CREAR CUENTA -->
            <div style="text-align: center; margin-top: 25px;">
                <p style="color: var(--letras-secundarias); font-size: 15px;">
                    ¿No tienes cuenta? <a href="registro.php" style="color: var(--letras-especiales); font-weight: bold; text-decoration: none; transition: color 0.3s ease;">Solicita tu registro</a>
                </p>
            </div>

        </div>
    </div>
</body>
</html>