<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contacto - JuDy Simulator</title>
    <link rel="stylesheet" href="assets/css/styles.css">
    <style>
        .login-container { display: flex; justify-content: center; align-items: flex-start; padding: 40px 20px; }
        .login-box { background: var(--fondo-tarjeta); padding: 40px; border-radius: 12px; border: 1px solid var(--borde-color); width: 100%; max-width: 600px; }
        .input-group { margin-bottom: 20px; text-align: left; }
        .input-group label { display: block; margin-bottom: 8px; font-weight: 600; }
        .input-group input, .input-group textarea { width: 100%; padding: 12px; border-radius: 6px; border: 1px solid var(--borde-color); background: var(--fondo-principal); color: var(--letras-principales); font-size: 16px; font-family: inherit; }
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
            <li><a href="index.php">Inicio</a></li>
            <li><a href="seguimiento.php">Seguimiento</a></li>
            <li><a href="avances.php">Avances VR</a></li>
            <li><a href="acerca.php">Acerca de</a></li>
            <li><a href="contacto.php">¡Contáctanos!</a></li>
        </ul>
    </nav>

    <div class="login-container">
        <div class="login-box">
            <h2 style="margin-bottom: 15px; text-align: center; font-size: 32px;">¡Contáctanos!</h2>
            
            <?php if(isset($_GET['enviado'])): ?>
                <p style="color: #22c55e; font-weight: bold; text-align: center; margin-bottom: 20px;">¡Mensaje enviado con éxito! Nos pondremos en contacto pronto.</p>
            <?php else: ?>
                <p style="text-align: center; color: var(--letras-secundarias); margin-bottom: 30px;">¿Dudas sobre el simulador VR o las normativas STPS? Escríbenos.</p>
            <?php endif; ?>

            <form action="procesar_contacto.php" method="POST">
                <div class="input-group">
                    <label>Nombre Completo</label>
                    <input type="text" name="nombre" required>
                </div>
                <div class="input-group">
                    <label>Correo Electrónico</label>
                    <input type="email" name="correo" required>
                </div>
                <div class="input-group">
                    <label>Mensaje</label>
                    <textarea name="mensaje" rows="4" required></textarea>
                </div>
                <button type="submit" class="btn-registro" style="width: 100%; border: none; cursor: pointer; padding: 15px;">Enviar Mensaje</button>
            </form>
        </div>
    </div>
</body>
</html>