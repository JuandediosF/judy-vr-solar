<?php
// ============================================================
//  config.sample.php — Plantilla de configuración
//  Copia este archivo como "config.php" en tu servidor (htdocs)
//  y completa tus credenciales reales de InfinityFree y Gmail.
// ============================================================

// --- Configuración de errores en producción (InfinityFree) --
ini_set('display_errors', '0');
ini_set('display_startup_errors', '0');
error_reporting(0);

// --- Base de datos InfinityFree (Ver datos en vPanel / MySQL)
define('DB_HOST', 'sqlXXX.infinityfree.com'); // Host MySQL asignado
define('DB_USER', 'if0_XXXXXXXX');             // Usuario vPanel
define('DB_PASS', 'TU_PASSWORD_VPANEL');       // Contraseña de la cuenta
define('DB_NAME', 'if0_XXXXXXXX_judyb');       // Nombre de BD creada

// --- Correo (SMTP Gmail con contraseña de aplicación) --------
define('MAIL_HOST',     'smtp.gmail.com');
define('MAIL_PORT',     587);
define('MAIL_USER',     'tu_correo@gmail.com');
define('MAIL_PASS',     'tu_app_password_de_gmail');
define('MAIL_FROM_NAME','JuDy Simulator System');

// --- URL pública del dominio en InfinityFree ----------------
define('APP_URL', 'https://judysimulator.wuaze.com');

// --- Conexión centralizada a la base de datos ---------------
if (!isset($conexion) || !($conexion instanceof mysqli)) {
    $conexion = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if ($conexion->connect_error) {
        error_log('Error de conexion MySQL: ' . $conexion->connect_error);
        die('Error de conexión a la base de datos.');
    }
    $conexion->set_charset('utf8mb4');
}
