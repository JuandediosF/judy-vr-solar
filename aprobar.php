<?php
// ============================================================
//  aprobar.php — Procesa el enlace de aprobación por correo.
//  El token se valida antes de mostrar el formulario; el rol
//  se envía por POST para que un simple clic no apruebe nada.
// ============================================================
require_once __DIR__ . '/config.php';

$matricula      = $_GET['m'] ?? $_POST['m'] ?? '';
$token_recibido = $_GET['t'] ?? $_POST['t'] ?? '';

// Verificar que todos los parámetros llegaron
if ($matricula === '' || $token_recibido === '') {
    mostrarMensaje('error', 'Enlace incompleto o dañado. Por favor contacta al administrador.');
    exit();
}

// Buscar al usuario pendiente con esa matrícula
$sql  = 'SELECT id_usuario, token_aprobacion, estatus, token_expira_en
           FROM usuarios
          WHERE matricula = ?
          LIMIT 1';
$stmt = $conexion->prepare($sql);
$stmt->bind_param('s', $matricula);
$stmt->execute();
$fila = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$fila) {
    mostrarMensaje('error', 'No se encontró ningún usuario con esa matrícula.');
    exit();
}

if ($fila['estatus'] === 'aprobado') {
    mostrarMensaje('info', 'Este usuario ya había sido aprobado anteriormente.');
    exit();
}

// Comparar el token recibido con el almacenado (hash_equals evita timing attacks)
if (!hash_equals($fila['token_aprobacion'] ?? '', $token_recibido)) {
    mostrarMensaje('error', 'Token de seguridad inválido. El enlace puede haber expirado o ya fue usado.');
    exit();
}

// El enlace debe tener fecha de expiración y estar vigente
if (empty($fila['token_expira_en']) || strtotime($fila['token_expira_en']) < time()) {
    mostrarMensaje('error', 'Este enlace de aprobación ha expirado. Solicita un nuevo registro.');
    exit();
}

// Si viene del formulario, procesar la aprobación con el rol elegido
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $rol_asignado = $_POST['r'] ?? '';

    if ($rol_asignado !== 'empleado' && $rol_asignado !== 'gerencia') {
        mostrarMensaje('error', 'El rol especificado no es válido.');
        exit();
    }

    // Actualizar: aprobar, asignar rol e invalidar el token (un solo uso)
    $sql2  = "UPDATE usuarios
                 SET estatus = 'aprobado', rol = ?, token_aprobacion = NULL, token_expira_en = NULL
               WHERE matricula = ? AND estatus = 'pendiente' AND token_aprobacion = ?";
    $stmt2 = $conexion->prepare($sql2);
    $stmt2->bind_param('sss', $rol_asignado, $matricula, $token_recibido);
    $stmt2->execute();

    if ($stmt2->affected_rows > 0) {
        mostrarMensaje('exito', '¡Usuario aprobado con éxito! Se le asignó la jerarquía de <strong>' . strtoupper(htmlspecialchars($rol_asignado)) . '</strong>.');
    } else {
        mostrarMensaje('error', 'No se pudo actualizar el usuario. Puede que ya haya sido procesado.');
    }
    $stmt2->close();
    exit();
}

// GET: mostrar confirmación para elegir jerarquía
mostrarFormulario($matricula, $token_recibido);
exit();

// ============================================================
//  Funciones auxiliares
// ============================================================
function mostrarFormulario(string $matricula, string $token): void {
    $m = htmlspecialchars($matricula, ENT_QUOTES, 'UTF-8');
    $t = htmlspecialchars($token, ENT_QUOTES, 'UTF-8');
    echo "<!DOCTYPE html>
<html lang='es'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Aprobar Usuario — JuDy Simulator</title>
    <link rel='stylesheet' href='assets/css/styles.css'>
</head>
<body>
    <div style='display:flex; flex-direction:column; align-items:center; justify-content:center;
                min-height:100vh; text-align:center; padding:20px; font-family:sans-serif;'>
        <img src='assets/img/logo_blanco.jpg' alt='Logo JuDy Simulator'
             style='height:60px; border-radius:6px; margin-bottom:30px;'>
        <div style='background:#f8fafc; border:1px solid #e2e8f0; border-radius:12px;
                    padding:40px; max-width:500px; width:100%; color:#0f172a;'>
            <h2 style='margin-bottom:10px;'>Aprobar solicitud</h2>
            <p style='margin-bottom:25px;'>Matrícula: <strong>{$m}</strong></p>
            <form method='POST' action='aprobar.php'>
                <input type='hidden' name='m' value='{$m}'>
                <input type='hidden' name='t' value='{$t}'>
                <button type='submit' name='r' value='empleado'
                        style='display:inline-block; padding:12px 20px; margin:5px; background:#22c55e;
                               color:#fff; border:none; border-radius:5px; font-weight:bold; cursor:pointer;'>
                    Aprobar como EMPLEADO
                </button>
                <button type='submit' name='r' value='gerencia'
                        style='display:inline-block; padding:12px 20px; margin:5px; background:#f37021;
                               color:#fff; border:none; border-radius:5px; font-weight:bold; cursor:pointer;'>
                    Aprobar como GERENCIA
                </button>
            </form>
        </div>
    </div>
</body>
</html>";
}

function mostrarMensaje(string $tipo, string $texto): void {
    $colores = [
        'exito' => '#22c55e',
        'info'  => '#f59e0b',
        'error' => '#dc2626',
    ];
    $color = $colores[$tipo] ?? '#0b132b';
    echo "<!DOCTYPE html>
<html lang='es'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Aprobación de Usuario — JuDy Simulator</title>
    <link rel='stylesheet' href='assets/css/styles.css'>
</head>
<body>
    <div style='display:flex; flex-direction:column; align-items:center; justify-content:center;
                min-height:100vh; text-align:center; padding: 20px; font-family: sans-serif;'>
        <img src='assets/img/logo_blanco.jpg' alt='Logo JuDy Simulator'
             style='height:60px; border-radius:6px; margin-bottom:30px;'>
        <div style='background:#f8fafc; border:1px solid #e2e8f0; border-radius:12px;
                    padding:40px; max-width:500px; width:100%;'>
            <p style='font-size:20px; color:{$color}; font-weight:bold; margin-bottom:20px;'>{$texto}</p>
            <a href='login.php'
               style='display:inline-block; padding:12px 24px; background:#0b132b; color:#fff;
                      text-decoration:none; border-radius:8px; font-weight:bold;'>
                Ir al Inicio de Sesión
            </a>
        </div>
    </div>
</body>
</html>";
}
