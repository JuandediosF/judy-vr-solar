<?php
session_start();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear Cuenta — JuDy Simulator</title>
    <link rel="stylesheet" href="assets/css/styles.css">
    <style>
        .login-container { display:flex; justify-content:center; align-items:center;
                           padding:40px 20px; min-height:calc(100vh - 80px); }
        .login-box       { background:var(--fondo-tarjeta); padding:40px; border-radius:12px;
                           border:1px solid var(--borde-color); width:100%; max-width:500px;
                           box-shadow:0 10px 30px rgba(0,0,0,0.1); }
        .input-group     { margin-bottom:20px; }
        .input-group label { display:block; margin-bottom:8px; font-weight:600; }
        .input-group input { width:100%; padding:12px; border-radius:6px;
                             border:1px solid var(--borde-color);
                             background:var(--fondo-principal);
                             color:var(--letras-principales); font-size:16px; }
    </style>
</head>
<body>

<?php
$nav_tipo = 'auth';
include __DIR__ . '/navbar.php';
?>

<div class="login-container">
    <div class="login-box">
        <h2 style="margin-bottom:25px; text-align:center;">Solicitud de Registro</h2>

        <?php
        $error = $_GET['error'] ?? '';
        if ($error === 'duplicado'):
        ?>
            <p style="color:#dc2626; font-size:14px; margin-bottom:15px; font-weight:bold; text-align:center;">
                La matrícula ingresada ya se encuentra registrada.
            </p>
        <?php elseif ($error === 'campos'): ?>
            <p style="color:#dc2626; font-size:14px; margin-bottom:15px; font-weight:bold; text-align:center;">
                Por favor completa todos los campos requeridos.
            </p>
        <?php endif; ?>

        <form action="procesar_registro.php" method="POST">
            <div class="input-group">
                <label for="matricula">Matrícula / ID</label>
                <input type="text" id="matricula" name="matricula"
                       placeholder="Ej. ALUM-123" autocomplete="username" required>
            </div>
            <div class="input-group">
                <label for="nombre">Nombre Completo</label>
                <input type="text" id="nombre" name="nombre"
                       placeholder="Ej. Juan Pérez" autocomplete="name" required>
            </div>
            <div class="input-group">
                <label for="correo">Correo Electrónico</label>
                <input type="email" id="correo" name="correo"
                       placeholder="ejemplo@correo.com" autocomplete="email" required>
            </div>
            <div class="input-group">
                <label for="telefono">Teléfono</label>
                <input type="tel" id="telefono" name="telefono"
                       placeholder="Ej. 4611234567" autocomplete="tel" required>
            </div>
            <div class="input-group">
                <label for="password">Contraseña</label>
                <input type="password" id="password" name="password"
                       placeholder="Mínimo 8 caracteres" autocomplete="new-password"
                       minlength="8" required>
            </div>
            <button type="submit" class="btn-registro"
                    style="width:100%; border:none; cursor:pointer; font-size:18px;">
                Enviar Solicitud
            </button>
        </form>

        <div style="text-align:center; margin-top:20px;">
            <p style="color:var(--letras-secundarias); font-size:15px;">
                ¿Ya tienes cuenta?
                <a href="login.php"
                   style="color:var(--letras-especiales); font-weight:bold; text-decoration:none;">
                    Inicia sesión
                </a>
            </p>
        </div>
    </div>
</div>

</body>
</html>