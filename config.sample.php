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
define('DB_HOST', 'sql310.infinityfree.com');  // Host MySQL asignado
define('DB_USER', 'if0_43331816');             // Usuario vPanel
define('DB_PASS', 'TU_PASSWORD_VPANEL');       // Contraseña de la cuenta (llenar en htdocs)
define('DB_NAME', 'if0_43331816_JuDyb');       // Nombre de BD del proyecto

// --- Correo (SMTP Gmail con contraseña de aplicación) --------
define('MAIL_HOST',     'smtp.gmail.com');
define('MAIL_PORT',     587);
define('MAIL_USER',     'tu_correo@gmail.com');
define('MAIL_PASS',     'tu_app_password_de_gmail');
define('MAIL_FROM_NAME','JuDy Simulator System');

// --- URL pública del dominio en InfinityFree ----------------
define('APP_URL', 'https://judysimulator.wuaze.com');

// --- Chatbot con Gemini (solo servidor; NUNCA exponer al navegador) --
// Obtén tu clave en https://aistudio.google.com/app/apikey
// Cámbiala en config.php del servidor por tu clave real.
define('GEMINI_API_KEY', 'TU_GEMINI_API_KEY');
define('GEMINI_MODEL',   'gemini-3.8-flash');

// --- Conexión centralizada a la base de datos ---------------
if (!isset($conexion) || !($conexion instanceof mysqli)) {
    $conexion = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if ($conexion->connect_error) {
        error_log('Error de conexion MySQL: ' . $conexion->connect_error);
        die('Error de conexión a la base de datos.');
    }
    $conexion->set_charset('utf8mb4');
}
