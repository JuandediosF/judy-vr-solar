<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contacto - JuDy Simulator</title>
    <link rel="stylesheet" href="assets/css/styles.css">
    <style>
        .login-container {
            display: flex; justify-content: center; align-items: flex-start;
            min-height: calc(100vh - 80px); padding: 40px 20px;
        }
        .login-box {
            background: var(--fondo-tarjeta); padding: 40px;
            border-radius: 12px; border: 1px solid var(--borde-color);
            width: 100%; max-width: 600px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        .input-group { margin-bottom: 20px; text-align: left; }
        .input-group label { display: block; margin-bottom: 8px; font-weight: 600; }
        .input-group input, .input-group textarea {
            width: 100%; padding: 12px; border-radius: 6px;
            border: 1px solid var(--borde-color);
            background: var(--fondo-principal); color: var(--letras-principales);
            font-size: 16px; font-family: inherit;
        }
        .input-group textarea { resize: vertical; }
    </style>
</head>
<body>

    <nav class="navbar">
        <div class="navinicio">
            <a href="index.php" class="logo-container">
                <picture>
                    <source srcset="assets/img/logo_oscuro.jpg" media="(prefers-color-scheme: dark)">
                    <img src="assets/img/logo_blanco.jpg" alt="Logo JuDy Simulator" class="nav-logo">
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

        <div class="navacciones">
            <a href="login.php" class="btn-registro">Iniciar sesión</a>
        </div>
    </nav>

    <div class="login-container">
        <div class="login-box">
            <h2 style="margin-bottom: 15px; text-align: center; font-size: 32px;">¡Contáctanos!</h2>
            <p style="text-align: center; color: var(--letras-secundarias); margin-bottom: 30px;">
                ¿Interesado en implementar JuDy Simulator en tu empresa o institución educativa? Envíanos un mensaje.
            </p>
            
            <form action="#" method="POST">
                <div class="input-group">
                    <label for="nombre">Nombre Completo</label>
                    <input type="text" id="nombre" name="nombre" placeholder="Ej. Juan Pérez" required>
                </div>
                <div class="input-group">
                    <label for="correo">Correo Electrónico</label>
                    <input type="email" id="correo" name="correo" placeholder="tu@correo.com" required>
                </div>
                <div class="input-group">
                    <label for="mensaje">Mensaje</label>
                    <textarea id="mensaje" name="mensaje" rows="4" placeholder="Escribe tu mensaje aquí..." required></textarea>
                </div>
                <button type="submit" class="btn-registro" style="width: 100%; border: none; cursor: pointer; font-size: 18px; padding: 15px;">Enviar Mensaje</button>
            </form>
        </div>
    </div>

</body>
</html>