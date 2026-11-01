<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear Cuenta - JuDy Simulator</title>
    <link rel="stylesheet" href="assets/css/styles.css">
    <style>
        .login-container { display: flex; justify-content: center; align-items: center; padding: 40px 20px; }
        .login-box { background: var(--fondo-tarjeta); padding: 40px; border-radius: 12px; border: 1px solid var(--borde-color); width: 100%; max-width: 500px; }
        .input-group { margin-bottom: 20px; }
        .input-group label { display: block; margin-bottom: 8px; font-weight: 600; }
        .input-group input, .input-group select { width: 100%; padding: 12px; border-radius: 6px; border: 1px solid var(--borde-color); background: var(--fondo-principal); color: var(--letras-principales); font-size: 16px; }
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
        <ul class="navlinks"><li><a href="index.php">Volver al Inicio</a></li></ul>
    </nav>

    <div class="login-container">
        <div class="login-box">
            <h2 style="margin-bottom: 25px; text-align: center;">Crear Cuenta de Operador</h2>
            <form action="procesar_registro.php" method="POST">
                <div class="input-group">
                    <label>Matrícula Escolar</label>
                    <input type="text" name="matricula" placeholder="Ej. ALUM-123" required>
                </div>
                <div class="input-group">
                    <label>Nombre Completo</label>
                    <input type="text" name="nombre" placeholder="Ej. Juan Pérez" required>
                </div>
                <div class="input-group">
                    <label>Correo Electrónico</label>
                    <input type="email" name="correo" required>
                </div>
                <div class="input-group">
                    <label>Teléfono</label>
                    <input type="text" name="telefono" required>
                </div>
                <div class="input-group">
                    <label>Contraseña</label>
                    <input type="password" name="password" required>
                </div>
                <button type="submit" class="btn-registro" style="width: 100%; border: none; cursor: pointer; font-size: 18px;">Solicitar Registro</button>
            </form>
        </div>
    </div>
</body>
</html>