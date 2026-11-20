<?php
session_start();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión — JuDy Simulator</title>
    <link rel="stylesheet" href="assets/css/styles.css">
    <style>
        .login-container { display:flex; justify-content:center; align-items:center;
                           min-height:calc(100vh - 80px); padding:20px; }
        .login-box       { background:var(--fondo-tarjeta); padding:40px; border-radius:12px;
                           border:1px solid var(--borde-color); width:100%; max-width:400px;
                           box-shadow:0 10px 30px rgba(0,0,0,0.1); }
        .input-group     { margin-bottom:20px; }
        .input-group label { display:block; margin-bottom:8px; font-weight:600; }
        .input-group input { width:100%; padding:12px; border-radius:6px;
                             border:1px solid var(--borde-color);
                             background:var(--fondo-principal);
                             color:var(--letras-principales); font-size:16px; }
        .msg-error   { color:#dc2626; font-size:14px; margin-bottom:15px; font-weight:bold; }
        .msg-warning { color:#f59e0b; font-size:14px; margin-bottom:15px; font-weight:bold; }
        .msg-success { color:#22c55e; font-size:14px; margin-bottom:15px; font-weight:bold; }
    </style>
</head>
<body>

<?php
// Si ya tiene sesión activa, redirigir directamente al panel
if (isset($_SESSION['id_usuario'])) {
    header('Location: seguimiento.php');
    exit();
}
$nav_tipo = 'auth';
include __DIR__ . '/navbar.php';
?>

<div class="login-container">
    <div class="login-box">
        <h2 style="margin-bottom:25px; text-align:center;">Acceso al Panel</h2>

        <?php
        $error = $_GET['error'] ?? '';
        if ($error === '1'):
        ?>
            <p class="msg-error">Matrícula o contraseña incorrectos.</p>
        <?php elseif ($error === 'pendiente'): ?>
            <p class="msg-warning">Tu cuenta está pendiente de aprobación por el administrador.</p>
        <?php endif; ?>

        <?php if (isset($_GET['registro'])): ?>
            <p class="msg-success">✅ Registro exitoso. Espera la aprobación por correo.</p>
        <?php endif; ?>

        <form action="procesar_login.php" method="POST">
            <div class="input-group">
                <label for="matricula">Matrícula</label>
                <input type="text" id="matricula" name="matricula"
                       placeholder="Ej. GER-001" autocomplete="username" required>
            </div>
            <div class="input-group">
                <label for="password">Contraseña</label>
                <input type="password" id="password" name="password"
                       placeholder="••••••••" autocomplete="current-password" required>
            </div>
            <button type="submit" class="btn-registro"
                    style="width:100%; border:none; cursor:pointer; font-size:18px;">
                Ingresar
            </button>
        </form>

        <div style="text-align:center; margin-top:25px;">
            <p style="color:var(--letras-secundarias); font-size:15px;">
                ¿No tienes cuenta?
                <a href="registro.php"
                   style="color:var(--letras-especiales); font-weight:bold; text-decoration:none;">
                    Solicita tu registro
                </a>
            </p>
        </div>
    </div>
</div>

</body>
</html>