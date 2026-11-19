<?php
// ============================================================
//  aprobar.php — Procesa el enlace de aprobación por correo.
//  Valida el token aleatorio almacenado en la BD para evitar
//  autoaprobaciones o manipulación de roles.
// ============================================================
require_once 'config.php';

$matricula      = $_GET['m'] ?? '';
$token_recibido = $_GET['t'] ?? '';
$rol_asignado   = $_GET['r'] ?? '';

// Verificar que todos los parámetros llegaron
if ($matricula === '' || $token_recibido === '' || $rol_asignado === '') {
    mostrarMensaje('error', 'Enlace incompleto o dañado. Por favor contacta al administrador.');
    exit();
}

// Solo roles permitidos
if ($rol_asignado !== 'empleado' && $rol_asignado !== 'gerencia') {
    mostrarMensaje('error', 'El rol especificado no es válido.');
    exit();
}

$conexion = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($conexion->connect_error) {
    die('Error de conexión a la base de datos.');
}

// Buscar al usuario pendiente con esa matrícula
$sql  = 'SELECT id_usuario, token_aprobacion, estatus FROM usuarios WHERE matricula = ? LIMIT 1';
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

// Actualizar el usuario: aprobar, asignar rol y borrar el token (un solo uso)
$sql2  = "UPDATE usuarios SET estatus = 'aprobado', rol = ?, token_aprobacion = NULL WHERE matricula = ?";
$stmt2 = $conexion->prepare($sql2);
$stmt2->bind_param('ss', $rol_asignado, $matricula);
$stmt2->execute();

if ($stmt2->affected_rows > 0) {
    mostrarMensaje('exito', '¡Usuario aprobado con éxito! Se le asignó la jerarquía de <strong>' . strtoupper(htmlspecialchars($rol_asignado)) . '</strong>.');
} else {
    mostrarMensaje('error', 'No se pudo actualizar el usuario. Puede que ya haya sido procesado.');
}

$stmt2->close();
$conexion->close();

// ============================================================
//  Función auxiliar: muestra un mensaje HTML simple y limpio
// ============================================================
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